<?php

function generateRandomString($length = 32) {
	return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

//utils::debugAdmin($_SESSION);
//utils::debugAdmin($_REQUEST);
//$r = appUtils::getComunicazione($id);
//utils::debugAdmin($r);
if ($action==ACTION_MAIL){
	$r = appUtils::getComunicazione($id);
	if (!$r["protocollo"]){
		require_once LOCAL_LIB."wsprotocollo.class.php";
		$Errors["protocollo"]="Si sono verificati degli errori durante la protocollazione della comunicazione";
		//$_REQUEST["destinatari"] = sprintf("{%s}",implode(',',$_REQUEST["destinatari"]));
		//$_REQUEST["allegati"] = sprintf("{%s}",implode(',',$_REQUEST["allegati"]));
		//$_REQUEST["allegati_1"] = sprintf("{%s}",implode(',',$_REQUEST["allegati_1"]));
		include_once $active_form;
		exit;
	}
	if($r["success"]==1){
		require_once DATA_DIR."config.mail.php";
		require_once LIB."mail.class.php";
		$rr = gwMail::inviaPec("",$r["comunicazione"]["to"],$r["comunicazione"]["subject"],$r["comunicazione"]["text"],$r["comunicazione"]["attachments"]);
		
		
		if($rr["success"]==1){
			//$dbh = utils::getDb();
			//$sql = "UPDATE pe.comunicazioni SET data_invio=?, id_comunicazione=? WHERE pratica=? AND id=?;";
			//$stmt = $dbh->prepare->sql($sql);
			//if(!$stmt->execute(Array(date('d/m/Y'),$rr["uuid"],$idpratica,$id)){
			//	$err = $stmt->errorInfo();
			//	$Errors["data_invio"]=$err[2];
			//	include_once $active_form;
			//	exit;
			//	utils::debugAdmin($err);
			//}
		}
		else{
			$Errors["data_invio"]="Si sono verificati degli errori durante l'invio della comunicazione";
			include_once $active_form;
			exit;
		}
		
	}

}

?>