<?php
require_once LOCAL_LIB."pagopa.class.php";

$rr = pagopa::readPagamenti($idpratica);
if ($rr["success"]==1){
    $wsData = $rr["data"];
}

?>