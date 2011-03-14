<?php

/**
 * Class Term provides information about parliament terms of office through API and implements CRUD operations on database table TERM.
 *
 * Columns of table TERM are: <em>id, name_, short_name, description, parliament_kind_code, since, until</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */
class Term extends Entity
{
	/**
	 * Initialize list of column names of the table and which of them are read only (automatically generated on creation).
	 */
	public static function initColumnNames()
	{
		self::$tableColumns = array('id', 'name_', 'short_name', 'description', 'parliament_kind_code', 'since', 'until');
		self::$roColumns = array('id');
	}

	/**
	 * Read term(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the terms to select. Only terms satisfying all prescribed column values are returned.
	 *
	 * \return An array of terms with structure <code>array('term' => array(array('id' => 3, 'name_' => '2006-2010', 'short_name' => '6', ...), ...))</code>.
	 */
	public static function read($params)
	{
		return parent::readEntity($params, 'term');
	}

	/**
	 * Create term(s) with given values.
	 *
	 * \param $data An array of terms to create, where each term is given by array of pairs <em>column => value</em>. Eg. <code>array(array('term' => array(array(name_' => '2006-2010', 'short_name' => '6', parliament_kind_code = 'cz/psp', ...), ...)</code>.
	 *
	 * \return An array of \e id-s of created terms.
	 */
	public static function create($data)
	{
		return parent::createEntity($params, 'term', 'id');
	}

	/**
	 * Update term(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the terms to update. Only terms satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected term.
	 *
	 * \return An array of \e id-s of updated terms.
	 */
	public static function update($params, $data)
	{
		return parent::updateEntity($params, $data, 'term', 'id');
	}

	/**
	 * Delete term(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the terms to delete. Only terms satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e id-s of deleted terms.
	 */
	public static function delete($params)
	{
		return parent::deleteEntity($params, 'term', 'id');
	}
}

Term::initColumnNames();

?>
