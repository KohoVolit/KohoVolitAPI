<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table COUNTRY_ATTRIBUTE that holds countries' additional attributes.
 *
 * Columns of table COUNTRY_ATTRIBUTE are: <code>country_code, name_, value_, lang, since, until</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>country_code, name_, lang, since</code>.
 */
class CountryAttribute
{
	/// instance holding a list of table columns and table handling functions
	private $attribute;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Attribute(array(
			'name' => 'country_attribute',
			'columns' => array('country_code')
		));
	}

	/**
	 * Read the country attribute(s) that satisfy given parameters.
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
	 * read(array('country_code' => 'cz', 'name_' => 'name', 'lang' => 'sk'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [name_] => name
	 *             [value_] => Česká republika
	 *             [lang] => sk
	 *             [since] => -infinity
	 *             [until] => infinity
	 *             [country_code] => cz
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
	 * Create a country attribute(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the attribute to create. Alternatively, an array of such attribute specifications.
	 * If \c since, \c until or \c lang columns are ommitted, they are set to \c -infinity, \c infinity, \c -, respectively.
	 *
	 * \return An array of primary key values of the created attribute(s).
	 *
	 * \ex
	 * \code
	 * create(array('country_code' => 'cz', 'name_' => 'short_name', 'value_' => 'ČR', 'lang' => 'sk'))
	 * \endcode creates a new country attribute and returns
	 * \code
	 * Array
	 * (
	 *     [country_code] => cz
	 *     [name_] => short_name
	 *     [lang] => sk
	 *     [since] => -infinity
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->attribute->create($data);
	}

	/**
	 * Update the given values of the country attributes that satisfy given parameters.
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
	 * Delete the country attribute(s) that satisfy given parameters.
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
