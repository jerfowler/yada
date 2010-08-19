<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 * Handles belongs to relationships
 *
 */

abstract class Yada_Field_Key_Foreign extends Yada_Field_Key implements Yada_Field_Interface_Related
{
	public function initialize(Yada_Meta $meta, Yada_Model $model, $name, $alias)
	{
		parent::initialize($meta, $model, $name, $alias);
		if ( ! $this->related)
		{
			$this->related = $name;
		}
	}

	public function column()
	{
		return $this->_props->offsetExists('column') ? $this->alias.'.'.$this->column : $this->alias.'.'.$this->name.'_id';
	}

	public function related()
	{
		if ( ! $this->related instanceof Yada_Field_Interface_Related)
		{
			if (is_array($this->related) AND count($this->related) == 2)
			{
				list($this->related, $field) = $this->related;
			}
			elseif (is_string($this->related))
			{
				$field = $this->name;
			}
			else
			{
				throw new Kohana_Exception('Invalid related value for Field :field in Model :Model', array(
					':field' => $this->name, ':model' => Yada::common_name('model', $this->model)
				));
			}

			// Get the mapper and save it
			$this->mapper = $this->meta->mapper();

			// Focus the through model and get the meta data
			$meta = $this->meta->meta($this->related);

			// Get the Yada Field Object that points back to this model
			$field = $meta->fields->$field;
			//$field->mapper = $meta->mapper;

			// Join the fields with the mappers....
			$this->mapper->field($this)->related('join', $field);
			//$field->mapper->field($field)->related($this);

			// Set that field's related to point back to this field
			$field->related = $this;

			// Save the reference to that field
			$this->related = $field;
		}
		// Focus the related model
		$this->meta->model($this->related);
		return $this->related;
	}
}

