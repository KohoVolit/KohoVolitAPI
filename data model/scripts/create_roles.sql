-- KohoVolit.eu Generación Cuarta
-- roles (users) creation

-- must be executed by a superuser (postgres)

create user kv_superadmin superuser noinherit nocreatedb nocreaterole password 'kv_superadmin';
create user kv_admin noinherit password 'kv_admin';
create user kv_user noinherit password 'kv_user';
create user kv_editor noinherit password 'kv_editor';
create role kohovolit noinherit;

UPDATE pg_authid SET rolcatupdate=false WHERE rolname='kv_superadmin';

grant kv_superadmin, kv_admin, kv_user, kv_editor to giro, michal, tibor;
grant kohovolit to kv_admin, kv_superadmin;

revoke usage on language plpgsql from public;
grant usage on language plpgsql to kohovolit, kv_superadmin, kv_admin;
