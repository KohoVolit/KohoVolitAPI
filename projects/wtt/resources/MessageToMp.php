<?php

/**
 * Class MessageToMp lists all messages sent to a given MP.
 *
 * Only the \e read method is present.
 */
class MessageToMp
{
	/**
	 * Returns all messages sent to a given MP.
	 *
	 * \param $params An array with two pairs specifying addressee of the sent messages: \c 'mp_id' => \e mp_id ndash; id of the MP and \c 'parliament_code' => \e parliament_code.
	 * Example: <code>array('mp_id' => 150, 'parliament_code' => 'cz/senat')</code>.
	 *
	 * \return Details of the sent messages.
	 */
	public static function read($params)
	{
		$query = new Query();
		$query->setQuery(
			"select\n" .
			"	m.id, m.subject, m.body_, is_public, mp.id, mp.first_name, mp.middle_names, mp.last_name, mp.disambiguation\n" .
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
