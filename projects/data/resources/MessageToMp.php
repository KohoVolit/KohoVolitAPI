<?php

/**
 * \ingroup data
 *
 * Provides an interface to database table MESSAGE_TO_MP that holds many-to-many relationship between messages and MPs they were sent to.
 *
 * Columns of table MESSAGE_TO_MP are: <code>message_id, mp_id, parliament_code, reply_code, survey_code, private_reply_received</code>.
 *
 * All columns are allowed to write to.
 *
 * Primary key consists of columns <code>message_id, mp_id, parliament_code</code>.
 *
 * \note Due to privacy and security reasons only the following columns are accessible from remote: <code>message_id, mp_id, parliament_code</code>.
 */
class MessageToMp
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
			'name' => 'message_to_mp',
			'columns' => array('message_id', 'mp_id', 'parliament_code', 'reply_code', 'survey_code', 'private_reply_received'),
			'pkey_columns' => array('message_id', 'mp_id', 'parliament_code')
		));
		$this->public_fields = array('message_id', 'mp_id', 'parliament_code');
	}

	/**
	 * Read the message-MP relationships that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the relationships to select.
	 *
	 * \return An array of relationships that satisfy all prescribed column values.
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
	 *             [reply_code] => qwertyuiop
	 *             [survey_code] => 
	 *             [private_reply_received] => 
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		return $this->entity->read($params, 'message_to_mp');
	}

	/**
	 * Create a message-MP relationship from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the relationships to create. Alternatively, an array of such relationship specifications.
	 * \return An array of primary key values of the created relationship(s).
	 *
	 * \ex
	 * \code
	 * create(array('message_id' => 33, 'mp_id' => 712, 'parliament_code' => 'me/shire', 'reply_code_code' => 'qwertyuiop'))
	 * \endcode creates a new relationship and returns
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
		return $this->entity->create($data, 'message_to_mp');
	}

	/**
	 * Update the given values of the message-MP relationships that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the relationships to update. Only the relationships that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated relationship.
	 *
	 * \return An array of primary key values of the updated relationships.
	 */
	public function update($params, $data)
	{
		return $this->entity->update($params, $data, 'message_to_mp');
	}

	/**
	 * Delete the message-MP relationship(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the relationships to delete. Only the relationships that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted relationships.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params, 'message_to_mp');
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
				$restricted_element[$field] = $element[$field];
			$restricted_result[] = $restricted_element;
		}
		return $restricted_result;
	}
}

?>
