-- KohoVolit.eu Generación Quarta
-- inserts into tables of package BASE

insert into language_ (code, name_, short_name, description, locale) values
('-', 'any language', 'any', 'This language is referenced as a foreign key by language neutral attributes.', 'C'),
('en', 'in English', 'English', null, 'en_US.UTF-8'),
('sk', 'po slovensky', 'slovenčina', null, 'sk_SK.UTF-8'),
('cs', 'česky', 'čeština', null, 'cs_CZ.UTF-8');


insert into country (code, name_, short_name, description) values
('eu', 'European Union', 'EU', null),
('sk', 'Slovak republic', 'Slovakia', null),
('cz', 'Czech republic', 'Czechia', null);

insert into country_attribute (country_code, lang, name_, value_) values
('eu', 'sk', 'name_', 'Európska únia'),
('eu', 'sk', 'short_name', 'EÚ'),
('sk', 'sk', 'name_', 'Slovenská republika'),
('sk', 'sk', 'short_name', 'Slovensko'),
('cz', 'sk', 'name_', 'Česká republika'),
('cz', 'sk', 'short_name', 'Česko');

insert into country_attribute (country_code, lang, name_, value_) values
('eu', 'cs', 'name_', 'Evropská unie'),
('eu', 'cs', 'short_name', 'EU'),
('sk', 'cs', 'name_', 'Slovenská republika'),
('sk', 'cs', 'short_name', 'Slovensko'),
('cz', 'cs', 'name_', 'Česká republika'),
('cz', 'cs', 'short_name', 'Česko');
