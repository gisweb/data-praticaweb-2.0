DROP DATABASE IF EXISTS sit_alghero_old;
ALTER DATABASE sit_alghero RENAME TO sit_alghero_old;
CREATE DATABASE sit_alghero
  WITH OWNER = postgres
	   TEMPLATE = template_postgis
       ENCODING = 'UTF8'
       TABLESPACE = pg_default
       LC_COLLATE = 'Italian_Italy.1252'
       LC_CTYPE = 'Italian_Italy.1252'
       CONNECTION LIMIT = -1;

ALTER DATABASE sit_alghero
  SET standard_conforming_strings = 'off';
ALTER DATABASE sit_alghero
  SET DateStyle = 'SQL,DMY';