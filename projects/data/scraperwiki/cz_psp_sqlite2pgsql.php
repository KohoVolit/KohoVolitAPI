<?php
//transfer data from scraperwiki's sqlite db into kohovolit.eu's pgsql

$step = 100;
$sqlite = '/home/michal/dev/mds/cz_parliament_voting_records_retrieval.sqlite';
$parliament_code = 'cz/psp';
$file = fopen('/home/michal/dev/mds/'.str_replace('/','_',$parliament_code).'_mp_vote.csv',"w+");
$errors = fopen('/home/michal/dev/mds/'.str_replace('/','_',$parliament_code).'_errors.txt',"w+");

//API direct
const API_DIR = '/home/shared/api.kohovolit.eu';
require 'ApiDirect.php';
error_reporting(E_ALL);
$ac = new ApiDirect('data', array('parliament' => 'cz/psp'));
//$ac->update('Updater',

$start = new DateTime();

// MPs are already in the database, get them
$db_mps = $ac->read('MpAttribute',array('name' => 'source_code','parl' => $parliament_code));

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
$new_attribute = array();


//db
$db = new PDO('sqlite:'.$sqlite);

//get min,max division_id
$res = $db->query("SELECT max(id) as max, min(id) as min FROM info");

foreach ($res as $row) {
  $max = $row['max'];
  $min = $row['min'];
}

//loop through divisions
for ($i=$min; $i <= $max; $i = $i + $step) {
  $hi = $i + $step;
  $src_divisions = $db->query("SELECT * FROM info WHERE id>={$i} AND id<{$hi}");
  foreach ($src_divisions as $src_division) {
    $division_kind_code = division_kind_code($src_division['present'],$src_division['needed']);
    $new_division = array(
      'parliament_code' => $parliament_code,
      'date' => $src_division['date'] . ' ' . $src_division['time'] . ':00',
      'division_kind_code' => $division_kind_code,
      'name' => $src_division['name'],
    );
    //echo $src_division['id'];
    //print_r($new_division);die();
    //create new division
    $division_pkey = $ac->create('Division',$new_division);
    //create attributes
    $new_attribute = array(
      'name' => 'division in session',
      'value' => $src_division['division'],
      'division_id' => $division_pkey['id']
    );
    $ac->create('DivisionAttribute',$new_attribute);
    $new_attribute = array(
      'name' => 'session',
      'value' => $src_division['session'],
      'division_id' => $division_pkey['id']
    );
    $ac->create('DivisionAttribute',$new_attribute);
    $new_attribute = array(
      'name' => 'needed',
      'value' => $src_division['needed'],
      'division_id' => $division_pkey['id']
    );
    $ac->create('DivisionAttribute',$new_attribute);
    $new_attribute = array(
      'name' => 'passed',
      'value' => $src_division['passed'],
      'division_id' => $division_pkey['id']
    );
    $ac->create('DivisionAttribute',$new_attribute);
    $new_attribute = array(
      'name' => 'source_code',
      'value' => $src_division['id'],
      'division_id' => $division_pkey['id']
    );
    $ac->create('DivisionAttribute',$new_attribute);
    $new_attribute = array(
      'name' => 'present',
      'value' => $src_division['present'],
      'division_id' => $division_pkey['id']
    );
    $ac->create('DivisionAttribute',$new_attribute);
    
    $src_votes = $db->query("SELECT * FROM vote WHERE division_id=".$src_division['id']);
    if (count($src_votes) > 0) {
		foreach ($src_votes as $src_vote) {
		  //check MP
		  if ($mps[$src_vote['mp_id']] == '') {
		    $name = explode(' ',$src_vote['name']);
		    //check if already exists (error in psp.cz)
		    $mp = $ac->readOne('Mp',array('first_name' => $name[0], 'last_name' => $name[1]));
		    if (isset($mp['id'])) {
		      $mps[$src_vote['mp_id']] = $mp['id'];
		      $error = 'Old MP: ' . $src_vote['name'] . ', division: ' . $src_vote['division_id'] . ', club: ' . $src_vote['club'] . ', src_code: ' . $src_vote['mp_id'] . "\n";
		      fwrite($errors,$error);
		    } else {
				$mp_pkey = $ac->create('Mp',array('first_name' => $name[0], 'last_name' => $name[1]));
				$ac->create('MpAttribute',array('parliament' => $parliament_code, 'name' => 'source_code', 'mp_id' => $mp_pkey['id'], 'value' => $src_vote['mp_id']));
				$error = 'New MP: ' . $src_vote['name'] . ', division: ' . $src_vote['division_id'] . ', club: ' . $src_vote['club'];
				fwrite($errors,$error);
				$mps[$src_vote['mp_id']] = $mp_pkey['id'];
		    }
		  }
		  $row = $division_pkey['id'] . "," . $mps[$src_vote['mp_id']] . "," . '"'.$vote2vote_kind_code[$src_vote['vote']] . '"' . "\n";
		  fwrite($file, $row);
		}
    }
    
  }
  
}
fclose ($file);
fclose ($errors);

$end = new DateTime();
$interval = $end->diff($start);
echo "<br/>Seconds: ".$interval->s;


function division_kind_code ($present, $needed) {
  if ($needed >= 120) $out = '3/5';
  else if (($needed == 101) and ($present != 200)) $out = 'absolute';
  else $out = 'simple';
  return $out;
}
?>
