-- KohoVolit.eu Generaci�n Quarta
-- tables of package MP

create table mp
(
	id serial primary key,
	first_name varchar not null,
	middle_names varchar not null default '',
	last_name varchar not null,
	disambiguation varchar not null default '',
	sex char check (sex = 'm' or sex = 'f'),
	pre_title varchar,
	post_title varchar,
	born_on date,
	died_on date,
	email varchar,
	webpage varchar,
	address varchar,
	phone varchar,
	unique (last_name, first_name, middle_names, disambiguation),
	check (born_on < died_on)
);

create table office
(
	mp_id integer references mp on delete cascade on update cascade,
	address varchar,
	phone varchar,
	since timestamp default '-infinity',
	until timestamp default 'infinity',
	primary key (mp_id, address, since),
	check (since < until)
);

-- attributes
create table mp_attribute
(
	mp_id integer references mp on delete restrict on update cascade,
	primary key (mp_id, name_, lang, since),
	foreign key (lang) references language_ on delete restrict on update cascade
) inherits (attribute_);

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