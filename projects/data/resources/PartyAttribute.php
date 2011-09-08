<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table PARTY_ATTRIBUTE that holds parties' additional attributes.
 *
 * Columns of table PARTY_ATTRIBUTE are: <code>party_id, name_, value_, lang, parl, since, until</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>party_id, name_, lang, parl, since</code>.
 */
class PartyAttribute
{
	/// instance holding a list of table columns and table handling functions
	private $attribute;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->attribute = new Attribute(array(
			'name' => 'party_attribute',
			'columns' => array('party_id', 'parl')
		));
	}

	/**
	 * Read the party attribute(s) that satisfy given parameters.
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
	 * read(array('party_id' => 83, 'name_' => 'logo'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [name_] => logo
	 *             [value_] => parties/cz/cssd.gif
	 *             [lang] => -
	 *             [since] => -infinity
	 *             [until] => infinity
	 *             [party_id] => 83
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
	 * Create a party attribute(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the attribute to create. Alternatively, an array of such attribute specifications.
	 * If \c since, \c until, \c lang or \c parl columns are ommitted, they are set to \c -infinity, \c infinity, \c -, \c -, respectively.
	 *
	 * \return An array of primary key values of the created attribute(s).
	 *
	 * \ex
	 * \code
	 * create(array('group_id' => 83, 'name_' => 'logo', 'value_' => 'parties/cz/cssd.gif'))
	 * \endcode creates a new party attribute and returns
	 * \code
	 * Array
	 * (
	 *     [party_id] => 83
	 *     [name_] => logo
	 *     [lang] => -
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
	 * Update the given values of the party attributes that satisfy given parameters.
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
	 * Delete the party attribute(s) that satisfy given parameters.
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
