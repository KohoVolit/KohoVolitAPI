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

	/// primary key columns
	private $pkeyColumns;

	/**
	 * Initializes information about a database table for this attributtes of an entity.
	 *
	 * \param $table_name Name of database table with attributes, eg. 'mp_attribute'.
	 * \param $table_columns Array of table column names specific for this attribute table. Common columns of all *_ATTRIBUTE tables are added automatically.
	 */
	public function __construct($table_name, $table_columns)
	{
		$this->tableName = $table_name;
		$this->tableColumns = array_merge($table_columns, array('name_', 'value_', 'lang', 'since', 'until'));
		$this->pkeyColumns = array_merge($table_columns, array('name_', 'lang', 'since'));
	}

	/**
	 * Read attributes according to parameters from the given table.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to select. Only attributes satisfying all prescribed column values are returned.
	 *
	 * \return An array of attributes, eg. <code>array(array('mp_id' => 32, 'name_' => 'hobbies', 'value_' => 'eating, smoking', ...), ...)</code>.
	 */
	public function read($params)
	{
		$query = new Query();
		$query->buildSelect($this->tableName, '*', $params, $this->tableColumns);
//		$query->appendQuery(' order by ' . reset($this->tableColumns) . ', name_, lang, since desc');
		return $query->execute();
	}

	/**
	 * Create attributes with given values.
	 *
	 * \param $data An attribute to create given by array of pairs <em>column => value</em>. Alternatively, an array of such attributes. Eg. <code>array(array('mp_id' => 32, 'name_' => 'hobbies', 'value_' => 'eating, smoking', ...), ...)</code>.
	 *
	 * \return An array of primary key values of all created attributes.
	 */
	public function create($data)
	{
		if (!is_array($data)) return null;
		if (!is_array(reset($data)))
			$data = array($data);

		$query = new Query('kv_admin');
		$query->startTransaction();
		$pkeys = array();
		foreach ($data as $attr)
		{
			$query->buildInsert($this->tableName, $attr, $this->tableColumns, $this->pkeyColumns);
			$lines = $query->execute();
			$pkeys[] = $lines[0];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return $pkeys;
	}

	/**
	 * Update attributes satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to update. Only attributes satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected attribute.
	 *
	 * \return An array of primary key values of all updated attributes.
	 */
 	public function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate($this->tableName, $params, $data, $this->tableColumns, $this->pkeyColumns);
		return $query->execute();
	}

	/**
	 * Delete attributes according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to delete. Only attributes satisfying all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of all deleted attributes.
	 */
	public function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete($this->tableName, $params, $this->tableColumns, $this->pkeyColumns);
		return $query->execute();
	}
}

 ?>
