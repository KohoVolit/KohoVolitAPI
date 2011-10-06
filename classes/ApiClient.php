<?php

/**
 * ...
 */
class ApiClient
{
	/// KohoVolit.eu project to access through this API client
	private $project;

	/// data format to receive data in
	private $format;

	/// list of allowed formats
	private static $allowed_formats = array('xml', 'json', 'php', 'csv');

	/// default search params - parameters to include to the query part of the URL for each request
	private $default_params;

	/// default data - parameters to include to the body of each request
	private $default_data;

	/**
	 * ...
	 */
	public function __construct($project = 'data', $format = 'php', $default_params = null, $default_data = null)
	{
		$this->project = $project;
		$this->format = $format;
		if (!in_array($format, self::$allowed_formats, true))
			throw new \InvalidArgumentException("Result of the API call is not available in the requested format <em>$format</em>.");
		$this->default_params = $default_params;
		$this->default_data = $default_data;
	}

	/**
	 * ...
	 */
	public function read($resource, $params = null)
	{
		$url = $this->makeUrl($resource, $params);
		$curl_options = array(CURLOPT_URL => $url);
		return $this->executeHttpRequest($curl_options);
	}

	/**
	 * ...
	 */
	public function readOne($resource, $params = null)
	{
		return $this->read($resource, array('_limit' => 1) + $params);
	}

	/**
	 * ...
	 */
	public function create($resource, $data = null)
	{
		$url = $this->makeUrl($resource);
		$request_body = $this->makeRequestBody($data);
		$curl_options = array(CURLOPT_URL => $url, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $request_body);
		return $this->executeHttpRequest($curl_options);
	}

	/**
	 * ...
	 */
	public function update($resource, $params = null, $data = null)
	{
		$url = $this->makeUrl($resource, $params);
		$request_body = $this->makeRequestBody($data);
		$curl_options = array(CURLOPT_URL => $url, CURLOPT_CUSTOMREQUEST => 'PUT', CURLOPT_POSTFIELDS => $request_body);
		return $this->executeHttpRequest($curl_options);
	}

	/**
	 * ...
	 */
	public function delete($resource, $params = null)
	{
		$url = $this->makeUrl($resource, $params);
		$curl_options = array(CURLOPT_URL => $url, CURLOPT_CUSTOMREQUEST => 'DELETE');
		return $this->executeHttpRequest($curl_options);
	}

	/**
	 * ...
	 */
	private function makeUrl($resource, $params = null)
	{
		$full_params = (array)$params + (array)$this->default_params;
		$url = API_DOMAIN . '/' . $this->project . '/' . $resource . '.' . $this->format . '?' . http_build_query(self::encodeNullValues($full_params), '', '&');
		return $url;
	}

	/**
	 * ...
	 */
	private function makeRequestBody($data)
	{
		$full_data = (array)$data + (array)$this->default_data;
		$request_body = http_build_query(self::encodeNullValues($full_data));
		return $request_body;
	}

	/**
	 *	...
	 * encode all null values as \N
	 */
	private static function encodeNullValues($array, $null_code = '\\N')
	{
		$result = array();
		foreach ($array as $key => $value)
			$result[$key] = (is_null($value)) ? $null_code : $value;
		return $result;
	}

	/**
	 * ...
	 */
	private function executeHttpRequest($curl_options)
	{
		$ch = curl_init();
		curl_setopt_array($ch, $curl_options);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($status_code != 200)
		{
			preg_match('/<p>(.*)<\/p>/us', $response, $matches);
			throw new \RuntimeException($matches[1]);
		}

		return $response;
	}
}

?>
