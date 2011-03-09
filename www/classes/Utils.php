<?php

/**
 * ...
 */
class Utils
{
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
		if (is_array($array))
			self::fillXmlElement($xml, null, $array);
		return $xml->asXML();
	}

	/**
	 * ...
	 */
	private static function fillXmlElement($element, $tag, $array)
	{
		foreach ($array as $key => $value)
		{
			if (!isset($value)) continue;
			if (is_string($key))
			{
				if (is_array($value))
					self::fillXmlElement($element, $key, $value);	// add subelements <$key>
				else
					$element->addAttribute($key, $value);	// add attribute $key = $value
			}
			else
			{
				if (is_array($value))
				{
					$child = $element->addChild($tag);	// start subelement <$tag>
					self::fillXmlElement($child, $tag, $value);
				}
				else
					$element->addChild($tag, $value);	// add subelement <$tag>$value</$tag>
			}
		}
	}
}

?>
