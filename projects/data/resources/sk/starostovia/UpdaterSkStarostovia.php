<?php

/**
 * This class updates data in the database for a given term of office to the state scraped - mayors, SK
 */
class UpdaterSkStarostovia
{
	/// API client reference used for all API calls
	private $api;

	/// time and time zone used when storing dates into 'timestamp with time zone' fields
	const TIME_ZONE = 'Europe/Prague';
	const NOON = ' 12:00 Europe/Prague';

	/// properties of the updated parliaments
	private $parliaments;

	/// effective date which the update process actually runs to
	private $update_date;

	/// array of MPs in this parliament that have the same name as an already existing MP in the database
	private $conflict_mps;

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
		$this->api = new ApiDirect('data');
		$this->log = new Log(API_LOGS_DIR . '/update/sk/starostovia/' . strftime('%Y-%m-%d %H-%M-%S') . '.log', 'w');
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
		$this->term_id = $this->updateTermsAndParliament();
		//conflict mps
		$this->conflict_mps = $this->parseConflictMps($params);
		
		//constituencies
		$towns = $this->api->read('Scraper',array('parliament' => 'sk/starostovia', 'remote_resource' => 'mp_parliament_list'));
		$this->updateConstituencies($towns);
		
		//areas
		if ($params['area']) {
		  $areas = $this->api->read('Scraper',array('parliament' => 'sk/starostovia', 'remote_resource' => 'area_list'));
		  $this->updateAreas($areas);
		}
		
		//mps
		$this->marked = array();
		$this->updateMpsAndGroups($towns);
		
		
		
		$this->log->write('Everything done.');
		
	}
	
	/**
	* Insert or update MPs (and groups)
	*
	* \param towns array of parameters
	*/ 
	private function updateMpsAndGroups($towns) {
	  $this->log->write('Updating MPs.');
	  
	  foreach ($towns['list'] as $mp) {
	    $mp_id = self::updateMp($mp);
	    //$this->marked[$mp_id] == true;
	    if ($mp_id)
	      self::updateMembership($mp_id,$mp);
	  
	  }
	  
	  //close unmarked memberships
	  self::closeMemberships();
	}
	/**
	* Update or insert membership
	*
	* \param $mp_id
	* \param $town scraped info about one town
	*/
	private function updateMembership($mp_id,$town) {
	  $constit = $this->api->readOne('Constituency',array('parliament_code' => 'sk/starostovia', 'name' => "{$town->Name} ({$town->Id})"));

	  $membership = array(
	      'mp_id' => $mp_id,
	      'group_id' => $this->group_id, 
	      'role_code' => 'member',
	      'constituency_id' => $constit['id'],
	  );
	  //exists?
	  $m0 = $membership;
	  $m0['_datetime'] = 'now';
	  $membership_db = $this->api->readOne('MpInGroup', $m0);
	  
	  if(!$membership_db) { //insert
	    $membership['since'] = 'now';
	    $this->api->create('MpInGroup',$membership);
	    $this->log->write('Inserted a new membership: ' . $mp_id.','.$this->group_id);
	  }
	  $this->marked[$mp_id][$this->group_id]['member'] = true;
	}
	
	/**
	* close all memberships that are no longer valid
	*/
	private function closeMemberships() {
	  //get all mps with open membership in 'Senát'
	    //get group's id
	  $parl_id = $this->group_id;

	    //get all mps in the group
	  $mps_db = $this->api->read('MpInGroup', array('group_id' => $parl_id, 'role_code' => 'member', '_datetime' => 'now'));

	  //loop through all mps
	  foreach((array) $mps_db as $row) {
	    //get all memberships of MP
	    $membs = $this->api->read('MpInGroup', array('mp_id' => $row['mp_id'], '_datetime' => 'now'));
	    //loop through all mp's memberships
	    foreach((array) $membs as $memb) {

	      //leave the membership if it is marked
	      if (isset($this->marked[$memb['mp_id']][$memb['group_id']][$memb['role_code']]))
	        continue;

	      //leave the membership if it is not in this parliament
	      $group_db = $this->api->readOne('Group', array('id' => $memb['group_id']));
	      if ($group_db['parliament_code'] != 'sk/starostovia')
	        continue;

	      //otherwise close the membership
	      $this->log->write("Closing membership (mp_id={$memb['mp_id']}, group_id={$memb['group_id']}, role_code='{$memb['role_code']}', since={$memb['since']}).", Log::DEBUG);
	      $this->api->update('MpInGroup', array('mp_id' => $memb['mp_id'], 'group_id' => $memb['group_id'], 'role_code' => $memb['role_code'], 'since' => $memb['since']), array('until' => 'now'));
	    }

	  }

	}
	
	/**
	 * Update personal information about an MP. If MP is not present in database, insert him.
	 *
	 * \param $src_mp array of key => value pairs with properties of a scraped MP
	 *
	 * \returns id of the updated or inserted MP.
	 */  
	private function updateMp($src_mp) {
	  //errors - for some of the same town names the same names of mayors

	  $errors = array(502065,528137,504254,501069,511269,527165,509604,524239,542806,525596,507881,
527254,512222,518361,519189,523445,512265,518123,518522,521400,509671,508624,
514004,581747,557714,510513,517658,528773,507393,517682,518506,525855,523593,528455,
519430,528811,581704,528471,510572,524701,512435,515167,510815,517046,522741,558087,
519545,507369,509876,515302,521884,517879,517143,514306,505340,512508,510955,515345,
526118,521906,508969,557765,504769,529061,515485,520721,527777,504793,521949,502707,
515507,557820,518727,516368,523054,555746,516511,555509,524115,526631,523348);

	
 	  if (in_array($src_mp->Id,$errors)) return null;
	
	  $names = explode(' ',$src_mp->Mayor);
	  $src_code = implode('-',array($src_mp->Id,$names[0],end($names))); //524158-František-Štofko

	  if (end($names) != '') {
		  // if MP is already in the database, update his data
			$mps_attr_db = $this->api->read('MpAttribute',array('name' => 'source_code', 'value' => $src_code, 'parl' => 'sk/starostovia'));
			 $ok = false;
			 if ($mps_attr_db) {
			  foreach ($mps_attr_db as $mp_attr_db) {
	  	        $mp_db = $this->api->readOne('Mp',array('id' => $mp_attr_db['mp_id'], 'last_name' => end($names), 'first_name' => $names[0]));
	  	        if ($mp_db) {
	  	          $ok = true;
	  	          $mp_id = $mp_attr_db['mp_id'];
	  	          $action = self::MP_UPDATE;
	  	        }
	  	      }
			 }
	    
			// if MP is not in the database, insert him and his source code for this parliament
			if (!$ok) {
			  // check for an MP in database with the same name
			  $other_mp = $this->api->readOne('Mp', array('first_name' => $names[0], 'last_name' => end($names)));
			  if (!$other_mp)
					$action = self::MP_INSERT | self::MP_INSERT_SOURCE_CODE;
			  else {
				// if there is a person in the database with the same name as the MP and conflict resolution is not set for him on input, report a warning and skip this MP
				if (!isset($this->conflict_mps[$src_code])) {
				    $last_name = end($names);
					$this->log->write("MP {$names[0]} {$last_name} already exists in database! MP (source id = {$src_code}) skipped. Rerun the update process with the parameters specifying how to resolve the conflict for this MP.", Log::WARNING);
					return null;
				} else {
				  // if conflict_mps indicates that this MP is already in the database, update his data and insert his source code for this parliament
					if (!empty($this->conflict_mps[$src_code])) {
				
						$pmp_ar = explode('/',$this->conflict_mps[$src_code]);
						$mp_src_code = array_pop($pmp_ar);
						$parliament_code = implode('/',$pmp_ar);
						
						$mp_id_attr = $this->api->readOne('MpAttribute', array('name' => 'source_code', 'value' => $mp_src_code, 'parl' => $parliament_code));
						if ($mp_id_attr)
							$mp_id = $mp_id_attr['mp_id'];
						else
						{
							$this->log->write("Wrong parliament code and source code '$this->conflict_mps[$src_code]' of an MP existing in the database specified in the \$conflict_mps parameter. MP {$src_mp->Mayor} (source id/code = {$src_code}) skipped.", Log::ERROR);
							return null;
						}
						$action = self::MP_UPDATE;
						if ($parliament_code != 'sk/starostovia')
							$action |= self::MP_INSERT_SOURCE_CODE;
					}
					else
						// if null is given instead of an existing MP in database, insert MP as a new one, insert his source code for this parliament and generate a value for his disambigation column
						$action = self::MP_INSERT | self::MP_INSERT_SOURCE_CODE | self::MP_DISAMBIGUATE;
			  }
			}
			
			
			
			
		
			$data['first_name'] = $names[0];
			$data['last_name'] = end($names);
			$data['last_updated_on'] = 'now';
			// perform appropriate actions to update or insert MP
			if ($action & self::MP_INSERT)
			{
				if ($action & self::MP_DISAMBIGUATE)
				  if (!isset($data['disambiguation']) || empty($data['disambiguation']))
					$data['disambiguation'] = $src_code;
				$mp_pkey = $this->api->create('Mp', $data);

				$mp_id = $mp_pkey['id'];

				if ($action & self::MP_DISAMBIGUATE && $data['disambiguation'] == $src_code)
					$this->log->write("MP {$data['first_name']} {$data['last_name']} (id = $mp_id) inserted with automatic disambiguation. Refine his disambiguation by hand.", Log::WARNING);
			}
			
			if ($action & self::MP_INSERT_SOURCE_CODE)
				$this->api->create('MpAttribute', array('mp_id' => $mp_id, 'name' => 'source_code', 'value' => $src_code, 'parl' => 'sk/starostovia'));

			if ($action & self::MP_UPDATE)
				$this->api->update('Mp', array('id' => $mp_id), $data);

		}
		return $mp_id;
			
	} else return null;
	
	}
	
	/**
	* Insert Areas
	*
	* \param areas array of areas
	*/
	private function updateAreas($areas) {
	  $this->log->write('Updating areas.');
	  foreach ($areas['list'] as $area) {
	    $constit_db = $this->api->readOne('Constituency',array('parliament_code' => 'sk/starostovia', 'name' => "{$area->name} ({$area->id})"));
	    $area_db = $this->api->readOne('Area',array('constituency_id' => $constit_db['id']));
	    
	    if (!$area_db) {
 
	      //seems ok
		  $ar = array(
		    'constituency_id' => $constit_db['id'],
		    'country' => 'Slovenská republika',
		  );
		  if ($area->{'administrative_area_level_1-long_name'} != '')
		    $ar['administrative_area_level_1'] = $area->{'administrative_area_level_1-long_name'};
		  if ($area->{'administrative_area_level_2-long_name'} != '')
		    $ar['administrative_area_level_2'] = $area->{'administrative_area_level_2-long_name'};
		  if ($area->{'administrative_area_level_3-long_name'} != '')
		    $ar['administrative_area_level_3'] = $area->{'administrative_area_level_3-long_name'};
		  if ($area->{'locality-long_name'} != '')
		    $ar['locality'] = $area->{'locality-long_name'};
		  if ($area->{'sublocality-long_name'} != '')
		    $ar['sublocality'] = $area->{'sublocality-long_name'};
		  if ($area->{'neighborhood-long_name'} != '')
		    $ar['neighborhood'] = $area->{'neighborhood-long_name'};
		  if ($area->{'route-long_name'} != '')
		    $ar['route'] = $area->{'route-long_name'};
		  if ($area->{'street_number-long_name'} != '')
		    $ar['street_number'] = $area->{'street_number-long_name'};
	      
	      //there are several errors:
	      if (($area->{'administrative_area_level_2-long_name'} == '') or ($area->{'country-short_name'} == 'SK')) { //problems:
	        switch ($area->id) {
	          case '513474':
	            $ar['administrative_area_level_2'] = 'Považská Bystrica';
	            break;
	          case '523551':
	            $ar['administrative_area_level_2'] = 'Poprad';
	            break;
	          case '518638':
	            $ar['administrative_area_level_2'] = 'Humenné';
	            break;
	          case '500267':
	             $ar['administrative_area_level_1'] = 'Trnavský kraj';
	             $ar['administrative_area_level_2'] = 'Malacky';
	            break;
	          case '505811':
	            $ar['administrative_area_level_1'] = 'Trenčiansky kraj';
	            $ar['administrative_area_level_2'] = 'Bánovce nad Bebravou';
	            $ar['locality'] = 'Žitná';
	            break;
	          case '518581':
	            $ar['administrative_area_level_2'] = 'Zvolen';
	            break;
	          case '516601':
	            $ar['administrative_area_level_1'] = 'Banskobystrický kraj';
	            $ar['administrative_area_level_2'] = 'Banská Štiavnica';
	            $ar['locality'] = 'Baďan';
	            break;
	          case '529401':
	            $ar['administrative_area_level_1'] = 'Bratislavský kraj';
	            $ar['administrative_area_level_2'] = 'Bratislava';
	            $ar['locality'] = 'Bratislava';
	            $ar['sublocality'] = 'Devín';
	            break;
	          case '504025':
	            $ar['administrative_area_level_1'] = 'Nitriansky kraj';
	            $ar['administrative_area_level_2'] = 'Šaľa';
	            $ar['locality'] = 'Šaľa';
	            break;
	          case '519731':
	            $ar['administrative_area_level_1'] = 'Prešovský kraj';
	            $ar['administrative_area_level_2'] = 'Bardejov';
	            $ar['locality'] = 'Porúbka';
	            break;
	          case '506061':
	            $ar['administrative_area_level_1'] = 'Trenčiansky kraj';
	            $ar['administrative_area_level_2'] = 'Nové Mesto nad Váhom';
	            $ar['locality'] = 'Hrachovište';
	            break;
	          case '515507':
	            $ar['administrative_area_level_1'] = 'Banskobystrický kraj';
	            $ar['administrative_area_level_2'] = 'Revúca';
	            $ar['locality'] = 'Rybník';
	            break;
	          case '557820':
	            $ar['administrative_area_level_1'] = 'Banskobystrický kraj';
	            $ar['administrative_area_level_2'] = 'Revúca';
	            $ar['locality'] = 'Sása';
	            break;

	        }
	      }
	      
	      
	      $this->api->create('Area',$ar);
	      $this->log->write('Inserting a new area: ' . $ar['locality']);
	    }
	  }
	}
	
	/**
	* Insert Constituencies
	*
	* \param towns array of towns
	*/
	private function updateConstituencies($towns) {
	  $this->log->write('Updating constituencies.');
	  foreach ($towns['list'] as $town) {
	    $town_db = $this->api->readOne('Constituency',array('parliament_code' => 'sk/starostovia', 'name' => "{$town->Name} ({$town->Id})"));
	    if (!$town_db) {
	      $ar = array(
	        'name' => "{$town->Name} ({$town->Id})",
	        'short_name' => $town->Name,
	        'parliament_code' => 'sk/starostovia',
	        'since' => '-infinity',
	        'until' => 'infinity'
	      );
	      $this->api->create('Constituency',$ar);
	      $this->log->write('Inserting a new constituency ' . $ar['name']);
	    }  
	  }
	}

	
	/**
	 * Update a term, the parliament, if is not present in database yet, insert it.
	 *
	 * @return current term
	 */
	private function updateTermsAndParliament()
	{
	  //last item in this array MUST be current term
	  $terms = array(
	    array( 'name' => '2010 - 2014', 'since' => '2010-11-28'),
	  );
	  
	  foreach ($terms as $term) {
	    $term['country_code'] = 'sk';
	    $term['parliament_kind_code'] = 'mayors';
	    $term_db = $this->api->readOne('Term',array('name' => $term['name'], 'since' => $term['since']));
	    if ($term_db) { //update
	      $term_id = $term_db['id'];
	      $this->log->write('Updating term: ' . $term['name']);
	      $this->api->update('Term',array('name' => $term['name'], 'since' => $term['since']),$term);
	    } else { //insert
	      $this->log->write('Inserting new term: ' . $term['name']);
	      $term_id = $this->api->create('Term',$term);
	    }
	  }
	  
	  //parliament
	  $parliament = array(
	    'code' => 'sk/starostovia',
	    'name' => 'Starostovia obcí',
	    'short_name' => 'Starostovia',
	    'description' => 'Starostovia obcí Slovenska',
	    'parliament_kind_code' => 'mayors',
	    'country_code' => 'sk',
	    'time_zone' => 'Europe/Prague',
	    'weight' => '4',
	    'address_representatives_function' => 'address_representatives_national_local',
	    'last_updated_on' => 'now',
	  );
	  $parliament_db = $this->api->readOne('Parliament',array('code' => $parliament['code']));
	  if ($parliament_db) { //update
	    $this->log->write('Updating parliament: ' . $parliament['code']);
	    $this->api->update('Parliament',array('code' => $parliament['code']),$parliament);
	  } else { //insert
	    $this->log->write('Inserting new parliament: ' . $parliament['code']);
	    $this->api->create('Parliament',$parliament);
	  }

	  //parliament as group
	  $group_constr = array(
	    'name' => 'Starostovia obcí',
	    'parliament_code' => 'sk/starostovia',
	    'group_kind_code' => 'parliament',
	    'term_id' => $term_id,
	  );
	  $group = $group_constr;
	  $group['short_name'] = 'Starostovia';
	  
	  $group_db = $this->api->readOne('Group',$group_constr);
	  if ($group_db) { //update
	    $this->log->write('Updating group: ' . $group['name']);
	    $this->api->update('Group',$group_constr,$group);
	    $this->group_id = $group_db['id'];
	  } else { //insert
	    $this->log->write('Inserting a new group: ' . $group['name']);
	    $this->group_id = $this->api->create('Group',$group);
	  }
	  
	  
	  //return term
	  return $term_id;
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
?>
