<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table LANGUAGE that holds languages for content translations.
 *
 * Columns of table LANGUAGE are: <code>code, name, short_name, description, locale</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key is column <code>code</code>.
 */
class Language
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'language',
			'columns' => array('code', 'name', 'short_name', 'description', 'locale'),
			'pkey_columns' => array('code')
		));
	}

	/**
	 * Read the language(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the languages to select.
	 *
	 * \return An array of languages that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('code' => 'cs'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [code] => cs
	 *             [name] => česky
	 *             [short_name] => čeština
	 *             [description] => 
	 *             [locale] => cs_CZ.UTF-8
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		return $this->entity->read($params);
	}

	/**
	 * Create a language(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the language to create. Alternatively, an array of such language specifications.
	 * \return An array of primary key values of the created language(s).
	 *
	 * \ex
	 * \code
	 * create(array('code' => 'sk', 'name' => 'po slovensky', 'short_name' => 'slovenčina', 'locale' => 'sk_SK.UTF-8'))
	 * \endcode creates a new language and returns
	 * \code
	 * Array
	 * (
	 *     [code] => sk
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the languages that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the languages to update. Only the languages that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated language.
	 *
	 * \return An array of primary key values of the updated languages.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the language(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the languages to delete. Only the languages that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted languages.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
