<?php

/**
 * Class GroupKind provides information about kinds of groups of MPs (eg. 'committee', 'commission', etc.) through API and implements CRUD operations on database table GROUP_KIND.
 *
 * Columns of table GROUP_KIND are: <em>code, name_, short_name, description, subkind_of</em>. All columns are allowed to write to.
 */
class GroupKind
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity(
			'group_kind',
			array('code', 'name_', 'short_name', 'description', 'subkind_of'),
			'code'
		);
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
		return self::$entity->read($params);
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
		return self::$entity->create($data);
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
		return self::$entity->update($params, $data);
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
		return self::$entity->delete($params);
	}
}

GroupKind::init();

?>
