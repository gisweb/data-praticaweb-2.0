<?php
define('NOME_COMUNE','Comune di La Spezia - Pratiche Edilizie');//nome completo del comune che compare nell'intestazione

define('DEBUG', 1); // Debugging 0 off 1 on
define('DB_DRIVER','pdo_pgsql');

if (file_exists(DATA_DIR.'config.local.php')){
	/*LOCAL CONFIGURATION FOR TEST*/
	include DATA_DIR.'config.local.php';
}


if(!defined("ALWAYS_VIEWABLE")) define('ALWAYS_VIEWABLE',1);
if(!defined("ALWAYS_EDITABLE")) define('ALWAYS_EDITABLE',1);

if (!defined("DB_HOST")) define('DB_HOST','10.95.10.27');
if (!defined("DB_NAME")) define('DB_NAME','gw_spezia');
if(!defined("DB_PORT")) define('DB_PORT','5433');

if (!defined("DB_USER")) define('DB_USER','postgres');
if (!defined("DB_PWD")) define('DB_PWD','postgres');

if (!defined("DB_HOST_PROT")) define('DB_HOST_PROT','10.95.2.24');
if (!defined("DB_NAME_PROT")) define('DB_NAME_PROT','protocollo');
if(!defined("DB_PORT_PROT")) define('DB_PORT_PROT','5433');

if (!defined("DB_USER_PROT")) define('DB_USER_PROT','protocollo');
if (!defined("DB_PWD_PROT")) define('DB_PWD_PROT','protocollo');


define('MENU',DATA_DIR."praticaweb/mnu/");//cartella contenente la  configurazione dei menu
define('TAB',DATA_DIR."praticaweb/tab/");//cartella contenente la  configurazione dei forms via file tab
define('TAB_ELENCO',DATA_DIR."praticaweb/tab_elenco/");//cartella con elenchi testuali
define('LIB',APPS_DIR."lib".DIRECTORY_SEPARATOR);//cartella contenente le librerie

define('MODELLI',DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."modelli/");//cartella con i modelli di stampa 
define('STAMPE',DATA_DIR."praticaweb/documenti/");//cartella con le stampe


define('REPO_PATH','D');
define('DEBUG_DIR',DATA_DIR."praticaweb/debug/");//cartella con i debug
define('ALLEGATI',DATA_DIR."praticaweb/documenti/");//cartella dei file allegati sotto praticaweb


define('SMB_MODELLI','file://'.REPO_PATH.'/modelli-pe/');
define('URL_ALLEGATI','allegati/');//url relativo dei file allegati con / finale

define('LOCAL_DOCUMENT',0);     //DEFINISCE SE I DOCUMENTI VENGONO APERTI SU PERCORSO LOCALE O WEB
//define('SMB_PATH','\\\\vmserver\\sanremo\\documenti\\');  // PERCORSO DI RETE DOVE APRIRE I DOCUMENTI
define('DOCUMENTI',DATA_DIR."/praticaweb/documenti");

define('LOCAL_DB',DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."db".DIRECTORY_SEPARATOR);				//SALVATAGGI LOCALI DEI FILE
define('LOCAL_LIB',DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR); 			//LIBRERIE LOCALI
define('LOCAL_INCLUDE',DATA_DIR."praticaweb".DIRECTORY_SEPARATOR."include".DIRECTORY_SEPARATOR); 	//INCLUDE LOCALI

/******************************* DEFINIZIONI DELLE AZIONI STANDARD *******************************/

define('ACTION_SAVE','salva');
define('ACTION_CANCEL','annulla');
define('ACTION_DELETE','elimina');

/************************************************************************************************/


/*******************************  COSTANTI DELLA FIRMA DIGITALE  *******************************/

define('FIRMA_STATO_DEFAULT','IMM');


define('AREA_MIN','5');//area minima di intersezione per le query di overlay

define('SELF',$_SERVER["PHP_SELF"]);

define('NEW_VINCOLI',1);

define('THE_GEOM','bordo_gb');

define('MAPSETID','MAPPA DELLE PRATICHE');
define('CDUMAPSETID','MAPPA DEL CDU');
define('TEMPLATE','gisclient');
define('GC_VERSION',2);
define('QTID_PARTICELLE','8');
define('QTID_CIVICI','34');

//in sessione per pmapper

$_SESSION['USER_DATA']=DATA_DIR;

require_once DATA_DIR."config".DIRECTORY_SEPARATOR."pagopa.php";
require_once DATA_DIR."config".DIRECTORY_SEPARATOR."modelli.php";

$tmpDir=ini_get('include_path');
$tmpDir=(in_array('/apps/includes',explode(':',$tmpDir)))?($tmpDir):($tmpDir.':/apps/includes');
$incDir=(in_array('/apps/includes/utils',explode(':',$tmpDir)))?($tmpDir):($tmpDir.':/apps/includes/utils');
ini_set('include_path',$incDir);
//includo il file per il database in uso
require_once (APPS_DIR."wrapdb/postgres.php");
require_once (APPS_DIR."utils/debugutils.php");
?>
