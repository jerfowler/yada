<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

 /**
  * Yada Core Helper Class
  *
  * Contains static factory functions to initialize the
  * various Yada classes
  *
  */
abstract class Yada_Core
{
	/**
	 * @var array	class prefixes for the various types
	 */
	public static $_prefix = array(
		'model'   => 'model_yada_',
		'meta'    => 'yada_meta_',
		'field'   => 'yada_field_',
		'mapper'  => 'yada_mapper_',
		'collect' => 'yada_collect_',
		'record'  => 'yada_record_',
	);

	/**
	 * Adds prefixes to common names to return the full class name
	 *
	 * @param string $type The class type
	 * @param string $name The Common name
	 * @return string
	 */
	public static function class_name($type, $name)
	{
		$prefix = isset(Yada::$_prefix[$type])
			? Yada::$_prefix[$type]
			: 'yada_'.$type.'_';
		return strtolower($prefix.$name);
	}

	/**
	 * Removes prefixes of class names to return the common name
	 *
	 * @param string $type The class type
	 * @param object|string $name An instance of a class or the class name
	 * @return string
	 */
	public static function common_name($type, $name)
	{
		$name = is_object($name) ? get_class($name) : $name;
		$prefix = isset(Yada::$_prefix[$type])
			? Yada::$_prefix[$type]
			: 'yada_'.$type.'_';
		return substr($name, strlen($prefix));
	}

	/**
	 * Factory Pattern Method to initialize Yada and return an object
	 *
	 * This function initializes the Meta object which catalogs models and
	 * their relations. It then attaches an instance of a model created
	 * using the ::model static factory method.
	 *
	 * @param string $model Common name of the model
	 * @param array $values Array of key/value pairs used to initialize the model
	 * @return Yada_Model
	 */
	public static function factory($model, $values = NULL)
	{
		$class = Yada::class_name('model', $model);
		$meta = Yada::meta($class);
		return $meta->attach(Yada::model($model), $values);
	}

	/**
	 * Factory Pattern Method used to create a Yada Model Object
	 *
	 * A simple factory method to create a new Model. The model object
	 * will then need to be attached to a Meta object to work.
	 *
	 * @param string $model Common name of the model
	 * @param array $init fields and other meta data
	 * @return Yada_Model
	 */
	public static function model($model, $init = NULL)
	{
		$class = Yada::class_name('model', $model);
		return new $class($init);
	}

	/**
	 * Factory Pattern Method used to create a Yada Meta Object
	 *
	 * Meta objects catalog models and their relations. Custom meta objects
	 * can be used if sepcified as a static variable in the Model Class passed to the method
	 *
	 * @param object|string $model The initial model of this meta object
	 * @return Yada_Meta
	 */
	public static function meta($model)
	{
		$meta = isset($model::$meta) ? $model::$meta : 'default';
		$class = Yada::class_name('meta', $meta);
		return new $class();
	}

	/**
	 * Factory Pattern Method used to create a Yada Field Object
	 *
	 * Field objects operate on the fields and specifies the data type
	 *
	 * @param string $field Common name of the field
	 * @param array|NULL $options optional array of key/value list of initialization options
	 * @return Yada_Field
	 */
	public static function field($field, $options = NULL)
	{
		$class = Yada::class_name('field', $field);
		return new $class($options);
	}

	/**
	 * Factory Pattern Method used to create a Yada Mapper Object
	 *
	 * Mapper Objects are Data Mapper Pattern objects that encapsulate all
	 * data source inserts, updates, and deletes. Custom mappers can be specified
	 * in the Model's ::$mapper static variable
	 *
	 * @param Yada_Meta $meta
	 * @param Yada_Model $model
	 * @return Yada_Mapper
	 */
	public static function mapper($meta, $model)
	{
		$mapper = isset($model::$mapper) ? $model::$mapper : 'SQL_PDO';
		$class = Yada::class_name('mapper', $mapper);
		return new $class($meta, $model);
	}

	/**
	 * Factory Pattern Method used to create a Yada Collect Object
	 *
	 * Collect Objects contain a set of Yada Records. Custom collects can be
	 * specified in the Model's ::$collect static variable
	 *
	 * @param Yada_Meta $meta
	 * @param Yada_Model $model
	 * @param mixed $data
	 * @return Yada_Collect
	 */
	public static function collect($meta, $model, $data)
	{
		$collect = isset($model::$collect) ? $model::$collect : 'SQL_PDO';
		$class = Yada::class_name('collect', $collect);
		return new $class($meta, $model, $data);
	}

	/**
	 * Factory Pattern Method used to create a Yada Collect Object
	 *
	 * Record Objects are Active Record Pattern Objects that act on a single
	 * record or row. Custom records can be specified in the Model's ::$record
	 * static variable
	 *
	 * @param Yada_Meta $meta
	 * @param Yada_Model $model
	 * @param mixed $data
	 * @return Yada_Record
	 */
	public static function record($meta, $model, $data)
	{
		$record = isset($model::$record) ? $model::$record : 'SQL_PDO';
		$class = Yada::class_name('record', $record);
		return new $class($meta, $model, $data);
	}

	public static function debug($item)
	{

		$stack = xdebug_get_function_stack();
		$out = array();
		foreach ($stack as $num => $call)
		{
			// if ($num < 5) continue;
			if (! isset($call['function'])) continue;
			$class = (isset($call['class'])) ? $call['class'].'::' : 'Function ';
			$out[] = $class.$call['function'].' ('.$call['line'].')';
		}
		//var_dump($out);
		//var_dump($item);
	}

}
