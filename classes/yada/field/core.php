<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
 
abstract class Yada_Field_Core
{
	/**
	 *
	 * @var <type>
	 */
	protected $_props;

	/**
	 *
	 * @param <type> $options
	 */
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

		if (is_array($options))
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

	/**
	 *
	 * @param <type> $name
	 * @return <type>
	 */
	public function __get($name)
	{
		return ($this->_props->offsetExists($name)) ? $this->_props[$name] : NULL;
	}

	/**
	 *
	 * @param <type> $name
	 * @param <type> $value
	 */
	public function __set($name, $value)
	{
		$this->_props[$name] = $value;
	}

	/**
	 *
	 * @param Yada_Meta $meta
	 * @param Yada_Model $model
	 * @param <type> $name
	 * @param <type> $alias
	 */
	public function initialize(Yada_Meta $meta, Yada_Model $model, $name, $alias)
	{
		$this->meta = $meta;

		// This will come in handy for setting complex relationships
		$this->model = $model;

		// This is for naming form fields
		$this->name = $name;

		// This is the alias of the table
		$this->alias = $alias;

		// Check for a name, because we can easily provide a default
		if ( ! $this->label)
		{
			$this->label = inflector::humanize($name);
		}
	}

	/**
	 * Returns a particular value processed according
	 * to the class's standards.
	 *
	 * @param   mixed
	 * @return  mixed
	 **/
	public function get($value)
	{
		return $value;
	}

	public function save()
	{
	    
	}

}
