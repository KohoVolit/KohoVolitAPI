<?php

/**
 * This class downloads and parses data from given remote resources for Parliament of the Czech republic - Chamber of deputies.
 */
class ScraperCzPsp
{
	/**
	 * Downloads and parses data from a given remote resource.
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the remote resource to scrape. The resource is specified by a \e remote_resource parameter.
	 *
	 * \return An array of data parsed from the remote resource.
	 */
	public static function scrape($params)
	{
		$remote_resource = $params['remote_resource'];
		switch ($remote_resource)
		{
			case 'current_term': return self::scrapeCurrentTerm($params);
			case 'term_list': return self::scrapeTermList($params);
			case 'constituency_list': return self::scrapeConstituencyList($params);
			case 'mp': return self::scrapeMp($params);
			case 'group': return self::scrapeGroup($params);
			case 'geocode': return self::scrapeGeocode($params);
			default:
				throw new Exception("Scraping of the remote resource <em>$remote_resource</em> is not implemented for parliament <em>{$params['parliament']}</em>.", 400);
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
	* example: Scraper?parliament=cz/psp&remote_resource=geocode&address=Plasy
	*/
	public static function scrapeGeocode($params)
	{
		$lat = '';
		$lng = '';
		//download
		$url = 'http://maps.googleapis.com/maps/api/geocode/json?region=cz&language=cs&sensor=false&address=' . urlencode($params['address']);
		//geocode
		$geo_object = json_decode(file_get_contents($url));
		//check if ok
		if ($geo_object->status == 'OK')
		{
			$lat = $geo_object->results[0]->geometry->location->lat;
			$lng = $geo_object->results[0]->geometry->location->lng;
			$ok = true;
		}
		else
			$ok = false;
		return array('coordinates' => array('lat' => $lat, 'lng' => $lng,'ok' => $ok));
	}

	/**
	 * ...
	 */
	private static function scrapeCurrentTerm($params)
	{
		$html = self::download('http://www.psp.cz/sqw/hp.sqw');
		$out['id'] = ScraperUtils::getFirstString($html, '"ischuze.sqw?o=', '&');
		self::appendHtml($params, $out, $html);
		return array('term' => $out);
	}

	/**
	 * ...
	 */
	private static function scrapeTermList($params)
	{
		$out = array(
			array('id' => '1', 'name' => '1992 - 1996', 'since' => '1992-06-06', 'until' => '1996-06-06'),
			array('id' => '2', 'name' => '1996 - 1998', 'since' => '1996-06-01', 'until' => '1998-06-19'),
			array('id' => '3', 'name' => '1998 - 2002', 'since' => '1998-06-20', 'until' => '2002-06-20'),
			array('id' => '4', 'name' => '2002 - 2006', 'since' => '2002-06-15', 'until' => '2006-06-15'),
			array('id' => '5', 'name' => '2006 - 2010', 'since' => '2006-06-03', 'until' => '2010-06-03'),
			array('id' => '6', 'name' => 'od 2010', 'since' => '2010-05-29'),
		);
		return array('term' => $out);
	}

	/**
	 * ...
	 */
	private static function scrapeConstituencyList($params)
	{
		$term_id = self::getTermId($params);
		$html = self::download("http://www.psp.cz/sqw/organy2.sqw?kr=1&o={$term_id}");
		$kraje = ScraperUtils::returnSubstrings($html, '<li><A HREF=', '</li>');
		$out = array();
		foreach ($kraje as $kraj)
		{
			preg_match('/id=([0-9]*).*>(.*)<.*\((.*)\)/us', $kraj, $matches);
			$out[] = array('id' => $matches[1], 'name' => $matches[2], 'mp_count' => $matches[3]);
		}

		self::appendHtml($params, $out, $html);
		return array('constituency' => $out);
	}

	/**
	 * ...
	 */
	private static function scrapeMp($params)
	{
		if (!isset($params['id']) || empty($params['id'])) return array('mp' => null);
		$mp_id = $params['id'];
		$term_id = self::getTermId($params);

		$html = self::download("http://www.psp.cz/sqw/detail.sqw?id={$mp_id}&t=1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,41,42,45,77,78,83,84&o={$term_id}");
		$out['id'] = $mp_id;
		$out['term_id'] = $term_id;

		// pole udaju o poslanci
		$name_full = str_replace('&nbsp;', ' ', ScraperUtils::getFirstString($html, "<h2>", "</h2>"));
		$out += ScraperUtils::tokenizeName($name_full);

		//poslanec vs. poslankyne
		if (strpos($html, 'Narozena') > 0)
		{
			$out['sex'] = 'f';
			$datum_narozeni_cs = ScraperUtils::getFirstString($html, "Narozena: ", "<br />");
			$died_on_cs = ScraperUtils::getFirstString($html, "Zemřela: ", "<br />");
		}
		else
		{
			$out['sex'] = 'm';
			$datum_narozeni_cs = ScraperUtils::getFirstString($html, "Narozen: ", "<br />");
			$died_on_cs = ScraperUtils::getFirstString($html, "Zemřel: ", "<br />");
		}

		// datum narozeni
		$out['born_on'] = Utils::dateToIso($datum_narozeni_cs, 'cs');

		// died on
		$out['died_on'] = Utils::dateToIso($died_on_cs, 'cs');

		// obrazek
		$img = ScraperUtils::getFirstString($html, '<img src="/forms/tmp_sqw/', '"');
		if (!empty($img))
			$out['image_url'] = 'http://www.psp.cz/forms/tmp_sqw/' . $img;

		// kraj
		$out['constituency'] = trim(ScraperUtils::getFirstString($html, "Volební kraj:", "<br />"));

		// asistenti
		$assistants = ScraperUtils::getFirstString($html, "Asistent:", "</dl>");
		$a_ar = ScraperUtils::returnSubstrings($assistants, '<dd>', '</dd>');
		$j = 0;
		foreach ($a_ar as $row)
			$out['assistant'][$j++] = str_replace('&nbsp;', ' ', $row);

		// adresy
		$kancelar_full = str_replace('&nbsp;', ' ', ScraperUtils::getFirstString($html, 'Kancelář: ', '<p>'));
		$office_ar = explode('<br /><br />', $kancelar_full);
		$j = 1;
		foreach ($office_ar as $off)
		{
			if (!empty($kancelar_full))
			{
				if ($j == 1 || substr($off, 0, 6) == 'Kancel')
				{
					$out['office'][$j]['address'] = ScraperUtils::getFirstString($off, '<b>', '</b>');
					$out['office'][$j]['phone'] = ScraperUtils::getFirstString($off, 'tel.: <b>', '</b>');
					$out['office'][$j]['fax'] = ScraperUtils::getFirstString($off, 'fax: <b>', '</b>');
					$pattern = '/, *([0-9]{3} [0-9]{2})? *(\S.+)/';
					preg_match($pattern, $out['office'][$j]['address'], $matches);
					if (isset($matches[1]))
						$out['office'][$j]['postcode'] = $matches[1];
					if (isset($matches[2]))
						$out['office'][$j]['city'] = trim($matches[2]);
					$j++;
				}
			}
		}

		// e-mail
			//mailto:andrysoval@psp.cz">Můžete mi napsat
		$tmp_pos_last = strpos($html, '">Můžete mi napsat');
		if ($tmp_pos_last) {
		  $tmp_pos_first = strrpos(substr($html,0,$tmp_pos_last),'mailto:')+strlen('mailto:');
		  $out['email'] = substr($html, $tmp_pos_first, $tmp_pos_last - $tmp_pos_first);
		} else 
		  $out['email'] = '';
		//trim(ScraperUtils::getFirstString($html, 'mailto:','">'));	//wrong, because some MPs have their assistant's email on the page

		// www
		$out['website'] = trim(ScraperUtils::getFirstString($html, 'href="http://','">Další informace (vlastní stránka)'), '/');

		// clenstva poslance
		if (isset($params['list_memberships']))
		{
			// typy skupin (group kind)
			$typy = ScraperUtils::returnSubstrings($html,">o</a></tt>",'<');
			$typy_position = array();
			$j = 0;
			foreach ($typy as $typ)
			{
				$typy_position[] = strpos($html, $typ, ($j > 0 ? $typy_position[$j-1] : 0) + 1);
				$j++;
			}

			// pro kazdy group_kind
			$group_kinds = array(
				'Parlament' => 'parliament',
				'Výbor' => 'committee',
				'Podvýbor' => 'subcommittee',
				'Komise' => 'commission',
				'Delegace' => 'delegation',
				'Klub' => 'political group',
				'Meziparlamentní skupina vrámci MPU' => 'friendship group',
				'Pracovní skupina' => 'working group',
				'Vláda' => 'government',
				'Instituce' => 'institution',
				'Mezinárodní organizace' => 'international organization',
				'Evropský parlament' => 'european parliament',
				'Prezident' => 'president'
			);
			$i = 0;  // group_kind
			foreach($typy as $typ)  // typ = Parlament, Vybor, Komise, ...
			{
				$group_kind_name = trim(str_replace('&nbsp;', '', $typ));
				$group_kind_code = $group_kinds[$group_kind_name];

				// rozlisim, zda jde o posledni group_kind nebo ne (jine rozpoznani ukonceni)
				if (isset($typy_position[$i+1]))  // neni posledni group_kind jednoho poslance
					$group_ar = ScraperUtils::returnSubstrings(substr($html, $typy_position[$i], $typy_position[$i+1] - $typy_position[$i]), '</tt>', 'br />');
				else  // je posledni
					$group_ar = ScraperUtils::returnSubstrings(substr($html, $typy_position[$i]), '</tt>', 'br />');

				foreach ($group_ar as $group_full)  // group = Poslanecka snemovna, Vybor pro xxx, Vybor pro yyy,...
				{
					$group_id = ScraperUtils::getFirstString($group_full, 'id=', '&');
					if (empty($group_id))
						$group_id = 0;
					if (strpos($group_full, 'org='))
						$group_id = ScraperUtils::getFirstString($group_full, 'org=', '">');
					$group = array();
					$group['id'] = $group_id;
					$group['kind'] = $group_kind_code;

					$group_full = str_replace('&nbsp;', ' ', $group_full);
					$pattern1 = '/od ([0-9]{1,2}. [0-9]{1,2}. [0-9]{4})/';
					$pattern2 = '/do ([0-9]{1,2}. [0-9]{1,2}. [0-9]{4})/';
					preg_match($pattern1, $group_full, $matches1);
					preg_match($pattern2, $group_full, $matches2);
					$group['since'] = Utils::dateToIso($matches1[1], 'cs');
					if (isset($matches2[1]))
						$group['until'] = Utils::dateToIso($matches2[1], 'cs');

					$pom = ScraperUtils::getFirstString($group_full, '>,', $matches1[0]);
					$pom = trim($pom);
					if (empty($pom))  // nema odkaz
					{
						$pom = ScraperUtils::getFirstString($group_full, ',', $matches1[0]);
						$pom = explode(',', $pom);
						$pom = end($pom);
					}
					$group['role'] = $pom;
					$st = trim(strip_tags($group_full));
					$pom = strpos($st, $group['role']);
					$group['name'] = rtrim(substr($st, 0, $pom - 1), ', ');
					$group['role'] = trim($group['role']);
					$out['group'][] = $group;
				}
				$i++;
			}
		}

		self::appendHtml($params, $out, $html);
		return array('mp' => $out);
	}

	/**
	 * ...
	 */
	private static function scrapeGroup($params)
	{
		$term_id = self::getTermId($params);
		$active = isset($params['active']);
		$t_bit = !$active ? '&o=' . $term_id : '';
		$a_bit = isset($params['id']) ? 'id=' . $params['id'] : 'P1=0&P2=0';
		$url = 'http://www.psp.cz' . (isset($params['language']) && $params['language'] == 'en' ? '/cgi-bin/eng' : '') . '/sqw/snem.sqw?' . $a_bit . $t_bit;
		$html = self::download($url);  // 591, o=5 - whole term,  otherwise active only
		if (isset($params['id']))
			$out['id'] = $params['id'];
		$out['term_id'] = $term_id;
		$out['active'] = $active ? 'true' : 'false';

		// name
		$name_full = ScraperUtils::getFirstString($html, '<h2>', '</h2>');
		preg_match('/([^<]+)(<br \/>)?(.+)?/u', $name_full, $matches);
		if (!empty($matches[3]))
		{
			// a group with parent group
			$out['name'] = trim(str_replace('&nbsp;', ' ', $matches[3]));
			$out['parent_name'] = trim(str_replace('&nbsp;', ' ', $matches[1]));
		}
		else
		{
			// a group without parent group
			$out['name'] = trim(str_replace('&nbsp;', ' ', $matches[1]));
			$out['short_name'] = trim(ScraperUtils::getFirstString($html, 'title="' . trim($matches[1]) . '">', '</a>'));
		}

		// group members
		if (isset($params['list_members']))
		{
			$group_ar = ScraperUtils::returnSubstrings($html, '<tr>', '</tr>');
			foreach ($group_ar as $row)
			{
				$r_ar = str_replace('&nbsp;', ' ', ScraperUtils::returnSubstrings($row, '<td', '/td>'));
				preg_match('/ - viz /u', $r_ar[1], $matches);	// preskoc odkazy na poslankyne, co zmenili meno
				if (!empty($matches)) continue;
				preg_match('/id=([0-9]+)[^>]*">(\S+) ([^<]+)<\/a>(, *[^<]+)?\.?</u', $r_ar[1], $matches);
				$mp_id = $matches[1];
				$mp = array();
				$mp['id'] = $mp_id;
				$pre_title = trim(ScraperUtils::getFirstString($r_ar[0],'>','<'));
				$mp['pre_title'] = $pre_title;
				$mp['first_name'] = $matches[2];
				$last_name_ar = explode(' ', $matches[3]);
				$mp['last_name'] = $last_name_ar[0];
				$mp['disambiguation'] = (isset($last_name_ar[1])) ? rtrim($last_name_ar[1], '.') : '';
				$mp['post_title'] = (isset($matches[4])) ? ltrim($matches[4], ', ') : '';

				// constituencies
				$id_pattern = '/id=([0-9]+)/';
				preg_match($id_pattern, $r_ar[3], $matches);
				$group_id = $matches[1];
				$mp['group'][] = array(
					'id' => $group_id,
					'kind' => 'constituency',
					'name' => ScraperUtils::getFirstString($r_ar[3],'">','<')
				);

				// political groups
				$pom = explode('>,', $r_ar[5]);
				foreach($pom as $p)
				{
					preg_match($id_pattern, $p, $matches);
					if (isset($matches[1]))
					{
						$group_id = $matches[1];
						$mp['group'][] = array(
							'id' => $group_id,
							'kind' => 'group',
							'name' => str_replace('&nbsp;', ' ', ScraperUtils::getFirstString($p, 'title="', '"')),
							'short_name' => ScraperUtils::getFirstString($p, '">', '<')
						);
					}
				}

				//committees
				$pom = explode('>,', $r_ar[7]);
				foreach($pom as $p)
				{
					preg_match($id_pattern, $p, $matches);
					if (isset($matches[1]))
					{
						$group_id = $matches[1];
						$mp['group'][] = array(
							'id' => $group_id,
							'kind' => 'committee',
							'name' => str_replace('&nbsp;',' ', ScraperUtils::getFirstString($p, 'title="', '"')),
							'short_name' => ScraperUtils::getFirstString($p, '">', '<')
						);
					}
				}

				//commissions
				$pom = explode('>, ', $r_ar[9]);
				foreach($pom as $p)
				{
					preg_match($id_pattern, $p, $matches);
					if (isset($matches[1]))
					{
						$group_id = $matches[1];
						$mp['group'][] = array(
							'id' => $group_id,
							'kind' => 'commission',
							'name' => str_replace('&nbsp;', ' ', ScraperUtils::getFirstString($p, 'title="', '"')),
							'short_name' => ScraperUtils::getFirstString($p, '">', '<')
						);
					}
				}

				//delegations
				$pom = explode('>,', $r_ar[11]);
				foreach($pom as $p)
				{
					preg_match($id_pattern, $p, $matches);
					if (isset($matches[1]))
					{
						$group_id = $matches[1];
						$mp['group'][] = array(
							'id' => $group_id,
							'kind' => 'delegation',
							'name' => str_replace('&nbsp;', ' ', ScraperUtils::getFirstString($p, 'title="', '"')),
							'short_name' => ScraperUtils::getFirstString($p, '">', '<')
						);
					}
				}
				$out['mp'][] = $mp;
			}
		}

		//sub committee etc.
		if (isset($params['list_children']))
		{
			$html = self::download("http://www.psp.cz/sqw/fsnem.sqw?{$a_bit}{$t_bit}");  // 591, o=5 - whole term,  otherwise active only
			preg_match_all('/fsnem.sqw\?id=([0-9]+)/', $html, $matches);
			$j = 0;
			if (isset($matches[1]))
				foreach($matches[1] as $id)
					if ($id != $out['group_id'])
						$out['child'][$j++]['id'] = $id;
		}

		self::appendHtml($params, $out, $html);
		return array('group' => $out);
	}

	private static function download($url)
	{
		$page = file_get_contents($url);
		if (strlen($page) < 1000)
			throw new Exception('The file from psp.cz was not downloaded well. Is not around 3 in the morning CET? The psp.cz is being mainteined at that time... (file too short)', 503);
		return iconv("cp1250", "UTF-8//TRANSLIT", $page);
	}

	private static function appendHtml($params, &$out, $html)
	{
		if (isset($params['original_html']))
			$out['original_html'] = $html;
	}

	private static function getTermId($params)
	{
		if (!empty($params['term']))
			return $params['term'];
		else
		{
			$term_ar = self::scrapeCurrentTerm($params);
			return $term_ar['term']['id'];
		}
	}
}

?>
