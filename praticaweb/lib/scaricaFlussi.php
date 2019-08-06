<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function logging($commento) {
	$file_pointer = fopen("errori-flussi.log", "a+");
	#Scriviamo il nuovo refer
	fwrite($file_pointer, $commento);
	#Chiudiamo il file txt
	fclose($file_pointer); 
return 1;
}

function flusso_singolo($idFlusso) {
    
    $url = 'https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/rest/riconciliazione/flusso';
    $fields = array(
        'idFlusso' => '"'.$idFlusso.'"',
        'tipo' => '"dettaglio"',
        'nomeEnte' => '"Unione Dei Comuni Valmerula e Montarosio"'
    );
    
    $fields_string="";
    //url-ify the data for the POST
    foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
    rtrim($fields_string, '&');

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, count($fields));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

    //execute post
    $result = curl_exec($ch);
    $record = json_decode($result, true);
    //close connection
    curl_close($ch);   
    
   
    return $record;
}


function flussi($da,$a) {
    

    $url = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/rest/riconciliazione/listaflussi";

    /*Modifica Marco*/
    $fields = array(
        'nomeEnte' => '"Unione Dei Comuni Valmerula e Montarosio"',
        'da' => '"'.$da.'"',
        'a' => '"'.$a.'"'
    );
    $fields_string="";
    //url-ify the data for the POST
    foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
    rtrim($fields_string, '&');

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, count($fields));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

    //execute post
    $result = curl_exec($ch);
    $record = json_decode($result, true);

    //close connection
    curl_close($ch);


    if (is_array($record)) {
        foreach($record as $field)
        {
            $idflusso=$field;
            echo "Considero il flusso $idflusso\n";
            $res[$idflusso]= flusso_singolo($idflusso);
            if (count($res[$idflusso])==0) echo "\tIl flusso $idflusso è vuoto\n";

        }
    }

    return $res;
}

function inserisciflussi($record){
    $dsn = sprintf('pgsql:dbname=%s;host=%s;port=%s',"gw_andora",'127.0.0.1','5434');
    $dbh = new PDO($dsn, 'postgres', 'postgres');
    $sql = "INSERT INTO ragioneria.flussi(flusso,iuv,importo,capitolo,indice) VALUES(?,?,?,?,?)";
    $stmt = $dbh->prepare($sql);
    foreach($record as $idflusso=>$flusso){
        for($i=0;$i<count($flusso);$i++){
            $r = $flusso[$i];
            $data=Array($idflusso,$r["iuv"],$r["importo"],$r["capitoloBilancio"],$r["indiceDati"]);
            if(!$stmt->execute($data)){
                logging("Errore nel Flusso $idflusso con IUV ".$r["iuv"]."\n");
                /*print "$idflusso\n";
                print_r($r);
                print "\n";*/
            }
            
        }
    }
}

$urlFlussi = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/rest/riconciliazione/listaflussi";
$urlFlusso = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/rest/riconciliazione/flusso";

$ente = "Unione Dei Comuni Valmerula e Montarosio";
$da = "01/01/2019";
$a = "09/08/2019";
$res = flussi($da,$a);
inserisciflussi($res)

?>
