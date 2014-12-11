<?php
define('NOME_COMUNE','Comune di Alghero');//nome completo del comune che compare nell'intestazione

define('DEBUG', 1); // Debugging 0 off 1 on
define('DB_DRIVER','pdo_pgsql');
define('DB_PORT','5434');
define('DB_HOST','127.0.0.1');

define('DB_NAME','sit_alghero');
define('DB_USER','postgres');
define('DB_PWD','postgres');


define('APPS_DIR',implode(DIRECTORY_SEPARATOR,Array("D:","Applicazioni","apps","praticaweb-alghero")).DIRECTORY_SEPARATOR);
define('MENU',DATA_DIR."praticaweb/mnu/");//cartella contenente la  configurazione dei menu
define('TAB',DATA_DIR."praticaweb/tab/");//cartella contenente la  configurazione dei forms via file tab
define('TAB_ELENCO',DATA_DIR."praticaweb/tab_elenco/");//cartella con elenchi testuali
define('LIB',DATA_DIR."praticaweb/lib/");//cartella contenente la  configurazione dei forms via file tab

define('MODELLI',DATA_DIR."praticaweb/modelli/");//cartella con i modelli di stampa 
define('STAMPE',DATA_DIR."praticaweb/documenti/");//cartella con le stampe

//define('SAMBA_URL','\\\\192.168.56.9\\documenti\\');
define('DEBUG_DIR',DATA_DIR."praticaweb/debug/");//cartella con i debug
define('ALLEGATI',DATA_DIR."praticaweb/documenti/");//cartella dei file allegati sotto praticaweb
define('SMB_PATH','file://srv-gis/documenti-pe/');
define('SMB_MODELLI','file://srv-gis/modelli-pe/');
define('URL_ALLEGATI','allegati/');//url relativo dei file allegati con / finale
define('DOCUMENTI',DATA_DIR."praticaweb/documenti/");
define('AREA_MIN','5');//area minima di intersezione per le query di overlay

define('SELF',$_SERVER["PHP_SELF"]);

define('NEW_VINCOLI',1);

define('THE_GEOM','bordo_gb');
define('MAPPA_PRATICHE','alghero');
define('LAYER_MAPPALI','particelle');
define('OBJ_LAYER','2183:particelle');
define('MAPSETID','pratiche_edilizie');
define('TEMPLATE','alghero');
define('GC_VERSION',2);
define('QTID_PARTICELLE','133');
define('QTID_CIVICI','1');

//in sessione per pmapper

$_SESSION['USER_DATA']=DATA_DIR;


$tmpDir=ini_get('include_path');
$tmpDir=(in_array('/apps/includes',explode(':',$tmpDir)))?($tmpDir):($tmpDir.':/apps/includes');
$incDir=(in_array('/apps/includes/utils',explode(':',$tmpDir)))?($tmpDir):($tmpDir.':/apps/includes/utils');
ini_set('include_path',$incDir);
//includo il file per il database in uso
require_once (APPS_DIR."wrapdb/postgres.php");
require_once (APPS_DIR."utils/debug.php");
?>
