/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

 function setValue(d){
	 $.each(d,function(k,v){
		$('#'+k).val(v);
	 });
 }
 
 function createSelect(item){
	var html = '<select id="' + item.id + '" name="' + item.name + '" class="textbox">';
	for(i=0;i<item.opts.length;i++){
		var h = '<option value="' + item.opts[i].value + '">' + item.opts[i].option + '</option>';
		html += h;
	}
	html += '</select>';
	return html;
 }
 
 function createText(item){
	 var html = '<input';
	 $.each(item,function(k,v){
		 var h = ' ' + k + ' = "' + v + '"';
		 html += h;
	 });
	 html += '/>';
	 return html;
 }
 function createCell(item){
	 var html = '\
		<td valigne="middle">\
		%(el)s\
		</td>\
	 ';
	 var htmlElement = '';
	 if (item.type == 'text'){
		 htmlElement = createText(item);
	 }
	 else if (item.type == 'select'){
		 htmlElement = createSelect(item);
	 }
	 else{
		 htmlElement = '';
	 }
	 var elem = {el: htmlElement};
	 var cell = sprintf(html,elem);
	 return cell;
 }
 function createRow(elements){
	 var html = '\
	 <tr>\
		<td class="label">%(labels)s</td>\
		%(columns)s\
	 </tr>\
	';	
	var columns = '';
	var labels = '';
	for(i=0;i<elements.length;i++){
		var el = elements[i];
		columns += createCell(el);
		labels += el.label;
	}
	var row = sprintf(html, {labels: labels, columns: columns});
	return row;
 }

$(document).ready(function(){
	/*var data = {tipo_allegato: 7};
    var item = [{value: 0, option: 'Seleziona =====>'},{value: 10, option: 'Iter 10'},{value: 20, option: 'Iter 20'},{value: 30, option: 'Iter 30'},{value: 40, option: 'Iter 40'},{value: 50, option: 'Iter 50'}];
	var html = '<tr><td>Iter </td><td><select id="iter" name="iter">';
	for(i=0;i<item.length;i++){
		html += '<option value="' + item[i].value + '">' + item[i].option + '</option>'
	}
	html += '</select></td></tr>'
	var newRow = '<tr><td>Elenco Iter</td><td>AAA</td></tr>';
	*/
	var mode = $('#mode').val();
	if (mode == 'new' || mode == 'edit'){
		var html = createRow([{label : 'Iter', type : 'select', id : 'iter', name : 'iter', opts : item}]);
		$('#tipo_allegato').parent().parent().prev().after(html);
		$('#iter').bind('change',function(event){
			var v  = $(this).val();
			var options = $('#tipo_allegato option');
			for (var i = 0; i < options.length; i++){
				var d = $(options[i]).data();
				if (d['id_iter'] == v || d['id_iter'] == 0)
					$(options[i]).css("display", "block");
				else
					$(options[i]).css("display", "none");
			}
		});
		$('#tipo_allegato').bind('change',function(event){
			var d = $('#tipo_allegato option').filter(':selected').attr('data-id_iter');
			$('#iter').val(d);
		});
		//setValue(data);
		$('#tipo_allegato').trigger('change');
	}
	
});