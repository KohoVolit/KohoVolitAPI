-- KohoVolit.eu Generación Quarta
-- triggers of package PARLIAMENT

create or replace function parliament_kind_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from parliament_kind_attribute where (parliament_kind_code, name_, lang) = (new.parliament_kind_code, new.name_, new.lang) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from parliament_kind_attribute where (parliament_kind_code, name_, lang) = (new.parliament_kind_code, new.name_, new.lang) and until > new.since and since < new.until
			and (parliament_kind_code, name_, lang, since) != (old.parliament_kind_code, old.name_, old.lang, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (parliament_kind_code=%, name_=''%'', value_=''%'', lang=''%'', since=''%'', until=''%'') being inserted (or updated) into PARLIAMENT_KIND_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.parliament_kind_code, new.name_, new.value_, new.lang, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger parliament_kind_attribute_temporal_check
	before insert or update /* of since, until */ on parliament_kind_attribute
	for each row execute procedure parliament_kind_attribute_temporal_check();

create or replace function parliament_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from parliament_attribute where (parliament_code, name_, lang) = (new.parliament_code, new.name_, new.lang) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from parliament_attribute where (parliament_code, name_, lang) = (new.parliament_code, new.name_, new.lang) and until > new.since and since < new.until
			and (parliament_code, name_, lang, since) != (old.parliament_code, old.name_, old.lang, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (parliament_code=%, name_=''%'', value_=''%'', lang=''%'', since=''%'', until=''%'') being inserted (or updated) into PARLIAMENT_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.parliament_code, new.name_, new.value_, new.lang, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger parliament_attribute_temporal_check
	before insert or update /* of since, until */ on parliament_attribute
	for each row execute procedure parliament_attribute_temporal_check();
	
-- also table TERM has columns 'since' and 'until', but there is no temporal check because terms often overlaps each other by a few days in practice

create or replace function term_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from term_attribute where (term_id, name_, lang) = (new.term_id, new.name_, new.lang) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from term_attribute where (term_id, name_, lang) = (new.term_id, new.name_, new.lang) and until > new.since and since < new.until
			and (term_id, name_, lang, since) != (old.term_id, old.name_, old.lang, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (term_id=%, name_=''%'', value_=''%'', lang=''%'', since=''%'', until=''%'') being inserted (or updated) into TERM_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.term_id, new.name_, new.value_, new.lang, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger term_attribute_temporal_check
	before insert or update /* of since, until */ on term_attribute
	for each row execute procedure term_attribute_temporal_check();

create or replace function constituency_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from constituency_attribute where (constituency_id, name_, lang) = (new.constituency_id, new.name_, new.lang) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from constituency_attribute where (constituency_id, name_, lang) = (new.constituency_id, new.name_, new.lang) and until > new.since and since < new.until
			and (constituency_id, name_, lang, since) != (old.constituency_id, old.name_, old.lang, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (constituency_id=%, name_=''%'', value_=''%'', lang=''%'', since=''%'', until=''%'') being inserted (or updated) into CONSTITUENCY_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.constituency_id, new.name_, new.value_, new.lang, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger constituency_attribute_temporal_check
	before insert or update /* of since, until */ on constituency_attribute
	for each row execute procedure constituency_attribute_temporal_check();

create or replace function constituency_archive_value(a_constituency_id integer, a_column_name varchar, a_column_value varchar)
returns void as $$
declare
	l_since timestamp;
begin
	select until into l_since from constituency_attribute where constituency_id = a_constituency_id and name_ = a_column_name and lang = '-' order by until desc limit 1;
	if not found then l_since = '-infinity'; end if;
	insert into constituency_attribute(constituency_id, name_, value_, since, until) values (a_constituency_id, a_column_name, a_column_value, l_since, 'now');
end; $$ language plpgsql;

create or replace function constituency_changed_values_archivation()
returns trigger as $$
begin
	if new.name_ is distinct from old.name_ then perform constituency_archive_value(old.id, 'name_', old.name_); end if;
	if new.short_name is distinct from old.short_name then perform constituency_archive_value(old.id, 'short_name', old.short_name); end if;
	if new.description is distinct from old.description then perform constituency_archive_value(old.id, 'description', old.description); end if;
	return new;
end; $$ language plpgsql;

create trigger constituency_changed_values_archivation
	before update on constituency
	for each row execute procedure constituency_changed_values_archivation();
