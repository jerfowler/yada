<?php defined('SYSPATH') or die('No direct script access.');


/*
 * Yada: Yet Another Data Abstraction
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 *
 * Handles belongs to relationships
 *
 */
abstract class Yada_Field_Key_Foreign extends Yada_Field_Key implements Yada_Field_Interface_Related
{
	public function initialize($meta, $model, $column)
	{
		parent::initialize($meta, $model, $column);
		if ( ! $this->related)
		{
			$this->related = $column;
		}
	}

	public function related()
	{
		return $this->related;
	}
}
