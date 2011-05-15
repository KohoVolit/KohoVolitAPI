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
	state_ varchar not null default 'created' check (state_ in ('created', 'waiting for approval', 'refused', 'sent', 'answered', 'unanswered')),
	reply_code varchar not null unique,
	approval_code varchar,
	unique (subject, body_, sender_email)
);

create table letter_to_mp
(
	letter_id integer references letter on delete cascade on update cascade,
	mp_id integer references mp on delete cascade on update cascade,
	parliament_code varchar references parliament on delete set null on update cascade,
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
	neighborhood varchar default '*',
	route varchar default '*',
	street_number varchar default '*',
	primary key (constituency_id, country, administrative_area_level_1, administrative_area_level_2, administrative_area_level_3, locality, sublocality, neighborhood, route, street_number)
);

-- privileges on objects
grant select
	on table letter, letter_to_mp, answer, area
	to kv_user, kv_editor, kv_admin;
grant insert, update, delete, truncate
	on table letter, letter_to_mp, answer, area
	to kv_admin;
grant usage
	on sequence letter_id_seq
	to kv_admin;
