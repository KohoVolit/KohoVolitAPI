<?php

/**
 * \ingroup wtt
 *
 * Provides detailed information about given MPs to be used as addressees.
 */
class MpDetails
{
	/**
	 * Returns details about given MPs and their attributes useful to show them as addressees.
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying an MP(s). Available parameters are:
	 * - \c mp specifying a vertical bar-separated list of MPs' ids with prepended parliament codes for attributes selection (e.g. email, image).
	 *
	 * \return Details about the given MPs in an array ordered the same way as the input list.
	 *
	 * \ex
	 * \code
	 * read(array('mp' => 'cz/psp/717|cz/senat/823'))
	 * \endcode returns something like
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 717
	 *             [first_name] => Jan
	 *             [middle_names] => 
	 *             [last_name] => Pajer
	 *             [disambiguation] => 
	 *             [parliament_code] => cz/psp
	 *             [parliament_name] => Poslanecká sněmovna Parlamentu České republiky
	 *             [source_code] => 5953
	 *             [email] => pajerj@psp.cz
	 *             [assistant] => Mgr. Karin Holečková, Jiří Horník, Mgr. Libor Mojžíš
	 *             [image] => cz/psp/images/mp/5953_6.jpg
	 *         )
	 *
	 *     [1] => Array
	 *         (
	 *             [id] => 823
	 *             [first_name] => Petr
	 *             [middle_names] => 
	 *             [last_name] => Vícha
	 *             [disambiguation] => 
	 *             [parliament_code] => cz/senat
	 *             [parliament_name] => Senát Parlamentu České republiky
	 *             [source_code] => 206
	 *             [email] => vzatkova.vera.senat@mubo.cz, balcarova.lucie.senat@mubo.cz, vichap@senat.cz
	 *             [website] => www.petr-vicha.cz
	 *             [phone] => +420 777 029 121, +420 605 517 545, +420 596 092 292, +420 596 092 101 fax
	 *             [assistant] => Ing. Věra Vzatková, Mgr. Lucie Balcarová, Libuše Michalíková, Dagmar Procházková, Jarmila Světlíková, Vladimír Talaga
	 *             [image] => cz/senat/images/mp/206_8.jpg
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		if (!isset($params['mp']))
			return array();

		// get the MPs' details from database
		$query = new Query();
		$query->setQuery(
			"select id, first_name, middle_names, last_name, disambiguation, mpa.\"name\" as attr_name, mpa.\"value\" as attr_value, p.code as parliament_code, p.\"name\" as parliament_name from mp\n" .
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
		$query->appendQuery('order by id');
		$rows = $query->execute();

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
