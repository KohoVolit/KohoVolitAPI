<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table MP_ATTRIBUTE that holds MPs' additional attributes.
 *
 * Columns of table MP_ATTRIBUTE are: <code>gmp_id, name_, value_, lang, parl, since, until</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>mp_id, name_, lang, parl, since</code>.
 */
class MpAttribute
{
	/// instance holding a list of table columns and table handling functions
	private $attribute;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Attribute(array(
			'name' => 'mp_attribute',
			'columns' => array('mp_id', 'parl')
		));
	}

	/**
	 * Read the MP attribute(s) that satisfy given parameters.
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
	 * read(array('mp_id' => 556, 'name_' => 'email', 'parl' => 'cz/psp'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [name_] => email
	 *             [value_] => dedicf@psp.cz
	 *             [lang] => -
	 *             [since] => 2010-05-29 00:00:00
	 *             [until] => infinity
	 *             [mp_id] => 556
	 *             [parl] => cz/psp
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
	 * Create a MP attribute(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the attribute to create. Alternatively, an array of such attribute specifications.
	 * If \c since, \c until, \c lang or \c parl columns are ommitted, they are set to \c -infinity, \c infinity, \c -, \c -, respectively.
	 *
	 * \return An array of primary key values of the created attribute(s).
	 *
	 * \ex
	 * \code
	 * create(array('mp_id' => 556, 'name_' => 'assistant', 'value_' => 'Martin Schuster, Richard Střelka', 'parl' => 'cz/psp', 'since' => '2011-05-29'))
	 * \endcode creates a new MP attribute and returns
	 * \code
	 * Array
	 * (
	 *     [mp_id] => 556
	 *     [name_] => assistant
	 *     [lang] => -
	 *     [parl] => cz/psp
	 *     [since] => 2011-05-29 00:00:00
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->attribute->create($data);
	}

	/**
	 * Update the given values of the MP attributes that satisfy given parameters.
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
	 * Delete the MP attribute(s) that satisfy given parameters.
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
