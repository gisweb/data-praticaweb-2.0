<?php
/**
 * Created by PhpStorm.
 * User=> mamo
 * Date=> 06/07/17
 * Time=> 11=>06
 */

define('TEMPLATE_DIR',DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR);

$paramsProtOut = Array(
    "service"=>"SicraWeb",
    "wsUrl"=> "http://93.57.10.175:50080/client/services/ProWSApi?WSDL",
    "login"=> "!gisweb/cec.gisweb@sicraweb/sicraweb",
    "mittente"=> Array(
        "Denominazione_Entita"=> "Comune di Andora",
        "Denominazione"=>"URBANISTICA",
        "CodiceAmministrazione"=>"c_l315",
        "IndirizzoTelematico"=>"comune@prova.it",
        "UnitaOrganizzativa"=>"URB",
        "CodiceTitolario"=>"1.1",
        "CodiceA00"=>"udcvem",
        "Indirizzo" => "Via Cavour 94",
        "Identificativo" => ""
    )
);





?>