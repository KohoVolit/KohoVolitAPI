-- KohoVolit.eu Generación Quarta
-- inserts into tables of package GROUP

insert into group_kind (code, name_, short_name, description, subkind_of) values
('parliament', 'Parliament', 'Parliament', 'Parliament at any administrative level.', null),
('political group', 'Political group', 'Group', 'Political group in a parliament.', 'parliament'),
('committee', 'Parliamentary committee', 'Committee', 'Committee of a parliament.', 'parliament'),
('subcommittee', 'Parliamentary subcommittee', 'Subcommittee', 'Subcommittee of a committee of a parliament.', 'committee'),
('commission', 'Parliamentary commission', 'Commission', 'Commission of a parliament.', 'parliament'),
('delegation', 'Parliamentary delegation', 'Delegation', 'Delegation of a parliament.', 'parliament'),
('friendship group', 'Interparliamentary friendship group', 'Friendship group', 'Friendship group with a parliament of a different country.', 'parliament'),
('working group', 'Working group', 'Workgroup', 'Working or expert group.', 'parliament'),
('institution', 'Other institution', 'Institution', 'Other institution outside parliament.', null);

insert into group_kind_attribute (group_kind_code, lang, name_, value_) values
('parliament', 'sk', 'name_', 'Parlament'),
('parliament', 'sk', 'short_name', 'Parlament'),
('parliament', 'sk', 'description', 'Parlament na ľubovoľnej administratívnej úrovni.'),
('political group', 'sk', 'name_', 'Poslanecký klub'),
('political group', 'sk', 'short_name', 'Klub'),
('political group', 'sk', 'description', 'Poslanecký klub v parlamente.'),
('committee', 'sk', 'name_', 'Parlamentný výbor'),
('committee', 'sk', 'short_name', 'Výbor'),
('committee', 'sk', 'description', 'Výbor parlamentu.'),
('subcommittee', 'sk', 'name_', 'Parlamentný podvýbor'),
('subcommittee', 'sk', 'short_name', 'Podvýbor'),
('subcommittee', 'sk', 'description', 'Podvýbor výboru parlamentu.'),
('commission', 'sk', 'name_', 'Parlamentná komisia'),
('commission', 'sk', 'short_name', 'Komisia'),
('commission', 'sk', 'description', 'Komisia parlamentu.'),
('delegation', 'sk', 'name_', 'Parlamentná delegácia'),
('delegation', 'sk', 'short_name', 'Delegácia'),
('delegation', 'sk', 'description', 'Delegácia parlamentu.'),
('friendship group', 'sk', 'name_', 'Medziparlamentná skupina'),
('friendship group', 'sk', 'short_name', 'Medziparlament'),
('friendship group', 'sk', 'description', 'Medziparlamentná skupina priateľstva s parlamentom inej krajiny.'),
('working group', 'sk', 'name_', 'Pracovná skupina'),
('working group', 'sk', 'short_name', 'Prac. skupina'),
('working group', 'sk', 'description', 'Pracovná alebo expertná skupina.'),
('institution', 'sk', 'name_', 'Iná inštitúcia'),
('institution', 'sk', 'short_name', 'Inštitúcia'),
('institution', 'sk', 'description', 'Inštitúcia mimo parlamentu.');

insert into group_kind_attribute (group_kind_code, lang, name_, value_) values
('parliament', 'cs', 'name_', 'Parlament'),
('parliament', 'cs', 'short_name', 'Parlament'),
('parliament', 'cs', 'description', 'Parlament na libovolné administrativní úrovni.'),
('political group', 'cs', 'name_', 'Poslanecký klub'),
('political group', 'cs', 'short_name', 'Klub'),
('political group', 'cs', 'description', 'Poslanecký klub v parlamentu.'),
('committee', 'cs', 'name_', 'Parlamentní výbor'),
('committee', 'cs', 'short_name', 'Výbor'),
('committee', 'cs', 'description', 'Výbor parlamentu.'),
('subcommittee', 'cs', 'name_', 'Parlamentní podvýbor'),
('subcommittee', 'cs', 'short_name', 'Podvýbor'),
('subcommittee', 'cs', 'description', 'Podvýbor výboru parlamentu.'),
('commission', 'cs', 'name_', 'Parlamentní komise'),
('commission', 'cs', 'short_name', 'Komise'),
('commission', 'cs', 'description', 'Komise parlamentu.'),
('delegation', 'cs', 'name_', 'Parlamentní delegace'),
('delegation', 'cs', 'short_name', 'Delegace'),
('delegation', 'cs', 'description', 'Delegace parlamentu.'),
('friendship group', 'cs', 'name_', 'Meziparlamentní skupina'),
('friendship group', 'cs', 'short_name', 'Meziparlament'),
('friendship group', 'cs', 'description', 'Meziparlamentní skupina v rámci MPU.'),
('working group', 'cs', 'name_', 'Pracovní skupina'),
('working group', 'cs', 'short_name', 'Prac. skupina'),
('working group', 'cs', 'description', 'Pracovní nebo expertní skupina.'),
('institution', 'cs', 'name_', 'Jiná instituce'),
('institution', 'cs', 'short_name', 'Instituce'),
('institution', 'cs', 'description', 'Instituce mimo parlamentu.');


insert into role_ (code, male_name, female_name, description) values
('member', 'member', 'member', null),
('substitute', 'substitute', 'substitute', null),
('chairman', 'chairman', 'chairwoman', null),
('vice-chairman', 'vice-chairman', 'vice-chairwoman', null),
('president', 'president', 'president', null),
('vice-president', 'vice-president', 'vice-president', null),
('co-president', 'co-president', 'co-president', null),
('member of the bureau', 'member of the bureau', 'member of the bureau', null),
('chairman of the bureau', 'chairman of the bureau', 'chairwoman of the bureau', null),
('treasurer', 'treasurer', 'treasurer', null),
('deputy-treasurer', 'deputy-treasurer', 'deputy-treasurer', null),
('quaestor', 'quaestor', 'quaestor', null);

insert into role_attribute (role_code, lang, name_, value_) values
('member', 'sk', 'male_name', 'člen'),
('member', 'sk', 'female_name', 'členka'),
('substitute', 'sk', 'male_name', 'náhradník'),
('substitute', 'sk', 'female_name', 'náhradníčka'),
('chairman', 'sk', 'male_name', 'predseda'),
('chairman', 'sk', 'female_name', 'predsedníčka'),
('vice-chairman', 'sk', 'male_name', 'podpredseda'),
('vice-chairman', 'sk', 'female_name', 'podpredsedníčka'),
('president', 'sk', 'male_name', 'predseda'),
('president', 'sk', 'female_name', 'predsedníčka'),
('vice-president', 'sk', 'male_name', 'podpredseda'),
('vice-president', 'sk', 'female_name', 'podpredsedníčka'),
('co-president', 'sk', 'male_name', 'spolupredseda'),
('co-president', 'sk', 'female_name', 'spolupredsedníčka'),
('member of the bureau', 'sk', 'male_name', 'člen predsedníctva'),
('member of the bureau', 'sk', 'female_name', 'členka predsedníctva'),
('chairman of the bureau', 'sk', 'male_name', 'predseda úradu'),
('chairman of the bureau', 'sk', 'female_name', 'predsedníčka úradu'),
('treasurer', 'sk', 'male_name', 'pokladník'),
('treasurer', 'sk', 'female_name', 'pokladníčka'),
('deputy-treasurer', 'sk', 'male_name', 'zástupca pokladníka'),
('deputy-treasurer', 'sk', 'female_name', 'zástupkyňa pokladníka'),
('quaestor', 'sk', 'male_name', 'kvestor'),
('quaestor', 'sk', 'female_name', 'kvestorka');

insert into role_attribute (role_code, lang, name_, value_) values
('member', 'cs', 'male_name', 'člen'),
('member', 'cs', 'female_name', 'členka'),
('substitute', 'cs', 'male_name', 'náhradník'),
('substitute', 'cs', 'female_name', 'náhradníčka'),
('chairman', 'cs', 'male_name', 'předseda'),
('chairman', 'cs', 'female_name', 'předsedkyně'),
('vice-chairman', 'cs', 'male_name', 'místopředseda'),
('vice-chairman', 'cs', 'female_name', 'místopředsedkyně'),
('president', 'cs', 'male_name', 'předseda'),
('president', 'cs', 'female_name', 'předsedkyně'),
('vice-president', 'cs', 'male_name', 'místopředseda'),
('vice-president', 'cs', 'female_name', 'místopředsedkyně'),
('co-president', 'cs', 'male_name', 'spolupředseda'),
('co-president', 'cs', 'female_name', 'spolupředsedkyně'),
('member of the bureau', 'cs', 'male_name', 'člen předsednictva'),
('member of the bureau', 'cs', 'female_name', 'členka předsednictva'),
('chairman of the bureau', 'cs', 'male_name', 'předseda výboru'),
('chairman of the bureau', 'cs', 'female_name', 'předsedkyně výboru'),
('treasurer', 'cs', 'male_name', 'pokladník'),
('treasurer', 'cs', 'female_name', 'pokladník'),
('deputy-treasurer', 'cs', 'male_name', 'zástupce pokladníka'),
('deputy-treasurer', 'cs', 'female_name', 'zástupkyně pokladníka'),
('quaestor', 'cs', 'male_name', 'kvestor'),
('quaestor', 'cs', 'female_name', 'kvestorka');
