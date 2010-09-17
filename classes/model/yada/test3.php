<?php defined('SYSPATH') or die ('No direct script access.');

class Model_Yada_Test3 extends Yada_Model
{
	public static function initialize(Yada_Model $model, Yada_Meta $meta)
        {
                $meta->initialize(array(
			'fields' => array(
				'id' => Yada::field('Primary'),
				'name' => Yada::field('Name'),
				'description' => Yada::field('String'),
				'value' => Yada::field('Integer'),
				'condition' => Yada::field('Boolean'),
				'number' => Yada::field('Float'),
				'stamp' => Yada::field('Timestamp'),
				'date' => Yada::field('DateTime'),
				'test' => Yada::field('ManyToMany', array(
					'related' => array('Test', 'test3'),
					'through' => array(array('Dynamic', array(
						'fields' => array(
							'test' => Yada::field('Foreign', array(
								'related' => array($model, 'test3'))),
							'test3' => Yada::field('Foreign', array(
								'related' => array('Test3', 'test'))),
							'name' => Yada::field('Name'),
						),
						'name'  => 'Test<-x->Test3',
						'index' => array('name'),
						'table' => 'test_test3_join',
					 )), 'test3'),
				    )),
			),
			'table' => 'test3',
		));

        }
}