<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
$usr=$_SESSION['USER_NAME'];
$idpratica=$_REQUEST["pratica"];
$form=$_POST["form"];
$form = $_REQUEST["active_form"];
$azione=$_POST["azione"];
$procedimento=$_POST["procedimento"];
$id_modello= MODELLO_RATEIZZAZIONE_ONERI;
list($schema_iter,$nomeform)=explode(".",$form);
$schema_iter="pe";	//Redirigo gli schemi collegati a PE
//$_REQUEST["codice_richiesta"] = $codTrans;
//$_REQUEST["PAGOPA_RICHIESTA"] = 1;
$db=appUtils::getDB();

//	Creo un nuovo documento
require_once APPS_DIR."lib/stampe.word.class.php";
list($sc,$f)=explode(".",$form);
$schema="stp";
$doc=new wordDoc($id_modello,$idpratica);
$doc->createDoc();
$data=Array(
    'pratica'=>$pratica,
    'modello'=>$id_modello,
    'file_doc'=>$doc->docName,
    'file_pdf'=>$doc->docName,
    'form'=>$form,
    'utente_doc'=>$usr,
    'utente_pdf'=>$usr,
    'data_creazione_doc'=>'NOW',
    'data_creazione_pdf'=>'NOW',
    //"riferimento_record"=>"ragioneria.importi_dovuti.$codTrans"
);
try{
    $db->insert('stp.stampe',$data);
    $duplicated=0;
}
catch (Exception $e) {
    $duplicated=1;
}
if(!$duplicated){
    $lastid=appUtils::getLastId($db,'stp.stampe');
    //sql="INSERT INTO stp.stampe(pratica,modello,file_doc,file_pdf,form,utente_doc,utente_pdf,data_creazione_doc,data_creazione_pdf) VALUES($idpratica,$id_modello,$doc->docName,$doc->docName,'$form','$usr','$usr',now(),now())";
    ((if(!$db->sql_query($sql)) print_debug($sql);
    //$lastid=$db->sql_nextid();
    $type="";
    $edit="<img src=\"images/word.gif\" border=0 >&nbsp;&nbsp;<a target=\"documenti\" href=\"./openDocument.php?id=$lastid&pratica=$idpratica$type\" >$doc->basename</a>";
    $view="Creato il Documento ".$doc->basename;
    $data=Array(
        'pratica'=>$pratica,
        'data'=>'NOW',
        'utente'=>$usr,
        'nota'=>$view,
        'uidins'=>$_SESSION["USER_ID"],
        'tmsins'=>time(),
        'nota_edit'=>$edit,
        'stampe'=>$lastid,
        'immagine'=>'word.png'
    );
    $db->insert($schema_iter.'.iter',$data);
    //$sql="INSERT INTO $schema_iter.iter(pratica,data,utente,nota,nota_edit,uidins,tmsins,stampe,immagine) VALUES($idpratica,'$today','$usr','$testoview','$testoedit',".$_SESSION["USER_ID"].",".time().",$lastid,'laserjet.gif');";
    //$db->sql_query($sql);
    //Azioni da eseguire sulla stampa dei documenti

}

*/

?>


