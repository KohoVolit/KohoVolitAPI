<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table MP_VOTE that holds votes of MPs in divisions.
 *
 * Columns of table MP_VOTE are: <code>mp_id, division_id, vote_kind_code</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>mp_id, division_id</code>.
 */
class MpVote
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'mp_vote',
			'columns' => array('mp_id', 'division_id', 'vote_kind_code'),
			'pkey_columns' => array('mp_id', 'division_id')
		));
	}

	/**
	 * Read the MP vote(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the MP votes to select.
	 *
	 * \return An array of MP votes that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('mp_id' => 664, 'division_id' => '12345'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [mp_id] => 664
	 *             [division_id] => 12345
	 *             [vote_kind_code] => a
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
	 * Create a MP vote(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the MP vote to create. Alternatively, an array of such MP vote specifications.
	 * If \c since and \c until columns are ommitted, they are set to \c -infinity, \c infinity, respectively.
	 *
	 * \return An array of primary key values of the created MP vote(s).
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the MP votes that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the MP votes to update. Only the MP votes that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated MP vote.
	 *
	 * \return An array of primary key values of the updated MP votes.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the MP vote(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the MP votes to delete. Only the MP votes that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted MP votes.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
