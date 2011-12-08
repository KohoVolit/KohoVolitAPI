<?php

/**
 * \file Scraper.php for transparent accounts in Fio bank
 *
 * Scrapes a particular account or the list of all transparent accounts in Fio bank
 */
 
 /**
 * \mainpage 
 * This project requires PHP RESTful API (KohoVolit.eu API).
 *
 * \section sec_1 Example 1 (generated table, can be used within <ifram>)
 * http://api.kohovolit.eu/fio/Table?account=2300049454&format=html&header=Trasparentn%C3%AD%20%C3%BA%C4%8Det%20K%C4%8D%7CStav:,value_until,K%C4%8D%7C%3Ca%20href=%27https%3A%2F%2Fwww.fio.cz%2Fscgi-bin%2Fhermes%2Fdz-transparent.cgi%3FID_ucet%3D2300049454%27%3E2300049454/2100%3C/a%3E|Na%C5%A1i%20posledn%C3%AD%20dono%C5%99i:&columns=user_identification,ammount%28%28round%29%29,K%C4%8D&rows=5&way=1&min=50&table_css=border-collapse:collapse;width:200px;height:200px;background-color:%23ffffff;font-family:sans-serif&since=1.1.2011&tbody_css=font-size:10px&thead_css=font-size:14px&row-even_css=background-color:%23D5E2EC&other_css=.column-2{text-align:right}
 *
 * \see Table::createTable and Scraper::scrapeAccount for details on parameters
 * 
 * \section sec_2 Example 2 (data about a particular transparent accounts)
 * http://api.kohovolit.eu/fio/Scraper?remote_resource=account&account=2300049454&since=1.1.2010&format=php
 *
 * \section sec_3 Example 3 (list of all transparent accounts)
 * http://api.kohovolit.eu/fio/Scraper?remote_resource=account_list&format=xml
 */

/**
* class Scraper
*/
 class Scraper {
   /**
   * Downloads and parses data from the fio websites. 
   * 
   * \param $params array of parameters
   * \return scrape($params)
   */
   public function read($params) {
     return self::scrape($params);
   }
   
   /**
   * Main function of the scraper
   * 
   * \param $params array of parameters
   * - remote_resource: account or account_list
   * - format: output format (raw, html, php, json, xml)
   *
   * all other parameters are described in scrapeAccount($params)
   * \return scraped data
   */
   public function scrape($params) {
     $remote_resource = $params['remote_resource'];
     switch ($remote_resource) {
		case 'account': return self::scrapeAccount($params);
		case 'account_list': return self::scrapeAccountList();
		default:
			throw new Exception("Scraping of the remote resource <em>$remote_resource</em> is not implemented.", 400);
	 }
   }
   
   /**
   * Scrapes account list from scraperwiki
   *
   * \return array of transparent accounts
   */
   public function scrapeAccountList() {
      $csv = ScraperUtils::grabber("https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=csv&name=fio_bank_-_list_of_transparent_accounts&query=select%20*%20from%20swdata");
	  $array = self::parse_csv($csv);
	  return array('account' => $array);
   }
   
   /**
   * scrapes an account directly from Fio bank
   * \ingroup fio
   * @param params array of parameters as described below
   * - remote_resource: account or account_list
   * - Original parameters:
   *   - ID_ucet: (required) number of the account 
   *   - pohyby_DAT_od: date since (Czech/Slovak format, e.g. 5.9.2011)
   *   - pohyby_DAT_do: date until (Czech/Slovak format, e.g. 5.10.2011)
   *   - protiucet: the other account
   *   - kod_banky: the other account's bank code
   *   - VS: 'variabilni symbol' = field 70/remittance information
   *   - UID: user identification field
   *   - PEN_typ_pohybu: type of item
   *   - smer: income:1, expenditure:-1
   *   - castka_min: minimal ammount
   *   - castka_max: maximal ammount
   * - New (English) parameters:
   *   - account: (required) number of the account 
   *   - since: since (Czech/Slovak format, e.g. 5.9.2011)
   *   - until: until (Czech/Slovak format, e.g. 5.10.2011)
   *   - other_account: the other account
   *   - other_account_code: the other account's bank code
   *   - vs: 'variabilni symbol' = field 70/remittance information
   *   - user_identification: user identification field
   *   - type: type of item
   *   - way: income:1, expenditure:-1
   *   - min: minimal ammount
   *   - max: maximal ammount
   * 
   * \return array of transactions (rows)
   */
	public function scrapeAccount($params) {
    /*
	https://www.fio.cz/scgi-bin/hermes/dz-transparent.cgi?
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
	  $html = str_replace('&nbsp;',' ',iconv("cp1250", "UTF-8//TRANSLIT//IGNORE", ScraperUtils::grabber($url,$options)));
	
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
      $out['info']['account'] = (isset($params['account']) ? $params['account'] : '') .
       (isset($params['ID_ucet']) ? $params['ID_ucet'] : '');
    
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
* Parses csv file
* 
* the first row is considered a header!
* 
* \see http://php.net/manual/en/function.str-getcsv.php (Rob 07-Nov-2008 04:54) + prev. note; 
*  we cannot use str_getscv(), because of a problem with locale settings en_US / utf-8
* @param file csv string
* @param options options
* @return array (row => array(header1 => item1 ...
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
