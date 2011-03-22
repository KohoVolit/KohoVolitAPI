-- KohoVolit.eu Generación Quarta
-- inserts into tables of package PARLIAMENT

insert into parliament_kind (code, name_, short_name, description) values
('supernational', 'Supernational parliament', 'Supernational', 'Parliament at supernational level, eg. European parliament.'),
('national-upper', 'Upper house of the national parliament', 'Upper house', 'Upper house of the national level parliament - senate.'),
('national-lower', 'Lower house of the national parliament', 'Lower house', 'Lower house of the national level parliament - chamber of deputies.'),
('regional', 'Regional parliament', 'Regional', 'Parliament at regional level.'),
('local', 'Local parliament', 'Local', 'Parliament at city level.');

insert into parliament_kind_attribute (parliament_kind_code, lang, name_, value_) values
('supernational', 'sk', 'name_', 'Nadnárodný parlament'),
('supernational', 'sk', 'short_name', 'Nadnárodný'),
('supernational', 'sk', 'description', 'Parlament na nadnárodnej úrovni, napr. Európsky parlament.'),
('national-upper', 'sk', 'name_', 'Horná komora národného parlamentu'),
('national-upper', 'sk', 'short_name', 'Horná komora'),
('national-upper', 'sk', 'description', 'Horná komora národného parlamentu - senát.'),
('national-lower', 'sk', 'name_', 'Dolná komora národného parlamentu'),
('national-lower', 'sk', 'short_name', 'Dolná komora'),
('national-lower', 'sk', 'description', 'Dolná komora národného parlamentu - poslanecká snemovňa.'),
('regional', 'sk', 'name_', 'Krajské zastupiteľstvo'),
('regional', 'sk', 'short_name', 'Krajský'),
('regional', 'sk', 'description', 'Zastupiteľstvo na krajskej úrovni.'),
('local', 'sk', 'name_', 'Miestne zastupiteľstvo'),
('local', 'sk', 'short_name', 'Miestny'),
('local', 'sk', 'description', 'Zastupiteľstvo na obecnej alebo mestskej úrovni.');

insert into parliament_kind_attribute (parliament_kind_code, lang, name_, value_) values
('supernational', 'cs', 'name_', 'Nadnárodní parlament'),
('supernational', 'cs', 'short_name', 'Nadnárodní'),
('supernational', 'cs', 'description', 'Parlament na nadnárodní úrovni, např. Evropský parlament.'),
('national-upper', 'cs', 'name_', 'Horní komora národního parlamentu'),
('national-upper', 'cs', 'short_name', 'Horní komora'),
('national-upper', 'cs', 'description', 'Horní komora národního parlamentu - senát.'),
('national-lower', 'cs', 'name_', 'Dolní komora národního parlamentu'),
('national-lower', 'cs', 'short_name', 'Dolní komora'),
('national-lower', 'cs', 'description', 'Dolní komora národního parlamentu - poslanecká sněmovna.'),
('regional', 'cs', 'name_', 'Krajské zastupitelstvo'),
('regional', 'cs', 'short_name', 'Krajský'),
('regional', 'cs', 'description', 'Zastupitelstvo na krajské úrovni.'),
('local', 'cs', 'name_', 'Místní zastupitelstvo'),
('local', 'cs', 'short_name', 'Místní'),
('local', 'cs', 'description', 'Zastupitelstvo na obecní nebo městské úrovni.');
