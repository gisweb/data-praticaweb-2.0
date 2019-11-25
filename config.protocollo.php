<?php
define('SERVICE_URL','http://217.141.118.10:4080/SuapDOCAREA/protocollazione.php?wsdl');
define('DIZIONARI_URL','http://217.141.118.10:4080/SuapDOCAREA/dizionario.php?wsdl');
define('SERVICE_USER','SERVIZIO PROTOCOLLO');
define('SERVICE_PASSWD','Sportello2018!');

if (!defined('LOCAL_LIB')) define('LOCAL_LIB',DATA_DIR.'praticaweb'.DIRECTORY_SEPARATOR.'lib/');
if (!defined('APPS_DIR')) define('APPS_DIR',DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR."praticaweb-2.1".DIRECTORY_SEPARATOR);
if (!defined('LIB')) define('LIB',APPS_DIR."lib".DIRECTORY_SEPARATOR);
define('TEMPLATE_DIR',DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR);

define('DENOMINAZIONE',"EDILIZIA PRIVATA");
define('CODICE_AMMINISTRAZIONE',"18");
define('INDIRIZZO_TELEMATICO',"protocollo@comune.camogli.ge.it");
define('CODICE_UO',"18");
define('CODICE_TITOLARIO',"6728");
define('CODICE_A00',"1");
define('CODICE_ENTE',"1");

define('WS_URL_PROT','https://camogli.istanze-online.it/iol_praticaweb/protocolla_comunicazione');
define('IOL_USER','marco.carbone@gisweb.it');
define('IOL_PWD','pipino')
?>