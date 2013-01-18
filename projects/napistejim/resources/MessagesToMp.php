<?php

/**
 * \ingroup napistejim
 *
 * Lists all messages sent to a given MP.
 *
 * \note Due to privacy and security reasons only the following columns are accessible from remote: <code>id, subject, body, sender_name, mp_id, first_name, middle_names, last_name, disambiguation</code>.
 * Furthermore, only the public, sent messages are listed from remote.
*/
class MessagesToMp
{
	/// fields of the resource that are publicly accessible from remote
	private $public_fields;

	/**
	 * Initialize information about the fields accessible from remote.
	 */
	public function __construct()
	{
		$this->public_fields = array('id', 'subject', 'body', 'sender_name', 'mp_id', 'first_name', 'middle_names', 'last_name', 'disambiguation');
	}

	/**
	 * Returns all messages sent to a given MP.
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying an addressee. Available parameters are:
	 * - \c mp_id specifying id of an MP
	 * - \c parliament_code specifying a parliament_code the MP acts for as an addressee
	 *
	 * \return Details of the sent messages.
	 *
	 * \ex
	 * \code
	 * read(array('mp_id' => 809, 'parliament_code' => 'cz/senat'))
	 * \endcode returns something like
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 1030
	 *             [subject] => My law proposal
	 *             [body] => Dear Mr. ...
	 *             [is_public] => yes
	 *             [sender_name] => Bilbo Baggins
	 *             [sender_address] => Bag End, Hobbiton 
	 *             [mp_id] => 809
	 *             [first_name] => Jan
	 *             [middle_names] => 
	 *             [last_name] => Hajda
	 *             [disambiguation] => 
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		$query = new Query();
		$query->setQuery(
			"select\n" .
			"	m.id, m.subject, m.\"body\", m.is_public, m.sender_name, sender_address, mp.id as mp_id, mp.first_name, mp.middle_names, mp.last_name, mp.disambiguation\n" .
			"from\n" .
			"	message as m\n" .
			"	join message_to_mp as mtm on mtm.message_id = m.id\n" .
			"	join mp on mp.id = mtm.mp_id\n" .
			"where\n" .
			"	m.\"state\" = 'sent'\n" .
			"	and mtm.mp_id = $1\n" .
			"	and mtm.parliament_code = $2"
		);
		$query->appendParam($params['mp_id']);
		$query->appendParam($params['parliament_code']);
		return $query->execute();
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
			if ($element['is_public'] == 'no') continue;
			foreach ($this->public_fields as $field)
				$restricted_element[$field] = $element[$field];
			$restricted_result[] = $restricted_element;
		}
		return $restricted_result;
	}
}

?>
