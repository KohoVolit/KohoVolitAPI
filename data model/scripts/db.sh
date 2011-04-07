#!/bin/sh

# Lin/Win
PSQL="/usr/bin/psql"
# PSQL="c:/Program Files/PostgreSQL/9.0/bin/psql"

# "$PSQL" -U giro -d postgres -f create_roles.sql
"$PSQL" -U giro -d postgres -f create_db.sql
"$PSQL" -U giro -d kohovolit -f build_db.sql
