<?php

/**
 * Class Language provides information about languages through API and implements CRUD operations on database table LANGUAGE.
 *
 * Columns of table LANGUAGE are: <em>code, name_, short_name, description, locale</em>. All columns are allowed to write to.
 */
class Language
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity(
			'language_',
			array('code', 'name_', 'short_name', 'description', 'locale'),
			array('code')
		);
	}

	/**
	 * Read language(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the languages to select. Only languages satisfying all prescribed column values are returned.
	 *
	 * \return An array of languages with structure <code>array(array('code' => 'en', 'name_' => 'in English', 'short_name' => 'English', ...), ...)</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params);
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
		return self::$entity->create($data);
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
		return self::$entity->update($params, $data);
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
		return self::$entity->delete($params);
	}
}

Language::init();

?>
