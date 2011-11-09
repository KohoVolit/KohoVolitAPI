<?php

/**
 * This class updates data in the database for a given term of office to the state scraped from Parliament of the Czech republic - Chamber of deputies official website www.psp.cz.
 */
class UpdaterSkNrsr
{
	/// API client reference used for all API calls
	private $api;

	/// id and source code (ie. id on the official website) of the term of office to update the data for
	private $term_id;
	private $term_src_code;

	/// effective date which the update process actually runs to
	private $date; //class DateTime
	private $update_date; //formatted date/time

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

	/**
	 * Creates API client reference to use during the whole update process.
	 */
	public function __construct($params)
	{
	    $this->parliament_code = $params['parliament'];
		$this->api = new ApiDirect('data', array('parliament' => $this->parliament_code));
		$this->log = new Log(API_LOGS_DIR . '/update/' . $this->parliament_code . '/' . strftime('%Y-%m-%d %H-%M-%S') . '.log', 'w');
		$this->log->setMinLogLevel(Log::DEBUG);

		//convert $param['date'] into DateTime object, default = today
		if (isset($params['date']))
	  		$this->date = new DateTime($params['date']);
		else
	  		$this->date = new DateTime();
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

		//update parliament, term, parse conflict mps
		$this->updateParliament();
		$this->term_id = $this->updateTerm($params);
		$this->conflict_mps = $this->parseConflictMps($params);

		//update constituency and area (only 1 constituency)
		$constituency_id = $this->updateConstituency();

		//update group_kinds and groups
		$src_groups['political_group'] = $this->api->read('Scraper', array('remote_resource' => 'current_group_list','group_kind'=>'political_group'));
		$src_groups['committee'] = $this->api->read('Scraper', array('remote_resource' => 'current_group_list','group_kind'=>'committee'));
		$src_groups['parliament']['group'][0] = array(
		  'name' => array('value' => 'Národná rada Slovenskej republiky'),
		  'short_name' => array('value' => 'Národná rada'),
		);
		$this->updateGroups($src_groups);

		// read list of all MPs in the term of office to update data for
		$src_mps = $this->api->read('Scraper', array('remote_resource' => 'mp_list'));
		//insert or update all MPs (all terms) + update/insert membership in current term
		foreach ((array) $src_mps['mp'] as $src_mp) {
		  $mp_id = $this->updateMp($src_mp);
		  if (is_null($mp_id)) continue;		// skip conflicting MPs with no given conflict resolution
		  // update other MP attributes and offices, mps

		  $my_src_mp = array(
		      'ethnicity' => $src_mp['narodnost']['value'],
	          'location' => $src_mp['bydlisko']['value'],
	          'email' => $src_mp['e-mail']['value']
		  );
		  $this->updateMpAttribute($my_src_mp, $mp_id, 'ethnicity');
		  $this->updateMpAttribute($my_src_mp, $mp_id, 'location');
		  $this->updateMpAttribute($my_src_mp, $mp_id, 'email');
		  if ($src_mp['kraj']['value'] != '') {
		  	  $my_src_mp['region'] = $src_mp['kraj']['value'];
		  	  $this->updateMpAttribute($my_src_mp, $mp_id, 'region');
		  }
		  if ($src_mp['www']['value'] != '') {
		  	  $my_src_mp['website'] = $src_mp['www']['value'];
		  	  $this->updateMpAttribute($my_src_mp, $mp_id, 'website');
		  }
		  //geocode
		  $geo = $this->api->read('Scraper', array('remote_resource' => 'geocode', 'address' => $src_mp['bydlisko']['value']));
			if ($geo['coordinates']['ok'])
			{
				$my_src_mp['latitude'] = $geo['coordinates']['lat'];
				$my_src_mp['longitude'] = $geo['coordinates']['lng'];
				$this->updateMpAttribute($my_src_mp, $mp_id, 'latitude');
				$this->updateMpAttribute($my_src_mp, $mp_id, 'longitude');
			}
		}

		//update/insert roles, memberships
				//prepare variable to mark (still valid) memberships
		$marked = array();

		$src_memberships_all['political group'] = $this->api->read('Scraper', array('remote_resource' => 'current_group_membership','group_kind'=>'political_group'));
		$src_memberships_all['committee'] = $this->api->read('Scraper', array('remote_resource' => 'current_group_membership','group_kind'=>'committee'));
		foreach ($src_memberships_all as $group_kind_code => $src_memberships) {
		  foreach((array) $src_memberships['membership'] as $src_membership) {
			//get mp_id
			$mp_ar = $this->api->readOne('MpAttribute',
				array(
					'name' => 'source_code',
					'value' => $src_membership['mp_id']['value'],
					'parl' => $this->parliament_code,
				)
			);
			$mp_id = $mp_ar['mp_id'];
		    //role
		    if (isset($src_membership['role']['value']))
		      $role_code = $this->updateRole($src_membership['role']['value']);
		    else $role_code = 'member';

		    //memberships
		    //parliament - everybody is a member of parliament
		    $group_id_ar = $this->api->readOne('Group',
		    	array(
		    		'name' => 'Národná rada Slovenskej republiky',
		    		'parliament' => $this->parliament_code,
		    		'term' => $this->term_id,
		    	)
		    );
		    $parl_group_id = $group_id_ar['id'];
		    $this->UpdateMembership($mp_id,$parl_group_id,'member',$constituency_id);
		    	//mark the membership
		    $marked[$mp_id][$parl_group_id]['member'] = true;

		    //group
		    $group_attr_ar = $this->api->read('GroupAttribute',
		    	array(
		    		'name' => 'source_code',
		    		'parl' => $this->parliament_code,
		    		'value' => $src_membership['src_group_id']['value']
		    	)
		    );
		    	//might be more groups with the same source code!
		    if (count($group_attr_ar) > 0) {
		      foreach ($group_attr_ar as $group_attr) {
		        $group = $this->api->readOne('Group',
			    	array(
			    		'id' => $group_attr['group_id'],
			    	)
			    );
		        if ($group['group_kind_code'] == $group_kind_code)
		        	$group_id = $group['id'];
		      }
		    }
		    $this->UpdateMembership($mp_id,$group_id,'member',null);
		    if ($role_code != 'member')
			    $this->UpdateMembership($mp_id,$group_id,$role_code,null);
		    //mark the membership
			$marked[$mp_id][$group_id][$role_code] = true;
			$marked[$mp_id][$group_id]['member'] = true;

		  }
		}
		$this->closeMemberships($marked);

		$this->log->write('Completed.');
		return array('update' => 'OK');
	}

	/**
	* close all memberships that are no longer valid
	*
	* @param $marked array of valid memberships; isset $marked[$mp_id][$group_id][$role_code]
	*/
	private function closeMemberships($marked) {
	  //get all mps with open membership in 'NRSR'
	    //get NRSR's id
	  $parl_db = $this->api->readOne('Group', array('name' => 'Národná rada Slovenskej republiky', 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
	  $parl_id = $parl_db['id'];

	    //get all mps in NRSR
	  $mps_db = $this->api->read('MpInGroup', array('group_id' => $parl_id, 'role_code' => 'member', '#datetime' => $this->date->format('Y-m-d')));

	  //loop through all mps
	  foreach((array) $mps_db as $row) {
	    //get all memberships of MP
	    $membs = $this->api->read('MpInGroup', array('mp_id' => $row['mp_id'], '#datetime' => $this->date->format('Y-m-d')));
	    //loop through all mp's memberships
	    foreach((array) $membs as $memb) {

	      //leave the membership if it is marked
	      if (isset($marked[$memb['mp_id']][$memb['group_id']][$memb['role_code']]))
	        continue;

	      //leave the membership if it is not in this parliament
	      $group_db = $this->api->readOne('Group', array('id' => $memb['group_id']));
	      if ($group_db['parliament_code'] != $this->parliament_code)
	        continue;

	      //otherwise close the membership
	      $this->log->write("Closing membership (mp_id={$memb['mp_id']}, group_id={$memb['group_id']}, role_code='{$memb['role_code']}', since={$memb['since']}).", Log::DEBUG);
	      $this->api->update('MpInGroup', array('mp_id' => $memb['mp_id'], 'group_id' => $memb['group_id'], 'role_code' => $memb['role_code'], 'since' => $memb['since']), array('until' => $this->date->format('Y-m-d')));
	    }

	  }

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
	 * \param $date \e date of the membership, object DateTime
	 */
	private function updateMembership($mp_id, $group_id, $role_code, $constituency_id)
	{
		$this->log->write("Updating membership (mp_id=$mp_id, group_id=$group_id, role_code='$role_code').", Log::DEBUG);

		// if membership is already present in database, update its details
		$memb = $this->api->readOne('MpInGroup', array('mp_id' => $mp_id, 'group_id' => $group_id, 'role_code' => $role_code, '#datetime' => $this->date->format('Y-m-d')));
		if ($memb)
		{
		  if ($constituency_id) {	//chybne, pokud $constituency_id je null
			$data = array('constituency_id' => $constituency_id);
			$this->api->update('MpInGroup', array('mp_id' => $mp_id, 'group_id' => $group_id, 'role_code' => $role_code, 'since' => $memb['since']), $data);
		  }
		}
		// if it is not present, insert it
		else
		{
			$data = array('mp_id' => $mp_id, 'group_id' => $group_id, 'role_code' => $role_code, 'constituency_id' => $constituency_id, 'since' => $this->date->format('Y-m-d'));
			$this->api->create('MpInGroup', $data);
		}
	}

	 /** Return code of the role present in database for a given male name or female name or, if it is not present, insert it.
	 *
	 * \param $src_role array of key => value pairs with properties of a scraped role
	 *
	 * \returns code of the role.
	 */
	private function updateRole($src_role)
	{
		$this->log->write("Updating role '{$src_role}'.", Log::DEBUG);

		$src_role_code = preg_replace('/[\'^"]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', strip_tags($src_role)))); // code = lowercase name without accents

		//search slovak translations of common roles for the given (fe)male name (this is the case of generic roles like 'chairman')
		$role = $this->api->readOne('RoleAttribute', array('name' => 'male_name', 'value' => $src_role, 'lang' => 'sk'));
		if ($role)
			return $role['role_code'];
		$role = $this->api->readOne('RoleAttribute', array('name' => 'female_name', 'value' => $src_role, 'lang' => 'sk'));
		if ($role)
			return $role['role_code'];

		// search roles for the given name (this is the case of parliament-specific roles like government members)
		$role = $this->api->readOne('Role', array('code' => $src_role_code));
		if ($role)
			return $role['code'];

		// if role has not been found, insert it
		$data = array('code' => $src_role_code, 'male_name' => $src_role, 'female_name' => $src_role, 'description' => "Appears in parliament {$this->parliament_code}.");
		$role_pkey = $this->api->create('Role', $data);
		return $role_pkey['code'];
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
		$value_in_db = $this->api->readOne('MpAttribute', array('mp_id' => $mp_id, 'name' => $attr_name, 'parl' => $this->parliament_code, '#datetime' => $this->update_date));
		if ($value_in_db)
			$db_value = $value_in_db['value'];

		if (!isset($src_value) && !isset($db_value) || isset($src_value) && isset($db_value) && (string)$src_value == (string)$db_value) return;

		// close the current record
		if (isset($db_value))
			$this->api->update('MpAttribute', array('mp_id' => $mp_id, 'name' => $attr_name, 'parl' => $this->parliament_code, 'since' =>  $value_in_db['since']), array('until' => $this->update_date));

		// and insert a new one
		if (isset($src_value))
			$this->api->create('MpAttribute', array('mp_id' => $mp_id, 'name' => $attr_name, 'value' => $src_value, 'parl' => $this->parliament_code, 'since' => $this->update_date));
	}


	/**
	* Update or insert MPs (from all terms)
	*
	* @params $src_mps array of source mps
	*/
	private function updateMp($src_mp) {

		$this->log->write("Updating mp '{$src_mp['priezvisko']['value']} {$src_mp['meno']['value']} (source code {$src_mp['id']['value']})'", Log::DEBUG);

		$src_code = $src_mp['id']['value'];

		// if MP is already in the database, update his data
		$src_code_in_db = $this->api->readOne('MpAttribute', array('name' => 'source_code', 'value' => $src_code, 'parl' => $this->parliament_code));
		if ($src_code_in_db)
		{
			$mp_id = $src_code_in_db['mp_id'];
			$action = self::MP_UPDATE;
		}
		// if MP is not in the database, insert him and his source code for this parliament
		else
		{
		// check for an MP in database with the same name
			$other_mp = $this->api->read('Mp', array('first_name' => $src_mp['meno']['value'], 'last_name' => $src_mp['priezvisko']['value']));
			if (count($other_mp) == 0)
				$action = self::MP_INSERT | self::MP_INSERT_SOURCE_CODE;
			else
			{
				// if there is a person in the database with the same name as the MP and conflict resolution is not set for him on input, report a warning and skip this MP
				if (!isset($this->conflict_mps[$src_code]))
				{
					$this->log->write("MP {$src_mp['meno']['value']} {$src_mp['priezvisko']['value']} already exists in database! MP (source id = {$src_code}) skipped. Rerun the update process with the parameters specifying how to resolve the conflict for this MP.", Log::WARNING);
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
							$this->log->write("Wrong parliament code and source code '$pmp_code' of an MP existing in the database specified in the \$conflict_mps parameter. MP {$src_mp['meno']['value']} {$src_mp['priezvisko']['value']} (source id/code = {$src_code}) skipped.", Log::ERROR);
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
		//if (isset($src_mp['meno']['value']))
		$first_name_ar = explode(' ',$src_mp['meno']['value']);
		$data['first_name'] = $first_name_ar[0];
		if (isset($first_name_ar[1]))
			$data['middle_names'] = $first_name_ar[1];
		//if (isset($src_mp['priezvisko']['value']))
			$data['last_name'] = $src_mp['priezvisko']['value'];
		/*if (isset($src_mp['sex']))
			$data['sex'] = $src_mp['sex'];*/
		$title_ar = explode(',',$src_mp['titul']['value']);
		if (isset($title_ar[0]))
			$data['pre_title'] = trim($title_ar[0]);
		if (isset($title_ar[1]))
			$data['post_title'] = trim($title_ar[1]);
		//birthday
		if ($src_mp['narodeny-a']['value'] != '') {
		  $d = new DateTime(str_replace(' ','',$src_mp['narodeny-a']['value']));
		  $data['born_on'] = $d->format('Y-m-d');
		}
		//date now
		$data['last_updated_on'] = date('Y-m-d H:i:s.u');

		// perform appropriate actions to update or insert MP
		if ($action & self::MP_INSERT)
		{
			if ($action & self::MP_DISAMBIGUATE)
				$data['disambiguation'] = $this->parliament_code . '/' . $src_code;
			$mp_pkey = $this->api->create('Mp', $data);
			$mp_id = $mp_pkey['id'];
			if ($action & self::MP_DISAMBIGUATE)
				$this->log->write("MP {$data['first_name']} {$data['last_name']} (id = $mp_id) inserted with automatic disambiguation. Refine his disambiguation by hand.", Log::WARNING);
		}

		if ($action & self::MP_INSERT_SOURCE_CODE)
			$this->api->create('MpAttribute', array('mp_id' => $mp_id, 'name' => 'source_code', 'value' => $src_code, 'parl' => $this->parliament_code));

		if ($action & self::MP_UPDATE)
			$this->api->update('Mp', array('id' => $mp_id), $data);

		return $mp_id;
	}


	/**
	* Update groups. If  a group is not in db, insert it
	* group_kinds are in the db already
	*
	* @param $src_groups array of scraped groups
	*/
	private function updateGroups($src_groups) {

	  foreach((array) $src_groups as $group_kind_code => $src_group_kind) {
	    $group_kind_code = str_replace('_', ' ',$group_kind_code);

		foreach ((array) $src_group_kind['group'] as $src_group) {
	      //update or insert groups
	      $this->log->write("Updating group '{$src_group['name']['value']}'", Log::DEBUG);

	      unset($group_id);

	      //find parent group id
	      if ($group_kind_code != 'parliament') {
			  $parent_group = $this->api->readOne('Group', array('name' => 'Národná rada Slovenskej republiky', 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
			  if ($parent_group)
				$parent_group_id = $parent_group['id'];
			  else
				$parent_group_id = null;
		  } else
		  		$parent_group_id = null;

		  //if group exists in db, read its id
		  $group_db = $this->api->readOne('Group', array('name' => $this->nrsrGroupName($src_group['name']['value'],$group_kind_code), 'group_kind_code' => $group_kind_code, 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
		  if ($group_db)
			$group_id = $group_db['id'];

		  //construct array of values
		  $data = array (
		  	'name' => $this->nrsrGroupName($src_group['name']['value'],$group_kind_code),
			'group_kind_code' => $group_kind_code,
			'parliament_code' => $this->parliament_code,
			'term_id' => $this->term_id,
			'last_updated_on' => $this->update_date,
			'subgroup_of' => $parent_group_id,
		  );
		  //add short_name for political groups and parliament
		  $short_name = str_replace(' – ','-',str_replace('Klub ','',$src_group['name']['value']));
		  if ($short_name == 'Poslanci, ktorí nie sú členmi poslaneckých klubov')
			$short_name = 'nezaradení';
		  if ($group_kind_code == 'political group')
		    $data['short_name'] = $short_name;
		  if ($group_kind_code == 'parliament')
		    $data['short_name'] = $src_group['short_name']['value'];

		  if (isset($group_id))
	        //update
	        $this->api->update('Group', array('id' => $group_id), $data);
	      else {
	        //insert
	        $group_pkey = $this->api->create('Group', $data);
			$group_id = $group_pkey['id'];

			// insert group's source code
			if ($group_kind_code != 'parliament')
			  $this->api->create('GroupAttribute', array('group_id' => $group_id, 'name' => 'source_code', 'value' => $src_group['id']['value'], 'parl' => $this->parliament_code));
	      }
		}
	  }

	}

	/**
	* correct known names from short names
	* e.g. nrsrGroupName(Klub MOST-HǏD, political_club) ->
	*
	* @param group_name scraped group name
	* @param group_kind_code
	*
	* @return corrected name
	*/
	private function nrsrGroupName($group_name, $group_kind_code) {
	  $short_names2names = array(
	    'HZDS' => 'Hnutie za demokratické Slovensko',
	    'SDK' => 'Slovenská demokratická koalícia',
	    'SMK' => 'Strana maďarskej koalície',
	    'SNS' => 'Slovenská národná strana',
	    'SDĽ' => 'Strana demokratickej ľavice',
	    'KDH' => 'Kresťanskodemokratické hnutie',
	    'SOP' => 'Strana občianskeho porozumenia',
	    'PSNS' => 'Pravá Slovenská národná strana',
	    'ĽS-HZDS' => 'Ľudová strana – Hnutie za demokratické Slovensko',
	    'ĽS – HZDS' => 'Ľudová strana – Hnutie za demokratické Slovensko',
	    'KSS' => 'Komunistická strana Slovenska',
	    'SDKÚ' => 'Slovenská demokratická a kresťanská únia',
	    'Smer-SD' => 'SMER - sociálna demokracia',
	    'SMER – SD' => 'SMER - sociálna demokracia',
	    'SMK-MKP' => 'Strana maďarskej koalície - Magyar Koalíció Pártja',
	    'SMK – MKP' => 'Strana maďarskej koalície - Magyar Koalíció Pártja',
	    'ANO' => 'Aliancia nového občana',
	    'SDKÚ – DS' => 'Slovenská demokratická a kresťanská únia – Demokratická strana',
	    'SDKÚ-DS' => 'Slovenská demokratická a kresťanská únia – Demokratická strana',
	    'MOST – HÍD' => 'MOST - HÍD',
	    'MOST-HÍD' => 'MOST - HÍD',
	    'SaS' => 'Sloboda a Solidarita',
	  );

	  if ($group_kind_code == 'political group') {
	    $just_name = str_replace('Klub ','',$group_name);
	    if (isset($short_names2names[$just_name]))
	      return $short_names2names[$just_name];
	    else
	      return $group_name;
	  } else
	  	return $group_name;
	}

	/**
	 * Update information about a constituency. If it is not present in database,
	 * insert it together with area
	 * There is just one constituency - Slovakia
	 *
	 * \param $region_code
	 *
	 * \returns id of the updated or inserted constituency.
	 */
	private function updateConstituency()
	{
		$this->log->write("Updating constituency 'Slovensko'.", Log::DEBUG);

		$constituency = $this->api->readOne('Constituency', array('parliament_code' => $this->parliament_code));

		if ($constituency)
		{
		  //return the constituency id
		  return $constituency['id'];
		} else {
		//insert the constituency (and its area) and return constituency id
		  $data = array(
		    'name' => 'Slovensko',
		    'short_name' => 'Slovensko',
		    'description' => 'Slovenská republika',
		    'parliament_code' => $this->parliament_code,
		  );
		  $constituency_pkey = $this->api->create('Constituency', $data);

		  //area
		  $data = array(
		    'constituency_id' => $constituency_pkey['id'],
		    'country' => 'Slovenská republika',

		  );
		  $this->api->create('Area', $data);

		  return $constituency_pkey['id'];
		}
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
			$this->log->write("The term to update parliament {$this->parliament_code} for does not exist, check " . API_DOMAIN . "/data/Scrape?parliament={$this->parliament_code}&remote_resource=term_list", Log::FATAL_ERROR, 400);

		// if the term is present in the database, update it and get its id
		$src_code_in_db = $this->api->readOne('TermAttribute', array('name' => 'source_code', 'value' => $term_src_code, 'parl' => $this->parliament_code));
		if ($src_code_in_db)
		{
			$term_id = $src_code_in_db['term_id'];
			$data = array('name' => $term_to_update['name'], 'since' => $term_to_update['since']);
			if (isset($term_to_update['short_name']))
				$data['short_name'] = $term_to_update['short_name'];
			if (isset($term_to_update['until']))
				$data['until'] = $term_to_update['until'];
			$this->api->update('Term', array('id' => $term_id), $data);
		}
		else
		{
			// if term is not in the database, insert it and get its id
			$data = array('name' => $term_to_update['name'], 'country_code' => 'sk', 'parliament_kind_code' => 'national', 'since' => $term_to_update['since']);
			if (isset($term_to_update['name']))
				$data['short_name'] = $term_to_update['name'];
			if (isset($term_to_update['until']))
				$data['until'] = $term_to_update['until'];
			$term_pkey = $this->api->create('Term', $data);
			$term_id = $term_pkey['id'];

			// insert term's source code as an attribute
			$this->api->create('TermAttribute', array('term_id' => $term_id, 'name' => 'source_code', 'value' => $term_src_code, 'parl' => $this->parliament_code));
		}

		// set the effective date which the update process actually runs to
		$this->update_date = (isset($params['term'])) ? $this->term_since : 'now';

		return $term_id;
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
				'name' => 'Národná rada',
				'short_name' => 'NRSR',
				'description' => 'Parlament Slovenské republiky.',
				'parliament_kind_code' => 'national',
				'country_code' => 'sk',
				'time_zone' => 'Europe/Bratislava'
			));

			// english translation
			$this->api->create('ParliamentAttribute', array(
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name' => 'name', 'value' => 'National Council'),
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name' => 'short_name', 'value' => 'NC SR'),
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name' => 'description', 'value' => 'Parliament of Slovak republic.')
			));

			// a function to show appropriate info about representatives of this parliament for use by WriteToThem application
			$this->api->create('ParliamentAttribute', array(array('parliament_code' => $this->parliament_code, 'name' => 'wtt_repinfo_function', 'value' => 'wtt_repinfo_politgroup_location')));
		}

		// update the timestamp the parliament has been last updated on
		$this->api->update('Parliament', array('code' => $this->parliament_code), array('last_updated_on' => 'now'));

		return $this->parliament_code;
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
