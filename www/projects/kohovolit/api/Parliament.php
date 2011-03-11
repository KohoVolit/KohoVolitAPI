<?php

/**
 * Class Parliament provides information about parliaments through API and implements CRUD operations on database table PARLIAMENT.
 *
 * Columns of table PARLIAMENT are: <em>code, name_, short_name, description, parliament_kind_code, country_code, default_language</em>. All columns are allowed to write to.
 */
class Parliament
{
	/// columns of the table PARLIAMENT
	private static $tableColumns = array('code', 'name_', 'short_name', 'description', 'parliament_kind_code', 'country_code', 'default_language');

	/**
	 * Retrieve parliament(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliaments to select. Only parliaments satisfying all prescribed column values are returned.
	 *
	 * \return An array of parliaments with structure <code>array('parliament' => array(array('code' => 'sk/nrsr', 'name_' => 'Národná rada Slovenskej Republiky', 'short_name' => 'NRSR', null, 'national-lower', ...), ...))</code>.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('parliament', '*', $params, self::$tableColumns);
		$parliaments = $query->execute();
		return array('parliament' => $parliaments);
	}

	/**
	 * Create parliament(s) with given values.
	 *
	 * \param $data An array of parliaments to create, where each parliament is given by array of pairs <em>column => value</em>. Eg. <code>array(array('name_' => 'Praha 9', 'short_name' => '9', null, 'cz/praha'), ...)</code>.
	 *
	 * \return An array of \e code-s of created parliaments.
	 */
	public static function create($data)
	{
		$query = new Query('kv_admin');
		$codes = array();
		$query->startTransaction();		
		foreach ((array)$data as $parliament)
		{
			$query->buildInsert('parliament', $parliament, 'code', self::$tableColumns, self::$roColumns);
			$res = $query->execute();
			$codes[] = $res[0]['code'];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return $codes;
	}

	/**
	 * Update parliament(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliaments to update. Only parliaments satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected parliament.
	 *
	 * \return An array of \e code-s of updated parliaments.
	 */
	public static function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate('parliament', $params, $data, 'code', self::$tableColumns, self::$roColumns);
		$res = $query->execute();
		$codes = array();
		foreach ((array)$res as $line)
			$codes[] = $line['code'];
		return $codes;
	}

	/**
	 * Delete parliament(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliaments to delete. Only parliaments satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e code-s of deleted parliaments.
	 */
	public static function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete('parliament', $params, 'code', self::$tableColumns);
		$res = $query->execute();
		$codes = array();
		foreach ((array)$res as $line)
			$codes[] = $line['code'];
		return $codes;
	}
}

?>
