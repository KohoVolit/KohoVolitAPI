<?php

/**
 * \ingroup wtt
 *
 * Finds MPs that are elected in a given constituency and/or that are members of all given groups at the given moment.
 */
class FindMp
{
	/**
	 * Searches for MPs that are elected in a given constituency and/or that are members of all given groups at the given moment.
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying MPs to select. Available parameters are:
	 * - \c constituency specifying constituency id
	 * - \c groups specifying a vertical bar-separated list of groups' ids
	 * - \c _datetime specifying date and time of the memeberships. If ommitted the current moment is supposed.
	 *
	 * \return An array of MP names.
	 *
	 * \ex
	 * \code
	 * read(array('constituency' => 10, 'groups' => '576|592'))
	 * \endcode returns something like
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 688
	 *             [first_name] => Luděk
	 *             [middle_names] =>
	 *             [last_name] => Jeništa
	 *             [disambiguation] => 
	 *         )
	 *
	 *     [1] => Array
	 *         (
	 *             [id] => 734
	 *             [first_name] => Jan
	 *             [middle_names] =>
	 *             [last_name] => Smutný
	 *             [disambiguation] => 
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		$groups = isset($params['groups']) ? explode('|', $params['groups']) : null;
		$constituency = isset($params['constituency']) ? $params['constituency'] : null;
		$datetime = isset($params['_datetime']) ? $params['_datetime'] : 'now';

		// build a query to search for MPs
		$query = new Query();
		$query->setQuery(
			"select mp.id, mp.first_name, mp.middle_names, mp.last_name, mp.disambiguation\n" .
			"from mp\n"
		);
		$query->appendParam($datetime);
		if (!empty($groups))
		{
			$query->appendQuery(
				"	join (\n" .
				"		select mp_id from mp_in_group\n" .
				"		where role_code = 'member' and since <= $1 and until > $1 and group_id = any ($2)\n" .
				"		group by mp_id\n" .
				"		having count(*) = $3\n" .
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
			"		where since <= $1 and until > $1 and constituency_id = $" . ($query->getParamsCount() + 1) . "\n" .
			"	) as const_mp on const_mp.mp_id = mp.id"
			);
			$query->appendParam($constituency);
		}

		// execute the query
		return $query->execute();
	}
}

?>
