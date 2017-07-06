var result={};
var dataPost={};
$(document).ready(function(){
    $("#chk-avanzata").bind("change",function(event){
        event.preventDefault();
        $("#ricerca-avanzata").toggle();
    });
    $(".textbox").bind("keyup",function(event){
        if(event.keyCode == 13){
            $("#avvia-ricerca").click();
        }
    });
    $( "#result-container" ).hide();
    $('#btn-report').button({
        icons:{primary:'ui-icon-document'}
    }).bind('click',function(event){
        event.preventDefault();
        $('#frm-report').remove();
        $('body').append('<form id="frm-report" action="./services/xReport.php" method="POST" target="reportPraticaweb"><input type="hidden" value="" name="elenco" id="elencopratiche"/></form>')
        $('#elencopratiche').val($('#elenco').val())
        $('#frm-report').submit();
    });
    $('#btn-back').button({
        icons:{primary:'ui-icon-arrowreturnthick-1-w'}
    }).bind('click',function(event){
        event.preventDefault();
        $( "#result-container" ).hide( 'slide', 500 );
        $( "#ricerca" ).show( 'slide', 500 );
    });
    $('#btn-close').button({
        icons:{primary:'ui-icon-circle-close'}
    }).bind('click',function(event){
        event.preventDefault();
        closeWindow();
    });

    $('#avvia-ricerca').button({
        icons:{primary:'ui-icon-search'}
    }).bind('click',function(event){

        event.preventDefault();
        var oper=$('#op').val();
        dataPost=getSearchFilter();
        $('#ricerca').hide('slide',500);
        $('#result-container').show('slide',500);
        $('#result-table').datagrid({
            title:'Risultato della ricerca',
            url:searchUrl,
            method:'post',
            nowrap:false,
            //columns:colsDef['pratica'],
            fitColumns:false,
            pagination:true,
            autoRowHeight:true,

            queryParams:{data:dataPost,action:'search',op:oper},
            view: myview,
            /*detailFormatter:function(index,row){
                return '<div class="ddv" style="padding:5px 0;background-color:#EEF7FF"></div>';
            },*/
            onLoadSuccess:function(data){
                $('#elenco').val(data['elenco_id']);
            }

        });
    });
});
 