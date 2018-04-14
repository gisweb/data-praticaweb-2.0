<?php
$baseDir = dirname(dirname(dirname(__FILE__)));

require_once $baseDir.DIRECTORY_SEPARATOR."config.protocollo.php";

require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoapmime.php";

require_once LIB."tbs_class.php";

function mergeFields($T,$data){
    foreach($data as $key=>$value){
        if(is_array($value)){
                $T->MergeBlock($key, $value);
        }
        else{
                $T->MergeField($key, $value);
        }
    }
}

function getInfoDocumento($filename){
    $result = Array(
        "success" => "",
        "message" => "",
        "file" => "",
        "data" => Array(
            "nomefile" => "",
            "mimetype" => "",
            "filesize" => "",
            "descrizione" => "",
        )
    );
    if(!file_exists($filename)){
        $result["success"] = -1; 
        $result["message"] = sprintf("Impossibile trovare il file %s.",$filename);
    }
    else{
        $result["success"] = 1; 
        $f = fopen($filename,'r');
        $str = fread($f, filesize($filename));
        fclose($f);
        $result["file"] = base64_encode($str);
        $result["data"] = Array(
            "nomefile" => pathinfo($filename,PATHINFO_BASENAME),
            "mimetype" => mime_content_type($filename),
            "filesize" => filesize($filename),
            "descrizione" => "",
        );
    }
    return $result;
}

class wsProtocollo{

    var $params;
    var $dbh;
    var $wsUrl;
    var $login;
    var $service;
    var $wsClient;
    var $result;
    var $modalita;
    var $direzione;
    var $data;
    var $allegati;
    

    /******************************************************************************************************************/

    /******************************************************************************************************************/
    function __construct($direzione='E',$servizio,$modo='PEC',$params){
        $this->wsUrl = WSPROT_URL;
        $this->login = SERVICE_LOGIN;
        $this->service = "SicraWeb";
        $this->wsClient =  new nusoap_client_mime($this->wsUrl,'wsdl');
        $this->direzione = $direzione;
        $this->modalita = $modo;
        $this->codAoo = $params["AOO"]["Codice"];
        $this->denomAoo = $params["AOO"]["Denominazione"];
        $this->codAmm = $params["Amministrazione"]["Codice"];
        $this->denomAmm = $params["Amministrazione"]["Denominazione"];
        $this->codUO = $params["UO"][strtoupper($servizio)]["Codice"];
        $this->denomUO = $params["UO"][strtoupper($servizio)]["Denominazione"];
        $this->data = Array(
            "oggetto" => "",
            "mittente" => Array(),
            "mittenti" => Array(),
            "allegato" => Array(),
            "allegati" => Array(),
            "identificatore" => Array(
                Array(
                    "codamm" => $this->codAmm,
                    "codaoo" => $this->codAoo,
                    "id" => "",
                    "data_registrazione" => date("d/m/Y"),
                    "flusso" => $this->direzione,
                )
            ),
            "amministrazione" => Array(
                Array(
                    "codice" => $this->codAmm,
                    "denominazione" => $this->denomAmm
                )
            ),
            "uo" => Array(Array(
                "codice" => $this->codUO,
                "denominazione" => $this->denomUO,
                "id" => $this->codUO
            )),
            "aoo" => Array(Array(
                "codice" => $this->codAoo,
                "denominazione" => $this->denomAoo,
            ))
        );
        
        
        $this->result =  $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        ); 
    }
    
    function initDati(){
        
    }
    function infoProtocollo($prot,$anno){
        $result = $this->result;
        $a = $this->wsClient->call("infoProtocollo",Array($this->login,$prot,$anno));
        $xml = simplexml_load_string($a);
        //print_r($a);
        $json = json_encode($xml);
        $response = json_decode($json,TRUE);
        $result["response"] = $a;
        $result["result"] = $response;
        $result["success"] = 1;
        return $result;
    }
    
    function caricaXML($nome){
        $result = $this->result;
        $fName = TEMPLATE_DIR.$nome.".xml";
        if (file_exists($fName)){
            $TBS = new clsTinyButStrong;                                             // default behavior
            $TBS->LoadTemplate($fName);
            mergeFields($TBS,$this->data);
            $TBS->Show(TBS_NOTHING);                                                // terminate the merging without leaving the script nor to display the result
            $xml = $TBS->Source;
            return Array("success"=>1,"result"=>$xml);
        }
        else{
            $result["success"] = -1;
            $result["message"] = "Il file $fName non è stato trovato";
        }
        return $result;
    }
    function setData($oggetto,$soggetti){
        $this->data["oggetto"] = $oggetto;
        $this->data["soggetto"] = Array($soggetti[0]);
        $this->data["soggetti"] = $soggetti;
        
    }
    function inserisciDocumento($file){
        $result = $this->result;
        $res = getInfoDocumento($file);
        if ($res["success"]==1){
 
            $err = $this->wsClient->getError();
            if ($err) {
                $result["success"] = -1;
                $result["message"] = $err;

            }
            else{
                $this->wsClient->clearAttachments();
                $this->wsClient->addAttachment($res["file"],$res["data"]["nomefile"],$res["data"]["mimetype"]);
                $response = $this->wsClient->call("insertDocumento",Array($this->login,$res["data"]["nomefile"],$res["data"]["descrizione"]));
                if(!$response["lngErrNumber"] && $response["lngDocID"]){
                    $result["success"] = 1;

                    $res["data"]["idrichiesta"] = $response["lngDocID"];
                    $r = $this->caricaXML("documento",$res["data"]);
                    $xmlAllegato = $r["result"];
                    $result["result"] = Array("id"=>$response["lngDocID"],"xml"=>$xmlAllegato);
                    if(!$this->data["allegato"]){
                        $this->data["allegato"] = Array(
                            Array("id"=>$response["lngDocID"],"nome"=>$res["data"]["nomefile"],"tipo" => "","descrizione" => $res["data"]["descrizione"])
                        );
                    }
                    else{
                        $this->data["allegati"][] = Array("id"=>$response["lngDocID"],"nome"=>$res["data"]["nomefile"],"tipo" => "","descrizione" => $res["data"]["descrizione"]);
                    }
                    
                }
                else{
                    $result["success"] = -2;
                    $result["message"] = sprintf("Errore Numero %s - %s",$response["lngErrNumber"],$response["strErrString"]);
                }
 
            }
        }
        else{
            $result = $res;
        }
        return $result;
    }
    
    function richiediProtIn(){
        $res = $this->caricaXML("prot_in");
        if($res["success"]==1){
            $fileXML = $res["result"];
            print_r($fileXML);
            $this->wsClient->clearAttachments();
            $this->wsClient->addAttachment($fileXML,"richiesta_protocollo_in.xml","text/xml");
            $response =$this->wsClient->call("registraProtocollo",Array($this->login));
            
        }
        else{
            $response = Array("success"=>-1);
        }
        return $response;
    }
    
    function richiediProt(){
        
    }
}
?>
