<?php
/**
 * Created by PhpStorm.
 * User: mamo
 * Date: 01/04/2017
 * Time: 14:36
 */

$idPratica=(defined('FIELDS_LIST'))?(0):($this->pratica);
$dbh= utils::getDb();
$sql=<<<EOT
with A as (
select A.pratica,A.foglio ,A.mappale , format('Zona %s di %s',B.sigla,C.descrizione) as vinc from cdu.mappali A left join vincoli.zona B ON(vincolo=nome_vincolo AND tavola=nome_tavola AND zona=nome_zona) inner join vincoli.vincolo C USING(nome_vincolo) WHERE vincolo in ('PRG','PTCP') AND pratica=? order by pratica,foglio,mappale,vincolo 
),
B AS (select pratica,foglio,mappale,array_agg(vinc) as vinc from A group by 1,2,3 order by 1,2,3),
C as (select pratica,foglio,array_to_string(array_agg(mappale),',') as mappali,vinc from B group by 1,2,4 order by 1,2,4)
select pratica,format('Foglio n. %s Mappali %s',foglio,mappali) as particelle,unnest(vinc) as vincoli from C
EOT;
$stmt = $dbh->prepare($sql);
if ($stmt->execute(Array($idPratica))){
    $res = $stmt->fetchAll();
    for($i=0;$i<count($res);$i++){
        $particella[$res[$i]["particelle"]]["particella"] = $res[$i]["particelle"];
        $particella[$res[$i]["particelle"]]["vincoli"][] = Array("vincolo"=>$res[$i]["vincoli"]);
    }
    $customData["cdu"] = array_values($particella);
}
print_array($customData);
?>