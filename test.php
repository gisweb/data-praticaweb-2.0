<?php
session_start();
$_SESSION["USER_ID"]=1;
define('APPS_DIR','/apps/praticaweb-2.1/');
define('DATA_DIR','/data/rapallo/pe/');

require_once DATA_DIR."config.php";
require_once DATA_DIR."config.protocollo.php";
require_once LIB.'utils.class.php';
require_once LOCAL_LIB."wsProtocollo.class.php";
//require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
//require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoapmime.php";


$xml =<<<EOT
<![CDATA[
    <messaggioIn>
        <annoProt>2019</annoProt>
        <numProt>29548</numProt>
        <docId>326338</docId>
        <utente></utente>
        <ruolo></ruolo>
    </messaggioIn>
]]>
EOT;

$ws = new wsMail();
$r = $ws->verificaInvio("29597","2019",326404);
//var_dump($client->__getFunctions());die();
//$data = Array("strXML"=>$xml,"CodiceAmministrazione"=>SERVICE_LOGIN,"CodiceAOO"=>"c_h183");

//$res = $ws->wsClient->call('VerificaInvio',$data);
print_r($r);

?>
