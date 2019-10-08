<?php

$appsDir=  getenv('PWAppsDir');
require_once $appsDir."login.php";

$conn = utils::getDb();
$action=(isset($_REQUEST["action"]) && $_REQUEST["action"])?($_REQUEST["action"]):("");
$result=Array("success"=>0,"message"=>"");
switch($action){
    case "richiedi_protocollo":
            $tipo = $_REQUEST["tipo"];
            $dataprot = ($_REQUEST["data_prot"])?($_REQUEST["data_prot"]):(date("d/m/Y"));
            $nominativo = ($_REQUEST["nominativo"])?($_REQUEST["nominativo"]):("");
            $conn = utils::getDb();
            $sql = "SELECT nome FROM pe.e_tipopratica WHERE id = ?;";
            $stmt=$conn->prepare($sql);
            $stmt->execute(Array($tipo));
            $r = $stmt->fetchColumn();
            $oggetto = sprintf("%s presentata il %s da %s",$r,$dataprot,$nominativo);
            $userid = appUtils::getUserId();
            $usr = appUtils::getUserProtocollo($userid);
            $username = $usr["username"];
            $res = appUtils::richiediProtocollo($username,$nominativo,$oggetto);
            if($res["success"]==1){
                $result=Array(
                    "success"=>1,
                    "message"=>"",
                    "data"=>Array("protocollo"=>ltrim($res["protocollo"],"0"),"data_prot"=>$dataprot),
                    "errors"=>$res["error"]
                );
            }
            else{
                $result["message"]=$res["message"];
            }
        break;
    default:
        $result["message"]=($action=="")?("Nessuna azione definita"):("Azione $action non supportata");
        break;
}
header('Content-Type: application/json; charset=utf-8');
print json_encode($result);
?>
