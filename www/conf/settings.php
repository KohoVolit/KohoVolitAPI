<?php

set_include_path('/var/www/api.kohovolit.eu' . PATH_SEPARATOR . get_include_path());

error_reporting(E_NONE);

function __autoload($class_name)
{
    require "classes/$class_name.php";
}

?>
