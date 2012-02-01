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
	 * - \c lang [optional] specifying language code to return information in
	 *
	 * \return Details of the replies and of their authors. Authors are ordered by date of their (first) reply.
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
	 *                     [mp_image] => 5292_6.jpg
	 *                     [political_group] => ČSSD
	 *                     [parliament_code] => cz/psp
	 *                     [parliament] => Sněmovna
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
		$query->appendParam($params['message_id']);
		if (isset($params['lang']) && !empty($params['lang']))
		{
			$query->appendParam($params['lang']);
			$query->setQuery('select * from replies_to_message($1, $2)');
		}
		else
			$query->setQuery('select * from replies_to_message($1)');		
		$replies = $query->execute();

		// aggregate replies by MPs
		$result = array();
		foreach ($replies as $r)
		{
			if (!isset($result[$r['mp_id']]))
			{
				$mp = $r;
				unset($mp['message_id'], $mp['subject'], $mp['body'], $mp['received_on']);
				if (!empty($mp['mp_image']))
					$mp['mp_image'] = $mp['parliament_code'] . '/images/mp/' . $mp['mp_image'];
				$result[$mp['mp_id']] = $mp;
			}
			$result[$r['mp_id']]['reply'][] = array('subject' => $r['subject'], 'body' => $r['body'], 'received_on' => $r['received_on']);
		}

		// sort MPs by date of their (first) reply
		usort($result, array('RepliesToMessage', 'cmpByFirstReply'));

		return array('message_id' => $params['message_id'], 'mp' => array_values($result));
	}

	private static function cmpByFirstReply($a, $b)
	{
		$alr = end($a['reply']);
		$blr = end($b['reply']);
		$alrd = $alr['received_on'];
		$blrd = $blr['received_on'];
		if (empty($alrd)) return 1;
		if (empty($blrd)) return -1;
		if ($alrd < $blrd) return -1;
		if ($alrd > $blrd) return 1;
		return 0;
	}
}

?>
