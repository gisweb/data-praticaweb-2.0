<?php
if (!defined(DATA_DIR)) define("DATA_DIR",dirname(dirname(dirname(__FILE__))));
require_once DATA_DIR."config.protocollo.php";

class protocollo{
    
    
    function login(){
        $cl = new SoapClient(SERVICE_URL,array("trace" => 1, "exception" => 0));
        $res = $cl->Login(Array("strCodEnte"=>CODICE_AMMINISTRAZIONE,"strUserName"=>SERVICE_USER,"strPassword"=>SERVICE_PASSWD));
    }
}
?>