<?php

/**
 * Class Response provides information about responses of MPs to received messages through API and implements CRUD operations on database table RESPONSE.
 *
 * Columns of table RESPONSE are: <em>message_id, mp_id, parliament_code, subject, body_, full_email_data, received_on, received_privately, reply_code, survey_code</em>. All columns are allowed to write to.
 */
class Response
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity('response', array('message_id', 'mp_id', 'parliament_code', 'subject', 'body_', 'full_email_data', 'received_on', 'received_privately', 'reply_code', 'survey_code'));
	}

	/**
	 * Read MP(s)' response(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the responses to select. Only responses satisfying all prescribed column values are returned.
	 *
	 * \return An array of responses with structure <code>array('response' => array(array('message_id' => 44, 'mp_id' => 515, 'parliament_code' => 'cz/senat', 'subject' => 'Re: My law proposal', ...), ...))</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params, 'response');
	}

	/**
	 * Create MP(s)' response(s) with given values.
	 *
	 * \param $data An array of responses to create, where each response is given by array of pairs <em>column => value</em>. Eg. <code>array(array('message_id' => 44, 'mp_id' => 515, 'parliament_code' => 'cz/senat', 'subject' => 'Re: My law proposal', ...), ...)</code>.
	 *
	 * \return Number of created responses.
	 */
	public static function create($data)
	{
		return self::$entity->create($data, 'response');
	}

	/**
	 * Update MP(s)' response(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the responses to update. Only responses satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected response.
	 *
	 * \return Number of updated responses.
	 */
	public static function update($params, $data)
	{
		return self::$entity->update($params, $data, 'response');
	}

	/**
	 * Delete MP(s)' response(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the responses to delete. Only responses satisfying all prescribed column values are deleted.
	 *
	 * \return Number of deleted responses.
	 */
	public static function delete($params)
	{
		return self::$entity->delete($params, 'response');
	}
}

Response::init();

?>
