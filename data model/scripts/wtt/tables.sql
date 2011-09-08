-- KohoVolit.eu Generación Cuarta
-- tables of package WTT

create table message
(
	id serial primary key,
	subject varchar not null,
	body_ text not null,
	sender_name varchar not null,
	sender_address varchar,
	sender_email varchar not null,
	is_public varchar not null check (is_public in ('yes', 'no')),
	state_ varchar not null default 'created' check (state_ in ('created', 'waiting for approval', 'refused', 'sent')),
	written_on timestamp not null default current_timestamp,
	sent_on timestamp,
	confirmation_code varchar not null unique,
	approval_code varchar
--	unique (subject, body_, sender_email)
);

create table response
(
	message_id integer references message on delete cascade on update cascade,
	mp_id integer references mp on delete cascade on update cascade,
	parliament_code varchar references parliament on delete set null on update cascade,
	subject varchar,
	body_ text,
	full_email_data text,
	received_on timestamp,
	received_privately varchar check (received_privately in ('yes', 'no')),
	reply_code varchar not null unique,
	survey_code varchar,	
	primary key (message_id, mp_id, parliament_code)
);

create table area
(
	constituency_id integer references constituency on delete cascade on update cascade,
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

-- indexes (except PRIMARY KEY and UNIQUE constraints, for which the indexes have been created automatically)
create index message_sender_email on message(sender_email);
create index message_sent_on on message(sent_on);
create index response_message_id on response(message_id);
create index response_mp_id on response(mp_id);

-- privileges on objects
grant select
	on table message, response, area
	to kv_user, kv_editor, kv_admin;
grant insert, update, delete, truncate
	on table message, response, area
	to kv_admin;
grant usage
	on sequence message_id_seq
	to kv_admin;
