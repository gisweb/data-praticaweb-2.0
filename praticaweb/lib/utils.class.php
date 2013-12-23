<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of utils
 *
 * @author marco
 */
class utils {
    const jsURL = "/js";
    const cssURL="/css";
    public static $js = Array('jquery-1.9.1','jquery-ui-1.10.2.min','jquery.ui.datepicker-it','jquery.dataTables.min','dataTables.date.order','window','praticaweb','page.controller');
    public static $css = Array('praticaweb/jquery-ui-1.9.1.custom','styles','TableTools','TableTools_JUI','demo_page','demo_table_jui','tabella_v','menu');
    
    static function mergeParams($prms=Array(),$defaultParams=Array()){
        foreach($defaultParams as $key=>$val){
            $result[$key]=(!array_key_exists($key, $prms) || is_null($prms[$key]))?($val):($prms[$key]);
        }
        
    }
//Funzione che restituisce Array di File in una directory
//Params :  1) srcDir = Directory da scandire
//          2) ext    = Array con le estensioni dei file da cercare
//          3) dir    = Elenco anche delle directory    
    static function listFile($prms=Array()){
        $defaultPrms=Array("srcDir"=>"./","ext"=>Array(),"dir"=>false);
        $result=Array();
        return $result;
    }
    static function uploadFiles($prms=Array()) {
        
    }
    static function resizeImages($prms=Array()) {
        
    }
    static function getDb($params=Array()){
        $dsn = sprintf('pgsql:dbname=%s;host=%s;port=%s',DB_NAME,DB_HOST,DB_PORT);
		$conn = new PDO($dsn, DB_USER, DB_PWD);
        return $conn;
    }
    static function writeJS(){
        foreach(self::$js as $js){
           $tag=sprintf("\n\t\t<SCRIPT language=\"javascript\" src=\"%s/%s.js\"></script>",self::jsURL,$js);
           echo $tag;
        }
        
    }
    static function writeCSS(){
        foreach(self::$css as $css){
           $tag=sprintf("\n\t\t<LINK media=\"screen\" href=\"%s/%s.css\" type=\"text/css\" rel=\"stylesheet\"></link>",self::cssURL,$css);
           echo $tag;
        }
        
    }
    static function rand_str($length = 8, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'){
        // Length of character list
        $chars_length = (strlen($chars) - 1);

        // Start our string
        $string = $chars{rand(0, $chars_length)};

        // Generate random string
        for ($i = 1; $i < $length; $i = strlen($string))
        {
            // Grab a random character from our list
            $r = $chars{rand(0, $chars_length)};

            // Make sure the same two characters don't appear next to each other
            if ($r != $string{$i - 1}) $string .=  $r;
        }

        // Return the string
        return $string;
    }
    static function debug($file,$data,$mode='a+'){
	$f=fopen(DEBUG_DIR.$file.".debug",$mode);
	ob_start();
	print_r($data);
	$result=ob_get_contents();
	ob_end_clean();
	fwrite($f,$result."\n");
	fclose($f);
    }
    /*funzione di Raggruppamento dei dati*/
    static function groupData($mode,$res){
        $result=Array();
        switch($mode){
            case "civico":
                for($i=0;$i<count($res);$i++){
                    $rec=$res[$i];
                    $codvia=preg_replace("/[^A-Za-z0-9]/", '', strtolower($rec["via"]));
                    $via=$rec["via"];
                    $civico=preg_replace("([\\/]+)","-",$rec["civico"]);
                    $civico=preg_replace("([\.]+)","",$civico);
                    $descrizione=sprintf("Pratica n° %s del %s",$rec["numero"],$rec["data_presentazione"]);
                    $ct=$rec["elenco_ct"];
                    $cu=$rec["elenco_cu"];
                    $linkToPratica=$rec["pratica"];
                    $r[$codvia][$civico][$rec["pratica"]]=Array("via"=>$via,"civico"=>$civico,"info"=>Array("id"=>$rec["pratica"],"name"=>$descrizione,"descrizione"=>$descrizione,"ct"=>$ct,"cu"=>$cu,"civico"=>"","via"=>"","pratica"=>$linkToPratica,"oggetto"=>$rec["oggetto"]));

                }
                foreach($r as $codvia=>$values){
                    $civici=Array();
                    foreach($values as $civ=>$vals){
                        $pratiche=Array();
                        foreach($vals as $pr=>$data){
                            $pratiche[]=$data["info"];
                            $via=$data["via"];
                        }
                        $civico=Array("id"=>"$codvia-$civ","name"=>$civ,"civico"=>"$civ","via"=>"$via","descrizione"=>"","ct"=>"","cu"=>"","children"=>$pratiche,"oggetto"=>"","pratica"=>"","state"=>"closed");
                        $civici[]=$civico;
                    }
                    $via=Array("id"=>"$codvia","civico"=>"","name"=>$via,"via"=>"$via","oggetto"=>"","descrizione"=>"","ct"=>"","cu"=>"","pratica"=>"","state"=>"closed","children"=>$civici);
                    $result[]=$via;
                }

                break;
            case "particella-terreni":
            case "particella-urbano":
                for($i=0;$i<count($res);$i++){
                    $rec=$res[$i];
                    $sez=preg_replace("/[^A-Za-z0-9 ]/", '', strtolower($rec["sezione"]));
                    $fg=preg_replace("/[^A-Za-z0-9 ]/", '',$rec["foglio"]);
                    $mp=preg_replace("/[^A-Za-z0-9 ]/", '',$rec["mappale"]);
                    $descrizione=sprintf("Pratica n° %s del %s",$rec["numero"],$rec["data_presentazione"]);
                    $ubicazione=$rec["ubicazione"];
                    $cu=$rec["elenco_cu"];
                    $r[$sez][$fg][$mp][$rec["pratica"]]=Array(
                        "sezione"=>$sez,
                        "foglio"=>$fg,
                        "mappale"=>$mp,
                        "info"=>Array(
                            "id"=>$rec["pratica"],
                            "name"=>$descrizione,
                            "descrizione"=>$descrizione,
                            "ubicazione"=>$ubicazione,
                            "cu"=>$cu,
                            "pratica"=>$rec["pratica"],
                            "oggetto"=>$rec["oggetto"]
                        )
                    );

                }
                $sezioni=Array();
                foreach($r as $sez=>$values){
                    $fogli=Array();
                    foreach($values as $fgs=>$v){
                        $mappali=Array();
                        foreach($v as $maps=>$vals){

                            $pratiche=Array();
                            foreach($vals as $pr=>$data){
                                $pratiche[]=$data["info"];
                                $mappale=$data["mappale"];
                            }
                            $mappale=Array("id"=>sprintf("%s-%s-%s",$sez,$fgs,$maps),"name"=>"Mappale $maps","descrizione"=>"","ubicazione"=>"","cu"=>"","children"=>$pratiche,"oggetto"=>"","pratica"=>"","state"=>"closed");
                            $mappali[]=$mappale;
                        }
                        $foglio=Array("id"=>sprintf("%s-%s",$sez,$fgs),"name"=>"Foglio $fgs","descrizione"=>"","ubicazione"=>"","cu"=>"","children"=>$mappali,"oggetto"=>"","pratica"=>"","state"=>"closed");
                        $fogli[]=$foglio;
                    }
                    $sezione=Array("id"=>sprintf("%s",$sez),"name"=>"Sezione $sez","descrizione"=>"","ubicazione"=>"","cu"=>"","children"=>$fogli,"oggetto"=>"","pratica"=>"","state"=>"closed");
                    $result[]=$sezione;
                }
                break;
        }
        return $result;

    }
}
?>