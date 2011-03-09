<?php

/**
 * ...
 */ 
class ApiLocalClient
{
	public function __construct($parliament = '', $language = '-')
	{
		$this->parliament = $parliament;
		$this->language = $language;
	}
	
	/**
	 * ...
	 */ 
	public function get($function, $params = null)
	{
		$specific_function = "api/$this->parliament/$function.php";
		$general_function = "api/$function.php";

		if (file_exists($specific_function)
			include $specific_function;
		else
			include $general_function;

		$params['parliament'] = $this->parliament;
		$params['language'] = $this->language;
		$params['function'] = $function;

		return $function($params);
	}

	/// parliament to get data from
	private $parliament;

	/// preferred language of the returned data
	private $language;		
}

?>
