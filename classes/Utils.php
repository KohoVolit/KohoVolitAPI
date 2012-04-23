<?php

/**
 * ...
 */
class Utils
{
	// remove accents
	// for locale see http://www.php.net/manual/en/function.iconv.php#77315
	public static function unaccent($text, $locale = null)
	{
		if (!is_null($locale)) {
		  $old = setlocale(LC_TYPE,0);
		  setlocale(LC_CTYPE, $locale);
		}
		$unaccented = preg_replace('/[\'^"~]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text));
		if (!is_null($locale))
		  setlocale(LC_CTYPE, $old);
		return $unaccented;
	}

	/**
	 * Adjusts the given text for input by PostgreSQL to_tsquery() function.
	 *
	 * \param $text The text to adjust.
	 *
	 * All accents and punctuation are removed and the text is converted to lowercase.
	 * The individual lexemes are adjusted to be searched as prefixes and combined by logical AND.
	 */
	public static function makeTsQuery($text, $locale = null)
	{
		$res = strtolower(self::unaccent($text,$locale));
		$res = preg_replace('/\W+/', ' ', $res);
		$res = preg_replace('/(\S)\s/', '$1:* ', $res . ' ');
		$res = preg_replace('/(\S)\s+(\S)/', '$1 & $2', $res);
		return $res;
	}

	/**
	 * konvertuje zapis datumu v danom jazyku (DD.MM.YYYY pre 'cs' a 'sk' a MM/DD/YYYY pre 'en') na ISO format YYYY-MM-DD, pripadne medzery ignoruje, upraveno ms: muze obsahovat &nbsp;
	 */
	public static function dateToIso($date, $language)
	{
		$date = str_replace('&nbsp;', ' ', $date);
		if ($language == 'cs' || $language == 'sk')
			$date = preg_replace('#(\d{1,2})\. *(\d{1,2})\. *(\d{4}) *#', '\3-0\2-0\1', $date);
		else if ($language == 'en')
			$date = preg_replace('#(\d{1,2}/(\d{1,2})/(\d{4})#', '\3-0\1-0\2', $date);
		else
			$date = null;
		$date = preg_replace('#-0(\d{2})#', '-\1', $date);
		if (trim($date) == "")
			$date = null;
		return $date;
	}

	/**
	 * ...
	 */
	public static function formatArray($array, $format)
	{
		switch ($format)
		{
			case 'serialized':
				return serialize($array);

			case 'json':
				return json_encode($array, JSON_FORCE_OBJECT);

			case 'csv':
				return self::arrayToCsv($array);

			case 'xml':
				return self::arrayToXml($array);

			default:
				throw new \InvalidArgumentException("Formatting an array into an uknown format <em>$format</em>.");
		}
	}

	/**
	 * ...
	 */
	public static function arrayToCsv($array, $separator = ',', $quote = '"')
	{
		$result = null;
		if (is_array($array))
			foreach ($array as $row)
			{
				$line = '';
				foreach ($row as $value)
				{
					if (!empty($line))
						$line .= $separator;
					if (!is_null($value))
						$line .= $quote . $value . $quote;
				}
				$result .= $line . "\n";
			}
		else
			$result = $quote . $array . $quote . "\n";
		return $result;
	}

	/**
	 * ...
	 */
	public static function arrayToXml($array, $root_name = 'KohoVolit.eu')
	{
 		$xml = new SimpleXMLElement('<'.'?'.'xml version="1.0" encoding="UTF-8"'.'?'.'><'.$root_name.'></'.$root_name.'>');
		if (!is_array($array)) return $xml->asXML();

		$key = key($array);
		$val = current($array);
		if (is_array($val))
			self::fillXmlElement($xml, $key, $val);
		else
			$xml->addChild($key, $val);

		return $xml->asXML();
	}

	/**
	 * ...
	 */
	private static function fillXmlElement($element, $tag, $array)
	{
		if (is_string(key($array)))
			$element = $element->addChild($tag);
		foreach ($array as $key => $value)
		{
			if (!isset($value)) continue;
			if (is_string($key))
			{
				if (is_array($value))
					self::fillXmlElement($element, $key, $value);
				else
					$element->addAttribute($key, $value);
			}
			else
			{
				if (is_array($value))
					self::fillXmlElement($element, $tag, $value);
				else
					$element->addChild($tag, $value);
			}
		}
	}

	/**
	* Parse a CSV file.
	* The first row is considered a header.
	*
	* \internal http://php.net/manual/en/function.str-getcsv.php (Rob 07-Nov-2008 04:54) + prev. note.
	* We cannot use str_getscv(), because of a problem with locale settings en_US / utf-8.
	*
	* \param file CSV file contents
	* \param options parsing options
	* \returns array(row => array(header1 => item1 ...
	*/
	public static function parseCsv($file, $options = null)
	{
		$delimiter = !isset($options['delimiter']) || empty($options['delimiter']) ? "," : $options['delimiter'];
		$to_object = !isset($options['to_object']) || empty($options['to_object']) ? false : true;
		$expr = "/$delimiter(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/"; // added
		$str = $file;
		$lines = explode("\n", $str);
		$field_names = explode($delimiter, array_shift($lines));
		foreach ($lines as $line) {
			if (empty($line)) continue;
			$fields = preg_split($expr, trim($line));
			$fields = preg_replace("/^\"(.*)\"$/s", "$1", $fields);
			$fields = preg_replace('/("")/', '"', $fields);
			$_res = $to_object ? new stdClass : array();
			foreach ($field_names as $key => $f)
			{
				if (isset($options['header_replace']) && $options['header_replace'])
					$f = str_replace(' ', '_', trim($f));
				$val = isset($fields[$key]) ? $fields[$key] : null;
				if ($to_object)
					$_res->{$f} = $val;
				else
					$_res[$f] = $val;
			}
			$res[] = $_res;
		}
		return $res;
	}
}

?>
