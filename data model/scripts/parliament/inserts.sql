-- KohoVolit.eu Generación Cuarta
-- inserts into tables of package PARLIAMENT

insert into parliament_kind (code, "name", short_name, description) values
('supernational', 'Supernational parliament', 'Supernational', 'Parliament at supernational level, eg. European parliament.'),
('national', 'National parliament', 'National', 'National parliament.'),
('national-upper', 'Upper house of the national parliament', 'Upper house', 'Upper house of the national level parliament - senate.'),
('national-lower', 'Lower house of the national parliament', 'Lower house', 'Lower house of the national level parliament - chamber of deputies.'),
('government', 'Government', 'Government', 'Government of the country.'),
('regional', 'Regional parliament', 'Regional', 'Parliament at a regional level.'),
('local', 'Local parliament', 'Local', 'Parliament at a city level.');

insert into parliament_kind_attribute (parliament_kind_code, lang, "name", "value") values
('supernational', 'sk', 'name', 'Nadnárodný parlament'),
('supernational', 'sk', 'short_name', 'Nadnárodný'),
('supernational', 'sk', 'description', 'Parlament na nadnárodnej úrovni, napr. Európsky parlament.'),
('national-upper', 'sk', 'name', 'Horná komora národného parlamentu'),
('national-upper', 'sk', 'short_name', 'Horná komora'),
('national-upper', 'sk', 'description', 'Horná komora národného parlamentu - senát.'),
('national-lower', 'sk', 'name', 'Dolná komora národného parlamentu'),
('national-lower', 'sk', 'short_name', 'Dolná komora'),
('national-lower', 'sk', 'description', 'Dolná komora národného parlamentu - poslanecká snemovňa.'),
('national', 'sk', 'name', 'Národný parlament'),
('national', 'sk', 'short_name', 'Národný'),
('national', 'sk', 'description', 'Národný parlament.'),
('government', 'sk', 'name', 'Vláda'),
('government', 'sk', 'short_name', 'Vláda'),
('government', 'sk', 'description', 'Vláda krajiny.'),
('regional', 'sk', 'name', 'Krajské zastupiteľstvo'),
('regional', 'sk', 'short_name', 'Krajský'),
('regional', 'sk', 'description', 'Zastupiteľstvo na krajskej úrovni.'),
('local', 'sk', 'name', 'Miestne zastupiteľstvo'),
('local', 'sk', 'short_name', 'Miestny'),
('local', 'sk', 'description', 'Zastupiteľstvo na obecnej alebo mestskej úrovni.');

insert into parliament_kind_attribute (parliament_kind_code, lang, "name", "value") values
('supernational', 'cs', 'name', 'Nadnárodní parlament'),
('supernational', 'cs', 'short_name', 'Nadnárodní'),
('supernational', 'cs', 'description', 'Parlament na nadnárodní úrovni, např. Evropský parlament.'),
('national-upper', 'cs', 'name', 'Horní komora národního parlamentu'),
('national-upper', 'cs', 'short_name', 'Horní komora'),
('national-upper', 'cs', 'description', 'Horní komora národního parlamentu - senát.'),
('national-lower', 'cs', 'name', 'Dolní komora národního parlamentu'),
('national-lower', 'cs', 'short_name', 'Dolní komora'),
('national-lower', 'cs', 'description', 'Dolní komora národního parlamentu - poslanecká sněmovna.'),
('national', 'cs', 'name', 'Národní parlament'),
('national', 'cs', 'short_name', 'Národní'),
('national', 'cs', 'description', 'Národní parlament.'),
('government', 'cs', 'name', 'Vláda'),
('government', 'cs', 'short_name', 'Vláda'),
('government', 'cs', 'description', 'Vláda krajiny.'),
('regional', 'cs', 'name', 'Krajské zastupitelstvo'),
('regional', 'cs', 'short_name', 'Krajský'),
('regional', 'cs', 'description', 'Zastupitelstvo na krajské úrovni.'),
('local', 'cs', 'name', 'Místní zastupitelstvo'),
('local', 'cs', 'short_name', 'Místní'),
('local', 'cs', 'description', 'Zastupitelstvo na obecní nebo městské úrovni.');


insert into parliament (code, "name", short_name, description) values
('-', 'any parliament', 'any', 'This parliament is referenced as a foreign key by MP attributes that are parliament independent.');
