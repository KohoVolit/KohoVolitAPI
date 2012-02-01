<?php

/**
 * \ingroup napistejim
 *
 * Lists statistics of MPs about received messages and sent replies.
 */
class MpStatistics
{
	/**
	 * Lists statistics of MPs about received messages and sent replies ordered by numeber of received public messages.
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying the messages to select. Available parameters are:
	 *	- \c parliament_code specifies that only the messages addressed (also) to an MP as a member of this parliament should be counted. In that case also MP's political group is returned.
	 *	- \c country specifies to count only the messages in the given country. It contains country code.
	 *	- \c mp specifies a vertical bar-separated list of ids of MPs' to select
	 *	- \c _limit, \c _offset restrict the result to return at most \c _limit records skipping the first \c _offset records.
	 *
	 * \return The statistics in an array.
	 *
	 * \ex
	 * \code
	 * read(array('parliament_code' => 'cz/starostove'))
	 * \endcode returns something like
	 * \code
	 * Array
	 * (
	 *    [0] => Array
	 *        (
	 *            [first_name] => Roman
	 *            [middle_names] =>
	 *            [last_name] => Onderka
	 *            [disambiguation] =>
	 *            [political_group] =>
	 *            [id] => 950
	 *            [received_public_messages] => 2
	 *            [received_private_messages] => 0
	 *            [sent_public_replies] => 0
	 *            [replied_public_messages] => 0
	 *            [average_days_for_reply] => 5.8019913429591
	 *        )
	 *    ...
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		$n = 0;
		if (isset($params['parliament_code']) && !empty($params['parliament_code']))
			$parl_index = ++$n;
		if (isset($params['country']) && !empty($params['country']))
			$country_index = ++$n;

		$query = new Query();
		$query->setQuery(
			"select\n" .
			"	mp.first_name, mp.middle_names, mp.last_name, mp.disambiguation,\n"
		);
		if (isset($parl_index))
		{
			$query->appendQuery("	coalesce(pg.short_name, pg.\"name\") as political_group,\n");
			$query->appendParam($params['parliament_code']);
		}
		$query->appendQuery(
			"	stats.*\n" .
			"from\n" .
			"	mp\n" .
			"	join\n" .
			"	(\n" .
			"		select\n" .
			"			mp.id,\n" .
			"			sum(case m.is_public when 'yes' then 1 else 0 end) as received_public_messages,\n" .
			"			sum(case m.is_public when 'no' then 1 else 0 end) as received_private_messages,\n" .
			"			coalesce(sum(r.count), 0) as sent_public_replies,\n" .
			"			sum(case when m.is_public = 'yes' and r.count > 0 then 1 else 0 end) as replied_public_messages,\n" .
			"			avg(extract(epoch from r.received_on - m.sent_on)) / 86400 as average_days_to_reply\n" .
			"		from\n" .
			"			mp\n" .
			"			join message_to_mp as mtm on mtm.mp_id = mp.id" . (isset($parl_index) ? ' and parliament_code = $' . $parl_index : '') . "\n"
		);

		// filter messages to a given country
		if (isset($country_index))
		{
			$query->appendQuery('			join parliament as p on p.code = mtm.parliament_code and p.country_code = $' . $country_index . "\n");
			$query->appendParam($params['country']);
		}

		$query->appendQuery(
			"			join message as m on m.id = mtm.message_id and m.state = 'sent'\n" .
			"			left join (select reply_code, count(*), min(received_on) as received_on from reply group by reply_code) as r on r.reply_code = mtm.reply_code\n"
		);

		// filter MPs to the given ones
		if (isset($params['mp']) && !empty($params['mp']))
		{
			$mps = explode('|', $params['mp']);
			$query->appendQuery(
				"		where\n" .
				"			mp.id = any ($" . ++$n . ")\n"
			);
			$query->appendParam(Db::arrayOfIntegersArgument($mps));
		}

		$query->appendQuery(
			"		group by\n" .
			"			mp.id\n" .
			"	) as stats on stats.id = mp.id\n"
		);

		if (isset($parl_index))
			// retreive current political group of the MPs in this parliament
			$query->appendQuery(
				"	left join\n" .
				"	(\n" .
				"		select\n" .
				"			mp_id, g.\"name\", g.short_name\n" .
				"		from\n" .
				"			mp_in_group as mig\n" .
				"			join \"group\" as g on g.id = mig.group_id and g.group_kind_code = 'political group' and g.parliament_code = $" . $parl_index . "\n" .
				"			join term as t on t.id = g.term_id and t.since <= 'now' and t.until > 'now'\n" .
				"		where\n" .
				"			mig.since <= 'now' and mig.until > 'now' and mig.role_code = 'member'\n" .
				"	) as pg on pg.mp_id = mp.id\n"
			);

		// show only the ones with public messages and sort the result
		$query->appendQuery(
			"	where received_public_messages > 0\n" .
			"	order by received_public_messages desc, received_private_messages desc, replied_public_messages desc, sent_public_replies desc, mp.id\n"
		);

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
}

?>
