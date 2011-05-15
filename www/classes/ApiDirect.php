<?php

/**
 * ...
 */
class ApiDirect
{
	/// KohoVolit.eu project to access through this API client
	private $project;

	/// default search params - parameters to include for each function call
	private $default_params;

	/// default data - data to include for each function call
	private $default_data;

	/**
	 * ...
	 */
	public function __construct($project = 'kohovolit', $default_params = null, $default_data = null)
	{
		$this->project = $project;
		$this->default_params = $default_params;
		$this->default_data = $default_data;
	}

	/**
	 * ...
	 */
	public function read($function, $params = null)
	{
		$this->includeApiFunctionClass($function);
		$full_params = (array)$params + (array)$this->default_params;
		return $function::read($full_params);
	}

	/**
	 * ...
	 */
	public function create($function, $data = null)
	{
		$this->includeApiFunctionClass($function);
		$full_data = (array)$data + (array)$this->default_data;
		return $function::create($full_data);
	}

	/**
	 * ...
	 */
	public function update($function, $params = null, $data = null)
	{
		$this->includeApiFunctionClass($function);
		$full_params = (array)$params + (array)$this->default_params;
		$full_data = (array)$data + (array)$this->default_data;
		return $function::update($full_params, $full_data);
	}

	/**
	 * ...
	 */
	public function delete($function, $params = null)
	{
		$this->includeApiFunctionClass($function);
		$full_params = (array)$params + (array)$this->default_params;
		return $function::delete($full_params);
	}

	/**
	 * ...
	 */
	private function includeApiFunctionClass($function)
	{
		$api_path = 'd:/projekty/KohoVolit.eu/KVG4/api.kohovolit.eu/www';
		require_once  "$api_path/conf/settings.php";
		@include_once "$api_path/projects/{$this->project}/conf/settings.php";
		$ok = @include_once "projects/{$this->project}/api/$function.php";
		if (!$ok)
			throw new \Exception("There is no API function <em>$function</em>.", 404);
	}
}

?>
