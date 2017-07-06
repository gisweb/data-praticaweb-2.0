/**
 * Created by mamo on 05/07/17.
 */
$.fn.serializeObject = function() {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};


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