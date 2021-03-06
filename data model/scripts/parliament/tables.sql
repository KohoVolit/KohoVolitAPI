-- KohoVolit.eu Generación Cuarta
-- tables of package PARLIAMENT

create table parliament_kind
(
	code varchar primary key,
	"name" varchar not null,
	short_name varchar,
	description text,
	weight real
);

create table parliament
(
	code varchar primary key,
	"name" varchar not null,
	short_name varchar,
	description text,
	parliament_kind_code varchar references parliament_kind on delete restrict on update cascade,
	country_code varchar references country on delete restrict on update cascade,
	time_zone varchar,
	last_updated_on timestamp
);

create table term
(
	id serial primary key,
	"name" varchar not null,
	short_name varchar,
	description text,
	country_code varchar not null references country on delete restrict on update cascade,
	parliament_kind_code varchar not null references parliament_kind on delete restrict on update cascade,
	since timestamp with time zone not null default '-infinity',
	until timestamp with time zone not null default 'infinity',
	unique ("name", country_code, parliament_kind_code),
	check (since <= until)
);

create table constituency
(
	id serial primary key,
	"name" varchar not null,
	short_name varchar,
	description text,
	parliament_code varchar not null references parliament on delete cascade on update cascade,
	since timestamp with time zone not null default '-infinity',
	until timestamp with time zone not null default 'infinity',
	unique ("name", parliament_code, since),
	check (since <= until)
);

--attributes
create table parliament_kind_attribute
(
	parliament_kind_code varchar references parliament_kind on delete cascade on update cascade,
	cntry varchar references country on delete restrict on update cascade default '-',
	primary key (parliament_kind_code, "name", lang, cntry, since),
	foreign key (lang) references "language" on delete restrict on update cascade
) inherits ("attribute");

create table parliament_attribute
(
	parliament_code varchar references parliament on delete cascade on update cascade,
	primary key (parliament_code, "name", lang, since),
	foreign key (lang) references "language" on delete restrict on update cascade
) inherits ("attribute");

create table term_attribute
(
	term_id integer references term on delete cascade on update cascade,
	parl varchar references parliament on delete restrict on update cascade default '-',
	primary key (term_id, "name", lang, parl, since),
	foreign key (lang) references "language" on delete restrict on update cascade
) inherits ("attribute");

create table constituency_attribute
(
	constituency_id integer references constituency on delete cascade on update cascade,
	parl varchar references parliament on delete restrict on update cascade default '-',
	primary key (constituency_id, "name", lang, parl, since),
	foreign key (lang) references "language" on delete restrict on update cascade
) inherits ("attribute");

-- privileges on objects
grant select
	on table parliament_kind, parliament, term, constituency, parliament_kind_attribute, parliament_attribute, term_attribute, constituency_attribute
	to kv_user, kv_editor, kv_admin;
grant insert, update, delete, truncate
	on table parliament_kind, parliament, term, constituency, parliament_kind_attribute, parliament_attribute, term_attribute, constituency_attribute
	to kv_admin;
grant usage
	on sequence term_id_seq, constituency_id_seq
	to kv_admin;
