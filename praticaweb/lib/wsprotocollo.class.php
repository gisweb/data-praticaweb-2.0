<?php
if (!defined('DATA_DIR')) define("DATA_DIR",dirname(dirname(dirname(__FILE__))));
require_once DATA_DIR.DIRECTORY_SEPARATOR."config.protocollo.php";

define('TEMPLATE_DIR',DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR);

require_once LOCAL_LIB."app.utils.class.php";
require_once LIB."utils.class.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoapmime.php";

class protocollo{
    
    
    function login(){
        $cl = new nusoap_client_mime(SERVICE_URL,'wsdl');
        $res = $cl->call('Login',Array("strCodEnte"=>CODICE_AMMINISTRAZIONE,"strUserName"=>SERVICE_USER,"strPassword"=>SERVICE_PASSWD));
        $res = json_decode(json_encode($res),TRUE);
        
        if(array_key_exists("LoginResult",$res)){
            if(array_key_exists("lngErrNumber", $res["LoginResult"]) && !$res["LoginResult"]["lngErrNumber"]){
                return Array("success"=>1,"dst"=>$res["LoginResult"]["strDST"]);
            }
            else{
                return Array("success"=>0,"message"=>$res["LoginResult"]["strErrString"]);
            }
        }
        else{
            return Array("success"=>-1);
        }
        unset($cl);
    }
    
    function cercaFascicolo($prot,$anno){
        $res = $this->login();
        if ($res["success"]===1){
            $dst = $res["dst"];
            $cl = new SoapClient(DIZIONARI_URL,array("trace" => 1, "exception" => 0));
            $result = $cl->searchFascicoli(Array("strUserName"=>SERVICE_USER,"strDST"=>$dst,"codiceAOO"=>Codice_A00,"numeroProtocollo"=>$prot,"annoProtocollo"=>$anno));
            return $result;
        }
    }

    function listaFascicoli(){
        $res = $this->login();
        if ($res["success"]===1){
            $dst = $res["dst"];
            $cl = new SoapClient(DIZIONARI_URL,array("trace" => 1, "exception" => 0));
            $result = $cl->listaFascicoli(Array("strUserName"=>SERVICE_USER,"strDST"=>$dst));
            return $result;
        }
    }

}
?>
