<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Yada_Meta objects act as a registry of information about a particular model.
 *
 * @package Yada
 */
abstract class Yada_Meta_Default_Core extends Yada_Meta
{
        protected static $_exported = array();

        public static $mapped = array(
//                'primary'  => 'Yada_Field_Primary',
//                'name_key' => 'Yada_Field_Name',
//                'foreign'  => 'Yada_Field_Foreign',
//                'related'  => array('Yada_Field_Interface_Related', 'related'),
                'defaults' => array('Yada_Field_Interface_Saveable', 'default'),
                'labels'   => array('Yada_Field', 'label'),
                'unique'   => array('Yada_Field_Interface_Saveable', array('unique', TRUE)),
        );

        public function __call($name, $arguments)
        {
                $meta = $this->meta();
                if ($meta->offsetExists($name))
                {
                        return $meta[$name];
                }
                
                if(isset(self::$mapped[$name]))
                {
                        list ($model, $values) = $arguments;
                        $this->model($model);
                        return $this->get_map($name, $values);
                }
        }

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

        protected function _initialize($name, Yada_Field $field)
        {
                $_maps = $this->maps();
                $_fields = $this->fields();
                foreach (self::$mapped as $map => $class)
                {
                        if (is_array($class))
                        {
                                list($class, $property) = $class;
                                if ($field instanceof $class)
                                {
                                        if (is_array($property))
                                        {
                                                list($property, $value) = $property;
                                                if ($field->$property === $value)
                                                {
                                                        $_maps[$map][$name] = $_fields[$name];
                                                        $_fields[$name][$map] = TRUE;
                                                }
                                                else
                                                {
                                                        $_fields[$name][$map] = FALSE;
                                                }
                                        }
                                        else
                                        {
                                                $_maps[$map][$name] = $field->$property;
                                                $_fields[$name][$property] = $field->$property;
                                        }
                                }
                        }
                        elseif ($field instanceof $class)
                        {
                                $_maps[$map][$name] = $_fields[$name];
                                $_fields[$name][$map] = TRUE;
                        }
                        else
                        {
                                $_fields[$name][$map] = FALSE;
                        }
                }
        }


        public function get_map($map, $name = NULL)
        {
                $maps = $this->maps();
                if ( ! isset($maps[$map])) return NULL;

                $map = $maps[$map];
                $name = (isset($name[0])) ? $name[0] : $name;

                if ($name === NULL)
                {
                        return $map;
                }

                if (is_array($name))
                {
                        $result = array();
                        foreach ($name as $value)
                        {
                                if ($map->offsetExists($value))
                                {
                                        $result[$value] = $map[$value];
                                }
                        }
                        return (empty($result)) ? $map : $result;
                }

                return ($map->offsetExists($name)) ? $map[$name] : NULL;

        }
}
