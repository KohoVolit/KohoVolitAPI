<?php

/**
 * Class Group provides information about groups of MPs (eg. committees, commissions, etc.) through API and implements CRUD operations on database table GROUP.
 *
 * Columns of table GROUP are: <em>id, name_, short_name, group_kind_code, term_id, constituency_id, parliament_code, subgroup_of</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */
class Group
{
	/// columns of the table GROUP
	private static $tableColumns = array('id', 'name_', 'short_name', 'group_kind_code', 'term_id', 'constituency_id', 'parliament_code', 'subgroup_of');	

	/// read-only columns
	private static $roColumns = array('id');

	/**
	 * Retrieve group(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the groups to select. Only groups satisfying all prescribed column values are returned.
	 *
	 * \return An array of groups with structure <code>array('group' => array(array('id' => 6, 'name_' => 'Committee on Environment', 'short_name' => 'ENV', 'group_kind_code' => 'committee', ...), ...))</code>.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('group', '*', $params, self::$tableColumns);
		$groups = $query->execute();
		return array('group' => $groups);
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
		$query = new Query('kv_admin');
		$ids = array();
		$query->startTransaction();		
		foreach ((array)$data as $group)
		{
			$query->buildInsert('group', $group, 'id', self::$tableColumns, self::$roColumns);
			$res = $query->execute();
			$ids[] = $res[0]['id'];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return $ids;
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
		$query = new Query('kv_admin');
		$query->buildUpdate('group', $params, $data, 'id', self::$tableColumns, self::$roColumns);
		$res = $query->execute();
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line['id'];
		return $ids;
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
		$query = new Query('kv_admin');
		$query->buildDelete('group', $params, 'id', self::$tableColumns);
		$res = $query->execute();
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line['id'];
		return $ids;
	}
}

?>
