<?php

/**
 * Abstract class Entity is a base class for all classes providing information about entities in database tables through API, eg. classes Mp, Group, Parliament, etc.
 *
 * The class implements CRUD operations on those database tables.
 */
abstract class Entity
{
	/// columns of the table
	protected static $tableColumns;

	/// read-only columns
	protected static $roColumns;
	
	/**
	 * Read entity(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the entities to select. Only entities satisfying all prescribed column values are returned.
	 * \param $table_name Name of database table with entities, eg. 'mp'.
	 * \param $temporal Indicates whether the table contains \e since and \e until columns. In that case \e datetime within the \a $params can be used (eg. 'datetime' => '2010-06-30 9:30:00') to select only entites valid at the given moment (the ones where <em>since</em> <= datetime < <em>until</em>). Value 'datetime' => 'now' can be used to get entities valid at this moment.
	 *
	 * \return An array of entities with structure <code>array('<entity>' => array(array(...), array(...), ...))</code>.
	 */
	public static function readEntity($params, $table_name, $temporal = false)
	{
		$query = new Query();
		$query->buildSelect($table_name, '*', $params, self::$tableColumns);
		if ($temporal && !empty($params['datetime']))
		{
			$query->appendParam($params['datetime']);
			$n = $query->getParamsCount();
			$query->appendQuery(' and since <= $' . $n . ' and until > $' . $n);
		}		
		$entities = $query->execute();
		return array(rtrim($table_name, '_') => $entities);
	}

	/**
	 * Create entity(s) with given values.
	 *
	 * \param $data An array of entities to create, where each entity is given by array of pairs <em>column => value</em>.
	 * \param $table_name Name of database table with entities, eg. 'mp'.
	 * \param $ret_column Name of a column to return values from for all created entities (usually automatically generated column like 'id') or \e '1' (default value) to return only number of created rows.
	 *
	 * \return An array of \e id-s or \e code-s of created entities or only number of them if \e $ret_column is null.
	 */
	public static function createEntity($data, $table_name, $ret_column = '1')
	{
		$query = new Query('kv_admin');
		$ids = array();
		$query->startTransaction();		
		foreach ((array)$data as $entity)
		{
			$query->buildInsert($table_name, $entity, $ret_column, self::$tableColumns, self::$roColumns);
			$res = $query->execute();
			if ($ret_column != '1')
				$ids[] = $res[0][$ret_column];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();

		if ($ret_column != '1')
			return $ids;
		else
			return count($data);
	}

	/**
	 * Update entity(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the entities to update. Only entities satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected entity.
	 * \param $table_name Name of database table with entities, eg. 'mp'.
	 * \param $ret_column Name of a column to return values from for all created entities (usually primary_key column like 'id') or \e '1' (default value) to return only number of updated rows.
	 *
	 * \return An array of \e id-s or \e code-s of updated entities or only number of them if \e $ret_column is null.
	 */
	public static function updateEntity($params, $data, $table_name, $ret_column = '1')
	{
		$query = new Query('kv_admin');
		$query->buildUpdate($table_name, $params, $data, $ret_column, self::$tableColumns, self::$roColumns);
		$res = $query->execute();

		if ($ret_column == '1')
			return count($res);
			
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line[$ret_column];
		return $ids;
	}

	/**
	 * Delete entity(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the entities to delete. Only entities satisfying all prescribed column values are deleted.
	 * \param $table_name Name of database table with entities, eg. 'mp'.
	 * \param $ret_column Name of a column to return values from for all created entities (usually primary key column like 'id') or '1' (default value) to return only number of deleted rows.
	 *
	 * \return An array of \e id-s or \e code-s of deleted entities or only number of them if \e $ret_column is null.
	 */
	public static function deleteEntity($params, $table_name, $ret_column = '1')
	{
		$query = new Query('kv_admin');
		$query->buildDelete($table_name, $params, $ret_column, self::$tableColumns);
		$res = $query->execute();

		if ($ret_column == '1')
			return count($res);

		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line[$ret_column];
		return $ids;
	}
}

?>
