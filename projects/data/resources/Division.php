<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table MP that holds information about divisions in parliament.
 *
 * Columns of table Division are: <code>id, parliament_code, date, division_kind_code, name</code>.
 *
 * Column <code>id</code> is a read-only column automaticaly generated on create.
 *
 * Primary key is column <code>id</code>.
 */
class Division
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'division',
			'columns' => array('id', 'parliament_code', 'date', 'division_kind_code', 'name'),
			'pkey_columns' => array('id'),
			'readonly_columns' => array('id')
		));
	}

	/**
	 * Read the divisions that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the divisions to select.
	 *
	 * \return An array of divisions that satisfy all prescribed column values.
	 *
	 */
	public function read($params)
	{
		return $this->entity->read($params);
	}

	/**
	 * Create a division from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the division to create. Alternatively, an array of such division specifications.
	 *
	 * \return An array of primary key values of the created division(s).
	 *
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the divisions that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the divisions to update. Only the divisions that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated division.
	 *
	 *
	 * \return An array of primary key values of the updated divisions.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the division(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the divisions to delete. Only the divisions that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted divisions.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
