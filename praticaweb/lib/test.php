<?php

$xmlMail = <<<EOT
<messaggioIn>
    <docId></docId>
    <annoProt>2019</annoProt>
    <numProt>16789</numProt>
    <mittenteMail>protocollo@pec.comune.rapallo.ge.it</mittenteMail>
    <oggettoMail>Prova Invio Pec da WSPOSTA_WEB</oggettoMail>
    <testoMail>Prova GisWeb Invio mail</testoMail>
    <destinatariMail>
        <destinatarioMail>carbone.marco@pec.it</destinatarioMail>
        <destinatarioMail>amministrazione@pec.gisweb.it</destinatarioMail>
    </destinatariMail>
    <utente></utente>
    <ruolo></ruolo>
    <invioInteroperabile>S</invioInteroperabile>
</messaggioIn>
EOT;


$xmlMail = <<<EOT
<messaggioIn>
    <docId></docId>
    <annoProt>2019</annoProt>
    <numProt>16789</numProt>
    <mittenteMail>protocollo@pec.comune.rapallo.ge.it</mittenteMail>
    <oggettoMail>TEST GISWEB</oggettoMail>
    <testoMail>iwniofweniw &lt; kq &amp;</testoMail>
    <destinatariMail>
        <destinatarioMail>carbone.marco@pec.it</destinatarioMail><destinatarioMail>amministrazione@pec.gisweb.it</destinatarioMail>
    </destinatariMail>
    <utente></utente>
    <ruolo></ruolo>
    <invioInteroperabile>S</invioInteroperabile>
</messaggioIn>

EOT;

$xmlVerifica = <<<EOT
<messaggioIn>
    <docId></docId>
    <annoProt>2019</annoProt>
    <numProt>46547</numProt>
    <utente></utente>
    <ruolo></ruolo>    
</messaggioIn>
EOT;

define('DATA_DIR','/data/rapallo/pe/');
define('APPS_DIR','/apps/praticaweb-2.1/');

require_once "../../config.php";
require_once "../../config.protocollo.php";
require_once "wsProtocollo.class.php";
require_once LIB."utils.class.php";


//require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
//require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoapmime.php";


// RICHIESTA PROTOCOLLO OUT
/*
$pr=63645;
$idCom=6;

$ws = new wsProtocollo();
$res = $ws->richiediProtOut($pr,$idCom);
//print_r($ws);
print_r($res);
*/

// INFO PROTOCOLLO
/*
$prot = "16745";
$anno = "2019";

$ws = new nusoap_client_mime(WSPROT_URL,'wsdl');
$login = SERVICE_LOGIN;

$a = $ws->call("infoProtocollo",Array($login,$prot,$anno));
print $a;
*/
/*
//INVIO PEC
$idCom = 7;
$ws = new wsMail();
$res = $ws->inviaPec($idCom);
print_r($res);
*/

// RICEVUTA INVIO PEC

$dbh = utils::getDb();
$sql = "SELECT *,date_part('year',data_protocollo) as anno from pe.comunicazioni where coalesce(protocollo,'')<>'' order by 1 desc";
$stmt = $dbh->prepare($sql);
if($stmt->execute()){
    $res = $stmt->fetchAll();
}
else{
    die("Errore nella query $sql\n");
}
for($i=0;$i<count($res);$i++){
$prot=$res[$i]["protocollo"];
$anno=$res[$i]["anno"];
$id = $res[$i]["id_comunicazione"];
$xmlVerifica = <<<EOT
<messaggioIn>
    <docId>$id</docId>
    <annoProt>$anno</annoProt>
    <numProt>$prot</numProt>
    <utente></utente>
    <ruolo></ruolo>
</messaggioIn>
EOT;

    echo "Prot $prot anno $anno :\n";
    $wsClient =  new nusoap_client(WSMAIL_URL,false,false, false, false, false, 0, 180);
    $wsClient->soap_defencoding = 'UTF-8';
    $wsClient->decode_utf8 = false;
    $codAOO = "c_h183";
    $response = $wsClient->call("VerificaInvio",Array("strXML"=>$xmlVerifica,"CodiceAmministrazione"=>SERVICE_LOGIN,"CodiceAOO"=>$codAOO));

    $xml = simplexml_load_string($response);
    $json = json_encode($xml);
    $rr = json_decode($json,TRUE);
    if(array_key_exists("accettazioni",$rr) && is_array($rr["accettazioni"]) && $rr["numAccettazioni"]>0){
       echo "\tOK\n";
    }
    else{
        print_r($rr);
        $handle = fopen('php://stdin','r');
        $line=fgets($handle);
        fclose($handle);
    }
}

 ?>
 
