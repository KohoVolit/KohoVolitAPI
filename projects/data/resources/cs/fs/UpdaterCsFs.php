<?php

/**
 * This class updates data in the database for a given term of office to the state scraped from Parliament of the Czech republic - Chamber of deputies official website www.psp.cz.
 */
class UpdaterCsFs
{
	/// API client reference used for all API calls
	private $api;

	/// time and time zone used when storing dates into 'timestamp with time zone' fields
	const TIME_ZONE = 'Europe/Prague';
	const NOON = ' 12:00 Europe/Prague';

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
		$this->api = new ApiDirect('data', array('parliament' => $this->parliament_code));
		$this->log = new Log(API_LOGS_DIR . '/update/' . $this->parliament_code . '/' . strftime('%Y-%m-%d %H-%M-%S') . '.log', 'w');
		$this->log->setMinLogLevel(10);
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
	 * \return Name of the log file with update report.
	 */
	public function update($params)
	{
		$this->log->write('Started with parameters: ' . print_r($params, true));

		$this->updateParliament();
		$this->term_id = $this->updateTerm($params);
		//$this->constituencies = $this->updateConstituencies($params);
		$this->conflict_mps = $this->parseConflictMps($params);

		// remember already updated groups' source codes to update each group only once and the same for roles
		$updated_groups = array();
		$updated_roles = array();

		// the groups to resolve parent group relation for are collected here
		$this->groups_with_parent = array();

		// read list of all MPs in the term of office to update data for
		$src_mps = $this->api->read('Scraper', array('remote_resource' => 'group', 'term' => $this->term_src_code, 'list_members' => 'true'));
		$src_mps = $src_mps['group']['mp'];

		// update (or insert) all MPs in the list
		foreach($src_mps as $src_mp)
		{
			// scrape details of the MP
			$src_mp = $this->api->read('Scraper', array('remote_resource' => 'mp', 'term' => $this->term_src_code, 'id' => $src_mp['id'], 'list_memberships' => 'true'));
			$src_mp = $src_mp['mp'];

			// update the MP personal details
			$mp_id = $this->updateMp($src_mp);
			if (is_null($mp_id)) continue;		// skip conflicting MPs with no given conflict resolution

			// update other MP attributes and offices
			/*$this->updateMpAttribute($src_mp, $mp_id, 'email');
			$this->updateMpAttribute($src_mp, $mp_id, 'website');
			$this->updateMpAttribute($src_mp, $mp_id, 'address');
			$this->updateMpAttribute($src_mp, $mp_id, 'phone');
			$this->updateMpAttribute($src_mp, $mp_id, 'assistant', ', ');
			$this->updateMpImage($src_mp, $mp_id);
			$this->updateOffices($src_mp, $mp_id);*/

			// get constituency of the MP
			//$constituency_id = $this->constituencies[$src_mp['constituency']];

			$src_groups = $src_mp['group'];
			foreach ($src_groups as $src_group)
			{
				// ommit non-parliament institutions
				if ($src_group['kind'] == 'government' || $src_group['kind'] == 'institution' || $src_group['kind'] == 'international organization' ||
					$src_group['kind'] == 'european parliament' || $src_group['kind'] == 'president') continue;

				// skip wrong groups on the official cz/psp parliament website
				/*if (($src_group['id'] == 988 && $this->term_src_code == '6') || //twice the same name
					($src_group['id'] == 989 && $this->term_src_code == '6') || //twice the same name
					($src_group['id'] == 990 && $this->term_src_code == '6') || //twice the same name
					($src_group['id'] == 992 && $this->term_src_code == '6') || //twice the same name
					($src_group['id'] == 993 && $this->term_src_code == '6') || //twice the same name
					($src_group['id'] == 864 && $this->term_src_code != '6') ||
					($src_group['id'] == 728 && $this->term_src_code == '4') ||
					(strcmp($src_group['since'] . self::NOON, $this->term_until) > 0 ||
						isset($src_group['until']) && strcmp($src_group['until'] . self::NOON, $this->term_since) < 0))
				{
					$this->log->write('Skipping wrong group (' . print_r($src_group, true) . ") in MP (source id = {$src_mp['id']}).", Log::DEBUG);
					continue;
				}*/

				// update (or insert) groups the MP is member of
				if (isset($updated_groups[$src_group['name']]))
					$group_id = $updated_groups[$src_group['name']];
				else
				{
					$group_id = $this->updateGroup($src_group);
					$updated_groups[$src_group['name']] = $group_id;
				}

				// update (or insert) roles the MP stands in groups
				$src_role_name = (!empty($src_group['role'])) ? $src_group['role'] : 'člen';
				$src_role_name = strtr($src_role_name, array('poslanec' => 'člen', 'poslankyně' => 'člen'));
				$src_role_name = strtr($src_role_name, array('Hlasoval za klub' => 'člen', 'Hlasovala za klub' => 'člen'));	
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
		
		//update divisions and mps' votes 
		//$this->updateDivisionsAndVotes();

		$this->log->write('Completed.');
		return array('log' => $this->log->getFilename());
	}
	
	private function updateDivisionsAndVotes() {
	  $this->log->write("Updating divisions and votes: '{$this->parliament_code}'.", Log::DEBUG);
	  //get last source division id from database
	  $query = new Query();
	  $query->setQuery("
	    SELECT max(CAST (da.value as INT)) FROM division_attribute as da
		LEFT JOIN division as d
		ON da.division_id = d.id
		WHERE da.name='source_code' and d.parliament_code = '{$this->parliament_code}'");
	  $maxs_db = $query->execute();
	  $this->log->write("Last division in db: " . $maxs_db[0]['max'], Log::DEBUG);
	  
	  //get last source division from scraperwiki
	  $maxs_scraper = $this->api->read('Scraper', array('remote_resource' => 'last_division'));
	  $this->log->write("Last division in scraper: " . $maxs_scraper['division_id'], Log::DEBUG);
	  
	  //vote2vote_kind_code
		//cz_psp
	  $vote2vote_kind_code = array (
		  'A' => 'y',
		  'N' => 'n',
		  'Z' => 'a',
		  'X' => 'm',
		  'I' => 'b',
	  );
	  //division attributes
		//cz_psp
	  $attributes = array(
		  array('name' => 'division in session','src' => 'division'),
		  array('name' => 'session','src' => 'session'),
		  array('name' => 'needed','src' => 'needed'),
		  array('name' => 'passed','src' => 'passed'),
		  array('name' => 'source_code','src' => 'id'),
		  array('name' => 'present','src' => 'present'),
	  );
	  
	  //insert new divisions and votes, from last in db to last in scraperwiki
	  for ($i = $maxs_db[0]['max']+1; $i <= $maxs_scraper['division_id']; $i++) {
	    $division_src = $this->api->read('Scraper', array('remote_resource' => 'division', 'id' => $i));
	    
	    //create new division in db
	    $division_pkey = $this->api->create('Division', array(
	      'name' => $division_src['info'][0]->name,
	      'divided_on' => $division_src['info'][0]->date . ' ' . $division_src['info'][0]->time . ':00',
	      'parliament_code' => $this->parliament_code,
	      'division_kind_code' => $this->division_kind_code($division_src['info'][0]->present,$division_src['info'][0]->needed)
	    ));
	    //create attributes
	    foreach ($attributes as $attribute) {
	      $new_attribute = array(
	        'name' => $attribute['name'],
	        'value' => $division_src['info'][0]->$attribute['src'],
	        'division_id' => $division_pkey['id'],
	      );
	      $this->api->create('DivisionAttribute',$new_attribute);
	    }
	    
	    
	    //insert votes
	    if (count($division_src['votes']) > 0) {
	          // MPs already in the database, get them
		  $db_mps = $this->api->read('MpAttribute',array('name' => 'source_code','parl' => $this->parliament_code));
		  $mps = array();
		  foreach ($db_mps as $db_mp) {
			  $mps[$db_mp['value']] = $db_mp['mp_id'];
		  }
		  	  //add known errors
		  $mps['287'] = 388;
		  $mps['5253'] = 388;
		  $mps['223'] = 256;
		  $mps['388'] = 256;
		  $mps['189'] = 193;
		  $mps['329'] = 193;
		  $mps['4147'] = 278;
		  
		  
	      foreach($division_src['votes'] as $mp_src) {
	        //check for the mp, get mp_id
	        if (isset($mps[$mp_src->mp_id]))
	          $mp_id = $mps[$mp_src->mp_id];
	        else 
			  $this->log->write("When updating divisions from parliament '{$this->parliament_code}' found MP, which is not in DB:" . print_r($mp_src,1), Log::FATAL_ERROR);
			//insert vote
			$this->api->create("MpVote",array(
			  'mp_id' => $mp_id,
			  'division_id' => $division_pkey['id'],
			  'vote_kind_code' => $vote2vote_kind_code[$mp_src->vote]
			));
	      }
	    }
	  }
	  
	}

	  /**
	  * calculates division_kind_code in cz/psp
	  *
	  * \param present mps
	  * \param needed to pass the division
	  */
	  private function division_kind_code ($present, $needed) {
		  if ($needed >= 120) $out = '3/5';
		  else if (($needed == 101) and ($present != 200)) $out = 'absolute';
		  else $out = 'simple';
		  return $out;
	  }
  
	/**
	 * Update the last_updated timestamp for this parliament. If the parliament is not present in database yet, insert it.
	 *
	 * \returns code of the parliament.
	 */
	private function updateParliament()
	{
		$this->log->write("Updating parliament '{$this->parliament_code}'.", Log::DEBUG);

		// if parliament does not exist yet, insert it
		$parliament = $this->api->readOne('Parliament', array('code' => $this->parliament_code));
		if (!$parliament)
		{
			$this->api->create('Parliament', array(
				'code' => $this->parliament_code,
				'name' => 'Federální shromáždění',
				'short_name' => 'Federální shromáždění',
				'description' => 'Federální shromáždění ČSFR.',
				'parliament_kind_code' => 'national',
				'country_code' => 'cs',
				'time_zone' => self::TIME_ZONE
			));

			// english translation
			$this->api->create('ParliamentAttribute', array(
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name' => 'name', 'value' => 'Federal Assembly'),
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name' => 'short_name', 'value' => 'Federal Assembly'),
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name' => 'description', 'value' => 'Federal Assembly of Czech and Slovak Federative Republic')
			));

			// a function to show appropriate info about representatives of this parliament for use by NapisteJim application
			$this->api->create('ParliamentAttribute', array(array('parliament_code' => $this->parliament_code, 'name' => 'napistejim_repinfo_function', 'value' => 'napistejim_repinfo_politgroup_office')));
		}

		// update the timestamp the parliament has been last updated on
		$this->api->update('Parliament', array('code' => $this->parliament_code), array('last_updated_on' => 'now'));

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
			$current_term = $this->api->read('Scraper', array('remote_resource' => 'current_term'));
			$term_src_code = $current_term['term']['id'];
		}
		$this->term_src_code = $term_src_code;

		// get details of the term
		$term_list = $this->api->read('Scraper', array('remote_resource' => 'term_list'));
		$term_list = $term_list['term'];
		foreach($term_list as $term)
			if ($term['id'] == $term_src_code)
				$term_to_update = $term;

		// if there is no such term in the term list, terminate with error (class Log writing a message with level FATAL_ERROR throws an exception)
		if (!isset($term_to_update))
			$this->log->write("The term to update parliament {$this->parliament_code} for does not exist, check " . API_DOMAIN . "/data/Scraper?parliament={$this->parliament_code}&remote_resource=term_list", Log::FATAL_ERROR, 400);

		// if the term is present in the database, update it and get its id
		$src_code_in_db = $this->api->readOne('TermAttribute', array('name' => 'source_code', 'value' => $term_src_code, 'parl' => $this->parliament_code));
		if ($src_code_in_db)
		{
			$term_id = $src_code_in_db['term_id'];
			$data = array('name' => $term_to_update['name'], 'since' => $term_to_update['since'] . self::NOON);
			if (isset($term_to_update['short_name']))
				$data['short_name'] = $term_to_update['short_name'];
			if (isset($term_to_update['until']))
				$data['until'] = $term_to_update['until'] . self::NOON;
			$this->api->update('Term', array('id' => $term_id), $data);
		}
		else
		{
			// if term is not in the database, insert it and get its id
			$data = array('name' => $term_to_update['name'], 'country_code' => 'cz', 'parliament_kind_code' => 'national-lower', 'since' => $term_to_update['since'] . self::NOON);
			if (isset($term_to_update['short_name']))
				$data['short_name'] = $term_to_update['short_name'];
			if (isset($term_to_update['until']))
				$data['until'] = $term_to_update['until'] . self::NOON;
			$term_pkey = $this->api->create('Term', $data);
			$term_id = $term_pkey['id'];

			// insert term's source code as an attribute
			$this->api->create('TermAttribute', array('term_id' => $term_id, 'name' => 'source_code', 'value' => $term_src_code, 'parl' => $this->parliament_code));
		}

		// prepare start and end dates of this term and start date of the following term
		$this->term_since = $term_to_update['since'] . self::NOON;
		$this->term_until = isset($term_to_update['until']) ? $term_to_update['until'] . self::NOON : '9999-12-31';	//	'infinity' cannot be used due to date comparisons by strcmp
		$index = array_search($term_to_update, $term_list);
		$this->next_term_since = isset($term_list[$index+1]) ? $term_list[$index+1]['since'] . self::NOON : 'infinity';

		// set the effective date which the update process actually runs to
		$this->update_date = (isset($params['term'])) ? $this->term_since : 'now';

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
		$src_constituencies = $this->api->read('Scraper', array('remote_resource' => 'constituency_list', 'term' => $this->term_src_code));
		$res = array();
		foreach ($src_constituencies['constituency'] as $src_constituency)
			$res[$src_constituency['name']] = $this->updateConstituency($src_constituency);

		// close all older constituencies
		$open_constituencies = $this->api->read('Constituency', array('parliament_code' => $this->parliament_code, '_datetime' => $this->update_date));
		foreach ($open_constituencies as $open_constituency)
			if (!array_key_exists($open_constituency['name'], $res))
				$this->api->update('Constituency', array('id' => $open_constituency['id']), array('until' => $this->term_since));

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
		$src_code_in_db = $this->api->readOne('ConstituencyAttribute', array('name' => 'source_code', 'value' => $src_code, 'parl' => $this->parliament_code));
		if ($src_code_in_db)
		{
			$constituency_id = $src_code_in_db['constituency_id'];
			$data = array('name' => $src_constituency['name']);
			if (isset($src_constituency['short_name']))
				$data['short_name'] = $src_constituency['short_name'];
			if (isset($src_constituency['description']))
				$data['description'] = $src_constituency['description'];
			$this->api->update('Constituency', array('id' => $constituency_id), $data);
		}
		// if constituency is not in the database, insert it and its source code
		else
		{
			// in case that another constituency with the same name for this parliament exists in database, close its validity
			$other_constituency = $this->api->readOne('Constituency', array('name' => $src_constituency['name'], 'parliament_code' => $this->parliament_code, '_datetime' => $this->update_date));
			if ($other_constituency)
				$this->api->update('Constituency', array('id' => $other_constituency['id']), array('until' => $this->term_since));

			// insert the constituency
			$data = array('name' => $src_constituency['name'], 'parliament_code' => $this->parliament_code, 'since' => $this->term_since);
			if (isset($src_constituency['short_name']))
				$data['short_name'] = $src_constituency['short_name'];
			if (isset($src_constituency['description']))
				$data['description'] = $src_constituency['description'];
			$constituency_pkey = $this->api->create('Constituency', $data);
			$constituency_id = $constituency_pkey['id'];

			// insert source code of the constituency
			$this->api->create('ConstituencyAttribute', array('constituency_id' => $constituency_id, 'name' => 'source_code', 'value' => $src_code, 'parl' => $this->parliament_code));
		}

		// in case of current constituency, update its areas
		if ($this->update_date == 'now')
		{
			$area = $this->api->read('Area', array('constituency_id' => $constituency_id));
			if (count($area) == 0)
				$this->api->create('Area', array('constituency_id' => $constituency_id, 'administrative_area_level_1' => $src_constituency['name'], 'country' => 'Česká republika'));
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

		// if MP is already in the database, do not! update his data
		$src_code_in_db = $this->api->readOne('MpAttribute', array('name' => 'source_code', 'value' => $src_code, 'parl' => $this->parliament_code));
		
		if ($src_code_in_db)
		{
			$mp_id = $src_code_in_db['mp_id'];
			//$action = self::MP_UPDATE;
		}
		// if MP is not in the database, insert him and his source code for this parliament
		else
		{
		  //try cz/psp, ids are common for cz/psp and cs/fs
		  $src_code_in_db = $this->api->readOne('MpAttribute', array('name' => 'source_code', 'value' => $src_code, 'parl' => 'cz/psp'));
		  if ($src_code_in_db) {
		    $mp_id = $src_code_in_db['mp_id'];
		    $action = self::MP_INSERT_SOURCE_CODE;
		  } else {   
			// check for an MP in database with the same name
			$other_mp = $this->api->read('Mp', array('first_name' => $src_mp['first_name'], 'last_name' => $src_mp['last_name']));
			if (count($other_mp) == 0)
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
						$mp_id_attr = $this->api->readOne('MpAttribute', array('name' => 'source_code', 'value' => $mp_src_code, 'parl' => $parliament_code));
						if ($mp_id_attr)
							$mp_id = $mp_id_attr['mp_id'];
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
			$mp_pkey = $this->api->create('Mp', $data);
			$mp_id = $mp_pkey['id'];
			if ($action & self::MP_DISAMBIGUATE)
				$this->log->write("MP {$src_mp['first_name']} {$src_mp['last_name']} (id = $mp_id) inserted with automatic disambiguation. Refine his disambiguation by hand.", Log::WARNING);
		}

		if ($action & self::MP_INSERT_SOURCE_CODE)
			$this->api->create('MpAttribute', array('mp_id' => $mp_id, 'name' => 'source_code', 'value' => $src_code, 'parl' => $this->parliament_code));

		if ($action & self::MP_UPDATE)
			$this->api->update('Mp', array('id' => $mp_id), $data);

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
		$attr_in_db = $this->api->readOne('MpAttribute', array('mp_id' => $mp_id, 'name' => $attr_name, 'parl' => $this->parliament_code, '_datetime' => $this->update_date));
		if ($attr_in_db)
			$db_value = $attr_in_db['value'];

		if (!isset($src_value) && !isset($db_value) || isset($src_value) && isset($db_value) && (string)$src_value == (string)$db_value) return;

		// close the current record
		if (isset($db_value))
			$this->api->update('MpAttribute', array('mp_id' => $mp_id, 'name' => $attr_name, 'parl' => $this->parliament_code, 'since' =>  $attr_in_db['since']), array('until' => $this->update_date));

		// and insert a new one
		if (isset($src_value))
			$this->api->create('MpAttribute', array('mp_id' => $mp_id, 'name' => $attr_name, 'value' => $src_value, 'parl' => $this->parliament_code, 'since' => $this->update_date, 'until' => $this->next_term_since));
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
		$image_in_db = $this->api->readOne('MpAttribute', array('mp_id' => $mp_id, 'name' => 'image', 'parl' => $this->parliament_code, 'since' => $this->term_since));
		if (!$image_in_db)
		{
			// close record for image from previous term-of-office
			$this->api->update('MpAttribute', array('mp_id' => $mp_id, 'name' => 'image', 'parl' => $this->parliament_code, 'until' => 'infinity'), array('until' => $this->term_since));

			// insert current image
			$db_image_filename = $src_mp['id'] . '_' . $this->term_src_code . '.jpg';
			$this->api->create('MpAttribute', array('mp_id' => $mp_id, 'name' => 'image', 'value' => $db_image_filename, 'parl' => $this->parliament_code, 'since' => $this->term_since, 'until' => $this->next_term_since));

			// if the directory for MP images does not exist, create it
			$path = API_FILES_DIR . '/' . $this->parliament_code . '/images/mp';
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
		$db_offices = $this->api->read('Office', array('mp_id' => $mp_id, 'parliament_code' => $this->parliament_code, '_datetime' => $this->update_date));

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
					$this->api->update('Office', array('mp_id' => $mp_id, 'parliament_code' => $this->parliament_code, 'address' => $src_parsed_address, 'since' => $db_office['since']), array('phone' => $phone));

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
				$geo = $this->api->read('Scraper', array('remote_resource' => 'geocode', 'address' => $src_office['address']));
				if ($geo['coordinates']['ok'])
				{
					$data['latitude'] = $geo['coordinates']['lat'];
					$data['longitude'] = $geo['coordinates']['lng'];
				}

				$this->api->create('Office', $data);
			}
		}

		// close offices in the database that are no more valid
		foreach ($db_offices as $db_office)
			if (!isset($db_office['#valid']))
				$this->api->update('Office', array('mp_id' => $db_office['mp_id'], 'parliament_code' => $this->parliament_code, 'address' => $db_office['address'],  'since' => $db_office['since']), array('until' => $this->update_date));
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
		/*if ($src_group['kind'] != 'parliament')
		{
			$src_code_in_db = $this->api->readOne('GroupAttribute', array('name' => 'source_code', 'value' => $src_group['id'], 'parl' => $this->parliament_code));
			if ($src_code_in_db)
				$group_id = $src_code_in_db['group_id'];

			// and scrape further details about the group
			$grp = $this->api->read('Scraper', array('remote_resource' => 'group', 'term' => $this->term_src_code, 'id' => $src_group['id']));
			$src_group['short_name'] = (isset($grp['group']['short_name'])) ? $grp['group']['short_name'] : null;
			$src_group['parent_name'] = (isset($grp['group']['parent_name'])) ? $grp['group']['parent_name'] : null;

			if (in_array($src_group['kind'], array('political group', 'committee', 'commission', 'delegation', 'friendship group', 'working group'), true))
				$src_group['parent_name'] = 'Poslanecká sněmovna';
		}
		// presence of the group "whole parliament" in the database is tested differently
		else
		{
			$parl_in_db = $this->api->readOne('Group', array('group_kind_code' => 'parliament', 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
			if ($parl_in_db)
				$group_id = $parl_in_db['id'];

			// add further details about the group
			$src_group['short_name'] = 'Sněmovna';
		}*/
		
		$parl_in_db = $this->api->readOne('Group', array('term_id' => $this->term_id, 'parliament_code' => $this->parliament_code, 'name'=>$src_group['name']));
			if ($parl_in_db)
				$group_id = $parl_in_db['id'];

		// extract column values to update or insert from the scraped group
		$data = array(
			'name' => $src_group['name'],
			//'short_name' => $src_group['short_name'],
			'group_kind_code' => $src_group['kind'],
			'parliament_code' => $this->parliament_code,
			'term_id' => $this->term_id,
			'last_updated_on' => $this->update_date
		);

		if (isset($group_id))
			// update
			$this->api->update('Group', array('id' => $group_id), $data);
		else
		{
			// insert
			$group_pkey = $this->api->create('Group', $data);
			$group_id = $group_pkey['id'];

			// insert group's source code
			/*if ($src_group['kind'] != 'parliament')
				$this->api->create('GroupAttribute', array('group_id' => $group_id, 'name' => 'source_code', 'value' => $src_group['id'], 'parl' => $this->parliament_code));*/
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
		$role = $this->api->readOne('RoleAttribute', array('name' => 'male_name', 'value' => $src_role['male_name'], 'lang' => 'cs'));
		if ($role)
			return $role['role_code'];

		// search roles for the given male name (this is the case of parliament-specific roles like government members)
		$role = $this->api->readOne('Role', array('male_name' => $src_role['male_name']));
		if ($role)
			return $role['code'];

		// if role has not been found, insert it
		$role_code = preg_replace('/[\'^"]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $src_role['male_name'])));		// code = lowercase male name without accents
		$data = array('code' => $role_code, 'male_name' => $src_role['male_name'], 'female_name' => $src_role['female_name'], 'description' => "Appears in parliament {$this->parliament_code}.");
		$role_pkey = $this->api->create('Role', $data);
		return $role_pkey['code'];
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
		$memb = $this->api->readOne('MpInGroup', array('mp_id' => $mp_id, 'group_id' => $group_id, 'role_code' => $role_code, 'since' => $src_group['since'] . self::NOON));
		if ($memb)
		{
			$data = array('constituency_id' => $constituency_id);
			if (isset($src_group['until']))
				$data['until'] = $src_group['until'] . self::NOON;
			$this->api->update('MpInGroup', array('mp_id' => $mp_id, 'group_id' => $group_id, 'role_code' => $role_code, 'since' => $src_group['since'] . self::NOON), $data);
		}
		// if it is not present, insert it
		else
		{
			$data = array('mp_id' => $mp_id, 'group_id' => $group_id, 'role_code' => $role_code, 'constituency_id' => $constituency_id, 'since' => $src_group['since'] . self::NOON);
			if (isset($src_group['until']))
				$data['until'] = $src_group['until'] . self::NOON;
			$this->api->create('MpInGroup', $data);
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
			$parent = $this->api->readOne('Group', array('name' => $parent_group_name, 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
			if ($parent)
				$this->api->update('Group', array('id' => $id), array('subgroup_of' => $parent['id'], 'last_updated_on' => $this->update_date));
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
