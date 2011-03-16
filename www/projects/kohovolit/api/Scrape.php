<?php

/**
 * Class Scrape (and the API function of the same name) downloads and parses data from given resources.
 *
 * This class is an entry point to scraping classes for individual parliaments.
 */
class Scrape
{
	/**
	 * Downloads and parses data from a resource for a given parliament.
	 *
	 * It actually includes a ScrapeParliament class specific for the given parliament and returns the result of its read() method.
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the resource to scrape. Common parameters are \e parliament and \e resource.
	 *
	 * \return An array of data parsed from the resource.
	 */
	public static function read($params)
	{
		$parliament = $params['parliament'];
		if (file_exists($parliament_class_file = "api/$parliament/ScrapeParliament.php"))
			include $parliament_class_file;
		else
			throw new Exception("The API function <em>Scrape</em> is not implemented for parliament <em>$parliament</em>.", 400);

		return ScrapeParliament::read($params);
	}	
}

?>
