-- KohoVolit.eu Generaci�n Quarta
-- tables of package PARLIAMENT

create table parliament_kind
(
	code varchar primary key,
	name_ varchar not null,
	short_name varchar,
	description text
);

create table parliament
(
	code varchar primary key,
	name_ varchar not null,
	short_name varchar,
	description text,
	parliament_kind_code varchar references parliament_kind on delete restrict on update cascade,
	country_code varchar references country on delete restrict on update cascade,
	default_language varchar references language_ on delete restrict on update cascade
);

create table term
(
	id serial primary key,
	name_ varchar not null,
	short_name varchar,
	description text,
	parliament_kind_code varchar references parliament_kind on delete restrict on update cascade,
	since timestamp not null default '-infinity',
	until timestamp not null default 'infinity',
	unique (name_, parliament_kind_code),
	check (since < until)
);

create table constituency
(
	id serial primary key,
	name_ varchar not null,
	short_name varchar,
	description text,
	parliament_code varchar not null references parliament on delete cascade on update cascade,
	unique (name_, parliament_code)
);

--attributes
create table parliament_kind_attribute
(
	parliament_kind_code varchar references parliament_kind on delete restrict on update cascade,
	primary key (parliament_kind_code, name_, lang, since),
	foreign key (lang) references language_ on delete restrict on update cascade
) inherits (attribute_);

create table parliament_attribute
(
	parliament_code varchar references parliament on delete restrict on update cascade,
	primary key (parliament_code, name_, lang, since),
	foreign key (lang) references language_ on delete restrict on update cascade
) inherits (attribute_);

create table term_attribute
(
	term_id integer references term on delete restrict on update cascade,
	primary key (term_id, name_, lang, since),
	foreign key (lang) references language_ on delete restrict on update cascade
) inherits (attribute_);

create table constituency_attribute
(
	constituency_id integer references constituency on delete restrict on update cascade,
	primary key (constituency_id, name_, lang, since),
	foreign key (lang) references language_ on delete restrict on update cascade
) inherits (attribute_);

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