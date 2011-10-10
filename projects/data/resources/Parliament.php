<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table PARLIAMENT that holds parliaments.
 *
 * Columns of table PARLIAMENT are: <code>code, name, short_name, description, parliament_kind_code, country_code, weight, time_zone, last_updated_on</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key is column <code>code</code>.
 */
class Parliament
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'parliament',
			'columns' => array('code', 'name', 'short_name', 'description', 'parliament_kind_code', 'country_code', 'weight', 'time_zone', 'last_updated_on'),
			'pkey_columns' => array('code')
		));
	}

	/**
	 * Read the parliament(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliaments to select.
	 *
	 * \return An array of parliaments that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('code' => 'cz/psp'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [code] => cz/psp
	 *             [name] => Poslanecká sněmovna Parlamentu České republiky
	 *             [short_name] => PSP ČR
	 *             [description] => Dolní komora parlamentu České republiky.
	 *             [parliament_kind_code] => national-lower
	 *             [country_code] => cz
	 *             [weight] => 1.0
	 *             [time_zone] => Europe/Prague
	 *             [last_updated_on] => 2011-06-24 00:30:09.234649
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
	 * Create a parliament(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the parliament to create. Alternatively, an array of such parliament specifications.
	 * If \c last_updated_on column is ommitted, it is set to the current timestamp.	 
	 *
	 * \return An array of primary key values of created parliament(s).
	 *
	 * \ex
	 * \code
	 * create(array('code' => 'cz/senat', 'name' => 'Senát Parlamentu České republiky', 'short_name' => 'Senát ČR', 'parliament_kind_code' => 'national-upper', 'country_code' => 'cz', 'weight' => 2.0, 'time_zone' => 'Europe/Prague'))
	 * \endcode creates a new parliament and returns
	 * \code
	 * Array
	 * (
	 *     [code] => cz/senat
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the parliaments that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliaments to update. Only the parliaments that satisfy all prescribed column values are updated.
	 * If the parameter contain \c last_updated_on column, then only the parliaments with older value in their \c last_updated_on column are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated parliament.
	 *
	 *
	 * \return An array of primary key values of updated parliaments.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the parliament(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliaments to delete. Only the parliaments that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of deleted parliaments.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
