<?php
use Doctrine\Common\ClassLoader;
require_once APPS_DIR.'plugins/Doctrine/Common/ClassLoader.php';
require_once APPS_DIR.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'app.utils.class.php';


class appUtils extends generalAppUtils {
   static function getDB(){
		$classLoader = new ClassLoader('Doctrine', APPS_DIR.'plugins/');
		$classLoader->register();
		$config = new \Doctrine\DBAL\Configuration();
		$connectionParams = array(
			'dbname' => DB_NAME,
			'user' => DB_USER,
			'password' => DB_PWD,
			'host' => DB_HOST,
			'port' => DB_PORT,
			'driver' => DB_DRIVER,
		);
		$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
		return $conn;
	}
    static function getLastId($db,$tab,$sk=null,$tb=null){
		if(!$sk || !$tb) list($sk,$tb)=explode('.',$tab);
		//$db=self::getDB();
		$sql="select array_to_string(regexp_matches(column_default, 'nextval[(][''](.+)['']::regclass[)]'),'') as sequence from information_schema.columns where table_schema=? and table_name=? and column_default ilike 'nextval%'";
		$sequence=$db->fetchColumn($sql,Array($sk,$tb));
		return $db->fetchColumn("select currval('$sequence')");
	}
    
    static function isNumeric($v){
        try{
            $value=self::toNumber($v);
            return (int)(is_numeric($value));
        }
        catch(Exception $e){
            return 0;
        }
    }
    static function toNumber($v){
        return str_replace(",",".",$v);
    }
    
    static function getUserId(){
        return $_SESSION["USER_ID"];
    }
    
    static function getUserName(){
        return $_SESSION["USER_NAME"];
    }
/*-------------------------------------------------------------------------------*/    
    static function normalizeNumero($numero){
        return preg_replace("|([^A-z0-9\-]+)|",'',str_replace('/','-',str_replace('\\','-',$numero)));
    }
    static function getAnno($numero){
        $numero=self::normalizeNumero($numero);
    }

    
    
/*-------------------------------------------------------------------------------------------*/
    static function getInfoPratica($pratica){
        $db=self::getDb();
        $sql="SELECT numero,tipo,resp_proc,resp_it,resp_ia,date_part('year',data_presentazione) as anno,data_presentazione,data_prot FROM pe.avvioproc  WHERE pratica=?";
		$r=$db->fetchAssoc($sql, Array($pratica));
        //ESTRAGGO INFORMAZIONI SUL DIRIGENTE
		$sql="SELECT userid as dirigente FROM admin.users WHERE attivato=1 and '13' = ANY(string_to_array(coalesce(gruppi,''),','));";
		$dirig=$db->fetchColumn($sql);
		$r['dirigente']=$dirig;
		//ESTRAGGO INFORMAZIONI SUL RESPONSABILE DEL SERVIZIO
		$sql="SELECT userid as rds FROM admin.users WHERE attivato=1 and '15' = ANY(string_to_array(coalesce(gruppi,''),','));";
		$rds=$db->fetchColumn($sql);
		$r['rds']=$rds;
		return $r;
    }
    
    static function getStato($id){
		$db=pratica::setDB();
		$sql="SELECT codice,data,descrizione FROM pe.elenco_transizioni_pratiche WHERE pratica=? order by data DESC,tmsins DESC LIMIT 1;";
		$ris=$db->fetchAssoc($sql,Array($id));
		return $ris;
	}
    
    static function getIdTrans($m){
        $db=self::getDb();
        $id=$db->fetchColumn("SELECT id FROM pe.e_transizioni WHERE codice=?",Array($m),0);
        return $id;
    }
/*--------------------------------------------------------------------------------------------*/  
    static function getPraticaRole($cfg,$pratica){
        $db=self::getDB();
		//Recupero il responsabile del procedimento
		$rdp=$db->fetchColumn("SELECT resp_proc FROM pe.avvioproc WHERE pratica=?",Array($pratica),0);
		
		//Verifico il dirigente
		$idDiri=$db->fetchColumn("SELECT userid FROM admin.users WHERE (SELECT DISTINCT id::varchar FROM admin.groups WHERE nome='dirigenza')=ANY(string_to_array(coalesce(gruppi,''),','))",Array(),0);
		/*$db->sql_query($sql);
		$idDiri=$db->sql_fetchfield('userid');*/
		
		//Verifico il responsabile del Servizio
		$idRds=$db->fetchColumn("SELECT userid FROM admin.users WHERE (SELECT DISTINCT id::varchar FROM admin.groups WHERE nome='rds')=ANY(string_to_array(coalesce(gruppi,''),','))",Array(),0);
		/*$db->sql_query($sql);
		$idRds=$db->sql_fetchfield('userid');*/
        
        //Verifico gli archivisti
		$sql="SELECT userid FROM admin.users WHERE (SELECT DISTINCT id::varchar FROM admin.groups WHERE nome='archivio')=ANY(string_to_array(coalesce(gruppi,''),','));";
		$r=$db->fetchAll($sql);
        for($i=0;$i<count($r);$i++){
            $idArch[]=$r[$i];
            $roles[$r[$i]]="archivio";
            $ris[]=$r[$i];
        }
		//Array con tutti i ruoli
        $supRoles=Array($rdp,$idRds,$idDiri);
		$ris=Array($rdp,$idRds,$idDiri);
		
		$sql="SELECT role,utente FROM pe.wf_roles WHERE pratica=?";
        $res=$db->fetchAll($sql,Array($pratica));
        $roles[$idDiri]=Array('dir');
		$roles[$idRds]=Array('rds');
        
        for($i=0;$i<count($res);$i++){
				$r=$res[$i];
				$roles[$r['utente']][]=$r['role'];
				$ris[]=$r['utente'];
			}
		if(count($res)){
			if (in_array($_SESSION["USER_ID"],$ris) or $_SESSION["PERMESSI"]<2)
				$owner=1;
			else
				$owner=2;
		}
		else
			$owner=3;
        
        return Array("roles"=>$roles,"owner"=>$owner,"ris"=>$ris,"editor"=>$supRoles);
    }
/*---------------------------------------------------------------------------------------------*/    
    static function addRole($pratica,$role,$usr,$d){
		$t=time();
		$db=self::getDB();
		$data=($d)?(($d=='CURRENT_DATE')?('now'):($d)):('now');
		$arrDati=Array(
			'pratica'=>$pratica,
			'role'=>$role,
			'utente'=>$usr,
			'data'=>$data,
			'tmsins'=>$t,
			'uidins'=>self::getUserId()
		);
		$db->insert('pe.wf_roles', $arrDati);
	}
	static function delRole($pratica,$role){
		$db=self::getDB();
		$db->delete('pe.wf_roles',Array('pratica'=>$pratica,'role'=>$role));
	}
    
    
    static function addTransition($pratica,$prms){
        $db=self::getDb();
        $userid=appUtils::getUserId();
		$initVal=Array("pratica"=>$pratica,'codice'=>null,'utente_in'=>$userid,'utente_fi'=>null,'data'=>"now",'stato_in'=>null,'stato_fi'=>null,'note'=>null,'tmsins'=>time(),'uidins'=>$userid);
		foreach($initVal as $key=>$val) $params[$key]=(in_array($key,array_keys($prms)) && $prms[$key])?($prms[$key]):($val);
		$params['note']=($params['note'])?($db->quote($params['note'])):($params['note']);
		$cod=$params['codice'];
		
		if($db->insert("pe.wf_transizioni",$params)){
			switch($cod){
				case "ardp":
				case "aitec":
				case "aiamm":
				case "aipre":
				case "aiagi":
				case "ailav":
					self::addRole($pratica,substr($cod,1),$params['utente_fi'],$params['data']);
					break;
				case "rardp":
				case "raitec":
				case "raiamm":
					self::delRole($pratica,substr($cod,2));
					self::addRole($pratica,substr($cod,2),$params['utente_fi'],$params['data']);
					break;
				default:
					break;
			}
		}
		
	}
	static function delTransition($pratica,$id=null){
		$db=self::getDb();
        $isCodice=(is_numeric($id))?(0):(1);
		$filter=($isCodice)?(Array('pratica'=>$pratica,'id'=>$id)):(Array('pratica'=>$pratica,'codice'=>$id));
		$db->delete('pe.wf_transizioni',$filter);
	}
    
    static function addIter($pratica,$prms){
        $db=self::getDb();
        $usr=self::getUserName();
        $initVal=Array("pratica"=>$pratica,'data'=>'now()','utente'=>$usr,'nota'=>null,'nota_edit'=>null,'stampe'=>null,'immagine'=>'laserjet.gif','tmsins'=>time(),'uidins'=>$userid);
        foreach($initVal as $key=>$val) $params[$key]=(in_array($key,array_keys($prms)) && $prms[$key])?($prms[$key]):($val);
		//$params['nota']=($params['nota'])?($db->quote($params['nota'])):($params['nota']);
        //$params['nota_edit']=($params['nota_edit'])?($db->quote($params['nota_edit'])):($params['nota_edit']);
		$db->insert("pe.iter",$params);
    }
    
    
    static function setPrmProgCalcolati($pratica,$data){
        $db=self::getDB();
        $table="pe.parametri_prog";
        $sql="select distinct id,codice from pe.e_parametri order by 2;";
        $res=$db->fetchAll($sql);
        for($i=0;$i<count($res);$i++) $e_prm[$res[$i]["codice"]]=$res[$i]["id"];
        $sql="select distinct B.id,A.codice from pe.e_parametri A inner join pe.parametri_prog B on(A.id=B.parametro) order by 2;";
        $res=$db->fetchAll($sql);
        for($i=0;$i<count($res);$i++) $prms[$res[$i]["codice"]]=$res[$i]["id"];
        $params=array_keys($data);
        
        //Volume Totale
        if (self::isNumeric($data[$prms["ve"]]) && self::isNumeric($data[$prms["vp"]]) && self::isNumeric($data[$prms["vd"]])){
            $v=(double)self::toNumber($data[$prms["ve"]])+(double)self::toNumber($data[$prms["vp"]])-(double)self::toNumber($data[$prms["vd"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["v"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["v"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Indice di Fabbricabilità
        if (self::isNumeric($data[$prms["v"]]) && self::isNumeric($data[$prms["slot"]])){
            $v=(double)self::toNumber($data[$prms["v"]])/(double)self::toNumber($data[$prms["slot"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["iif"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["iif"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        
        //Indice di copertura
        if (self::isNumeric($data[$prms["sc"]]) && self::isNumeric($data[$prms["slot"]])){
            $v=((double)self::toNumber($data[$prms["sc"]])/(double)self::toNumber($data[$prms["slot"]]))*100;
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["ic"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["ic"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Superficie Coperta Totale
        if (self::isNumeric($data[$prms["sce"]]) && self::isNumeric($data[$prms["scp"]]) && self::isNumeric($data[$prms["scd"]])){
            $v=(double)self::toNumber($data[$prms["sce"]])+(double)self::toNumber($data[$prms["scp"]])-(double)self::toNumber($data[$prms["scd"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["sc"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["sc"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Superficie Utile Totale
        if (self::isNumeric($data[$prms["sue"]]) && self::isNumeric($data[$prms["sup"]]) && self::isNumeric($data[$prms["sud"]])){
            $v=(double)self::toNumber($data[$prms["sue"]])+(double)self::toNumber($data[$prms["sup"]])-(double)self::toNumber($data[$prms["sud"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["su"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["su"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        
        //Indice di utilizzo fondiario
        if (self::isNumeric($data[$prms["su"]]) && self::isNumeric($data[$prms["sf"]])){
            $v=(double)self::toNumber($data[$prms["su"]])/(double)self::toNumber($data[$prms["sf"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["uf"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["uf"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Indice di copertura esistente
        if (self::isNumeric($data[$prms["sce"]]) && self::isNumeric($data[$prms["slot"]])){
            $v=(double)self::toNumber($data[$prms["sce"]])/(double)self::toNumber($data[$prms["slot"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["ice"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["ice"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Volume con indice 3/1
        if (self::isNumeric($data[$prms["slot"]])){
            $v=(double)3*(double)self::toNumber($data[$prms["slot"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["v3_1"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["v3_1"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Volume Esistente - Volume da demolire
        if (self::isNumeric($data[$prms["ve"]]) && self::isNumeric($data[$prms["vd"]])){
            $v=(double)self::toNumber($data[$prms["ve"]])-(double)self::toNumber($data[$prms["vd"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["ve_vd"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["ve_vd"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Volume Progetto - Volume da demolire
        if (self::isNumeric($data[$prms["vp"]]) && self::isNumeric($data[$prms["vd"]])){
            $v=(double)self::toNumber($data[$prms["vp"]])-(double)self::toNumber($data[$prms["vd"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["vp_vd"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["vp_vd"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
    }
    /*funzione di Raggruppamento dei dati*/
    static function groupData($mode,$res){
        $result=Array();
        switch($mode){
            case "print-field":
                for($i=0;$i<count($res);$i++){
                    $result[]=$res[$i];
                }
                break;
            case "pratiche-civici":
                for($i=0;$i<count($res);$i++){
                    $rec=$res[$i];
                    $via=$rec["via"];
                    $cartella=($rec["cartella"])?($rec["cartella"]):($rec["pratica"]);
                    $civico=($rec["civico"])?($rec["civico"]):('n.c.');
                    $interno=($rec["interno"])?($rec["interno"]):('n.i.');
                    $r[$via][$civico][$interno][$cartella][]=Array("pratica"=>$rec["pratica"],"interno"=>$interno,"via"=>$via,"civico"=>$civico,"info"=>Array("id"=>$rec["pratica"],"civico"=>$rec["civico"],"interno"=>$rec["interno"],"numero"=>$rec["numero"],"cartella"=>$rec["cartella"],"text"=>sprintf("%s n° %s del %s - Richiedenti : %s",$rec["tipo"],$rec["numero"],$rec["data"],$rec["richiedente"])));

                }
                
                $vie=Array();
                foreach($r as $ind=>$values){
                    $civici=Array();
                    
                    foreach($values as $civ=>$vals){
                        
                        $interni=Array();
                        foreach ($vals as $i=>$v){
                            $folders=Array();
                            foreach($v as $fld=>$prs){
                                $pratiche=Array();
                                foreach($prs as $p){
                                    $pratiche[]=$p["info"];
                                }
                                $state=(count($pratiche)>1)?("closed"):("open");
                                $folders[]=Array("id"=>$fld,"text"=>$p["info"]["text"],"state"=>"closed","children"=>$pratiche);
                            }
                                
                            $state=(count($folders)>1)?("closed"):("open");
                            $interni[]=Array("id"=>$i,"text"=>$i,"state"=>"closed","children"=>$folders);
                        }
			$state=(count($interni)>1)?("closed"):("open");
                        $civici[]=Array("id"=>$civ,"text"=>$civ,"state"=>"closed","children"=>$interni);
                        
                    } 
                    $state=(count($civici)>1)?("closed"):("open");
                    $vie[]=Array("id"=>$ind,"text"=>$via,"state"=>"open","children"=>$civici);   
                        
                }
                
                $result=  array_values($vie);
                break;
            case "modelli":
                for($i=0;$i<count($res);$i++){
                    $rec=$res[$i];
                    $idtipo=$rec["idtipo"];
                    
                    $r["$idtipo"][$rec["id"]]=Array("tipo"=>$rec["tipo_pratica"],"modello"=>$rec["modello"],"info"=>Array("id"=>$rec["id"],"text"=>$rec["modello"],"descrizione"=>$rec["modello"]));

                }
                foreach($r as $idTipo=>$values){
                    $modelli=Array();
                    foreach($values as $idMod=>$data){
                        $modelli[]=$data["info"];
                        $tipoPratica=$data["tipo"];
                    }   
                    $tipo=Array("id"=>$idTipo,"text"=>$tipoPratica,"state"=>"closed","children"=>$modelli);
                    $tipi[$idTipo]=$tipo;    
                        
                }
                
                $result=  array_values($tipi);
                
                break;
            case "civico":
                for($i=0;$i<count($res);$i++){
                    $rec=$res[$i];
                    $codvia=preg_replace("/[^A-Za-z0-9]/", '', strtolower($rec["via"]));
                    $via=$rec["via"];
                    $civico=preg_replace("([\\/]+)","-",$rec["civico"]);
                    $civico=preg_replace("([\.]+)","",$civico);
                    $interno=preg_replace("([\\/]+)","-",$rec["interno"]);
                    $interno=preg_replace("([\.]+)","",$interno);
                    $descrizione=sprintf("Pratica n° %s del %s",$rec["numero"],$rec["data_presentazione"]);
                    $ct=$rec["elenco_ct"];
                    $cu=$rec["elenco_cu"];
                    $linkToPratica=$rec["pratica"];
                    $r[$codvia][$civico][$interno][$rec["pratica"]]=Array("via"=>$via,"civico"=>$civico,"interno"=>$interno,"info"=>Array("id"=>$rec["pratica"],"name"=>$descrizione,"descrizione"=>$descrizione,"ct"=>$ct,"cu"=>$cu,"civico"=>"","via"=>"","pratica"=>$linkToPratica,"oggetto"=>$rec["oggetto"]));

                }
                foreach($r as $codvia=>$values){
                    $civici=Array();
                    foreach($values as $civ=>$vals){
                        $interni=Array();
                        foreach($vals as $int=>$vs){
                            $pratiche=Array();
                            foreach($vs as $pr=>$data){
                                $pratiche[]=$data["info"];
                                $via=$data["via"];
                            }
                            $labelInt=($int)?("Int. $int"):("Nessun interno");
                            $interno=Array("id"=>"$codvia-$civ-$int","name"=>$labelInt,"interno"=>$int,"civico"=>"$civ","via"=>"$via","descrizione"=>"","ct"=>"","cu"=>"","children"=>$pratiche,"oggetto"=>"","pratica"=>"","state"=>"closed");
                            $interni[]=$interno;
                        }
                        $civico=Array("id"=>"$codvia-$civ","name"=>$civ,"interno"=>"","civico"=>"$civ","via"=>"$via","descrizione"=>"","ct"=>"","cu"=>"","children"=>$interni,"oggetto"=>"","pratica"=>"","state"=>"closed");
                        $civici[]=$civico;
                    }
                    $via=Array("id"=>"$codvia","interno"=>"","civico"=>"","name"=>$via,"via"=>"$via","oggetto"=>"","descrizione"=>"","ct"=>"","cu"=>"","pratica"=>"","state"=>"closed","children"=>$civici);
                    $result[]=$via;
                }

                break;
            case "particella-terreni":
            case "particella-urbano":
                for($i=0;$i<count($res);$i++){
                    $rec=$res[$i];
                    $sez=preg_replace("/[^A-Za-z0-9 ]/", '', strtolower($rec["sezione"]));
                    $fg=preg_replace("/[^A-Za-z0-9 ]/", '',$rec["foglio"]);
                    $mp=preg_replace("/[^A-Za-z0-9 ]/", '',$rec["mappale"]);
                    $sub=preg_replace("/[^A-Za-z0-9 ]/", '',$rec["sub"]);
                    $sub=($sub)?($sub):('ns');
                    $descrizione=sprintf("Pratica n° %s del %s",$rec["numero"],$rec["data_presentazione"]);
                    $ubicazione=$rec["ubicazione"];
                    $cu=$rec["elenco_cu"];
                    $r[$sez][$fg][$mp][$sub][$rec["pratica"]]=Array(
                        "sezione"=>$sez,
                        "foglio"=>$fg,
                        "mappale"=>$mp,
                        "sub"=>$sub,
                        "info"=>Array(
                            "id"=>$rec["pratica"],
                            "name"=>$descrizione,
                            "descrizione"=>$descrizione,
                            "ubicazione"=>$ubicazione,
                            "cu"=>$cu,
                            "pratica"=>$rec["pratica"],
                            "oggetto"=>$rec["oggetto"]
                        )
                    );

                }
                $sezioni=Array();
                foreach($r as $sez=>$values){
                    $fogli=Array();
                    foreach($values as $fgs=>$v){
                        $mappali=Array();
                        foreach($v as $maps=>$vals){
                            $subalterni=Array();
                            foreach($vals as $sb=>$vs){
                                $pratiche=Array();
                                foreach($vs as $pr=>$data){
                                    $pratiche[]=$data["info"];
                                    $mappale=$data["mappale"];
                                }
                                $labelSub=($sb!='ns')?("Sub $sb"):("Nessun Subalterno");
                                $subalterno=Array("id"=>sprintf("%s-%s-%s-%s",$sez,$fgs,$maps,$sb),"name"=>$labelSub,"descrizione"=>"","ubicazione"=>"","cu"=>"","children"=>$pratiche,"oggetto"=>"","pratica"=>"","state"=>"closed");
                                $subalterni[]=$subalterno;
                            }
                            $mappale=Array("id"=>sprintf("%s-%s-%s",$sez,$fgs,$maps),"name"=>"Mappale $maps","descrizione"=>"","ubicazione"=>"","cu"=>"","children"=>$subalterni,"oggetto"=>"","pratica"=>"","state"=>"closed");
                            $mappali[]=$mappale;
                        }
                        $foglio=Array("id"=>sprintf("%s-%s",$sez,$fgs),"name"=>"Foglio $fgs","descrizione"=>"","ubicazione"=>"","cu"=>"","children"=>$mappali,"oggetto"=>"","pratica"=>"","state"=>"closed");
                        $fogli[]=$foglio;
                    }
                    $labelSez=($sez)?("Sezione $sez"):("Nessuna Sezione");
                    $sezione=Array("id"=>sprintf("%s",$sez),"name"=>$labelSez,"descrizione"=>"","ubicazione"=>"","cu"=>"","children"=>$fogli,"oggetto"=>"","pratica"=>"","state"=>"closed");
                    $result[]=$sezione;
                }
                break;
        }
        return $result;

    }
    
    static function getScadenze($userId){
            $conn=utils::getDb();
            //DETTAGLI DELLE SCADENZE
            $lLimit=(defined('LOWER_LIMIT'))?(LOWER_LIMIT):(5);
            $uLimit=(defined('UPPER_LIMIT'))?(UPPER_LIMIT):(3);
            $sql="select * from pe.vista_scadenze_utenti where $userId = ANY(interessati) and scadenza <= CURRENT_DATE +$lLimit  and scadenza >= CURRENT_DATE -$uLimit and completata=0 order by scadenza";
            
            $stmt=$conn->prepare($sql);
            if(!$stmt->execute()){
                return Array("errore"=>1,"query"=>$sql);
            }
            else{
                $res=$stmt->fetchAll(PDO::FETCH_ASSOC);
                return Array("totali"=>count($res),"data"=>$res);
            }
    }
    static function getVerifiche($userId){
            $conn=utils::getDb();

            $sql="select * from pe.vista_verifiche_utenti where $userId = ANY(interessati);";
            
            $stmt=$conn->prepare($sql);
            if(!$stmt->execute()){
                return Array("errore"=>1,"query"=>$sql);
            }
            else{
                $res=$stmt->fetchAll(PDO::FETCH_ASSOC);
                return Array("totali"=>count($res),"data"=>$res);
            }
    }
    static function chooseRespVerifiche($tipo){
        $res = 'NULL';
        if ($tipo == 4){
            $res = 22;
        }
        return $res;
        
    }
    static function getNotifiche($userId){
            $conn=utils::getDb();
            //DETTAGLI DELLE SCADENZE
            $lLimit=(defined('LOWER_LIMIT'))?(LOWER_LIMIT):(5);
            $uLimit=(defined('UPPER_LIMIT'))?(UPPER_LIMIT):(3);
            $sql="select A.id,A.pratica,B.numero,B.data_prot,testo as oggetto,ARRAY[soggetto_notificato] as interessati from pe.notifiche A inner join pe.avvioproc B using(pratica) where soggetto_notificato=$userId and visionato=0;";
            
            $stmt=$conn->prepare($sql);
            if(!$stmt->execute()){
                return Array("errore"=>1,"query"=>$sql);
            }
            else{
                $res=$stmt->fetchAll(PDO::FETCH_ASSOC);
                return Array("totali"=>count($res),"data"=>$res);
            }
    }
}   
?>
