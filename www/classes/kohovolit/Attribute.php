<?php

/**
 * Class Attribute encapsulates manipulation with underlying database table for attributes of entities through API.
 *
 * The class specifies columns common for all attribute tables and implements CRUD operations on those database tables.
 * Columns common for all attribute tables are: <em>name_, value_, lang, since, until</em>. All columns are allowed to write to.
 */
 class Attribute
 {
	/// name of the database table
	private $tableName;

	/// columns of the database table
	private $tableColumns;

	/**
	 * Initializes information about a database table for this attributtes of an entity.
	 *
	 * \param $table_name Name of database table with attributes, eg. 'mp_attribute'.
	 * \param $table_columns Array of names of the database table columns specific for this attribute table. Common columns of all *_ATTRIBUTE tables are added automatically.
	 */
	public function __construct($table_name, $table_columns)
	{
		$this->tableName = $table_name;
		$this->tableColumns = array_merge($table_columns, array('name_', 'value_', 'lang', 'since', 'until'));
	}

	/**
	 * Read attributes according to parameters from the given table.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to select. Only attributes satisfying all prescribed column values are returned.
	 *
	 * \return An array of attributes, eg. <code>array('mp_attribute' => array(array('mp_id' => 32, 'name_' => 'hobbies', 'value_' => 'eating, smoking', ...), ...))</code> sorted by \e <entity_id> than by \e name_ than by \e lang all ascending and then by \e since descending.
	 *
	 * You can use <em>datetime</em> within the <em>$params</em> (eg. 'datetime' => '2010-06-30 9:30:00') to select only attributes valid at the given moment (the ones where <em>since</em> <= datetime < <em>until</em>). Use 'datetime' => 'now' to get attributes valid now.
	 */
	public function read($params)
	{
		$query = new Query();
		$query->buildSelect($this->tableName, '*', $params, $this->tableColumns);
		if (!empty($params['datetime']))
		{
			$query->appendParam($params['datetime']);
			$n = $query->getParamsCount();
			$query->appendQuery(' and since <= $' . $n . ' and until > $' . $n);
		}
		$query->appendQuery(' order by ' . reset($this->tableColumns) . ', name_, lang, since desc');
		$attrs = $query->execute();
		return array($this->tableName => $attrs);
	}

	/**
	 * Create attributes with given values.
	 *
	 * \param $data An array of attributes to create, where each attribute is given by array of pairs <em>column => value</em>. Eg. <code>array(array('mp_id' => 32, 'name_' => 'hobbies', 'value_' => 'eating, smoking', ...), ...)</code>.
	 *
	 * \return Number of created attributes.
	 */
	public function create($data)
	{
		$query = new Query('kv_admin');
		$query->startTransaction();
		foreach ((array)$data as $attr)
		{
			$query->buildInsert($this->tableName, $attr, null, $this->tableColumns);
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
	 *
	 * \return Number of updated attributes.
	 */
 	public function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate($this->tableName, $params, $data, '1', $this->tableColumns);
		$res = $query->execute();
		return count($res);
	}

	/**
	 * Delete attributes according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to delete. Only attributes satisfying all prescribed column values are deleted.
	 *
	 * \return Number of deleted attributes.
	 */
	public function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete($this->tableName, $params, '1', $this->tableColumns);
		$res = $query->execute();
		return count($res);
	}
}

 ?>
