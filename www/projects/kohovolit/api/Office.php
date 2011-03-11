<?php

/**
 * Class Office provides information about MPs' offices through API and implements CRUD operations on database table OFFICE.
 *
 * Columns of table OFFICE are: <em>mp_id, address, phone, since, until</em>. All columns are allowed to write to.
 */
class Office
{
	/// columns of the table OFFICE
	private static $tableColumns = array('mp_id', 'address', 'phone', 'since', 'until');

	/**
	 * Retrieve MP office(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the offices to select. Only offices satisfying all prescribed column values are returned.
	 *
	 * \return An array of offices with structure <code>array('office' => array(array('mp_id' => 32, 'address' => '(, 'Bag end', '12', 'Hobbiton', 'SH-12345', 'Middle-earth)', 'phone' => '+421 123 456 789', ...), ...))</code>.
	 *
	 * You can use <em>datetime</em> within the <em>$params</em> (eg. 'datetime' => '2010-06-30 9:30:00') to select only offices valid at the given moment (the ones where <em>since</em> <= datetime < <em>until</em>). Use 'datetime' => 'now' to get offices valid at this moment.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('office', '*', $params, self::$tableColumns);
		if (!empty($params['datetime']))
		{
			$query->appendParam($params['datetime']);
			$n = $query->getParamsCount();
			$query->appendQuery(' and since <= $' . $n . ' and until > $' . $n);
		}		
		$offices = $query->execute();
		return array('office' => $offices);
	}

	/**
	 * Create MP office(s) with given values.
	 *
	 * \param $data An array of offices to create, where each office is given by array of pairs <em>column => value</em>. Eg. <code>array(array('mp_id' => 32, 'address' => '(, 'Bag end', '12', 'Hobbiton', 'SH-12345', 'Middle-earth)', 'phone' => '+421 123 456 789', ...), ...)</code>.
	 *
	 * \return Number of created offices.
	 */
	public static function create($data)
	{
		$query = new Query('kv_admin');
		$query->startTransaction();		
		foreach ((array)$data as $office)
		{
			$query->buildInsert('office', $office, null, self::$tableColumns);
			$query->execute();
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return count($data);
	}

	/**
	 * Update MP office(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the offices to update. Only offices satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected office.
	 *
	 * \return Number of updated offices.
	 */
	public static function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate('office', $params, $data, '1', self::$tableColumns);
		$res = $query->execute();
		return count($res);
	}

	/**
	 * Delete MP office(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the offices to delete. Only offices satisfying all prescribed column values are deleted.
	 *
	 * \return Number of deleted offices.
	 */
	public static function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete('office', $params, '1', self::$tableColumns);
		$res = $query->execute();
		return count($res);
	}
}

?>
