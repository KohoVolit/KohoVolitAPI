<?php

set_include_path(
	'/var/www/api.kohovolit.eu' . PATH_SEPARATOR .
	'/var/www/api.kohovolit.eu/classes' . PATH_SEPARATOR .
	'/var/www/api.kohovolit.eu/classes/kohovolit' . PATH_SEPARATOR .
	get_include_path());

error_reporting(E_ALL | E_STRICT);

function __autoload($class_name)
{
    require "$class_name.php";
}

?>
