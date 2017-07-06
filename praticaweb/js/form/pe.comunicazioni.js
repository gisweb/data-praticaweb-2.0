/**
 * Created by mamo on 05/07/17.
 */
$(document).ready(function(){
    $("#richiesta_prot").button({
        icons:{primary:'ui-icon-work'}
    }).bind('click',function(event){
        event.preventDefault();
        var data = $("form #comunicazioni").serialize();
        console.log(data);
        /*$.ajax({
            url:localServerUrl,
            type:'POST',
            dataType:'json',
            data:{

            },
            success:function(data){

            }
        })*/
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