<?php
/**
 * Created by PhpStorm.
 * User: mamo
 * Date: 11/07/17
 * Time: 09:17
 */

$dir = dirname(__FILE__);
define('DATA_DIR',$dir.DIRECTORY_SEPARATOR);
define('APPS_DIR',"/apps/PraticaWeb-2.1-dev/");
require_once "config.php";
require_once "protocollo.config.php";
require_once LOCAL_LIB."protocollo.class.php";
echo "A";
$r = protocollo::recuperaSoggetto("27675");
if ($r["success"]==1){
    $sogg = $r["result"];
    $sogg["modalita_invio"] = "PEC";
    $r = protocollo::caricaXML("destinatario",$sogg);
    echo "<pre>";print_r($r);echo "</pre>";
}

?>