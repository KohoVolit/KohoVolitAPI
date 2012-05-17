<?php

/**
 * \ingroup data
 *
 */
class EntityInfo {

  public function read($params) {
  error_reporting(E_ALL);
    $query = new Query();
    
    if (!isset($params['entity']))
      throw new Exception("The API resource <em>EntityInfo</em> needs to have specified entity.", 403);
    
    //build query 
    
    //set up filter
  	if (isset($params['filter']))
  	  foreach ($params['filter'] as $key=>$f)
  	    //if possibly ambiguous, add to 0th (basic) entity
  	    if (in_array($key, $params['entity'][0]['columns']))
  	      $filter["\"{$params['entity'][0]['name']}\".\"{$key}\""] = $f;
  	    else
	      $filter[$key] = $f;
	else $filter = array();
	
	//datetime
	if (isset($params['_datetime'])) $datetime = $params['_datetime'];
	else $datetime = 'now';
	      
	//for each entity
    $i = 0;
    $t = 0;
    foreach ($params['entity'] as $entity) {
      $from[] = '"'.$entity['name'].'"' . ($i>0 ? " ON \"{$entity['name']}\".{$entity['on']}" : '');
      foreach ($entity['columns'] as $column) {
          $select[] = "\"{$entity['name']}\".\"{$column}\" as {$entity['name']}_{$column}";
          $table_columns[] = "\"{$entity['name']}\".\"{$column}\"";
          
          //translation
          if (isset($params['lang']) and (!in_array($column,$entity['pkey_columns'])) and (!isset($entity['no_translation']))) {
            $pkey = array();
            foreach ($entity['pkey_columns'] as $pc)
              $pkey[] = "\"{$entity['name']}_attribute_{$t}\".\"{$entity['name']}_{$pc}\" = \"{$entity['name']}\".\"{$pc}\"";
          
            $from[] = '"'.$entity['name'].'_attribute" AS ' . "\"{$entity['name']}_attribute_{$t}\" ON     \"{$entity['name']}_attribute_{$t}\".\"lang\" = '{$params['lang']}' AND
              \"{$entity['name']}_attribute_{$t}\".\"name\" = '{$column}' AND " . 
              "\"{$entity['name']}_attribute_{$t}\".\"since\" <= '{$datetime}' AND \"{$entity['name']}_attribute_{$t}\".\"until\" > '{$datetime}' AND " . 
              implode(' AND ', $pkey);
            
            $select[] = "\"{$entity['name']}_attribute_{$t}\".\"value\" as \"{$entity['name']}_{$column}_translated\"\n";
            $t++;
          }
          
      }
      if (isset($entity['filter']) and (!is_null($entity['filter'])))
          foreach ($entity['filter'] as $key=>$ef)
            $filter["\"{$entity['name']}\".\"{$key}\""] = $ef;
      $i++;
    }
    
    $query->setQuery("SELECT " . implode(',',$select) . " FROM " . implode("\n LEFT JOIN ", $from));

	//add filters

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
		//echo $query->getQuery() . "\n\n";
	//return query results
    return $query->execute();
  }
}
