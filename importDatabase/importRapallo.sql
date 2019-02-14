-- View: pe.ct_info

DROP VIEW pe.ct_info;
DROP VIEW pe.grp_particelle_ct;
DROP VIEW stp.multiple_particelle_ct;
DROP VIEW stp.single_elenco_ct;

ALTER TABLE pe.cterreni DROP COLUMN foglio;
ALTER TABLE pe.cterreni DROP COLUMN mappale;
ALTER TABLE pe.cterreni DROP COLUMN sub;

ALTER TABLE pe.cterreni ADD COLUMN foglio varchar;
ALTER TABLE pe.cterreni ADD COLUMN mappale varchar;
ALTER TABLE pe.cterreni ADD COLUMN sub varchar;

CREATE OR REPLACE VIEW pe.ct_info AS 

 SELECT cterreni.pratica, cterreni.id, 
        CASE
            WHEN cterreni.sezione IS NULL THEN (cterreni.mappale::text || '@'::text) || cterreni.foglio::text
            ELSE (((cterreni.mappale::text || '@'::text) || cterreni.foglio::text) || '@'::text) || cterreni.sezione::text
        END AS mapkey, cterreni.sezione, cterreni.foglio, cterreni.mappale, cterreni.sub, cterreni.note, cterreni.sez_censimento, cterreni.chk
   FROM pe.cterreni;

ALTER TABLE pe.ct_info
  OWNER TO postgres;
GRANT ALL ON TABLE pe.ct_info TO postgres;
GRANT SELECT ON TABLE pe.ct_info TO mapserver;


CREATE OR REPLACE VIEW pe.grp_particelle_ct AS 
 WITH grp_mappali_ct AS (
         SELECT a.pratica, b.nome AS sezione, COALESCE(a.foglio, ''::character varying) AS foglio, array_to_string(array_agg(COALESCE(a.mappale, ''::character varying)), ','::text) AS mappali
           FROM pe.cterreni a
      LEFT JOIN "nct_BOH".sezioni b USING (sezione)
     GROUP BY a.pratica, b.nome, COALESCE(a.foglio, ''::character varying)
        ), grp_fogli_ct AS (
         SELECT foo.pratica, foo.sezione, foo.foglio, btrim(COALESCE(' Foglio: '::text || foo.foglio::text, ''::text) || COALESCE(' Mappali: '::text || foo.mappali, ''::text)) AS elenco_fogli
           FROM grp_mappali_ct foo
        ), grp_sezioni_ct AS (
         SELECT grp_fogli_ct.pratica, grp_fogli_ct.sezione, array_to_string(array_agg(grp_fogli_ct.elenco_fogli), ' - '::text) AS elenco_fogli
           FROM grp_fogli_ct
          GROUP BY grp_fogli_ct.pratica, grp_fogli_ct.sezione
        ), grp_particelle_ct AS (
         SELECT grp_sezioni_ct.pratica, array_to_string(array_agg(grp_sezioni_ct.elenco_fogli), ' '::text) AS elenco_ct
           FROM grp_sezioni_ct
          GROUP BY grp_sezioni_ct.pratica
        )
 SELECT grp_particelle_ct.pratica, grp_particelle_ct.elenco_ct
   FROM grp_particelle_ct;

ALTER TABLE pe.grp_particelle_ct
  OWNER TO postgres;


CREATE OR REPLACE VIEW stp.multiple_particelle_ct AS 
 SELECT DISTINCT a.pratica, COALESCE(b.nome, ''::character varying) AS sezione, a.foglio, a.mappale
   FROM pe.cterreni a
   LEFT JOIN nct.sezioni b USING (sezione);

ALTER TABLE stp.multiple_particelle_ct
  OWNER TO postgres;

CREATE OR REPLACE VIEW stp.single_elenco_ct AS 
 SELECT foo.pratica, btrim((COALESCE('Sezione: '::text || foo.sezione::text, ''::text) || COALESCE(' Foglio: '::text || foo.foglio::text, ''::text)) || COALESCE(' Mappali: '::text || foo.mappali, ''::text)) AS elenco_ct
   FROM ( SELECT a.pratica, b.nome AS sezione, COALESCE(a.foglio, ''::character varying) AS foglio, array_to_string(array_agg(COALESCE(a.mappale, ''::character varying)), ','::text) AS mappali
           FROM pe.cterreni a
      LEFT JOIN "nct_BOH".sezioni b USING (sezione)
     GROUP BY a.pratica, b.nome, COALESCE(a.foglio, ''::character varying)) foo;

ALTER TABLE stp.single_elenco_ct
  OWNER TO postgres;  



/*--------------------------------------------------------------------------------------------------*/  
DROP VIEW stp.single_elenco_cu;
DROP VIEW stp.multiple_particelle_cu;
DROP VIEW pe.grp_particelle_cu;


ALTER TABLE pe.curbano DROP COLUMN foglio;
ALTER TABLE pe.curbano DROP COLUMN mappale;
ALTER TABLE pe.curbano DROP COLUMN sub;

ALTER TABLE pe.curbano ADD COLUMN foglio varchar;
ALTER TABLE pe.curbano ADD COLUMN mappale varchar;
ALTER TABLE pe.curbano ADD COLUMN sub varchar;



CREATE OR REPLACE VIEW pe.grp_particelle_cu AS 
 WITH grp_mappali_cu AS (
         SELECT a.pratica, b.nome AS sezione, COALESCE(a.foglio, ''::character varying) AS foglio, array_to_string(array_agg(COALESCE(a.mappale, ''::character varying)), ','::text) AS mappali
           FROM pe.curbano a
      LEFT JOIN "nct_BOH".sezioni b USING (sezione)
     GROUP BY a.pratica, b.nome, COALESCE(a.foglio, ''::character varying)
        ), grp_fogli_cu AS (
         SELECT foo.pratica, foo.sezione, foo.foglio, btrim(COALESCE(' Foglio: '::text || foo.foglio::text, ''::text) || COALESCE(' Mappali: '::text || foo.mappali, ''::text)) AS elenco_fogli
           FROM grp_mappali_cu foo
        ), grp_sezioni_cu AS (
         SELECT grp_fogli_cu.pratica, grp_fogli_cu.sezione, array_to_string(array_agg(grp_fogli_cu.elenco_fogli), ' - '::text) AS elenco_fogli
           FROM grp_fogli_cu
          GROUP BY grp_fogli_cu.pratica, grp_fogli_cu.sezione
        ), grp_particelle_cu AS (
         SELECT grp_sezioni_cu.pratica, array_to_string(array_agg(grp_sezioni_cu.elenco_fogli), ' '::text) AS elenco_cu
           FROM grp_sezioni_cu
          GROUP BY grp_sezioni_cu.pratica
        )
 SELECT grp_particelle_cu.pratica, grp_particelle_cu.elenco_cu
   FROM grp_particelle_cu;

ALTER TABLE pe.grp_particelle_cu
  OWNER TO postgres;





CREATE OR REPLACE VIEW stp.multiple_particelle_cu AS 
 SELECT DISTINCT a.pratica, COALESCE(b.nome, ''::character varying) AS sezione, a.foglio, a.mappale
   FROM pe.curbano a
   LEFT JOIN "nct_BOH".sezioni b USING (sezione);

ALTER TABLE stp.multiple_particelle_cu
  OWNER TO postgres;



CREATE OR REPLACE VIEW stp.single_elenco_cu AS 
 SELECT foo.pratica, btrim((COALESCE('Sezione: '::text || foo.sezione::text, ''::text) || COALESCE(' Foglio: '::text || foo.foglio::text, ''::text)) || COALESCE(' Mappali: '::text || foo.mappali, ''::text)) AS elenco_cu
   FROM ( SELECT a.pratica, b.nome AS sezione, COALESCE(a.foglio, ''::character varying) AS foglio, array_to_string(array_agg(COALESCE(a.mappale, ''::character varying)), ','::text) AS mappali
           FROM pe.curbano a
      LEFT JOIN "nct_BOH".sezioni b USING (sezione)
     GROUP BY a.pratica, b.nome, COALESCE(a.foglio, ''::character varying)) foo;

ALTER TABLE stp.single_elenco_cu
  OWNER TO postgres;



-- INSERIMENTO TIPI PRATICA VECCHI
INSERT INTO pe.e_tipopratica(id, nome,  menu_file, menu_default, enabled)
    SELECT id, nome, 'pratica', '10,20,50,40,70,75,80,92,110,100,90,91,101,102,170,210,250,260,270,160,285,295,300,293,305,135', 0 FROM import.elenco_tipipratica;


ALTER TABLE pe.avvioproc DISABLE TRIGGER ALL;
INSERT INTO pe.avvioproc(pratica, protocollo, numero, oggetto, data_presentazione, data_prot, 
        anno, tipo)

SELECT DISTINCT id, coalesce(protocollo,''), numero, oggetto,
       data_inizio as data_presentazione, data_inizio as data_prot, anno, tipo
  FROM import.procedimento order by id;
ALTER TABLE pe.avvioproc ENABLE TRIGGER ALL;


INSERT INTO pe.menu(pratica,menu_file,menu_list) SELECT pratica,menu_file,menu_default FROM pe.avvioproc A INNER JOIN pe.e_tipopratica B ON(A.tipo=B.id)

ALTER TABLE pe.indirizzi DISABLE TRIGGER ALL;
INSERT INTO pe.indirizzi(pratica,via) SELECT * FROM import.elenco_indirizzi;
ALTER TABLE pe.indirizzi ENABLE TRIGGER ALL;




ALTER TABLE pe.cterreni DISABLE TRIGGER ALL;
INSERT INTO pe.cterreni(pratica,foglio,mappale,sub) SELECT * FROM import.elenco_cterreni;
ALTER TABLE pe.cterreni ENABLE TRIGGER ALL;

ALTER TABLE pe.curbano DISABLE TRIGGER ALL;
INSERT INTO pe.curbano(pratica,foglio,mappale,sub) SELECT * FROM import.elenco_curbano;
ALTER TABLE pe.curbano ENABLE TRIGGER ALL;


CREATE VIEW import.elenco_soggetti AS 
WITH indirizzi_persone AS (
SELECT A.id,B.descr as comune,A.descrvia as indirizzo, trim(format('%s%s',trim(coalesce(A.numciv,'')),coalesce('/'||A.espciv,'')))as civico, A.numinteger as interno FROM import.indir A inner join import.comune B ON(A.idcomune=B.id)
)

select A.id,A.pratica,tipoper,A.tiporef,titolo as app,cognome,nome,sesso,codfiscp as codfis,import.inttodate(datanasc) as datanato,cittad as comunato,comune,indirizzo,civico,interno,partiva as piva,ragsoc,codfiscg as codfisd,numccia from import.elenco_soggetti_temp A left join indirizzi_persone B ON(A.idindir = B.id) order by pratica
--select * from import.elenco_soggetti_temp A left join indirizzi_persone B ON(A.idindir = B.id) order by pratica


insert into pe.soggetti(pratica, app, cognome, nome, sesso, codfis, 
       datanato, comunato, comune, indirizzo, civico,  piva, 
       ragsoc, codfisd, ccia) SELECT DISTINCT pratica, app, cognome, nome, sesso, codfis, 
       datanato, comunato, comune, indirizzo, civico,  piva, 
       ragsoc, codfisd, numccia
  FROM import.elenco_soggetti where pratica in (select distinct pratica from pe.avvioproc);

SELECT DISTINCT tiporef FROM import.elenco_soggetti order by 1;
UPDATE pe.soggetti SET richiedente=0,proprietario=0,progettista=0,direttore=0,esecutore=0;
  UPDATE pe.soggetti A SET richiedente = 1, proprietario = 1 FROM import.elenco_soggetti B where A.pratica=B.pratica AND tiporef IN ('0','-1') AND coalesce(A.codfis=B.codfis) AND tipoper='F';
  UPDATE pe.soggetti A SET richiedente = 1, proprietario = 1 FROM import.elenco_soggetti B where A.pratica=B.pratica AND tiporef IN ('0','-1') AND coalesce(A.codfisd=B.codfisd) AND tipoper='G';
   UPDATE pe.soggetti A SET progettista = 1 FROM import.elenco_soggetti B where A.pratica=B.pratica AND tiporef IN ('2') AND coalesce(A.codfis=B.codfis) AND tipoper='F';
   UPDATE pe.soggetti A SET progettista = 1 FROM import.elenco_soggetti B where A.pratica=B.pratica AND tiporef IN ('2') AND coalesce(A.codfisd=B.codfisd) AND tipoper='G';
   UPDATE pe.soggetti A SET direttore = 1 FROM import.elenco_soggetti B where A.pratica=B.pratica AND tiporef IN ('3') AND coalesce(A.codfis=B.codfis) AND tipoper='F';
   UPDATE pe.soggetti A SET direttore = 1 FROM import.elenco_soggetti B where A.pratica=B.pratica AND tiporef IN ('3') AND coalesce(A.codfisd=B.codfisd) AND tipoper='G';
   UPDATE pe.soggetti A SET esecutore = 1 FROM import.elenco_soggetti B where A.pratica=B.pratica AND tiporef IN ('4') AND coalesce(A.codfis=B.codfis) AND tipoper='F';
   UPDATE pe.soggetti A SET esecutore = 1 FROM import.elenco_soggetti B where A.pratica=B.pratica AND tiporef IN ('4') AND coalesce(A.codfisd=B.codfisd) AND tipoper='G';



CREATE OR REPLACE VIEW import.documenti AS
SELECT 
B.idprati as pratica, A.cod as documento,
A.descr as descrizione, B.tipodoc,B.appunti as note,
import.inttodate(datains) as data_inserimento,esito,anno, numprot as protocollo, import.inttodate(dataprot) as data_protocollo
FROM import.rd_cldoc A inner join import.rd_docpr B on(A.cod=B.codcldoc) where coalesce(datains,0)<>0 order by import.inttodate(datains)