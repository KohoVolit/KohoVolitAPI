-- KohoVolit.eu Generación Cuarta
-- triggers of package NapišteJim

create or replace function message_last_reply_on_update()
returns trigger as $$
begin
	update message set last_reply_on = (
		select max(received_on) from reply where reply_code = new.reply_code
	)
	where
		id = (
			select message_id from message_to_mp where reply_code = new.reply_code
		);
	return new;
end; $$ language plpgsql;

create trigger message_last_reply_on_update
	after insert or update on reply
	for each row execute procedure message_last_reply_on_update();
