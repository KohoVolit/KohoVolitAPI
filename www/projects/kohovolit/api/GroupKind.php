<?php

/**
 * Class GroupKind provides information about kinds of groups of MPs (eg. 'committee', 'commission', etc.) through API and implements CRUD operations on database table GROUP_KIND.
 *
 * Columns of table GROUP_KIND are: <em>code, name_, short_name, description, subkind_of</em>. All columns are allowed to write to.
 */
class GroupKind extends Entity
{
	/**
	 * Initialize list of column names of the table and which of them are read only (automatically generated on creation).
	 */
	public static function initColumnNames()
	{
		self::$tableColumns = array('code', 'name_', 'short_name', 'description', 'subkind_of');
		self::$roColumns = array();
	}

	/**
	 * Read group kind(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the group kinds to select. Only group kinds satisfying all prescribed column values are returned.
	 *
	 * \return An array of group kinds with structure <code>array('group' => array(array('code' => 'committee', 'name_' => 'Committee', 'short_name' => 'Cmt', ...), ...))</code>.
	 */
	public static function read($params)
	{
		return parent::readEntity($params, 'group_kind');
	}

	/**
	 * Create group kind(s) with given values.
	 *
	 * \param $data An array of group kinds to create, where each group kind is given by array of pairs <em>column => value</em>. Eg. <code>array(array('code' => 'committee', 'name_' => 'Committee', 'short_name' => 'Cmt', ...), ...)</code>.
	 *
	 * \return An array of \e code-s of created group kinds.
	 */
	public static function create($data)
	{
		return parent::createEntity($params, 'group_kind', 'code');
	}

	/**
	 * Update group kind(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the group kinds to update. Only group kinds satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected group kind.
	 *
	 * \return An array of \e codes-s of updated group kinds.
	 */
	public static function update($params, $data)
	{
		return parent::updateEntity($params, $data, 'group_kind', 'code');
	}

	/**
	 * Delete group kind(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the group kinds to delete. Only group kinds satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e code-s of deleted groups.
	 */
	public static function delete($params)
	{
		return parent::deleteEntity($params, 'group_kind', 'code');
	}
}

GroupKind::initColumnNames();

?>
