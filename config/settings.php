<?php
$config['displayErrorDetails'] = false; // set to false in production
$config['addContentLengthHeader'] = false;
$config['table_name'] = 'branches';
$config['db'] = [
	'driver' => 'sqlite',
	'database' => '../database/api_db.sqlite3'
]
?>
