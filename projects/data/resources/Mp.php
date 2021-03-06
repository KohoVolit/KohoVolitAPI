<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table MP that holds information about MPs (members of parliament).
 *
 * Columns of table MP are: <code>id, first_name, middle_names, last_name, disambiguation, sex, pre_title, post_title, born_on, died_on, last_updated_on, name_data</code>.
 *
 * Columns <code>id, name_data</code> are read-only. The \c id is automaticaly generated on create,
 * the latter is derived from other columns on create and on each update automatically.
 *
 * Primary key is column <code>id</code>.
 */
class Mp
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'mp',
			'columns' => array('id', 'first_name', 'middle_names', 'last_name', 'disambiguation', 'sex', 'pre_title', 'post_title', 'born_on', 'died_on', 'last_updated_on', 'name_data'),
			'pkey_columns' => array('id'),
			'readonly_columns' => array('id', 'name_data')
		));
	}

	/**
	 * Read the MP(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the MPs to select.
	 *
	 * \return An array of MPs that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('first_name' => 'Marek'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 11
	 *             [first_name] => Marek
	 *             [middle_names] =>
	 *             [last_name] => Benda
	 *             [disambiguation] =>
	 *             [sex] => m
	 *             [pre_title] =>
	 *             [post_title] =>
	 *             [born_on] => 1968-11-10
	 *             [died_on] =>
	 *             [last_updated_on] => 2011-08-04 12:21:42.015
	 *             [name_data] => 'benda':2A 'marek':1B
	 *         )
	 *
	 *     [1] => Array
	 *         (
	 *             [id] => 177
	 *             [first_name] => Marek
	 *             [middle_names] =>
	 *             [last_name] => Šnajdr
	 *             [disambiguation] =>
	 *             [sex] => m
	 *             [pre_title] => Bc.
	 *             [post_title] =>
	 *             [born_on] => 1975-01-06
	 *             [died_on] =>
	 *             [last_updated_on] => 2011-08-04 12:28:38.39
	 *             [name_data] => 'marek':1B 'snajdr':2A
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
	 * Create an MP(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the MP to create. Alternatively, an array of such MP specifications.
	 * If \c last_updated_on column is ommitted, it is set to the current timestamp.
	 *
	 * \return An array of primary key values of the created MP(s).
	 *
	 * \ex
	 * \code
	 * create(array(
	 * 	array('first_name' => 'Vlasta', 'last_name' => 'Parkanová', 'sex' => 'f', 'pre_title' => 'JUDr.', 'post_title' => '', 'born_on' => '1951-11-21'),
	 * 	array('first_name' => 'Václav', 'last_name' => 'Cempírek', 'sex' => 'm', 'pre_title' => 'prof. Ing.', 'post_title' => 'Ph.D.', 'born_on' => '1954-05-06'),
	 * ))
	 * \endcode creates new MPs and returns something like
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 310
	 *         )
	 *
	 *     [1] => Array
	 *         (
	 *             [id] => 311
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		$created = $this->entity->create($data);
		self::updateFulltextData($created);
		return $created;
	}

	/**
	 * Update the given values of the MPs that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the MPs to update. Only the MPs that satisfy all prescribed column values are updated.
	 * If the parameter contains \c last_updated_on column, then only the MPs with older value in their \c last_updated_on column are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated MP.
	 *
	 *
	 * \return An array of primary key values of the updated MPs.
	 */
	public function update($params, $data)
	{
		$updated = $this->entity->update($params, $data);
		if (0 < count(array_intersect(array_keys($data), array('first_name', 'middle_names', 'last_name', 'disambiguation'))))
			self::updateFulltextData($updated);
		return $updated;
	}

	/**
	 * Delete the MP(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the MPs to delete. Only the MPs that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted MPs.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}

	/**
	 * Updates derived column needed for fulltext search for the given MPs.
	 *
	 * \param $mps An array of MPs where each MP is an array of MP attributes where only the \c id attribute is really used.
	 */
	private static function updateFulltextData($mps)
	{
		if (!is_array(reset($mps)))
			$mps = array($mps);

		$query = new Query('kv_admin');
		foreach ($mps as $m)
		{
			// get names of MP
			$query->clearParams();
			$query->setQuery('select * from mp where id = $1');
			$query->appendParam($m['id']);
			$mp = $query->execute();
			$mp = $mp[0];

			// normalize names for fulltext search (remove accents and convert to lowercase)
			$first_name = strtolower(Utils::unaccent($mp['first_name']));
			$middle_names = strtolower(Utils::unaccent($mp['middle_names']));
			$last_name = strtolower(Utils::unaccent($mp['last_name']));
			$disambiguation = strtolower(Utils::unaccent($mp['disambiguation']));

			// set the column with search data to weighted concatenation of the normalized names
			$query->setQuery(
				"update mp set\n" .
				"	name_data =\n" .
				"		setweight(to_tsvector('simple', $2), 'B') ||\n" .
				"		setweight(to_tsvector('simple', $3), 'C') ||\n" .
				"		setweight(to_tsvector('simple', $4), 'A') ||\n" .
				"		setweight(to_tsvector('simple', $5), 'D')\n" .
				"where id = $1\n" .
				"returning id");
			$query->appendParam($first_name);
			$query->appendParam($middle_names);
			$query->appendParam($last_name);
			$query->appendParam($disambiguation);
			$query->execute();
		}
	}
}

?>
