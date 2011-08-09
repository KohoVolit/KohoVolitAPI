<?php

/**
 * Class Country provides information about countries through API and implements CRUD operations on database table COUNTRY.
 *
 * Columns of table COUNTRY are: <em>code, name_, short_name, description</em>. All columns are allowed to write to.
 */
class Country
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity(
			'country',
			array('code', 'name_', 'short_name', 'description'),
			array('code')
		);
	}

	/**
	 * Read country(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the countries to select. Only countries satisfying all prescribed column values are returned.
	 *
	 * \return An array of countries with structure <code>array(array('code' => 'sk', 'name_' => 'Slovak republic', 'short_name' => 'Slovakia', ...), array('code' => 'eu', 'name_' => 'European Union', 'short_name' => 'EU', ...), ...)</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params);
	}

	/**
	 * Create country(s) with given values.
	 *
	 * \param $data An array of countries to create, where each country is given by array of pairs <em>column => value</em>. Eg. <code>array(array('code' => 'sk', 'name_' => 'Slovak republic', 'short_name' => 'Slovakia', ...), ...)</code>.
	 *
	 * \return An array of \e code-s of created countries.
	 */
	public static function create($data)
	{
		return self::$entity->create($data);
	}

	/**
	 * Update country(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the countries to update. Only countries satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected country.
	 *
	 * \return An array of \e code-s of updated countries.
	 */
	public static function update($params, $data)
	{
		return self::$entity->update($params, $data);
	}

	/**
	 * Delete country(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the countries to delete. Only countries satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e code-s of deleted countries.
	 */
	public static function delete($params)
	{
		return self::$entity->delete($params);
	}
}

Country::init();

?>
