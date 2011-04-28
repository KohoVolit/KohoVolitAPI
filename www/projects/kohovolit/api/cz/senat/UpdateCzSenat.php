<?php

/**
 * This class updates data in the database for a given term of office to the state scraped from Parliament of the Czech republic - Senate official website www.senat.cz.
 */
class UpdateCzSenat
{
	/// API client reference used for all API calls
	private $ac;

	/// id and source code (ie. id on the official website) of the term of office to update the data for
	private $term_id;
	private $term_src_code;

	/// start date of the term of office to update the data for and start date of the next term
	private $term_since;
	private $next_term_since;

	/// effective date which the update process actually runs to
	private $update_date;
	
	/// code of this updated parliament
	private $parliament_code;

	/// array of MPs in this parliament that have the same name as an already existing MP in the database
	private $conflict_mps;

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
		$this->ac = new ApiClient('kohovolit', 'php', array('parliament' => $this->parliament_code));
		$this->log = new Log(LOGS_DIRECTORY . '/update/' . $this->parliament_code . '/' . strftime('%Y-%m-%d') . '.log');
		$this->log->setMinLogLevel(Log::DEBUG);
	}
	
	/**
	 * Main method called from API function Update - it scrapes data and updates the database.
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the update process.
	 *
	 * Parameter <em>$params['term']</em> indicates source code of the term of office to update data for. If ommitted, the current term is assumed.
	 *
	 * Parameter <em>$param['conflict_mps']</em> specifies how to resolve the cases when there is an MP in the database with the same name as a new MP scraped from this parliament.
	 *
	 * Parameter <em>$param['date']</em> is date in Czech format ('1.1.2001', '20.2.1976'). If ommitted, current date is assumed.
	 *
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
		
		//$this->test($params);die();  neni hotove
		

		$this->updateParliament();
		$this->term_id = $this->updateTerm($params);
		$this->conflict_mps = $this->parseConflictMps($params);
		
		// remember already updated groups' source codes to update each group only once and the same for roles
		$updated_groups = array();
		$updated_roles = array();

		// the groups to resolve parent group relation for are collected here
		$this->groups_with_parent = array();

		// read list of all MPs in the term of office to update data for
		$src_mps = $this->ac->read('Scrape', array('resource' => 'mp_list', ));
		$src_mps = $src_mps['mps'];	
		//$this->log->write('MPS: ' . print_r($src_mps, true));
		
		//to record mp_ids
		$mp_ids = array();	

		// update (or insert) all MPs in the list
		foreach((array) $src_mps as $src_mp)
		{
			// scrape details of the MP
			$src_mp = $this->ac->read('Scrape', array('resource' => 'mp', 'id' => $src_mp['source_code']));
			
			// update the MP personal details
			$mp_id = $this->updateMp($src_mp['mp']);
			if (is_null($mp_id)) continue;		// skip conflicting MPs with no given conflict resolution

			// update other MP attributes and offices
			$this->updateMpAttribute($src_mp['mp'], $mp_id, 'email', ', ');
			$this->updateMpAttribute($src_mp['mp'], $mp_id, 'website');
//			$this->updateMpAttribute($src_mp['mp'], $mp_id, 'address');
			$this->updateMpAttribute($src_mp['mp'], $mp_id, 'phone', ', ');
			$this->updateMpAttribute($src_mp['mp'], $mp_id, 'assistant', ', ');
			$this->updateMpImage($src_mp['mp'], $mp_id);
			$this->updateMpAttribute($src_mp['mp'], $mp_id, 'office');
			
			// update (or insert) constituency of the MP
			$constituency_id = $this->updateConstituency($src_mp['mp']['region_code']);
			
			//update (or insert) groups and memberships, every senator is member of senate
			$src_groups = $src_mp['mp']['group'];
			array_unshift($src_groups,array (
				'name' => 'Senát',
				'name_en' => 'Senate',
				'kind' => 'parliament',
				'role' => 'člen',
				'role_en' => 'member',
				)
			);
			
			//convert $param['date'] into iso, default = today
			if (isset($param['date']))
			  $date = new DateTime($param['date']);
			else
			  $date = new DateTime();
	
			foreach ((array) $src_groups as $src_group)
			{
				// ommit non-parliament institutions
				/*if ($src_group['kind'] == 'government' || $src_group['kind'] == 'institution' || $src_group['kind'] == 'international organization' ||
					$src_group['kind'] == 'european parliament' || $src_group['kind'] == 'president') continue;*/

				// skip wrong groups on the official cz/psp parliament website
				/*if (($src_group['id'] == 864 && $this->term_src_code != '6') ||
					($src_group['id'] == 728 && $this->term_src_code == '4') ||
					(strcmp($src_group['since'], $this->term_until) > 0 || isset($src_group['until']) && strcmp($src_group['until'], $this->term_since) < 0))
				{
					$this->log->write('Skipping wrong group (' . print_r($src_group, true) . ") in MP (source id = {$src_mp['id']}).", Log::ERROR);
					continue;
				}*/

				// update (or insert) groups the MP is member of
				if ((isset($src_group['group_id'])) and (isset($updated_groups[$src_group['group_id']])))
					$group_id = $updated_groups[$src_group['group_id']];
				else
				{
					$group_id = $this->updateGroup($src_group);
					if (isset($src_group['group_id']))
					   $updated_groups[$src_group['group_id']] = $group_id;
				}

				// update (or insert) roles the MP stands in groups
				$src_role_name = (!empty($src_group['role_en'])) ? strtolower(strip_tags($src_group['role_en'])) : 'member';
				//$src_role_name = strtr($src_role_name, array('poslanec' => 'člen', 'poslankyně' => 'člen'));
				if (isset($updated_roles[$src_role_name]))
					$role_code = $updated_roles[$src_role_name];
				else
				{
					$role_code = $this->updateRole(array('male_name' => $src_role_name, 'female_name' => $src_role_name));
					$updated_roles[$src_role_name] = $role_code;
				}

				// update memberships of the MP in groups
				if (isset($src_group['kind']))
				  $cid = $src_group['kind'] == 'parliament' ? $constituency_id : null;
				else
				  $cid = null;  

				// skip wrong memberships on the official cz/psp parliament website
				/*if (($src_mp['id'] == 377 && $src_group['id'] == 478 && $role_code == 'member' && $src_group['since'] == '2000-07-11') ||
					($src_mp['id'] == 310 && $src_group['id'] == 596 && $role_code == '1mistopredseda' && $src_group['since'] == '2002-09-17') ||
					($src_mp['id'] == 250 && $src_group['id'] == 599 && $role_code == 'vice-chairman' && $src_group['since'] == '2005-12-13'))
				{
					$this->log->write('Skipping wrong membership (' . print_r($src_group, true) . ") in MP (source id = {$src_mp['id']}).", Log::ERROR);
					continue;
				}*/

				$this->updateMembership($src_group, $mp_id, $group_id, $role_code, $cid, $date);
			}
			// close all memberships that do not exist anymore for scraped mps
			$all_memb = $this->ac->read('MpInGroup', array('mp_id' => $mp_id, 'parl' => $this->parliament_code, 'datetime' => $date->date));
			$this->closeMemberships($all_memb['mp_in_group'],$src_groups,$date);	
						
			//record mp_ids
			$mp_ids[] = $mp_id;
			//break;
		}
		//close all memberships of mps that are no longer in parliament
		$total_all_memb = $this->ac->read('MpInGroup', array('parl' => $this->parliament_code, 'datetime' => $date->date));
		$memb_ending_mps = $this->selectEndingMpsMemberships($total_all_memb,$mp_ids);
		$this->closeMemberships($memb_ending_mps,array(),$date);
	}
	
	/**
	* select memberships of mps that are no longer in parliament from array of all memberships to date
	*
	* @param $total_all_memb array of all memberships (at given date)
	* @param mp_ids array of current mps' ids
	* @return array of all memberships of mps no longer in parliament
	*/
	private function selectEndingMpsMemberships($total_all_memb,$mp_ids)
	{
	  $memb_ending_mps = array();
	  foreach ((array) $total_all_memb['mp_in_group'] as $tam)
	  {
	    if (!(in_array($tam['mp_id'],$mp_ids)))
	      $memb_ending_mps[] = $tam;
	  }
	  return $memb_ending_mps;
	}
	
	/**
	* Close all open memberships that do not exist anymore
	* i.e., closing all but those where a membership exists in scraped groups with the same(group_id,role_code) and interval since-until
	*
	* @param $memb_in_db array of all memberships that belong to the mp (and parliament)
	* @param $memb_current array of current groups/memberships (as scraped); if empty array closing all memberships from $memb_in_array
	* @date date of the membership, object DateTime
	*/
	private function closeMemberships($memb_in_db,$memb_scraped,$date)
	{

	  //$this->log->write("Closing membership (mp_id=$mp_id, group_id=$group_id, role_code='$role_code').", Log::DEBUG);
	  
	  //mark each scraped membership in memb_in_db
	  foreach ((array) $memb_scraped as $ms)
	  {
	  	$ms_db = $this->ac->read('Group', array('name_' => $ms['name'], 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
	  	
	  	if (isset($ms_db['group'][0]))
		  $group_id = $ms_db['group'][0]['id'];
		else $group_id = -1;	
		
		// mark memb_in_dbs
		foreach ((array) $memb_in_db as $key => $mid)
		{
		  //mark, if the same(group_id,role_code) and within interval since-until
		  //if it is a parliament/member, treat differently and mark directly
		  $tmp = $this->ac->read('Group',array('id' => $mid['group_id']));
		  //$this->log->write(print_r($tmp,1));die();
		  if (($tmp['group'][0]['group_kind_code'] == 'parliament') and ($mid['role_code'] == 'member')) 
		    $memb_in_db[$key]['marked'] = true;
		  else
			  if (($mid['group_id'] == $group_id) and (isset($ms['role_en'])) and ($mid['role_code'] == strtolower(strip_tags($src_group['role_en']))))
				$memb_in_db[$key]['marked'] = true;
		}
	  }
	  
	  //close all unmarked memberships in db
	  foreach ((array) $memb_in_db as $mid)
	  {
	    if (!(isset($mid['marked'])))
	    {
	      $data = array('until' => $date->format('Y-m-d'));
	      $this->ac->update('MpInGroup', array('mp_id' => $mid['mp_id'], 'group_id' => $mid['group_id'], 'role_code' => $mid['role_code'], 'since' => $mid['since']), $data);
	    }
	  } 	//$this->log->write(print_r($memb_in_db,1).print_r($memb_scraped,1).print_r($date,1), Log::DEBUG);
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
	private function updateMembership($src_group, $mp_id, $group_id, $role_code, $constituency_id, $date)
	{
		$this->log->write("Updating membership (mp_id=$mp_id, group_id=$group_id, role_code='$role_code').", Log::DEBUG);
	$this->log->write('aaa'.print_r($date,1), Log::DEBUG);
		// if membership is already present in database, update its details
		$memb = $this->ac->read('MpInGroup', array('mp_id' => $mp_id, 'group_id' => $group_id, 'role_code' => $role_code, 'datetime' => $date->date));
		$this->log->write('bbb'.print_r($memb,1), Log::DEBUG);
		if (isset($memb['mp_in_group'][0]))
		{
		    $membership = $memb['mp_in_group'][0];
			$data = array('constituency_id' => $constituency_id);
			/*if (isset($src_group['until']))
				$data['until'] = $src_group['until'];*/
			$this->ac->update('MpInGroup', array('mp_id' => $mp_id, 'group_id' => $group_id, 'role_code' => $role_code, 'since' => $membership['since']), $data);
		}
		// if it is not present, insert it
		else
		{
			$data = array('mp_id' => $mp_id, 'group_id' => $group_id, 'role_code' => $role_code, 'constituency_id' => $constituency_id, 'since' => $date->format('Y-m-d '));
			/*if (isset($src_group['until']))
				$data['until'] = $src_group['until'];*/
			$this->ac->create('MpInGroup', array($data));
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

		// search czech translations of common roles for the given male name (this is the case of generic roles like 'chairman')
		/*$role = $this->ac->read('RoleAttribute', array('name_' => 'male_name', 'value_' => $src_role['male_name'], 'lang' => 'cs'));
		if (isset($role['role_attribute'][0]))
			return $role['role_attribute'][0]['role_code'];
*/
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
	 * Update information about a group. If the group is not present in database, insert it.
	 *
	 * \param $src_group array of key => value pairs with properties of a scraped group
	 *
	 * \returns id of the updated or inserted group.
	 */
	private function updateGroup($src_group)
	{
		$this->log->write("Updating group '{$src_group['name']}' (source id ".(isset($src_group['group_id']) ? $src_group['group_id'] : "" ) . ").", Log::DEBUG);

		// for all groups except the whole parliament check presence in the database by group's source code as an attribute
		if ((!isset($src_group['kind'])) or ($src_group['kind'] != 'parliament'))
		{
			//add group kind, universal one in senate, except 'Senate'
			if ($src_group['name'] == 'Senát')
			{
			  $src_group['kind'] = 'parliament';
			  $src_group['parent_name'] = null;
			} 
			else
			{
  			  $src_group['kind'] = 'group in parliament';
  			  $src_group['parent_name'] = 'Senát';
  			}
			$src_code_in_db = $this->ac->read('GroupAttribute', array('name_' => 'source_code', 'value_' => $src_group['group_id'], 'parl' => $this->parliament_code));
			if (isset($src_code_in_db['group_attribute'][0]))
				$group_id = $src_code_in_db['group_attribute'][0]['group_id'];

			// and scrape further details about the group
			/*$grp = $this->ac->read('Scrape', array('resource' => 'group', 'term' => $this->term_src_code, 'id' => $src_group['id']));
			$src_group['short_name'] = (isset($grp['group']['short_name'])) ? $grp['group']['short_name'] : null;
			$src_group['parent_name'] = (isset($grp['group']['parent_name'])) ? $grp['group']['parent_name'] : null;*/
			


			//if (in_array($src_group['kind'], array('political group', 'committee', 'commission', 'delegation', 'friendship group', 'working group')))
		}
		// presence of the group "whole parliament" in the database is tested differently
		else
		{
			$parl_in_db = $this->ac->read('Group', array('group_kind_code' => 'parliament', 'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));
			if (isset($parl_in_db['group'][0]))
				$group_id = $parl_in_db['group'][0]['id'];

			// add further details about the group
			//$src_group['short_name'] = 'Sněmovna';
		}

		// extract column values to update or insert from the scraped group
		if (isset($src_group['parent_group']))
		{
		  $parent_group = $this->ac->read('Group', array('name_' => $src_group['parent_name'],'term_id' => $this->term_id, 'parliament_code' => $this->parliament_code));$this->log->write(print_r($parent_group,1), Log::DEBUG);die();
		  if ($parent_group['group']['id'] == '')
		    $parent_group = null;
		  else
		    $parent_group = $parent_group['group']['id'];
		} else
		    $parent_group = null;
		    
		$data = array(
			'name_' => $src_group['name'],
			//'short_name' => $src_group['short_name'],
			'group_kind_code' => $src_group['kind'],
			'parliament_code' => $this->parliament_code,
			'term_id' => $this->term_id,
			'last_updated_on' => $this->update_date,
			'subgroup_of' => $parent_group,
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
				$this->ac->create('GroupAttribute', array(array('group_id' => $group_id, 'name_' => 'source_code', 'value_' => $src_group['group_id'], 'parl' => $this->parliament_code)));
		}

		// if the group has a parent group, add it to the list to resolve parentship
		if (isset($src_group['parent_name']))
			$this->groups_with_parent[$group_id] = $src_group['parent_name'];

		return $group_id;
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
		
		$src_constituency = $this->ac->read('Scrape', array('resource' => 'constituency', 'id' => $region_code));
		$src_constituency = $src_constituency['constituency'];

		$constituency = $this->ac->read('Constituency', array('parliament_code' => $this->parliament_code, 'name_' => $region_code.'-'.$src_constituency['name']));
		if (isset($constituency['constituency'][0]))
		{
			// update existing constituency
			$data = array('last_updated_on' => $this->update_date);
			$data['short_name'] = $region_code;
			if (isset($src_constituency['description']))
				$data['description'] = $src_constituency['description'];
			$this->ac->update('Constituency', array('parliament_code' => $this->parliament_code, 'name_' => $region_code.'-'.$src_constituency['name']), $data);
			return $constituency['constituency'][0]['id'];
		}

		// insert a new constituency
		$data = array('name_' => $region_code.'-'.$src_constituency['name'], 'parliament_code' => $this->parliament_code, 'last_updated_on' => $this->update_date);
		$data['short_name'] = $region_code;
		if (isset($src_constituency['description']))
			$data['description'] = $src_constituency['description'];
		$constituency_id = $this->ac->create('Constituency', array($data));
		return $constituency_id[0];
	}
	
	/**
	 * Update information about image of an MP.
	 *
	 * Not a full update is implemented, only if an image for this term-of-office is not present in the database and it is detected on the source website, it is inserted into database.
	 * Change of the image during the term is be detected.
	 * Image filenames stay the same even during more terms. 
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
		$image_in_db = $this->ac->read('MpAttribute', array('mp_id' => $mp_id, 'name_' => 'image', 'parl' => $this->parliament_code, 'since' => $this->term_since));
		if (!isset($image_in_db['mp_attribute'][0]))
		{
			// close record for image from previous term-of-office
			$this->ac->update('MpAttribute', array('mp_id' => $mp_id, 'name_' => 'image', 'parl' => $this->parliament_code, 'until' => 'infinity'), array('until' => $this->term_since));

			// insert current image
			$db_image_filename = $src_mp['source_code'] . '_' . $this->term_src_code . '.jpg';
			$this->ac->create('MpAttribute', array(array('mp_id' => $mp_id, 'name_' => 'image', 'value_' => $db_image_filename, 'parl' => $this->parliament_code, 'since' => $this->term_since, 'until' => $this->next_term_since)));

			// if the directory for MP images does not exist, create it
			$path = DATA_DIRECTORY . '/' . $this->parliament_code . '/images/mp';
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
			$other_mp = $this->ac->read('Mp', array('first_name' => $src_mp['name']['first_name'], 'last_name' => $src_mp['name']['last_name']));
			if (!isset($other_mp['mp'][0]['id']))
				$action = self::MP_INSERT | self::MP_INSERT_SOURCE_CODE;
			else
			{
				// if there is a person in the database with the same name as the MP and conflict resolution is not set for him on input, report a warning and skip this MP
				if (!isset($this->conflict_mps[$src_code]))
				{
					$this->log->write("MP {$data['first_name']} {$data['last_name']} already exists in database! MP (source id = {$src_mp['id']}) skipped. Rerun the update process with the parameters specifying how to resolve the conflict for this MP.", Log::WARNING);
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
							$this->log->write("Wrong parliament code and source code '$pmp_code' of an MP existing in the database specified in the \$conflict_mps parameter. MP {$data['first_name']} {$data['last_name']} (source id/code = {$src_code}) skipped.", Log::ERROR);
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
		if (isset($src_mp['name']['title_after'])) 
			$data['post_title'] = $src_mp['name']['title_after'];			
		if (isset($src_mp['name']['title_before'])) 
			$data['pre_title'] = $src_mp['name']['title_before'];
		$data['last_updated_on'] = $this->update_date;
								
		// perform appropriate actions to update or insert MP
		if ($action & self::MP_INSERT)
		{
			if ($action & self::MP_DISAMBIGUATE)
				$data['disambiguation'] = $this->parliament_code . '/' . $src_code;
			$mp_id = $this->ac->create('Mp', array($data));
			$mp_id = $mp_id[0];
			if ($action & self::MP_DISAMBIGUATE)
				$this->log->write("MP {$data['first_name']} {$data['last_name']} (id = $mp_id) inserted with automatic disambiguation. Refine his disambiguation by hand.", Log::WARNING);
		}

		if ($action & self::MP_INSERT_SOURCE_CODE)
			$this->ac->create('MpAttribute', array(array('mp_id' => $mp_id, 'name_' => 'source_code', 'value_' => $src_code, 'parl' => $this->parliament_code)));

		if ($action & self::MP_UPDATE)
			$this->ac->update('Mp', array('id' => $mp_id), $data);

		return $mp_id;
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
				'name_' => 'Senát Parlamentu České republiky',
				'short_name' => 'Senát ČR',
				'description' => 'Horní komora parlamentu České republiky.',
				'parliament_kind_code' => 'national-upper',
				'country_code' => 'cz',
				'default_language' => 'cs'
			)));

			// english translation
			$this->ac->create('ParliamentAttribute', array(
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name_' => 'name_', 'value_' => 'Senate of Parliament of the Czech republic'),
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name_' => 'short_name', 'value_' => 'Senate CR'),
				array('parliament_code' => $this->parliament_code, 'lang' => 'en', 'name_' => 'description', 'value_' => 'Upper house of the Czech republic parliament.')
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
		{  //current term is the last from term list
			$current_term_ar = $this->ac->read('Scrape', array('resource' => 'term_list'));
			$term_src_code = $current_term_ar['current_term']['term_code'];
		}
		$this->term_src_code = $term_src_code;

		// get details of the term
		$term_list = $this->ac->read('Scrape', array('resource' => 'term_list'));
		$term_list = $term_list['term'];
		foreach($term_list as $term)
			if ($term['term_code'] == $term_src_code)
				$term_to_update = $term;

		// if there is no such term in the term list, terminate with error (class Log writing a message with level FATAL_ERROR throws an exception)
		if (!isset($term_to_update))
			$this->log->write("The term to update parliament {$this->parliament_code} for does not exist, check http://api.kohovolit.eu/kohovolit/Scrape?parliament={$this->parliament_code}&resource=term_list", Log::FATAL_ERROR, 400);

		// if the term is present in the database, update it and get its id
		$src_code_in_db = $this->ac->read('TermAttribute', array('name_' => 'source_code', 'value_' => $term_src_code, 'parl' => $this->parliament_code));
		if (isset($src_code_in_db['term_attribute'][0]))
		{
			$term_id = $src_code_in_db['term_attribute'][0]['term_id'];
			$data = array('name_' => $term_to_update['name'], 'since' => $term_to_update['since']);
			if (isset($term_to_update['short_name']))
				$data['short_name'] = $term_to_update['short_name'];
			if ((isset($term_to_update['until'])) and ($term_to_update['until'] != ''))
				$data['until'] = $term_to_update['until'];
			$this->ac->update('Term', array('id' => $term_id), $data);
		}
		else
		{
			// if term is not in the database, insert it and get its id
			$data = array('name_' => $term_to_update['name'], 'country_code' => 'cz', 'parliament_kind_code' => 'national-upper', 'since' => $term_to_update['since']);
			if (isset($term_to_update['short_name']))
				$data['short_name'] = $term_to_update['short_name'];
			if ((isset($term_to_update['until'])) and ($term_to_update['until'] != ''))
				$data['until'] = $term_to_update['until'];
			$term_id = $this->ac->create('Term', array($data));
			$term_id = $term_id[0];

			// insert term's source code as an attribute
			$this->ac->create('TermAttribute', array(array('term_id' => $term_id, 'name_' => 'source_code', 'value_' => $term_src_code, 'parl' => $this->parliament_code)));
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
	* neni hotove
	*/
	
	public function test($params)
	{
		$this->log->write('Started with parameters: ' . print_r($params, true));
		
		$regions = $this->ac->read('Scrape', array('resource' => 'region'));
		$regions = self::region($regions);
		//$this->log->write(print_r($regions, true));
	}
	
	/**
	* neni hotove
	*/
	
	public function region($array)
	{
	  $array['resource'] = 'region';//$this->log->write(print_r($array, true));
	  $regions = $this->ac->read('Scrape', $array);
	  if (!isset($regions['regions']['constituency']) or (count($regions['regions']['constituency']) != 1))
	  {
	    foreach((array) $regions['regions']['region'] as $r)
	    {
	      $ar[$r['region_type']] = $r['number'];
	      if (!isset($r['number']) or ($r['number'] == ''))
	      {
	        foreach((array) $r['region'] as $rr)
	        {
	          //$this->log->write('aa'.print_r($r, true));//die();
	          $new_array = $array;
	          $new_array[$r['region_type']] = $rr['number'];
	          $out = self::region($new_array);
	        }
	      }
	    }
	  }
	  else
	  {
		foreach((array) $regions['regions']['region'] as $r)
	    {
	      //$this->log->write('bb'.print_r($regions, true));die();
	      $out[$r['region_type']] = $r['number'];
	    }
	  }
	return $out;
	}
}
