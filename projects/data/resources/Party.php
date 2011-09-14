<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table PARTY that holds political parties.
 *
 * Columns of table PARTY are: <code>id, name, short_name, description, country_code, last_updated_on</code>.
 *
 * Column <code>id</code> is a read-only column automaticaly generated on create.
 *
 * Primary key is column <code>id</code>.
 */
class Party
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'party',
			'columns' => array('id', 'name', 'short_name', 'description', 'country_code', 'last_updated_on'),
			'pkey_columns' => array('id'),
			'readonly_columns' => array('id')
		));
	}

	/**
	 * Read the party(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parties to select.
	 *
	 * \return An array of constituencies that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('short_name' => 'KDH', 'country_code' => 'sk'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 234
	 *             [name] => Kresťanskodemokratické hnutie
	 *             [short_name] => KDH
	 *             [description] =>
	 *             [country_code] => sk
	 *             [last_updated_on] => 2011-06-24 00:50:44
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
	 * Create a party(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the party to create. Alternatively, an array of such party specifications.
	 * If \c last_updated_on column is ommitted, it is set to the current timestamp.
	 *
	 * \return An array of primary key values of the created party(s).
	 *
	 * \ex
	 * \code
	 * create(array('name' => 'Strana zelených', 'short_name' => 'SZ', 'country_code' => 'sk'))
	 * \endcode creates a new party and returns something like
	 * \code
	 * Array
	 * (
	 *     [id] => 258
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the parties that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parties to update. Only the parties that satisfy all prescribed column values are updated.
	 * If the parameter contains \c last_updated_on column then only the parties with older value in their \c last_updated_on column are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated party.
	 *
	 * \return An array of primary key values of the updated parties.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the party(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parties to delete. Only the parties that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted parties.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
