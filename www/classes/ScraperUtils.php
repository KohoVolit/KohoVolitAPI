<?php

/**
 * ...
 */
class ScraperUtils
{
	/**
	 * najde dane podstringy mezi opening a closing a ulozi je do pole
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
	 * first string
	 */
	public static function getFirstString($text, $openingMarker, $closingMarker)
	{
		$out_ar = self::returnSubstrings($text, $openingMarker, $closingMarker);
		if (count($out_ar) > 0)
			return $out_ar[0];
		else
			return null;
	}
}

?>
