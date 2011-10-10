<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table MP_IN_GROUP that holds memberships of MPs in groups.
 *
 * Columns of table MP_IN_GROUP are: <code>mp_id, group_id, role_code, party_id, constituency_id, since, until</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>mp_id, group_id, role_code, since</code>.
 */
class MpInGroup
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'mp_in_group',
			'columns' => array('mp_id', 'group_id', 'role_code', 'party_id', 'constituency_id', 'since', 'until'),
			'pkey_columns' => array('mp_id', 'group_id', 'role_code', 'since')
		));
	}

	/**
	 * Read the membership(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the memberships to select.
	 * A special parameter \c _datetime can be used (eg. '_datetime' => '2010-06-30 9:30:00') to select only the memberships
	 * valid at the given moment (the ones where \c since <= \c _datetime < \c until).
	 * Use <code>'_datetime' => 'now'</code> to get memberships valid now.
	 *
	 * \return An array of memberships that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('mp_id' => 664, '_datetime' => 'now'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [mp_id] => 664
	 *             [group_id] => 514
	 *             [role_code] => member
	 *             [party_id] => 
	 *             [constituency_id] => 14
	 *             [since] => 2010-05-29 12:00:00
	 *             [until] => infinity
	 *         )
	 *
	 *     [1] => Array
	 *         (
	 *             [mp_id] => 664
	 *             [group_id] => 576
	 *             [role_code] => member
	 *             [party_id] => 
	 *             [constituency_id] => 
	 *             [since] => 2010-06-10 12:00:00
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
	 * Create a membership(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the membership to create. Alternatively, an array of such membership specifications.
	 * If \c since and \c until columns are ommitted, they are set to \c -infinity, \c infinity, respectively.
	 *
	 * \return An array of primary key values of the created membership(s).
	 *
	 * \ex
	 * \code
	 * create(array(
	 * 	array('mp_id' => 664, 'group_id' => 514, 'role_code' => 'member', 'constituency_id' => 14),
	 * 	array('mp_id' => 664, 'group_id' => 576, 'role_code' => 'member', since => '2011-01-01'),
	 * ))
	 * \endcode creates new memberships and returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [mp_id] => 664
	 *             [group_id] => 514
	 *             [role_code] => member
	 *             [since] => -infinity
	 *         )
	 *
	 *     [1] => Array
	 *         (
	 *             [mp_id] => 664
	 *             [group_id] => 576
	 *             [role_code] => member
	 *             [since] => 2011-01-01 12:00:00
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the memberships that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the memberships to update. Only the memberships that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated membership.
	 *
	 * \return An array of primary key values of the updated memberships.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the membership(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the memberships to delete. Only the memberships that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted memberships.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
