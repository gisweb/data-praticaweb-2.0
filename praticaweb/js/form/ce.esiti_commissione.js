/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function(){
    var codice = $("#codice_ente").val();
    if (codice=='clp'){
        var testo = $("#testo").val();
        if (!testo) $("#testo").val("La Commissione Locale per il Paesaggio,  all'unanimità, ");
    }
          
});

