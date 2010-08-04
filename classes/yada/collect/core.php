<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: Yet Another Data Abstraction
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 */
 
abstract class Yada_Collect_Core
implements Yada_Interface_Module, Countable, SeekableIterator, ArrayAccess
{
	protected $_model;
	protected $_current = 'selected';
	protected $_key = 0;
	protected $_data = array();

	protected $_exported = array();

	public function __construct($object, ArrayAccess $data)
	{
		$this->_data['selected'] = $data;
		$this->_data['inserted'] = new ArrayObject(array());
		$this->_data['updated'] = new ArrayObject(array());
		$this->_data['deleted'] = new ArrayObject(array());
		$this->export($object);
	}

	public function __set($name, $value)
	{
		if ($this->_current == 'inserted')
		{
			$this->_data[$this->_current][$this->_key][$name] = $value;
		}
		else
		{
			if ( ! isset($this->_data['updated'][$this->_key]))
			{
				$this->_data['updated'][$this->_key] = new Yada_Record(array(), $this->_model);
			}
			$this->_data['updated'][$this->_key][$name] = $value;
		}
	}

	public function __get($name)
	{
		if ($this->_current == 'selected' AND isset($this->_data['updated'][$this->_key]))
		{
			if ($this->_data['updated'][$this->_key]->offsetExists($name))
			{
				return $this->_data['updated'][$this->_key][$name];
			}
		}
		if (isset($this->_data[$this->_current][$this->_key][$name]))
		{
			return $this->_data[$this->_current][$this->_key][$name];
		}
		else return NULL;
	}

	public function export(Yada_Interface_Aggregate $object)
	{
		$this->_model = $object;
	  	$this->_exported += array('as_array', 'add', 'delete');
		$object->register($this, $this->_exported, '_Collection');
	}

	public function as_array()
	{
		$result = array();
		foreach($this->_data['selected'] as $key => $values)
		{
			if (isset ($this->_data['deleted'][$key])) continue;
			$result[$key] = $values->getArrayCopy();
			if (isset ($this->_data['updated'][$key]))
			{
				$result[$key] = array_merge($result[$key], $this->_data['updated'][$key]->getArrayCopy());
			}
		}

		foreach($this->_data['inserted'] as $values)
		{
			$result[] = $values->getArrayCopy();
		}
		return array_values($result);
	}

	public function add(array $arguments = array())
	{
		$this->_current = 'inserted';
		$this->_key = $this->_data[$this->_current]->count();

		if (empty($arguments))
		{
			$record = new Yada_Record(array(), $this->_model);
			$record->prefill();
			$this->_data[$this->_current]->offsetSet($this->_key, $record);
		}
		else
		{
			foreach($arguments as $values)
			{
				if ( ! $values instanceof Record_Core)
				{
					$values = new Yada_Record($values, $this->_model);
					$values->prefill();
				}
				$this->_data[$this->_current]->offsetSet($this->_key, $values);
				++$this->_key;
			}
			--$this->_key;
		}
	}

	public function delete()
	{
		if ($this->_current == 'inserted')
		{
			unset($this->_data[$this->_current][$this->_key]);
			$this->_data[$this->_current] = array_values($this->_data[$this->_current]);
		}
		else
		{
			$this->_data['deleted'][$this->_key] = $this->_data[$this->_current][$this->_key];
		}
	}

	public function current()
	{
		return $this->_data[$this->_current][$this->_key];
	}

	public function key()
	{
		return $this->_key;
	}

	public function next()
	{
		$this->_key += 1;
		if ($this->_current == 'selected')
		{
			while (isset($this->_data['deleted'][$this->_key]))
			{
				$this->_key += 1;
			}
			$max = $this->_data['selected']->count();
			if ($this->_key >= $max)
			{
				$this->_key = 0;
				$this->_current = 'inserted';
			}
		}
	}

	public function rewind()
	{
		$this->_key = 0;
		$this->_current = 'selected';
	}

	public function valid()
	{
		return isset($this->_data[$this->_current][$this->_key]);
	}

	public function seek($position)
	{
		$count = $this->_data['selected']->count() - $this->_data['deleted']->count();
		if($position >= $count)
		{
			$this->_current = 'inserted';
			$this->_key = $position-$count;
		}
		else
		{
			foreach ($this->_data['deleted'] as $key => $value)
			{
				if ($key > $position) break;
				if ($key <= $position)
				{
					++$position;
				}
			}
			$this->_current = 'selected';
			$this->_key = $position;
		}
	}

	public function count()
	{
		return $this->_data['selected']->count()
			 + $this->_data['inserted']->count()
			 - $this->_data['deleted']->count();
	}

	public function offsetExists($offset)
	{
		return ($offset > 0 AND $offset <= $this->count());
	}

	public function offsetGet($offset)
	{
		$this->seek($offset);
		return $this->current();
	}

	public function offsetSet($offset, $value)
	{
		if ($offset === NULL)
		{
			$this->add(array($value));
			return;
		}
		$this->seek($offset);
		if ($value === NULL AND $this->valid())
		{
			$this->delete();
			return;
		}
		$index = ($this->_current == 'selected') ? 'updated' : 'inserted';
		if ($value instanceof Yada_Record)
		{
			$this->_data[$index][$this->_key] = $value;
		}
		else
		{
			$record = new Yada_Record($value, $this->_model);
			$record->prefill();
			$this->_data[$index][$this->_key] = $record;
		}
	}

	public function offsetUnset($offset)
	{
		$this->seek($offset);
		$this->delete();
	}
}