<?php
$idpratica=$_REQUEST["pratica"];
$action = $_REQUEST["azione"];

if (in_array($action,Array("Salva","Elimina","Protocolla"))){
	if ($_REQUEST["azione"]=="Protocolla") $_REQUEST["azione"]='Salva';
        include_once APPS_DIR."/db/db.savedata.php";
        $id = ($_REQUEST["id"])?($_REQUEST["id"]):($_SESSION["ADD_NEW"]);
	if ($action=="Protocolla" && defined('PROT_OUT')  && PROT_OUT==1){
            require_once LOCAL_LIB."wsProtocollo.class.php";
            $allegati = $_POST["allegati"];
            $destinatari = $_POST["destinatari"];
            $prot = new wsProtocollo('U','PEC');
            $params["id"] = $id;
            $params["allegati"] = $allegati;
            $params["destinatari"] = $destinatari;
            $params["app"] = "pe";
            $r = $prot->richiediProtOut($idpratica,$params);
            print_array($r);
            $_REQUEST["mode"]="edit";
            include_once APPS_DIR."./db/db.savedata.php";
	}
}

	
$active_form="pe.comunicazioni.php?pratica=$idpratica&mode=list";
	
?>

