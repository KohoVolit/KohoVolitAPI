<?php

require '../../conf/settings.php';
include './conf/settings.php';

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
