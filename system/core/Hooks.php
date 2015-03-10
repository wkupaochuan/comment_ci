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
 * 钩子
 * 1--没理解钩子的意义
 * 2--
 * 3--
 * CodeIgniter Hooks Class
 *
 * Provides a mechanism to extend the base system without hacking.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/encryption.html
 */
class CI_Hooks {

	/**
	 * 是否启用钩子
	 * Determines wether hooks are enabled
	 *
	 * @var bool
	 */
	var $enabled		= FALSE;
	/**
	 * List of all hooks set in config/hooks.php
	 *
	 * @var array
	 */
	var $hooks			= array();
	/**
	 * Determines wether hook is in progress, used to prevent infinte loops
	 *
	 * @var bool
	 */
	var $in_progress	= FALSE;

	/**
	 * 构造方法
	 * 1--构造方法的内容放在了_initialize方法内
	 * 2--为什么很多其他类不仿照这种方法封装呢?
	 * Constructor
	 *
	 */
	function __construct()
	{
		$this->_initialize();
		log_message('debug', "Hooks Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * 初始化
	 * 1--初始化钩子的各项特性
	 * 2--钩子定义在hooks.php配置文件中
	 * 3--config.php中定义了是否启用钩子
	 * 4--
	 * Initialize the Hooks Preferences
	 *
	 * @access	private
	 * @return	void
	 */
	function _initialize()
	{
		// 获取config.php配置文件
		$CFG =& load_class('Config', 'core');

		// If hooks are not enabled in the config file
		// there is nothing else to do

		// 校验是否启用了钩子
		if ($CFG->item('enable_hooks') == FALSE)
		{
			return;
		}

		// Grab the "hooks" definition file.
		// If there are no hooks, we're done.

		// 获取钩子定义，将配置文件包含进来
		if (defined('ENVIRONMENT') AND is_file(APPPATH.'config/'.ENVIRONMENT.'/hooks.php'))
		{
		    include(APPPATH.'config/'.ENVIRONMENT.'/hooks.php');
		}
		elseif (is_file(APPPATH.'config/hooks.php'))
		{
			include(APPPATH.'config/hooks.php');
		}


		// $hook变量为配置文件指定，必须是数组
		if ( ! isset($hook) OR ! is_array($hook))
		{
			return;
		}

		// 获取钩子定义(从文件读取配置到内存)
		$this->hooks =& $hook;
		$this->enabled = TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * 调用特定的钩子
	 * Call Hook
	 *
	 * Calls a particular hook
	 *
	 * @access	private
	 * @param	string	the hook name
	 * @return	mixed
	 */
	function _call_hook($which = '')
	{
		// 校验是否启用了钩子且在hook.php中配置了相应的hook
		if ( ! $this->enabled OR ! isset($this->hooks[$which]))
		{
			return FALSE;
		}

		
		// 启动hook
		if (isset($this->hooks[$which][0]) AND is_array($this->hooks[$which][0]))
		{
			foreach ($this->hooks[$which] as $val)
			{
				$this->_run_hook($val);
			}
		}
		else
		{
			$this->_run_hook($this->hooks[$which]);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * 启动一个钩子
	 * Run Hook
	 *
	 * Runs a particular hook
	 *
	 * @access	private
	 * @param	array	the hook details
	 * @return	bool
	 */
	function _run_hook($data)
	{
		if ( ! is_array($data))
		{
			return FALSE;
		}

		// -----------------------------------
		// Safety - Prevents run-away loops
		// -----------------------------------

		// If the script being called happens to have the same
		// hook call within it a loop can happen

		// 钩子之间不可并行(调用钩子的地方都是单例模式,保证了in_progress的有效性)
        // 防止重入
		if ($this->in_progress == TRUE)
		{
			return;
		}

		// -----------------------------------
		// Set file path
		// -----------------------------------

		// 指定钩子的路径和文件名
		if ( ! isset($data['filepath']) OR ! isset($data['filename']))
		{
			return FALSE;
		}

		
		$filepath = APPPATH.$data['filepath'].'/'.$data['filename'];

		if ( ! file_exists($filepath))
		{
			return FALSE;
		}

		// -----------------------------------
		// Set class/function name
		// -----------------------------------

		$class		= FALSE;
		$function	= FALSE;
		$params		= '';

		// 类名
		if (isset($data['class']) AND $data['class'] != '')
		{
			$class = $data['class'];
		}

		// 方法名
		if (isset($data['function']))
		{
			$function = $data['function'];
		}

		// 参数
		if (isset($data['params']))
		{
			$params = $data['params'];
		}

		// 类名和方法名必须指定
		if ($class === FALSE AND $function === FALSE)
		{
			return FALSE;
		}

		// -----------------------------------
		// Set the in_progress flag
		// -----------------------------------

		// 标记钩子启动
		$this->in_progress = TRUE;

		// -----------------------------------
		// Call the requested class and/or function
		// -----------------------------------

		// 调用被请求的钩子的方法
		// 钩子文件可以是类也可以是纯方法
		if ($class !== FALSE)
		{
			if ( ! class_exists($class))
			{
				require($filepath);
			}

			$HOOK = new $class;
			$HOOK->$function($params);
		}
		else
		{
			if ( ! function_exists($function))
			{
				require($filepath);
			}

			$function($params);
		}

		$this->in_progress = FALSE;
		return TRUE;
	}

}

// END CI_Hooks class

/* End of file Hooks.php */
/* Location: ./system/core/Hooks.php */