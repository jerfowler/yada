<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: Yet Another Data Abstraction
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 *
 * Related Core
 * 
 */
abstract class Yada_Field_Related_Core extends Yada_Field implements Yada_Field_Interface_Related
{
	public function initialize($meta, $model, $column)
	{
		$this->meta = $meta;

		// This will come in handy for setting complex relationships
		$this->model = $model;

		// This is for naming form fields
		$this->name = $column;

		if ( ! $this->related)
		{
			$this->related = $column;
		}

		// Check for a name, because we can easily provide a default
		if ( ! $this->label)
		{
			$this->label = inflector::humanize($column);
		}
	}

	public function related()
	{
		return $this->related;
	}
}