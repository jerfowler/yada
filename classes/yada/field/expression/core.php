<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

abstract class Yada_Field_Expression_Core extends Yada_Field implements Yada_Field_Interface_Expression
{
	public function initialize(Yada_Meta $meta, Yada_Model $model, $name, $alias)
	{
		parent::initialize($meta, $model, $name, $alias);
		if ( ! isset ($this->expression))
		{
			throw new Exception('You must specifiy an Expression for '.Yada::common_name('model', $model).'->'.$name);
		}
	}


	public function alias()
	{
		return $this->alias.'_'.$this->name;
	}

	public function expression()
	{
		return $this->expression;
	}

	public function set($value)
	{
		return $this->expression = $value;
	}
}