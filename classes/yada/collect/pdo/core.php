<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
 
abstract class Yada_Collect_PDO_Core extends Yada_Collect
{
	/**
	 *
	 * @var array
	 */
	protected static $_exported = array('as_array');

	public function as_array($model = NULL, $args = NULL)
	{
//		$data = new PDOStatement();
//		$data->
	}

}