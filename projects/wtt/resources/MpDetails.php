<?php

/**
 * Class MpDetails provides detailed information about the given MPs to be used as addressees.
 *
 * Only the \e read method is present.
 */
class MpDetails
{
	/**
	 * Returns details about given MPs and their attributes useful to show them as addressees.
	 *
	 * \param $params An array with one pair \c 'mp' => \e mp_list specifying the MPs to select.
	 * The \e mp_list is a vertical-bar-separated list of MPs' ids with prepended parliament code to select the attributes (e.g. email, image) for.
	 * Example: <code>array('mp' => 'cz/psp/64|cz/senat/123|cz/psp/17')</code>.
	 *
	 * \return Details about the given MPs in an array ordered the same way as the input list.
	 */
	public static function read($params)
	{
		if (!isset($params['mp']))
			return array();

		// get the MPs' details from database
		$query = new Query();
		$query->setQuery(
			"select id, first_name, middle_names, last_name, disambiguation, mpa.name_ as attr_name, mpa.value_ as attr_value, p.code as parliament_code, p.name_ as parliament_name from mp\n" .
			"left join mp_attribute as mpa on mpa.mp_id = mp.id and mpa.since <= 'now' and mpa.until > 'now'\n" .
			"left join parliament as p on p.code = mpa.parl\n" .
			"where false\n"
		);
		$mps = explode('|', $params['mp']);
		$n = $i = 0;
		foreach ((array)$mps as $mp)
		{
			$p = strrpos($mp, '/');
			if ($p === false) continue;
			$query->appendQuery('or mp_id = $' . ++$n . ' and parl = $' . ++$n . "\n");
			$query->appendParam(substr($mp, $p + 1));
			$query->appendParam(substr($mp, 0, $p));
			$id_to_order[substr($mp, $p + 1)] = $i++;
		}
		$rows = $query->execute();

		// include settings of kohovolit project API to get a path to kohovolit data (DATA_DIR)
		require_once API_ROOT . '/projects/kohovolit/config/settings.php';

		// aggregate each MP's attributes to one row, rows in the same order as MPs in the input list
		$prev_id = null;
		$mp_details = null;
		foreach ($rows as $row)
		{
			$i = $id_to_order[$row['id']];
			if ($row['id'] != $prev_id)
			{
				$mp_details[$i] = $row;
				unset($mp_details[$i]['attr_name'], $mp_details[$i]['attr_value']);
			}
			$mp_details[$i][$row['attr_name']] = $row['attr_value'];
			if ($row['attr_name'] == 'image')
				$mp_details[$i]['image'] = $row['parliament_code'] . '/images/mp/' . $row['attr_value'];
			$prev_id = $row['id'];
		}
		if (!empty($mp_details))
			ksort($mp_details);

		return $mp_details;
	}
}

?>
