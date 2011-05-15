<?php

/**
 * Class Answer provides information about answers of MPs to received letters through API and implements CRUD operations on database table ANSWER.
 *
 * Columns of table ANSWER are: <em>letter_id, mp_id, subject, body_, received_on</em>. All columns are allowed to write to.
 */

class Answer
{
	/// instance holding a list of table columns and table handling functions
	private static $entity;

	/**
	 * Initialize information about the entity table.
	 */
	public static function init()
	{
		self::$entity = new Entity(
			'answer',
			array('letter_id', 'mp_id', 'subject', 'body_', 'received_on')
		);
	}

	/**
	 * Read MPs' answer(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the answers to select. Only answers satisfying all prescribed column values are returned.
	 *
	 * \return An array of answers with structure <code>array('answer' => array(array('letter_id' => 89, 'mp_id' => 542, 'subject' => 'Re: My law proposal', ...), ...))</code>.
	 */
	public static function read($params)
	{
		return self::$entity->read($params);
	}

	/**
	 * Create MPs' answers(s) with given values.
	 *
	 * \param $data An array of answers to create, where each answer is given by array of pairs <em>column => value</em>. Eg. <code>array(array('letter_id' => 89, 'mp_id' => 542, 'subject' => 'Re: My law proposal', ...), ...)</code>.
	 *
	 * \return Number of created answers.
	 */
	public static function create($data)
	{
		return self::$entity->create($data);
	}

	/**
	 * Update MPs answers(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the answers to update. Only answers satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected answer.
	 *
	 * \return Number of updated answers.
	 */
	public static function update($params, $data)
	{
		return self::$entity->update($params, $data);
	}

	/**
	 * Delete MPs answers(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the answers to delete. Only answers satisfying all prescribed column values are deleted.
	 *
	 * \return Number of deleted answers.
	 */
	public static function delete($params)
	{
		return self::$entity->delete($params);
	}
}

Answer::init();

?>
