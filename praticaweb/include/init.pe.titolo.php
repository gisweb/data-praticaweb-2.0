<?php
$db=utils::getDB();
$sql="SELECT oggetto FROM pe.avvioproc WHERE pratica=?;";
$sth=$db->prepare($sql);
$sth->execute(Array($idpratica));
$oggetto=$sth->fetchColumn();
?>