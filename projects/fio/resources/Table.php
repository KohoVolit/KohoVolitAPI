<?php
/**
* \ingroup fio
*
* Generates table of last transactions in a particular account in Fio bank
*/
class Table {
  /// API client reference used for all API calls
  private $api;
  
  	/**
	 * Creates API client reference to use during the whole process.
	 */
	public function __construct()
	{
		$this->api = new ApiDirect('fio');

	}
	/**
	* Generates table of last transactions in a particular account in Fio bank
	*/
  public function read($params) {
  //return API_DIR;
    return self::createTable($params);
  }
  /**
  *
  */
  public function createTable($params) {
    //get the account info
    $src_params = $params;
    $src_params['format'] = 'php';
    $src_params['remote_resource'] = 'account';
    $source = $this->api->read('Scraper',$src_params);
    
    $table = new simple_html_dom();
    //there is a bug (http://sourceforge.net/tracker/?func=detail&aid=3399037&group_id=218559&atid=1044037) in simple_html_dom(), which prevents working with tbody/thead, so we use a workaround
    $table = str_get_html('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html><head></head><body><style></style><table id="table"><thead id="thead"></thead><tbody id="tbody"></tbody></table></body></html>');
    
    //css
    $css = '';
	if(isset($params['table_css'])) $css .= '#table{'.$params['table_css'].'} ';
	if(isset($params['thead_css'])) $css .= '#thead{'.$params['thead_css'].'} ';
	if(isset($params['tbody_css'])) $css .= '#tbody{'.$params['tbody_css'].'} ';
	if(isset($params['row_css'])) $css .= '.row{'.$params['row_css'].'} ';
	if(isset($params['hrow_css'])) $css .= '.hrow{'.$params['hrow_css'].'} ';
	if(isset($params['hcolumn_css'])) $css .= '.hcolumn{'.$params['hcolumn_css'].'} ';
	if(isset($params['column_css'])) $css .= '.column{'.$params['column_css'].'} ';
	if(isset($params['row-even_css'])) $css .= '.row-even{'.$params['row-even_css'].'} ';
	if(isset($params['row-odd_css'])) $css .= '.row-odd{'.$params['row-odd_css'].'} ';
	if(isset($params['other_css'])) $css .= $params['other_css'];
    
    $table->find('style',0)->innertext = $css;
    //table cells
    //example: columns=user_identification,ammount&rows=5
    if(isset($params['columns'])) {
      $col_ar = explode(',',$params['columns']);
      if (isset($params['rows']) and is_numeric($params['rows'])) $max_row = floor($params['rows']);
      else $max_row = -1;
      $i = 0;
      $src = $source['account'];
      if ((isset($src['rows'])) and (count($source['account']['rows']['row']) > 0)) {
        $tbody_html = '';
        //rows
        foreach ($source['account']['rows']['row'] as $source_row) {
          if (($i >= $max_row) and ($i != -1)) break;
          $row_html = "<tr class='row row-" . ((($i % 2) == 0) ? 'even' : 'odd') ."'>";
          $j = 1;
          //columns
          foreach($col_ar as $column) {
            $row_html .= "<td class='column column-" . $j . "'>";
            if (strpos($column,'((round))')) {
              $column = str_replace('((round))','',$column);
              $round = true;
            } else
              $round = false;
            if (isset($source_row[$column])) {
              if ($round) $row_html .= round($source_row[$column],0);
              else $row_html .= $source_row[$column];
            }
            else $row_html .= $column;
            $row_html .= "</td>";
            $j++;
          }
          $row_html .= "</tr>";
          $tbody_html .= $row_html;
          $i++;
        }
        $table->find('[id=tbody]',0)->innertext = $tbody_html;
      }
    }
    
    //header
    if (isset($params['header'])) {
      $header = '';
      $header_rows = explode('|',$params['header']);
      foreach ($header_rows as $h => $header_row) {
        $header .= "<tr class='hrow hrow-" . $h . "'>";
        $hr_ar = explode(',',$header_row);
        $j = 1;
        foreach ($hr_ar as $cell) {
          $header .= "<td class='hcolumn hcolumn-". $j . "'" . ((count($hr_ar) == 1) ? ' colspan="'.count($col_ar).'"' : '') . ">";
          if (isset($source['account']['info'][$cell])) $header .= $source['account']['info'][$cell];
          else $header .= $cell;
          $header .= '</td>';
          $j++;
        }
        $header .= '</tr>'; 
      }
      $table->find('[id=thead]',0)->innertext = $header;
    }
    
    return $table;
  }
}

?>
