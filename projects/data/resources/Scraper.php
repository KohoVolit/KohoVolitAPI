<?php

/**
 * \ingroup data
 *
 * Downloads and parses data from a remote resource for a given parliament.
 *
 * \internal
 * This class is an entry point to scraping classes for individual parliaments.
 * \endinternal
 */
class Scraper
{
	/**
	 * Downloads and parses data from a remote resource for a given parliament.
	 *
	 * \internal
	 * It actually includes a Scraper<parliament code> class specific for the given parliament and returns the result of its read() method.
	 * \endinternal	 
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the parliament and its remote resource to scrape.
	 * Parameter \c parliament specifies the parliament. Other parameters are specific to a scraper for the particular parliament.
	 * Common ones are \c remote_resource and \c id.
	 *
	 * \return An array of data parsed from the resource.
	 *
	 * \ex
	 * \code
	 * read(array('parliament' => 'cz/psp', 'remote_resource' => 'group', 'id' => 42))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [group] => Array
	 *         (
	 *             [id] => 42
	 *             [term_id] => 6
	 *             [active] => false
	 *             [name] => Poslanecký klub Komunistické strany Čech a Moravy
	 *             [short_name] => KSČM
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		$parliament = $params['parliament'];
		$scraper = 'Scraper' . str_replace(' ', '', ucwords(strtr($parliament, '/-', '  ')));
		$ok = include_once API_ROOT . "/projects/data/resources/$parliament/$scraper.php";
		if (!$ok)
			throw new Exception("The API resource <em>Scraper</em> is not implemented for parliament <em>$parliament</em>.", 400);

		$scraper_class = new $scraper;
		return $scraper_class->scrape($params);
	}
}

?>
