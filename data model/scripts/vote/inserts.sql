-- KohoVolit.eu Generación Cuarta
-- inserts into tables of package DIVISION

insert into vote_kind (code, "name", description) values
('y', 'yes', 'Vote for the proposal.'),
('n', 'no', 'Vote against the proposal.'),
('a', 'abstain', 'Abstain, usually means against the proposal.'),
('s', 'secret', 'Secret vote.'),
('m', 'not present', 'Not present.'),
('e', 'excused', 'Not present, excused.'),
('b', 'present, not voted', 'Present, but not voted.'),
('p', 'paired', 'Paired another representative, not voting.'),
('5', 'article 5', 'Not voting because of Article 5 (Chile).'),
('17', 'article 17', 'Not voting because of Article 17 (Brazil).'),
('k', 'blank', 'Blank vote.'),
('o', 'obstruction', 'Obstruction.'),
('i', 'invalid', 'Invalid vote.');

insert into vote_kind_attribute("name", vote_kind_code, lang, "value") values
('name', 'y', 'cs', 'ano'),
('name', 'n', 'cs', 'ne'),
('name', 'a', 'cs', 'zdržel(a) se'),
('name', 's', 'cs', 'tajný'),
('name', 'm', 'cs', 'nepřítomen(a)'),
('name', 'e', 'cs', 'omluven(a)'),
('name', 'b', 'cs', 'přítomen(a), ale nehlasoval(a)'),
('name', 'p', 'cs', 'pároval(a)'),
('name', '5', 'cs', 'článek 5'),
('name', '17', 'cs', 'článek 17'),
('name', 'k', 'cs', 'prázdný'),
('name', 'o', 'cs', 'obstrukce'),
('name', 'i', 'cs', 'neplatný'),
('description', 'y', 'cs', 'Hlasování pro návrh.'),
('description', 'n', 'cs', 'Hlasování proti návrhu.'),
('description', 'a', 'cs', 'Zdržení se hlasování, obvykle stejné jako být proti návrhu.'),
('description', 's', 'cs', 'Tajný hlas.'),
('description', 'm', 'cs', 'Nepřítomnost na hlasování.'),
('description', 'e', 'cs', 'Nepřítomnost na hlasování, ale s omluvou.'),
('description', 'b', 'cs', 'Přítomnost na hlasování, ale nehlasování.'),
('description', 'p', 'cs', 'Párovaní jiného, nehlasujícího, zastupitele.'),
('description', '5', 'cs', 'Nehlasování podle Článku 5 (Čile).'),
('description', '17', 'cs', 'Nehlasování podle Článku 17 (Brazílie).'),
('description', 'k', 'cs', 'Prázdný hlas.'),
('description', 'o', 'cs', 'Obstrukce.'),
('description', 'i', 'cs', 'Neplatný hlas.');

insert into vote_kind_attribute("name", vote_kind_code, lang, "value") values
('name', 'y', 'sk', 'áno'),
('name', 'n', 'sk', 'nie'),
('name', 'a', 'sk', 'zdržal(a) sa'),
('name', 's', 'sk', 'tajný'),
('name', 'm', 'sk', 'neprítomný(á)'),
('name', 'e', 'sk', 'ospravedlnený(á)'),
('name', 'b', 'sk', 'prítomný(á), ale nehlasoval(a)'),
('name', 'p', 'sk', 'pároval(a)'),
('name', '5', 'sk', 'článok 5'),
('name', '17', 'sk', 'článok 17'),
('name', 'k', 'sk', 'prázdny'),
('name', 'o', 'sk', 'obštrukcia'),
('name', 'i', 'sk', 'neplatný'),
('description', 'y', 'sk', 'Hlasovanie za návrh.'),
('description', 'n', 'sk', 'Hlasovanie proti návrhu.'),
('description', 'a', 'sk', 'Zdržanie sa hlasovania, obvykle rovnaké ako byť proti návrhu.'),
('description', 's', 'sk', 'Tajný hlas.'),
('description', 'm', 'sk', 'Neprítomnosť na hlasovaní.'),
('description', 'e', 'sk', 'Neprítomnosť na hlasovaní, ale s ospravedlnením.'),
('description', 'b', 'sk', 'Prítomnosť na hlasovaní, ale nehlasovanie.'),
('description', 'p', 'sk', 'Párovanie iného, nehlasujúceho, poslanca.'),
('description', '5', 'sk', 'Nehlasovanie podľa Článku 5 (Čile).'),
('description', '17', 'sk', 'Nehlasovanie podľa Článku 17 (Brazília).'),
('description', 'k', 'sk', 'Prázdny hlas.'),
('description', 'o', 'sk', 'Obštrukcia.'),
('description', 'i', 'sk', 'Neplatný hlas.');


insert into division_kind (code, "name", description) values
('simple', 'simple majority', 'More than half of present representatives.'),
('absolute', 'absolute majority', 'More than half of all representatives.'),
('3/5', '3/5 absolute majority', 'More than three fifths of all representatives.'),
('unknown', 'unknown kind', 'Unknown kind of division.');

insert into division_kind_attribute("name", division_kind_code, lang, "value") values
('name', 'simple', 'cs', 'prostá většina'),
('name', 'absolute', 'cs', 'absolutní většina'),
('name', '3/5', 'cs', 'třípětinová většina'),
('name', 'unknown', 'cs', 'neznámý'),
('description', 'simple', 'cs', 'Více jak polovina přítomných zastupitelů.'),
('description', 'absolute', 'cs', 'Více jak polovina všech zastupitelů.'),
('description', '3/5', 'cs', 'Více jak tři pětiny všech zastupitelů.'),
('description', 'unknown', 'cs', 'Neznámý druh hlasování.');

insert into division_kind_attribute("name", division_kind_code, lang, "value") values
('name', 'simple', 'sk', 'prostá väčšina'),
('name', 'absolute', 'sk', 'absolútna väčšina'),
('name', '3/5', 'sk', 'trojpätinová väčšina'),
('name', 'unknown', 'sk', 'neznáme'),
('description', 'simple', 'sk', 'Viac než polovica prítomných poslancov.'),
('description', 'absolute', 'sk', 'Viac než polovica všetkých poslancov.'),
('description', '3/5', 'sk', 'Viac než tri pätiny všetkých poslancov.'),
('description', 'unknown', 'sk', 'Neznámy druh hlasovania.');
