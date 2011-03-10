<?php

/**
 * ...
 */
class Db
{
	/// connection to the database
	private static $connection;

	/**
	 * ...
	 */
	public static function query($query, $params = array(), $user = 'kv_user', $parliament = null)
	{
		if (empty($query)) return array();

		self::open($user);

		if (!empty($parliament))
			pg_query(self::$connection, "set search_path to \"$parliament\", public");
			
		if (!empty($params))
			$result = pg_query_params(self::$connection, $query, $params);
		else
			$result = pg_query(self::$connection, $query);
			
		if ($result === false)
			throw new Exception('Query to the database failed: ' . pg_last_error(self::$connection) . '. Parameters: ' . print_r($params, true), 400);
			
		$res = pg_fetch_all($result);
		return ($res !== false) ? $res : array();
	}

	/**
	 * ...
	 */
	private static function open($user)
	{
		self::$connection = pg_connect(file_get_contents("conf/db/$user", true));
		
		if (!self::$connection)
			throw new Exception('Could not connect to database.', 503);
	}

	/**
	 * ...
	 */
	private static function close()
	{
		pg_close(self::$connection);
	}
}

?>
