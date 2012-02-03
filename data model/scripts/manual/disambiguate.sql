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


-- parliament cz/local
select set_mp_disambiguation('cz/psp', '268', 'PSP ČR 1996-1998');
select set_mp_disambiguation('cz/brno', 'cz_brno_2010-2014_37', 'MZ Brno 2010-');

select set_mp_disambiguation('cz/psp', '5951', 'PSP ČR 2010-');
select set_mp_disambiguation('cz/starostove', 'cz_starostove_2010-2014_72', 'Konice, starosta 2010-');

select set_mp_disambiguation('cz/brno', 'cz_brno_2010-2014_4', 'MZ Brno 2010-');
select set_mp_disambiguation('cz/velke-mezirici', 'cz_velke-mezirici_2010-2014_4', 'MZ Vel. Meziříčí 2010-');

select set_mp_disambiguation('cz/psp', '5021', 'PSP ČR 2006-2010');
select set_mp_disambiguation('cz/starostove', 'cz_starostove_2010-2014_204', 'Ústí n. Orlicí, starosta 2010-');

select set_mp_disambiguation('cz/jihomoravsky', 'cz_jihomoravsky_2008-2012_37', 'JM kraj 2008-');
select set_mp_disambiguation('cz/nepomuk', 'cz_nepomuk_2010-2014_4', 'Nepomuk 2010-');


-- parliament sk/starostovia
select set_mp_disambiguation('sk/starostovia', '514349-Stanislav-Bartoš', 'Radobica, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '525260-Stanislav-Bartoš', 'Široké, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '526762-Jozef-Dufala', 'Jakubany, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '510505-Jozef-Dufala', 'Jakubovany, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '524557-Marián-Dujava', 'Jakovany, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '524573-Marián-Dujava', 'Jakubovany, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '506842-Jozef-Gabriel', 'Brestovany, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '510050-Jozef-Gabriel', 'Sihelné, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '556165-Ľubomír-Goga', 'Chrabrany, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '505064-Ľubomír-Goga', 'Lužany, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '527645-Milan-Grega', 'N. Pisaná, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '543705-Milan-Grega', 'V. Folkmár, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '521701-Iveta-Horvatová', 'Mudrovce, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '527769-Iveta-Horvatová', 'Rakovčík, starosta 2010-');

select set_mp_disambiguation('sk/nrsr', '264', 'NRSR 2010-');
select set_mp_disambiguation('sk/starostovia', '504076-Zoltán-Horváth', 'Tomášikovo, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '507041-Ján-Hrčka', 'H. Krupá, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '507059-Ján-Hrčka', 'H. Dubové, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '524727-Milan-Hudák', 'Lada, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '521809-Milan-Hudák', 'N. Salaš, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '527971-Milan-Hudák', 'Varechovce, starosta 2010-');

select set_mp_disambiguation('cz/hodonin', 'cz_hodonin_2010-2014_11', 'MZ Hodonín 2010-');
select set_mp_disambiguation('sk/starostovia', '504394-Anton-Ivánek', 'Chropov, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '524514-Ján-Jakab', 'Chmeľovec, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '558214-Ján-Jakab', 'V. Straciny, starosta 2010-');

select set_mp_disambiguation('sk/nrsr', '663', 'NRSR 2010-');
select set_mp_disambiguation('sk/starostovia', '504297-Vladimír-Jánoš', 'Cerová, starosta 2010-');

select set_mp_disambiguation('sk/nrsr', '801', 'NRSR 2010-');
select set_mp_disambiguation('sk/starostovia', '513814-Jozef-Kollár', 'Záriečie, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '506231-Jozef-Kováč', 'Mn. Lehota, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '500607-Jozef-Kováč', 'Nevidzany, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '524531-Jozef-Lukáč', 'Ch. Jakubovany, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '522066-Jozef-Lukáč', 'Šemša, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '525847-Ján-Lukáč', 'Koceľovce, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '512672-Ján-Lukáč', 'Trnovo, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '519961-Ján-Lukáč', 'Zborov, starosta 2010-');

select set_mp_disambiguation('cz/psp', '268', 'PSP ČR 1996-1998');
select set_mp_disambiguation('cz/starostove', 'cz_starostove_2010-2014_72', 'Konice, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '525421-František-Novák', 'Vysoká, starosta 2010-');

select set_mp_disambiguation('sk/nrsr', '685', 'NRSR 2010-');
select set_mp_disambiguation('sk/starostovia', '509477-Ján-Podmanický', 'St. Bystrica, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '526029-Ján-Poliak', 'Mur. Zdychava, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '517976-Ján-Poliak', 'Stráža, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '544132-Štefan-Straka', 'Č. n. Topľou, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '544213-Štefan-Straka', 'Han. n. Topľou, starosta 2010-');

select set_mp_disambiguation('sk/nrsr', '835', 'NRSR 2010-');
select set_mp_disambiguation('sk/starostovia', '515426-Jaroslav-Suja', 'Rim. Baňa, starosta 2010-');

select set_mp_disambiguation('sk/nrsr', '306', 'NRSR 2010-');
select set_mp_disambiguation('sk/starostovia', '502448-Tibor-Tóth', 'Kubáňovo, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '555843-Ján-Varga', 'Čata, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '525332-Ján-Varga', 'Tuhrina, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '515892-Zoltán-Végh', 'Bušince, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '511897-Zoltán-Végh', 'Šurice, starosta 2010-');

select set_mp_disambiguation('cz/psp', '198', 'PSP ČR 1992-1998');
select set_mp_disambiguation('sk/starostovia', '580449-Anton-Zima', 'Brodzany, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '517763-Anton-Štefko', 'Lutiše, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '525383-Anton-Štefko', 'Varhaňovce, starosta 2010-');

select set_mp_disambiguation('sk/starostovia', '508870-Jozef-Kalman', 'Pohorelá, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '512001-Jozef-Líška', 'V. Ves, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '514462-Jozef-Šimko', 'R. Sobota, starosta 2010-');
select set_mp_disambiguation('sk/starostovia', '522732-Ján-Šimko', 'Ložín, starosta 2010-');

drop function set_mp_disambiguation(parliament_code varchar, mp_source_code varchar, mp_disambiguation varchar);
