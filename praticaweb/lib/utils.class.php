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
        foreach($this->js as $js){
           $tag=sprintf("\n\t\t<SCRIPT language=\"javascript\" src=\"%sjs/%s.js\"/>",self::jsURL,$js);
           echo $tag;
        }
        
    }
    static function writeCSS(){
        foreach($this->css as $css){
           $tag=sprintf("\n\t\t<LINK media=\"screen\" href=\"%s/%s.css\" type=\"text/css\" rel=\"stylesheet\"/>",self::jsURL,$js);
           echo $tag;
        }
        
    }
}
?>