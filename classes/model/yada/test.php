<?php defined('SYSPATH') or die ('No direct script access.');

class Model_Yada_Test extends Yada_Model
{
	public static function initialize(Yada_Model $model, Yada_Meta $meta)
        {
		$meta->initialize(array(
			'fields' => array(
				'id' => Yada::field('Primary'),
				'test2' => Yada::field('Foreign', array(
					'related' => array('Test2', 'test'))),
				'name' => Yada::field('Name'),
				'description' => Yada::field('String'),
				'value' => Yada::field('Integer'),
				'condition' => Yada::field('Boolean'),
				'number' => Yada::field('Float'),
				'stamp' => Yada::field('Timestamp'),
				'date' => Yada::field('DateTime'),
				'test3' => Yada::field('ManyToMany', array(
					'related' => array('Test3', 'test'),
//					'through' => array(array('Dynamic', array(
//						'fields' => array(
//							'test' => Yada::field('Foreign', array(
//							//	'column'  => 'test_id',
//								'related' => array($model, 'test3'))),
//							'test3' => Yada::field('Foreign', array(
//							//	'column'  => 'test3_id',
//								'related' => array('Test3', 'test'))),
//	 						'name' => Yada::field('Name'),
//						),
//						'name'  => 'Test<-x->Test3',
//						'index' => array('name'),
//						'table' => 'test_test3_join',
//					 )), 'test'),
				 )),
			),
			'table' => 'tests',
//			'clauses' => array(
//				array('condition', 'is', TRUE),
//				array('value', 'is', NULL),
//				array('value', 'like', '%example%'),
//				array('number', 'between', array(1, 10)),
//			),
		));
        }
}