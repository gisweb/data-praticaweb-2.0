<?php

$xmlMail = <<<EOT
<messaggioIn>
    <docId></docId>
    <annoProt>2019</annoProt>
    <numProt>16235</numProt>
    <mittenteMail>protocollo@pec.comune.rapallo.ge.it</mittenteMail>
    <oggettoMail>Prova Invio Pec da WSPOSTA_WEB</oggettoMail>
    <testoMail>Prova GisWeb Invio mail</testoMail>
    <destinatariMail>
        <destinatarioMail>carbone.marco@pec.it</destinatarioMail>
        <destinatarioMail>amministrazione@pec.gisweb.it</destinatarioMail>
    </destinatariMail>
    <utente></utente>
    <ruolo></ruolo>
    <invioInteroperabile>S</invioInteroperabile>
</messaggioIn>
EOT;


define('DATA_DIR','/data/rapallo/pe/');
define('APPS_DIR','/apps/praticaweb-2.1/');

require_once "../../config.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoapmime.php";
/*$ws = new nusoap_client_mime(WSPROT_URL,'wsdl');
$login = SERVICE_LOGIN;
$prot = "16235";
$anno = "2019";
$a = $ws->call("infoProtocollo",Array($login,$prot,$anno));
print $a;*/
/*
$wsClient =  new nusoap_client(WSMAIL_URL,false,false, false, false, false, 0, 180);
$wsClient->soap_defencoding = 'UTF-8';
$wsClient->decode_utf8 = false;
$codAOO = "c_h183";
$response = $wsClient->call("InviaMail",Array("strXML"=>$xmlMail,"CodiceAmministrazione"=>SERVICE_LOGIN,"CodiceAOO"=>$codAOO));

 **/
 ?>
 