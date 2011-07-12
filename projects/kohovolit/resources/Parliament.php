<?php

/**
 * Class Parliament provides information about parliaments through API and implements CRUD operations on database table PARLIAMENT.
 *
 * Columns of table PARLIAMENT are: <em>code, name_, short_name, description, parliament_kind_code, country_code, default_language, last_updated_on</em>. All columns are allowed to write to.
 */
class Parliament
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity(
			'parliament',
			array('code', 'name_', 'short_name', 'description', 'parliament_kind_code', 'country_code', 'default_language', 'last_updated_on'),
			'code'
		);
	}

	/**
	 * Read parliament(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliaments to select. Only parliaments satisfying all prescribed column values are returned.
	 *
	 * \return An array of parliaments with structure <code>array('parliament' => array(array('code' => 'sk/nrsr', 'name_' => 'Národná rada Slovenskej Republiky', 'short_name' => 'NRSR', null, 'national-lower', ...), ...))</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params);
	}

	/**
	 * Create parliament(s) with given values.
	 *
	 * \param $data An array of parliaments to create, where each parliament is given by array of pairs <em>column => value</em>. Eg. <code>array(array('name_' => 'Praha 9', 'short_name' => '9', null, 'cz/praha'), ...)</code>.
	 *
	 * \return An array of \e code-s of created parliaments.
	 */
	public static function create($data)
	{
		return self::$entity->create($data);
	}

	/**
	 * Update parliament(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliaments to update. Only parliaments satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected parliament.
	 *
	 * \return An array of \e code-s of updated parliaments.
	 */
	public static function update($params, $data)
	{
		return self::$entity->update($params, $data);
	}

	/**
	 * Delete parliament(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliaments to delete. Only parliaments satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e code-s of deleted parliaments.
	 */
	public static function delete($params)
	{
		return self::$entity->delete($params);
	}
}

Parliament::init();

?>
