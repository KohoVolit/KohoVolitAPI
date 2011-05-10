<?php

/**
 * Class Letter provides information about letters sent to MPs through API and implements CRUD operations on database table LETTER.
 *
 * Columns of table LETTER are: <em>id, subject, body_, sender_name, sender_address, sender_email, sent_on, is_public, state_, reply_code, approval_code</em>. All columns are allowed to write to except the <em>id</em> which is automaticaly generated on create and it is read-only.
 */

class Letter extends Entity
{
	/**
	 * Initialize list of column names of the table and which of them are read only (automatically generated on creation).
	 */
	public static function initColumnNames()
	{
		self::$tableColumns = array('id', 'subject', 'body_', 'sender_name', 'sender_address', 'sender_email', 'sent_on', 'is_public', 'state_', 'reply_code', 'approval_code');
		self::$roColumns = array('id');
	}

	/**
	 * Read letter(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the letters to select. Only letters satisfying all prescribed column values are returned.
	 *
	 * \return An array of letters with structure <code>array('letter' => array(array('id' => 12, 'subject' => 'My law proposal', 'body_' => 'Dear Mr. ...', ...), ...))</code>.
	 */
	public static function read($params)
	{
		return parent::readEntity($params, 'letter');
	}

	/**
	 * Create letter(s) with given values.
	 *
	 * \param $data An array of letters to create, where each letter is given by array of pairs <em>column => value</em>. Eg. <code>array(array('id' => 12, 'subject' => 'My law proposal', 'body_' => 'Dear Mr. ...', ...), ...)</code>.
	 *
	 * \return An array of \e id-s of created letters.
	 */
	public static function create($data)
	{
		return parent::createEntity($data, 'letter', 'id');
	}

	/**
	 * Update letter(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the letters to update. Only letters satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected letter.
	 *
	 * \return An array of \e id-s of updated letters.
	 */
	public static function update($params, $data)
	{
		return parent::updateEntity($params, $data, 'letter', 'id');
	}

	/**
	 * Delete letter(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the letters to delete. Only letters satisfying all prescribed column values are deleted.
	 *
	 * \return An array of \e id-s of deleted letters.
	 */
	public static function delete($params)
	{
		return parent::deleteEntity($params, 'letter', 'id');
	}
}

Letter::initColumnNames();

?>
