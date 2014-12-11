DROP TYPE stp.tpratica cascade;

CREATE TYPE stp.tpratica AS
   (pratica integer,
    numero character varying,
    prot character varying,
    d_prot date,
    d_pres date,
    dirigente character varying,
    rup character varying,
    it character varying,
    ia character varying,
    oggetto text,
    tipo_pratica character varying,
    el_rich text,
    el_conc text,
    el_prop text,
    el_prog text,
    el_dlav text,
    el_datirich text,
    el_daticonc text,
    el_datiprop text,
    el_datiprog text,
    el_datidlav text,
    et_rich text,
    et_conc text,
    et_prop text,
    et_prog text,
    et_dlav text,
    el_indi text,
    el_cata text,
    el_zprg text,
    li_pareri text,
    d_it date,
    esito_it character varying,
    testo_it text,
    prescr_it text,
    aut_paes text,
    tot_oc character varying,
    tot_cc character varying,
    tot_ou character varying,
    tot_co character varying,
	tot_oi character varying,
    r1_oc character varying,
    n1_oc character varying,
    d1_oc date,
    r2_oc character varying,
    n2_oc character varying,
    d2_oc date,
    r3_oc character varying,
    n3_oc character varying,
    d3_oc date,
    r1_co character varying,
    n1_co character varying,
    d1_co date,
    r2_co character varying,
    n2_co character varying,
    d2_co date,
	r1_oi character varying,
    n1_oi character varying,
    d1_oi date,
    fidejussione character varying,
    fido_oc text,
    fido_cm text,
    allegati_mancanti text,
    allegati_presenti text,
    el_atti text,
    d_tit date,
    n_tit text);
ALTER TYPE stp.tpratica OWNER TO postgres;



CREATE OR REPLACE FUNCTION stp.pratica(pr integer)
  RETURNS stp.tpratica AS
$BODY$
DECLARE
	rec record;
	i integer;
	aux varchar;
	r stp.tpratica;
	o stp.oneri;
	co stp.corr_monetario;
	ap integer;
BEGIN
	r.pratica:=pr;
	for rec in select A.numero,A.protocollo as prot,A.data_presentazione as d_pres,A.data_prot as d_prot,A.oggetto,B.nome AS rup,C.nome AS it,D.nome AS ia,E.nome as tipo_pratica,coalesce(aut_amb,0) as autpaes,F.importo as diritti_segreteria,data_vers_oi,n_vers_oi,importo_vers_oi from pe.avvioproc A left join admin.users B on (resp_proc=B.userid) left join admin.users C on (resp_it=C.userid) left join admin.users D on (resp_ia=D.userid) left join pe.e_tipopratica E on(A.tipo=E.id) left join pe.e_diritti_segreteria F on(A.diritti_segreteria=F.id) where pratica = pr loop
		r.numero:=rec.numero;
		r.prot:=rec.prot;
		r.d_prot:=rec.d_prot;
		r.oggetto:=rec.oggetto;
		r.d_pres:=rec.d_pres;
		r.rup:=rec.rup;
		r.ia:=rec.ia;
		r.it:=rec.it;
		r.tipo_pratica:=rec.tipo_pratica;
        r.tot_oi:=rec.diritti_segreteria;
        r.r1_oi:=rec.importo_vers_oi;
        r.d1_oi:=rec.data_vers_oi;
        r.n1_oi:=rec.n_vers_oi;
		ap:=rec.autpaes;
	end loop;
		
	select into r.dirigente dirigente from stp.dirigente where pratica=pr;
	/*SEZIONE ALLEGATI*/
	r.allegati_mancanti:=stp.elenco_allegati_mancanti(pr);
	r.allegati_presenti:=stp.elenco_allegati_presenti(pr);
	/*SEZIONE SOGGETTI*/
	r.el_rich:=stp.elenco_richiedenti(pr);
	r.el_conc:=stp.elenco_concessionari(pr);
	r.el_prop:=stp.elenco_proprietari(pr);
	r.el_prog:=stp.elenco_datiprogettisti(pr);
	r.el_dlav:=stp.elenco_datidirettorelavori(pr);
	r.et_rich:=stp.etichette_richiedenti(pr);
	r.et_conc:=stp.etichette_concessionari(pr);
	r.et_prop:=stp.etichette_proprietari(pr);
	r.et_prog:=stp.etichette_progettisti(pr);
	r.et_dlav:=stp.etichette_direttorelavori(pr);
	r.el_datirich:=stp.elenco_datirichiedenti(pr);
	r.el_daticonc:=stp.elenco_daticoncessionari(pr);
	r.el_datiprop:=stp.elenco_datiproprietari(pr);
	r.el_datiprog:=stp.elenco_datiprogettisti(pr);
	r.el_datidlav:=stp.elenco_datidirettorelavori(pr);
	/*SEZIONE INDIRIZZI*/
	r.el_indi:=stp.elenco_indirizzi(pr);
	r.el_cata:=stp.elenco_catasto(pr);
	r.el_zprg:=stp.elenco_zoneprg(pr);
	/*SEZIONE PARERI*/
	r.li_pareri:=stp.elenco_pareri_favorevoli(pr);
	select into rec A.data_ril as d_it,B.nome as esito_it,prescrizioni as prescr_it,testo as testo_it from pe.pareri A left join pe.e_pareri B on (A.parere=B.id) where pratica=pr and ente=1 order by data_ril desc limit 1;
	r.d_it:=rec.d_it;
	r.esito_it:=rec.esito_it;
	r.testo_it:=rec.testo_it;
	r.prescr_it:=rec.prescr_it;

	select into rec titolo,to_char(data_rilascio,'DD/MM/YYYY') as data from pe.titolo where pratica=ap;
	r.aut_paes:='VISTA l''Autorizzazione Paesaggistica, Provvedimento Comunale n.' || rec.titolo || ' del ' || rec.data || ' ai sensi dell''art. 146 del D.Lgs. 22/01/2004, n.42  « Codice dei Beni Culturali e del Paesaggio »;';
	/*SEZIONE ONERI*/
	select into o * from stp.info_oneri(pr);
	r.tot_oc:=o.totale;
	r.tot_cc:=o.costo_costruzione;
	r.tot_ou:=o.urbanizzazione;
	r.r1_oc:=o.rata_1_versato;
	r.n1_oc:=o.rata_1_quietanza;
	r.d1_oc:=o.rata_1_dataversamento;
	r.r2_oc:=o.rata_2_versato;
	r.n2_oc:=o.rata_2_quietanza;
	r.d2_oc:=o.rata_2_dataversamento;
	r.r3_oc:=o.rata_3_versato;
	r.n3_oc:=o.rata_3_quietanza;
	r.d3_oc:=o.rata_3_dataversamento;	
	/*SEZIONE CORRISPETTIVO MONETARIO*/
	select into co * from stp.info_corr_monetario(pr);
	r.tot_co:=co.totale;
	r.r1_co:=co.rata_1_versato;
	r.n1_co:=co.rata_1_quietanza;
	r.d1_co:=co.rata_1_dataversamento;
	r.r2_co:=co.rata_2_versato;
	r.n2_co:=co.rata_2_quietanza;
	r.d2_co:=co.rata_2_dataversamento;
	/*SEZIONE ONERI ISTRUTTORIA*/
	
	/*SEZIONE FIDEJUSSIONE*/
	r.fido_oc:=stp.fido_oc(pr);
    r.fido_cm:=stp.fido_cm(pr);
	/*SEZIONE ATTI*/
	/*aux:='';
	for rec in select X.*,Y.nome as tipo_atto from pe.atti X inner join pe.e_atti Y on (X.tipo=Y.id) where pratica=pr loop
		aux:=aux || 'VISTO '||coalesce(rec.tipo_atto,'')||' dal Notaio '||coalesce(rec.notaio,'')||' , in data,'|| coalesce(To_char(rec.data_reg,'DD/MM/YYYY'),'')||' Rep. n. '||coalesce(rec.numero,'');
	end loop;*/
	r.el_atti:=stp.elenco_atti(pr);
	/*SEZIONE TITOLO*/
	select into rec titolo,to_char(data_rilascio,'DD/MM/YYYY') as data from pe.titolo WHERE pratica=pr;
	r.n_tit:=rec.titolo;
	r.d_tit:=rec.data;
	return r;
END
$BODY$
  LANGUAGE plpgsql IMMUTABLE
  COST 100;
ALTER FUNCTION stp.pratica(integer) OWNER TO postgres;


CREATE OR REPLACE FUNCTION stp.pratica_ap(pr integer)
  RETURNS stp.tpratica AS
$BODY$
DECLARE
	r stp.tpratica;
	rif integer;
BEGIN
	
	select into rif coalesce(aut_amb,0) from pe.avvioproc where pratica = pr;
	select into r * from stp.pratica(rif);
	r.pratica:=pr;
	return r;
	
END
$BODY$
  LANGUAGE plpgsql IMMUTABLE
  COST 100;
ALTER FUNCTION stp.pratica_ap(integer) OWNER TO postgres;


CREATE OR REPLACE FUNCTION stp.pratica_rif(pr integer)
  RETURNS stp.tpratica AS
$BODY$
DECLARE
	r stp.tpratica;
	rif integer;
BEGIN
	
	select into rif coalesce(riferimento,0) from pe.avvioproc where pratica = pr;
	select into r * from stp.pratica(rif);
	r.pratica:=pr;
	return r;
	
END
$BODY$
  LANGUAGE plpgsql IMMUTABLE
  COST 100;
ALTER FUNCTION stp.pratica_rif(integer) OWNER TO postgres;
