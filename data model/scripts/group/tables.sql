-- KohoVolit.eu Generación Quarta
-- tables of package GROUP

create table group_kind
(
	code varchar primary key,
	name_ varchar not null,
	short_name varchar,
	description text,
	subkind_of varchar references group_kind on delete cascade on update cascade
);

create table group_
(
	id serial primary key,
	name_ varchar not null,
	short_name varchar,
	group_kind_code varchar not null references group_kind on delete restrict on update cascade,
	term_id integer not null references term on delete restrict on update cascade,
	parliament_code varchar not null references parliament on delete restrict on update cascade,
	subgroup_of integer references group_ on delete cascade on update cascade,
	unique (name_, group_kind_code, term_id, parliament_code)
);

create table role_
(
	code varchar primary key,
	male_name varchar not null,
	female_name varchar not null,
	description text
);

create table party
(
	id serial primary key,
	name_ varchar not null,
	short_name varchar,
	description text,
	country_code varchar references country on delete restrict on update cascade,
	unique (name_, country_code)
);

create table mp_in_group
(
	mp_id integer references mp on delete cascade on update cascade,
	group_id integer references group_ on delete cascade on update cascade,
	role_code varchar references role_ on delete restrict on update cascade,
	party_id integer references party on delete restrict on update cascade,
	constituency_id integer references constituency on delete restrict on update cascade,
	since timestamp default '-infinity',
	until timestamp default 'infinity',
	primary key (mp_id, group_id, role_code, since),
	check (since <= until)
);

-- attributes
create table group_kind_attribute
(
	group_kind_code varchar references group_kind on delete cascade on update cascade,
	parl varchar references parliament on delete restrict on update cascade default '-',
	primary key (group_kind_code, name_, lang, parl, since),
	foreign key (lang) references language_ on delete restrict on update cascade,
	foreign key (parl) references parliament on delete restrict on update cascade
) inherits (attribute_);

create table group_attribute
(
	group_id integer references group_ on delete cascade on update cascade,
	parl varchar references parliament on delete restrict on update cascade default '-',
	primary key (group_id, name_, lang, parl, since),
	foreign key (lang) references language_ on delete restrict on update cascade,
	foreign key (parl) references parliament on delete restrict on update cascade
) inherits (attribute_);

create table role_attribute
(
	role_code varchar references role_ on delete cascade on update cascade,
	parl varchar references parliament on delete restrict on update cascade default '-',
	primary key (role_code, name_, lang, parl, since),
	foreign key (lang) references language_ on delete restrict on update cascade,
	foreign key (parl) references parliament on delete restrict on update cascade
) inherits (attribute_);

create table party_attribute
(
	party_id integer references party on delete cascade on update cascade,
	parl varchar references parliament on delete restrict on update cascade default '-',
	primary key (party_id, name_, lang, parl, since),
	foreign key (lang) references language_ on delete restrict on update cascade,
	foreign key (parl) references parliament on delete restrict on update cascade
) inherits (attribute_);

-- indexes (except PRIMARY KEY and UNIQUE constraints, for which the indexes have been created automatically)
create index mp_in_group_group_id_fkey on mp_in_group(group_id);

-- privileges on objects
grant select
	on table group_kind, group_, role_, party, mp_in_group, group_kind_attribute, group_attribute, role_attribute, party_attribute
	to kv_user, kv_editor, kv_admin;
grant insert, update, delete, truncate
	on table group_kind, group_, role_, party, mp_in_group, group_kind_attribute, group_attribute, role_attribute, party_attribute
	to kv_admin;
grant usage
	on sequence group__id_seq, party_id_seq
	to kv_admin;
