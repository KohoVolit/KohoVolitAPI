-- KohoVolit.eu Generación Cuarta
-- triggers of package NapišteJim

create or replace function message_last_reply_on_update()
returns trigger as $$
begin
	update message set last_reply_on = (
		select max(received_on) from reply join message_to_mp using (reply_code) where message_id = new.id
	);
	return new;
end; $$ language plpgsql;

create trigger message_last_reply_on_update
	after insert or update on reply
	for each row execute procedure message_last_reply_on_update();
