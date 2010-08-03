<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @package  Yada
 */
abstract class Yada_Field_Key_Core extends Yada_Field implements Yada_Field_Interface_Column
{

	public function save($model, $value, $loaded)
	{
		return $value;
	}

}