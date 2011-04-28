-- KohoVolit.eu Generación Cuarta
-- functions of package MP

create function set_mp_disambiguation(parliament_code varchar, mp_source_code varchar, mp_disambiguation varchar)
returns void as $$
	update mp set disambiguation = $3 where id = (select mp_id from mp_attribute where name_ = 'source_code' and value_ = $2 and parl = $1);
$$ language sql;