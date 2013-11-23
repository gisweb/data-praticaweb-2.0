<?
define('NOME_COMUNE','Comune di Savona - Pratiche Edilizie');//nome completo del comune che compare nell'intestazione

define('DEBUG', 1); // Debugging 0 off 1 on
define('DB_DRIVER','pdo_pgsql');
define('DB_HOST','127.0.0.1');
define('DB_PORT','5434');
define('DB_NAME','gw_savona');
define('DB_USER','postgres');
define('DB_PWD','postgres');

define('ALWAYS_VIEWABLE',1);
define('ALWAYS_EDITABLE',1);

define('MENU',DATA_DIR."praticaweb/mnu/");//cartella contenente la  configurazione dei menu
define('TAB',DATA_DIR."praticaweb/tab/");//cartella contenente la  configurazione dei forms via file tab
define('TAB_ELENCO',DATA_DIR."praticaweb/tab_elenco/");//cartella con elenchi testuali
define('LIB',DATA_DIR."praticaweb/lib/");//cartella contenente la  configurazione dei forms via file tab

define('MODELLI',DATA_DIR."praticaweb/modelli/");//cartella con i modelli di stampa 
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


define('AREA_MIN','5');//area minima di intersezione per le query di overlay

define('SELF',$_SERVER["PHP_SELF"]);

define('NEW_VINCOLI',1);

define('THE_GEOM','bordo_gb');
define('MAPPA_PRATICHE','sanremo_riservata');
define('LAYER_MAPPALI','particelle');
define('OBJ_LAYER','2183:particelle');
define('MAPSETID','savona_osm');
define('CDUMAPSETID','savona_osm');
define('TEMPLATE','gisclient');
define('GC_VERSION',2);
define('QTID_PARTICELLE','170');
define('QTID_CIVICI','25');

//in sessione per pmapper

$_SESSION['USER_DATA']=DATA_DIR;


$tmpDir=ini_get('include_path');
$tmpDir=(in_array('/apps/includes',explode(':',$tmpDir)))?($tmpDir):($tmpDir.':/apps/includes');
$incDir=(in_array('/apps/includes/utils',explode(':',$tmpDir)))?($tmpDir):($tmpDir.':/apps/includes/utils');
ini_set('include_path',$incDir);
//includo il file per il database in uso
require_once (APPS_DIR."wrapdb/postgres.php");
require_once (APPS_DIR."utils/debugutils.php");
?>
