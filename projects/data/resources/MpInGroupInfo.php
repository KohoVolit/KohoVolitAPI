<?php

/**
 * \ingroup data
 *
 */
class MpInGroupInfo
{

	/**
	 * Read the info about mp in group
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

		if (!isset($params['mp_id']) and !isset($params['group_id'])) 
		  throw new Exception("The API resource <em>MpInGroupInfo</em> needs to have specified mp_id or/and group_id.", 403);
		
		$query->setQuery("
		SELECT 
			m.*,
			mig.*,
			g.name as group_name,g.short_name as group_short_name,g.subgroup_of as group_subgroup_of,
			c.name as constituency_name,c.short_name as constituency_short_name, c.description as constituency_description,
			r.male_name as role_male_name, r.female_name as role_female_name,
			p.code as parliament_code, p.name as parliament_name, p.short_name as parliament_short_name, p.description as parliament_description,
			p.time_zone as parliament_time_zone,
			gk.code as group_kind_code, gk.name as group_kind_name, gk.short_name as group_kind_short_name, gk.description as group_kind_description,
			t.id as term_id, t.name as term_name, t.short_name as term_short_name, t.description as term_description,t.since as term_since,t.until as term_until,
			co.code as country_code, co.name as country_name, co.short_name as country_short_name, co.description as country_description

		FROM mp as m
			LEFT JOIN mp_in_group as mig ON m.id=mig.mp_id
			LEFT JOIN \"group\" as g ON g.id=mig.group_id
			LEFT JOIN constituency as c ON mig.constituency_id = c.id
			LEFT JOIN \"role\" as r ON mig.role_code = r.code
			LEFT JOIN parliament as p ON p.code = g.parliament_code
			LEFT JOIN group_kind as gk ON gk.code = g.group_kind_code
			LEFT JOIN term as t ON t.id = g.term_id
			LEFT JOIN country as co ON co.code = p.country_code
		");
		
		//addWhereCondition
		$table_columns = array( 
			'first_name','middle_names','last_name','disambiguation','sex','pre_title','post_title', 'born_on','died_on',
			'mp_id','group_id','role_code','party_id','constituency_id','since','until',
			'group_name','group_short_name','group_subgroup_of',
			'constituency_name','constituency_short_name','constituency_description',
			'role_male_name','role_female_name',
			'parliament_code','parliament_name','parliament_short_name','parliament_description',
			'group_kind_code','group_kind_name','group_kind_short_name','group_kind_description',
			'term_id','term_name','term_short_name','term_description','term_since','term_until',
			'country_code','country_name','country_short_name','country_description',
		);		
		$filter = $params;		
		$query->addWhereCondition($filter, $table_columns);
		if (isset($filter['_order']))
		  $query->addOrderBy($filter['_order'], $table_columns);
		if (isset($filter['_limit']))
		{
			$query->params[] = $filter['_limit'];
			$query->query .= ' limit $' . count($this->params);
		}
		if (isset($filter['_offset']))
		{
			$query->params[] = $filter['_offset'];
			$query->query .= ' offset $' . count($this->params);
		}
		return $query->execute();
	}
}

?>
