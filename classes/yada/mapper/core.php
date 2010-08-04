<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: Yet Another Data Abstraction
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 *
 *
 */
 
abstract class Yada_Mapper_Core implements Yada_Interface_Module
{

	protected $_meta;
	protected $_model;
	protected $_stage;
	protected $_field;

	public function __construct($meta, $object)
	{
		$this->_meta = $meta;
		$this->_model = $object;
		$this->_stage = new ArrayObject(array(
			'values' => new ArrayObject(array()),
			'clause' => new ArrayObject(array()),
			), ArrayObject::ARRAY_AS_PROPS);
		$this->export($object);
	}

	public function __get($name) {
		return $this->field($name);
	}

	public function __set($name, $value) {
		$this->field($name)->assign($value);
	}

	abstract protected function _load($limit = NULL, $offset = NULL);
	abstract protected function _save();

	protected function _meta()
	{
		$this->_meta->model($this->_model);
		return $this->_meta->meta();
	}

	protected function _fields()
	{
		$this->_meta->model($this->_model);
		return $this->_meta->fields();
	}

	protected function _field($name)
	{
		$fields = $this->_fields();
		return $fields[$name];
	}

	protected function _collect($collect = NULL)
	{
		$meta = $this->_meta();
		$meta['collect'] = $collect;
	}

	protected function _reset(ArrayObject $field = NULL)
	{
		$this->_collect(NULL);
		$stage = $this->stage($field);
		$stage->exchangeArray(array());
		return $this;
	}

	public function export(Yada_Interface_Aggregate $object)
	{
		$exported = isset($this::$_exported) ? $this::$_exported : array();
		$object->register($this, $exported);
	}

	public function clause()
	{
		return $this->_stage->clause;
	}

	public function values()
	{
		return $this->_stage->values;
	}

	public function stage()
	{
		return $this->_stage;
	}

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

	public function save(Yada_Model $model, array $args)
	{
		$this->_save();
		return $model;
	}

	public function reset()
	{
		if (func_num_args() > 0)
		{
			return $this->_reset();
		}
		return $this->_reset($this->_field);
	}

	public function field($field)
	{
		$this->_field = (is_string($field)) ? $this->_field($field) : $field;
		return $this;
	}

	public function set($value)
	{
		$values = $this->values();
		$values[$this->_field['name']] = array($this->_field, $value);
		return $this;
	}

	public function equal($value)
	{
		$this->clause()->append(array('=', array($this->_field, $value)));
		return $this;
	}

	public function like($value)
	{
		$this->clause()->append(array('like', array($this->_field, $value)));
		return $this;
	}

	public function in(array $values)
	{
		$this->clause()->append(array('in', array($this->_field, $values)));
		return $this;
	}

	public function op($operator)
	{
		$this->clause()->append(array($operator, $this->_field));
		return $this;
	}

}
