<?php

require '../../config/settings.php';
require '../../setup.php';
require './config/settings.php';

try
{
	$result = ApiServer::processHttpRequest();
	ApiServer::sendHttpResponse(200, $result);
}
catch (Exception $e)
{
	ApiServer::sendHttpResponse($e->GetCode(), $e->getMessage());
}

?>
