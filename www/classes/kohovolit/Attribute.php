<?php

/**
 * Abstract class Attribute is a base class for all classes providing information about additional attributes of entities through API.
 *
 * The class specifies columns common for all attribute tables and implements CRUD operations on those database tables.
 */
 abstract class Attribute
 {
	/// common columns of all *_ATTRIBUTE tables
	protected static $tableColumns = array('name_', 'value_', 'lang', 'since', 'until');
	
	/// Must be defined in a derived class to initialize column names of the table to work with.
	abstract protected static function initColumnNames();
	
	/**
	 * Read attributes according to parameters from the given table.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to select. Only attributes satisfying all prescribed column values are returned.
	 * \param $table_name Name of database table with attributes, eg. 'mp_attribute'.
	 *
	 * \return An array of attributes, eg. <code>array('mp_attribute' => array(array('mp_id' => 32, 'name_' => 'hobbies', 'value_' => 'eating, smoking', ...), ...))</code>.
	 *
	 * You can use <em>datetime</em> within the <em>$params</em> (eg. 'datetime' => '2010-06-30 9:30:00') to select only attributes valid at the given moment (the ones where <em>since</em> <= datetime < <em>until</em>). Use 'datetime' => 'now' to get attributes valid at this moment.
	 */
	protected static function readAttribute($params, $table_name)
	{
		$query = new Query();
		$query->buildSelect($table_name, '*', $params, self::$tableColumns);
		if (!empty($params['datetime']))
		{
			$query->appendParam($params['datetime']);
			$n = $query->getParamsCount();
			$query->appendQuery(' and since <= $' . $n . ' and until > $' . $n);
		}		
		$attrs = $query->execute();
		return array($table_name => $attrs);
	}
	
	/**
	 * Create attributes with given values.
	 *
	 * \param $data An array of attributes to create, where each attribute is given by array of pairs <em>column => value</em>. Eg. <code>array(array('mp_id' => 32, 'name_' => 'hobbies', 'value_' => 'eating, smoking', ...), ...)</code>.
	 * \param $table_name Name of database table with attributes, eg. 'mp_attribute'.
	 *
	 * \return Number of created attributes.
	 */
	protected static function createAttribute($data, $table_name)
	{
		$query = new Query('kv_admin');
		$query->startTransaction();
		foreach ((array)$data as $attr)
		{
			$query->buildInsert($table_name, $attr, null, self::$tableColumns);
			$query->execute();
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return count($data);
	}
	
	/**
	 * Update attributes satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to update. Only attributes satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected attribute.
	 * \param $table_name Name of database table with attributes, eg. 'mp_attribute'.
	 *
	 * \return Number of updated attributes.
	 */
 	protected static function updateAttribute($params, $data, $table_name)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate($table_name, $params, $data, '1', self::$tableColumns);
		$res = $query->execute();
		return count($res);
	}

	/**
	 * Delete attributes according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to delete. Only attributes satisfying all prescribed column values are deleted.
	 * \param $table_name Name of database table with attributes, eg. 'mp_attribute'.
	 *
	 * \return Number of deleted attributes.
	 */
	protected static function deleteAttribute($params, $table_name)
	{
		$query = new Query('kv_admin');
		$query->buildDelete($table_name, $params, '1', self::$tableColumns);
		$res = $query->execute();
		return count($res);
	}
}

 ?>
