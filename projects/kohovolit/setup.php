<?php

$private_resources = array('Message');

function api_kohovolit_autoload($class_name)
{
    if (file_exists(API_ROOT . "/projects/kohovolit/classes/$class_name.php"))
		require_once API_ROOT . "/projects/kohovolit/classes/$class_name.php";
}
spl_autoload_register('api_kohovolit_autoload');

?>
