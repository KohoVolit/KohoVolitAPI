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
	 *	- \c country specifies to select only the messages in the given country. It contains country code.
	 *	- \c mp_id specifies to select only the messages addressed to this MP
	 *	- \c since specifies to select only the messages sent since (including) this date and time
	 *	- \c until specifies to select only the messages sent until (excluding) this date and time
	 *	- \c text specifies to apply fulltext search of \c text in messages and replies (in both subject and title)
	 *	- \c sender specifies to apply fulltext search of \c sender in message senders
	 *	- \c recipient specifies to apply fulltext search of \c recipient in message recipient names (MP full names)
	 *	- \c order specifies sorting of the result, two values are accepted: \c replies to order by date of the latest reply and \c messages (or anything else) to order by by message date
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
	 *             [sender_name] => Bilbo Baggins
	 *             [sent_on] => 2011-09-06 09:09:35.00988
	 *             [last_reply_on] => 2011-09-16 12:20:54.12345
	 *             [recipients] => Array
	 *                 (
	 *                     [0] => Array
	 *                         (
	 *                             [mp_id] => 691
	 *                             [first_name] => Radek
	 *                             [middle_names] =>
	 *                             [last_name] => John
	 *                             [disambiguation] =>
	 *                             [first_reply_on] => 2011-10-10 12:38:13.531474
	 *                         )
	 *
	 *                     ...
	 *                 )
	 *
	 *         )
	 *
	 *     ...
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		$n = 0;
		$query = new Query();
		$query->setQuery(
			"select\n" .
			"	m.id, m.subject, m.sender_name, m.sent_on,\n" .
			"	substring(m.\"body\" from 1 for 160) || case when length(m.\"body\") > 160 then '...' else '' end as \"body\",\n" .
			"	mp_id, first_name, middle_names, last_name, disambiguation, first_reply_on, last_reply_on, rs.\"body\" as reply_body\n" .
			"from\n" .
			"	(\n" .
			"		select\n" .
			"			message_id, mp_id, min(r.received_on) as first_reply_on,\n" . 
			"			min(substring(r.\"body\" from 1 for 160) || case when length(r.\"body\") > 160 then '...' else '' end) as \"body\"\n" .
			"		from\n" .
			"			message_to_mp as mtm\n" .
			"			left join reply as r on r.reply_code = mtm.reply_code\n" .
			"		where\n" .
			"			message_id in\n" .
			"			(\n" .
			"				select\n" .
			"					min(m.id) as id\n" .
			"				from\n" .
			"					message as m\n" .
			"					join message_to_mp as mtm on mtm.message_id = m.id\n"
		);

		// filter messages to a given country
		if (isset($params['country']) && !empty($params['country']))
		{
			$query->appendQuery("					join parliament as p on p.code = mtm.parliament_code and p.country_code = $" . ++$n . "\n");
			$query->appendParam($params['country']);
		}
		$query->appendQuery(
			"					join mp on mp.id = mtm.mp_id\n" .
			"				where\n" .
			"					\"state\" = 'sent'\n" .
			"					and is_public = 'yes'\n"
		);

		// filter messages for only the ones sent since or until a particular date and time
		if (isset($params['since']) && !empty($params['since']))
		{
			$query->appendQuery('					and sent_on >= $' . ++$n . "\n");
			$query->appendParam($params['since']);
		}
		if (isset($params['until']) && !empty($params['until']))
		{
			$query->appendQuery('					and sent_on < $' . ++$n . "\n");
			$query->appendParam($params['until']);
		}

		// filter messages by fulltext search
		if (isset($params['text']) && !empty($params['text']))
		{
			$query->appendQuery("					and text_data @@ to_tsquery('simple', $" . ++$n . ")\n");
			$query->appendParam(Utils::makeTsQuery($params['text']));
		}
		if (isset($params['sender']) && !empty($params['sender']))
		{
			$query->appendQuery("					and sender_data @@ to_tsquery('simple', $" . ++$n . ")\n");
			$query->appendParam(Utils::makeTsQuery($params['sender']));
		}

		// filter messages for only the ones addressed to a particular parliaments or MP(s)
		if (isset($params['parliament']) && !empty($params['parliament']))
		{
			$query->appendQuery('					and mtm.parliament_code = any($' . ++$n . ")\n");
			$query->appendParam(Db::arrayOfStringsArgument(explode('|', $params['parliament'])));
		}
		if (isset($params['mp_id']) && !empty($params['mp_id']))
		{
			$query->appendQuery('					and mp.id = $' . ++$n . "\n");
			$query->appendParam($params['mp_id']);
		}
		if (isset($params['recipient']) && !empty($params['recipient']))
		{
			$query->appendQuery("					and mp.name_data @@ to_tsquery('simple', $" . ++$n . ")\n");
			$query->appendParam(Utils::makeTsQuery($params['recipient']));
		}

		// sort the result
		$order_by = (isset($params['order']) && $params['order'] == 'replies') ? 'last_reply_on' : 'sent_on';
		$query->appendQuery(
			"				group by\n" .
			"					$order_by, sent_on\n" .
			"				order by\n" .
			"					$order_by desc nulls last, sent_on desc nulls last\n"
		);

		// restrict the output by _limit and _offset
		if (isset($params['_limit']) && !empty($params['_limit']))
		{
			$query->appendQuery('				limit $' . ++$n . "\n");
			$query->appendParam($params['_limit']);
		}
		if (isset($params['_offset']) && !empty($params['_offset']))
		{
			$query->appendQuery('				offset $' . ++$n . "\n");
			$query->appendParam($params['_offset']);
		}

		$query->appendQuery(
			"			)\n" .
			"		group by\n" .
			"			message_id, mp_id\n" .
			"	) as rs\n" .
			"	join message as m on m.id = rs.message_id\n" .
			"	join mp on mp.id = rs.mp_id\n" .
			"order by\n" .
			"	$order_by desc nulls last, sent_on desc, first_reply_on, mp.id\n"
		);

		$message_mp = $query->execute();

		// aggregate replies into messages
		$messages = array();
		foreach ($message_mp as $mm)
		{
			$message_id = $mm['id'];
			if (!isset($messages[$message_id]))
				$messages[$message_id] = array(
					'id' => $message_id,
					'subject' => $mm['subject'],
					'body' => $mm['body'],
					'sender_name' => $mm['sender_name'],
					'sent_on' => $mm['sent_on'],
					'last_reply_on' => $mm['last_reply_on']
				);
			unset($mm['id'], $mm['subject'], $mm['body'], $mm['sender_name'], $mm['sent_on'], $mm['last_reply_on']);
			$messages[$message_id]['recipients'][] = $mm;
		}

		return array_values($messages);
	}
}

?>
