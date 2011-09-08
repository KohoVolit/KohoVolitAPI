<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table AREA that holds constituencies' areas.
 *
 * Each constituency is composed of one or more areas. Each area is specified by fields of <a href="http://code.google.com/apis/maps/documentation/javascript/">Google Maps API</a>
 * (eg. country, administrative_area_level_1, etc.).
 * A field containing \c * character instead of a geographic name means that any value of this field belongs to the area.
 * A value of the \c neighborhood field in the form <code>~name1,name2,name3</code> means that all values except the listed ones belongs to the area.
 * A constituency is formed by a union of all its areas.
 *
 * Columns of table AREA are: <code>constituency_id, country, administrative_area_level_1, administrative_area_level_2, administrative_area_level_3, locality, sublocality, neighborhood, route, street_number</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of all columns.
 */
class Area
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'area',
			'columns' => array('constituency_id', 'country', 'administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3', 'locality', 'sublocality', 'neighborhood', 'route', 'street_number'),
			'pkey_columns' => array('constituency_id', 'country', 'administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3', 'locality', 'sublocality', 'neighborhood', 'route', 'street_number')
		));
	}

	/**
	 * Read the constituency area(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the areas to select.
	 *
	 * \return An array of areas that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('constituency_id' => 42))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [constituency_id] => 42
	 *             [country] => Česká republika
	 *             [administrative_area_level_1] => Hlavní město Praha
	 *             [administrative_area_level_2] => Hlavní město Praha
	 *             [administrative_area_level_3] => *
	 *             [locality] => Praha
	 *             [sublocality] => Praha 4
	 *             [neighborhood] => ~Hodkovičky,Lhotka
	 *             [route] => *
	 *             [street_number] => *
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		return $this->entity->read($params);
	}

	/**
	 * Create a constituency areas(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the area to create. Alternatively, an array of such area specifications.
	 * All ommited columns will be set to value <code>*</code>.
	 *
	 * \return An array of primary key values of the created area(s).
	 *
	 * \ex
	 * \code
	 * create(array('constituency_id' => 40, 'country' => 'Česká republika', 'administrative_area_level_1' => 'Středočeský', 'administrative_area_level_2' => 'Benešov', 'locality' => 'Červený Újezd'))
	 * \endcode creates a new area and returns
	 * \code
	 * Array
	 * (
	 *     [constituency_id] => 40
	 *     [country] => Česká republika
	 *     [administrative_area_level_1] => Středočeský
	 *     [administrative_area_level_2] => Benešov
	 *     [administrative_area_level_3] => *
	 *     [locality] => Červený Újezd
	 *     [sublocality] => *
	 *     [neighborhood] => *
	 *     [route] => *
	 *     [street_number] => *
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the constituency areas(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the areas to update. Only the areas that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated area.
	 *
	 * \return An array of primary key values of the updated areas.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the constituency areas(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the areas to delete. Only the areas that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted areas.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
