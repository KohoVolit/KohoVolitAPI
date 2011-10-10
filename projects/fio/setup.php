<?php

$private_resources = array('Message');

function api_fio_autoload($class_name)
{
    if (file_exists(API_ROOT . "/projects/fio/classes/$class_name.php"))
		require_once API_ROOT . "/projects/fio/classes/$class_name.php";
}
spl_autoload_register('api_fio_autoload');

?>
