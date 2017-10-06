<?php
/**
 * Created by PhpStorm.
 * User: mamo
 * Date: 06/10/17
 * Time: 08:40
 */
$dir = dirname(__FILE__);
define('DATA_DIR',$dir.DIRECTORY_SEPARATOR);
define('APPS_DIR',"/apps/praticaweb-2.1/");
require_once "../config.php";
require_once LIB."utils.class.php";
require_once LOCAL_LIB."pratica.class.php";

$template =<<<EOT
<html>
<head>
    <style>
        body{
            margin-top:0.5cm;
            margin-left:1cm;
            margin-right:1cm;
            margin-bottom:0.5cm;
            font-family:Times New Roman;
            font-size:14px;
        }
    </style>
</head>
<body>
    <div align="center"><img  border="0" alt=" " src="http://pieveligure.praticaweb.it/images/pieve.header.jpg" /></div>
    %s
</body>
</html>
EOT;

$dbh = utils::getDb();
$sql = "select pratica,file_doc,testohtml from stp.stampe A  where file_doc ilike '%\.html'  and not A.form IN ('cdu.vincoli','ce.commissione') order by A.pratica ";
$stmt = $dbh->prepare($sql);
if($stmt->execute()){
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    for($i=0;$i<count($res);$i++){
        $info[$res[$i]["pratica"]][]= $res[$i];
    }
    foreach($info as $id=>$v){
        $pr = new pratica($idpr);
        $nf = count($v);
        print $pr->documenti." con $nf documenti\n";
    }
}
die();




?>