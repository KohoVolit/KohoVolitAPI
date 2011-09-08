<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table COUNTRY that holds countries.
 *
 * Columns of table COUNTRY are: <code>code, name_, short_name, description</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key is column <code>code</code>.
 */
class Country
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'country',
			'columns' => array('code', 'name_', 'short_name', 'description'),
			'pkey_columns' => array('code')
		));
	}

	/**
	 * Read the country(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the countries to select.
	 *
	 * \return An array of countries that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('code' => 'cz'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [code] => cz
	 *             [name_] => Czech republic
	 *             [short_name] => Czechia
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
	 * Create a country(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the country to create. Alternatively, an array of such country specifications.
	 * \return An array of primary key values of the created country(s).
	 *
	 * \ex
	 * \code
	 * create(array('code' => 'sk', 'name_' => 'Slovak republic', 'short_name' => 'Slovakia'))
	 * \endcode creates a new country and returns
	 * \code
	 * Array
	 * (
	 *     [code] => sk
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the countries that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the countries to update. Only the countries that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated country.
	 *
	 * \return An array of primary key values of the updated countries.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the country(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the countries to delete. Only the countries that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted countries.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
