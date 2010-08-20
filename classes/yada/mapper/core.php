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
		$meta->select = new ArrayObject(array());
		$meta->exclude = new SplObjectStorage;

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
		return $this->field($this->_model, $name);
	}

	/**
	 * Sets the field instance and its value
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->field($this->_model, $name)->set($value);
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
	protected function _meta($model = NULL)
	{
		$model = isset($model) ? $model : $this->_model;
		return $this->_meta->meta($model);
	}

	/**
	 * Get the fields object
	 * @return ArrayObject
	 */
	protected function _fields($model = NULL)
	{
		$model = isset($model) ? $model : $this->_model;
		return $this->_meta->fields($model);
	}

	/**
	 * Get the field object
	 * @param string $name
	 * @return Yada_Field
	 */
	protected function _field($name, $model = NULL)
	{
		$model = isset($model) ? $model : $this->_model;
		return $this->_meta->fields($model)->$name;
	}

	/**
	 *
	 * @return ArrayObject
	 */
	protected function _select($model = NULL)
	{
		$model = isset($model) ? $model : $this->_model;
		return $this->_meta->select($model);;
	}

	/**
	 *
	 * @return SplObjectStorage;
	 */
	protected function _exclude($model = NULL)
	{
		$model = isset($model) ? $model : $this->_model;
		return $this->_meta->exclude($model);;
	}

	/**
	 *
	 * @return ArrayObject
	 */
	protected function _clauses($model = NULL)
	{
		$model = isset($model) ? $model : $this->_model;
		return $this->_meta->clauses($model);;
	}

	/**
	 *
	 * @return ArrayObject
	 */
	protected function _values($model = NULL)
	{
		$model = isset($model) ? $model : $this->_model;
		return $this->_meta->values($model);;
	}

	/**
	 *
	 * @return ArrayObject
	 */
	protected function _related($model = NULL)
	{
		$model = isset($model) ? $model : $this->_model;
		return $this->_meta->related($model);
	}

	/**
	 *
	 * @return ArrayObject
	 */
	protected function _collect($model = NULL)
	{
		$model = isset($model) ? $model : $this->_model;
		return $this->_meta->collect($model);
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
	public function load($model = NULL, $args = NULL)
	{
		$offset = $limit = NULL;

		if ($model instanceof Yada_Model)
		{
			if (is_array($args))
			{
				list($limit, $offset) = $args;
			}
			else
			{
				$limit = $args;
			}
		}
		else
		{
			$limit = $model;
			$offset = $args;
			$model = $this->_model;
		}

		if ($this->_load($limit, $offset))
		{
			$this->_state = 'loaded';
		}
		else
		{
			$this->_state = 'error';
		}
		return $model;
	}

	/**
	 *
	 * @param Yada_Model $model
	 * @param array $args
	 * @return Yada_Model
	 */
	public function save($model = NULL, $field = NULL)
	{
		if ($this->_save())
		{
			$this->_state = 'saved';
		}
		else
		{
			$this->_state = 'error';
		}

		return $this->_model;
	}

	/**
	 *
	 * @return Yada_Mapper
	 */
	public function reset($model = NULL, $field = NULL)
	{
	    	if ($model instanceof Yada_Model)
		{
			return $this->_reset($field);
		}
		else
		{
			$field = (is_null($model)) ? $this->_field : $this->_field($model);
			return $this->_reset($field);
		}
	}

	/**
	 *
	 * @param string|Yada_Field $field
	 * @return Yada_Mapper
	 */
	public function field($model = NULL, $field = NULL)
	{
	    	if ( ! $model instanceof Yada_Model)
		{
			$field = $model;
		}

		$this->_field = (is_string($field))
				? $this->_field($field)
				: $field;

		return $this;
	}

	public function values($model = NULL, $values = NULL)
	{
	    	if ( ! $model instanceof Yada_Model)
		{
			$values = $model;
			$model = $this->_model;
		}

		foreach($values as $name => $value)
		{
			$this->field($model, $name)->set($value);
		}
		return $this;
	}

	public function select($model = NULL, $fields = NULL)
	{
	    	if ( ! $model instanceof Yada_Model)
		{
			$fields = $model;
		}

		$select = $this->_select();
		if (empty ($fields))
		{
			$select->exchangeArray(array());
		}
		else
		{
			if ($fields instanceof ArrayObject)
			{
				$fields = array_keys($fields->getArrayCopy());
			}
			$select->exchangeArray((array)$fields);
		}
		return $this;
	}

	public function exclude($model = NULL, $fields = NULL)
	{
	    	if ( ! $model instanceof Yada_Model)
		{
			$fields = $model;
			$model = $this->_model;
		}

		$exclude = $this->_exclude();
		if ( is_null($fields))
		{
			$fields = array();
		}
		elseif ($fields instanceof ArrayObject)
		{
			$fields = array_keys($fields->getArrayCopy());
		}
		else
		{
			$fields = is_array($fields) ? $fields : array($fields);
		}
		
		var_dump('***Exclude***');
		var_dump($fields);

		$fields = new ArrayObject(array_combine($fields, $fields));
		$exclude[$model] = $fields;
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

	public function related($action, $value)
	{
		$related = $this->_related();

		ini_set('xdebug.var_display_max_depth', 2 );
		var_dump('Related: '.$action);
		var_dump($this);
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
