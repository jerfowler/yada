<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
 
abstract class Yada_Field_Data_Core extends Yada_Field implements Yada_Field_Interface_Column
{

	/**
	 *
	 * @param Yada_Meta $meta
	 * @param Yada_Model $model
	 * @param <type> $name
	 * @param <type> $alias
	 */
	public function initialize(Yada_Meta $meta, Yada_Model $model, $name, $alias)
	{
		parent::initialize($meta, $model, $name, $alias);
	}

	public function alias()
	{
		return $this->alias.'_'.$this->name;
	}

	public function column()
	{
		return $this->_props->offsetExists('column') ? $this->alias.'.'.$this->column : $this->alias.'.'.$this->name;
	}

	/**
	 *
	 * @param  mixed   $value
	 * @return mixed
	 */
	public function set($value)
	{
		return $value;
	}

}