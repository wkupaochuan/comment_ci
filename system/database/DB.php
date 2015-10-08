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
 * Initialize the database
 * 初始化数据库对象
 * 结构:
 * 1--获取数据库连接参数(包括取配置文件和通过dns字符串传递方式)
 * 2--根据active_record采用eval方法获取相应的CI_DB类
 * 3--加载正确的DB类,根据driver类型到database/driver下面查找相应的driver并实例化DB类
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 * @param 	string
 * @param 	bool	Determines if active record should be used or not
 * 
 */
function &DB($params = '', $active_record_override = NULL)
{
	// Load the DB config file if a DSN string wasn't passed
	// 调用database.php配置文件中的数据库配置
	// 如果形参params是字符串并且字符串中不包含://字符
	// 这说明params是在APPPATH/config/database.php中配置的$db数组的某个key
	if (is_string($params) AND strpos($params, '://') === FALSE)
	{
		// Is the config file in the environment folder?
		// 获取配置文件的路径，并赋值给$file_path，如果不存在配置文件需要报错
		if ( ! defined('ENVIRONMENT') OR ! file_exists($file_path = APPPATH.'config/'.ENVIRONMENT.'/database.php'))
		{
			if ( ! file_exists($file_path = APPPATH.'config/database.php'))
			{
				show_error('The configuration file database.php does not exist.');
			}
		}

		include($file_path);

		// 验证配置文件的正确性
		// 配置文件必须采用$db数组配置各个数据库连接属性
		if ( ! isset($db) OR count($db) == 0)
		{
			show_error('No database connection settings were found in the database config file.');
		}

		// 确定需要实例化的数据库配置，赋值给$active_group
		// 如果不指定则，默认使用database.php中指定的$active_group
		// 用户通过这个参数指定想要加载的数据库配置
		if ($params != '')
		{
			$active_group = $params;
		}

		// 验证active_group
		// 必须指定，且db数组中存在这项配置
		if ( ! isset($active_group) OR ! isset($db[$active_group]))
		{
			show_error('You have specified an invalid database connection group.');
		}

		// 获取数据库配置参数，赋值给params
		$params = $db[$active_group];
	}// 调用配置文件中的数据库配置完毕
	// 用户通过拼接字符串的方式指定数据库配置
	elseif (is_string($params))
	{

		/* parse the URL from the DSN string
		 *  Database settings can be passed as discreet
		 *  parameters or as a data source name in the first
		 *  parameter. DSNs must have this prototype:
		 *  $dsn = 'driver://username:password@hostname/database';
		 */

		/*
		 * 
		 * 字符串格式:$dsn = 'driver://username:password@hostname/database';
		 * */
		
		// 解析数据库参数字符串
		if (($dns = @parse_url($params)) === FALSE)
		{
			show_error('Invalid DB Connection String');
		}

		// 将参数赋值给$params数组
		$params = array(
							'dbdriver'	=> $dns['scheme'],
							'hostname'	=> (isset($dns['host'])) ? rawurldecode($dns['host']) : '',
							'username'	=> (isset($dns['user'])) ? rawurldecode($dns['user']) : '',
							'password'	=> (isset($dns['pass'])) ? rawurldecode($dns['pass']) : '',
							'database'	=> (isset($dns['path'])) ? rawurldecode(substr($dns['path'], 1)) : ''
						);

		// were additional config items set?
		// 是否在query中指定了更多的参数
		if (isset($dns['query']))
		{
			/*
			 * 解析query获取过多的参数
			 * ex: pconnect=TRUE&db_debug=TRUE
			 * 会解析出$extra = array(
			 * 	'pconnect'=>'TRUE'
			 * 	'db_debug'=>'TRUE'
			 * );
			 * */
			parse_str($dns['query'], $extra);

			foreach ($extra as $key => $val)
			{
				// booleans please
				if (strtoupper($val) == "TRUE")
				{
					$val = TRUE;
				}
				elseif (strtoupper($val) == "FALSE")
				{
					$val = FALSE;
				}

				$params[$key] = $val;
			}
		}
	}// 结束  用户通过拼接字符串的方式指定数据库配置

	// No DB specified yet?  Beat them senseless...
	// 必须指定driver类型，因为后续的程序会根据dirver类型找到子类，并建立数据库连接
	if ( ! isset($params['dbdriver']) OR $params['dbdriver'] == '')
	{
		show_error('You have not selected a database type to connect to.');
	}

	// Load the DB classes.  Note: Since the active record class is optional
	// we need to dynamically create a class that extends proper parent class
	// based on whether we're using the active record class or not.
	// Kudos to Paul for discovering this clever use of eval()

	if ($active_record_override !== NULL)
	{
		$active_record = $active_record_override;
	}

	/**
	 * 加载数据库实例化类
	 * 1--是否启用active_record,如果将active_record指定为true或者不指定，则启用
	 * 2--根据active_record确定CI_DB类从哪个类继承
	 * 
	 */
	require_once(BASEPATH.'database/DB_driver.php');
	if ( ! isset($active_record) OR $active_record == TRUE)
	{
		require_once(BASEPATH.'database/DB_active_rec.php');

		if ( ! class_exists('CI_DB'))
		{
            // 执行代码
			eval('class CI_DB extends CI_DB_active_record { }');
		}
	}
	else
	{
		if ( ! class_exists('CI_DB'))
		{
			eval('class CI_DB extends CI_DB_driver { }');
		}
	}

	// 加载子类,这个子类继承自CI_DB类
	require_once(BASEPATH.'database/drivers/'.$params['dbdriver'].'/'.$params['dbdriver'].'_driver.php');

	// Instantiate the DB adapter
	// 确定类名(DB类都已CI_DB_开头)
	$driver = 'CI_DB_'.$params['dbdriver'].'_driver';
	// 实例化类
	$DB = new $driver($params);

	// 是否自动初始化
    // db_driver中都是自动加载的
	if ($DB->autoinit == TRUE)
	{
		$DB->initialize();
	}

	if (isset($params['stricton']) && $params['stricton'] == TRUE)
	{
		$DB->query('SET SESSION sql_mode="STRICT_ALL_TABLES"');
	}

	return $DB;
}



/* End of file DB.php */
/* Location: ./system/database/DB.php */