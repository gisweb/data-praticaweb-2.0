<?php
$baseDir = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR;
$dbh = new PDO("pgsql:dbname=gw_savona;host=127.0.0.1","postgres","postgres");
$sql = "SELECT pe.sorteggio_cila();" ;
$stmt = $dbh->prepare($sql);
$stmt->execute();


?>
