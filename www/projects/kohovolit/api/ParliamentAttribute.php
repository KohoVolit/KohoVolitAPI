<?php

include 'classes/kohovolit/Attribute.php';

/**
 * Class ParliamentAttribute provides information about parliaments' additional attributes through API and implements CRUD operations on database table PARLIAMENT_ATTRIBUTE.
 *
 * Columns of table PARLIAMENT_ATTRIBUTE are: <em>parliament_code</em> and columns common for all attribute tables defined in the base class Attribute. All columns are allowed to write to.
 */
class ParliamentAttribute extends Attribute
{
	/**
	 * Add a table specific column to the list of common columns of all attribute tables.
	 */
	public static function initColumnNames()
	{
		self::$tableColumns[] = 'parliament_code';
	}

	/**
	 * Retrieve parliament(s)' attributes according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to select. Only attributes satisfying all prescribed column values are returned.
	 *
	 * \return An array of attributes with structure <code>array('parliament_attribute' => array(array('parliament_code' => 'cz/psp', 'name_' => 'last update', 'value_' => '2011-05-24', ...), ...))</code>.
	 *
	 * You can use <em>datetime</em> within the <em>$params</em> (eg. 'datetime' => '2010-06-30 9:30:00') to select only attributes valid at the given moment (the ones where <em>since</em> <= datetime < <em>until</em>). Use 'datetime' => 'now' to get attributes valid at this moment.
	 */
	public static function retrieve($params)
	{
		return parent::retrieveAttr($params, 'parliament_attribute');
	}

	/**
	 * Create MP parliament(s)' attributes with given values.
	 *
	 * \param $data An array of attributes to create, where each attribute is given by array of pairs <em>column => value</em>. Eg. <code>array(array('parliament_code' => 'cz/psp', 'name_' => 'last update', 'value_' => '2011-05-24', ...), ...)</code>.
	 *
	 * \return Number of created attributes.
	 */
	public static function create($data)
	{
		return parent::createAttr($data, 'parliament_attribute');
	}

	/**
	 * Update MP parliament(s)' attributes satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to update. Only attributes satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected attribute.
	 *
	 * \return Number of updated attributes.
	 */
	public static function update($params, $data)
	{
		return parent::updateAttr($params, $data, 'parliament_attribute');
	}

	/**
	 * Delete MP parliament(s)' attributes according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to delete. Only attributes satisfying all prescribed column values are deleted.
	 *
	 * \return Number of deleted attributes.
	 */
	public static function delete($params)
	{
		return parent::deleteAttr($params, 'parliament_attribute');
	}
}

ParliamentAttribute::initColumnNames();

?>
