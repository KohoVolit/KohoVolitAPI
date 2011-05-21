<?php

require '../../config/settings.php';
require '../../setup.php';

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
