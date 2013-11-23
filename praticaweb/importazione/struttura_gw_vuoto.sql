/*STRUTTURA DEL DATABASE */

/*FUNZIONI*/
--Funzione che restituisce il nome della sequenza della tabella
CREATE OR REPLACE FUNCTION getSequence(tableName varchar) RETURNS varchar AS $$
DECLARE
	res varchar;
BEGIN
SELECT DISTINCT a.relname AS sequence_name INTO res
FROM
(
SELECT CASE 
WHEN UPPER(c.relname) LIKE E'%\\_ID\\_%' -- for the sequence named table_name_ID_seq
THEN SUBSTRING(c.relname from 1 for char_length(c.relname) - 7)
WHEN UPPER(c.relname) LIKE E'%\\_KRKEY\\_%' -- for sequence named table_name_krkey_seq
THEN SUBSTRING(c.relname from 1 for char_length(c.relname) - 10)
ELSE SUBSTRING(c.relname from 1 for char_length(c.relname) - 4) -- all other sequences
END AS table_name,
c.relname
FROM pg_class c WHERE c.relkind = 'S'
) a
JOIN pg_class AS rl -- checks that such table exists
ON rl.relname = a.table_name
WHERE table_name=tableName
ORDER BY 1 LIMIT 1;
return res;
END;
$$
LANGUAGE 'plpgsql';

--Funzione che setta il valore al massimo il valore della sequenza
CREATE OR REPLACE function setSequence(schemaName varchar,tableNam varchar,fieldName varchar) RETURNS varchar AS $$
    DECLARE
	seq varchar;
	query text; 
	maxid integer;
    BEGIN
	select into seq getSequence(tableNam);
	query := 'select coalesce(max('||fieldName||'),0)+1 from '||schemaName||'.'||tableNam||';';
	EXECUTE query INTO maxid;
	query := 'ALTER SEQUENCE '||schemaName||'.'||seq||' restart with '||maxid;
	EXECUTE query;
	return maxid::varchar;
    END;
$$
LANGUAGE 'plpgsql';


/*CAMPI*/
--Aggiunto testo_tampa alla tabella pe.e_enti
DO $$ 
    BEGIN
		BEGIN
			ALTER TABLE pe.e_enti ADD COLUMN testo_stampa text;
			RAISE NOTICE 'Aggiunto campo testo_stampèa alla tabella pe.e_enti';
		EXCEPTION	
			WHEN duplicate_column THEN RAISE NOTICE 'column testo_stampa already exists in pe.e_enti.';
		END;
    END;
$$;
--Aggiunto campo pe alla tabella vincoli.tabella
DO $$ 
    BEGIN
		BEGIN
			ALTER TABLE vincoli.tavola ADD COLUMN pe integer default 1;
			RAISE NOTICE 'Aggiunto campo pe alla tabella vincoli.tavola';
		EXCEPTION	
			WHEN duplicate_column THEN RAISE NOTICE 'column pe already exists in vincoli.tavola';
		END;
    END;
$$;
--Aggiunto campo enabled alla tabella pe.e_tipopratica  
DO $$ 
    BEGIN
	BEGIN
		ALTER TABLE pe.e_tipopratica ADD COLUMN enabled integer DEFAULT 1;
		RAISE NOTICE 'Aggiunto campo enabled alla tabella pe.e_tipopratica';
	EXCEPTION	
		WHEN duplicate_column THEN RAISE NOTICE 'column enabled already exists in pe.e_tipopratica.';
	END;
    END;
$$;
-- Modificato il DEFAULT su data_creazione
ALTER TABLE admin.users ALTER COLUMN data_creazione SET DEFAULT CURRENT_DATE;
/*TABELLE*/

DO $$
	BEGIN
		BEGIN
			CREATE TABLE nct.sezioni
			(
			  id serial NOT NULL,
			  sezione character varying,
			  nome character varying,
			  CONSTRAINT sezioni_pkey PRIMARY KEY (id )
			)
			WITH (
			  OIDS=FALSE
			);
			ALTER TABLE nct.sezioni
			  OWNER TO postgres;
			GRANT ALL ON TABLE nct.sezioni TO postgres;
		EXCEPTION
			WHEN duplicate_table THEN RAISE NOTICE 'table nct.sezioni already exists in schema nct.';
		END;
    END;
$$;
/*VISTE*/

-- View: pe.elenco_tipopratica

-- DROP VIEW pe.elenco_tipopratica;

CREATE OR REPLACE VIEW pe.elenco_tipopratica AS 
         SELECT NULL::smallint AS id, 'Seleziona il tipo di pratica ----->'::character varying AS opzione, (-1) AS ordine
UNION 
         SELECT e_tipopratica.id, e_tipopratica.nome AS opzione, e_tipopratica.ordine
           FROM pe.e_tipopratica
          WHERE e_tipopratica.enabled = 1
  ORDER BY 3, 2;

ALTER TABLE pe.elenco_tipopratica
  OWNER TO postgres;


-- View: pe.elenco_tipopratica_all

-- DROP VIEW pe.elenco_tipopratica_all;

CREATE OR REPLACE VIEW pe.elenco_tipopratica_all AS 
         SELECT NULL::smallint AS id, 'Seleziona il tipo di pratica ----->'::character varying AS opzione, (-1) AS ordine
UNION 
         SELECT e_tipopratica.id, e_tipopratica.nome AS opzione, e_tipopratica.ordine
           FROM pe.e_tipopratica
  ORDER BY 3, 2;

ALTER TABLE pe.elenco_tipopratica_all
  OWNER TO postgres;

-- View: pe.elenco_tipopratica_amb

-- DROP VIEW pe.elenco_tipopratica_amb;

CREATE OR REPLACE VIEW pe.elenco_tipopratica_amb AS 
         SELECT NULL::smallint AS id, 'Seleziona il tipo di pratica ----->'::character varying AS opzione, 0 AS ordine
UNION 
         SELECT e_tipopratica.id, e_tipopratica.nome AS opzione, e_tipopratica.ordine
           FROM pe.e_tipopratica
          WHERE tipologia='ambientale' and enabled = 1
  ORDER BY 3;

ALTER TABLE pe.elenco_tipopratica_amb
  OWNER TO postgres;


-- View: pe.elenco_tipopratica_dia

-- DROP VIEW pe.elenco_tipopratica_dia;

CREATE OR REPLACE VIEW pe.elenco_tipopratica_dia AS 
         SELECT NULL::smallint AS id, 'Seleziona il tipo di pratica ----->'::character varying AS opzione, 0 AS ordine
UNION 
         SELECT e_tipopratica.id, e_tipopratica.nome AS opzione, e_tipopratica.ordine
           FROM pe.e_tipopratica
          WHERE tipologia = 'dia' and enabled = 1
  ORDER BY 3;

ALTER TABLE pe.elenco_tipopratica_dia
  OWNER TO postgres;

-- View: pe.elenco_tipopratica_permesso

-- DROP VIEW pe.elenco_tipopratica_permesso;

CREATE OR REPLACE VIEW pe.elenco_tipopratica_permesso AS 
         SELECT NULL::smallint AS id, 'Seleziona il tipo di pratica ----->'::character varying AS opzione, 0 AS ordine
UNION 
         SELECT e_tipopratica.id, e_tipopratica.nome AS opzione, e_tipopratica.ordine
           FROM pe.e_tipopratica
          WHERE tipologia = 'pratica' and enabled = 1
  ORDER BY 3;

ALTER TABLE pe.elenco_tipopratica_permesso
  OWNER TO postgres;

-- View: pe.elenco_tipopratica_scia

-- DROP VIEW pe.elenco_tipopratica_scia;

CREATE OR REPLACE VIEW pe.elenco_tipopratica_scia AS 
         SELECT NULL::smallint AS id, 'Seleziona il tipo di pratica ----->'::character varying AS opzione, 0 AS ordine
UNION 
         SELECT e_tipopratica.id, e_tipopratica.nome AS opzione, e_tipopratica.ordine
           FROM pe.e_tipopratica
          WHERE tipologia = 'scia' and enabled = 1
  ORDER BY 3;

ALTER TABLE pe.elenco_tipopratica_scia
  OWNER TO postgres;

--Viste per normalizzare l'autosuggest dei civici  
create or replace view civici.pe_vie as select codice as id,toponomast as nome from civici.stradario;
create or replace view civici.pe_civici as select gid,codice as strada,replace(civico||colore,'N','') as label from civici.civici where data_end='0';  