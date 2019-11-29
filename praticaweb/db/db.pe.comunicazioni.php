<?php

function generateRandomString($length = 32) {
	return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

//utils::debugAdmin($_SESSION);
//utils::debugAdmin($_REQUEST);
//$r = appUtils::getComunicazione($id);
//utils::debugAdmin($r);
$dbh = utils::getDb();
if ($action==ACTION_MAIL){
	$res = appUtils::getComunicazione($id);
	$com = $res["comunicazione"];
	if (!$com["protocollo"]){
		require_once LOCAL_LIB."wsprotocollo.class.php";
		
		
		if ($res["success"]==1)
		{
			$com = $res["comunicazione"];
            for($i=0;$i<count($com["persone"]);$i++){
                $persone[]=Array(
                    "Nome"=>$com["persone"][$i]["nome"],
                    "Cognome"=>$com["persone"][$i]["cognome"],
                    "CodiceFiscale"=>$com["persone"][$i]["codfis"],
                    "IndirizzoTelematico"=>$com["persone"][$i]["pec"],
                );
            }
            $destinatari = $persone;
            for($i=0;$i<count($com["attachments"]);$i++){
                $allegati[]=Array(
                        "id"=>$com["attachments"][$i]["id"],
                        "nome_documento"=>$com["attachments"][$i]["name"],
                        "descrizione_documento"=>"Documento Generico",
                        "tipo_documento"=>"Richiesta",
                        "file"=>base64_encode($com["attachments"][$i]["file"])
                );
            }
		}	

		$mittente=Array(
			Array(
				"codice_amministrazione"=>CODICE_AMMINISTRAZIONE,
				"codice_a00"=>CODICE_A00,
				"codice_titolario"=>CODICE_TITOLARIO,
				"codice_uo"=>CODICE_UO,
				"denominazione_amministrazione"=>DENOMINAZIONE
			)
		);
		$ws = new protocollo();
		$prot = $ws->protocolla("U",$com["subject"],$mittente,$destinatari,$allegati);
		if (!$prot["protocollo"]){
			$Errors["protocollo"]="Si sono verificati degli errori durante la protocollazione della comunicazione";
		//$_REQUEST["destinatari"] = sprintf("{%s}",implode(',',$_REQUEST["destinatari"]));
		//$_REQUEST["allegati"] = sprintf("{%s}",implode(',',$_REQUEST["allegati"]));
		//$_REQUEST["allegati_1"] = sprintf("{%s}",implode(',',$_REQUEST["allegati_1"]));
			include_once $active_form;
			exit;
		}
		else{
			
			$sql = "UPDATE pe.comunicazioni SET protocollo=?, data_protocollo=? WHERE pratica=? AND id=?;";
			$stmt = $dbh->prepare($sql);
			if(!$stmt->execute(Array($prot["protocollo"],$prot["data"],$idpratica,$id))){
				$err = $stmt->errorInfo();
				$Errors["protocollo"]=$err[2];
				include_once $active_form;
				exit;
			}
			
		}
	}
	if(!$com["id_comunicazione"]==1){
		require_once DATA_DIR."config.mail.php";
		require_once LIB."mail.class.php";
		$rr = gwMail::inviaPec("",$com["to"],$com["subject"],$com["text"],$com["attachments"]);
		
		
		if($rr["success"]==1){
			
			$sql = "UPDATE pe.comunicazioni SET data_invio=?, id_comunicazione=? WHERE pratica=? AND id=?;";
			$stmt = $dbh->prepare($sql);
			if(!$stmt->execute(Array(date('d/m/Y'),$rr["uuid"],$idpratica,$id))){
				$err = $stmt->errorInfo();
				$Errors["data_invio"]=$err[2];
				include_once $active_form;
				exit;
			}
			
		}
		else{
			$Errors["data_invio"]="Si sono verificati degli errori durante l\'invio della comunicazione";
			include_once $active_form;
			exit;
		}
		
	}

}

?>