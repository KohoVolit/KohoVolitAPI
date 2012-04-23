-- KohoVolit.eu Generación Cuarta
-- inserts into tables of package PARLIAMENT

insert into parliament_kind (code, "name", short_name, description, weight) values
('supernational', 'Supernational parliament', 'Supernational', 'Parliament at supernational level, eg. European parliament.', 1.0),
('national', 'National parliament', 'National', 'National parliament.', 2.0),
('national-upper', 'Upper house of the national parliament', 'Upper house', 'Upper house of the national level parliament - senate.', 2.2),
('national-lower', 'Lower house of the national parliament', 'Lower house', 'Lower house of the national level parliament - chamber of deputies.', 2.1),
('government', 'Government', 'Government', 'Government of the country.', 2.3),
('regional', 'Regional parliament', 'Regional', 'Parliament at a regional level.', 3.0),
('mayors', 'Local mayors', 'Mayors', 'Mayors of towns and cities.', 4.0),
('local', 'Local parliament', 'Local', 'Parliament at a city level.', 4.1);

insert into parliament_kind_attribute (parliament_kind_code, lang, "name", "value") values
('supernational', 'sk', 'name', 'Nadnárodný parlament'),
('supernational', 'sk', 'short_name', 'Nadnárodný'),
('supernational', 'sk', 'description', 'Parlament na nadnárodnej úrovni, napr. Európsky parlament.'),
('national', 'sk', 'name', 'Národný parlament'),
('national', 'sk', 'short_name', 'Národný'),
('national', 'sk', 'description', 'Národný parlament.'),
('national-upper', 'sk', 'name', 'Horná komora národného parlamentu'),
('national-upper', 'sk', 'short_name', 'Horná komora'),
('national-upper', 'sk', 'description', 'Horná komora národného parlamentu - senát.'),
('national-lower', 'sk', 'name', 'Dolná komora národného parlamentu'),
('national-lower', 'sk', 'short_name', 'Dolná komora'),
('national-lower', 'sk', 'description', 'Dolná komora národného parlamentu - poslanecká snemovňa.'),
('government', 'sk', 'name', 'Vláda'),
('government', 'sk', 'short_name', 'Vláda'),
('government', 'sk', 'description', 'Vláda krajiny.'),
('regional', 'sk', 'name', 'Krajské zastupiteľstvo'),
('regional', 'sk', 'short_name', 'Krajský'),
('regional', 'sk', 'description', 'Zastupiteľstvo na krajskej úrovni.'),
('mayors', 'sk', 'name', 'Starostovia obcí'),
('mayors', 'sk', 'short_name', 'Starostovia'),
('mayors', 'sk', 'description', 'Starostovia obcí a miest.'),
('local', 'sk', 'name', 'Miestne zastupiteľstvo'),
('local', 'sk', 'short_name', 'Miestny'),
('local', 'sk', 'description', 'Zastupiteľstvo na obecnej alebo mestskej úrovni.');

insert into parliament_kind_attribute (parliament_kind_code, lang, "name", "value") values
('supernational', 'cs', 'name', 'Nadnárodní parlament'),
('supernational', 'cs', 'short_name', 'Nadnárodní'),
('supernational', 'cs', 'description', 'Parlament na nadnárodní úrovni, např. Evropský parlament.'),
('national', 'cs', 'name', 'Národní parlament'),
('national', 'cs', 'short_name', 'Národní'),
('national', 'cs', 'description', 'Národní parlament.'),
('national-upper', 'cs', 'name', 'Horní komora národního parlamentu'),
('national-upper', 'cs', 'short_name', 'Horní komora'),
('national-upper', 'cs', 'description', 'Horní komora národního parlamentu - senát.'),
('national-lower', 'cs', 'name', 'Dolní komora národního parlamentu'),
('national-lower', 'cs', 'short_name', 'Dolní komora'),
('national-lower', 'cs', 'description', 'Dolní komora národního parlamentu - poslanecká sněmovna.'),
('government', 'cs', 'name', 'Vláda'),
('government', 'cs', 'short_name', 'Vláda'),
('government', 'cs', 'description', 'Vláda krajiny.'),
('regional', 'cs', 'name', 'Krajské zastupitelstvo'),
('regional', 'cs', 'short_name', 'Krajský'),
('regional', 'cs', 'description', 'Zastupitelstvo na krajské úrovni.'),
('mayors', 'cs', 'name', 'Starostové obcí'),
('mayors', 'cs', 'short_name', 'Starostové'),
('mayors', 'cs', 'description', 'Starostové obcí a měst.'),
('local', 'cs', 'name', 'Místní zastupitelstvo'),
('local', 'cs', 'short_name', 'Místní'),
('local', 'cs', 'description', 'Zastupitelstvo na obecní nebo městské úrovni.');

insert into parliament_kind_attribute (parliament_kind_code, cntry, "name", lang, "value") values
('supernational', 'eu', 'competence', 'en', 'Together with the Council, it adopts laws (regulations, directives) proposed by European Commission, it has control over the EU budget, it must agree on accession of any new member-state.'),
('supernational', 'eu', 'competence', 'sk', 'Spolu s Radou EÚ prijíma zákony (nariadenia, smernice) navrhnuté Európskou komisiou, rozhoduje o rozpočte EÚ, schvaľuje prijatie nových členských štátov EÚ.'),
('supernational', 'eu', 'competence', 'cs', 'Společně s Radou EU přijímá zákony (nařízení, směrnice) navržené Evropskou komisí, rozhoduje o rozpočtu EU, schvaluje přijetí nových členských států EU.'),
('national', 'sk', 'competence', 'sk', 'Prejednáva a schvaľuje návrhy zákonov, štátny rozpočet, zmeny ústavy. Ratifikuje medzinárodné zmluvy. Môže vysloviť nedôveru vláde.'),
('national', 'sk', 'competence', 'en', 'It negotiates and adopts law proposals, government budget, constitution amendments. Ratifies international treaties. Can impeach the government.'),
('government', 'sk', 'competence', 'sk', 'Vláde náleží výkonná moc a správa štátu.'),
('government', 'sk', 'competence', 'en', 'The government holds the executive power and administration of the country.'),
('regional', 'sk', 'competence', 'sk', 'Krajské zastupiteľstvo spravuje kraj a hospodári s jeho majetkom, vydáva všeobecne záväzné nariadenia, zriaďuje krajský úrad.'),
('regional', 'sk', 'competence', 'en', 'Regional assembly administrates the region and manages its wealth, adopts regional regulations, constitutes regional office.'),
('mayors', 'sk', 'competence', 'sk', 'Starosta alebo primátor spravuje obec a zastupuje ju navonok, podpisuje nariadenia zastupiteľstva.'),
('mayors', 'sk', 'competence', 'en', 'Mayor adminstrates the town and represents it on public, ratifies regulations of municipal assembly.'),
('local', 'sk', 'competence', 'sk', 'Obecné zastupiteľstvo zodpovedá za rozvoj obce a za hospodárenie s obecným majetkom, určuje miestne dane a usporiadanie obecného úradu, vydáva všeobecne záväzné nariadenia.'),
('local', 'sk', 'competence', 'en', 'Municipal assembly accounts for town development and management of its wealth, assesses local taxes and arranges organization of municipal office, adopts municipal regulations.'),
('national-lower', 'cz', 'competence', 'cs', 'Projednává a schvaluje návrhy zákonů, státní rozpočet, změny ústavy. Ratifikuje mezinárodní smlouvy. Volí prezidenta. Může vyslovit nedůvěru vládě.'),
('national-lower', 'cz', 'competence', 'en', 'It negotiates and adopts law proposals, government budget, constitution amendments. Ratifies international treaties. Elects president. Can impeach the government.'),
('national-upper', 'cz', 'competence', 'cs', 'Projednává a schvaluje návrhy zákonů, změny ústavy a mezinárodní smlouvy přijaté Sněmovnou. Volí prezidenta.'),
('national-upper', 'cz', 'competence', 'en', 'It negotiates and adopts law proposals, constitution amendments and international treaties adopted by the Chamber of deputies. Elects president.'),
('government', 'cz', 'competence', 'cs', 'Vládě náleží výkonná moc a správa státu.'),
('government', 'cz', 'competence', 'en', 'The government holds the executive power and administration of the country.'),
('regional', 'cz', 'competence', 'cs', 'Krajské zastupitelstvo spravuje kraj a hospodaří s jeho majetkem, vydává obecně závazné vyhlášky. Volí radu a hejtmana.'),
('regional', 'cz', 'competence', 'en', 'Regional assembly administrates the region and manages its wealth, adopts regional regulations. Elects regional council and hetman.'),
('mayors', 'cz', 'competence', 'cs', 'Starosta zastupuje obec navenek, podepisuje vyhlášky a usnesení zastupitelstva, odpovídá za informování veřejnosti o činnosti obce.'),
('mayors', 'cz', 'competence', 'en', 'Mayor represents the town on public, ratifies regulations and resolutions of municipal assembly, accounts for acquainting with town functioning.'),
('local', 'cz', 'competence', 'cs', 'Obecní zastupitelstvo odpovídá za rozvoj obce a za hospodaření s obecním majetkem, vydává obecně závazné vyhlášky. Volí radu a starostu.'),
('local', 'cz', 'competence', 'en', 'Municipal assembly accounts for town development and management of its wealth, adopts municipal regulations. Elects local council and mayor.');

insert into parliament (code, "name", short_name, description) values
('-', 'any parliament', 'any', 'This parliament is referenced as a foreign key by attributes that are parliament independent.');

INSERT INTO parliament_attribute ("name","value",parliament_code) VALUES
('default language', 'cs', 'cz/psp'),
('default language', 'cs', 'cz/senat'),
('default language', 'sk', 'sk/nrsr'),
('default language', 'cs', 'cs/fs');
