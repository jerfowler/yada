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
	 *
         * @var Yada_Meta
         */
	protected $_meta;

	/**
	 *
	 * @var Yada_Model
	 */
	protected $_model;

	/**
	 *
	 * @var ArrayObject
	 */
	protected $_stage;

	/**
	 *
	 * @var ArrayObject
	 */
	protected $_field;

	/**
	 *
	 * @param Yada_Meta $meta
	 * @param Yada_Model $model
	 * @param <type> $values
	 */
	public function __construct(Yada_Meta $meta, Yada_Model $model, $values = NULL)
	{
		$this->_meta = $meta;
		$this->_model = $model;
		$this->_stage = new ArrayObject(array(
			'values' => new ArrayObject(array()),
			'clause' => new ArrayObject(array()),
			), ArrayObject::ARRAY_AS_PROPS);
		$this->export($model);
	}

	/**
	 *
	 * @param <type> $name
	 * @return <type>
	 */
	public function __get($name) {
		return $this->field($name);
	}

	/**
	 *
	 * @param <type> $name
	 * @param <type> $value
	 */
	public function __set($name, $value) {
		$this->field($name)->set($value);
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
	 *
	 * @return <type>
	 */
	protected function _meta()
	{
		$this->_meta->model($this->_model);
		return $this->_meta->meta();
	}

	/**
	 *
	 * @return <type>
	 */
	protected function _fields()
	{
		$this->_meta->model($this->_model);
		return $this->_meta->fields();
	}

	/**
	 *
	 * @param <type> $name
	 * @return <type>
	 */
	protected function _field($name)
	{
		$fields = $this->_fields();
		return $fields[$name];
	}

	/**
	 *
	 * @param <type> $collect
	 */
	protected function _collect($collect = NULL)
	{
		$meta = $this->_meta();
		$meta['collect'] = $collect;
	}

	/**
	 *
	 * @param ArrayObject $field
	 * @return Yada_Mapper_Core
	 */
	protected function _reset(ArrayObject $field = NULL)
	{
		$this->_collect(NULL);
		$stage = $this->stage($field);
		$stage->exchangeArray(array());
		return $this;
	}

	/**
	 *
	 * @param Yada_Interface_Aggregate $object
	 */
	public function export(Yada_Interface_Aggregate $object)
	{
		$exported = isset($this::$_exported) ? $this::$_exported : array();
		$object->register($this, $exported);
	}

	/**
	 *
	 * @return <type>
	 */
	public function clause()
	{
		return $this->_stage->clause;
	}

	/**
	 *
	 * @return <type>
	 */
	public function values()
	{
		return $this->_stage->values;
	}

	/**
	 *
	 * @return <type>
	 */
	public function stage()
	{
		return $this->_stage;
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
		return $model;
	}

	/**
	 *
	 * @return <type>
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
	 * @param <type> $field
	 * @return Yada_Mapper_Core
	 */
	public function field($field)
	{
		$this->_field = (is_string($field)) ? $this->_field($field) : $field;
		return $this;
	}

	/**
	 *
	 * @param <type> $value
	 * @return Yada_Mapper_Core
	 */
	public function set($value)
	{
		$values = $this->values();
		$values[$this->_field['name']] = array($this->_field, $value);
		return $this;
	}

	/**
	 *
	 * @param <type> $value
	 * @return Yada_Mapper_Core
	 */
	public function equal($value)
	{
		$this->clause()->append(array('=', array($this->_field, $value)));
		return $this;
	}

	/**
	 *
	 * @param <type> $value
	 * @return Yada_Mapper_Core
	 */
	public function like($value)
	{
		$this->clause()->append(array('like', array($this->_field, $value)));
		return $this;
	}

	/**
	 *
	 * @param array $values
	 * @return Yada_Mapper_Core
	 */
	public function in($values)
	{
		$values = (is_array($values)) ? $values : func_get_args();
		$this->clause()->append(array('in', array($this->_field, $values)));
		return $this;
	}

	/**
	 *
	 * @param <type> $operator
	 * @return Yada_Mapper_Core
	 */
	public function op($operator)
	{
		$this->clause()->append(array($operator, $this->_field));
		return $this;
	}

}
