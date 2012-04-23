<?php
/**
* \file DivisionSummary.php
*
* Info about a division and MPs voting in it
*/

/**
* class DivisionSummary
*/
class DivisionSummary {
  /// API client reference used for all API calls
  //private $api;
  
  	/**
	 * Creates API client reference to use during the whole process.
	 */
	public function __construct()
	{
		$this->api = new ApiDirect('data');
		//error_reporting(E_ALL);
	}  
	
	/**
	* get info about a division
	*
	* \param division_id 
	* \param parliament parliament_code
	* \code division source code
	* \param lang language
	* \param attributes whether to include division attributes, default no
	* \param division_kind_attributes whether to include division kind attributes (transl), default no inclusion
	* \param votes whether to include individual votes, default inclusion
	*   \param vote_kind whether to include vote_kind, default no inclusion
	*   \param no_mps whether to include mps' info, default inclusion
	*   \param no_meaning whether to include meaning of votes, default inclusion
	* \param membership to include group memberships
	*   \param role_code only given role_code, e.g. 'member'
	*   \param group_kind_code only given group_kind_code, e.g. 'political group'
	
	*/
	public function read($params) {
	  //division
	  $division_summary['division'] = $this->getDivision($params);
	  $division_id = $division_summary['division']['id'];
	  $divided_on = $division_summary['division']['divided_on'];
	  $division_kind_code = $division_summary['division']['division_kind_code'];
	  $parliament_code = $division_summary['division']['parliament_code'];
	  
	  if (isset($params['lang']))
	    $lang = pg_escape_string($params['lang']);
	  else {
	    $l = $this->api->readOne("ParliamentAttribute",array('name'=>'default language', 'parliament_code' => $parliament_code, '_datetime' => $divided_on));
	    if (isset($l['value']))
	      $lang = $l['value'];
	  }
	 
	  //division attributes
	  if (isset ($params['attributes'])) {
	    $p = array('division_id'=>$division_id, );
	    if (isset($lang)) {
		  $a = array();
		  $p['lang'] = '-';
		  $a[0] = $this->api->read("DivisionAttribute",$p);
	      $p['lang'] = $lang;
	      $a[1] = $this->api->read("DivisionAttribute",$p);
		  $division_summary['attributes'] = array_merge($a[0],$a[1]);
		} else 
	      $division_summary['attributes'] = $this->api->read("DivisionAttribute",$p);
	 }
	 
	 //division_kind_attributes
	 if (isset ($params['division_kind_attributes'])) {
	    $p = array('division_kind_code'=>$division_summary['division']['division_kind_code'], );
	    if (isset($lang)) {
		  $a = array();
		  $p['lang'] = '-';
		  $a[0] = $this->api->read("DivisionKindAttribute",$p);
	      $p['lang'] = $lang;
	      $a[1] = $this->api->read("DivisionKindAttribute",$p);
		  $division_summary['division_kind_attributes'] = array_merge($a[0],$a[1]);
		} else 
	      $division_summary['division_kind_attributes'] = $this->api->read("DivisionKindAttribute",$p);
	 }
	 
	 //votes
	 if (isset ($params['votes'])) {
	   
	   //query
	   $query = new Query;
	   $select = "SELECT mv.*";
	   $from = ' FROM mp_vote as mv';
	   $where = " WHERE mv.division_id='{$division_id}'";
	   
	    //mps
	    if (!isset($params['no_mps'])) {
	     $select .= ' ,m.*';
	     $from .= ' LEFT JOIN mp as m ON mv.mp_id=m.id';
	    }
	   
	    //vote kind
	    if (isset($params['vote_kind'])) {
	     $select .= ' ,vk.name as vote_kind_name, vk.description as vote_kind_description';
	     $from .= ' LEFT JOIN vote_kind as vk ON mv.vote_kind_code=vk.code';
	     //it is very slow to do it this way:
	     /*if (isset($params['lang'])) {
	       $select .= ' ,vka1.value as vote_kind_name_translated';
	       $from .= ' LEFT JOIN vote_kind_attribute as vka1 ON vk.code = vka1.vote_kind_code';
	       $where .= " AND vka1.lang='{$lang}' AND vka1.since<='{$divided_on}' AND vka1.until>'{$divided_on}' AND vka1.name='name'";
	     }*/
	     if (isset($lang)) {
	       $vka = array();
	       $vka['name'] = $this->api->read("VoteKindAttribute",array('name'=>'name','lang'=>$lang,'_datetime'=>$divided_on));
	       $vka['description'] = $this->api->read("VoteKindAttribute",array('name'=>'description','lang'=>$lang,'_datetime'=>$divided_on));
	       //reorder:
	       $division_summary['vote_kind_attributes'] = $this->reorderArray($vka,'vote_kind_code',2);
	     }
	    }
	    //vote meaning
	    //again, not to be slow:
	    if (!isset($params['no_meaning'])) {
	       if (isset($lang)) {
	         $query_meaning = new Query;
	         $query_meaning->setQuery($this->constructQueryVoteMeaning($lang,$divided_on));
	         $vm = $query_meaning->execute();
	       } else 
	         $vm = $this->api->read("VoteMeaning",array());
	       //reorder
	       $division_summary['vote_meaning'] = $this->reorderArray($vm,'code');   
	      
	       $vkm = $this->api->read("VoteKindMeaning",array('division_kind_code' => $division_kind_code)); 
	       //reorder
	       $division_summary['vote_kind_meaning'] = $this->reorderArray($vkm,'vote_kind_code');
	    }
	   
	    $query->setQuery($select . $from . $where);
	    $division_summary['votes'] = $query->execute();
	    
	    //add vote kind and its meaning to votes
	    $mps_id = array();
	    foreach ($division_summary['votes'] as &$vote) {
	      $vote_kind_code = $vote['vote_kind_code'];
	      if (isset($params['vote_kind']) and isset($lang)) {
	        $vote['vote_kind_name'] = $division_summary['vote_kind_attributes'][$vote_kind_code]['name']['value'];
	        $vote['vote_kind_description'] = $division_summary['vote_kind_attributes'][$vote_kind_code]['description']['value'];
	      }
	      if (!isset($params['no_meaning'])) {
	        $vote_meaning_code =  $division_summary['vote_kind_meaning'][$vote_kind_code]['vote_meaning_code'];
	        $vote['vote_meaning_code'] = $vote_meaning_code;
	        $vote['vote_meaning_name'] = $division_summary['vote_meaning'][$vote_meaning_code]['name'];
	        $vote['vote_meaning_description'] = $division_summary['vote_meaning'][$vote_meaning_code]['description'];
	        if (isset($lang)) {
	          $vote['vote_meaning_name'] = $division_summary['vote_meaning'][$vote_meaning_code]['name_translated'];
	          $vote['vote_meaning_description'] = $division_summary['vote_meaning'][$vote_meaning_code]['description_translated'];
	        }
	      }
	      //prepare for membership
	      $mps_id[] = $vote['mp_id'];
	    }
	    
	    //memberships in groups at the time
	    if (isset($params['membership'])) {
	      $query_membership = new Query;
	      $query_membership->setQuery($this->constructQueryMembership($params,$mps_id,$divided_on,$parliament_code));
	      $division_summary['memberships'] = $query_membership->execute();
	    }
	   
	  } //end of votes
	 
	  return $division_summary;
	}
	
	/**
	* get the division from division_id or source code + parliament
	* if no division exists, throw exception
	*/
	public function getDivision($params) {
	  //check if there is enough info to select 1 division
	  if (!isset($params['division_id']) and !(isset($params['parliament']) and isset($params['code']) ))
		throw new Exception("Parameter division_id or a couple parliament+code is required, selecting multiple divisions is not allowed.", 400);
	
	  if (isset($params['division_id'])) {
	    //from division_id
	    $division = $this->api->readOne("Division",array("id" => $params['division_id']));
	    if (count($division) == 0)
	      throw new Exception("No division found.", 400);
	  } else {
	    //from division source code + parliament
	    $query = new Query;
	    $query->setQuery("SELECT * FROM division_from_source($1,$2)");
	    $query->appendParam($params['parliament']);
	    $query->appendParam($params['code']);
	    $division_from_source = $query->execute();
	    if (!isset($division_from_source[0]['id']))
	      throw new Exception("No division found.", 400);
	    $division = $division_from_source[0];
	  }
	  return $division;
	}
	
	/**
	* reorder array
	*/
	private function reorderArray($in, $code, $level = 1) {
	  $out = array();
	  if ($level == 2)
		  foreach ($in as $key => $row)
		     foreach ($row as $item)
		       $out[$item[$code]][$key] = $item;
	  else
	    foreach ($in as $key => $row)
	      $out[$row[$code]] = $row;
      return $out;
	}
	
	/**
	* construct query for vote meaning
	*/
	private function constructQueryMembership($params,$mps_id,$divided_on,$parliament_code) {
	  $mps = implode(',',$mps_id);
	  $select = 'SELECT *';
	  $from = ' FROM mp_in_group as mig LEFT JOIN "group" as g ON g.id=mig.group_id';
	  $where = " WHERE mig.since<='{$divided_on}' AND mig.until>'{$divided_on}' AND mig.mp_id IN ({$mps}) AND g.parliament_code = '{$parliament_code}'";
	  
	  //role_code
	  if (isset($params['role_code'])) {
	    $role_code = pg_escape_string($params['role_code']);
	    $where .=" AND mig.role_code='{$role_code}'";
	  }
	  //group_kind_code
	  if (isset($params['group_kind_code'])) {
	    $group_kind_code = pg_escape_string($params['group_kind_code']);
	    $where .=" AND g.group_kind_code='{$group_kind_code}'";
	  }
	  
	  return $select . $from . $where;
	}
	
	/**
	* construct query for vote meaning
	*/
	private function constructQueryVoteMeaning($lang,$divided_on) {
		return "SELECT vm.*,vma1.value as name_translated, vma2.value as description_translated FROM vote_meaning as vm
				LEFT JOIN vote_meaning_attribute as vma1 ON vm.code = vma1.vote_meaning_code
				LEFT JOIN vote_meaning_attribute as vma2 ON vm.code = vma2.vote_meaning_code
				WHERE vma1.lang='{$lang}' AND vma1.since<='{$divided_on}' AND vma1.until>'{$divided_on}' AND vma1.name='name'
				AND vma2.lang='{$lang}' AND vma2.since<='{$divided_on}' AND vma2.until>'{$divided_on}' AND vma2.name='description'";
	}
  
}
?>
