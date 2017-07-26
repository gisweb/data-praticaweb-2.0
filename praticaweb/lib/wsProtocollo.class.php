<?php
/**
 * Created by PhpStorm.
 * User: mamo
 * Date: 06/07/17
 * Time: 17:55
 */

define("WSPROT_URL","http://93.57.10.175:50080/client/services/ProWSApi?WSDL");
define("WSMAIL_URL","http://93.57.10.175:50080/client/services/WSPostaWebSoap?WSDL");
define('SERVICE_LOGIN',"!suap/sicraweb@tovosangiacomo/tovosangiacomo");
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
                "Denominazione_Entita"=> "Comune di Andora",
                "Denominazione"=>"URBANISTICA",
                "CodiceAmministrazione"=>"c_l315",
                "IndirizzoTelematico"=>"comune@prova.it",
                "UnitaOrganizzativa"=>"T",
                "CodiceTitolario"=>"1.1",
                "CodiceA00"=>"PL",
                "Indirizzo" => "Via Cavour 94",
                "Identificativo" => "T"
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
    function inserisciDocumento($id){
        $result = $this->result;
        $res = appUtils::getInfoDocumento($id);
        if ($res["success"]==1){
 
            $err = $this->wsClient->getError();
            if ($err) {
                $result["success"] = -1;
                $result["message"] = $err;

            }
            else{
                $this->wsClient->addAttachment($res["file"],$res["data"]["nomefile"],$res["mimetype"]);
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
    /******************************************************************************************************************/

    /******************************************************************************************************************/

    function richiediProtOut($pratica,$params=Array()){
        $result = $this->result;
        $dataSubst = $this->params["mittente"];
        $paramsKeys = array_keys($params);
        if (!(in_array("destinatari",$paramsKeys) && $params["destinatari"])) return -2;
        $app = ((in_array('app',$paramsKeys) && $params["app"]))?($params["app"]):("pe");
        $d = $this->recuperaPratica($pratica,$app);
        foreach($d["result"] as $k=>$v){
            $dataSubst[$k] = $v;
        }
        $dataSubst["data_registrazione"] = date ("d/m/Y");

        $documentiOk = 1;
        $multiDest =  (count($params["destinatari"]) > 1)?(1):(0);
        if (in_array("allegati",$paramsKeys) && $params["allegati"]){
            for($i=0;$i<count($params["allegati"]);$i++){
                $idDoc = $params["allegati"][$i];
                $res = $this->inserisciDocumento($idDoc);
                $documentiOk = $documentiOk && $res["success"];
                if ($res["success"]==1){
                    $xmlAll[] = $res["result"]["xml"];
                }
                else{

                }
                $this->wsClient->clearAttachments();
            }
            $dataSubst["allegati"] = implode("\n",$xmlAll);
        }

        for($i=0;$i<count($params["destinatari"]);$i++){
            $idDest = $params["destinatari"][$i];
            echo "Recupero Soggetto $idDest \n";
            $res = $this->recuperaSoggetto($idDest,$app,$multiDest);
            print_r($res);
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
        $r = $this->caricaXML("prot_out",$dataSubst);
        $fileXML = $r["result"];
        $this->wsClient->clearAttachments();

        echo "\n$fileXML\n";
        $this->wsClient->addAttachment($fileXML,"richiesta_protocollo_out.xml","text/xml");
        $res =$this->wsClient->call("registraProtocollo",Array($this->login));
        return $res;

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
            $result["message"] = "Il file $fName non è stato trovato";
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
        $this->wsClient =  new nusoap_client_mime($this->wsUrl,'wsdl');

        $this->params = Array(
            "mittente"=> Array(
                "Denominazione_Entita"=> "Comune di Andora",
                "Denominazione"=>"URBANISTICA",
                "CodiceAmministrazione"=>"c_l315",
                "IndirizzoTelematico"=>"comune@prova.it",
                "UnitaOrganizzativa"=>"T",
                "CodiceTitolario"=>"1.1",
                "CodiceA00"=>"PL",
                "Indirizzo" => "Via Cavour 94",
                "Identificativo" => "T"
            ),
            "destinatario"=> Array()
        );

        $this->result =  $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );


    }

}
?>
