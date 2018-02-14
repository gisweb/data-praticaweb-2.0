<?php
//require_once "praticaweb/nusoap/nusoap.php";
//require_once "praticaweb/nusoap/nusoapmime.php";
$url = "http://93.57.10.175:50080/client/services/ProWSApi?WSDL";
$login = "!suap/sicraweb@tovosangiacomo/tovosangiacomo";

$pratica = 39390;
$app = "pe";
$idComunicazione = 1;


error_reporting(E_ERROR);
$dir = dirname(__FILE__);
define('DATA_DIR',$dir.DIRECTORY_SEPARATOR);
define('APPS_DIR',"/apps/PraticaWeb-2.1-dev/");
require_once "config.php";
require_once "praticaweb/lib/wsProtocollo.class.php";


$wsProt = new wsProtocollo('U','PEC');
$r = $wsProt->richiediProtOut($pratica,$app,$idComunicazione);
print_r($r);
?>
