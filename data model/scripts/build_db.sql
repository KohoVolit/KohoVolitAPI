-- KohoVolit.eu Generación Cuarta
-- database building

-- must be connected to the database kohovolit
set role to kohovolit;

comment on database kohovolit is 'Project KohoVolit.';

\i base/tables.sql
\i base/triggers.sql
\i base/inserts.sql
\i parliament/tables.sql
\i parliament/triggers.sql
\i parliament/inserts.sql
\i mp/tables.sql
\i mp/functions.sql
\i mp/triggers.sql
\i group/tables.sql
\i group/triggers.sql
\i group/inserts.sql
\i wtt/tables.sql
\i wtt/functions.sql
