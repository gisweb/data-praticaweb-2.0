<?php
//require_once "praticaweb/nusoap/nusoap.php";
//require_once "praticaweb/nusoap/nusoapmime.php";
$url = "http://93.57.10.175:50080/client/services/ProWSApi?WSDL";
$login = "!suap/sicraweb@tovosangiacomo/tovosangiacomo";

$prot = "1";
$anno = 2018;
error_reporting(E_ERROR);
$dir = dirname(__FILE__);
define('DATA_DIR',$dir.DIRECTORY_SEPARATOR);
define('APPS_DIR',"/apps/PraticaWeb-2.1-dev/");
require_once "config.php";
require_once "praticaweb/lib/wsProtocollo.class.php";
$wsProt = new wsProtocollo('U','PEC');

$res = $wsProt->infoProtocollo($prot,$anno);
print_r($res);die();
?>
