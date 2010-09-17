<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Abstract Core Model Class
 *
 * The Yada Model Object is a Dynamic Class that uses Object Aggregation of
 * methods and properties of the classes that implement the Yada Module Interface.
 *
 * The Model class is intentionally left fairly empty and is basically a shell.
 * This allows Yada to be very powerful and adapt to new technology by providing
 * different modules that make up its core functionality.
 *
 * Yada_Model_Core is the class all models must extend.
 *
 */
abstract class Yada_Model_Core implements Yada_Interface_Aggregate //, Iterator, Countable
{
	/**
	 * The default module types and their class name
	 * @var array
	 */
	protected static $_types = array(
		'meta'    => 'Yada_Meta',
		'mapper'  => 'Yada_Mapper',
		'collect' => 'Yada_Collect',
		'record'  => 'Yada_Record',
	);

	/**
	 *
	 * @var <type> 
	 */
	public $_init = array();

	/**
	 * An array of registered modules
	 * @var array
	 */
	protected $_modules = array();

	/**
	 * An array of registered methods.
	 *
	 * Its an array of object references indexed by the dynamic method's name
	 *
	 * @var array
	 */
	protected $_methods = array();

	/**
	 * Constructor
	 * @param array $init
	 */
	public function __construct($init = NULL)
	{
		if (is_array($init))
		{
			$this->_init = $init;
		}
	}

	/**
	 * Magic Method used to call the aggregated dynamic methods
	 *
	 * @param string $name Dynamic method name
	 * @param mixed $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		// If there exists a module with a type called $name
		if (isset($this->_modules[$name]))
		{
			// Return the module instance by it's type name
			return $this->_modules[$name];
		}

		if (count($arguments) == 0)
		{
			$value = NULL;
		}
		elseif (count($arguments) == 1)
		{
			list($value) = $arguments;
		}
		else
		{
			$value = $arguments;
		}

		if(isset($this->_methods[$name]))
		{
			return $this->_methods[$name]->{$name}($this, $value);
		}
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		// If attached to a meta object, get the field
		if (isset($this->_modules['meta']))
		{
			return $this->_modules['meta']->get_field($this, $name);
		}
		// Default to the _init var
		else
		{
			return isset($this->_init[$name]) ? $this->_init[$name] : NULL;
		}
	}

	/**
	 * Magic Method used to set the aggregated dynamic properties
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		// If attached to a meta object, set the field
		if (isset($this->_modules['meta']))
		{
			$this->_modules['meta']->set_field($this, $name, $value);
		}
		// Default to the _init var
		else
		{
			$this->_init[$name] = $value;
		}
	}

	/**
	 *
	 * @param string $name
	 * @return boolean 
	 */
	public function  __isset($name)
	{
		// If there exists a module with a type called $name
		if (isset($this->_modules[$name]))
		{
			return TRUE;
		}
	}

	/**
	 *
	 * @param string $name
	 */
	public function __unset($name)
	{
		// *** Probably need to control this more...
		unset($this->_modules[$name]);
	}

	/**
	 * Method to register a module
	 *
	 * @param Yada_Interface_Module $module
	 * @param array $methods 
	 */
	public function register(Yada_Interface_Module $module, array $methods)
	{
		// Look for existing modules of the same type and unregister them
		foreach (self::$_types AS $name => $class)
		{
			if ($module instanceof $class)
			{
				if (isset($this->_modules[$name]))
				{
					$this->unregister($this->_modules[$name]);
				}
				$this->_modules[$name] = $module;
				break;
			}
		}
		// Register all the methods this module exports
		foreach($methods as $method)
		{
			if ( ! isset($this->_methods[$method]))
			{
				$this->_methods[$method] = $module;
			}
			else
			{
				// Throw an Exception if another module is exporting a method with the same name
				throw new Kohana_Exception('Method ":method" is already registered by :class, :object is incompatible',
					array(
						':method' => $method,
						':class' => get_class($this->_methods[$method]),
						':object' => get_class($module)));
			}
		}
	}

	/**
	 * Method to remove a registed module
	 *
	 * @param Yada_Interface_Module $object
	 */
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
