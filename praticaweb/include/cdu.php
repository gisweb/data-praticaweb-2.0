<?php
$idPratica=(defined('FIELDS_LIST'))?(0):($this->pratica);

$sql="SELECT A.*,B.dovuto as diritti_segreteria FROM cdu.richiesta A inner join cdu.diritti_segreteria B using(pratica) WHERE pratica=?";
$ris=$db->fetchAll($sql,Array($idPratica));
array_walk_recursive($ris, 'decode');
$customData["cdu_richiesta"]=$ris;
$sql=<<<EOT
SELECT DISTINCT
pratica,D.nome||','||foglio||','||mappale as key,D.nome as sezione,foglio,mappale,vincolo,tavola,zona,perc_area,C.sigla,A.descrizione as descrizione_vincolo,B.descrizione as descrizione_tavola,C.descrizione  as descrizione_zona,A.ordine as ordine_v,B.ordine as ordine_t,C.ordine as ordine_z,gruppo 
FROM 
cdu.mappali INNER JOIN vincoli.vincolo A ON(vincolo=A.nome_vincolo) 
INNER JOIN vincoli.tavola B on (vincolo=B.nome_vincolo AND tavola = B.nome_tavola) 
INNER JOIN vincoli.zona C on(vincolo=C.nome_vincolo AND tavola=C.nome_tavola AND zona=nome_zona) 
INNER JOIN nct.sezioni D USING(sezione)
WHERE pratica=? ORDER BY ordine_v,ordine_t,ordine_z
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
                "percentuale"=>$r["perc_area"]
        );
	//}
	
}
$customData["mappali"]=array_values($mappali);	
?>