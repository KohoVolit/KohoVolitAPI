<?php

/**
 * Class Entity encapsulates manipulation with underlying database table for entities through API, eg. for classes Mp, Group, Parliament, etc.
 *
 * The class implements CRUD operations on those database tables.
 */
class Entity
{
	/// properties of the database table
	private $tableProperties;

	/**
	 * Initializes information about a database table for this entity.
	 *
	 * \param $table_properties Array of pairs <em>property</em> => <em>value</em> where needed properties are:
	 * \li \c name name of the database table,
	 * \li \c columns array of table column names,
	 * \li \c pkey_columns (optional) array of column names that primary key of the table consists of. Values of those columns are returned for created/updated/deleted entities,
	 * \li \c readonly_columns (optional) array of table column names not allowed to write to (e.g. that are automatically generated on insert).
	 */
	public function __construct($table_properties)
	{
		$this->tableProperties = $table_properties;
		if (!isset($this->tableProperties['pkey_columns']))
			$this->tableProperties['pkey_columns'] = array();
		if (!isset($this->tableProperties['readonly_columns']))
			$this->tableProperties['readonly_columns'] = array();
	}

	/**
	 * Read entity(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the entities to select. Only entities satisfying all prescribed column values are returned.
	 *
	 * \return An array of entities.
	 */
	public function read($params)
	{
		$query = new Query();
		$query->buildSelect($this->tableProperties['name'], '*', $params, $this->tableProperties['columns']);
		return $query->execute();
	}

	/**
	 * Create entity(s) with given values.
	 *
	 * \param $data An entity to create given by array of pairs <em>column => value</em>. Alternatively, an array of such entities.
	 *
	 * \return An array of primary key values of all created entities.
	 */
	public function create($data)
	{
		if (!is_array($data)) return null;
		$entities = is_array(reset($data)) ? $data : array($data);

		$query = new Query('kv_admin');
		$query->startTransaction();
		$pkeys = array();
		foreach ($entities as $entity)
		{
			$query->buildInsert($this->tableProperties['name'], $entity, $this->tableProperties['columns'], $this->tableProperties['pkey_columns'], $this->tableProperties['readonly_columns']);
			$lines = $query->execute();
			$pkeys[] = $lines[0];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();

		if (is_array(reset($data)))
			return $pkeys;
		else
			return reset($pkeys);
	}

	/**
	 * Update entity(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the entities to update. Only entities satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected entity.
	 *
	 * \return An array of primary key values of all updated entities.
	 */
	public function update($params, $data)
	{
		$query = new Query('kv_admin');
		$query->buildUpdate($this->tableProperties['name'], $params, $data, $this->tableProperties['columns'], $this->tableProperties['pkey_columns'], $this->tableProperties['readonly_columns']);
		return $query->execute();
	}

	/**
	 * Delete entity(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the entities to delete. Only entities satisfying all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of all deleted entities.
	 */
	public function delete($params)
	{
		$query = new Query('kv_admin');
		$query->buildDelete($this->tableProperties['name'], $params, $this->tableProperties['columns'], $this->tableProperties['pkey_columns']);
		return $query->execute();
	}
}

?>
