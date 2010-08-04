<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: Yet Another Data Abstraction
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 *
 */
abstract class Yada_Mapper_Kohana_Core extends Yada_Mapper
{
	protected static $_exported = array('load', 'save');

	protected function _load($limit = NULL, $offset = NULL)
	{

	}

	protected function _save()
	{
		$values = $this->values();
		$clause = $this->clause();
		$meta = $this->_meta();
		if ($clause->count() != 0)
		{
			$query = DB::update($meta['table']);
			$pairs = array();
			foreach ($values as $set)
			{
				list($field, $value) = $set;
				
			}
			foreach ($clause as $set)
			{
				//list($op, $)
			}
		}
	}

}