<?php defined('SYSPATH') or die ('No direct script access.');

class Model_Yada_Test2 extends Yada_Model
{
        public static $table = 'test2';

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
				'test' => Yada::field('HasOne', array('related' => array('Test', 'test2'))),
			),
			'table' => 'test2',

		));

        }
}