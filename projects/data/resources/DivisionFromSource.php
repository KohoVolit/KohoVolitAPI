<?php
/**
* \file DivisionFromSource.php
*
* Info about a division given source or id
*/
class DivisionFromSource {
  	/**
	 * Creates API client reference to use during the whole process.
	 */
	public function __construct()
	{		
		error_reporting(E_ALL);
		$this->api = new ApiDirect('data');
	}
	
	/**
	* get the division from division_id or source code + parliament
	*/
	public function read($params) {
	  //return $params;
	  if (isset($params['division_id'])) {
	    //from division_id
	    $division = $this->api->read("Division",array("id" => $params['division_id']));
	  } else {
	    //from division source code + parliament
	    if (isset($params['parliament_code']) and isset($params['source_code'])) {
	      $query = new Query;
	      $query->setQuery("SELECT * FROM division_from_source($1,$2)");
	      $query->appendParam($params['parliament_code']);
	      $query->appendParam($params['source_code']);
	      $division = $query->execute();
	    } else 
	      $division = array();
	  }
	  return $division;
	}
}
?>
