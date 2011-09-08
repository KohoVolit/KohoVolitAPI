<?php

/**
 * \ingroup wtt
 *
 * Lists all messages sent to a given MP.
 */
class MessageToMp
{
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
	 * read(array('parliament_code' => 'cz/senat', 'since' => '2011-09-01 10:00:00'))
	 * \endcode returns something like
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 1030
	 *             [subject] => My law proposal
	 *             [body_] => Dear Mr. ...
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
			"	m.id, m.subject, m.body_, m.is_public, m.sender_name, sender_address, mp.id as mp_id, mp.first_name, mp.middle_names, mp.last_name, mp.disambiguation\n" .
			"from\n" .
			"	message as m\n" .
			"	join response as r on r.message_id = m.id\n" .
			"	join mp on mp.id = r.mp_id\n" .
			"where\n" .
			"	m.state_ = 'sent'\n" .
			"	and r.mp_id = $1\n" .
			"	and r.parliament_code = $2"
		);
		$query->appendParam($params['mp_id']);
		$query->appendParam($params['parliament_code']);
		return $query->execute();
	}
}

?>
