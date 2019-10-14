<?php
require_once LOCAL_LIB."pagopa.class.php";

$rr = utils::readPagamenti($pratica);
if ($rr["success"]==1){
    $wsData = $rr["data"];
}

?>