<?php

$query["search-online"]=<<<EOT
WITH istanze_online AS (
select id,pratica,prot_integ::varchar as protocollo,data_integ as data_protocollo,'Integrazione'::varchar as tipo,2 as ordine from pe.integrazioni where online=1
UNION ALL
select id,pratica,protocollo_il::varchar as protocollo,data_prot_il as data_protocollo,'Inizio Lavori'::varchar as tipo,3 as ordine from pe.lavori where il_online=1
UNION ALL
select id,pratica,protocollo_fl::varchar as protocollo,data_prot_fl as data_protocollo,'Fine Lavori'::varchar as tipo,4 as ordine from pe.lavori where fl_online=1
UNION ALL
SELECT id,pratica,protocollo::varchar,data_prot as data_protocollo,'Istanza'::varchar as tipo,1 as ordine from pe.avvioproc WHERE online=1
)
SELECT
    XX.tipo as tipo_istanza,A.pratica,XX.data_protocollo as data_ordinamento,A.numero,XX.protocollo,XX.data_protocollo as data_prot,A.data_presentazione,A.oggetto,1 as online,
    B.nome as tipo_pratica,C.descrizione as tipo_intervento,coalesce(D.nome,'non assegnata') as responsabile,
    E.richiedente,F.progettista,L.esecutore,G.elenco_ct,H.elenco_cu,I.ubicazione,
    CASE WHEN (coalesce(A.resp_it,coalesce(A.resp_ia,0)) = 0) THEN 0 ELSE 1 END as assegnata_istruttore
    ,coalesce(O.nome,'non assegnata') as responsabile_it,M.titolo,M.data_rilascio,A.sportello,Q.opzione as vincolo_paes
    ,coalesce(R.nome,'non assegnata') as responsabile_ia
FROM 
istanze_online XX INNER JOIN 
pe.avvioproc A USING (pratica) LEFT JOIN 
pe.e_tipopratica B ON(A.tipo=B.id) LEFT JOIN
pe.e_intervento C ON (A.intervento=C.id) LEFT JOIN
admin.users D ON(A.resp_proc=D.userid) LEFT JOIN 
(SELECT pratica,trim(array_to_string(array_agg(coalesce(app||' ','')||coalesce(' '||nome,'')||coalesce(' '||cognome)||coalesce(' - '||ragsoc,'')),',')) as richiedente FROM pe.soggetti WHERE richiedente=1 AND coalesce(voltura,0)=0 GROUP BY pratica) E USING(pratica) LEFT JOIN
(SELECT pratica,trim(array_to_string(array_agg(coalesce(app||' ','')||coalesce(' '||nome,'')||coalesce(' '||cognome)||coalesce(' - '||ragsoc,'')),',')) as progettista FROM pe.soggetti WHERE progettista=1 AND coalesce(voltura,0)=0 GROUP BY pratica) F USING(pratica) LEFT JOIN
(SELECT pratica,trim(array_to_string(array_agg(coalesce(app||' ','')||coalesce(' '||nome,'')||coalesce(' '||cognome)||coalesce(' - '||ragsoc,'')),',')) as esecutore FROM pe.soggetti WHERE esecutore=1 AND coalesce(voltura,0)=0 GROUP BY pratica) L USING(pratica) LEFT JOIN
(SELECT * FROM pe.grp_particelle_ct) G USING(pratica) LEFT JOIN
(SELECT * FROM pe.grp_particelle_cu) H USING(pratica) LEFT JOIN
(SELECT indirizzi.pratica, array_to_string(array_agg((COALESCE(indirizzi.via, ''::character varying)::text || COALESCE(' '::text || indirizzi.civico::text,'')) || COALESCE(' int.'::text || indirizzi.interno::text, ''::text)), ', '::text) AS ubicazione
   FROM pe.indirizzi
  GROUP BY indirizzi.pratica) I USING(pratica) LEFT JOIN
(SELECT pratica,titolo,data_rilascio FROM pe.titolo) M USING(pratica) LEFT JOIN
(SELECT pratica,trim(array_to_string(array_agg(cip::varchar),',')) as cip FROM pe.soggetti WHERE esecutore=1 AND coalesce(voltura,0)=0 GROUP BY pratica) N USING(pratica) LEFT JOIN
(SELECT pratica,il,fl,protocollo_il,protocollo_fl FROM pe.lavori) P USING(pratica)
LEFT JOIN admin.users O ON(A.resp_it=O.userid)
LEFT JOIN admin.users R ON(A.resp_ia=R.userid)
LEFT JOIN pe.elenco_opzione_ap Q ON (vincolo_paes=Q.id)
%s %s %s LIMIT %s OFFSET %s     
EOT;
?>