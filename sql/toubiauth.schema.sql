-- Adminer 4.17.1 PostgreSQL 17.4 (Debian 17.4-1.pgdg120+2) dump

DROP TABLE IF EXISTS "users";
CREATE TABLE "public"."users" (
    "id" uuid NOT NULL,
    "email" character varying(128) NOT NULL,
    "password" character varying(256) NOT NULL,
    "role" smallint DEFAULT '0' NOT NULL,
    CONSTRAINT "users_email" UNIQUE ("email"),
    CONSTRAINT "users_id" PRIMARY KEY ("id")
) WITH (oids = false);


-- 2025-06-30 12:33:23.669555+00
