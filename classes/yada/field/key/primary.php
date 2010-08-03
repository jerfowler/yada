<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Handles primary keys.
 *
 * Currently, a primary key can be an integer, float, or a string.
 *
 * @package  Yada
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
