<?php

/**
 * Class Updater implements updating of the database by scraping data from a source website.
 *
 * This class is an entry point to updating classes for individual parliaments.
 */
class Updater
{
	/**
	 * Scrapes data from source website and updates the database for a given parliament.
	 *
	 * It actually includes an Updater<parliament code> class specific for the given parliament and returns the result of its update() method.
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the update process. Common parameter are \e parliament and \e conflict_mps.
	 * \param $data Not used, just for consistency with update() methods of other API resources.
	 *
	 * \return Result of the update process.
	 */
	public static function update($params, $data = null)
	{
		$parliament = $params['parliament'];
		$api_class = 'Updater' . str_replace(' ', '', ucwords(strtr($parliament, '/-', '  ')));
		$ok = @include_once API_ROOT . "/projects/kohovolit/resources/$parliament/$api_class.php";
		if (!$ok)
			throw new Exception("The API resource <em>Updater</em> is not implemented for parliament <em>$parliament</em>.", 400);

		$updater = new $api_class($params);
		return $updater->update($params);
	}
}

?>
