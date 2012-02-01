-- KohoVolit.eu Generación Cuarta
-- tables of package DIVISION

create table vote_kind
(
	code varchar primary key,
	"name" varchar not null,
	description text
);

create table division_kind
(
	code varchar primary key,
	"name" varchar not null,
	description text
);

create table division
(
	id serial primary key,
	"name" varchar not null,
	division_kind_code varchar not null references division_kind on delete restrict on update cascade,
	divided_on timestamp with time zone not null,	
	parliament_code varchar not null references parliament on delete cascade on update cascade
);

create table mp_vote
(
	mp_id integer references mp on delete cascade on update cascade,
	division_id integer references division on delete cascade on update cascade,
	vote_kind_code varchar not null references vote_kind on delete restrict on update cascade,
	primary key (mp_id, division_id)
);

-- attributes
create table vote_kind_attribute
(
	vote_kind_code varchar references vote_kind on delete cascade on update cascade,
	primary key (vote_kind_code, "name", lang, since),
	foreign key (lang) references "language" on delete restrict on update cascade
) inherits ("attribute");

create table division_kind_attribute
(
	division_kind_code varchar references division_kind on delete cascade on update cascade,
	primary key (division_kind_code, "name", lang, since),
	foreign key (lang) references "language" on delete restrict on update cascade
) inherits ("attribute");

create table division_attribute
(
	division_id integer references division on delete cascade on update cascade,
	primary key (division_id, "name", lang, since),
	foreign key (lang) references "language" on delete restrict on update cascade
) inherits ("attribute");

-- indexes (except PRIMARY KEY and UNIQUE constraints, for which the indexes have been created automatically)
create index mp_vote_division_id on mp_vote(division_id);

-- privileges on objects
grant select
	on table vote_kind, division_kind, division, mp_vote, vote_kind_attribute, division_kind_attribute, division_attribute
	to kv_user, kv_editor, kv_admin;
grant insert, update, delete, truncate
	on table vote_kind, division_kind, division, mp_vote, vote_kind_attribute, division_kind_attribute, division_attribute
	to kv_admin;
grant usage
	on sequence division_id_seq
	to kv_admin;
