<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Yada: To know in a relational sense.
 * @package Yada
 * @author Jeremy Fowler <jeremy.f76@gmail.com>
 * @copyright Copyright (c) 2010, Jeremy Fowler
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 */


/**
 *  FYI: Much of this code is borrowed from Kohana's Database Module.
 */

abstract class Yada_Mapper_SQL_PDO_Core extends Yada_Mapper_SQL
{

	/**
	 *
	 * @var PDO
	 */
	protected $_db;

	/**
	 *
	 * @var Kohana_Config
	 */
	protected $_config;

	/**
	 *
	 * @var string
	 */
	protected $_instance;

	public function __construct(Yada_Meta $meta, Yada_Model $model, $values = NULL)
	{
		parent::__construct($meta, $model, $values);
		$name = (isset($model::$database)) ? $model::$database : 'default';
		$this->_config = Kohana::config('yada')->$name;
		$this->_instance = $name;
	}

	protected function _is_joinable($mapper)
	{
		return ($mapper instanceof Yada_Mapper_SQL_PDO AND $mapper->_name() == $this->_instance)
			? TRUE
			: FALSE;
	}

	protected function _name()
	{
		return $this->_instance;
	}

	protected function _table_prefix($table)
	{
		return $this->_config['table_prefix'].$table;
	}

	protected function _connect()
	{
		if ($this->_db)
			return;

		// Extract the connection parameters, adding required variabels
		extract($this->_config['connection'] + array(
			'dsn'        => '',
			'username'   => NULL,
			'password'   => NULL,
			'persistent' => FALSE,
		));

		// Clear the connection parameters for security
		unset($this->_config['connection']);

		// Force PDO to use exceptions for all errors
		$attrs = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

		if ( ! empty($persistent))
		{
			// Make the connection persistent
			$attrs[PDO::ATTR_PERSISTENT] = TRUE;
		}

		// Create a new PDO connection
		$this->_db = new PDO($dsn, $username, $password, $attrs);

		if ( ! empty($this->_config['charset']))
		{
			// Execute a raw SET NAMES query
			$this->_db->exec('SET NAMES '.$this->_db->quote($this->_config['charset']));
		}
	}

	protected function _disconnect()
	{
		// Destroy the PDO object
		$this->_db = NULL;

		return TRUE;
	}

	protected function _prepare($sql) 
	{
		// Make sure the database is connected
		$this->_db or $this->_connect();

		if ( ! empty($this->_config['profiling']))
		{
			// Benchmark this query for the current instance
			$benchmark = Profiler::start('Yada PDO ('.$this->_instance.'), Prepare:', $sql);
		}
		try
		{
			$result = $this->_db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			if ($result === FALSE)
			{
				$result = $this->_db->prepare($sql);
			}
		}
		catch (Exception $e)
		{
			if (isset($benchmark))
			{
				// This benchmark is worthless
				Profiler::delete($benchmark);
			}

			// Rethrow the exception
			throw $e;
		}
		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}
		return $result;
	}

	protected function _bind_value($query, $param, $value)
	{
		$type = PDO::PARAM_STR;
		if (is_int($value))
		{
			$type = PDO::PARAM_INT;
		}
		elseif (is_bool($value))
		{
			$type = PDO::PARAM_BOOL;
		}
		elseif (is_resource($value))
		{
			$type = PDO::PARAM_LOB;
		}
		$query->bindValue($param, $value, $type);
	}

	protected function _bind_param($query, $param, &$var)
	{
		$type = PDO::PARAM_STR;
		if (is_int($var))
		{
			$type = PDO::PARAM_INT;
		}
		elseif (is_bool($var))
		{
			$type = PDO::PARAM_BOOL;
		}
		elseif (is_resource($value))
		{
			$type = PDO::PARAM_LOB;
		}
		$query->bindParam($param, $var, $type);
	}

	protected function _execute($query, Array $data = NULL)
	{
		// Make sure the database is connected
		$this->_db or $this->_connect();

		if ( ! empty($this->_config['profiling']))
		{
			//$sql = strtr($query->queryString, $this->_params);
			$params = array();
			foreach ($this->_params as $key => $value)
			{
				$params[] = $key.' = '.$value;
			}
			// Benchmark this query for the current instance
			$benchmark = Profiler::start('Yada PDO ('.$this->_instance.'), Execute:', implode(', ', $params));
		}

		try
		{
			$result = $query->execute($data);
		}
		catch (Exception $e)
		{
			if (isset($benchmark))
			{
				// This benchmark is worthless
				Profiler::delete($benchmark);
			}

			// Rethrow the exception
			throw $e;
		}
		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		return $result;
//
//		// Set the last query
//		$this->last_query = $sql;
//
//		if ($type === Database::SELECT)
//		{
//			// Convert the result into an array, as PDOStatement::rowCount is not reliable
//			if ($as_object === FALSE)
//			{
//				$result->setFetchMode(PDO::FETCH_ASSOC);
//			}
//			elseif (is_string($as_object))
//			{
//				$result->setFetchMode(PDO::FETCH_CLASS, $as_object);
//			}
//			else
//			{
//				$result->setFetchMode(PDO::FETCH_CLASS, 'stdClass');
//			}
//
//			$result = $result->fetchAll();
//
//			// Return an iterator of results
//			return new Database_Result_Cached($result, $sql, $as_object);
//		}
//		elseif ($type === Database::INSERT)
//		{
//			// Return a list of insert id and rows created
//			return array(
//				$this->_connection->lastInsertId(),
//				$result->rowCount(),
//			);
//		}
//		else
//		{
//			// Return the number of rows affected
//			return $result->rowCount();
//		}
	}

	protected function _exec($sql)
	{
		// Make sure the database is connected
		$this->_db or $this->_connect();

		if ( ! empty($this->_config['profiling']))
		{
			// Benchmark this query for the current instance
			$benchmark = Profiler::start('Yada PDO ('.$this->_instance.'), Exec:', $sql);
		}

		try
		{
			$result = $this->_db->exec($sql);
		}
		catch (Exception $e)
		{
			if (isset($benchmark))
			{
				// This benchmark is worthless
				Profiler::delete($benchmark);
			}

			// Rethrow the exception
			throw $e;
		}
		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}
		return $result;
	}

	protected function _last()
	{
		return $this->_db->lastInsertId();
	}

	protected function  _get_sql($query)
	{
		return $query->queryString;
	}

	/**
	 *
	 * @param PDOStatement $query
	 * @param Integer $num
	 */
	protected function _fetch_column($query, $num = 0)
	{
		return $query->fetchColumn($num);
	}
}

