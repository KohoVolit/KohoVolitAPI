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
			case 'parliament_list': return self::scrapeParliamentList($params);
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
	  $array = Utils::parseCsv($csv, array('header_replace' => true));
	  return array('mp' => $array);
	}

	/**
	* get list of parliaments (cities) from google docs
	* @return array of parliaments
	*/
	private static function scrapeParliamentList($params)
	{
	  $csv = self::download("https://docs.google.com/a/g.kohovolit.eu/spreadsheet/pub?hl=en_US&hl=en_US&key=0ApmBqWaAzMn_dHJlNjN2WWpaLVVXc005N2E0bTdVeXc&single=true&gid=0&output=csv");
	  $array = Utils::parseCsv($csv, array('header_replace' => true));
	  return array('parliament' => $array);
	}

	/**
	*
	*/
	private static function download($url)
	{
		$page = ScraperUtils::grabber($url, array(
			array(CURLOPT_SSL_VERIFYPEER, false),
			array(CURLOPT_FOLLOWLOCATION, 1),
			array(CURLOPT_HEADER, 0)
		));
		if (strlen($page) < 1000)
			throw new Exception('The file from scraperwiki.com was not downloaded well (file too short)', 503);
		return $page;
	}
}
