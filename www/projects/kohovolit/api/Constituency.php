<?php

/**
 * Class Constituency provides information about constituencies of a parliament through API and implements CRUD operations on database table CONSTITUENCY.
 *
 * Columns of table CONSTITUENCY are: <em>id, name_, short_name, description, parliament_code, since, until</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */
class Constituency extends Entity
{
	/**
	 * Initialize list of column names of the table and which of them are read only (automatically generated on creation).
	 */
	public static function initColumnNames()
	{
		self::$tableColumns = array('id', 'name_', 'short_name', 'description', 'parliament_code', 'since', 'until');
		self::$roColumns = array('id');
	}

	/**
	 * Read constituency(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the constituencies to select. Only constituencies satisfying all prescribed column values are returned.
	 *
	 * \return An array of constituencies with structure <code>array('constituency' => array(array('id' => 123, 'name_' => 'Praha 9', 'short_name' => '9', 'description' => null, 'cz/praha'), ...))</code>.
	 *
	 * You can use <em>datetime</em> within the <em>$params</em> (eg. 'datetime' => '2010-06-30 9:30:00') to select only constituencies valid at the given moment (the ones where <em>since</em> <= datetime < <em>until</em>). Use 'datetime' => 'now' to get constituencies valid at this moment.
	 */
	public static function read($params)
	{
		return parent::readEntity($params, 'constituency', true);
	}

	/**
	 * Create constituency(s) with given values.
	 *
	 * \param $data An array of constituencies to create, where each constituency is given by array of pairs <em>column => value</em>. Eg. <code>array(array('name_' => 'Praha 9', 'short_name' => '9', 'description' => null, 'cz/praha'), ...)</code>.
	 *
	 * \return An array of \e id-s of created constituencies.
	 */
	public static function create($data)
	{
		return parent::createEntity($data, 'constituency', 'id');
	}

	/**
	 * Update constituency(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the constituencies to update. Only constituencies satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected constituency.
	 *
	 * \return An array of \e id-s of updated constituencies.
	 */
	public static function update($params, $data)
	{
		return parent::updateEntity($params, $data, 'constituency', 'id');
	}

	/**
	 * Delete constituency(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the constituencies to delete. Only constituencies satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e id-s of deleted constituencies.
	 */
	public static function delete($params)
	{
		return parent::deleteEntity($params, 'constituency', 'id');
	}
}

Constituency::initColumnNames();

?>
