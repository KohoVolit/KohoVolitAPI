<?php

/**
 * Class Language provides information about languages through API and implements CRUD operations on database table LANGUAGE.
 *
 * Columns of table LANGUAGE are: <em>code, name_, short_name, description, locale</em>. All columns are allowed to write to.
 */
class Language extends Entity
{
	/**
	 * Initialize list of column names of the table and which of them are read only (automatically generated on creation).
	 */
	public static function initColumnNames()
	{
		self::$tableColumns = array('code', 'name_', 'short_name', 'description', 'locale');
		self::$roColumns = array();
	}

	/**
	 * Read language(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the languages to select. Only languages satisfying all prescribed column values are returned.
	 *
	 * \return An array of languages with structure <code>array('language' => array(array('code' => 'en', 'name_' => 'in English', 'short_name' => 'English', ...), ...))</code>.
	 */
	public static function read($params)
	{
		return parent::readEntity($params, 'language_');
	}

	/**
	 * Create language(s) with given values.
	 *
	 * \param $data An array of languages to create, where each language is given by array of pairs <em>column => value</em>. Eg. <code>array(array('code' => 'en', 'name_' => 'in English', 'short_name' => 'English', ...), ...)</code>.
	 *
	 * \return An array of \e code-s of created languages.
	 */
	public static function create($data)
	{
		return parent::createEntity($params, 'language_', 'code');
	}

	/**
	 * Update language(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the languages to update. Only languages satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected language.
	 *
	 * \return An array of \e code-s of updated languages.
	 */
	public static function update($params, $data)
	{
		return parent::updateEntity($params, $data, 'language_', 'code');
	}

	/**
	 * Delete language(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the languages to delete. Only languages satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e code-s of deleted languages.
	 */
	public static function delete($params)
	{
		return parent::deleteEntity($params, 'language_', 'code');
	}
}

Language::initColumnNames();

?>
