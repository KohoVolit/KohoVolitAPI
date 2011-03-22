-- KohoVolit.eu Generación Quarta
-- user defined data types

create type address_type as
(
	addressee varchar,
	street varchar,
	house_number varchar,
	town varchar,
	postal_code varchar,
	country varchar
);
