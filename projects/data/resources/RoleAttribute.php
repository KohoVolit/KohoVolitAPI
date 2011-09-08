<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table ROLE_ATTRIBUTE that holds MP roles' additional attributes.
 *
 * Columns of table ROLE_ATTRIBUTE are: <code>role_code, name_, value_, lang, parl, since, until</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>role_code, name_, lang, parl, since</code>.
 */
class RoleAttribute
{
	/// instance holding a list of table columns and table handling functions
	private $attribute;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Attribute(array(
			'name' => 'role_attribute',
			'columns' => array('role_code', 'parl')
		));
	}

	/**
	 * Read the role attribute(s) that satisfy given parameters.
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
	 * read(array('role_code' => 'chairman', 'name_' => 'icon'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [name_] => icon
	 *             [value_] => roles/chairman.gif
	 *             [lang] => -
	 *             [since] => -infinity
	 *             [until] => infinity
	 *             [role_code] => chairman
	 *             [parl] => -
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
	 * Create a role attribute(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the attribute to create. Alternatively, an array of such attribute specifications.
	 * If \c since, \c until, \c lang or \c parl columns are ommitted, they are set to \c -infinity, \c infinity, \c -, \c -, respectively.
	 *
	 * \return An array of primary key values of the created attribute(s).
	 *
	 * \ex
	 * \code
	 * create(array('role_code' => chairman, 'name_' => 'female_name', 'value_' => 'predsedníčka', 'lang' => 'sk'))
	 * \endcode creates a new role attribute and returns
	 * \code
	 * Array
	 * (
	 *     [role_code] => chairman
	 *     [name_] => female_name
	 *     [lang] => sk
	 *     [parl] => -
	 *     [since] => -infinity
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->attribute->create($data);
	}

	/**
	 * Update the given values of the role attributes that satisfy given parameters.
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
	 * Delete the role attribute(s) that satisfy given parameters.
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
