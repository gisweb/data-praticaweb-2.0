<?php

if (!defined('APPS_DIR')) define('APPS_DIR','/apps/praticaweb-2.1/');
if (!defined('DATA_DIR')) define('APPS_DIR','/data/andora/pe/');
require_once DATA_DIR.'config.php';
require_once LIB.'utils.class.php';
require_once LOCAL_LIB."pratcia.class.php";
$anni = Array(2019,2018,2017,2016,2015,2014);
$dbh = utils::getDb();
$sql = "SELECT * FROM pe.avvioproc WHERE anno = ?;";
$sqlAll = "SELECT id,nome_file,prot_allegato,data_prot_allegato FROM pe.file_allegati WHERE pratica = ?;";
$sqlDoc = "SELECT * FROM stp.documenti WHERE pratica = ?;";
$stmtAll = $dbh->prepare($sqlAll);
$stmtDoc = $dbh->prepare($sqlDoc);
$stmt = $dbh->prepare($sql);
foreach($anni as $anno){
    if($stmt->execute(Array($anno))){
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $k =0;
        //Ciclo sulle pratiche dell'anno
        for($i=0;$i<count($res);$i++){
            $datiPr = $res[$i];
            $pr = $datiPr["pratica"];
            $pratica = new pratica($pr);
            $allegatiDir = $pratica->allegati;
            //Ciclo sugli allegati della pratica
            if($stmtAll->execute(Array($pr))){
                $rr = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
                for($j=0;$j<count($rr);$j++){
                    $r = $rr[$j];
                    $fileName = sprintf("%s%s",$allegatiDir,$r["nome_file"]);
                    if (!file_exists($fileName)){
                        $k++;
                        $message = sprintf("%d) Il file %s non esiste\n",$k,$fileName);
                        print $message;
                    }
                    else{
                        $a = 1;
                    }
                }
            }
            //Ciclo sui documenti generati/caricati della pratica
            //TODO
            unset($pratica);
        }
    }
}

        
?>