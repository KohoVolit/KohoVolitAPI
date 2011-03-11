<?php

/**
 * Class Language provides information about languages through API and implements CRUD operations on database table LANGUAGE.
 *
 * Columns of table LANGUAGE are: <em>code, name_, short_name, description, locale</em>. All columns are allowed to write to.
 */
class Language
{
	/// columns of the table LANGUAGE
	private static $tableColumns = array('code', 'name_', 'short_name', 'description', 'locale');

	/**
	 * Retrieve language(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the languages to select. Only languages satisfying all prescribed column values are returned.
	 *
	 * \return An array of languages with structure <code>array('language' => array(array('code' => 'en', 'name_' => 'in English', 'short_name' => 'English', ...), ...))</code>.
	 */
	public static function retrieve($params)
	{
		$query = new Query();
		$query->buildSelect('language', '*', $params, self::$tableColumns);
		$languages = $query->execute();
		return array('language' => $languages);
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
		$query = new Query('kv_admin');
		$codes = array();
		$query->startTransaction();
		foreach ((array)$data as $language)
		{
			$query->buildInsert('language', $language, 'code', self::$tableColumns);
			$res = $query->execute();
			$codes[] = $res[0]['code'];
			// in case of an exception thrown by Query::execute, the transaction is rolled back in destructor of $query variable; thus no data are inserted into database by this call of create()
		}
		$query->commitTransaction();
		return $codes;
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
		$query = new Query('kv_admin');
		$query->buildUpdate('language', $params, $data, 'code', self::$tableColumns);
		$res = $query->execute();
		$codes = array();
		foreach ((array)$res as $line)
			$codes[] = $line['code'];
		return $codes;
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
		$query = new Query('kv_admin');
		$query->buildDelete('language', $params, 'code', self::$tableColumns);
		$res = $query->execute();
		$codes = array();
		foreach ((array)$res as $line)
			$codes[] = $line['code'];
		return $codes;
	}
}

?>
