<?php
/**
 * Created by PhpStorm.
 * User=> mamo
 * Date=> 06/07/17
 * Time=> 11=>06
 */
$params = Array(
    "service"=>"SicraWeb",
    "wsUrl"=> "http://93.57.10.175:50080/client/services/ProWSApi?WSDL",
    "login"=> "!gisweb/cec.gisweb@sicraweb/sicraweb",
    "destinatario"=> Array(
        "Denominazione"=>"URBANISTICA",
        "CodiceAmministrazione"=>"c_l315",
        "IndirizzoTelematico"=>"comune@prova.it",
        "UnitaOrganizzativa"=>"URB",
        "CodiceTitolario"=>"1.1",
        "CodiceA00"=>"udcvem"
    ),
    "mittente"=> Array(
        "Nome"=>"fisica_nome",
        "Cognome"=>"fisica_cognome",
        "CodiceFiscale"=>"fisica_cf",
        "IndirizzoTelematico"=>"fisica_email",
        "Indirizzo"=>"fisica_indirizzo",
        "CAP"=>"fisica_cap",
        "Comune"=>"fisica_comune",
        "Prov"=>"fisica_provincia"
    )
);





?>