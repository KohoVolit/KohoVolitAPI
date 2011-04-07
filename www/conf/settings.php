<?php

set_include_path(
	'd:/projekty/KohoVolit.eu/KVG4/api.kohovolit.eu/www' . PATH_SEPARATOR .
	'd:/projekty/KohoVolit.eu/KVG4/api.kohovolit.eu/www/classes' . PATH_SEPARATOR .
	'd:/projekty/KohoVolit.eu/KVG4/api.kohovolit.eu/www/classes/kohovolit' . PATH_SEPARATOR .
	get_include_path());

error_reporting(E_ALL | E_STRICT);

function __autoload($class_name)
{
    require "$class_name.php";
}

date_default_timezone_set('Europe/Prague');

?>
