<?php

/**
 * Class FindMp provides implements search of MPs that are members of all given groups.
 *
 * Only the \e read method is present.
 */
class FindMp
{
	/**
	 * Find all MPs that are elected for a given constituency and/or that are members of all given groups at this moment.
	 *
	 * \param $params An array of pairs \c 'constituency' => \e constituency_id and/or \c 'groups' => \e group_id_list specifying the MPs to select.
	 * The \e group_id_list is a comma-separated list of groups' ids.
	 * Example: <code>array('constituency' => 12, 'groups' => '64, 53')</code>.
	 *
	 * \return An array of MP names.
	 */
	public static function read($params)
	{
		$groups = isset($params['groups']) ? $params['groups'] : null;
		$constituency = isset($params['constituency']) ? $params['constituency'] : null;

		// build a query to search for MPs
		$query = new Query();
		$query->setQuery(
			"select mp.id, mp.first_name, mp.last_name, mp.disambiguation\n" .
			"from mp\n"
		);
		if (!empty($groups))
		{
			$query->appendQuery(
				"	join (\n" .
				"		select mp_id from mp_in_group\n" .
				"		where role_code = 'member' and since <= 'now' and until > 'now' and group_id = any ($1)\n" .
				"		group by mp_id\n" .
				"		having count(*) = $2\n" .
				"	) as all_groups_mp on all_groups_mp.mp_id = mp.id\n"
			);
			$query->appendParam('{' . implode(', ', $groups) . '}');
			$query->appendParam(count($groups));
		}
		if (!empty($constituency))
		{
			$query->appendQuery(
			"	join (\n" .
			"		select distinct mp_id from mp_in_group\n" .
			"		where since <= 'now' and until > 'now' and constituency_id = $" . ($query->getParamsCount() + 1) . "\n" .
			"	) as const_mp on const_mp.mp_id = mp.id"
			);
			$query->appendParam($constituency);
		}

		// execute the query
		return $query->execute();
	}
}

?>
