<?php
$dirMod = "/Users/mamo/Desktop/Data/savona/pe/praticaweb/modelli/";
$dirMod = "/data/savona/pe/praticaweb/modelli/";
$fileToModify = "styles.xml";
$fileList = glob($dirMod."*.odt");
$i=0;
$res = Array();
foreach($fileList as $f){
    $i++;
    $zip = new ZipArchive();
    $zip->open($f);
    $text = $zip->getFromName($fileToModify);
    //Modify contents:
    //$text = str_replace('onload.', "", $text);
    $text = str_replace('[istruttore_tecnico]', "[onload.istruttore_tecnico]", $text);
    $text = str_replace('[data_presentazione]', "[onload.data_presentazione]", $text);
    $text = str_replace('[responsabile_procedimento]', "[onload.responsabile_procedimento]", $text);
    $text = str_replace('[struttore_tecnico]', "[onload.istruttore_tecnico]", $text);
    $text = str_replace('[tecnico_responsabile]', "[onload.tecnico_responsabile]", $text);
    /* Search for fields*/
    //preg_match_all("/\[([\.a-z_]+)\]/",$text,$matches);
    //if (count($matches[0])) $res = array_merge($matches[1],$res);
    //Delete the old...
    $zip->deleteName($fileToModify);
    //Write the new...
    $zip->addFromString($fileToModify, $text);
    //And write back to the filesystem.
    $zip->close();
    print "\t$i) Modified File : $f \n";
}
//print_r(array_unique($res));
