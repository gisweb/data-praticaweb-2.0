<?php
$text  = <<<EOT
<?xml version='1.0'?> 
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
	<soap:Body>
		<ns2:creaLetteraResponse xmlns:ns2="http://ws.integrazioni.protocollo.finmatica.it/">
			<esito>OK</esito>
			<id>97439</id>
			<idDocumentoEsterno>1786645</idDocumentoEsterno>
			<url>http://documentale.comune.savona.it/Protocollo/standalone.zul?operazione=APRI_DOCUMENTO&amp;tipoDocumento=LETTERA&amp;idDoc=1786645</url>
		</ns2:creaLetteraResponse>
	</soap:Body>
</soap:Envelope>
EOT;


$text = <<<XML
<?xml version='1.0'?> 
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
	<soap:Body>
		<ns2:creaLetteraResponse>
			<esito>OK</esito>
			<id>97439</id>
			<idDocumentoEsterno>1786645</idDocumentoEsterno>
			<url>http://documentale.comune.savona.it/Protocollo/standalone.zul?operazione=APRI_DOCUMENTO&amp;tipoDocumento=LETTERA&amp;idDoc=1786645</url>
		</ns2:creaLetteraResponse>
	</soap:Body>
</soap:Envelope>
XML;
$esitoRE="/<esito>(.+)<\/esito>/";
$idRE="/<id>(.+)<\/id>/";
$idDocumentoEsternoRE="/<idDocumentoEsterno>(.+)<\/idDocumentoEsterno>/";
$urlRE="/<url>(.+)<\/url>/";
preg_match_all($esitoRE,$text,$res1);
preg_match_all($idRE,$text,$res2);
preg_match_all($idDocumentoEsternoRE,$text,$res3);
preg_match_all($urlRE,$text,$res4);

print_r($res1);
print_r($res2);
print_r($res3);
print_r($res4);
?>