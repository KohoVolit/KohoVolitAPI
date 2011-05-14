<?php

/**
 * This class downloads and parses data from given resources for Parliament of the Czech republic - Senate.
 */
class ScrapeCzSenat
{
	/**
	 * Downloads and parses data from a given resource.
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the resource to scrape. The resource is specified by a \e resource parameter.
	 *
	 * \return An array of data parsed from the resource.
	 */
	public static function scrape($params)
	{
		$resource = $params['resource'];
		switch ($resource)
		{
			//case 'current_term': return self::scrapeCurrentTerm($params);
			//case 'term_list': return self::scrapeTermList($params);
			case 'mp_list': return self::scrapeMpList($params);
			case 'mp': return self::scrapeMp($params);
			case 'term_list': return self::scrapeTermList($params);
			case 'group_list': return self::scrapeGroupList($params);
			case 'group': return self::scrapeGroup($params);
			case 'elected_mp_list': return self::scrapeElectedMpList($params);
			case 'division': return self::scrapeDivision($params);
			case 'constituency': return self::scrapeConstituency($params);
			case 'region': return self::scrapeRegion($params);
			case 'geocode': return self::scrapeGeocode($params);
			default:
				throw new Exception("Scraping of the resource <em>$resource</em> is not implemented for parliament <em>{$params['parliament']}</em>.", 400);
		}
	}

  /**
  * geocode address using google services
  * 
  * see http://code.google.com/apis/maps/documentation/geocoding/index.html
  * using settings: region=cz, language=cs, sensor=false
  *
  * @param address
  *
  * @return array('coordinates' => array(lat, lng, ok))
  *
  * example: Scrape?parliament=cz/senat&resource=region&address=Plasy
  */
  public static function scrapeGeocode($params) {
    $lat = '';
    $lng = '';
	//download  
     $url = 'http://maps.googleapis.com/maps/api/geocode/json?region=cz&language=cs&sensor=false&address=' . urlencode($params['address']);
     //geocode
     $geo_object = json_decode(file_get_contents($url));
     //check if ok
     if ($geo_object->status == 'OK') {
       $lat = $geo_object->results[0]->geometry->location->lat;
       $lng = $geo_object->results[0]->geometry->location->lng;
       $ok = true;
     } else {
       $ok = false;
     }
     return array('coordinates' => array('lat' => $lat, 'lng' => $lng,'ok' => $ok));   
  }
  

  /**
  * scrape regions for constituencies
  * @param date status at given date in Czech format
  * 		default today
  * @param kraj
  * @param okres
  * @param obec
  * @param uzemi
  *
  * example: Scrape?parliament=cz/senat&resource=region&kraj=43
  */
  private static function scrapeRegion($params)
  {
     //set date
    if (isset($params['date'])) {
   	 $d = $params['date'];
   	} else {
   	 $date_oo = new DateTime();
	 $d = $date_oo->format('d.m.Y');
	} 
	//download the page
	$str = (isset($params['kraj']) ? '&kraj=' . $params['kraj'] : '') .
			(isset($params['okres']) ? '&okres=' . $params['okres'] : '') .
			(isset($params['obec']) ? '&obec=' . $params['obec'] : '') .
			(isset($params['uzemi']) ? '&uzemi=' . $params['uzemi'] : '');
	$url = 'http://www.senat.cz/volby/hledani/index.php?&ke_dni='.$d . $str;
	$html = self::download($url);
	//start parsing
	$text_parts = ScraperUtils::returnSubstrings($html,'<form action="/volby/hledani/index.php','</select>');
	//print_r($params);
	foreach ((array) $text_parts as $tp)
	{
	  $region_type = ScraperUtils::getFirstString($tp,'name="','"');
	  $result['region'][$region_type]['region_type'] = $region_type;  
	  $rows = ScraperUtils::returnSubstrings($tp, '<option', '/option');
	  $i = 1;
	  foreach ((array) $rows as $r) 
	  {
	    $number = ScraperUtils::getFirstString($r, 'value="', '">');
	    if ($number != '0') {
	      $result['region'][$region_type]['region']['region_'.$i]['number'] = $number;
	      $result['region'][$region_type]['region']['region_'.$i]['name'] = ScraperUtils::getFirstString($r, '">', '<');
	    }
	    $i++;
	  }
	  $result['region'][$region_type]['number'] = ScraperUtils::getFirstString($tp, 'selected" value="', '"');
	}
	
	$constituencies = ScraperUtils::returnSubstrings($html,'o_obvodu.php?kod=','"');
	foreach ((array) $constituencies as $c) {
	  $result['constituency']['constituency_'.$c]['number'] = $c;
	}
	return array('regions' => $result);
	
  }

  /**
  * scrape list of MPs in Czech and English
  * @param date status at given date in Czech format
  * 		default today
  *
  * example: Scrape?parliament=cz/senat&resource=mps
  * 	Scrape?parliament=cz/senat&resource=mps&date=20.2.2002
  */
  private static function scrapeMpList($params)
  {
    //set date
    if (isset($params['date'])) {
   	 $d = $params['date'];
   	} else {
   	 $date_oo = new DateTime();
	 $d = $date_oo->format('d.m.Y');
	}
	//download Czech an English pages
	$url = 'http://www.senat.cz/senatori/index.php?lng=cz&par_2=1&ke_dni='.$d;
	$html = self::download($url);
	$url_en = 'http://www.senat.cz/senatori/index.php?lng=en&par_2=1&ke_dni='.$d;
	$html_en = self::download($url_en);
   //start parsing
   $text_part = ScraperUtils::getFirstString($html,'<table>','</table>');
   $rows = ScraperUtils::returnSubstrings($text_part,'<tr','</tr>');
   $text_part_en = ScraperUtils::getFirstString($html_en,'<table>','</table>');
   $rows_en = ScraperUtils::returnSubstrings($text_part_en,'<tr','</tr>');
   if (count($rows) < 5) {
     throw new Exception('Too few rows in the downloaded file.', 503);
   } else {
     //get rid of first 4 rows
     for ($i = 1;$i <= 4;$i++) {
	   array_shift($rows);
	   array_shift($rows_en);
	 }
	 //extract the information
	 $i = 0;
	 foreach ((array) $rows as $row) {
	   $items = ScraperUtils::returnSubstrings(str_replace('&nbsp;',' ',$row),'<td>','</td>');
	   $items_en = ScraperUtils::returnSubstrings(str_replace('&nbsp;',' ',$rows_en[$i]),'<td>','</td>');
	   $id = ScraperUtils::getFirstString($items[1],'par_3=','"');
	   $line_1 = explode(' ',trim(strip_tags($items[0])));
	   $result['mp_'.$id]['source_code'] = $id;
	   $result['mp_'.$id]['region_code'] = $line_1[0];
	   array_shift($line_1);
	   array_shift($line_1);
	   $result['mp_'.$id]['region_name'] = implode(' ',$line_1);
	   $line_2 = explode(' ',trim(strip_tags($items[1])));
	   $result['mp_'.$id]['first_name'] = $line_2[0];
	   $result['mp_'.$id]['last_name'] = end($line_2);
	   $result['mp_'.$id]['website'] = strip_tags($items[2]);
	   $result['mp_'.$id]['party'] = strip_tags($items[3]);
	   $result['mp_'.$id]['party_en'] = strip_tags($items_en[3]);
	   $i++;
	 }
   }
   return array('mps' => $result);
  }

  /**
  * scrape details about an MP in Czech and English
  * @param date status at given date in Czech format
  * 		default today
  *
  * example: Scrape?parliament=cz/senat&resource=mp
  * 	Scrape?parliament=cz/senat&resource=mp&date=20.2.2002
  */
  private static function scrapeMp($params)
  {
    //set date
    if (isset($params['date'])) {
   	 $d = $params['date'];
   	} else {
   	 $date_oo = new DateTime();
	 $d = $date_oo->format('d.m.Y');
	}
	//get html
	$id = $params['id'];
	$url = 'http://www.senat.cz/senatori/index.php?lng=cz&ke_dni='.$d.'&par_3='.$id;
	$html = self::download($url);
	$url_en = 'http://www.senat.cz/senatori/index.php?lng=en&ke_dni='.$d.'&par_3='.$id;
	$html_en = self::download($url_en);
	//$result['original_url'] = $url;
	//$result['original_url_en'] = $url_en;	  
	//extract info
	if (strpos($html,'V daném období nemá senátor/ka platný mandát') > 0) {
	  throw new Exception('The senator does not have a legitimate mandate during this period',404);
	} else {
	  $name = ScraperUtils::name2array(trim(strip_tags(str_replace("\xc2\xa0",' ',str_replace('&nbsp;',' ',ScraperUtils::getFirstString($html,'<h1 class="h1-back-sen">','</h1>'))))));
	  $result['mp']['source_code'] = $id;
	  $result['mp']['name'] = $name;
	  $foto_part = ScraperUtils::getFirstString($html,'<div class="foto">','/>');
	  $result['mp']['image_url'] = 'http://senat.cz/'. ScraperUtils::getFirstString($foto_part,'img src="../','"');
	  $result['mp']['party'] = trim(strip_tags(ScraperUtils::getFirstString($html,'Pol. příslušnost:','</dd>')));
	  $result['mp']['party_en'] = trim(strip_tags(ScraperUtils::getFirstString($html_en,'Political affiliation:','</dd>')));
	  $result['mp']['region_code'] = trim(strip_tags(str_replace('&nbsp;',' ',ScraperUtils::getFirstString($html,'Volební obvod:</dt><dd>č.','</dd>'))));
	  $result['mp']['candidate_list'] = trim(strip_tags(str_replace('&nbsp;',' ',ScraperUtils::getFirstString($html,'Zvolen za:','v roce'))));
	  $result['mp']['election_year'] = trim(strip_tags(str_replace('&nbsp;',' ',ScraperUtils::getFirstString($html,'v roce','</dd>'))));
	  $mandate = explode('-',trim(strip_tags(str_replace('&nbsp;',' ',ScraperUtils::getFirstString($html,'Mandát:','</dd>')))));
	  $m_date = new DateTime(trim($mandate[0]));
	  $result['mp']['mandate_since'] = $m_date->format('Y-m-d');
	  $m_date = new DateTime(trim($mandate[1]));
	  $result['mp']['mandate_until'] = $m_date->format('Y-m-d');
	  $result['mp']['website'] = trim(strip_tags(str_replace('&nbsp;',' ',ScraperUtils::getFirstString($html,'WWW:','</dd>'))));
	  $text_part = ScraperUtils::getFirstString($html,'<h3>Členství:</h3>','</div>');
	  $groups = ScraperUtils::returnSubstrings($text_part,'<li>','</li>');
	  $text_part_en = ScraperUtils::getFirstString($html_en,'<h3>Membership:</h3>','</div>');
	  $groups_en = ScraperUtils::returnSubstrings($text_part_en,'<li>','</li>');
	  $i = 0;
	  if (isset($groups[0])) {
	    foreach ((array) $groups as $group) {
		  $group_id = ScraperUtils::getFirstString($group,'par_2=','">');
		  $result['mp']['group']['group_'.$group_id]['group_id'] = $group_id;
		  $result['mp']['group']['group_'.$group_id]['name'] = ScraperUtils::getFirstString($group,'">','</a>');
		  $result['mp']['group']['group_'.$group_id]['name_en'] = ScraperUtils::getFirstString($groups_en[$i],'">','</a>');
		  $result['mp']['group']['group_'.$group_id]['role'] = ScraperUtils::getFirstString($group,'(',')');
		  $result['mp']['group']['group_'.$group_id]['role_en'] = ScraperUtils::getFirstString($groups_en[$i],'(',')');
		  $i++;
		}
	  }
	  $address = explode('<br />',ScraperUtils::getFirstString($html,'Adresa senátorské kanceláře:','</dd>'));
	  $result['mp']['office'] = '';
	  if (strlen(strip_tags($address[0])) > 1) {
	    foreach ((array) $address as $a) {
	      $result['mp']['office'] .= trim(trim(strip_tags($a)),', ') . ', ';
		}
		$result['mp']['office'] = trim(trim($result['mp']['office']),',');
	  }
	  $assistants = explode(', ',trim(trim(strip_tags(ScraperUtils::getFirstString($html,'Asistenti senátora:','</dd>'))),','));
	  if ($assistants[0] != '') {
	    $a = 1;
	    foreach ((array) $assistants as $assistant) {
		  $result['mp']['assistant']['assistant_'.$a] = trim(str_replace('&nbsp;',' ',$assistant));
		  $a++;
		}
	  }
	  $phones = explode(', ',trim(strip_tags(ScraperUtils::getFirstString($html,'Telefony:','</dd>'))));
	  if ($phones[0] != '') {
	    $a = 1;
	    foreach ((array) $phones as $phone) {
		  $result['mp']['phone']['phone_'.$a] = trim(str_replace('&nbsp;',' ',$phone));
		  $a++;
		}
	  }
	  $emails = explode(' &nbsp;',trim(strip_tags(ScraperUtils::getFirstString($html,'e-maily:','</dd>'))));
	  if ($emails[0] != '') {
	    $a = 1;
	    foreach ((array) $emails as $email) {
		  $result['mp']['email']['email_'.$a] = trim(str_replace('&nbsp;',' ',$email));
		  $a++;
		}
	  }
	  //sex
	  if (strpos($html,'Jak jsem hlasovala') > 0) {
	    $result['mp']['sex'] = 'f';
	  } else {
	    $result['mp']['sex'] = 'm';
	  }
	}
	//print_r($result);die();
	return $result;
  }
  
  /**
  * scrape list of terms
  * example: 
  *     scrapeTermList();
  * note: 'until' should add +1 day when used
  */
  private static function scrapeTermList($params)
  { 
  	//get html
	$url = 'http://senat.cz/datum/datum.php';
	$html = self::download($url);
	$text_part = ScraperUtils::getFirstString($html,'<h2>Aktuální nastavení je','</ul>');
	  $term_lines = ScraperUtils::returnSubstrings($text_part,'<li','</li>');
	  if (strlen($term_lines[0]) > 1) {
	    foreach ((array) $term_lines as $line) {
		  $line = str_replace('&nbsp;',' ',$line);
		  $term_code = ScraperUtils::getFirstString($line,'>','.');
		  $term['term_code'] = $term_code;
		  $line_ar = explode(' ',$line);
		  $term['name'] = $term_code . '. ' . $line_ar[1] . ' ' . $line_ar[2];
		  $date = new DateTime($line_ar[3]);
		  $term['since'] = $date->format('Y-m-d');
		  if ($line_ar[5] != 'do') {
		    $date = new DateTime($line_ar[5]);
			$term['until'] = $date->format('Y-m-d');
		  } else {
		    $term['until'] = '';
		  }
		  $result['term']['term_'.$term_code] = $term;
		}
		$result['current_term'] = $term;
	  }
	return $result;
  }
  
  /**
  * scrape list of elected MPs in given year
  * @param year
  * example: 
  *     scrapeElectedMpList(array('year' => 2010));
  */
  private static function scrapeElectedMpList($params)
  { 
  	//get html
    $year = $params['year'];
    $url = 'http://senat.cz/volby/v'.$year.'.php';
	$html = self::download($url);
	//echo $html;die();
	  	  $tables = ScraperUtils::returnSubstrings($html,'class="bordered-table"','</table>');
	  foreach ((array) $tables as $table) {
	    $tbody = ScraperUtils::getFirstString($table.'</tbody>','<tbody>','</tbody>');	//roor in 2003
		if ($tbody != '') {
		  $rows = ScraperUtils::returnSubstrings($tbody,'<tr','</tr>');
		  foreach ((array) $rows as $row) {
		    $items = ScraperUtils::returnSubstrings($row, '<td','</td>');
			if (isset($items[3])) {
				$pattern = '/par_3=([0-9]{1,})/';
				preg_match($pattern, $items[3], $matches);
				$id = $matches[1];//ScraperUtils::getFirstString($items[3],'par_3=','&');
				$result['mp']['mp_'.$id]['source_code'] = $id;
				$name = ScraperUtils::name2array(strip_tags(ScraperUtils::getFirstString($items[3],'>','</a>')));
				$result['mp']['mp_'.$id]['name'] = $name;
				$sd = ScraperUtils::getFirstString($items[3],'ke_dni=','"');
				if ($sd != '') {
					$sd2 = new DateTime($sd);
					$result['mp']['mp_'.$id]['safe_date'] = $sd2->format('Y-m-d');
				}
			}
		  }
		}
	  }

	if (count($result) == 0) {
	  throw new Exception('Something is wrong, maybe a wrong year (e.g., this scraper cannot be used for year 1996)',404);
	}
	//print_r($result);die();
	return $result;
  }
  
  /**
  * scrape voting records from one division
  * @param id
  * example: 
  */
  private static function scrapeDivision($params)
  {
    $id = $params['id'];
    $url = 'http://www.senat.cz/xqw/xervlet/pssenat/hlasy?G='.$id;
	$html = str_replace('&nbsp;',' ',iconv("cp1250", "UTF-8//TRANSLIT", self::download($url, 1)));
	if (strlen($html) > 0) {
		$result['division']['source_code'] = $id;
		$head = explode(',',ScraperUtils::getFirstString($html,'<h1 class="h1-back-dok">','</h1>'));
		$pom = explode('.',$head[0]);
		$result['division']['session'] = $pom[0];
		$pom = explode('.',trim($head[1]));
		$result['division']['number_in_session'] = $pom[0];	
		$d_date = new DateTime(trim($head[2]));
		$result['division']['divided_on'] = $d_date->format('Y-m-d');
		$text_part = ScraperUtils::getFirstString($html,'<h2>','</h2>');
		$pattern = '/^([0-9]{1,})\/ ([0-9]{1,})/';
		preg_match($pattern,$text_part,$matches);
		if (isset($matches[0])) {
		  $result['division']['print_number'] = $matches[1];
		  $result['division']['print_term'] = $matches[2];
		} else {
		  $pattern = '/^([a-zA-Z]{1} [0-9]{1,})\/ ([0-9]{1,})/';
		  preg_match($pattern,$text_part,$matches);
		  if (isset($matches[0])) {
			$result['division']['print_number'] = $matches[1];
			$result['division']['print_term'] = $matches[2];
		  }
		}
		$text_part = trim(str_replace($matches[0] . ' -','',$text_part));
		$tp_ar = explode('<br />',$text_part);
		$result['division']['name'] = $tp_ar[0];
		$result['division']['action'] = $tp_ar[1];
		$result['division']['note'] = trim(strip_tags(ScraperUtils::getFirstString($html,'Pozn.:','</b>')));
		
		$result['division']['result_text'] = ScraperUtils::getFirstString($html,'<center>','<hr');
		$result2approved = array(
		  'NÁVRH BYL PŘIJAT' => 'yes',
		  'NÁVRH BYL ZAMÍTNUT' => 'no',
		  'ZMATEČNÉ HLASOVÁNÍ' => 'cancelled',
		);
		$result['division']['approved'] = $result2approved[$result['result_text']];
		$result['division']['present'] = ScraperUtils::getFirstString($html,'PŘÍTOMNO=',' ');
		$result['division']['necessary'] = ScraperUtils::getFirstString($html,'JE TŘEBA=',' ');
		$result['division']['yes'] = ScraperUtils::getFirstString($html,'ANO=',' ');
		$result['division']['no'] = ScraperUtils::getFirstString($html,'NE=',' ');
		$result['division']['not_present'] = ScraperUtils::getFirstString($html,'NEPŘÍTOMEN=',' ');
		$result['division']['abstain'] = ScraperUtils::getFirstString($html,'ZDRŽEL SE=',' ');
		
		$groups = ScraperUtils::returnSubstrings($html,'<h3','<hr />');
		if (strlen($groups[0]) > 1) {
		  $i = 1;
		  $vote2code = array(
			'A' => 'y',
			'N' => 'n',
			'0' => 'm',
			'X' => 'a',
			'T' => 's',
		  );
		  foreach ($groups as $group) {
			$group_name = ScraperUtils::getFirstString($group,'>','</h3>');
			$mps = ScraperUtils::returnSubstrings($group,'<td>','</td>');
			if (strlen($mps[0]) > 1) {
			  foreach ((array) $mps as $mp) {
				$result['division']['mp']['mp_'.$i]['vote_code'] = substr($mp,0,1);
				$result['division']['mp']['mp_'.$i]['vote'] = $vote2code[$result['mp']['mp_'.$i]['vote_code']];
				$name = trim(substr($mp,1));
				$name_ar = explode(' ',$name);
				$result['division']['mp']['mp_'.$i]['first_name'] = trim($name_ar[0]);
				$result['division']['mp']['mp_'.$i]['last_name'] = trim(end($name_ar));
				$result['division']['mp']['mp_'.$i]['group'] = $group_name;
				$i++;
			  }
			}
		  }
		
		}
	} else {
	  throw new Exception('Wrong division ID. No data.',404);
	}
	return $result;
  }
  
  /**
  * scrape list of groups
  * @param params['date']
  * example: 
  *     scrapeGroupList();
  *     scrapeGroupList(array('date' => '20.2.2002'));
  */
  private static function scrapeGroupList($params)
  {  
    //set date
    if (isset($params['date'])) {
   	 $d = $params['date'];
   	} else {
   	 $date_oo = new DateTime();
	 $d = $date_oo->format('d.m.Y');
	 }
	//there are 4 types of groups, par_1=
	$group_kinds = array('S','V','M','D','K','O','P');
	foreach ((array) $group_kinds as $group_kind) {
		$url = 'http://www.senat.cz/organy/index.php?lng=cz&ke_dni='.$d.'&par_1='.$group_kind;
		$html = self::download($url);
		$url_en = 'http://www.senat.cz/organy/index.php?lng=en&ke_dni='.$d.'&par_1='.$group_kind;
		$html_en = self::download($url_en);
		//$result['original_url'][$group_kind] = $url;
		//$result['original_url_en'][$group_kind] = $url_en;
		$type = ScraperUtils::getFirstString($html,'<h1 class="h1-back-sen">','</h1>');
		$type_en = ScraperUtils::getFirstString($html_en,'<h1 class="h1-back-sen">','</h1>');
		$result['group_kind']['group_kind_'.$group_kind]['group_kind_plural'] = $type;
		$result['group_kind']['group_kind_'.$group_kind]['group_kind_plural_en'] = $type_en;
		$text_part = ScraperUtils::getFirstString($html,'<h1 class="h1-back-sen">','</ul>');
		$rows = ScraperUtils::returnSubstrings($text_part,'<li>','</li>');
		$text_part_en = ScraperUtils::getFirstString($html_en,'<h1 class="h1-back-sen">','</ul>');
		$rows_en = ScraperUtils::returnSubstrings($text_part_en,'<li>','</li>');
		$i = 0;
		if (strlen($rows[0]) > 1) {
		  foreach ((array) $rows as $row) {
		    $id = ScraperUtils::getFirstString($row,'par_2=','>');
		    $result['group_kind']['group_kind_'.$group_kind]['group']['group_'.$id]['source_code'] = $id;
			$result['group_kind']['group_kind_'.$group_kind]['group']['group_'.$id]['name'] = strip_tags($row);
			$result['group_kind']['group_kind_'.$group_kind]['group']['group_'.$id]['name_en'] = strip_tags($rows_en[$i]);
			$i++;
		  }
		}
		
	}
		
	return $result;
  }
  
  /**
  * scrape list of groups
  * @param params['date']
  * example: 
  *     scrapeGroupList(array('id' => 66, 'date' => '20.2.2002'));
  */
  private static function scrapeGroup($params)
  {  
    //set date
    if (isset($params['date'])) {
   	 $d = $params['date'];
   	} else {
   	 $date_oo = new DateTime();
	 $d = $date_oo->format('d.m.Y');
	}
	//download the files
	$id = $params['id'];
    $url = 'http://www.senat.cz/organy/index.php?lng=cz&ke_dni='.$d.'&par_2='.$id;
	$html = self::download($url);
	$url_en = 'http://www.senat.cz/organy/index.php?lng=en&ke_dni='.$d.'&par_2='.$id;
	$html_en = self::download($url_en);
	//$result['original_url'] = $url;
	//$result['original_url_en'] = $url_en;
	//extract info
	$name = ScraperUtils::getFirstString($html,'<h1 class="h1-back-sen">','</h1>');
	if ($name != 'Odkaz na tuto stránku má nesprávné parametry nebo bylo zadáno datum mimo funkční období odkazovaného orgánu Senátu.') {
		$result['group']['name'] = $name;
		$result['group']['name_en'] = ScraperUtils::getFirstString($html_en,'<h1 class="h1-back-sen">','</h1>');
		$text_part = ScraperUtils::getFirstString($html,'<h1 class="h1-back-sen">','počet členů');
		$text_part_en = ScraperUtils::getFirstString($html_en,'<h1 class="h1-back-sen">','number of members');
		$blocks = ScraperUtils::returnSubstrings($text_part,'<div>','</div>');
		$blocks_en = ScraperUtils::returnSubstrings($text_part_en,'<div>','</div>');
		array_shift($blocks);
		array_shift($blocks_en);
		$m = 0;
		if (isset($blocks[0])) {
		  $i = 0;
		  foreach ((array) $blocks as $block) {
			$block = str_replace('&nbsp;',' ', $block);
			$blocks_en[$i] = str_replace('&nbsp;',' ', $blocks_en[$i]);
			$role = rtrim(rtrim(strip_tags(ScraperUtils::getFirstString($block,'<h3>','</h3>')),':'));
			$role_en = rtrim(rtrim(strip_tags(ScraperUtils::getFirstString($blocks_en[$i],'<h3>','</h3>')),':'));
			$lines = ScraperUtils::returnSubstrings($block,'<li class="bez">','</li>');
			foreach ((array) $lines as $line) {
				$source_code = ScraperUtils::getFirstString($line,'par_3=','"');
				$result['group']['mp']['mp_'.$source_code]['source_code'] = $source_code;
				$name = explode(' ',strip_tags($line));
				$result['group']['mp']['mp_'.$source_code]['first_name'] = $name[0];
				$result['group']['mp']['mp_'.$source_code]['last_name'] = end($name);
				$result['group']['mp']['mp_'.$source_code]['role'] = $role;
				$result['group']['mp']['mp_'.$source_code]['role_en'] = $role_en;
				$m++;
			}
			$i++;
		  }
		
		}
		//check number of members
		$number = ScraperUtils::getFirstString($html,'počet členů:&nbsp;<strong>','<strong>');
		if ($m == $number) {
		  $result['group']['number'] = $m;
		} else {
		  throw new Exception('Numbers of MPs are incorrect!',503);
		}
	} else {
	  $result['group']['number'] = 0;
	}
	return $result;
  }

  /**
  * scrape constituency
  * @param params['id']
  * example: 
  *     scrapeConstituency(array('id' => '8'));
  */
  private static function scrapeConstituency($params)
  { 
    //set date
    if (isset($params['date'])) {
   	 $d = $params['date'];
   	} else {
   	 $date_oo = new DateTime();
	 $d = $date_oo->format('d.m.Y');
	}
	//download the file
	$c = $params['id'];
	$url = "http://senat.cz/volby/hledani/o_obvodu.php?ke_dni={$d}&kod={$c}";
	$html = self::download($url);
	//parse
	$tmp = ScraperUtils::getFirstString($html,'selected="selected" value="'.$c.'">','</option>');
	$tmp_ar = explode('-',$tmp);
	$result['constituency']['name'] = trim($tmp_ar[1]);
	$result['constituency']['number'] = $c;
	$result['constituency']['description'] = strip_tags(trim(ScraperUtils::getFirstString($html,'<h3>Popis dle zákona 247/1995 Sb: </h3>','<h4>')));
	$text_part = ScraperUtils::getFirstString($html,'<h4>Části územního členění příslušné obvodu:</h4>','</ul>');
	$towns = ScraperUtils::returnSubstrings($text_part,'<li>','</li>');
	foreach ((array) $towns as $town) {
	  $result['constituency']['part'][] = $town;
	}
	return $result;
  }

	/**
	 * downloads the page and checks if downloaded a reasonable page
	 * @return html page
	 */
	private static function download($url, $lo_limit = 0, $curl_options = array())
	{
		$html = ScraperUtils::grabber($url,$curl_options);
		if ((strlen($html) < 7500) and strlen($html >= $lo_limit))
			throw new Exception('Downloaded file too short.', 503);
		return $html;
	}
}
?>
