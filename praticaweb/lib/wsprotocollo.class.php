<?php
if (!defined('DATA_DIR')) define("DATA_DIR",dirname(dirname(dirname(__FILE__))));
require_once DATA_DIR.DIRECTORY_SEPARATOR."config.protocollo.php";
require_once LOCAL_LIB."app.utils.class.php";
require_once LIB."utils.class.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoapmime.php";

class protocollo{
    var $data = Array(
        "altri_documenti"=>"",
        "mittente"=>"",
        "destinatari"=>"",
        "codice_amministrazione"=>CODICE_AMMINISTRAZIONE,
        "codice_a00"=>CODICE_A00,
        "codice_titolario"=>CODICE_TITOLARIO,
        "codice_uo"=>CODICE_UO,
        "denominazione_amministrazione"=>DENOMINAZIONE,
        "id_documento"=>"",
        "nome_documento"=>"",
        "descrizione_documento"=>"",
        "tipo_documento"=>""
    );
    
    private function subst($txt,$data){
        foreach($data as $k=>$v){
            $txt = str_replace("%($k)s",htmlspecialchars($v, ENT_XML1, 'UTF-8'),$txt);
        }
        return $txt;
    }
    
    function caricaXML($nome,$data){
        $result = $this->result;
        $fName = TEMPLATE_DIR.$nome.".xml";
        if (file_exists($fName)){
            $f = fopen($fName,'r');
            $tXml = fread($f,filesize($fName));
            fclose($f);
            $xml = $this->subst($tXml,$data);
            $result["success"] = 1;
            $result["result"] = $xml;
            return Array("success"=>1,"result"=>$xml);
        }
        else{
            $result["success"] = -1;
            $result["message"] = "Il file $fName non è stato trovato";
        }
        return $result;
    }
    function login(){
        $cl = new SoapClient(SERVICE_URL,array("trace" => 1, "exception" => 0));
        $res = $cl->Login(Array("strCodEnte"=>CODICE_AMMINISTRAZIONE,"strUserName"=>SERVICE_USER,"strPassword"=>SERVICE_PASSWD));
        $res = json_decode(json_encode($res),TRUE);
        
        if(array_key_exists("LoginResult",$res)){
            if(array_key_exists("lngErrNumber", $res["LoginResult"]) && !$res["LoginResult"]["lngErrNumber"]){
                return Array("success"=>1,"dst"=>$res["LoginResult"]["strDST"]);
            }
            else{
                return Array("success"=>0,"message"=>$res["LoginResult"]["strErrString"]);
            }
        }
        else{
            return Array("success"=>-1);
        }
        unset($cl);
    }
    
    function cercaFascicolo($prot,$anno){
        $res = $this->login();
        if ($res["success"]===1){
            $dst = $res["dst"];
            $cl = new SoapClient(DIZIONARI_URL,array("trace" => 1, "exception" => 0));
            $result = $cl->searchFascicoli(Array("strUserName"=>SERVICE_USER,"strDST"=>$dst,"codiceAOO"=>Codice_A00,"numeroProtocollo"=>$prot,"annoProtocollo"=>$anno));
            return $result;
        }
    }

    function listaFascicoli(){
        $res = $this->login();
        if ($res["success"]===1){
            $dst = $res["dst"];
            $cl = new SoapClient(DIZIONARI_URL,array("trace" => 1, "exception" => 0));
            $result = $cl->listaFascicoli(Array("strUserName"=>SERVICE_USER,"strDST"=>$dst));
            return $result;
        }
    }
    
    function protocolla($mode='U',$mittente,$destinatari,$allegati){
        $xmlData = "";
        $res = $this->login();
        if ($res["success"]===1){
            $dst = $res["dst"];
        }
        else{
            return -1;
        }
        $cl = new nusoap_client_mime(SERVICE_URL,'wsdl');
        if($mode=='U'){
            $fileProt = "PROT-OUT";
            $fileDest = "DESTINATARIO-OUT";
            $fileMitt = "MITTENTE-OUT";
        }
        $suffix = ($mode=='U')?("OUT"):("IN");
        if(count($allegati)>0){
            for($i=0;$i<count($allegati);$i++){
                //$res = $cl->call('Inserimento',Array(SERVICE_USER,$dst,$allegati[$i]["nome_documento"],$allegati[$i]["file"]));
                $res = Array("lngErrNumber"=>0,"lngDocID"=>random_int(100,999999));
                if($res["lngErrNumber"]===0){
                    $allegato = $allegati[$i];
                    $allegato["id_documento"] = $res["lngDocID"];
                    $resAllegati[] = $allegato;
                }
            }
            $allegato = array_shift($resAllegati);
            $this->data = array_merge($this->data,$allegato);
            for($i=0;$i<count($resAllegati);$i++){
                $res = $this->caricaXML("DOCUMENTO",$resAllegati[$i]);
                if($res["success"]==1){
                    $this->data["altri_documenti"].=$res["result"];
                }
            }
        }
        for($i=0;$i<count($mittente);$i++){
            $res = $this->caricaXML("MITTENTE-".$suffix,$mittente[$i]);
            if($res["success"]==1){
                $this->data["mittente"].=$res["result"];
            }
        }
        for($i=0;$i<count($destinatari);$i++){
            $res = $this->caricaXML("DESTINATARIO-".$suffix,$destinatari[$i]);
            if($res["success"]==1){
                $this->data["mittente"].=$res["result"];
            }
        }
        $res = $this->caricaXML("PROT-".$suffix,$this->data);
        if($res["success"]==1){
            $xmlData=$res["result"];
        }
        return $xmlData;
    }
    
}
?>
