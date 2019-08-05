<?php


function creaDocumento($cl,$user,$fascicolo,$anno,$oggetto,$id,$documento){
		//Raccolgo informazioni sul file da inviare 
		if (file_exists($documento)){
			$info = pathinfo($documento);
			$filename=$info["basename"];
			$f = fopen($documento,'r');
			$stream = fread($f,filesize($documento));
			fclose($f);
			$file = base64_encode($stream);
			$contentType = mime_content_type($documento);
		}
		else{
			return Array("success"=>0,"data"=>Array(),"message"=>"Il file $documento non esiste");
		}
		$header = new SoapHeader(WSDL_DOCUM_URL,'Authorization',"Basic ".base64_encode(SERVICE_USER.":".SERVICE_PASSWD));
		$cl->__setSoapHeaders($header);
		//$xmlData = sprintf($template,$user,ENTE_CREA_LETTERA,TIPO_LETTERA,SCHEMA_LETTERA,CLASSIFICAZIONE,$fascicolo,$anno,$oggetto,$id,$contentType,$filename,$file);
		$operatore = new SoapVar(Array("utenteAd4"=>$user),SOAP_ENC_OBJECT);
		$ente = new SoapVar(ENTE_CREA_LETTERA,XSD_STRING);
		$protocollo = new SoapVar(Array(
			"tipo"=>TIPO_LETTERA,
			"schema"=>SCHEMA_LETTERA,
			"classificazione"=>CLASSIFICAZIONE,
			"numeroFascicolo"=>$fascicolo,
			"annoFascicolo"=>$anno,
			"oggetto"=>$oggetto,
			"allegatoPrincipale"=>
				new SoapVar(Array(
					"idRiferimento"=>$id,
					"contentType"=>$contentType,
					"nomeFile"=>$filename,
					"file"=>$file
				),SOAP_ENC_OBJECT)
			),
		SOAP_ENC_OBJECT);
		
		/*$ente = ENTE_CREA_LETTERA;
		$operatore = Array("utente"=>$user);
		$protocollo = Array("tipo"=>TIPO_LETTERA,"schema"=>SCHEMA_LETTERA,"classificazione"=>CLASSIFICAZIONE,"numeroFasciccolo"=>$fascicolo,"annoFascicolo"=>$anno,"oggetto"=>$oggetto);
		$allegato = Array("idRiferimento"=>$id,"contentType"=>$contentType,"nomeFile"=>$filename,"file"=>$file);*/
		$params = Array($operatore,$ente,$protocollo);
		//$res = $cl->creaLettera($operatore,$ente,$protocollo);
		try{
			$res = $cl->creaLettera($operatore,$ente,$protocollo);
                        $textSent = $cl->__getLastRequest();
		}
		catch (Exception $e) {
			$text = $cl->__getLastResponse();
                        $textSent = $cl->__getLastRequest();
                        print_debug($textSent,null,"CREA_LETTERA");
			$esitoRE="/<esito>(.+)<\/esito>/";
			$idRE="/<id>(.+)<\/id>/";
			$idDocumentoEsternoRE="/<idDocumentoEsterno>(.+)<\/idDocumentoEsterno>/";
			$urlRE="/<url>(.+)<\/url>/";
			preg_match_all($esitoRE,$text,$res1);
			$esito = $res1[1][0];
			if ($esito=='OK'){
		                $textSent = $cl->__getLastRequest();
				preg_match_all($idRE,$text,$res2);
				$id = $res2[1][0];
				preg_match_all($idDocumentoEsternoRE,$text,$res3);
				$idDoc = $res3[1][0];
				preg_match_all($urlRE,$text,$res4);
				$url = $res4[1][0];
				$r = Array("esito"=>$esito,"id"=>$id,"idDocumentoEsterno"=>$idDoc,"url"=>$url);
				return Array("success"=>1,"data"=>$r,"message"=>"");
			}
			else{
				return Array("success"=>0,"data"=>Array(),"message"=>"Errore nella richiesta Crealettera");
			}
			
		}
		$r = json_decode(json_encode($res),true);
		$xml = simplexml_load_string($r["return"]);
		if($xml===FALSE){
			return Array("success"=>0,"data"=>Array(),"message"=>"NO XML Response");
		}
		$r = json_decode(json_encode($xml),true);
		return Array("success"=>1,"data"=>$r,"message"=>"");
}



?>
