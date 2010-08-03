<?php defined('SYSPATH') or die('No direct script access.');

abstract class Yada_Field_Data_Float extends Yada_Field_Data
{
        public function __construct($options = array())
        {
            parent::__construct($options);
            // The number of places to round the number, NULL to forgo rounding
            $this->places = (isset($this->places)) ? $this->places : NULL;
        }

	/**
	 * Converts to float and rounds the number if necessary
	 *
	 * @param   mixed  $value
	 * @return  mixed
	 */
	public function set($value)
	{
		if ($value === NULL OR ($this->null AND empty($value)))
		{
			return NULL;
		}

		$value = (float)$value;

		if (is_numeric($this->places))
		{
			$value = round($value, $this->places);
		}

		return $value;
	}
}