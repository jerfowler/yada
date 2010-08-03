<?php defined('SYSPATH') or die('No direct script access.');

abstract class Yada_Field_Data_Core extends Yada_Field implements Yada_Field_Interface_Column
{
	/**
	 * Casts to a string, preserving NULLs along the way
	 *
	 * @param  mixed   $value
	 * @return string
	 */
	public function set($value)
	{
		if ($value === NULL OR ($this->null AND empty($value)))
		{
			return NULL;
		}

		return (string) $value;
	}

	/**
	 * Called just before saving if the field is $in_db, and just after if it's not.
	 *
	 * If $in_db, it is expected to return a value suitable for insertion
	 * into the database.
	 *
	 * @param   Yada  $model
	 * @param   mixed  $value
	 * @return  mixed
	 */
	public function save($model, $value, $loaded)
	{
		return $value;
	}

}