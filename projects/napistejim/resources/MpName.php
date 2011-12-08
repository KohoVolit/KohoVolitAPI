<?php

/**
 * \ingroup napistejim
 *
 * Lists full names matching the search term(s) of MPs currently in function.
 */
class MpName
{
	/**
	 * Lists full names matching the search term(s) of MPs currently in function.
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying the messages to select. Available parameters are:
	 *	- \c terms specifies space-separated terms to search for in MP names.
	 *
	 * MP name is considered matching the terms if EACH given term matches one of the MP's names (first_name, middle_names, etc.) prefix.
	 * The matching is case and accent insensitive.
	 *
	 * \return List of all names satisfying the given query.
	 *
	 * \ex
	 * \code
	 * read(array('terms' => pa JA))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *    [0] => Array
	 *        (
	 *            [id] => 128
	 *            [first_name] => Jaroslav
	 *            [middle_names] =>
	 *            [last_name] => Palas
	 *            [disambiguation] =>
	 *        )
	 *
	 *    [1] => Array
	 *        (
	 *            [id] => 717
	 *            [first_name] => Jan
	 *            [middle_names] =>
	 *            [last_name] => Pajer
	 *            [disambiguation] =>
	 *        )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		$query = new Query();
		$query->setQuery(
			"select\n" .
			"	mp.id, mp.first_name, mp.middle_names, mp.last_name, mp.disambiguation\n" .
			"from\n" .
			"	mp\n" .
			"	join mp_in_group as mig on mig.mp_id = mp.id and mig.since <= 'now' and mig.until > 'now' and mig.role_code = 'member'\n" .
			"	join \"group\" as g on g.id = mig.group_id and g.group_kind_code = 'parliament'\n" .
			"	join term as t on t.id = g.term_id and t.since <= 'now' and t.until > 'now'\n" .
			"where\n" .
			"	name_data @@ to_tsquery('simple', $1)\n"
		);
		$query->appendParam(Utils::makeTsQuery($params['terms']));
		return $query->execute();
	}
}

?>
