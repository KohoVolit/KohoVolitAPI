<?php

/**
 * Class Term provides information about parliament terms of office through API and implements CRUD operations on database table TERM.
 *
 * Columns of table TERM are: <em>id, name_, short_name, description, parliament_kind_code, since, until</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */
class Term
{
	/// columns of the table TERM
	private static $tableColumns = array('id', 'name_', 'short_name', 'description', 'parliament_kind_code', 'since', 'until');

	/// read-only columns
	private static $roColumns = array('id');

	/**
	 * Retrieve term(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the terms to select. Only terms satisfying all prescribed column values are returned.
	 *
	 * \return An array of terms with structure <code>array('term' => array(array('id' => 3, 'name_' => '2006-2010', 'short_name' => '6', ...), ...))</code>.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('term', '*', $params, self::$tableColumns);
		$terms = $query->execute();
		return array('term' => $terms);
	}

	/**
	 * Create term(s) with given values.
	 *
	 * \param $data An array of terms to create, where each term is given by array of pairs <em>column => value</em>. Eg. <code>array(array('term' => array(array(name_' => '2006-2010', 'short_name' => '6', parliament_kind_code = 'cz/psp', ...), ...)</code>.
	 *
	 * \return An array of \e id-s of created terms.
	 */
	public static function create($data)
	{
		$query = new Query('kv_admin');
		$ids = array();
		$query->startTransaction();		
		foreach ((array)$data as $term)
		{
			$query->buildInsert('term', $term, 'id', self::$tableColumns, self::$roColumns);
			$res = $query->execute();
			$ids[] = $res[0]['id'];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return $ids;
	}

	/**
	 * Update term(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the terms to update. Only terms satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected term.
	 *
	 * \return An array of \e id-s of updated terms.
	 */
	public static function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate('term', $params, $data, 'id', self::$tableColumns, self::$roColumns);
		$res = $query->execute();
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line['id'];
		return $ids;
	}

	/**
	 * Delete term(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the terms to delete. Only terms satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e id-s of deleted terms.
	 */
	public static function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete('term', $params, 'id', self::$tableColumns);
		$res = $query->execute();
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line['id'];
		return $ids;
	}
}

?>
