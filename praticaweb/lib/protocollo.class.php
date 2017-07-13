<?php
/**
 * Created by PhpStorm.
 * User: mamo
 * Date: 06/07/17
 * Time: 17:55
 */



require_once LOCAL_LIB."app.utils.class.php";
require_once LIB."utils.class.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoap.php";
require_once LIB."nusoap".DIRECTORY_SEPARATOR."nusoapmime.php";
require_once DATA_DIR."protocollo.config.php";

class protocollo{

    static function getParams(){
        return Array(
            "service"=>"SicraWeb",
            "wsUrl"=> "http://93.57.10.175:50080/client/services/ProWSApi?WSDL",
            "login"=> "!suap/sicraweb@tovosangiacomo/tovosangiacomo",
            "mittente"=> Array(

                "Denominazione_Entita"=> "Comune di Andora",
                "Denominazione"=>"URBANISTICA",
                "CodiceAmministrazione"=>"c_l315",
                "IndirizzoTelematico"=>"comune@prova.it",
                "UnitaOrganizzativa"=>"T",
                "CodiceTitolario"=>"1.1",
                "CodiceA00"=>"PL",
                "Indirizzo" => "Via Cavour 94",
                "Identificativo" => ""
            )
        );
    }

    static function subst($txt,$data){
        foreach($data as $k=>$v){
            $txt = str_replace("%($k)s",$v,$txt);
        }
        return $txt;
    }

    private static function inserisciDocumento($id){
        $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
        $res = appUtils::getInfoDocumento($id);
        $prms = self::getParams();
        if ($res["success"]==1){
            $client = new nusoap_client_mime($prms["wsUrl"],'wsdl');
            $err = $client->getError();
            if ($err) {
                $result["success"] = -1;
                $result["message"] = $err;

            }
            else{
                $client->addAttachment($res["file"],$res["data"]["nomefile"],$res["mimetype"]);
                $response = $client->call("insertDocumento",Array($prms["login"],$res["data"]["nomefile"],$res["data"]["descrizione"]));
                if(!$response["lngErrNumber"] && $response["lngDocID"]){
                    $result["success"] = 1;

                    $res["data"]["idrichiesta"] = $response["lngDocID"];
                    $r = self::caricaXML("documento",$res["data"]);
                    $xmlAllegato = $r["result"];
                    $result["result"] = Array("idallegato"=>$response["lngDocID"],"xml"=>$xmlAllegato);
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

    static function richiediProtOut($pratica,$params=Array()){
        $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
        $dataSubst = self::getParams();
        $paramsKeys = array_keys($params);

        if (!$params["app"]) $app="pe";
        else{
            $app=$params["app"];
        }
        $d = self::recuperaPratica($pratica,$app);
        foreach($d["result"] as $k=>$v){
            $dataSubst[$k] = $v;
        }
        if (!(in_array("destinatari",$paramsKeys) && $params["destinatari"])) return -2;

        $documentiOk = 1;
        $multiDest =  (count($params["destinatari"]) > 1)?(1):(0);
        if (in_array("allegati",$paramsKeys) && $params["allegati"]){
            for($i=0;$i<count($params["allegati"]);$i++){
                $idDoc = $params["allegati"][$i];
                $res = self::inserisciDocumento($idDoc);
                $documentiOk = $documentiOk && $res["success"];
                if ($res["success"]==1){
                    $xmlAll[] = $res["result"]["xml"];
                }
                else{

                }

            }
            $dataSubst["allegati"] = implode("\n",$xmlAll);
        }

        for($i=0;$i<count($params["destinatari"]);$i++){
            $idDest = $params["destinatari"][$i];
            $res = self::recuperaSoggetto($idDest,$app,$multiDest);
            if ($res["success"]==1){
                $denominazioni[] = $res["result"]["data"]["denominazione"];
                $xmlDest[] = $res["result"]["data"]["xml"];
            }
        }
        if (!$multiDest){
            $dataSubst["destinatari"] = implode("\n",$xmlDest);
        }
        else{
            $r = self::caricaXML("destinatari",Array("denominazioni"=>implode(", ",$denominazioni),"destinatari"=>implode("\n",$xmlDest)));
            $dataSubst["destinatari"] = $r["result"];
        }
        $r = self::caricaXML("prot_out",$dataSubst);
        $fileXML = $r["result"];

    }

    static function richiediProtIn(){

    }

    static function infoProtocollo($prot,$anno){
        $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
        $client = new nusoap_client($paramsProtOut["wsUrl"],'wsdl');
        $a = $client->call("infoProtocollo",Array($paramsProtOut["login"],$prot,$anno));
        $xml = simplexml_load_string($a);
        $json = json_encode($xml);
        $response = json_decode($json,TRUE);
        $result["result"] = $response;
        $result["success"] = 1;
        return $result;
    }

    static function recuperaSoggetto($id,$app="pe",$multi){
        $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
        $sql=<<<EOT
WITH elenco_soggetti AS(        
SELECT id::varchar as id, coalesce(codfis,piva) as codfis, nome, cognome, coalesce(ragsoc,cognome || ' ' || nome) as denominazione, comune, prov, cap, trim(coalesce(indirizzo, '')|| '' || coalesce(civico,'')) as indirizzo, pec as mail FROM pe.soggetti
UNION ALL
SELECT mail as id,codfis, ''::varchar as nome, ''::varchar as cognome, nome as denominazione, comune, prov, cap, trim(coalesce(indirizzo, '')|| '' || coalesce(civico,'')) as indirizzo,mail FROM pe.e_enti
)
SELECT * FROM elenco_soggetti WHERE id = ?;
EOT;
        $dbh = utils::getDb();
        $stmt = $dbh->prepare($sql);
        $res = Array();
        if($stmt->execute(Array($id))){
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $fileXML = ($multi)?("destinatario_multi"):("destinatario");
            $xml = self::caricaXML($fileXML,$res);
            $result["result"] = Array("data"=>$res,"xml"=>$xml["result"]);
            $result["success"] = 1;
        }
        else{
            $result["message"] = $stmt->errorInfo();
            $result["success"] = -1;
        }
        return $result;
    }
    static function recuperaPratica($pr,$app){
        $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
        $sql = <<<EOT
SELECT numero,protocollo as prot,date_part('year',data_prot) as anno_prot,coalesce(oggetto,'') as oggetto FROM pe.avvioproc WHERE pratica=?
EOT;
        $dbh = utils::getDb();
        $stmt = $dbh->prepare($sql);
        $res = Array();
        if($stmt->execute(Array($pr))) {
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $result["result"] = $res;
            $result["success"] = 1;
        }
        else{
            $result["message"] = $stmt->errorInfo();
            $result["success"] = -1;
        }
        return $result;
    }
    static function caricaXML($nome,$data){
        $result = Array(
            "success" => 0,
            "message" => "",
            "result" => ""
        );
        $fName = TEMPLATE_DIR.$nome.".xml";
        if (file_exists($fName)){
            $f = fopen($fName,'r');
            $tXml = fread($f,filesize($fName));
            fclose($f);
            $xml = self::subst($tXml,$data);
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



}
?>