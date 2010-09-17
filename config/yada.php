<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'default' => array(
		'type'       => 'pdo',
		'connection' => array(
			'dsn'        => 'mysql:host=localhost;dbname=test',
			'username'   => 'tester',
			'password'   => 'H9Rs4xKzF9ZFrD9f',
			'persistent' => FALSE,
		),
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => FALSE,
		'profiling'    => TRUE,
	),

);