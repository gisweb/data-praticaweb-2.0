<?php

$xmlTemplate=Array();
$xmlTemplate["documenti_protocollati"] =<<<EOT
<xml>
<![CDATA[
	<ROOT>
		<CLASS_COD>06-03</CLASS_COD>
		<FASCICOLO_ANNO>%s</FASCICOLO_ANNO>
		<FASCICOLO_NUMERO>%s</FASCICOLO_NUMERO>
		<UTENTE>%s</UTENTE>
		<OGGETTO>%s</OGGETTO>
		<DATA_DAL>27/02/2000</DATA_DAL>
		<DATA_AL>%s</DATA_AL>
	</ROOT>
]]>
</xml>
EOT;
$xmlTemplate["documenti_nonprotocollati"] =<<<EOT
<xml>
<![CDATA[
	<ROOT>
		<CLASS_COD>06-03</CLASS_COD>
		<FASCICOLO_ANNO>%s</FASCICOLO_ANNO>
		<FASCICOLO_NUMERO>%s</FASCICOLO_NUMERO>
		<UTENTE>%s</UTENTE>
		<OGGETTO>%s</OGGETTO>
		<DATA_DAL>27/02/2000</DATA_DAL>
		<DATA_AL>%s</DATA_AL>
	</ROOT>
]]>
</xml>
EOT;
$xmlTemplate["documento"] =<<<EOT
<xml>
<![CDATA[
	<ROOT>
		<ID_DOCUMENTO>%s</ID_DOCUMENTO>
		<UTENTE>%s</UTENTE>
	</ROOT>
]]>
</xml>
EOT;

$xmlTemplate["lettera"] =<<<EOT
   <soapenv:Body>
      <ws:operatore>
         <utenteAd4>%s</utenteAd4>
      </ws:operatore>
      <ws:ente>%s</ws:ente>
      <ws:protocollo>
         <tipo>%s</tipo>
         <schema>%s</schema>
         <classificazione>%s</classificazione>
         <numeroFascicolo>%s</numeroFascicolo>
         <annoFascicolo>%s</annoFascicolo>
         <oggetto>%s</oggetto>
         <allegatoPrincipale>
            <idRiferimento>%s</idRiferimento>
            <contentType>%s</contentType>
            <nomeFile>%s</nomeFile>
            <file>%s</file>
         </allegatoPrincipale>
      </ws:protocollo>
   </soapenv:Body>

EOT;
?>