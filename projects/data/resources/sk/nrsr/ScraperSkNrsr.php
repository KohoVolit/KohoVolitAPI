<?php

/**
 * This class downloads and parses data from given remote resources for Slovak parliament.
 */
class ScraperSkNrsr
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
			case 'current_term'; return self::scrapeCurrentTerm($params);
			case 'term_list': return self::scrapeTermList($params);
			case 'mp_list': return self::scrapeMpList($params);
			case 'current_mp_list': return self::scrapeCurrentMpList($params);
			case 'current_group_list': return self::scrapeCurrentGroupList($params);
			case 'current_group_membership': return self::scrapeCurrentGroupMembership($params);
			case 'geocode': return self::scrapeGeocode($params);
			default:
				throw new Exception("Scraping of the remote resource <em>$remote_resource</em> is not implemented for parliament <em>{$params['parliament']}</em>.", 400);
		}
	}

	/**
	* geocode address using google services
	*
	* see http://code.google.com/apis/maps/documentation/geocoding/index.html
	* using settings: region=sk, language=sk, sensor=false
	*
	* @param params
	*
	* @return array('coordinates' => array(lat, lng, ok))
	*
	* example: Scraper?parliament=sk/nrsr&remote_resource=geocode&address=Košice
	*/
	public static function scrapeGeocode($params)
	{
		$lat = '';
		$lng = '';
		//download
		$url = 'http://maps.googleapis.com/maps/api/geocode/json?region=sk&language=sk&sensor=false&address=' . urlencode($params['address']);
		//geocode
		$geo_object = json_decode(file_get_contents($url));
		//check if ok
		if ($geo_object->status == 'OK')
		{
			$lat = $geo_object->results[0]->geometry->location->lat;
			$lng = $geo_object->results[0]->geometry->location->lng;
			$ok = true;
		}
		else
			$ok = false;
		return array('coordinates' => array('lat' => $lat, 'lng' => $lng,'ok' => $ok));
	}

	/**
	* Gets current membership in groups - political groups (clubs) or committees
	*/
	private static function scrapeCurrentGroupMembership($params) {
	  //which group kind
	  if (isset($params['group_kind'])) {
	    switch ($params['group_kind']) {
	      case 'committee':
	      	$table_bit = 'committees';
	      	break;
	      case 'political_group':
	      default:
	        $table_bit = 'clubs';
	    }
	  } else {
	    $table_bit = 'clubs';
	  }
	  //get the list
	  $html = self::download("https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=sk_parliament_{$table_bit}_current&query=select%20*%20from%20membership");
	  $json_data = json_decode($html);
	  //object to array
		foreach($json_data as $row) {
		  $dd = array();
		  foreach ($row as $key => $item) {
		    $d = array();
		    $d['label'] = $key;
		    $d['value'] = $item;
		    $dd[self::friendly_url($key,'sk_SK.utf-8')] = $d;
		  }
		  $data['membership'][] = $dd;
		}

	  return $data;
	}

	/**
	* Gets info about current pol. groups
	*/
	private static function scrapeCurrentGroupList($params) {
	  //which group kind
	  if (isset($params['group_kind'])) {
	    switch ($params['group_kind']) {
	      case 'committee':
	      	$table_bit = 'committees';
	      	$column = 'committee';
	      	break;
	      case 'political_group':
	      default:
	        $table_bit = 'clubs';
	        $column = 'club';
	    }
	  } else {
	    $table_bit = 'clubs';
	    $column = 'club';
	  }
	  //get the list
	  $html = self::download("https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=sk_parliament_{$table_bit}_current&query=select%20*%20from%20{$column}");
	  $json_data = json_decode($html);
	  //object to array
		foreach($json_data as $row) {
		  $dd = array();
		  foreach ($row as $key => $item) {
		    $d = array();
		    $d['label'] = $key;
		    $d['value'] = $item;
		    $dd[self::friendly_url($key,'sk_SK.utf-8')] = $d;
		  }
		  $data['group'][] = $dd;
		}

	  return $data;
	}

	/**
	* Gets list of current MPs from ScraperWiki
	*/
	private static function scrapeCurrentMpList($params) {
		//get current term
		$html0 = self::download("https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=sk_parliament_clubs_current&query=select%20*%20from%20swvariables%20where%20name%3D'current_term'");
		$data0 = json_decode($html0);
		$current_term = $data0[0]->value_blob;

	  	$html = self::download("https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=sk_mps_list&query=select%20*%20from%20swdata%20where%20term%3D'".$current_term."'");
		$json_data = json_decode($html);
		foreach($json_data as $row) {
		  $d = array();
		  foreach ($row as $key => $item) {
		    $d[$key] = $item;
		  }
		  $data['mp'][] = $d;
		}
		return $data;
	}

	/**
	* Gets list and info about MPs from ScraperWiki
	*/
	private static function scrapeMpList($params) {
	  	$html = self::download("https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=sk_mps&query=select%20*%20from%20mp");
		$json_data = json_decode($html);
		foreach($json_data as $row) {
		  $dd = array();
		  foreach ($row as $key => $item) {
		    $d = array();
		    $d['label'] = $key;
		    $d['value'] = $item;
		    $dd[self::friendly_url($key,'sk_SK.utf-8')] = $d;
		  }
		  $data['mp'][] = $dd;
		}
		return $data;
	}

	/**
	 * ...
	 */
	private static function scrapeTermList($params)
	{
		$out = array(
			array('id' => '1', 'name' => '1994 - 1998', 'since' => '1994-10-01', 'until' => '1998-09-26'),
			array('id' => '2', 'name' => '1998 - 2002', 'since' => '1998-09-26', 'until' => '2002-09-21'),
			array('id' => '3', 'name' => '2002 - 2006', 'since' => '2002-09-21', 'until' => '2006-06-17'),
			array('id' => '4', 'name' => '2006 - 2010', 'since' => '2006-06-17', 'until' => '2010-06-12'),
			array('id' => '5', 'name' => 'od 2010', 'since' => '2010-06-12'),
		);
		return array('term' => $out);
	}

 	/**
	 * Get current term from scraperwiki
	 */
	private static function scrapeCurrentTerm($params)
	{
		$html = self::download("https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=sk_parliament_clubs_current&query=select%20*%20from%20swvariables%20where%20name%3D'current_term'");
		$data = json_decode($html);
		$out['id'] = $data[0]->value_blob;
		return array('term' => $out);
	}

	private static function download($url)
	{
		$page = file_get_contents($url);
		return $page;
	}

	/**
	* creates "friendly url" version of text, translits string (gets rid of diacritics) and substitutes ' ' for '-', etc.
	* @return friendly url version of text
	* example:
	* friendly_url('klub ČSSD')
	*     returns 'klub-cssd'
	*/
	private static function friendly_url($text,$locale = 'cs_CZ.utf-8') {
		$old_locale = setlocale(LC_ALL, "0");
	setlocale(LC_ALL,$locale);
	$url = $text;
	$url = preg_replace('~[^\\pL0-9_]+~u', '-', $url);
	$url = trim($url, "-");
	$url = iconv("utf-8", "us-ascii//TRANSLIT//IGNORE", $url);
	$url = strtolower($url);
	$url = preg_replace('~[^-a-z0-9_]+~', '', $url);
	setlocale(LC_ALL,$old_locale);
	return $url;
	}

}
