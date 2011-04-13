-- KohoVolit.eu Generación Cuarta
-- triggers of package WTT

create or replace function letter_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from letter_attribute where (letter_id, name_, lang) = (new.letter_id, new.name_, new.lang) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from letter_attribute where (letter_id, name_, lang) = (new.letter_id, new.name_, new.lang) and until > new.since and since < new.until
			and (letter_id, name_, lang, since) != (old.letter_id, old.name_, old.lang, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (letter_id=%, name_=''%'', value_=''%'', lang=''%'', since=''%'', until=''%'') being inserted (or updated) into LETTER_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.letter_id, new.name_, new.value_, new.lang, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger letter_attribute_temporal_check
	before insert or update /* of since, until */ on letter_attribute
	for each row execute procedure letter_attribute_temporal_check();
