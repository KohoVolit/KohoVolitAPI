<?php

/**
 * \ingroup napistejim
 *
 * Finds MPs that are elected in a given parliament, constituency and/or that are members of all given groups at the given moment.
 */
class FindMps
{
	/**
	 * Searches for MPs that are elected in a given parliament, constituency and/or that are members of all given groups at the given moment.
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying MPs to select. Available parameters are:
	 * - \c parliament_code specifying parliament code
	 * - \c constituency_id specifying constituency id
	 * - \c groups specifying a vertical bar-separated list of groups' ids
	 * - \c _datetime specifying date and time of the memeberships. If ommitted the current moment is supposed.
	 *
	 * At least one of the \c parliament_code, \c constituency_id or \c groups must be specified.
	 *
	 * \return An array of MP names.
	 *
	 * \ex
	 * \code
	 * read(array('constituency_id' => 10, 'groups' => '576|592'))
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
		$parliament_code = isset($params['parliament_code']) ? $params['parliament_code'] : null;
		$constituency_id = isset($params['constituency_id']) ? $params['constituency_id'] : null;
		$groups = isset($params['groups']) && !empty($params['groups']) ? explode('|', $params['groups']) : null;
		$datetime = isset($params['_datetime']) ? $params['_datetime'] : 'now';

		// at least one criterion must be specified
		if (empty($parliament_code) && empty($constituency_id) && empty($groups))
			throw new Exception('At least criterion (parliament code, constituency id or groups) must be specified to find MPs for.', 400);

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
			$query->appendParam(Db::arrayOfIntegersArgument($groups));
			$query->appendParam(count($groups));
		}
		if (!empty($constituency_id))
		{
			$query->appendQuery(
			"	join (\n" .
			"		select distinct mp_id from mp_in_group\n" .
			"		where since <= $1 and until > $1 and constituency_id = $" . ($query->getParamsCount() + 1) . "\n" .
			"	) as const_mp on const_mp.mp_id = mp.id"
			);
			$query->appendParam($constituency_id);
		}
		if (!empty($parliament_code))
		{
			$query->appendQuery(
			"	join (\n" .
			"		select distinct mp_id\n" .
			"		from mp_in_group as mig\n" .
			"			join \"group\" as g on g.id = mig.group_id\n" .
			"		where mig.since <= $1 and mig.until > $1 and g.group_kind_code = 'parliament' and g.parliament_code = $" . ($query->getParamsCount() + 1) . "\n" .
			"	) as parl_mp on parl_mp.mp_id = mp.id"
			);
			$query->appendParam($parliament_code);
		}

		// execute the query
		return $query->execute();
	}
}

?>
