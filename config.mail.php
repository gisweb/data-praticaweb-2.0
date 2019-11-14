<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$project = "camogli";

$actualDir = dirname(__FILE__);
define('DB_ENABLED',0);
define('MAILPEC',1);
define('MAILHOSTOUT', "smtps.pec.aruba.it");
define('MAILHOSTIN', "imaps.pec.aruba.it");
define("MAILUSER", "admin@pec.istanze-online.it");
define("MAILPWDIN", "IUHIAZJOJU");
define("MAILPWDOUT", "IUHIAZJOJU");
define("MAILFROM", "admin@pec.istanze-online.it");
define("MAILALIAS","Gis&Web S.a.S.");
define("MAILPORTOUT", "465");
define("MAILPORTIN", "993");
define("MAILTLSIN", 'notls');
define("MAILSSLIN", 'ssl');
define("MAILSECURE", 'ssl');
define('MAILAUTH',true);
if(file_exists($actualDir.DIRECTORY_SEPARATOR."sml.config.local.php")){
    require_once $actualDir.DIRECTORY_SEPARATOR."sml.config.local.php";
}
else{
    define('MAILDSN',"pgsql:dbname=gw_$project;user=gwAdmin;password=!{!dpQ3!Hg7kdCA9;host=127.0.0.1;port=5434");
}

$url = "http://webservice.gisweb.it/wsmail/$project.wsMail.php?wsdl";
define('SERVICE_URL',$url);
?>



