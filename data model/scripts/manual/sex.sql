-- KohoVolit.eu Generación Cuarta
-- MPs' sex column correction

update mp set sex = 'f' where sex is null and (last_name like '%á' or first_name in ('Erzsébet', 'Klára', 'Ágnes', 'Edita', 'Anna', 'Taťána', 'Jana'));
update mp set sex = 'm' where sex is null and not (last_name like '%á' or first_name in ('Erzsébet', 'Klára', 'Ágnes', 'Edita', 'Anna', 'Taťána', 'Jana'));
