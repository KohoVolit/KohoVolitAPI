<?php

/**
 * ...
 */
class MpAttribute
{
	/// read-only columns of the table MP
	private static $roColumns = array();

	/// columns of the table MP allowed to write to
	private static $wColumns = array('mp_id', 'lang', 'since', 'until', 'name_', 'value_');

	/**
	 * ...
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('mp_attribute', '*', $params, self::$roColumns, self::$wColumns);
		if (!empty($params['datetime']))
		{
			$query->appendParam($params['datetime']);
			$n = $query->getParamsCount();
			$query->appendQuery(' and since <= $' . $n . ' and until > $' . $n);
		}		
		$attrs = $query->execute();
		return array('mp_attribute' => $attrs);
	}

	/**
	 * ...
	 */
	public static function create($data)
	{
		$query = new Query();
		foreach ((array)$data as $attr)
		{
			$query->buildInsert('mp_attribute', $attr, null, self::$roColumns, self::$wColumns);
			$query->execute('kv_admin');
		}
		return count($data);
	}

	/**
	 * ...
	 */
	public static function update($params, $data)
	{
		$query = new Query();
		$query->buildUpdate('mp_attribute', $params, $data, null, self::$roColumns, self::$wColumns);
		$res = $query->execute('kv_admin');
		return count($res);
	}

	/**
	 * ...
	 */
	public static function delete($params)
	{
		$query = new Query();
		$query->buildDelete('mp_attribute', $params, null, self::$roColumns, self::$wColumns);
		$res = $query->execute('kv_admin');
		return count($res);
	}
}

?>
