SELECT DISTINCT zona FROM stp.zone_prg WHERE pratica=8064
SELECT DISTINCT app,nominativo FROM stp.richiedenti WHERE pratica=8064
SELECT DISTINCT zona,descr FROM stp.zone_suscettibilita WHERE pratica=8064
SELECT DISTINCT oggetto,numero,tipo_pratica FROM stp.avv_procedimento WHERE pratica=8064
SELECT DISTINCT indirizzo FROM stp.ubicazione WHERE pratica=8064
SELECT DISTINCT zona FROM stp.zone_ptcpi WHERE pratica=8064
SELECT DISTINCT zona FROM stp.zone_ptcpg WHERE pratica=8064
SELECT DISTINCT zona FROM stp.zone_ptcpv WHERE pratica=8064
SELECT lista_catasto_terreni FROM stp.lista_catasto_terreni(8064);
SELECT vincoli_paesistici FROM stp.vincoli_paesistici(8064);
SELECT vincoli_conformita FROM stp.vincoli_conformita(8064);

ELENCO DEI CICLI
	0
		<span class="iniziocicli">IN_CICLO</span><br />            <span class="valore">V.zone_prg.zona</span>&nbsp;<br />            <span class="finecicli">FI_CICLO</span>	1
		<span class="iniziocicli">IN_CICLO</span>&nbsp; <br />            <span class="valore">V.richiedenti.app</span>&nbsp; <span class="valore">V.richiedenti.nominativo</span></i><br />            <i><span class="finecicli">FI_CICLO</span>	2
		<span class="iniziocicli">IN_CICLO</span><table cellspacing="1" cellpadding="1" border="1" width="100%">    <tbody>        <tr>            <td nowrap="nowrap" width="40%"><span class="valore">V.zone_prg.zona</span></td>            <td nowrap="nowrap" width="10%">&nbsp;</td>            <td nowrap="nowrap" width="10%">&nbsp;</td>            <td nowrap="nowrap" width="40%">&nbsp;</td>        </tr>    </tbody></table><span class="finecicli">FI_CICLO</span>	3
		<span class="iniziocicli">IN_CICLO</span> <span class="valore">V.zone_suscettibilita.zona</span> <span class="finecicli">FI_CICLO</span>
ELENCO DELLE VISTE
	0
		zone_prg	1
		richiedenti	4
		zone_suscettibilita	5
		avv_procedimento	6
		ubicazione	9
		zone_ptcpi	10
		zone_ptcpg	11
		zone_ptcpv
ELENCO DEI CAMPI
	TABELLA zone_prg
		zona
	TABELLA richiedenti
		app
		nominativo
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
			ZEC
	TABELLA richiedenti
		CAMPO app
			Sig.
	TABELLA richiedenti
		CAMPO nominativo
			MIGONE ANDREA
	TABELLA zone_suscettibilita
		CAMPO zona
			PG3A
	TABELLA zone_suscettibilita
		CAMPO descr
			Suscettività al dissesto (D.L. 180)
	TABELLA avv_procedimento
		CAMPO oggetto
			Realizzazione magazzino agricolo interrato
	TABELLA avv_procedimento
		CAMPO numero
			37/2008
	TABELLA avv_procedimento
		CAMPO tipo_pratica
			Permesso di Costruire
	TABELLA ubicazione
		CAMPO indirizzo
			Via Teriasca
	TABELLA zone_ptcpi
		CAMPO zona
			ID_MA
			IS_MA
	TABELLA zone_ptcpg
		CAMPO zona
			MO_A
	TABELLA zone_ptcpv
		CAMPO zona
			COL_ISS_MA
	TABELLA lista_catasto_terreni
		CAMPO lista_catasto_terreni
			Foglio : 7, Mappale : 12
	TABELLA vincoli_paesistici
		CAMPO vincoli_paesistici
			<table border="1" width="100%"><tr><td>AMBITI TERRITORIALI</td><td>Valutazione di conformità</td></tr></table>
	TABELLA vincoli_conformita
		CAMPO vincoli_conformita
			<table border="1" width="100%"><tr><td>VINCOLI PAESAGGISTICI</td></tr><tr><td>Vincolo Paesistico-Ambientale D.M. 14/12/1959</td></tr></table><br><br><table border="1" width="100%"><tr><td>ALTRI VINCOLI</td></tr><tr><td>Zonizzazione suscettività al dissesto</td></tr></table>

ELENCO DEI DATI OBBLIGATORI
		Nessun dato obbligatorio trovato

ELENCO DEGLI ERRORI
		Nessun errore trovato
