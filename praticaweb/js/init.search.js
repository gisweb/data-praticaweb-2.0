/**
 * Created by mamo on 28/02/17.
 */
var colsDef={
    civici:[[
        {title:'Indirizzo',field:'indirizzo',sortable:true,width:1000},
        //{title:'Via',field:'via',sortable:true,width:500},
        //{title:'Civico',field:'civico',sortable:true,width:100}
    ]],
    scadenze:[[
        {title:'Data Scadenza',field:'scadenza',sortable:true,width:200},
        {title:'GG. alla Scadenza',field:'diff',sortable:true,width:100},
        {title:'Scadenza',field:'testo',sortable:true,width:400},
        {title:'',field:'pratica',sortable:false,width:300,formatter: function(value,row,index){return '<a target="new" href="praticaweb.php?pratica=' + value + '">' + row['tipo_pratica'] + ' n° ' + row['numero'] + ' del ' + row['data_presentazione'] + '</a>'}},
        {title:'',hidden:true,field:'tipo_pratica',sortable:true},
        {title:'Tipo Scadenza',hidden:true,field:'cod_scadenza',sortable:true,width:200},
        {title:'',hidden:true,field:'numero',sortable:true}
    ]],
    pratica:[[
        {title:'',field:'pratica',sortable:false,width:20,formatter: function(value,row,index){return '<a target="new" href="praticaweb.php?pratica=' + value + '"><div class="ui-icon ui-icon-search"/></a>'}},
        {title:'Tipo Pratica',field:'tipo_pratica',sortable:true,width:150},
        {title:'Numero',field:'numero',sortable:true,width:100},
        {title:'Protocollo',sortable:true,field:'protocollo',width:100},
        {title:'Data Prot.',sortable:true,field:'data_prot',width:100},

        {title:'Intervento',sortable:true,field:'tipo_intervento',width:150},
        {title:'Oggetto',sortable:true,field:'oggetto',width:350}
    ]],
    online:[[
        {title:'',field:'pratica',sortable:false,width:20,formatter: function(value,row,index){return '<a target="new" href="praticaweb.php?pratica=' + value + '"><div class="ui-icon ui-icon-search"/></a>'}},
        {title:'Tipo Pratica',field:'tipo_pratica',sortable:true,width:150,styler: function(value,row,index){return 'font-size:11px;';}},
        {title:'Numero',field:'numero',sortable:true,width:70,styler: function(value,row,index){return 'font-size:11px;';}},
        {title:'Data Pres.',sortable:true,field:'data_presentazione',width:100,styler: function(value,row,index){return 'font-size:11px;';}},
        {title:'Prot.',sortable:true,field:'protocollo',width:50,styler: function(value,row,index){return 'font-size:11px;';}},
        {title:'Data Prot.',sortable:true,field:'data_prot',width:80,styler: function(value,row,index){return 'font-size:11px;';}},
        //{title:'Intervento',sortable:true,field:'tipo_intervento',width:150,formatter: function(value,row,index){if (value) return value; else return 'Da Definire';},styler: function(value,row,index){return 'font-size:11px;';}},
        {title:'Oggetto',sortable:true,field:'oggetto',width:150,styler: function(value,row,index){return 'font-size:11px;';}},
        {title:'Richiedenti',sortable:true,field:'richiedente',width:150,styler: function(value,row,index){return 'font-size:11px;';}},
        {title:'Progettista',sortable:true,field:'progettista',width:150,styler: function(value,row,index){return 'font-size:11px;';}},
        {title:'Responsabile',sortable:true,field:'responsabile',width:100,styler: function(value,row,index){return 'font-size:11px;';}},
        {title:'Assegnata',sortable:true,field:'assegnata_istruttore',width:70,styler: function(value,row,index){return 'font-size:11px;';},formatter:function(value,row,index){if (value) return 'SI'; else return 'NO';}},
        {title:'Istruttore',sortable:true,field:'responsabile_it',width:100,styler: function(value,row,index){return 'font-size:11px;';}}
    ]],
    delete:[[
        {title:'',field:'pratica',sortable:false,width:40,formatter: function(value,row,index){return '<input type="radio" data-testo="' + row['numero'] + '" name="pratica" id="' + value + '"class="textbox delete-radio"/>'}},
        {title:'Tipo Pratica',field:'tipo_pratica',sortable:true,width:150},
        {title:'Numero',field:'numero',sortable:true,width:100},
        {title:'Protocollo',sortable:true,field:'protocollo',width:100},
        {title:'Data Prot.',sortable:true,field:'data_prot',width:100},
        {title:'Oggetto',sortable:true,field:'oggetto',width:350},
        {title:'Richiedenti',sortable:true,field:'richiedente',width:350}
    ]],
    draw:[[
        {title:'',field:'pratica',sortable:false,width:40,formatter: function(value,row,index){return '<a target="new" href="praticaweb.php?pratica=' + value + '"><div class="ui-icon ui-icon-search"/></a>'}},
        {title:'Tipologia Sorteggio',field:'tipo',sortable:true,width:250},
        {title:'Tipo Pratica',field:'tipo_pratica',sortable:true,width:250},
        //{title:'Categoria',sortable:true,field:'categoria',width:300},
        {title:'Numero',field:'numero',sortable:true,width:100},
        {title:'Data Sorteggio',sortable:true,field:'data_sorteggio',width:100},
        {title:'Data Avvio',sortable:true,field:'data_avvio',width:100},
        {title:'Resp Proc',sortable:true,field:'resp_proc',width:200},

    ]],
    default_cols:[[
        {title:'',sortable:true,field:'',width:100},
    ]]

}