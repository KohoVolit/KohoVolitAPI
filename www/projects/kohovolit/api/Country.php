<?php

/**
 * Class Country provides information about countries through API and implements CRUD operations on database table COUNTRY.
 *
 * Columns of table COUNTRY are: <em>code, name_, short_name, description</em>. All columns are allowed to write to.
 */
class Country
{
	/// columns of the table COUNTRY
	private static $tableColumns = array('code', 'name_', 'short_name', 'description');

	/**
	 * Retrieve country(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the countries to select. Only countries satisfying all prescribed column values are returned.
	 *
	 * \return An array of countries with structure <code>array('country' => array(array('code' => 'sk', 'name_' => 'Slovak republic', 'short_name' => 'Slovakia', ...), array('code' => 'eu', 'name_' => 'European Union', 'short_name' => 'EU', ...), ...))</code>.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('country', '*', $params, self::$tableColumns);
		$countries = $query->execute();
		return array('country' => $countries);
	}

	/**
	 * Create country(s) with given values.
	 *
	 * \param $data An array of countries to create, where each country is given by array of pairs <em>column => value</em>. Eg. <code>array(array('code' => 'sk', 'name_' => 'Slovak republic', 'short_name' => 'Slovakia', ...), ...)</code>.
	 *
	 * \return An array of \e code-s of created countries.
	 */
	public static function create($data)
	{
		$query = new Query('kv_admin');
		$codes = array();
		$query->startTransaction();
		foreach ((array)$data as $country)
		{
			$query->buildInsert('country', $country, 'code', self::$tableColumns);
			$res = $query->execute();
			$codes[] = $res[0]['code'];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return $codes;
	}

	/**
	 * Update country(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the countries to update. Only countries satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected country.
	 *
	 * \return An array of \e code-s of updated countries.
	 */
	public static function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate('country', $params, $data, 'code', self::$tableColumns);
		$res = $query->execute();
		$codes = array();
		foreach ((array)$res as $line)
			$codes[] = $line['code'];
		return $codes;
	}

	/**
	 * Delete country(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the countries to delete. Only countries satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e code-s of deleted countries.
	 */
	public static function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete('country', $params, 'code', self::$tableColumns);
		$res = $query->execute();
		$codes = array();
		foreach ((array)$res as $line)
			$codes[] = $line['code'];
		return $codes;
	}
}

?>
