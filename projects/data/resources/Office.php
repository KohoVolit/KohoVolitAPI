<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table OFFICE that holds MPs' offices.
 *
 * Columns of table OFFICE are: <code>mp_id, parliament_code, address, phone, latitude, longitude, relevance, since, until</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>mp_id, parliament_code, address, since</code>.
 */
class Office
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'office',
			'columns' => array('mp_id', 'parliament_code', 'address', 'phone', 'latitude', 'longitude', 'relevance', 'since', 'until'),
			'pkey_columns' => array('mp_id', 'parliament_code', 'address', 'since')
		));
	}

	/**
	 * Read the MP office(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the offices to select.
	 * A special parameter \c _datetime can be used (eg. '_datetime' => '2010-06-30 9:30:00') to select only the offices
	 * valid at the given moment (the ones where \c since <= \c _datetime < \c until).
	 * Use <code>'_datetime' => 'now'</code> to get offices valid now.
	 *
	 * \return An array of MP offices that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('mp_id' => 829, 'parliament_code' => 'cz/senat', '_datetime' => 'now'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [mp_id] => 829
	 *             [parliament_code] => cz/senat
	 *             [address] => Mariánské nám. 127, Uherské Hradiště 686 01
	 *             [phone] => 
	 *             [latitude] => 49.0701727
	 *             [longitude] => 17.4594702
	 *             [relevance] => 
	 *             [since] => 2011-05-26 12:00:00
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
	 * Create an MP office(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the office to create. Alternatively, an array of such office specifications.
	 * If \c since and \c until columns are ommitted, they are set to \c -infinity, \c infinity, respectively.
	 *
	 * \return An array of primary key values of the created office(s).
	 *
	 * \ex
	 * \code
	 * create(array('mp_id' => 684, 'parliament_code' => 'cz/psp', 'address' => '|Sněmovní|4|Praha 1|118 26|Česká republika', 'phone' => '25717 2079', 'relevance' => 0.5))
	 * \endcode creates a new office and returns
	 * \code
	 * Array
	 * (
	 *     [mp_id] => 684
	 *     [parliament_code] => cz/psp
	 *     [address] => |Sněmovní|4|Praha 1|118 26|Česká republika
	 *     [since] => -infinity
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the MP offices that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the offices to update. Only the offices that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated office.
	 *
	 * \return An array of primary key values of the updated offices.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
	}

	/**
	 * Delete the MP office(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the offices to delete. Only the offices that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted offices.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
}

?>
