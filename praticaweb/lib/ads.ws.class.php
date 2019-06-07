<?php
$confFile = sprintf("%s%sconfig.ads.php",dirname(dirname(dirname(__FILE__))),DIRECTORY_SEPARATOR);
require_once $confFile;
class adsWS{
	var $wsLogin = WSDL_LOGIN_URL;
	var $wsExtProt = WSDL_PROTEXT_URL;
	var $wsDocum = WSDL_DOCUM_URL;
	var $wsSfera = WSDL_SFERA_URL;
	
	var $DST;
	var $user;
	var $passwd;
	var $codEnte;
	
	var $xmlTemplate;
	
	function __construct($usr,$pwd,$codEnte){
		$this->$user = $user;
		$this->passwd = $pwd;
		$this->codEnte = $codEnte;
	}
	
	function login(){
		
	}
}

?>