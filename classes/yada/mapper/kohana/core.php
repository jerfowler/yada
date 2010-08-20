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
	 * @var array
	 */
	protected static $_exported = array('load', 'save', 'begin', 'end', 'field', 'values', 'select', 'exclude');

	/**
	 *
	 * @var array
	 */
	protected static $_ops = array(
		'begin'	    => array('(', 'NOT ('),
		'end'	    => array(')', ''),
		'and'	    => array('AND', 'AND NOT'),
		'or'	    => array('OR', 'OR NOT'),
		'eq'	    => array('=', '!='),
		'gt'	    => array('>', '<='),
		'lt'	    => array('<', '>='),
		'is'	    => array('IS', 'IS NOT'),
		'in'	    => array('IN', 'NOT IN'),
		'between'   => array('BETWEEN', 'NOT BETWEEN')
	);

	/**
	 *
	 * @var Database
	 */
	protected $_db = NULL;

	/**
	 *
	 * @var array
	 */
	protected $_query = array(
	    'select' => array(),
	    'from' => array(),
	    'where' => array(),
	    'group' => array(),
	    'order' => array(),
	    'having' => array(),
	);

	/**
	 *
	 * @var SplObjectStorage
	 */
	protected $_related = NULL;

	public function __construct(Yada_Meta $meta, Yada_Model $model, $values = NULL)
	{
		parent::__construct($meta, $model, $values);
		$database = (isset($model::$database)) ? $model::$database : NULL;
		$this->_db = Database::instance($database);
		$this->_related = new SplObjectStorage;
	}

	protected function _select_fields(ArrayObject $fields, ArrayObject $select, ArrayObject $exclude)
	{
		ini_set('xdebug.var_display_max_depth', 2 );
		var_dump('Select Fields');
		var_dump($fields);
		var_dump($select);
		var_dump($exclude);

		$pointer =& $this->_query['select'];
		foreach($fields as $field)
		{
			if ($exclude->offsetExists($field->name)) continue;
			if ($select->count() == 0 or $select->offsetExists($field->name))
			{		    
				if ($field instanceof Yada_Field_Interface_Column)
				{
					$pointer[] = $this->_db->quote_identifier(array(
						$field->column(), $field->alias()));
				}
				elseif($field instanceof Yada_Field_Interface_Expression)
				{
					$pointer[] = $this->_db->quote_identifier(array(
						DB::expr($field->expression()),	$field->alias()));
				}
			}
		}
	}

	protected function _process_values(ArrayObject $values)
	{
		
		ini_set('xdebug.var_display_max_depth', 2 );
		var_dump('Where Values');
		var_dump($values);

		foreach($values as $name => $value)
		{
			if (($value === NULL) OR ($value === TRUE) OR ($value === FALSE))
			{
				$op = 'is';
			}
			else
			{
				$op = (is_array($value)) ? 'in' : 'eq';
			}
			$this->field($this->_model, $name)->$op($value);
		}
	}

	protected function _process_clauses(ArrayObject $clauses)
	{
		var_dump('Process Clauses');
		ini_set('xdebug.var_display_max_depth', 3 );
		var_dump($clauses);

		$glue = array_merge(self::$_ops['begin'], self::$_ops['and'], self::$_ops['or']);
		foreach ($clauses as $clause)
		{
			list ($field, $op, $value) = $clause;
			$ops = explode('_', $op);
			$ops = array_combine($ops, $ops);

			if ($field instanceof Yada_Field_Interface_Column)
			{
				$pointer =& $this->_query['where'];
				$subject = $field->column();

			}
			elseif($field instanceof Yada_Field_Interface_Expression)
			{
				$pointer =& $this->_query['having'];
				$subject = $field->alias();

			}
			else
			{
				$pointer =& $this->_query['where'];
				$subject = '';
			}

			if (isset($ops['not']))
			{
				$not = 1;
				unset ($ops['not']);
			}
			else
			{
				$not = 0;
			}


			if (isset($ops['or']))
			{
				$pointer[] = self::$_ops['or'][$not];
				unset ($ops['or']);
				$not = 0;
			}
			elseif (isset($ops['and']))
			{
				$pointer[] = self::$_ops['and'][$not];
				unset ($ops['and']);
				$not = 0;
			}

			if (isset($ops['begin']))
			{
				$pointer[] = self::$_ops['begin'][$not];
				unset($ops['begin']);
				$not = 0;
			}
			elseif (isset($ops['end']))
			{
				$pointer[] = self::$_ops['end'][$not]; // should always be 0....
				unset($ops['end']);
			}

			if ( ! empty($ops))
			{
				if ( ! in_array (end($pointer), $glue))
				{
					$pointer[] = self::$_ops['and'][0];
				}
				$expr = array();
				$expr[] = $this->_db->quote_identifier($subject);
				foreach ($ops as $op)
				{
					$expr[] = isset(self::$_ops[$op])
						? self::$_ops[$op][$not]
						: strtoupper($op);
				}
				$expr[] = $this->_db->quote($value);
				$pointer[] = implode(' ', $expr);
			}

		}
	}

	protected function _process_related(ArrayObject $related, Yada_Model $model)
	{
		ini_set('xdebug.var_display_max_depth', 2 );
		var_dump('Process Related');
		var_dump($related);
		$this->_related->attach($model);
		if (isset($related['join']))
		{
			$this->_related_join($related, $model);
		}
	}

	protected function _build_join(Yada_Field_Interface_Related $left, Yada_Field_Interface_Related $right, $type = '')
	{
		$expr = array();
		$expr[] = $type.' JOIN';
		$expr[] = $this->_db->quote_table($right->table());
		$expr[] = 'ON';
		$expr[] = $this->_db->quote_identifier($left->column());
		$expr[] = '=';
		$expr[] = $this->_db->quote_identifier($right->column());
		$this->_query['from'][] = implode(' ', $expr);
	}

	protected function _related_join(ArrayObject $related, Yada_Model $model)
	{
		ini_set('xdebug.var_display_max_depth', 2 );
		var_dump('Join Related');
		var_dump($related);

		foreach ($related['join'] as $name => $type)
		{
			$field = $this->_field($name, $model);

			if ($field instanceof Yada_Field_Interface_Through)
			{
				$through = $field->through();
				$model = $through->model;
				if ($this->_related->contains($model)) continue;
				$this->_build_join($through, $field, $type);
				$this->_build_select($model);
				$field = $through->through;
			}
			if ($field instanceof Yada_Field_Interface_Related)
			{
				$related = $field->related();
				$model = $related->model;
				if ($this->_related->contains($model)) continue;
				$this->_build_join($related, $field, $type);
				$this->_build_select($model);
			}
		}
		
	}

	protected function _build_select(Yada_Model $model)
	{
		$exclude = $this->_exclude();
		$exclude = $exclude->offsetExists($model)
			? $exclude->offsetGet($model)
			: new ArrayObject(array());
		$this->_select_fields($this->_fields($model), $this->_select($model), $exclude);
		$this->_process_values($this->_values($model));
		$this->_process_clauses($this->_clauses($model));
		$this->_process_related($this->_related($model), $model);
	}

	/**
	 *
	 * @param <type> $limit
	 * @param <type> $offset
	 */
	protected function _load($limit = NULL, $offset = NULL)
	{
		$meta = $this->_meta->meta();
		$this->_query['from'][] = $this->_db->quote_table(array($meta->table, $meta->alias));
		$this->_build_select($this->_model);
		var_dump($this->_query);
		return TRUE;
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