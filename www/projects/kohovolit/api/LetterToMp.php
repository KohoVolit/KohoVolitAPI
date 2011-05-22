<?php

/**
 * Class LetterToMp provides information about which letter was sent to which MP through API and implements CRUD operations on database table LETTER_TO_MP.
 *
 * Columns of table LETTER_TO_MP are: <em>letter_id, mp_id, parliament_code</em>. All columns are allowed to write to.
 */
class LetterToMp
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity('letter_to_mp', array('letter_id', 'mp_id', 'parliament_code'));
	}

	/**
	 * Read letter-MP relation(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the relations to select. Only relations satisfying all prescribed column values are returned.
	 *
	 * \return An array of relations with structure <code>array('letter_to_mp' => array(array('letter_id' => 44, 'mp_id' => 515, 'parliament_code' => 'cz/senat'), ...))</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params, 'letter_to_mp');
	}

	/**
	 * Create letter-MP relation(s) with given values.
	 *
	 * \param $data An array of relations to create, where each relation is given by array of pairs <em>column => value</em>. Eg. <code>array(array('letter_id' => 44, 'mp_id' => 515, 'parliament_code' => 'cz/senat'), ...)</code>.
	 *
	 * \return Number of created relations.
	 */
	public static function create($data)
	{
		return self::$entity->create($data, 'letter_to_mp');
	}

	/**
	 * Update letter-MP relation(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the relations to update. Only relations satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected relation.
	 *
	 * \return Number of updated relations.
	 */
	public static function update($params, $data)
	{
		return self::$entity->update($params, $data, 'letter_to_mp');
	}

	/**
	 * Delete letter-MP relation(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the relations to delete. Only relations satisfying all prescribed column values are deleted.
	 *
	 * \return Number of deleted relations.
	 */
	public static function delete($params)
	{
		return self::$entity->delete($params, 'letter_to_mp');
	}
}

LetterToMp::init();

?>
