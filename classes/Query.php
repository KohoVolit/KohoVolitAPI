<?php

/**
 * ...
 * If a table contains \e since and \e until columns then \e _datetime can be used within parameters of \c read(), \c update() and \c delete() methods (eg. '_datetime' => '2010-06-30 9:30:00') that restricts the where condition to only entities valid at the given moment (the ones where <em>since</em> <= _datetime < <em>until</em>). Value '_datetime' => 'now' can be used to get entities valid now.
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

	public function buildSelect($table, $columns, $filter, $table_columns)
	{
		foreach ($table_columns as $tc)
			$columns = str_replace($tc, '"' . $tc . '"', $columns);
		$this->query = "select $columns from \"$table\"";
		$this->params = array();
		$this->addWhereCondition($filter, $table_columns);
		if (isset($filter['_limit']))
		{
			$this->params[] = $filter['_limit'];
			$this->query .= ' limit $' . count($this->params);
		}
		if (isset($filter['_offset']))
		{
			$this->params[] = $filter['_offset'];
			$this->query .= ' offset $' . count($this->params);
		}
	}

	public function buildInsert($table, $data, $table_columns, $return_columns = array(), $ro_columns = array())
	{
		$this->params = array();
		$columns = array();
		$dollars = '';

		foreach ((array)$data as $column => $value)
		{
			if (in_array($column, $ro_columns, true))
				throw new Exception("Trying to write to read-only column <em>$column</em> in table <em>" . strtoupper($table) . '</em>.', 400);
			if (!in_array($column, $table_columns, true)) continue;

			$columns[] = $column;
			if (is_null($value))
				$dollars .= 'null, ';
			else
			{
				$this->params[] = $value;
				$dollars .= '$' . count($this->params) . ', ';
			}
		}
		$this->query = "insert into \"$table\" (\"" . implode('", "', $columns) . '") values (' . rtrim($dollars, ', ') . ')';

		if (!empty($return_columns))
			$this->query .= ' returning "' . implode('", "', $return_columns) . '"';
	}

	public function buildUpdate($table, $filter, $data, $table_columns, $return_columns = array(), $ro_columns = array())
	{
		$this->query = "update \"$table\" set";
		$this->params = array();

		foreach ((array)$data as $column => $value)
		{
			if (in_array($column, $ro_columns, true))
				throw new Exception("Trying to write to read-only column <em>$column</em> in table <em>" . strtoupper($table) . '</em>.', 400);
			if (!in_array($column, $table_columns, true)) continue;

			if (is_null($value))
				$this->query .= " \"$column\" = null,";
			else
			{
				$this->params[] = $value;
				$this->query .= " \"$column\" = $" . count($this->params) . ',';
			}
		}
		$this->query = rtrim($this->query, ',');

		$this->addWhereCondition($filter, $table_columns);

		if (!empty($return_columns))
			$this->query .= ' returning "' . implode('", "', $return_columns) . '"';
	}

	public function buildDelete($table, $filter, $table_columns, $return_columns = array())
	{
		$this->query = "delete from \"$table\"";
		$this->params = array();
		$this->addWhereCondition($filter, $table_columns);
		if (!empty($return_columns))
			$this->query .= ' returning "' . implode('", "', $return_columns) . '"';
	}

	private function addWhereCondition($filter, $table_columns)
	{
		$this->query .= ' where true';
		if (!is_array($filter) || !is_array($table_columns)) return;

		foreach ($filter as $column => $value)
		{
			if (!in_array($column, $table_columns, true)) continue;

			if (is_null($value))
				$this->query .= " and \"$column\" is null";
			else
			{
				$this->params[] = $value;
				$this->query .= " and \"$column\" = $" . count($this->params);
			}
		}
		if (isset($filter['_datetime']) && in_array('since', $table_columns, true) && in_array('until', $table_columns, true))
		{
			$this->params[] = $filter['_datetime'];
			$n = count($this->params);
			$this->query .= ' and since <= $' . $n . ' and until > $' . $n;
		}
	}
}

?>