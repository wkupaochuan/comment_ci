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
 * Database Driver Class
 *
 * This is the platform-independent base DB implementation class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_driver {

	var $username;
	var $password;
	var $hostname;
	var $database;
	var $dbdriver		= 'mysql';
	var $dbprefix		= '';
	var $char_set		= 'utf8';
	var $dbcollat		= 'utf8_general_ci';
	var $autoinit		= TRUE; // Whether to automatically initialize the DB
	var $swap_pre		= '';
	var $port			= '';
	var $pconnect		= FALSE;
	var $conn_id		= FALSE;
	var $result_id		= FALSE;
	var $db_debug		= FALSE;
	var $benchmark		= 0;
	var $query_count	= 0;
	var $bind_marker	= '?';
	var $save_queries	= TRUE;
	var $queries		= array();
	var $query_times	= array();
	var $data_cache		= array();
	var $trans_enabled	= TRUE;
	var $trans_strict	= TRUE;
	var $_trans_depth	= 0;
	var $_trans_status	= TRUE; // Used with transactions to determine if a rollback should occur
	var $cache_on		= FALSE;
	var $cachedir		= '';
	var $cache_autodel	= FALSE;
	var $CACHE; // The cache class object

	// Private variables
	var $_protect_identifiers	= TRUE;
    // 不应该转义的标示符
	var $_reserved_identifiers	= array('*'); // Identifiers that should NOT be escaped

	// These are use with Oracle
	var $stmt_id;
	var $curs_id;
	var $limit_used;



	/**
     * 构造方法，根据参数，给私有属性赋值
	 * Constructor.  Accepts one parameter containing the database
	 * connection settings.
	 *
	 * @param array
	 */
	function __construct($params)
	{
		if (is_array($params))
		{
			foreach ($params as $key => $val)
			{
				$this->$key = $val;
			}
		}

		log_message('debug', 'Database Driver Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
     * 连接数据库--选择db--设置字符集
	 * Initialize Database Settings
	 *
	 * @access	private Called by the constructor
	 * @param	mixed
	 * @return	void
	 */
	function initialize()
	{
		// If an existing connection resource is available
		// there is no need to connect and select the database
        // 不重复连接
		if (is_resource($this->conn_id) OR is_object($this->conn_id))
		{
			return TRUE;
		}

		// ----------------------------------------------------------------

		// Connect to the database and set the connection ID
        // 连接数据库，保存连接(根据配置，是否使用pconnect)
		$this->conn_id = ($this->pconnect == FALSE) ? $this->db_connect() : $this->db_pconnect();

		// No connection resource?  Throw an error
		if ( ! $this->conn_id)
		{
			log_message('error', 'Unable to connect to the database');

			if ($this->db_debug)
			{
				$this->display_error('db_unable_to_connect');
			}
			return FALSE;
		}

		// ----------------------------------------------------------------

		// Select the DB... assuming a database name is specified in the config file
        // 设置数据库名称
		if ($this->database != '')
		{
			if ( ! $this->db_select())
			{
				log_message('error', 'Unable to select database: '.$this->database);

				if ($this->db_debug)
				{
					$this->display_error('db_unable_to_select', $this->database);
				}
				return FALSE;
			}
			else
			{
				// We've selected the DB. Now we set the character set
                // 设置字符集
				if ( ! $this->db_set_charset($this->char_set, $this->dbcollat))
				{
					return FALSE;
				}

				return TRUE;
			}
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
     * 设置客户端字符集
	 * Set client character set
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	resource
	 */
	function db_set_charset($charset, $collation)
	{
		if ( ! $this->_db_set_charset($this->char_set, $this->dbcollat))
		{
			log_message('error', 'Unable to set database connection charset: '.$this->char_set);

			if ($this->db_debug)
			{
				$this->display_error('db_unable_to_set_charset', $this->char_set);
			}

			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
     * 获取驱动类型
	 * The name of the platform in use (mysql, mssql, etc...)
	 *
	 * @access	public
	 * @return	string
	 */
	function platform()
	{
		return $this->dbdriver;
	}

	// --------------------------------------------------------------------

	/**
     * 获取数据库版本
	 * Database Version Number.  Returns a string containing the
	 * version of the database being used
	 *
	 * @access	public
	 * @return	string
	 */
	function version()
	{
		if (FALSE === ($sql = $this->_version()))
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_unsupported_function');
			}
			return FALSE;
		}

		// Some DBs have functions that return the version, and don't run special
		// SQL queries per se. In these instances, just return the result.
        // 显然pdo也在其中
		$driver_version_exceptions = array('oci8', 'sqlite', 'cubrid');

		if (in_array($this->dbdriver, $driver_version_exceptions))
		{
			return $sql;
		}
		else
		{
			$query = $this->query($sql);
			return $query->row('ver');
		}
	}

	// --------------------------------------------------------------------

	/**
     * 执行sql语句
	 * Execute the query
	 *
	 * Accepts an SQL string as input and returns a result object upon
	 * successful execution of a "read" type query.  Returns boolean TRUE
	 * upon successful execution of a "write" type query. Returns boolean
	 * FALSE upon failure, and if the $db_debug variable is set to TRUE
	 * will raise an error.
	 *
	 * @access	public
	 * @param	string	An SQL query string
	 * @param	array	An array of binding data
	 * @return	mixed
	 */
	function query($sql, $binds = FALSE, $return_object = TRUE)
	{
        // sql不为空
		if ($sql == '')
		{
			if ($this->db_debug)
			{
				log_message('error', 'Invalid query: '.$sql);
				return $this->display_error('db_invalid_query');
			}
			return FALSE;
		}

		// Verify table prefix and replace if necessary
        // 添加前缀
		if ( ($this->dbprefix != '' AND $this->swap_pre != '') AND ($this->dbprefix != $this->swap_pre) )
		{
			$sql = preg_replace("/(\W)".$this->swap_pre."(\S+?)/", "\\1".$this->dbprefix."\\2", $sql);
		}

		// Compile binds if needed
        // 替换变量
		if ($binds !== FALSE)
		{
			$sql = $this->compile_binds($sql, $binds);
		}

		// Is query caching enabled?  If the query is a "read type"
		// we will load the caching class and return the previously
		// cached query if it exists
        // read类型的sql根据配置返回缓存
        // 根据select字符串来判定是否是查询语句?
		if ($this->cache_on == TRUE AND stristr($sql, 'SELECT'))
		{
			if ($this->_cache_init())
			{
				$this->load_rdriver();
				if (FALSE !== ($cache = $this->CACHE->read($sql)))
				{
					return $cache;
				}
			}
		}

		// Save the  query for debugging
        // 存储sql语句，用来调试
		if ($this->save_queries == TRUE)
		{
			$this->queries[] = $sql;
		}

		// Start the Query Timer
        // 开始计时器
		$time_start = list($sm, $ss) = explode(' ', microtime());

		// Run the Query
        // sql准备好，执行
		if (FALSE === ($this->result_id = $this->simple_query($sql)))
		{
			if ($this->save_queries == TRUE)
			{
				$this->query_times[] = 0;
			}

			// This will trigger a rollback if transactions are being used
            // 查询出错，给事务做标记，用来回滚
			$this->_trans_status = FALSE;

            // 展示错误
			if ($this->db_debug)
			{
				// grab the error number and message now, as we might run some
				// additional queries before displaying the error
				$error_no = $this->_error_number();
				$error_msg = $this->_error_message();

				// We call this function in order to roll-back queries
				// if transactions are enabled.  If we don't call this here
				// the error message will trigger an exit, causing the
				// transactions to remain in limbo.
				$this->trans_complete();

				// Log and display errors
				log_message('error', 'Query error: '.$error_msg);
				return $this->display_error(
										array(
												'Error Number: '.$error_no,
												$error_msg,
												$sql
											)
										);
			}

			return FALSE;
		}

		// Stop and aggregate the query time results
        // 停止计时器，计时
		$time_end = list($em, $es) = explode(' ', microtime());
		$this->benchmark += ($em + $es) - ($sm + $ss);

        // 记录执行时间
		if ($this->save_queries == TRUE)
		{
			$this->query_times[] = ($em + $es) - ($sm + $ss);
		}

		// Increment the query counter
        // 查询次数计数
		$this->query_count++;

		// Was the query a "write" type?
		// If so we'll simply return true
        // write类型的sql，直接返回true
		if ($this->is_write_type($sql) === TRUE)
		{
			// If caching is enabled we'll auto-cleanup any
			// existing files related to this particular URI
            // 如果打开了缓存，将当前uri关联的缓存删除(明显这里也删除的不够)
			if ($this->cache_on == TRUE AND $this->cache_autodel == TRUE AND $this->_cache_init())
			{
				$this->CACHE->delete();
			}

			return TRUE;
		}

		// Return TRUE if we don't need to create a result object
		// Currently only the Oracle driver uses this when stored
		// procedures are used
		if ($return_object !== TRUE)
		{
			return TRUE;
		}

		// Load and instantiate the result driver
        // 加载一个结果驱动
		$driver			= $this->load_rdriver();
		$RES			= new $driver();
		$RES->conn_id	= $this->conn_id;
		$RES->result_id	= $this->result_id;

		if ($this->dbdriver == 'oci8')
		{
			$RES->stmt_id		= $this->stmt_id;
			$RES->curs_id		= NULL;
			$RES->limit_used	= $this->limit_used;
			$this->stmt_id		= FALSE;
		}

		// oci8 vars must be set before calling this
        // 受影响的结果的行数
		$RES->num_rows	= $RES->num_rows();

		// Is query caching enabled?  If so, we'll serialize the
		// result object and save it to a cache file.
        // 缓存
		if ($this->cache_on == TRUE AND $this->_cache_init())
		{
			// We'll create a new instance of the result object
			// only without the platform specific driver since
			// we can't use it with cached data (the query result
			// resource ID won't be any good once we've cached the
			// result object, so we'll have to compile the data
			// and save it)
			$CR = new CI_DB_result();
			$CR->num_rows		= $RES->num_rows();
			$CR->result_object	= $RES->result_object();
			$CR->result_array	= $RES->result_array();

			// Reset these since cached objects can not utilize resource IDs.
			$CR->conn_id		= NULL;
			$CR->result_id		= NULL;

			$this->CACHE->write($sql, $CR);
		}

		return $RES;
	}

	// --------------------------------------------------------------------

	/**
     * 加载一个结果驱动
	 * Load the result drivers
	 *
	 * @access	public
	 * @return	string	the name of the result class
	 */
	function load_rdriver()
	{
		$driver = 'CI_DB_'.$this->dbdriver.'_result';

		if ( ! class_exists($driver))
		{
			include_once(BASEPATH.'database/DB_result.php');
			include_once(BASEPATH.'database/drivers/'.$this->dbdriver.'/'.$this->dbdriver.'_result.php');
		}

		return $driver;
	}

	// --------------------------------------------------------------------

	/**
     * 执行简单sql
     * 1-- 为了确保db_connection已经建立，所以不直接调用底层的execute
     * 2-- 这里只执行一些简单的sql(这些sql与用户输入没有任何关联，也不会产生sql注入)
	 * Simple Query
	 * This is a simplified version of the query() function.  Internally
	 * we only use it when running transaction commands since they do
	 * not require all the features of the main query() function.
	 *
	 * @access	public
	 * @param	string	the sql query
	 * @return	mixed
	 */
	function simple_query($sql)
	{
		if ( ! $this->conn_id)
		{
			$this->initialize();
		}

		return $this->_execute($sql);
	}

	// --------------------------------------------------------------------

	/**
     * 关闭事务
	 * Disable Transactions
	 * This permits transactions to be disabled at run-time.
	 *
	 * @access	public
	 * @return	void
	 */
	function trans_off()
	{
		$this->trans_enabled = FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Enable/disable Transaction Strict Mode
	 * When strict mode is enabled, if you are running multiple groups of
	 * transactions, if one group fails all groups will be rolled back.
	 * If strict mode is disabled, each group is treated autonomously, meaning
	 * a failure of one group will not affect any others
	 *
	 * @access	public
	 * @return	void
	 */
	function trans_strict($mode = TRUE)
	{
		$this->trans_strict = is_bool($mode) ? $mode : TRUE;
	}

	// --------------------------------------------------------------------

	/**
     * 开始事务
	 * Start Transaction
	 *
	 * @access	public
	 * @return	void
	 */
	function trans_start($test_mode = FALSE)
	{
		if ( ! $this->trans_enabled)
		{
			return FALSE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
        // 可以嵌套事务，但是只处理最外层的
		if ($this->_trans_depth > 0)
		{
			$this->_trans_depth += 1;
			return;
		}

		$this->trans_begin($test_mode);
	}

	// --------------------------------------------------------------------

	/**
     * 结束事务
	 * Complete Transaction
	 *
	 * @access	public
	 * @return	bool
	 */
	function trans_complete()
	{
		if ( ! $this->trans_enabled)
		{
			return FALSE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 1)
		{
			$this->_trans_depth -= 1;
			return TRUE;
		}

		// The query() function will set this flag to FALSE in the event that a query failed
		if ($this->_trans_status === FALSE)
		{
			$this->trans_rollback();

			// If we are NOT running in strict mode, we will reset
			// the _trans_status flag so that subsequent groups of transactions
			// will be permitted.
			if ($this->trans_strict === FALSE)
			{
				$this->_trans_status = TRUE;
			}

			log_message('debug', 'DB Transaction Failure');
			return FALSE;
		}

		$this->trans_commit();
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
     * 获取事务状态
     * 1--用作业务逻辑的判定，是否需要记录错误日志&&返货错误信息
	 * Lets you retrieve the transaction flag to determine if it has failed
	 *
	 * @access	public
	 * @return	bool
	 */
	function trans_status()
	{
		return $this->_trans_status;
	}

	// --------------------------------------------------------------------

	/**
     * 替换绑定值
	 * Compile Bindings
	 *
	 * @access	public
	 * @param	string	the sql statement
	 * @param	array	an array of bind data
	 * @return	string
	 */
	function compile_binds($sql, $binds)
	{
        // 是否有绑定值标记
		if (strpos($sql, $this->bind_marker) === FALSE)
		{
			return $sql;
		}

        // 按照数组处理
		if ( ! is_array($binds))
		{
			$binds = array($binds);
		}

		// Get the sql segments around the bind markers
        // 根据绑定标记，切割sql
		$segments = explode($this->bind_marker, $sql);

		// The count of bind should be 1 less then the count of segments
		// If there are more bind arguments trim it down
        // 绑定值必须比被分开段少一个，多余的去除。
		if (count($binds) >= count($segments)) {
			$binds = array_slice($binds, 0, count($segments)-1);
		}

		// Construct the binded query
		$result = $segments[0];
		$i = 0;
		foreach ($binds as $bind)
		{
            // 绑定值转义
			$result .= $this->escape($bind);
			$result .= $segments[++$i];
		}

		return $result;
	}

	// --------------------------------------------------------------------

	/**
     * 判定一个查询语句是否市write类型的
     * 在sql开始处(出去空格之外)如果匹配到set/update/delete等句子, 则是write类型
	 * Determines if a query is a "write" type.
	 *
	 * @access	public
	 * @param	string	An SQL query string
	 * @return	boolean
	 */
	function is_write_type($sql)
	{
		if ( ! preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK)\s+/i', $sql))
		{
			return FALSE;
		}
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
     * 计算总的查询时间
     * 1--总查询时间指当前db connection中执行的所有sql的时间
	 * Calculate the aggregate query elapsed time
	 *
	 * @access	public
	 * @param	integer	The number of decimal places
	 * @return	integer
	 */
	function elapsed_time($decimals = 6)
	{
		return number_format($this->benchmark, $decimals);
	}

	// --------------------------------------------------------------------

	/**
     * 查询次数
     * 1--同上
	 * Returns the total number of queries
	 *
	 * @access	public
	 * @return	integer
	 */
	function total_queries()
	{
		return $this->query_count;
	}

	// --------------------------------------------------------------------

	/**
     * 返回最后一条sql语句
	 * Returns the last query that was executed
	 *
	 * @access	public
	 * @return	void
	 */
	function last_query()
	{
		return end($this->queries);
	}

	// --------------------------------------------------------------------

	/**
     * 转义字符，不能处理数组
     * 1--调用mysql方法
     * 2--需要重点关注
	 * "Smart" Escape String
	 *
	 * Escapes data based on type
	 * Sets boolean and null types
	 *
	 * @access	public
	 * @param	string
	 * @return	mixed
	 */
	function escape($str)
	{
		if (is_string($str))
		{
			$str = "'".$this->escape_str($str)."'";
		}
		elseif (is_bool($str))
		{
            // mysql中1代表true，0代表false
			$str = ($str === FALSE) ? 0 : 1;
		}
		elseif (is_null($str))
		{
			$str = 'NULL';
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
     *
	 * Escape LIKE String
	 *
	 * Calls the individual driver for platform
	 * specific escaping for LIKE conditions
	 *
	 * @access	public
	 * @param	string
	 * @return	mixed
	 */
	function escape_like_str($str)
	{
		return $this->escape_str($str, TRUE);
	}

	// --------------------------------------------------------------------

	/**
     * 获取主键
     * 1--默认主键是第一个字段
	 * Primary
	 *
	 * Retrieves the primary key.  It assumes that the row in the first
	 * position is the primary key
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	string
	 */
	function primary($table = '')
	{
		$fields = $this->list_fields($table);

		if ( ! is_array($fields))
		{
			return FALSE;
		}

		return current($fields);
	}

	// --------------------------------------------------------------------

	/**
	 * Returns an array of table names
	 *
	 * @access	public
	 * @return	array
	 */
	function list_tables($constrain_by_prefix = FALSE)
	{
		// Is there a cached result?
		if (isset($this->data_cache['table_names']))
		{
			return $this->data_cache['table_names'];
		}

		if (FALSE === ($sql = $this->_list_tables($constrain_by_prefix)))
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_unsupported_function');
			}
			return FALSE;
		}

		$retval = array();
		$query = $this->query($sql);

		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				if (isset($row['TABLE_NAME']))
				{
					$retval[] = $row['TABLE_NAME'];
				}
				else
				{
					$retval[] = array_shift($row);
				}
			}
		}

		$this->data_cache['table_names'] = $retval;
		return $this->data_cache['table_names'];
	}

	// --------------------------------------------------------------------

	/**
	 * Determine if a particular table exists
	 * @access	public
	 * @return	boolean
	 */
	function table_exists($table_name)
	{
		return ( ! in_array($this->_protect_identifiers($table_name, TRUE, FALSE, FALSE), $this->list_tables())) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
     * 获取mysql表的字段名称
	 * Fetch MySQL Field Names
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	array
	 */
	function list_fields($table = '')
	{
		// Is there a cached result?
        // 是否有缓存的
		if (isset($this->data_cache['field_names'][$table]))
		{
			return $this->data_cache['field_names'][$table];
		}

        // 表名称不为空
		if ($table == '')
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_field_param_missing');
			}
			return FALSE;
		}

        // 获取查询语句
		if (FALSE === ($sql = $this->_list_columns($table)))
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_unsupported_function');
			}
			return FALSE;
		}

        // 查询
		$query = $this->query($sql);

		$retval = array();
		foreach ($query->result_array() as $row)
		{
			if (isset($row['COLUMN_NAME']))
			{
				$retval[] = $row['COLUMN_NAME'];
			}
			else
			{
				$retval[] = current($row);
			}
		}

		$this->data_cache['field_names'][$table] = $retval;
		return $this->data_cache['field_names'][$table];
	}

	// --------------------------------------------------------------------

	/**
     * 判定表中是否存在某个列
	 * Determine if a particular field exists
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	boolean
	 */
	function field_exists($field_name, $table_name)
	{
		return ( ! in_array($field_name, $this->list_fields($table_name))) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
     * 获取表结构
	 * Returns an object with field data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	object
	 */
	function field_data($table = '')
	{
		if ($table == '')
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_field_param_missing');
			}
			return FALSE;
		}

		$query = $this->query($this->_field_data($this->_protect_identifiers($table, TRUE, NULL, FALSE)));

		return $query->field_data();
	}

	// --------------------------------------------------------------------

	/**
     * 生成一条insert语句
     * 1--仅仅是生成一条sql语句，不做其他操作
     * insert
	 * Generate an insert string
	 *
	 * @access	public
	 * @param	string	the table upon which the query will be performed
	 * @param	array	an associative array data of key/values
	 * @return	string
	 */
	function insert_string($table, $data)
	{
		$fields = array();
		$values = array();

		foreach ($data as $key => $val)
		{
            // 转义字段名称name=>`name`
			$fields[] = $this->_escape_identifiers($key);
            // 转义value
			$values[] = $this->escape($val);
		}

		return $this->_insert($this->_protect_identifiers($table, TRUE, NULL, FALSE), $fields, $values);
	}

	// --------------------------------------------------------------------

	/**
     * 生成一条update语句
	 * Generate an update string
	 *
	 * @access	public
	 * @param	string	the table upon which the query will be performed
	 * @param	array	an associative array data of key/values
	 * @param	mixed	the "where" statement
	 * @return	string
	 */
	function update_string($table, $data, $where)
	{
        // 必须是有条件的更新
		if ($where == '')
		{
			return false;
		}

		$fields = array();
		foreach ($data as $key => $val)
		{
            // 转义字段 and value
			$fields[$this->_protect_identifiers($key)] = $this->escape($val);
		}

		if ( ! is_array($where))
		{
			$dest = array($where);
		}
		else
		{
			$dest = array();
			foreach ($where as $key => $val)
			{
				$prefix = (count($dest) == 0) ? '' : ' AND ';

				if ($val !== '')
				{
                    // where中如果没有符号，默认是=
					if ( ! $this->_has_operator($key))
					{
						$key .= ' =';
					}

					$val = ' '.$this->escape($val);
				}

				$dest[] = $prefix.$key.$val;
			}
		}

        // 组装set table key=value where
		return $this->_update($this->_protect_identifiers($table, TRUE, NULL, FALSE), $fields, $dest);
	}

	// --------------------------------------------------------------------

	/**
     * 判定sql中是否含有操作符
     * 1--不包含like
	 * Tests whether the string has an SQL operator
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */
	function _has_operator($str)
	{
        // 疑问：虽然去掉了首位的空格，str中的制表符被当做操作符，也不一定正确
		$str = trim($str);
		if ( ! preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str))
		{
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
     * 调用php原生的方法
     * 1--通常需要传递参数进来，例如conn_id
	 * Enables a native PHP function to be run, using a platform agnostic wrapper.
	 *
	 * @access	public
	 * @param	string	the function name
	 * @param	mixed	any parameters needed by the function
	 * @return	mixed
	 */
	function call_function($function)
	{
		$driver = ($this->dbdriver == 'postgre') ? 'pg_' : $this->dbdriver.'_';

		if (FALSE === strpos($driver, $function))
		{
			$function = $driver.$function;
		}

		if ( ! function_exists($function))
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_unsupported_function');
			}
			return FALSE;
		}
		else
		{
			$args = (func_num_args() > 1) ? array_splice(func_get_args(), 1) : null;
			if (is_null($args))
			{
				return call_user_func($function);
			}
			else
			{
				return call_user_func_array($function, $args);
			}
		}
	}

	// --------------------------------------------------------------------

	/**
     * 设置缓存的位置
	 * Set Cache Directory Path
	 *
	 * @access	public
	 * @param	string	the path to the cache directory
	 * @return	void
	 */
	function cache_set_path($path = '')
	{
		$this->cachedir = $path;
	}

	// --------------------------------------------------------------------

	/**
     * 打开缓存
	 * Enable Query Caching
	 *
	 * @access	public
	 * @return	void
	 */
	function cache_on()
	{
		$this->cache_on = TRUE;
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
     * 关闭缓存
	 * Disable Query Caching
	 *
	 * @access	public
	 * @return	void
	 */
	function cache_off()
	{
		$this->cache_on = FALSE;
		return FALSE;
	}


	// --------------------------------------------------------------------

	/**
     * 删除当前URI的缓存
	 * Delete the cache files associated with a particular URI
	 *
	 * @access	public
	 * @return	void
	 */
	function cache_delete($segment_one = '', $segment_two = '')
	{
		if ( ! $this->_cache_init())
		{
			return FALSE;
		}
		return $this->CACHE->delete($segment_one, $segment_two);
	}

	// --------------------------------------------------------------------

	/**
     * 删除所有缓存
	 * Delete All cache files
	 *
	 * @access	public
	 * @return	void
	 */
	function cache_delete_all()
	{
		if ( ! $this->_cache_init())
		{
			return FALSE;
		}

		return $this->CACHE->delete_all();
	}

	// --------------------------------------------------------------------

	/**
     * 初始化缓存对象
	 * Initialize the Cache Class
	 *
	 * @access	private
	 * @return	void
	 */
	function _cache_init()
	{
		if (is_object($this->CACHE) AND class_exists('CI_DB_Cache'))
		{
			return TRUE;
		}

		if ( ! class_exists('CI_DB_Cache'))
		{
			if ( ! @include(BASEPATH.'database/DB_cache.php'))
			{
				return $this->cache_off();
			}
		}

		$this->CACHE = new CI_DB_Cache($this); // pass db object to support multiple db connections and returned db objects
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
     * 关闭数据库连接
	 * Close DB Connection
	 *
	 * @access	public
	 * @return	void
	 */
	function close()
	{
		if (is_resource($this->conn_id) OR is_object($this->conn_id))
		{
			$this->_close($this->conn_id);
		}
		$this->conn_id = FALSE;
	}

	// --------------------------------------------------------------------

	/**
     * 展示错误信息
	 * Display an error message
	 *
	 * @access	public
	 * @param	string	the error message
	 * @param	string	any "swap" values
	 * @param	boolean	whether to localize the message
	 * @return	string	sends the application/error_db.php template
	 */
	function display_error($error = '', $swap = '', $native = FALSE)
	{
		$LANG =& load_class('Lang', 'core');
		$LANG->load('db');

		$heading = $LANG->line('db_error_heading');

		if ($native == TRUE)
		{
			$message = $error;
		}
		else
		{
			$message = ( ! is_array($error)) ? array(str_replace('%s', $swap, $LANG->line($error))) : $error;
		}

		// Find the most likely culprit of the error by going through
		// the backtrace until the source file is no longer in the
		// database folder.

		$trace = debug_backtrace();

		foreach ($trace as $call)
		{
			if (isset($call['file']) && strpos($call['file'], BASEPATH.'database') === FALSE)
			{
				// Found it - use a relative path for safety
				$message[] = 'Filename: '.str_replace(array(BASEPATH, APPPATH), '', $call['file']);
				$message[] = 'Line Number: '.$call['line'];

				break;
			}
		}

		$error =& load_class('Exceptions', 'core');
		echo $error->show_error($heading, $message, 'error_db');
		exit;
	}

	// --------------------------------------------------------------------

	/**
	 * Protect Identifiers
	 *
	 * This function adds backticks if appropriate based on db type
	 *
	 * @access	private
	 * @param	mixed	the item to escape
	 * @return	mixed	the item with backticks
	 */
	function protect_identifiers($item, $prefix_single = FALSE)
	{
		return $this->_protect_identifiers($item, $prefix_single);
	}

	// --------------------------------------------------------------------

	/**
     * 增加db_prefix
     * 1--还有更多逻辑，暂时没完全看明白
	 * Protect Identifiers
	 *
	 * This function is used extensively by the Active Record class, and by
	 * a couple functions in this class.
	 * It takes a column or table name (optionally with an alias) and inserts
	 * the table prefix onto it.  Some logic is necessary in order to deal with
	 * column names that include the path.  Consider a query like this:
	 *
	 * SELECT * FROM hostname.database.table.column AS c FROM hostname.database.table
	 *
	 * Or a query with aliasing:
	 *
	 * SELECT m.member_id, m.member_name FROM members AS m
	 *
	 * Since the column name can include up to four segments (host, DB, table, column)
	 * or also have an alias prefix, we need to do a bit of work to figure this out and
	 * insert the table prefix (if it exists) in the proper position, and escape only
	 * the correct identifiers.
	 *
	 * @access	private
	 * @param	string
	 * @param	bool
	 * @param	mixed
	 * @param	bool
	 * @return	string
	 */
	function _protect_identifiers($item, $prefix_single = FALSE, $protect_identifiers = NULL, $field_exists = TRUE)
	{
		if ( ! is_bool($protect_identifiers))
		{
			$protect_identifiers = $this->_protect_identifiers;
		}

        // 递归处理数组
		if (is_array($item))
		{
			$escaped_array = array();

			foreach ($item as $k => $v)
			{
				$escaped_array[$this->_protect_identifiers($k)] = $this->_protect_identifiers($v);
			}

			return $escaped_array;
		}

		// Convert tabs or multiple spaces into single spaces
        // 去除多余的空格或者制表符
		$item = preg_replace('/[\t ]+/', ' ', $item);

		// If the item has an alias declaration we remove it and set it aside.
		// Basically we remove everything to the right of the first space
        // 提取出别名
		if (strpos($item, ' ') !== FALSE)
		{
			$alias = strstr($item, ' ');
			$item = substr($item, 0, - strlen($alias));
		}
		else
		{
			$alias = '';
		}

		// This is basically a bug fix for queries that use MAX, MIN, etc.
		// If a parenthesis is found we know that we do not need to
		// escape the data or add a prefix.  There's probably a more graceful
		// way to deal with this, but I'm not thinking of it -- Rick
		if (strpos($item, '(') !== FALSE)
		{
			return $item.$alias;
		}

		// Break the string apart if it contains periods, then insert the table prefix
		// in the correct location, assuming the period doesn't indicate that we're dealing
		// with an alias. While we're at it, we will escape the components
        // 如果item是这样的: host.database.table.column
		if (strpos($item, '.') !== FALSE)
		{
			$parts	= explode('.', $item);

			// Does the first segment of the exploded item match
			// one of the aliases previously identified?  If so,
			// we have nothing more to do other than escape the item
			if (in_array($parts[0], $this->ar_aliased_tables))
			{
				if ($protect_identifiers === TRUE)
				{
					foreach ($parts as $key => $val)
					{
						if ( ! in_array($val, $this->_reserved_identifiers))
						{
							$parts[$key] = $this->_escape_identifiers($val);
						}
					}

					$item = implode('.', $parts);
				}
				return $item.$alias;
			}

			// Is there a table prefix defined in the config file?  If not, no need to do anything
            // 是否有表前缀
			if ($this->dbprefix != '')
			{
				// We now add the table prefix based on some logic.
				// Do we have 4 segments (hostname.database.table.column)?
				// If so, we add the table prefix to the column name in the 3rd segment.
				if (isset($parts[3]))
				{
					$i = 2;
				}
				// Do we have 3 segments (database.table.column)?
				// If so, we add the table prefix to the column name in 2nd position
				elseif (isset($parts[2]))
				{
					$i = 1;
				}
				// Do we have 2 segments (table.column)?
				// If so, we add the table prefix to the column name in 1st segment
				else
				{
					$i = 0;
				}

				// This flag is set when the supplied $item does not contain a field name.
				// This can happen when this function is being called from a JOIN.
                // 如果没有列名，需要往前挪一个
				if ($field_exists == FALSE)
				{
					$i++;
				}

				// Verify table prefix and replace if necessary
				if ($this->swap_pre != '' && strncmp($parts[$i], $this->swap_pre, strlen($this->swap_pre)) === 0)
				{
					$parts[$i] = preg_replace("/^".$this->swap_pre."(\S+?)/", $this->dbprefix."\\1", $parts[$i]);
				}

				// We only add the table prefix if it does not already exist
				if (substr($parts[$i], 0, strlen($this->dbprefix)) != $this->dbprefix)
				{
					$parts[$i] = $this->dbprefix.$parts[$i];
				}

				// Put the parts back together
				$item = implode('.', $parts);
			}

			if ($protect_identifiers === TRUE)
			{
				$item = $this->_escape_identifiers($item);
			}

			return $item.$alias;
		}

		// Is there a table prefix?  If not, no need to insert it
		if ($this->dbprefix != '')
		{
			// Verify table prefix and replace if necessary
			if ($this->swap_pre != '' && strncmp($item, $this->swap_pre, strlen($this->swap_pre)) === 0)
			{
				$item = preg_replace("/^".$this->swap_pre."(\S+?)/", $this->dbprefix."\\1", $item);
			}

			// Do we prefix an item with no segments?
			if ($prefix_single == TRUE AND substr($item, 0, strlen($this->dbprefix)) != $this->dbprefix)
			{
				$item = $this->dbprefix.$item;
			}
		}

		if ($protect_identifiers === TRUE AND ! in_array($item, $this->_reserved_identifiers))
		{
			$item = $this->_escape_identifiers($item);
		}

		return $item.$alias;
	}

	// --------------------------------------------------------------------

	/**
	 * Dummy method that allows Active Record class to be disabled
	 *
	 * This function is used extensively by every db driver.
	 *
	 * @return	void
	 */
	protected function _reset_select()
	{
	}

}

/* End of file DB_driver.php */
/* Location: ./system/database/DB_driver.php */