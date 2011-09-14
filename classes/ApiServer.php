<?php

/**
 * ...
 */
class ApiServer
{
	private $put_request_data;

	/**
	 * ...
	 */
	public function processHttpRequest()
	{
		$request_method = strtoupper($_SERVER['REQUEST_METHOD']);

		// data of PUT request can be read only once, store them for multiple use
		if ($request_method == 'PUT')
			parse_str(file_get_contents('php://input'), $this->put_request_data);

		$this->logRequest();

		// include specific project settings if they are present
		$project = $_GET['project'];
		@include_once API_ROOT . "/projects/$project/config/settings.php";
		@include_once API_ROOT . "/projects/$project/setup.php";

		// block access to the API resources that are private
		$resource = $_GET['resource'];
		if (isset($private_resources) && in_array($resource, $private_resources, true))
			throw new Exception("The API resource <em>$resource</em> is not accessible from remote.", 403);

		// include the underlying class of the requested API resource
		$ok = @include API_ROOT . "/projects/$project/resources/$resource.php";
		if (!$ok)
			throw new Exception("There is no API resource <em>$resource</em> in project <em>$project</em>.", 404);
		$resource_class = new $resource;

		// get the search criteria for the record to work with
		$params = self::decodeNullValues($_GET);

		// call the proper method of the API resource class depending on the HTTP request method
		switch ($request_method)
		{
			case 'GET':
				if (method_exists($resource_class, 'read'))
				{
					$result = $resource_class->read($params);
					if (isset($params['#limit']) && $params['#limit'] == 1 && !empty($result))
						$result = current($result);
					return array($resource => $result);
				}
				break;
/*
The public API access is read-only.
Data modifying request methods are not allowed from remote, on localhost use ApiDirect class instead.
			case 'POST':
				if (method_exists($resource_class, 'create'))
					return array($resource => $resource_class->create(self::decodeNullValues($_POST)));
				break;

			case 'PUT':
				if (method_exists($resource_class, 'update'))
					return array($resource => $resource_class->update($params, self::decodeNullValues($this->put_request_data)));
				break;

			case 'DELETE':
				if (method_exists($resource_class, 'delete'))
					return array($resource => $resource_class->delete($params));
				break;
*/
		}

		throw new Exception("The API resource <em>$resource</em> does not accept " . $_SERVER['REQUEST_METHOD'] . ' requests.', 405);
	}


	/**
	 * ...
	 */
	public function sendHttpResponse($status_code, $data)
	{
		// in case of successfull API request, format the result according to requested format
		if ($status_code == 200)
		{
			$format = (!empty($_GET['format'])) ? $_GET['format'] : 'xml';
			switch ($format)
			{
				case 'php':
					$header = 'Content-Type: text/plain; charset=UTF-8';
					$body = serialize(current($data));
					break;

				case 'json':
					$header = 'Content-Type: application/json';
					$body = json_encode(current($data), JSON_FORCE_OBJECT);
					break;

				case 'csv':
					$header = 'Content-Type: text/csv; charset=UTF-8';
					$body = Utils::arrayToCsv(current($data));
					break;

				case 'xml':
					$header = 'Content-Type: text/xml; charset=UTF-8';
					$body = Utils::arrayToXml($data);
					break;

				default:
					$status_code = 406;
					$data = "Result of the API call is not available in the requested format <em>$format</em>.";
			}
		}

		// if the API request failed, make an error page
		if ($status_code != 200)
		{
			$header = 'Content-type: text/html; charset=UTF-8';
			$body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
				<html>
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
						<title>' . $status_code . ' ' . self::getHttpStatusCodeMessage($status_code) . '</title>
					</head>
					<body>
						<h1>' . self::getHttpStatusCodeMessage($status_code) . '</h1>
						<p>' . $data . '</p>
						<hr />
						' . $_SERVER['SERVER_SIGNATURE'] . '
					</body>
				</html>';
		}

		// send actual HTTP headers and body
		header('HTTP/1.1 ' . $status_code . ' ' . self::getHttpStatusCodeMessage($status_code));
		header($header);
		echo $body;
	}


	private static function getHttpStatusCodeMessage($status_code)
	{
		$message = array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);

		return $message[$status_code];
	}

	/**
	 * Logs current API call.
	 */
	private function logRequest()
	{
		$p1 = strpos($_SERVER['REQUEST_URI'], '/', 1);
		$p2 = strpos($_SERVER['REQUEST_URI'], '?', $p1 + 1);
		$project = substr($_SERVER['REQUEST_URI'], 1, $p1 - 1);
		$resource = substr($_SERVER['REQUEST_URI'], $p1 + 1, $p2 - $p1 - 1);
		$query = urldecode(substr($_SERVER['REQUEST_URI'], $p2 + 1));
		$method = strtoupper($_SERVER['REQUEST_METHOD']);
		$format = $_SERVER['HTTP_ACCEPT'];
		$referrer = $_SERVER['REMOTE_ADDR'];
		$data = null;
		if ($method == 'POST')
			$data = json_encode(self::decodeNullValues($_POST));
		else if ($method == 'PUT')
			$data = json_encode(self::decodeNullValues($this->put_request_data));

		return Db::query('insert into api_log(method, project, resource, query, data, format, referrer) values ($1, $2, $3, $4, $5, $6, $7)',
			array($method, $project, $resource, $query, $data, $format, $referrer),
			'kv_admin');
	}

	/**
	 *	...
	 * decode all null values from \N
	 */
	private static function decodeNullValues($array, $null_code = '\\N')
	{
		$result = array();
		foreach ((array)$array as $key => $value)
			$result[$key] = ($value == $null_code) ? null : $value;
		return $result;
	}
}

?>
