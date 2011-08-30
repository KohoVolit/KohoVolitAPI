<?php

/**
 * Class PublicMessagePreview lists all sent public messages.
 *
 * Only the \e read method is present.
 */
class PublicMessagePreview
{
	/**
	 * Lists all sent public messages in descending order by the date and time they were sent.
	 *
	 * \param $params An array of pairs <em>name => value</em> where allowed names are:
	 *	\li \c parliament_code - select only messages addressed (also) to a member of this parliament
	 *	\li \c mp_id - select only messages addressed to this MP
	 *	\li \c since - select only messages sent since (including) this date and time
	 *	\li \c until - select only messages sent until (excluding) this date and time.
	 * Example: <code>array('parliament_code' => 'cz/senat', 'since' => '2011-06-22 10:00:00')</code>.
	 *
	 * \return Basic information on all sent public messages.
	 */
	public static function read($params)
	{
		$query = new Query();
		$query->setQuery(
			"select\n" .
			"	id, subject,\n" .
			"	substr(body_, 1, 200) || case when length(body_) > 200 then '...' else '' end as body_,\n" .
			"	sender_name, sent_on,\n" .
			"	(extract('epoch' from now()) - extract('epoch' from sent_on)) / 86400 as age_days,\n" .
			"	recipients, response_exists\n" .
			"from\n" .
			"	message as m\n" .
			"	join (\n" .
			"		select\n" .
			"			message_id,\n" .
			"			string_agg(mp.last_name || ' ' || substr(mp.first_name, 1, 1) || '.' || case when length(mp.middle_names) > 0 then substr(mp.middle_names, 1, 1) || '. ' else '' end, ', ' order by mp_id) as recipients,\n" .
			"			string_agg(case when received_on is not null then 'yes' else 'no' end, ', ' order by mp_id) as response_exists\n" .
			"		from\n" .
			"			response as r\n" .
			"			join mp on mp.id = r.mp_id\n" .
			"		where\n" .
			"			true\n"
		);

		// filter messages for only the ones addressed to a particular parliament or MP
		$n = 0;
		if (isset($params['parliament_code']))
		{
			$query->appendQuery('			and r.parliament_code = $' . ++$n . "\n");
			$query->appendParam($params['parliament_code']);
		}
		if (isset($params['mp_id']))
		{
			$query->appendQuery('			and mp.id = $' . ++$n . "\n");
			$query->appendParam($params['mp_id']);
		}

		$query->appendQuery(
			"		group by\n" .
			"			message_id\n" .
			"	) as r\n" .
			"	on r.message_id = m.id\n" .
			"where\n" .
			"	state_ = 'sent'\n" .
			"	and is_public = 'yes'\n"
		);

		// filter messages for only the ones sent since or until a particular date and time
		if (isset($params['since']))
		{
			$query->appendQuery('	and sent_on >= $' . ++$n . "\n");
			$query->appendParam($params['since']);
		}
		if (isset($params['until']))
		{
			$query->appendQuery('	and sent_on < $' . ++$n . "\n");
			$query->appendParam($params['until']);
		}

		$query->appendQuery("order by\n	sent_on desc");

		return $query->execute();
	}
}

?>
