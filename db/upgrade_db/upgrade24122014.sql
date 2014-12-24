ALTER TABLE pe.file_allegati ADD COLUMN data_prot_allegato date;
ALTER TABLE pe.file_allegati ADD COLUMN prot_allegato character varying;
ALTER TABLE pe.file_allegati ADD COLUMN stato_allegato character varying;

DROP VIEW pe.doc_dettaglio;

CREATE OR REPLACE VIEW pe.doc_dettaglio AS 
 SELECT b.id, c.id AS idfile, b.pratica, a.nome AS documento, a.descrizione, c.prot_allegato AS protocollo, c.data_prot_allegato AS data_protocollo, c.stato_allegato, c.note, NULL::character varying AS file_allegato, c.nome_file, c.chk, c.tmsins, c.uidins, c.uidupd, c.tmsupd
   FROM pe.e_documenti a
   JOIN pe.allegati b ON a.id = b.documento
   LEFT JOIN pe.file_allegati c ON b.id = c.allegato
  ORDER BY c.id;

ALTER TABLE pe.doc_dettaglio
  OWNER TO postgres;
GRANT ALL ON TABLE pe.doc_dettaglio TO postgres;
GRANT SELECT ON TABLE pe.doc_dettaglio TO mapserver;