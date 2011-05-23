<?php

/**
 * This class updates data in the database for a given term of office to the state scraped from Parliament of the Czech republic - Chamber of deputies official website www.psp.cz.
 */
class UpdaterCzPsp
{
	/// API client reference used for all API calls
	private $ac;

	/// id and source code (ie. id on the official website) of the term of office to update the data for
	private $term_id;
	private $term_src_code;

	/// start and end dates of the term of office to update the data for and start date of the next term
	private $term_since;
	private $term_until;
	private $next_term_since;

	/// effective date which the update process actually runs to
	private $update_date;

	/// code of this updated parliament
	private $parliament_code;

	/// array of MPs in this parliament that have the same name as an already existing MP in the database
	private $conflict_mps;

	/// mapping of constituency names to constituency id-s for the updated term
	private $constituencies;

	/// constants for actions in updating of an MP, actions may be combined
	const MP_INSERT = 0x1;
	const MP_INSERT_SOURCE_CODE = 0x2;
	const MP_DISAMBIGUATE = 0x4;
	const MP_UPDATE = 0x8;

	/// groups that have a parent group are collected during the update process and the parentship is resolved at the end of the process
	private $groups_with_parent;

	/**
	 * Creates API client reference to use during the whole update process.
	 */
	public function __construct($params)
	{
		$this->parliament_code = $params['parliament'];
		$this->ac = new ApiDirect('kohovolit', array('parliament' => $this->parliament_code));
		$this->log = new Log(LOGS_DIR . '/update/' . $this->parliament_code . '/' . strftime('%Y-%m-%d %H-%M-%S') . '.log', 'w');
		$this->log->setMinLogLevel(Log::DEBUG);
	}

	/**
	 * Main method called by API resource Updater - it scrapes data and updates the database.
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the update process.
	 *
	 * Parameter <em>$params['term']</em> indicates source code of the term of office to update data for. If ommitted, the current term is assumed.
	 *
	 * Parameter <em>$param['conflict_mps']</em> specifies how to resolve the cases when there is an MP in the database with the same name as a new MP scraped from this parliament.
	 * The variable maps the source codes of conflicting MPs being scraped to either parliament code/source code of an MP in the database to merge with (eg. <em>cz/psp/5229</em>)
	 * or to nothing to create a new MP with the same name.
	 * In the latter case the new created MP will have a generated value in the disambiguation column that should be later changed by hand.
	 * The mapping is expected as a string in the form <em>pair1,pair2,...</em> where each pair is either <em>mp_src_code->parliament_code/mp_src_code</em> or
	 *<em>mp_src_code-></em>.
	 *
	 * \return Result of the update process.
	 */
	public function update($params)
	{
		$this->log->write('Started with parameters: ' . print_r($params, true));

		$this->updateParliament();
		$this->term_id = $this->updateTerm($params);
		$this->constituencies = $this->updateConstituencies($params);
		$this->conflict_mps = $this->parseConflictMps($params);

		// remember already updated groups' source codes to update each group only once and the same for roles
		$updated_groups = array();
		$updated_roles = array();

		// the groups to resolve parent group relation for are collected here
		$this->groups_with_parent = array();

		// read list of all MPs in the term of office to update data for
		$src_mps = $this->ac->read('Scraper', array('remote_resource' => 'group', 'term' => $this->term_src_code, 'list_members' => 'true'));
		$src_mps = $src_mps['group']['mp'];

		// update (or insert) all MPs in the list
		foreach($src_mps as $src_mp)
		{
			// scrape details of the MP
			$src_mp = $this->ac->read('Scraper', array('remote_resource' => 'mp', 'term' => $this->term_src_code, 'id' => $src_mp['id'], 'list_memberships' => 'true'));
			$src_mp = $src_mp['mp'];

			// update the MP personal details
			$mp_id = $this->updateMp($src_mp);
			if (is_null($mp_id)) continue;		// skip conflicting MPs with no given conflict resolution

			// update other MP attributes and offices
			$this->updateMpAttribute($src_mp, $mp_id, 'email');
			$this->updateMpAttribute($src_mp, $mp_id, 'website');
			$this->updateMpAttribute($src_mp, $mp_id, 'address');
			$this->updateMpAttribute($src_mp, $mp_id, 'phone');
			$this->updateMpAttribute($src_mp, $mp_id, 'assistant', ', ');
			$this->updateMpImage($src_mp, $mp_id);
			$this->updateOffices($src_mp, $mp_id);

			// get constituency of the MP
			$constituency_id = $this->constituencies[$src_mp['constituency']];

			$src_groups = $src_mp['group'];
			foreach ($src_groups as $src_group)
			{
				// ommit non-parliament institutions
				if ($src_group['kind'] == 'government' || $src_group['kind'] == 'institution' || $src_group['kind'] == 'international organization' ||
					$src_group['kind'] == 'european parliament' || $src_group['kind'] == 'president') continue;

				// skip wrong groups on the official cz/psp parliament website
				if (($src_group['id'] == 864 && $this->term_src_code != '6') ||
					($src_group['id'] == 728 && $this->term_src_code == '4') ||
					(strcmp($src_group['since'], $this->term_until) > 0 || isset($src_group['until']) && strcmp($src_group['until'], $this->term_since) < 0))
				{
					$this->log->write('Skipping wrong group (' . print_r($src_group, true) . ") in MP (source id = {$src_mp['id']}).", Log::ERROR);
					continue;
				}

				// update (or insert) groups the MP is member of
				if (isset($updated_groups[$src_group['id']]))
					$group_id = $updated_groups[$src_group['id']];
				else
				{
					$group_id = $this->updateGroup($src_group);
					$updated_groups[$src_group['id']] = $group_id;
				}

				// update (or insert) roles the MP stands in groups
				$src_role_name = (!empty($src_group['role'])) ? $src_group['role'] : 'člen';
				$src_role_name = strtr($src_role_name, array('poslanec' => 'člen', 'poslankyně' => 'člen'));
				if (isset($updated_roles[$src_role_name]))
					$role_code = $updated_roles[$src_role_name];
				else
				{
					$role_code = $this->updateRole(array('male_name' => $src_role_name, 'female_name' => $src_role_name));
					$updated_roles[$src_role_name] = $role_code;
				}

				// update memberships of the MP in groups
				$cid = $src_group['kind'] == 'parliament' ? $constituency_id : null;

				// skip wrong memberships on the official cz/psp parliament website
				if (($src_mp['id'] == 377 && $src_group['id'] == 478 && $role_code == 'member' && $src_group['since'] == '2000-07-11') ||
					($src_mp['id'] == 310 && $src_group['id'] == 596 && $role_code == '1. mistopredseda' && $src_group['since'] == '2002-09-17') ||
					($src_mp['id'] == 250 && $src_group['id'] == 599 && $role_code == 'vice-chairman' && $src_group['since'] == '2005-12-13'))
				{
					$this->log->write('Skipping wrong membership (' . print_r($src_group, true) . ") in MP (source id = {$src_mp['id']}).", Log::ERROR);
					continue;
				}

				$this->updateMembership($src_group, $mp_id, $group_id, $role_code, $cid);
			}
		}

		// resolve the parentship relation for collected groups having a parent group
		$this->updateParentship();

		$this->log->write('Completed.');
		return array('update' => 'OK');
	}

	/**
	 * Update the last_updated timestamp for this parliament. If the parliament is not present in database yet, insert it.
	 *
	 * \returns code of the parliament.
	 */
	private function updateParliament()
	{
		$this->log->write("Updating parliament '{$this->parliament_code}'.", Log::DEBUG);

		$parliament = $this->ac->read('Parliament', array('code' => $this->parliament_code));

		// if parliament does not exist yet, insert it
		if (!isset($parliament['parliament'][0]))
		{
			$this->ac->create('Parliament', array(array(
				'code' => $this->parliament_code,
				'name_' => 'Poslanecká sněmovna Parlamentu České republiky',
				'short_name' => 'PSP ČR',
				'description' => 'Dolní komora parlamentu České republiky.',
				'parliament_kind_code' => 'national-lower',
				'country_code' => 'cz',
				'default_language' => 'cs'
			)));

			// english translation
			$this->ac->create('ParliamentAttribute', array(
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name_' => 'name_', 'value_' => 'Chamber of Deputies of Parliament of the Czech republic'),
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name_' => 'short_name', 'value_' => 'CDP CR'),
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name_' => 'description', 'value_' => 'Lower house of the Czech republic parliament.')
			));
		}

		// update the timestamp the parliament has been last updated on
		$this->ac->update('Parliament', array('code' => $this->parliament_code), array('last_updated_on' => 'now'));

		return $this->parliament_code;
	}

	/**
	 * Update information about the term of office to update data for. If the term is not present in database, insert it.
	 *
	 * \param $params <em>$params['term']</em> indicates source code of the term of office to update data for. If ommitted, the current term is assumed.
	 *
	 * \returns id of the term to update data for.
	 */
	private function updateTerm($params)
	{
		$this->log->write("Updating term.", Log::DEBUG);

		// get source code of the term to update data for
		if (isset($params['term']))
			$term_src_code = $params['term'];
		else
		{
			$current_term = $this->ac->read('Scraper', array('remote_resource' => 'current_term'));
			$term_src_code = $current_term['term']['id'];
		}
		$this->term_src_code = $term_src_code;

		// get details of the term
		$term_list = $this->ac->read('Scraper', array('remote_resource' => 'term_list'));
		$term_list = $term_list['term'];
		foreach($term_list as $term)
			if ($term['id'] == $term_src_code)
				$term_to_update = $term;

		// if there is no such term in the term list, terminate with error (class Log writing a message with level FATAL_ERROR throws an exception)
		if (!isset($term_to_update))
			$this->log->write("The term to update parliament {$this->parliament_code} for does not exist, check http://api.kohovolit.eu/kohovolit/Scrape?parliament={$this->parliament_code}&remote_resource=term_list", Log::FATAL_ERROR, 400);

		// if the term is present in the database, update it and get its id
		$src_code_in_db = $this->ac->read('TermAttribute', array('name_' => 'source_code', 'value_' => $term_src_code, 'parl' => $this->parliament_code));
		if (isset($src_code_in_db['term_attribute'][0]))
		{
			$term_id = $src_code_in_db['term_attribute'][0]['term_id'];
			$data = array('name_' => $term_to_update['name'], 'since' => $term_to_update['since']);
			if (isset($term_to_update['short_name']))
				$data['short_name'] = $term_to_update['short_name'];
			if (isset($term_to_update['until']))
				$data['until'] = $term_to_update['until'];
			$this->ac->update('Term', array('id' => $term_id), $data);
		}
		else
		{
			// if term is not in the database, insert it and get its id
			$data = array('name_' => $term_to_update['name'], 'country_code' => 'cz', 'parliament_kind_code' => 'national-lower', 'since' => $term_to_update['since']);
			if (isset($term_to_update['short_name']))
				$data['short_name'] = $term_to_update['short_name'];
			if (isset($term_to_update['until']))
				$data['until'] = $term_to_update['until'];
			$term_id = $this->ac->create('Term', array($data));
			$term_id = $term_id[0];

			// insert term's source code as an attribute
			$this->ac->create('TermAttribute', array(array('term_id' => $term_id, 'name_' => 'source_code', 'value_' => $term_src_code, 'parl' => $this->parliament_code)));
		}

		// prepare start and end dates of this term and start date of the following term
		$this->term_since = $term_to_update['since'];
		$this->term_until = isset($term_to_update['until']) ? $term_to_update['until'] : '9999-12-31';
		$index = array_search($term_to_update, $term_list);
		$this->next_term_since = isset($term_list[$index+1]) ? $term_list[$index+1]['since'] : 'infinity';

		// set the effective date which the update process actually runs to
		$this->update_date = ($this->next_term_since == 'infinity') ? 'now' : $this->term_since;

		return $term_id;
	}

	/**
	 * Update information about all constituencies for this term and close the older ones.
	 *
	 * \returns mapping of all constituency names for this term to their id-s.
	 */
	private function updateConstituencies($params)
	{
		$this->log->write("Updating constituencies.", Log::DEBUG);

		// update constituencies of this term
		$src_constituencies = $this->ac->read('Scraper', array('remote_resource' => 'constituency_list', 'term' => $this->term_src_code));
		$res = array();
		foreach ($src_constituencies['constituency'] as $src_constituency)
			$res[$src_constituency['name']] = $this->updateConstituency($src_constituency);

		// close all older constituencies
		$open_constituencies = $this->ac->read('Constituency', array('parliament_code' => $this->parliament_code, 'datetime' => $this->update_date));
		if (isset($open_constituencies['constituency'][0]))
			foreach ($open_constituencies['constituency'] as $open_constituency)
				if (!array_key_exists($open_constituency['name_'], $res))
					$this->ac->update('Constituency', array('id' => $open_constituency['id']), array('until' => $this->term_since));

		return $res;
	}

	/**
	 * Update information about a constituency. If it is not present in database, insert it.
	 *
	 * \param $src_constituency array of key => value pairs with properties of a scraped constituency
	 *
	 * \returns id of the updated or inserted constituency.
	 */
	private function updateConstituency($src_constituency)
	{
		$this->log->write("Updating constituency '{$src_constituency['name']}'.", Log::DEBUG);

		// if constituency is already in the database, update its data
		$src_code = $src_constituency['id'];
		$src_code_in_db = $this->ac->read('ConstituencyAttribute', array('name_' => 'source_code', 'value_' => $src_code, 'parl' => $this->parliament_code));
		if (isset($src_code_in_db['constituency_attribute'][0]))
		{
			$constituency_id = $src_code_in_db['constituency_attribute'][0]['constituency_id'];
			$data = array('name_' => $src_constituency['name']);
			if (isset($src_constituency['short_name']))
				$data['short_name'] = $src_constituency['short_name'];
			if (isset($src_constituency['description']))
				$data['description'] = $src_constituency['description'];
			$this->ac->update('Constituency', array('id' => $constituency_id), $data);
		}
		// if constituency is not in the database, insert it and its source code
		else
		{
			// in case that another constituency with the same name for this parliament exists in database, close its validity
			$other_constituency = $this->ac->read('Constituency', array('name_' => $src_constituency['name'], 'parliament_code' => $this->parliament_code, 'datetime' => $this->update_date));
			if (isset($other_constituency['constituency'][0]))
				$this->ac->update('Constituency', array('id' => $other_constituency['constituency'][0]['id']), array('until' => $this->term_since));

			// insert the constituency
			$data = array('name_' => $src_constituency['name'], 'parliament_code' => $this->parliament_code, 'since' => $this->term_since);
			if (isset($src_constituency['short_name']))
				$data['short_name'] = $src_constituency['short_name'];
			if (isset($src_constituency['description']))
				$data['description'] = $src_constituency['description'];
			$constituency_id = $this->ac->create('Constituency', array($data));
			$constituency_id = $constituency_id[0];

			// insert source code of the constituency
			$this->ac->create('ConstituencyAttribute', array(array('constituency_id' => $constituency_id, 'name_' => 'source_code', 'value_' => $src_code, 'parl' => $this->parliament_code)));
		}

		// in case of current constituency, update its areas
		if ($this->update_date == 'now')
		{
			$area = $this->ac->read('Area', array('constituency_id' => $constituency_id));
			if (!isset($area['area'][0]))
				$this->ac->create('Area', array(array('constituency_id' => $constituency_id, 'administrative_area_level_1' => $src_constituency['name'], 'country' => 'Česká republika')));
		}

		return $constituency_id;
	}

	/**
	 * Update personal information about an MP. If MP is not present in database, insert him.
	 *
	 * \param $src_mp array of key => value pairs with properties of a scraped MP
	 *
	 * \returns id of the updated or inserted MP.
	 */
	private function updateMp($src_mp)
	{
		$src_code = $src_mp['id'];
		$this->log->write("Updating MP '{$src_mp['first_name']} {$src_mp['last_name']}' (source id $src_code).", Log::DEBUG);

		// if MP is already in the database, update his data
		$src_code_in_db = $this->ac->read('MpAttribute', array('name_' => 'source_code', 'value_' => $src_code, 'parl' => $this->parliament_code));
		if (isset($src_code_in_db['mp_attribute'][0]))
		{
			$mp_id = $src_code_in_db['mp_attribute'][0]['mp_id'];
			$action = self::MP_UPDATE;
		}
		// if MP is not in the database, insert him and his source code for this parliament
		else
		{
			// check for an MP in database with the same name
			$other_mp = $this->ac->read('Mp', array('first_name' => $src_mp['first_name'], 'last_name' => $src_mp['last_name']));
			if (!isset($other_mp['mp'][0]))
				$action = self::MP_INSERT | self::MP_INSERT_SOURCE_CODE;
			else
			{
				// if there is a person in the database with the same name as the MP and conflict resolution is not set for him on input, report a warning and skip this MP
				if (!isset($this->conflict_mps[$src_code]))
				{
					$this->log->write("MP {$src_mp['first_name']} {$src_mp['last_name']} already exists in database! MP (source id = {$src_mp['id']}) skipped. Rerun the update process with the parameters specifying how to resolve the conflict for this MP.", Log::WARNING);
					return null;
				}
				else
				{
					// if conflict_mps indicates that this MP is already in the database, update his data and insert his source code for this parliament
					if (!empty($this->conflict_mps[$src_code]))
					{
						$pmp_code = $this->conflict_mps[$src_code];
						$p = strrpos($pmp_code, '/');
						$parliament_code = substr($pmp_code, 0, $p);
						$mp_src_code = substr($pmp_code, $p + 1);
						$mp_id = $this->ac->read('MpAttribute', array('name_' => 'source_code', 'value_' => $mp_src_code, 'parl' => $parliament_code));
						if (isset($mp_id['mp_attribute'][0]))
							$mp_id = $mp_id['mp_attribute'][0]['mp_id'];
						else
						{
							$this->log->write("Wrong parliament code and source code '$pmp_code' of an MP existing in the database specified in the \$conflict_mps parameter. MP {$src_mp['first_name']} {$src_mp['last_name']} (source id = {$src_mp['id']}) skipped.", Log::ERROR);
							return null;
						}
						$action = self::MP_UPDATE;
						if ($parliament_code != $this->parliament_code)
							$action |= self::MP_INSERT_SOURCE_CODE;
					}
					else
						// if null is given instead of an existing MP in database, insert MP as a new one, insert his source code for this parliament and generate a value for his disambigation column
						$action = self::MP_INSERT | self::MP_INSERT_SOURCE_CODE | self::MP_DISAMBIGUATE;
				}
			}
		}

		// extract column values to update or insert from the scraped MP
		$data = array(
			'first_name' => $src_mp['first_name'],
			'last_name' => $src_mp['last_name'],
			'sex' => $src_mp['sex'],
			'pre_title' => $src_mp['pre_title'],
			'post_title' => $src_mp['post_title'],
			'born_on' => $src_mp['born_on'],
			'died_on' => $src_mp['died_on'],
			'last_updated_on' => $this->update_date
		);

		// perform appropriate actions to update or insert MP
		if ($action & self::MP_INSERT)
		{
			if ($action & self::MP_DISAMBIGUATE)
				$data['disambiguation'] = $this->parliament_code . '/' . $src_code;
			$mp_id = $this->ac->create('Mp', array($data));
			$mp_id = $mp_id[0];
			if ($action & self::MP_DISAMBIGUATE)
				$this->log->write("MP {$src_mp['first_name']} {$src_mp['last_name']} (id = $mp_id) inserted with automatic disambiguation. Refine his disambiguation by hand.", Log::WARNING);
		}

		if ($action & self::MP_INSERT_SOURCE_CODE)
			$this->ac->create('MpAttribute', array(array('mp_id' => $mp_id, 'name_' => 'source_code', 'value_' => $src_code, 'parl' => $this->parliament_code)));

		if ($action & self::MP_UPDATE)
			$this->ac->update('Mp', array('id' => $mp_id), $data);

		return $mp_id;
	}

	/**
	 * Update value of an attribute of an MP. If its value has changed, close the current record and insert a new one.
	 *
	 * \param $src_mp array of key => value pairs with properties of a scraped MP
	 * \param $mp_id \e id of that MP in database
	 * \param $attr_name name of the attribute
	 * \param $implode_separator in case that <em>$src_mp[$attr_name]</em> is an array, use this parameter to set a string used for implosion of the array to a string value.
	 */
	private function updateMpAttribute($src_mp, $mp_id, $attr_name, $implode_separator = null)
	{
		$this->log->write("Updating MP's attribute '$attr_name'.", Log::DEBUG);

		$src_value = !empty($src_mp[$attr_name]) ? (is_null($implode_separator) ? $src_mp[$attr_name] : implode($implode_separator, $src_mp[$attr_name])) : null;
		$value_in_db = $this->ac->read('MpAttribute', array('mp_id' => $mp_id, 'name_' => $attr_name, 'parl' => $this->parliament_code, 'datetime' => $this->update_date));
		if (isset($value_in_db['mp_attribute'][0]))
			$db_value = $value_in_db['mp_attribute'][0]['value_'];

		if (!isset($src_value) && !isset($db_value) || isset($src_value) && isset($db_value) && $src_value == $db_value) return;

		// close the current record
		if (isset($db_value))
			$this->ac->update('MpAttribute', array('mp_id' => $mp_id, 'name_' => $attr_name, 'parl' => $this->parliament_code, 'since' =>  $value_in_db['mp_attribute'][0]['since']), array('until' => $this->update_date));

		// and insert a new one
		if (isset($src_value))
			$this->ac->create('MpAttribute', array(array('mp_id' => $mp_id, 'name_' => $attr_name, 'value_' => $src_value, 'parl' => $this->parliament_code, 'since' => $this->update_date, 'until' => $this->next_term_since)));
	}

	/**
	 * Update information about image of an MP.
	 *
	 * Not a full update is implemented, only if an image for this term-of-office is not present in the database and it is detected on the source website, it is inserted into database.
	 * Change of the image during the term cannot be detected, because image filenames are randomly generated on each request. It would need to compare images by file content.
	 *
	 * \param $src_mp array of key => value pairs with properties of a scraped MP
	 * \param $mp_id \e id of that MP in database
	 */
	private function updateMpImage($src_mp, $mp_id)
	{
		if (!isset($src_mp['image_url'])) return;
		$this->log->write("Updating MP's image.", Log::DEBUG);

		// check for existing image in the database and if it is not present, insert its filename and download the image file
		$image_in_db = $this->ac->read('MpAttribute', array('mp_id' => $mp_id, 'name_' => 'image', 'parl' => $this->parliament_code, 'since' => $this->term_since));
		if (!isset($image_in_db['mp_attribute'][0]))
		{
			// close record for image from previous term-of-office
			$this->ac->update('MpAttribute', array('mp_id' => $mp_id, 'name_' => 'image', 'parl' => $this->parliament_code, 'until' => 'infinity'), array('until' => $this->term_since));

			// insert current image
			$db_image_filename = $src_mp['id'] . '_' . $this->term_src_code . '.jpg';
			$this->ac->create('MpAttribute', array(array('mp_id' => $mp_id, 'name_' => 'image', 'value_' => $db_image_filename, 'parl' => $this->parliament_code, 'since' => $this->term_since, 'until' => $this->next_term_since)));

			// if the directory for MP images does not exist, create it
			$path = DATA_DIR . '/' . $this->parliament_code . '/images/mp';
			if (!file_exists($path))
				mkdir($path, 0775, true);

			$image = file_get_contents($src_mp['image_url']);
			file_put_contents($path . '/' . $db_image_filename, $image);
		}
	}

	/**
	 * Update information about offices of an MP. Insert new offices and close records for the ones that are no more valid.
	 *
	 * \param $src_mp array of key => value pairs with properties of a scraped MP
	 * \param $mp_id \e id of that MP in database
	 */
	private function updateOffices($src_mp, $mp_id)
	{
		$this->log->write("Updating MP's offices.", Log::DEBUG);

		$src_offices = isset($src_mp['office']) ? $src_mp['office'] : array();
		$db_offices = $this->ac->read('Office', array('mp_id' => $mp_id, 'parliament_code' => $this->parliament_code, 'datetime' => $this->update_date));
		$db_offices = isset($db_offices['office']) ? $db_offices['office'] : array();

		// insert all scraped offices that are not present in the database yet
		foreach ($src_offices as $src_office)
		{
			$src_parsed_address = $this->parseAddress($src_office['address']);
			$found = false;
			foreach ($db_offices as &$db_office)
			{
				if ($src_parsed_address == $db_office['address'])
				{
					// update phone number of the office
					$phone = isset($src_office['phone']) ? $src_office['phone'] : '';
					$this->ac->update('Office', array('mp_id' => $mp_id, 'parliament_code' => $this->parliament_code, 'address' => $src_parsed_address, 'since' => $db_office['since']), array('phone' => $phone));

					$db_office['#valid'] = true;
					$found = true;
					break;
				}
			}
			if (!$found)
			{
				$phone = isset($src_office['phone']) ? $src_office['phone'] : '';
				$relevance = ($src_parsed_address == '|Sněmovní|4|Praha 1|118 26|Česká republika') ? 0.5 : 1.0;

				$data = array('mp_id' => $mp_id, 'parliament_code' => $this->parliament_code, 'address' => $src_parsed_address, 'phone' => $phone, 'relevance' => $relevance, 'since' => $this->update_date, 'until' => $this->next_term_since);

				//geocode
				$geo = $this->ac->read('Scraper', array('remote_resource' => 'geocode', 'address' => $src_office['address']));
				if ($geo['coordinates']['ok'])
				{
					$data['latitude'] = $geo['coordinates']['lat'];
					$data['longitude'] = $geo['coordinates']['lng'];
				}

				$this->ac->create('Office', array($data));
			}
		}

		// close offices in the database that are no more valid
		foreach ($db_offices as $db_office)
			if (!isset($db_office['#valid']))
				$this->ac->update('Office', array('mp_id' => $db_office['mp_id'], 'parliament_code' => $this->parliament_code, 'address' => $db_office['address'],  'since' => $db_office['since']), array('until' => $this->update_date));
	}

	/**
	 * Update information about a group. If the group is not present in database, insert it.
	 *
	 * \param $src_group array of key => value pairs with properties of a scraped group
	 *
	 * \returns id of the updated or inserted group.
	 */
	private function updateGroup($src_group)
	{
		$this->log->write("Updating group '{$src_group['name']}' (source id {$src_group['id']}).", Log::DEBUG);

		// for all groups except the whole parliament check presence in the database by group's source code as an attribute
		if ($src_group['kind'] != 'parliament')
		{
			$src_code_in_db = $this->ac->read('GroupAttribute', array('name_' => 'source_code', 'value_' => $src_group['id'], 'parl' => $this->parliament_code));
			if (isset($src_code_in_db['group_attribute'][0]))
				$group_id = $src_code_in_db['group_attribute'][0]['group_id'];

			// and scrape further details about the group
			$grp = $this->ac->read('Scraper', array('remote_resource' => 'group', 'term' => $this->term_src_code, 'id' => $src_group['id']));
			$src_group['short_name'] = (isset($grp['group']['short_name'])) ? $grp['group']['short_name'] : null;
			$src_group['parent_name'] = (isset($grp['group']['parent_name'])) ? $grp['group']['parent_name'] : null;

			if (in_array($src_group['kind'], array('political group', 'committee', 'commission', 'delegation', 'friendship group', 'working group')))
				$src_group['parent_name'] = 'Poslanecká sněmovna';
		}
		// presence of the group "whole parliament" in the database is tested differently
		else
		{
			$parl_in_db = $this->ac->read('Group', array('group_kind_code' => 'parliament', 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
			if (isset($parl_in_db['group'][0]))
				$group_id = $parl_in_db['group'][0]['id'];

			// add further details about the group
			$src_group['short_name'] = 'Sněmovna';
		}

		// extract column values to update or insert from the scraped group
		$data = array(
			'name_' => $src_group['name'],
			'short_name' => $src_group['short_name'],
			'group_kind_code' => $src_group['kind'],
			'parliament_code' => $this->parliament_code,
			'term_id' => $this->term_id,
			'last_updated_on' => $this->update_date
		);

		if (isset($group_id))
			// update
			$this->ac->update('Group', array('id' => $group_id), $data);
		else
		{
			// insert
			$group_id = $this->ac->create('Group', array($data));
			$group_id = $group_id[0];

			// insert group's source code
			if ($src_group['kind'] != 'parliament')
				$this->ac->create('GroupAttribute', array(array('group_id' => $group_id, 'name_' => 'source_code', 'value_' => $src_group['id'], 'parl' => $this->parliament_code)));
		}

		// if the group has a parent group, add it to the list to resolve parentship
		if (isset($src_group['parent_name']))
			$this->groups_with_parent[$group_id] = $src_group['parent_name'];

		return $group_id;
	}

	/**
	 * Return code of the role present in database for a given male name or, if it is not present, insert it.
	 *
	 * \param $src_role array of key => value pairs with properties of a scraped role
	 *
	 * \returns code of the role.
	 */
	private function updateRole($src_role)
	{
		$this->log->write("Updating role '{$src_role['male_name']}'.", Log::DEBUG);

		// search czech translations of common roles for the given male name (this is the case of generic roles like 'chairman')
		$role = $this->ac->read('RoleAttribute', array('name_' => 'male_name', 'value_' => $src_role['male_name'], 'lang' => 'cs'));
		if (isset($role['role_attribute'][0]))
			return $role['role_attribute'][0]['role_code'];

		// search roles for the given male name (this is the case of parliament-specific roles like government members)
		$role = $this->ac->read('Role', array('male_name' => $src_role['male_name']));
		if (isset($role['role'][0]))
			return $role['role'][0]['code'];

		// if role has not been found, insert it
		$role_code = preg_replace('/[\'^"]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $src_role['male_name'])));		// code = lowercase male name without accents
		$data = array('code' => $role_code, 'male_name' => $src_role['male_name'], 'female_name' => $src_role['female_name'], 'description' => "Appears in parliament {$this->parliament_code}.");
		$role_code = $this->ac->create('Role', array($data));
		return $role_code[0];
	}

	/**
	 * Update information about the membership of an MP in a group. If it is not present in database, insert it.
	 * Membership is identified by the quadruple (\e mp_id, \e group_id, \e role_code, \e since).
	 *
 	 * \param $src_group array of key => value pairs with properties of a scraped group
	 * \param $mp_id \e id of the MP in database
	 * \param $group_id \e id of the group in database
	 * \param $role_code \e code of the role in database that MP stands in the membership
	 * \param $constituency_id \e id of the constituency in database applicable for the membership or null
	 */
	private function updateMembership($src_group, $mp_id, $group_id, $role_code, $constituency_id)
	{
		$this->log->write("Updating membership (mp_id=$mp_id, group_id=$group_id, role_code='$role_code', since={$src_group['since']}).", Log::DEBUG);

		// if membership is already present in database, update its details
		$memb = $this->ac->read('MpInGroup', array('mp_id' => $mp_id, 'group_id' => $group_id, 'role_code' => $role_code, 'since' => $src_group['since']));
		if (isset($memb['mp_in_group'][0]))
		{
			$data = array('constituency_id' => $constituency_id);
			if (isset($src_group['until']))
				$data['until'] = $src_group['until'];
			$this->ac->update('MpInGroup', array('mp_id' => $mp_id, 'group_id' => $group_id, 'role_code' => $role_code, 'since' => $src_group['since']), $data);
		}
		// if it is not present, insert it
		else
		{
			$data = array('mp_id' => $mp_id, 'group_id' => $group_id, 'role_code' => $role_code, 'constituency_id' => $constituency_id, 'since' => $src_group['since']);
			if (isset($src_group['until']))
				$data['until'] = $src_group['until'];
			$this->ac->create('MpInGroup', array($data));
		}
	}

	/**
	 * Update parent group id for all groups with a parent group collected during the update process.
	 */
	private function updateParentship()
	{
		$this->log->write("Updating parent reference of the updated groups.", Log::DEBUG);

		foreach ($this->groups_with_parent as $id => $parent_group_name)
		{
			$parent = $this->ac->read('Group', array('name_' => $parent_group_name, 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
			if (isset($parent['group'][0]))
				$this->ac->update('Group', array('id' => $id), array('subgroup_of' => $parent['group'][0]['id'], 'last_updated_on' => $this->update_date));
			else
				$this->log->write("Parent '$parent_group_name' of group with id = $id has not been found in the database.", Log::WARNING);
		}
	}

	/**
	 * Decodes parameter with conflicitng MPs to an array.
	 *
	 * \param $params['conflict_mps'] list of conflicting MPs in a string of the form <em>pair1,pair2,...</em> where each pair is either
	 * <em>mp_src_code->parliament_code/mp_src_code</em> or <em>mp_src_code-></em>.
	 *
	 * \returns mapping of conflicting MPs in an array corresponding to the given list
	 */
	private function parseConflictMps($params)
	{
		if (!isset($params['conflict_mps'])) return array();

		$cmps = explode(',', $params['conflict_mps']);
		$res = array();
		foreach ($cmps as $mp)
		{
			$p = strpos($mp, '->');
			$res[trim(substr($mp, 0, $p))] = trim(substr($mp, $p + 2));
		}
		return $res;
	}

	/**
	 * Parse address scraped from source web and convert to the format used in the database.
	 *
	 * \param $address address in the source form, ie. "$street $house_number, $postal_code $town"
	 *
	 * \returns address in the format used in the database, ie. "$addressee|$street|$house_number|$town|$postal_code|$country"
	 */
	private function parseAddress($address)
	{
		preg_match('/([^0-9,]*)([^,]*), *(\d\d\d \d\d)?(.*)$/u', str_replace('|', '/', $address), $matches);
		$street = isset($matches[1]) ? trim($matches[1]) : '';
		$house_number = isset($matches[2]) ? trim($matches[2]) : '';
		$postal_code = isset($matches[3]) ? trim($matches[3]) : '';
		$town = isset($matches[4]) ? trim($matches[4]) : '';
		return "|$street|$house_number|$town|$postal_code|Česká republika";
	}
}

?>
