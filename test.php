<?php
/**
 * Created by PhpStorm.
 * User: mamo
 * Date: 11/07/17
 * Time: 09:17
 */
error_reporting(E_ERROR);
$dir = dirname(__FILE__);
define('DATA_DIR',$dir.DIRECTORY_SEPARATOR);
define('APPS_DIR',"/apps/PraticaWeb-2.1-dev/");
require_once "config.php";
require_once "protocollo.config.php";
require_once LOCAL_LIB."protocollo.class.php";
$s = Array("27675","27674","autostrade@pec.it");
for($i=0;$i<count($s);$i++){
    $id = $s[$i];
    $r = protocollo::recuperaSoggetto($id);
    if ($r["success"]==1){
        $sogg = $r["result"];
        $sogg["modalita_invio"] = "PEC";
        $res = protocollo::caricaXML("destinatario_multi",$sogg);
        $rr[] = $res["result"];
        $soggetti[] = $sogg["denominazione"];
    }
}
$data=Array("destinatari"=>implode(", ",$soggetti),"destinatari_multi"=>implode("\n",$rr));
$r = protocollo::caricaXML("destinatari",$data);

//echo "<pre>";
print_r($r["result"]);
//echo "</pre>";
?>