<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: Yet Another Data Abstraction
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 */
 
abstract class Yada_Field_Key_Core extends Yada_Field implements Yada_Field_Interface_Column
{

	public function save($model, $value, $loaded)
	{
		return $value;
	}

}