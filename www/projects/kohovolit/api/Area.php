<?php

/**
 * Class Area provides information about constituencies' areas through API and implements CRUD operations on database table AREA.
 *
 * Columns of table AREA are: <em>constituency_id, country, administrative_area_level_1, administrative_area_level_2, administrative_area_level_3, locality, sublocality, neigborhood, route, street_number</em>. All columns are allowed to write to.
 */
class Area extends Entity
{
	/**
	 * Initialize list of column names of the table and which of them are read only (automatically generated on creation).
	 */
	public static function initColumnNames()
	{
		self::$tableColumns = array('constituency_id', 'country', 'administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3', 'locality', 'sublocality', 'neigborhood', 'route', 'street_number');
		self::$roColumns = array();
	}

	/**
	 * Read constituency areas(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the areas to select. Only areas satisfying all prescribed column values are returned.
	 *
	 * \return An array of areas with structure <code>array('area' => array(array('constituency_id' => 25, 'country' => 'Middle-earth', 'administrative_area_level_1' => 'Shire', 'administrative_area_level_2' => '*', ...), ...))</code>.
	 */
	public static function read($params)
	{
		return parent::readEntity($params, 'area');
	}

	/**
	 * Create constituency areas(s) with given values.
	 *
	 * \param $data An array of areas to create, where each area is given by array of pairs <em>column => value</em>. Eg. <code>array(array('constituency_id' => 25, 'country' => 'Middle-earth', 'administrative_area_level_1' => 'Shire', 'administrative_area_level_2' => '*', ...), ...)</code>.
	 *
	 * \return Number of created areas.
	 */
	public static function create($data)
	{
		return parent::createEntity($data, 'area');
	}

	/**
	 * Update constituency areas(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the areas to update. Only areas satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected area.
	 *
	 * \return Number of updated areas.
	 */
	public static function update($params, $data)
	{
		return parent::updateEntity($params, $data, 'area');
	}

	/**
	 * Delete constituency areas(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the areas to delete. Only areas satisfying all prescribed column values are deleted.
	 *
	 * \return Number of deleted areas.
	 */
	public static function delete($params)
	{
		return parent::deleteEntity($params, 'area');
	}
}

Area::initColumnNames();

?>
