<?php

require '../../config/settings.php';
require '../../setup.php';
require '../kohovolit/config/settings.php';	// not working with ./config/ - bug in PHP?

$private_resources = array('Letter');

try
{
	$resource = $_GET['resource'];
	if (in_array($resource, $private_resources))
		throw new Exception("The API resource <em>$resource</em> is not accessible from remote.", 403);

	$result = ApiServer::processHttpRequest();
	ApiServer::sendHttpResponse(200, $result);
}
catch (Exception $e)
{
	ApiServer::sendHttpResponse($e->GetCode(), $e->getMessage());
}

?>
