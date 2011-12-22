<?php

/**
 * This class updates data in the database using the manual corrections data scraped from Google Spreadsheet.
 */
class UpdaterCorrections
{
	/// API client reference used for all API calls
	private $api;

	/// effective date which the update process actually runs to
	private $update_date;

	/**
	 * Creates API client reference to use during the whole update process.
	 */
	public function __construct($params)
	{
		$this->api = new ApiDirect('data');
		$this->log = new Log(API_LOGS_DIR . '/update/corrections/' . strftime('%Y-%m-%d %H-%M-%S') . '.log', 'w');
	}

	/**
	 * Main method called by API resource Updater - it scrapes data and updates the database.
	 *
	 * \return Name of the log file with update report.
	 */
	public function update($params)
	{
		$this->log->write('Started with parameters: ' . print_r($params, true));

		$this->update_date = 'now';

		// update private e-mail addresses
		$rows = $this->api->read('Scraper', array('parliament' => 'corrections', 'remote_resource' => 'private_emails'));
		$rows = $rows['private_emails'];
		foreach ($rows as $row)
			$this->updateMpAttribute('private_email', $row);

		// close all private_email attribute records not present in the scraped corrections
		$this->closeMissingCorrections('private_email', $rows);

		$this->log->write('Completed.');
		return array('log' => $this->log->getFilename());
	}

	/**
	 * Update value of an attribute of an MP. If its value has changed, close the current record and insert a new one.
	 *
	 * \param $attr_name name of the attribute
	 * \param $data array of key => value pairs identifying an MP and its attribute value
	 * \param $mandatory [boolean] whether the attribute must be always present even with an empty value (true) or may be completely missing for some MP (false)
	 */
	private function updateMpAttribute($attr_name, $data, $mandatory = false)
	{
		$this->log->write("Updating attribute '$attr_name' of MP '{$data['first_name']} {$data['last_name']}'.", Log::DEBUG);

		// get id of the MP from his source code
		$mp_src_code = $this->api->readOne('MpAttribute', array('name' => 'source_code', 'value' => $data['source_code'], 'parl' => $data['parliament_code']));
		if (!$mp_src_code)
		{
			$this->log->write("MP with source code '{$data['source_code']}' and parliament code '{$data['parliament_code']}' not found in the database. {$data['first_name']} {$data['last_name']} skipped.", Log::ERROR);
			return;
		}
		$mp_id = $mp_src_code['mp_id'];

		// check, if the value has changed
		$value_to_set = isset($data[$attr_name]) && !empty($data[$attr_name]) ? $data[$attr_name] : ($mandatory ? '' : null);
		$attr_in_db = $this->api->readOne('MpAttribute', array('mp_id' => $mp_id, 'name' => $attr_name, 'parl' => $data['parliament_code'], '_datetime' => $this->update_date));
		if ($attr_in_db)
			$value_in_db = $attr_in_db['value'];
		if (!isset($value_to_set) && !isset($value_in_db) || isset($value_to_set) && isset($value_in_db) && (string)$value_to_set == (string)$value_in_db) return;

		// close the current record
		if (isset($value_in_db))
			$this->api->update('MpAttribute', array('mp_id' => $mp_id, 'name' => $attr_name, 'parl' => $data['parliament_code'], 'since' =>  $attr_in_db['since']), array('until' => $this->update_date));

		// and insert a new one
		if (isset($value_to_set))
			$this->api->create('MpAttribute', array('mp_id' => $mp_id, 'name' => $attr_name, 'value' => $value_to_set, 'parl' => $data['parliament_code'], 'since' => $this->update_date));
	}

	/**
	 * Close records of the given attribute in database for all MPs not present in the given corrections data.
	 *
	 * \param $attr_name name of the attribute
	 * \param $data array of key => value pairs identifying an MP and its attribute value in corrections
	 * \param $mandatory [boolean] whether the attribute must be always present even with an empty value (true) or may be completely missing for some MP (false)
	 */
	private function closeMissingCorrections($attr_name, $data, $mandatory = false)
	{
		$this->log->write("Closing records of attribute '$attr_name' missing in scraped correction data.", Log::DEBUG);

		// get id-s of all MPs present in corrections data
		$updated_mps = array();
		foreach ($data as $row)
		{
			$mp_src_code = $this->api->readOne('MpAttribute', array('name' => 'source_code', 'value' => $row['source_code'], 'parl' => $row['parliament_code']));
			if ($mp_src_code)
				$updated_mps[] = array('id' => $mp_src_code['mp_id'], 'parl' => $row['parliament_code']);
		}

		// get all current values of this attribute
		$attrs = $this->api->read('MpAttribute', array('name' =>$attr_name, '_datetime' => $this->update_date));

		// close the records not present in corrections data
		foreach ($attrs as $attr)
		{
			if (in_array(array('id' => $attr['mp_id'], 'parl' => $attr['parl']), $updated_mps)) continue;

			$this->api->update('MpAttribute', array('mp_id' => $attr['mp_id'], 'name' => $attr_name, 'parl' => $attr['parl'], 'since' =>  $attr['since']), array('until' => $this->update_date));
			if ($mandatory)
				$this->api->create('MpAttribute', array('mp_id' => $attr['mp_id'], 'name' => $attr_name, 'value' => '', 'parl' => $attr['parl'], 'since' => $this->update_date));
		}
	}
}
