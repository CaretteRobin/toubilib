-- Adminer 4.17.1 PostgreSQL 17.4 (Debian 17.4-1.pgdg120+2) dump

DROP TABLE IF EXISTS "rdv";
CREATE TABLE "public"."rdv" (
    "id" character varying(64) NOT NULL,
    "praticien_id" character varying(64) NOT NULL,
    "patient_id" character varying(64) NOT NULL,
    "patient_email" character varying(128),
    "date_heure_debut" timestamp NOT NULL,
    "status" smallint DEFAULT '0' NOT NULL,
    "duree" smallint DEFAULT '30' NOT NULL,
    "date_heure_fin" timestamp,
    "date_creation" timestamp,
    "motif_visite" character varying(128)
) WITH (oids = false);


-- 2025-06-30 12:27:45.555336+00
