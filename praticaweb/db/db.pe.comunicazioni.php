<?php
$idpratica=$_REQUEST["pratica"];
$action = $_REQUEST["azione"];

if (in_array($action,Array("Salva","Elimina"))){
	
	if ($action=="Protocolla" && defined('PROT_OUT')  && PROT_OUT==1){
        require_once LOCAL_LIB."protocollo.class.php";
        $prot = new protocollo('U','PEC');
        $params["id"] = 1;
        $params["allegati"] = Array(1393,1392);
        $params["destinatari"] = Array(27675,27674);
        $params["app"] = "pe";
        $r = $prot->richiediProtOut($idpratica,$params);

	}
	include_once "./db/db.savedata.php";
}

	
$active_form="pe.comunicazioni.php?pratica=$idpratica&mode=list";
	
?>

