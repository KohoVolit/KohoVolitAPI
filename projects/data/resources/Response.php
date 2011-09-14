<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table RESPONSE that holds responses of MPs to received messages.
 *
 * Columns of table RESPONSE are: <code>message_id, mp_id, parliament_code, subject, body, full_email_data, received_on, received_privately, reply_code, survey_code</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>message_id, mp_id, parliament_code</code>.
 */
class Response
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'response',
			'columns' => array('message_id', 'mp_id', 'parliament_code', 'subject', 'body', 'full_email_data', 'received_on', 'received_privately', 'reply_code', 'survey_code'),
			'pkey_columns' => array('message_id', 'mp_id', 'parliament_code')
		));
	}

	/**
	 * Read the MP(s)' response(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the responses to select.
	 *
	 * \return An array of responses that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('message_id' => 33, 'mp_id' => 712))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [message_id] => 33
	 *             [mp_id] => 712
	 *             [parliament_code] => me/shire
	 *             [subject] => Re: My law proposal
	 *             [body] => Dear Mr. Baggins ...
	 *             [full_email_data] => From ...
	 *             [received_on] => 2011-05-29 16:08:32
	 *             [received_privately] => 
	 *             [reply_code] => qwertyuiop
	 *             [survey_code] => 
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		return $this->entity->read($params, 'response');
	}

	/**
	 * Create a MP(s)' response(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the responses to create. Alternatively, an array of such response specifications.
	 * \return An array of primary key values of the created response(s).
	 *
	 * \ex
	 * \code
	 * create(array('message_id' => 33, 'mp_id' => 712, 'parliament_code' => 'me/shire', 'reply_code_code' => 'qwertyuiop'))
	 * \endcode creates a new response and returns
	 * \code
	 * Array
	 * (
	 *     [message_id] => 33
	 *     [mp_id] => 712
	 *     [parliament_code] => me/shire
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data, 'response');
	}

	/**
	 * Update the given values of the MPs' responses that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the responses to update. Only the responses that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated response.
	 *
	 * \return An array of primary key values of the updated responses.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data, 'response');
	}

	/**
	 * Delete the MP(s)' response(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the responses to delete. Only the responses that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted responses.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params, 'response');
	}
}

?>
