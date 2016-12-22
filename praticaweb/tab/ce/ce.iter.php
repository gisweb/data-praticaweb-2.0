<?
include_once("login.php");
include "./lib/tabella_h.class.php";
$tabpath="ce";

$idpratica=$_REQUEST["pratica"];
$modo=$_POST["mode"];
$titolo=$_SESSION["TITOLO_$idpratica"];
$today=date('j-m-y'); 


if ($_POST["azione"]){
	//$id=$_POST["idrow"];
	$active_form=$_REQUEST["active_form"];
	if($_SESSION["ADD_NEW"]!==$_POST)
		unset($_SESSION["ADD_NEW"]);//serve per non inserire più record con f5
	if (isset($array_dati["errors"])) //sono al ritorno errore
		$Errors=$array_dati["errors"];
	else{
		include "db/db.ce.iter.php";
	}
	$_SESSION["ADD_NEW"]=$_POST;				
}



?>

<html>
<head>
<title>Iter - <?=$titolo?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media="screen" href="src/styles.css" type="text/css" rel="stylesheet">
<LINK media="print" href="src/styles_print.css" type="text/css" rel="stylesheet">
<SCRIPT language="javascript" src="src/window.js" type="text/javascript"></SCRIPT>
<script language="javascript">
function confirmSubmit()
{
	document.getElementById("azione").value="Salva";
	return true ;
}
function elimina(id){
	var agree=confirm('Sicuro di voler eliminare definitivamente la riga selezionata?');
	if (agree){
		document.getElementById("azione").value="Elimina";
		document.getElementById("idriga").value=id;
		document.iter.submit();
	}
}
</script>
</head>
<body>
<?if (($modo=="edit") or ($modo=="new") ){
	//---<<<<<<<<<<<<<<<<<<<<<<<<<<<<<  EDITA ELENCO ITER >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>------------------------------>
		$tabella=new tabella_h("$tabpath/iter_edit.tab");
		include "./inc/inc.page_header.php";?>
		
		<form method="post" name="iter" action="cdu.iter.php">		
		<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="99%" align="center">			
		<tr> 
			<td> 
			<!-- intestazione-->
				<H2 class="blueBanner">Modifica elenco eventi della pratica</H2>
			<!-- fine intestazione-->
			</td>
		  </tr>
		  <tr>
			  <td>
				  <table cellPadding="2" border="0" class="stiletabella">
				  <tr>
					  <td colspan="4">
					  <select style="width:650px" class="textbox"  name="evento"  id="evento" onmousewheel="return false"  onchange="javascript:document.iter.nota_edit.value=document.iter.evento.options[document.iter.evento.selectedIndex].text">
								  <option>Seleziona da elenco >>></option>
								<option >Avvio del procedimento</option>
								<option >Richiesta documentazione integrativa</option>
								<option >Pratica presa in carico dall'utente <?=$_SESSION["USER_NAME"]?></option>
							</select>
					  </td>
				  </tr>
					<tr>
						<td width="130" height="24" bgColor="#728bb8"><font color="#ffffff"><b>Evento</b></font></td>
						<td valign="middle" colspan="3">
							<textarea cols="62" rows="2" name="nota_edit" id="nota_edit"></textarea>
						</td>
					</tr>
					<tr>
						<td height="24" valign="top" bgColor="#728bb8"><font color="#ffffff"><b>Data</b></font></td>
						<td width="66" valign="top"><INPUT  maxLength="10" size="10"  class="textbox" name="data" id="data" value="<?=$today?>"></td>
						<td  valign="top"><input type="checkbox"  name="pubblico" checked>
					    <b>Commento pubblicato</b></td>				
						<td  valign="top">
							<input  name="aggiungi"  id="aggiungi" class="hexfield1" style="width:130px" type="submit" value="Aggiungi" onclick="return confirmSubmit()" >
							<input  class="hexfield1" style="width:130px" type="submit" value="Carica Documento" onclick="NewWindow('stp.carica_documento.php?pratica=<?=$idpratica?>','documento',500,200);" >
						</td>
					</tr>
				</table>
				<input type="hidden" name="utente" value="<?=$_SESSION["USER_NAME"]?>">
			  <br>
					<table width="90%">		  	
						<tr>
							<td  bgColor="#728bb8" ><font face="Verdana" color="#FFFFFF" size="2">
								<b>Elenco degli eventi registrati</b></font>	
							</td>
						</tr>
					</table>					
			  </td>
		  </tr>
		  

		  
		  <tr> 
			<td> 
				<!-- contenuto-->
				<?$numrows=$tabella->set_dati("pratica=$idpratica");
				  if ($numrows)  print $tabella->elenco();?>	
				<input type="hidden" name="azione" id="azione" value="aggiungi">
				<input type="hidden"  id="idriga" name="idriga" value="0">
				<input type="hidden" name="mode" value="new">
				<INPUT type="hidden" name="pratica" value="<?=$idpratica?>">
				<INPUT type="hidden" name="chk" value="">
				
				<INPUT type="hidden" name="config_file" value="<?=$tabpath?>/iter_edit.tab">
				<br><br><br>
				<!-- fine contenuto-->			
			</td>
		  </tr>
		  
		  <tr> 
				<!-- riga finale -->
				<td align="left"><img src="images/gray_light.gif" height="2" width="90%"></td>
		   </tr>  
		</TABLE>
		</form>		
		
		<TABLE>
		<FORM method="post" action="praticaweb.php">	
			<tr>
				<td><input name="active_form" type="hidden" value="ce.iter.php">
				<input name="pratica" type="hidden" value="<?=$idpratica?>"></td>
				<INPUT type="hidden" name="comm" value="1">
				<td valign="bottom"><input name="azione" type="submit" class="hexfield" tabindex="14" value="Chiudi"></td>
			</tr>
		</FORM>		
		</TABLE>
	<?
}	
else{
	//-<<<<<<<<<<<<<<<<<<<<<< VISUALIZZA ITER >>>>>>>>>>>>>>>>>>>>>>>>>>>----------------------->	
		$tabella=new tabella_h("$tabpath/iter_view.tab");
		$titolo="Iter della pratica";
		$nrec=$tabella->set_dati("pratica = $idpratica");	?>			
		<H2 class="blueBanner">Iter della pratica</H2>
		<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="100%">		
		  <TR> 
			<TD> 
			<!-- contenuto-->
				<?
					$tabella->set_titolo($titolo,"modifica");
					$tabella->get_titolo();
					if ($nrec)	
						$tabella->elenco();
					else
						print ("<p><b>Nessun evento</b></p>");			
					?>
			<!-- fine contenuto-->
			 </TD>
	      </TR>
		</TABLE>
	
<?}?>		

</body>
</html>
