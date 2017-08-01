<?php

$dir = dirname(__FILE__);
define('DATA_DIR',$dir.DIRECTORY_SEPARATOR);
define('APPS_DIR',"/apps/PraticaWeb-2.1-dev/");
require_once "config.php";



require_once "praticaweb/nusoap/nusoap.php";
require_once "praticaweb/nusoap/nusoapmime.php";

$urlSearch = "http://93.57.10.175:50080/client/services/ProtocolloSoap?WSDL";
$urlMail = "http://93.57.10.175:50080/client/services/WsPostaWebSoap?WSDL";
$login = "!suap/sicraweb@tovosangiacomo/tovosangiacomo";
$codAOO = "PL";
$prot = "67";
$anno = 2017;
$docIds = Array(11533,11534);

//$client =  new nusoap_client($urlSearch,'wsdl');
//$res = $client->call("LeggiProtocolloString",Array("AnnoProtocollo"=>$anno,"NumeroProtocollo"=>$prot,"Utente"=>"","Ruolo"=>"","CodiceAmministrazione"=>$login,"CodiceAOO"=>$codAOO,"OutputBreve"=>""));
//$res = $client->call("LeggiProtocollo",Array("AnnoProtocollo"=>$anno,"NumeroProtocollo"=>$prot,"Utente"=>"","Ruolo"=>"","CodiceAmministrazione"=>$login,"CodiceAOO"=>$codAOO));
//print_r($res);
//exit;

$client = new nusoap_client($urlMail,false,false, false, false, false, 0, 180);
//print_r($client);
$f = fopen('praticaweb/templates/mail.xml','r');
$xml = fread($f,filesize('praticaweb/templates/mail.xml'));
fclose($f);
//$xml = simplexml_load_string($xml);

//print_r($xml);die();
/*$xml = Array(
    "messaggioIn"=>Array(
        "docId" => "1",
        "annoProtocollo" => "2017",
        "numeroProtocollo" => "39",
        "oggettoMail" => "Test Invio Pec",
        "testoMail" => "Pec di prova di gisweb",
        "mittenteMail" => "comune@prova.it",
        "destinatariMail" => Array(
            "destinatarioMail" => "carbone.marco@pec.it",
            "destinatarioMail" => "amministrazione@gisweb.pec.it"
        ),
        "utente" => "",
        "Ruolo" => "",
        "invioInteroperabile" => "N"
));
*/

//echo $msg;
//$res = $client->send($msg, $urlMail);
$res = $client->call("InviaMail",Array("strXML"=>$xml,"CodiceAmministrazione"=>$login,"CodiceAOO"=>$codAOO));
$f = fopen('mail.debug','w');
ob_start();
print_r($client);
$r = ob_get_contents();
ob_end_clean();
fwrite($f,$r);
fclose($f);
print_r($res);
?>


