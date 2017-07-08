<?php
session_start();
$appsDir=  getenv('PWAppsDir');
$dataDir=  getenv('PWDataDir');
require_once $appsDir.DIRECTORY_SEPARATOR."login.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoapmime.php";
require_once DATA_DIR."protocollo.config.php";

/*$client = new nusoap_client($paramsProt["wsUrl"],'wsdl');

$prot = "11471";
$anno = "2017";

$err = $client->getError();
if ($err) {
    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}
$response = $client->call("infoProtocollo",Array($paramsProt["login"],$prot,$anno));
*/
$id = 1394;
$result = Array(
    "success" => 0,
    "message" => "",
    "id" => ""
);
$res = appUtils::getInfoDocumento($id);
if ($res["success"]==1){
    $client = new nusoap_client_mime($paramsProt["wsUrl"],'wsdl');
    $err = $client->getError();
    if ($err) {
        $result["success"] = -1;
        $result["message"] = $err;
        return $result;
    }
    $client->addAttachment($res["file"],$res["data"]["nomefile"],$res["mimetype"]);
    $a = $client->call("insertDocumento",Array($paramsProt["login"],$res["nomefile"],$res["descrizione"]));
    $xml = simplexml_load_string($a);
    $json = json_encode($xml);
    $response = json_decode($json,TRUE);

}

$xml = simplexml_load_string($response);
$json = json_encode($xml);
$array = json_decode($json,TRUE);
echo "<pre>";print_r($array);echo "</pre>";
?>
