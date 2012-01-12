<?php

/**
 * ...
 */
class ScraperUtils
{
	/**
	* finds substrings between opening and closing markers
	* @return result array of the substrings
	*/
	public static function returnSubstrings($text, $openingMarker, $closingMarker)
	{
		$openingMarkerLength = strlen($openingMarker);
		$closingMarkerLength = strlen($closingMarker);

		$result = array();
		$position = 0;
		while (($position = strpos($text, $openingMarker, $position)) !== false)
		{
			$position += $openingMarkerLength;
			if (($closingMarkerPosition = strpos($text, $closingMarker, $position)) !== false)
			{
				$result[] = substr($text, $position, $closingMarkerPosition - $position);
				$position = $closingMarkerPosition + $closingMarkerLength;
			}
		}
		return $result;
	}

	/**
	* finds 1st substring between opening and closing markers
	* @return result 1st substring
	*/
	public static function getFirstString($text, $openingMarker, $closingMarker)
	{
		$out_ar = self::returnSubstrings($text, $openingMarker, $closingMarker);
		if (count($out_ar) > 0)
			return $out_ar[0];
		else
			return null;
	}
	
	/**
	 * Returns structured information about a given name with titles (academic degrees).
	 *
	 * \param $name A full name with titles before and/or behind it.
	 *
	 * \returns Array containing keys <em>pre_title, first_name, last_name, disambiguation, post_title</em>.
	 */
	public static function tokenizeName($name)
	{
		preg_match('/([^,]+\.)? *(\S+) ([^,]+)(, *.+)?/u', $name, $matches);
		$res['pre_title'] = $matches[1];
		$res['first_name'] = $matches[2];
		$last_name_ar = explode(' ', $matches[3]);
		$res['last_name'] = $last_name_ar[0];
		$res['disambiguation'] = (isset($last_name_ar[1])) ? rtrim($last_name_ar[1], '.') : '';
		$res['post_title'] = (isset($matches[4])) ? ltrim($matches[4], ', ') : '';
		return $res;
	}
	
	/**
	* curl downloader, with possible options
	* @return html
	* example:
	* grabber('http://example.com',array(array(CURLOPT_TIMEOUT, 180)));
	*/
	public static function grabber($url,$options = array())
	{
		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 120);
		if (count($options) > 0) {
			foreach($options as $option) {
				curl_setopt ($ch, $option[0], $option[1]);
			}
		}
		$out = curl_exec($ch);
		curl_close ($ch);
		return $out;
	}
}

?>
