<?php
define('SERVICE_URL','http://93.63.112.4:4080/SuapDOCAREA/protocollazione.php?wsdl');
define('DIZIONARI_URL','http://93.63.112.4:4080/SuapDOCAREA/dizionario.php?wsdl');
define('SERVICE_USER','protocollo sue');
define('SERVICE_PASSWD','Prot/2018');

if (!defined('LOCAL_LIB')) define('LOCAL_LIB',DATA_DIR.'praticaweb'.DIRECTORY_SEPARATOR.'lib/');
if (!defined('APPS_DIR')) define('APPS_DIR',DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR."praticaweb-2.1".DIRECTORY_SEPARATOR);
if (!defined('LIB')) define('LIB',APPS_DIR."lib".DIRECTORY_SEPARATOR);
define('TEMPLATE_DIR',DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR);

define('DENOMINAZIONE',"EDILIZIA PRIVATA");
define('CODICE_AMMINISTRAZIONE',"1");
define('INDIRIZZO_TELEMATICO',"protocollo@comune.ge.it");
define('CODICE_UO',"7");
define('CODICE_TITOLARIO',"1702");
define('CODICE_A00',"1");

?>