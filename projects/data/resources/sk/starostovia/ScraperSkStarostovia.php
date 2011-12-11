<?php

/**
 * This class downloads and parses data from given remote resources for mayors from Slovakia
 */
class ScraperSkStarostovia
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
			case 'mp_parliament_list': return self::scrapeMpParliamentList($params);
			case 'area_list': return self::scrapeAreaList($params);
			default:
				throw new Exception("Scraping of the remote resource <em>$remote_resource</em> is not implemented for parliament <em>{$params['parliament']}</em>.", 400);
		}
	}
	
	/**
	* get list of mayors and parliaments (cities) from scraperwiki
	* @return array of mayors and parliaments
	*/
	private static function scrapeAreaList($params)
	{
	  $json = self::download("https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=sk_towns_geocode&query=select%20*%20from%20%60swdata%60");
	  $array = json_decode($json);
	  return array('list' => $array);
	}
	
	/**
	* get list of mayors and parliaments (cities) from scraperwiki
	* @return array of mayors and parliaments
	*/
	private static function scrapeMpParliamentList($params)
	{
	  $json = self::download("https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=sk_towns&query=select%20*%20from%20%60swdata%60");
	  $array = json_decode($json);
	  return array('list' => $array);
	}


	/**
	* download
	*/
	private static function download($url)
	{
		$page = self::grabber($url);
		if (strlen($page) < 1000)
			throw new Exception('The file from scraperwiki.com was not downloaded well (file too short)', 503);
		return $page;
	}
	/**
	* curl downloader, with possible options
	* @return html
	* example:
	* grabber('http://example.com',array(CURLOPT_TIMEOUT,180));
	*/
	public static function grabber($url,$options = array())
	{
		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 120);
		if (count($options) > 0) {
		  foreach($options as $option) {
		    curl_setopt ($ch, $option[0], $option[1]);
		  }
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$out = curl_exec($ch);
		curl_close ($ch);
		return $out;
	}
	
	
}

?>
