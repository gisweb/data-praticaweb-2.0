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
    static function titoloPratica($req){

        if (!$_REQUEST["pratica"]) return "";
        $pr=$_REQUEST["pratica"];
        if ($_REQUEST["cdu"]){
            $sql="SELECT 'Certificato di Destinazione Urbanitica Prot n° '||protocollo as titolo FROM cdu.richiesta WHERE pratica=?";
        }
        else{
            $sql="SELECT B.nome|| coalesce(' - '||C.nome,'') ||' n° '||A.numero as titolo FROM pe.avvioproc A INNER JOIN pe.e_tipopratica B ON(A.tipo=B.id) LEFT JOIN pe.e_categoriapratica C ON (coalesce(A.categoria,0)=C.id)  WHERE pratica=?;";
        }
        //echo $pr;
        $db=self::getDb();
        $result=$db->fetchAll($sql,Array($pr));
        return $result[0]["titolo"];
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
            $res = 50;
        }
        return $res;
        
    }
    static function getNotifiche($userId){
            $conn=utils::getDb();
            //DETTAGLI DELLE SCADENZE
            $lLimit=(defined('LOWER_LIMIT'))?(LOWER_LIMIT):(5);
            $uLimit=(defined('UPPER_LIMIT'))?(UPPER_LIMIT):(3);
            $sql="select A.id,A.pratica,B.numero,B.data_prot,testo as oggetto,ARRAY[soggetto_notificato] as interessati,data_notifica from pe.notifiche A inner join pe.avvioproc B using(pratica) where soggetto_notificato=$userId and visionato=0 order by data_notifica DESC,id DESC;";
            
            $stmt=$conn->prepare($sql);
            if(!$stmt->execute()){
                return Array("errore"=>1,"query"=>$sql);
            }
            else{
                $res=$stmt->fetchAll(PDO::FETCH_ASSOC);
                return Array("totali"=>count($res),"data"=>$res);
            }
    }
    
    static function getUserProtocollo($id){
        $dsn = sprintf('pgsql:dbname=%s;host=%s;port=%s',DB_NAME,DB_HOST,DB_PORT);
        $conn1 = new PDO($dsn, DB_USER, DB_PWD);

        $sql= "SELECT * FROM admin.users where userid in (?)";
        $stmt1=$conn1->prepare($sql);
        $stmt1->execute(Array($id));
        $result = $stmt1->fetch(PDO::FETCH_ASSOC);
        $dsn = sprintf('pgsql:dbname=%s;host=%s;port=%s',DB_NAME_PROT,DB_HOST_PROT,DB_PORT_PROT);
        
		try{
			$conn = new PDO($dsn, DB_USER_PROT, DB_PWD_PROT);
		}
		catch (Exception $e){
	        return Array("success"=>-1,"message"=>"nessuna connessione attiva con il server del protocollo");
		}
        $sql="select id_entita, position,username from entita join positions using(id_entita) WHERE trim(cognome) || ' ' || trim(nome) ilike ?;";
        //$sql="select id_entita, position,username,cognome,nome from entita join positions using(id_entita) WHERE nome ilike ?;";
        $stmt=$conn->prepare($sql);
        $username= sprintf("%s %s",trim($result["cognome"]),trim($result["nominativo"]));
        //$username= sprintf("%s",$result[$i]["nominativo"]);
        if ($stmt->execute(Array($username))){
            $res=$stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($res)==0 ) return Array();
            else{
                return $res[0];
            }

        }
        else{
            return Array();
        }
    }
    static function testProtocollo($username,$nominativo,$oggetto,$note="",$data_prot=""){
        $dsn = sprintf('pgsql:dbname=%s;host=%s;port=%s',DB_NAME_PROT,DB_HOST_PROT,DB_PORT_PROT);
        try{
			$conn = new PDO($dsn, DB_USER_PROT, DB_PWD_PROT);
		}
		catch (Exception $e){
	        return Array("success"=>-1,"message"=>"nessuna connessione attiva con il server del protocollo");
		}
        
        $pg_inseritodagruppo = "c_E463"; 
        $aoo = "aoo000"; 
        $pg_protocollo_documento = NULL; 
        $pg_tipo_protocollo = "'E'";
        $pg_data_documento =  ($data_prot)?($data_prot):(date("Y-m-d")); 
        $pg_corrispondente = $nominativo; //Nominativo 
        $pg_indirizzo = ""; //Indirizzo + civico 
        $pg_indirizzo_cap =  ""; //CAP 
        $pg_indirizzo_citta =  ""; // Città 
        $pg_indirizzo_provincia =  ""; //Provincia

        $pg_oggetto = $oggetto;//Oggetto e tipo pratica
        $pg_annotazioni = $note; //Note

        $pg_inseritoda = $username;
        $pg_titolo = "6";
        $pg_classe = "3";
        $pg_sottoclasse = "0";
        
	if ($conn){
            $sql="select id_entita, position,denominazione from entita join positions using(id_entita) where username=?;";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($username))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }

            if ($numrows>0){
                    $id_utente = $res["position"];
                    $id_entita = $res["id_entita"];
                    $denominazione = $res["denominazione"];
                    $sql="select parent from entita where id_entita =?";
                    $stmt = $conn->prepare($sql);
                    if ($stmt->execute(Array($res["position"]))){
                        $res = $stmt->fetch(PDO::FETCH_ASSOC);
                        $pg_inseritodagruppo = $res["parent"];
                    }
                    
            } else {
                return Array("success"=>-1,"message"=>"utente non presente con username $username");
            }
/*
            $sql="select denominazione from entita where id_entita=?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($id_identita))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }
            if ($numrows>0){
                    $denominazione = $res["denominazione"];
            } else {
                return Array("success"=>-1,"message"=>"gruppo non presente con identità $id_entita");
            }
*/
            $sql="select \"pg_Descrizione\" from pg_titolario_aoo000 where \"pg_Titolo\"=? and \"pg_Classe\"=? and \"pg_SottoClasse\"=?;";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($pg_titolo,$pg_classe,$pg_sottoclasse))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }
            if ($numrows>0){
                    $denominazione = $res["pg_Descrizione"];
            } else {
                return Array("success"=>-1,"message"=>"titolario non presente");
            }


            $sql="select \"pg_Descrizione\" from pg_titolario_aoo000 where \"pg_Titolo\"=? and \"pg_Classe\"=? and \"pg_SottoClasse\"=?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($pg_titolo,$pg_classe,$pg_sottoclasse))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }
            if ($numrows>0){
                    $denominazione = $res["pg_Descrizione"];
            } else {
                return Array("success"=>-1,"message"=>"classe titolario non presente");
            }

            $conn->beginTransaction();
            $conn->exec("LOCK TABLE pg_numeroprotocollo_aoo000 IN ACCESS EXCLUSIVE MODE;");
            $sql="select prossimo_numero_protocollo from pg_numeroprotocollo_aoo000 where aoo='aoo000' and anno=?";
            $stmt=$conn->prepare($sql);
            if ($stmt->execute(Array(date("Y",time())))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            if ($numrows>0){
                $prossimo_numero_protocollo = $res["prossimo_numero_protocollo"];
                $sql="update pg_numeroprotocollo_aoo000 set prossimo_numero_protocollo=prossimo_numero_protocollo+1 where aoo='aoo000' and anno=?";
                $stmt = $conn->prepare($sql);
                if (!$stmt->execute(Array(date("Y",time())))){
                    $conn->rollBack();
                    return Array("success"=>-1,"message"=>"aggiornamento numero protocollo non effettuato");
                }

            }
            else{
                $conn->rollBack();
                return Array("success"=>-1,"message"=>"numero protocollo non assegnato");
            }
            
            $conn->rollBack();
            return Array("success"=>1,"message"=>"inserimento riuscito","protocollo"=>$prossimo_numero_protocollo,"errors"=>$error);
	} 
        else{	
            return Array("success"=>-1,"message"=>"nessuna connessione attiva");
	}
    }
    
	static function testProtocolloOut($username,$nominativo,$oggetto,$note="",$data_prot=""){
        $dsn = sprintf('pgsql:dbname=%s;host=%s;port=%s',DB_NAME_PROT,DB_HOST_PROT,DB_PORT_PROT);
        try{
			$conn = new PDO($dsn, DB_USER_PROT, DB_PWD_PROT);
		}
		catch (Exception $e){
	        return Array("success"=>-1,"message"=>"nessuna connessione attiva con il server del protocollo");
		}
        
        $pg_inseritodagruppo = "c_E463"; 
        $aoo = "aoo000"; 
        $pg_protocollo_documento = NULL; 
        $pg_tipo_protocollo = "'E'";
        $pg_data_documento =  ($data_prot)?($data_prot):(date("Y-m-d")); 
        $pg_corrispondente = $nominativo; //Nominativo 
        $pg_indirizzo = ""; //Indirizzo + civico 
        $pg_indirizzo_cap =  ""; //CAP 
        $pg_indirizzo_citta =  ""; // Città 
        $pg_indirizzo_provincia =  ""; //Provincia

        $pg_oggetto = $oggetto;//Oggetto e tipo pratica
        $pg_annotazioni = $note; //Note

        $pg_inseritoda = $username;
        $pg_titolo = "6";
        $pg_classe = "3";
        $pg_sottoclasse = "0";
        
	if ($conn){
            $sql="select id_entita, position,denominazione from entita join positions using(id_entita) where username=?;";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($username))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }

            if ($numrows>0){
                    $id_utente = $res["position"];
                    $id_entita = $res["id_entita"];
                    $denominazione = $res["denominazione"];
                    $sql="select parent from entita where id_entita =?";
                    $stmt = $conn->prepare($sql);
                    if ($stmt->execute(Array($res["position"]))){
                        $res = $stmt->fetch(PDO::FETCH_ASSOC);
                        $pg_inseritodagruppo = $res["parent"];
                    }
                    
            } else {
                return Array("success"=>-1,"message"=>"utente non presente con username $username");
            }
/*
            $sql="select denominazione from entita where id_entita=?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($id_identita))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }
            if ($numrows>0){
                    $denominazione = $res["denominazione"];
            } else {
                return Array("success"=>-1,"message"=>"gruppo non presente con identità $id_entita");
            }
*/
            $sql="select \"pg_Descrizione\" from pg_titolario_aoo000 where \"pg_Titolo\"=? and \"pg_Classe\"=? and \"pg_SottoClasse\"=?;";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($pg_titolo,$pg_classe,$pg_sottoclasse))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }
            if ($numrows>0){
                    $denominazione = $res["pg_Descrizione"];
            } else {
                return Array("success"=>-1,"message"=>"titolario non presente");
            }


            $sql="select \"pg_Descrizione\" from pg_titolario_aoo000 where \"pg_Titolo\"=? and \"pg_Classe\"=? and \"pg_SottoClasse\"=?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($pg_titolo,$pg_classe,$pg_sottoclasse))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }
            if ($numrows>0){
                    $denominazione = $res["pg_Descrizione"];
            } else {
                return Array("success"=>-1,"message"=>"classe titolario non presente");
            }

            $conn->beginTransaction();
            $conn->exec("LOCK TABLE pg_numeroprotocollo_aoo000 IN ACCESS EXCLUSIVE MODE;");
            $sql="select prossimo_numero_protocollo from pg_numeroprotocollo_aoo000 where aoo='aoo000' and anno=?";
            $stmt=$conn->prepare($sql);
            if ($stmt->execute(Array(date("Y",time())))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            if ($numrows>0){
                $prossimo_numero_protocollo = $res["prossimo_numero_protocollo"];
                $sql="update pg_numeroprotocollo_aoo000 set prossimo_numero_protocollo=prossimo_numero_protocollo+1 where aoo='aoo000' and anno=?";
                $stmt = $conn->prepare($sql);
                if (!$stmt->execute(Array(date("Y",time())))){
                    $conn->rollBack();
                    return Array("success"=>-1,"message"=>"aggiornamento numero protocollo non effettuato");
                }

            }
            else{
                $conn->rollBack();
                return Array("success"=>-1,"message"=>"numero protocollo non assegnato");
            }
            
            $conn->rollBack();
            return Array("success"=>1,"message"=>"inserimento riuscito","protocollo"=>$prossimo_numero_protocollo,"errors"=>$error);
	} 
        else{	
            return Array("success"=>-1,"message"=>"nessuna connessione attiva");
	}
    }
    
	
	
	static function richiediProtocollo($username,$nominativo,$oggetto,$note="",$data_prot=""){
        $dsn = sprintf('pgsql:dbname=%s;host=%s;port=%s',DB_NAME_PROT,DB_HOST_PROT,DB_PORT_PROT);
        try{
			$conn = new PDO($dsn, DB_USER_PROT, DB_PWD_PROT);
		}
		catch (Exception $e){
	        return Array("success"=>-1,"message"=>"nessuna connessione attiva");
		}
        $pg_inseritodagruppo = "c_E463"; 
        $aoo = "aoo000"; 
        $pg_protocollo_documento = NULL; 
        $pg_tipo_protocollo = "E";
        $pg_data_documento =  ($data_prot)?($data_prot):(date("Y-m-d")); 
        $pg_corrispondente = addslashes(stripslashes($nominativo)); //Nominativo 
        $pg_indirizzo = ""; //Indirizzo + civico 
        $pg_indirizzo_cap =  ""; //CAP 
        $pg_indirizzo_citta =  ""; // Città 
        $pg_indirizzo_provincia =  ""; //Provincia

        $pg_oggetto = addslashes(stripslashes($oggetto));//Oggetto e tipo pratica
        $pg_annotazioni = addslashes(stripslashes($note)); //Note

        $pg_inseritoda = $username;
        $pg_titolo = "6";
        $pg_classe = "3";
        $pg_sottoclasse = "0";
        
	if ($conn){
            $sql="select id_entita, position,denominazione from entita join positions using(id_entita) where username=?;";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($username))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }

            if ($numrows>0){
                    $id_utente = $res["position"];
                    $id_entita = $res["id_entita"];
                    $denominazione = $res["denominazione"];
                    $sql="select parent from entita where id_entita =?";
                    $stmt = $conn->prepare($sql);
                    if ($stmt->execute(Array($res["position"]))){
                        $res = $stmt->fetch(PDO::FETCH_ASSOC);
                        $pg_inseritodagruppo = $res["parent"];
                    }
            } else {
                return Array("success"=>-1,"message"=>"utente non presente con username $username");
            }
/*
            $sql="select denominazione from entita where id_entita=?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($id_identita))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }
            if ($numrows>0){
                    $denominazione = $res["denominazione"];
            } else {
                return Array("success"=>-1,"message"=>"gruppo non presente");
            }
*/
            $sql="select \"pg_Descrizione\" from pg_titolario_aoo000 where \"pg_Titolo\"=? and \"pg_Classe\"=? and \"pg_SottoClasse\"=?;";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($pg_titolo,$pg_classe,$pg_sottoclasse))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }
            if ($numrows>0){
                    $denominazione = $res["pg_Descrizione"];
            } else {
                return Array("success"=>-1,"message"=>"titolario non presente");
            }


            $sql="select \"pg_Descrizione\" from pg_titolario_aoo000 where \"pg_Titolo\"=? and \"pg_Classe\"=? and \"pg_SottoClasse\"=?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($pg_titolo,$pg_classe,$pg_sottoclasse))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }
            if ($numrows>0){
                    $denominazione = $res["pg_Descrizione"];
            } else {
                return Array("success"=>-1,"message"=>"classe titolario non presente");
            }

            $conn->beginTransaction();
            $conn->exec("LOCK TABLE pg_numeroprotocollo_aoo000 IN ACCESS EXCLUSIVE MODE;");
            $sql="select prossimo_numero_protocollo from pg_numeroprotocollo_aoo000 where aoo='aoo000' and anno=?";
            $stmt=$conn->prepare($sql);
            if ($stmt->execute(Array(date("Y",time())))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            if ($numrows>0){
                $prossimo_numero_protocollo = $res["prossimo_numero_protocollo"];
                $sql="update pg_numeroprotocollo_aoo000 set prossimo_numero_protocollo=prossimo_numero_protocollo+1 where aoo='aoo000' and anno=?";
                $stmt = $conn->prepare($sql);
                if (!$stmt->execute(Array(date("Y",time())))){
                    $conn->rollBack();
                    return Array("success"=>-1,"message"=>"aggiornamento numero protocollo non effettuato");
                }

            }
            else{
                $conn->rollBack();
                return Array("success"=>-1,"message"=>"numero protocollo non assegnato");
            }

            


            $numero = sprintf("%07d",$prossimo_numero_protocollo);
            $pg_id = substr(date("Y",time()),-2) . "1" . $numero . "00";
            $pg_numero_protocollo =  $prossimo_numero_protocollo;
            $pg_anno_protocollo = date("Y",time());
            $pg_data_protocollo = date("Y-m-d");
            $pg_ora_protocollo = date("H:i:s", time());
            $pg_data_arrivo = NULL;
            $pg_numero_documento = NULL;
            $pg_f_riservato = 0;
            $pg_f_annullato = "N";
            $pg_f_modificato = "N";
            $pg_f_fascicolato = "N";
            $pg_f_notificato = "N";
            $pg_noteannullamento = "";
            $pg_id_spedizione = 0;
            $pg_importo_vlr_euro = 0;
            $pg_importo_spz_euro = 0;
            $pg_n_allegati = 1;
            $pg_numeroraccomandata = NULL;
            $pg_postaparticolare = "";
            $pg_reg_emergenza = NULL;
            $pg_nr_reg_emergenza = NULL;
            $pg_data_reg_emergenza = NULL;
            $pg_data_timbro = NULL;
            $id_registrazione = $pg_id;
            $pg_id_oggetto = NULL;
            $numero_protocollo = $pg_numero_protocollo;
            $data_immissione = date("Y-m-d") . " " . date("H:i:s", time()) ;
            $data_registrazione =  $pg_data_protocollo;
            $tempo_registrazione = $pg_ora_protocollo;
            $modificato = "FALSE";
            $annullato = "FALSE";
            $note =  $pg_annotazioni ;
            $utente_emergenza = "";
            $ambito = "esterno";
            $private = 0;
            $type = NULL;
            $emergency_protocol = NULL;
            $document_medium = 0;
            $pg_corrispondente_codice = "";
            $pg_mail = NULL;
            $pg_inseritoda_settore = $pg_inseritodagruppo;
            $pg_inseritoda_servizio = 0;
            $pg_inseritoda_ufficio = 0;
            $pg_spedizione = NULL;
            $pg_altri_corrispondenti = NULL;
            $pg_accessing_docs = NULL;
            $pg_num_attachfiles = 0;
            $pg_albo_pubblished = 0;
            $sql = "insert into pg_protocollo_aoo000 (
                pg_id, pg_anno_protocollo, pg_numero_protocollo, pg_tipo_protocollo, pg_data_protocollo, pg_ora_protocollo, pg_data_arrivo, pg_corrispondente, 
                pg_indirizzo, pg_oggetto, pg_data_documento, pg_protocollo_documento, pg_numero_documento, pg_annotazioni, pg_f_riservato, pg_f_annullato, 
                pg_f_modificato, pg_f_fascicolato, pg_f_notificato, pg_noteannullamento, pg_titolo, pg_classe, pg_sottoclasse, pg_inseritoda, 
                pg_id_spedizione, pg_importo_vlr_euro, pg_importo_spz_euro, pg_n_allegati, pg_inseritodagruppo, pg_numeroraccomandata, pg_postaparticolare, pg_reg_emergenza, 
                pg_nr_reg_emergenza, pg_data_reg_emergenza, pg_data_timbro, id_registrazione, pg_id_oggetto, numero_protocollo, data_immissione, data_registrazione, 
                tempo_registrazione, id_utente, modificato, annullato, note, utente_emergenza, ambito, aoo, 
                private, type, emergency_protocol, document_medium, pg_corrispondente_codice, pg_mail, pg_inseritoda_settore, pg_inseritoda_servizio, 
                pg_inseritoda_ufficio, pg_spedizione, pg_indirizzo_cap, pg_indirizzo_citta, pg_indirizzo_provincia, pg_altri_corrispondenti, pg_accessing_docs, pg_num_attachfiles, 
                pg_albo_pubblished)
                VALUES(
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?)";

            $stmt = $conn->prepare($sql);
            $data=Array(
                $pg_id, $pg_anno_protocollo, $pg_numero_protocollo, $pg_tipo_protocollo, $pg_data_protocollo, $pg_ora_protocollo, $pg_data_arrivo, $pg_corrispondente,
                $pg_indirizzo, $pg_oggetto, $pg_data_documento, $pg_protocollo_documento, $pg_numero_documento, $pg_annotazioni, $pg_f_riservato, $pg_f_annullato,
                $pg_f_modificato, $pg_f_fascicolato, $pg_f_notificato, $pg_noteannullamento, $pg_titolo, $pg_classe, $pg_sottoclasse, $pg_inseritoda, 
                $pg_id_spedizione,$pg_importo_vlr_euro, $pg_importo_spz_euro, $pg_n_allegati, $pg_inseritodagruppo, $pg_numeroraccomandata, $pg_postaparticolare, $pg_reg_emergenza,
                $pg_nr_reg_emergenza, $pg_data_reg_emergenza, $pg_data_timbro, $id_registrazione, $pg_id_oggetto, $numero_protocollo, $data_immissione, $data_registrazione,  
                $tempo_registrazione, $id_utente, $modificato, $annullato, $note, $utente_emergenza, $ambito, $aoo, 
                $private, $type, $emergency_protocol, $document_medium,$pg_corrispondente_codice, $pg_mail, $pg_inseritoda_settore, $pg_inseritoda_servizio, 
                $pg_inseritoda_ufficio, $pg_spedizione,$pg_indirizzo_cap, $pg_indirizzo_citta, $pg_indirizzo_provincia, $pg_altri_corrispondenti, $pg_accessing_docs, $pg_num_attachfiles, 
                $pg_albo_pubblished);

            $error = Array();
            if (!$stmt->execute($data)){
                $error[]=Array("Errore inserimento dati nella tabella pg_protocollo_aoo000",$conn->errorInfo());
                $conn->rollBack();
                return Array("success"=>-1,"errors"=>$error);
            }
            else{
                $conn->commit();
            }

            $strxml = "<?xml version=''1.0'' encoding=''UTF-8''?>\n";
            $strxml = $strxml . "<!DOCTYPE Segnatura SYSTEM \"Segnatura.dtd\">\n";
            $strxml = $strxml . "<Segnatura versione=''2001-05-07'' xml:lang=''it''>\n";
            $strxml = $strxml . "  <Intestazione>\n";
            $strxml = $strxml . "    <Identificatore>\n";
            $strxml = $strxml . "      <CodiceAmministrazione>c_e463</CodiceAmministrazione>\n";
            $strxml = $strxml . "      <CodiceAOO>$aoo</CodiceAOO>\n";
            $strxml = $strxml . "      <NumeroRegistrazione>$numero</NumeroRegistrazione>\n";
            $strxml = $strxml . "      <DataRegistrazione>$pg_data_protocollo</DataRegistrazione>\n";
            $strxml = $strxml . "    </Identificatore>\n";
            $strxml = $strxml . "    <Origine>\n";
            $strxml = $strxml . "      <IndirizzoTelematico>\n";
            $strxml = $strxml . "      </IndirizzoTelematico>\n";
            $strxml = $strxml . "      <Mittente>\n";
            $strxml = $strxml . "        <Amministrazione>\n";
            $strxml = $strxml . "          <Denominazione>Comune della Spezia</Denominazione>\n";
            $strxml = $strxml . "          <CodiceAmministrazione>c_e463</CodiceAmministrazione\n";
            $strxml = $strxml . "          <UnitaOrganizzativa tipo=''permanente''>\n";
            $strxml = $strxml . "            <Denominazione>$denominazione</Denominazione>\n";
            $strxml = $strxml . "            <IndirizzoPostale>\n";
            $strxml = $strxml . "              <Toponimo dug=''Piazza''>Europa</Toponimo>\n";
            $strxml = $strxml . "              <Civico>1</Civico>\n";
            $strxml = $strxml . "              <CAP>19125</CAP>\n";
            $strxml = $strxml . "              <Comune>La Spezia</Comune>\n";
            $strxml = $strxml . "              <Provincia>SP</Provincia>\n";
            $strxml = $strxml . "            </IndirizzoPostale>\n";
            $strxml = $strxml . "          </UnitaOrganizzativa>\n";
            $strxml = $strxml . "        </Amministrazione>\n";
            $strxml = $strxml . "        <AOO>\n";
            $strxml = $strxml . "          <Denominazione>Comune della Spezia</Denominazione>\n";
            $strxml = $strxml . "        </AOO>\n";
            $strxml = $strxml . "      </Mittente>\n";
            $strxml = $strxml . "    </Origine>\n";
            $strxml = $strxml . "    <Destinazione confermaRicezione=''si''>\n";
            $strxml = $strxml . "      <IndirizzoTelematico>\n";
            $strxml = $strxml . "      </IndirizzoTelematico>\n";
            $strxml = $strxml . "      <Destinatario>\n";
            $strxml = $strxml . "        <Denominazione>$pg_corrispondente</Denominazione>\n";
            $strxml = $strxml . "      </Destinatario>\n";
            $strxml = $strxml . "    </Destinazione>\n";
            $strxml = $strxml . "    <Riservato>N</Riservato>\n";
            $strxml = $strxml . "    <RiferimentoDocumentiCartacei/>\n";
            $strxml = $strxml . "    <Oggetto>$pg_oggetto</Oggetto>\n";
            $strxml = $strxml . "    <Classifica>\n";
            $strxml = $strxml . "      <CodiceAmministrazione>c_e463</CodiceAmministrazione>\n";
            $strxml = $strxml . "      <CodiceAOO>$aoo</CodiceAOO>\n";
            $strxml = $strxml . "      <Denominazione>$titolo / $classe</Denominazione>\n";
            $strxml = $strxml . "      <Livello nome=''titolo''>$pg_titolo</Livello>\n";
            $strxml = $strxml . "      <Livello nome=''classe''>$pg_classe</Livello>\n";
            $strxml = $strxml . "      <Livello nome=''sottoclasse''>$pg_sottoclasse</Livello>\n";
            $strxml = $strxml . "    </Classifica>\n";
            $strxml = $strxml . "    <Note>$pg_annotazioni</Note>\n";
            $strxml = $strxml . "  </Intestazione>\n";
            $strxml = $strxml . "  <Descrizione>\n";
            $strxml = $strxml . "    <Documento id=''main_doc'' tipoRiferimento=''cartaceo''>\n";
            $strxml = $strxml . "      <TitoloDocumento>Protocollo: $pg_anno_protocollo-$numero</TitoloDocumento>\n";
            $strxml = $strxml . "      <Classifica>\n";
            $strxml = $strxml . "        <CodiceAmministrazione>c_e463</CodiceAmministrazione>\n";
            $strxml = $strxml . "        <CodiceAOO>$aoo</CodiceAOO>\n";
            $strxml = $strxml . "        <Denominazione>$titolo / $classe</Denominazione>\n";
            $strxml = $strxml . "        <Livello nome=''titolo''>$pg_titolo</Livello>\n";
            $strxml = $strxml . "        <Livello nome=''classe''>$pg_classe</Livello>\n";
            $strxml = $strxml . "        <Livello nome=''sottoclasse''>$pg_sottoclasse</Livello>\n";
            $strxml = $strxml . "      </Classifica>\n";
            $strxml = $strxml . "    </Documento>\n";
            $strxml = $strxml . "  </Descrizione>\n";
            $strxml = $strxml . "</Segnatura>";

            $sql = "insert into pg_protocollo_segnatura_aoo000 (id_registrazione, segnatura) VALUES(?,?);";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute(Array($pg_id,$strxml))){
                $error[]=Array("Errore inserimento dati nella tabella pg_protocollo_segnatura_aoo000",$conn->errorInfo());
                $conn->rollBack();
                return Array("success"=>-1,"errors"=>$error);
                
            }
            //$conn->commit();
            if ($pg_tipo_protocollo=="U") {
                    $role_id=1;
            } else {
                    $role_id=2;
            }
            $sql = "insert into pg_referer_aoo000 (registration_id, referer_id, role_id, data) VALUES(?, ?, ?, ?);";
            //$qresult = @pg_Exec ($connection, $sql);
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute(Array($pg_id, $pg_inseritodagruppo, $role_id, $pg_data_protocollo))){
                $error[]=Array("Errore inserimento dati nella tabella pg_referer_aoo000",$conn->errorInfo());
                //$conn->rollBack();
                return Array("success"=>-1,"errors"=>$error);
                
            }
            $sql = "insert into pg_registration_history_aoo000 (registration_id, history_id) VALUES (?, nextval ('counter_ammgen_history_id'));";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute(Array($pg_id))){
                $error[]=Array("Errore inserimento dati nella tabella pg_registration_history_aoo000",$conn->errorInfo());
                //$conn->rollBack();
                return Array("success"=>-1,"errors"=>$error);
                
            }

            $sql = "select history_id from pg_registration_history_aoo000 where registration_id=?;";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($pg_id))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            if ($numrows>0){
                $history_id = $res["history_id"];
            }

            $sql = "insert into history_events (history_id, event_id) VALUES(?, nextval ('counter_ammgen_event_id'));";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute(Array($history_id))){
                $error[]=Array("Errore inserimento dati nella tabella history_events",$conn->errorInfo());
                //$conn->rollBack();
                return Array("success"=>-1,"errors"=>$error);
            }

            $sql = "select event_id from history_events where history_id=?;";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($history_id))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            if ($numrows>0){
                $event_id = $res[0]["event_id"];
                $next_event_id = $res[1]["event_id"];
            }
            else{
                $error[]=Array("Errore nella selezione degli event_id della tabella history_events");
                //$conn->rollBack();
                return Array("success"=>-1,"errors"=>$error);
            }
            $event_kind = "registering";
            $previous_id = 0;
            $charge_id = $id_utente;
            $event_time = $data_immissione;
            $user_id = $id_entita;
            $data = Array($event_id, $event_kind, $previous_id, $charge_id, $event_time, $user_id);
            $sql = "insert into events (event_id, event_kind, previous_id, charge_id, event_time, user_id) VALUES(?,?,?,?,?,?);";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute($data)){
                $error[]=Array("Errore inserimento dati nella tabella events",$conn->errorInfo());
                //$conn->rollBack();
                //return Array("success"=>-1,"errors"=>$error);
            }


            $previous_id = $event_id;
            $event_id = $next_event_id;
            $event_kind = "assignment";
            $charge_id = $id_utente;
            $event_time = $data_immissione;
            $user_id = $id_entita;

            
            
            $data = Array($event_id, $event_kind, $previous_id, $charge_id, $event_time, $user_id);
            $sql = "insert into events (event_id, event_kind, previous_id, charge_id, event_time, user_id) VALUES(?,?,?,?,?,?);";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute($data)){
                $error[]=Array("Errore inserimento dati nella tabella events 2 time",$conn->errorInfo());
                //$conn->rollBack();
                //return Array("success"=>-1,"errors"=>$error); 
            }
            $registration_id = $pg_id;
            $to_entity_id = $pg_inseritodagruppo;
            $from_db_event_id = 0;
            $event_status = "D";
            $id_dashboard_type = "AssignmentDashBoard";
            $note = "Primo smistamento della registrazione";
            $timestamp = $data_immissione;
            $from_entity_id = $id_utente;
            $insert_by_charge = $id_utente;
            $data = Array($registration_id, $to_entity_id, $from_db_event_id, $event_status, $id_dashboard_type, $note, $timestamp, $from_entity_id, $insert_by_charge);
            $sql = "insert into pg_dashboard_events_aoo000 (db_event_id, registration_id, to_entity_id, from_db_event_id, event_status, id_dashboard_type, note, timestamp, from_entity_id, insert_by_charge) VALUES
                    (nextval('counter_aoo000_dashboard_event_id'), ?, ?, ?, ?, ?, ?, ?, ?, ?);";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute($data)){
                $error[]=Array("Errore inserimento dati nella tabella pg_dashboard_events_aoo000",$conn->errorInfo());
                //$conn->rollBack();
                //return Array("success"=>-1,"errors"=>$error);
            }

            $timestamp = date("Y-m-d") . " " . date("H:i:s", time());
            $event_status = "A";
            $id_dashboard_type = "ManagedDashBoard";
            $note = "<i>Spostato manualmente in Documenti elaborati</i>";
            $data = Array($registration_id, $to_entity_id, $from_db_event_id, $event_status, $id_dashboard_type, $note, $timestamp, $from_entity_id, $insert_by_charge);
            $sql = "insert into pg_dashboard_events_aoo000 (db_event_id, registration_id, to_entity_id, from_db_event_id, event_status, id_dashboard_type, note, timestamp, from_entity_id, insert_by_charge) VALUES
                    (nextval('counter_aoo000_dashboard_event_id'), ?, ?, ?, ?, ?, ?, ?, ?, ?);";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute($data)){
                $error[]=Array("Errore inserimento dati nella tabella pg_dashboard_events_aoo000 2 time",$conn->errorInfo());
                //$conn->rollBack();
                //return Array("success"=>-1,"errors"=>$error);
                
            }
            
            return Array("success"=>1,"message"=>"inserimento riuscito","protocollo"=>$numero,"errors"=>$error);
	} 
        else{	
            return Array("success"=>-1,"message"=>"nessuna connessione attiva");
	}
    }
	
	
	static function richiediProtocolloOut($username,$nominativo,$oggetto,$note="",$data_prot=""){
        $dsn = sprintf('pgsql:dbname=%s;host=%s;port=%s',DB_NAME_PROT,DB_HOST_PROT,DB_PORT_PROT);
        try{
			$conn = new PDO($dsn, DB_USER_PROT, DB_PWD_PROT);
		}
		catch (Exception $e){
	        return Array("success"=>-1,"message"=>"nessuna connessione attiva");
		}
        $pg_inseritodagruppo = "c_E463"; 
        $aoo = "aoo000"; 
        $pg_protocollo_documento = NULL; 
        $pg_tipo_protocollo = "E";
        $pg_data_documento =  ($data_prot)?($data_prot):(date("Y-m-d")); 
        $pg_corrispondente = $nominativo; //Nominativo 
        $pg_indirizzo = ""; //Indirizzo + civico 
        $pg_indirizzo_cap =  ""; //CAP 
        $pg_indirizzo_citta =  ""; // Città 
        $pg_indirizzo_provincia =  ""; //Provincia

        $pg_oggetto = $oggetto;//Oggetto e tipo pratica
        $pg_annotazioni = $note; //Note

        $pg_inseritoda = $username;
        $pg_titolo = "6";
        $pg_classe = "3";
        $pg_sottoclasse = "0";
        
	if ($conn){
            $sql="select id_entita, position,denominazione from entita join positions using(id_entita) where username=?;";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($username))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }

            if ($numrows>0){
                    $id_utente = $res["position"];
                    $id_entita = $res["id_entita"];
                    $denominazione = $res["denominazione"];
                    $sql="select parent from entita where id_entita =?";
                    $stmt = $conn->prepare($sql);
                    if ($stmt->execute(Array($res["position"]))){
                        $res = $stmt->fetch(PDO::FETCH_ASSOC);
                        $pg_inseritodagruppo = $res["parent"];
                    }
            } else {
                return Array("success"=>-1,"message"=>"utente non presente con username $username");
            }
/*
            $sql="select denominazione from entita where id_entita=?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($id_identita))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }
            if ($numrows>0){
                    $denominazione = $res["denominazione"];
            } else {
                return Array("success"=>-1,"message"=>"gruppo non presente");
            }
*/
            $sql="select \"pg_Descrizione\" from pg_titolario_aoo000 where \"pg_Titolo\"=? and \"pg_Classe\"=? and \"pg_SottoClasse\"=?;";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($pg_titolo,$pg_classe,$pg_sottoclasse))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }
            if ($numrows>0){
                    $denominazione = $res["pg_Descrizione"];
            } else {
                return Array("success"=>-1,"message"=>"titolario non presente");
            }


            $sql="select \"pg_Descrizione\" from pg_titolario_aoo000 where \"pg_Titolo\"=? and \"pg_Classe\"=? and \"pg_SottoClasse\"=?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($pg_titolo,$pg_classe,$pg_sottoclasse))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return Array("success"=>-1);
            }
            if ($numrows>0){
                    $denominazione = $res["pg_Descrizione"];
            } else {
                return Array("success"=>-1,"message"=>"classe titolario non presente");
            }

            $conn->beginTransaction();
            $conn->exec("LOCK TABLE pg_numeroprotocollo_aoo000 IN ACCESS EXCLUSIVE MODE;");
            $sql="select prossimo_numero_protocollo from pg_numeroprotocollo_aoo000 where aoo='aoo000' and anno=?";
            $stmt=$conn->prepare($sql);
            if ($stmt->execute(Array(date("Y",time())))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            if ($numrows>0){
                $prossimo_numero_protocollo = $res["prossimo_numero_protocollo"];
                $sql="update pg_numeroprotocollo_aoo000 set prossimo_numero_protocollo=prossimo_numero_protocollo+1 where aoo='aoo000' and anno=?";
                $stmt = $conn->prepare($sql);
                if (!$stmt->execute(Array(date("Y",time())))){
                    $conn->rollBack();
                    return Array("success"=>-1,"message"=>"aggiornamento numero protocollo non effettuato");
                }

            }
            else{
                $conn->rollBack();
                return Array("success"=>-1,"message"=>"numero protocollo non assegnato");
            }

            


            $numero = sprintf("%07d",$prossimo_numero_protocollo);
            $pg_id = substr(date("Y",time()),-2) . "1" . $numero . "00";
            $pg_numero_protocollo =  $prossimo_numero_protocollo;
            $pg_anno_protocollo = date("Y",time());
            $pg_data_protocollo = date("Y-m-d");
            $pg_ora_protocollo = date("H:i:s", time());
            $pg_data_arrivo = NULL;
            $pg_numero_documento = NULL;
            $pg_f_riservato = 0;
            $pg_f_annullato = "N";
            $pg_f_modificato = "N";
            $pg_f_fascicolato = "N";
            $pg_f_notificato = "N";
            $pg_noteannullamento = "";
            $pg_id_spedizione = 0;
            $pg_importo_vlr_euro = 0;
            $pg_importo_spz_euro = 0;
            $pg_n_allegati = 1;
            $pg_numeroraccomandata = NULL;
            $pg_postaparticolare = "";
            $pg_reg_emergenza = NULL;
            $pg_nr_reg_emergenza = NULL;
            $pg_data_reg_emergenza = NULL;
            $pg_data_timbro = NULL;
            $id_registrazione = $pg_id;
            $pg_id_oggetto = NULL;
            $numero_protocollo = $pg_numero_protocollo;
            $data_immissione = date("Y-m-d") . " " . date("H:i:s", time()) ;
            $data_registrazione =  $pg_data_protocollo;
            $tempo_registrazione = $pg_ora_protocollo;
            $modificato = "FALSE";
            $annullato = "FALSE";
            $note =  $pg_annotazioni ;
            $utente_emergenza = "";
            $ambito = "esterno";
            $private = 0;
            $type = NULL;
            $emergency_protocol = NULL;
            $document_medium = 0;
            $pg_corrispondente_codice = "";
            $pg_mail = NULL;
            $pg_inseritoda_settore = $pg_inseritodagruppo;
            $pg_inseritoda_servizio = 0;
            $pg_inseritoda_ufficio = 0;
            $pg_spedizione = NULL;
            $pg_altri_corrispondenti = NULL;
            $pg_accessing_docs = NULL;
            $pg_num_attachfiles = 0;
            $pg_albo_pubblished = 0;
            $sql = "insert into pg_protocollo_aoo000 (
                pg_id, pg_anno_protocollo, pg_numero_protocollo, pg_tipo_protocollo, pg_data_protocollo, pg_ora_protocollo, pg_data_arrivo, pg_corrispondente, 
                pg_indirizzo, pg_oggetto, pg_data_documento, pg_protocollo_documento, pg_numero_documento, pg_annotazioni, pg_f_riservato, pg_f_annullato, 
                pg_f_modificato, pg_f_fascicolato, pg_f_notificato, pg_noteannullamento, pg_titolo, pg_classe, pg_sottoclasse, pg_inseritoda, 
                pg_id_spedizione, pg_importo_vlr_euro, pg_importo_spz_euro, pg_n_allegati, pg_inseritodagruppo, pg_numeroraccomandata, pg_postaparticolare, pg_reg_emergenza, 
                pg_nr_reg_emergenza, pg_data_reg_emergenza, pg_data_timbro, id_registrazione, pg_id_oggetto, numero_protocollo, data_immissione, data_registrazione, 
                tempo_registrazione, id_utente, modificato, annullato, note, utente_emergenza, ambito, aoo, 
                private, type, emergency_protocol, document_medium, pg_corrispondente_codice, pg_mail, pg_inseritoda_settore, pg_inseritoda_servizio, 
                pg_inseritoda_ufficio, pg_spedizione, pg_indirizzo_cap, pg_indirizzo_citta, pg_indirizzo_provincia, pg_altri_corrispondenti, pg_accessing_docs, pg_num_attachfiles, 
                pg_albo_pubblished)
                VALUES(
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?)";

            $stmt = $conn->prepare($sql);
            $data=Array(
                $pg_id, $pg_anno_protocollo, $pg_numero_protocollo, $pg_tipo_protocollo, $pg_data_protocollo, $pg_ora_protocollo, $pg_data_arrivo, $pg_corrispondente,
                $pg_indirizzo, $pg_oggetto, $pg_data_documento, $pg_protocollo_documento, $pg_numero_documento, $pg_annotazioni, $pg_f_riservato, $pg_f_annullato,
                $pg_f_modificato, $pg_f_fascicolato, $pg_f_notificato, $pg_noteannullamento, $pg_titolo, $pg_classe, $pg_sottoclasse, $pg_inseritoda, 
                $pg_id_spedizione,$pg_importo_vlr_euro, $pg_importo_spz_euro, $pg_n_allegati, $pg_inseritodagruppo, $pg_numeroraccomandata, $pg_postaparticolare, $pg_reg_emergenza,
                $pg_nr_reg_emergenza, $pg_data_reg_emergenza, $pg_data_timbro, $id_registrazione, $pg_id_oggetto, $numero_protocollo, $data_immissione, $data_registrazione,  
                $tempo_registrazione, $id_utente, $modificato, $annullato, $note, $utente_emergenza, $ambito, $aoo, 
                $private, $type, $emergency_protocol, $document_medium,$pg_corrispondente_codice, $pg_mail, $pg_inseritoda_settore, $pg_inseritoda_servizio, 
                $pg_inseritoda_ufficio, $pg_spedizione,$pg_indirizzo_cap, $pg_indirizzo_citta, $pg_indirizzo_provincia, $pg_altri_corrispondenti, $pg_accessing_docs, $pg_num_attachfiles, 
                $pg_albo_pubblished);

            $error = Array();
            if (!$stmt->execute($data)){
                $error[]=Array("Errore inserimento dati nella tabella pg_protocollo_aoo000",$conn->errorInfo());
                $conn->rollBack();
                return Array("success"=>-1,"errors"=>$error);
            }
            else{
                $conn->commit();
            }

            $strxml = "<?xml version=''1.0'' encoding=''UTF-8''?>\n";
            $strxml = $strxml . "<!DOCTYPE Segnatura SYSTEM \"Segnatura.dtd\">\n";
            $strxml = $strxml . "<Segnatura versione=''2001-05-07'' xml:lang=''it''>\n";
            $strxml = $strxml . "  <Intestazione>\n";
            $strxml = $strxml . "    <Identificatore>\n";
            $strxml = $strxml . "      <CodiceAmministrazione>c_e463</CodiceAmministrazione>\n";
            $strxml = $strxml . "      <CodiceAOO>$aoo</CodiceAOO>\n";
            $strxml = $strxml . "      <NumeroRegistrazione>$numero</NumeroRegistrazione>\n";
            $strxml = $strxml . "      <DataRegistrazione>$pg_data_protocollo</DataRegistrazione>\n";
            $strxml = $strxml . "    </Identificatore>\n";
            $strxml = $strxml . "    <Origine>\n";
            $strxml = $strxml . "      <IndirizzoTelematico>\n";
            $strxml = $strxml . "      </IndirizzoTelematico>\n";
            $strxml = $strxml . "      <Mittente>\n";
            $strxml = $strxml . "        <Amministrazione>\n";
            $strxml = $strxml . "          <Denominazione>Comune della Spezia</Denominazione>\n";
            $strxml = $strxml . "          <CodiceAmministrazione>c_e463</CodiceAmministrazione\n";
            $strxml = $strxml . "          <UnitaOrganizzativa tipo=''permanente''>\n";
            $strxml = $strxml . "            <Denominazione>$denominazione</Denominazione>\n";
            $strxml = $strxml . "            <IndirizzoPostale>\n";
            $strxml = $strxml . "              <Toponimo dug=''Piazza''>Europa</Toponimo>\n";
            $strxml = $strxml . "              <Civico>1</Civico>\n";
            $strxml = $strxml . "              <CAP>19125</CAP>\n";
            $strxml = $strxml . "              <Comune>La Spezia</Comune>\n";
            $strxml = $strxml . "              <Provincia>SP</Provincia>\n";
            $strxml = $strxml . "            </IndirizzoPostale>\n";
            $strxml = $strxml . "          </UnitaOrganizzativa>\n";
            $strxml = $strxml . "        </Amministrazione>\n";
            $strxml = $strxml . "        <AOO>\n";
            $strxml = $strxml . "          <Denominazione>Comune della Spezia</Denominazione>\n";
            $strxml = $strxml . "        </AOO>\n";
            $strxml = $strxml . "      </Mittente>\n";
            $strxml = $strxml . "    </Origine>\n";
            $strxml = $strxml . "    <Destinazione confermaRicezione=''si''>\n";
            $strxml = $strxml . "      <IndirizzoTelematico>\n";
            $strxml = $strxml . "      </IndirizzoTelematico>\n";
            $strxml = $strxml . "      <Destinatario>\n";
            $strxml = $strxml . "        <Denominazione>$pg_corrispondente</Denominazione>\n";
            $strxml = $strxml . "      </Destinatario>\n";
            $strxml = $strxml . "    </Destinazione>\n";
            $strxml = $strxml . "    <Riservato>N</Riservato>\n";
            $strxml = $strxml . "    <RiferimentoDocumentiCartacei/>\n";
            $strxml = $strxml . "    <Oggetto>$pg_oggetto</Oggetto>\n";
            $strxml = $strxml . "    <Classifica>\n";
            $strxml = $strxml . "      <CodiceAmministrazione>c_e463</CodiceAmministrazione>\n";
            $strxml = $strxml . "      <CodiceAOO>$aoo</CodiceAOO>\n";
            $strxml = $strxml . "      <Denominazione>$titolo / $classe</Denominazione>\n";
            $strxml = $strxml . "      <Livello nome=''titolo''>$pg_titolo</Livello>\n";
            $strxml = $strxml . "      <Livello nome=''classe''>$pg_classe</Livello>\n";
            $strxml = $strxml . "      <Livello nome=''sottoclasse''>$pg_sottoclasse</Livello>\n";
            $strxml = $strxml . "    </Classifica>\n";
            $strxml = $strxml . "    <Note>$pg_annotazioni</Note>\n";
            $strxml = $strxml . "  </Intestazione>\n";
            $strxml = $strxml . "  <Descrizione>\n";
            $strxml = $strxml . "    <Documento id=''main_doc'' tipoRiferimento=''cartaceo''>\n";
            $strxml = $strxml . "      <TitoloDocumento>Protocollo: $pg_anno_protocollo-$numero</TitoloDocumento>\n";
            $strxml = $strxml . "      <Classifica>\n";
            $strxml = $strxml . "        <CodiceAmministrazione>c_e463</CodiceAmministrazione>\n";
            $strxml = $strxml . "        <CodiceAOO>$aoo</CodiceAOO>\n";
            $strxml = $strxml . "        <Denominazione>$titolo / $classe</Denominazione>\n";
            $strxml = $strxml . "        <Livello nome=''titolo''>$pg_titolo</Livello>\n";
            $strxml = $strxml . "        <Livello nome=''classe''>$pg_classe</Livello>\n";
            $strxml = $strxml . "        <Livello nome=''sottoclasse''>$pg_sottoclasse</Livello>\n";
            $strxml = $strxml . "      </Classifica>\n";
            $strxml = $strxml . "    </Documento>\n";
            $strxml = $strxml . "  </Descrizione>\n";
            $strxml = $strxml . "</Segnatura>";

            $sql = "insert into pg_protocollo_segnatura_aoo000 (id_registrazione, segnatura) VALUES(?,?);";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute(Array($pg_id,$strxml))){
                $error[]=Array("Errore inserimento dati nella tabella pg_protocollo_segnatura_aoo000",$conn->errorInfo());
                $conn->rollBack();
                return Array("success"=>-1,"errors"=>$error);
                
            }
            //$conn->commit();
            if ($pg_tipo_protocollo=="U") {
                    $role_id=1;
            } else {
                    $role_id=2;
            }
            $sql = "insert into pg_referer_aoo000 (registration_id, referer_id, role_id, data) VALUES(?, ?, ?, ?);";
            //$qresult = @pg_Exec ($connection, $sql);
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute(Array($pg_id, $pg_inseritodagruppo, $role_id, $pg_data_protocollo))){
                $error[]=Array("Errore inserimento dati nella tabella pg_referer_aoo000",$conn->errorInfo());
                //$conn->rollBack();
                return Array("success"=>-1,"errors"=>$error);
                
            }
            $sql = "insert into pg_registration_history_aoo000 (registration_id, history_id) VALUES (?, nextval ('counter_ammgen_history_id'));";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute(Array($pg_id))){
                $error[]=Array("Errore inserimento dati nella tabella pg_registration_history_aoo000",$conn->errorInfo());
                //$conn->rollBack();
                return Array("success"=>-1,"errors"=>$error);
                
            }

            $sql = "select history_id from pg_registration_history_aoo000 where registration_id=?;";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($pg_id))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            if ($numrows>0){
                $history_id = $res["history_id"];
            }

            $sql = "insert into history_events (history_id, event_id) VALUES(?, nextval ('counter_ammgen_event_id'));";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute(Array($history_id))){
                $error[]=Array("Errore inserimento dati nella tabella history_events",$conn->errorInfo());
                //$conn->rollBack();
                return Array("success"=>-1,"errors"=>$error);
            }

            $sql = "select event_id from history_events where history_id=?;";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(Array($history_id))){
                $numrows = $stmt->rowCount();
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            if ($numrows>0){
                $event_id = $res[0]["event_id"];
                $next_event_id = $res[1]["event_id"];
            }
            else{
                $error[]=Array("Errore nella selezione degli event_id della tabella history_events");
                //$conn->rollBack();
                return Array("success"=>-1,"errors"=>$error);
            }
            $event_kind = "registering";
            $previous_id = 0;
            $charge_id = $id_utente;
            $event_time = $data_immissione;
            $user_id = $id_entita;
            $data = Array($event_id, $event_kind, $previous_id, $charge_id, $event_time, $user_id);
            $sql = "insert into events (event_id, event_kind, previous_id, charge_id, event_time, user_id) VALUES(?,?,?,?,?,?);";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute($data)){
                $error[]=Array("Errore inserimento dati nella tabella events",$conn->errorInfo());
                //$conn->rollBack();
                //return Array("success"=>-1,"errors"=>$error);
            }


            $previous_id = $event_id;
            $event_id = $next_event_id;
            $event_kind = "assignment";
            $charge_id = $id_utente;
            $event_time = $data_immissione;
            $user_id = $id_entita;

            
            
            $data = Array($event_id, $event_kind, $previous_id, $charge_id, $event_time, $user_id);
            $sql = "insert into events (event_id, event_kind, previous_id, charge_id, event_time, user_id) VALUES(?,?,?,?,?,?);";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute($data)){
                $error[]=Array("Errore inserimento dati nella tabella events 2 time",$conn->errorInfo());
                //$conn->rollBack();
                //return Array("success"=>-1,"errors"=>$error); 
            }
            $registration_id = $pg_id;
            $to_entity_id = $pg_inseritodagruppo;
            $from_db_event_id = 0;
            $event_status = "D";
            $id_dashboard_type = "AssignmentDashBoard";
            $note = "Primo smistamento della registrazione";
            $timestamp = $data_immissione;
            $from_entity_id = $id_utente;
            $insert_by_charge = $id_utente;
            $data = Array($registration_id, $to_entity_id, $from_db_event_id, $event_status, $id_dashboard_type, $note, $timestamp, $from_entity_id, $insert_by_charge);
            $sql = "insert into pg_dashboard_events_aoo000 (db_event_id, registration_id, to_entity_id, from_db_event_id, event_status, id_dashboard_type, note, timestamp, from_entity_id, insert_by_charge) VALUES
                    (nextval('counter_aoo000_dashboard_event_id'), ?, ?, ?, ?, ?, ?, ?, ?, ?);";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute($data)){
                $error[]=Array("Errore inserimento dati nella tabella pg_dashboard_events_aoo000",$conn->errorInfo());
                //$conn->rollBack();
                //return Array("success"=>-1,"errors"=>$error);
            }

            $timestamp = date("Y-m-d") . " " . date("H:i:s", time());
            $event_status = "A";
            $id_dashboard_type = "ManagedDashBoard";
            $note = "<i>Spostato manualmente in Documenti elaborati</i>";
            $data = Array($registration_id, $to_entity_id, $from_db_event_id, $event_status, $id_dashboard_type, $note, $timestamp, $from_entity_id, $insert_by_charge);
            $sql = "insert into pg_dashboard_events_aoo000 (db_event_id, registration_id, to_entity_id, from_db_event_id, event_status, id_dashboard_type, note, timestamp, from_entity_id, insert_by_charge) VALUES
                    (nextval('counter_aoo000_dashboard_event_id'), ?, ?, ?, ?, ?, ?, ?, ?, ?);";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute($data)){
                $error[]=Array("Errore inserimento dati nella tabella pg_dashboard_events_aoo000 2 time",$conn->errorInfo());
                //$conn->rollBack();
                //return Array("success"=>-1,"errors"=>$error);
                
            }
            
            return Array("success"=>1,"message"=>"inserimento riuscito","protocollo"=>$numero,"errors"=>$error);
	} 
        else{	
            return Array("success"=>-1,"message"=>"nessuna connessione attiva");
	}
    }
	
	
	
	function nuovoNumeroTitolo($app="pe"){
	  $sql="SELECT pe.nuovo_titolo();";
	  $conn = utils::getDB();
	  $stmt = $conn->prepare($sql);
	  if($stmt->execute()){
		 $res = $stmt->fetchColumn();
		 return $res;
	  }
	  else{
		 return -1;
	  }
	}
}   
?>
