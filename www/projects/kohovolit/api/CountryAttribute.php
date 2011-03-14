<?php

/**
 * Class CountryAttribute provides information about countries' additional attributes through API and implements CRUD operations on database table COUNTRY_ATTRIBUTE.
 *
 * Columns of table COUNTRY_ATTRIBUTE are: <em>country_code</em> and columns common for all attribute tables defined in the base class Attribute. All columns are allowed to write to.
 */ 
class CountryAttribute extends Attribute
{
	/**
	 * Add a table specific column to the list of common columns of all attribute tables.
	 */
	public static function initColumnNames()
	{
		self::$tableColumns[] = 'country_code';
	}

	/**
	 * Read country(s)' attributes according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to select. Only attributes satisfying all prescribed column values are returned.
	 *
	 * \return An array of attributes with structure <code>array('country_attribute' => array(array('country_code' => 'cz', 'name_' => 'flag', 'value_' => 'czech.gif', '-', ...), ...))</code>.
	 *
	 * You can use <em>datetime</em> within the <em>$params</em> (eg. 'datetime' => '2010-06-30 9:30:00') to select only attributes valid at the given moment (the ones where <em>since</em> <= datetime < <em>until</em>). Use 'datetime' => 'now' to get attributes valid at this moment.
	 */
	public static function read($params)
	{
		return parent::readAttribute($params, 'country_attribute');
	}

	/**
	 * Create country(s)' attributes with given values.
	 *
	 * \param $data An array of attributes to create, where each attribute is given by array of pairs <em>column => value</em>. Eg. <code>array(array('country_code' => 'cz', 'name_' => 'flag', 'value_' => 'czech.gif', '-', ...), ...)</code>.
	 *
	 * \return Number of created attributes.
	 */
	public static function create($data)
	{
		return parent::createAttribute($data, 'country_attribute');
	}

	/**
	 * Update country(s)' attributes satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to update. Only attributes satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected attribute.
	 *
	 * \return Number of updated attributes.
	 */
	public static function update($params, $data)
	{
		return parent::updateAttribute($params, $data, 'country_attribute');
	}

	/**
	 * Delete country(s)' attributes according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to delete. Only attributes satisfying all prescribed column values are deleted.
	 *
	 * \return Number of deleted attributes.
	 */
	public static function delete($params)
	{
		return parent::deleteAttribute($params, 'country_attribute');
	}
}

CountryAttribute::initColumnNames();

?>
