<?php
session_start();
$appsDir=  getenv('PWAppsDir');
$dataDir=  getenv('PWDataDir');
require_once $appsDir.DIRECTORY_SEPARATOR."login.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
require_once $dataDir.DIRECTORY_SEPARATOR."protocollo.config.php";

$client = new nusoap_client($paramsProt["wsUrl"],'wsdl');

$prot = "11471";
$anno = "2017";

$err = $client->getError();
if ($err) {
    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}
$r = $client->call("infoProtocollo",Array($paramsProt["login"],$prot,$anno));
$xml = simplexml_load_string($r);
$json = json_encode($xml);
$array = json_decode($json,TRUE);
echo "<pre>";print_r($array);echo "</pre>";
?>
