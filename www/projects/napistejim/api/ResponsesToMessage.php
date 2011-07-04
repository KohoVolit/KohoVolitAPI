<?php

/**
 * Class ResponsesToMessage provides all needed information about responses to a given message.
 *
 * Only the \e read method is present.
 */
class ResponsesToMessage
{
	/**
	 * Returns all needed information to show about responses to a given message.
	 *
	 * \param $params An array with one pair \c 'message_id' => \e message_id specifying the message id to get responses to.
	 * Example: <code>array('message' => 229)</code>.
	 *
	 * \return Details of the selected responses and of their authors - MPs.
	 */
	public static function read($params)
	{
		$query = new Query();
		$query->setQuery(
			"select\n" .
			"	message_id, subject, body_, received_on, mp_id, first_name, middle_names, last_name, disambiguation, sex, parliament_code\n" .
			"from\n" .
			"	response as r\n" .
			"	join mp on mp.id = r.mp_id\n" .
			"where\n" .
			"	r.message_id = $1"
		);
		$query->appendParam($params['message_id']);
		$responses = $query->execute();
		return array('responses_to_message' => $responses);
	}
}

?>
