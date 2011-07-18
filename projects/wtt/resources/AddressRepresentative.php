<?php

/**
 * Class AddressRepresentative implements search of MPs that are representatives to a given address.
 *
 * Only the \e read method is present.
 */
class AddressRepresentative
{
	/**
	 * Search for all MPs that are representatives to the address given in Google Maps API structure.
	 *
	 * \param $params An array of pairs <em>address_field => value</em> specifying the address. Available address fields are:
	 * <em>latitude, longitude, country, administrative_area_level_1, administrative_area_level_2, administrative_area_level_3,
	 * locality, sublocality, neighborhood, route, street_number</em>. Any fields can be ommitted.
	 *
	 * \return An array of MPs structured by parliament name and political group.
	 */
	public static function read($params)
	{
		// get the list of representatives from database
		$query = new Query();
		$query->setQuery('select * from address_representative($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)');
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
