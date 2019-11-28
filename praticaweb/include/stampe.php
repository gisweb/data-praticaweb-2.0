<?php
//error_reporting(E_ALL);
$customData["richiedente"]=$customData["richiedenti"];
$dbh=utils::getDb();
$sql="SELECT A.pratica,array_to_string(array_agg(C.sigla_zona),', ') as zona FROM pe.avvioproc A LEFT JOIN pe.vincoli B USING(pratica) LEFT JOIN vincoli.descrizione_vincoli C ON (nome_vincolo=vincolo AND nome_tavola=tavola AND nome_zona=zona)  WHERE pratica=? AND nome_vincolo ILIKE ? and nome_tavola ILIKE ? GROUP BY pratica";
$vincoli=Array(
    // (P.R.I.S.)
    "zone_piano_1"=>Array($pratica,"PRIS","%"),
    //(PIANI DI BACINO - Fasce di inondabilità)
    "zone_piano_2"=>Array($pratica,"PDB","TAV_9"),
    //(PIANI DI BACINO - Suscettività al Dissesto di versante)
    "zone_piano_8"=>Array($pratica,"PDB","TAV_8"),
    //(P.T.C.P. - Assetto Geomorfologico)
    "zone_piano_3"=>Array($pratica,"PTCP","ASSETTO_GEOMORFOLOGICO"),
    //(P.T.C.P. - Assetto Insediativo)
    "zone_piano_4"=>Array($pratica,"PTCP","ASSETTO_INSEDIATIVO"),
    // (P.T.C.P. - Assetto Vegetazionale)
    "zone_piano_5"=>Array($pratica,"PTCP","ASSETTO_VEGETAZIONALE"),
    //(P.U.C.)
    "zone_piano_6"=>Array($pratica,"PUC","%"),
    "zone_piano_7"=>Array($pratica,"PUC","%")
);

foreach($vincoli as $key=>$params){
    $sth=$dbh->prepare($sql);
    if(!$sth->execute($params)){
         $Errors[$key]=$sth->errorInfo();
    }
    else{
        $customData[$key]=$sth->fetchColumn(1);
    }
}

if($_REQUEST["STAMPE_ORDINATIVO_PAGOPA"]==1){
    $richiesta = $_REQUEST["codice_richiesta"];
    $sql= "SELECT coalesce(SUM(importo),0) as totale FROM ragioneria.importi_dovuti WHERE pratica=? and codice_richiesta=?";
    $stmt = $dbh->prepare($sql);
    $stmt->execute(Array($pratica,$richiesta));
    $totale=$stmt->fetchColumn();
    $sql = <<<EOT
SELECT A.pratica,A.causale,B.nome as pagamento_tipo,B.capitolo as pagamento_capitolo,A.quantita as pagamento_numero,A.importo as pagamento_importo,A.importo as importo_totale
FROM ragioneria.importi_dovuti A left join ragioneria.e_codici_pagamento B ON (A.tipo=B.codice) WHERE pratica=? and codice_richiesta=?;            
EOT;
    $stmt = $dbh->prepare($sql);
    if($stmt->execute(Array($pratica,$richiesta))){
        $customData["pagamenti"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $customData["pagamenti_totale"] = $totale;
    }
}
?>