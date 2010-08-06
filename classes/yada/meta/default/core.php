<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * The Yada_Meta object acts as an information repository and is the heart of
 * the Yada Framework
 *
 * The Default Meta Object adds mapped field support so the specified field
 * values or meta data can be returned easily from a dynamic function call
 *
 */
abstract class Yada_Meta_Default_Core extends Yada_Meta
{
	/**
	 * This static variable stores all the aggregate methods this class exports
	 * @var array
	 */
	protected static $_exported = array();

	/**
	 * An array of mapped fields
	 *
	 * A string value is used to match the class or interface name of the field.
	 * Any field matching the class or interface will have its Feild ArrayObject
	 * Meta data added to the map. This is so you can map all fields of a
	 * particular type together as a set.
	 *
	 * An array value will have the class or interface name of the field as
	 * the first element in the array. The second element can be either an
	 * array or a string.
	 *
	 * If the second element is an array, then the Field's ArrayObject will be
	 * mapped if the propety value of the field specified in the first element
	 * matches the value of the second element. This is so you can group fields
	 * that all have a particular property value.
	 *
	 * If the second element is a string, then the value of that field's property
	 * that matches that string is stored in the map. This is so you can map the
	 * values of that property as a set.
	 *
	 * @var array
	 */
	public static $mapped = array(
		// Map all Fields that are keys
		'keys'     => 'Yada_Field_Key',
		// Map all the Field's default values
		'defaults' => array('Yada_Field_Interface_Saveable', 'default'),
		// Map all the Field's label values
		'labels'   => array('Yada_Field', 'label'),
		// Map all Fields that are unique
		'unique'   => array('Yada_Field_Interface_Saveable', array('unique', TRUE)),
	);

	/**
	 * Magic Method that returns property values of the Meta ArrayObject
	 *as well as mapped fields.
	 *
	 * The Model object must be passed as the first argument with mapped fields
	 *
	 * @param string $name
	 * @param mixed $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		// Get the Meta ArrayObject
		$meta = $this->meta();
		// See if the property exists and return it
		if ($meta->offsetExists($name))
		{
			return $meta[$name];
		}

		// See if the mapped index exists
		if(isset(self::$mapped[$name]))
		{
			// get the model and values from the passed arguments
			list ($model, $values) = $arguments;
			// focus the model
			$this->model($model);
			// return the mapped field values
			return $this->get_map($name, $values);
		}
	}

	/**
	 * Initialize the maps property in the Meta ArrayObject
	 *
	 * @param ArrayObject $attached
	 */
	protected function _attach(ArrayObject $attached)
	{
		$attached['maps'] = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		if (isset(self::$mapped))
		{
			foreach (self::$mapped as $map => $class)
			{
				$attached->maps[$map] = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
			}
		}
	}

	/**
	 * Map all the fields
	 *
	 * @param string $name field name
	 * @param Yada_Field $field
	 */
	protected function _initialize($name, Yada_Field $field)
	{
		// get the maps Meta ArrayObject
		$_maps = $this->maps();

		// get the Fields Meta ArrayObject
		$_fields = $this->fields();

		// iterate through each of the mapped field types
		foreach (self::$mapped as $map => $class)
		{
			// Check for an array
			if (is_array($class))
			{
				list($class, $property) = $class;
				// See if the class matches
				if ($field instanceof $class)
				{
					if (is_array($property))
					{
						// Map the ArrayObject if the property matches
						list($property, $value) = $property;
						if ($field->$property === $value)
						{
							$_maps[$map][$name] = $_fields[$name];
						}
					}
					// Map the value of the property
					else
					{
						$_maps[$map][$name] = $field->$property;
					}
				}
			}
			// Map the ArrayObject if the class matches
			elseif ($field instanceof $class)
			{
				$_maps[$map][$name] = $_fields[$name];
			}
		}
	}

	/**
	 * Return the mapped values
	 *
	 * @param string $map
	 * @param mixed $name
	 * @return mixed
	 */
	public function get_map($map, $name = NULL)
	{
		// get the maps Meta ArrayObject
		$maps = $this->maps();

		// Return NULL if we don't have a map
		if ( ! isset($maps[$map])) return NULL;

		// Get the map
		$map = $maps[$map];

		// Check for a 0 indexed array value
		$name = (isset($name[0])) ? $name[0] : $name;

		// Return the entire map if no name specified
		if ($name === NULL)
		{
			return $map;
		}

		// See if we have a list of index values
		if (is_array($name))
		{
			// Build a result list using those values
			$result = array();
			foreach ($name as $value)
			{
				if ($map->offsetExists($value))
				{
					$result[$value] = $map[$value];
				}
			}
			return $result;
		}

		// Return the map value specified by name
		return ($map->offsetExists($name)) ? $map[$name] : NULL;

	}
}
