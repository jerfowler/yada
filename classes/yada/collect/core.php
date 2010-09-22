<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

abstract class Yada_Collect_Core implements Yada_Interface_Module, Countable, SeekableIterator, ArrayAccess
{
	protected $_model;
	protected $_meta;
	protected $_data;

	public function __construct(Yada_Meta $meta, Yada_Model $model, $data)
	{
		$this->_meta = $meta;
		$this->_model = $model;
		$this->_data = $data;

		$meta->meta($model)->collect = $this;
		$this->export($model);
	}

	public function export(Yada_Interface_Aggregate $object)
	{
		$exported = isset($this::$_exported) ? $this::$_exported : array();
		$object->register($this, $exported);
	}

	abstract public function as_array($model = NULL, $args = NULL);
}