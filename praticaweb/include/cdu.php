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
$sql= <<<EOT
WITH A AS(
SELECT * FROM cdu.mappali order by pratica,coalesce(nullif(regexp_replace(foglio, '[^0-9]+', '', 'g'),''),'0')::integer,coalesce(nullif(regexp_replace(mappale, '[^0-9]+', '', 'g'),''),'0')::integer
),
BB AS(
select A.pratica,Z.protocollo,Z.data,A.foglio,B.descrizione,array_to_string(array_agg(mappale),',') as mappali from cdu.richiesta Z inner join  A USING(pratica) left join vincoli.zona B ON(vincolo=nome_vincolo AND tavola=nome_tavola AND zona=nome_zona) inner join vincoli.vincolo C USING(nome_vincolo) WHERE tavola='INCENDI' AnD pratica= ?group by 1,2,3,4,5 order by pratica,foglio 
)
select pratica,descrizione as nome_incendio,array_to_string(array_agg(format('al foglio %s mappali %s,',foglio,mappali)),' ') as particelle from BB group by 1,2;
EOT;
$stmt = $dbh->prepare($sql);
if ($stmt->execute(Array($idPratica))){
    $res = $stmt->fetchAll();
    if (count($res)){
        for($i=0;$i<count($res);$i++){
            $incendi[] = Array("particelle"=>$res[$i]["particelle"],"nome_incendio"=>$res[$i]["particelle"]);
        }
    }
    else{
        $incendi = Array(
            "che il terreno di cui ai mappali sopra citati non RISULTA percorso dal fuoco."
        );
    }
    $customData["incendi_cdu"] =$incendi;
}
$sql=<<<EOT
select DISTINCT A.pratica,D.titolo,norma,articolo,format('art_%s.docx',articolo::varchar) as file_name from cdu.richiesta A inner join cdu.mappali B USING(pratica) INNER JOIN cdu.vincoli_norme C USING(vincolo,tavola,zona) INNER JOIN cdu.normativa D ON (id_normativa=C.id) order by pratica,articolo
EOT;
$stmt = $dbh->prepare($sql);
//if ($stmt->execute(Array($idPratica))){
    require_once APPS_DIR."plugins/openTbs/tbs_class_php5.php";
    require_once APPS_DIR."plugins/openTbs/tbs_plugin_opentbs.php";
    $TBS = new clsTinyButStrong; // new instance of TBS
    $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
    $TBS->LoadTemplate($this->modelliDir."NORMATIVA CAMOGLI.docx",OPENTBS_DEFAULT);
    $norme[]=$TBS->GetBlockSource("art25", FALSE, FALSE);
    $norme[]=$TBS->GetBlockSource("art27", FALSE, FALSE);
    //$res = $stmt->fetchAll();
    /*for($i=0;$i<count($res);$i++){
        $file="";
        $norme[]=$file;
    }*/
    $customData["normativa"] =$norme;
//}
print_array($customData);
?>