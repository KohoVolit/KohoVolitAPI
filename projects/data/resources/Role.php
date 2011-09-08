<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table ROLE_ that holds roles of MP memberships in groups (eg.\ member, chairman, treasurer, etc.).
 *
 * Columns of table ROLE_ are: <code>code, male_name, female_name, description</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key is column <code>code</code>.
 */
class Role
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'role_',
			'columns' => array('code', 'male_name', 'female_name', 'description'),
			'pkey_columns' => array('code')
		));
	}

	/**
	 * Read the role(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the roles to select.
	 *
	 * \return An array of roles that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('code' => 'chairman'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [code] => chairman
	 *             [male_name] => chairman
	 *             [female_name] => chairwoman
	 *             [description] => 
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
	 * Create a role(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the role to create. Alternatively, an array of such role specifications.
	 * \return An array of primary key values of the created role(s).
	 *
	 * \ex
	 * \code
	 * create(array('code' => 'chairman', 'male_name' => 'chairman', 'female_name' => 'chairwoman'))
	 * \endcode creates a new role and returns
	 * \code
	 * Array
	 * (
	 *     [code] => chairman
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the roles that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the roles to update. Only the roles that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated role.
	 *
	 * \return An array of primary key values of the updated roles.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the role(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the roles to delete. Only the roles that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted roles.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
