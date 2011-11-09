-- KohoVolit.eu Generación Cuarta
-- functions of package WTT

-- returns all areas matching the given address
create or replace function area_match(
	country varchar = null,
	administrative_area_level_1 varchar = null,
	administrative_area_level_2 varchar = null,
	administrative_area_level_3 varchar = null,
	locality varchar = null,
	sublocality varchar = null,
	neighborhood varchar = null,
	route varchar = null,
	street_number varchar = null)
returns setof area
as $$
	select * from area where
		($1 is null or country in ($1, '*'))
		and ($2 is null or administrative_area_level_1 in ($2, '*'))
		and ($3 is null or administrative_area_level_2 in ($3, '*'))
		and ($4 is null or administrative_area_level_3 in ('*', $4))
		and ($5 is null or locality in ($5, '*'))
		and ($6 is null or sublocality in ('*', $6))
		and ($7 is null or neighborhood in ('*', $7) or substr(neighborhood, 1, 1) = '~' and $7 != all (string_to_array(substr(neighborhood, 2), ',')))
		and ($8 is null or route in ('*', $8))
		and ($9 is null or street_number in ('*', $9))
$$ language sql stable;

-- returns details of the given parliaments or of all parliaments if they are not specified
create or replace function parliament_details(
	parliament_code varchar[] = null,
	lang varchar = null)
returns table(code varchar, "name" varchar, short_name varchar, description varchar, time_zone varchar, wtt_repinfo_function varchar, kind varchar, competence varchar, weight real)
as $$
	select
		p.code,
		coalesce(pa_n."value", p."name"),
		coalesce(pa_sn."value", p.short_name),
		coalesce(pa_d."value", p.description),
		p.time_zone,
		pa_wrf."value",
		pk.code,
		pka_c."value",
		pk.weight
	from
		parliament as p
		join parliament_kind as pk on pk.code = p.parliament_kind_code
		left join parliament_attribute as pa_n on pa_n.parliament_code = p.code and pa_n."name" = 'name' and pa_n.lang = $2 and pa_n.since <= 'now' and pa_n.until > 'now'
		left join parliament_attribute as pa_sn on pa_sn.parliament_code = p.code and pa_sn."name" = 'short_name' and pa_sn.lang = $2 and pa_sn.since <= 'now' and pa_sn.until > 'now'
		left join parliament_attribute as pa_d on pa_d.parliament_code = p.code and pa_d."name" = 'description' and pa_d.lang = $2 and pa_d.since <= 'now' and pa_d.until > 'now'
		left join parliament_attribute as pa_wrf on pa_wrf.parliament_code = p.code and pa_wrf."name" = 'wtt_repinfo_function'
		left join parliament_kind_attribute as pka_c on pka_c.parliament_kind_code = pk.code and pka_c."name" = 'competence' and pka_c.lang = $2 and pka_c.cntry = p.country_code and pka_c.since <= 'now' and pka_c.until > 'now'
	where
		$1 is null or p.code = any ($1)
	order by pk.weight, p.code
$$ language sql stable;

-- returns id-s of all MPs that are representatives for the given address and parliament(s); returns also parliament and constituency details translated to given language
-- records are ordered by parliament weight and constituency name
create or replace function address_representative(
	parliament varchar[] = null,
	lang varchar = null,
	country varchar = null,
	administrative_area_level_1 varchar = null,
	administrative_area_level_2 varchar = null,
	administrative_area_level_3 varchar = null,
	locality varchar = null,
	sublocality varchar = null,
	neighborhood varchar = null,
	route varchar = null,
	street_number varchar = null)
returns table(
	mp_id integer,
	parliament_code varchar,
	constituency_name varchar, constituency_short_name varchar, constituency_description varchar)
as $$
	select
		mig.mp_id,
		p.code,
		coalesce(ca_n."value", c."name") as constituency_name,
		coalesce(ca_sn."value", c.short_name),
		coalesce(ca_d."value", c.description)
	from
		(select distinct constituency_id from area_match($3, $4, $5, $6, $7, $8, $9, $10, $11)) as a
		join constituency as c on c.id = a.constituency_id and ($1 is null or c.parliament_code = any ($1))
		join parliament as p on p.code = c.parliament_code
		join parliament_kind as pk on pk.code = p.parliament_kind_code
		join mp_in_group as mig on mig.constituency_id = c.id and mig.role_code = 'member' and mig.since <= 'now' and mig.until > 'now'
		join "group" as g on g.id = mig.group_id and g.group_kind_code = 'parliament'
		join term as t on t.id = g.term_id and t.since <= 'now' and t.until > 'now'
		left join constituency_attribute as ca_n on ca_n.constituency_id = c.id and ca_n."name" = 'name' and ca_n.lang = $2 and ca_n.since <= 'now' and ca_n.until > 'now'
		left join constituency_attribute as ca_sn on ca_sn.constituency_id = c.id and ca_sn."name" = 'short_name' and ca_sn.lang = $2 and ca_sn.since <= 'now' and ca_sn.until > 'now'
		left join constituency_attribute as ca_d on ca_d.constituency_id = c.id and ca_d."name" = 'description' and ca_d.lang = $2 and ca_d.since <= 'now' and ca_d.until > 'now'
	order by pk.weight, p."name", constituency_name
$$ language sql stable;

-- returns information (with political group) about given MPs as representatives of a given parliament
-- records are ordered by political group name and distance of the office
create or replace function wtt_repinfo_politgroup(
	mp_id integer[],
	parliament_code varchar,
	lang varchar = null,
	latitude double precision = null,
	longitude double precision = null)
returns table(
	id integer, first_name varchar, middle_names varchar, last_name varchar, disambiguation varchar,
	email varchar, image varchar, additional_info varchar,
	political_group_name varchar, political_group_short_name varchar, political_group_logo varchar)
as $$
	select
		mp.id, mp.first_name, mp.middle_names, mp.last_name, mp.disambiguation,
		ma_e."value",
		$2 || '/images/mp/' || ma_i."value",
		cast (null as varchar),
		coalesce(ga_n."value", g."name") as political_group_name,
		coalesce(ga_sn."value", g.short_name),
		$2 || '/images/group/' || ga_i."value"
	from
		mp
		left join mp_attribute as ma_e on ma_e.mp_id = mp.id and ma_e."name" = 'email' and ma_e.parl = $2 and ma_e.since <= 'now' and ma_e.until > 'now'
		left join mp_attribute as ma_i on ma_i.mp_id = mp.id and ma_i."name" = 'image' and ma_i.parl = $2 and ma_i.since <= 'now' and ma_i.until > 'now'
		left join mp_in_group as mig on mig.mp_id = mp.id and mig.role_code = 'member' and mig.since <= 'now' and mig.until > 'now' and mig.group_id in (select id from "group" where group_kind_code = 'political group' and parliament_code = $2)
		left join "group" as g on g.id = mig.group_id
		left join group_attribute as ga_n on ga_n.group_id = g.id and ga_n."name" = 'name' and ga_n.lang = $3 and ga_n.since <= 'now' and ga_n.until > 'now'
		left join group_attribute as ga_sn on ga_sn.group_id = g.id and ga_sn."name" = 'short_name' and ga_sn.lang = $3 and ga_sn.since <= 'now' and ga_sn.until > 'now'
		left join group_attribute as ga_i on ga_i.group_id = g.id and ga_i."name" = 'logo' and ga_i.since <= 'now' and ga_i.until > 'now'
	where
		 mp.id = any ($1)
	order by
		political_group_name
$$ language sql stable;

-- returns information (with political group and office) about given MPs as representatives of a given parliament
-- records are ordered by political group name and distance of the office
create or replace function wtt_repinfo_politgroup_office(
	mp_id integer[],
	parliament_code varchar,
	lang varchar = null,
	latitude double precision = null,
	longitude double precision = null)
returns table(
	id integer, first_name varchar, middle_names varchar, last_name varchar, disambiguation varchar,
	email varchar, image varchar, additional_info varchar,
	political_group_name varchar, political_group_short_name varchar, political_group_logo varchar)
as $$
	select
		mp.id, mp.first_name, mp.middle_names, mp.last_name, mp.disambiguation,
		ma_e."value",
		$2 || '/images/mp/' || ma_i."value",
		split_part(o.address, '|', 4) || ', ' || round(acos(sin(radians(o.latitude)) * sin(radians($4)) + cos(radians(o.latitude)) * cos(radians($4)) * cos(radians($5 - o.longitude))) * 6371) || ' km',
		coalesce(ga_n."value", g."name") as political_group_name,
		coalesce(ga_sn."value", g.short_name),
		$2 || '/images/group/' || ga_i."value"
	from
		mp
		left join mp_attribute as ma_e on ma_e.mp_id = mp.id and ma_e."name" = 'email' and ma_e.parl = $2 and ma_e.since <= 'now' and ma_e.until > 'now'
		left join mp_attribute as ma_i on ma_i.mp_id = mp.id and ma_i."name" = 'image' and ma_i.parl = $2 and ma_i.since <= 'now' and ma_i.until > 'now'
		left join mp_in_group as mig on mig.mp_id = mp.id and mig.role_code = 'member' and mig.since <= 'now' and mig.until > 'now' and mig.group_id in (select id from "group" where group_kind_code = 'political group' and parliament_code = $2)
		left join "group" as g on g.id = mig.group_id
		left join group_attribute as ga_n on ga_n.group_id = g.id and ga_n."name" = 'name' and ga_n.lang = $3 and ga_n.since <= 'now' and ga_n.until > 'now'
		left join group_attribute as ga_sn on ga_sn.group_id = g.id and ga_sn."name" = 'short_name' and ga_sn.lang = $3 and ga_sn.since <= 'now' and ga_sn.until > 'now'
		left join group_attribute as ga_i on ga_i.group_id = g.id and ga_i."name" = 'logo' and ga_i.since <= 'now' and ga_i.until > 'now'
		left join (select distinct on (mp_id, parliament_code) mp_id, parliament_code, address, latitude, longitude from office
			where since <= 'now' and until > 'now' order by mp_id, parliament_code, relevance desc) as o
			on o.mp_id = mp.id and o.parliament_code = $2
	where
		 mp.id = any ($1)
	order by
		political_group_name,
		acos(sin(radians(o.latitude)) * sin(radians($4)) + cos(radians(o.latitude)) * cos(radians($4)) * cos(radians($5 - o.longitude))) * 6371
$$ language sql stable;

-- returns information (with political group and location) about given MPs as representatives of a given parliament
-- records are ordered by political group name and distance of the location
-- only MPs from the nearest 3 locations are returned for each political group
create or replace function wtt_repinfo_politgroup_location(
	mp_id integer[],
	parliament_code varchar,
	lang varchar = null,
	latitude double precision = null,
	longitude double precision = null)
returns table(
	id integer, first_name varchar, middle_names varchar, last_name varchar, disambiguation varchar,
	email varchar, image varchar, additional_info varchar,
	political_group_name varchar, political_group_short_name varchar, political_group_logo varchar)
as $$
	select
		id, first_name, middle_names, last_name, disambiguation, email, image, additional_info, political_group_name, political_group_short_name, political_group_logo
	from
	(
		select
			mp.id, mp.first_name, mp.middle_names, mp.last_name, mp.disambiguation,
			ma_e."value" as email,
			$2 || '/images/mp/' || ma_i."value" as image,
			ma_loc."value" || ', ' || round(acos(sin(radians(cast(ma_lat."value" as double precision))) * sin(radians($4)) + cos(radians(cast(ma_lat."value" as double precision))) * cos(radians($4)) * cos(radians($5 - cast(ma_lng."value" as double precision)))) * 6371) || ' km' as additional_info,
			coalesce(ga_n."value", g."name") as political_group_name,
			coalesce(ga_sn."value", g.short_name) as political_group_short_name,
			$2 || '/images/group/' || ga_i."value" as political_group_logo,
			rank() over (partition by g.id order by acos(sin(radians(cast(ma_lat."value" as double precision))) * sin(radians($4)) + cos(radians(cast(ma_lat."value" as double precision))) * cos(radians($4)) * cos(radians($5 - cast(ma_lng."value" as double precision)))) * 6371) as rnk
		from
			mp
			left join mp_attribute as ma_e on ma_e.mp_id = mp.id and ma_e."name" = 'email' and ma_e.parl = $2 and ma_e.since <= 'now' and ma_e.until > 'now'
			left join mp_attribute as ma_i on ma_i.mp_id = mp.id and ma_i."name" = 'image' and ma_i.parl = $2 and ma_i.since <= 'now' and ma_i.until > 'now'
			left join mp_in_group as mig on mig.mp_id = mp.id and mig.role_code = 'member' and mig.since <= 'now' and mig.until > 'now' and mig.group_id in (select id from "group" where group_kind_code = 'political group' and parliament_code = $2)
			left join "group" as g on g.id = mig.group_id
			left join group_attribute as ga_n on ga_n.group_id = g.id and ga_n."name" = 'name' and ga_n.lang = $3 and ga_n.since <= 'now' and ga_n.until > 'now'
			left join group_attribute as ga_sn on ga_sn.group_id = g.id and ga_sn."name" = 'short_name' and ga_sn.lang = $3 and ga_sn.since <= 'now' and ga_sn.until > 'now'
			left join group_attribute as ga_i on ga_i.group_id = g.id and ga_i."name" = 'logo' and ga_i.since <= 'now' and ga_i.until > 'now'
			left join mp_attribute as ma_loc on ma_loc.mp_id = mp.id and ma_loc."name" = 'location' and ma_loc.parl = $2 and ma_loc.since <= 'now' and ma_loc.until > 'now'
			left join mp_attribute as ma_lat on ma_lat.mp_id = mp.id and ma_lat."name" = 'latitude' and ma_lat.parl = $2 and ma_lat.since <= 'now' and ma_lat.until > 'now'
			left join mp_attribute as ma_lng on ma_lng.mp_id = mp.id and ma_lng."name" = 'longitude' and ma_lng.parl = $2 and ma_lng.since <= 'now' and ma_lng.until > 'now'
		where
			 mp.id = any ($1)
		order by
			political_group_name,
			rnk
	) as all_mps
	where rnk <= 3
$$ language sql stable;

-- returns groups of a given parlament (all or only direct subgroups of a given group kind)
-- records are ordered by group kind weight
create or replace function parliament_group(
	parliament_code varchar,
	lang varchar = null,
	subkind_of varchar = null)
returns table(
	id integer, "name" varchar, short_name varchar,
	group_kind_code varchar, group_kind_name varchar, group_kind_short_name varchar, group_kind_description varchar)
as $$
select
	g.id,
	coalesce(ga_n."value", g."name") as group_name,
	coalesce(ga_sn."value", g.short_name),
	gk.code,
	coalesce(gka_n."value", gk."name"),
	coalesce(gka_sn."value", gk.short_name),
	coalesce(gka_d."value", gk.description)
from
	parliament as p
	join "group" as g on g.parliament_code = p.code and g.group_kind_code != 'parliament'
	join term as t on t.id = g.term_id and t.parliament_kind_code = p.parliament_kind_code and since <= 'now' and until > 'now'
	join group_kind as gk on gk.code = g.group_kind_code and ($3 is null or gk.subkind_of = $3)
	left join group_attribute as ga_n on ga_n.group_id = g.id and ga_n."name" = 'name' and ga_n.lang = $2 and ga_n.since <= 'now' and ga_n.until > 'now'
	left join group_attribute as ga_sn on ga_sn.group_id = g.id and ga_sn."name" = 'short_name' and ga_sn.lang = $2 and ga_sn.since <= 'now' and ga_sn.until > 'now'
	left join group_kind_attribute as gka_n on gka_n.group_kind_code = gk.code and gka_n."name" = 'name' and gka_n.lang = $2 and gka_n.since <= 'now' and gka_n.until > 'now'
	left join group_kind_attribute as gka_sn on gka_sn.group_kind_code = gk.code and gka_sn."name" = 'short_name' and gka_sn.lang = $2 and gka_sn.since <= 'now' and gka_sn.until > 'now'
	left join group_kind_attribute as gka_d on gka_d.group_kind_code = gk.code and gka_d."name" = 'description' and gka_d.lang = $2 and gka_d.since <= 'now' and gka_d.until > 'now'
where
	p.code = $1
order by
	gk.weight, gk.code, group_name
$$ language sql stable;
