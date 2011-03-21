<?php

/**
 * Class ParliamentKind provides information about kinds of parliament through API and implements CRUD operations on database table PARLIAMENT_KIND.
 *
 * Columns of table PARLIAMENT_KIND are: <em>code, name_, short_name, description</em>. All columns are allowed to write to.
 */
class ParliamentKind extends Entity
{
	/**
	 * Initialize list of column names of the table and which of them are read only (automatically generated on creation).
	 */
	public static function initColumnNames()
	{
		self::$tableColumns = array('code', 'name_', 'short_name', 'description');
		self::$roColumns = array();
	}

	/**
	 * Read parliament kind(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliament kinds to select. Only parliament kinds satisfying all prescribed column values are returned.
	 *
	 * \return An array of parliament kinds with structure <code>array('parliament_kind' => array(array('code' => 'regional', 'name_' => 'Regional parliament', 'short_name' => 'Regional', ...), ...))</code>.
	 */
	public static function read($params)
	{
		return parent::readEntity($params, 'parliament_kind');
	}

	/**
	 * Create parliament kind(s) with given values.
	 *
	 * \param $data An array of parliament kinds to create, where each parliament kind is given by array of pairs <em>column => value</em>. Eg. <code>array(array('code' => 'regional', 'name_' => 'Regional parliament', 'short_name' => 'Regional', ...), ...)</code>.
	 *
	 * \return An array of \e code-s of created parliament kinds.
	 */
	public static function create($data)
	{
		return parent::createEntity($data, 'parliament_kind', 'code');
	}

	/**
	 * Update parliament kind(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliament kinds to update. Only parliament kinds satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected parliament kind.
	 *
	 * \return An array of \e code-s of updated parliament kinds.
	 */
	public static function update($params, $data)
	{
		return parent::updateEntity($params, $data, 'parliament_kind', 'code');
	}

	/**
	 * Delete parliament kind(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliament kinds to delete. Only parliament kinds satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e code-s of deleted parliament kinds.
	 */
	public static function delete($params)
	{
		return parent::deleteEntity($params, 'parliament_kind', 'code');
	}
}

ParliamentKind::initColumnNames();

?>
