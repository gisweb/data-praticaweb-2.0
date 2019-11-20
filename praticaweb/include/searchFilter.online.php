<?php
$dbh = utils::getDb();
$sql =<<<EOT
WITH unnested_groups AS(        
SELECT 
    DISTINCT userid as id,nome,cognome,nominativo,unnest(string_to_array(gruppi,',')) as group_id 
FROM 
    admin.users 
)
SELECT 
    DISTINCT A.id as value,A.nome as label
FROM 
    unnested_groups A INNER JOIN 
    admin.groups B ON(A.group_id::varchar=B.id::varchar)
WHERE
    B.nome = 'itec_sue'
ORDER BY A.nome;
EOT;
$stmt = $dbh->prepare($sql);
$radio =<<<EOT
                            <input type="radio" value="%s" id="%s_pe-avvioproc-istruttore" name="istruttore"  data-plugins="dynamic-search">
                            <label for="%_pe-avvioproc-istruttore" class="value">%s</label><br/>        
EOT;

if($stmt->execute()){
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    for($i=0;$i<count($res);$i++){
        $_radioHTML[]=sprintf($radio,$res[$i]["value"],($i+1),($i+1),$res[$i]["label"]);
    }
    $_radioHTML[]=sprintf($radio,"0",($i+1),($i+1),"Tutti");
    $radioHTML = implode("",$_radioHTML);
}

?>
                <input type="hidden" datatable="pe.avvioproc" id="op_pe-avvioproc-online" class="search text" name="online" value="equal">
                <input type="hidden" value="1" id="1_pe-avvioproc-online" name="online" class="text">
                <table id="table-filter">
                    <tr id="flt-assegnata_istruttore">
                        <td valign="middle">
                            <label for="assegnata_istruttore" class="title">Istruttore assegnato</label><br/>
                            <input type="hidden" datatable="pe.vista_assegnate" id="op_pe-vista_assegnate-assegnata_istruttore" class="search text check" name="assegnata_istruttore" value="equal">                           
                            <input type="radio" value="0" id="1_pe-vista_assegante-assegnata_istruttore" name="assegnata_istruttore"  data-plugins="dynamic-search">
                            <label for="1_pe-vista_assegante-assegnata_istruttore" class="value">No</label><br/>
                            <input type="radio" value="1" id="1_pe-vista_assegante-assegnata_istruttore" name="assegnata_istruttore"  data-plugins="dynamic-search">
                            <label for="2_pe-vista_assegante-assegnata_istruttore" class="value">SI</label><br/>
                            <input type="radio" value="" id="1_pe-vista_assegante-assegnata_istruttore" name="assegnata_istruttore"  data-plugins="dynamic-search">
                            <label for="3_pe-vista_assegante-assegnata_istruttore" class="value">Tutte</label><br/>
                        </td>
                    </tr>
					<tr id="flt-sportello">
                        <td valign="middle">
                            <label for="istruttore" class="title">Istruttore</label><br/>
                            <input type="hidden" datatable="pe.avvioproc" id="op_pe-avvioproc-istruttore" class="search text check" name="istruttore" value="equal">                           
<?php
print $radioHTML;
?>
                        </td>
                    </tr>
                </table>