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
	 * Another available field is \c parliament restricting the search to given parliament(s). It contains parliament codes separated by | character.
	 * Any of the listed fields can be ommitted. If the parliament restriction is ommited, the search performs for all parliaments.
	 *
	 * \return An array of MPs structured by parliament, constituency and political group.
	 *
	 * \ex
	 * \code
	 * read(array('country' => 'Česká republika', 'administrative_area_level_1' => 'Karlovarský', 'administrative_area_level_2' => 'Sokolov'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [parliament] => Array
	 *         (
	 *             [0] => Array
	 *                 (
	 *                     [code] => cz/psp
	 *                     [name] => Poslanecká sněmovna Parlamentu České republiky
	 *                     [constituency] => Array
	 *                         (
	 *                             [0] => Array
	 *                                 (
	 *                                     [name] => Karlovarský
	 *                                     [group] => Array
	 *                                         (
	 *                                             [0] => Array
	 *                                                 (
	 *                                                     [name] => KSČM
	 *                                                     [mp] => Array
	 *                                                         (
	 *                                                             [0] => Array
	 *                                                                 (
	 *                                                                     [parliament_code] => cz/psp
	 *                                                                     [id] => 367
	 *                                                                     [first_name] => Pavel
	 *                                                                     [middle_names] => 
	 *                                                                     [last_name] => Hojda
	 *                                                                     [disambiguation] => 
	 *                                                                     [email] => hojda@psp.cz
	 *                                                                     [office_town] => Sokolov
	 *                                                                     [office_distance] => 
	 *                                                                 )
	 *
	 *                                                         )
	 *
	 *                                                 )
	 *
	 *                                             ...
	 *
	 *                                             [4] => Array
	 *                                                 (
	 *                                                     [name] => ČSSD
	 *                                                     [mp] => Array
	 *                                                         (
	 *                                                             [0] => Array
	 *                                                                 (
	 *                                                                     [parliament_code] => cz/psp
	 *                                                                     [id] => 713
	 *                                                                     [first_name] => Josef
	 *                                                                     [middle_names] => 
	 *                                                                     [last_name] => Novotný
	 *                                                                     [disambiguation] => ml., PSP ČR 2010-, Karlovarský kraj
	 *                                                                     [email] => novotnyj1@psp.cz
	 *                                                                     [office_town] => Karlovy Vary
	 *                                                                     [office_distance] => 
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
	 *                     [name] => Senát Parlamentu České republiky
	 *                     [constituency] => Array
	 *                         (
	 *                             [0] => Array
	 *                                 (
	 *                                     [name] => Sokolov (2)
	 *                                     [group] => Array
	 *                                         (
	 *                                             [0] => Array
	 *                                                 (
	 *                                                     [name] => ODS
	 *                                                     [mp] => Array
	 *                                                         (
	 *                                                             [0] => Array
	 *                                                                 (
	 *                                                                     [parliament_code] => cz/senat
	 *                                                                     [id] => 760
	 *                                                                     [first_name] => Pavel
	 *                                                                     [middle_names] => 
	 *                                                                     [last_name] => Čáslava
	 *                                                                     [disambiguation] => 
	 *                                                                     [email] => caslavap@senat.cz
	 *                                                                     [office_town] => 
	 *                                                                     [office_distance] => 
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
	 *
	 * The \c office_distance is calculated only if \c latitude and \c longitude are specified among the address fields and an office of the MP exists.
	 */
	public function read($params)
	{
		// get the list of representatives from database
		$query = new Query();
		$query->setQuery('select * from address_representative($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)');
		$query->appendParam(isset($params['parliament']) ? $params['parliament'] : null);
		$query->appendParam(isset($params['latitude']) ? $params['latitude'] : null);
		$query->appendParam(isset($params['longitude']) ? $params['longitude'] : null);
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

		// make a hierarchical representation of the list structured by parliament, constituency and political group
		$parliaments = array();
		foreach ($reps as $rep)
		{
			$parliament_code = $rep['parliament_code'];
			$parliaments[$parliament_code]['code'] = $parliament_code;
			$parliaments[$parliament_code]['name'] = $rep['parliament_name'];

			$constituency_name = $rep['constituency_name'];
			$parliaments[$parliament_code]['constituency'][$constituency_name]['name'] = $constituency_name;

			$political_group = $rep['political_group'];
			$mp = $rep;
			unset($mp['parliament_name'], $mp['constituency_name'], $mp['political_group']);
			$parliaments[$parliament_code]['constituency'][$constituency_name]['group'][$political_group]['name'] = $political_group;
			$parliaments[$parliament_code]['constituency'][$constituency_name]['group'][$political_group]['mp'][] = $mp;
		}

		// reindex the arrays from names to integer numbers to be covertable to XML format
		foreach ($parliaments as &$parliament)
		{
			$parliament['constituency'] = array_values($parliament['constituency']);
			foreach ($parliament['constituency'] as &$constituency)
				$constituency['group'] = array_values($constituency['group']);
		}
		return array('parliament' => array_values($parliaments));
	}
}

?>
