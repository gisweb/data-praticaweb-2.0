--ALTER TABLE pe.soggetti ADD COLUMN codfisd varchar;
UPDATE pe.e_tipopratica SET menu_default=replace(menu_default,',75,',',');
UPDATE pe.menu SET menu_list=replace(menu_list,',75,',',');
UPDATE pe.e_parametri SET default_ins=0;
INSERT INTO pe.e_parametri( id, nome,  ordine, codice, default_ins ) VALUES (100, 'Sup. agibile di nuova costruzione (mq)',  1, 'supagibnew', 1);
INSERT INTO pe.e_parametri( id, nome,  ordine, codice, default_ins ) VALUES (101, 'Sup. agibile di ampliamento (mq)',  2, 'supagibamp', 1);
INSERT INTO pe.e_parametri( id, nome,  ordine, codice, default_ins ) VALUES (102, 'Sup. agibile di ristrutturazione (mq)',  3, 'supagibrist', 1);
INSERT INTO pe.e_parametri( id, nome,  ordine, codice, default_ins ) VALUES (103, 'Sup. sedime (mq)',  4, 'supsedime', 1);

-- View: pe.vista_iter

-- DROP VIEW pe.vista_iter;

CREATE OR REPLACE VIEW pe.vista_iter AS 
        (        (        (        (        (        (        (         SELECT aa.id, aa.pratica, aa.row_form, aa.row_class, aa.data, aa.testo, aa.chk, aa.nome
                                                                   FROM ( SELECT a.id, a.pratica, 'pe.scadenze' AS row_form, 'scadenze' AS row_class, a.scadenza AS data, aaa.nome, COALESCE(b.nome, ''::character varying)::text || COALESCE(' - '::text || a.note, ''::text) AS testo, a.chk
                                                                           FROM pe.scadenze a
                                                                      LEFT JOIN admin.users aaa ON aaa.userid = COALESCE(a.uidins, a.uidupd)
                                                                 JOIN pe.e_scadenze b USING (codice)) aa
                                                        UNION ALL 
                                                                 SELECT a.id, a.pratica, 'stp.documenti'::text AS row_form, ''::text AS row_class, a.data_creazione_doc AS data, 
                                                                        CASE
                                                                            WHEN a.file_doc::text ~~* '________-%'::text THEN (((('<span class="stampe" data-url="'::text || a.file_doc::text) || '">'::text) || substr(a.file_doc::text, 10, length(a.file_doc::text))) || '-'::text) || '</span>'::text
                                                                            ELSE ((('<span class="stampe" data-url="'::text || a.file_doc::text) || '">'::text) || a.file_doc::text) || '-'::text
                                                                        END AS testo, a.chk, btrim(aaa.nome::text) AS nome
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
                                 SELECT a.id, a.pratica, 'pe.sospensioni'::text AS row_form, 'integrazioni'::text AS row_class, a.data_integrazione AS data, (('Pratica integrata - (Sospensione del '::text || a.data_richiesta::character varying::text) || ').'::text) || COALESCE(' Note:'::text || a.note_integrazione, ''::text) AS testo, a.chk, aaa.nome
                                   FROM pe.sospensioni a
                              JOIN pe.e_tiposospensione b ON b.id = a.tipo
                         LEFT JOIN admin.users aaa ON aaa.userid = COALESCE(a.uidins, a.uidupd)
                        WHERE NOT a.data_integrazione IS NULL)
                UNION ALL 
                         SELECT pareri.id, pareri.pratica, 'pe.pareri'::text AS row_form, 'pareri-rilasciato'::text AS row_class, pareri.data_ril AS data, ('Rilasciato parere a '::text || e_enti.nome::text) || COALESCE(' con esito: '::text || e_pareri.nome::text, ''::text) AS testo, pareri.chk, ''::character varying AS nome
                           FROM pe.pareri
                      JOIN pe.e_enti ON pareri.ente = e_enti.id
                 JOIN pe.e_pareri ON pareri.parere = e_pareri.id
                WHERE COALESCE(pareri.data_ril::character varying, ''::character varying)::text <> ''::text)
        UNION ALL 
                 SELECT lavori.id, lavori.pratica, 'pe.lavori'::text AS row_form, 'note'::text AS row_class, lavori.fl AS data, 'Fine lavori'::text AS testo, lavori.chk, aaa.nome
                   FROM pe.lavori
              LEFT JOIN admin.users aaa ON aaa.userid = COALESCE(lavori.uidins, lavori.uidupd)
             WHERE lavori.fl IS NOT NULL)
/*UNION ALL 
         SELECT avvioproc.id, avvioproc.pratica, 'pe.avvioproc'::text AS row_form, 'note'::text AS row_class, avvioproc.data_resp_it AS data, COALESCE('Assegnazione istruttore tecnico: '::text || users.nome::text, ''::text) AS testo, avvioproc.chk, aaa.nome
           FROM pe.avvioproc
      LEFT JOIN admin.users ON avvioproc.resp_it = users.userid
   LEFT JOIN admin.users aaa ON aaa.userid = COALESCE(avvioproc.uidins, avvioproc.uidupd)
  WHERE NOT (avvioproc.resp_it IS NULL AND avvioproc.data_resp_it IS NULL)*/
  ORDER BY 2, 5;

ALTER TABLE pe.vista_iter
  OWNER TO postgres;
GRANT ALL ON TABLE pe.vista_iter TO postgres;
GRANT SELECT ON TABLE pe.vista_iter TO mapserver;
