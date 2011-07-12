<?php

/**
 * Class Role provides information about roles of MP memberships in groups through API and implements CRUD operations on database table ROLE_.
 *
 * Columns of table ROLE_ are: <em>code, male_name, female_name, description</em>. All columns are allowed to write to.
 */
class Role
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity(
			'role_',
			array('code', 'male_name', 'female_name', 'description'),
			'code'
		);
	}

	/**
	 * Read role(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the roles to select. Only roles satisfying all prescribed column values are returned.
	 *
	 * \return An array of roles with structure <code>array('role' => array(array('code' => 'chairman', 'male_name' => 'chairman', 'female_name' => 'chairwoman', 'description' => null), ...))</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params);
	}

	/**
	 * Create role(s) with given values.
	 *
	 * \param $data An array of roles to create, where each role is given by array of pairs <em>column => value</em>. Eg. <code>array(array('code' => 'chairman', 'male_name' => 'chairman', 'female_name' => 'chairwoman', 'description' => null), ...)</code>.
	 *
	 * \return An array of \e code-s of created roles.
	 */
	public static function create($data)
	{
		return self::$entity->create($data);
	}

	/**
	 * Update role(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the roles to update. Only roles satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected role.
	 *
	 * \return An array of \e code-s of updated roles.
	 */
	public static function update($params, $data)
	{
		return self::$entity->update($params, $data);
	}

	/**
	 * Delete role(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the roles to delete. Only roles satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e code-s of deleted roles.
	 */
	public static function delete($params)
	{
		return self::$entity->delete($params);
	}
}

Role::init();

?>
