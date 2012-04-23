CREATE OR REPLACE FUNCTION division_from_source(
parliament_code character varying, source_code_value character varying
)
  RETURNS SETOF division AS
$BODY$
	SELECT d.* FROM division as d
	LEFT JOIN division_attribute as da
	ON d.id = da.division_id 
	WHERE da.name = 'source_code' AND da.value = $2
	AND d.parliament_code = $1
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION division_from_source(character varying, character varying) OWNER TO kohovolit;
