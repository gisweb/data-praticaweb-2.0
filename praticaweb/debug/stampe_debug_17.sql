SELECT DISTINCT zona FROM stp.zone_prg WHERE pratica=8490
SELECT DISTINCT app,nominativo FROM stp.richiedenti WHERE pratica=8490
SELECT DISTINCT presente,nome FROM stp.allegati_check WHERE pratica=8490
SELECT DISTINCT zona,descr FROM stp.zone_suscettibilita WHERE pratica=8490
SELECT DISTINCT oggetto,numero,tipo_pratica FROM stp.avv_procedimento WHERE pratica=8490
SELECT DISTINCT indirizzo FROM stp.ubicazione WHERE pratica=8490
SELECT DISTINCT zona FROM stp.zone_ptcpi WHERE pratica=8490
SELECT DISTINCT zona FROM stp.zone_ptcpg WHERE pratica=8490
SELECT DISTINCT zona FROM stp.zone_ptcpv WHERE pratica=8490
SELECT lista_catasto_terreni FROM stp.lista_catasto_terreni(8490);
SELECT vincoli_paesistici FROM stp.vincoli_paesistici(8490);
SELECT vincoli_conformita FROM stp.vincoli_conformita(8490);

ELENCO DEI CICLI
	0
		<span class="iniziocicli">IN_CICLO</span><br />            <span class="valore">V.zone_prg.zona</span>&nbsp;<br />            <span class="finecicli">FI_CICLO</span>	1
		<span class="iniziocicli">IN_CICLO</span>&nbsp; <br />            <span class="valore">V.richiedenti.app</span>&nbsp; <span class="valore">V.richiedenti.nominativo</span></i><br />            <i><span class="finecicli">FI_CICLO</span>	2
		<span class="iniziocicli">IN_CICLO</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="valore">V.allegati_check.presente</span>&nbsp;<span class="valore">V.allegati_check.nome</span> <br /><span class="finecicli">FI_CICLO</span>	3
		<span class="iniziocicli">IN_CICLO</span><table border="1" cellspacing="1" cellpadding="1" width="100%">    <tbody>        <tr>            <td width="40%" nowrap="nowrap"><span class="valore">V.zone_prg.zona</span></td>            <td width="10%" nowrap="nowrap">&nbsp;</td>            <td width="10%" nowrap="nowrap">&nbsp;</td>            <td width="40%" nowrap="nowrap">&nbsp;</td>        </tr>    </tbody></table><span class="finecicli">FI_CICLO</span>	4
		<span class="iniziocicli">IN_CICLO</span> <span class="valore">V.zone_suscettibilita.zona</span> <span class="finecicli">FI_CICLO</span>
ELENCO DELLE VISTE
	0
		zone_prg	1
		richiedenti	3
		allegati_check	6
		zone_suscettibilita	7
		avv_procedimento	8
		ubicazione	11
		zone_ptcpi	12
		zone_ptcpg	13
		zone_ptcpv
ELENCO DEI CAMPI
	TABELLA zone_prg
		zona
	TABELLA richiedenti
		app
		nominativo
	TABELLA allegati_check
		presente
		nome
	TABELLA zone_suscettibilita
		zona
		descr
	TABELLA avv_procedimento
		oggetto
		numero
		tipo_pratica
	TABELLA ubicazione
		indirizzo
	TABELLA zone_ptcpi
		zona
	TABELLA zone_ptcpg
		zona
	TABELLA zone_ptcpv
		zona

ELENCO DEI TAG
	zone_prg.zona
		<span class="valore">V.zone_prg.zona</span>
	richiedenti.app
		<span class="valore">V.richiedenti.app</span>
	richiedenti.nominativo
		<span class="valore">V.richiedenti.nominativo</span>
	allegati_check.presente
		<span class="valore">V.allegati_check.presente</span>
	allegati_check.nome
		<span class="valore">V.allegati_check.nome</span>
	zone_suscettibilita.zona
		<span class="valore">V.zone_suscettibilita.zona</span>
	avv_procedimento.oggetto
		<span class="valore">V.avv_procedimento.oggetto</span>
	ubicazione.indirizzo
		<span class="valore">V.ubicazione.indirizzo</span>
	lista_catasto_terreni.lista_catasto_terreni
		<span class="valore">F.lista_catasto_terreni.lista_catasto_terreni</span>
	avv_procedimento.numero
		<span class="valore">V.avv_procedimento.numero</span>
	avv_procedimento.tipo_pratica
		<span class="valore">V.avv_procedimento.tipo_pratica</span>
	vincoli_paesistici.vincoli_paesistici
		<span class="valore">F.vincoli_paesistici.vincoli_paesistici</span>
	vincoli_conformita.vincoli_conformita
		<span class="valore">F.vincoli_conformita.vincoli_conformita</span>
	zone_ptcpi.zona
		<span class="valore">V.zone_ptcpi.zona</span>
	zone_ptcpg.zona
		<span class="valore">V.zone_ptcpg.zona</span>
	zone_ptcpv.zona
		<span class="valore">V.zone_ptcpv.zona</span>
	zone_suscettibilita.descr
		<span class="valore">V.zone_suscettibilita.descr</span>
	data.data
		<span class="valore">D.data.data</span>

ELENCO DELLE FUNZIONI
	0
		lista_catasto_terreni	1
		vincoli_paesistici	2
		vincoli_conformita
ELENCO DEI TERMINATORI DI CICLO
		Nessun terminatore di ciclo trovato

ELENCO DEI DATI
	TABELLA zone_prg
		CAMPO zona
			AMBITO_8
			ZBS
	TABELLA richiedenti
		CAMPO app
			Sig.ra
	TABELLA richiedenti
		CAMPO nominativo
			CONSIGLIERE LUISA TERESA
	TABELLA allegati_check
		CAMPO presente
			
	TABELLA allegati_check
		CAMPO nome
			
	TABELLA zone_suscettibilita
		CAMPO zona
			PG3A
	TABELLA zone_suscettibilita
		CAMPO descr
			Suscettività al dissesto (D.L. 180)
	TABELLA avv_procedimento
		CAMPO oggetto
			AUMENTO DI SUPERFICIE AL PIANO PRIMO ED AMPLIAMENTO LOCALE VERANDA AL PIANO TERRA.
	TABELLA avv_procedimento
		CAMPO numero
			C56/2004
	TABELLA avv_procedimento
		CAMPO tipo_pratica
			Condono Edilizio 2004
	TABELLA ubicazione
		CAMPO indirizzo
			Via S. Gaetano 3
	TABELLA zone_ptcpi
		CAMPO zona
			ID_MA
	TABELLA zone_ptcpg
		CAMPO zona
			MO_A
	TABELLA zone_ptcpv
		CAMPO zona
			COL_ISS_MA
	TABELLA lista_catasto_terreni
		CAMPO lista_catasto_terreni
			Foglio : 7, Mappale : 110
	TABELLA vincoli_paesistici
		CAMPO vincoli_paesistici
			<table border="1" width="100%"><tr><td>AMBITI TERRITORIALI</td><td>Valutazione di conformità</td></tr><tr><td>Ambito di impianto rurale</td><td></td></tr></table>
	TABELLA vincoli_conformita
		CAMPO vincoli_conformita
			<table border="1" width="100%"><tr><td>VINCOLI PAESAGGISTICI</td></tr><tr><td>Vincolo Paesistico-Ambientale D.M. 28/01/1949</td></tr><tr><td>Territori costieri</td></tr></table><br><br><table border="1" width="100%"><tr><td>ALTRI VINCOLI</td></tr><tr><td>Zonizzazione suscettività al dissesto</td></tr></table>

ELENCO DEI DATI OBBLIGATORI
		Nessun dato obbligatorio trovato

ELENCO DEGLI ERRORI
		Nessun errore trovato
