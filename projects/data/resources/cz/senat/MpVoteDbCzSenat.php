<?php

/**
 * This class transfer data for mp_vote from scraperwiki's sqlite for Parliament of the Czech republic - Chamber of deputies.
 */
class MpVoteDbCzSenat
{

	/**
	 * Creates API client reference to use during the whole update process.
	 */
	public function __construct($params)
	{
		$this->parliament_code = $params['parliament'];
		$this->api = new ApiDirect('data', array('parliament' => $this->parliament_code));
		$this->log = new Log(API_LOGS_DIR . '/mp_vote_db/' . $this->parliament_code . '/' . strftime('%Y-%m-%d %H-%M-%S') . '.log', 'w');
		error_reporting(E_ALL);
		set_time_limit(0);
	}

  /**
  * inserts data from sqlite into database
  *
  * \param terms whether to insert new terms
  * \param mps whether to insert new mps
  */
  public function update($params) {
   $this->log->write('Started with parameters: ' . print_r($params, true));
   
  

    //create temp file for copy
    $file_name = API_FILES_DIR . '/tmp/' . str_replace('/','_',$this->parliament_code).'_mp_vote.csv';
    $file = fopen($file_name,"w+");
    
    // MPs are (must be) already in the database, get them
    $sqlite = API_ROOT . '/projects/data/scraperwiki/database/' . str_replace('/','_',$this->parliament_code) . '.sqlite';
    $db = new PDO('sqlite:'.$sqlite);
	//check all MPs if they are already in db
	$res = $db->query("SELECT distinct(mp) FROM vote");
	foreach ($res as $row) {
	    $name = explode (' ',$row['mp']);
	    $query = new Query;
    	$query->setQuery('SELECT  m.id as mp_id, "value"  FROM mp  as m
LEFT JOIN mp_in_group as mig ON m.id=mig.mp_id
LEFT JOIN "group" as g ON g.id = mig.group_id
LEFT JOIN mp_attribute as ma ON m.id=ma.mp_id
WHERE first_name=$1 AND last_name=$2
AND g.parliament_code = \'cz/senat\' AND role_code=\'member\' AND g.group_kind_code=\'parliament\'
AND ma.name=\'source_code\' AND ma.parl=\'cz/senat\'
		');
		$query->appendParam($name[0]);
		$query->appendParam($name[1]);
		$mp_db1 = $query->execute();
		$mps[$row['mp']] = $mp_db1[0]['mp_id'];
	 }
    

	  
	//vote2vote_kind_code
	//cz_psp
	$vote2vote_kind_code = array (
	  'A' => 'y',
	  'N' => 'n',
	  'X' => 'a',
	  '0' => 'm',
	  'T' => 's'
	);
	
	//division attributes
	//cz_psp
	$attributes = array(
	  array('name' => 'division','src' => 'division'),
	  array('name' => 'session','src' => 'session'),
	  array('name' => 'needed','src' => 'needed'),
	  array('name' => 'passed','src' => 'passed'),
	  array('name' => 'source_code','src' => 'id'),
	  array('name' => 'present','src' => 'present'),
	  array('name' => 'detail','src' => 'detail'),
	);
	
	//sqlite
	$sqlite = API_ROOT . '/projects/data/scraperwiki/database/' . str_replace('/','_',$this->parliament_code) . '.sqlite';
    if (!file_exists($sqlite)) {
      $this->log->write('Missing sqlite database: ' . $sqlite);
      $this->log->write('Stopping!');
      return array('log' => $this->log->getFilename());
    }
	$db = new PDO('sqlite:'.$sqlite);

	//get min,max division_id
	$res = $db->query("SELECT max(id) as max, min(id) as min FROM info");
	foreach ($res as $row) {
	  $max = $row['max'];
	  $min = $row['min'];
	}

	//loop through divisions (it is necessary to loop because of limited memory)
	$step = 100;
	//for ($i=$min; $i <= 2000; $i = $i + $step) {	//temp
	for ($i=$min; $i <= $max; $i = $i + $step) {
	  $hi = $i + $step;
	  $src_divisions = $db->query("SELECT * FROM info WHERE id>={$i} AND id<{$hi}");
	  foreach ($src_divisions as $src_division) {
	    //division_kind_code
		$division_kind_code = $this->division_kind_code($src_division['present'],$src_division['needed']);
		
	    //create new division	
		$new_division = array(
		  'parliament_code' => $this->parliament_code,
		  'divided_on' => $src_division['date'] . ' 12:00:00',
		  'division_kind_code' => $division_kind_code,
		  'name' => (($src_division['name'] == '') ? '-' : $src_division['name']),
		);	
	    $division_pkey = $this->api->create('Division',$new_division);
	    
	    //create attributes
	    foreach ($attributes as $attribute) {
	      $new_attribute = array(
	        'name' => $attribute['name'],
	        'value' => $src_division[$attribute['src']],
	        'division_id' => $division_pkey['id'],
	      );
	      $this->api->create('DivisionAttribute',$new_attribute);
	    }
	    
	    //mps' votes
	    $src_votes = $db->query("SELECT * FROM vote WHERE division_id=".$src_division['id']);
        if (count($src_votes) > 0) {
			foreach ($src_votes as $src_vote) {
	
			  //check MP
			  if ($mps[$src_vote['mp']] == '') {
			    $this->log->write('Missing MP in database, in division: ' . print_r($src_vote,1));
  				$this->log->write('Stopping!');
  				return array('log' => $this->log->getFilename());
  			  }
			  
			  $row = $division_pkey['id'] . "," . $mps[$src_vote['mp']] . "," . '"'.$vote2vote_kind_code[$src_vote['vote']] . '"' . "\n";
			  fwrite($file, $row);			  
			}
		}
	  }
	}
	//close csv
	fclose($file);
	
	//copy votes-file into db
	$query = new Query('kv_superadmin');
		//$query_params = array($file_name);
	$query->setQuery(
		"COPY mp_vote (division_id,mp_id,vote_kind_code)
		FROM '{$file_name}'
		WITH CSV");  //somehow cannot use $query->setParams or other way to add $1
		//$query->setParams($query_params);
	$query->execute();
	
	$this->log->write('Inserted file: ' . $file_name);
	
	//delete the file
	if (isset($params['delete_csv']))
	  unlink($file_name);
	  
	return array('log' => $this->log->getFilename());
  }
  
  /**
  * calculates division_kind_code 
  *
  * \param present mps
  * \param needed to pass the division
  */
  public function division_kind_code ($present, $needed) {
	  if ($needed >= 49) $out = '3/5';
	  else if (($needed == 41) and ($present < 80)) $out = 'absolute';
	  else $out = 'simple';
	  return $out;
  }
  
  /**
  * checks if the sqlite exists, whether the mps are already in db, gets all distinct votes
  * called by 'read'
  */
  public function check($params) {
    $this->log->write('Started with parameters: ' . print_r($this, true));
    $out = array();
    
    //does the sqlite db exists?
    $sqlite = API_ROOT . '/projects/data/scraperwiki/database/' . str_replace('/','_',$this->parliament_code) . '.sqlite';
    $out['db_exist'] = file_exists($sqlite);

    if ($out['db_exist']) {
      //initiate db
	  $db = new PDO('sqlite:'.$sqlite);
	  //get distinct votes
	  $out['vote'] = array();
	  $res = $db->query("SELECT distinct(vote) FROM vote");
	  foreach ($res as $row) {
		$out['vote'][] = $row['vote'];
	  }
	  //check all MPs if they are already in db
	  $out['missing_mp'] = array();
	  $res = $db->query("SELECT distinct(mp) FROM vote");
	  foreach ($res as $row) {
	    $name = explode (' ',$row['mp']);
	    
	    $mp = $this->api->read('Mp',array('first_name' => $name[0], 'last_name' => $name[1]));
	    
	    if (!$mp) {
	      $mp_obj = $db->query("SELECT * FROM vote WHERE mp='{$row['mp']}' LIMIT 1");
	      foreach ($mp_obj as $mp)
		    $out['missing_mp'][] = array('last_name'=>$name[1],'first_name'=>$name[0],'random_division'=>$mp['division_id']);
	    } else {
	      $mp_obj = $db->query("SELECT * FROM vote WHERE mp='{$row['mp']}' LIMIT 1");
	      
	      $query = new Query();
	      $query->setQuery('
	        SELECT * FROM mp as m
	        LEFT JOIN mp_in_group as mig ON m.id=mig.mp_id
	        LEFT JOIN "group" as g ON g.id = mig.group_id
	        WHERE first_name=$1 AND last_name=$2 AND group_kind_code=\'parliament\'
	      ');
	      $query->appendParam($name[0]);
	      $query->appendParam($name[1]);
	      $mp_from_db = $query->execute();
		  foreach ($mp_obj as $mp) {
		    $parls = array();
		    $potential_conflict = true;
		    foreach ($mp_from_db as $mfd) {
		      $parls[$mfd['parliament_code']][] = array('parliament_code' => $mfd['parliament_code'], 'since' => $mfd['since'], 'name' => $mfd['name'], 'mp_id' => $mfd['mp_id']);
		      if ($mfd['parliament_code'] == 'cz/senat') $potential_conflict = false;
		    }
	        $out['mp_in_db'][] = array('last_name'=>$name[1],'first_name'=>$name[0],'random_division'=>$mp['division_id'],'membership' => $parls);
	        if ($potential_conflict)
	          $out['potential_conflict'][] = array('last_name'=>$name[1],'first_name'=>$name[0],'random_division'=>$mp['division_id'],'membership' => $parls);
	      }
	    }
	    
	  }
	}
	
    return $out;
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
