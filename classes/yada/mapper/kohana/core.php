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
	protected static $_exported = array('load', 'save', 'begin', 'end');

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

	protected $_query = array(
	    'select' => array(),
	    'from' => array(),
	    'where' => array(),
	    'group' => array(),
	    'having' => array(),
	);

	public function __construct(Yada_Meta $meta, Yada_Model $model, $values = NULL)
	{
		parent::__construct($meta, $model, $values);
		$database = (isset($model::$database)) ? $model::$database : NULL;
		$this->_db = Database::instance($database);
	}

	protected function _process_clauses()
	{
		$clauses = $this->_clauses();
		var_dump('Process Clauses');
		ini_set('xdebug.var_display_max_depth', 3 );
		var_dump($clauses);
		$expr = array();
		$pointer =& $this->_query['where'];
		foreach ($clauses as $clause)
		{
			list ($field, $op, $value) = $clause;
			$ops = explode('_', $op);
			$ops = array_combine($ops, $ops);

			if (isset($ops['not']))
			{
				$not = 1;
				unset ($ops['not']);
			}
			else
			{
				$not = 0;
			}

			if (isset($ops['and']))
			{
				$expr[] = self::$_ops['and'][$not];
				unset ($ops['and']);
				$not = 0;
			}

			if (isset($ops['or']))
			{
				$expr[] = self::$_ops['or'][$not];
				unset ($ops['or']);
				$not = 0;
			}

			if (isset($ops['begin']))
			{
				$expr[] = self::$_ops['begin'][$not];
			}
			elseif (isset($ops['end']))
			{
				$expr[] = self::$_ops['end'][$not];
			}
			elseif (count($ops) != 0)
			{
				if ($field instanceof Yada_Field_Interface_Column)
				{
					$expr[] = $this->_db->quote_identifier($field->column());
					$pointer =& $this->_query['where'];
				}
				elseif($field instanceof Yada_Field_Interface_Expression)
				{
					$expr[] = $this->_db->quote_identifier($field->alias());
					$pointer =& $this->_query['having'];
				}
				foreach ($ops as $op)
				{
					$expr[] = isset(self::$_ops[$op])
						? self::$_ops[$op][$not]
						: $op;
				}
				$expr[] = $this->_db->quote($value);
			}
			$pointer[] = implode(' ', $expr);
			$expr = array();
		}
	}

	protected function _select_fields()
	{
		$fields = $this->_fields();
		ini_set('xdebug.var_display_max_depth', 2 );
		var_dump('Select Fields');
		var_dump($fields);
		$select =& $this->_query['select'];
		foreach($fields as $field)
		{
			if ($field instanceof Yada_Field_Interface_Column)
			{
				$select[] = $this->_db->quote_identifier(array(
					$field->column(),
					$field->alias()));
			}
			elseif($field instanceof Yada_Field_Interface_Expression)
			{
				$select[] = $this->_db->quote_identifier(array(
					DB::expr($field->expression()),
					$field->alias()));
			}
		}
	}

	protected function _where_values()
	{
		$values = $this->_values();
		ini_set('xdebug.var_display_max_depth', 2 );
		var_dump('Where Values');
		var_dump($values);
		$expr = array();
		$pointer =& $this->_query['where'];
		foreach($values as $name => $value)
		{
			$field = $this->_field($name);
			if ($field instanceof Yada_Field_Interface_Column)
			{
				$expr[] = $this->_db->quote_identifier($field->column());
				$pointer =& $this->_query['where'];
			}
			elseif ($field instanceof Yada_Field_Interface_Expression)
			{
				$expr[] = $this->_db->quote_identifier($field->alias());
				$pointer =& $this->_query['having'];
			}

			if ( ! in_array(end($pointer), array(FALSE, '(')))
			{
				$pointer[] = 'AND';
			}

			if (is_null($value))
			{
				$expr[] = 'IS NULL';
			}
			elseif ($value === TRUE)
			{
				$expr[] = 'IS TRUE';
			}
			elseif ($value === FALSE)
			{
				$expr[] = 'IS FALSE';
			}
			else
			{
				$expr[] = (is_array($value)) ? 'IN' : '=';
				$expr[] = $this->_db->quote($value);
			}
			$pointer[] = implode(' ', $expr) ;
			$expr = array();
		}
	}

	protected function _join_related()
	{
		$related = $this->_related();
		ini_set('xdebug.var_display_max_depth', 2 );
		var_dump('Join Related');
		var_dump($related);
		$expr = array();
		if (isset($related['join']))
		{

			foreach ($related['join'] as $name => $value)
			{
				$field = $this->_field($name);
				$expr[] = 'JOIN';
				if ($field instanceof Yada_Field_Interface_Through)
				{
					$key = $this->_meta->key();
					$through = $field->through();
					$model = $through->model;
					$meta = $this->_meta->meta($model);
					$expr[] = $this->_db->quote_table(array(
						$meta->table, $meta->alias));
					$expr[] = 'ON';
					$expr[] = $this->_db->quote_identifier($key->column());
					$expr[] = '=';
					$expr[] = $this->_db->quote_identifier($through->column());
					$this->_query['from'][] = implode(' ', $expr);
					$expr = array();
					$field = $through->through;
				}
				if ($field instanceof Yada_Field_Interface_Related)
				{
					var_dump($field);
				}
				$this->_query['from'][] = implode(' ', $expr);
				$expr = array();
			}
		}
	}

	protected function _build_select()
	{
		$this->_select_fields();
		$meta = $this->_meta->meta();
		$this->_query['from'][] = $this->_db->quote_table(array($meta->table, $meta->alias));
		$this->_process_clauses();
		$this->_where_values();
		$this->_join_related();
	}

	/**
	 *
	 * @param <type> $limit
	 * @param <type> $offset
	 */
	protected function _load($limit = NULL, $offset = NULL)
	{
		$this->_build_select();
		var_dump($this->_query);
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

	/**
	 *
	 */
	public function join_parents()
	{

	}

	public function join_children()
	{

	}
}