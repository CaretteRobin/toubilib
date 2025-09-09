-- Adminer 4.17.1 PostgreSQL 17.4 (Debian 17.4-1.pgdg120+2) dump

DROP TABLE IF EXISTS "dossiers";
CREATE TABLE "public"."dossiers" (
    "id" character varying(64) NOT NULL,
    "patient_id" character varying(64) NOT NULL,
    "type_document" character varying(64) NOT NULL,
    "date_creation" date,
    "cree_par" character varying(64),
    "filename" character varying(128)
) WITH (oids = false);


DROP TABLE IF EXISTS "patient";
CREATE TABLE "public"."patient" (
    "id" character varying(64) NOT NULL,
    "nom" character varying(64) NOT NULL,
    "prenom" character varying(64) NOT NULL,
    "date_naissance" date,
    "adresse" text,
    "code_postal" character varying(8),
    "ville" character varying(64),
    "email" character varying(128),
    "telephone" character varying(24) NOT NULL
) WITH (oids = false);


-- 2025-06-30 12:29:55.790373+00
