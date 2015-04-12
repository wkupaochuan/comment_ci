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
 * 
 * 日志类
 * 1--日志级别.入口log_threshold为开发者配置,打日志过程中只有日志级别低于入口才可以打印.可用日志级别ERROR/DEBUG/INFO/ALL
 * 2--日志路径.log_path配置或者使用默认的apppath/log
 * 3--日志格式
 * 4--
 * Logging Class
 * 
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Logging
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/general/errors.html
 */
class CI_Log {

	protected $_log_path;
	protected $_threshold	= 1;
	protected $_date_fmt	= 'Y-m-d H:i:s';
	protected $_enabled	= TRUE;
	// 日志级别
	protected $_levels	= array('ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4');

	/**
	 * 构造方法
	 * 1--日志路径
	 * 2--日志级别入口threshold
	 * 3--日期格式
	 * Constructor
	 */
	public function __construct()
	{
		// 获取config.php配置
		$config =& get_config();

		// 日志路径(开发者可以自定义或者使用默认的apppath/log)
		$this->_log_path = ($config['log_path'] != '') ? $config['log_path'] : APPPATH.'logs/';

		// 校验日志路径(保证日志路径存在并且有写入权限)
		if ( ! is_dir($this->_log_path) OR ! is_really_writable($this->_log_path))
		{
			$this->_enabled = FALSE;
		}

		
		// 级别
		if (is_numeric($config['log_threshold']))
		{
			$this->_threshold = $config['log_threshold'];
		}

		// 日志中的日期格式(开发者可以自己在配置文件中配置，也可以使用默认的Y-m-d H:i:s)
		if ($config['log_date_format'] != '')
		{
			$this->_date_fmt = $config['log_date_format'];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * 写日志方法
	 * 1--判定日志是否可写(日志文件可写、日志级别高于threshold)
	 * 2--按照固定格式拼接 日志内容
	 * 3--写文件
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @param	string	the error level
	 * @param	string	the error message
	 * @param	bool	whether the error is a native PHP error
	 * @return	bool
	 */
	public function write_log($level = 'error', $msg, $php_error = FALSE)
	{
		// 保证可写
		if ($this->_enabled === FALSE)
		{
			return FALSE;
		}

		// 日志级别
		$level = strtoupper($level);

		// threshold标记了系统中可写的日志级别，只有级别高于threshold的才可以写入,threshold默认配置为0,所以不会有日志产生
		if ( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
		{
			return FALSE;
		}

		// 日志名称(日志按天分)
		$filepath = $this->_log_path.'log-'.date('Y-m-d').'.php';
		$message  = '';

		// 写入日志文件头(CI默认所有文件的文件头)
		if ( ! file_exists($filepath))
		{
			$message .= "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
		}

		
		// 获取文件句柄
		if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
		{
			return FALSE;
		}

		// 按照固定格式拼接日志内容
		$message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";

        /**
         * 写入日志文件，为了防止多个进程同时写文件，所以使用了文件锁
         * (0) 有人写了一个代替flock的方法///http://www.cnblogs.com/web-lover/archive/2012/01/23/2615949.html
         */
        flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);


		@chmod($filepath, FILE_WRITE_MODE);
		return TRUE;
	}

}
// END Log Class

/* End of file Log.php */
/* Location: ./system/libraries/Log.php */