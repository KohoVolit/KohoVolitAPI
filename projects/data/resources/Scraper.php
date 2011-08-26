<?php

/**
 * Class Scraper downloads and parses data from a given web resource.
 *
 * This class is an entry point to scraping classes for individual parliaments.
 */
class Scraper
{
	/**
	 * Downloads and parses data from a resource for a given parliament.
	 *
	 * It actually includes a Scraper<parliament code> class specific for the given parliament and returns the result of its read() method.
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the resource to scrape. Common parameters are \e parliament and \e resource.
	 *
	 * \return An array of data parsed from the resource.
	 */
	public static function read($params)
	{
		$parliament = $params['parliament'];
		$api_class = 'Scraper' . str_replace(' ', '', ucwords(strtr($parliament, '/-', '  ')));
		$ok = include_once API_ROOT . "/projects/data/resources/$parliament/$api_class.php";
		if (!$ok)
			throw new Exception("The API resource <em>Scraper</em> is not implemented for parliament <em>$parliament</em>.", 400);

		return $api_class::scrape($params);
	}
}

?>
