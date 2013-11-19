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
 * 异常处理类
 * Exceptions Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Exceptions
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/exceptions.html
 */
class CI_Exceptions {
	var $action;
	var $severity;
	var $message;
	var $filename;
	var $line;

	/**
	 * Nesting level of the output buffering mechanism
	 *
	 * @var int
	 * @access public
	 */
	var $ob_level;

	/**
	 * 可用的错误级别
	 * List if available error levels
	 *
	 * @var array
	 * @access public
	 */
	var $levels = array(
						E_ERROR				=>	'Error',
						E_WARNING			=>	'Warning',
						E_PARSE				=>	'Parsing Error',
						E_NOTICE			=>	'Notice',
						E_CORE_ERROR		=>	'Core Error',
						E_CORE_WARNING		=>	'Core Warning',
						E_COMPILE_ERROR		=>	'Compile Error',
						E_COMPILE_WARNING	=>	'Compile Warning',
						E_USER_ERROR		=>	'User Error',
						E_USER_WARNING		=>	'User Warning',
						E_USER_NOTICE		=>	'User Notice',
						E_STRICT			=>	'Runtime Notice'
					);


	/**
	 * 构造方法
	 * Constructor
	 */
	public function __construct()
	{
		$this->ob_level = ob_get_level();
		// Note:  Do not log messages from this constructor.
	}

	// --------------------------------------------------------------------

	/**
	 * 异常日志处理方法
	 * 1--记录错误级别
	 * 2--调用log_message公用方法记录日志(日志级别为error)
	 * Exception Logger
	 *
	 * This function logs PHP generated error messages
	 *
	 * @access	private
	 * @param	string	the error severity
	 * @param	string	the error string
	 * @param	string	the error filepath
	 * @param	string	the error line number
	 * @return	string
	 */
	function log_exception($severity, $message, $filepath, $line)
	{
		// 记录错误级别(开发者可以自己指定， 也可以使用默认的)
		$severity = ( ! isset($this->levels[$severity])) ? $severity : $this->levels[$severity];

		// 打印日志
		log_message('error', 'Severity: '.$severity.'  --> '.$message. ' '.$filepath.' '.$line, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * 处理404错误
	 * 404 Page Not Found Handler
	 *
	 * @access	private
	 * @param	string	the page
	 * @param 	bool	log error yes/no
	 * @return	string
	 */
	function show_404($page = '', $log_error = TRUE)
	{
		$heading = "404 Page Not Found";
		$message = "The page you requested was not found.";

		// By default we log this, but allow a dev to skip it
		// 记录错误日志
		if ($log_error)
		{
			log_message('error', '404 Page Not Found --> '.$page);
		}

		// 显示404页面并退出
		echo $this->show_error($heading, $message, 'error_404', 404);
		exit;
	}

	// --------------------------------------------------------------------

	/**
	 * 错误页面
	 * General Error Page
	 *
	 * This function takes an error message as input
	 * (either as a string or an array) and displays
	 * it using the specified template.
	 *
	 * @access	private
	 * @param	string	the heading
	 * @param	string	the message
	 * @param	string	the template name
	 * @param 	int		the status code
	 * @return	string
	 */
	function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		
		// 设定header
		set_status_header($status_code);

		// 错误提示(用html标签包过错误信息$message)
		$message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		// 包含apppath/errors目录下的错误页面
		include(APPPATH.'errors/'.$template.'.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

	// --------------------------------------------------------------------

	/**
	 * 显示php错误
	 * Native PHP error handler
	 *
	 * @access	private
	 * @param	string	the error severity
	 * @param	string	the error string
	 * @param	string	the error filepath
	 * @param	string	the error line number
	 * @return	string
	 */
	function show_php_error($severity, $message, $filepath, $line)
	{
		$severity = ( ! isset($this->levels[$severity])) ? $severity : $this->levels[$severity];

		$filepath = str_replace("\\", "/", $filepath);

		// For safety reasons we do not show the full file path
		if (FALSE !== strpos($filepath, '/'))
		{
			$x = explode('/', $filepath);
			$filepath = $x[count($x)-2].'/'.end($x);
		}

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		include(APPPATH.'errors/error_php.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}


}
// END Exceptions Class

/* End of file Exceptions.php */
/* Location: ./system/core/Exceptions.php */