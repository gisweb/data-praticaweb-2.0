/**
 * Created by mamo on 05/07/17.
 */
$.fn.serializeObject = function() {
    var o = {};
    //    var a = this.serializeArray();
    $(this).find('input[type="hidden"], input[type="text"], input[type="password"], input[type="checkbox"]:checked, input[type="radio"]:checked, select').each(function() {
        if ($(this).attr('type') == 'hidden') { //if checkbox is checked do not take the hidden field
            var $parent = $(this).parent();
            var $chb = $parent.find('input[type="checkbox"][name="' + this.name.replace(/\[/g, '\[').replace(/\]/g, '\]') + '"]');
            if ($chb != null) {
                if ($chb.prop('checked')) return;
            }
        }
        if (this.name === null || this.name === undefined || this.name === '')
            return;
        var elemValue = null;
        if ($(this).is('select'))
            elemValue = $(this).val();
        else
            elemValue = $(this).val();
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(elemValue || '');
        } else {
            o[this.name] = elemValue || '';
        }
    });
    return o;
}

$(document).ready(function(){
/*    $("#richiesta_prot").button({
        icons:{primary:'ui-icon-gear'}
    }).bind('click',function(event){
        event.preventDefault();
        var formData = $("#comunicazioni").serializeObject();
        formData["azione"]="protocolla";
        $.ajax({
            url:"/services/local/xServer.php",
            type:'POST',
            dataType:'json',
            data:formData,
            success:function(data){

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
}
*/
);
