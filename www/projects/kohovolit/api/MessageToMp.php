<?php

/**
 * Class MessageToMp provides information about which message was sent to which MP through API and implements CRUD operations on database table MESSAGE_TO_MP.
 *
 * Columns of table MESSAGE_TO_MP are: <em>message_id, mp_id, parliament_code, is_responded, reply_code, survey_code</em>. All columns are allowed to write to.
 */
class MessageToMp
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity('message_to_mp', array('message_id', 'mp_id', 'parliament_code', 'is_responded', 'reply_code', 'survey_code'));
	}

	/**
	 * Read message-MP relation(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the relations to select. Only relations satisfying all prescribed column values are returned.
	 *
	 * \return An array of relations with structure <code>array('message_to_mp' => array(array('message_id' => 44, 'mp_id' => 515, 'parliament_code' => 'cz/senat'), ...))</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params, 'message_to_mp');
	}

	/**
	 * Create message-MP relation(s) with given values.
	 *
	 * \param $data An array of relations to create, where each relation is given by array of pairs <em>column => value</em>. Eg. <code>array(array('message_id' => 44, 'mp_id' => 515, 'parliament_code' => 'cz/senat'), ...)</code>.
	 *
	 * \return Number of created relations.
	 */
	public static function create($data)
	{
		return self::$entity->create($data, 'message_to_mp');
	}

	/**
	 * Update message-MP relation(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the relations to update. Only relations satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected relation.
	 *
	 * \return Number of updated relations.
	 */
	public static function update($params, $data)
	{
		return self::$entity->update($params, $data, 'message_to_mp');
	}

	/**
	 * Delete message-MP relation(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the relations to delete. Only relations satisfying all prescribed column values are deleted.
	 *
	 * \return Number of deleted relations.
	 */
	public static function delete($params)
	{
		return self::$entity->delete($params, 'message_to_mp');
	}
}

MessageToMp::init();

?>
