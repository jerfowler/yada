<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: Yet Another Data Abstraction
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 *
 * Handles primary keys.
 * Currently, a primary key can be an integer, float, or a string.
 *
 */
abstract class Yada_Field_Key_Primary extends Yada_Field_Key
{
	
	public function  __construct($options = array()) 
	{
		parent::__construct($options);
		$this->unique = TRUE;
	}

	/**
	 * Converts numeric IDs to ints/floats
	 *
	 * @param   mixed  $value
	 * @return  int|string
	 */
	public function set($value)
	{
		if ($value)
		{
			if (is_int($value) OR is_float($value))
			{
				return $value;
			}
			if (is_numeric($value))
			{
				// Automatic conversion...
				return $value * 1;
			}
			else
			{
				return (string)$value;
			}
		}
		else
		{
			// Empty values should be null so
			// they are auto-incremented properly
			return NULL;
		}
	}
}