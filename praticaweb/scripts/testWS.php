<?php
require_once "/data/savona/pe/config.ads.php";
require_once "WSSoapClient.php";
require_once "template.php";
$client = new SoapClient(WSDL_LOGIN_URL);

$xmlData =<<<EOT
<xml>
<![CDATA[
	<ROOT>
		<FASCICOLO_ANNO>2019</FASCICOLO_ANNO>
		<FASCICOLO_NUMERO>3052.1</FASCICOLO_NUMERO>
		<UTENTE>AGSPRWS</UTENTE>
		<OGGETTO>%</OGGETTO>
		<DATA_DAL>27/02/2000</DATA_DAL>
		<DATA_AL>01/03/2019</DATA_AL>
	</ROOT>
]]>
</xml>
EOT;

$xmlData1 =<<<EOT
<xml>
<![CDATA[
	<ROOT>
		<ID_DOCUMENTO>%s</ID_DOCUMENTO>
		<UTENTE>%s</UTENTE>
	</ROOT>
]]>
</xml>
EOT;



$res = $client->login(Array("strCodEnte"=>CODICE_ENTE,"strUserName"=>SERVICE_USER,"strPassword"=>SERVICE_PASSWD));
$r = json_decode(json_encode($res),true);
//print_r($r);
$strDST=$r["LoginResult"]["strDST"];
//echo "$strDST\n";
$clientDocs = new SoapClient(WSDL_PROTEXT_URL,array("login"=>SERVICE_USER,"password"=>SERVICE_PASSWD,'trace' => true, 'exceptions' => true));

//$clientDocs->__setUsernameToken(SERVICE_USER,SERVICE_PASSWD);
$auth = array(
    'Username' => SERVICE_USER,
    'Password' => SERVICE_PASSWD
);

$header = new SoapHeader(WSDL_PROTEXT_URL,'Authorization',"Basic ".base64_encode(SERVICE_USER.":".SERVICE_PASSWD));
$clientDocs->__setSoapHeaders($header);

$params[] = new SoapVar(SERVICE_USER,XSD_STRING,null,null,'user');
$params[] = new SoapVar($strDST,XSD_STRING,null,null,'DST');
$xml = new SoapVar($xmlData,XSD_ANYXML,null,null,'xml');
$obj["getDocumentiProtocollati"] = new SoapVar($params, SOAP_ENC_OBJECT,null,null,'getDocumentiProtocollati'); 

$res = $clientDocs->getDocumentiProtocollati(Array("user"=>SERVICE_USER,"DST"=>$strDST,"xml"=>$xml));print_r($res);die($xmlData);
$r = json_decode(json_encode($res),true);

$xml = simplexml_load_string($r["return"]);
if($xml===FALSE){
	die("Errore XML");
}
$r = json_decode(json_encode($xml),true);
for($i=0;$i<count($r["DOCUMENTO"]);$i++){
	$idDoc = $r["DOCUMENTO"][$i]["ID_DOCUMENTO"];
	$xmlDoc = sprintf($xmlData1,$idDoc,SERVICE_USER);
	$xml = new SoapVar($xmlDoc,XSD_ANYXML,null,null,'xml');
	$clientDocs->__setSoapHeaders($header);
	$res = $clientDocs->getDocumento(Array("user"=>SERVICE_USER,"DST"=>$strDST,"xml"=>$xml));
	$rr = json_decode(json_encode($res),true);

	$xml = simplexml_load_string($rr["return"]);
	if($xml===FALSE){
		die("Errore XML");
	}
	$rrr = json_decode(json_encode($xml),true);
	print_r($rrr);
}
//print_r($r);

//echo "REQUEST:\n" . $clientDocs->__getLastRequest() . "\n";
?>
