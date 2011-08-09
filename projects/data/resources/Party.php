<?php

/**
 * Class Party provides information about parties through API and implements CRUD operations on database table PARTY.
 *
 * Columns of table PARTY are: <em>id, name_, short_name, description, country_code, last_updated_on</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */
class Party
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity(
			'party',
			array('id', 'name_', 'short_name', 'description', 'country_code', 'last_updated_on'),
			array('id'),
			array('id')
		);
	}

	/**
	 * Read party(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parties to select. Only parties satisfying all prescribed column values are returned.
	 *
	 * \return An array of parties with structure <code>array(array('id' => 8, 'name_' => 'The Labour Party', 'short_name' => 'Lab', ...), ...)</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params);
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
		return self::$entity->create($data);
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
		return self::$entity->update($params, $data);
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
		return self::$entity->delete($params);
	}
}

Party::init();

?>
