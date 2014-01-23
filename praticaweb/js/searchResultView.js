var myview = $.extend({}, $.fn.datagrid.defaults.view, {
    renderRow: function(target, fields, frozen, rowIndex, rowData){
        var cc = [];
        var titolo= sprintf('%(tipo_pratica)s n° %(numero)s del %(data_presentazione)s',rowData);
        cc.push('<td colspan=' + fields.length + ' style="padding:10px 5px;border:1px;">');
        if (!frozen){
            cc.push('<table width="100%" style="float:left;margin-left:20px;">');
            cc.push('<tr class="datagrid-row">');
            cc.push('<td colspan="3" field="titolo">');
            cc.push('<div style="height:auto;" class="datagrid-cell"><a href="praticaweb?pratica=' + rowData['pratica'] + '">' + titolo +'</a></div>');
            cc.push('</td>');
            cc.push('</tr>');
            cc.push('<tr class="datagrid-row">');
            cc.push('<td field="numero">');
            cc.push('<div style="height:auto;" class="datagrid-cell"><span class="c-label">N° Pratica : </span>' + rowData["numero"] + '</div>');
            cc.push('</td>');
            cc.push('<td field="tipo_pratica">');
            cc.push('<div style="height:auto;" class="datagrid-cell"><span class="c-label">Tipo : </span>' + rowData["tipo_pratica"] + '</div>');
            cc.push('</td>');
            cc.push('</tr>');
            cc.push('<tr class="datagrid-row">');
            cc.push('<td field="richiedente">');
            cc.push('<div style="height:auto;" class="datagrid-cell"><span class="c-label">Richiedenti : </span>' + rowData["richiedente"] + '</div>');
            cc.push('</td>');
            cc.push('<td field="progettista">');
            cc.push('<div style="height:auto;" class="datagrid-cell"><span class="c-label">Progettisti : </span>' + (rowData["progettista"] || '') + '</div>');
            cc.push('</td>');
            cc.push('</tr>');
            cc.push('</table>');
        }
        cc.push('</td>');
        return cc.join('');
    }
});

