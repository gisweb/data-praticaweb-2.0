SELECT DISTINCT app,nominativo FROM stp.richiedenti WHERE pratica=8581
SELECT DISTINCT app,nominativo FROM stp.progettisti WHERE pratica=8581
SELECT DISTINCT presente,nome FROM stp.allegati_check WHERE pratica=8581
SELECT DISTINCT zona FROM stp.zone_prg WHERE pratica=8581
SELECT DISTINCT numero,tipo_intervento,oggetto FROM stp.avv_procedimento WHERE pratica=8581
SELECT DISTINCT zona FROM stp.zone_ptcpi WHERE pratica=8581
SELECT DISTINCT zona FROM stp.zone_ptcpg WHERE pratica=8581
SELECT DISTINCT zona FROM stp.zone_ptcpv WHERE pratica=8581
SELECT vincoli_paesistici FROM stp.vincoli_paesistici(8581);
SELECT vincoli_conformita FROM stp.vincoli_conformita(8581);

ELENCO DEI CICLI
	0
		<span class="iniziocicli">IN_CICLO</span>&nbsp; <br />            <span class="valore">V.richiedenti.app</span>&nbsp; <span class="valore">V.richiedenti.nominativo</span><br />            <span class="finecicli">FI_CICLO</span>	1
		<span class="iniziocicli">IN_CICLO</span><br />            <span class="valore">V.progettisti.app</span>&nbsp;<br />            <span class="valore">V.progettisti.nominativo</span>&nbsp;<br />            <span class="finecicli">FI_CICLO</span>	2
		<span class="iniziocicli">IN_CICLO</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="valore">V.allegati_check.presente</span>&nbsp;<span class="valore">V.allegati_check.nome</span> <br /><span class="finecicli">FI_CICLO</span>	3
		<span class="iniziocicli">IN_CICLO</span><table border="1" cellspacing="1" cellpadding="1" width="100%">    <tbody>        <tr>            <td width="40%" nowrap="nowrap"><span class="valore">V.zone_prg.zona</span></td>            <td width="40%" nowrap="nowrap">&nbsp;</td>        </tr>    </tbody></table><span class="finecicli">FI_CICLO</span>
ELENCO DELLE VISTE
	0
		richiedenti	2
		progettisti	4
		allegati_check	6
		zone_prg	7
		avv_procedimento	10
		zone_ptcpi	11
		zone_ptcpg	12
		zone_ptcpv
ELENCO DEI CAMPI
	TABELLA richiedenti
		app
		nominativo
	TABELLA progettisti
		app
		nominativo
	TABELLA allegati_check
		presente
		nome
	TABELLA zone_prg
		zona
	TABELLA avv_procedimento
		numero
		tipo_intervento
		oggetto
	TABELLA zone_ptcpi
		zona
	TABELLA zone_ptcpg
		zona
	TABELLA zone_ptcpv
		zona

ELENCO DEI TAG
	richiedenti.app
		<span class="valore">V.richiedenti.app</span>
	richiedenti.nominativo
		<span class="valore">V.richiedenti.nominativo</span>
	progettisti.app
		<span class="valore">V.progettisti.app</span>
	progettisti.nominativo
		<span class="valore">V.progettisti.nominativo</span>
	allegati_check.presente
		<span class="valore">V.allegati_check.presente</span>
	allegati_check.nome
		<span class="valore">V.allegati_check.nome</span>
	zone_prg.zona
		<span class="valore">V.zone_prg.zona</span>
	avv_procedimento.numero
		<span class="valore">V.avv_procedimento.numero</span>
	avv_procedimento.tipo_intervento
		<span class="valore">V.avv_procedimento.tipo_intervento</span>
	avv_procedimento.oggetto
		<span class="valore">V.avv_procedimento.oggetto</span>
	zone_ptcpi.zona
		<span class="valore">V.zone_ptcpi.zona</span>
	zone_ptcpg.zona
		<span class="valore">V.zone_ptcpg.zona</span>
	zone_ptcpv.zona
		<span class="valore">V.zone_ptcpv.zona</span>
	vincoli_paesistici.vincoli_paesistici
		<span class="valore">F.vincoli_paesistici.vincoli_paesistici</span>
	vincoli_conformita.vincoli_conformita
		<span class="valore">F.vincoli_conformita.vincoli_conformita</span>
	data.data
		<span class="valore">D.data.data</span>

ELENCO DELLE FUNZIONI
	0
		vincoli_paesistici	1
		vincoli_conformita
ELENCO DEI TERMINATORI DI CICLO
		Nessun terminatore di ciclo trovato

ELENCO DEI DATI
	TABELLA richiedenti
		CAMPO app
			Sig.
	TABELLA richiedenti
		CAMPO nominativo
			Bozzo Pierino
	TABELLA progettisti
		CAMPO app
			Arch.
	TABELLA progettisti
		CAMPO nominativo
			FABBRI Claudia
	TABELLA allegati_check
		CAMPO presente
			x
			x
			x
			x
			x
			x
	TABELLA allegati_check
		CAMPO nome
			Doc. fotografica
			Elaborati grafici
			Fotoinserimento
			Relazione paesaggistica
			Relazione tecnica
			Stralcio PRG/PUC
	TABELLA zone_prg
		CAMPO zona
			AMBITO_6
			ZEC
	TABELLA avv_procedimento
		CAMPO numero
			38/2012
	TABELLA avv_procedimento
		CAMPO tipo_intervento
			NON SPECIFICATO
	TABELLA avv_procedimento
		CAMPO oggetto
			realizzazione posto auto scoperto
	TABELLA zone_ptcpi
		CAMPO zona
			IS_MA_SAT
	TABELLA zone_ptcpg
		CAMPO zona
			MO_A
	TABELLA zone_ptcpv
		CAMPO zona
			COL_ISS_MA
	TABELLA vincoli_paesistici
		CAMPO vincoli_paesistici
			<table border="1" width="100%"><tr><td>AMBITI TERRITORIALI</td><td>Valutazione di conformità</td></tr><tr><td>Ambito di impianto lineare lungo la via Roma</td><td></td></tr></table>
	TABELLA vincoli_conformita
		CAMPO vincoli_conformita
			<table border="1" width="100%"><tr><td>VINCOLI PAESAGGISTICI</td></tr><tr><td>Vincolo Paesistico-Ambientale D.M. 14/12/1959</td></tr></table><br><br><table border="1" width="100%"><tr><td>ALTRI VINCOLI</td></tr><tr><td>Zonizzazione suscettività al dissesto</td></tr><tr><td>Zonizzazione suscettività al dissesto</td></tr></table>

ELENCO DEI DATI OBBLIGATORI
		Nessun dato obbligatorio trovato

ELENCO DEGLI ERRORI
		Nessun errore trovato
