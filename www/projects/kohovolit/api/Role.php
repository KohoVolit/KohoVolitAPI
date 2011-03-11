<?php

/**
 * Class Role provides information about roles of MP memberships in groups through API and implements CRUD operations on database table ROLE_.
 *
 * Columns of table ROLE_ are: <em>code, male_name, female_name, description</em>. All columns are allowed to write to.
 */
class Role
{
	/// columns of the table ROLE_
	private static $tableColumns = array('code', 'male_name', 'female_name', 'description');

	/**
	 * Retrieve role(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the roles to select. Only roles satisfying all prescribed column values are returned.
	 *
	 * \return An array of roles with structure <code>array('role' => array(array('code' => 'chairman', 'male_name' => 'chairman', 'female_name' => 'chairwoman', 'description' => null), ...))</code>.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('role_', '*', $params, self::$tableColumns);
		$roles = $query->execute();
		return array('role' => $roles);
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
		$query = new Query('kv_admin');
		$codes = array();
		$query->startTransaction();
		foreach ((array)$data as $role)
		{
			$query->buildInsert('role_', $role, 'code', self::$tableColumns);
			$res = $query->execute();
			$codes[] = $res[0]['code'];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return $codes;
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
		$query = new Query('kv_admin');
		$query->buildUpdate('role_', $params, $data, 'code', self::$tableColumns);
		$res = $query->execute();
		$codes = array();
		foreach ((array)$res as $line)
			$codes[] = $line['code'];
		return $codes;
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
		$query = new Query('kv_admin');
		$query->buildDelete('role_', $params, 'code', self::$tableColumns);
		$res = $query->execute();
		$codes = array();
		foreach ((array)$res as $line)
			$codes[] = $line['code'];
		return $codes;
	}
}

?>
