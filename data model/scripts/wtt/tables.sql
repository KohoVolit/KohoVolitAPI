-- KohoVolit.eu Generación Cuarta
-- tables of package WTT

create table letter
(
	id serial primary key,
	subject varchar not null,
	body_ text not null,
	sender_name varchar not null,
	sender_address varchar,
	sender_email varchar not null,
	sent_on timestamp not null default current_timestamp,
	is_public boolean not null,
	confirmed boolean not null default false,
	reply_code varchar unique,
	unique (subject, body_, sender_email)
);

create table letter_to_mp
(
	letter_id integer references letter on delete cascade on update cascade,
	mp_id integer references mp on delete cascade on update cascade,
	primary key (letter_id, mp_id)
);

create table answer
(
	letter_id integer references letter on delete restrict on update cascade,
	mp_id integer references mp on delete cascade on update cascade,
	subject varchar not null,
	body_ text not null,
	received_on timestamp not null default current_timestamp,
	primary key (letter_id, mp_id)
);

create table area
(
	constituency_id integer references constituency on delete restrict on update cascade,
	country varchar default '*',
	administrative_area_level_1 varchar default '*',
	administrative_area_level_2 varchar default '*',
	administrative_area_level_3 varchar default '*',
	locality varchar default '*',
	sublocality varchar default '*',
	neigborhood varchar default '*',
	route varchar default '*',
	street_number varchar default '*',
	since timestamp not null default '-infinity',
	until timestamp not null default 'infinity',
	primary key (constituency_id, country, administrative_area_level_1, administrative_area_level_2, administrative_area_level_3, locality, sublocality, neigborhood, route, street_number, since),
	check (since <= until)
);

-- attributes
create table letter_attribute
(
	letter_id integer references letter on delete cascade on update cascade,
	primary key (letter_id, name_, lang, since),
	foreign key (lang) references language_ on delete restrict on update cascade
) inherits (attribute_);

-- privileges on objects
grant select
	on table letter, letter_to_mp, answer, area, letter_attribute
	to kv_user, kv_editor, kv_admin;
grant insert, update, delete, truncate
	on table letter, letter_to_mp, answer, area, letter_attribute
	to kv_admin;
grant usage
	on sequence letter_id_seq
	to kv_admin;
