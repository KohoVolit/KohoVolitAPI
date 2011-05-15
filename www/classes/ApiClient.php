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

	/// MIME type respective to the data format
	private $mime_type;
	
	/// list of allowed formats and their mapping to the MIME types
	private static $allowed_formats = array(
		'php' => 'text/plain',
		'json' => 'application/json',
		'csv' => 'text/csv',
		'xml' => 'text/xml'
	);
	
	/// default search params - parameters to include to the query part of the URL for each request
	private $default_params;

	/// default data - parameters to include to the body of each request
	private $default_data;

	/**
	 * ...
	 */
	public function __construct($project = 'kohovolit', $format = 'php', $default_params = null, $default_data = null)
	{
		$this->project = $project;
		$this->format = $format;
		if (array_key_exists($format, self::$allowed_formats))
			$this->mime_type = self::$allowed_formats[$format];
		else
			throw new \InvalidArgumentException("Result of the API call is not available in the requested format <em>$format</em>.");

		$this->default_params = $default_params;
		$this->default_data = $default_data;
	}

	/**
	 * ...
	 */
	public function read($function, $params = null)
	{
		$url = $this->makeUrl($function, $params);
		$curl_options = array(CURLOPT_URL => $url);
		return $this->executeHttpRequest($curl_options);
	}
	
	/**
	 * ...
	 */
	public function create($function, $data = null)
	{
		$url = $this->makeUrl($function);
		$request_body = $this->makeRequestBody($data);
		$curl_options = array(CURLOPT_URL => $url, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $request_body);
		return $this->executeHttpRequest($curl_options);
	}

	/**
	 * ...
	 */
	public function update($function, $params = null, $data = null)
	{
		$url = $this->makeUrl($function, $params);
		$request_body = $this->makeRequestBody($data);
		$curl_options = array(CURLOPT_URL => $url, CURLOPT_CUSTOMREQUEST => 'PUT', CURLOPT_POSTFIELDS => $request_body);
		return $this->executeHttpRequest($curl_options);
	}

	/**
	 * ...
	 */
	public function delete($function, $params = null)
	{
		$url = $this->makeUrl($function, $params);
		$curl_options = array(CURLOPT_URL => $url, CURLOPT_CUSTOMREQUEST => 'DELETE');
		return $this->executeHttpRequest($curl_options);
	}

	/**
	 * ...
	 */
	private function makeUrl($function, $params = null)
	{
		$full_params = (array)$params + (array)$this->default_params;
		$url = "http://api.kohovolit.eu/{$this->project}/$function?" . http_build_query(self::encodeNullValues($full_params), '', '&');
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
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: ' . $this->mime_type));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if ($status_code != 200)
		{
			preg_match('/<p>(.*)<\/p>/us', $response, $matches);
			throw new \RuntimeException($matches[1]);
		}

		if ($this->format == 'php')
			$response = unserialize($response);

		return $response;
	}
}

?>
