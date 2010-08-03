<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @package  Yada
 */
interface Yada_Field_Interface_Column
{
        public function column($alias);
	public function save($model, $value, $loaded);
}
