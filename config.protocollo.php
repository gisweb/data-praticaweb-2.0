<?php

define("WSPROT_URL","http://servizi.comune.rapallo.ge.it:50080/client/services/ProWSApi?wsdl");
define("WSMAIL_URL","http://servizi.comune.rapallo.ge.it:50080/client/services/WsPostaWebSoap?WSDL");
//define('SERVICE_LOGIN','!gisweb/cec.gisweb@sicraweb/sicraweb');
define('SERVICE_LOGIN',"!wsgisweb/rap.1885.gis@sicraweb/sicraweb");

define('DATA_DIR','/data/rapallo/pe/');
define('LIB',DATA_DIR.'praticaweb'.DIRECTORY_SEPARATOR.'lib/');
define('TEMPLATE_DIR',DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR);


$params = Array(
    "UO"=>Array(
        "SUE" => Array(
            "Denominazione" => "S.U.E. - EDILIZIA PRIVATA",
            "Codice"=>"ED.PR",   
            "Codice" => "c_h183"
        ),
    ),
    "Amministrazione" =>Array(
        "Codice"=>"c_h183",
        "Denominazione" => "Comune di Rapallo",
        "Indirizzo" => "Via Cavour 94",
        "IndirizzoTelematico"=>"comune@prova.it",
    ),
    "AOO" => Array(
        "Codice" => "c_h183",
        "Denominazione" => "Comune di Rapallo",
    )
);
?>