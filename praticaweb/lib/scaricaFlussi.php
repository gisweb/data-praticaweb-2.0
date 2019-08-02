<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$urlFlussi = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/rest/riconciliazione/listaflussi";
$urlFlusso = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/rest/riconciliazione/flusso";

$ente = "Unione Dei Comuni Valmerula e Montarosio";
$da = "01/01/2019";
$a = "01/08/2019";
$dataFlussi = Array(
    'nomeEnte' => $ente,
	'da' => $da,
	'a' => $a
);
$dataFlusso = Array(
    'idFlusso' => "",
	'tipo' => 'dettaglio',
    'nomeEnte' => $ente
);
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $urlFlussi);
curl_setopt($ch,CURLOPT_POST, count($dataFlussi));
curl_setopt($ch,CURLOPT_POSTFIELDS, $dataFlussi);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

//execute post
$result = curl_exec($ch);
$record = json_decode($result, true);

print_r($result);
?>