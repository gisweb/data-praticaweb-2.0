create or replace function stp.fido_oc(pr integer) returns text as $$
DECLARE
    rec record;
BEGIN
    select into rec  * from oneri.fidi where pratica=pr and tipo_fido=1 order by data DESC LIMIT 1;
    return ' e fidejussione con la ' || coalesce(rec.istituto,'') || ', poliza n. ' || rec.numero || ' del ' || coalesce(to_char(rec.data,'DD/MM/YYYY'),'') || ' per Euro ' || coalesce(trim(to_char(rec.importo,'999G999G999D99')),'') || '';
END
$$
language plpgsql immutable;


create or replace function stp.fido_cm(pr integer) returns text as $$
DECLARE
    rec record;
    rec_cm record;
BEGIN
    select into rec  * from oneri.fidi where pratica=pr and tipo_fido=2 order by data DESC LIMIT 1;
    select into rec_cm  * from oneri.c_monetario where pratica=pr order by uidins DESC LIMIT 1;
    return 'e fidejussione con la ' || coalesce(rec.istituto,'') || ', poliza n. ' || coalesce(rec.numero,'') || ' del ' || coalesce(to_char(rec.data,'DD/MM/YYYY'),'') || ' per Euro ' || coalesce(trim(to_char(rec.importo,'999G999G999D99')),'') || ' quale corrispettivo monetario in sostituzione delle aree di cessione nei lotti interclusi, per la sola quota di mq ' || trim(to_char(rec_cm.sup_cessione,'999G999D99')) || '.';
END
$$
language plpgsql immutable; 
