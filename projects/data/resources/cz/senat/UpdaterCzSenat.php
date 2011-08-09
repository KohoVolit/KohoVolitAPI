<?php

/**
 * This class updates data in the database for a given term of office to the state scraped from Parliament of the Czech republic - Senate official website www.senat.cz.
 */
class UpdaterCzSenat
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
		$this->ac = new ApiDirect('data', array('parliament' => $this->parliament_code));
		$this->log = new Log(API_LOGS_DIR . '/update/' . $this->parliament_code . '/' . strftime('%Y-%m-%d %H-%M-%S') . '.log', 'w');
		$this->log->setMinLogLevel(Log::DEBUG);

		//convert $param['date'] into DateTime object, default = today
		if (isset($params['date']))
	  		$this->date = new DateTime($params['date']);
		else
	  		$this->date = new DateTime();
		//$this->update_date = $this->date->format('Y-m-d H:i:s');
	}

	/**
	 * Main method called from API function Update - it scrapes data and updates the database.
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the update process.
	 *
	 * Parameter <em>$param['date']</em> is date in Czech format ('1.1.2001', '20.2.1976'). If ommitted, current date is assumed.
	 *
	 * Parameter <em>$param['area']</em> If set constituency areas are updated as well.
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

		//update parliament and term
		$this->updateParliament();
		$this->term_id = $this->updateTerm($params);
		$this->conflict_mps = $this->parseConflictMps($params);

		// read list of all MPs in the term of office to update data for
		$src_mps = $this->api->read('Scraper', array('remote_resource' => 'mp_list'));
		$src_mps = $src_mps['mp_list'];

		//prepare variable to mark (still valid) memberships
		$marked = array();

		//update group_kinds and groups
		$src_groups = $this->api->read('Scraper', array('remote_resource' => 'group_list'));
		$this->updateGroups($src_groups);


		// update (or insert) all MPs in the list
		foreach((array) $src_mps as $src_mp)
		{
			// scrape details of the MP
			$src_mp = $this->api->read('Scraper', array('remote_resource' => 'mp', 'id' => $src_mp['source_code']));

			// update the MP personal details
			$mp_id = $this->updateMp($src_mp['mp']);
			if (is_null($mp_id)) continue;		// skip conflicting MPs with no given conflict resolution

			// update other MP attributes and offices, mps
			$this->updateMpAttribute($src_mp['mp'], $mp_id, 'email', ', ');
			$this->updateMpAttribute($src_mp['mp'], $mp_id, 'website');
			$this->updateMpAttribute($src_mp['mp'], $mp_id, 'phone', ', ');
			$this->updateMpAttribute($src_mp['mp'], $mp_id, 'assistant', ', ');
			//$this->updateMpAttribute($src_mp['mp'], $mp_id, 'office');
			$this->updateOffice($src_mp['mp'], $mp_id, 'office');
			$this->updateMpImage($src_mp['mp'], $mp_id);

			// update (or insert) constituency of the MP
			$constituency_id = $this->updateConstituency($src_mp['mp']['region_code']);

			//every senator is member of senate
			$src_groups = $src_mp['mp']['group'];
			array_unshift($src_groups, array (
				'name' => 'Senát',
				'name_en' => 'Senate',
				'kind' => 'parliament',
				'role' => 'člen',
				'role_en' => 'member',
				)
			);

			//update (or insert) memberships

			foreach ((array) $src_groups as $src_group) {
			  //get group_id
			  $group_db = $this->api->readOne('Group', array('name_' => $src_group['name'], 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
			  if ($group_db)
			    $group_id = $group_db['id'];
			  else {
			    $this->log->write("Group {$src_group['name']} (source id: {$src_group['group_id']} is not in db! Membership of MP ({$mp_id}) skipped. Check probably ScrapeCzSenat.php, function scrapeGroupList(), line with: group_kinds = array('S', 'V', 'M' ...; might have missing some of the 'S', 'V', 'M', ...", Log::WARNING);
			    continue;
			  }

			  //update (or insert) role
			  $role_code = $this->updateRole(array('male_name' => $src_group['role_en'], 'female_name' => $src_group['role_en']));

			  //check (or insert) membership
			    //for parliament add constituency_id
			  if (isset($src_group['kind']) and ($role_code == 'member'))
			    $const_id = $constituency_id;
			  else
			    $const_id = null;

			  $this->updateMembership($mp_id, $group_id, $role_code, $const_id);
			  
			  //every person with other role than 'member' (e.g. 'chairman' must be also a 'member')
			  if ($role_code != 'member') {
			    //Senát is already updated with constituency
			    if ($src_group['name'] != 'Senát')
			      $this->updateMembership($mp_id, $group_id, 'member', null);
			  }

			  //mark the membership
			  $marked[$mp_id][$group_id][$role_code] = true;
			  $marked[$mp_id][$group_id]['member'] = true;


			}
			//die();
		}
		$this->closeMemberships($marked);
		$this->closeOffice($marked);
		
		//update areas only if param 'area' is set (approx. 2 hours of updating)
		if (isset($params['area']))
		  $this->updateAreas();

		$this->log->write('Completed.');
		return array('update' => 'OK');
	}

	/**
	* update areas (of constituencies)
	*
	* kraj, okres, obec ~ administrative_area_1,administrative_area_2,locality
	* Praha: Praha 3 ~ sublocality; (Praha-)Bubeneč~neighborhood
	* Plzeň: Plzeň 4 ~ sublocality; (Plzeň 10-)Lhota ~ sublocality !
	* Ostrava: Slezská Ostrava ~ sublocality
	* Brno: (Brno-)Kohoutovice ~ sublocality
	*
	* warnings:
	* Ostrava: Moravská Ostrava a Přívoz, ... (split it)
	* Plzeň: Plzeň 10-Lhota (split it)
	* Brno: Brno-jih (sublocality, small letter 'jih'), (Brno-)Řečkovice a Morká Hora, ... (split it)
	* Praha: (Praha 10-)Hrdlořezy,Malešice (split it)
	*
	* errors:
	* Praha: Praha 5 (bez části k.ú.Malá Strana) + Praha 5-Malá Strana have the same constituency (wrong!), ...
	*
	*/
	private function updateAreas() {
	  $src_regions_0 = $this->api->read('Scraper', array('remote_resource' => 'region'));
	  //kraje
	  foreach((array) $src_regions_0['regions']['region']['kraj']['region'] as $kraj) {
	    $src_regions_1 = $this->api->read('Scraper', array('remote_resource' => 'region', 'kraj' => $kraj['number']));
	    //okresy
	    foreach((array) $src_regions_1['regions']['region']['okres']['region'] as $okres) {
	      $src_regions_2 = $this->api->read('Scraper', array('remote_resource' => 'region', 'kraj'=>$kraj['number'], 'okres' => $okres['number']));
	      //obce
	      foreach((array) $src_regions_2['regions']['region']['obec']['region'] as $obec) {
	        $src_regions_3 = $this->api->read('Scraper', array('remote_resource' => 'region', $kraj['number'], 'okres' => $okres['number'], 'obec' => $obec['number']));

	        //set data (kraj,okres,obec)
	        $data = array(
	          'country' => 'Česká republika',
	          'administrative_area_level_1' => $kraj['name'],
	          'administrative_area_level_2' => $okres['name'],
	          'locality' => $obec['name'],
	        );
	        //Praha, Ostrava, Brno, Plzeň
	        if (isset($src_regions_3['regions']['region']['uzemi'])) {
	          if (!isset($src_regions_3['regions']['region']['uzemi']['region']))
	            $this->log->write('Something is wrong with uzemi. Notice: Undefined index: region - ' . print_r($src_regions_3['regions']['region'],1), Log::WARNING);
	          else
	          foreach ((array) $src_regions_3['regions']['region']['uzemi']['region'] as $uzemi) {
	            $src_regions_4 = $this->api->read('Scraper', array('remote_resource' => 'region', $kraj['number'], 'okres' => $okres['number'], 'obec' => $obec['number'], 'uzemi' => $uzemi['number']));
	            $constituency = $src_regions_4['regions']['constituency'];
	            //treat every city differently
	            switch($obec['name']) {

		          case 'Brno':
	                $subs = explode('-', $uzemi['name']);
	                $subs2 = explode (' a ', $subs[1]);
	                //correct for 'Brno-jih'
	                if ($subs[1] == mb_convert_case($subs[1], MB_CASE_LOWER, 'UTF-8'))
	                  $subs2 = array($subs);

	                foreach ((array) $subs2 as $sub) {
	                  $data['sublocality'] = $sub;
	                  $this->updateArea($data, $constituency);
	                }
	                break;

		          case 'Plzeň':
	                $subs = explode('-', $uzemi['name']);
	                foreach ((array) $subs as $sub) {
	                  $data['sublocality'] = $sub;
	                  $this->updateArea($data, $constituency);
	                }
	                break;

	              case 'Ostrava':
	                $subs = explode(' a ', $uzemi['name']);
	                foreach ((array) $subs as $sub) {
	                  $data['sublocality'] = $sub;
	                  $this->updateArea($data, $constituency);
	                }
	                break;

	              case 'Praha';

	                $subs = explode('-', $uzemi['name']); //e.g., Praha 2-Nové Město = sublocality-neighborhood

	                //strip part in (), e.g.Praha 10(bez části k.ú.Vinohrady)
	                $subs2 = explode('(', $subs[0]);
	                $data['sublocality'] = $subs2[0];

	                if (isset($subs[1])) { //if isset neighborhood
	                  $subs3 = explode(',', $subs[1]); //Hrdlořezy,Malešice
	                  foreach ((array) $subs3 as $sub3) {
	                    $data['neighborhood'] = $sub3;
	                    //correct errors
	                    if ($corr_const = $this->correctPrahaAreaErrors($data['sublocality'], $data['neighborhood']))
	                      $constituency = $corr_const;
	                    $this->updateArea($data, $constituency);
	                  }

	                } else {
	                  unset($data['neighborhood']);
	                  //correct  parts with (), e.g.Praha 10(bez části k.ú.Vinohrady) -> (Praha 10, ~Vinohrady)
	                  if (isset($subs2[1])) { //if it is such a case, e.g.Praha 10(bez části k.ú.Vinohrady)
	                    $data['neighborhood'] = $this->correctPrahaExceptErrors($uzemi['name']);
	                  }
	                  //correct errors
	                  if ($corr_const = $this->correctPrahaAreaErrors($data['sublocality']))
	                      $constituency = $corr_const;
	                  $this->updateArea($data, $constituency);
	                }
					break;
	            }
	          }
	        } else { //not Praha, Ostrava, Brno, Plzeň
	          $constituency = $src_regions_3['regions']['constituency'];
	          $this->updateArea($data, $constituency);
	        }
	      }


	    }
	  }

	}
	
	/**
	* correct exception in Praha regions into db format
	*
	* example: correctPrahaExceptErrors(Praha6(bez Bubenče,Střeš.,Hrad.,Sedlce)) ->
	*	 '~Bubeneč,Střešovice,Hradčany,Sedlec'
	*
	* @param $name
	*
	* @result $neighborhood
	*/
	private function correctPrahaExceptErrors($name) {
	  switch ($name) {
	    case 'Praha 10(bez části k.ú.Vinohrady)':
	      return '~Vinohrady';
	    case 'Praha 2(bez Nového Města a Vyšehradu)':
	      return '~Nové Město,Vyšehrad';
	    case 'Praha 4(bez Hodkoviček,Lhotky)':
	      return '~Hodkovičky,Lhotka';
	    case 'Praha 5(bez části k.ú.Malá Strana)':
	      return '~Malá Strana';
	    case 'Praha 6(bez Bubenče,Střeš.,Hrad.,Sedlce)':
	      return '~Bubeneč,Střešovice,Hradčany,Sedlec';
	    case 'Praha 9(bez Hrdlořez,Hloubětína,Malešic)':
	      return '~Hrdlořezy,Hloubětín,Malešice';
	    default:
	      //issue warning
	      $this->log->write("No correction rule for {$name} in correctPrahaExceptErrors()", Log::WARNING);
	  }
	}

	/**
	* treat errors in Praha area
	*
	* @param $sublocality
	* @param $neighborhood
	*
	* @return false if no error; array(array('number' => constituency_number)) otherwise
	*/
	private function correctPrahaAreaErrors($sublocality, $neighborhood = false) {
	  switch ($sublocality) {
	    case 'Praha 10':
	      if ($neighborhood) $const = 26;
	      else $const = 22;
	      break;
	    case 'Praha 2':
	      if ($neighborhood) $const = 27;
	      else $const = 26;
	      break;
	    case 'Praha 4':
	      if ($neighborhood) $const = 17;
	      else $const = 20;
	      break;
	    case 'Praha 5':
	      if ($neighborhood) $const = 27;
	      else $const = 21;
	      break;
	    case 'Praha 6':
	      if ($neighborhood) $const = 27;
	      else $const = 25;
	      break;
	    case 'Praha 9':
	      if ($neighborhood) $const = 26;
	      else $const = 24;
	      break;
	  }
	  if (isset($const))
	    return array(array('number' => $const));
	  else
	    return false;
	}
	/**
	* update or insert area
	*
	* @param $data array of (administrative_area_level_1, administrative_area_level_2,...)
	* @param $constituency array
	*/
	private function updateArea($data, $constituency) {
	  //constituency number
	  foreach((array) $constituency as $c)
	    $const_number = $c['number'];
	  //get constituency id from db
	  $const_db = $this->api->readOne('Constituency', array('short_name' => $const_number, 'parliament_code' => $this->parliament_code));
	  $data['constituency_id'] = $const_db['id'];

	  //full area:
	  $data_full = array(
	    'constituency_id' => '*',
	    'country' => '*',
	    'administrative_area_level_1' => '*',
	    'administrative_area_level_2' => '*',
	    'administrative_area_level_3' => '*',
	    'locality' => '*',
	    'sublocality' => '*',
	    'neighborhood' => '*',
	    'route' => '*',
	    'street_number' => '*',
	  );
	  foreach((array) $data as $key => $d) {
	    $data_full[$key] = $d;
	  }
	  //get area from db
	  $area_db = $this->api->read('Area', $data);
	  //insert area if not in db
	  if (count($area_db) == 0) {
	    $this->api->create('Area', $data_full);
	    $this->log->write("Inserted new area: {$data_full['administrative_area_level_1']}, {$data_full['administrative_area_level_2']}, {$data_full['locality']}, {$data_full['sublocality']}, {$data_full['neighborhood']}", Log::DEBUG);
	  }

	}

	/**
	* close all offices of MPs no longer in parliament
	*
	* @param $marker array of valid memberships; if one membership for a MP exists, do not close office
	*/
	private function closeOffice($marked) {
	  //get Senát's id
	  $parl_db = $this->api->readOne('Group', array('name_' => 'Senát', 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
	  $parl_id = $parl_db['id'];

	    //get all mps in Senát
	  $mps_db = $this->api->read('MpInGroup', array('group_id' => $parl_id, 'role_code' => 'member', '#datetime' => $this->date->format('Y-m-d')));

	  //loop through all mps
	  foreach((array) $mps_db as $row) {
	    if (!isset($marked[$row['mp_id']])) { //mp no longer in parliament
	      //get his office(s)
	      $offices = $this->api->read('Office', array('mp_id' => $row['mp_id'], 'parliament_code' => $this->parliament_code, '#datetime' => $this->date->format('Y-m-d')));
	      //close them
	      foreach((array) $offices as $office) {
	        $this->log->write("Closing office (mp_id={$row['mp_id']}, address={$office['address']}, since={$row['since']}).", Log::DEBUG);
	      $this->api->update('Office', array('mp_id' => $row['mp_id'], 'address' => $office['address'], 'since' => $office['since'], 'parliament_code' => $this->parliament_code), array('until' => $this->date->format('Y-m-d')));
	      }
	    }

	  }
	}

	/**
	* close all memberships that are no longer valid
	*
	* @param $marked array of valid memberships; isset $marked[$mp_id][$group_id][$role_code]
	*/
	private function closeMemberships($marked) {
	  //get all mps with open membership in 'Senát'
	    //get Senát's id
	  $parl_db = $this->api->readOne('Group', array('name_' => 'Senát', 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
	  $parl_id = $parl_db['id'];

	    //get all mps in Senát
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

		$src_role_code = preg_replace('/[\'^"]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', strip_tags($src_role['male_name'])))); // code = lowercase male name without accents

		// search czech translations of common roles for the given male name (this is the case of generic roles like 'chairman')
		/*$role = $this->api->readOne('RoleAttribute', array('name_' => 'male_name', 'value_' => $src_role['male_name'], 'lang' => 'cs'));
		if ($role)
			return $role['role_code'];
*/
		// search roles for the given male name (this is the case of parliament-specific roles like government members)
		$role = $this->api->readOne('Role', array('code' => $src_role_code));
		if ($role)
			return $role['code'];

		// if role has not been found, insert it
		$data = array('code' => $src_role_code, 'male_name' => $src_role['male_name'], 'female_name' => $src_role['female_name'], 'description' => "Appears in parliament {$this->parliament_code}.");
		$role_code = $this->api->create('Role', $data);
		return $role_code[0]['code'];
	}

	/** Update groups. If a group_kind or a group is not in db, insert it
	*
	* @param $src_groups array of scraped groups
	*/
	private function updateGroups($src_groups) {

	  foreach((array) $src_groups['group_kind'] as $src_group_kind) {

	    $src_group_kind_code = strtolower($src_group_kind['group_kind_plural_en']);

	    //common data (insert)
	    $data = array(
	        'name_' => $src_group_kind['group_kind_plural_en'],
	        'code' => $this->senateGroupKind2DbGroupKind($src_group_kind_code),
	        'subkind_of' => 'parliament',
	    );

	    $group_kind_db = $this->api->readOne('GroupKind', array('code' => $this->senateGroupKind2DbGroupKind($src_group_kind_code)));
	    if ($group_kind_db) {
	      //update - no updating here, because of standard names!
	      //$this->api->update('GroupKind', array('code' => senateGroupKind2DbGroupKind($src_group_kind_code), $data));
	    } else {
	      //insert
	      $this->api->create('GroupKind', $data);
	      //insert group_kinds
	      $this->log->write("Inserting group kind '{$src_group_kind['group_kind_plural_en']}'", Log::DEBUG);
	    }

	    foreach ((array) $src_group_kind['group'] as $src_group) {
	      //update or insert groups
	      $this->log->write("Updating group '{$src_group['name']}'", Log::DEBUG);

	      unset($group_id);

	      //find parent group id
	      if ($src_group_kind_code != 'parliament') {
			  $parent_group = $this->api->readOne('Group', array('name_' => 'Senát', 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
			  if ($parent_group)
				$parent_group_id = $parent_group['id'];
			  else
				$parent_group_id = null;
		  } else
		  		$parent_group_id = null;

		  //if group exists in db, it has source_code in group_attributes
		  $src_code_in_db = $this->api->readOne('GroupAttribute', array('name_' => 'source_code', 'value_' => $src_group['source_code'], 'parl' => $this->parliament_code));
		  if ($src_code_in_db)
			$group_id = $src_code_in_db['group_id'];

	      //construct array of values
		  $data = array (
		  	'name_' => $src_group['name'],
			//'short_name' => $src_group['short_name'],
			'group_kind_code' => $this->senateGroupKind2DbGroupKind($src_group_kind_code),
			'parliament_code' => $this->parliament_code,
			'term_id' => $this->term_id,
			'last_updated_on' => $this->update_date,
			'subgroup_of' => $parent_group_id,
		  );
		  
		  //add short_name for political groups:
		  if ($src_group_kind_code == 'caucuses')
		    $data['short_name'] = $this->senateShortName($src_group['name']);

	      if (isset($group_id))
	        //update
	        $this->api->update('Group', array('id' => $group_id), $data);
	      else {
	        //insert
	        $group_id = $this->api->create('Group', $data);
			$group_id = $group_id[0]['id'];

			// insert group's source code
			$this->api->create('GroupAttribute', array('group_id' => $group_id, 'name_' => 'source_code', 'value_' => $src_group['source_code'], 'parl' => $this->parliament_code));
	      }
	    }
	  }
	}
	
	/**
	* transform name into short name for political group in Senate (not present in senat.cz)
	* issue warning if a new political group is formed
	*
	* example: senateShortName('Senátorský klub České strany sociálně demokratické') -> 'ČSSD'
	*
	* @param $name
	* 
	* @return $short_name
	*/
	private function senateShortName($name) {
	  switch ($name) {
	    case 'Senátorský klub České strany sociálně demokratické':
	      return 'ČSSD';
	    case 'Senátorský klub Občanské demokratické strany':
	      return 'ODS';
	    case 'Senátorský klub Křesťanské a demokratické unie - Československé strany lidové':
	      return 'KDU-ČSL';
	    case 'Klub TOP 09 a Starostové':
	      return 'TOP09-S';
	    case 'Senátoři nezařazení do klubu':
	      return 'Nezařazení';
	    default:
	      $this->log->write("New political group found: {$name}'. Add its short name into senateShortName() in UpdaterCzSenat.", Log::WARNING);
	      return $name;
	  }
	}
	

	/**
	* transform group_kind_code as at senat.cz into standard ones for db
	*
	* example: senateGroupKind2DbGroupKind('senate commissions') -> 'commission'
	*
	* @param $src_group_kind_code
	*
	* @return $group_kind
	*/
	private function senateGroupKind2DbGroupKind($src_group_kind_code) {
	  switch ($src_group_kind_code) {
	    case 'caucuses' :
	      return 'political group';
	    case 'senate commissions' :
	      return 'commission';
	    case 'senate committees' :
	      return 'committee';
	    case 'senate delegations' :
	      return 'delegation';
	    case 'senate verifiers' :
	      return 'verifier';
	    case 'sub-committees' :
	      return 'subcommittee';
	    case 'senate':
	      return 'parliament';
	    default:
	      $this->log->write("Inserting new group_kind {$src_group_kind_code}'. Is it ok?", Log::WARNING);
	      return $src_group_kind_code;
	  }
	}

	/**
	 * Update information about a constituency. If it is not present in database, download info, scrape it and insert it.
	 *
	 * \param $region_code
	 *
	 * \returns id of the updated or inserted constituency.
	 */
	private function updateConstituency($region_code)
	{
		$this->log->write("Updating constituency '{$region_code}'.", Log::DEBUG);

		$src_constituency = $this->api->read('Scraper', array('remote_resource' => 'constituency', 'id' => $region_code));
		$src_constituency = $src_constituency['constituency'];

		$constituency = $this->api->readOne('Constituency', array('parliament_code' => $this->parliament_code, 'name_' => $src_constituency['name'] . ' (' .$region_code.')'));
		if ($constituency)
		{
			// update existing constituency
			$data['short_name'] = $region_code;
			if (isset($src_constituency['description']))
				$data['description'] = $src_constituency['description'];
			$this->api->update('Constituency', array('parliament_code' => $this->parliament_code, 'name_' => $src_constituency['name'] . ' (' .$region_code.')'), $data);
			return $constituency['id'];
		}

		// insert a new constituency
		$data = array('name_' => $src_constituency['name'] . ' (' .$region_code.')', 'parliament_code' => $this->parliament_code);
		$data['short_name'] = $region_code;
		if (isset($src_constituency['description']))
			$data['description'] = $src_constituency['description'];
		$constituency_id = $this->api->create('Constituency', $data);
		return $constituency_id[0]['id'];
	}

	/**
	* Update info about MP's office
	*
	* Note: There is max. 1 office for each senator at senat.cz
	*
	* @param $src_mp array 'office' => value
	* @param mp_id \e id of that MP in db
	*/
	private function updateOffice($src_mp, $mp_id) {
	  $this->log->write("Updating MP's office", Log::DEBUG);
	  // check for existing office in db
	  $office_in_db = $this->api->readOne('Office', array('mp_id' => $mp_id, 'parliament_code' => $this->parliament_code, 'address' => $src_mp['office']));

	  //if new office, insert it and close previous one
	  if (!$office_in_db and ($src_mp['office'] != '')) { //new office
	    //close previous
	    $this->api->update('Office', array('mp_id' => $mp_id, 'parl' => $this->parliament_code, '#datetime' => $this->date->format('Y-m-d')), array('until' => $this->date->format('Y-m-d')));
	    //insert the new one
	    $geo = $this->api->read('Scraper', array('remote_resource' => 'geocode', 'address' => $src_mp['office']));
	    if ($geo['coordinates']['ok'])
	      $this->api->create('Office', array('mp_id' => $mp_id, 'parliament_code' => $this->parliament_code, 'address' => $src_mp['office'], 'since' => $this->date->format('Y-m-d'), 'until' => $this->next_term_since, 'latitude' => $geo['coordinates']['lat'], 'longitude' => $geo['coordinates']['lng']));
	    else
	      $this->api->create('Office', array('mp_id' => $mp_id, 'parliament_code' => $this->parliament_code, 'address' => $src_mp['office'], 'since' => $this->date->format('Y-m-d'), 'until' => $this->next_term_since));

	  } else { //no new office
	    //check if any office at all, if not, close all
	    if ($src_mp['office'] == '') { //no src office
	      $this->api->update('Office', array('mp_id' => $mp_id, 'parl' => $this->parliament_code, '#datetime' => $this->date->format('Y-m-d')), array('until' => $this->date->format('Y-m-d')));
	    }
	  }
	}

	/**
	 * Update information about image of an MP.
	 *
	 * Not a full update is implemented, only if an image for this term-of-office is not present in the database and it is detected on the source website, it is inserted into database.
	 * Change of the image during the term is to be detected.
	 * Image filenames stay the same even during more terms at www.senat.cz.
	 * (It may require to compare images by file content)
	 *
	 * \param $src_mp array of key => value pairs with properties of a scraped MP
	 * \param $mp_id \e id of that MP in database
	 */
	private function updateMpImage($src_mp, $mp_id)
	{
		if (!isset($src_mp['image_url'])) return;
		$this->log->write("Updating MP's image.", Log::DEBUG);
		// check for existing image in the database and if it is not present, insert its filename and download the image file
		$image_in_db = $this->api->readOne('MpAttribute', array('mp_id' => $mp_id, 'name_' => 'image', 'parl' => $this->parliament_code, 'since' => $this->term_since));
		if (!$image_in_db)
		{
			// close record for image from previous term-of-office
			$this->api->update('MpAttribute', array('mp_id' => $mp_id, 'name_' => 'image', 'parl' => $this->parliament_code, 'until' => 'infinity'), array('until' => $this->term_since));

			// insert current image
			$db_image_filename = $src_mp['source_code'] . '_' . $this->term_src_code . '.jpg';
			$this->api->create('MpAttribute', array('mp_id' => $mp_id, 'name_' => 'image', 'value_' => $db_image_filename, 'parl' => $this->parliament_code, 'since' => $this->term_since, 'until' => $this->next_term_since));

			// if the directory for MP images does not exist, create it
			$path = API_FILES_DIR . '/' . $this->parliament_code . '/images/mp';
			if (!file_exists($path))
				mkdir($path, 0775, true);

			$image = file_get_contents($src_mp['image_url']);
			file_put_contents($path . '/' . $db_image_filename, $image);
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
		$value_in_db = $this->api->readOne('MpAttribute', array('mp_id' => $mp_id, 'name_' => $attr_name, 'parl' => $this->parliament_code, '#datetime' => $this->update_date));
		if ($value_in_db)
			$db_value = $value_in_db['value_'];

		if (!isset($src_value) && !isset($db_value) || isset($src_value) && isset($db_value) && $src_value == $db_value) return;

		// close the current record
		if (isset($db_value))
			$this->api->update('MpAttribute', array('mp_id' => $mp_id, 'name_' => $attr_name, 'parl' => $this->parliament_code, 'since' =>  $value_in_db['since']), array('until' => $this->update_date));

		// and insert a new one
		if (isset($src_value))
			$this->api->create('MpAttribute', array('mp_id' => $mp_id, 'name_' => $attr_name, 'value_' => $src_value, 'parl' => $this->parliament_code, 'since' => $this->update_date, 'until' => $this->next_term_since));
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
		$this->log->write("Updating MP '{$src_mp['name']['first_name']} {$src_mp['name']['last_name']}' (source id $src_code).", Log::DEBUG);

		// if MP is already in the database, update his data
		$src_code_in_db = $this->api->readOne('MpAttribute', array('name_' => 'source_code', 'value_' => $src_code, 'parl' => $this->parliament_code));
		if ($src_code_in_db)
		{
			$mp_id = $src_code_in_db['mp_id'];
			$action = self::MP_UPDATE;
		}
		// if MP is not in the database, insert him and his source code for this parliament
		else
		{
			// check for an MP in database with the same name
			$other_mp = $this->api->read('Mp', array('first_name' => $src_mp['name']['first_name'], 'last_name' => $src_mp['name']['last_name']));
			if (count($other_mp) == 0)
				$action = self::MP_INSERT | self::MP_INSERT_SOURCE_CODE;
			else
			{
				// if there is a person in the database with the same name as the MP and conflict resolution is not set for him on input, report a warning and skip this MP
				if (!isset($this->conflict_mps[$src_code]))
				{
					$this->log->write("MP {$src_mp['name']['first_name']} {$src_mp['name']['last_name']} already exists in database! MP (source id = {$src_code}) skipped. Rerun the update process with the parameters specifying how to resolve the conflict for this MP.", Log::WARNING);
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
							$this->log->write("Wrong parliament code and source code '$pmp_code' of an MP existing in the database specified in the \$conflict_mps parameter. MP {$src_mp['name']['first_name']} {$src_mp['name']['last_name']} (source id/code = {$src_code}) skipped.", Log::ERROR);
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
		if (isset($src_mp['name']['first_name']))
			$data['first_name'] = $src_mp['name']['first_name'];
		if (isset($src_mp['name']['last_name']))
			$data['last_name'] = $src_mp['name']['last_name'];
		if (isset($src_mp['sex']))
			$data['sex'] = $src_mp['sex'];
		if (isset($src_mp['name']['post_title']))
			$data['post_title'] = $src_mp['name']['post_title'];
		if (isset($src_mp['name']['pre_title']))
			$data['pre_title'] = $src_mp['name']['pre_title'];
		//date now
		$data['last_updated_on'] = date('Y-m-d H:i:s.u');

		// perform appropriate actions to update or insert MP
		if ($action & self::MP_INSERT)
		{
			if ($action & self::MP_DISAMBIGUATE)
				$data['disambiguation'] = $this->parliament_code . '/' . $src_code;
			$mp_id = $this->api->create('Mp', $data);
			$mp_id = $mp_id[0]['id'];
			if ($action & self::MP_DISAMBIGUATE)
				$this->log->write("MP {$data['first_name']} {$data['last_name']} (id = $mp_id) inserted with automatic disambiguation. Refine his disambiguation by hand.", Log::WARNING);
		}

		if ($action & self::MP_INSERT_SOURCE_CODE)
			$this->api->create('MpAttribute', array('mp_id' => $mp_id, 'name_' => 'source_code', 'value_' => $src_code, 'parl' => $this->parliament_code));

		if ($action & self::MP_UPDATE)
			$this->api->update('Mp', array('id' => $mp_id), $data);

		return $mp_id;
	}

	/**
	 * Update information about the term of office for given date. If the term is not present in database, insert it.
	 *
	 * \param $params <em>$params['term']</em> indicates date to update data for. If ommitted, today is assumed.
	 *
	 * \returns id of the term to update data for.
	 */
	private function updateTerm($params)
	{
		$this->log->write('Updating term.', Log::DEBUG);


		$term_ar = $this->api->read('Scraper', array('remote_resource' => 'term_list'));
		$term_list = $term_ar['term'];

		//find scraped term for the date
		foreach((array) $term_ar['term'] as $t) {
		  if ($t['until'] != '') { //if it is not current term
		    $until = new DateTime($t['until']);
		    $until->add(new DateInterval('P1D'));	//add 1 day
		    $since = new DateTime($t['since']);
		    if (($this->date < $until) and ($this->date >= $since))
		      $term_to_update = $t;
		  } else { //it is current term
		    $since = new DateTime($t['since']);
		    if ($this->date >= $since)
		      $term_to_update = $t;
		  }
		}
		// if there is no such term in the term list, terminate with error (class Log writing a message with level FATAL_ERROR throws an exception)
		if (!isset($term_to_update))
		  $this->log->write("The date {$this->date->format('Y-m-d')} for updating parliament {$this->parliament_code} does not belong to any term, check " . API_DOMAIN . "/data/Scrape?parliament={$this->parliament_code}&remote_resource=term_list", Log::FATAL_ERROR, 400);

		//set "global" variables
		$this->term_src_code = $term_to_update['term_code'];

		// if the term is present in the database, update it and get its id
		$src_code_in_db = $this->api->readOne('TermAttribute', array('name_' => 'source_code', 'value_' => $term_to_update['term_code'], 'parl' => $this->parliament_code));
		if ($src_code_in_db) {
		  $term_id = $src_code_in_db['term_id'];
		  $data = array('name_' => $term_to_update['name'], 'since' => $term_to_update['since'], 'short_name' => $term_to_update['term_code']);
		 if ((isset($term_to_update['until'])) and ($term_to_update['until'] != ''))
		   $data['until'] = $term_to_update['until'];
		 $this->api->update('Term', array('id' => $term_id), $data);
		} else {
		  // if term is not in the database, insert it and get its id
		  $data = array('name_' => $term_to_update['name'], 'country_code' => 'cz', 'parliament_kind_code' => 'national-upper', 'since' => $term_to_update['since'], 'short_name' => $term_to_update['term_code']);
		  if ((isset($term_to_update['until'])) and ($term_to_update['until'] != ''))
			$data['until'] = $term_to_update['until'];
		  $term_id = $this->api->create('Term', $data);
		  $term_id = $term_id[0]['id'];

		  	// insert term's source code as an attribute
			$this->api->create('TermAttribute', array('term_id' => $term_id, 'name_' => 'source_code', 'value_' => $term_to_update['term_code'], 'parl' => $this->parliament_code));
		}

		// prepare start date of this term and start date of the following term
		$this->term_since = $term_to_update['since'];
		$index = array_search($term_to_update, $term_list);
		$this->next_term_since = isset($term_list[$index+1]) ? $term_list[$index+1]['since'] : 'infinity';

		// set the effective date which the update process actually runs to
		$this->update_date = ($this->next_term_since == 'infinity') ? 'now' : $this->term_since;

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
				'name_' => 'Senát Parlamentu České republiky',
				'short_name' => 'Senát ČR',
				'description' => 'Horní komora parlamentu České republiky.',
				'parliament_kind_code' => 'national-upper',
				'country_code' => 'cz',
				'default_language' => 'cs'
			));

			// english translation
			$this->api->create('ParliamentAttribute', array(
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name_' => 'name_', 'value_' => 'Senate of Parliament of the Czech republic'),
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name_' => 'short_name', 'value_' => 'Senate CR'),
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name_' => 'description', 'value_' => 'Upper house of the Czech republic parliament.')
			));
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

?>
