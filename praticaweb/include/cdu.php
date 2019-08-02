<?php
$idPratica=(defined('FIELDS_LIST'))?(0):($this->pratica);

$sql="SELECT A.*,B.dovuto as diritti_segreteria FROM cdu.richiesta A inner join cdu.diritti_segreteria B using(pratica) WHERE pratica=?";
$ris=$db->fetchAll($sql,Array($idPratica));
array_walk_recursive($ris, 'decode');
$customData["cdu_richiesta"]=$ris;
/*$sql=<<<EOT
SELECT DISTINCT
pratica,foglio||','||mappale as key,'' as sezione,foglio,mappale,vincolo,tavola,zona,perc_area,C.sigla,A.descrizione as descrizione_vincolo,B.descrizione as descrizione_tavola,C.descrizione  as descrizione_zona,A.ordine as ordine_v,B.ordine as ordine_t,C.ordine as ordine_z 
FROM 
cdu.mappali INNER JOIN vincoli.vincolo A ON(vincolo=A.nome_vincolo) 
INNER JOIN vincoli.tavola B on (vincolo=B.nome_vincolo AND tavola = B.nome_tavola) 
INNER JOIN vincoli.zona C on(vincolo=C.nome_vincolo AND tavola=C.nome_tavola AND zona=nome_zona) 
WHERE pratica=? ORDER BY ordine_v,ordine_t,ordine_z
EOT;

$sql = <<<EOT
WITH vincoli_puc as (
SELECT 
pratica,sezione,foglio,mappale,array_to_string(array_agg(A.descrizione || ' - ' ||C.descrizione),'\n') as puc
FROM 
cdu.mappali INNER JOIN vincoli.vincolo A ON(vincolo=A.nome_vincolo) 
INNER JOIN vincoli.tavola B on (vincolo=B.nome_vincolo AND tavola = B.nome_tavola) 
INNER JOIN vincoli.zona C on(vincolo=C.nome_vincolo AND tavola=C.nome_tavola AND zona=nome_zona) 
WHERE pratica=? AND B.cdu = 1 AND vincolo = 'PUC' --AND B.tipo='sug' 
GROUP BY 1,2,3,4
),
vincoli_ptcp as (
SELECT 
pratica,sezione,foglio,mappale,array_to_string(array_agg(B.descrizione || ' - ' ||C.descrizione),'\n') as ptcp
FROM 
cdu.mappali INNER JOIN vincoli.vincolo A ON(vincolo=A.nome_vincolo) 
INNER JOIN vincoli.tavola B on (vincolo=B.nome_vincolo AND tavola = B.nome_tavola) 
INNER JOIN vincoli.zona C on(vincolo=C.nome_vincolo AND tavola=C.nome_tavola AND zona=nome_zona) 
WHERE pratica=? AND vincolo = 'PTCP' 
GROUP BY 1,2,3,4
),
altri_vincoli as (
SELECT 
pratica,sezione,foglio,mappale,array_to_string(array_agg(A.descrizione || ' - ' ||C.descrizione),'\n') as vincoli
FROM 
cdu.mappali INNER JOIN vincoli.vincolo A ON(vincolo=A.nome_vincolo) 
INNER JOIN vincoli.tavola B on (vincolo=B.nome_vincolo AND tavola = B.nome_tavola) 
INNER JOIN vincoli.zona C on(vincolo=C.nome_vincolo AND tavola=C.nome_tavola AND zona=nome_zona) 
WHERE pratica=? AND B.tipo <> 'sug' AND not vincolo in ('PUC','PTCP') 
GROUP BY 1,2,3,4
)
SELECT pratica,sezione,foglio,mappale,puc,ptcp,vincoli 
FROM
vincoli_puc INNER JOIN 
vincoli_ptcp USING(pratica,sezione,foglio,mappale) INNER JOIN
altri_vincoli USING(pratica,sezione,foglio,mappale)

EOT;
*/
$sql = <<<EOT
WITH vincoli_puc as 
( 
SELECT pratica,sezione,foglio,mappale,array_to_string(array_agg(format('(%s) %s - %s',D.perc_area||'%',A.descrizione,C.descrizione)),' ') as puc 
    FROM 
        cdu.mappali D INNER JOIN 
        vincoli.vincolo A ON(vincolo=A.nome_vincolo) 
        INNER JOIN vincoli.tavola B on (vincolo=B.nome_vincolo AND tavola = B.nome_tavola) 
        INNER JOIN vincoli.zona C on(vincolo=C.nome_vincolo AND tavola=C.nome_tavola AND zona=nome_zona) 
    WHERE 
        pratica=? AND B.cdu = 1 AND vincolo  IN ('PUC','PRG','PRG_STELLA','PRG_TESTICO','PUC_CESIO','PRG_CHIUSA') 
    GROUP BY 1,2,3,4 
),
 vincoli_ptcp as 
 ( 
 SELECT pratica,sezione,foglio,mappale,array_to_string(array_agg(format('(%s) %s - %s',D.perc_area||'%',A.descrizione,C.descrizione)),' ') as ptcp 
	FROM 
            cdu.mappali D INNER JOIN 
            vincoli.vincolo A ON(vincolo=A.nome_vincolo) INNER JOIN 
            vincoli.tavola B on (vincolo=B.nome_vincolo AND tavola = B.nome_tavola) INNER JOIN 
            vincoli.zona C on(vincolo=C.nome_vincolo AND tavola=C.nome_tavola AND zona=nome_zona) 
	WHERE 
            pratica=? AND vincolo = 'PTCP' GROUP BY 1,2,3,4 
), 
altri_vincoli as 
( 
SELECT pratica,sezione,foglio,mappale,array_to_string(array_agg(format('(%s) %s - %s',D.perc_area||'%',A.descrizione,C.descrizione)),' ') as vincoli 
    FROM 
        cdu.mappali D INNER JOIN vincoli.vincolo A ON(vincolo=A.nome_vincolo) 
        INNER JOIN vincoli.tavola B on (vincolo=B.nome_vincolo AND tavola = B.nome_tavola) 
        INNER JOIN vincoli.zona C on(vincolo=C.nome_vincolo AND tavola=C.nome_tavola AND zona=nome_zona) 
    WHERE 
        pratica=? AND B.tipo <> 'sug' AND not vincolo in ('PUC','PTCP') GROUP BY 1,2,3,4 
    ) 
SELECT 
    pratica,sezione,foglio,mappale,puc,ptcp,vincoli 
FROM 
    vincoli_puc INNER JOIN vincoli_ptcp USING(pratica,sezione,foglio,mappale) 
    INNER JOIN altri_vincoli USING(pratica,sezione,foglio,mappale) 
EOT;

$ris=$db->fetchAll($sql,Array($idPratica,$idPratica,$idPratica));
$mappali = $ris;
/*for($i=0;$i<count($ris);$i++){
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
	
}*/

//echo "<p>$sql</p>";
$customData["mappali"]=$mappali;	
?>
