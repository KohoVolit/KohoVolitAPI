<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table VOTE_KIND that holds kinds of possible vote (eg.\ 'yes', 'no', 'abstain', etc.).
 *
 * Columns of table VOTE_KIND are: <code>code, name, description</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key is column <code>code</code>.
 */
class VoteKind
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'vote_kind',
			'columns' => array('code', 'name', 'description'),
			'pkey_columns' => array('code')
		));
	}

	/**
	 * Read the vote kind(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the vote kinds to select.
	 * \return An array of vote kinds that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('code' => 'n'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [code] => n
	 *             [name] => no
	 *             [description] => Vote against the proposal.
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
	 * Create a vote kind(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the vote kind to create. Alternatively, an array of such vote kind specifications.
	 *
	 * \return An array of primary key values of the created vote kind(s).
	 *
	 * \ex
	 * \code
	 * create(array('code' => 's', 'name' => 'secret', 'description' => 'Secret vote.'))
	 * \endcode creates a new vote kind and returns
	 * \code
	 * Array
	 * (
	 *     [code] => s
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the vote kinds that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the vote kinds to update. Only the vote kinds that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated vote kind.
	 *
	 * \return An array of primary key values of the updated vote kinds.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the vote kind(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the vote kinds to delete. Only the vote kinds that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted vote kinds.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
