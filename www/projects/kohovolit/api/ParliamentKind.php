<?php

/**
 * Class ParliamentKind provides information about kinds of parliament through API and implements CRUD operations on database table PARLIAMENT_KIND.
 *
 * Columns of table PARLIAMENT_KIND are: <em>code, name_, short_name, description</em>. All columns are allowed to write to.
 */
class ParliamentKind
{
	/// columns of the table PARLIAMENT_KIND
	private static $tableColumns = array('code', 'name_', 'short_name', 'description');

	/**
	 * Retrieve parliament kind(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliament kinds to select. Only parliament kinds satisfying all prescribed column values are returned.
	 *
	 * \return An array of parliament kinds with structure <code>array('parliament_kind' => array(array('code' => 'regional', 'name_' => 'Regional parliament', 'short_name' => 'Regional', ...), ...))</code>.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('parliament_kind', '*', $params, self::$tableColumns);
		$parliament_kinds = $query->execute();
		return array('parliament_kind' => $parliament_kinds);
	}

	/**
	 * Create parliament kind(s) with given values.
	 *
	 * \param $data An array of parliament kinds to create, where each parliament kind is given by array of pairs <em>column => value</em>. Eg. <code>array(array('code' => 'regional', 'name_' => 'Regional parliament', 'short_name' => 'Regional', ...), ...)</code>.
	 *
	 * \return An array of \e code-s of created parliament kinds.
	 */
	public static function create($data)
	{
		$query = new Query('kv_admin');
		$codes = array();
		$query->startTransaction();		
		foreach ((array)$data as $parliament_kind)
		{
			$query->buildInsert('parliament_kind', $parliament_kind, 'code', self::$tableColumns, self::$roColumns);
			$res = $query->execute();
			$codes[] = $res[0]['code'];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return $codes;
	}

	/**
	 * Update parliament kind(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliament kinds to update. Only parliament kinds satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected parliament kind.
	 *
	 * \return An array of \e code-s of updated parliament kinds.
	 */
	public static function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate('parliament_kind', $params, $data, 'code', self::$tableColumns, self::$roColumns);
		$res = $query->execute();
		$codes = array();
		foreach ((array)$res as $line)
			$codes[] = $line['code'];
		return $codes;
	}

	/**
	 * Delete parliament kind(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliament kinds to delete. Only parliament kinds satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e code-s of deleted parliament kinds.
	 */
	public static function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete('parliament_kind', $params, 'code', self::$tableColumns);
		$res = $query->execute();
		$codes = array();
		foreach ((array)$res as $line)
			$codes[] = $line['code'];
		return $codes;
	}
}

?>
