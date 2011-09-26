-- KohoVolit.eu Generación Cuarta
-- tables of package BASE

create table "language"
(
	code varchar primary key,
	"name" varchar not null,
	short_name varchar,
	description text,
	locale varchar
);

create table country
(
	code varchar primary key,
	"name" varchar not null,
	short_name varchar,
	description text
);

create table api_log
(
	method varchar not null,
	project varchar not null,
	resource varchar not null,
	query varchar,
	"data" text,
	format varchar,
	referrer inet not null,
	called_on timestamp not null default current_timestamp
);

create table "attribute"
(
	"name" varchar,
	"value" varchar,
	lang varchar references "language" on delete restrict on update cascade default '-',
	since timestamp with time zone not null default '-infinity',
	until timestamp with time zone not null default 'infinity',
	check (since <= until)
);

--attributes
create table language_attribute
(
	language_code varchar references "language" on delete cascade on update cascade,
	primary key (language_code, "name", lang, since),
	foreign key (lang) references "language" on delete restrict on update cascade
) inherits ("attribute");

create table country_attribute
(
	country_code varchar references country on delete cascade on update cascade,
	primary key (country_code, "name", lang, since),
	foreign key (lang) references "language" on delete restrict on update cascade
) inherits ("attribute");

-- privileges on objects
grant select
	on table "language", country, api_log, language_attribute, country_attribute
	to kv_user, kv_editor, kv_admin;
grant insert, update, delete, truncate
	on table "language", country, api_log, language_attribute, country_attribute
	to kv_admin;
