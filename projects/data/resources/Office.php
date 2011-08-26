<?php

/**
 * Class Office provides information about MPs' offices through API and implements CRUD operations on database table OFFICE.
 *
 * Columns of table OFFICE are: <em>mp_id, parliament_code, address, phone, latitude, longitude, relevance, since, until</em>. All columns are allowed to write to.
 */
class Office
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity(
			'office',
			array('mp_id', 'parliament_code', 'address', 'phone', 'latitude', 'longitude', 'relevance', 'since', 'until'),
			array('mp_id', 'parliament_code', 'address', 'since')
		);
	}

	/**
	 * Read MP office(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the offices to select. Only offices satisfying all prescribed column values are returned.
	 *
	 * \return An array of offices with structure <code>array(array('mp_id' => 32, 'parliament_code' => 'me/shc', 'address' => '|Bag end|12|Hobbiton|SH-12345|Middle-earth', 'phone' => '+421 123 456 789', ...), ...)</code>.
	 *
	 * You can use <em>#datetime</em> within the <em>$params</em> (eg. '#datetime' => '2010-06-30 9:30:00') to select only offices valid at the given moment (the ones where <em>since</em> <= #datetime < <em>until</em>). Use '#datetime' => 'now' to get offices valid at this moment.
	 */
	public static function read($params)
	{
		return self::$entity->read($params);
	}

	/**
	 * Create MP office(s) with given values.
	 *
	 * \param $data An array of offices to create, where each office is given by array of pairs <em>column => value</em>. Eg. <code>array(array('mp_id' => 32, 'parliament_code' => 'me/shc', 'address' => '|Bag end|12|Hobbiton|SH-12345|Middle-earth', 'phone' => '+421 123 456 789', ...), ...)</code>.
	 *
	 * \return Number of created offices.
	 */
	public static function create($data)
	{
		return self::$entity->create($data);
	}

	/**
	 * Update MP office(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the offices to update. Only offices satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected office.
	 *
	 * \return Number of updated offices.
	 */
	public static function update($params, $data)
	{
		return self::$entity->update($params, $data);
	}

	/**
	 * Delete MP office(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the offices to delete. Only offices satisfying all prescribed column values are deleted.
	 *
	 * \return Number of deleted offices.
	 */
	public static function delete($params)
	{
		return self::$entity->delete($params);
	}
}

Office::init();

?>