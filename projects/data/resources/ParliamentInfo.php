<?php

/**
 * \ingroup data
 *
 */
class ParliamentInfo
{

	/**
	 * Read the info about parliament
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the countries to select.
	 *
	 * \return An array of countries that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('code' => 'cz'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [code] => cz
	 *             [name] => Czech republic
	 *             [short_name] => Czechia
	 *             [description] => 
	 *         ) 
	 * 
	 * )
	 * \endcode
	 */
	public function read($params)
	{
		$query = new Query();
		$query->setQuery("
			SELECT p.code as parliament_code, p.name as parliament_name,p.short_name as parliament_short_name, p.description as parliament_description,
			
			pk.code as parliament_kind_code,pk.name as parliament_kind_name,pk.short_name as parliament_kind_short_name, pk.description as parliament_kind_description,
			c.code as country_code, c.name as country_name, c.short_name as country_short_name, c.description as country_description

			FROM parliament as p
			LEFT JOIN parliament_kind as pk ON p.parliament_kind_code = pk.code
			LEFT JOIN country as c ON c.code = p.country_code
		");
		
		//addWhereCondition
		$table_columns = array( 
			'p.code','p.name','p.short_name','p.description',
			'pk.code','pk.name','pk.short_name','pk.description',
			'c.code','c.name','c.short_name','c.description',
		);		
		$filter = $params;
		if (count($filter) > 0) {
		  foreach ($filter as $key=>$f) {
			if (strpos($key,'parliament_kind_') !== false) {
			  $new_key = str_replace('parliament_kind_','pk.',$key);
			  $filter[$new_key] = $filter[$key];
			  unset($filter[$key]);		
			}
			if (strpos($key,'country_') !== false) {
			  $new_key = str_replace('parliament_kind_','c.',$key);
			  $filter[$new_key] = $filter[$key];
			  unset($filter[$key]);		
			}
			if (strpos($key,'parliament_') !== false) {
			  $new_key = str_replace('parliament_','p.',$key);
			  $filter[$new_key] = $filter[$key];
			  unset($filter[$key]);		
			}
			if ($key == 'code') {
			  $new_key = 'p.code';
			  $filter[$new_key] = $filter[$key];
			  unset($filter[$key]);		
			} 
		  }
		}
		
		$query->addWhereCondition($filter, $table_columns);
		if (isset($filter['_order']))
		  $query->addOrderBy($filter['_order'], $table_columns);
		if (isset($filter['_limit']))
		{
			$query->appendParam($filter['_limit']);
			$query->setQuery($query->getQuery() . ' limit $' . $query->getParamsCount());
		}
		if (isset($filter['_offset']))
		{
			$query->appendParam($filter['_offset']);
			$query->setQuery($query->getQuery() . ' offset $' . $query->getParamsCount());
		}

		return $query->execute();
	}
}

?>
