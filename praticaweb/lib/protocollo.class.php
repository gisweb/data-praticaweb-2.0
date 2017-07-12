<?php
/**
 * Created by PhpStorm.
 * User: mamo
 * Date: 06/07/17
 * Time: 17:55
 */


$paramsProtOut=Array();

require_once LIB."utils.class.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoapmime.php";
require_once DATA_DIR."protocollo.config.php";

class protocollo{
    static function subst($txt,$data){
        foreach($data as $k=>$v){
            $txt = str_replace("%($k)s",$v,$txt);
        }
        return $txt;
    }
    static function richiediProtOut($pratica,$params=Array()){
        $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
        $documentiOk = 1;
        if (!$params["destinatari"]) return -2;
        if ($params["allegati"]){
            for($i=0;$i<count($params["allegati"]);$i++){
                $idDoc = $params["allegati"][$i];
                $res = self::inserisciDocumento($idDoc);
                $documentiOk = $documentiOk && $res["success"];
                if ($res["success"]==1){

                }
                else{

                }

            }
        }

    }

    static function richiediProtIn(){

    }

    static function infoProtocollo($prot,$anno){
        $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
        $client = new nusoap_client($paramsProtOut["wsUrl"],'wsdl');
        $a = $client->call("infoProtocollo",Array($paramsProtOut["login"],$prot,$anno));
        $xml = simplexml_load_string($a);
        $json = json_encode($xml);
        $response = json_decode($json,TRUE);
        $result["result"] = $response;
        $result["success"] = 1;
        return $result;
    }

    static function recuperaSoggetto($id,$app="pe"){
        $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
        $sql=<<<EOT
WITH elenco_soggetti AS(        
SELECT id::varchar as id, coalesce(codfis,piva) as codfis, nome, cognome, coalesce(ragsoc,cognome || ' ' || nome) as denominazione, comune, prov, cap, trim(coalesce(indirizzo, '')|| '' || coalesce(civico,'')) as indirizzo, pec as mail FROM pe.soggetti
UNION ALL
SELECT mail as id,codfis, ''::varchar as nome, ''::varchar as cognome, nome as denominazione, comune, prov, cap, trim(coalesce(indirizzo, '')|| '' || coalesce(civico,'')) as indirizzo,mail FROM pe.e_enti
)
SELECT * FROM elenco_soggetti WHERE id = ?;
EOT;
        $dbh = utils::getDb();
        $stmt = $dbh->prepare($sql);
        $res = Array();
        if($stmt->execute(Array($id))){
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

    static function caricaXML($nome,$data){
        $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
        $fName = TEMPLATE_DIR.$nome.".xml";
        if (file_exists($fName)){
            $f = fopen($fName,'r');
            $tXml = fread($f,filesize($fName));
            fclose($f);
            $xml = self::subst($tXml,$data);
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

    private static function inserisciDocumento($id){
        $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
        $res = appUtils::getInfoDocumento($id);
        if ($res["success"]==1){
            $client = new nusoap_client_mime($paramsProtOut["wsUrl"],'wsdl');
            $err = $client->getError();
            if ($err) {
                $result["success"] = -1;
                $result["message"] = $err;

            }
            else{
                $client->addAttachment($res["file"],$res["data"]["nomefile"],$res["mimetype"]);
                $a = $client->call("insertDocumento",Array($paramsProtOut["login"],$res["nomefile"],$res["descrizione"]));
                $xml = simplexml_load_string($a);
                $json = json_encode($xml);
                $response = json_decode($json,TRUE);
                $result["result"] = $response;
                $result["success"] = 1;
            }
        }
        else{
            $result = $res;
        }
        return $result;
    }

}
?>