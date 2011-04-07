<?php

/**
 * Class Update (and the API function of the same name) updates the database by scraping data from a source website.
 *
 * This class is an entry point to updating classes for individual parliaments.
 */
class Update
{
	/**
	 * Scrapes data from source website and updates the database for a given parliament.
	 *
	 * It actually includes an Update<parliament code> class specific for the given parliament and returns the result of its update() method.
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the update process. Common parameter are \e parliament and \e conflict_mps.
	 *
	 * \return Result of the update process.
	 */
	public static function read($params)
	{
		$parliament = $params['parliament'];
		$api_class = 'Update' . str_replace(' ', '', ucwords(strtr($parliament, '/-', '  ')));
		if (file_exists($api_class_file = "api/$parliament/$api_class.php"))
			include $api_class_file;
		else
			throw new Exception("The API function <em>Update</em> is not implemented for parliament <em>$parliament</em>.", 400);

		$updater = new $api_class($params);
		return $updater->update($params);
	}	
}

?>
