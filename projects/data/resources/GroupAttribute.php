<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table GROUP_ATTRIBUTE that holds groups' additional attributes.
 *
 * Columns of table GROUP_ATTRIBUTE are: <code>group_id, name, value, lang, parl, since, until</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>group_id, name, lang, parl, since</code>.
 */
class GroupAttribute
{
	/// instance holding a list of table columns and table handling functions
	private $attribute;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->attribute = new Attribute(array(
			'name' => 'group_attribute',
			'columns' => array('group_id', 'parl')
		));
	}

	/**
	 * Read the group attribute(s) that satisfy given parameters.
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
	 * read(array('group_id' => 54, 'name' => 'source_code', 'parl' => 'cz/psp'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [name] => source_code
	 *             [value] => 387
	 *             [lang] => -
	 *             [since] => 2010-06-20 12:00:00
	 *             [until] => infinity
	 *             [group_id] => 54
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
	 * Create a group attribute(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the attribute to create. Alternatively, an array of such attribute specifications.
	 * If \c since, \c until, \c lang or \c parl columns are ommitted, they are set to \c -infinity, \c infinity, \c -, \c -, respectively.
	 *
	 * \return An array of primary key values of the created attribute(s).
	 *
	 * \ex
	 * \code
	 * create(array('group_id' => 54, 'name' => 'source_code', 'value' => '387', 'parl' => 'cz/psp', 'since' => '2010-06-20'))
	 * \endcode creates a new group attribute and returns
	 * \code
	 * Array
	 * (
	 *     [group_id] => 54
	 *     [name] => source_code
	 *     [lang] => -
	 *     [parl] => cz/psp
	 *     [since] => 2010-06-20 12:00:00
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->attribute->create($data);
	}

	/**
	 * Update the given values of the group attributes that satisfy given parameters.
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
	 * Delete the group attribute(s) that satisfy given parameters.
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
