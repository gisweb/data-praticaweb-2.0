<?php
/**************************************   Pratica  ***********************************************/
$sql="SELECT  numero, B.nome as tipo, C.descrizione as intervento, anno, 
		data_presentazione, protocollo, data_prot, protocollo_int, data_prot_int,  
		D.nome as resp_proc, data_resp, com_resp, data_com_resp, E.nome as resp_it, data_resp_it, F.nome as resp_ia, data_resp_ia,  
		rif_aut_amb, aut_amb, riferimento_to, oggetto, note, rif_pratica, riferimento, 
		diritti_segreteria, riduzione_diritti, pagamento_diritti
  FROM 
  pe.avvioproc A LEFT JOIN pe.e_tipopratica B ON (A.tipo=B.id) LEFT JOIN pe.e_intervento C ON (A.intervento=C.id) LEFT JOIN admin.users D ON (A.resp_proc=D.userid)  LEFT JOIN admin.users E ON (A.resp_it=E.userid)  LEFT JOIN admin.users F ON (A.resp_ia=F.userid) 
  WHERE A.pratica=?";
  $ris=$db->fetchAll($sql,Array($this->pratica));
  $customData=array_merge($ris[0],$customData);
/************************************  Soggetti Interessati ***************************************/
$sql="SELECT DISTINCT
		 coalesce(app,'') as app, coalesce(cognome,'') as cognome, coalesce(nome,'') as nome,coalesce(app||' ','')||coalesce(cognome||' ','')||coalesce(nome,'') as nominativo, 
		coalesce(indirizzo,'') as indirizzo, coalesce(comune,'') as comune, coalesce(prov,'') as prov, coalesce(cap,'') as cap, 
		comunato, provnato, datanato, sesso, codfis,titolo,
		telefono, email, pec, 
		titolod, ragsoc, 
		sede, comuned, provd, capd, 
		piva, ccia, cciaprov, inail, inailprov, inps, inpsprov, cedile, cedileprov, 
		albo, albonumero, alboprov,
		coalesce(voltura,0) as voltura, comunicazioni, note, 
		proprietario,richiedente, concessionario, progettista, direttore, esecutore, 
		sicurezza, collaudatore,geologo, collaudatore_ca, progettista_ca, economia_diretta 
		FROM pe.soggetti WHERE pratica=? and comunicazioni = 1";
$ris=$db->fetchAll($sql,Array($this->pratica));
for($i=0;$i<count($ris);$i++){
	$soggetto=$ris[$i];
	extract($soggetto);
	if ($soggetto["proprietario"] && !$soggetto["voltura"]) {
		$customData["proprietario"][]=$soggetto;
	}
	if ($soggetto["richiedente"] && !$soggetto["voltura"]) {
		$customData["richiedente"][]=$soggetto;
		$richiedenti[]="$nominativo, nato a $comune, il $datanato, C.F. $codfis";
	}
	if ($soggetto["progettista"] && !$soggetto["voltura"]) {
		$customData["progettista"][]=$soggetto;
		$progettisti=="$nominativo, nato a $comune, il $datanato, C.F. $codfis";
	}
	if ($soggetto["progettista_ca"] && !$soggetto["voltura"]) {
		$customData["progettista_ca"][]=$soggetto;
	}
	if ($soggetto["esecutore"] && !$soggetto["voltura"]) {
		$customData["esecutore"][]=$soggetto;
	}
	if ($soggetto["sicurezza"] && !$soggetto["voltura"]) {
		$customData["sicurezza"][]=$soggetto;
	}
	if ($soggetto["geologo"] && !$soggetto["voltura"]) {
		$customData["geologo"][]=$soggetto;
	}
	if ($soggetto["collaudatore"] && !$soggetto["voltura"]) {
		$customData["collaudatore"][]=$soggetto;
	}
	if ($soggetto["collaudatore_ca"] && !$soggetto["voltura"]) {
		$customData["collaudatore_ca"][]=$soggetto;
	}
}   
$customData["elenco_richiedenti"]=implode(", ",$richiedenti);
$customData["elenco_progettisti"]=implode(", ",$progettisti);
/**************************************   Indirizzi  ***********************************************/
$sql="SELECT pratica, via, civico, interno, scala, piano FROM pe.indirizzi WHERE pratica=?;";
$ris=$db->fetchAll($sql,Array($this->pratica));
for($i=0;$i<count($ris);$i++){
	extract($ris[$i]);
	$indirizzo=$ris[$i];
	$customData["indirizzo"][]=$indirizzo;
	$indirizzi[]="$via $civico";
}
$customData["ubicazione"]=implode(", ",$indirizzi);
/**************************************   Catasto Terreni  ***********************************************/
$sql="SELECT DISTINCT coalesce(B.nome,'') as sezione,foglio,A.sezione as sez FROM pe.cterreni A LEFT JOIN nct.sezioni B USING(sezione) WHERE pratica=?";
$ris=$db->fetchAll($sql,Array($this->pratica));
for($i=0;$i<count($ris);$i++){
	$sez=$ris[$i]["sez"];
	$fg=$ris[$i]["foglio"];
	$sezione=$ris[$i]["sezione"];
	$sql="SELECT DISTINCT mappale FROM pe.cterreni WHERE pratica=? AND coalesce(sezione::varchar,'')=? AND coalesce(foglio::varchar,'')=?";
	$ris=$db->fetchAll($sql,Array($this->pratica,$sez,$fg));
	$mappali=Array();
	for($j=0;$i<count($ris);$i++){
		$arrMap[]=$ris[$i]["mappale"];
		$customData["particelle_ct"][]=Array("sezione"=>$sezione,"foglio"=>$fg,"mappale"=>$ris[$i]["mappale"]);
	}
	$mappali=implode(", ",$arrMap);
	$customData["particelle_fg"][]=($sez)?(sprintf("Sez. %s Foglio %s Mappali %s",$sezione,$fg,$mappali)):(sprintf("Foglio %s Mappali %s",$fg,$mappali));
}
$customData["elenco_cterreni"]=implode(", ",$customData["particelle_fg"]);
/**************************************   Catasto Terreni  ***********************************************/
$sql="SELECT DISTINCT coalesce(B.nome,'') as sezione,foglio,A.sezione as sez FROM pe.curbano A LEFT JOIN nct.sezioni B USING(sezione) WHERE pratica=?";
$ris=$db->fetchAll($sql,Array($this->pratica));
for($i=0;$i<count($ris);$i++){
	$sez=$ris[$i]["sez"];
	$fg=$ris[$i]["foglio"];
	$sezione=$ris[$i]["sezione"];
	$sql="SELECT DISTINCT mappale FROM pe.curbano WHERE pratica=? AND coalesce(sezione::varchar,'')=? AND coalesce(foglio::varchar,'')=?";
	$ris=$db->fetchAll($sql,Array($this->pratica,$sez,$fg));
	for($j=0;$i<count($ris);$i++){
		$customData["particelle_cu"][]=Array("sezione"=>$sezione,"foglio"=>$fg,"mappale"=>$ris[$i]["mappale"]);
	}
	
}
/**************************************   Vincoli  ***********************************************/


/**************************************   Pareri  ***********************************************/
$sql="SELECT A.*,B.nome as nome_ente,B.codice FROM (SELECT AA.* FROM pe.pareri AA INNER JOIN (SELECT ente,max(data_rich) as data_rich FROM pe.pareri GROUP BY ente ) BB USING(ente,data_rich)) A INNER JOIN (SELECT * FROM pe.e_enti WHERE enabled=1) B ON (A.ente=B.id) WHERE pratica=? ORDER BY data_rich DESC";
$ris=$db->fetchAll($sql,Array($this->pratica));
for($i=0;$i<count($ris);$i++){
	$parere=$ris[$i];
	$customData["pareri"][]=$parere;
	if($parere["codice"]=="ce"){
		$customData["data_ce"]=$parere["data_ril"];
		$customData["prescrizioni_ce"]=$parere["prescrizioni"];
	}
	if($parere["codice"]=="cei"){
		$customData["data_cei"]=$parere["data_ril"];
		$customData["prescrizioni_cei"]=$parere["prescrizioni"];
	}
}
/**************************************   Allegati  ***********************************************/
$sql="SELECT coalesce(B.descrizione,B.nome) as documento,allegato,mancante,integrato,sostituito
	FROM pe.allegati A INNER JOIN pe.e_documenti B ON(A.documento=B.id) 
	WHERE pratica=?";
$ris=$db->fetchAll($sql,Array($this->pratica));
$allegati=Array();
$mancanti=Array();
for($i=0;$i<count($ris);$i++){
	$documento=$ris[$i];
	if ($documento["allegato"]) $allegati[]=Array("nome"=>$documento["documento"]);
	if ($documento["mancante"]) $mancanti[]=Array("nome"=>$documento["documento"]);
}
$customData["allegati"]=$allegati;
$customData["allegati_mancanti"]=$mancanti;

/**************************************   Agibilità  ***********************************************/
$sql="SELECT * FROM pe.abitabi WHERE pratica=?";
$ris=$db-> fetchAssoc($sql,Array($this->pratica));

$customData["numero_richiesta_agi"]=$ris["numero_rich"];
$customData["prot_richiesta_agi"]=$ris["prot_rich"];
$customData["data_richiesta_agi"]=$ris["data_rich"];

$customData["numero_agi"]=$ris["numero_doc"];
$customData["protocollo_agi"]=$ris["prot_doc"];
$customData["data_agi"]=$ris["data_ril"];
/***************************************************************************************************/
print_debug($customData,NULL,'STAMPA-UNIONE');
//array_walk_recursive($customData, 'decode');
?>