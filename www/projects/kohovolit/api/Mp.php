<?php

/**
 * ...
 */
class Mp
{
	/// read-only columns of the table MP
	private static $roColumns = array('id');

	/// columns of the table MP allowed to write to
	private static $wColumns = array('first_name', 'middle_names', 'last_name', 'disambiguation', 'sex', 'pre_title', 'post_title', 'born_on', 'died_on', 'email', 'webpage', 'address', 'phone', 'source', 'source_code');

	/**
	 * ...
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('mp', '*', $params, self::$roColumns, self::$wColumns);
		$mps = $query->execute();
		return array('mp' => $mps);
	}

	/**
	 * ...
	 */
	public static function create($data)
	{
		$query = new Query();
		$ids = array();
		foreach ((array)$data as $mp)
		{
			$query->buildInsert('mp', $mp, 'id', self::$roColumns, self::$wColumns);
			$res = $query->execute('kv_admin');
			$ids[] = $res[0]['id'];
		}
		return $ids;
	}

	/**
	 * ...
	 */
	public static function update($params, $data)
	{
		$query = new Query();
		$query->buildUpdate('mp', $params, $data, 'id', self::$roColumns, self::$wColumns);
		$res = $query->execute('kv_admin');
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line['id'];
		return $ids;
	}

	/**
	 * ...
	 */
	public static function delete($params)
	{
		$query = new Query();
		$query->buildDelete('mp', $params, 'id', self::$roColumns, self::$wColumns);
		$res = $query->execute('kv_admin');
		$ids = array();
		foreach ((array)$res as $line)
			$ids[] = $line['id'];
		return $ids;
	}
}

?>
