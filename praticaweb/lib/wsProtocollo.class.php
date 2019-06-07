<?php
/**
 * Created by PhpStorm.
 * User: mamo
 * Date: 06/07/17
 * Time: 17:55
 */

require_once "/data/rapallo/pe/config.protocollo.php";

define('TEMPLATE_DIR',DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR);

require_once LOCAL_LIB."app.utils.class.php";
require_once LIB."utils.class.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoapmime.php";

class wsProtocollo{

    var $params;
    var $dbh;
    var $wsUrl;
    var $login;
    var $service;
    var $wsClient;
    var $result;
    var $modalita;
    var $direzione;

    /******************************************************************************************************************/

    /******************************************************************************************************************/
    function __construct($direzione='U',$modo='PEC'){
        $this->wsUrl = WSPROT_URL;
        $this->login = SERVICE_LOGIN;
        $this->service = "SicraWeb";
        $this->dbh = utils::getDb();
        $this->wsClient =  new nusoap_client_mime($this->wsUrl,'wsdl');

        $this->params = Array(
            "mittente"=> Array(
                "Denominazione_Entita"=> "Comune di Rapallo",
                "Denominazione"=>"S.U.E. - EDILIZIA PRIVATA",
                "CodiceAmministrazione"=>"c_h183",
                "IndirizzoTelematico"=>"protocollo@pec.comune.rapallo.ge.it",
                "UnitaOrganizzativa"=>"ED.PR",
                "CodiceTitolario"=>"6.3",
                "CodiceA00"=>"c_h183",
                "Indirizzo" => "Piazza Molfino 10",
                "Identificativo" => "ED.PR"
            ),
            "destinatario"=> Array()
        );
        $this->direzione = $direzione;
        $this->modalita = $modo;
        $this->result =  $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
    }
    /******************************************************************************************************************/

    /******************************************************************************************************************/
    private function subst($txt,$data){
        foreach($data as $k=>$v){
            $txt = str_replace("%($k)s",$v,$txt);
        }
        return $txt;
    }

    /******************************************************************************************************************/

    /******************************************************************************************************************/
    function inserisciDocumento($id,$type=0){
        $result = $this->result;
        $res = appUtils::getInfoDocumento($id,$type);
        
        if ($res["success"]==1){
 
            $err = $this->wsClient->getError();
            if ($err) {
                $result["success"] = -1;
                $result["message"] = $err;

            }
            else{
                $this->wsClient->addAttachment($res["file"],$res["data"]["nomefile"],$res["mimetype"]);
                $res["data"]["nomefile"] = basename($res["data"]["nomefile"]);
                $response = $this->wsClient->call("insertDocumento",Array($this->login,$res["data"]["nomefile"],$res["data"]["descrizione"]));
                if(!$response["lngErrNumber"] && $response["lngDocID"]){
                    $result["success"] = 1;
		    $res["data"]["idrichiesta"] = $response["lngDocID"];
                    $r = $this->caricaXML("documento",$res["data"]);
                    $xmlAllegato = $r["result"];
                    $result["result"] = Array("idallegato"=>$response["lngDocID"],"xml"=>$xmlAllegato);
                }
                else{
                    $result["success"] = -2;
                    $result["message"] = sprintf("Errore Numero %s - %s",$response["lngErrNumber"],$response["strErrString"]);
                }
 
            }
        }
        else{
            $result = $res;
        }
        return $result;
    }
    /********************************************************************************************************************/
    /*					RICHIESTA DI PROTOCOLLO IN USCITA						*/
    /********************************************************************************************************************/

    function richiediProtOut($pratica,$id=null,$app='pe'){
        $result = $this->result;
        $dataSubst = $this->params["mittente"];

        $sql = "SELECT * FROM pe.comunicazioni WHERE id = ?;";
        $stmt = $this->dbh->prepare($sql);
        if($stmt->execute(Array($id))){
           $res = $stmt->fetch();
           if ($res["destinatari"] && $res["destinatari"]!="{}"){
               $params["destinatari"] = explode(",",str_replace("{","",str_replace("}","",$res["destinatari"])));
           }
           else{
               $params["destinatari"] = Array();
           }
           if ($res["allegati"] && $res["allegati"]!="{}"){
               $params["allegati"] = explode(",",str_replace("{","",str_replace("}","",$res["allegati"])));
           }
           else{
               $params["allegati"] = Array();
           }
           if ($res["allegati_1"] && $res["allegati_1"]!="{}"){
               $params["allegati_1"] = explode(",",str_replace("{","",str_replace("}","",$res["allegati_1"])));
           }
           else{
               $params["allegati_1"] = Array();
           }
        }
        else{

        }
        $paramsKeys = array_keys($params);
        if (!(in_array("destinatari",$paramsKeys) && $params["destinatari"])) {
            $result["success"] =  -2;
            $result["message"] = "Nessun destinatario selezionato";
            return $result;
        }

        $d = $this->recuperaPratica($pratica,$app);
        foreach($d["result"] as $k=>$v){
            $dataSubst[$k] = $v;
        }
        $dataSubst["data_registrazione"] = date ("d/m/Y");

        $documentiOk = 1;
        $altriAllegati = 0;
        $multiDest =  (count($params["destinatari"]) > 1)?(1):(0);
        if (in_array("allegati",$paramsKeys) && $params["allegati"]){
            for($i=0;$i<count($params["allegati"]);$i++){
                $idDoc = $params["allegati"][$i];                
		$res = $this->inserisciDocumento($idDoc);
                $documentiOk = $documentiOk && $res["success"];
                if ($res["success"]==1){
                    if($i==0){
                        $dataSubst["documento"] = $res["result"]["xml"];
                        $altriAllegati = 1;
                    }
                    else
                        $xmlAll[] = $res["result"]["xml"];
                }
                else{

                }
                $this->wsClient->clearAttachments();
            }
            
        }

        if (in_array("allegati_1",$paramsKeys) && $params["allegati_1"]){
            for($i=0;$i<count($params["allegati_1"]);$i++){
                $idDoc = $params["allegati_1"][$i];                
		$res = $this->inserisciDocumento($idDoc,1);
                $documentiOk = $documentiOk && $res["success"];
                if ($res["success"]==1){
                    if($i==0 && $altriAllegati==0){
                        $dataSubst["documento"] = $res["result"]["xml"];
                    }
                    else
                        $xmlAll[] = $res["result"]["xml"];
                }
                else{

                }
                $this->wsClient->clearAttachments();
            }
            
        }
        $dataSubst["allegati"] = implode("\n",$xmlAll);
        for($i=0;$i<count($params["destinatari"]);$i++){
            $idDest = $params["destinatari"][$i];
            $res = $this->recuperaSoggetto($idDest,$multiDest,$app);
            if ($res["success"]==1){
                $denominazioni[] = $res["result"]["data"]["denominazione"];
                $xmlDest[] = $res["result"]["xml"];
            }
        }
        if (!$multiDest){
            $dataSubst["destinatari"] = implode("\n",$xmlDest);
        }
        else{
            $r = $this->caricaXML("destinatari",Array("destinatari"=>implode(", ",$denominazioni),"destinatari_multi"=>implode("\n",$xmlDest)));
            $dataSubst["destinatari"] = $r["result"];
        }
        $f = fopen(LOCAL_LIB.'../debug/DESTINATARI.debug','w');
        ob_start();
        print_r($dataSubst);
        $r = ob_get_contents();
        ob_end_clean();
        fwrite($f,$r);
        fclose($f);

        $r = $this->caricaXML("prot_out",$dataSubst);
        $fileXML = $r["result"];
        $this->wsClient->clearAttachments();
        $this->wsClient->addAttachment($fileXML,"richiesta_protocollo_out.xml","text/xml");
        $res =$this->wsClient->call("registraProtocollo",Array($this->login));
        $f = fopen(LOCAL_LIB.'../debug/protocollo.debug','w');
        ob_start();
        print_r($this->wsClient);
        $r = ob_get_contents();
        ob_end_clean();
        fwrite($f,$r);
        fclose($f);


        if ($res["lngErrNumber"]==0){
            $result["success"]=1;
            $result["result"] = Array("protocollo"=>$res["lngNumPG"],"data_protocollo"=>date ("d/m/Y"));
        }
        else{
            $result["success"]= -1;
            $result["message"] = sprintf("Error number %s - %s",(string)$res["lngErrNumber"],$res["lngErrString"]);
        }
        return $result;
    }
    /******************************************************************************************************************/

    /******************************************************************************************************************/
    function richiediProtIn(){

    }

    /******************************************************************************************************************/

    /******************************************************************************************************************/

    function infoProtocollo($prot,$anno){
        $result = $this->result;
        $a = $this->wsClient->call("infoProtocollo",Array($this->login,$prot,$anno));
        $xml = simplexml_load_string($a);
        //print_r($a);
        $json = json_encode($xml);
        $response = json_decode($json,TRUE);
        $result["result"] = $response;
        $result["success"] = 1;
        return $result;
    }

    /******************************************************************************************************************/

    /******************************************************************************************************************/
    function recuperaSoggetto($id,$multi,$app="pe"){
        $result = $this->result;
        $modo = $this->modalita;
        $sql=<<<EOT
WITH elenco_soggetti AS(        
SELECT id::varchar as id, coalesce(codfis,piva) as codfis, nome, cognome, coalesce(ragsoc,cognome || ' ' || nome) as denominazione, comune, prov, cap, trim(coalesce(indirizzo, '')|| '' || coalesce(civico,'')) as indirizzo, pec as mail,'$modo' as modalita_invio FROM pe.soggetti
UNION ALL
SELECT mail as id,codfis, ''::varchar as nome, ''::varchar as cognome, nome as denominazione, comune, prov, cap, trim(coalesce(indirizzo, '')|| '' || coalesce(civico,'')) as indirizzo,mail,'$modo' as modalita_invio FROM pe.e_enti
)
SELECT * FROM elenco_soggetti WHERE id = ?;
EOT;

        $stmt = $this->dbh->prepare($sql);
        $res = Array();
        if($stmt->execute(Array($id))){
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $fileXML = ($multi)?("destinatario_multi"):("destinatario");
            $xml = $this->caricaXML($fileXML,$res);
            $result["result"] = Array("data"=>$res,"xml"=>$xml["result"]);
            $result["success"] = 1;
        }
        else{
            $result["message"] = $stmt->errorInfo();
            $result["success"] = -1;
        }
        return $result;
    }

    /******************************************************************************************************************/

    /******************************************************************************************************************/

    function recuperaPratica($pr,$app){
        $result = $this->result;
        $sql = <<<EOT
SELECT 
  numero,protocollo as prot,date_part('year',data_prot) as anno_prot,coalesce(oggetto,'') as oggetto 
FROM 
  pe.avvioproc 
WHERE 
  pratica=?
EOT;

        $stmt = $this->dbh->prepare($sql);
        $res = Array();
        if($stmt->execute(Array($pr))) {
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $result["result"] = $res;
            $result["success"] = 1;
        }
        else{
            $result["message"] = $stmt->errorInfo();
            $result["success"] = -1;
        }
        return $result;
    }

    /******************************************************************************************************************/

    /******************************************************************************************************************/
    function caricaXML($nome,$data){
        $result = $this->result;
        $fName = TEMPLATE_DIR.$nome.".xml";
        if (file_exists($fName)){
            $f = fopen($fName,'r');
            $tXml = fread($f,filesize($fName));
            fclose($f);
            $xml = $this->subst($tXml,$data);
            $result["success"] = 1;
            $result["result"] = $xml;
            return Array("success"=>1,"result"=>$xml);
        }
        else{
            $result["success"] = -1;
            $result["message"] = "Il file $fName non � stato trovato";
        }
        return $result;
    }
}

class wsMail{
    var $params;
    var $dbh;
    var $wsUrl;
    var $login;
    var $service;
    var $wsClient;
    var $result;

    /******************************************************************************************************************/

    /******************************************************************************************************************/
    function __construct(){
        $this->wsUrl = WSMAIL_URL;
        $this->login = SERVICE_LOGIN;
        $this->service = "SicraWeb";
        $this->dbh = utils::getDb();
        $this->wsClient =  new nusoap_client($this->wsUrl,false,false, false, false, false, 0, 180);
        $this->wsClient->soap_defencoding = 'UTF-8';
        $this->wsClient->decode_utf8 = false;
        $this->params = Array(
            "mittente"=> Array(
                "Denominazione_Entita"=> "Comune di Rapallo",
                "Denominazione"=>"S.U.E. - EDILIZIA PRIVATA",
                "CodiceAmministrazione"=>"c_h183",
                "IndirizzoTelematico"=>"protocollo@pec.comune.rapallo.ge.it",
                "UnitaOrganizzativa"=>"ED.PR",
                "CodiceTitolario"=>"6.3",
                "CodiceAOO"=>"c_h183",
                "Indirizzo" => "Piazza Molfino 10",
                "Identificativo" => "ED.PR"
            ),
            "destinatario"=> Array()
        );

        $this->result =  $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
    }
    private function subst($txt,$data){
        foreach($data as $k=>$v){
            $txt = str_replace("%($k)s",$v,$txt);
        }
        return $txt;
    }
    function caricaXML($nome,$data){
        $result = $this->result;
        $fName = TEMPLATE_DIR.$nome.".xml";
        if (file_exists($fName)){
            $f = fopen($fName,'r');
            $tXml = fread($f,filesize($fName));
            fclose($f);
            $xml = $this->subst($tXml,$data);
            $result["success"] = 1;
            $result["result"] = $xml;
            return Array("success"=>1,"result"=>$xml);
        }
        else{
            $result["success"] = -1;
            $result["message"] = "Il file $fName non � stato trovato";
        }
        return $result;
    }
    function inviaPec($id){
        $result = $this->result;
        $mittente = $this->params["mittente"]["IndirizzoTelematico"];
        $codAOO = $this->params["mittente"]["CodiceAOO"];

        $sql =<<<EOT
WITH destinatari as(
select unnest(destinatari) as id FROM pe.comunicazioni WHERE id = ?
),
mail as (
(SELECT array_to_string(array_agg(format('<destinatarioMail>%s</destinatarioMail>',pec)),'') as destinatari from (select id::varchar,pec from pe.soggetti) A INNER JOIN destinatari USING(id) WHERE destinatari.id ~ '^[0-9]*$')
UNION ALL
(SELECT array_to_string(array_agg(format('<destinatarioMail>%s</destinatarioMail>',id)),'') as destinatari from destinatari where not  destinatari.id ~ '^[0-9]*$')
)
select pratica,protocollo,date_part('year',data_protocollo)::varchar as anno,oggetto,testo,'$mittente' as mittente,array_to_string(array_agg(mail.destinatari),'') as destinatari FROM pe.comunicazioni,mail WHERE id = ? group by 1,2,3,4,5,6
EOT;
        $stmt = $this->dbh->prepare($sql);
        if($stmt->execute(Array($id,$id))){
            $res = $stmt->fetch();
            //$res["testo"] = $res["link"]."\n".$res["testo"];
	    $res["testo"] = htmlspecialchars($res["testo"], ENT_XML1, 'UTF-8');
            $result = $this->caricaXML('mail',$res);
	    //print_r($result); die();
            if($result["success"]==1){
                $xml=$result["result"];
                $response = $this->wsClient->call("InviaMail",Array("strXML"=>$xml,"CodiceAmministrazione"=>SERVICE_LOGIN,"CodiceAOO"=>$codAOO));
                $f = fopen(LOCAL_LIB.'../debug/mail.debug','w');
                ob_start();
                print_r($xml);
                $r = ob_get_contents();
                ob_end_clean();
                fwrite($f,$r);
                fclose($f);

                if (is_array($response)){
                    $result = $response;
                }
                else{
                    $xml = simplexml_load_string($response);
                    $json = json_encode($xml);
                    $result = json_decode($json,TRUE);
                }
                $result["success"]=1;
            }

        }
        else{
            
        }
        return $result;
    }
    
    function verificaInvio($prot,$anno,$id){
        $res = $this->result;
        $result = $this->caricaXML('verificaInvio',Array("anno"=>$anno,"protocollo"=>$prot,"docId"=>$id));
        if($result["success"]==1){
            $xml=$result["result"];
            $response = $this->wsClient->call("verificaInvio",Array("strXML"=>$xml,"CodiceAmministrazione"=>SERVICE_LOGIN,"CodiceAOO"=>$codAOO));
            $f = fopen(LOCAL_LIB.'../debug/verificaInvio.debug','w');
            ob_start();
            print_r($xml);
            $r = ob_get_contents();
            ob_end_clean();
            fwrite($f,$r);
            fclose($f);

            if (is_array($response)){
                $res = $response;
            }
            else{
                $xml = simplexml_load_string($response);
                $json = json_encode($xml);
                $res = json_decode($json,TRUE);
            }
            $res["success"]=1;
        }    
        return $res;
    }

}
?>
