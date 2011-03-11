<?php

/**
 * Class Constituency provides information about constituencies of a parliament through API and implements CRUD operations on database table CONSTITUENCY.
 *
 * Columns of table CONSTITUENCY are: <em>id, name_, short_name, description, parliament_code</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */
class Constituency
{
	/// columns of the table CONSTITUENCY
	private static $tableColumns = array('id', 'name_', 'short_name', 'description', 'parliament_code');

	/// read-only columns
	private static $roColumns = array('id');

	/**
	 * Retrieve constituency(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the constituencies to select. Only constituencies satisfying all prescribed column values are returned.
	 *
	 * \return An array of constituencies with structure <code>array('constituency' => array(array('id' => 123, 'name_' => 'Praha 9', 'short_name' => '9', 'description' => null, 'cz/praha'), ...))</code>.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('constituency', '*', $params, self::$tableColumns);
		$constituencies = $query->execute();
		return array('constituency' => $constituencies);
	}

	/**
	 * Create constituency(s) with given values.
	 *
	 * \param $data An array of constituencies to create, where each constituency is given by array of pairs <em>column => value</em>. Eg. <code>array(array('name_' => 'Praha 9', 'short_name' => '9', 'description' => null, 'cz/praha'), ...)</code>.
	 *
	 * \return An array of \e id-s of created constituencies.
	 */
	public static function create($data)
	{
		$query = new Query('kv_admin');
		$ids = array();
		$query->startTransaction();		
		foreach ((array)$data as $constituency)
		{
			$query->buildInsert('constituency', $constituency, 'id', self::$tableColumns, self::$roColumns);
			$res = $query->execute();
			$ids[] = $res[0]['id'];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return $ids;
	}

	/**
	 * Update constituency(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the constituencies to update. Only constituencies satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected constituency.
	 *
	 * \return An array of \e id-s of updated constituencies.
	 */
	public static function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate('constituency', $params, $data, 'id', self::$tableColumns, self::$roColumns);
		$res = $query->execute();
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line['id'];
		return $ids;
	}

	/**
	 * Delete constituency(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the constituencies to delete. Only constituencies satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e id-s of deleted constituencies.
	 */
	public static function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete('constituency', $params, 'id', self::$tableColumns);
		$res = $query->execute();
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line['id'];
		return $ids;
	}
}

?>
