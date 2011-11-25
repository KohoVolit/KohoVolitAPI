-- KohoVolit.eu Generación Cuarta
-- tables of package DIVISION

-- Table: division_kind

-- DROP TABLE division_kind;

CREATE TABLE division_kind
(
  code character varying NOT NULL,
  "name" character varying NOT NULL,
  description text,
  CONSTRAINT division_kind_pkey PRIMARY KEY (code)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE division_kind OWNER TO kohovolit;
GRANT ALL ON TABLE division_kind TO kohovolit;
GRANT SELECT ON TABLE division_kind TO kv_user;
GRANT SELECT ON TABLE division_kind TO kv_editor;
GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE division_kind TO kv_admin;

-- Table: division

-- DROP TABLE division;

CREATE TABLE division
(
  id serial NOT NULL,
  "name" character varying,
  "date" timestamp without time zone NOT NULL,
  parliament_code character varying NOT NULL,
  division_kind_code character varying NOT NULL,
  CONSTRAINT division_pkey PRIMARY KEY (id),
  CONSTRAINT division_division_kind_code_fkey FOREIGN KEY (division_kind_code)
      REFERENCES division_kind (code) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT division_parliament_code_fkey FOREIGN KEY (parliament_code)
      REFERENCES parliament (code) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE division OWNER TO kohovolit;
GRANT ALL ON TABLE division TO kohovolit;
GRANT SELECT ON TABLE division TO kv_user;
GRANT SELECT ON TABLE division TO kv_editor;
GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE division TO kv_admin;

-- Table: division_attribute

-- DROP TABLE division_attribute;

CREATE TABLE division_attribute
(
-- Inherited from table attribute:  "name" character varying NOT NULL,
-- Inherited from table attribute:  "value" character varying,
-- Inherited from table attribute:  lang character varying NOT NULL DEFAULT '-'::character varying,
-- Inherited from table attribute:  since timestamp with time zone NOT NULL DEFAULT '-infinity'::timestamp without time zone,
-- Inherited from table attribute:  "until" timestamp with time zone NOT NULL DEFAULT 'infinity'::timestamp without time zone,
  division_id integer NOT NULL,
  parl character varying NOT NULL DEFAULT '-'::character varying,
  CONSTRAINT division_attribute_pkey PRIMARY KEY (division_id, name, lang, parl, since),
  CONSTRAINT division_attribute_division_id_fkey FOREIGN KEY (division_id)
      REFERENCES division (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT mp_attribute_parl_fkey FOREIGN KEY (parl)
      REFERENCES parliament (code) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT attribute_check CHECK (since <= until)
)
INHERITS (attribute)
WITH (
  OIDS=FALSE
);
ALTER TABLE division_attribute OWNER TO kohovolit;
GRANT ALL ON TABLE division_attribute TO kohovolit;
GRANT SELECT ON TABLE division_attribute TO kv_user;
GRANT SELECT ON TABLE division_attribute TO kv_editor;
GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE division_attribute TO kv_admin;

-- Table: vote_kind

-- DROP TABLE vote_kind;

CREATE TABLE vote_kind
(
  code character varying NOT NULL,
  "name" character varying NOT NULL,
  description text,
  CONSTRAINT vote_kind_pkey PRIMARY KEY (code)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE vote_kind OWNER TO kohovolit;
GRANT ALL ON TABLE vote_kind TO kohovolit;
GRANT SELECT ON TABLE vote_kind TO kv_user;
GRANT SELECT ON TABLE vote_kind TO kv_editor;
GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE vote_kind TO kv_admin;

-- Table: mp_vote

-- DROP TABLE mp_vote;

CREATE TABLE mp_vote
(
  mp_id integer NOT NULL,
  division_id integer NOT NULL,
  vote_kind_code character varying NOT NULL,
  CONSTRAINT mp_vote_pkey PRIMARY KEY (mp_id, division_id),
  CONSTRAINT mp_vote_division_id_fkey FOREIGN KEY (division_id)
      REFERENCES division (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT mp_vote_mp_id_fkey FOREIGN KEY (mp_id)
      REFERENCES mp (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE mp_vote OWNER TO kohovolit;
GRANT ALL ON TABLE mp_vote TO kohovolit;
GRANT SELECT ON TABLE mp_vote TO kv_user;
GRANT SELECT ON TABLE mp_vote TO kv_editor;
GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE mp_vote TO kv_admin;

-- Table: vote_kind_attribute

-- DROP TABLE vote_kind_attribute;

CREATE TABLE vote_kind_attribute
(
-- Inherited from table attribute:  "name" character varying NOT NULL,
-- Inherited from table attribute:  "value" character varying,
-- Inherited from table attribute:  lang character varying NOT NULL DEFAULT '-'::character varying,
-- Inherited from table attribute:  since timestamp with time zone NOT NULL DEFAULT '-infinity'::timestamp without time zone,
-- Inherited from table attribute:  "until" timestamp with time zone NOT NULL DEFAULT 'infinity'::timestamp without time zone,
  vote_kind_code character varying NOT NULL,
  CONSTRAINT vote_kind_attribute_pkey PRIMARY KEY (vote_kind_code, name, lang, since),
  CONSTRAINT vote_kind_attribute_vote_kind_code_fkey FOREIGN KEY (vote_kind_code)
      REFERENCES vote_kind (code) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT attribute_check CHECK (since <= until)
)
INHERITS (attribute)
WITH (
  OIDS=FALSE
);
ALTER TABLE vote_kind_attribute OWNER TO kohovolit;
GRANT ALL ON TABLE vote_kind_attribute TO kohovolit;
GRANT SELECT ON TABLE vote_kind_attribute TO kv_user;
GRANT SELECT ON TABLE vote_kind_attribute TO kv_editor;
GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE vote_kind_attribute TO kv_admin;

-- Table: division_kind_attribute

-- DROP TABLE division_kind_attribute;

CREATE TABLE division_kind_attribute
(
-- Inherited from table attribute:  "name" character varying NOT NULL,
-- Inherited from table attribute:  "value" character varying,
-- Inherited from table attribute:  lang character varying NOT NULL DEFAULT '-'::character varying,
-- Inherited from table attribute:  since timestamp with time zone NOT NULL DEFAULT '-infinity'::timestamp without time zone,
-- Inherited from table attribute:  "until" timestamp with time zone NOT NULL DEFAULT 'infinity'::timestamp without time zone,
  division_kind_code character varying NOT NULL,
  CONSTRAINT division_kind_attribute_pkey PRIMARY KEY (division_kind_code, name, lang, since),
  CONSTRAINT division_kind_attribute_division_kind_code_fkey FOREIGN KEY (division_kind_code)
      REFERENCES division_kind (code) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT attribute_check CHECK (since <= until)
)
INHERITS (attribute)
WITH (
  OIDS=FALSE
);
ALTER TABLE division_kind_attribute OWNER TO kohovolit;
GRANT ALL ON TABLE division_kind_attribute TO kohovolit;
GRANT SELECT ON TABLE division_kind_attribute TO kv_user;
GRANT SELECT ON TABLE division_kind_attribute TO kv_editor;
GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE division_kind_attribute TO kv_admin;



GRANT ALL ON TABLE division_id_seq TO kohovolit;
GRANT USAGE ON TABLE division_id_seq TO kv_admin;
