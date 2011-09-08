<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table MESSAGE that holds messages sent to MPs.
 *
 * Columns of table MESSAGE are: <code>id, subject, body_, sender_name, sender_address, sender_email, is_public, state_, written_on, sent_on, confirmation_code, approval_code</code>.
 *
 * Column <code>id</code> is a read-only column automaticaly generated on create.
 *
 * Primary key is column <code>id</code>.
 *
 * \note Messages are not accessible through API from remote due to privacy and security reasons.
 */
class Message
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'message',
			'columns' => array('id', 'subject', 'body_', 'sender_name', 'sender_address', 'sender_email', 'is_public', 'state_', 'written_on', 'sent_on', 'confirmation_code', 'approval_code'),
			'pkey_columns' => array('id'),
			'readonly_columns' => array('id')
		));
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
	 * read(array('sender_email' => 'bilbo@hobbiton.me', 'state_' => 'sent'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 123
	 *             [subject] => My law proposal
	 *             [body_] => Dear Mr. ...
	 *             [sender_name] => Bilbo Baggins
	 *             [sender_address] => Bag End, Hobbiton
	 *             [sender_email] => bilbo@hobbiton.me
	 *             [is_public] => yes
	 *             [state_] => sent
	 *             [written_on] => 2011-05-26 13:06:04
	 *             [sent_on] => 2011-05-26 13:09:32
	 *             [confirmation_code] => abcdefghij
	 *             [approval_code] => 
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
	 * If \c state_ or \c written_on columns are ommitted, they are set to \c created and the current timestamp, respectively.
	 *
	 * \return An array of primary key values of the created message(s).
	 *
	 * \ex
	 * \code
	 * create(array('subject' => 'My law proposal', 'body_' => 'Dear Mr. ...', 'sender_name' => 'Bilbo Baggins', 'sender_email' => 'bilbo@hobbiton.me', 'is_public' => 'yes', 'confirmation_code' => 'abcdefghij'))
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
		return $this->entity->create($data);
	}

	/**
	 * Update the given values of the  messages that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the messages to update. Only the  messages that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated message.
	 *
	 * \return An array of primary key values of the updated messages.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data);
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
}

?>
