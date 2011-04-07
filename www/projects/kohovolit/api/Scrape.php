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
	 * It actually includes a Scrape<parliament code> class specific for the given parliament and returns the result of its scrape() method.
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the resource to scrape. Common parameters are \e parliament and \e resource.
	 *
	 * \return An array of data parsed from the resource.
	 */
	public static function read($params)
	{
		$parliament = $params['parliament'];
		$api_class = 'Scrape' . str_replace(' ', '', ucwords(strtr($parliament, '/-', '  ')));
		if (file_exists($api_class_file = "api/$parliament/$api_class.php"))
			include $api_class_file;
		else
			throw new Exception("The API function <em>Scrape</em> is not implemented for parliament <em>$parliament</em>.", 400);

		return $api_class::scrape($params);
	}	
}

?>
