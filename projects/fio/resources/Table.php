<?php
/**
* \ingroup fio
*
* Generates table of last transactions in a particular account in Fio bank
*/
class Table {
  /// API client reference used for all API calls
  private $api;
  
  	/**
	 * Creates API client reference to use during the whole process.
	 */
	public function __construct()
	{
		$this->api = new ApiDirect('fio');

	}
	/**
	* Generates table of last transactions in a particular account in Fio bank
	*/
  public function read($params) {
  //return API_DIR;
    return self::createTable($params);
  }
  /**
  *
  */
  public function createTable($params) {
    //get the account info
    $src_params = $params;
    $src_params['format'] = 'php';
    $source = $this->api->read('Scraper');
    return $source;
    
    $table = new simple_html_dom();
    $table = str_get_html("<table><div>**</div></table>");
    
  
    return $table;
  }
}

?>
