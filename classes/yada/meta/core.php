<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * The Yada_Meta object acts as a repository of information and is the heart of
 * the Yada framework
 *
 * Model meta data is indexed by the model's common name and the currently
 * focusd model name is stored for reference.
 *
 * ArrayObjects are used to store the meta data on models and fields in favor
 * of arrays to improve memory perfromance as objects are passed by reference
 * automatically when returned from or passed into methods.
 *
 */
abstract class Yada_Meta_Core implements Yada_Interface_Module
{
	/**
	 * An array of ArrayObjects that contain various information on the
	 * Yada Model objects indexed by the Model's common name
	 * @var array
	 */
	protected $_models = array();

	/**
	 * The common name of the current Yada Model
	 * @var string
	 */
	protected $_current;

	/**
	 * Magic Method that returns property values of the Meta ArrayObject
	 *
	 * @param string $name
	 * @param NULL $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		// Get the Meta ArrayObject
		$meta = $this->meta();
		// See if the property exists and return it
		if ($meta->offsetExists($name))
		{
			return $meta[$name];
		}
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
	 * Attach a new Model Object by storing a new Meta ArrayObject
	 *
	 * @param Yada_Model $model
	 * @param mixed $values
	 * @return Yada_Model
	 */
	public function attach(Yada_Model $model, $values = NULL)
	{
		// Get the class and common names of the Model
		$class = get_class($model);
		$name = Yada::common_name('model', $class);

		// Get the table name for the model, or use the inflector helper
		$table = isset($model::$table) ? $model::$table : inflector::plural($name);

		// Create a new ArrayObject to act as the Meta Object and initialize some properties
		$this->_models[$class] = new ArrayObject(array(
			'object'  => $model,
			'name'    => $name,
			'class'   => $class,
			'table'   => $table,
			// Unique Table aliases are used for all queires
			'alias'   => count($this->_models).'_'.$table,
			// Field Information is also stored in ArrayObjects
			'fields'  => new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS),
			// Each Model gets its own Mapper Object
			'mapper' => NULL,
			// Collect Objects are referenced here
			'collect' => NULL,
		), ArrayObject::ARRAY_AS_PROPS);

		// Initialize objects of derived Meta classes
		$this->_attach($this->_models[$class], $values);

		// Set the focus
		$this->_current = $class;

		// Register aggregate methods
		$this->export($model);

		// Initialize the Model's field meta data
		$model::initialize($model, $this);

		// Initialize the Mapper Object
		$this->_models[$class]['mapper'] = Yada::mapper($this, $model, $values);

		// return the model
		return $model;
	}

	/**
	 * Returns the current focused model, sets the focus if $class is specified
	 *
	 * @param Yada_Model|string $class
	 * @return Yada_Model
	 */
	public function model($class = NULL)
	{
		if ($class instanceof Yada_Model)
		{
			$this->_current = get_class($class);
		}
		elseif (isset($class))
		{
			$model = $class;
			$class = Yada::class_name('model', $model);

			// Attach and initialize unknown models
			if ( ! isset($this->_models[$class]))
			{
				$this->attach(Yada::model($model));
			}
		}
		// Return he reference to the Model object stored in the Meta data
		return $this->_models[$this->_current]['object'];
	}

	/**
	 * Returns the currently focused model's meta data
	 * @return ArrayObject
	 */
	public function meta()
	{
		return $this->_models[$this->_current];
	}

	/**
	 * Returns the currently focused model's field meta data
	 * @return ArrayObject
	 */
	public function fields()
	{
		return $this->_models[$this->_current]['fields'];
	}

	/**
	 * A method to initialize the model's field meta data
	 *
	 * This method is called from the Model's initialize static function
	 *
	 * @param array $fields
	 * @return Yada_Meta
	 */
	public function initialize($fields)
	{
		// Get the focused model's object
		$_model = $this->model();
		// Get the focused model's meta data
		$_meta = $this->meta();
		// Get the focused model's field data
		$_fields = $this->fields();

		foreach ($fields as $name => $field)
		{
			if ($field instanceof Yada_Field)
			{
				// Initalize the field object
				$field->initialize($this, $_model, $name);

				// Add the field to the meta data
				$_fields[$name] = new ArrayObject(array(
					'object' => $field,
					'name'   => $name,
					'class'  => get_class($field),
					'loaded' => FALSE,
				), ArrayObject::ARRAY_AS_PROPS);

				// See if its a related field
				if ($field instanceof Yada_Field_Interface_Related)
				{
					$_fields[$name]['related'] = $field->related();
					if ($field instanceof Yada_Field_Interface_Through)
					{
						$_fields[$name]['through'] = $field->through();
					}
				}
				// See if the field references "real" data in a column
				elseif ($field instanceof Yada_Field_Interface_Column)
				{
					$_fields[$name]['column'] = $field->column($_meta['alias']);
					// All column fields are aliased
					$_fields[$name]['alias'] = $_meta['alias'].'_'.$name;
					// See if we have a default value
					$_fields[$name]['default'] = $field->default;
				}
				// Expression fields calculates data dynamically at the source
				elseif ($field instanceof Yada_Field_Interface_Expression)
				{
					$_fields[$name]['expression'] = $field->expression($_meta['alias']);
					$_fields[$name]['alias'] = $_meta['alias'].'_'.$name;
				}
				// Initialize objects of derived Meta classes
				$this->_initialize($name, $field);
			}
		}
		return $this;
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
		// Focus the model
		$this->model($model);

		// Get the Fields meta data
		$fields = $this->fields();

		// See if its a related field
		if (isset($fields[$name]['related']))
		{
			// Get the related field's model
			$related = $fields[$name]['related'];
			if (is_array($related))
			{
				list($related, $field) = $related;
			}
			// return the related model
			return $this->model($related);
		}

		// See if we have a collection
		$collect = $this->collect();
		if ($collect instanceof Yada_Collect)
		{
			if ($fields[$name]['loaded'] == TRUE)
			{
				return $collect->$name;
			}
			
		}

		// No collection, pass the field meta data to the mapper object, which
		// focuses the field and returns the mapper object instance
		return $this->mapper()->field($fields[$name]);
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
		// Focus the model
		$this->model($model);
		
		// Get the Fields meta data
		$fields = $this->fields();

		// See if we have a collection
		$collect = $this->collect();
		if ($collect instanceof Yada_Collect)
		{
			// Change the collection's field value
			return $collect->$name = $value;
		}
		// No collection yet, pass the field value to the mapper
		else
		{
			$fields = $this->fields();
			return $this->mapper()->field($fields[$name])->$name = $value;
		}
	}

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

}