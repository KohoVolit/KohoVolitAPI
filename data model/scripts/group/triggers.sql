-- KohoVolit.eu Generación Cuarta
-- triggers of package GROUP

create or replace function group_kind_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from group_kind_attribute where (group_kind_code, "name", lang, parl) = (new.group_kind_code, new."name", new.lang, new.parl) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from group_kind_attribute where (group_kind_code, "name", lang, parl) = (new.group_kind_code, new."name", new.lang, new.parl) and until > new.since and since < new.until
			and (group_kind_code, "name", lang, parl, since) != (old.group_kind_code, old."name", old.lang, old.parl, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (group_kind_code=''%'', name=''%'', value=''%'', lang=''%'', parl=''%'', since=''%'', until=''%'') being inserted (or updated) into GROUP_KIND_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.group_kind_code, new."name", new."value", new.lang, new.parl, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger group_kind_attribute_temporal_check
	before insert or update on group_kind_attribute
	for each row execute procedure group_kind_attribute_temporal_check();

create or replace function group_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from group_attribute where (group_id, "name", lang, parl) = (new.group_id, new."name", new.lang, new.parl) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from group_attribute where (group_id, "name", lang, parl) = (new.group_id, new."name", new.lang, new.parl) and until > new.since and since < new.until
			and (group_id, "name", lang, parl, since) != (old.group_id, old."name", old.lang, old.parl, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (group_id=%, name=''%'', value=''%'', lang=''%'', parl=''%'', since=''%'', until=''%'') being inserted (or updated) into GROUP_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.group_id, new."name", new."value", new.lang, new.parl, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger group_attribute_temporal_check
	before insert or update on group_attribute
	for each row execute procedure group_attribute_temporal_check();

create or replace function role_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from role_attribute where (role_code, "name", lang, parl) = (new.role_code, new."name", new.lang, new.parl) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from role_attribute where (role_code, "name", lang, parl) = (new.role_code, new."name", new.lang, new.parl) and until > new.since and since < new.until
			and (role_code, "name", lang, parl, since) != (old.role_code, old."name", old.lang, old.parl, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (role_code=''%'', name=''%'', value=''%'', lang=''%'', parl=''%'', since=''%'', until=''%'') being inserted (or updated) into ROLE_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.role_code, new."name", new."value", new.lang, new.parl, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger role_attribute_temporal_check
	before insert or update on role_attribute
	for each row execute procedure role_attribute_temporal_check();

create or replace function party_attribute_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from party_attribute where (party_id, "name", lang, parl) = (new.party_id, new."name", new.lang, new.parl) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from party_attribute where (party_id, "name", lang, parl) = (new.party_id, new."name", new.lang, new.parl) and until > new.since and since < new.until
			and (party_id, "name", lang, parl, since) != (old.party_id, old."name", old.lang, old.parl, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (party_id=%, name=''%'', value=''%'', lang=''%'', parl=''%'', since=''%'', until=''%'') being inserted (or updated) into PARTY_ATTRIBUTE overlaps with a period of another value of the attribute.',
			new.party_id, new."name", new."value", new.lang, new.parl, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger party_attribute_temporal_check
	before insert or update on party_attribute
	for each row execute procedure party_attribute_temporal_check();

create or replace function mp_in_group_temporal_check()
returns trigger as $$
begin
	if tg_op = 'INSERT' then
		perform * from mp_in_group where (mp_id, group_id, role_code) = (new.mp_id, new.group_id, new.role_code) and until > new.since and since < new.until limit 1;
	else  -- tg_op = 'UPDATE'
		perform * from mp_in_group where (mp_id, group_id, role_code) = (new.mp_id, new.group_id, new.role_code) and until > new.since and since < new.until
			and (mp_id, group_id, role_code, since) != (old.mp_id, old.group_id, old.role_code, old.since)
			limit 1;
	end if;
	if found then
		raise exception 'Time period in the row (mp_id=%, group_id=%, role_code=''%'', party_id=%, constituency_id=%, since=''%'', until=''%'') being inserted (or updated) into MP_IN_GROUP overlaps with a period of another MP''s membership in the same group with the same role.',
			new.mp_id, new.group_id, new.role_code, new.party_id, new.constituency_id, new.since, new.until;
	end if;
	return new;
end; $$ language plpgsql;

create trigger mp_in_group_temporal_check
	before insert or update on mp_in_group
	for each row execute procedure mp_in_group_temporal_check();

create or replace function group_archive_value(a_group_id integer, a_column_name varchar, a_column_value varchar, a_update_date timestamp)
returns void as $$
declare
	l_since timestamp;
begin
	select until into l_since from group_attribute where group_id = a_group_id and "name" = a_column_name and lang = '-' and parl = '-' order by until desc limit 1;
	if not found then l_since = '-infinity'; end if;
	insert into group_attribute(group_id, "name", "value", since, until) values (a_group_id, a_column_name, a_column_value, l_since, a_update_date);
end; $$ language plpgsql;

create or replace function group_changed_values_archivation()
returns trigger as $$
begin
	if new.last_updated_on is null then new.last_updated_on = 'now'; end if;
	if new.last_updated_on < old.last_updated_on then return null; end if;
	if new."name" is distinct from old."name" then perform group_archive_value(old.id, 'name', old."name", new.last_updated_on); end if;
	if new.short_name is distinct from old.short_name then perform group_archive_value(old.id, 'short_name', old.short_name, new.last_updated_on); end if;
	return new;
end; $$ language plpgsql;

create trigger group_changed_values_archivation
	before update on "group"
	for each row execute procedure group_changed_values_archivation();
	
create or replace function party_archive_value(a_party_id integer, a_column_name varchar, a_column_value varchar, a_update_date timestamp)
returns void as $$
declare
	l_since timestamp;
begin
	select until into l_since from party_attribute where party_id = a_party_id and "name" = a_column_name and lang = '-' and parl = '-' order by until desc limit 1;
	if not found then l_since = '-infinity'; end if;
	insert into party_attribute(party_id, "name", "value", since, until) values (a_party_id, a_column_name, a_column_value, l_since, a_update_date);
end; $$ language plpgsql;

create or replace function party_changed_values_archivation()
returns trigger as $$
begin
	if new.last_updated_on is null then new.last_updated_on = 'now'; end if;
	if new.last_updated_on < old.last_updated_on then return null; end if;
	if new."name" is distinct from old."name" then perform party_archive_value(old.id, 'name', old."name", new.last_updated_on); end if;
	if new.short_name is distinct from old.short_name then perform party_archive_value(old.id, 'short_name', old.short_name, new.last_updated_on); end if;
	if new.description is distinct from old.description then perform party_archive_value(old.id, 'description', old.description, new.last_updated_on); end if;
	return new;
end; $$ language plpgsql;

create trigger party_changed_values_archivation
	before update on party
	for each row execute procedure party_changed_values_archivation();
