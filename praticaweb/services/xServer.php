<?php
session_start();
$appsDir=  getenv('PWAppsDir');
$dataDir=  getenv('PWDataDir');
require_once $appsDir.DIRECTORY_SEPARATOR."login.php";
error_reporting(E_ERROR);
$db=  appUtils::getDB();
$dbh = utils::getDb();
$result=Array(
    "success"=> 0,
    "messages"=> Array(),
    "data" =>Array()
);
$action=(isset($_REQUEST["action"]) && $_REQUEST["action"])?($_REQUEST["action"]):("");

switch($action){
    case "protocolla":
        break;
}
header('Content-Type: application/json; charset=utf-8');
print json_encode($result);
return;
?>
