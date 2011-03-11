<?php

/**
 * Class Mp provides information about MPs through API and implements CRUD operations on database table MP.
 *
 * Columns of table MP are: <em>id, first_name, middle_names, last_name, disambiguation, sex, pre_title, post_title, born_on, died_on, email, webpage, address, phone, source, source_code</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */
class Mp
{
	/// columns of the table MP
	private static $tableColumns = array('id', 'first_name', 'middle_names', 'last_name', 'disambiguation', 'sex', 'pre_title', 'post_title', 'born_on', 'died_on', 'email', 'webpage', 'address', 'phone', 'source', 'source_code');

	/// read-only columns
	private static $roColumns = array('id');

	/**
	 * Retrieve MP(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the MPs to select. Only MPs satisfying all prescribed column values are returned.
	 *
	 * \return An array of MPs with structure <code>array('mp' => array(array('id' => 32, 'first_name' => 'Balin', ...), array('id' => 10, 'first_name' => 'Dvalin', ...), ...))</code>.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('mp', '*', $params, self::$tableColumns);
		$mps = $query->execute();
		return array('mp' => $mps);
	}

	/**
	 * Create MP(s) with given values.
	 *
	 * \param $data An array of MPs to create, where each MP is given by array of pairs <em>column => value</em>. Eg. <code>array(array('first_name' => 'Bilbo', 'last_name' = 'Baggins', ...), ...)</code>.
	 *
	 * \return An array of \e id-s of created MPs.
	 */
	public static function create($data)
	{
		$query = new Query('kv_admin');
		$ids = array();
		$query->startTransaction();		
		foreach ((array)$data as $mp)
		{
			$query->buildInsert('mp', $mp, 'id', self::$tableColumns, self::$roColumns);
			$res = $query->execute();
			$ids[] = $res[0]['id'];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return $ids;
	}

	/**
	 * Update MP(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the MPs to update. Only MPs satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected MP.
	 *
	 * \return An array of \e id-s of updated MPs.
	 */
	public static function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate('mp', $params, $data, 'id', self::$tableColumns, self::$roColumns);
		$res = $query->execute();
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line['id'];
		return $ids;
	}

	/**
	 * Delete MP(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the MPs to delete. Only MPs satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e id-s of deleted MPs.
	 */
	public static function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete('mp', $params, 'id', self::$tableColumns);
		$res = $query->execute();
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line['id'];
		return $ids;
	}
}

?>
