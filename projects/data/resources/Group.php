<?php

/**
 * Class Group provides information about groups of MPs (eg. committees, commissions, etc.) through API and implements CRUD operations on database table GROUP.
 *
 * Columns of table GROUP are: <em>id, name_, short_name, group_kind_code, term_id, parliament_code, subgroup_of, last_updated_on</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */
class Group
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity(
			'group_',
			array('id', 'name_', 'short_name', 'group_kind_code', 'term_id', 'parliament_code', 'subgroup_of', 'last_updated_on'),
			array('id'),
			array('id')
		);
	}

	/**
	 * Read group(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the groups to select. Only groups satisfying all prescribed column values are returned.
	 *
	 * \return An array of groups with structure <code>array(array('id' => 6, 'name_' => 'Committee on Environment', 'short_name' => 'ENV', 'group_kind_code' => 'committee', ...), ...)</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params);
	}

	/**
	 * Create group(s) with given values.
	 *
	 * \param $data An array of groups to create, where each group is given by array of pairs <em>column => value</em>. Eg. <code>array(array('name_' => 'Committee on Environment', 'short_name' => 'ENV', 'group_kind_code' => 'committee', ...), ...)</code>.
	 *
	 * \return An array of \e id-s of created groups.
	 */
	public static function create($data)
	{
		return self::$entity->create($data);
	}

	/**
	 * Update group(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the groups to update. Only groups satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected group.
	 *
	 * \return An array of \e id-s of updated groups.
	 */
	public static function update($params, $data)
	{
		return self::$entity->update($params, $data);
	}

	/**
	 * Delete group(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the groups to delete. Only groups satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e id-s of deleted groups.
	 */
	public static function delete($params)
	{
		return self::$entity->delete($params);
	}
}

Group::init();

?>
