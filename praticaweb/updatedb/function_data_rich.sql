DROP TYPE stp.soggetti cascade;

CREATE TYPE stp.soggetti AS
   (pratica integer,
    el_nominativi text,
    el_citta_nas text,
    el_citta_res text,
    el_indi_res text,
    el_residenza text
    );
ALTER TYPE stp.soggetti OWNER TO postgres;



CREATE OR REPLACE FUNCTION stp.data_rich(pr integer)
  RETURNS stp.soggetti AS
$BODY$
DECLARE
	rec record;
	res stp.soggetti;
BEGIN
	select into rec
	array_to_string(array_agg(case when coalesce(piva,'')<>'' then ragsoc else cognome || ' ' || nome end),',') as nominativo,
	array_to_string(array_agg(case when coalesce(piva,'')<>'' then null else comunato end),',') as comune_nascita,
	array_to_string(array_agg(case when coalesce(piva,'')<>'' then comuned else comune end),',') as comune_residenza,
	array_to_string(array_agg(case when coalesce(piva,'')<>'' then sede else indirizzo end),',') as indirizzo_residenza,
	array_to_string(array_agg(case when coalesce(piva,'')<>'' then coalesce(sede,'') || ' - ' || coalesce(comuned,'')|| ' - ' || coalesce(capd,'') else coalesce(indirizzo,'') || ' - ' || coalesce(comune,'')|| ' - ' || coalesce(cap,'')  end),',') as residenza
	from 
	pe.soggetti 
	where
	richiedente=1 and voltura=0 and pratica=pr;
	res.pratica:=pr;
	res.el_nominativi:=rec.nominativo;
	res.el_citta_nas:=rec.comune_nascita;
	res.el_citta_res:=rec.comune_residenza;
	res.el_indi_res:=rec.indirizzo_residenza;
	res.el_residenza:=rec.residenza;
	return res;
END
$BODY$
  LANGUAGE plpgsql IMMUTABLE
  COST 100;
ALTER FUNCTION stp.data_rich(integer) OWNER TO postgres;

select * from stp.data_rich(33568)
