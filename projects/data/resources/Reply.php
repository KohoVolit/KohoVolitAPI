<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table REPLY that holds replies of MPs to received messages.
 *
 * Columns of table REPLY are: <code>reply_code, subject, body, full_email_data, received_on</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>message_id, mp_id, parliament_code</code>.
 */
class Reply
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'reply',
			'columns' => array('reply_code', 'subject', 'body', 'full_email_data', 'received_on'),
			'pkey_columns' => array('reply_code', 'received_on')
		));
	}

	/**
	 * Read the MP(s)' reply(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the replies to select.
	 *
	 * \return An array of replies that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('reply_code' => 'qwertyuiop'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [reply_code] => qwertyuiop
	 *             [subject] => Re: My law proposal
	 *             [body] => Dear Mr. Baggins ...
	 *             [full_email_data] => From ...
	 *             [received_on] => 2011-05-29 16:08:32
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		return $this->entity->read($params, 'reply');
	}

	/**
	 * Create a MP(s)' reply(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the replies to create. Alternatively, an array of such reply specifications.
	 * \return An array of primary key values of the created reply(s).
	 *
	 * \ex
	 * \code
	 * create(array('reply_code' => 'qwertyuiop', 'subject' => 'Re: My law proposal', 'body' => 'Dear Mr. Baggins ...', 'full_email_data' => 'From ...'))
	 * \endcode creates a new reply and returns
	 * \code
	 * Array
	 * (
	 *     [reply_code] => qwertyuiop
	 *     [received_on] => 2011-05-29 16:08:32
	 * )
	 * \endcode
	 */
	public function create($data)
	{
		return $this->entity->create($data, 'reply');
	}

	/**
	 * Update the given values of the MPs' replies that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the replies to update. Only the replies that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated reply.
	 *
	 * \return An array of primary key values of the updated replies.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data, 'reply');
	}

	/**
	 * Delete the MP(s)' reply(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the replies to delete. Only the replies that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted replies.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params, 'reply');
	}
}

?>
