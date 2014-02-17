<?php
//error_reporting(E_ALL);
$customData["richiedente"]=$customData["richiedenti"];
$dbh=utils::getDb();
$sql="SELECT A.pratica,C.* FROM pe.avvioproc A LEFT JOIN pe.vincoli B USING(pratica) LEFT JOIN vincoli.descrizione_vincoli C ON (nome_vincolo=vincolo AND nome_tavola=tavola AND nome_zona=zona)  WHERE pratica=? AND nome_vincolo ILIKE ? and nome_tavola ILIKE ?";
$vincoli=Array(
    // (P.R.I.S.)
    "vincoli_pris"=>Array($pratica,"PRIS","%"),
    //(PIANI DI BACINO - Fasce di inondabilità)
    "vincoli_pdb_inondabilita"=>Array($pratica,"PDB","TAV_9"),
    //(PIANI DI BACINO - Suscettività al Dissesto di versante)
    "vincoli_pdb_dissesto"=>Array($pratica,"PDB","TAV_8"),
    //(P.T.C.P. - Assetto Geomorfologico)
    "vincoli_ptcpg"=>Array($pratica,"PTCP","ASSETTO_GEOMORFOLOGICO"),
    //(P.T.C.P. - Assetto Insediativo)
    "vincoli_ptcpi"=>Array($pratica,"PTCP","ASSETTO_INSEDIATIVO"),
    // (P.T.C.P. - Assetto Vegetazionale)
    "vincoli_ptcpv"=>Array($pratica,"PTCP","ASSETTO_VEGETAZIONALE"),
    //(P.U.C.)
    "vincoli_puc"=>Array($pratica,"PUC","%"),
);

foreach($vincoli as $key=>$params){
    $sth=$dbh->prepare($sql);
    if(!$sth->execute($params)){
         $Errors[$key]=$sth->errorInfo();
    }
    else{
        $customData[$key]=$sth->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>