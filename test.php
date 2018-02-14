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



require_once "praticaweb/nusoap/nusoap.php";
require_once "praticaweb/nusoap/nusoapmime.php";
$url = "http://93.57.10.175:50080/client/services/ProWSApi?WSDL";
$login = "!suap/sicraweb@tovosangiacomo/tovosangiacomo";

$prot = "2";
$anno = 2017;
$fpath = "praticaweb/documenti/pe/2017/103-2017-A/allegati/doc.boscolo-pdf-a.pdf";
$f = fopen($fpath,'r');
$txt = fread($f,filesize($fpath));
fclose($f);

$client = new nusoap_client_mime($url,'wsdl');
$cid = $client->addAttachment($txt,'Test.pdf','application/pdf');
$res = $client->call('insertDocumento',Array($login,'Test.pdf','Documento di Prova'));
print_r($res);
$res = $client->call("infoProtocollo",Array($login,$prot,$anno));
print_r($res);




$r = protocollo::infoProtocollo("3","2016");
print_r($r["result"]);
exit;
$docs=Array("1393","1392");
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