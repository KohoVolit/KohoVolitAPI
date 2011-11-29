<?php

/**
 * \ingroup napistejim
 *
 * Provides all needed information about replies to a given message.
 */
class RepliesToMessage
{
	/**
	 * Returns all needed information to show about replies to a given message.
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying the message to get replies to. Available parameters are:
	 * - \c message_id specifying id of the message
	 *
	 * \return Details of the selected replies and of their authors - MPs.
	 *
	 * \ex
	 * \code
	 * read(array('message_id' => 232))
	 * \endcode returns something like
	 * \code
	 * Array
	 * (
	 *     [message_id] => 232
	 *     [mp] => Array
	 *         (
	 *             [0] => Array
	 *                 (
	 *                     [mp_id] => 731
	 *                     [first_name] => Jiří
	 *                     [middle_names] =>
	 *                     [last_name] => Skalický
	 *                     [disambiguation] => PSP ČR 2010-
	 *                     [sex] => m
	 *                     [parliament_code] => cz/psp
	 *                     [reply] => Array
	 *                         (
	 *                             [0] => Array
	 *                                 (
	 *                                     [subject] => Re: My law proposal
	 *                                     [body] => Dear Mr. Baggins ...
	 *                                     [received_on] => 2011-07-12 18:35:56.226578
	 *                                 )
	 *
	 *                         )
	 *
	 *                 )
	 *
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		$query = new Query();
		$query->setQuery('select * from replies_to_message($1)');
		$query->appendParam($params['message_id']);
		$replies = $query->execute();

		// aggregate replies by MPs
		$result = array();
		foreach ($replies as $r)
		{
			if (!isset($result[$r['mp_id']]))
			{
				$mp = $r;
				unset($mp['message_id'], $mp['subject'], $mp['body'], $mp['received_on']);
				$result[$mp['mp_id']] = $mp;
			}
			$result[$r['mp_id']]['reply'][] = array('subject' => $r['subject'], 'body' => $r['body'], 'received_on' => $r['received_on']);
		}
		return array('message_id' => $params['message_id'], 'mp' => array_values($result));
	}
}

?>
