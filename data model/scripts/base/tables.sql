-- KohoVolit.eu Generación Quarta
-- tables of package BASE

create table language_
(
	code varchar primary key,
	name_ varchar not null,
	short_name varchar,
	description text,
	locale varchar
);

create table country
(
	code varchar primary key,
	name_ varchar not null,
	short_name varchar,
	description text
);

create table api_log
(
	method varchar not null,
	function_ varchar not null,
	query varchar,
	data_ text,
	format varchar,
	referrer inet not null,
	called_on timestamp not null default current_timestamp
);

create table attribute_
(
	name_ varchar,
	value_ varchar,
	lang varchar references language_ on delete restrict on update cascade default '-',
	since timestamp default '-infinity',
	until timestamp default 'infinity',
	check (since < until)
);

--attributes
create table language_attribute
(
	language_code varchar references language_ on delete restrict on update cascade,
	primary key (language_code, name_, lang, since),
	foreign key (lang) references language_ on delete restrict on update cascade
) inherits (attribute_);

create table country_attribute
(
	country_code varchar references country on delete restrict on update cascade,
	primary key (country_code, name_, lang, since),
	foreign key (lang) references language_ on delete restrict on update cascade
) inherits (attribute_);

-- privileges on objects
grant select
	on table language_, country, api_log, language_attribute, country_attribute
	to kv_user, kv_editor, kv_admin;
grant insert, update, delete, truncate
	on table language_, country, api_log, language_attribute, country_attribute
	to kv_admin;
