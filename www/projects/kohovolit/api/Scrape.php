<?php

/**
 * ...
 */
class Scrape
{
	public static function retrieve($params)
	{
		$parliament = $params['parliament'];
		if (file_exists($parliament_class_file = "api/$parliament/ScrapeParliament.php"))
			include $parliament_class_file;
		else
			throw new Exception("The API function <em>Scrape</em> is not implemented for parliament <em>$parliament</em>.", 400);

		return ScrapeParliament::retrieve($params);
	}	
}

?>