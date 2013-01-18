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
 * Primary key consists of columns <code>reply_code, received_on</code>.
 *
 * \note Due to privacy and security reasons only the following columns are accessible from remote: <code>subject, body, received_on</code>.
 */
class Reply
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	// fields of the resource (here columns of the table) that are publicly accessible from remote
	private $public_fields;

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
		$this->public_fields = array('subject', 'body', 'received_on');
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
		$created = $this->entity->create($data, 'reply');
		self::updateMessageFulltextData($created);
		return $created;
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
		$updated  = $this->entity->update($params, $data, 'reply');
		if (array_key_exists('subject', $data) || array_key_exists('body', $data))
			self::updateMessageFulltextData($updated);
		return $updated;
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

	/**
	 * Remove all information that is not accessible from remote from result of the read method.
	 *
	 * \param $read_result Result of the read method.
	 *
	 * \return Result of the read method with private information removed.
	 */
	public function restrict($read_result)
	{
		$restricted_result = array();
		foreach ($read_result as $element)
		{
			foreach ($this->public_fields as $field)
				$restricted_element[$field] = element[$field];
			$restricted_result[] = $restricted_element;
		}
		return $restricted_result;
	}

	/**
	 * Updates data needed for fulltext search in the messages (and their replies) respective to the given replies.
	 *
	 * \param $replies An array of replies where each reply is an array of reply attributes where only the \c reply_code attribute is really used.
	 */
	private static function updateMessageFulltextData($replies)
	{
		if (!is_array(reset($replies)))
			$replies = array($replies);

		// prepare reply codes of all created/updated replies
		$reply_codes = array();
		foreach ($replies as $reply)
			$reply_codes[] = $reply['reply_code'];

		// get the respective messages
		$query = new Query();
		$query->setQuery('select id, subject from message_to_mp as mtm join message as m on m.id = mtm.message_id where mtm.reply_code = any ($1)');
		$query->appendParam(Db::arrayOfStringsArgument($reply_codes));
		$messages = $query->execute();

		// pretend update of subject for all those messages to update their fulltext data
		$api = new ApiDirect('data');
		foreach ($messages as $message)
			$api->update('Message', array('id' => $message['id']), array('subject' => $message['subject']));
	}
}

?>
