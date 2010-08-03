<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Handles many to many relationships
 *
 * @package  Yada
 */
abstract class Yada_Field_Related_ManyToMany extends Yada_Field_Related implements Yada_Field_Interface_Through
{
	public function initialize($meta, $model, $column)
	{
                if (! $this->through)
                {
                        throw new Kohana_Exception(
                                'No through option specified for many-to-many field :field in model :model',
                                array(':field' => $column, ':model' => Yada::common_name('model', $model)));
                }
                parent::initialize($meta, $model, $column);
        }

        public function through() {
            return $this->through;
        }
}
