<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: Yet Another Data Abstraction
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
 
abstract class Yada_Core
{
	const OP_NOT   = 'not';
	const OP_AND   = 'and';
	const OP_OR    = 'or';
	const OP_BEGIN = 'begin';
	const OP_END   = 'end';
	const WILD     = '%';

	public static $_prefix = array(
		'model'   => 'model_',
		'meta'    => 'yada_meta_',
		'field'   => 'yada_field_',
		'mapper'  => 'yada_mapper_',
		'collect' => 'yada_collect_',
		'record'  => 'yada_record_',
	);

	public static function class_name($type, $name)
	{
		return strtolower(Yada::$_prefix[$type].$name);
	}

	public static function common_name($type, $name)
	{
		$name = is_object($name) ? get_class($name) : $name;
		return substr($name, strlen(Yada::$_prefix[$type]));
	}

	public static function factory($model, $values = NULL)
	{
		$class = Yada::class_name('model', $model);
		$meta = Yada::meta($class);
		return $meta->attach(Yada::model($model, $values));
	}

	public static function model($model, $values = NULL)
	{
		$class = Yada::class_name('model', $model);
		return new $class($values);
	}

	public static function meta($model)
	{
		$meta = isset($model::$meta) ? $model::$meta : 'default';
		$class = Yada::class_name('meta', $meta);
		return new $class();
	}

	public static function field($field, $options = NULL)
	{
		$class = Yada::class_name('field', $field);
		return new $class($options);
	}

	public static function mapper($meta, $model)
	{
		$mapper = isset($model::$mapper) ? $model::$mapper : 'kohana';
		$class = Yada::class_name('mapper', $mapper);
		return new $class($meta, $model);
	}

	public static function collect($meta, $model, $data)
	{
		$collect = isset($model::$collect) ? $model::$collect : 'kohana';
		$class = Yada::class_name('collect', $collect);
		return new $class($meta, $model, $data);
	}

	public static function record($meta, $model, $data)
	{
		$record = isset($model::$record) ? $model::$record : 'kohana';
		$class = Yada::class_name('record', $record);
		return new $class($meta, $model, $data);
	}
}
