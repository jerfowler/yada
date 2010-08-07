<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 *
 */
abstract class Yada_Mapper_Kohana_Core extends Yada_Mapper
{
	/**
	 *
	 * @var <type>
	 */
	protected static $_exported = array('load', 'save');

	/**
	 *
	 * @param <type> $limit
	 * @param <type> $offset
	 */
	protected function _load($limit = NULL, $offset = NULL)
	{
		$meta = $this->_meta->meta();
		$columns = $this->_meta->columns();
		$expressions = $this->_meta->expressions();

		$query = DB::select();
		foreach($columns as $field)
		{
			$query->select(array($field->column, $field->alias));
		}
		foreach($expressions as $field)
		{
			$query->select(array(DB::expr($field->expression), $field->alias));
		}
		$query->from(array($meta->table, $meta->alias));

		$values = $this->values();
		foreach($values as $name => $pair)
		{
			list($field, $value) = $pair;
			$query->where($field->column, '=', $value);
		}

		$clauses = $this->clause();
		foreach($clauses as $clause)
		{
			if (is_array($clause))
			{
			    var_dump($clause);
			    list($op, $pair) = $clause;
			    list($field, $value) = $pair;
			    $query->where($field->column, $op, $value);
			}
		}

		var_dump($query->compile(Database::instance()));

	}

	/**
	 *
	 */
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