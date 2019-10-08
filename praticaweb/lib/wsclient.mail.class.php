<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class wsClientMail{
    const serviceURL = "http://10.95.10.42/wsPECMail.asmx?WSDL";
    const user = "gisweb_auth";
    
    static function getInfoPEC($objId,$remoteIds=Array()){
        $req = Array(
            "utente"=>self::user,
            "objectid"=>$objId,
            "remoteids"=>$remoteIds,
        );
        $jReq = json_encode($req);
        $client = new SoapClient(self::serviceURL, array("trace" => 1, "exception" => 0));
        $res = $client->checkPec(Array("jrequest"=>$jReq));
        $r = json_decode(json_encode($res),1);
        $rr = json_decode($r["checkPecResult"],true);
        if(array_key_exists('PecList', $rr))
            return $rr["PecList"];
        else
            return Array();
    }
}
?>
