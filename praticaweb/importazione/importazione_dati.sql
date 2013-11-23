/* IMPORTAZIONE PRATICHE (pe.avvioproc)*/
DELETE FROM pe.avvioproc;
select setSequence('pe','avvioproc','id');
DELETE FROM pe.scadenze;
select setSequence('pe','scadenze','id');
INSERT INTO pe.avvioproc(pratica, numero,data_presentazione,  protocollo, data_prot, tipo, oggetto,data_chiusura, note, anno)
(SELECT "ID","NUMERO", "DATA", "PROTOCOLLO", "DATAPROT", "TIPO", "OGGETTO", "DATACHIUSA",  coalesce("NOTE_SCADENZE"||E'\n','')|| coalesce("NOTE_PROGETTO"||E'\n','')|| coalesce("NOTE_SORVEGLIANZA"||E'\n',''),date_part('year',"DATA"::date)
  FROM import."PRATICHE"
);

INSERT INTO pe.menu(pratica,menu_file,menu_list) (SELECT pratica,'pratica',menu_default FROM pe.avvioproc inner join pe.e_tipopratica B on (tipo=B.id));

/* AGGIORNAMENTO DEI TIPI DI PRATICA DA CUSTOMIZZARE OGNI VOLTA*/
ALTER TABLE pe.e_tipopratica ADD COLUMN enabled integer DEFAULT 1;


/* INSERIMENTO DELLA DESTINAZIONE D'USO (pe.progetto)*/



/*INSERIMENTO DATI DEL TITOLO (pe.titolo)*/			
delete from pe.titolo;
select setSequence('pe','titolo','id');
INSERT INTO pe.titolo(pratica,titolo,protocollo,data_rilascio,data_ritiro,intervento) (SELECT "ID","TITOLO","PROT_TITOLO","DATARILASCIO","DATARITIRO","INTERVENTO" FROM  import."PRATICHE" WHERE NOT "TITOLO" IS NULL);


/* INSERIMENTO SCADENZE DEI LAVORI (pe.lavori)*/
delete from pe.lavori;
select setSequence('pe','lavori','id');
INSERT INTO pe.lavori(pratica,scade_il,scade_fl,il,fl) (SELECT "ID","SCADE_IL","SCADE_FL","IL","FL" FROM  import."PRATICHE" WHERE (NOT "SCADE_FL" IS NULL) or (NOT "SCADE_IL" IS NULL) or (NOT "IL" IS NULL) or (NOT "FL" IS NULL));


/* INSERIMENTO PARERI (pe.pareri)*/
delete from pe.pareri;
select setSequence('pe','pareri','id');
INSERT INTO pe.pareri(
            pratica, ente, prot_rich, data_rich, prot_soll, data_soll, 
            parere, numero_doc, prot_ril, data_ril,
            prescrizioni, testo)

(SELECT "PRATICA", "ENTE", "PROT_RICH", "DATA_RICH", "PROT_SOLL", "DATA_SOLL", 
       CASE 
WHEN ("PARERE" ILIKE '%non favorevole%' or "PARERE" ILIKE '%negativo%' or "PARERE" ILIKE '%contrario%' or "PARERE" ILIKE '%diniego%') THEN 2 
WHEN ("PARERE" ILIKE '%favorevole con%' or "PARERE" ILIKE '%favorevole a%' or "PARERE" ILIKE '%prescrizioni%') THEN 3 
WHEN ("PARERE" ILIKE '%favorevole con%' or "PARERE" ILIKE '%favorevole a%' or "PARERE" ILIKE '%prescrizioni%') THEN 7 
WHEN ("PARERE" ILIKE '%integrazion%' ) THEN 4 
WHEN ("PARERE" IS NULL) THEN 6 
WHEN ("PARERE" ILIKE '%favorevole%' or "PARERE" ILIKE '%positivo%' or "PARERE" ILIKE '%autorizzazione%') THEN 1 
ELSE NULL END as parere ,
 "NUMERO", "PROT_RIL", "DATA_RIL",  "PRESCRIZIONI","PARERE"
  FROM import."PARERI" WHERE "PRATICA" IN (SELECT DISTINCT pratica from pe.avvioproc));



 /* INSERIMENTO DEGLI INDIRIZZI DELLA PRATICA (pe.indirizzi) */
delete from pe.curbano; 
delete from pe.indirizzi;
delete from pe.cterreni;
select setSequence('pe','curbano','id');
select setSequence('pe','cterreni','id');
select setSequence('pe','indirizzi','id');

 INSERT INTO pe.indirizzi(pratica, via, civico, interno, id_via, id_civico)
(SELECT "PRATICA", "VIA", "CIVICO", "INTERNO", "ID_VIA", "ID_CIVICO" FROM import."PRATICHE_INDIRIZZI" WHERE "PRATICA" IN (SELECT DISTINCT pratica from pe.avvioproc)); 

/* INSERIMENTO DEI DATI CATASTALI (pe.cterreni pe.curbano) */

INSERT INTO pe.cterreni(pratica, sezione, foglio, mappale, sub)
(SELECT "PRATICA", "SEZIONE", "FOGLIO", "NUMERO", "SUBALTERNO" FROM import."PRATICHE_TERRENI"  WHERE "PRATICA" IN (SELECT DISTINCT pratica from pe.avvioproc));

           
INSERT INTO pe.curbano(pratica, sezione, foglio, mappale, sub)
(SELECT "PRATICA", "SEZIONE", "FOGLIO", "NUMERO", "SUBALTERNO" FROM import."PRATICHE_URBANO"  WHERE "PRATICA" IN (SELECT DISTINCT pratica from pe.avvioproc)); 

 /* INSERIMENTO DEGLI ASSERVIMENTI DELLA PRATICA (pe.asservimenti pe.asservimenti_map) */



DELETE FROM pe.asservimenti;
select setSequence('pe','asservimenti','id');
 INSERT INTO pe.asservimenti(
            pratica, tipo, notaio, repertorio, loc_reg, loc_tras, data_reg, 
            data_tras, reg_part, reg_ord,  note,  numero, sup_particelle, sup_asservita,loc)
(
SELECT B."PRATICA", "TIPO", "NOTAIO", "REPERTORIO", "LOC_REG", "LOC_TRAS", 
       "DATA_REG", "DATA_TRAS", "REG_PART", "REG_ORD", "NOTE", "NUMERO","TOTSUP", 
       "SUPASS", "LOC"
  FROM import."ASSERVIMENTI" A INNER JOIN import."ASSERVIMENTI_PRATICHE" B ON("ASSERVIMENTO"="NUMERO")   WHERE "PRATICA" IN (SELECT DISTINCT pratica from pe.avvioproc)
); 

/*INSERT INTO pe.asservimenti_map(pratica, asservimento, sezione, foglio, mappale, supass)
(SELECT B."PRATICA",A."ASSERVIMENTO", "SEZIONE", "FOGLIO", "NUMERO",  "SUPASS"
  FROM import."MAPPALI_ASSERVITI"A INNER JOIN import."ASSERVIMENTI_PRATICHE" B USING("ASSERVIMENTO")   WHERE "PRATICA" IN (SELECT DISTINCT pratica from pe.avvioproc)
) ;*/

/* INSERIMENTO DEI SOGGETTI *(pe.soggetti)*/

DELETE FROM pe.soggetti;
select setSequence('pe','soggetti','id');

INSERT INTO pe.soggetti(
            idsogge,pratica, app, cognome, nome,ragsoc, 
			indirizzo, comune, prov, cap, datanato, comunato,
			provnato, sesso, codfis,piva, telefono, email,
            titolo,albo, albonumero,  inail,ccia, comunicazioni, note,
            proprietario, richiedente, concessionario, progettista, direttore,sicurezza, esecutore, inps,cedile
            )
(
SELECT "ID", pratica, "APPELLATIVO", "COGNOME", "NOME", "RAGSOC",  
       "INDIRIZZO", "COMUNE", "PROV", "CAP", "DATANATO", "COMUNATO", 
       "PROVNATO", "SESSO", "CODFIS", "PIVA", "TELEFONO", "EMAIL", 
	   "TITOLO","ALBO", "NALBO", "INAIL", "CCIAA", 1,"NOTE", 
	   "P", "R", "C", "G", "D", "S", "E",   "INPS", "CASSAEDILE" 
  FROM import."ANAGRAFICA" WHERE "RAGSOC" IS NULL AND pratica IN (SELECT DISTINCT pratica from pe.avvioproc)
);
INSERT INTO pe.soggetti(
            idsogge,pratica, app, cognome, nome,ragsoc, 
			sede, comuned, provd, capd, datanato, comunato,
			provnato, sesso, codfis,piva, telefono, email,
            titolod,albo, albonumero,  inail,ccia, comunicazioni, note,
            proprietario, richiedente, concessionario, progettista, direttore,sicurezza, esecutore, inps,cedile)
(
SELECT "ID", pratica, "APPELLATIVO", "COGNOME", "NOME", "RAGSOC",  
       "INDIRIZZO", "COMUNE", "PROV", "CAP", "DATANATO", "COMUNATO", 
       "PROVNATO", "SESSO", "CODFIS", "PIVA", "TELEFONO", "EMAIL", 
	   "TITOLO","ALBO", "NALBO", "INAIL", "CCIAA", 1,"NOTE", 
	   "P", "R", "C", "G", "D", "S", "E",   "INPS", "CASSAEDILE" 
  FROM import."ANAGRAFICA" WHERE NOT "RAGSOC" IS NULL AND pratica IN (SELECT DISTINCT pratica from pe.avvioproc)
);

/* IMPORTAZIONE TARIFFE ONERI */
delete from oneri.e_tariffe;
INSERT INTO oneri.e_tariffe( tabella,anno, funzione, descrizione, tr, a, ie,k)
(select "TABELLA","ANNO"::smallint,"FUNZIONE","DESCRIZIONE","TR","A","IE",100 from import."ONERI_ANNO_TARIFFE");


/* IMPORTAZIONE DOCUMENTI ALLEGATI (pe.allegati) */

DELETE FROM pe.allegati;
select setSequence('pe','allegati','id');
INSERT INTO pe.allegati(
            pratica, documento, allegato, mancante,protocollo,data_protocollo )

(SELECT "PRATICA", "DOCUMENTO", CASE WHEN ("STATO"=0) THEN 0 ELSE 1 END,CASE WHEN ("STATO"=0) THEN 1 ELSE 0 END, "PROTOCOLLO", "DATAPROT"
  FROM import."ALLEGATI" WHERE "PRATICA" IN (select  distinct pratica from pe.avvioproc)
);

/* IMPORTAZIONE ONERI (oneri.totali) */
DELETE FROM oneri.totali;
SELECT setSequence('oneri','totali','id');
INSERT INTO oneri.totali(
            pratica, cc, b1, b2, scb1, scb2,  monet, quietanza, 
            data, oblazione, q_oblazione, data_oblazione, indennita, q_indennita, 
            data_indennita)

(SELECT "PRATICA", "CC", "B1", "B2", "SCB1", "SCB2", "MONET", "QUIETANZA", "DATA", "OBLAZIONE", "Q_OBLAZIONE", "DATA_OBLAZIONE", "INDENNITA", "Q_INDENNITA", "DATA_INDENNITA"
  FROM import."ONERI" WHERE "PRATICA" IN (select distinct pratica from pe.avvioproc));
  
  /* IMPORTAZIONE ONERI (oneri.calcolati) */
DELETE FROM oneri.calcolati;
SELECT setSequence('oneri','calcolati','id');

INSERT INTO oneri.calcolati(
            pratica, tabella,   sup, cc, b1, b2, e1, e2,
            c1, c2, c3, c4, d1, d2,  intervento)

(SELECT "PRATICA", "TABELLA", "SUP", "CC", "B1", "B2", "E1", "E2", "C1", 
       "C2", "C3", "C4", "D1", "D2",  "INTERVENTO"
  FROM import."ONERI_DETTAGLIO" WHERE "PRATICA" IN (select distinct pratica FROM pe.avvioproc));  