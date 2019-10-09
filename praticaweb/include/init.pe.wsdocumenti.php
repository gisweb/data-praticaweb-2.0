<?php
require_once DATA_DIR."config.ads.php";
require_once LOCAL_LIB."WSSoapClient.php";
define('TEMPLATE_FILE',DATA_DIR."praticaweb/scripts/template.php");
$client = new SoapClient(WSDL_LOGIN_URL);
require_once TEMPLATE_FILE;
/*function print_debug($t="",$file=NULL){
		if (!defined("DEBUG_DIR")) {
			define("DEBUG_DIR",'/data/savona/pe/praticaweb/scripts/');
		}
        $uid="";
        $data=date('j-m-y');
        $ora=date("H:i:s");
        if (!$file) $nomefile=DEBUG_DIR.$uid."standard.debug";
        else
            $nomefile=DEBUG_DIR.$uid.$file.".debug";
        $size=(file_exists($nomefile))?(filesize($nomefile)):(0);

        $f=($size>1000000)?(fopen($nomefile,"w+")):(fopen($nomefile,"a+"));
        if (!$f) die("<p>Impossibile aprire il file $nomefile</p>");

        if (is_array($t)||is_object($t)){
            ob_start();
            print_r($t);
            $out=ob_get_contents ();
            ob_end_clean();
            if (!fwrite($f,"\n$data\t$ora\t --- STAMPA DI UN ARRAY ---\n\t$out")) echo "<p>Impossibile scrivere sul file $nomefile </p>";
            fclose($f);
        }
        else{
            if (!fwrite($f,"\n$data\t$ora\n\t".$t)) echo "<p>Impossibile scrivere sul file $nomefile </p>";
            else
                fclose($f);
        }

}
*/
function getDocumentiProtocollati($cl,$template,$strDST,$user,$fascicolo,$anno,$oggetto){

	$header = new SoapHeader(WSDL_PROTEXT_URL,'Authorization',"Basic ".base64_encode(SERVICE_USER.":".SERVICE_PASSWD));
	$cl->__setSoapHeaders($header);
	$data = date('d/m/Y');

	$xmlData = sprintf($template,$anno,$fascicolo,$user,$oggetto,$data);
	$xml = new SoapVar($xmlData,XSD_ANYXML,null,null,'xml');
	$res = $cl->getDocumentiProtocollati(Array("user"=>SERVICE_USER,"DST"=>$strDST,"xml"=>$xml));
	$r = json_decode(json_encode($res),true);

	$xml = simplexml_load_string($r["return"]);
   
	if($xml===FALSE){
		return Array("success"=>0,"data"=>Array(),"message"=>"NO XML Response");
	}
	
	$r = json_decode(json_encode($xml),true);
	
	if(!in_array("DOCUMENTO",array_keys($r))){ 
        return Array("success"=>1,"data"=>Array(),"message"=>"Nessun documento trovato");
    }
    
	$docMax = count($r["DOCUMENTO"]);
    if ($docMax > 50 ) $docMax = 50;
    if($docMax > 1){
        for($k=0;$k<$docMax;$k++){
            $r["DOCUMENTO"][$k]["data"] = str_replace("/","-",$r["DOCUMENTO"][$k]["data"]);
        }
    }
	if(count($r["DOCUMENTO"])==1) {
        $dataRes[0] = $r["DOCUMENTO"];
        
    }
    else{
        $dataRes = $r["DOCUMENTO"];
    }
	return Array("success"=>1,"data"=>$dataRes,"message"=>"");
}

function getDocumentiNonProtocollati($cl,$template,$strDST,$user,$fascicolo,$anno,$oggetto){

	$header = new SoapHeader(WSDL_PROTEXT_URL,'Authorization',"Basic ".base64_encode(SERVICE_USER.":".SERVICE_PASSWD));
	$cl->__setSoapHeaders($header);
	$data = date('d/m/Y');

	$xmlData = sprintf($template,$anno,$fascicolo,$user,$oggetto,$data);
	$xml = new SoapVar($xmlData,XSD_ANYXML,null,null,'xml');
	$res = $cl->getDocumentiNonProtocollati(Array("user"=>SERVICE_USER,"DST"=>$strDST,"xml"=>$xml));
	$r = json_decode(json_encode($res),true);

	$xml = simplexml_load_string($r["return"]);
	if($xml===FALSE){
		return Array("success"=>0,"data"=>Array(),"message"=>"NO XML Response");
	}
	$r = json_decode(json_encode($xml),true);
	if(!in_array("DOCUMENTO",array_keys($r))) return Array("success"=>1,"data"=>Array(),"message"=>"Nessun documento trovato");
	if(count($r["DOCUMENTO"])==1) $r["DOCUMENTO"] = Array($r["DOCUMENTO"]);

	return Array("success"=>1,"data"=>$r["DOCUMENTO"],"message"=>"");
}

function getInfoDocumento($cl,$template,$strDST,$user,$idDoc){
	$header = new SoapHeader(WSDL_PROTEXT_URL,'Authorization',"Basic ".base64_encode(SERVICE_USER.":".SERVICE_PASSWD));
	$cl->__setSoapHeaders($header);
	$xmlData = sprintf($template,$idDoc,$user);
	$xml = new SoapVar($xmlData,XSD_ANYXML,null,null,'xml');
	$res = $cl->getDocumento(Array("user"=>SERVICE_USER,"DST"=>$strDST,"xml"=>$xml));
	$r = json_decode(json_encode($res),true);

	$xml = simplexml_load_string($r["return"]);
	if($xml===FALSE){
		return Array("success"=>0,"data"=>Array(),"message"=>"NO XML Response");
	}
	$r = json_decode(json_encode($xml),true);

	$result = Array(
		"data" => $r["DOC"]["DATA"],
		"oggetto" => $r["DOC"]["OGGETTO"],
		"protocollo" => $r["DOC"]["NUMERO"],
		"id_documento" => $r["FILE_PRINCIPALE"]["FILE"]["ID_DOCUMENTO"],
		"id_oggetto" => $r["FILE_PRINCIPALE"]["FILE"]["ID_OGGETTO_FILE"],
		"documento" => $r["FILE_PRINCIPALE"]["FILE"]["FILENAME"],
		"soggetti" => ($r["RAPPORTI"]["RAPPORTO"]["DENOMINAZIONE"])?($r["RAPPORTI"]["RAPPORTO"]["DENOMINAZIONE"]):($r["RAPPORTI"]["RAPPORTO"]["COGNOME_NOME"]),
		"direzione" => ""
	);


	return Array("success"=>1,"data"=>$result,"message"=>"");
}

$res = $client->login(Array("strCodEnte"=>CODICE_ENTE,"strUserName"=>SERVICE_USER,"strPassword"=>SERVICE_PASSWD));
$r = json_decode(json_encode($res),true);

$strDST=$r["LoginResult"]["strDST"];
$clientDocs = new SoapClient(WSDL_PROTEXT_URL,array("login"=>SERVICE_USER,"password"=>SERVICE_PASSWD,'trace' => true, 'exceptions' => true));

$auth = array(
    'Username' => SERVICE_USER,
    'Password' => SERVICE_PASSWD
);

$result = getDocumentiProtocollati($clientDocs,$xmlTemplate["documenti_protocollati"],$strDST,SERVICE_USER,$fascicolo,$anno,"%");
if ($result["success"]!==1){
	die("Nessun Risposta");
}


//if (!count($result["data"])) print "Nessun documento protocollato per il fascicolo $fascicolo dell'anno $anno\n";
//print_r($result);
for($i=0;$i<count($result["data"]);$i++){
	$idDoc = $result["data"][$i]["ID_DOCUMENTO"];
    
	$res = getInfoDocumento($clientDocs,$xmlTemplate["documento"],$strDST,SERVICE_USER,$idDoc);
	if ($res["success"]!==1){
		die("Nessun Risposta");
	}
	else{
		//print_debug($res,"PROT-".$idDoc);
		$wsData[] = $res["data"];
	}
	
}
$result = getDocumentiNonProtocollati($clientDocs,$xmlTemplate["documenti_protocollati"],$strDST,SERVICE_USER,$fascicolo,$anno,"%");
if ($result["success"]!==1){
	die("Nessun Risposta");
}

//if (!count($result["data"])) print "Nessun documento non protocollato per il fascicolo $fascicolo dell'anno $anno\n";
//print_r($result);
for($i=0;$i<count($result["data"]);$i++){
	$idDoc = $result["data"][$i]["ID_DOCUMENTO"];
    
	//$clientDocs->__setSoapHeaders($header);
	$res = getInfoDocumento($clientDocs,$xmlTemplate["documento"],$strDST,SERVICE_USER,$idDoc);
	if ($res["success"]!==1){
		die("Nessun Risposta");
	}
	else{
		//print_debug($res,"NONPROT-".$idDoc);
		$wsData[] = $res["data"];
	}
	
}
?>
