var myview = $.extend({}, $.fn.datagrid.defaults.view, {
    renderRow: function(target, fields, frozen, rowIndex, rowData){
        var cc = [];
        var titolo= sprintf('%(tipo_pratica)s n° %(numero)s del %(data_presentazione)s',rowData);
        cc.push('<td colspan=' + fields.length + ' style="padding:0px 0px;">');
        if (!frozen){
            cc.push('<table style="float:left;margin-left:10px;width:1200px;" class="stiletabella">');
            cc.push('<tr class="">');
            cc.push('<td colspan="3" field="titolo" style="border:1px 1px 0px 0px dotted #CCCCCC;">');
            cc.push('<div style="height:auto;" class=""><a href="praticaweb.php?pratica=' + rowData['pratica'] + '" target="Praticaweb">' + titolo +'</a></div>');
            cc.push('</td>');
            cc.push('</tr>');
            cc.push('<tr class="">');
            cc.push('<td field="oggetto" colspan="3" style="word-wrap:break-word !important">');
            cc.push('<div style="height:auto;" class=""><span class="c-label">Oggetto : </span>' + rowData["oggetto"] + '</div>');
            cc.push('</td>');
            cc.push('</tr>');
            cc.push('<tr class="">');
            cc.push('<td field="ubicazione" colspan="3">');
            cc.push('<div style="height:auto;" class=""><span class="c-label">Ubicazione : </span>' + (rowData["ubicazione"]|| '') + '</div>');
            cc.push('</td>');
            cc.push('</tr>');
            cc.push('<tr class="">');
            cc.push('<td field="richiedente">');
            cc.push('<div style="height:auto;" class=""><span class="c-label">Richiedenti : </span>' + rowData["richiedente"] + '</div>');
            cc.push('</td>');
            cc.push('<td field="progettista">');
            cc.push('<div style="height:auto;" class=""><span class="c-label">Progettisti : </span>' + (rowData["progettista"] || '') + '</div>');
            cc.push('</td>');
            cc.push('</tr>');
            
            cc.push('<tr class="">');
            cc.push('<td field="elenco_ct">');
            cc.push('<div style="height:auto;" class=""><span class="c-label">C.T. : </span>' + (rowData["elenco_ct"]|| '') + '</div>');
            cc.push('</td>');
            cc.push('<td field="elenco_cu">');
            cc.push('<div style="height:auto;" class=""><span class="c-label">C.U. : </span>' + (rowData["elenco_cu"]|| '') + '</div>');
            cc.push('</td>');
            cc.push('</tr>');
            
            cc.push('</table>');
        }
        cc.push('</td>');
        return cc.join('');
    }
});

