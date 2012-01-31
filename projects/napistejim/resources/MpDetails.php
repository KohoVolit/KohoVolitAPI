<?php

/**
 * \ingroup napistejim
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
	 * - \c lang [optional] specifying language code to return details in
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
	 *             [sex] => m
	 *             [parliament_code] => cz/psp
	 *             [parliament] => Sněmovna
	 *             [political_group] => ODS
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
	 *             [sex] => m
	 *             [parliament_code] => cz/senat
	 *             [parliament] => Senát
	 *             [political_group] => ČSSD
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
			"select\n" .
			"	mp.id, first_name, middle_names, last_name, disambiguation, sex,\n" .
			"	mpa.\"name\" as attr_name, mpa.\"value\" as attr_value,\n" .
			"	p.code as parliament_code, coalesce(coalesce(pa_sn.\"value\", p.short_name), coalesce(pa_n.\"value\", p.\"name\")) as parliament,\n" .
			"	coalesce(coalesce(ga_sn.\"value\", g.short_name), coalesce(ga_n.\"value\", g.\"name\")) as political_group\n" .
			"from\n" .
			"	mp\n" .
			"	left join mp_attribute as mpa on mpa.mp_id = mp.id and mpa.since <= 'now' and mpa.until > 'now'\n" .
			"	left join parliament as p on p.code = mpa.parl\n" .
			"	left join mp_in_group as mig on mig.mp_id = mp.id and mig.role_code = 'member' and mig.since <= 'now' and mig.until > 'now'\n" .
			"		and mig.group_id in (select id from \"group\" where group_kind_code = 'political group' and parliament_code = p.code)\n" .
			"	left join \"group\" as g on g.id = mig.group_id\n" .
			"	left join group_attribute as ga_n on ga_n.group_id = g.id and ga_n.\"name\" = 'name' and ga_n.lang = $1 and ga_n.since <= 'now' and ga_n.until > 'now'\n" .
			"	left join group_attribute as ga_sn on ga_sn.group_id = g.id and ga_sn.\"name\" = 'short_name' and ga_sn.lang = $1 and ga_sn.since <= 'now' and ga_sn.until > 'now'\n" .
			"	left join parliament_attribute as pa_n on pa_n.parliament_code = p.code and pa_n.\"name\" = 'name' and pa_n.lang = $1 and pa_n.since <= 'now' and pa_n.until > 'now'\n" .
			"	left join parliament_attribute as pa_sn on pa_sn.parliament_code = p.code and pa_sn.\"name\" = 'short_name' and pa_sn.lang = $1 and pa_sn.since <= 'now' and pa_sn.until > 'now'\n" .
			"where\n" .
			"	false\n"
		);
		$query->appendParam(isset($params['lang']) && !empty($params['lang']) ? $params['lang'] : null);
		$mps = explode('|', $params['mp']);
		$n = 1;
		$i = 0;
		foreach ((array)$mps as $mp)
		{
			$p = strrpos($mp, '/');
			if ($p === false) continue;
			$query->appendQuery('	or mp.id = $' . ++$n . ' and mpa.parl = $' . ++$n . "\n");
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
