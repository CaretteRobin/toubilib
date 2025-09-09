-- Adminer 4.17.1 PostgreSQL 17.4 (Debian 17.4-1.pgdg120+2) dump

DROP TABLE IF EXISTS "motif_visite";
DROP SEQUENCE IF EXISTS motif_visite_id_seq;
CREATE SEQUENCE motif_visite_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."motif_visite" (
    "id" integer DEFAULT nextval('motif_visite_id_seq') NOT NULL,
    "specialite_id" integer NOT NULL,
    "libelle" character varying(128) NOT NULL,
    CONSTRAINT "motif_visite_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "moyen_paiement";
DROP SEQUENCE IF EXISTS moyen_paiement_id_seq;
CREATE SEQUENCE moyen_paiement_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."moyen_paiement" (
    "id" integer DEFAULT nextval('moyen_paiement_id_seq') NOT NULL,
    "libelle" character varying(32) NOT NULL,
    CONSTRAINT "moyen_paiement_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "praticien";
CREATE TABLE "public"."praticien" (
    "id" uuid NOT NULL,
    "nom" character varying(48) NOT NULL,
    "prenom" character varying(48) NOT NULL,
    "ville" character varying(48) NOT NULL,
    "email" character varying(128) NOT NULL,
    "telephone" character varying(24) NOT NULL,
    "specialite_id" integer NOT NULL,
    "structure_id" uuid,
    "rpps_id" character varying(12),
    "organisation" bit(1) DEFAULT '0' NOT NULL,
    "nouveau_patient" bit(1) DEFAULT '1' NOT NULL,
    "titre" character varying(8) DEFAULT 'Dr.' NOT NULL
) WITH (oids = false);


DROP TABLE IF EXISTS "praticien2motif";
CREATE TABLE "public"."praticien2motif" (
    "praticien_id" uuid NOT NULL,
    "motif_id" integer NOT NULL
) WITH (oids = false);


DROP TABLE IF EXISTS "praticien2moyen";
CREATE TABLE "public"."praticien2moyen" (
    "praticien_id" uuid NOT NULL,
    "moyen_id" integer NOT NULL
) WITH (oids = false);


DROP TABLE IF EXISTS "specialite";
DROP SEQUENCE IF EXISTS specialite_id_seq;
CREATE SEQUENCE specialite_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."specialite" (
    "id" integer DEFAULT nextval('specialite_id_seq') NOT NULL,
    "libelle" character varying(48) NOT NULL,
    "description" text,
    CONSTRAINT "specialite_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "structure";
CREATE TABLE "public"."structure" (
    "id" uuid DEFAULT gen_random_uuid() NOT NULL,
    "nom" character varying(48) NOT NULL,
    "adresse" text NOT NULL,
    "ville" character varying(128),
    "code_postal" character varying(12),
    "telephone" character varying(24)
) WITH (oids = false);


-- 2025-06-30 12:31:32.033009+00
