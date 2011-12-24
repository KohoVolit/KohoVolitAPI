-- KohoVolit.eu Generación Cuarta
-- tables of package NapišteJim

create table message
(
	id serial primary key,
	subject varchar not null,
	"body" text not null,
	sender_name varchar not null,
	sender_address varchar,
	sender_email varchar not null,
	is_public varchar not null check (is_public in ('yes', 'no')),
	"state" varchar not null default 'created' check ("state" in ('created', 'waiting for approval', 'refused', 'sent', 'blocked')),
	written_on timestamp not null default current_timestamp,
	sent_on timestamp,
	confirmation_code varchar not null unique,
	approval_code varchar,
	last_reply_on timestamp,
	text_data tsvector,
	sender_data tsvector,
	remote_addr inet,
	typing_duration real,
	mp_parameter varchar
);

create table message_to_mp
(
	message_id integer references message on delete cascade on update cascade,
	mp_id integer references mp on delete cascade on update cascade,
	parliament_code varchar references parliament on delete restrict on update cascade,
	reply_code varchar not null unique,
	survey_code varchar,
	private_reply_received varchar check (private_reply_received in ('yes', 'no', 'unknown')),
	primary key (message_id, mp_id, parliament_code)
);

create table reply
(
	reply_code varchar references message_to_mp(reply_code) on delete restrict on update cascade,
	subject varchar,
	"body" text,
	full_email_data text,
	received_on timestamp not null default current_timestamp,
	primary key (reply_code, received_on)
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
create index message_last_reply_on on message(last_reply_on);
create index message_text_data on message using gin(text_data);
create index message_sender_data on message using gin(sender_data);
create index message_to_mp_mp_id on message_to_mp(mp_id);
create index message_to_mp_reply_code on message_to_mp(reply_code);

-- privileges on objects
grant select
	on table message, message_to_mp, reply, area
	to kv_user, kv_editor, kv_admin;
grant insert, update, delete, truncate
	on table message, message_to_mp, reply, area
	to kv_admin;
grant usage
	on sequence message_id_seq
	to kv_admin;
