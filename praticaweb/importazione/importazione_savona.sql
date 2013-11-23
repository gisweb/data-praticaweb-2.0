﻿/************************************************************************************/
/*		IMPORTAZIONE DELLE PRATICHE EDILIZIE DI EDILIZIA PRIVATA IN PRATICAWEB 2.0	*/
/************************************************************************************/
/*************************************************************/
/*                IMPORTAZIONE DEGLI ELENCHI                 */
/*************************************************************/
/* IMPORTAZIONE TIPI PRATICA */
DROP TABLE IF EXISTS e_tipopratica;
CREATE TEMP TABLE e_tipopratica AS
SELECT "CODICE" as id, "NOME" as nome, "GIORNI" as gg
  FROM import."ELENCO_TIPOPRATICA" WHERE "CODICE" NOT IN (SELECT DISTINCT id from pe.e_tipopratica);

INSERT INTO pe.e_tipopratica(id, nome) (SELECT id,nome from e_tipopratica);

DELETE FROM pe.e_tipopratica WHERE id not in (SELECT DISTINCT "CODICE" FROM import."ELENCO_TIPOPRATICA");

/*IMPORTANTE!!!!!
RICORDARSI DI SETTARE LE TIPOLOGIE DI PRATICHE E I MENUFILE
*/

/* IMPORTAZIONE ELENCO UTENTI */
UPDATE admin.users SET gisclient=0 WHERE userid >10;
DELETE FROM admin.users WHERE userid>10;
SELECT setval('admin.users_userid_seq', 10, true);

insert into admin.users(app,cognome,nominativo,gruppi,username,pwd,enc_pwd)
(SELECT 
trim(split_part("NOME",' ',1)) as app,
trim(split_part("NOME",' ',3)) as cognome,
trim(split_part("NOME",' ',2)) as nominativo,
CASE WHEN ("RESPPROC"=1) THEN '1,3,4' ELSE '3,4' END AS gruppi, 
CASE WHEN (trim(split_part("NOME",' ',3))='') THEN lower(trim(split_part("NOME",' ',2))) ELSE substr(lower(trim(split_part("NOME",' ',2))) , 1 , 1)||lower(trim(split_part("NOME",' ',3))) END as username,
CASE WHEN (trim(split_part("NOME",' ',3))='') THEN lower(trim(split_part("NOME",' ',2))) ELSE substr(lower(trim(split_part("NOME",' ',2))) , 1 , 1)||lower(trim(split_part("NOME",' ',3))) END as pwd,
CASE WHEN (trim(split_part("NOME",' ',3))='') THEN md5(lower(trim(split_part("NOME",' ',2)))) ELSE md5(substr(lower(trim(split_part("NOME",' ',2))) , 1 , 1)||lower(trim(split_part("NOME",' ',3)))) END as enc_pwd
FROM import."ELENCO_UTENTI" WHERE "ID">0 AND "ID"<>35
) ;
/*IMPORTAZIONE ELENCO ENTI*/

DELETE FROM pe.e_enti;
INSERT INTO pe.e_enti(id,nome,ordine,stampa,testo_stampa) (select distinct "ID","NOME","ORDINE","STAMPA"::int,"TESTO_STAMPA" FROM import."ELENCO_ENTI");

/*IMPORTANTE!!!!!!
RICORDARSI DI SETTARE PARERI NON PIU' VALIDI, PARERI INTERNI*/

/*IMPORTAZIONE ELENCO DOCUMENTI*/
DELETE FROM pe.e_documenti;
INSERT INTO pe.e_documenti(id,iter, nome, descrizione)
(SELECT "ID",("PROG_ITER"+1)*10, "NOME", "DESCRIZIONE"
  FROM import."ELENCO_DOCUMENTI");

/*IMPORTAZIONE ELENCO TARIFFE ONERI*/



  
