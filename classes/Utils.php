<?php

/**
 * ...
 */
class Utils
{
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
		if (empty(trim($date))
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
}

?>
