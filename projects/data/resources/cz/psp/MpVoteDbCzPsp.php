<?php

/**
 * This class transfer data for mp_vote from scraperwiki's sqlite for Parliament of the Czech republic - Chamber of deputies.
 */
class MpVoteDbCzPsp
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
  */
  public function update($params) {
   $this->log->write('Started with parameters: ' . print_r($params, true));
    
    //create temp file for copy
    $file_name = API_FILES_DIR . '/tmp/' . str_replace('/','_',$this->parliament_code).'_mp_vote.csv';
    $file = fopen($file_name,"w+");
    
    // MPs are (must be) already in the database, get them
    $db_mps = $this->api->read('MpAttribute',array('name' => 'source_code','parl' => $this->parliament_code));
    $mps = array();
	foreach ($db_mps as $db_mp) {
	  $mps[$db_mp['value']] = $db_mp['mp_id'];
	}
	  //add errors
	  $mps['287'] = 388;
	  $mps['5253'] = 388;
	  $mps['223'] = 256;
	  $mps['388'] = 256;
	  $mps['189'] = 193;
	  $mps['329'] = 193;
	  
	//vote2vote_kind_code
	//cz_psp
	$vote2vote_kind_code = array (
	  'A' => 'y',
	  'N' => 'n',
	  'Z' => 'a',
	  'X' => 'b',
	  '0' => 'm',
	  'M' => 'e'
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
		  'divided_on' => $src_division['date'] . ' ' . $src_division['time'] . ':00',
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
			  if ($mps[$src_vote['mp_id']] == '') {
			    $this->log->write('Missing MP in database, in division: ' . print_r($src_vote,1));
  				$this->log->write('Stopping!');
  				return array('log' => $this->log->getFilename());
  			  }
			  
			  $row = $division_pkey['id'] . "," . $mps[$src_vote['mp_id']] . "," . '"'.$vote2vote_kind_code[$src_vote['vote']] . '"' . "\n";
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
	  if ($needed >= 120) $out = '3/5';
	  else if (($needed == 101) and ($present != 200)) $out = 'absolute';
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
	  $res = $db->query("SELECT distinct(mp_id) FROM vote");
	  foreach ($res as $row) {
		if (!($this->api->read('MpAttribute',array('parl'=>$this->parliament_code,'name'=>'source_code','value'=>$row['mp_id'])))) {
			$mp_obj = $db->query("SELECT * FROM vote WHERE mp_id={$row['mp_id']} LIMIT 1");
			foreach ($mp_obj as $mp)
		      $out['missing_mp'][] = array('mp_id'=>$row['mp_id'],'name'=>$mp['name'],'random_division'=>$mp['division_id']);
		  }
	  }
	}
	
    return $out;
  }
  
}
