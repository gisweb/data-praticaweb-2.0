<?php

define("WSPROT_URL","http://93.57.10.175:50080/client/services/ProWSApi?WSDL");
define("WSMAIL_URL","http://93.57.10.175:50080/client/services/WsPostaWebSoap?WSDL");
//define('SERVICE_LOGIN','!gisweb/cec.gisweb@sicraweb/sicraweb');
define('SERVICE_LOGIN',"!gisweb/cec.gisweb@sicraweb/sicraweb");

define('DATA_DIR','/data/andora/pe/');
define('LIB',DATA_DIR.'praticaweb'.DIRECTORY_SEPARATOR.'lib/');
define('TEMPLATE_DIR',DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR);


$params = Array(
    "UO"=>Array(
        "SUE" => Array(
            "Denominazione" => "URBANISTICA",
            "Codice"=>"URB"   
            //"Codice" => "c_l315"
        ),
    ),
    "Amministrazione" =>Array(
        "Codice"=>"c_l315",
        "Denominazione" => "Comune di Andora",
        "Indirizzo" => "Via Cavour 94",
        "IndirizzoTelematico"=>"comune@prova.it",
    ),
    "AOO" => Array(
        "Codice" => "c_h183",
        "Denominazione" => "Comune di Andora",
    )
);
?>
