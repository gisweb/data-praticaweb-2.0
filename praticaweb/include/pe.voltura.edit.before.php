<?php
$numTitolo=appUtils::nuovoNumeroTitolo();

$html = <<<EOT
<div style="color:red;font-weight:bold;font-size:13">Sistema di numerazione automatico del titolo ATTIVO.</br>
Attenzione inserire solo numeri di titolo antecedenti al numero $numTitolo, altrimenti il sistema di numerazione non sarà corretto.
</div>
EOT;
?>