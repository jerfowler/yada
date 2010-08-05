<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: Yet Another Data Abstraction
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 * Yada_Model is the class all models must extend. It handles
 * various CRUD operations and relationships to other models.
 *
 */
abstract class Yada_Model_Core implements Yada_Interface_Aggregate //, Iterator, Countable
{
	protected static $_types = array(
		'meta'	=> 'Yada_Meta',
		'mapper'  => 'Yada_Mapper',
		'collect' => 'Yada_Collect',
		'record'  => 'Yada_Record',
	);

	protected $_modules = array();
	protected $_methods = array();
	protected $_loaded = FALSE;

	public function __construct(array $data = NULL)
	{
	}

	public function __call($name, $arguments)
	{
		if(isset($this->_methods[$name]))
		{
			var_dump('Calling method: '.$name);
			var_dump($arguments);
			return $this->_methods[$name]->{$name}($this, $arguments);
		}
	}

	public function __get($name)
	{
		if (isset($this->_modules[$name]))
		{
			return $this->_modules[$name];
		}
		elseif (isset($this->_methods['get_field']))
		{
			return $this->_methods['get_field']->get_field($this, $name);
		}
		else
		{
			return $this->_modules['meta']->get_field($this, $name);
		}
	}

	public function __set($name, $value)
	{
		if (isset($this->_methods['set_field']))
		{
			$this->_methods['set_field']->set_field($this, $name, $value);
		}
		else
		{
			$this->_modules['meta']->set_field($this, $name, $value);
		}
	}

	public function unregister(Yada_Interface_Module $object)
	{
		foreach ($this->_methods as $name => $module)
		{
			if ($module === $object)
			{
				unset($this->_methods[$name]);
			}
		}
		foreach($this->_modules as $name => $module)
		{
			if ($module === $object)
			{
				unset($this->_modules[$name]);
			}
		}
	}

	public function register(Yada_Interface_Module $object, array $methods)
	{
		foreach (self::$_types AS $name => $class)
		{
			if ($object instanceof $class)
			{
				if (isset($this->_modules[$name]))
				{
					$this->unregister($this->_modules[$name]);
				}
				$this->_modules[$name] = $object;
				break;
			}
		}

		foreach($methods as $method)
		{
			if ( ! isset($this->_methods[$method]))
			{
				$this->_methods[$method] = $object;
			}
			else
			{
				throw new Kohana_Exception('Method ":method" is already registered by :class, :object is incompatible',
					array(
						':method' => $method,
						':class' => get_class($this->_methods[$method]),
						':object' => get_class($object)));
			}
		}
	}

//
//	public function current()
//	{
//		return $this;
//	}
//
//	public function key()
//	{
//		return $this->_modules['_Collection']->key();
//	}
//
//	public function next()
//	{
//		$this->_modules['_Collection']->next();
//	}
//
//	public function rewind()
//	{
//		$this->_modules['_Collection']->rewind();
//	}
//
//	public function valid()
//	{
//		return $this->_modules['_Collection']->valid();
//	}
//
//	public function count()
//	{
//		return $this->_modules['_Collection']->count();
//	}
}
