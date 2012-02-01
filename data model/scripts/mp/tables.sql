-- KohoVolit.eu Generación Cuarta
-- tables of package MP

create table mp
(
	id serial primary key,
	first_name varchar not null,
	middle_names varchar not null default '',
	last_name varchar not null,
	disambiguation varchar not null default '',
	sex char check (sex in ('m', 'f')),
	pre_title varchar,
	post_title varchar,
	born_on date,
	died_on date,
	last_updated_on timestamp not null default current_timestamp,
	name_data tsvector,
	unique (last_name, first_name, middle_names, disambiguation),
	check (born_on <= died_on)
);

create table office
(
	mp_id integer references mp on delete cascade on update cascade,
	parliament_code varchar references parliament on delete restrict on update cascade,
	address varchar,
	phone varchar,
	latitude double precision,
	longitude double precision,
	relevance real,
	since timestamp with time zone not null default '-infinity',
	until timestamp with time zone not null default 'infinity',
	primary key (mp_id, parliament_code, address, since),
	check (since <= until)
);

-- attributes
create table mp_attribute
(
	mp_id integer references mp on delete cascade on update cascade,
	parl varchar references parliament on delete restrict on update cascade default '-',
	primary key (mp_id, "name", lang, parl, since),
	foreign key (lang) references "language" on delete restrict on update cascade
) inherits ("attribute");

-- indexes (except PRIMARY KEY and UNIQUE constraints, for which the indexes have been created automatically)
create index mp_name_data on mp using gin(name_data);

-- privileges on objects
grant select
	on table mp, office, mp_attribute
	to kv_user, kv_editor, kv_admin;
grant insert, update, delete, truncate
	on table mp, office, mp_attribute
	to kv_admin;
grant usage
	on sequence mp_id_seq
	to kv_admin;
