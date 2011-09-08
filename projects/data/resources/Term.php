<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table TERM that holds terms of office a parliament is elected in.
 *
 * Columns of table TERM are: <code>id, name_, short_name, description, country_code, parliament_kind_code, since, until</code>.
 *
 * Column <code>id</code> is a read-only column automaticaly generated on create.
 *
 * Primary key is column <code>id</code>.
 */
class Term
{
		/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'term',
			'columns' => array('id', 'name_', 'short_name', 'description', 'country_code', 'parliament_kind_code', 'since', 'until'),
			'pkey_columns' => array('id'),
			'readonly_columns' => array('id')
		));
	}

	/**
	 * Read the term(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the terms to select.
	 * A special parameter \c \#datetime can be used (eg. '\#datetime' => '2010-06-30 9:30:00') to select only the terms
	 * open at the given moment (the ones where \c since <= \c \#datetime < \c until).
	 * Use <code>'\#datetime' => 'now'</code> to get terms open now.
	 *
	 * \return An array of terms that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('country_code' => 'cz', 'parliament_kind_code' => 'national-lower', '#datetime' => 'now'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 6
	 *             [name_] => od 2010
	 *             [short_name] => 
	 *             [description] => 
	 *             [country_code] => cz
	 *             [parliament_kind_code] => national-lower
	 *             [since] => 2010-05-29 00:00:00
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
	 * Create a term(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the term to create. Alternatively, an array of such term specifications.
	 * If \c since and \c until columns are ommitted, they are set to \c -infinity, \c infinity, respectively.
	 *
	 * \return An array of primary key values of the created term(s).
	 *
	 * \ex
	 * \code
	 * create(array('name_' => '2006 - 2010', 'country_code' => 'sk', 'parliament_kind_code' => 'local', 'since' => '2006-12-03', 'until' => '2010-11-28'))
	 * \endcode creates a new term and returns something like
	 * \code
	 * Array
	 * (
	 *     [id] => 14
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the terms that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the terms to update. Only the terms that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated term.
	 *
	 * \return An array of primary key values of the updated terms.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the term(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the terms to delete. Only the terms that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted terms.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
