<?php

/**
 * \ingroup napistejim
 *
 * Provides detailed information about given parliaments.
 */
class ParliamentDetails
{
	/**
	 * Returns details about given parliaments.
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying parliaments. Available parameters are:
	 *   - \c parliament specifying a vertical bar-separated list of parliament codes.
	 *   - \c lang specifying the language to return names in. It contains a language code.
	 *
	 * If the parliament specification is ommited, details of all parliaments are returned.
	 * If the language specification is ommited or names are not available in the given language, they are returned in default language of the parliament.
	 *
	 * \return An array with details about the given parliaments ordered by their kind's weight and parliament code respectively.
	 *
	 * \ex
	 * \code
	 * read(array('parliament' => 'cz/brno|cz/psp'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [code] => cz/psp
	 *             [name] => Poslanecká sněmovna Parlamentu České republiky
	 *             [short_name] => PSP ČR
	 *             [description] => Dolní komora parlamentu České republiky.
	 *             [time_zone] => Europe/Prague
	 *             [napistejim_repinfo_function] => napistejim_repinfo_politgroup_office
	 *             [kind] => national-lower
	 *             [kind_description] => Lower house of the national level parliament - chamber of deputies.
	 *             [weight] => 2.1
	 *         )
	 *
	 *     [1] => Array
	 *         (
	 *             [code] => cz/brno
	 *             [name] => Brno
	 *             [short_name] =>
	 *             [description] => Zastupitelstvo Brno
	 *             [time_zone] => TIME_ZONE
	 *             [napistejim_repinfo_function] => napistejim_repinfo_politgroup
	 *             [kind] => local
	 *             [kind_description] => Parliament at a city level.
	 *             [weight] => 4.1
	 *         )
	 *
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		$query = new Query();
		$query->setQuery('select * from parliament_details($1, $2)');
		$query->appendParam(isset($params['parliament']) ? Db::arrayOfStringsArgument(explode('|', $params['parliament'])) : null);
		$query->appendParam(isset($params['lang']) ? $params['lang'] : null);
		return $query->execute();
	}
}

?>
