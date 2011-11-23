-- KohoVolit.eu Generación Cuarta
-- inserts into tables of package DIVISION

INSERT INTO division_kind (code,"name",description) VALUES
('simple','simple majority','More than 1/2 of present votes'),
('absolute','absolute majority','More than 1/2 of all representatives'),
('3/5','3/5 absolute majority', 'More than 3/5 of all representatives'),
('unknown','unknown kind','Unknown kind of division');

INSERT INTO vote_kind (code,"name",description) VALUES
('y','yes','Vote for the proposal'),
('n','no','Vote against the proposal'),
('a','abstain','Abstain, usually means against the proposal'),
('s','secret','Secret vote'),
('m','not present','Not present'),
('e','excused','Not present, excused'),
('b','present, not voted', 'Present, but not voted'),
('p','paired','Paired another representative, not voting'),
('5','article 5','Not voting because of Article 5 (Chile)'),
('17','article 17','Not voting because of Article 17 (Brazil)'),
('k','blank','Blank vote'),
('o','obstruction','Obstruction');

INSERT INTO division_kind_attribute("name",division_kind_code, lang, "value") VALUES
('name','simple','cs','prostá většina'),
('name','absolute','cs','absolutní většina'),
('name','3/5','cs','třípětinová většina'),
('name','unknown','cs','neznámý'),
('description','simple','cs','Více jak polovina přítomných zástupců'),
('description','absolute','cs','Více jak polovina všech zástupců'),
('description','3/5','cs','Více jak 2/3 všech zástupců'),
('description','unknown','cs','Neznámý druh hlasování');

INSERT INTO vote_kind_attribute("name",vote_kind_code, lang, "value") VALUES
('name','y','cs','ano'),
('name','n','cs','ne'),
('name','a','cs','zdržet se'),
('name','m','cs','nebýt přítomen'),
('name','e','cs','být omluven'),
('name','b','cs','přítomen, ale nehlasovat'),
('description','y','cs','Hlasovat pro návrh'),
('description','n','cs','Hlasovat proti návrhu'),
('description','a','cs','Zdržet se hlasování, obvykle stejné jako být proti návrhu'),
('description','m','cs','Nebýt přítomen hlasování'),
('description','b','cs','Být přítomen hlasování, ale nehlasovat'),
('description','e','cs','nebýt přítomen hlasování, ale s omluvou');

