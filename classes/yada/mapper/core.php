<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 *
 */
abstract class Yada_Mapper_Core implements Yada_Interface_Module
{

        /**
	 * The meta object
         * @var Yada_Meta
         */
	protected $_meta;

	/**
	 * Our Model object
	 * @var Yada_Model
	 */
	protected $_model;

	/**
	 * The current focused field
	 * @var ArrayObject
	 */
	protected $_field;

	/**
	 * The state of the mapper, values:
	 *   new      = No values or clauses set
	 *   changed  = New values/clauses have been set
	 *   loaded   = A Collection has been loaded
	 *   saved    = Values have been saved
	 *   error    = There was an error
	 * @var string
	 */
	protected $_state;

	/**
	 *
	 * @param Yada_Meta $meta
	 * @param Yada_Model $model
	 * @param <type> $values
	 */
	public function __construct(Yada_Meta $meta, Yada_Model $model, $values = NULL)
	{
		// Store the meta and model objects for reference
		$this->_meta = $meta;
		$this->_model = $model;

		$values = (isset($values)) ? $values : array();
		if (empty ($values))
		{
		    $this->_state = 'new';
		}
		else
		{
		    $this->_state = 'changed';
		}

		// Create some new meta properties to store mapper data..
		$meta->values = new ArrayObject($values, ArrayObject::ARRAY_AS_PROPS);
		$meta->related = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		$meta->clauses = new ArrayObject(array());

		// Register with the model
		$this->export($model);
	}

	/**
	 * Sets the field instance
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->field($name);
	}

	/**
	 * Sets the field instance and its value
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->field($name)->set($value);
	}

	/**
	 * Magic function that adds a clause
	 *
	 * @param string $name The operator of the clause
	 * @param mixed $arguments
	 */
	public function __call($name, $arguments)
	{
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

		if ($this->_field instanceof Yada_Field_Interface_Related)
		{
			return $this->related($name, $value);
		}

		$clause = array($this->_field, $name, $value);
		$this->_clauses()->append($clause);
		$this->_state = 'changed';
		return $this;
	}

	/**
	 *
	 * @param <type> $limit
	 * @param <type> $offset
	 */
	abstract protected function _load($limit = NULL, $offset = NULL);

	/**
	 *
	 */
	abstract protected function _save();

	/**
	 * Yada_Interface_Module interface method
	 * @param Yada_Interface_Aggregate $object
	 */
	public function export(Yada_Interface_Aggregate $object)
	{
		$exported = isset($this::$_exported) ? $this::$_exported : array();
		$object->register($this, $exported);
	}

	/**
	 * Get the meta data object
	 * @return ArrayObject
	 */
	protected function _meta()
	{
		return $this->_meta->meta($this->_model);
	}

	/**
	 * Get the fields object
	 * @return ArrayObject
	 */
	protected function _fields()
	{
		return $this->_meta->fields($this->_model);
	}

	/**
	 * Get the field object
	 * @param string $name
	 * @return Yada_Field
	 */
	protected function _field($name)
	{
		return $this->_meta->fields($this->_model)->$name;
	}

	/**
	 *
	 * @return ArrayObject
	 */
	protected function _clauses()
	{
		return $this->_meta->clauses($this->_model);;
	}

	/**
	 *
	 * @return ArrayObject
	 */
	protected function _values()
	{
		return $this->_meta->values($this->_model);;
	}

	/**
	 *
	 * @return ArrayObject
	 */
	protected function _related()
	{
		return $this->_meta->related($this->_model);
	}

	/**
	 *
	 * @return ArrayObject
	 */
	protected function _collect()
	{
		return $this->_meta->collect($this->_model);
	}

	/**
	 *
	 * @param ArrayObject $field
	 * @return Yada_Mapper_Core
	 */
	protected function _reset(ArrayObject $field = NULL)
	{
		$this->_collect(NULL);
		if (isset($field))
		{
			if ($this->_values()->offsetExists($field->name))
			{
				$this->_values()->offsetUnset($field->name);
				$count = $this->_values()->count() + $this->_clauses()->count();
				$this->_state = ($count > 0 ) ? 'changed' : 'new';
			}
		}
		else
		{
			$this->_state = 'new';
			$this->_values()->exchangeArray(array());
			$this->_clauses()->exchangeArray(array());
		}
		return $this;
	}


	/**
	 *
	 * @param Yada_Model $model
	 * @param array $args
	 * @return Yada_Model
	 */
	public function load(Yada_Model $model, array $args)
	{
		$offset = $limit = NULL;
		if ( ! empty ($args)) 
		{
			if (count($args) == 2)
			{
				list($limit, $offset) = $args;
			}
			else
			{
				list($limit) = $args;
			}
		}
		$this->_load($limit, $offset);
		$this->_state = 'loaded';
		return $model;
	}

	/**
	 *
	 * @param Yada_Model $model
	 * @param array $args
	 * @return Yada_Model
	 */
	public function save(Yada_Model $model, array $args)
	{
		$this->_save();
		$this->_state = 'saved';
		return $model;
	}

	/**
	 *
	 * @return Yada_Mapper
	 */
	public function reset()
	{
		if (func_num_args() > 0)
		{
			return $this->_reset();
		}
		return $this->_reset($this->_field);
	}

	/**
	 *
	 * @param string|Yada_Field $field
	 * @return Yada_Mapper
	 */
	public function field($field)
	{
		$this->_field = (is_string($field)) 
			? $this->_field($field)
			: $field;
		return $this;
	}

	/**
	 *
	 * @param mixed $value
	 * @return Yada_Mapper
	 */
	public function set($value)
	{
		if (method_exists($this->_field, 'set'))
		{
			$value = $this->_field->set($value);
		}

		if ($this->_field instanceof Yada_Field_Interface_Related)
		{
			return $this->related('add', $value);
		}
		else
		{
			$values = $this->_values();
			$name = $this->_field->name;
			$values[$name] = $value;
		}
		$this->_state = 'changed';
		return $this;
	}

	/**
	 *
	 * @return mixed
	 */
	public function get()
	{
		if (method_exists($this->_field, 'get'))
		{
			$value = $this->_field->get($value);
		}
		$values = $this->_values();
		$name = $this->_field->name;

		if ($values->offsetExists($name))
		{
			return $values[$name];
		}
		else
		{
			return $this->_field->default;
		}
	}

	public function values(Array $values)
	{
		foreach($values as $name => $value)
		{
			$this->field($name)->set($value);
		}
		return $this;
	}

	public function related($action, $value)
	{
		$related = $this->_related();
		$name = $this->_field->name;
		if ( ! $this->_field instanceof Yada_Field_Interface_Related)
		{
			throw new Kohana_Exception('Field :name in Model :model isn\'t a Related Field', array(
				':name' => $name,
				':model' => Yada::common_name('model', $this->_model)));
		}
		if ( ! $related->offsetExists($action))
		{
			$related[$action] = array($name => $value);
		}
		else
		{
			$related[$action][$name] = $value;
		}
		$this->_state = 'changed';
		return $this;
	}

}
