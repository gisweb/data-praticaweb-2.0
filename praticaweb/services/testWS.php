<?php
session_start();
$appsDir=  getenv('PWAppsDir');
$dataDir=  getenv('PWDataDir');
require_once $appsDir.DIRECTORY_SEPARATOR."login.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
require_once $dataDir.DIRECTORY_SEPARATOR."protocollo.config.php";

$client = new soapclient($params["wsUrl"]);

$err = $client->getError();
if ($err) {
    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}

?>
