<?php

/**
 * Class Parliament provides information about parliaments through API and implements CRUD operations on database table PARLIAMENT.
 *
 * Columns of table PARLIAMENT are: <em>code, name_, short_name, description, parliament_kind_code, country_code, default_language</em>. All columns are allowed to write to.
 */
class Parliament extends Entity
{
	/**
	 * Initialize list of column names of the table and which of them are read only (automatically generated on creation).
	 */
	public static function initColumnNames()
	{
		self::$tableColumns = array('code', 'name_', 'short_name', 'description', 'parliament_kind_code', 'country_code', 'default_language');
		self::$roColumns = array();
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
		return parent::readEntity($params, 'parliament');
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
		return parent::createEntity($data, 'parliament', 'code');
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
		return parent::updateEntity($params, $data, 'parliament', 'code');
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
		return parent::deleteEntity($params, 'parliament', 'code');
	}
}

Parliament::initColumnNames();

?>
