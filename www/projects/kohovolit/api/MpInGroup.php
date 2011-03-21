<?php

/**
 * Class MpInGroup provides information about memberships of MPs in groups through API and implements CRUD operations on database table MP_IN_GROUP.
 *
 * Columns of table MP_IN_GROUP are: <em>mp_id, group_id, role_code, party_id, since, until</em>. All columns are allowed to write to.
 */
class MpInGroup extends Entity
{
	/**
	 * Initialize list of column names of the table and which of them are read only (automatically generated on creation).
	 */
	public static function initColumnNames()
	{
		self::$tableColumns = array('mp_id', 'group_id', 'role_code', 'party_id', 'since', 'until');
		self::$roColumns = array();
	}

	/**
	 * Read membership(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the memberships to select. Only memberships satisfying all prescribed column values are returned.
	 *
	 * \return An array of memberships with structure <code>array('mp_in_group' => array(array('mp_id' => 32, 'group_id' => 4, 'role_code' => 'treasurer', 'party_id' => null, ...), ...))</code>.
	 *
	 * You can use <em>datetime</em> within the <em>$params</em> (eg. 'datetime' => '2010-06-30 9:30:00') to select only memberships valid at the given moment (the ones where <em>since</em> <= datetime < <em>until</em>). Use 'datetime' => 'now' to get memberships valid at this moment.
	 */
	public static function read($params)
	{
		return parent::readEntity($params, 'mp_in_group', true);
	}

	/**
	 * Create membership(s) with given values.
	 *
	 * \param $data An array of memberships to create, where each membership is given by array of pairs <em>column => value</em>. Eg. <code>array(array('mp_id' => 32, 'group_id' => 4, 'role_code' => 'treasurer', 'party_id' => null, ...), ...)</code>.
	 *
	 * \return Number of created memberships.
	 */
	public static function create($data)
	{
		return parent::createEntity($data, 'mp_in_group');
	}

	/**
	 * Update membership(s) satisfying parameters to the given values.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the memberships to update. Only memberships satisfying all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each selected membership.
	 *
	 * \return Number of updated memberships.
	 */
	public static function update($params, $data)
	{
		return parent::updateEntity($params, $data, 'mp_in_group');
	}

	/**
	 * Delete membership(s) according to given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the memberships to delete. Only memberships satisfying all prescribed column values are deleted.
	 *
	 * \return Number of deleted memberships.
	 */
	public static function delete($params)
	{
		return parent::deleteEntity($params, 'mp_in_group');
	}
}

MpInGroup::initColumnNames();

?>
