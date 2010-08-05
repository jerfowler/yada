<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: Yet Another Data Abstraction
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
 
abstract class Yada_Field_Data_Boolean extends Yada_Field_Data
{
	public function __construct($options = array())
	{
		parent::__construct($options);
		// How TRUE is represented in the database
		$this->true = (isset($this->true)) ? $this->true : 1;

		// How TRUE is represented to users (mainly in forms)
		$this->label_true = (isset($this->label_true)) ? $this->label_true : 'Yes';

		// How FALSE is represented in the database
		$this->false = (isset($this->false)) ? $this->false : 1;

		// How FALSE is represented to users (mainly in forms)
		$this->label_false = (isset($this->label_false)) ? $this->label_false : 'No';
	}

	/**
	 * Validates a boolean out of the value with filter_var
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function set($value)
	{
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Returns the value as it should be represented in the database
	 *
	 * @param   Jelly_Model  $model
	 * @param   mixed		$value
	 * @param   boolean	  $loaded
	 * @return  mixed
	 */
	public function save($model, $value, $loaded)
	{
		return ($value) ? $this->true : $this->false;
	}
}