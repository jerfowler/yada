<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 *
 */
 
interface Yada_Interface_Aggregate
{
    public function register(Yada_Interface_Module $object, array $methods);
    public function unregister(Yada_Interface_Module $object);
}
