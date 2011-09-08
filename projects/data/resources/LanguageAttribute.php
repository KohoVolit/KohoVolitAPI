<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table LANGUAGE_ATTRIBUTE that holds languages' additional attributes.
 *
 * Columns of table LANGUAGE_ATTRIBUTE are: <code>language_code, name_, value_, lang, since, until</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>language_code, name_, lang, since</code>.
 */
class LanguageAttribute
{
	/// instance holding a list of table columns and table handling functions
	private $attribute;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->attribute = new Attribute(array(
			'name' => 'language_attribute',
			'columns' => array('language_code')
		));
	}

	/**
	 * Read the language attribute(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to select.
	 * A special parameter \c \#datetime can be used (eg. '\#datetime' => '2010-06-30 9:30:00') to select only the attributes
	 * valid at the given moment (the ones where \c since <= \c \#datetime < \c until).
	 * Use <code>'\#datetime' => 'now'</code> to get attributes valid now.
	 *
	 * \return An array of attributes that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('language_code' => 'sk', 'name_' => 'flag'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [name_] => flag
	 *             [value_] => sk.gif
	 *             [lang] => -
	 *             [since] => -infinity
	 *             [until] => infinity
	 *             [language_code] => sk
	 *         ) 
	 * 
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		return $this->attribute->read($params);
	}

	/**
	 * Create a language attribute(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the attribute to create. Alternatively, an array of such attribute specifications.
	 * If \c since, \c until or \c lang columns are ommitted, they are set to \c -infinity, \c infinity, \c -, respectively.
	 *
	 * \return An array of primary key values of the created attribute(s).
	 *
	 * \ex
	 * \code
	 * create(array('language_code' => 'sk', 'name_' => 'flag', 'value_' => 'sk.gif'))
	 * \endcode creates a new language attribute and returns
	 * \code
	 * Array
	 * (
	 *     [language_code] => sk
	 *     [name_] => flag
	 *     [lang] => -
	 *     [since] => -infinity
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->attribute->create($data);
	}

	/**
	 * Update the given values of the language attributes that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to update. Only the attributes that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated attribute.
	 *
	 * \return An array of primary key values of the updated attributes.
	 */
	public function update($params, $data)
	{
		return $this->attribute->update($params, $data);
	}

	/**
	 * Delete the language attribute(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the attributes to delete. Only the attributes that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted attributes.
	 */
	public function delete($params)
	{
		return $this->attribute->delete($params);
	}
}

?>
