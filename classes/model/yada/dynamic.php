<?php defined('SYSPATH') or die ('No direct script access.');

class Model_Yada_Dynamic extends Yada_Model
{
	public static function initialize(Yada_Model $model, Yada_Meta $meta)
        {
		$meta->initialize($meta, $model->_init);
		unset($model->_init);
	}
}