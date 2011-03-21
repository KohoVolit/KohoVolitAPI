<?php

/**
 * Class Party provides information about parties through API and implements CRUD operations on database table PARTY.
 *
 * Columns of table PARTY are: <em>id, name_, short_name, description, country_code</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */
class Party extends Entity
{
	/**
	 * Initialize list of column names of the table and which of them are read only (automatically generated on creation).
	 */
	public static function initColumnNames()
	{
		self::$tableColumns = array('id', 'name_', 'short_name', 'description', 'country_code');
		self::$roColumns = array('id');
	}

	/**
	 * Read party(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parties to select. Only parties satisfying all prescribed column values are returned.
	 *
	 * \return An array of parties with structure <code>array('party' => array(array('id' => 8, 'name_' => 'The Labour Party', 'short_name' => 'Lab', ...), ...))</code>.
	 */
	public static function read($params)
	{
		return parent::readEntity($params, 'party');
	}

	/**
	 * Create party(s) with given values.
	 *
	 * \param $data An array of parties to create, where each party is given by array of pairs <em>column => value</em>. Eg. <code>array(array('name_' => 'The Labour Party', 'short_name' => 'Lab', ...), ...)</code>.
	 *
	 * \return An array of \e id-s of created parties.
	 */
	public static function create($data)
	{
		return parent::createEntity($data, 'party', 'id');
	}

	/**
	 * Update party(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parties to update. Only parties satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected party.
	 *
	 * \return An array of \e id-s of updated parties.
	 */
	public static function update($params, $data)
	{
		return parent::updateEntity($params, $data, 'party', 'id');
	}

	/**
	 * Delete party(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parties to delete. Only parties satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e id-s of deleted parties.
	 */
	public static function delete($params)
	{
		return parent::deleteEntity($params, 'party', 'id');
	}
}

Party::initColumnNames();

?>
