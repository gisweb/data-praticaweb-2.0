/**
 * Created by mamo on 05/07/17.
 */
$(document).ready(function(){
    $("#richiesta_prot").button({
        icons:{primary:'ui-icon-work'}
    }).bind('click',function(event){
        event.preventDefault();
        var formData = $("form #comunicazioni").serializeObject();

        $.ajax({
            url:"/services/local/xServer.php",
            type:'POST',
            dataType:'json',
            data:formData,
            success:function(data){
				alert("Done");
            }
        });
    });
	$("#tipo_comunicazione").change(function () {
		var v = $(this).val();
		if (!v) $("#destinatari").children('option').show();
		else {
			$("#destinatari").children('option').hide();
			$("#destinatari").children("option[data-metodo='" + v + "']").show()
		}
		
	});
	$("#tipo_comunicazione").trigger("change");
});