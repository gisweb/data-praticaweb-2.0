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

die("test");
$xml =<<<EOT
<![CDATA[
<messaggioIn>
<annoProt>2019</annoProt>
<numProt>23432</numProt>
<docId></docId>
<utente></utente>
<ruolo></ruolo>
</messaggioIn>
]]>
EOT;

$client = new SoapClient(WSMAIL_URL);
//var_dump($client->__getFunctions());die();
$data = Array("strXML"=>$xml,"CodiceAmministrazione"=>SERVICE_LOGIN,"CodiceAOO"=>"c_h183");
print_r($data);
$res = $client->__call('VerificaInvio',$data);
print_r($res);

?>
