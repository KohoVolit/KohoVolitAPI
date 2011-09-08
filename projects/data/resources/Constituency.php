<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table CONSTITUENCY that holds constituencies a parliament is elected for.
 *
 * Columns of table CONSTITUENCY are: <code>id, name_, short_name, description, parliament_code, since, until</code>.
 *
 * Column <code>id</code> is a read-only column automaticaly generated on create.
 *
 * Primary key is column <code>id</code>.
 */
class Constituency
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'constituency',
			'columns' => array('id', 'name_', 'short_name', 'description', 'parliament_code', 'since', 'until'),
			'pkey_columns' => array('id'),
			'readonly_columns' => array('id')
		));
	}

	/**
	 * Read the constituency(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the constituencies to select.
	 * A special parameter \c \#datetime can be used (eg. '\#datetime' => '2010-06-30 9:30:00') to select only the constituencies
	 * valid at the given moment (the ones where \c since <= \c \#datetime < \c until).
	 * Use <code>'\#datetime' => 'now'</code> to get constituencies valid now.
	 *
	 * \return An array of constituencies that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('short_name' => '5', 'parliament_code' => 'cz/senat', '#datetime' => 'now'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 27
	 *             [name_] => Chomutov (5)
	 *             [short_name] => 5
	 *             [description] => celý okres Chomutov
	 *             [parliament_code] => cz/senat
	 *             [since] => -infinity
	 *             [until] => infinity
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
	 * Create a constituency(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the constituency to create. Alternatively, an array of such constituency specifications.
	 * If \c since and \c until columns are ommitted, they are set to \c -infinity, \c infinity, respectively.
	 *
	 * \return An array of primary key values of the created constituency(s).
	 *
	 * \ex
	 * \code
	 * create(array('name_' => 'Chomutov (5)', 'short_name' => '5', 'description' => 'celý okres Chomutov', 'parliament_code' => 'cz/senat'))
	 * \endcode creates a new constituency and returns something like
	 * \code
	 * Array
	 * (
	 *     [id] => 27
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the constituencies that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the constituencies to update. Only the constituencies that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated constituency.
	 *
	 * \return An array of primary key values of the updated constituencies.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the constituency(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the constituencies to delete. Only the constituencies that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted constituencies.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
