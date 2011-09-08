<?php

/**
 * \ingroup wtt
 *
 * Provides all needed information about responses to a given message.
 */
class ResponseToMessage
{
	/**
	 * Returns all needed information to show about responses to a given message.
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying the message to get responses to. Available parameters are:
	 * - \c message_id specifying id of the message
	 *
	 * \return Details of the selected responses and of their authors - MPs.
	 *
	 * \ex
	 * \code
	 * read(array('message_id' => 232))
	 * \endcode returns something like
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [message_id] => 232
	 *             [subject] => Re: My law proposal
	 *             [body_] => Dear Mr. Baggins ...
	 *             [received_on] => 2011-07-12 18:35:56.226578
	 *             [mp_id] => 731
	 *             [first_name] => Jiří
	 *             [middle_names] => 
	 *             [last_name] => Skalický
	 *             [disambiguation] => PSP ČR 2010-
	 *             [sex] => m
	 *             [parliament_code] => cz/psp
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
			"	message_id, subject, body_, received_on, mp_id, first_name, middle_names, last_name, disambiguation, sex, parliament_code\n" .
			"from\n" .
			"	response as r\n" .
			"	join mp on mp.id = r.mp_id\n" .
			"where\n" .
			"	r.message_id = $1\n" .
			"order by\n" .
			"	mp_id"
		);
		$query->appendParam($params['message_id']);
		return $query->execute();
	}
}

?>
