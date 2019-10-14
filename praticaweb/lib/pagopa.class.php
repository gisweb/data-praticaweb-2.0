<?php

require_once APPS_DIR.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'pagopa.class.php';

class pagopa extends generalPagopa{
    const actionRead = "iol-GetElencoImportiPagamenti";
    const actionSet = "iol-SetImporti";
    
    const user = "marco.carbone@gisweb.it";
    const passwd = "pipino";
    
    
    
    static function readPagamenti($pratica){
        $result = Array("success"=>0,"message"=>Array(),"data"=>Array());
        $sql = "SELECT * FROM pe.istanze WHERE pratica=?";
        $dbh = utils::getDb();
        $stmt = $dbh->prepare($sql);
        if($stmt->execute(Array($pratica))){
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            for($i=0;$i<count($res);$i++){
                $url = sprintf("%s/%s",$res[$i]["url"],self::actionRead);
                //$url = "https://www.istanze.spezianet.it/iol_sp/04028-2019-dehor/iol-GetElencoImportiPagamenti";


                $headers = array(
                    "Content-type: text/json;charset=\"utf-8\"",
                    //"Accept: text/json",
                    "Cache-Control: no-cache",
                    "Pragma: no-cache",
                    "Authorization: Basic ".base64_encode(self::user.":".self::passwd),
                ); //SOAPAction: your op URL

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                //curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
                //            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_POST, true);
                //curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                // converting
                $response = curl_exec($ch); 
                curl_close($ch);
                if ($response){
                    $res = json_decode($response,1);
                    if(!$res){
                        $jsonErr = json_last_error();
                        utils::json_error($jsonErr);
                        
                    }
                    else{ 
                        $result["success"] = 1;
                        for($j=0;$j<count($res["results"]);$j++){
                            $result["data"][] = $res["results"][$j];
                        }
                    }
                }
                /*else{
                    return Array();
                }*/
            }
        }
        return $result;
    }
    
    static function setPagamenti($pratica,$codice){
        $result = Array("success"=>0,"message"=>Array(),"data"=>Array());
        $sql = "SELECT * FROM pe.avvioproc WHERE pratica=? and online=1;";
        $dbh = utils::getDb();
        $stmt = $dbh->prepare($sql);
        if($stmt->execute(Array($pratica))){
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $url = sprintf("%s/%s",$res["foreign_id"],self::actionSet);
            
            $sql = "SELECT * FROM ragioneria.importi_dovuti WHERE pratica=? AND codice_richiesta=?";
            $stmt = $dbh->prepare($sql);
            if($stmt->execute(Array($pratica,$codice))){
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
                for($i=0;$i<count($res);$i++){
                    $dataPost[] = Array(
                        "codimp"=>$res[$i]["contatore"],
                        "importo" => $res[$i]["importo"],
                        "tipo"=>$res[$i]["tipo"],
                        "causale"=>$res[$i]["causale"],
                        "scadenza"=>$res[$i]["data_scadenza"],
                        "azione"=>""

                    );
                }
                $post_string = json_encode($dataPost);
            }
            else{
                return $result;
            }
            $post_string = "";
            
            $headers = array(
                    //"Content-type: text/json;charset=\"utf-8\"",
                    //"Accept: text/json",
                    "Cache-Control: no-cache",
                    "Pragma: no-cache",
                    "Authorization: Basic ".base64_encode(self::user.":".self::passwd),
                ); //SOAPAction: your op URL

            
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
            //            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string); // the SOAP request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // converting
            $response = curl_exec($ch); 
            curl_close($ch);
        }
    }
    
    static function verificaPagamento($iuv){
        
    }
    
    static function ricevutaPagamento($iuv){
        
    }
}