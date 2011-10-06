<?php

/**
 * \ingroup fio
 *
 * Downloads and parses data from the fio websites
 */
 class Scraper {
   /**
   * Downloads and parses data from the fio websites
   *
   * 
   */
   public function read($params) {
     return self::scrape($params);
   }
   
   /**
   *
   */
   public function scrape($params) {
     $remote_resource = $params['remote_resource'];
     switch ($remote_resource) {
		case 'account': return self::scrapeAccount($params);
		case 'account_list': return self::scrapeAccountList($params);
		default:
			throw new Exception("Scraping of the remote resource <em>$remote_resource</em> is not implemented.", 400);
	 }
   }
   
   /**
   *
   */
   public function scrapeAccountList($params) {
      $csv = ScraperUtils::grabber("https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=csv&name=fio_bank_-_list_of_transparent_accounts&query=select%20*%20from%20swdata");
	  $array = self::parse_csv($csv);
	  return array('account' => $array);
   }
   
   /**
   *
   */
	public function scrapeAccount($params) {
    /*https://www.fio.cz/scgi-bin/hermes/dz-transparent.cgi?
     pohyby_DAT_od=5.9.2011
     &pohyby_DAT_do=
     &protiucet=
     &kod_banky=
     &VS=
     &UID=
     &PEN_typ_pohybu=
     &smer=
     &castka_min=
     &castka_max=
     &x=16
     &y=11
     &ID_ucet=2300049454
    */
    $url = "https://www.fio.cz/scgi-bin/hermes/dz-transparent.cgi?".
     'pohyby_DAT_od='.(isset($params['since']) ? $params['since'] : '') .
       (isset($params['pohyby_DAT_od']) ? $params['pohyby_DAT_od'] : '') .
     '&pohyby_DAT_do='.(isset($params['until']) ? $params['until'] : '') .
       (isset($params['pohyby_DAT_do']) ? $params['pohyby_DAT_do'] : '') .
     '&protiucet='.(isset($params['other_account']) ? $params['other_account'] : '') .
       (isset($params['protiucet']) ? $params['protiucet'] : '') .
     '&kod_banky='.(isset($params['other_account_code']) ? $params['other_account_code'] : '') .
       (isset($params['kod_banky']) ? $params['kod_banky'] : '') .
     '&VS='.(isset($params['vs']) ? $params['vs'] : '') .
       (isset($params['VS']) ? $params['VS'] : '') .
     '&UID='.(isset($params['user_identification']) ? $params['user_identification'] : '') .
       (isset($params['UID']) ? $params['UID'] : '') .
     '&PEN_typ_pohybu='.(isset($params['type']) ? $params['type'] : '') .
       (isset($params['PEN_typ_pohybu']) ? $params['PEN_typ_pohybu'] : '') .
     '&smer='.(isset($params['way']) ? $params['way'] : '') .
       (isset($params['smer']) ? $params['smer'] : '') .
     '&castka_min='.(isset($params['min']) ? $params['min'] : '') .
       (isset($params['castka_min']) ? $params['castka_min'] : '') .
     '&castka_max='.(isset($params['max']) ? $params['max'] : '') .
       (isset($params['castka_max']) ? $params['castka_max'] : '') .
	 '&ID_ucet='.(isset($params['account']) ? $params['account'] : '') .
       (isset($params['ID_ucet']) ? $params['ID_ucet'] : '');
    
      $options = array(
       array(CURLOPT_USERAGENT,'Greetings to FIO (-:'), //'Googlebot/2.1 (+http://www.google.com/bot.html)'
    );
	  $html = str_replace('&nbsp;',' ',iconv("cp1250", "UTF-8//TRANSLIT", ScraperUtils::grabber($url,$options)));
	
	  //get dom
      $dom = new simple_html_dom();
      $dom->load($html);
    
      //info = header
      $spans = $dom->find('span[class=main_header_row_text]');
      $out['info']['owner'] = str_replace('Majitel účtu: ','',$spans[2]->innertext);
      $out['info']['account_name'] = str_replace('Název účtu: ','',$spans[3]->innertext);
      $out['info']['currency'] = str_replace('Měna účtu: ','',$spans[4]->innertext);
     $out['info']['openning_date'] = Utils::dateToIso(str_replace('Datum založení účtu: ','',$spans[5]->innertext),'cs');
      $tmp = explode('-',str_replace('Období: ','',$spans[6]->innertext));
     $out['info']['since'] = Utils::dateToIso(trim($tmp[0]),'cs');
      $out['info']['until'] = Utils::dateToIso(trim($tmp[1]),'cs');
    
      $tables = $dom->find('table[class=table_prm]');
      if (count($tables) > 0) {
		$trs = $tables[0]->find('tr');
		$out['info']['value_since'] = str_replace(',','.',str_replace(' ','',str_replace($out['info']['currency'],'',$trs[1]->plaintext)));
		$trs = $tables[1]->find('tr');
		$out['info']['value_until'] = str_replace(',','.',str_replace(' ','',str_replace($out['info']['currency'],'',$trs[1]->plaintext)));
		
		//rows
		$trs = $tables[2]->find('tr');
		array_pop($trs);
		array_shift($trs);
		foreach ($trs as $tr) {
		  $tds = $tr->find('td');
		  $row = array(
		    'date' => Utils::dateToIso($tds[0]->innertext,'cs'),
		    'ammount' => str_replace(',','.',str_replace(' ','',$tds[1]->innertext)),
		    'type' => trim($tds[2]->innertext),
		    'ks' => trim($tds[3]->innertext),
		    'vs' => trim($tds[4]->innertext),
		    'ss' => trim($tds[5]->innertext),
		    'user_identification' => trim($tds[6]->innertext),
		    'message' => trim($tds[7]->innertext),  
		  );
		  $out['rows']['row'][] = $row;
		}
      }
	  return array('account' => $out); 
	}
	
	/**
* parse csv file
* the first row is considered a header!
* http://php.net/manual/en/function.str-getcsv.php (Rob 07-Nov-2008 04:54) + prev. note
* we cannot use str_getscv(), because of a problem with locale settings en_US / utf-8
* @param file csv string
* @param options options
* @return array(row => array(header1 => item1 ...
*/

  public static function parse_csv($file, $options = null) {
    $delimiter = empty($options['delimiter']) ? "," : $options['delimiter'];
    $to_object = empty($options['to_object']) ? false : true;
    $expr="/$delimiter(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/"; // added
    $str = $file;
    $lines = explode("\n", $str);
    $field_names = explode($delimiter, array_shift($lines));
    foreach ($lines as $line) {
        // Skip the empty line
        if (empty($line)) continue;
        $fields = preg_split($expr,trim($line)); // added
        $fields = preg_replace("/^\"(.*)\"$/s","$1",$fields); //added
        //$fields = explode($delimiter, $line);
        $_res = $to_object ? new stdClass : array();
        foreach ($field_names as $key => $f) {
        	if (isset($options['header_replace']) and $options['header_replace']) $f = str_replace(' ','_',$f);
            if ($to_object) {
                $_res->{$f} = $fields[$key];
            } else {
                $_res[$f] = $fields[$key];
            }
        }
        $res[] = $_res;
    }
    return $res;
  }
   
}

?>
