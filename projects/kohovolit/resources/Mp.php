<?php

/**
 * Class Mp provides information about MPs through API and implements CRUD operations on database table MP.
 *
 * Columns of table MP are: <em>id, first_name, middle_names, last_name, disambiguation, sex, pre_title, post_title, born_on, died_on, last_updated_on</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */
class Mp
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity(
			'mp',
			array('id', 'first_name', 'middle_names', 'last_name', 'disambiguation', 'sex', 'pre_title', 'post_title', 'born_on', 'died_on', 'last_updated_on'),
			'id',
			array('id')
		);
	}

	/**
	 * Read MP(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the MPs to select. Only MPs satisfying all prescribed column values are returned.
	 *
	 * \return An array of MPs with structure <code>array('mp' => array(array('id' => 32, 'first_name' => 'Balin', ...), array('id' => 10, 'first_name' => 'Dvalin', ...), ...))</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params);
	}

	/**
	 * Create MP(s) with given values.
	 *
	 * \param $data An array of MPs to create, where each MP is given by array of pairs <em>column => value</em>. Eg. <code>array(array('first_name' => 'Bilbo', 'last_name' = 'Baggins', ...), ...)</code>.
	 *
	 * \return An array of \e id-s of created MPs.
	 */
	public static function create($data)
	{
		return self::$entity->create($data);
	}

	/**
	 * Update MP(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the MPs to update. Only MPs satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected MP.
	 *
	 * \return An array of \e id-s of updated MPs.
	 */
	public static function update($params, $data)
	{
		return self::$entity->update($params, $data);
	}

	/**
	 * Delete MP(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the MPs to delete. Only MPs satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e id-s of deleted MPs.
	 */
	public static function delete($params)
	{
		return self::$entity->delete($params);
	}
}

Mp::init();

?>
