
	/*------------------------------------------------- CREAZIONE DELLE TABELLE MANCANTI -------------------------------------------------------*/
CREATE TABLE pe.e_esiti_verifiche
(
  id integer NOT NULL,
  nome character varying,
  enabled integer DEFAULT 1,
  ordine integer DEFAULT 0,
  CONSTRAINT e_esiti_verifiche_pkey PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE pe.e_esiti_verifiche
  OWNER TO postgres;

  
SET search_path = pe, pg_catalog;
INSERT INTO e_esiti_verifiche (id, nome, enabled, ordine) VALUES (0, 'in attesa', 1, 0);
INSERT INTO e_esiti_verifiche (id, nome, enabled, ordine) VALUES (1, 'positivo', 1, 1);
INSERT INTO e_esiti_verifiche (id, nome, enabled, ordine) VALUES (2, 'negativo', 1, 2);
SET search_path = public, pg_catalog;  
  
  CREATE TABLE pe.e_tipofidi
(
  id serial,
  nome character varying(150) NOT NULL,
  codice character varying,
  ordine smallint DEFAULT 0,
  descrizione text,
  enabled integer DEFAULT 1,
  CONSTRAINT e_tiposanzioni_pkey PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE pe.e_tipofidi
  OWNER TO postgres;

SET search_path = pe, pg_catalog;
INSERT INTO e_tipofidi (id, nome, codice, ordine, descrizione, enabled) VALUES (1, 'Rateizzazione Oneri', 'oneri', 1, '', 1);
INSERT INTO e_tipofidi (id, nome, codice, ordine, descrizione, enabled) VALUES (2, 'Realizzazione Opere di Urbanizzazione', 'urb', 2, '', 1);
INSERT INTO e_tipofidi (id, nome, codice, ordine, descrizione, enabled) VALUES (3, 'Adempimenti da Convenzioni', 'convenzione', 3, '', 1);
INSERT INTO e_tipofidi (id, nome, codice, ordine, descrizione, enabled) VALUES (4, 'Altro', 'altro', 100, '', 1);
SELECT pg_catalog.setval('e_tipofidi_id_seq', 4, true);
SET search_path = public, pg_catalog;

  
CREATE TABLE pe.e_tipodoc
(
  id serial,
  codice character varying NOT NULL,
  nome character varying,
  descrizione text,
  ordine integer DEFAULT 1,
  CONSTRAINT e_tipodoc_pkey PRIMARY KEY (id),
  CONSTRAINT e_tipodoc_codice_key UNIQUE (codice)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE pe.e_tipodoc
  OWNER TO postgres;  

SET search_path = pe, pg_catalog;
INSERT INTO e_tipodoc (id, codice, nome, descrizione, ordine) VALUES (1, 'modello', 'Documento da inviare', NULL, 1);
INSERT INTO e_tipodoc (id, codice, nome, descrizione, ordine) VALUES (3, 'chiusura', 'Allegato di chiusura pratica', NULL, 3);
INSERT INTO e_tipodoc (id, codice, nome, descrizione, ordine) VALUES (4, 'altro', 'Altro', NULL, 4);
INSERT INTO e_tipodoc (id, codice, nome, descrizione, ordine) VALUES (2, 'allegato', 'Allegati', NULL, 2);
SELECT pg_catalog.setval('e_tipodoc_id_seq', 4, true);
SET search_path = public, pg_catalog;  
  
CREATE TABLE pe.e_direzioni
(
  id integer NOT NULL,
  nome character varying,
  ordine integer DEFAULT 0,
  CONSTRAINT e_direzioni_pkey PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE pe.e_direzioni
  OWNER TO postgres;

SET search_path = pe, pg_catalog;
INSERT INTO e_direzioni (id, nome, ordine) VALUES (1, 'Uscita', 1);
INSERT INTO e_direzioni (id, nome, ordine) VALUES (0, 'Entrata', 2);
SET search_path = public, pg_catalog;

CREATE TABLE vincoli.file
(
  id serial NOT NULL,
  nome_vincolo character varying,
  nome_tavola character varying,
  nome_zona character varying,
  file character varying,
  titolo character varying,
  CONSTRAINT file_pkey PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE vincoli.file
  OWNER TO postgres;
GRANT ALL ON TABLE vincoli.file TO postgres;
GRANT SELECT ON TABLE vincoli.file TO mapserver;


 
	/*------------------------------------------------- AGGIUNTA DEI CAMPI MANCANTI -------------------------------------------------------------*/
ALTER TABLE pe.abitabi ADD COLUMN autocertificata integer;
ALTER TABLE pe.abitabi ADD COLUMN data_ritiro date;
ALTER TABLE pe.avvioproc ADD COLUMN categoria integer;
ALTER TABLE pe.avvioproc ADD COLUMN note_chiusura text;
ALTER TABLE pe.cterreni ADD COLUMN sez_censimento character varying;
ALTER TABLE pe.cterreni ADD COLUMN tmsupd integer;
ALTER TABLE pe.cterreni ADD COLUMN uidupd integer;
ALTER TABLE pe.curbano ADD COLUMN sez_censimento character varying;
ALTER TABLE pe.curbano ADD COLUMN tmsupd integer;
ALTER TABLE pe.curbano ADD COLUMN uidupd integer;
ALTER TABLE pe.e_intervento ADD COLUMN ordine integer;
ALTER TABLE pe.e_scadenze ADD COLUMN enabled integer;
ALTER TABLE pe.e_tipopratica ADD COLUMN label_tipologia character varying;
ALTER TABLE pe.e_tipopratica_scadenze ADD COLUMN campo character varying;
ALTER TABLE pe.e_verifiche ADD COLUMN sorteggio integer;
ALTER TABLE pe.indirizzi ADD COLUMN tmsupd integer;
ALTER TABLE pe.indirizzi ADD COLUMN uidupd integer;
ALTER TABLE pe.indirizzi ADD COLUMN via_old character varying;
ALTER TABLE pe.lavori ADD COLUMN titolo integer;
ALTER TABLE pe.sanzioni ADD COLUMN data_sanzione date;
ALTER TABLE pe.soggetti ADD COLUMN denunciante integer;
ALTER TABLE pe.soggetti ADD COLUMN resp_abuso integer;
ALTER TABLE vigi.sopralluoghi ADD COLUMN flag_vigilanza integer;
ALTER TABLE stp.stampe ADD COLUMN direzione integer;
ALTER TABLE stp.stampe ADD COLUMN prot_in character varying;
ALTER TABLE stp.stampe ADD COLUMN prot_out character varying;
ALTER TABLE stp.stampe ADD COLUMN tipo_documento character varying;
ALTER TABLE pe.verifiche ADD COLUMN data_sorteggio date;
ALTER TABLE pe.vincoli ADD COLUMN tmsupd integer;
ALTER TABLE pe.vincoli ADD COLUMN uidupd integer;
	
/*-------------------------------------------------- AGGIUNTA DELLE VISTE MANCANTI ---------------------------------------------------------------*/
CREATE OR REPLACE VIEW pe.zone_pratica AS 
 SELECT a.id, a.pratica, a.vincolo, a.zona, COALESCE(b.descrizione, a.zona) AS descrizione, b.sigla, a.tavola, COALESCE(c.descrizione, a.tavola) AS descrizione_tavola, d.file, d.titolo, a.chk
   FROM pe.vincoli a
   JOIN vincoli.zona b ON a.vincolo::text = b.nome_vincolo::text AND a.tavola::text = b.nome_tavola::text AND a.zona::text = b.nome_zona::text
   JOIN vincoli.tavola c ON c.nome_tavola::text = a.tavola::text AND c.nome_vincolo::text = a.vincolo::text
   LEFT JOIN vincoli.file d ON b.nome_vincolo::text = d.nome_vincolo::text AND b.nome_tavola::text = d.nome_tavola::text AND b.nome_zona::text = d.nome_zona::text
  ORDER BY c.ordine, b.ordine;

ALTER TABLE pe.zone_pratica
  OWNER TO postgres;
GRANT ALL ON TABLE pe.zone_pratica TO postgres;
GRANT SELECT ON TABLE pe.zone_pratica TO mapserver;
DROP VIEW IF EXISTS pe.elenco_soggetti CASCADE;
CREATE VIEW pe.elenco_soggetti AS SELECT DISTINCT soggetti.id, soggetti.pratica, soggetti.app, soggetti.cognome, soggetti.nome, soggetti.indirizzo, soggetti.comune, soggetti.prov, soggetti.cap, soggetti.telefono, soggetti.email, soggetti.comunato, soggetti.provnato, soggetti.datanato, soggetti.sesso, soggetti.codfis, soggetti.titolo, soggetti.ragsoc, soggetti.titolod, soggetti.sede, soggetti.comuned, soggetti.provd, soggetti.capd, soggetti.piva, soggetti.ccia, soggetti.cciaprov, soggetti.inail, soggetti.inailprov, soggetti.inps, soggetti.inpsprov, soggetti.cedile, soggetti.cedileprov, soggetti.albo, soggetti.albonumero, soggetti.alboprov, soggetti.voltura, soggetti.comunicazioni, soggetti.note, soggetti.proprietario, soggetti.richiedente, soggetti.concessionario, soggetti.progettista, soggetti.direttore, soggetti.esecutore, soggetti.sicurezza, soggetti.collaudatore, soggetti.resp_abuso, soggetti.chk, soggetti.uidins, soggetti.tmsins, soggetti.uidupd, soggetti.tmsupd, soggetti.idsogge, soggetti.geologo, soggetti.collaudatore_ca, soggetti.progettista_ca, soggetti.economia_diretta, ((((((COALESCE(soggetti.cognome, ''::character varying))::text || COALESCE((' '::text || (soggetti.nome)::text), ''::text)) || COALESCE((' '::text || (soggetti.titolo)::text), ''::text)) || COALESCE((' '::text || (soggetti.ragsoc)::text), ''::text)) || COALESCE((' '::text || (soggetti.indirizzo)::text), ''::text)) || COALESCE(((' ('::text || (soggetti.prov)::text) || ')'::text), ''::text)) AS soggetto FROM pe.soggetti;
DROP VIEW IF EXISTS pe.zone_pratica CASCADE;
CREATE VIEW pe.zone_pratica AS SELECT a.id, a.pratica, a.vincolo, a.zona, COALESCE(b.descrizione, a.zona) AS descrizione, b.sigla, a.tavola, COALESCE(c.descrizione, a.tavola) AS descrizione_tavola, d.file, d.titolo, a.chk FROM (((pe.vincoli a JOIN vincoli.zona b ON (((((a.vincolo)::text = (b.nome_vincolo)::text) AND ((a.tavola)::text = (b.nome_tavola)::text)) AND ((a.zona)::text = (b.nome_zona)::text)))) JOIN vincoli.tavola c ON ((((c.nome_tavola)::text = (a.tavola)::text) AND ((c.nome_vincolo)::text = (a.vincolo)::text)))) LEFT JOIN vincoli.file d ON (((((b.nome_vincolo)::text = (d.nome_vincolo)::text) AND ((b.nome_tavola)::text = (d.nome_tavola)::text)) AND ((b.nome_zona)::text = (d.nome_zona)::text)))) ORDER BY c.ordine, b.ordine;
DROP VIEW IF EXISTS stp.single_agibilita CASCADE;
CREATE VIEW stp.single_agibilita AS SELECT abitabi.pratica, abitabi.numero_rich AS agibilita_numero_richiesta_numero, abitabi.prot_rich AS agibilita_protocollo_richiesta, abitabi.data_rich AS agibilita_data_richiesta, abitabi.numero_doc AS agibilita_numero_autorizzazione, abitabi.prot_doc AS agibilita_data_protocollo, abitabi.data_ril AS agibilita_data_rilascio FROM pe.abitabi WHERE (abitabi.isagi = 1);
DROP VIEW IF EXISTS stp.single_lavori CASCADE;
CREATE VIEW stp.single_lavori AS SELECT lavori.pratica, lavori.scade_il AS scadenza_il, lavori.scade_fl AS scadenza_fl, lavori.il AS inizio_lavori, lavori.fl AS fine_lavori FROM pe.lavori;
DROP VIEW IF EXISTS stp.single_rate_oneri CASCADE;
CREATE VIEW stp.single_rate_oneri AS SELECT a.pratica, a.rata_1, a.data_scadenza_1, a.data_avviso_1, a.data_pagata_1, a.versato_1, a.quietanza_1, b.rata_2, b.data_scadenza_2, b.data_avviso_2, b.data_pagata_2, b.versato_2, b.quietanza_2, c.rata_3, c.data_scadenza_3, c.data_avviso_3, c.data_pagata_3, c.versato_3, c.quietanza_3, d.rata_4, d.data_scadenza_4, d.data_avviso_4, d.data_pagata_4, d.versato_4, d.quietanza_4, e.rata_unica, e.data_scadenza_unica, e.data_avviso_unica, e.data_pagata_unica, e.versato_unica, e.quietanza_unica FROM (((((SELECT rate.pratica, rate.totale AS rata_1, rate.data_scadenza AS data_scadenza_1, rate.data_avviso AS data_avviso_1, rate.data_pagata AS data_pagata_1, rate.versato AS versato_1, rate.quietanza AS quietanza_1 FROM oneri.rate WHERE (rate.rata = 1)) a LEFT JOIN (SELECT rate.pratica, rate.totale AS rata_2, rate.data_scadenza AS data_scadenza_2, rate.data_avviso AS data_avviso_2, rate.data_pagata AS data_pagata_2, rate.versato AS versato_2, rate.quietanza AS quietanza_2 FROM oneri.rate WHERE (rate.rata = 2)) b USING (pratica)) LEFT JOIN (SELECT rate.pratica, rate.totale AS rata_3, rate.data_scadenza AS data_scadenza_3, rate.data_avviso AS data_avviso_3, rate.data_pagata AS data_pagata_3, rate.versato AS versato_3, rate.quietanza AS quietanza_3 FROM oneri.rate WHERE (rate.rata = 3)) c USING (pratica)) LEFT JOIN (SELECT rate.pratica, rate.totale AS rata_4, rate.data_scadenza AS data_scadenza_4, rate.data_avviso AS data_avviso_4, rate.data_pagata AS data_pagata_4, rate.versato AS versato_4, rate.quietanza AS quietanza_4 FROM oneri.rate WHERE (rate.rata = 4)) d USING (pratica)) LEFT JOIN (SELECT rate.pratica, rate.totale AS rata_unica, rate.data_scadenza AS data_scadenza_unica, rate.data_avviso AS data_avviso_unica, rate.data_pagata AS data_pagata_unica, rate.versato AS versato_unica, rate.quietanza AS quietanza_unica FROM oneri.rate WHERE (rate.rata = 5)) e USING (pratica));
DROP VIEW IF EXISTS pe.elenco_tipo_verifiche CASCADE;
CREATE VIEW pe.elenco_tipo_verifiche AS SELECT e_verifiche.id, e_verifiche.nome AS opzione FROM pe.e_verifiche WHERE (e_verifiche.enabled = 1) ORDER BY e_verifiche.ordine, e_verifiche.nome;
DROP VIEW IF EXISTS pe.elenco_esiti_verifiche CASCADE;
CREATE VIEW pe.elenco_esiti_verifiche AS SELECT e_esiti_verifiche.id, e_esiti_verifiche.nome AS opzione FROM pe.e_esiti_verifiche WHERE (e_esiti_verifiche.enabled = 1) ORDER BY e_esiti_verifiche.ordine, e_esiti_verifiche.nome;
DROP VIEW IF EXISTS pe.elenco_tipofidi CASCADE;
CREATE VIEW pe.elenco_tipofidi AS SELECT NULL::integer AS id, 'Seleziona =======>'::character varying AS opzione, NULL::character varying AS codice, (-1) AS ordine UNION ALL SELECT e_tipofidi.id, e_tipofidi.nome AS opzione, e_tipofidi.codice, e_tipofidi.ordine FROM pe.e_tipofidi ORDER BY 4, 2;
DROP VIEW IF EXISTS stp.single_fidi_oneri CASCADE;
CREATE VIEW stp.single_fidi_oneri AS SELECT fidi.pratica, fidi.numero AS numero_fido, fidi.importo AS importo_fido, fidi.data AS data_fido, fidi.istituto AS istituto_fido, fidi.note AS note_fido, fidi.intestatario AS intestatario_fido, fidi.indirizzo_ist AS indirizzo_istituto_fido, fidi.citta_ist AS citta_istituto_fido, fidi.cap_ist AS cap_istituto_fido, fidi.indirizzo_int AS indirizzo_intestatario_fido, fidi.citta_int AS citta_intestatario_fido, fidi.cap_int AS cap_intestatario_fido FROM oneri.fidi WHERE (fidi.id = (SELECT e_tipofidi.id FROM pe.e_tipofidi WHERE ((e_tipofidi.codice)::text = 'oneri'::text)));
DROP VIEW IF EXISTS stp.single_titolo CASCADE;
CREATE VIEW stp.single_titolo AS SELECT titolo.pratica, titolo.titolo AS numero_titolo, titolo.protocollo AS protocollo_titolo, titolo.data_rilascio AS data_rilascio_titolo, titolo.data_ritiro AS data_ritiro_titolo, titolo.data_notifica AS data_notifica_titolo, titolo.intervento AS intervento_titolo, titolo.note AS note_titolo FROM pe.titolo;
DROP VIEW IF EXISTS pe.ct_info CASCADE;
CREATE VIEW pe.ct_info AS SELECT cterreni.pratica, cterreni.id, CASE WHEN (cterreni.sezione IS NULL) THEN (((cterreni.mappale)::text || '@'::text) || (cterreni.foglio)::text) ELSE (((((cterreni.mappale)::text || '@'::text) || (cterreni.foglio)::text) || '@'::text) || (cterreni.sezione)::text) END AS mapkey, cterreni.sezione, cterreni.foglio, cterreni.mappale, cterreni.sub, cterreni.note, cterreni.sez_censimento, cterreni.chk FROM pe.cterreni;
DROP VIEW IF EXISTS stp.single_abitabilita CASCADE;
CREATE VIEW stp.single_abitabilita AS SELECT abitabi.pratica, abitabi.numero_rich AS abitabilita_numero_richiesta_numero, abitabi.prot_rich AS abitabilita_protocollo_richiesta, abitabi.data_rich AS abitabilita_data_richiesta, abitabi.numero_doc AS abitabilita_numero_autorizzazione, abitabi.prot_doc AS abitabilita_data_protocollo, abitabi.data_ril AS abitabilita_data_rilascio FROM pe.abitabi WHERE (abitabi.isabi = 1);
DROP VIEW IF EXISTS stp.single_rate_in_scadenza CASCADE;
CREATE VIEW stp.single_rate_in_scadenza AS SELECT rate.pratica, rate.rata AS numero_rata_in_scadenza, rate.totale AS importo_rata_in_scadenza, rate.data_scadenza AS data_rata_in_scadenza FROM (oneri.rate JOIN (SELECT min(rate.rata) AS rata, rate.pratica FROM oneri.rate WHERE (((NOT (rate.data_scadenza IS NULL)) AND (('now'::text)::date <= rate.data_scadenza)) AND (rate.data_pagata IS NULL)) GROUP BY rate.pratica) b USING (pratica, rata));
DROP VIEW IF EXISTS pe.avvio_procedimento_indirizzi CASCADE;
CREATE VIEW pe.avvio_procedimento_indirizzi AS SELECT a.id, a.pratica, a.numero, a.tipo, a.categoria, a.intervento, a.data_presentazione, a.protocollo, a.data_prot, a.protocollo_int, a.data_prot_int, a.resp_proc, a.data_resp, a.com_resp, a.data_com_resp, a.oggetto, a.note, a.rif_pratica, a.riferimento, a.chk, a.uidins, a.tmsins, a.uidupd, a.tmsupd, a.prog, a.anno, a.rif_aut_amb, a.aut_amb, a.riferimento_to, a.resp_it, a.data_resp_it, a.resp_ia, a.data_resp_ia, a.diritti_segreteria, a.riduzione_diritti, a.pagamento_diritti, a.data_chiusura, a.cartella, b.via, b.civico, b.interno FROM (pe.avvioproc a LEFT JOIN pe.indirizzi b USING (pratica)) LIMIT 0;
DROP VIEW IF EXISTS stp.single_pratica CASCADE;
CREATE VIEW stp.single_pratica AS SELECT a.pratica, a.numero, b.nome AS tipo_pratica, c.descrizione AS intervento, a.anno, a.data_presentazione, a.protocollo, a.data_prot AS data_protocollo, a.protocollo_int, a.data_prot_int, d.nome AS responsabile_procedimento, a.data_resp AS data_responsabile, a.com_resp AS protocollo_com_rdp, a.data_com_resp AS data_comunicazione_responsabile, e.nome AS istruttore_tecnico, a.data_resp_it AS data_responsabile_it, f.nome AS istruttore_amministrativo, a.data_resp_ia AS data_responsabile_ia, a.rif_aut_amb AS numero_autorizzazione_amb, a.oggetto, a.note, a.rif_pratica AS numero_pratica_precedente, a.diritti_segreteria, a.riduzione_diritti, a.pagamento_diritti FROM (((((pe.avvioproc a LEFT JOIN pe.e_tipopratica b ON ((a.tipo = b.id))) LEFT JOIN pe.e_intervento c ON ((a.intervento = c.id))) LEFT JOIN admin.users d ON ((a.resp_proc = d.userid))) LEFT JOIN admin.users e ON ((a.resp_it = e.userid))) LEFT JOIN admin.users f ON ((a.resp_ia = f.userid)));
DROP VIEW IF EXISTS pe.elenco_tipo_documenti CASCADE;
CREATE VIEW pe.elenco_tipo_documenti AS SELECT DISTINCT e_tipodoc.codice AS id, e_tipodoc.nome AS opzione, e_tipodoc.ordine FROM pe.e_tipodoc ORDER BY e_tipodoc.ordine, e_tipodoc.nome;
DROP VIEW IF EXISTS pe.elenco_categorie CASCADE;
CREATE VIEW pe.elenco_categorie AS SELECT NULL::integer AS id, 'Seleziona ======>'::character varying AS opzione, ''::character varying AS tipo, (-1) AS ordine UNION ALL SELECT e_categoriapratica.id, COALESCE(e_categoriapratica.nome, (e_categoriapratica.descrizione)::character varying) AS opzione, e_categoriapratica.tipo, e_categoriapratica.ordine FROM pe.e_categoriapratica WHERE (e_categoriapratica.enabled = 1) ORDER BY 4, 2;
DROP VIEW IF EXISTS pe.elenco_direzioni_documenti CASCADE;
CREATE VIEW pe.elenco_direzioni_documenti AS SELECT DISTINCT e_direzioni.id, e_direzioni.nome AS opzione, e_direzioni.ordine FROM pe.e_direzioni ORDER BY e_direzioni.ordine, e_direzioni.nome;
	


CREATE OR REPLACE VIEW pe.vista_iter AS 
        (        (        (        (         SELECT aa.id, aa.pratica, aa.row_form, aa.row_class, aa.data, aa.testo, aa.chk, aa.nome
                                           FROM ( SELECT a.id, a.pratica, 'pe.scadenze' AS row_form, 'scadenze' AS row_class, a.scadenza AS data, aaa.nome, COALESCE(b.nome, ''::character varying)::text || COALESCE(' - '::text || a.note, ''::text) AS testo, a.chk
                                                   FROM pe.scadenze a
                                              LEFT JOIN admin.users aaa ON aaa.userid = COALESCE(a.uidins, a.uidupd)
                                         JOIN pe.e_scadenze b USING (codice)) aa
                                UNION ALL 
                                         SELECT a.id, a.pratica, 'stp.documenti'::text AS row_form, ''::text AS row_class, a.data_creazione_doc AS data, ((('<span class="stampe" data-url="'::text || a.file_doc::text) || '">'::text) || replace(a.file_doc::text, split_part(a.file_doc::text, '-'::text, 1) || '-'::text, ''::text)) || '</span>'::text AS testo, a.chk, aaa.nome
                                           FROM stp.stampe a
                                      LEFT JOIN admin.users aaa ON aaa.username::text = a.utente_doc::text
                                     WHERE COALESCE(a.form, ''::character varying)::text <> 'cdu.vincoli'::text)
                        UNION ALL 
                                 SELECT annotazioni.id, annotazioni.pratica, 'pe.annotazioni'::text AS row_form, 'note'::text AS row_class, annotazioni.data_annotazione AS data, annotazioni.note AS testo, annotazioni.chk, aaa.nome
                                   FROM pe.annotazioni
                              LEFT JOIN admin.users aaa ON aaa.userid = COALESCE(annotazioni.utente, COALESCE(annotazioni.uidins, annotazioni.uidupd)))
                UNION ALL 
                         SELECT pareri.id, pareri.pratica, 'pe.pareri'::text AS row_form, 'pareri'::text AS row_class, pareri.data_rich AS data, 'Richiesto parere a '::text || e_enti.nome::text AS testo, pareri.chk, aaa.nome
                           FROM pe.pareri
                      JOIN pe.e_enti ON pareri.ente = e_enti.id
                 LEFT JOIN admin.users aaa ON aaa.userid = COALESCE(pareri.uidins, pareri.uidupd)
                WHERE COALESCE(pareri.data_rich::character varying, ''::character varying)::text <> ''::text)
        UNION ALL 
                 SELECT a.id, a.pratica, 'pe.sospensioni'::text AS row_form, 'sospensioni'::text AS row_class, a.data_richiesta AS data, (('Pratica sospesa per '::text || COALESCE(b.nome::text || COALESCE((': ('::text || a.motivo_richiesta) || ')'::text, ''::text), ''::character varying::text)) || ' da '::text) || COALESCE(aaa.nome, ''::character varying)::text AS testo, a.chk, aaa.nome
                   FROM pe.sospensioni a
              JOIN pe.e_tiposospensione b ON b.id = a.tipo
         LEFT JOIN admin.users aaa ON aaa.userid = COALESCE(a.uidins, a.uidupd))
UNION ALL 
         SELECT pareri.id, pareri.pratica, 'pe.pareri'::text AS row_form, 'pareri-rilasciato'::text AS row_class, pareri.data_ril AS data, 'Rilasciato parere a '::text || e_enti.nome::text AS testo, pareri.chk, ''::character varying AS nome
           FROM pe.pareri
      JOIN pe.e_enti ON pareri.ente = e_enti.id
     WHERE COALESCE(pareri.data_ril::character varying, ''::character varying)::text <> ''::text
  ORDER BY 2, 5;

ALTER TABLE pe.vista_iter
  OWNER TO postgres;
GRANT ALL ON TABLE pe.vista_iter TO postgres;
GRANT SELECT ON TABLE pe.vista_iter TO mapserver;
	
/*------------------------------------------  AGGIUNTA DEI TRIGGER -------------------------------------------------------------*/	


CREATE OR REPLACE FUNCTION pe.aggiorna_vincoli()
  RETURNS trigger AS
$BODY$
DECLARE
	d date;
BEGIN
	begin
		if (TG_OP='INSERT') then
			SELECT into d coalesce(data_prot,data_presentazione) FROM pe.avvioproc WHERE pratica=NEW.pratica;
			INSERT INTO pe.vincoli (pratica,vincolo,tavola,zona)
				SELECT DISTINCT 
					NEW.pratica,nome_vincolo,nome_tavola,nome_zona  
				FROM 
					nct.particelle,(SELECT A.* FROM vincoli.zona_plg A inner join vincoli.zona B using(nome_vincolo,nome_tavola,nome_zona) WHERE d BETWEEN coalesce(data_da,'01/01/1970'::date) AND coalesce(data_a,CURRENT_DATE)) zona_plg 
				WHERE
					particelle.bordo_gb && zona_plg.the_geom AND 
					(area(intersection (particelle.bordo_gb,zona_plg.the_geom))>10 OR (area(intersection(particelle.bordo_gb,zona_plg.the_geom))/area (particelle.bordo_gb)*100)>=0.02) AND 
					(coalesce(sezione,'')=coalesce(new.sezione,'') and coalesce(foglio,'')=coalesce(new.foglio,'') and coalesce(mappale,'')=coalesce(new.mappale,'')) AND
					nome_vincolo||nome_tavola||nome_zona not in (select vincolo||tavola||zona from pe.vincoli where pratica=NEW.pratica) 
				GROUP BY nome_vincolo,nome_tavola,nome_zona;
				
		elsif(TG_OP='DELETE') then
			delete from pe.vincoli where pratica=old.pratica and (vincolo,tavola,zona) in (
				(select nome_vincolo,nome_tavola,nome_zona  from nct.particelle,vincoli.zona_plg 
				where 	(particelle.bordo_gb && zona_plg.the_geom )
					and (area(intersection (particelle.bordo_gb,zona_plg.the_geom))>10 or (area(intersection(particelle.bordo_gb,zona_plg.the_geom))/area (particelle.bordo_gb)*100)>=0.02)
					and coalesce(sezione,'')=coalesce(old.sezione,'') and foglio=old.foglio and mappale=old.mappale
				)
				except
				(
				select nome_vincolo,nome_tavola,nome_zona  from nct.particelle,vincoli.zona_plg 
				where 	(particelle.bordo_gb && zona_plg.the_geom )
					and (area(intersection (particelle.bordo_gb,zona_plg.the_geom))>10 or (area(intersection(particelle.bordo_gb,zona_plg.the_geom))/area (particelle.bordo_gb)*100)>=0.02)
					and (coalesce(sezione,''),foglio,mappale) in (select coalesce(sezione,'') as sezione,foglio,mappale from pe.cterreni where pratica=old.pratica)
				)
			);
			return OLD;
		end if;
	exception when others then
		raise notice 'Errore nella Query';
		return new;
	end;
	return new;
END
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION pe.aggiorna_vincoli()
  OWNER TO postgres;
  
CREATE TRIGGER elimina_vincoli
  AFTER DELETE
  ON pe.cterreni
  FOR EACH ROW
  EXECUTE PROCEDURE pe.aggiorna_vincoli();  
  
CREATE TRIGGER inserisci_vincoli
  AFTER INSERT
  ON pe.cterreni
  FOR EACH ROW
  EXECUTE PROCEDURE pe.aggiorna_vincoli();  

  
UPDATE pe.e_tipopratica SET menu_default=replace(menu_default,'295','285,295') WHERE enabled=1;
UPDATE pe.menu SET menu_list=replace(menu_list,'295','285,295')  