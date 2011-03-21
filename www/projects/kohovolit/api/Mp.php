<?php

/**
 * Class Mp provides information about MPs through API and implements CRUD operations on database table MP.
 *
 * Columns of table MP are: <em>id, first_name, middle_names, last_name, disambiguation, sex, pre_title, post_title, born_on, died_on, email, webpage, address, phone</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */
class Mp extends Entity
{
	/**
	 * Initialize list of column names of the table and which of them are read only (automatically generated on creation).
	 */
	public static function initColumnNames()
	{
		self::$tableColumns = array('id', 'first_name', 'middle_names', 'last_name', 'disambiguation', 'sex', 'pre_title', 'post_title', 'born_on', 'died_on', 'email', 'webpage', 'address', 'phone');
		self::$roColumns = array('id');
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
		return parent::readEntity($params, 'mp');
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
		return parent::createEntity($data, 'mp', 'id');
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
		return parent::updateEntity($params, $data, 'mp', 'id');
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
		return parent::deleteEntity($params, 'mp', 'id');
	}
}

Mp::initColumnNames();

?>
