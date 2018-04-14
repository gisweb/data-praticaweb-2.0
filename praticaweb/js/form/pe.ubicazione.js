$(document).ready(function(){
    var cod = $("#cod_belfiore").val();
    $("#sezione option[data-comune!='" + cod + "']").remove();
});
