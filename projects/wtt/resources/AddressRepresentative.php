<?php

/**
 * \ingroup wtt
 *
 * Searches for MPs that are representatives for a given address in a given parliament(s).
 */
class AddressRepresentative
{
	/**
	 * Search for all MPs that are representatives for the address (given in Google Maps API structure) in a given parliament(s).
	 *
	 * \param $params An array of pairs <em>field => value</em> specifying the address and the parliament(s).
	 * Available address fields are: <code>latitude, longitude, country, administrative_area_level_1, administrative_area_level_2, administrative_area_level_3,
	 * locality, sublocality, neighborhood, route, street_number</code>.
	 * Another available fields are:
	 *   - \c parliament restricting the search to given parliament(s). It contains parliament codes separated by | character.
	 *   - \c lang specifying the language to return names in. It contains a language code.
	 * Any of the listed fields can be ommitted.
	 * If the parliament restriction is ommited, the search performs for all parliaments.
	 * If the language specification is ommited or names are not available in the given language, they are returned in default language of the parliament
	 * (additonal attributes are returned empty).
	 *
	 * \return An array of MPs structured by parliament, constituency and political group.
	 *
	 * \ex
	 * \code
	 * read(array('latitude' => 50.183384, 'longitude' => 12.549942, 'country' => 'Česká republika', 'administrative_area_level_1' => 'Karlovarský', 'administrative_area_level_2' => 'Sokolov', lang => 'cs'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [parliament] => Array
	 *         (
	 *             [0] => Array
	 *                 (
	 *                     [code] => cz/psp
	 *                     [name] => Poslanecká sněmovna
	 *                     [short_name] => Sněmovna
	 *                     [description] => Dolní komora parlamentu České republiky.
	 *                     [time_zone] => Europe/Prague
	 *                     [kind] => national-lower
	 *                     [competence] => Projednává a schvaluje návrhy zákonů, státní rozpočet, změny ústavy. Ratifikuje mezinárodní smlouvy. Volí prezidenta. Může vyslovit nedůvěru vládě.
	 *                     [weight] => 2.1
	 *                     [constituency] => Array
	 *                         (
	 *                             [0] => Array
	 *                                 (
	 *                                     [name] => Karlovarský
	 *                                     [short_name] =>
	 *                                     [description] =>
	 *                                     [group] => Array
	 *                                         (
	 *                                             [0] => Array
	 *                                                 (
	 *                                                     [name] => Poslanecký klub Komunistické strany Čech a Moravy
	 *                                                     [short_name] => KSČM
	 *                                                     [logo] => cz/psp/images/group/kscm.gif
	 *                                                     [mp] => Array
	 *                                                         (
	 *                                                             [0] => Array
	 *                                                                 (
	 *                                                                     [id] => 367
	 *                                                                     [first_name] => Pavel
	 *                                                                     [middle_names] =>
	 *                                                                     [last_name] => Hojda
	 *                                                                     [disambiguation] =>
	 *                                                                     [email] => hojda@psp.cz
	 *                                                                     [image] => cz/psp/images/mp/366_6.jpg
	 *                                                                     [additional_info] => Sokolov, 16 km
	 *                                                                 )
	 *
	 *                                                         )
	 *
	 *                                                 )
	 *
	 * 											...
	 *
	 *                                             [4] => Array
	 *                                                 (
	 *                                                     [name] => Poslanecký klub České strany sociálně demokratické
	 *                                                     [short_name] => ČSSD
	 *                                                     [logo] => cz/psp/images/group/cssd.gif
	 *                                                     [mp] => Array
	 *                                                         (
	 *                                                             [0] => Array
	 *                                                                 (
	 *                                                                     [id] => 713
	 *                                                                     [first_name] => Josef
	 *                                                                     [middle_names] =>
	 *                                                                     [last_name] => Novotný
	 *                                                                     [disambiguation] => ml., PSP ČR 2010-, Karlovarský kraj
	 *                                                                     [email] => novotnyj1@psp.cz
	 *                                                                     [image] => cz/psp/images/mp/5991_6.jpg
	 *                                                                     [additional_info] => Sokolov, 7 km
	 *                                                                 )
	 *
	 *                                                         )
	 *
	 *                                                 )
	 *
	 *                                         )
	 *
	 *                                 )
	 *
	 *                         )
	 *
	 *                 )
	 *
	 *             [1] => Array
	 *                 (
	 *                     [code] => cz/senat
	 *                     [name] => Senát
	 *                     [short_name] => Senát
	 *                     [description] => Horní komora parlamentu České republiky.
	 *                     [time_zone] => Europe/Prague
	 *                     [kind] => national-upper
	 *                     [competence] => Projednává a schvaluje návrhy zákonů, změny ústavy a mezinárodní smlouvy přijaté Sněmovnou. Volí prezidenta.
	 *                     [weight] => 2.2
	 *                     [constituency] => Array
	 *                         (
	 *                             [0] => Array
	 *                                 (
	 *                                     [name] => Sokolov (2)
	 *                                     [short_name] => 2
	 *                                     [description] => celý okres Sokolov, jihovýchodní část okresu Cheb, ohraničená obcemi Teplá, Mnichov, Prameny, Mariánské Lázně, Vlkovice, Ovesné Kladruby, jižní část okresu Karlovy Vary, ohraničená na severu obcemi Toužim, Otročín
	 *                                     [group] => Array
	 *                                         (
	 *                                             [0] => Array
	 *                                                 (
	 *                                                     [name] => Senátorský klub Občanské demokratické strany
	 *                                                     [short_name] => ODS
	 *                                                     [logo] => cz/senat/images/group/ods.png
	 *                                                     [mp] => Array
	 *                                                         (
	 *                                                             [0] => Array
	 *                                                                 (
	 *                                                                     [id] => 760
	 *                                                                     [first_name] => Pavel
	 *                                                                     [middle_names] =>
	 *                                                                     [last_name] => Čáslava
	 *                                                                     [disambiguation] =>
	 *                                                                     [email] => caslavap@senat.cz
	 *                                                                     [image] => cz/senat/images/mp/190_8.jpg
	 *                                                                     [additional_info] =>
	 *                                                                 )
	 *
	 *                                                         )
	 *
	 *                                                 )
	 *
	 *                                         )
	 *
	 *                                 )
	 *
	 *                         )
	 *
	 *                 )
	 *
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		// get the list of representatives to the given address
		$query = new Query();
		$query->setQuery('select * from address_representative($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)');
		$query->appendParam(isset($params['parliament']) ? Db::arrayOfStringsArgument(explode('|', $params['parliament'])) : null);
		$query->appendParam(isset($params['lang']) ? $params['lang'] : null);
		$query->appendParam(isset($params['country']) ? $params['country'] : null);
		$query->appendParam(isset($params['administrative_area_level_1']) ? $params['administrative_area_level_1'] : null);
		$query->appendParam(isset($params['administrative_area_level_2']) ? $params['administrative_area_level_2'] : null);
		$query->appendParam(isset($params['administrative_area_level_3']) ? $params['administrative_area_level_3'] : null);
		$query->appendParam(isset($params['locality']) ? $params['locality'] : null);
		$query->appendParam(isset($params['sublocality']) ? $params['sublocality'] : null);
		$query->appendParam(isset($params['neighborhood']) ? $params['neighborhood'] : null);
		$query->appendParam(isset($params['route']) ? $params['route'] : null);
		$query->appendParam(isset($params['street_number']) ? $params['street_number'] : null);
		$reps = $query->execute();

		// distribute the MPs among parliaments and make a MP.id->consituency mapping
		$parliaments = $constituencies = array();
		foreach ($reps as $rep)
		{
			$parliament_code = $rep['parliament_code'];
			$parliaments[$parliament_code]['mp_ids'][] = $rep['mp_id'];
			$constituencies[$rep['mp_id']][$parliament_code] = array('name' => $rep['constituency_name'], 'short_name' => $rep['constituency_short_name'], 'description' => $rep['constituency_description']);
		}

		// get details of all parliaments where a representative has been found
		$api_wtt = new ApiDirect('wtt');
		$parliament_details = $api_wtt->read('ParliamentDetails', array('parliament' => implode('|', array_keys($parliaments)), 'lang' => isset($params['lang']) ? $params['lang'] : null));

		// get info about the MPs using a particular function for each individual parliament and make a structured result
		$result = array();
		foreach($parliament_details as $pd)
		{
			$parliament_code = $pd['code'];
			$result[$parliament_code] = $pd;
			unset($result[$parliament_code]['wtt_repinfo_function']);
			if (empty($pd['wtt_repinfo_function'])) continue;
			$query->setQuery('select * from ' . $pd['wtt_repinfo_function'] . '($1, $2, $3, $4, $5)');
			$query->clearParams();
			$query->appendParam(Db::arrayOfIntegersArgument($parliaments[$parliament_code]['mp_ids']));
			$query->appendParam($parliament_code);
			$query->appendParam(isset($params['lang']) ? $params['lang'] : null);
			$query->appendParam(isset($params['latitude']) ? $params['latitude'] : null);
			$query->appendParam(isset($params['longitude']) ? $params['longitude'] : null);
			$rep_info = $query->execute();

			// add hierarchy of constituencies, political groups and MPs within a parliament into the result
			foreach ($rep_info as $ri)
			{
				$constituency = $constituencies[$ri['id']][$parliament_code];
				$constituency_name = $constituency['name'];
				$result[$parliament_code]['constituency'][$constituency_name]['name'] = $constituency['name'];
				$result[$parliament_code]['constituency'][$constituency_name]['short_name'] = $constituency['short_name'];
				$result[$parliament_code]['constituency'][$constituency_name]['description'] = $constituency['description'];
				if (isset($ri['political_group_name']))
				{
					$political_group_name = $ri['political_group_name'];
					$result[$parliament_code]['constituency'][$constituency_name]['group'][$political_group_name]['name'] = $political_group_name;
					$result[$parliament_code]['constituency'][$constituency_name]['group'][$political_group_name]['short_name'] = $ri['political_group_short_name'];
					$result[$parliament_code]['constituency'][$constituency_name]['group'][$political_group_name]['logo'] = $ri['political_group_logo'];
					unset($ri['political_group_name'], $ri['political_group_short_name'], $ri['political_group_logo']);
					$result[$parliament_code]['constituency'][$constituency_name]['group'][$political_group_name]['mp'][] = $ri;
				}
				else
					$result[$parliament_code]['constituency'][$constituency_name]['mp'][] = $ri;
			}

			// sort political groups by size (by number of found members)
			foreach ($result[$parliament_code]['constituency'] as &$c)
				if (isset($c['group']))
					uasort($c['group'], 'cmp_by_group_size_name');
		}

		// if parliaments to search in are specified explicitly, reorder the result according to the parliament order in the specification
		if (isset($params['parliament']))
		{
			$parliaments_order = explode('|', $params['parliament']);
			foreach ($result as $code => $parliament)
			{
				$order = array_search($code, $parliaments_order);
				$result[$order] = $parliament;
				unset($result[$code]);
			}
			ksort($result);
		}

		// reindex the arrays from names to integer numbers to be covertable to XML format
		foreach ($result as &$parliament)
		{
			$parliament['constituency'] = array_values($parliament['constituency']);
			foreach ($parliament['constituency'] as &$constituency)
				$constituency['group'] = array_values($constituency['group']);
		}

		return array('parliament' => array_values($result));
	}
}

function cmp_by_group_size_name($a, $b)
{
	$ca = count($a['mp']);
	$cb = count($b['mp']);
	return ($ca > $cb) ? -1 : (($ca < $cb) ? 1 : strcoll($a['name'], $b['name']));
}

?>
