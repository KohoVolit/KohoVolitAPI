<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table DIVISION_KIND that holds kinds of divisions (eg.\ 'simple majority', 'absolute majority', etc.).
 *
 * Columns of table DIVISION_KIND are: <code>code, name, description</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key is column <code>code</code>.
 */
class DivisionKind
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'division_kind',
			'columns' => array('code', 'name', 'description'),
			'pkey_columns' => array('code')
		));
	}

	/**
	 * Read the division kind(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the division kinds to select.
	 * \return An array of division kinds that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('code' => 'absolute'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [code] => absolute
	 *             [name] => absolute majority
	 *             [description] => More than half of all representatives.
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
	 * Create a division kind(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the division kind to create. Alternatively, an array of such division kind specifications.
	 *
	 * \return An array of primary key values of the created division kind(s).
	 *
	 * \ex
	 * \code
	 * create(array('code' => '3/5', 'name' => '3/5 absolute majority', 'description' => 'More than three fifths of all representatives.'))
	 * \endcode creates a new division kind and returns
	 * \code
	 * Array
	 * (
	 *     [code] => 3/5
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the division kinds that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the division kinds to update. Only the division kinds that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated division kind.
	 *
	 * \return An array of primary key values of the updated division kinds.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the division kind(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the division kinds to delete. Only the division kinds that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted division kinds.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
