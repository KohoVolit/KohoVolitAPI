-- KohoVolit.eu Generaci�n Quarta
-- triggers of package MP
	
create or replace function mp_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from mp_attribute where (mp_id, name_, lang) = (new.mp_id, new.name_, new.lang) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from mp_attribute where (mp_id, name_, lang) = (new.mp_id, new.name_, new.lang) and until > new.since and since < new.until 
			and (mp_id, name_, lang, since, until) != (old.mp_id, old.name_, old.lang, old.since, old.until)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (mp_id=%, name_=''%'', value_=''%'', lang=''%'', since=''%'', until=''%'') beeing inserted (or updated) into MP_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.mp_id, new.name_, new.value_, new.lang, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger mp_attribute_temporal_check
	before insert or update /* of since, until */ on mp_attribute
	for each row execute procedure mp_attribute_temporal_check();

create or replace function office_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from office where (mp_id, address) = (new.mp_id, new.address) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from office where (mp_id, address) = (new.mp_id, new.address) and until > new.since and since < new.until 
			and (mp_id, address, since, until) != (old.mp_id, old.address, old.since, old.until)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (mp_id=%, addreess=''%'', since=''%'', until=''%'') beeing inserted (or updated) into OFFICE overlaps with another period of the same address.',
			new.mp_id, new.address, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger office_temporal_check
	before insert or update /* of since, until */ on office
	for each row execute procedure office_temporal_check();

create or replace function mp_save_value(a_mp_id integer, a_col_name varchar, a_col_value varchar)
returns void as $$
declare
	l_since timestamp;
begin
	select until into l_since from mp_attribute where mp_id = a_mp_id and name_ = a_col_name and lang = '-' order by until desc limit 1;
	if not found then l_since = '-infinity'; end if;
	insert into mp_attribute(mp_id, name_, value_, since, until) values (a_mp_id, a_col_name, a_col_value, l_since, 'now');
end; $$ language plpgsql;

create or replace function mp_save_changed_values()
returns trigger as $$
begin
	if new.first_name is distinct from old.first_name then perform mp_save_value(old.id, 'first_name', old.first_name); end if;
	if new.middle_names is distinct from old.middle_names then perform mp_save_value(old.id, 'middle_names', old.middle_names); end if;
	if new.last_name is distinct from old.last_name then perform mp_save_value(old.id, 'last_name', old.last_name); end if;
	if new.disambiguation is distinct from old.disambiguation then perform mp_save_value(old.id, 'disambiguation', old.disambiguation); end if;
	if new.sex is distinct from old.sex then perform mp_save_value(old.id, 'sex', old.sex); end if;
	if new.pre_title is distinct from old.pre_title then perform mp_save_value(old.id, 'pre_title', old.pre_title); end if;
	if new.post_title is distinct from old.post_title then perform mp_save_value(old.id, 'post_title', old.post_title); end if;
	if new.born_on is distinct from old.born_on then perform mp_save_value(old.id, 'born_on', cast(old.born_on as varchar)); end if;
	if new.died_on is distinct from old.died_on then perform mp_save_value(old.id, 'died_on', cast(old.died_on as varchar)); end if;
	if new.email is distinct from old.email then perform mp_save_value(old.id, 'email', old.email); end if;
	if new.webpage is distinct from old.webpage then perform mp_save_value(old.id, 'webpage', old.webpage); end if;
	if new.address is distinct from old.address then perform mp_save_value(old.id, 'address', old.address); end if;
	if new.phone is distinct from old.phone then perform mp_save_value(old.id, 'phone', old.phone); end if;
	return new;
end; $$ language plpgsql;

create trigger mp_save_changed_values
	before update on mp
	for each row execute procedure mp_save_changed_values();
