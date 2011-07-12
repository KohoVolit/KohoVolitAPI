<?php

/**
 * Class Message provides information about messages sent to MPs through API and implements CRUD operations on database table MESSAGE.
 *
 * Columns of table MESSAGE are: <em>id, subject, body_, sender_name, sender_address, sender_email, is_public, state_, written_on, sent_on, confirmation_code, approval_code</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */

class Message
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity(
			'message',
			array('id', 'subject', 'body_', 'sender_name', 'sender_address', 'sender_email', 'is_public', 'state_', 'written_on', 'sent_on', 'confirmation_code', 'approval_code'),
			'id',
			array('id')
		);
	}

	/**
	 * Read message(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the messages to select. Only messages satisfying all prescribed column values are returned.
	 *
	 * \return An array of messages with structure <code>array('message' => array(array('id' => 12, 'subject' => 'My law proposal', 'body_' => 'Dear Mr. ...', ...), ...))</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params);
	}

	/**
	 * Create message(s) with given values.
	 *
	 * \param $data An array of messages to create, where each message is given by array of pairs <em>column => value</em>. Eg. <code>array(array('id' => 12, 'subject' => 'My law proposal', 'body_' => 'Dear Mr. ...', ...), ...)</code>.
	 *
	 * \return An array of \e id-s of created messages.
	 */
	public static function create($data)
	{
		return self::$entity->create($data);
	}

	/**
	 * Update message(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the messages to update. Only messages satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected message.
	 *
	 * \return An array of \e id-s of updated messages.
	 */
	public static function update($params, $data)
	{
		return self::$entity->update($params, $data);
	}

	/**
	 * Delete message(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the messages to delete. Only messages satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e id-s of deleted messages.
	 */
	public static function delete($params)
	{
		return self::$entity->delete($params);
	}
}

Message::init();

?>
