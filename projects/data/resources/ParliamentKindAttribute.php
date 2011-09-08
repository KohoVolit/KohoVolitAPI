<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table PARLIAMENT_KIND_ATTRIBUTE that holds parliament kinds' additional attributes.
 *
 * Columns of table PARLIAMENT_KIND_ATTRIBUTE are: <code>parliament_kind_code, name_, value_, lang, since, until</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>parliament_kind_code, name_, lang, since</code>.
 */
class ParliamentKindAttribute
{
	/// instance holding a list of table columns and table handling functions
	private $attribute;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->attribute = new Attribute(array(
			'name' => 'parliament_kind_attribute',
			'columns' => array('parliament_kind_code')
		));
	}

	/**
	 * Read the parliament kind attribute(s) that satisfy given parameters.
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
	 * read(array('parliament_kind_code' => 'regional', 'name_' => 'short_name', 'lang' => 'cs'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [name_] => short_name
	 *             [value_] => Krajský
	 *             [lang] => cs
	 *             [since] => -infinity
	 *             [until] => infinity
	 *             [parliament_kind_code] => regional
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
	 * Create a parliament kind attribute(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the attribute to create. Alternatively, an array of such attribute specifications.
	 * If \c since, \c until or \c lang columns are ommitted, they are set to \c -infinity, \c infinity, \c -, respectively.
	 *
	 * \return An array of primary key values of the created attribute(s).
	 *
	 * \ex
	 * \code
	 * create(array('parliament_kind_code' => 'regional', 'name_' => 'name', 'value_' => 'Krajské zastupitelstvo', 'lang' => 'cs'))
	 * \endcode creates a new parliament kind attribute and returns
	 * \code
	 * Array
	 * (
	 *     [parliament_kind_code] => regional
	 *     [name_] => name
	 *     [lang] => cs
	 *     [since] => -infinity
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->attribute->create($data);
	}

	/**
	 * Update the given values of the parliament kind attributes that satisfy given parameters.
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
	 * Delete the parliament kind attribute(s) that satisfy given parameters.
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
