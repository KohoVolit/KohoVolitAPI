<?php

const API_ROOT = 'd:/projekty/KohoVolit.eu/KVG4/api.kohovolit.eu/www';

set_include_path(
	API_ROOT . PATH_SEPARATOR .
	API_ROOT . '/classes' . PATH_SEPARATOR .
	API_ROOT . '/classes/kohovolit' . PATH_SEPARATOR .
	get_include_path());

error_reporting(E_ALL | E_STRICT);

function __autoload($class_name)
{
    require_once "$class_name.php";
}

date_default_timezone_set('Europe/Prague');

?>
