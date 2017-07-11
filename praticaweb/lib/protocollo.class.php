<?php
/**
 * Created by PhpStorm.
 * User: mamo
 * Date: 06/07/17
 * Time: 17:55
 */
require_once LIB."utility.class.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoapmime.php";
require_once DATA_DIR."protocollo.config.php";
class protocollo{
    static function richiediProtOut($pratica,$params=Array()){
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

    static function infoProtocollo(){

    }

    static function recuperaSoggetto($id,$app="pe"){
        $sql=<<<EOT
WITH elenco soggetti AS(        
SELECT id::varchar as id, coalesce(codfis,piva) as codfis, nome, cognome, coalesce(ragsoc,cognome || ' ' || nome) as denominazione, comune, cap, trim(coalesce(indirizzo, '')|| '' || coalesce(civico,'')) as indirizzo, pec as mail FROM pe.soggetti
UNION ALL
SELECT mail as id,codfis, ''::varchar as nome, ''::varchar as cognome, nome as denominazione, comune, cap, trim(coalesce(indirizzo, '')|| '' || coalesce(civico,'')) as indirizzo,mail FROM pe.e_enti
)
SELECT * FROM elenco_soggetti WHERE id = ?;
EOT;
        $dbh = utils::getDb();
        $stmt = $dbh->prepare($sql);
        $res = Array();
        if($stmt->execute(Array($id))){
            $res = $stmt->fetch();
        }
        return $res;
    }

    private static function caricaXML($nome,$data){
        $fName = TEMPLATE_DIR.$nome.".xml";
        if (file_exists($fName)){
            $f = fopen($fName,'r');
            $tXml = fread($f,filesize($fName));
            fclose($f);
            $xml = utility::dsprintf($tXml,$data);
            return Array("success"=>1,"result"=>$xml);
        }
        return Array("success"=>-1,"result"=>"");
    }

    private static function inserisciDocumento($id){
        $result = Array(
            "success" => 0,
            "message" => "",
            "id" => ""
        );
        $res = appUtils::getInfoDocumento($id);
        if ($res["success"]==1){
            $client = new nusoap_client_mime($paramsProt["wsUrl"],'wsdl');
            $err = $client->getError();
            if ($err) {
                $result["success"] = -1;
                $result["message"] = $err;
                return $result;
            }
            $client->addAttachment($res["file"],$res["data"]["nomefile"],$res["mimetype"]);
            $a = $client->call("insertDocumento",Array($paramsProt["login"],$res["nomefile"],$res["descrizione"]));
            $xml = simplexml_load_string($a);
            $json = json_encode($xml);
            $response = json_decode($json,TRUE);
            return $response;
        }
    }

}
?>