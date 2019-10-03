<?php
use Doctrine\Common\ClassLoader;
require_once APPS_DIR.'plugins/Doctrine/Common/ClassLoader.php';
require_once APPS_DIR.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'pratica.class.php';

class pratica extends generalPratica{
   function __construct($id,$type=0){

        $this->pratica=$id;
        $db = new sql_db(DB_HOST.":".DB_PORT,DB_USER,DB_PWD,DB_NAME, false);
        if(!$db->db_connect_id)  die( "Impossibile connettersi al database ".DB_NAME);
        $this->db=$db;
        $this->db1=$this->setDB();
        switch($type){
            case 1:
                $this->initCdu();
                break;
            case 2:
                $this->initCE();
                break;
            case 3:
                $this->initVigi();
                break;
            default:
                $this->initPE();
                break;
        }

    }
  

    function initPE(){
		$db=$this->db1;
		if ($this->pratica && is_numeric($this->pratica)){
			//INFORMAZIONI SULLA PRATICA
			$sql="SELECT numero,tipo,resp_proc,resp_it,resp_ia,date_part('year',coalesce(data_prot,data_presentazione)) as anno,data_presentazione,data_prot FROM pe.avvioproc  WHERE pratica=?";
			$r=$db->fetchAssoc($sql, Array($this->pratica));
			$this->info=$r;

			$numero=appUtils::normalizeNumero($this->info['numero']);
			$tmp=explode('-',$numero);
			if (count($tmp)==2 && preg_match("|([A-z0-9]+)|",$tmp[0])){
				$tmp[0]=(preg_match("|^[89]|",$tmp[0]))?("19".$tmp[0]):($tmp[0]);
				$numero=implode('-',$tmp);
			}
			$anno=($r['anno'])?($r['anno']):($tmp[0]);
			$numero = $this->pratica;
			//Struttura delle directory
			$arrDir=Array(DOCUMENTI,'pe');
	                $arrDir[]=$anno;
			$this->annodir=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
			$arrDir[]=$numero;
			$this->documenti=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
			$arrDir[]="allegati";
			$this->allegati=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
			$arrDir[]="tmb";
			$this->allegati_tmb=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;

			$this->url_documenti="/documenti/pe/$anno/$numero/";
			$this->url_allegati="/documenti/pe/$anno/$numero/allegati/";
			$this->smb_documenti=SMB_PATH."$anno\\$numero\\";
			$this->createStructure();
			//INFO PRATICA PREC E SUCC
			$sql="SELECT max(pratica) as pratica FROM pe.avvioproc WHERE pratica < ?";
			$this->prev=$db->fetchColumn($sql,Array($this->pratica));
			$sql="SELECT min(pratica) as pratica FROM pe.avvioproc WHERE pratica > ?";
			$this->next=$db->fetchColumn($sql,Array($this->pratica));
		}

		
        $this->_setInfoUsers();
        //ESTRAGGO INFORMAZIONI SUL DIRIGENTE
    }
    
    private function _setInfoUsers(){
        $db=$this->db1;    
		$sql="SELECT userid as dirigente FROM admin.users WHERE attivato=1 and '13' = ANY(string_to_array(coalesce(gruppi,''),','));";
		$dirig=$db->fetchColumn($sql);
		$this->info['dirigente']=$dirig;
		//ESTRAGGO INFORMAZIONI SUL RESPONSABILE DEL SERVIZIO
		$sql="SELECT userid as rds FROM admin.users WHERE attivato=1 and '15' = ANY(string_to_array(coalesce(gruppi,''),','));";
		$rds=$db->fetchColumn($sql);
		$this->info['rds']=$rds;
		//INFO UTENTE (ID-GRUPPI-NOME)
		$this->userid=$_SESSION['USER_ID'];
		$this->usergroups=$_SESSION['GROUPS'];
		$sql="SELECT username FROM admin.users WHERE userid=?";
		$this->user=$db->fetchColumn($sql,Array($this->userid));
				
	}
    private function initCE(){
        $conn = utils::getDb();
        if ($this->pratica && is_numeric($this->pratica)){
            //INFORMAZIONI SULLA PRATICA
            $sql="SELECT A.pratica,C.nome,A.numero,A.data_convocazione,A.ora_convocazione,date_part('year',data_convocazione) as anno,A.sede1 as sede,C.tipologia FROM ce.commissione A inner join pe.e_enti B ON(A.tipo_comm=B.id) inner join ce.e_tipopratica C ON(B.codice=C.tipologia)  WHERE A.pratica=?";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute(Array($this->pratica))){
                print "<p>Errore nell'esecuzione della query : <br>$sql</p>";
                return;
            }
            $r=$stmt->fetch(PDO::FETCH_ASSOC);
            $this->info=$r;
            $this->titolo=sprintf("%s n° %s del %s",$r["tipo_pratica"],$r["numero"],$r["data_convocazione"]);
            /*if($this->info['tipo'] < 10000 || in_array($this->info['tipo'],Array(14000,15000))){
                    $this->tipopratica='pratica';
            }
            elseif($this->info['tipo'] < 13000){
                    $this->tipopratica='dia';
            }
            else{
                    $this->tipopratica='ambientale';
            }*/
            $this->tipopratica=$info["tipologia"];
            $numero=appUtils::normalizeNumero($this->info['numero']);
            $tmp=explode('-',$numero);
            if (count($tmp)==2 && preg_match("|([A-z0-9]+)|",$tmp[0])){
                    $tmp[0]=(preg_match("|^[89]|",$tmp[0]))?("19".$tmp[0]):($tmp[0]);
                    $numero=implode('-',$tmp);
            }
            $anno=($r['anno'])?($r['anno']):($tmp[0]);
            $numero = $this->pratica;
            //Struttura delle directory
            //$arrDir=Array('/data','sanremo','pe','praticaweb','documenti','pe',$anno);
            $arrDir=Array(DATA_DIR,'praticaweb','documenti','ce',$anno);
            $this->annodir=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
            $arrDir[]=$numero;
            $this->documenti=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
            $arrDir[]="allegati";
            $this->allegati=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
            $arrDir[]="tmb";
            $this->allegati_tmb=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;

            $this->url_documenti="/documenti/ce/$anno/$numero/";
            $this->url_allegati="/documenti/ce/$anno/$numero/allegati/";
            $this->smb_documenti=SMB_PATH."$anno/$numero/";


            //INFO PRATICA PREC E SUCC
            $sql="SELECT max(pratica) as pratica FROM pe.avvioproc WHERE pratica < ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute(Array($this->pratica));
            $this->prev=$stmt->fetchColumn();
            $sql="SELECT min(pratica) as pratica FROM pe.avvioproc WHERE pratica > ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute(Array($this->pratica));
            $this->prev=$stmt->fetchColumn();
        }
        
    }
       
	private function initCdu(){
		$db=$this->db1;
		$this->tipopratica='cdu';
		if($this->pratica){
			$sql="select protocollo,date_part('year',data) as anno FROM cdu.richiesta WHERE pratica=?";
			$r=$db->fetchAssoc($sql,Array($this->pratica));
			$this->info=$r;
			extract($r);
			$arrDir=Array(DATA_DIR,'praticaweb','documenti','cdu',$anno);
			$this->annodir=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
			$arrDir[]=$protocollo;
			$this->documenti=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
			$this->url_documenti="/documenti/cdu/$anno/$protocollo/";
		}
	}
	
	
		function createStructure(){
		if($this->pratica){
			if(!file_exists($this->annodir)) {
				mkdir($this->annodir);
				chmod($this->annodir,0777);
				print (!file_exists($this->annodir))?("Errore nella creazione della cartella $this->annodir\n"):("");
			}
			if(!file_exists($this->documenti)) {
				mkdir($this->documenti);
				chmod($this->documenti,0777);
				//print (!file_exists($this->documenti))?("Errore nella creazione della cartella $this->documenti\n"):("Cartella $this->documenti creata con successo\n");
			}
			if($this->allegati && !file_exists($this->allegati)) {
				mkdir($this->allegati);
				chmod($this->allegati,0777);
				//print (!file_exists($this->allegati))?("Errore nella creazione della cartella $this->allegati\n"):("Cartella $this->allegati creata con successo\n");
			}
			if($this->allegati_tmb && !file_exists($this->allegati_tmb)){
				mkdir($this->allegati_tmb);
				chmod($this->allegati_tmb,0777);
				//print (!file_exists($this->allegati_tmb))?("Errore nella creazione della cartella $this->allegati_tmb\n"):("Cartella $this->allegati_tmb creata con successo\n");

			}
		}
	}
	
	
	function nuovaPratica($arrInfo){
		//Creazione Struttura nuova Pratica
		$this->createStructure();
		if(in_array($this->tipopratica,Array("ambientale","dia","pratica"))){
			$this->setAllegati();
			//Array('codice'=>null,'utente_in'=>$this->userid,'utente_fi'=>null,'data'=>"now",'stato_in'=>null,'stato_fi'=>null,'note'=>null,'tmsins'=>time(),'uidins'=>$this->userid);
			//$this->addTransition(Array('codice'=>'ardp',"utente_fi"=>$this->info["resp_proc"],"data"=>$arrInfo["data_resp"]));
			//$this->addTransition(Array('codice'=>'aipre',"utente_fi"=>$this->userid));
			//if ($this->info["resp_it"]) $this->addTransition(Array('codice'=>'aitec',"utente_fi"=>$this->info["resp_it"],"data"=>$arrInfo["data_resp_it"]));
			//if ($this->info["resp_ia"]) $this->addTransition(Array('codice'=>'aiamm',"utente_fi"=>$this->info["resp_ia"],"data"=>$arrInfo["data_resp_ia"]));
		}
		
	}
	private function setAllegati($list=Array()){
		/*if(!$list){
			$db=$this->db1;
			$ris=$db->fetchAll("select $this->pratica as pratica,id as documento,1 as allegato,$this->userid as uidins,".time()." as tmsins from pe.e_documenti where default_ins=1");
			for ($i=0;$i<count($ris);$i++) $db->insert("pe.allegati",$ris[$i]);
		}*/
	}
    
/*********************************************************************************************************/	
/*------------------------------------     LAVORI         -----------------------------------------------*/
/*********************************************************************************************************/
    
	function setDateLavori($data){
		$db=$this->db;	
		$sql="select id from pe.lavori where pratica=$this->pratica";
		$db->sql_query($sql);
		$res=$db->sql_fetchrow();
		// se ho giÃƒÂ  il record esco
		
		if(!$res){
			$sql="SELECT tipologia FROM pe.avvioproc INNER JOIN pe.e_tipopratica ON(avvioproc.tipo=e_tipopratica.id) WHERE pratica=$this->pratica;";
			$db->sql_query($sql);
			$tipo=$db->sql_fetchfield('tipologia');
			switch($tipo){
				case "pratica":
					$sql="insert into pe.lavori (pratica,scade_il,scade_fl,uidins,tmsins) values ($this->pratica,'$data'::date + INTERVAL '1 year', '$data'::date + INTERVAL '3 year',".$_SESSION["USER_ID"].",".time().");";
		
					$db->sql_query($sql);
					//INSERIMENTO SCADENZE RATE ONERI URBANIZZAZIONE E CORRISPETTIVO MONETARIO
					//$db->sql_query($sql);
					break;
				case "dia":
					$sql="insert into pe.lavori (pratica,scade_il,scade_fl,uidins,tmsins) values ($this->pratica,('$data'::date + INTERVAL '1 year 30 day')::date, ('$data'::date + INTERVAL '3 year 30 day')::date,".$_SESSION["USER_ID"].",".time().");";
					
                                        break;
                                case "scia":
                                        $sql="insert into pe.lavori (pratica,scade_il,scade_fl,uidins,tmsins) values ($this->pratica,('$data'::date + INTERVAL '1 year')::date, ('$data'::date + INTERVAL '3 year')::date,".$_SESSION["USER_ID"].",".time().");";
					//INSERIMENTO SCADENZE RATE ONERI URBANIZZAZIONE E CORRISPETTIVO MONETARIO
					//$this->setDateRateCM($data);
					//$this->setDateRateOC($data);
					break;
				default:
					break;
			}
                        $db->sql_query($sql);
			//INSERIMENTO SCADENZE DATE INIZIO E FINE LAVORI
			
			
			
		}
	}
	
/*********************************************************************************************************/	
/*------------------------------------     TITOLO         -----------------------------------------------*/
/*********************************************************************************************************/
	
	function nuovoTitolo($data){
		$db=$this->db;
		$sql="SELECT numero,prog FROM pe.avvioproc WHERE pratica=$this->pratica;";
		$db->sql_query($sql);
		$numero=$db->sql_fetchfield('numero');
		$prog=$db->sql_fetchfield('prog');
		$sql="UPDATE pe.titolo X SET numero=$prog,titolo='$numero' WHERE pratica=$this->pratica;";
		$db->sql_query($sql);
		
	}
/*********************************************************************************************************/	
/*------------------------------------     WORKFLOW       -----------------------------------------------*/
/*********************************************************************************************************/	
	
	/* WORKFLOW Da Mettere*/
	

	function addTransition($prms){
		$db=$this->db1;
		$initVal=Array("pratica"=>$this->pratica,'codice'=>null,'utente_in'=>$this->userid,'utente_fi'=>null,'data'=>"now",'stato_in'=>null,'stato_fi'=>null,'note'=>null,'tmsins'=>time(),'uidins'=>$this->userid);
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
					$this->addRole(substr($cod,1),$params['utente_fi'],$params['data']);
					break;
				case "rardp":
				case "raitec":
				case "raiamm":
					$this->delRole(substr($cod,2));
					$this->addRole(substr($cod,2),$params['utente_fi'],$params['data']);
					break;
				default:
					break;
			}
		}
		
	}    
	
/*********************************************************************************************************/	
/*------------------------------------     ONERI          -----------------------------------------------*/
/*********************************************************************************************************/
	
	//Calcolo Corrispettivo Monetario
	function setCM(){
		$db=$this->db;
		$sql="UPDATE oneri.c_monetario SET totale_noscomputo = round(coalesce(sup_cessione*$this->cm_mq,0),2),totale = round(coalesce(sup_cessione*$this->cm_mq,0),2)-coalesce(scomputo,0) WHERE pratica=$this->pratica;";

		$db->sql_query($sql);
	}
	
	//Calcolo rate Corrispettivo Monetario
	function setRateCM(){
		$db=$this->db;
        $t=time();
		$sql="DELETE FROM oneri.rate WHERE pratica=$this->pratica and rata in (5,6);
INSERT INTO oneri.rate(pratica,rata,totale,uidins,tmsins) (
(SELECT $this->pratica as pratica,5 as rata,(totale*0.5),$this->userid,$t FROM oneri.c_monetario WHERE pratica=$this->pratica)
UNION
(SELECT $this->pratica as pratica,6 as rata,(totale*0.5),$this->userid,$t FROM oneri.c_monetario WHERE pratica=$this->pratica));";
		$db->sql_query($sql);
		
		
		
		$menu=new Menu('pratica','pe');
		$menu->add_menu($this->pratica,'120');
        $menu->add_menu($this->pratica,'130');
		
	}
	//Calcolo date scadenza rate CM
	function setDateRateCM($data){
		if($data){
			$db=$this->db;
			$sql="UPDATE oneri.rate SET data_scadenza='$data'::date WHERE pratica=$this->pratica  and rata=5;";
			$sql.="UPDATE oneri.rate SET data_scadenza='$data'::date + INTERVAL '1 year' WHERE pratica=$this->pratica  and rata=6;";
			$db->sql_query($sql);
		}
	}
	//Calcolo della Fideiussione CM
	function setFidiCM(){
		$db=$this->db;
		$sql="UPDATE oneri.c_monetario SET fideiussione=(SELECT totale-coalesce(versato,0) from oneri.rate where pratica=$this->pratica and rata=6) WHERE pratica=$this->pratica;";
		//echo $sql;        
		$db->sql_query($sql);
	}
	//Calcolo Totale Oneri Costruzione
	function setOC(){
		$db=$this->db;
		$sql="UPDATE oneri.oneri_concessori SET totale = coalesce(oneri_urbanizzazione,0) + coalesce(oneri_costruzione,0)-(coalesce(scomputo_urb,0)+coalesce(scomputo_costr,0)) WHERE pratica=$this->pratica;";
		$db->sql_query($sql);
	}
	
	//Calcolo Rate Oneri Costruzione
    function setRateOC($rateizzato=1){
		$db=$this->db;
        $t=time();
		if($rateizzato==1)	// <---- MODIFICA DEL 21/06/2012
			$sql="DELETE FROM oneri.rate WHERE pratica=$this->pratica and rata in (1,2,3,4);
INSERT INTO oneri.rate(pratica,rata,totale,versato,uidins,tmsins) (
(SELECT $this->pratica as pratica,1 as rata,totale/4,totale/4,$this->userid,$t FROM oneri.vista_totali WHERE pratica=$this->pratica)
UNION
(SELECT $this->pratica as pratica,2 as rata,totale/4,totale/4,$this->userid,$t FROM oneri.vista_totali WHERE pratica=$this->pratica)
UNION
(SELECT $this->pratica as pratica,3 as rata,totale/4,totale/4,$this->userid,$t FROM oneri.vista_totali WHERE pratica=$this->pratica)
)
UNION
(SELECT $this->pratica as pratica,4 as rata,totale/4,totale/4,$this->userid,$t FROM oneri.vista_totali WHERE pratica=$this->pratica)
;";
		else
			$sql="DELETE FROM oneri.rate WHERE pratica=$this->pratica and rata in (1,2,3,4);
INSERT INTO oneri.rate(pratica,rata,totale,versato,uidins,tmsins) (SELECT $this->pratica as pratica,5 as rata,totale,totale,$this->userid,$t FROM oneri.vista_totali WHERE pratica=$this->pratica);";
        $db->sql_query($sql);
	utils::debug('calcolo_rate', $sql);
		$menu=new Menu('pratica','pe');
		$menu->add_menu($this->pratica,'120');
        $menu->add_menu($this->pratica,'130');
    }
	//Calcolo date scadenza rate OC
	function setDateRateOC($data){
		$db=$this->db;
		if($data){
			$sql="UPDATE oneri.rate SET data_scadenza='$data'::date WHERE pratica=$this->pratica and rata=1;";
			//$sql.="UPDATE oneri.rate SET data_scadenza='$data'::date + INTERVAL '1 year' WHERE pratica=$this->pratica and rata=2;";
			//$sql.="UPDATE oneri.rate SET data_scadenza='$data'::date + INTERVAL '3 year' WHERE pratica=$this->pratica and rata=3;";
			$db->sql_query($sql);
		}
	}
	//Calcolo della Fideiussione OC
	function setFidiOC(){
		$db=$this->db;
		$sql="UPDATE oneri.oneri_concessori SET fideiussione=coalesce((SELECT sum(totale-coalesce(versato,0)) FROM oneri.rate WHERE rata in (2,3) and pratica=$this->pratica),0) WHERE pratica=$this->pratica;";
        $db->sql_query($sql);
	}
	

}

?>
