-- KohoVolit.eu Generación Cuarta
-- group attributes insertions

create or replace function insert_political_group_attribute(parliament_code varchar, group_short_name varchar, attr_name varchar, attr_value varchar)
returns integer as $$
insert into group_attribute(group_id, "name", "value", parl) values (
	(select g.id from "group" as g join term as t on t.id = g.term_id and t.since <= 'now' and t.until > 'now' where g.group_kind_code = 'political group' and g.parliament_code = $1 and g.short_name = $2),
	$3, $4, $1)
$$ language sql;

-- parliament cz/psp
select insert_political_group_attribute('cz/psp', 'ČSSD', 'logo', 'cssd.gif');
select insert_political_group_attribute('cz/psp', 'KSČM', 'logo', 'kscm.gif');
select insert_political_group_attribute('cz/psp', 'Nezařazení', 'logo', 'nezarazeni.gif');
select insert_political_group_attribute('cz/psp', 'ODS', 'logo', 'ods.gif');
select insert_political_group_attribute('cz/psp', 'TOP09-S', 'logo', 'top09.gif');
select insert_political_group_attribute('cz/psp', 'VV', 'logo', 'vv.gif');

-- parliament cz/senat
select insert_political_group_attribute('cz/senat', 'ČSSD', 'logo', 'cssd.gif');
select insert_political_group_attribute('cz/senat', 'KDU-ČSL', 'logo', 'kdu-csl.gif');
select insert_political_group_attribute('cz/senat', 'Nezařazení', 'logo', 'nezarazeni.gif');
select insert_political_group_attribute('cz/senat', 'ODS', 'logo', 'ods.gif');
select insert_political_group_attribute('cz/senat', 'TOP09-S', 'logo', 'top09.gif');


drop function insert_political_group_attribute(parliament_code varchar, group_short_name varchar, attr_name varchar, attr_value varchar);
