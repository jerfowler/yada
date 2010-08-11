<?php defined('SYSPATH') or die('No direct script access.');

// Catch-all route for Codebench classes to run
Route::set('yada', 'yada/(<controller>(/<action>(/<id>)))')
	->defaults(array(
		'directory'  => 'yada',
		'controller' => 'tester',
		'action' => 'index'));
