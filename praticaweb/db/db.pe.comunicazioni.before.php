<?php
require_once DATA_DIR."config.protocollo.php";
require_once LOCAL_LIB."wsProtocollo.class.php";
$dbh = utils::getDB();
$prot = 1;
if(!$protocollata){
	//echo "<p>Comunicazione $id della pratica $idpratica da Protocollare</p>";
	$ws = new wsProtocollo('U','PEC');
	$res = $ws->richiediProtOut($idpratica,$id);
        if ($res["success"]==1){
	    $result =$res["result"];
	    $sql = "UPDATE pe.comunicazioni SET protocollo=?,data_protocollo=? WHERE id = ?;";
	    $stmt = $dbh->prepare($sql);
            if(!$stmt->execute(Array($result["protocollo"],$result["data_protocollo"],$id))){
		echo "<h3 style='color:red; font-weight:bold;'>Errore nell'aggiornamento del protocollo della comunicazione $id</h3>";
		$prot = 0;
            }
        }
	else{
	    echo "<h3 style='color:red; font-weight:bold;'>Errore nella protocollazione della comunicazione $id</h3>";
	    $prot = 0;

	}
}

$sql = "SELECT id_comunicazione FROM pe.comunicazioni WHERE id = ?;"; 
$stmt = $dbh->prepare($sql);
$stmt->execute(Array($id));
$inviata = $stmt->fetchColumn();

if(!$inviata and $prot){
//	echo "<p>Comunicazione $id della pratica $idpratica da Inviare</p>"; 
        $dataInvio = date('d/m/Y');
        if(!$_REQUEST["oggetto"]){
	    $sql = "UPDATE pe.comunicazioni SET oggetto = ? WHERE id = ?;";
	    $oggetto = "Comunicazione Protocollo ".$result["protocollo"]." del $dataInvio";
            //print "<h3>$oggetto</h3>";
	    $stmt = $dbh->prepare($sql);
            if(!$stmt->execute(Array($oggetto,$id))){
	        echo "<h3 style='color:red; font-weight:bold;'>Errore nell'aggiornamento dell'oggetto della comunicazione $id</h3>";
		
            }

        }
	$ws = new wsMail();
	$res = $ws->inviaPec($id);
        //print_array($res);
	if($res["success"]==1){
	    $idComun = $res["descrizione"];
	    $dataInvio = date('d/m/Y');
	    $sql = "UPDATE pe.comunicazioni SET id_comunicazione=?,data_invio=? WHERE id = ?;";
	    $stmt = $dbh->prepare($sql);
            if(!$stmt->execute(Array($idComun,$dataInvio,$id))){
		echo "<h3 style='color:red; font-weight:bold;'>Errore nell'aggiornamento dell'invio della comunicazione $id</h3>";
		
            }
	    else{
                //echo "<h3 style='color:red; font-weight:bold;'>Aggiornamento dell'invio della comunicazione $id OK</h3>";
            }

	}
	else{
	    echo "<h3 style='color:red; font-weight:bold;'>Errore nell' invio della comunicazione $id</h3>";
	    $prot = 0;

	}
}
?>
