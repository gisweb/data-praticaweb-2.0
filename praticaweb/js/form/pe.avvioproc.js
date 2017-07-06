$(document).ready(function(){
    var mode = $('#mode').val();

    if (mode=='new') $('#tipo').trigger('change');
    if (mode == 'view'){
        var d = $('#cartella').data();
        $('#cartella').bind('click',function(event){
            event.preventDefault();
            $.ajax({
                url:serverUrl,
                type:'POST',
                dataType:'json',
                data:{action:'list-pratiche-folder',pratica:d['pratica'],value:d['cartella']},
                success:function(data){
                    var text = new Array();
                    var row = '';
                    if (data.length>0){
                        text.push('<div id="result-dialog"><ol>');
                        for(i=0;i<data.length;i++){
                            row = sprintf('<li><a style="text-decoration:none;" href="praticaweb.php?pratica=%(pratica)s" target="_new">%(tipo)s n°%(numero)s del %(data)s</a></li>',data[i]);
                            text.push(row)
                        }
                        text.push('</ol></div>');
                    }
                    else{
                        text.push('<b>Nessuna pratica nel faldone</b>');
                    }
                    $(text.join('')).dialog({
                        title:'Pratiche edilizie correlate',
                        width:600,
                        height: 400,
                        modal: true
                    });
                }
            })
        });
    }
    $('#rif_pratica').catcomplete({
      minLength:1,
      source: suggestUrl+'?field=rif_pratica',
      select: function( event, ui ) {
          $('#riferimento').val(ui['item']['id']);
      }
      
    });
});

