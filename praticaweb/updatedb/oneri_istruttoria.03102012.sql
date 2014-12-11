ALTER TABLE pe.avvioproc
   ADD COLUMN data_vers_oi date;
ALTER TABLE pe.avvioproc
   ADD COLUMN n_vers_oi character varying;
ALTER TABLE pe.avvioproc
   ADD COLUMN importo_vers_oi numeric;   

ALTER TABLE pe.atti
   ADD COLUMN raccolta character varying;
