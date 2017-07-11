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

$sogg = protocollo::recuperaSoggetto("27675");
echo "<pre>";print_r($sogg);echo "</pre>";
?>