-- KohoVolit.eu Generación Cuarta
-- triggers of package MP
	
create or replace function mp_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from mp_attribute where (mp_id, "name", lang, parl) = (new.mp_id, new."name", new.lang, new.parl) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from mp_attribute where (mp_id, "name", lang, parl) = (new.mp_id, new."name", new.lang, new.parl) and until > new.since and since < new.until 
			and (mp_id, "name", lang, parl, since) != (old.mp_id, old."name", old.lang, old. parl, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (mp_id=%, name=''%'', value=''%'', lang=''%'', parl=''%'', since=''%'', until=''%'') being inserted (or updated) into MP_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.mp_id, new."name", new."value", new.lang, new.parl, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger mp_attribute_temporal_check
	before insert or update on mp_attribute
	for each row execute procedure mp_attribute_temporal_check();

create or replace function office_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from office where (mp_id, parliament_code, address) = (new.mp_id, new.parliament_code, new.address) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from office where (mp_id, parliament_code, address) = (new.mp_id, new.parliament_code, new.address) and until > new.since and since < new.until 
			and (mp_id, parliament_code, address, since) != (old.mp_id, old.parliament_code, old.address, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (mp_id=%, parliament_code=''%'', address=''%'', phone=''%'', since=''%'', until=''%'') being inserted (or updated) into OFFICE overlaps with another period of the same address.',
			new.mp_id, new.parliament_code, new.address, new.phone, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger office_temporal_check
	before insert or update on office
	for each row execute procedure office_temporal_check();

create or replace function mp_archive_value(a_mp_id integer, a_column_name varchar, a_column_value varchar, a_update_date timestamp)
returns void as $$
declare
	l_since timestamp with time zone;
begin
	select until into l_since from mp_attribute where mp_id = a_mp_id and "name" = a_column_name and lang = '-' and parl = '-' order by until desc limit 1;
	if not found then l_since = '-infinity'; end if;
	insert into mp_attribute(mp_id, "name", "value", since, until) values (a_mp_id, a_column_name, a_column_value, l_since, a_update_date);
end; $$ language plpgsql;

create or replace function mp_changed_values_archivation()
returns trigger as $$
begin
	if new.last_updated_on is null then new.last_updated_on = 'now'; end if;
	if new.last_updated_on < old.last_updated_on then return null; end if;
	if new.first_name is distinct from old.first_name then perform mp_archive_value(old.id, 'first_name', old.first_name, new.last_updated_on); end if;
	if new.middle_names is distinct from old.middle_names then perform mp_archive_value(old.id, 'middle_names', old.middle_names, new.last_updated_on); end if;
	if new.last_name is distinct from old.last_name then perform mp_archive_value(old.id, 'last_name', old.last_name, new.last_updated_on); end if;
	if new.disambiguation is distinct from old.disambiguation then perform mp_archive_value(old.id, 'disambiguation', old.disambiguation, new.last_updated_on); end if;
	if new.sex is distinct from old.sex then perform mp_archive_value(old.id, 'sex', old.sex, new.last_updated_on); end if;
	if new.pre_title is distinct from old.pre_title then perform mp_archive_value(old.id, 'pre_title', old.pre_title, new.last_updated_on); end if;
	if new.post_title is distinct from old.post_title then perform mp_archive_value(old.id, 'post_title', old.post_title, new.last_updated_on); end if;
	return new;
end; $$ language plpgsql;

create trigger mp_changed_values_archivation
	before update on mp
	for each row execute procedure mp_changed_values_archivation();
