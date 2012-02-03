-- KohoVolit.eu Generación Cuarta
-- functions of package BASE

-- removes all null values from an array
create or replace function remove_nulls(anyarray)
returns anyarray as $$
	select array(select x from unnest($1) g(x) where x is not null)
$$ language sql immutable strict;

-- returns median value from a sorted array
create or replace function median_from_sorted(anyarray)
returns double precision as $$
	select ($1[array_upper($1,1)/2+1]::double precision + $1[(array_upper($1,1)+1) / 2]::double precision) / 2.0;
$$ language sql immutable strict;
