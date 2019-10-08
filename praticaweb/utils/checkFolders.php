<?php

define('DATA_DIR','/data/spezia/pe/');
define('APPS_DIR','/apps/praticaweb-2.1-dev/');
error_reporting(E_ERROR);
require_once "../../config.php";
require_once LIB."utils.class.php";
require_once LOCAL_LIB."pratica.class.php";
require_once LOCAL_LIB."app.utils.class.php";
$anno = "2013";
define('ALLEGATI_CONST',0);
$suffissoAnno = substr($anno,-2);
$baseDir = DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR,Array('data','spezia','pe','praticaweb','documenti','pe',$anno)).DIRECTORY_SEPARATOR;


$dbh = utils::getDb();
$sql = "SELECT DISTINCT pratica,numero FROM pe.avvioproc WHERE anno = ?";
$stmt = $dbh->prepare($sql);
if(!$stmt->execute(Array($anno))){
    $err = $stmt->errorInfo();
    die($err[2]);
}
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
$j = 0;
print "Pratiche Totali : ".count($res)."\n";
for($i=0;$i<count($res);$i++){
    $pratica = $res[$i]["pratica"];
    $numero = $res[$i]["numero"];
    $pr = new pratica($pratica);
    //$docPath = $pr->documenti;
    $newNum = "19".$numero;
    $n1 = appUtils::normalizeNumero($numero);
    $n2 = appUtils::normalizeNumero($newNum);
    if(ALLEGATI_CONST==1){
        $d1 = $baseDir.$n1.DIRECTORY_SEPARATOR."allegati".DIRECTORY_SEPARATOR;
        $d2 = $baseDir.$n2.DIRECTORY_SEPARATOR."allegati".DIRECTORY_SEPARATOR;
    }
    else{
        $d1 = $baseDir.$n1.DIRECTORY_SEPARATOR;
        $d2 = $baseDir.$n2.DIRECTORY_SEPARATOR;
    }
    $f1 = Array();
    $f2 = Array();
    
    if (file_exists($d2)){
        $ff1 = glob($d1."*");
        for($k=0;$k<count($ff1);$k++){
            $tmp = pathinfo($ff1[$k]);
            if($tmp['basename']!="allegati" && $tmp['basename']!="tmb") $f1[]=$tmp['basename'];
        }
        $ff2 = glob($d2."*");
        for($k=0;$k<count($ff2);$k++){
            $tmp = pathinfo($ff2[$k]);
            if($tmp['basename']!="allegati" && $tmp['basename']!="tmb" )  $f2[]=$tmp['basename'];
        }
        $j++;
        print "$j di $i) Pratica $pratica : $n1 duplicata in $d2\n";
        if (!file_exists($d1)){
            print "\t!!!!!!! Directory $d1 non esistente !!!!!!!!\n";
            mkdir($d1);
            mkdir($d1."allegati");
            mkdir($d1."allegati".DIRECTORY_SEPARATOR."tmb");
        }
        $result = array_diff($f2, $f1);
        if ($result){
            /*print "\tDirectory : $d1\n";
            print_r($f1);
            print "\tDirectory : $d2\n";
            print_r($f2);*/
            foreach($result as $file){
                if(copy($d2.$file,$d1.$file)) print "\tFile $file copiato da $d2 a $d1\n";
                else{
                    print "\t!!!!!!! ------  Errore nella copia di $file da $d2 a $d1 ------- !!!!!!!!!!\n";
                }
            }
            print_r($result);
        }
    }
    
}

die();


$dir = dirname(__FILE__);
$folders = glob($dir.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR.'*',GLOB_ONLYDIR);
foreach($folders as $folder){
    $newFolder=$folder."-".$suffissoAnno.DIRECTORY_SEPARATOR;
    if (file_exists($newFolder) && is_dir($newFolder)){
        $fld = $folder.DIRECTORY_SEPARATOR."*";
        $cmd = "cp -r $fld ".$newFolder;
    }
    else{
        $cmd = "cp -r $folder $newFolder";
    }
    system($cmd);
}
?>