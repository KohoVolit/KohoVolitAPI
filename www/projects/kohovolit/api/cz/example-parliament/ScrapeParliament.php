<?php

/**
 * ...
 */
class ScrapeParliament
{
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
				throw new Exception("Scraping of the page <em>$page</em> is not implemented for parliament <em>$parliament</em>.", 400);
		}
	}
	
	private static function scrapeMp($params)
	{
		// ...scrape Mp's website to an associative array $result...
		$result['foo'] = 'bar';
		
		return $result
	}
	
	private static function scrapeGroup($params)
	{
		// ...scrape Group's website to an associative array $result...
		$result['foo'] = 'bar';
		
		return $result
	}
}

?>
