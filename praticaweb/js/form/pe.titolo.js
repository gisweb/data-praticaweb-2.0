$(document).ready(function(){
    var mode=$('#mode').val();
    if (mode=='new'){
        $("#intervento").text($('#hidden-oggetto').val());
    }
});

