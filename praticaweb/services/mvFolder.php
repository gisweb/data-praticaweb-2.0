<?php
/**
 * Created by PhpStorm.
 * User: mamo
 * Date: 07/07/17
 * Time: 08:53
 */

session_start();
$_SESSION["USER_ID"]=1;
$appsDir=  getenv('PWAppsDir');
$dataDir=  getenv('PWDataDir');
require_once $appsDir.DIRECTORY_SEPARATOR."login.php";
$anno = ($_REQUEST["anno"])?($_REQUEST["anno"]):('2017');
$sql= "SELECT pratica,numero,anno FROM pe.avvioproc WHERE anno=?;";
$dbh = utils::getDb();

$stmt = $dbh->prepare($sql);
if($stmt->execute(Array($anno))){
    $res = $stmt->fetchAll();
    echo "<p>Trovate ".count($res)." pratiche per anno $anno</p>";
    $foundFolder = 0;
    $movedFolder = 0;
    foreach($res as $r) {
        $anno = $r["anno"];
        $numero = appUtils::normalizeNumero($r['numero']);
        $tmp = explode('-', $numero);
        if (count($tmp) == 2 && preg_match("|([A-z0-9]+)|", $tmp[0])) {
            $tmp[0] = (preg_match("|^[89]|", $tmp[0])) ? ("19" . $tmp[0]) : ($tmp[0]);
            $numero = implode('-', $tmp);
        }
        $oldFolder = DOCUMENTI . DIRECTORY_SEPARATOR . "pe" . DIRECTORY_SEPARATOR . $anno . DIRECTORY_SEPARATOR . $numero;
        $newFolder = DOCUMENTI . DIRECTORY_SEPARATOR . "pe" . DIRECTORY_SEPARATOR . $anno . DIRECTORY_SEPARATOR . $r["pratica"];
        if (is_dir($oldFolder)){
            $foundFolder++;
            if (rename($oldFolder,$newFolder)){
                $movedFolder++;
            }
            else {
                echo "<p>Impossibile rinominare $oldFolder in $newFolder</p>";
            }
        }
        else{
            echo "<p>Directory $oldFolder non trovata</p>";
        }
    }
    echo "<p>Trovate ".$foundFolder." cartelle per anno $anno<br> Rinominate $movedFolder cartelle</p>";
}
?>