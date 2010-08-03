<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Handles belongs to relationships
 *
 * @package  Yada
 */
abstract class Yada_Field_Key_Foreign extends Yada_Field_Key implements Yada_Field_Interface_Related
{
    	public function initialize($meta, $model, $column)
	{
                parent::initialize($meta, $model, $column);
		if ( ! $this->related)
		{
			$this->related = $column;
		}
        }

        public function related()
        {
                return $this->related;
        }
}
