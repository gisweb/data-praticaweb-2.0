$(document).ready(function(){
    var mode = $('#mode').val();

    if (mode=='new') {
        $('#tipo').trigger('change');
        $("#form-avvioproc").bind('submit',function(){
            $('<div id="wait-div"><center style="font-size:16px;font-weight:bold;">Attendere prego, salvataggio in corso.....</center></div>').dialog({
                height:200,
                width:400,
                title:'Attendere'
            });
            var data_pres=$('#data_presentazione').val();
            var prot=$('#protocollo').val();
            var save = true;
            var testo = '';
            $.ajax({
                url:searchUrl,
                data:{action:'search',op: 'AND',data:{'pe.avvioproc':["protocollo ilike '" + prot + "'","anno=date_part('year','" + data_pres + "'::date)"]}},
                async : false,
                method:'post',
                success:function(data){
                    $('#wait-div').dialog('destroy');
                    if (data['total']>0) {
                        save = false;
                        testo = sprintf('La <b style="font-size:13px">%(tipo_pratica)s</b> numero <b>%(numero)s</b> con oggetto <b style="font-size:13px">"%(oggetto)s"</b> assegnata a <b style="font-size:13px">%(responsabile)s</b> è gia stata inserita.',data['rows'][0]);
                         $(sprintf('<div style="font-size:12px;">%s</div>',testo)).dialog({
                            height:400,
                            width:600,
                            title:'Attenzione'
                        });
                    }
                }
                
            });
           
            return save;
        });
    }
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
    
        
});