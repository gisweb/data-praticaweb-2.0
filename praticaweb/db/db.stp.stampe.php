<?php
require_once DATA_DIR."config.ads.php";
require_once LOCAL_LIB."WSSoapClient.php";
define('TEMPLATE_FILE',DATA_DIR."praticaweb/scripts/template.php");
$client = new SoapClient(WSDL_LOGIN_URL);
require_once TEMPLATE_FILE;


?>