-- Adminer 4.3.1 PostgreSQL dump

DROP TABLE IF EXISTS "stat_info";
CREATE SEQUENCE stat_info_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "public"."stat_info" (
    "id" integer DEFAULT nextval('stat_info_id_seq') NOT NULL,
    "stat" character varying(8) NOT NULL,
    CONSTRAINT "stat_info_id_uindex" UNIQUE ("id"),
    CONSTRAINT "stat_info_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

INSERT INTO "stat_info" ("id", "stat") VALUES
(1,	'waiting'),
(2,	'process'),
(3,	'success'),
(4,	'error');

DROP TABLE IF EXISTS "queue_domain";
CREATE SEQUENCE table_name_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "public"."queue_domain" (
    "id" integer DEFAULT nextval('table_name_id_seq') NOT NULL,
    "user_id" integer NOT NULL,
    "ts_create" timestamp DEFAULT now(),
    "stat_id" integer DEFAULT 1 NOT NULL,
    "domain_id" integer NOT NULL,
    "ts_update" timestamp DEFAULT now(),
    "error_log" character varying,
    CONSTRAINT "table_name_id_uindex" UNIQUE ("id"),
    CONSTRAINT "table_name_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "queue_domain_domains_id_fk" FOREIGN KEY (domain_id) REFERENCES domains(id) NOT DEFERRABLE,
    CONSTRAINT "queue_domain_stat_info_id_fk" FOREIGN KEY (stat_id) REFERENCES stat_info(id) NOT DEFERRABLE,
    CONSTRAINT "queue_domain_users_id_fk" FOREIGN KEY (user_id) REFERENCES users(id) NOT DEFERRABLE
) WITH (oids = false);

DROP TABLE IF EXISTS "users";
CREATE SEQUENCE users_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "public"."users" (
    "id" integer DEFAULT nextval('users_id_seq') NOT NULL,
    "login" character varying(32),
    "ts_create" timestamp DEFAULT now() NOT NULL,
    "ts_update" timestamp DEFAULT now(),
    CONSTRAINT "users_id_uindex" UNIQUE ("id"),
    CONSTRAINT "users_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

DROP TABLE IF EXISTS "domains";
CREATE SEQUENCE domains_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "public"."domains" (
    "id" integer DEFAULT nextval('domains_id_seq') NOT NULL,
    "domain" character varying(255) NOT NULL,
    "ts_create" timestamp DEFAULT now() NOT NULL,
    "info" character varying,
    "ts_update" timestamp DEFAULT now(),
    "user_id" integer NOT NULL,
    CONSTRAINT "domains_id_uindex" UNIQUE ("id"),
    CONSTRAINT "domains_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "domains_users_id_fk" FOREIGN KEY (user_id) REFERENCES users(id) NOT DEFERRABLE
) WITH (oids = false);

DROP TABLE IF EXISTS "notify_settings";
CREATE TABLE "public"."notify_settings" (
    "user_id" integer NOT NULL,
    "channel_id" integer,
    "email" character varying(32),
    CONSTRAINT "notify_settings_user_id_channel_id_uindex" UNIQUE ("user_id", "channel_id"),
    CONSTRAINT "notify_settings_channel_info_id_fk" FOREIGN KEY (channel_id) REFERENCES channel_info(id) NOT DEFERRABLE,
    CONSTRAINT "notify_settings_users_id_fk" FOREIGN KEY (user_id) REFERENCES users(id) NOT DEFERRABLE
) WITH (oids = false);

DROP TABLE IF EXISTS "channel_info";
CREATE SEQUENCE channel_info_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "public"."channel_info" (
    "id" integer DEFAULT nextval('channel_info_id_seq') NOT NULL,
    "channel" character varying(16),
    CONSTRAINT "channel_info_id_uindex" UNIQUE ("id"),
    CONSTRAINT "channel_info_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

INSERT INTO "channel_info" ("id", "channel") VALUES
(1,	'main'),
(2,	'info'),
(3,	'official'),
(4,	'doc');

-- 2018-11-14 21:04:31.76356+00
