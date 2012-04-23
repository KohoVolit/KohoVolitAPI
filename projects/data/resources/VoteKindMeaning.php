<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table VOTE_KIND_MEANING that stores meaning of vote depending division kind.
 *
 * Columns of table VOTE_KIND_MEANING are: <code>vote_kind_code, division_kind_code, vote_kind_meaning</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>vote_kind_code, division_kind_code</code>.
 */
class VoteKindMeaning {
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'vote_kind_meaning',
			'columns' => array('vote_kind_code', 'division_kind_code', 'vote_meaning_code'),
			'pkey_columns' => array('vote_kind_code', 'division_kind_code')
		));
	}
	
	/**
	 * Read the vote meaning(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the votes' meaning.
	 *
	 * \return An array of votes' meaning(s) that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('division_kind_code' => 'simple', 'vote_kind_code' => 'a'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *              [vote_kind_code] => 'a',
	 *				[division_kind_code] => 'simple',
	 *				[vote_kind_meaning] => 'against',
	 *         )
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		return $this->entity->read($params);
	}
	/**
	 * Create a vote meaning(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the vote meaning to create. Alternatively, an array of such vote meaning specifications.
	 *
	 * \return An array of primary key values of the created.
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the vote meaning(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the vote meaning(s) to update. Only the vote meaning(s) that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated vote meaning.
	 *
	 * \return An array of primary key values of the updated vote meaning(s).
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the vote meaning(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the vote meaning(s) to delete. Only the vote meaning(s)that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted vote meaning(s).
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}
