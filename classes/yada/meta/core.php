<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Yada: Yet Another Data Abstraction
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 * Yada_Meta objects act as a registry of information
 *
 */
abstract class Yada_Meta_Core implements Yada_Interface_Module
{
	protected $_models = array();
	protected $_current;

	public function __call($name, $arguments)
	{
		$meta = $this->meta();
		if ($meta->offsetExists($name))
		{
			return $meta[$name];
		}
	}

	abstract protected function _initialize($name, Yada_Field $field);
	abstract protected function _attach(ArrayObject $attached);

	public function attach($object)
	{
		$class = get_class($object);
		$name = Yada::common_name('model', $class);
		$table = isset($object::$table) ? $object::$table : inflector::plural($name);
		$this->_models[$class] = new ArrayObject(array(
			'object'  => $object,
			'name'    => $name,
			'class'   => $class,
			'table'   => $table,
			'alias'   => count($this->_models).'_'.$table,
			'fields'  => new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS),
			'mapper' => Yada::mapper($this, $object),
			'collect' => NULL,
		), ArrayObject::ARRAY_AS_PROPS);
		$this->_attach($this->_models[$class]);
		$this->_current = $class;
		$this->export($object);
		$object::initialize($object, $this);
		return $object;
	}

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
			if ( ! isset($this->_models[$class]))
			{
				$this->attach(Yada::model($model));
			}
		}
		return $this->_models[$this->_current]['object'];
	}

	public function meta()
	{
		return $this->_models[$this->_current];
	}

	public function fields()
	{
		return $this->_models[$this->_current]['fields'];
	}

	public function initialize($fields)
	{
		$_model = $this->model();
		$_meta = $this->meta();
		$_fields = $this->fields();
		foreach ($fields as $name => $field)
		{
			if ($field instanceof Yada_Field)
			{
				$field->initialize($this, $_model, $name);
				$_fields[$name] = new ArrayObject(array(
					'object' => $field,
					'name'   => $name,
					'class'  => get_class($field),
					'loaded' => FALSE,
				), ArrayObject::ARRAY_AS_PROPS);

				if ($field instanceof Yada_Field_Interface_Related)
				{
					$_fields[$name]['related'] = $field->related();
					if ($field instanceof Yada_Field_Interface_Through)
					{
						$_fields[$name]['through'] = $field->through();
					}
				}
				elseif ($field instanceof Yada_Field_Interface_Column)
				{
					$_fields[$name]['column'] = $field->column($_meta['alias']);
					$_fields[$name]['alias'] = $_meta['alias'].'_'.$name;
					$_fields[$name]['default'] = $field->default;
				}
				elseif ($field instanceof Yada_Field_Interface_Expression)
				{
					$_fields[$name]['expression'] = $field->expression($_meta['alias']);
					$_fields[$name]['alias'] = $_meta['alias'].'_'.$name;
				}
				$this->_initialize($name, $field);
			}
		}
		return $this;
	}

	public function get_field($model, $name)
	{
		$this->model($model);
		$fields = $this->fields();
		if (isset($fields[$name]['related']))
		{
			$related = $fields[$name]['related'];
			if (is_array($related))
			{
				list($related, $field) = $related;
			}
			return $this->model($related);
		}

		$collect = $this->collect();
		if ($collect instanceof Yada_Collect)
		{
			if ($fields[$name]['loaded'] == TRUE)
			{
				return $collect->$name;
			}
			
		}

		return $this->mapper()->field($fields[$name]);
	}

	public function set_field($model, $name, $value)
	{
		$this->model($model);
		
		$collect = $this->collect();
		if ($collect instanceof Yada_Collect)
		{
			return $collect->$name = $value;
		}

		else
		{
			$fields = $this->fields();
			return $this->mapper()->field($fields[$name])->$name = $value;
		}
	}

	public function export(Yada_Interface_Aggregate $object)
	{
		$exported = isset(self::$_exported) ? self::$_exported : array();
		$object->register($this, $exported);
	}

}