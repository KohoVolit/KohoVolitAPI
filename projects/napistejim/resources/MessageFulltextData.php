<?php

/**
 * \ingroup napistejim
 *
 * Updates the preprocessed message data needed for fulltext search of messages.
 */
class MessageFulltextData
{
	/**
	 * Updates the preprocessed message data needed for fulltext search of messages for a given message.
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying the message to update the preprocessed data for. Available parameters are:
	 * - \c message_id specifying id of the message.
	 * \param $data Not used, just for consistency with update() methods of other API resources.
	 *
	 * \return Array with one element - id of the updated message or an empty array if the message does not exist.
	 *
	 * \note This method can be called only <a href="http://community.kohovolit.eu/doku.php/api#using_api_from_php_on_localhost">from localhost where the API is installed on</a>
	 * as it needs write access to the database.
	 *
	 * \ex
	 * \code
	 * update(array('message_id' => 123))
	 * \endcode updates the needed data and returns
	 * \code
	 * Array
	 * (
	 *     [id] => 123
	 * )
	 * \endcode
	 */
	public function update($params, $data)
	{
		$query = new Query();
		
		// get texts of the message and of all its replies
		$query->setQuery('select * from message where id = $1');
		$query->appendParam($params['message_id']);
		$message = $query->execute();
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
			"returning id";
		$query->appendParam($message_subject);
		$query->appendParam($message_body);
		$query->appendParam($replies_text);
		$query->appendParam($sender_name);
		$query->appendParam($sender_address);
		return $query->execute();
	}	
}

?>
