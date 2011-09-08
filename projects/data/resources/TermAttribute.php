<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table TERM_ATTRIBUTE that holds terms' additional attributes.
 *
 * Columns of table TERM_ATTRIBUTE are: <code>term_id, name_, value_, lang, parl, since, until</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>term_id, name_, lang, parl, since</code>.
 */
class TermAttribute
{
	/// instance holding a list of table columns and table handling functions
	private $attribute;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->attribute = new Attribute(array(
			'name' => 'term_attribute',
			'columns' => array('term_id', 'parl')
		));
	}

	/**
	 * Read the term attribute(s) that satisfy given parameters.
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
	 * read(array('term_id' => 7, 'name_' => 'source_code', 'parl' => 'cz/senat'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [name_] => source_code
	 *             [value_] => 8
	 *             [lang] => -
	 *             [since] => -infinity
	 *             [until] => infinity
	 *             [term_id] => 7
	 *             [parl] => cz/senat
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
	 * Create a term attribute(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the attribute to create. Alternatively, an array of such attribute specifications.
	 * If \c since, \c until, \c lang or \c parl columns are ommitted, they are set to \c -infinity, \c infinity, \c -, \c -, respectively.
	 *
	 * \return An array of primary key values of the created attribute(s).
	 *
	 * \ex
	 * \code
	 * create(array('term_id' => 7, 'name_' => 'source_code', 'value_' => '8', 'parl' => 'cz/senat'))
	 * \endcode creates a new term attribute and returns
	 * \code
	 * Array
	 * (
	 *     [term_id] => 7
	 *     [name_] => source_code
	 *     [lang] => -
	 *     [parl] => cz/senat
	 *     [since] => -infinity
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->attribute->create($data);
	}

	/**
	 * Update the given values of the term attributes that satisfy given parameters.
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
	 * Delete the term attribute(s) that satisfy given parameters.
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
