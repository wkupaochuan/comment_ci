<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * 结果类的作用主要是selet类型的操作使用
 * Database Result Class
 *
 * This is the platform-independent result class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_result {

    // 数据库连接，调用者赋值
	var $conn_id				= NULL;
    // 执行结果, 调用者赋值
	var $result_id				= NULL;

    // 缓存查询本次查询结果到内存，供后续使用(仅仅是查询结果, 再次发起新sql查询，结果对象重新实例化，结果缓存丢失)
	var $result_array			= array();
	var $result_object			= array();
	var $custom_result_object	= array();
	var $current_row			= 0;
	var $num_rows				= 0;
	var $row_data				= NULL;


	/**
     * 查询结果
	 * Query result.  Acts as a wrapper function for the following functions.
	 *
	 * @access	public
	 * @param	string	can be "object" or "array"
	 * @return	mixed	either a result object or array
	 */
	public function result($type = 'object')
	{
		if ($type == 'array') return $this->result_array();
		else if ($type == 'object') return $this->result_object();
		else return $this->custom_result_object($type);
	}

	// --------------------------------------------------------------------

	/**
     * 返回对象格式的结果，指定类
	 * Custom query result.
	 *
	 * @param class_name A string that represents the type of object you want back
	 * @return array of objects
	 */
	public function custom_result_object($class_name)
	{
		if (array_key_exists($class_name, $this->custom_result_object))
		{
			return $this->custom_result_object[$class_name];
		}

		if ($this->result_id === FALSE OR $this->num_rows() == 0)
		{
			return array();
		}

		// add the data to the object
		$this->_data_seek(0);
		$result_object = array();

		while ($row = $this->_fetch_object())
		{
			$object = new $class_name();

			foreach ($row as $key => $value)
			{
				$object->$key = $value;
			}

			$result_object[] = $object;
		}

		// return the array
		return $this->custom_result_object[$class_name] = $result_object;
	}

	// --------------------------------------------------------------------

	/**
     * 查询结果，对象方式
	 * Query result.  "object" version.
	 *
	 * @access	public
	 * @return	object
	 */
	public function result_object()
	{
		if (count($this->result_object) > 0)
		{
			return $this->result_object;
		}

		// In the event that query caching is on the result_id variable
		// will return FALSE since there isn't a valid SQL resource so
		// we'll simply return an empty array.
		if ($this->result_id === FALSE OR $this->num_rows() == 0)
		{
			return array();
		}

		$this->_data_seek(0);
		while ($row = $this->_fetch_object())
		{
			$this->result_object[] = $row;
		}

		return $this->result_object;
	}

	// --------------------------------------------------------------------

	/**
     * 返回数组格式的查询结果
	 * Query result.  "array" version.
	 *
	 * @access	public
	 * @return	array
	 */
	public function result_array()
	{
		if (count($this->result_array) > 0)
		{
			return $this->result_array;
		}

		// In the event that query caching is on the result_id variable
		// will return FALSE since there isn't a valid SQL resource so
		// we'll simply return an empty array.
		if ($this->result_id === FALSE OR $this->num_rows() == 0)
		{
			return array();
		}

		$this->_data_seek(0);
		while ($row = $this->_fetch_assoc())
		{
			$this->result_array[] = $row;
		}

		return $this->result_array;
	}

	// --------------------------------------------------------------------

	/**
     * 查询一行，支持行数选择
	 * Query result.  Acts as a wrapper function for the following functions.
	 *
	 * @access	public
	 * @param	string
	 * @param	string	can be "object" or "array"
	 * @return	mixed	either a result object or array
	 */
	public function row($n = 0, $type = 'object')
	{
        // 如果$n不是数组，则当做一个key来用
		if ( ! is_numeric($n))
		{
			// We cache the row data for subsequent uses
			if ( ! is_array($this->row_data))
			{
				$this->row_data = $this->row_array(0);
			}

			// array_key_exists() instead of isset() to allow for MySQL NULL values
			if (array_key_exists($n, $this->row_data))
			{
				return $this->row_data[$n];
			}
			// reset the $n variable if the result was not achieved
			$n = 0;
		}

		if ($type == 'object') return $this->row_object($n);
		else if ($type == 'array') return $this->row_array($n);
		else return $this->custom_row_object($n, $type);
	}

	// --------------------------------------------------------------------

	/**
     * 设置结果值，没看到太多用处
	 * Assigns an item into a particular column slot
	 *
	 * @access	public
	 * @return	object
	 */
	public function set_row($key, $value = NULL)
	{
		// We cache the row data for subsequent uses
		if ( ! is_array($this->row_data))
		{
			$this->row_data = $this->row_array(0);
		}

        // 支持数组($key参数中，包含key=>value)
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->row_data[$k] = $v;
			}

			return;
		}

		if ($key != '' AND ! is_null($value))
		{
			$this->row_data[$key] = $value;
		}
	}

	// --------------------------------------------------------------------

	/**
     * 按照对象的方式返回一行，并指定类型
	 * Returns a single result row - custom object version
	 *
	 * @access	public
	 * @return	object
	 */
	public function custom_row_object($n, $type)
	{
		$result = $this->custom_result_object($type);

		if (count($result) == 0)
		{
			return $result;
		}

		if ($n != $this->current_row AND isset($result[$n]))
		{
			$this->current_row = $n;
		}

		return $result[$this->current_row];
	}

	/**
     * 按照对象的方式返回一行
	 * Returns a single result row - object version
	 *
	 * @access	public
	 * @return	object
	 */
	public function row_object($n = 0)
	{
		$result = $this->result_object();

		if (count($result) == 0)
		{
			return $result;
		}

		if ($n != $this->current_row AND isset($result[$n]))
		{
			$this->current_row = $n;
		}

		return $result[$this->current_row];
	}

	// --------------------------------------------------------------------

	/**
     * 按照数组的方式返回一行
	 * Returns a single result row - array version
	 *
	 * @access	public
	 * @return	array
	 */
	public function row_array($n = 0)
	{
        // 缓存所有结果
		$result = $this->result_array();

        // 查询结果为空
		if (count($result) == 0)
		{
			return $result;
		}

		if ($n != $this->current_row AND isset($result[$n]))
		{
			$this->current_row = $n;
		}

		return $result[$this->current_row];
	}


	// --------------------------------------------------------------------

	/**
     * 返回第一行
	 * Returns the "first" row
	 *
	 * @access	public
	 * @return	object
	 */
	public function first_row($type = 'object')
	{
		$result = $this->result($type);

		if (count($result) == 0)
		{
			return $result;
		}
		return $result[0];
	}

	// --------------------------------------------------------------------

	/**
     * 返回最后一行
	 * Returns the "last" row
	 *
	 * @access	public
	 * @return	object
	 */
	public function last_row($type = 'object')
	{
		$result = $this->result($type);

		if (count($result) == 0)
		{
			return $result;
		}
		return $result[count($result) -1];
	}

	// --------------------------------------------------------------------

	/**
     * 返回下一行
	 * Returns the "next" row
	 *
	 * @access	public
	 * @return	object
	 */
	public function next_row($type = 'object')
	{
		$result = $this->result($type);

		if (count($result) == 0)
		{
			return $result;
		}

		if (isset($result[$this->current_row + 1]))
		{
			++$this->current_row;
		}

		return $result[$this->current_row];
	}

	// --------------------------------------------------------------------

	/**
     * 返回上一行
	 * Returns the "previous" row
	 *
	 * @access	public
	 * @return	object
	 */
	public function previous_row($type = 'object')
	{
		$result = $this->result($type);

		if (count($result) == 0)
		{
			return $result;
		}

		if (isset($result[$this->current_row - 1]))
		{
			--$this->current_row;
		}
		return $result[$this->current_row];
	}

	// --------------------------------------------------------------------

	/**
	 * The following functions are normally overloaded by the identically named
	 * methods in the platform-specific driver -- except when query caching
	 * is used.  When caching is enabled we do not load the other driver.
	 * These functions are primarily here to prevent undefined function errors
	 * when a cached result object is in use.  They are not otherwise fully
	 * operational due to the unavailability of the database resource IDs with
	 * cached results.
	 */
	public function num_rows() { return $this->num_rows; }
	public function num_fields() { return 0; }
	public function list_fields() { return array(); }
	public function field_data() { return array(); }
	public function free_result() { return TRUE; }
	protected function _data_seek() { return TRUE; }
	protected function _fetch_assoc() { return array(); }
	protected function _fetch_object() { return array(); }

}
// END DB_result class

/* End of file DB_result.php */
/* Location: ./system/database/DB_result.php */
