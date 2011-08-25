<?php

/**
 * This class downloads and parses data from given remote resources for several local councils from the Czech republic.
 */
class ScraperCzLocal
{
	/**
	 * Downloads and parses data from a given remote resource.
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the remote resource to scrape. The resource is specified by a \e remote_resource parameter.
	 *
	 * \return An array of data parsed from the remote resource.
	 */
	 
	public static function scrape($params)
	{
		$remote_resource = $params['remote_resource'];
		switch ($remote_resource)
		{
			case 'mp': return self::scrapeMp($params);
			default:
				throw new Exception("Scraping of the remote resource <em>$remote_resource</em> is not implemented for parliament <em>{$params['parliament']}</em>.", 400);
		}
	}
	/**
	* get mps from scraperwiki
	* @return array of MPs
	*/
	private static function scrapeMp($params)
	{
	  $csv = self::download("https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=csv&name=wttc_external_data&query=select%20*%20from%20swdata");
	  $array = self::parse_csv($csv);
	  return array('mp' => $array);
	}
	
	private static function download($url)
	{
		$page = file_get_contents($url);
		if (strlen($page) < 1000)
			throw new Exception('The file from psp.cz was not downloaded well. Is not around 3 in the morning CET? The psp.cz is being mainteined at that time... (file too short)', 503);
		return $page;
	}
	
/**
* parse csv file
* the first row is considered a header!
* http://php.net/manual/en/function.str-getcsv.php (Rob 07-Nov-2008 04:54) + prev. note
* we cannot use str_getscv(), because of a problem with locale settings en_US / utf-8
* @param file csv string
* @param options options
* @return array(row => array(header1 => item1 ...
*/

public static function parse_csv($file, $options = null) {
    $delimiter = empty($options['delimiter']) ? "," : $options['delimiter'];
    $to_object = empty($options['to_object']) ? false : true;
    $expr="/$delimiter(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/"; // added
    $str = $file;
    $lines = explode("\n", $str);
    $field_names = explode($delimiter, array_shift($lines));
    foreach ($lines as $line) {
        // Skip the empty line
        if (empty($line)) continue;
        $fields = preg_split($expr,trim($line)); // added
        $fields = preg_replace("/^\"(.*)\"$/s","$1",$fields); //added
        //$fields = explode($delimiter, $line);
        $_res = $to_object ? new stdClass : array();
        foreach ($field_names as $key => $f) {
            if ($to_object) {
                $_res->{$f} = $fields[$key];
            } else {
                $_res[$f] = $fields[$key];
            }
        }
        $res[] = $_res;
    }
    return $res;
}

}
