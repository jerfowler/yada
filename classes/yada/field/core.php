<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: Yet Another Data Abstraction
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
 
abstract class Yada_Field_Core
{
	protected $_props;

	public function __construct($options = array())
	{
		$defaults = array(
			'unique' => FALSE,
			'description' => '',
			'default' => NULL,
			'null' => FALSE,
			'filters' => array(),
			'rules' => array(),
			'callbacks' => array(),
		);

		// Assume it's the column name
		if (is_string($options))
		{
			$defaults['column'] = $options;
		}
		elseif (is_array($options))
		{
			$defaults = Arr::merge($defaults, $options);
		}

		// Check as to whether we need to add
		// some callbacks for shortcut properties
		if ($defaults['unique'] === TRUE)
		{
			$defaults['callbacks'][] = array($this, '_is_unique');
		}

		$this->_props = new ArrayObject($defaults, ArrayObject::ARRAY_AS_PROPS);
	}

	public function __get($name)
	{
		return ($this->_props->offsetExists($name)) ? $this->_props[$name] : NULL;
	}

	public function __set($name, $value)
	{
		$this->_props[$name] = $value;
	}

	public function initialize($meta, $model, $column)
	{
		$this->meta = $meta;

		// This will come in handy for setting complex relationships
		$this->model = $model;

		// This is for naming form fields
		$this->name = $column;

		if ( ! $this->column)
		{
			$this->column = $column;
		}

		// Check for a name, because we can easily provide a default
		if ( ! $this->label)
		{
			$this->label = inflector::humanize($column);
		}
	}

	public function column($prefix)
	{
			return $prefix.'.'.$this->column;
	}

	/**
	 * Sets a particular value processed according
	 * to the class's standards.
	 *
	 * @param   mixed  $value
	 * @return  mixed
	 **/
	public function set($value)
	{
		return $value;
	}

	/**
	 * Returns a particular value processed according
	 * to the class's standards.
	 *
	 * @param   Yada_Model  $model
	 * @param   mixed		$value
	 * @param   boolean	  $loaded
	 * @return  mixed
	 **/
	public function get($model, $value)
	{
		return $value;
	}

}
