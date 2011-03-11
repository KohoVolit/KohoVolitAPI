<?php

/**
 * Class Party provides information about parties through API and implements CRUD operations on database table PARTY.
 *
 * Columns of table PARTY are: <em>id, name_, short_name, description, country_code</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */
class Party
{
	/// columns of the table PARTY
	private static $tableColumns = array('id', 'name_', 'short_name', 'description', 'country_code');

	/// read-only columns
	private static $roColumns = array('id');

	/**
	 * Retrieve party(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parties to select. Only parties satisfying all prescribed column values are returned.
	 *
	 * \return An array of parties with structure <code>array('party' => array(array('id' => 8, 'name_' => 'The Labour Party', 'short_name' => 'Lab', ...), ...))</code>.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('party', '*', $params, self::$tableColumns);
		$parties = $query->execute();
		return array('party' => $parties);
	}

	/**
	 * Create party(s) with given values.
	 *
	 * \param $data An array of parties to create, where each party is given by array of pairs <em>column => value</em>. Eg. <code>array(array('name_' => 'The Labour Party', 'short_name' => 'Lab', ...), ...)</code>.
	 *
	 * \return An array of \e id-s of created parties.
	 */
	public static function create($data)
	{
		$query = new Query('kv_admin');
		$ids = array();
		$query->startTransaction();
		foreach ((array)$data as $party)
		{
			$query->buildInsert('party', $party, 'id', self::$tableColumns, self::$roColumns);
			$res = $query->execute();
			$ids[] = $res[0]['id'];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return $ids;
	}

	/**
	 * Update party(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parties to update. Only parties satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected party.
	 *
	 * \return An array of \e id-s of updated parties.
	 */
	public static function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate('party', $params, $data, 'id', self::$tableColumns, self::$roColumns);
		$res = $query->execute();
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line['id'];
		return $ids;
	}

	/**
	 * Delete party(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parties to delete. Only parties satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e id-s of deleted parties.
	 */
	public static function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete('party', $params, 'id', self::$tableColumns);
		$res = $query->execute();
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line['id'];
		return $ids;
	}
}

?>
