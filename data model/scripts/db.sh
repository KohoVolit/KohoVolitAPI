#!/bin/sh

# Lin/Win
PSQL="/usr/bin/psql"
# PSQL="c:/Program Files/PostgreSQL/9.0/bin/psql"

#"$PSQL" -U postgres -d postgres -f create_roles.sql
"$PSQL" -U postgres -d postgres -f create_db.sql
"$PSQL" -U postgres -d kohovolit -f build_db.sql
