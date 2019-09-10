<?php
$idPratica=(defined('FIELDS_LIST'))?(0):($this->pratica);

$mappali = Array();
$sql=<<<EOT
SELECT 
D.pratica,D.sezione,D.foglio,D.mappale,D.perc_area as percentuale,
A.nome_vincolo,A.descrizione as vincolo,
B.nome_tavola,B.descrizione as tavola,
C.nome_zona,C.descrizione as zona,sigla,
case when perc_area > 99.5 THEN
format('Insiste interamente in zona %s: %s',coalesce(sigla,''),C.descrizione)
ELSE
format('Insiste parzialmente in zona %s: %s',coalesce(sigla,''),C.descrizione)
END as testo
FROM
cdu.mappali D INNER JOIN vincoli.vincolo A ON(vincolo=A.nome_vincolo)
INNER JOIN vincoli.tavola B on (vincolo=B.nome_vincolo AND tavola = B.nome_tavola) 
INNER JOIN vincoli.zona C on(vincolo=C.nome_vincolo AND tavola=C.nome_tavola AND zona=nome_zona) 
where cdu=1 and pratica=9 order by foglio,mappale
EOT;

$customData["mappali"]=$mappali;	
?>
