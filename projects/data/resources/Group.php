<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table GROUP that holds groups of MPs (eg.\ committees, commissions, etc.).
 *
 * Columns of table GROUP are: <code>id, name_, short_name, group_kind_code, term_id, parliament_code, subgroup_of, last_updated_on</code>.
 *
 * Column <code>id</code> is a read-only column automaticaly generated on create.
 *
 * Primary key is column <code>id</code>.
 */
class Group
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'group_',
			'columns' => array('id', 'name_', 'short_name', 'group_kind_code', 'term_id', 'parliament_code', 'subgroup_of', 'last_updated_on'),
			'pkey_columns' => array('id'),
			'readonly_columns' => array('id')
		));
	}

	/**
	 * Read the group(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the groups to select.
	 *
	 * \return An array of groups that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('short_name' => 'TOP09-S', 'group_kind_code' => 'political group'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 576
	 *             [name_] => Poslanecký klub TOP 09 a Starostové
	 *             [short_name] => TOP09-S
	 *             [group_kind_code] => political group
	 *             [term_id] => 6
	 *             [parliament_code] => cz/psp
	 *             [subgroup_of] => 514
	 *             [last_updated_on] => 2011-06-24 00:39:00.795609
	 *         )
	 *
	 *     [1] => Array
	 *         (
	 *             [id] => 655
	 *             [name_] => Klub TOP 09 a Starostové
	 *             [short_name] => TOP09-S
	 *             [group_kind_code] => political group
	 *             [term_id] => 7
	 *             [parliament_code] => cz/senat
	 *             [subgroup_of] => 628
	 *             [last_updated_on] => 2011-06-24 00:39:12.991019
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
	 * Create a group(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the group to create. Alternatively, an array of such group specifications.
	 * If \c last_updated_on column is ommitted, it is set to the current timestamp.
	 *
	 * \return An array of primary key values of the created group(s).
	 *
	 * \ex
	 * \code
	 * create(array('name_' => 'Rozpočtový výbor', 'short_name' => 'RV', 'group_kind_code' => 'committee', 'parliament_code' => 'cz/psp', 'term_id' => 6, 'subgroup_of' => 514))
	 * \endcode creates a new group and returns something like
	 * \code
	 * Array
	 * (
	 *     [id] => 537
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the groups that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the groups to update. Only the groups that satisfy all prescribed column values are updated.
	 * If the parameter contains \c last_updated_on column then only the groups with older value in their \c last_updated_on column are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated group.
	 *
	 * \return An array of primary key values of the updated groups.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the group(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the groups to delete. Only the groups that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted groups.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
