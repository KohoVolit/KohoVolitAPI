<?php

/**
 * This class downloads and parses data from given resources for one parliament.
 */
class ScrapeParliament
{
	/**
	 * Downloads and parses data from a given resource.
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the resource to scrape. The resource is specified by a \e resource parameter.
	 *
	 * \return An array of data parsed from the resource.
	 */
	public static function read($params)
	{
		$page = $params['page'];
		switch ($page)
		{
			case 'mp':
				return self::scrapeMp($params);
				
			case 'group':
				return self::scrapeGroup($params);
				
			default:
				throw new Exception("Scraping of the resource <em>$resource</em> is not implemented for parliament <em>{$params['parliament']}</em>.", 400);
		}
	}
	
	private static function scrapeMp($params)
	{
		// ...scrape Mp's website to an associative array $result...
		$result['foo'] = 'bar';
		
		return $result;
	}
	
	private static function scrapeGroup($params)
	{
		// ...scrape Group's website to an associative array $result...
		$result['foo'] = 'bar';
		
		return $result;
	}
}

?>
