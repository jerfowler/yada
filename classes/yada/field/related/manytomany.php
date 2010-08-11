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
	public function initialize($meta, $model, $column)
	{
		parent::initialize($meta, $model, $column);
		if (! $this->through)
		{
			throw new Kohana_Exception(
				'No through option specified for many-to-many field :field in model :model',
				array(':field' => $column, ':model' => Yada::common_name('model', $model)));
		}
	}

	/**
	 *
	 * @return Yada_Model
	 */
	public function related()
	{
		// Get the through field to the through model
		// Set the through field's related to this
		$through = $this->through();

		// Set/Get the related field to the related model
		$related = parent::related();

		// Link the models back the other direction
		$related->link($through);

		return $related;
	}

	/**
	 *
	 * @param Yada_Field_Interface_Through $through
	 */
	public function link(Yada_Field_Interface_Through $through)
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

			// Focus the through model
			$this->meta->model($through->model);

			// Get the Yada Field Object that points back to this model
			$field = $this->meta->fields->$field;

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

		if ( ! $this->through instanceof Yada_Field_Foreign)
		{
			if (is_array($this->through) AND count($this->through) == 2)
			{
				list($model, $field) = $this->through;
			}
			elseif (is_string($this->through))
			{
				$model = $this->through;
				$field = $this->name;
			}
			else
			{
				throw new Kohana_Exception('Invalid through value for Field :field in Model :Model', array(
					':field' => $this->name, ':model' => Yada::common_name('model', $this->model)
				));
			}

			if (is_array($model) AND count($model) == 2)
			{
				list($model, $init) = $model;
				$model = Yada::model($model, $init);
			}

			// Focus the through model
			$this->meta->model($model);

			// Get the Yada Field Object that points back to this model
			$field = $this->meta->fields->$field;

			// Set that field's properties to point back to this model/field
			$field->related = $this;

			// Save the reference to that field
			$this->through = $field;

		}
		// return the through model
		return $this->through;
	}
}
