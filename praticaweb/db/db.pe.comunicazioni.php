<?php
$idpratica=$_REQUEST["pratica"];
$action = $_REQUEST["azione"];

if (in_array($action,Array("Salva","Elimina"))){
	
	if ($action=="Salva e Protocolla" && defined('PROT_OUT') && $_REQUEST["richiedi_protocollo_out"] && PROT_OUT==1){
				

	}
	include_once "./db/db.savedata.php";
}

	
$active_form="pe.comunicazioni.php?pratica=$idpratica&mode=list";
	
?>
