<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * The Yada_Meta object acts as an information repository and is the heart of
 * the Yada Framework
 *
 * Model meta data is indexed by the model's common name and the currently
 * focusd model name is stored for reference.
 *
 * ArrayObjects are used to store the meta data on models and fields in favor
 * of arrays to make use of PHP's automatic passing objects by reference
 * functionality when returned or passed into methods. Unless specifically passed
 * by reference, arrays are copied thus increasing memory load and CPU cycles
 *
 * The ARRAY_AS_PROPS Flag is set on the ArrayObjects as an added benefit
 *
 */
abstract class Yada_Meta_Core implements Yada_Interface_Module
{
	/**
	 *
	 * @var SplObjectStorage
	 */
	protected $_models;

	/**
	 *
	 * @var Yada_Model
	 */
	protected $_current;

	/**
	 *
	 * @var ArrayObject
	 */
	protected $_meta;

	public function __construct()
	{
		$this->_models = new SplObjectStorage();
		$this->_current = NULL;
		$this->_meta = NULL;
	}

	/**
	 * Magic Method that returns property values of the Meta ArrayObject
	 *
	 * @param string $name
	 * @param NULL $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		// See if we have been passed a model
		if (count($arguments) == 1)
		{
			list($model) = $arguments;
			if ($model instanceof Yada_Model)
			{
				// Get the Model's Meta ArrayObject
				$this->meta($model);
			}
		}

		// See if the property exists and return it
		if ($this->_meta->offsetExists($name))
		{
			return $this->_meta[$name];
		}
		else
		{
			throw new Kohana_Exception('Property :name doesn\'t exist in the Meta Object', array(
				':name' => $name));
		}
	}

	/**
	 * PHP 5.3 magic method, shortcut for meta()
	 */
	public function __invoke()
	{
		return $this->_meta;
	}

	/**
	 *
	 * @param <type> $name
	 * @param <type> $value 
	 */
	public function __set($name, $value)
	{
		$this->_meta[$name] = $value;
	}

	public function __get($name)
	{
		return isset($this->_meta[$name]) ? $this->_meta[$name] : NULL;
	}

	/**
	 * Abstract function called to intialize the properties of extended meta objects
	 * @param ArrayObject $attached
	 * @param mixed $values
	 */
	abstract protected function _attach(ArrayObject $attached, $values = NULL);

	/**
	 * Abstract function called to intialize the field properties of extended meta objects
	 * @param <type> $name
	 * @param Yada_Field $field 
	 */
	abstract protected function _initialize($name, Yada_Field $field);

	/**
	 * Export any aggregate methods to the model
	 * @param Yada_Interface_Aggregate $model
	 */
	public function export(Yada_Interface_Aggregate $model)
	{
		// Exported method names are stored in a static variable
		$exported = isset(self::$_exported) ? self::$_exported : array();
		$model->register($this, $exported);
	}

	/**
	 * Returns the current focused model, sets the focus if $class is specified
	 *
	 * @param Yada_Model|string $model
	 * @return Yada_Model
	 */
	public function model($model = NULL)
	{
		if ($model instanceof Yada_Model)
		{
			if ( ! $this->_models->contains($model))
			{
				$this->attach($model);
			}
			else
			{
				$this->_current = $model;
			}
		}
		elseif (is_string($model))
		{
			// Attach and initialize unknown models
			$model = Yada::model($model);
			$this->attach($model);
		}
		// Return the current Model object
		return $this->_current;
	}

	/**
	 * Return an array of stored model instances indexed by class name
	 * @return array
	 */
	public function &models()
	{
		$result = array();
		foreach($this->_models as $model)
		{
			$name = Yada::class_name('model', $model);
			$result[$name] = isset($result[$name]) ? $result[$name] : array();
			$result[$name][] = $model;
		}
		return $result;
	}

	/**
	 * Returns the currently focused model's meta data
	 * @param Yada_Model|string $model
	 * @return ArrayObject
	 */
	public function meta($model = NULL)
	{
		if (isset($model))
		{
			$this->model($model);

		}

		$stack = xdebug_get_function_stack();
		$out = array();
		foreach ($stack as $num => $call)
		{
		   // if ($num < 5) continue;
		    if (! isset($call['function'])) continue;
		    $class = (isset($call['class'])) ? $call['class'].'::' : 'Function ';
		    $out[] = $class.$call['function'].' ('.$call['line'].')';
		}
		var_dump($out);
		var_dump(get_class($this->_current));

		return $this->_meta = $this->_models->offsetGet($this->_current);
	}

	/**
	 * Attach a new Model Object by storing a new Meta ArrayObject
	 *
	 * @param Yada_Model $model
	 * @param mixed $values
	 * @return Yada_Model
	 */
	public function attach(Yada_Model $model, $values = NULL)
	{
		// Get the class and common name of the Model
		$class = get_class($model);
		$name = Yada::common_name('model', $class);
		$plural = inflector::plural($name);

		// Create a new ArrayObject to act as the Meta Object and initialize some properties
		$this->_meta = new ArrayObject(array(
			'name'    => $name,
			'class'   => $class,
			'plural'   => $plural,
			// Unique Table aliases are used for all queires
			'alias'   => $this->_models->count().'_'.$plural,
			// Field Information is also stored in ArrayObjects
			'fields'  => array(),
			// related models
			'parent'  => $this->_current,
			'children' => new SplObjectStorage(),
			// Each Model gets its own Mapper Object
			'mapper' => NULL,
			// Collect Objects are referenced here
			'collect' => NULL,
		), ArrayObject::ARRAY_AS_PROPS);

		// Initialize objects of derived Meta classes
		$this->_attach($this->_meta, $values);

		// Register children if there is a parent object
		if (isset($this->_current))
		{
			$this->children->attach($model, $this->_meta);
		}

		// Attach the model and meta data, set the focus
		$this->_models->attach($model, $this->_meta);
		$this->_current = $model;

		// Initialize the Model
		$model::initialize($model, $this);

		// Initialize the Mapper Object
		$this->_meta['mapper'] = Yada::mapper($this, $model, $values);

		// Register aggregate methods
		$this->export($model);

		// return the model
		return $model;
	}

	/**
	 * A method to initialize the model's field meta data
	 *
	 * This method is called from the Model's initialize static function
	 *
	 * @param Array $init
	 */

	public function initialize(Array $init)
	{
		foreach($init as $name => $value)
		{
			$this->_meta[$name] = $value;
		}

		// Initialize the Model's field meta data
		$this->_meta['fields'] = new ArrayObject($this->_meta['fields'], ArrayObject::ARRAY_AS_PROPS);
		foreach ($this->_meta['fields'] as $name => $field)
		{
			if ($field instanceof Yada_Field)
			{
				// Initalize the field object
				$field->initialize($this, $this->model(), $name, $this->alias);

				// Initialize objects of derived Meta classes
				$this->_initialize($name, $field);
			}
		}
	}

	/**
	 * Gets the model's field values
	 *
	 * @param Yada_Model $model
	 * @param string $name field name
	 * @return mixed
	 */
	public function get_field(Yada_Model $model, $name)
	{
		// Focus the model and get the Field's meta data
		$fields = $this->fields($model);

		// Return NULL if field doesn't exist
		if ( ! isset($fields[$name])) return NULL;

		// Get the Yada_Field Object
		$field = $fields[$name];

		// See if its a related field
		if ($field instanceof Yada_Field_Interface_Related)
		{
			// Return the related field's model
			return $field->related()->model;
		}

		// pass the field object to the mapper object, which
		// focuses the field and returns the mapper object instance
		return $this->mapper()->field($field);
	}

	/**
	 * Sets the model's field values
	 *
	 * @param Yada_Model $model
	 * @param string $name field name
	 * @param mixed $value
	 * @return mixed
	 */
	public function set_field(Yada_Model $model, $name, $value)
	{
		// Focus the model and get the Field's meta data
		$fields = $this->fields($model);

		// Return $value if field doesn't exist
		if ( ! isset($fields[$name]))
		{
			throw new Kohana_Exception('Field :name doesn\'t exist in Model :model', array(
				':name' => $name, ':model' => Yada::common_name('model', $model)
			));
		}

		// Get the Yada_Field Object
		$field = $fields[$name];

		// See if its a related field
		if ($field instanceof Yada_Field_Interface_Related)
		{
			$this->mapper()->field($field)->add($value);
			return $value;
		}
		else
		{
			$this->mapper()->field($field)->set($value);
			return $value;
		}
	}
}