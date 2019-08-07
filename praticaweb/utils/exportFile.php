<?php
session_start();
$_SESSION["USER_ID"] = 1;
error_reporting(E_ERROR);
if (!defined('APPS_DIR')) define('APPS_DIR','/apps/praticaweb-2.1/');
if (!defined('DATA_DIR')) define('DATA_DIR','/data/andora/pe/');
require_once DATA_DIR.'config.php';
require_once LIB.'utils.class.php';
require_once LOCAL_LIB."pratica.class.php";
require_once LOCAL_LIB."app.utils.class.php";
$anni = Array(2019,2018,2017,2016,2015,2014);
$anni=Array(2019,2018,2017,2016,2015);
$dbh = utils::getDb();
$sql = "SELECT * FROM pe.avvioproc WHERE anno = ?;";
$sqlAll = "SELECT id,nome_file,prot_allegato,data_prot_allegato FROM pe.file_allegati WHERE pratica = ?;";
$sqlDoc = "SELECT * FROM stp.documenti WHERE pratica = ?;";
$stmtAll = $dbh->prepare($sqlAll);
$stmtDoc = $dbh->prepare($sqlDoc);
$stmt = $dbh->prepare($sql);
$h=0;
$k=0;
foreach($anni as $anno){
    if($stmt->execute(Array($anno))){
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //Ciclo sulle pratiche dell'anno
        for($i=0;$i<count($res);$i++){
            $datiPr = $res[$i];
            $message = sprintf("Considero la Pratica %s\n",$datiPr["numero"]);
//            print $message;
            $pr = $datiPr["pratica"];
            $pratica = new pratica($pr);
            $allegatiDir = $pratica->allegati;
            //Ciclo sugli allegati della pratica
            if($stmtAll->execute(Array($pr))){
                $error = 0;
                $rr = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
                for($j=0;$j<count($rr);$j++){
                    $r = $rr[$j];
                    $fileName = sprintf("%s%s",$allegatiDir,$r["nome_file"]);
                    if (!file_exists($fileName)){
                        $k++;
                        $message = sprintf("\t%d) Il file %s non esiste\n",$k,$fileName);
                        print $message;
                        $error = 1;
                    }
                    else{
                        $h++;
                        $message = sprintf("\t%d) Trovato Il file %s non esiste\n",$h,$fileName);
//                        print $message;
                    }
                }
                if ($error==0 && count($rr)){
                    $message = sprintf("Pratica %s OK\n",$datiPr["numero"]);
//                    print $message;
                }
                else if ($error == 0 && !count($rr)){
                    $message = sprintf("Nessun allegato per la pratica %s\n",$datiPr["numero"]);
//                    print $message;
                }
            }
            //Ciclo sui documenti generati/caricati della pratica
            //TODO
            unset($pratica);
        }
    }
}
$message = sprintf("Trovati %d File di cui %d mancanti\n",($k+$h),$k);
print $message;
        
?>
