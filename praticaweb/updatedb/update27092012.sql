insert into admin.users
(userid,username,enc_pwd,data_creazione,permessi,attivato,pwd,gisclient,cognome) VALUES
(100,'sistema',md5('@1scl13nt!'),now(),1,0,'@1scl13nt!',0,'Utente di Sistema');

create temp table soggetti as (select idsogge,nominativo from importazione.ags_sogge where idsogge in (select distinct sog_idsogge from importazione.gen_mdo_movimento));


create table importazione.movimenti as 
select pratica,codice,100 as utente_in,userid as utente_fi,data,note,100 as uidins  from
(select gpr_id_pratica as pratica, dt_movidoc::date as data,annotazio,sog_idsogge as idsogge,id,codice,case when (codice='mv') then ds_tbcaumdo else note end  as note from importazione.gen_mdo_movimento inner join importazione.gen_mdo_tbcaumdo on(id_tbcaumdo=cdo_id_tbcaumdo) order by 2,3) X
inner join 
(select userid,idsogge,nome from soggetti, admin.users  WHERE soggetti.nominativo ilike '%'||users.cognome||'%') Y
using (idsogge);