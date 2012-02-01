<?php

/**
 * This class downloads and parses data from given remote resources that correct data from the official source.
 */
class ScraperCorrections
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
			case 'private_emails': return self::scrapePrivateEmails($params);
			default:
				throw new Exception("Scraping of the remote resource <em>$remote_resource</em> is not implemented for parliament <em>{$params['parliament']}</em>.", 400);
		}
	}
	
	/**
	* Get private e-mail adresses from Google Spreadsheet.
	*/
	private static function scrapePrivateEmails($params)
	{
		$csv = file_get_contents("https://docs.google.com/spreadsheet/pub?hl=en_US&hl=en_US&key=0AjnJxiiS6aewdG9FcXF6WHRXd21xSEV6RzZyZ1dyNUE&output=csv");
		$array = Utils::parseCsv($csv);
		return array('private_emails' => $array);
	}
}
