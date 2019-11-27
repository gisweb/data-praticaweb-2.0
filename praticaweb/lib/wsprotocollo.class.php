<?php
if (!defined('DATA_DIR')) define("DATA_DIR",dirname(dirname(dirname(__FILE__))));
require_once DATA_DIR.DIRECTORY_SEPARATOR."config.protocollo.php";
require_once LOCAL_LIB."app.utils.class.php";
require_once LIB."utils.class.php";
require_once LIB."protocollo.halley.class.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoapmime.php";
class protocollo extends HProtocollo{
    var $wsUrl = WS_URL_PROT;
    
    var $data = Array(
        "oggetto"=>"",
        "altri_documenti"=>"",
        "mittente"=>"",
        "destinatari"=>"",
        "codice_amministrazione"=>CODICE_AMMINISTRAZIONE,
        "codice_a00"=>CODICE_A00,
        "codice_titolario"=>CODICE_TITOLARIO,
        "codice_uo"=>CODICE_UO,
        "denominazione_amministrazione"=>DENOMINAZIONE,
        "indirizzo_telematico" => INDIRIZZO_TELEMATICO,
        "id_documento"=>"",
        "nome_documento"=>"",
        "descrizione_documento"=>"",
        "tipo_documento"=>""
    );
    
    function protocolla($mode = 'U', $oggetto, $destinatari = array(), $allegati = array()) {
        $postData = Array();
        if($mode=='TEST') return Array("success"=>1,"message"=>"","protocollo"=>rand(10000,99900),"anno"=>'2019',"data"=>date('d/m/Y',time()));
        $documento = array_shift($allegati);
        $postData["flusso"]=$mode;
        $postData["soggetti"] = ($mode=='U')?($destinatari):($mittenti);
        $postData["oggetto"] = $oggetto;
        $postData["documento"] = $documento;
        $postData["allegati"] = $allegati;
        $auth = base64_encode(IOL_USER.":".IOL_PWD);
        $xxxData = Array("data"=> json_encode($postData));
        $res = utils::curlJsonCall(WS_URL_PROT, $xxxData,Array());
        
        $data = json_encode($res,TRUE);
        if ($data && $data["success"]==1 && $data["NumeroProtocollo"]){
            return Array("success"=>1,"message"=>"","protocollo"=>$data["NumeroProtocollo"],"anno"=>'2019',"data"=>date('d/m/Y',time()));
        }
        return Array("success"=>0,"message"=>$data["message"],"protocollo"=>"","anno"=>"","data"=>"");
    }
    
    //Metodo di protocollazione che sfrutta il WS di IOL
    /*function protocolla($mode = 'U', $oggetto, $mittenti = array(), $destinatari = array(), $allegati = array()) {
        $postData = Array();
		if($mode=='TEST') return Array("success"=>1,"message"=>"","protocollo"=>rand(10000,99900),"anno"=>'2019',"data"=>date('d/m/Y',time()));
        $documento = array_shift($allegati);
        $postData["flusso"]=$mode;
        $postData["soggetti"] = ($mode=='U')?($destinatari):($mittenti);
        $postData["oggetto"] = $oggetto;
        $postData["documento"] = $documento;
        $postData["allegati"] = $allegati;
        $auth = base64_encode(IOL_USER.":".IOL_PWD);
        $res = utils::curlJsonCall($this->wsUrl, Array("data"=>json_encode($postData)),Array("Authorization"=>sprintf("Basic %s",$auth)));
        $data = json_encode($res,TRUE);
        if ($data && $data["success"]==1 && $data["NumeroProtocollo"]){
            return Array("success"=>1,"message"=>"","protocollo"=>$data["NumeroProtocollo"],"anno"=>'2019',"data"=>date('d/m/Y',time()));
        }
        return Array("success"=>0,"message"=>$data["message"],"protocollo"=>"","anno"=>"","data"=>"");
    }*/
/*    
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
    
    function protocolla($mode='U',$oggetto,$mittente,$destinatari,$allegati){
		return Array("success"=>1,"message"=>"","protocollo"=>rand(22300,22600),"anno"=>'2019',"data"=>date('d/m/Y',time()));
        $xmlData = "";
        $res = $this->login();
        if ($res["success"]===1){
            $dst = $res["dst"];
        }
        else{
            return -1;
        }
//       $cl = new nusoap_client_mime(SERVICE_URL,'wsdl');

        $suffix = ($mode=='U')?("OUT"):("IN");
        $this->data["oggetto"] = $oggetto;
		$clientDocs = new SoapClient(
            SERVICE_URL, 
            array(
                'trace' => true, 
                'exceptions' => true,
                'keep_alive' => true,
                'connection_timeout' => 30,
                'cache_wsdl' => WSDL_CACHE_NONE
            )
        );
        if(count($allegati)>0){
            for($i=0;$i<count($allegati);$i++){
				//$cl->addAttachment($allegati[$i]["file"],$allegati[$i]["nome"]);
                //$res = $cl->call('Inserimento',Array(Array(SERVICE_USER,$dst,$allegati[$i]["nome"],base64_encode($allegati[$i]["file"]))));
				//$res = $cl->call('Inserimento',Array(SERVICE_USER,$dst,$allegati[$i]["nome"]));
				$parm = array();
				$parm[] = new SoapVar(SERVICE_USER, XSD_STRING, null, null, 'strUserName' );
				$parm[] = new SoapVar($dst, XSD_STRING, null, null, 'strDST' );
				$parm[] = new SoapVar($allegati[$i]["nome_documento"], XSD_STRING, null, null, 'strDocument' );
				$parm[] = new SoapVar(base64_encode($allegati[$i]["file"]), XSD_BASE64BINARY, null, null, 'objDocument' );
				$res = $clientDocs->Inserimento(new SoapVar($parm, SOAP_ENC_OBJECT,null,null,'Inserimento'));
				//$res = $clientDocs->__soapCall('Inserimento',Array(SERVICE_USER,$dst,$allegati[$i]["nome"],base64_encode($allegati[$i]["file"])));
				
				$res = json_decode(json_encode($res->InserimentoResult),true);
				utils::debugAdmin($res);
				utils::debug(DEBUG_DIR."FILE_PROTOCOLLO.debug",$res,'w');
                //$res = Array("lngErrNumber"=>0,"lngDocID"=>rand(100,999999));
				
                if($res["lngDocID"]){
                    $allegato = $allegati[$i];
                    $allegato["id_documento"] = $res["lngDocID"];
                    $resAllegati[] = $allegato;
                }
				else{
					return Array("success"=>0,"message"=>sprintf("Errore Numero %s nell'inserimento del file %s - %s",$res["lngErrNumber"],$allegati[$i]["nome_documento"],$res["strErrString"]));
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
			utils::debug(DEBUG_DIR."XML_PROTOCOLLO.debug",$xmlData,'w');
			$parm = array();
			$parm[] = new SoapVar(SERVICE_USER, XSD_STRING, null, null, 'strUserName' );
			$parm[] = new SoapVar($dst, XSD_STRING, null, null, 'strDST' );
			$parm[] = new SoapVar($xmlData, XSD_ANYXML, null, null, 'strDocumentInfo' );
			
			$soapVarUser = new SoapVar(SERVICE_USER, XSD_STRING, null, null, 'strUserName' );
			$soapVarDst = new SoapVar($dst, XSD_STRING, null, null, 'strDST' );
			$soapVarXml = new SoapVar($xmlData, XSD_STRING, null, null, 'strDocumentInfo' );
			//utils::debugAdmin($xmlData);return;
			//$res = $clientDocs->Protocollazione(new SoapVar($parm,SOAP_ENC_OBJECT,null,null,'Protocollazione'));
			//utils::debugAdmin($xmlData);
			$postData = Array("strUserName"=>SERVICE_USER,"strDST"=>$dst,"strDocumentInfo"=>$xmlData);
			try{
				
				//$res = $clientDocs->__soapCall('Protocollazione',$parm);
				//$res = $clientDocs->__getTypes();
				$res = $clientDocs->Protocollazione($postData);
				//$res = $clientDocs->Protocollazione(new SoapVar($parm,SOAP_ENC_OBJECT,null,null,'Protocollazione'));
				utils::debugAdmin($postData);return;
			}
			catch (Exception $e){
				utils::debugAdmin($postData);return;
			}

			$res = json_decode(json_encode($res->ProtocollazioneResult),true);
			
			if($res["lngErrNumber"]){
				return Array("success"=>0,"message"=>sprintf("Errore durante la protocollazione numero %s - %s",$res["lngErrNumber"],$res["strErrString"]));
			}
			else{
				return Array("success"=>1,"message"=>"","protocollo"=>$res["lngNumPG"],"anno"=>$res["lngAnnoPG"],"data"=>$res["strDataPG"]);
			}
        }
        else{
			return Array("success"=>0,"message"=>"Errore nella creazione dell'XML per la protocollazione");
		}
		
		return $xmlData;
		
    }
*/    
}
?>
