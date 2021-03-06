-- KohoVolit.eu Generación Cuarta
-- triggers of package BASE

create or replace function language_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from language_attribute where (language_code, "name", lang) = (new.language_code, new."name", new.lang) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from language_attribute where (language_code, "name", lang) = (new.language_code, new."name", new.lang) and until > new.since and since < new.until
			and (language_code, "name", lang, since) != (old.language_code, old."name", old.lang, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (language_code=''%'', name=''%'', value=''%'', lang=''%'', since=''%'', until=''%'') being inserted (or updated) into LANGUAGE_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.language_code, new."name", new."value", new.lang, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger language_attribute_temporal_check
	before insert or update on language_attribute
	for each row execute procedure language_attribute_temporal_check();

create or replace function country_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from country_attribute where (country_code, "name", lang) = (new.country_code, new."name", new.lang) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from country_attribute where (country_code, "name", lang) = (new.country_code, new."name", new.lang) and until > new.since and since < new.until
			and (country_code, "name", lang, since) != (old.country_code, old."name", old.lang, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (country_code=''%'', name=''%'', value=''%'', lang=''%'', since=''%'', until=''%'') being inserted (or updated) into COUNTRY_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.country_code, new."name", new."value", new.lang, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger country_attribute_temporal_check
	before insert or update on country_attribute
	for each row execute procedure country_attribute_temporal_check();
