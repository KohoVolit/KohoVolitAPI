<?php

/**
 * Class ParliamentKind provides information about kinds of parliament through API and implements CRUD operations on database table PARLIAMENT_KIND.
 *
 * Columns of table PARLIAMENT_KIND are: <em>code, name_, short_name, description</em>. All columns are allowed to write to.
 */
class ParliamentKind
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity(
			'parliament_kind',
			array('code', 'name_', 'short_name', 'description'),
			array('code')
		);
	}

	/**
	 * Read parliament kind(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliament kinds to select. Only parliament kinds satisfying all prescribed column values are returned.
	 *
	 * \return An array of parliament kinds with structure <code>array(array('code' => 'regional', 'name_' => 'Regional parliament', 'short_name' => 'Regional', ...), ...)</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params);
	}

	/**
	 * Create parliament kind(s) with given values.
	 *
	 * \param $data An array of parliament kinds to create, where each parliament kind is given by array of pairs <em>column => value</em>. Eg. <code>array(array('code' => 'regional', 'name_' => 'Regional parliament', 'short_name' => 'Regional', ...), ...)</code>.
	 *
	 * \return An array of \e code-s of created parliament kinds.
	 */
	public static function create($data)
	{
		return self::$entity->create($data);
	}

	/**
	 * Update parliament kind(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliament kinds to update. Only parliament kinds satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected parliament kind.
	 *
	 * \return An array of \e code-s of updated parliament kinds.
	 */
	public static function update($params, $data)
	{
		return self::$entity->update($params, $data);
	}

	/**
	 * Delete parliament kind(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the parliament kinds to delete. Only parliament kinds satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e code-s of deleted parliament kinds.
	 */
	public static function delete($params)
	{
		return self::$entity->delete($params);
	}
}

ParliamentKind::init();

?>
