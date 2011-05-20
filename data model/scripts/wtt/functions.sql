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

-- returns all MPs that are representatives for the given address
-- MPs are sorted by parliament_code, political group and distance of their office to the given address
create or replace function address_representatives(
	latitude double precision = null,
	longitude double precision = null,
	country varchar = null,
	administrative_area_level_1 varchar = null,
	administrative_area_level_2 varchar = null,
	administrative_area_level_3 varchar = null,
	locality varchar = null,
	sublocality varchar = null,
	neighborhood varchar = null,
	route varchar = null,
	street_number varchar = null)
returns table(parliament_name varchar, parliament_code varchar, constituency_name varchar, id integer, first_name varchar, last_name varchar, disambiguation varchar,
	political_group varchar, office_town varchar, office_distance double precision)
as $$
	select
		p.name_,
		p.code,
		c.name_,
		mp.id, mp.first_name, mp.last_name, mp.disambiguation,
		club.short_name,
		split_part(o.address, '|', 4),
		acos(sin(radians(o.latitude)) * sin(radians($1)) + cos(radians(o.latitude)) * cos(radians($1)) * cos(radians($2 - o.longitude))) * 6371 as distance
	from
		area_match($3, $4, $5, $6, $7, $8, $9, $10, $11) as a
		join constituency as c on c.id = a.constituency_id
		join parliament as p on p.code = c.parliament_code
		join mp_in_group as mig_parl on mig_parl.constituency_id = c.id and mig_parl.role_code = 'member' and mig_parl.since <= 'now' and mig_parl.until > 'now'
		join group_ as g on g.id = mig_parl.group_id and g.group_kind_code = 'parliament'
		join term as t on t.id = g.term_id and t.since <= 'now' and t.until > 'now'
		join mp on mp.id = mig_parl.mp_id
		join mp_in_group as mig_club on mig_club.mp_id = mp.id and mig_club.role_code = 'member' and mig_club.since <= 'now' and mig_club.until > 'now'
		join group_ as club on club.id = mig_club.group_id and club.group_kind_code = 'political group' and club.term_id = t.id
		left join (select distinct on (mp_id, parliament_code) mp_id, parliament_code, address, latitude, longitude from office
			where since <= 'now' and until > 'now' order by mp_id, parliament_code, relevance desc) as o
			on o.mp_id = mp.id and o.parliament_code = c.parliament_code
	order by p.name_, c.name_, club.short_name, distance
$$ language sql stable;
