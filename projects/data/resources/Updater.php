<?php

/**
 * \ingroup data
 *
 * Updates the database by scraping data from a remote source.
 *
 * \internal
 * This class is an entry point to updating classes for individual parliaments.
 * \endinternal 
 *
 */
class Updater
{
	/**
	 * Scrapes data from a remote resource and updates the database for a given parliament.
	 *
	 * \internal
	 * It actually includes an Updater<parliament code> class specific for the given parliament and returns the result of its update() method.
	 * \endinternal	 
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the parliament to update and parameters of the process.
	 * Parameter \c parliament specifies the parliament. Other parameters are specific to an updater for the particular parliament.
	 * A common one is \c conflict_mps specifying resolution of conflicts with existing MPs of the same names as the scraped ones.
	 * \param $data Not used, just for consistency with update() methods of other API resources.
	 *
	 * \return Result of the update process.
	 *
	 * \note This method can be called only <a href="http://community.kohovolit.eu/doku.php/api#using_api_from_php_on_localhost">from localhost where the API is installed on</a>
	 * as it needs write access to the database.
	 *
	 * \ex
	 * \code
	 * update(array('parliament' => 'cz/psp', 'term' => '4', 'conflict_mps' => '5253->cz/psp/387, 5254->'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [update] => OK
	 * )
	 * \endcode
	 */
	public function update($params, $data = null)
	{
		$parliament = $params['parliament'];
		$updater = 'Updater' . str_replace(' ', '', ucwords(strtr($parliament, '/-', '  ')));
		$ok = include_once API_ROOT . "/projects/data/resources/$parliament/$updater.php";
		if (!$ok)
			throw new Exception("The API resource <em>Updater</em> is not implemented for parliament <em>$parliament</em>.", 400);

		$updater_class = new $updater($params);
		return $updater_class->update($params);
	}
}

?>
