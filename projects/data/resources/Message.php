<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table MESSAGE that holds messages sent to MPs.
 *
 * Columns of table MESSAGE are: <code>id, subject, body, sender_name, sender_address, sender_email, is_public, state, written_on, sent_on, confirmation_code, approval_code, text_data, sender_data, remote_addr, typing_duration</code>.
 *
 * Columns <code>id, text_data, sender_data</code> are read-only. The \c id is automaticaly generated on create,
 * the latter two are derived from other columns on create and on each update automatically.
 *
 * Primary key is column <code>id</code>.
 *
 * \note Due to privacy and security reasons only the following columns are accessible from remote: <code>id, subject, body, sender_name, sent_on</code>.
 * Furthermore, only the public, sent messages are accessible from remote.
 */
class Message
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
			'name' => 'message',
			'columns' => array('id', 'subject', 'body', 'sender_name', 'sender_address', 'sender_email', 'is_public', 'state', 'written_on', 'sent_on', 'confirmation_code', 'approval_code', 'text_data', 'sender_data', 'remote_addr', 'typing_duration'),
			'pkey_columns' => array('id'),
			'readonly_columns' => array('id', 'text_data', 'sender_data')
		));
		$this->public_fields = array('id', 'subject', 'body', 'sender_name', 'sent_on');
	}

	/**
	 * Read the message(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the messages to select.
	 *
	 * \return An array of messages that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('sender_email' => 'bilbo@hobbiton.me', 'state' => 'sent'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 123
	 *             [subject] => My law proposal
	 *             [body] => Dear Mr. ...
	 *             [sender_name] => Bilbo Baggins
	 *             [sender_address] => Bag End, Hobbiton
	 *             [sender_email] => bilbo@hobbiton.me
	 *             [is_public] => yes
	 *             [state] => sent
	 *             [written_on] => 2011-05-26 13:06:04
	 *             [sent_on] => 2011-05-26 13:09:32
	 *             [confirmation_code] => abcdefghij
	 *             [approval_code] =>
	 *             [text_data] => 'dear':4A 'law':2B 'mr':5A 'my':1B 'proposal':3B
	 *             [sender_data] => 'bag':3B 'baggins':2A 'bilbo':1A 'end':4B 'hobbiton':5B
	 *             [remote_addr] => 198.152.23.24
	 *             [typing_duration] => 238
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
	 * Create a message(s) from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the message to create. Alternatively, an array of such message specifications.
	 * If \c state or \c written_on columns are ommitted, they are set to \c created and the current timestamp, respectively.
	 *
	 * \return An array of primary key values of the created message(s).
	 *
	 * \ex
	 * \code
	 * create(array('subject' => 'My law proposal', 'body' => 'Dear Mr. ...', 'sender_name' => 'Bilbo Baggins', 'sender_email' => 'bilbo@hobbiton.me', 'is_public' => 'yes', 'confirmation_code' => 'abcdefghij', 'remote_addr' => '198.152.23.24', 'typing_duration' => '238'))
	 * \endcode creates a new message and returns something like
	 * \code
	 * Array
	 * (
	 *     [id] => 456
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
	 * Update the given values of the  messages that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the messages to update. Only the messages that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated message.
	 *
	 * \return An array of primary key values of the updated messages.
	 */
	public function update($params, $data)
	{
		$updated = $this->entity->update($params, $data);
		if (0 < count(array_intersect(array_keys($data), array('subject', 'body', 'sender_name', 'sender_address'))))
			self::updateFulltextData($updated);
		return $updated;
	}

	/**
	 * Delete the message(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the messages to delete. Only the messages that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted messages.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
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
			if (!($element['is_public'] && $element['state'] == 'sent')) continue;
			foreach ($this->public_fields as $field)
				$restricted_element[$field] = element[$field];
			$restricted_result[] = $restricted_element;
		}
		return $restricted_result;
	}

	/**
	 * Updates derived columns needed for fulltext search for the given messages.
	 *
	 * \param $messages An array of messages where each message is an array of message attributes where only the \c id attribute is really used.
	 */
	private static function updateFulltextData($messages)
	{
		if (!is_array(reset($messages)))
			$messages = array($messages);

		$query = new Query('kv_admin');
		foreach ($messages as $m)
		{
			// get texts of the message and of all its replies
			$query->clearParams();
			$query->setQuery('select * from message where id = $1');
			$query->appendParam($m['id']);
			$message = $query->execute();
			$message = $message[0];
			$query->setQuery('select * from replies_to_message($1)');
			$replies = $query->execute();

			// normalize texts for fulltext search (remove accents and convert to lowercase)
			$message_subject = strtolower(Utils::unaccent($message['subject']));
			$message_body = strtolower(Utils::unaccent($message['body']));
			$replies_text = '';
			foreach ((array)$replies as $reply)
				$replies_text .= strtolower(Utils::unaccent($reply['subject'] . ' ' . $reply['body'] . ' '));
			$sender_name = strtolower(Utils::unaccent($message['sender_name']));
			$sender_address = strtolower(Utils::unaccent($message['sender_address']));

			// set columns with search data to weighted concatenation of the normalized texts
			$query->setQuery(
				"update message set\n" .
				"	text_data =\n" .
				"		setweight(to_tsvector('simple', $2), 'A') ||\n" .
				"		setweight(to_tsvector('simple', $3), 'B') ||\n" .
				"		setweight(to_tsvector('simple', $4), 'C'),\n" .
				"	sender_data =\n" .
				"		setweight(to_tsvector('simple', $5), 'A') ||\n" .
				"		setweight(to_tsvector('simple', $6), 'B')\n" .
				"where id = $1\n" .
				"returning id");
			$query->appendParam($message_subject);
			$query->appendParam($message_body);
			$query->appendParam($replies_text);
			$query->appendParam($sender_name);
			$query->appendParam($sender_address);
			$query->execute();
		}
	}
}

?>
