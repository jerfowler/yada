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
abstract class Yada_Mapper_SQL_Core extends Yada_Mapper
{
	/**
	 *
	 * @var array
	 */
	protected static $_exported = array('load', 'save', 'begin', 'end', 'field', 'values', 'select', 'exclude', 'loaded', 'subquery', 'sql', 'params');

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
	 * @var array
	 */
	protected $_queries = array();

	/**
	 *
	 * @var array
	 */
	protected $_params = array();

	/**
	 *
	 * @var array
	 */
	protected $_filtered = array();

	/**
	 *
	 * @var SplObjectStorage
	 */
	protected $_linked = NULL;

	/**
	 *
	 * @param mixed $value 
	 */
	abstract protected function _prepare($sql);
	abstract protected function _bind_value($query, $param, $value);
	abstract protected function _bind_param($query, $param, &$var);
	abstract protected function _execute($query, Array $data = NULL);
	abstract protected function _is_joinable($mapper);
	abstract protected function _table_prefix($table);

	/**
	 *
	 * @param Yada_Meta $meta
	 * @param Yada_Model $model
	 * @param <type> $values
	 */
	public function __construct(Yada_Meta $meta, Yada_Model $model, $values = NULL)
	{
		parent::__construct($meta, $model, $values);
		$this->_linked = new SplObjectStorage;
	}

	protected function _init_query($name)
	{
		if (in_array($name, array('load', 'sub')))
		{
			$this->_queries[$name] =  array(
				'select' => array(),
				'from' => array(),
				'where' => array(),
				'group' => array(),
				'having' => array(),
				'order' => array(),
				'limit' => NULL,
				'offset' => NULL
			);
			$meta = $this->_meta();
			$this->_queries[$name]['from'][] = $this->_table_prefix($meta->table).' AS '.$meta->alias;
		}
		
		$this->_params[$name] = array();
		$this->_filtered[$name] = new SplObjectStorage;		

	}

	protected function _add_param($query, $value, $name = NULL)
	{
		if (is_array($value))
		{
			$out = array();
			foreach ($value as $val)
			{
				$idx = ':'.count($this->_params[$query]);
				$this->_params[$query][$idx] = $value;
				$out[] = $idx;
			}
			return '('.implode(', ', $out).')';
		}
		//else
		$idx  = ':'.$this->_index.'_';
		$idx .= (isset ($name)) ? $name : count($this->_params[$query]);
		$this->_params[$query][$idx] = $value;
		return $idx;
	}

	/**
	 *
	 * @param ArrayObject $fields
	 * @param ArrayObject $select
	 * @param ArrayObject $exclude
	 */
	protected function _build_select(ArrayObject $fields, ArrayObject $select, ArrayObject $exclude)
	{
		ini_set('xdebug.var_display_max_depth', 2 );
		var_dump('Build Select');
//		var_dump($fields);
//		var_dump($select);
//		var_dump($exclude);

		$pointer =& $this->_queries['load']['select'];

		foreach($fields as $field)
		{
			if ($exclude->offsetExists($field->name)) continue;
			if ($select->count() == 0 or $select->offsetExists($field->name))
			{		    
				if ($field instanceof Yada_Field_Interface_Column)
				{
					$pointer[] = $field->column().' AS '.$field->alias();
				}
				elseif($field instanceof Yada_Field_Interface_Expression)
				{
					$pointer[] = $field->expression().' AS '.$field->alias();
				}
			}
		}
	}

	/**
	 *
	 * @param Yada_Model $model
	 */
	protected function _process_model($query, Yada_Model $model)
	{
		var_dump('Process Model');
		$this->_convert_values($this->_values($model));
		$this->_build_clauses($query, $this->_clauses($model));
		$this->_process_related($query, $model);
	}

	/**
	 *
	 * @param ArrayObject $values
	 */
	protected function _convert_values(ArrayObject $values)
	{
		
		ini_set('xdebug.var_display_max_depth', 2 );
		var_dump('Process Values');
//		var_dump($values);


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

	/**
	 *
	 * @param ArrayObject $clauses
	 */
	protected function _build_clauses($query, ArrayObject $clauses)
	{
		var_dump('Build Clauses');
		ini_set('xdebug.var_display_max_depth', 3 );
		var_dump($clauses);

		$glue = array_merge(self::$_ops['begin'], self::$_ops['and'], self::$_ops['or']);
		foreach ($clauses as $clause)
		{
			list ($field, $op, $value) = $clause;
			$field = (is_string($field)) ? $this->_field($field): $field;
			$ops = explode('_', $op);
			$ops = array_combine($ops, $ops);

			if ($field instanceof Yada_Field_Interface_Column)
			{
				$pointer =& $this->_queries[$query]['where'];
				$subject = $field->column();

			}
			elseif($field instanceof Yada_Field_Interface_Expression)
			{
				$pointer =& $this->_queries[$query]['having'];
				$subject = $field->alias();
			}
			else
			{
				$pointer =& $this->_queries[$query]['where'];
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
				if ( ! empty($pointer) AND ! in_array (end($pointer), $glue))
				{
					$pointer[] = self::$_ops['and'][0];
				}
				$pointer[] = self::$_ops['begin'][$not];
				unset($ops['begin']);
				$not = 0;
			}
			elseif (isset($ops['end']))
			{
				$pointer[] = self::$_ops['end'][0]; // should always be 0....
				unset($ops['end']);
			}

			if ( ! empty($ops))
			{
				if ( ! empty($pointer) AND ! in_array (end($pointer), $glue))
				{
					$pointer[] = self::$_ops['and'][0];
				}
				$expr = array();
				$expr[] = $subject;
				foreach ($ops as $op)
				{
					$expr[] = isset(self::$_ops[$op])
						? self::$_ops[$op][$not]
						: strtoupper($op);
				}
				if ($op == 'is')
				{
					if ($value === NULL)
					{
						$expr[] = 'NULL';
					}
					else
					{
						$expr[] = ($value) ? 'TRUE' : 'FALSE';
					}
				}
				elseif ($op == 'between')
				{
					list($min, $max) = $value;
					$min = $this->_add_param($query, $min);
					$max = $this->_add_param($query, $max);
					$expr[] = $min.' AND '.$max;
				}
				else
				{
					$expr[] = $this->_add_param($query, $value);
				}
				$pointer[] = implode(' ', $expr);
			}

		}

	}

	/**
	 *
	 * @param ArrayObject $related
	 * @param Yada_Model $model
	 */
	protected function _process_related($query, Yada_Model $model)
	{
		ini_set('xdebug.var_display_max_depth', 2 );
		var_dump('Process Related');
		$related = $this->_related($model);
		var_dump($related);

		if (isset($related['filter']))
		{
			var_dump('Process Related: filter');
			foreach($related['filter'] as $filter)
			{
				if ($this->_filtered[$query]->contains($filter)) continue;
				$this->_process_model($query, $filter);
				$this->_filtered[$query]->attach($filter);
			}
		}
	}

	/**
	 *
	 * @param Yada_Field_Interface_Related $right
	 * @param Yada_Field_Interface_Related $left
	 * @param <type> $type
	 */
	protected function _build_join($query, Yada_Field_Interface_Related $right, Yada_Field_Interface_Related $left, $type = '')
	{
		var_dump('Build Join');

		list($table, $alias) = $right->table();

		$expr = array();
		$expr[] = $type.' JOIN';
		$expr[] = $this->_table_prefix($table);
		$expr[] = 'AS';
		$expr[] = $alias;
		$expr[] = 'ON';
		$expr[] = $left->column();
		$expr[] = '=';
		$expr[] = $right->column();

		var_dump($expr);
		$this->_queries[$query]['from'][] = implode(' ', $expr);
	}


	/**
	 *
	 * @return <type> 
	 */
	protected function _build_select_query($query)
	{
		var_dump('Build SELECT Query');
		$sql = array();
		$nl =  "\n";
		$sql[] = 'SELECT DISTINCT';
		$sql[] = implode($nl.', ', $this->_queries[$query]['select']);
		$sql[] = $nl.'FROM';
		$sql[] = implode($nl.' ', $this->_queries[$query]['from']);
		if ( ! empty($this->_queries[$query]['where']))
		{
			$sql[] = $nl.'WHERE';
			$sql[] = implode($nl.' ', $this->_queries[$query]['where']);
		}
		if ( ! empty($this->_queries[$query][$query]))
		{
			$sql[] = $nl.'GROUP BY';
			$sql[] = implode($nl.', ', $this->_queries[$query]['group']);
		}
		if ( ! empty($this->_queries[$query]['having']))
		{
			$sql[] = $nl.'HAVING';
			$sql[] = implode($nl.' ', $this->_queries[$query]['having']);
		}
		if ( ! empty($this->_queries[$query]['order']))
		{
			$sql[] = $nl.'ORDER BY';
			$sql[] = implode($nl.', ', $this->_queries[$query]['order']);
		}
		if ( ! empty($this->_queries[$query]['limit']))
		{
			$sql[] = $nl.'LIMIT '.$this->_queries[$query]['limit'];
		}
		if ( ! empty($this->_queries[$query]['offset']))
		{
			$sql[] = $nl.'OFFSET '.$this->_queries[$query]['offset'];
		}
		return implode(' ', $sql);
	}

	/**
	 *
	 * @param <type> $limit
	 * @param <type> $offset
	 */
	protected function _load($limit = NULL, $offset = NULL)
	{
		var_dump('Load');
		//$this->_params = array();

		if (( ! isset($this->_queries['load'])) OR $this->_state == 'changed')
		{
			$this->_init_query('load');
			
			if ( ! is_null($limit))
			{
				$offset = is_null($offset) ? 0 : $offset;
				$this->_queries['load']['limit'] = $this->_add_param('load', $limit, 'limit');
				$this->_queries['load']['offset'] = $this->_add_param('load', $offset, 'offset');
			}
			
			if ( ! $this->_filtered['load']->contains($this->_model))
			{
	//			$exclude = $this->_exclude();
	//			$exclude = $exclude->offsetExists($model)
	//				? $exclude->offsetGet($model)
	//				: new ArrayObject(array());
				$exclude = new ArrayObject(array());
				$this->_build_select($this->_fields($this->_model), $this->_select($this->_model), $exclude);
				$this->_process_joins('load');
				$this->_filtered['load']->attach($this->_model);

				$related = $this->_related();
				if (isset($related['sub']))
				{
					var_dump('Process Related: sub');
					foreach($related['sub'] as $values)
					{
						var_dump($values);
						list($field, $related) = $values;
						$sub = $related->model;
						$expr = array();
						$expr[] = $field->column();
						$expr[] = 'IN (';
						$expr[] = $sub->subquery($related);
						$expr[] = ')';
						$this->_queries['load']['where'][] = implode(' ', $expr);
						$this->_params['load'] += $sub->params('sub');
					}
				}
				else
				{
					$this->_process_model($this->_model);
				}
			}

			$sql = $this->_build_select_query('load');
			$this->_queries['load'] = $this->_prepare($sql);
		}
		else
		{
			if ( ! is_null($limit))
			{
				$offset = is_null($offset) ? 0 : $offset;
				$this->_add_param('load', $limit, 'limit');
				$this->_add_param('load', $offset, 'offset');
			}
		}

		
		ini_set('xdebug.var_display_max_data', 2048);
		var_dump($this->_queries['load']);
		var_dump($this->_params['load']);

		foreach ($this->_params['load'] as $key => $value)
		{
			$this->_bind_value($this->_queries['load'], $key, $value);
		}

		$this->_execute($this->_queries['load']);
		$this->_queries['load']->setFetchMode(PDO::FETCH_ASSOC);
		$result = $this->_queries['load']->fetchAll();

		ini_set('xdebug.var_display_max_depth', 4 );
		var_dump($result);

		var_dump(Profiler::group_stats('yada pdo (default), prepare:'));
		var_dump(Profiler::group_stats('yada pdo (default), execute:'));
		return TRUE;
	}

	protected function _link($field, $related, $reverse = FALSE)
	{
		var_dump('Link');
		$this->_linked[$field] = array($related, $reverse);
	}

	protected function _process_joins($query)
	{
		var_dump('Process Joins: '. $query);

		foreach($this->_linked as $field)
		{
			$values = $this->_linked->offsetGet($field);
			list($related, $reverse) = $values;
			if ($this->_is_joinable($related->mapper))
			{
				if ($reverse === FALSE)
				{
					$type = ($field instanceof Yada_Field_Foreign)
						? 'INNER'
						: 'LEFT OUTER';

					if ($field instanceof Yada_Field_Interface_Through)
					{
						var_dump('Link: Yada_Field_Interface_Through');
						$type = 'LEFT OUTER';
						$through = $field->through();
						$this->_build_join($query, $through, $field, $type);
						$field = $through->through;
					}

					$this->_build_join($query, $related, $field, $type);
					$this->filter($related->model);
				}
				else
				{
					$this->sub($field, $related);
				}
			}
			else
			{
			//TODO
			}
		}
	}

	/**
	 *
	 */
	protected function _save()
	{
		var_dump('Save');
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

	public function sql($model = NULL, $field = NULL)
	{
		if ( ! $this->_filtered['load']->contains($this->_model))
		{
//			$exclude = $this->_exclude();
//			$exclude = $exclude->offsetExists($model)
//				? $exclude->offsetGet($model)
//				: new ArrayObject(array());
			$exclude = new ArrayObject(array());
			$this->_build_select($this->_fields($this->_model), $this->_select($this->_model), $exclude);
			$this->_filtered['load']->attach($this->_model);
		}

		$this->_process_model($this->_model);
		return $this->_build_select_query();
	}

	public function subquery($model = NULL, $field = NULL)
	{
		if ($model instanceof Yada_Model)
		{
			$field = isset($field) ? $field : $this->_field;
		}
		else
		{
			$model = $this->_model;
			$field = isset($model) ? $model : $this->_field;
		}

		$this->field($model, $field);
		$field = $this->_field;

		$this->_init_query('sub');

		if ( ! $this->_filtered['sub']->contains($this->_model))
		{
			$this->_filtered['sub']->attach($this->_model);
			$this->_queries['sub']['select'] = array($field->column());
			$this->_process_joins('sub');
			$related = $this->_related();
			if (isset($related['sub']))
			{
				foreach($related['sub'] as $values)
				{
					list($field, $related) = $values;
					$sub = $related->model;
					$expr = $field->column().' IN ('.$sub->subquery($related).')';
					$this->_queries['sub']['where'][] = $expr;
					$this->_params['sub'] += $sub->params('sub');
				}
			}
			else
			{
				$this->_process_model('sub', $this->_model);
			}
		}

		return $this->_build_select_query('sub');
	}

	public function params($model = NULL, $query = NULL)
	{
		if ($model instanceof Yada_Model)
		{
			$query = isset($query) ? $query : 'load';
		}
		else
		{
			$query = isset($model) ? $model : 'load';
		}
		return isset($this->_params[$query]) ? $this->_params[$query] : array();
	}
}