<?php

/**
 * Class GroupKind provides information about kinds of groups of MPs (eg. 'committee', 'commission', etc.) through API and implements CRUD operations on database table GROUP_KIND.
 *
 * Columns of table GROUP_KIND are: <em>code, name_, short_name, description, subkind_of</em>. All columns are allowed to write to.
 */
class GroupKind
{
	/// columns of the table GROUP_KIND
	private static $tableColumns = array('code', 'name_', 'short_name', 'description', 'subkind_of');

	/**
	 * Retrieve group kind(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the group kinds to select. Only group kinds satisfying all prescribed column values are returned.
	 *
	 * \return An array of group kinds with structure <code>array('group' => array(array('code' => 'committee', 'name_' => 'Committee', 'short_name' => 'Cmt', ...), ...))</code>.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('group_kind', '*', $params, self::$tableColumns);
		$group_kinds = $query->execute();
		return array('group_kind' => $group_kinds);
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
		$query = new Query('kv_admin');
		$codes = array();
		$query->startTransaction();
		foreach ((array)$data as $group_kind)
		{
			$query->buildInsert('group_kind', $group_kind, 'code', self::$tableColumns);
			$res = $query->execute();
			$codes[] = $res[0]['code'];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return $codes;
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
		$query = new Query('kv_admin');
		$query->buildUpdate('group_kind', $params, $data, 'code', self::$tableColumns);
		$res = $query->execute();
		$codes = array();
		foreach ((array)$res as $line)
			$codes[] = $line['code'];
		return $codes;
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
		$query = new Query('kv_admin');
		$query->buildDelete('group_kind', $params, 'code', self::$tableColumns);
		$res = $query->execute();
		$codes = array();
		foreach ((array)$res as $line)
			$codes[] = $line['code'];
		return $codes;
	}
}

?>
