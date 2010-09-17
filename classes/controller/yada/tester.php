<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Yada_Tester extends Controller
{

	public function action_index()
        {

            $test = Yada::factory('Test');
	    $test->begin()
		    ->name->like('%test%')
		    ->or_not_begin()
			    ->eq('Example 01')  // name, uses last field
			    ->and()
			    ->date->is_not(NULL)
		    ->end()
		    ->or()
		    ->condition->is(TRUE)
		    ->value->between(1, 2)	    // AND is assumed when no boolean glue specified....
		    ->value->between(array(1, 2))
	    ->end();

	    $test->load();

//	    // These two are functionally the same
//	    $test->field('name')->set('test');
//	    $test->name = 'test';
//
//	    $test->values(array(
//		'date' => time(),
//		'value' => array(1,2)
//	    ));
//
//	    // Automatic joins for related fields... Just have to reference it once...
//	    $test->test3;
//
//
//	    $test->field('test3')->select();
//	    $test->field('test3')->join('INNER');
//	    $test->test3->load();

        }
}