<?php

/**
 * Class MpAttribute provides information about MPs' additional attributes through API and implements CRUD operations on database table MP_ATTRIBUTE.
 *
 * Columns of table MP_ATTRIBUTE are: <em>mp_id, parl</em> and columns common for all attribute tables defined in the base class Attribute. All columns are allowed to write to.
 */
class MpAttribute
{
	/// instance holding a list of table columns and table handling functions
	private static $attribute;

	/**
	 * Initialize information about the attribute table.
	 */
	public static function init()
	{
		self::$attribute = new Attribute(
			'mp_attribute',
			array('mp_id', 'parl')
		);
	}

	/**
	 * Read MP(s)' attributes according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to select. Only attributes satisfying all prescribed column values are returned.
	 *
	 * \return An array of attributes with structure <code>array(array('mp_id' => 32, 'name_' => 'hobbies', 'value_' => 'eating, smoking', ...), ...)</code>.
	 *
	 * You can use <em>#datetime</em> within the <em>$params</em> (eg. '#datetime' => '2010-06-30 9:30:00') to select only attributes valid at the given moment (the ones where <em>since</em> <= #datetime < <em>until</em>). Use '#datetime' => 'now' to get attributes valid at this moment.
	 */
	public static function read($params)
	{
		return self::$attribute->read($params);
	}

	/**
	 * Create MP(s)' attributes with given values.
	 *
	 * \param $data An array of attributes to create, where each attribute is given by array of pairs <em>column => value</em>. Eg. <code>array(array('mp_id' => 32, 'name_' => 'hobbies', 'value_' => 'eating, smoking', ...), ...)</code>.
	 *
	 * \return An array of primary keys (mp_id, name_, lang, parl, since) of created attributes.
	 */
	public static function create($data)
	{
		return self::$attribute->create($data);
	}

	/**
	 * Update MP(s)' attributes satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to update. Only attributes satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected attribute.
	 *
	 * \return An array of primary keys (mp_id, name_, lang, parl, since) of updated attributes.
	 */
	public static function update($params, $data)
	{
		return self::$attribute->update($params, $data);
	}

	/**
	 * Delete MP(s)' attributes according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to delete. Only attributes satisfying all prescribed column values are deleted.
	 *
	 * \return An array of primary keys (mp_id, name_, lang, parl, since) of deleted attributes.
	 */
	public static function delete($params)
	{
		return self::$attribute->delete($params);
	}
}

MpAttribute::init();

?>
