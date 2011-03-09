<?php

/**
 * ...
 */
class Query
{
	private $query;
	private $params;
	
	public function __construct()
	{
		$this->query = '';
		$this->params = array();
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

	public function execute($user = 'kv_user')
	{
		return Db::query($this->query, $this->params, $user);
	}
	
	public function buildSelect($table, $columns, $filter, $ro_columns, $w_columns)
	{
		$this->query = "select $columns from $table";
		$this->params = array();
		$this->addWhereCondition($filter, $ro_columns, $w_columns);
	}
	
	public function buildInsert($table, $data, $ret_column, $ro_columns, $w_columns)
	{
		$this->params = array();		
		$columns = array();
		$dollars = '';
		
		foreach ((array)$data as $column => $value)
		{
			if (in_array($column, $ro_columns))
				throw new Exception("Trying to write to read-only column <em>$column</em> in table <em>" . strtoupper($table) . '</em>.', 400);				
			if (!in_array($column, $w_columns)) continue;
			
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

	public function buildUpdate($table, $filter, $data, $ret_column, $ro_columns, $w_columns)
	{
		$this->query = "update $table set";
		$this->params = array();
		
		foreach ((array)$data as $column => $value)
		{
			if (in_array($column, $ro_columns))
				throw new Exception("Trying to write to read-only column <em>$column</em> in table <em>" . strtoupper($table) . '</em>.', 400);								
			if (!in_array($column, $w_columns)) continue;
			
			if (is_null($value))
				$this->query .= " $column = null,";
			else
			{
				$this->params[] = $value;
				$this->query .= " $column = $" . count($this->params) . ',';
			}
		}
		$this->query = rtrim($this->query, ',');

		$this->addWhereCondition($filter, $ro_columns, $w_columns);
		
		if (!empty($ret_column))
			$this->query .= " returning $ret_column";
	}

	public function buildDelete($table, $filter, $ret_column, $ro_columns, $w_columns)
	{
		$this->query = "delete from $table";
		$this->params = array();
		$this->addWhereCondition($filter, $ro_columns, $w_columns);
		if (!empty($ret_column))
			$this->query .= " returning $ret_column";
	}
	
	private function addWhereCondition($filter, $ro_columns, $w_columns)
	{
		$this->query .= ' where true';
		foreach ((array)$filter as $column => $value)
		{
			if (!in_array($column, (array)$ro_columns) && !in_array($column, (array)$w_columns)) continue;

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