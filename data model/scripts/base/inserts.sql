-- KohoVolit.eu Generación Cuarta
-- inserts into tables of package BASE

insert into "language" (code, "name", short_name, description, locale) values
('-', 'any language', 'any', 'This language is referenced as a foreign key by language neutral attributes.', 'C'),
('en', 'in English', 'English', null, 'en_US.utf-8'),
('sk', 'po slovensky', 'slovenčina', null, 'sk_SK.utf-8'),
('cs', 'česky', 'čeština', null, 'cs_CZ.utf-8');


insert into country (code, "name", short_name, description) values
('-', 'any country', 'any', 'This country is referenced as a foreign key by attributes that are country independent.'),
('eu', 'European Union', 'EU', null),
('sk', 'Slovak republic', 'Slovakia', null),
('cz', 'Czech republic', 'Czechia', null);

insert into country_attribute (country_code, lang, "name", "value") values
('eu', 'sk', 'name', 'Európska únia'),
('eu', 'sk', 'short_name', 'EÚ'),
('sk', 'sk', 'name', 'Slovenská republika'),
('sk', 'sk', 'short_name', 'Slovensko'),
('cz', 'sk', 'name', 'Česká republika'),
('cz', 'sk', 'short_name', 'Česko');

insert into country_attribute (country_code, lang, "name", "value") values
('eu', 'cs', 'name', 'Evropská unie'),
('eu', 'cs', 'short_name', 'EU'),
('sk', 'cs', 'name', 'Slovenská republika'),
('sk', 'cs', 'short_name', 'Slovensko'),
('cz', 'cs', 'name', 'Česká republika'),
('cz', 'cs', 'short_name', 'Česko');
