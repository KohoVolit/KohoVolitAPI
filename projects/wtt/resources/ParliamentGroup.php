<?php

/**
 * \ingroup wtt
 *
 * Lists groups of MPs that exist in a given parliament (ie. political groups, commitees, commissions, etc.).
 */
class ParliamentGroup
{
	/**
	 * Returns groups that exist in a given parliament except the group of kind 'parliament'.
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying the parliament and other information.
	 * Available parameters are:
	 *   - \c parliament_code specifies the parliament code to get groups of
	 *   - \c lang [optional] specifies the language to return names in. It contains a language code.
	 *   - \c subkind_of [optional] specifies a group kind code to get only direct subgroups of
	 * If the language specification is ommited or names are not available in the given language, they are returned in default language of the parliament.
	 * If the subkind is ommiteed all groups of the parliament are returned.
	 *
	 * \return An array of groups structured by group kind code and ordered by weight of the group kind code.
	 *
	 * \ex
	 * \code
	 * read(array('parliament_code' => 'cz/psp', 'lang' => 'cs'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [group_kind] => Array
	 *         (
	 *             [0] => Array
	 *                 (
	 *                     [code] => political group
	 *                     [name] => Poslanecký klub
	 *                     [short_name] => Klub
	 *                     [description] => Poslanecký klub v parlamentu.
	 *                     [group] => Array
	 *                         (
	 *                             [0] => Array
	 *                                 (
	 *                                     [id] => 518
	 *                                     [name] => Poslanecký klub Komunistické strany Čech a Moravy
	 *                                     [short_name] => KSČM
	 *                                 )
	 *
	 * 							...
	 *
	 *                         )
	 *
	 *                 )
	 *
	 * 				...
	 *
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		// get a list of groups of the given parliament
		$query = new Query();
		$query->setQuery('select * from parliament_group($1, $2, $3)');
		$query->appendParam(isset($params['parliament_code']) ? $params['parliament_code'] : null);
		$query->appendParam(isset($params['lang']) ? $params['lang'] : null);
		$query->appendParam(isset($params['subkind_of']) ? $params['subkind_of'] : null);
		$groups = $query->execute();

		// make a hierarchical representation of the list structured by group kind
		foreach ($groups as $group)
		{
			$group_kind_code = $group['group_kind_code'];
			$result[$group_kind_code]['code'] = $group_kind_code;
			$result[$group_kind_code]['name'] = $group['group_kind_name'];
			$result[$group_kind_code]['short_name'] = $group['group_kind_short_name'];
			$result[$group_kind_code]['description'] = $group['group_kind_description'];
			$result[$group_kind_code]['group'][] = array('id' => $group['id'], 'name' => $group['name'], 'short_name' => $group['short_name']);
		}

		// reindex the array from names to integer numbers to be covertable to XML format
		return array('group_kind' => array_values($result));
	}
}

?>
