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
	* curl downloader, with possible options
	* @return html
	* example:
	* grabber('http://example.com',array(CURLOPT_TIMEOUT,180));
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
		return curl_exec($ch);
		//curl_close ($ch);
	}
	
	/**
	* cut names into parts first_name, last_name, title_before, title_after
	* @return array of names
	* example:
	* name2array("Mgr. Michal Skop, Ph.D.") returns array('title_before' => 'Mgr.', title_after => 'Ph.D.', first_name => 'Michal', last_name => 'Skop')
	**/
	public static function name2array($name) {
	  $name = str_replace ('.','. ',$name);
	  $name = str_replace (',',' ',$name);
	  $name = preg_replace('/\s\s+/', ' ', $name);		//double space
	  $name_ar1 = explode(' ',$name);
	  foreach ($name_ar1 as $row) {
		if ($row != '') {
		  $name_ar2[] = $row;
		}
	  } unset ($row);
	  $which_title = 'title_before';
	  $which_name = 'first_name';
	  $out = array('title_before' => '','title_after' =>'', 'first_name' =>'', 'last_name' => '');
	  if ($name_ar2[0] != '') {
		  foreach ((array)$name_ar2 as $row) {
			if (strpos($row,'.') > 0) {
			  //if (substr($row,-1) == ',') {
			  //  $row = rtrim($row,',');
			  //}
			  if (substr($row,-1) == '.') {
				$out[$which_title] .= $row;
			  } else {
				$pom = explode('.',$row);
				if (count($pom) > 1) {
				  $pom = array_pop($pom);
				  print_r($pom);
				  $out[$which_title] .= implode('.',$pom);
				} else {
				  $out[$which_name] = $pom[0];
				  $which_name = 'last_name';
				  $which_title = 'title_after';
				}
			  } 
			} else {
			  if (self::false_title($row)) {
				$out[$which_title] .= $row;
			  } else {
				$out[$which_name] = $row;
				$which_name = 'last_name';
				$which_title = 'title_after';
			  }
			}
		  } unset($row);
	  }
	  $out['last_name'] = rtrim($out['last_name'],',');
	  return $out;
	}

	/**
	* helper function for name2array: checks for wrong titles - without '.'
	**/
	public static function false_title ($name) {
	 $array = array (
	   'MBA','CSc','Ing','Bc','Mgr','PhD','MPH','akad','soch',
	 );
	 if (in_array($name,$array)) {
	   return TRUE;
	 } else {
	   return FALSE;
	 }
	}
}

?>
