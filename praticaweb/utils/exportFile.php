<?php

function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

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
$anni=Array(2019,2018,2017,2016,2015,2014,2013);
$dbh = utils::getDb();
$sql = "SELECT * FROM pe.avvioproc WHERE coalesce(cod_belfiore,'A278') IN ('C578','C657','I947','L152') and anno = ?;";
$sqlAll = "SELECT id,nome_file,prot_allegato,data_prot_allegato FROM pe.file_allegati WHERE pratica = ?;";
$sqlDoc = "SELECT * FROM stp.documenti WHERE pratica = ?;";
$stmtAll = $dbh->prepare($sqlAll);
$stmtDoc = $dbh->prepare($sqlDoc);
$stmt = $dbh->prepare($sql);
$h=0;
$k=0;

$allegatiTmp = DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."documenti".DIRECTORY_SEPARATOR."pe".DIRECTORY_SEPARATOR."allegati".DIRECTORY_SEPARATOR;

foreach($anni as $anno){
    if($stmt->execute(Array($anno))){
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //Ciclo sulle pratiche dell'anno
        mkdir(DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."export".DIRECTORY_SEPARATOR."C578".DIRECTORY_SEPARATOR.$anno);
        mkdir(DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."export".DIRECTORY_SEPARATOR."C657".DIRECTORY_SEPARATOR.$anno);
        mkdir(DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."export".DIRECTORY_SEPARATOR."I947".DIRECTORY_SEPARATOR.$anno);
        mkdir(DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."export".DIRECTORY_SEPARATOR."L152".DIRECTORY_SEPARATOR.$anno);
        for($i=0;$i<count($res);$i++){
            $datiPr = $res[$i];
            $comune = $datiPr["cod_belfiore"];
            $numero = $datiPr["numero"];
            $message = sprintf("Considero la Pratica %s del comune %s\n",$numero,$comune);
            print $message;
            $pr = $datiPr["pratica"];
            
            $pratica = new pratica($pr);
            $allegatiDir = $pratica->allegati;
            $stampeDir = $pratica->documenti;
            $prDir = DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."export".DIRECTORY_SEPARATOR.$comune.DIRECTORY_SEPARATOR.$anno.DIRECTORY_SEPARATOR.$numero;
//            print "$stampeDir\n";
            mkdir($prDir);
            recurse_copy($stampeDir, $prDir);
            //Ciclo sugli allegati della pratica
            /*if($stmtAll->execute(Array($pr))){
                $error = 0;
                $rr = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
                for($j=0;$j<count($rr);$j++){
                    $r = $rr[$j];
                    $fileName = sprintf("%s%s",$allegatiDir,$r["nome_file"]);
                    if (!file_exists($fileName)){
                        $tmpFileName=sprintf("%s%s",$allegatiTmp,$r["nome_file"]);
                        if (file_exists($tmpFileName)){
                            if(!copy($tmpFileName,$fileName)){
                                $k++;
                                $message = sprintf("\t%d) Impossibile copiare il file %s in %s\n",$k,$r["nome_file"],$allegatiDir);
                                #print $message;
                                $error = 1;
                            }
                        }
                        else{
                            $k++;
                            $message = sprintf("\t%d) Il file %s non esiste\n",$k,$fileName);
                            print $message;
                            $error = 1;
                        }
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
             
            */
        }
    }
}
//$message = sprintf("Trovati %d File di cui %d mancanti\n",($k+$h),$k);
//print $message;
        
?>
