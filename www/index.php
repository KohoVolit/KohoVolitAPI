<?php

require '../config/settings.php';
require '../setup.php';

try
{
	$server = new ApiServer;
	$result = $server->processHttpRequest();
	$server->sendHttpResponse(200, $result);
}
catch (Exception $e)
{
	$server->sendHttpResponse($e->GetCode(), $e->getMessage());
}

?>
