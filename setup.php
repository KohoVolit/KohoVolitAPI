<?php

set_include_path(API_ROOT . PATH_SEPARATOR . get_include_path());

function api_autoload($class_name)
{
    if (file_exists(API_ROOT . "/classes/$class_name.php"))
		require_once API_ROOT . "/classes/$class_name.php";
}
spl_autoload_register('api_autoload');

?>
