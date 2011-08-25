<?php

/**
 * This class updates data in the database for a given term of office to the state scraped from some local councils in the Czech Republic.
 */
class UpdaterCzLocal
{
	/// API client reference used for all API calls
	private $api;
	
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
		$this->conflict_mps = $this->parseConflictMps($params);	
		
		// read list of all MPs in the term of office to update data for
		$src_mps = $this->api->read('Scraper', array('remote_resource' => 'mp'));
		

		// update (or insert) all MPs in the list
		foreach($src_mps as $src_mp) {
		  // update the MP personal details
		  $mp_id = $this->updateMp($src_mp);
		  if (is_null($mp_id)) continue;		// skip conflicting MPs with no given conflict resolution
		  	
		}
		
		
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
	  	$this->log->write("Updating MP '{$src_mp['first_name']} {$src_mp['last_name']}' (parliament $src_mp['parliament_name']).", Log::DEBUG);
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
