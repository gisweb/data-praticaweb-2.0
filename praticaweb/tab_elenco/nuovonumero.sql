-- Function: pe.nuovo_numero(date, integer)

-- DROP FUNCTION pe.nuovo_numero(date, integer);

CREATE OR REPLACE FUNCTION pe.nuovo_numero(date, integer)
  RETURNS character varying AS
$BODY$

DECLARE
	cont int2;
	scont varchar;
	tipoprat varchar;
	stipo varchar;
	anno varchar;
	
BEGIN
	IF ($2 = 13000) THEN
		stipo:='P';
	ELSIF ($2 = 14000) THEN
		stipo:='DUAP';
	ELSE
		stipo:='';
	END IF; 

	select into anno date_part('year',$1);
	--select into scont lpad(trim(leading '0' from (max(coalesce(substring(numero from 6 for 4),'0')::integer)+1)::varchar),4,'0') from pe.avvioproc where date_part('year',data_presentazione)=date_part('year',$1) and substring(numero from 6 for 4) ~ '^[0-9]{1,9}$';
	select into scont lpad(trim(leading '0' from (max(split_part(regexp_replace(numero,'([A-z]+)',''),'/',2)::integer)+1)::varchar),4,'0') from pe.avvioproc where  date_part('year',data_prot)=date_part('year',$1) and split_part(regexp_replace(numero,'([A-z]+)',''),'/',1)=date_part('year',$1)::varchar;
	
	

	IF scont IS NULL THEN 
		scont='0001';
	END IF;

	return anno||'/'||scont || stipo;
END;
  $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION pe.nuovo_numero(date, integer)
  OWNER TO postgres;
