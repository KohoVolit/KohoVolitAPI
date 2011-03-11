<?php

/**
 * ...
 */
class Query
{
	private $query;
	private $params;
	private $db_user;
	private $inside_transaction;
	
	public function __construct($db_user = 'kv_user')
	{
		$this->query = '';
		$this->params = array();
		$this->db_user = $db_user;
		$this->inside_transaction = false;
	}
	
	public function __destruct()
	{
		if ($this->inside_transaction)
			return $this->rollbackTransaction();
	}
	
	public function getQuery()
	{
		return $this->query;
	}
	
	public function setQuery($query)
	{
		$this->query = $query;
	}
	
	public function appendQuery($query)
	{
		$this->query .= $query;
	}

	public function clearQuery()
	{
		$this->query = '';
	}
	
	public function getParams()
	{
		return $this->params;
	}
	
	public function setParams($params)
	{
		$this->params = $params;
	}

	public function appendParam($param)
	{
		$this->params[] = $param;
	}

	public function clearParams()
	{
		$this->params = array();
	}
	
	public function getParamsCount()
	{
		return count($this->params);
	}

	public function execute()
	{
		return Db::query($this->query, $this->params, $this->db_user);
	}

	public function startTransaction()
	{
		if ($this->inside_transaction)
			throw new Exception('Database transactions cannot be nested.', 500);
		$this->inside_transaction = true;
		return Db::query('begin', null, $this->db_user);
	}
	
	public function commitTransaction()
	{
		if (!$this->inside_transaction)
			throw new Exception('Commiting transaction outside of any transaction.', 500);
		$this->inside_transaction = false;
		return Db::query('commit', null, $this->db_user);
	}
	
	public function rollbackTransaction()
	{
		if (!$this->inside_transaction)
			throw new Exception('Rolling back transaction outside of any transaction.', 500);
		$this->inside_transaction = false;
		return Db::query('rollback', null, $this->db_user);
	}

	public function buildSelect($table, $columns, $filter, $allowed_columns)
	{
		$this->query = "select $columns from $table";
		$this->params = array();
		$this->addWhereCondition($filter, $allowed_columns);
	}
	
	public function buildInsert($table, $data, $ret_column, $allowed_columns, $ro_columns = array())
	{
		$this->params = array();		
		$columns = array();
		$dollars = '';
		
		foreach ((array)$data as $column => $value)
		{
			if (in_array($column, $ro_columns))
				throw new Exception("Trying to write to read-only column <em>$column</em> in table <em>" . strtoupper($table) . '</em>.', 400);				
			if (!in_array($column, $allowed_columns)) continue;
			
			$columns[] = $column;
			if (is_null($value))
				$dollars .= 'null, ';
			else
			{
				$this->params[] = $value;
				$dollars .= '$' . count($this->params) . ', ';
			}
		}
		$this->query = "insert into $table (" . implode(', ', $columns) . ') values (' . rtrim($dollars, ', ') . ')';			
		
		if (!empty($ret_column))
			$this->query .= " returning $ret_column";
	}

	public function buildUpdate($table, $filter, $data, $ret_column, $allowed_columns, $ro_columns = array())
	{
		$this->query = "update $table set";
		$this->params = array();
		
		foreach ((array)$data as $column => $value)
		{
			if (in_array($column, $ro_columns))
				throw new Exception("Trying to write to read-only column <em>$column</em> in table <em>" . strtoupper($table) . '</em>.', 400);								
			if (!in_array($column, $allowed_columns)) continue;
			
			if (is_null($value))
				$this->query .= " $column = null,";
			else
			{
				$this->params[] = $value;
				$this->query .= " $column = $" . count($this->params) . ',';
			}
		}
		$this->query = rtrim($this->query, ',');

		$this->addWhereCondition($filter, $allowed_columns);
		
		if (!empty($ret_column))
			$this->query .= " returning $ret_column";
	}

	public function buildDelete($table, $filter, $ret_column, $allowed_columns)
	{
		$this->query = "delete from $table";
		$this->params = array();
		$this->addWhereCondition($filter, $allowed_columns);
		if (!empty($ret_column))
			$this->query .= " returning $ret_column";
	}
	
	private function addWhereCondition($filter, $allowed_columns)
	{
		$this->query .= ' where true';
		foreach ((array)$filter as $column => $value)
		{
			if (!in_array($column, (array)$allowed_columns)) continue;

			if (is_null($value))
				$this->query .= " and $column is null";
			else
			{
				$this->params[] = $value;
				$this->query .= " and $column = $" . count($this->params);
			}
		}
	}
}

?>