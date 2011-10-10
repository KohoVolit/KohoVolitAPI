-- KohoVolit.eu Generación Cuarta
-- MP disambiguations

create or replace function set_mp_disambiguation(parliament_code varchar, mp_source_code varchar, mp_disambiguation varchar)
returns void as $$
	update mp set disambiguation = $3 where id = (select mp_id from mp_attribute where "name" = 'source_code' and "value" = $2 and parl = $1);
$$ language sql;

-- parliament cz/psp
select set_mp_disambiguation('cz/psp', '117', 'PSP ČR 1992-1996, Východočeský kraj');
select set_mp_disambiguation('cz/psp', '118', 'PSP ČR 1992-1996, Severomoravský kraj');

select set_mp_disambiguation('cz/psp', '57', 'PSP ČR 1992-1996');
select set_mp_disambiguation('cz/psp', '375', 'PSP ČR 1998-2002,2006-2010');

select set_mp_disambiguation('cz/psp', '223', 'PSP ČR 1996-1998');
select set_mp_disambiguation('cz/psp', '387', 'ml., PSP ČR 1998-2006');
select set_mp_disambiguation('cz/psp', '388', 'st., PSP ČR 1998-2002');

select set_mp_disambiguation('cz/psp', '119', 'PSP ČR 1992-1996');
select set_mp_disambiguation('cz/psp', '5254', 'PSP ČR 2002-2006');

select set_mp_disambiguation('cz/psp', '288', 'PSP ČR 1996-1997');
select set_mp_disambiguation('cz/psp', '5455', 'PSP ČR 2006-2010');

select set_mp_disambiguation('cz/psp', '6', 'PSP ČR 1992-1996');
select set_mp_disambiguation('cz/psp', '348', 'PSP ČR 1998-2006');

select set_mp_disambiguation('cz/psp', '417', 'PSP ČR 1998-2006');
select set_mp_disambiguation('cz/psp', '5505', 'PSP ČR 2006-');

select set_mp_disambiguation('cz/psp', '5991', 'ml., PSP ČR 2010-, Karlovarský kraj');
select set_mp_disambiguation('cz/psp', '5992', 'st., PSP ČR 2010-, kraj Vysočina');

select set_mp_disambiguation('cz/psp', '4737', 'PSP ČR 2002-2003');
select set_mp_disambiguation('cz/psp', '5993', 'PSP ČR 2010-');

select set_mp_disambiguation('cz/psp', '294', 'PSP ČR 1996-1998');
select set_mp_disambiguation('cz/psp', '5964', 'PSP ČR 2010-');

select set_mp_disambiguation('cz/psp', '403', 'PSP ČR 1998-2006');
select set_mp_disambiguation('cz/psp', '5775', 'PSP ČR 2009-2010');

select set_mp_disambiguation('cz/psp', '5273', 'PSP ČR 2002-');

-- parliament cz/senat
select set_mp_disambiguation('cz/senat', '14', 'Senát ČR 2006-2012');


drop function set_mp_disambiguation(parliament_code varchar, mp_source_code varchar, mp_disambiguation varchar);
