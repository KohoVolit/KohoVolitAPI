#!/bin/sh

/usr/bin/psql -U giro -d postgres -f create_roles.sql
/usr/bin/psql -U giro -d postgres -f create_db.sql
/usr/bin/psql -U giro -d kohovolit -f build_db.sql
