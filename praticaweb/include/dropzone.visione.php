        
 <?php
    if (defined('DROPZONE_ENABLED') && DROPZONE_ENABLED){
 ?>
        
<script>
    Dropzone.autoDiscover = false;
    $(document).ready(function () {
    
        var options = {
            url:'./services/xUpload.php',
            params:function(){
                var res = {};
                $('div#uploadme input').each(function(k,v){
                    res[$(v).attr('name')] = $(v).val();
                });
                return res;
            },
            paramName: "file", // The name that will be used to transfer the file
            maxFilesize: 10, // MB
            parallelUploads:5,
            uploadMultiple:true,
            dictDefaultMessage:"Trascina i file all'interno oppure clicca su quest'area per caricare i file",
            successmultiple:function(file,response){
                target=window.parent.frames["myframe"];
                target.location=target.location;
            }
            
        };
        Dropzone.options.myAwesomeDropzone = options;
        var myDropzone = new Dropzone("div#uploadme", options);
   });
</script>
        
<?php
        
        
        $tabella=new tabella_h("$tabpath/visione_documenti","view");
        $titolo = "Documenti Relativi all' accesso agli atti";
		$nrec=$tabella->set_dati("pratica = $idpratica and form='pe.visione'");	?>			

		<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="100%">		
		  <TR> 
			<TD> 
			<!-- contenuto-->
				<?php
					$tabella->set_titolo($titolo);
					$tabella->get_titolo();
					if ($nrec)	
						$tabella->elenco();
					else
						print ("<p><b>Nessun Documento caricato</b></p>");			
					?>
			<!-- fine contenuto-->
			 </TD>
	      </TR>
		</TABLE>
        <br><hr><br>
        <div style="width:600px;height:200px;align-content: right;" id="uploadme" class="dropzone">
            <input type="hidden" name="pratica" value="<?php echo $idpratica;?>"/>
            <input type="hidden" name="app" value="pe"/>
            <input type="hidden" name="form" value="pe.visione"/>
        </div>
<?php
    }
?>