<?php

/**
 * Class MpAttribute provides information about MPs' additional attributes through API and implements CRUD operations on database table MP_ATTRIBUTE.
 *
 * Columns of table MP_ATTRIBUTE are: <em>mp_id, name_, value_, lang, since, until</em>. All columns are allowed to write to.
 */
class MpAttribute
{
	/// read-only columns of the table MP_ATTRIBUTE
	private static $roColumns = array();

	/// columns of the table MP_ATTRIBUTE allowed to write to
	private static $wColumns = array('mp_id', 'name_', 'value_', 'lang', 'since', 'until');

	/**
	 * Retrieve MP(s)' attributes according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to select. Only attributes satisfying all prescribed column values are returned.
	 *
	 * \return An array of attributes with structure <code>array('mp_attribute' => array(array('mp_id' => 32, 'name_' => 'hobbies', 'value_' => 'eating, smoking', ...), ...))</code>.
	 *
	 * You can use <em>datetime</em> within the <em>$params</em> (eg. 'datetime' => '2010-06-30 9:30:00') to select only attributes valid at the given moment (the ones where <em>since</em> <= datetime < <em>until</em>). Use 'datetime' => 'now' to get attributes valid at this moment.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('mp_attribute', '*', $params, self::$roColumns, self::$wColumns);
		if (!empty($params['datetime']))
		{
			$query->appendParam($params['datetime']);
			$n = $query->getParamsCount();
			$query->appendQuery(' and since <= $' . $n . ' and until > $' . $n);
		}		
		$attrs = $query->execute();
		return array('mp_attribute' => $attrs);
	}

	/**
	 * Create MP(s)' attributes with given values.
	 *
	 * \param $data An array of attributes to create, where each attribute is given by array of pairs <em>column => value</em>. Eg. <code>array(array('mp_id' => 32, 'name_' => 'hobbies', 'value_' => 'eating, smoking', ...), ...)</code>.
	 *
	 * \return Number of created attributes.
	 */
	public static function create($data)
	{
		$query = new Query('kv_admin');
		$query->startTransaction();		
		foreach ((array)$data as $attr)
		{
			$query->buildInsert('mp_attribute', $attr, null, self::$roColumns, self::$wColumns);
			$query->execute();
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return count($data);
	}

	/**
	 * Update MP(s)' attributes satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to update. Only attributes satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected attribute.
	 *
	 * \return Number of updated attributes.
	 */
	public static function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate('mp_attribute', $params, $data, 'mp_id', self::$roColumns, self::$wColumns);
		$res = $query->execute();
		return count($res);
	}

	/**
	 * Delete MP(s)' attributes according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to delete. Only attributes satisfying all prescribed column values are deleted.
	 *
	 * \return Number of deleted attributes.
	 */
	public static function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete('mp_attribute', $params, 'mp_id', self::$roColumns, self::$wColumns);
		$res = $query->execute();
		return count($res);
	}
}

?>
