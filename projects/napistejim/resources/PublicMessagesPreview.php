<?php

/**
 * \ingroup napistejim
 *
 * Lists previews of sent public messages.
 */
class PublicMessagesPreview
{
	/**
	 * Lists previews of sent public messages in descending order by the date and time they were sent.
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying the messages to select. Available parameters are:
	 *	- \c parliament specifies to select only the messages addressed (also) to a member of this parliament(s). It contains parliament codes separated by | character.
	 *	- \c mp_id specifies to select only the messages addressed to this MP
	 *	- \c since specifies to select only the messages sent since (including) this date and time
	 *	- \c until specifies to select only the messages sent until (excluding) this date and time
	 *	- \c text specifies to apply fulltext search of \c text in messages and replies (in both subject and title)
	 *	- \c sender specifies to apply fulltext search of \c sender in message senders
	 *	- \c _limit, \c _offset restrict the result to return at most \c _limit records skipping the first \c _offset records.
	 *
	 * \return Basic information on sent public messages.
	 *
	 * \ex
	 * \code
	 * read(array('mp_id' => 809))
	 * \endcode returns something like
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 1262
	 *             [subject] => My law proposal
	 *             [body] => Dear Mr. ...
	 *             [sender_name] => Bilbo Baggins
	 *             [sent_on] => 2011-09-06 09:09:35.00988
	 *             [age_days] => 2.29212016417748
	 *             [recipients] => Gajdůšková A.
	 *             [reply_exists] => yes
	 *         )

	 * )
	 * \endcode
	 */
	public function read($params)
	{
		$query = new Query();
		$query->setQuery(
			"select\n" .
			"	id, subject,\n" .
			"	substr(\"body\", 1, 200) || case when length(\"body\") > 200 then '...' else '' end as \"body\",\n" .
			"	sender_name, sent_on,\n" .
			"	(extract('epoch' from now()) - extract('epoch' from sent_on)) / 86400 as age_days,\n" .
			"	recipients, reply_exists\n" .
			"from\n" .
			"	message as m\n" .
			"	join (\n" .
			"		select\n" .
			"			message_id,\n" .
			"			string_agg(mp.last_name || ' ' || substr(mp.first_name, 1, 1) || '.' || case when length(mp.middle_names) > 0 then substr(mp.middle_names, 1, 1) || '. ' else '' end, ', ' order by mp_id) as recipients,\n" .
			"			string_agg(case when r.reply_code is not null then 'yes' else 'no' end, ', ' order by mp_id) as reply_exists\n" .
			"		from\n" .
			"			message_to_mp as mtm\n" .
			"			join mp on mp.id = mtm.mp_id\n" .
			"			left join (select distinct reply_code from reply) as r on r.reply_code = mtm.reply_code\n" .
			"		where\n" .
			"			true\n"
		);

		// filter messages for only the ones addressed to a particular parliaments or MP
		$n = 0;
		if (isset($params['parliament']))
		{
			$query->appendQuery('			and mtm.parliament_code = any($' . ++$n . ")\n");
			$query->appendParam(Db::arrayOfStringsArgument(explode('|', $params['parliament'])));
		}
		if (isset($params['mp_id']))
		{
			$query->appendQuery('			and mp.id = $' . ++$n . "\n");
			$query->appendParam($params['mp_id']);
		}

		$query->appendQuery(
			"		group by\n" .
			"			message_id\n" .
			"	) as replies\n" .
			"	on replies.message_id = m.id\n" .
			"where\n" .
			"	\"state\" = 'sent'\n" .
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

		// filter messages by fulltext search
		if (isset($params['text']))
		{
			$query->appendQuery("	and text_data @@ to_tsquery('simple', $" . ++$n . ")\n");
			$query->appendParam(self::makeTsQuery($params['text']));
		}
		if (isset($params['sender']))
		{
			$query->appendQuery("	and sender_data @@ to_tsquery('simple', $" . ++$n . ")\n");
			$query->appendParam(self::makeTsQuery($params['sender']));
		}

		$query->appendQuery("order by\n	sent_on desc\n");

		// restrict the output by _limit and _offset
		if (isset($params['_limit']))
		{
			$query->appendQuery('limit $' . ++$n . ' ');
			$query->appendParam($params['_limit']);
		}
		if (isset($params['_offset']))
		{
			$query->appendQuery('offset $' . ++$n);
			$query->appendParam($params['_offset']);
		}

		return $query->execute();
	}

	private static function makeTsQuery($text)
	{
		$res = strtolower(Utils::unaccent($text));
		$res = preg_replace('/\W+/', ' ', $res);
		$res = preg_replace('/(\S)\s/', '$1:* ', $res);
		$res = preg_replace('/(\S)\s+(\S)/', '$1 & $2', $res);
		return $res;
	}
}

?>
