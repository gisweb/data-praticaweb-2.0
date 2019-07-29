<?php
require_once "/data/savona/pe/config.ads.php";
require_once "WSSoapClient.php";
define('TEMPLATE_FILE',"/data/savona/pe/praticaweb/scripts/template.php");
$client = new SoapClient(WSDL_LOGIN_URL);
require_once TEMPLATE_FILE;


$anno = "2019";
//$fascicolo="3108.1";
$fascicolo="3368.1";



function print_debug($t="",$file=NULL){
		if (!defined("DEBUG_DIR")) {
			define("DEBUG_DIR",'/data/savona/pe/praticaweb/scripts/debug/');
		}
        $uid="";
        $data=date('j-m-y');
        $ora=date("H:i:s");
        if (!$file) $nomefile=DEBUG_DIR.$uid."standard.debug";
        else
            $nomefile=DEBUG_DIR.$uid.$file.".debug";
        $size=(file_exists($nomefile))?(filesize($nomefile)):(0);

        $f=($size>1000000)?(fopen($nomefile,"w+")):(fopen($nomefile,"a+"));
        if (!$f) die("<p>Impossibile aprire il file $nomefile</p>");

        if (is_array($t)||is_object($t)){
            ob_start();
            print_r($t);
            $out=ob_get_contents ();
            ob_end_clean();
            if (!fwrite($f,"\n$data\t$ora\t --- STAMPA DI UN ARRAY ---\n\t$out")) echo "<p>Impossibile scrivere sul file $nomefile </p>";
            fclose($f);
        }
        else{
            if (!fwrite($f,"\n$data\t$ora\n\t".$t)) echo "<p>Impossibile scrivere sul file $nomefile </p>";
            else
                fclose($f);
        }

}

function getDocumentiProtocollati($cl,$template,$strDST,$user,$fascicolo,$anno,$oggetto){

	$header = new SoapHeader(WSDL_PROTEXT_URL,'Authorization',"Basic ".base64_encode(SERVICE_USER.":".SERVICE_PASSWD));
	$cl->__setSoapHeaders($header);
	$data = date('d/m/Y');

	$xmlData = sprintf($template,$anno,$fascicolo,$user,$oggetto,$data);
	$xml = new SoapVar($xmlData,XSD_ANYXML,null,null,'xml');
	$res = $cl->getDocumentiProtocollati(Array("user"=>SERVICE_USER,"DST"=>$strDST,"xml"=>$xml));
	$r = json_decode(json_encode($res),true);
	print_r($res);die();
	$xml = simplexml_load_string($r["return"]);
	if($xml===FALSE){
		return Array("success"=>0,"data"=>Array(),"message"=>"NO XML Response");
	}
	
	$r = json_decode(json_encode($xml),true);
	
	if(!in_array("DOCUMENTO",array_keys($r))) return Array("success"=>1,"data"=>Array(),"message"=>"Nessun documento trovato");
	if(count($r["DOCUMENTO"])==1) $r["DOCUMENTO"] = Array($r["DOCUMENTO"]);
	return Array("success"=>1,"data"=>$r["DOCUMENTO"],"message"=>"");
}

function getDocumentiNonProtocollati($cl,$template,$strDST,$user,$fascicolo,$anno,$oggetto){

	$header = new SoapHeader(WSDL_PROTEXT_URL,'Authorization',"Basic ".base64_encode(SERVICE_USER.":".SERVICE_PASSWD));
	$cl->__setSoapHeaders($header);
	$data = date('d/m/Y');

	$xmlData = sprintf($template,$anno,$fascicolo,$user,$oggetto,$data);
	$xml = new SoapVar($xmlData,XSD_ANYXML,null,null,'xml');
	$res = $cl->getDocumentiNonProtocollati(Array("user"=>SERVICE_USER,"DST"=>$strDST,"xml"=>$xml));
	$r = json_decode(json_encode($res),true);

	$xml = simplexml_load_string($r["return"]);
	if($xml===FALSE){
		return Array("success"=>0,"data"=>Array(),"message"=>"NO XML Response");
	}
	$r = json_decode(json_encode($xml),true);
	if(!in_array("DOCUMENTO",array_keys($r))) return Array("success"=>1,"data"=>Array(),"message"=>"Nessun documento trovato");
	if(count($r["DOCUMENTO"])==1) $r["DOCUMENTO"] = Array($r["DOCUMENTO"]);

	return Array("success"=>1,"data"=>$r["DOCUMENTO"],"message"=>"");
}

function getInfoDocumento($cl,$template,$strDST,$user,$idDoc){
	$header = new SoapHeader(WSDL_PROTEXT_URL,'Authorization',"Basic ".base64_encode(SERVICE_USER.":".SERVICE_PASSWD));
	$cl->__setSoapHeaders($header);
	$xmlData = sprintf($template,$idDoc,$user);
	$xml = new SoapVar($xmlData,XSD_ANYXML,null,null,'xml');
	$res = $cl->getDocumento(Array("user"=>SERVICE_USER,"DST"=>$strDST,"xml"=>$xml));
	$r = json_decode(json_encode($res),true);
	$xml = simplexml_load_string($r["return"]);
	if($xml===FALSE){
		return Array("success"=>0,"data"=>Array(),"message"=>"NO XML Response");
	}
	$r = json_decode(json_encode($xml),true);
	return Array("success"=>1,"data"=>$r,"message"=>"");
}

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
//        print_r($protocollo);

		try{
			$res = $cl->creaLettera($operatore,$ente,$protocollo);
		}
		catch (Exception $e) {
			$text = $cl->__getLastResponse();
			$esitoRE="/<esito>(.+)<\/esito>/";
			$idRE="/<id>(.+)<\/id>/";
			$idDocumentoEsternoRE="/<idDocumentoEsterno>(.+)<\/idDocumentoEsterno>/";
			$urlRE="/<url>(.+)<\/url>/";
			preg_match_all($esitoRE,$text,$res1);
			$esito = $res1[1][0];
			if ($esito=='OK'){
					preg_match_all($idRE,$text,$res2);
					$id = $res2[1][0];
					preg_match_all($idDocumentoEsternoRE,$text,$res3);
					$idDoc = $res3[1][0];
					preg_match_all($urlRE,$text,$res4);
					$url = $res4[1][0];
					$r = Array("esito"=>$esito,"id"=>$id,"idDOcumentoEsterno"=>$idDoc,"url"=>$url);
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


function downloadAllegato($cl,$idDocumento,$idObj,$filename,$utente){
	$header = new SoapHeader(WSDL_DOWNLOAD,'Authorization',"Basic ".base64_encode(SERVICE_USER.":".SERVICE_PASSWD));
	$cl->__setSoapHeaders($header);
	try{
		$res = $cl->downloadAttach(Array("idDocumento"=>$idDocumento,"idObjFile"=>$idObj,"fileName"=>$filename,"utenteApplicativo"=>$utente));
	}
	catch (Exception $e) {
		$text = $cl->__getLastResponse();
		$esitoRE="/<result>(.+)<\/result>/";
		$fileRE="/<contentFile>(.+)<\/contentFile>/";
		preg_match_all($esitoRE,$text,$res1);
		$esito = $res1[1][0];
		if ($esito=='-1'){
			$errorRE="/<errStr>(.+)<\/errStr>/";
			preg_match_all($errorRE,$text,$res3);
			$error = $res3[1][0];
			return Array("success"=>0,"data"=>Array(),"message"=>$error);
		}
		else{
			preg_match_all($fileRE,$text,$res2);
			$file = $res2[1][0];
			$data   = _stripSoapHeaders($text);
			$file = _parseMimeData($data);
			return Array("success"=>1,"data"=>$file,"message"=>"");
		}
		print $text."\n";die();
	}
	$r = json_decode(json_encode($res),true);
	$xml = simplexml_load_string($r);
	if($r["result"]==-1){
		return Array("success"=>0,"data"=>Array(),"message"=>$r["errStr"]);
	}
	else{
		$data = $r["contentFile"];
	}
}


/**********************************************************************************************************************/
/*                                                  LOGIN                                                             */
/**********************************************************************************************************************/
$res = $client->login(Array("strCodEnte"=>CODICE_ENTE,"strUserName"=>SERVICE_USER,"strPassword"=>SERVICE_PASSWD));
$r = json_decode(json_encode($res),true);

$strDST=$r["LoginResult"]["strDST"];
$clientDocs = new SoapClient(WSDL_PROTEXT_URL,array("login"=>SERVICE_USER,"password"=>SERVICE_PASSWD,'trace' => true, 'exceptions' => true));

$auth = array(
    'Username' => SERVICE_USER,
    'Password' => SERVICE_PASSWD
);


/**********************************************************************************************************************/
/*                                         INFORMAZIONI SUI DOCUMENTI PROTOCOLLATI                                    */
/**********************************************************************************************************************/
/*
$starttime = microtime(true);

$result = getDocumentiProtocollati($clientDocs,$xmlTemplate["documenti_protocollati"],$strDST,SERVICE_USER,$fascicolo,$anno,"%");

$endtime = microtime(true);
$timediff = $endtime - $starttime;

print "Elapsing Time Documenti Protocollati: $timediff\n";
//print_r($result);die();
if ($result["success"]!==1){
	die("Nessun Risposta");
}
$starttime = microtime(true);

if (!count($result["data"])) print "Nessun documento protocollato per il fascicolo $fascicolo dell'anno $anno\n";
for($i=0;$i<count($result["data"]);$i++){
	$idDoc = $result["data"][$i]["ID_DOCUMENTO"];
	// INFORMAZIONI SUL SINGOLO DOCUMENTO
	$res = getInfoDocumento($clientDocs,$xmlTemplate["documento"],$strDST,SERVICE_USER,$idDoc);
	if ($res["success"]!==1){
		die("Nessun Risposta");
	}
	else{
		print_debug($res,"PROT-".$idDoc);
	}
	
}
$endtime = microtime(true);
$timediff = $endtime - $starttime;

print "Elapsing Time Informazioni Documenti: $timediff\n";
print_r($result);
*/
/**********************************************************************************************************************/
/*                                         INFORMAZIONI SUI DOCUMENTI DA PROTOCOLLARE                                 */
/**********************************************************************************************************************/
/*
$starttime = microtime(true);
$result = getDocumentiNonProtocollati($clientDocs,$xmlTemplate["documenti_protocollati"],$strDST,SERVICE_USER,$fascicolo,$anno,"%");
if ($result["success"]!==1){
	die("Nessun Risposta");
}
//print_r($result);die();
if (!count($result["data"])) print "Nessun documento non protocollato per il fascicolo $fascicolo dell'anno $anno\n";

for($i=0;$i<count($result["data"]);$i++){
	$idDoc = $result["data"][$i]["ID_DOCUMENTO"];
	// INFORMAZIONI SUL SINGOLO DOCUMENTO
	$res = getInfoDocumento($clientDocs,$xmlTemplate["documento"],$strDST,SERVICE_USER,$idDoc);
	if ($res["success"]!==1){
		die("Nessun Risposta");
	}
	else{
		print_debug($res,"NONPROT-".$idDoc);
	}
	
}
$endtime = microtime(true);
$timediff = $endtime - $starttime;

print "Elapsing Time Informazioni Documenti: $timediff\n";
print_r($result);
*/
/**********************************************************************************************************************/
/*                                           INVIO DEL DOCUMENTO AL GESTIONALE DI ADS                                 */
/**********************************************************************************************************************/

$fname= "/data/savona/pe/praticaweb/scripts/Test-CreaLettera.odt";
$clientCreation = new SoapClient(WSDL_DOCUM_URL,array("login"=>SERVICE_USER,"password"=>SERVICE_PASSWD,"trace" => true,'exceptions' => true));
$result = creaDocumento($clientCreation,'GMACARIO',$fascicolo,$anno,"PROVA CREAZIONE DOCUMENTO CON METODO WS CREA_LETTERA",'9999955',$fname);
print_r($result);
echo "REQUEST:\n" . $clientDocs->__getLastRequest() . "\n";


/**********************************************************************************************************************/
/*                                           DOWNLOAD ATTACHMENT                                                      */
/**********************************************************************************************************************/
//require_once "MTOMSoapClient.php";
/*$clientDownload = new MTOMSoapClient(WSDL_DOWNLOAD,array("login"=>SERVICE_USER,"password"=>SERVICE_PASSWD,"trace" => true,'exceptions' => true));
$filename = 'laurenzana.pdf';
$result = downloadAllegato($clientDownload,'1643561','1137808',$filename,SERVICE_USER);

$f = fopen($filename,'w');
fwrite($f,base64_decode($result["data"]));
fclose($f);*/
//print_r($result);

?>
