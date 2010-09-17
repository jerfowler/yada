<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 * Handles many to many relationships
 *
 */

abstract class Yada_Field_Related_ManyToMany extends Yada_Field_Related implements Yada_Field_Interface_Through
{

	public function _init_dynamic(Yada_Model $model, $field)
	{
		if ( ! isset($model->_init['fields']))
		{
			$model->_init['fields'] = array();
		}

		if ( ! isset($model->_init['fields'][$field]))
		{
			$through = Yada::field('Foreign');
			$this->through = $through;
			$through->related = $this;
			$model->_init['fields'][$field] = $through;
		}

		$related = $this->related;
		if (is_array($related->through))
		{
			list($m, $field) = $related->through;
		}
		else
		{
			$field = $this->name;
		}

		if ( ! isset($model->_init['fields'][$field]))
		{
			$link = Yada::field('Foreign');
			$link->related = $related;
			$link->through = $through;
			$through->through = $link;
			$model->_init['fields'][$field] = $link;

		}

		if ( ! isset($model->_init['table']))
		{
			$table = array();
			$meta = $this->meta->meta($this->model);
			$table[] = $meta->offsetExists('table') ? $meta->table : $meta->plural;
			$meta = $this->meta->meta($related->model);
			$table[] = $meta->offsetExists('table') ? $meta->table : $meta->plural;
			sort($table);
			$model->_init['table'] = implode('_', $table);
		}
	}

	public function initialize(Yada_Meta $meta, Yada_Model $model, $name, $alias)
	{
		parent::initialize($meta, $model, $name, $alias);
		if (! $this->through)
		{
			$this->through = 'Dynamic';
		}
	}

	/**
	 *
	 * @return Yada_Model
	 */
	public function related()
	{
		if ( ! $this->related instanceof Yada_Field_Related_ManyToMany)
		{
			// Set/Get the related field to the related model
			$related = parent::related();

			// Get the through field to the through model
			// Set the through field's related to this
			$through = $this->through();
			
			// Link the models back the other direction
			$related->link($through);
		}
		return $this->related;
	}

	/**
	 *
	 * @param Yada_Field_Foreign $through
	 */
	public function link(Yada_Field_Key $through)
	{
		if ( ! $this->through instanceof Yada_Field_Foreign)
		{

			if (is_array($this->through) AND count($this->through) == 2)
			{
				list($model, $field) = $this->through;
			}
			elseif (is_string($this->through))
			{
				$field = $this->name;
			}
			else
			{
				throw new Kohana_Exception('Invalid through value for Field :field in Model :Model', array(
					':field' => $this->name, ':model' => Yada::common_name('model', $this->model)
				));
			}

			// Focus the through model and get the meta data
			$meta = $this->meta->meta($through->model);

			// Get the Yada Field Object that points back to this model
			$field = $meta->fields->$field;

			// Set that field's properties to point back to this model/field
			$field->related = $this;
			
			// Create the bidirectional through link
			$field->through = $through;
			$through->through = $field;

			// Save the reference to that field
			$this->through = $field;
		}
	}

	/**
	 *
	 * @return Yada_Field_Foreign
	 */
	public function through()
	{
		var_dump('ManytoMany Through');
		if ( ! $this->through instanceof Yada_Field_Foreign)
		{
			$init = NULL;
			if (is_array($this->through))
			{
				list($model, $field) = $this->through;
				if (is_array($model))
				{
					list($model, $init) = $model;
				}				
			}
			elseif (is_string($this->through))
			{
				$model = $this->through;
				$field = $this->related->name;
			}
			else
			{
				throw new Kohana_Exception(
					'No through option specified for many-to-many field :field in model :model',
					array(':field' => $column, ':model' => Yada::common_name('model', $model)));
			}

			$model = Yada::model($model, $init);
			$dynamic = Yada::class_name('model', 'Dynamic');
			if ($model instanceof $dynamic)
			{
				$this->_init_dynamic($model, $field);
				$this->meta->attach($model);
			}
			else
			{
				// Focus the through model and get the meta data
				$meta = $this->meta->meta($model);

				// Get the Yada Field Object that points back to this model
				$field = $meta->fields->$field;

				// Set that field's properties to point back to this model/field
				$field->related = $this;

				// Save the reference to that field
				$this->through = $field;
			}

		}
		// Focus the through model
		$this->meta->model($this->through);
		// return the through model
		return $this->through;
	}
}
