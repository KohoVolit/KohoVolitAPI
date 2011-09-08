<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table PARLIAMENT_KIND that holds kinds of parliament.
 *
 * Columns of table PARLIAMENT_KIND are: <code>code, name_, short_name, description</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key is column <code>code</code>.
 */
class ParliamentKind
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'parliament_kind',
			'columns' => array('code', 'name_', 'short_name', 'description'),
			'pkey_columns' => array('code')
		));
	}

	/**
	 * Read the parliament kind(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliament kinds to select.
	 *
	 * \return An array of parliament kinds that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('code' => 'national-upper'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [code] => national-upper
	 *             [name_] => Upper house of the national parliament
	 *             [short_name] => Upper house
	 *             [description] => Upper house of the national level parliament - senate.
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
	 * Create a parliament kind(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the parliament kind to create. Alternatively, an array of such parliament kind specifications.
	 * \return An array of primary key values of the created parliament kind(s).
	 *
	 * \ex
	 * \code
	 * create(array('code' => 'local', 'name_' => 'Local parliament', 'short_name' => 'Local', 'description' => 'Parliament at a city level.'))
	 * \endcode creates a new parliament kind and returns
	 * \code
	 * Array
	 * (
	 *     [code] => local
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the parliament kinds that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliament kinds to update. Only the parliament kinds that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated parliament kind.
	 *
	 * \return An array of primary key values of the updated parliament kinds.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the parliament kind(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliament kinds to delete. Only the parliament kinds that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted parliament kinds.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
