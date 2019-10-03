<?php
require_once DATA_DIR."config.protocollo.php";
require_once LOCAL_LIB."wsProtocollo.class.php";
$dbh = utils::getDB();
$ws = new wsMail();
for($i=0;$i<count($arrayData);$i++){
    $d = $arrayData[$i];
    if($d["protocollo"] && $d["id_comunicazione"]){
        $anno = date('Y',strtotime(str_replace('/','-',$d["data_protocollo"])));
        $res = $ws->verificaInvio($d["protocollo"], $anno, $d["id_comunicazione"]);
        if ($res["success"]===1){
            $tmp = Array();
            for($j=0;$j<count($res["accettazione"]);$j++){
                $r = $res["accettazione"][$j];
                $tmp[] = sprintf("Comunicazione accettata il %s con id %s",$r["dataAccettazione"],$r["idRepAccettazione"]);
            }
            $d["accettazione"] = implode("<br>",$tmp);
            $tmp = Array();
            for($j=0;$j<count($res["consegna"]);$j++){
                $r = $res["consegna"][$j];
                $tmp[] = sprintf("Comunicazione consegnata il %s con id %s a %s",$r["dataConsegna"],$r["idRepConsegna"],$r["emailDestinatario"]);
            }
            $d["consegna"] = implode("<br>",$tmp);
        }
        else{
            $d["accettazione"]=" --- ";
            $d["consegna"] = " --- ";
        }
    }
    else{
        $d["accettazione"]=" --- ";
        $d["consegna"] = " --- ";
        $d["data_invio"] = "<b style=color:red>Non Inviata</b>";
    }
    $arrayData[$i] = $d;
}
?>
