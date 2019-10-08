<?php

$selpratica=isset($_REQUEST["pratica"])?($_REQUEST["pratica"]):(-1);
$selpratica = ($selpratica=="")?(-1):($selpratica);

$sql="select coalesce(resp_proc,0) as resp_proc, coalesce(resp_it,0) as resp_it,
                      coalesce(resp_ia,0) as resp_ia from pe.avvioproc where pratica=";
$res=$db->fetchAll($sql.$selpratica);

$sql="select min(data_annotazione) as datains from pe.annotazioni where pratica=";
$resd=$db->fetchAll($sql.$selpratica);

$datenow = strtotime('today UTC');
$dateprat = strtotime('today UTC');

if (isset($resd[0])) {
    $dateprat = 7200 + strtotime(substr($resd[0]["datains"],3,2)."/".substr($resd[0]["datains"],0,2)."/".substr($resd[0]["datains"],-4));
    //print substr($resd[0]["datains"],3,2)."/".substr($resd[0]["datains"],0,2)."/".substr($resd[0]["datains"],-4);
    //print " ".$dateprat;
}
else
{
    $dateprat = strtotime('today UTC');
}

$diff = abs($datenow - $dateprat);
//print $datenow." ".$dateprat." ".$diff;
$days = floor($diff / (60*60*24));
//print $days;

if (isset($res[0])) {

    //permessi da responsbile totale
    if (($res[0]["resp_proc"]==$_SESSION["USER_ID"])
        or ($_SESSION["USER_ID"]==131) or ($_SESSION["USER_ID"]==52) or ($_SESSION["USER_ID"]==62) or ($_SESSION["USER_ID"]==64) or ($res[0]["resp_proc"]==0))
    {
        $_SESSION["PERMESSI_$selpratica"]=$_SESSION['PERMESSI_OK'];
    }

    else
    {
        $_SESSION["PERMESSI_$selpratica"]=4;
    }

    //permessi amministrativi
    if (($res[0]["resp_ia"]==$_SESSION["USER_ID"]) or ($_SESSION["USER_ID"]==55) or ($_SESSION["USER_ID"]==630) or ($_SESSION["USER_ID"]==56)
        or ($_SESSION["USER_ID"]==57) or ($_SESSION["USER_ID"]==59) or ($_SESSION["USER_ID"]==66)) {
        $_SESSION["PERMESSI_A_$selpratica"]=$_SESSION['PERMESSI_OK'];
    }
    else {
        $_SESSION["PERMESSI_A_$selpratica"]=$_SESSION["PERMESSI_$selpratica"];
    }

    //permessi Galeazzi
    if ($_SESSION["USER_ID"]==140) {
        $_SESSION["PERMESSI_G_$selpratica"]=$_SESSION['PERMESSI_OK'];
    }
    else {
        $_SESSION["PERMESSI_G_$selpratica"]=$_SESSION["PERMESSI_$selpratica"];
    }

    //permessi Simonelli
    if ($_SESSION["USER_ID"]==65) {
        $_SESSION["PERMESSI_S_$selpratica"]=$_SESSION['PERMESSI_OK'];
    }
    else {
        $_SESSION["PERMESSI_S_$selpratica"]=$_SESSION["PERMESSI_$selpratica"];
    }

    //permessi Mariotti
    if ($_SESSION["USER_ID"]==62) {
        $_SESSION["PERMESSI_M_$selpratica"]=$_SESSION['PERMESSI_OK'];
    }
    else {
        $_SESSION["PERMESSI_M_$selpratica"]=$_SESSION["PERMESSI_$selpratica"];
    }

    //permessi Cibei
    if (($_SESSION["USER_ID"]==70) or ($_SESSION["USER_ID"]==71)) {
        $_SESSION["PERMESSI_C_$selpratica"]=$_SESSION['PERMESSI_OK'];
    }
    else {
        $_SESSION["PERMESSI_C_$selpratica"]=$_SESSION["PERMESSI_$selpratica"];
    }
}
?>