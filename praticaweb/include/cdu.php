<?php
$idPratica=(defined('FIELDS_LIST'))?(0):($this->pratica);

$sql="SELECT A.*,B.dovuto as diritti_segreteria FROM cdu.richiesta A inner join cdu.diritti_segreteria B using(pratica) WHERE pratica=?";
$ris=$db->fetchAll($sql,Array($idPratica));
array_walk_recursive($ris, 'decode');
$customData["cdu_richiesta"]=$ris;
$sql=<<<EOT
SELECT DISTINCT
pratica,D.nome||','||foglio||','||mappale as key,D.nome as sezione,foglio,mappale,
vincolo,tavola,zona,
perc_area,
A.descrizione as descrizione_vincolo, B.descrizione as descrizione_tavola, C.descrizione as descrizione_zona,

case
when A.nome_vincolo in ('PRG','PTCP','DISCIPLINA_PAESISTICA','PUC','PUC_2015') then A.sigla||' '
when (A.nome_vincolo='PIANO_DI_BACINO' and B.nome_tavola!='AMBITO') or (A.nome_vincolo='VINCOLI') THEN ''
else A.descrizione||' '
end
||
case
when A.nome_vincolo='PRG' and B.nome_tavola='PIANO_C2' THEN 'Piano di Zona '
when (A.nome_vincolo='PUC_2015') or (A.nome_vincolo='DISCIPLINA_PAESISTICA') or (A.nome_vincolo='PRG' and B.nome_tavola in ('ZONIZZAZIONE','LOTTIZZAZIONI','SOTTOZONE')) or (A.nome_vincolo='PIANO_DI_BACINO' and (B.nome_tavola IN ('AMBITO','BACINO') or B.nome_tavola ilike 'regimi%')) or (A.nome_vincolo='VINCOLI' and B.nome_tavola in ('INCENDI','VINCOLO_IDROGEOLOGICO'))then ''::text
else B.descrizione||' '
end
||
case
when A.nome_vincolo='PIANO_DI_BACINO' and B.nome_tavola in ('RISPETTO__ARMEA_E_FONTI','RISPETTO__OSPEDALETTI','RISPETTO__S_FRANCESCO') and C.nome_zona='RISPETTO' then ''
when A.nome_vincolo='PIANO_DI_BACINO' and B.nome_tavola in ('INEDIFICABILITA__ARMEA_E_FONTI','INEDIFICABILITA__OSPEDALETTI','INEDIFICABILITA__S_FRANCESCO') and C.nome_zona='INEDIFICABILITA' then ''
when A.nome_vincolo='PIANO_DI_BACINO' and B.nome_tavola ilike 'regimi%' then 'Regime '|| C.sigla
when A.nome_vincolo='PIANO_DI_BACINO' and B.nome_tavola='BACINO' then C.descrizione
when A.nome_vincolo='VINCOLI' and B.nome_tavola='INCENDI' then 'Zone boscate percorse dal fuoco: '||C.descrizione
when A.nome_vincolo='VINCOLI' and B.nome_tavola!='INCENDI' then C.descrizione
when (A.nome_vincolo='DISCIPLINA_PAESISTICA' and B.nome_tavola='ZONE_ISMA') then 'Sottozona Paesistica '|| C.sigla 
when (A.nome_vincolo='DISCIPLINA_PAESISTICA' and B.nome_tavola='MACRO_UNITA') then 'Unità Paesistica n° '|| C.sigla
when (A.nome_vincolo='PRG' and B.nome_tavola='SOTTOZONE') then 'sottozona '|| C.sigla
when (A.nome_vincolo='PUC_2015' and B.nome_tavola='AMBITI_E_DISTRETTI' and not C.nome_zona ilike 'DT%') then 'ambito '|| C.sigla
when (A.nome_vincolo='PUC_2015' and B.nome_tavola='AMBITI_E_DISTRETTI' and C.nome_zona ilike 'DT%') then 'distretto '|| C.sigla
when (A.nome_vincolo='PUC_2015' and B.nome_tavola='SERVIZI_ED_INFRASTRUTTURE' and C.nome_zona ilike 'SERVIZI_ESISTENTI%') then 'servizio esistente '|| C.sigla
when (A.nome_vincolo='PUC_2015' and B.nome_tavola='SERVIZI_ED_INFRASTRUTTURE' and C.nome_zona ilike 'SERVIZI_PROGETTO%') then 'servizio di progetto '|| C.sigla
when (A.nome_vincolo='PUC_2015' and B.nome_tavola='SERVIZI_ED_INFRASTRUTTURE' and not C.nome_zona ilike 'SERVIZI_PROGETTO%' and not C.nome_zona ilike 'SERVIZI_ESISTENTI%') then 'infrastruttura '|| C.sigla
when (A.nome_vincolo='PUC_2015' and B.nome_tavola='SOTTOAMBITI') then 'sottoambito '|| C.sigla
when (A.nome_vincolo='PUC_2015' and B.nome_tavola='SUSCETTIVITA_D_USO_DEL_SUOLO') then 'uso suolo zona '|| C.sigla
when (A.nome_vincolo='PUC_2015' and B.nome_tavola='VARIANTI_PRG') then 'variante '|| C.sigla
when (A.nome_vincolo='VINCOLI_AMBIENTALI' and B.nome_tavola='DLGS_42_2004_ART_142_LETT_G') or (A.nome_vincolo='PRG' and B.nome_tavola='LOTTIZZAZIONI') or (A.nome_vincolo='PIANO_DI_BACINO' and B.nome_tavola='AMBITO') then C.sigla
else 'zona '|| C.sigla
end AS testo,

C.descrizione  as descrizione_zona,A.ordine as ordine_v,B.ordine as ordine_t,C.ordine as ordine_z,gruppo 
FROM 
cdu.mappali INNER JOIN vincoli.vincolo A ON(vincolo=A.nome_vincolo) 
INNER JOIN vincoli.tavola B on (vincolo=B.nome_vincolo AND tavola = B.nome_tavola) 
INNER JOIN vincoli.zona C on(vincolo=C.nome_vincolo AND tavola=C.nome_tavola AND zona=nome_zona) 
INNER JOIN nct.sezioni D USING(sezione)
WHERE pratica=?
ORDER BY ordine_v,ordine_t,ordine_z
EOT;
$ris=$db->fetchAll($sql,Array($idPratica));

for($i=0;$i<count($ris);$i++){
	$r=$ris[$i];
	$mappali[$r["key"]]["sezione"]=$r["sezione"];
	$mappali[$r["key"]]["foglio"]=$r["foglio"];
	$mappali[$r["key"]]["mappale"]=$r["mappale"];
	if(!is_array($mappali[$r["key"]]["piani"])) $mappali[$r["key"]]["piani"]=Array();
	$r["perc_area"]=($r["perc_area"]==100)?(''):('in parte');
	//if(in_array($r["gruppo"],Array('1','2'))){
        $mappali[$r["key"]]["piani"][]=Array(
                "vincolo"=>$r["descrizione_vincolo"],
                "tavola"=>$r["descrizione_tavola"],
                "zona"=>$r["descrizione_zona"],
                "sigla"=>$r["sigla"],
				"testo"=>$r["testo"],
                "percentuale"=>$r["perc_area"]
        );
	//}
	
}

$customData["mappali"]=array_values($mappali);	

/*$sql="SELECT * FROM stp.normativa_pianodibacino WHERE pratica=?";
$ris=$db->fetchAll($sql,Array($idPratica));
$customData["normativa_pianodibacino"]=$ris;
$sql="SELECT * FROM stp.normativa_prg WHERE pratica=?";
$ris=$db->fetchAll($sql,Array($idPratica));
$customData["normativa_prg"]=$ris;
$sql="SELECT * FROM stp.normativa_ptcp WHERE pratica=?";
$ris=$db->fetchAll($sql,Array($idPratica));
$customData["normativa_ptcp"]=$ris;
$sql="SELECT * FROM stp.normativa_vincoli WHERE pratica=?";
$ris=$db->fetchAll($sql,Array($idPratica));
$customData["normativa_vincoli"]=$ris;
*/
?>
