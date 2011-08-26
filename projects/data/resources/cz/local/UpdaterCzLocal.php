<?php

/**
 * This class updates data in the database for a given term of office to the state scraped from some local councils in the Czech Republic.
 */
class UpdaterCzLocal
{
	/// API client reference used for all API calls
	private $api;

	/// id and source code (ie. id on the official website) of the term of office to update the data for
	private $term_id;
	private $term_src_code;
	
	/// code of this updated parliament
	private $parliament_code;
	
	/// array of MPs in this parliament that have the same name as an already existing MP in the database
	private $conflict_mps;

	/// constants for actions in updating of an MP, actions may be combined
	const MP_INSERT = 0x1;
	const MP_INSERT_SOURCE_CODE = 0x2;
	const MP_DISAMBIGUATE = 0x4;
	const MP_UPDATE = 0x8;

	/// date for update
	private $date; //class DateTime
	private $update_date; //formatted date/time
	/**
	 * Creates API client reference to use during the whole update process.
	 */
	public function __construct($params)
	{
	    $this->parliament_code = $params['parliament'];
		$this->api = new ApiDirect('data', array('parliament' => $this->parliament_code));
		$this->log = new Log(API_LOGS_DIR . '/update/' . $this->parliament_code . '/' . strftime('%Y-%m-%d %H-%M-%S') . '.log', 'w');
		$this->log->setMinLogLevel(Log::DEBUG);
	}
	
	/**
	 * Main method called by API resource Updater - it scrapes data and updates the database.
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
		
		//update parliaments and terms
		$parliaments = $this->updateParliamentsAndTermsAndGroups($params);
		$this->conflict_mps = $this->parseConflictMps($params);
		
		// read list of all MPs in the term of office to update data for
		$src_mps = $this->api->read('Scraper', array('remote_resource' => 'mp'));
		$src_mps = $src_mps['mp'];
		

		// update (or insert) all MPs in the list
		foreach($src_mps as $src_mp) {
		  // update the MP personal details
		  $mp_id = $this->updateMp($src_mp);
		  if (is_null($mp_id)) continue;		// skip conflicting MPs with no given conflict resolution
		  	
		  // update other MP attributes and offices, mps
		  $this->updateMpAttribute($src_mp, $mp_id, 'email', null);
		}
		
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
		$value_in_db = $this->api->readOne('MpAttribute', array('mp_id' => $mp_id, 'name_' => $attr_name, 'parl' => $src_mp['parliament_code'], '#datetime' => $this->update_date));
		if ($value_in_db)
			$db_value = $value_in_db['value_'];

		if (!isset($src_value) && !isset($db_value) || isset($src_value) && isset($db_value) && $src_value == $db_value) return;

		// close the current record
		if (isset($db_value))
			$this->api->update('MpAttribute', array('mp_id' => $mp_id, 'name_' => $attr_name, 'parl' => $src_mp['parliament_code'], 'since' =>  $value_in_db['since']), array('until' => $this->update_date));

		// and insert a new one
		if (isset($src_value))
			$this->api->create('MpAttribute', array('mp_id' => $mp_id, 'name_' => $attr_name, 'value_' => $src_value, 'parl' => $src_mp['parliament_code'], 'since' => $this->update_date, 'until' => $this->next_term_since));
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
		$src_code = $src_mp['source_code'];
	  	$this->log->write("Updating MP '{$src_mp['first_name']} {$src_mp['last_name']}' (parliament {$src_mp['parliament_name']}).", Log::DEBUG);
	  	
	  	// if MP is already in the database, update his data
		$src_code_in_db = $this->api->readOne('MpAttribute', array('name_' => 'source_code', 'value_' => $src_code, 'parl' => $src_mp['parliament_code']));
		if ($src_code_in_db)
		{
			$mp_id = $src_code_in_db['mp_id'];
			$action = self::MP_UPDATE;
		}
		// if MP is not in the database, insert him and his source code for this parliament
		else
		{
		    // check for an MP in database with the same name
			$other_mp = $this->api->read('Mp', array('first_name' => trim($src_mp['first_name']), 'last_name' => trim($src_mp['last_name'])));
			if (count($other_mp) == 0)
				$action = self::MP_INSERT | self::MP_INSERT_SOURCE_CODE;
			else
			{
				// if there is a person in the database with the same name as the MP and conflict resolution is not set for him on input, report a warning and skip this MP
				if (!isset($this->conflict_mps[$src_code]))
				{
					$this->log->write("MP {$src_mp['first_name']} {$src_mp['last_name']} already exists in database! MP (source id = {$src_code}) skipped. Rerun the update process with the parameters specifying how to resolve the conflict for this MP.", Log::WARNING);
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
						$mp_id_attr = $this->api->readOne('MpAttribute', array('name_' => 'source_code', 'value_' => $mp_src_code, 'parl' => $parliament_code));
						if ($mp_id_attr)
							$mp_id = $mp_id_attr['mp_id'];
						else
						{
							$this->log->write("Wrong parliament code and source code '$pmp_code' of an MP existing in the database specified in the \$conflict_mps parameter. MP {$src_mp['first_name']} {$src_mp['last_name']} (source id/code = {$src_code}) skipped.", Log::ERROR);
							return null;
						}
						$action = self::MP_UPDATE;
						if ($parliament_code != $src_mp['parliament_code'])
							$action |= self::MP_INSERT_SOURCE_CODE;
					}
					else
						// if null is given instead of an existing MP in database, insert MP as a new one, insert his source code for this parliament and generate a value for his disambigation column
						$action = self::MP_INSERT | self::MP_INSERT_SOURCE_CODE | self::MP_DISAMBIGUATE;
				}
			}
		}
	  	
	  	// extract column values to update or insert from the scraped MP
		if (isset($src_mp['first_name']))
			$data['first_name'] = trim($src_mp['first_name']);
		if (isset($src_mp['last_name']))
			$data['last_name'] = trim($src_mp['last_name']);
		if (isset($src_mp['sex']) and ($src_mp['sex'] != ''))
			$data['sex'] = self::correctSex(trim($src_mp['sex']));
		if (isset($src_mp['post_title']) and ($src_mp['post_title'] != ''))
			$data['post_title'] = trim($src_mp['post_title']);
		if (isset($src_mp['pre_title']) and ($src_mp['pre_title'] != ''))
			$data['pre_title'] = trim($src_mp['pre_title']);
		if (isset($src_mp['disambiguation']) and (trim($src_mp['disambiguation']) != ''))
			$data['disambiguation'] = trim($src_mp['disambiguation']);
		//date now
		$data['last_updated_on'] = date('Y-m-d H:i:s.u');
		
		// perform appropriate actions to update or insert MP
		if ($action & self::MP_INSERT)
		{
			if ($action & self::MP_DISAMBIGUATE)
			  if (!(isset($data['disambiguation']) and ($data['disambiguation'] != '')))
				$data['disambiguation'] = $src_code;
			$mp_pkey = $this->api->create('Mp', $data);
			$mp_id = $mp_pkey['id'];
			if ($action & self::MP_DISAMBIGUATE)
				$this->log->write("MP {$data['first_name']} {$data['last_name']} (id = $mp_id) inserted with automatic disambiguation. Refine his disambiguation by hand.", Log::WARNING);
		}

		if ($action & self::MP_INSERT_SOURCE_CODE)
			$this->api->create('MpAttribute', array('mp_id' => $mp_id, 'name_' => 'source_code', 'value_' => $src_code, 'parl' => $src_mp['parliament_code']));

		if ($action & self::MP_UPDATE)
			$this->api->update('Mp', array('id' => $mp_id), $data);

		return $mp_id;
	  	
	}

	/**
	 * Update the last_updated timestamp for these parliaments. If a parliament, a term, a group=parliament is not present in database yet, insert it.
	 *
	 * @return array of parliaments and terms
	 */
	private function updateParliamentsAndTermsAndGroups($params)
	{
	  //read list of parliaments
	  $src_parliaments = $this->api->read('Scraper', array('remote_resource' => 'parliament_list'));
	  
	  foreach ((array) $src_parliaments['parliament'] as $src_parliament) {
	
		$this->log->write("Updating parliament '{$src_parliament['parliament_code']}'.", Log::DEBUG);

		// if parliament does not exist yet, insert it
		$parliament = $this->api->readOne('Parliament', array('code' => $src_parliament['parliament_code']));
		  switch ($src_parliament['parliament_code']) {
		    case 'cz/starostove':
		      $description = 'Starostové';
		      break;
		    default:
		      $description = 'Zastupitelstvo ' . $src_parliament['parliament_name'];
		  }
		if (!$parliament)
		{
			$this->api->create('Parliament', array(
				'code' => $src_parliament['parliament_code'],
				'name_' => $src_parliament['parliament_name'],
				'parliament_kind_code' => 'local',
				'country_code' => 'cz',
				'default_language' => 'cs',
				'description' => $description
			));
		}
		
		// update the timestamp the parliament has been last updated on
		$this->api->update('Parliament', array('code' => $src_parliament['parliament_code']), array('last_updated_on' => 'now'));

		
		//if term does not exist yet, insert it, otherwise update it
		$this->log->write("Updating term '{$src_parliament['term']}'.", Log::DEBUG);
		$term = $this->api->readOne('Term', array('name_' => $src_parliament['term'], 'parliament_kind_code' => 'local', 'country_code' => 'cz'));
		if (!$term) {
		  $term = $this->api->create('Term', array(
		    'name_' => $src_parliament['term'],
		    'parliament_kind_code' => 'local',
		    'country_code' => 'cz',
		    'since' => $src_parliament['since'],
		    'until' => $src_parliament['until'],
		  ));
		} else {
		  $term = $this->api->update('Term', array('id' => $term['id']), array('since' => $src_parliament['since'], 'until' => $src_parliament['until']));
		  $term = $term[0];
		}
		
		// set the effective date which the update process actually runs to
		// ** NEEDS REWRITE (?)
		$this->next_term_since = 'infinity';
		$this->update_date = 'now';
		
		
		//if group(=parliament) does not exist yet, insert it, otherwise update it
		$this->log->write("Updating group '{$src_parliament['parliament_name']}'.", Log::DEBUG);
		// if group=parliament does not exist yet, insert it
		$group = $this->api->readOne('Group', array('name_' => $src_parliament['parliament_name'], 'parliament_code' => $src_parliament['parliament_code'], 'term_id' => $term['id']));
		if (!$group) {
		  $this->api->create('Group', array(
		    'name_' => $src_parliament['parliament_name'],
		    'parliament_code' => $src_parliament['parliament_code'],
		    'group_kind_code' => 'parliament',
		    'term_id' => $term['id'],
		  ));
		}
		
		// update the timestamp the group has been last updated on
		$this->api->update('Group', array('name_' => $src_parliament['parliament_name'], 'parliament_code' => $src_parliament['parliament_code'],'group_kind_code' => 'parliament','term_id' => $term['id']), array('last_updated_on' => 'now'));

		//get id
		$group = $this->api->readOne('Group', array('name_' => $src_parliament['parliament_name'], 'parliament_code' => $src_parliament['parliament_code'],'group_kind_code' => 'parliament','term_id' => $term['id']));		

		//save parliament and term and group=parliament into array
		$out[$src_parliament['parliament_code']] = array('parliament_code' => $src_parliament['parliament_code'],'term_id' => $term['id'], 'group_id' => $group['id']);
	  }
	  return $out;
	}

    private function correctSex ($sex) {
      switch ($sex) {
        case 'muž':
          $sex = 'm';
          break;
        case 'žena':
          $sex = 'f';
          break;
      }
      return $sex;
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

}
