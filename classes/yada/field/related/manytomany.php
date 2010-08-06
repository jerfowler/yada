<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 * Handles many to many relationships
 *
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

	public function through()
	{
		return $this->through;
	}
}
