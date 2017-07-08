<?php
/**
 * Created by PhpStorm.
 * User: mamo
 * Date: 06/07/17
 * Time: 17:55
 */

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

    private static function inserisciDocumento($id){
        $result = Array(
            "success" => 0,
            "message" => "",
            "id" => ""
        );
        $res = appUtils::getInfoDocumento($id);
        if ($res["success"]==1){
            $client = new nusoap_client_mime($paramsProt["wsUrl"],false);
            $err = $client->getError();
            if ($err) {
                $result["success"] = -1;
                $result["message"] = $err;
                return $result;
            }
            $client->addAttachment($res["file"],$res["data"]["nomefile"],$res["mimetype"]);
            $a = $client->call("insertDocumento",Array($paramsProt["login"],$res["nomefile"],$res["descrizione"]));
        }
    }

}
?>