<?php

/**
 * Class Entity encapsulates manipulation with underlying database table for entities through API, eg. for classes Mp, Group, Parliament, etc.
 *
 * The class implements CRUD operations on those database tables.
 */
class Entity
{
	/// name of the database table
	private $tableName;

	/// columns of the database table
	private $tableColumns;

	/// name of the column to return in create, update, delete operations
	private $returnColumn;

	/// read-only columns
	private $readonlyColumns;

	/// contains the table 'since' and 'until' columns?
	private $temporal;

	/**
	 * Initializes information about a database table for this entity.
	 *
	 * \param $table_name Name of database table with entities, eg. 'mp'.
	 * \param $table_columns Array of names of the database table columns.
	 * \param $return_column Name of a column to return values from for all created/updated/deleted entities (usually a primary key column like 'id'). If not set number of created/updated/deleted rows is returned.
	 * \param $readonly_columns Array of names of the database table columns that are read-only (ie. that are automatically generated on insert).
	 * \param $temporal Indicates whether the table contains \e since and \e until columns. In that case \e datetime within the \c read() method parameters can be used (eg. 'datetime' => '2010-06-30 9:30:00') to select only entities valid at the given moment (the ones where <em>since</em> <= datetime < <em>until</em>). Value 'datetime' => 'now' can be used to get entities valid now.
	 */
	public function __construct($table_name, $table_columns, $return_column = null, $readonly_columns = array(), $temporal = false)
	{
		$this->tableName = $table_name;
		$this->tableColumns = $table_columns;
		$this->returnColumn = isset($return_column) ? $return_column : '1';
		$this->readonlyColumns = $readonly_columns;
		$this->temporal = $temporal;
	}

	/**
	 * Read entity(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the entities to select. Only entities satisfying all prescribed column values are returned.
	 *
	 * \return An array of entities with structure <code>array('<entity>' => array(array(...), array(...), ...))</code>.
	 */
	public function read($params)
	{
		$query = new Query();
		$query->buildSelect($this->tableName, '*', $params, $this->tableColumns);
		if ($this->temporal && !empty($params['datetime']))
		{
			$query->appendParam($params['datetime']);
			$n = $query->getParamsCount();
			$query->appendQuery(' and since <= $' . $n . ' and until > $' . $n);
		}
		$entities = $query->execute();
		return array(rtrim($this->tableName, '_') => $entities);
	}

	/**
	 * Create entity(s) with given values.
	 *
	 * \param $data An array of entities to create, where each entity is given by array of pairs <em>column => value</em>.
	 *
	 * \return An array of values from the return column (set on class initialization) of created entities or number of them if the return column has not been set.
	 */
	public function create($data)
	{
		$query = new Query('kv_admin');
		$ids = array();
		$query->startTransaction();
		foreach ((array)$data as $entity)
		{
			$query->buildInsert($this->tableName, $entity, $this->returnColumn, $this->tableColumns, $this->readonlyColumns);
			$res = $query->execute();
			if ($this->returnColumn != '1')
				$ids[] = $res[0][$this->returnColumn];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();

		if ($this->returnColumn != '1')
			return $ids;
		else
			return count($data);
	}

	/**
	 * Update entity(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the entities to update. Only entities satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected entity.
	 *
	 * \return An array of values from the return column (set on class initialization) of updated entities or number of them if the return column has not been set.
	 */
	public function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate($this->tableName, $params, $data, $this->returnColumn, $this->tableColumns, $this->readonlyColumns);
		$res = $query->execute();

		if ($this->returnColumn == '1')
			return count($res);

		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line[$this->returnColumn];
		return $ids;
	}

	/**
	 * Delete entity(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the entities to delete. Only entities satisfying all prescribed column values are deleted.
	 *
	 * \return An array of values from the return column (set on class initialization) of deleted entities or number of them if the return column has not been set.
	 */
	public function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete($this->tableName, $params, $this->returnColumn, $this->tableColumns);
		$res = $query->execute();

		if ($this->returnColumn == '1')
			return count($res);

		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line[$this->returnColumn];
		return $ids;
	}
}

?>
