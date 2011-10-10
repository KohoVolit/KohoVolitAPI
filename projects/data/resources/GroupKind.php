<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table GROUP_KIND that holds kinds of groups of MPs (eg.\ 'committee', 'commission', etc.).
 *
 * Columns of table GROUP_KIND are: <code>code, name, short_name, description, subkind_of, weight</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key is column <code>code</code>.
 */
class GroupKind
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'group_kind',
			'columns' => array('code', 'name', 'short_name', 'description', 'subkind_of', 'weight'),
			'pkey_columns' => array('code')
		));
	}

	/**
	 * Read the group kind(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the group kinds to select.
	 * \return An array of group kinds that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('code' => 'committee'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [code] => committee
	 *             [name] => Parliamentary committee
	 *             [short_name] => Committee
	 *             [description] => Committee of a parliament.
	 *             [subkind_of] => parliament
	 *             [weight] => 3
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
	 * Create a group kind(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the group kind to create. Alternatively, an array of such group kind specifications.
	 *
	 * \return An array of primary key values of the created group kind(s).
	 *
	 * \ex
	 * \code
	 * create(array('code' => 'subcommittee', 'name' => 'Parliamentary subcommittee', 'short_name' => 'Subcommittee', 'description' => 'Subcommittee of a committee of a parliament.', 'subkind_of' => 'committee', 'weight' => 3.5))
	 * \endcode creates a new group kind and returns
	 * \code
	 * Array
	 * (
	 *     [code] => subcommittee
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the group kinds that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the group kinds to update. Only the group kinds that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated group kind.
	 *
	 * \return An array of primary key values of the updated group kinds.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the group kind(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the group kinds to delete. Only the group kinds that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted group kinds.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
