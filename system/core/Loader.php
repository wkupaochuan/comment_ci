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
 * Loader Class
 *
 * Loads views and files
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @author		ExpressionEngine Dev Team
 * @category	Loader
 * @link		http://codeigniter.com/user_guide/libraries/loader.html
 */
class CI_Loader {

	// All these are set automatically. Don't mess with them.
	/**
	 * Nesting level of the output buffering mechanism
	 *
	 * @var int
	 * @access protected
	 */
	protected $_ci_ob_level;
	/**
	 * List of paths to load views from
	 * 需要加载的view的路径数组
	 * @var array
	 * @access protected
	 */
	protected $_ci_view_paths		= array();
	/**
	 * List of paths to load libraries from
	 * 可能存放library的路径；最近本的是APPPATH和BASEPATH；其次有可能加载另外的APPPATH
	 * @var array
	 * @access protected
	 */
	protected $_ci_library_paths	= array();
	/**
	 * List of paths to load models from
	 * 需要加载的model路径数组
	 * @var array
	 * @access protected
	 */
	protected $_ci_model_paths		= array();
	/**
	 * List of paths to load helpers from
	 * 需要加载的helper路径数组
	 * @var array
	 * @access protected
	 */
	protected $_ci_helper_paths		= array();
	/**
	 * List of loaded base classes
	 * Set by the controller class
	 * 已经加载的基础类数组，内容由控制器类生成
	 * @var array
	 * @access protected
	 */
	protected $_base_classes		= array(); // Set by the controller class
	/**
	 * List of cached variables
	 * 缓存变量数组
	 * @var array
	 * @access protected
	 */
	protected $_ci_cached_vars		= array();
	/**
	 * List of loaded classes
	 * 已经加载的类数组
	 * @var array
	 * @access protected
	 */
	protected $_ci_classes			= array();
	/**
	 * List of loaded files
	 * 
	 * @var array
	 * @access protected
	 */
	protected $_ci_loaded_files		= array();
	/**
	 * List of loaded models
	 *
	 * @var array
	 * @access protected
	 */
	protected $_ci_models			= array();
	/**
	 * List of loaded helpers
	 *
	 * @var array
	 * @access protected
	 */
	protected $_ci_helpers			= array();
	/**
	 * List of class name mappings
	 *
	 * @var array
	 * @access protected
	 */
	protected $_ci_varmap			= array('unit_test' => 'unit',
											'user_agent' => 'agent');

	/**
	 * Constructor
	 * 构造函数
	 * Sets the path to the view files and gets the initial output buffering level
	 */
	public function __construct()
	{
		//ob_level不了解
		$this->_ci_ob_level  = ob_get_level();
		$this->_ci_library_paths = array(APPPATH, BASEPATH);
		$this->_ci_helper_paths = array(APPPATH, BASEPATH);
		$this->_ci_model_paths = array(APPPATH);
		$this->_ci_view_paths = array(APPPATH.'views/'	=> TRUE);

		log_message('debug', "Loader Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize the Loader
	 * 这个方法仅仅被CI_Controller调用一次
	 * This method is called once in CI_Controller.
	 *
	 * @param 	array
	 * @return 	object
	 */
	public function initialize()
	{
		//将已经加载的各个缓存数组都清空
		$this->_ci_classes = array();
		$this->_ci_loaded_files = array();
		$this->_ci_models = array();
		//获取在CI_Controller之前已经加载的基类
		$this->_base_classes =& is_loaded();

		//加载所有需要自动加载的类
		$this->_ci_autoloader();

		//返回load
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Is Loaded
	 *
	 * 判定某个class是否已经加载
	 * 如果已经加载就返回类, 否则返回false
	 * A utility function to test if a class is in the self::$_ci_classes array.
	 * This function returns the object name if the class tested for is loaded,
	 * and returns FALSE if it isn't.
	 *
	 * It is mainly used in the form_helper -> _get_validation_object()
	 *
	 * @param 	string	class being checked for
	 * @return 	mixed	class object name on the CI SuperObject or FALSE
	 */
	public function is_loaded($class)
	{
		if (isset($this->_ci_classes[$class]))
		{
			return $this->_ci_classes[$class];
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
     * 加载library下的类
	 * Class Loader
	 *
	 * This function lets users load and instantiate classes.
	 * It is designed to be called from a user's app controllers.
	 *
	 * 加载library(包括basepath和appath下的)，并赋值给超对象CI
	 * @param	string	the name of the class 可以是类名或者类名数组
	 * @param	mixed	the optional parameters 实例化类需要的参数
	 * @param	string	an optional object name//这个obj_name指的是，你可以指定给超类CI的成员变量的名字，可以按自己的喜好指定，方便使用
	 * @return	void
	 */
	public function library($library = '', $params = NULL, $object_name = NULL)
	{
		// 如果指定了类名数组，就递归这个方法(这个时候object_name肯定不能递归了)
		if (is_array($library))
		{
			foreach ($library as $class)
			{
				$this->library($class, $params);
			}

			return;
		}

		// 这个方法只用来家来library下的类，不能为空，而且不能加载组件类(例如Security, input等, 相当于这些都是关键词, 用户不可以使用这些)
		if ($library == '' OR isset($this->_base_classes[$library]))
		{
			return FALSE;
		}

		// 强制参数为null或者数组
		if ( ! is_null($params) && ! is_array($params))
		{
			$params = NULL;
		}

        // 加载
		$this->_ci_load_class($library, $params, $object_name);
	}

	// --------------------------------------------------------------------

	/**
	 * Model Loader
	 *
	 * This function lets users load and instantiate models.
	 * 加载一个model并赋值给超对象CI的某个属性
	 * 有几点需要注意
	 * 	1--单例模式的key，是指定的$model的除去路径后的纯粹文件名
	 *  2--CI中规定model文件名字母都是小写,model的类名首字母大写
	 * @param	string	the name of the class
	 * @param	string	name for the model
	 * @param	bool	database connection
	 * @return	void
	 */
	public function model($model, $name = '', $db_conn = FALSE)
	{
		// 如果指定了一组model则递归这个函数
		if (is_array($model))
		{
			foreach ($model as $babe)
			{
				$this->model($babe);
			}
			return;
		}

		// 必须指定model
		if ($model == '')
		{
			return;
		}

		$path = '';

		// Is the model in a sub-folder? If so, parse out the filename and path.
		// 如果model存在子路径中，则分别去除路径和纯粹的model名字
		if (($last_slash = strrpos($model, '/')) !== FALSE)
		{
			// The path is in front of the last slash
			$path = substr($model, 0, $last_slash + 1);

			// And the model name behind it
			$model = substr($model, $last_slash + 1);
		}

		if ($name == '')
		{
			$name = $model;
		}

		// 单例模式，非重复加载
		if (in_array($name, $this->_ci_models, TRUE))
		{
			return;
		}

		// 获取超对象CI，检查是否已经制定过这个属性
		$CI =& get_instance();
		if (isset($CI->$name))
		{
			show_error('The model name you are loading is the name of a resource that is already being used: '.$name);
		}

		$model = strtolower($model);

		foreach ($this->_ci_model_paths as $mod_path)
		{
			// 在每个app中查找这个model,要知道model文件名字全是小写
			if ( ! file_exists($mod_path.'models/'.$path.$model.'.php'))
			{
				continue;
			}

			if ($db_conn !== FALSE AND ! class_exists('CI_DB'))
			{
				if ($db_conn === TRUE)
				{
					$db_conn = '';
				}

				$CI->load->database($db_conn, FALSE, TRUE);
			}

			if ( ! class_exists('CI_Model'))
			{
				load_class('Model', 'core');
			}

			// 把model文件包含进来
			require_once($mod_path.'models/'.$path.$model.'.php');

			// 类名的首字符大写
			$model = ucfirst($model);

			// 实例化model，并赋值给超对象CI的属性$name
			$CI->$name = new $model();

			$this->_ci_models[] = $name;
			return;
		}

		// couldn't find the model
		show_error('Unable to locate the model you have specified: '.$model);
	}

	// --------------------------------------------------------------------

	/**
	 * Database Loader
	 * 加载数据库实例
     * 1-- 这里提出一个问题
	 * @param	string	the DB credentials
	 * @param	bool	whether to return the DB object
	 * @param	bool	whether to enable active record (this allows us to override the config setting)
	 * @return	object
	 */
	public function database($params = '', $return = FALSE, $active_record = NULL)
	{
		// Grab the super object
		$CI =& get_instance();

		// Do we even need to load the database class?
		// 单例模式，如果已经加载且不需要返回数据库对象，则返回false
		if (class_exists('CI_DB') AND $return == FALSE AND $active_record == NULL AND isset($CI->db) AND is_object($CI->db))
		{
			return FALSE;
		}

		// 加载DB类
		require_once(BASEPATH.'database/DB.php');

		// 需要返回数据库类，则直接返回类；否则把实例化的数据库对象赋值给超对象CI->db
		if ($return === TRUE)
		{
			return DB($params, $active_record);
		}

		// Initialize the db variable.  Needed to prevent
		// reference errors with some configurations
		$CI->db = '';

		// Load the DB class
		$CI->db =& DB($params, $active_record);
	}

	// --------------------------------------------------------------------

	/**
	 * Load the Utilities Class
	 *
	 * @return	string
	 */
	public function dbutil()
	{
		if ( ! class_exists('CI_DB'))
		{
			$this->database();
		}

		$CI =& get_instance();

		// for backwards compatibility, load dbforge so we can extend dbutils off it
		// this use is deprecated and strongly discouraged
		$CI->load->dbforge();

		require_once(BASEPATH.'database/DB_utility.php');
		require_once(BASEPATH.'database/drivers/'.$CI->db->dbdriver.'/'.$CI->db->dbdriver.'_utility.php');
		$class = 'CI_DB_'.$CI->db->dbdriver.'_utility';

		$CI->dbutil = new $class();
	}

	// --------------------------------------------------------------------

	/**
	 * Load the Database Forge Class
	 *
	 * @return	string
	 */
	public function dbforge()
	{
		if ( ! class_exists('CI_DB'))
		{
			$this->database();
		}

		$CI =& get_instance();

		require_once(BASEPATH.'database/DB_forge.php');
		require_once(BASEPATH.'database/drivers/'.$CI->db->dbdriver.'/'.$CI->db->dbdriver.'_forge.php');
		$class = 'CI_DB_'.$CI->db->dbdriver.'_forge';

		$CI->dbforge = new $class();
	}

	// --------------------------------------------------------------------

	/**
	 * Load View
	 *
	 * This function is used to load a "view" file.  It has three parameters:
	 *
	 * 1. The name of the "view" file to be included.
	 * 2. An associative array of data to be extracted for use in the view.
	 * 3. TRUE/FALSE - whether to return the data or load it.  In
	 * some cases it's advantageous to be able to return data so that
	 * a developer can process it in some way.
	 *
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	public function view($view, $vars = array(), $return = FALSE)
	{
		return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
	}

	// --------------------------------------------------------------------

	/**
	 * Load File
	 *
	 * This is a generic file loader
	 *
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	public function file($path, $return = FALSE)
	{
		return $this->_ci_load(array('_ci_path' => $path, '_ci_return' => $return));
	}

	// --------------------------------------------------------------------

	/**
	 * Set Variables
	 *
	 * Once variables are set they become available within
	 * the controller class and its "view" files.
	 *
	 * @param	array
	 * @param 	string
	 * @return	void
	 */
	public function vars($vars = array(), $val = '')
	{
		if ($val != '' AND is_string($vars))
		{
			$vars = array($vars => $val);
		}

		$vars = $this->_ci_object_to_array($vars);

		if (is_array($vars) AND count($vars) > 0)
		{
			foreach ($vars as $key => $val)
			{
				$this->_ci_cached_vars[$key] = $val;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get Variable
	 *
	 * Check if a variable is set and retrieve it.
	 *
	 * @param	array
	 * @return	void
	 */
	public function get_var($key)
	{
		return isset($this->_ci_cached_vars[$key]) ? $this->_ci_cached_vars[$key] : NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Load Helper
	 *
	 * This function loads the specified helper file.
	 * 加载某个helper，有两点需要注意
	 * 	1--helper中仅仅是方法，并不是一个类，所以只需要inlude进来即可
	 * 	2--调用Helper中的方法，在加载完之后直接使用即可
	 *  3--CI中规定每个helper必须以_helper后缀
	 * @param	mixed
	 * @return	void
	 */
	public function helper($helpers = array())
	{
		//确保helpers数组内的字符串都是_helper后缀(CI中规定helper必须加上_helper后缀)
		foreach ($this->_ci_prep_filename($helpers, '_helper') as $helper)
		{
			// 采用单例模式，不重复加载
			if (isset($this->_ci_helpers[$helper]))
			{
				continue;
			}

			// 先假定这个类是继承的basepath中的helper
			$ext_helper = APPPATH.'helpers/'.config_item('subclass_prefix').$helper.'.php';

			// Is this a helper extension request?
			if (file_exists($ext_helper))
			{
				$base_helper = BASEPATH.'helpers/'.$helper.'.php';

				// 父类必须存在
				if ( ! file_exists($base_helper))
				{
					show_error('Unable to load the requested file: helpers/'.$helper.'.php');
				}

				// 将子类和父类文件都包含进来
				include_once($ext_helper);
				include_once($base_helper);

				// 将加载完的Helper名字放入数组,并跳过此次循环
				$this->_ci_helpers[$helper] = TRUE;
				log_message('debug', 'Helper loaded: '.$helper);
				continue;
			}// 假定继承basepath结束

			// Try to load the helper
			// 下面的逻辑是这个helper非继承自basepath, 则到baspath和apppath中分别搜索
			foreach ($this->_ci_helper_paths as $path)
			{
				if (file_exists($path.'helpers/'.$helper.'.php'))
				{
					include_once($path.'helpers/'.$helper.'.php');

					$this->_ci_helpers[$helper] = TRUE;
					log_message('debug', 'Helper loaded: '.$helper);
					break;
				}
			}

			// unable to load the helper
			// 查看是否成功加载了这个helper
			if ( ! isset($this->_ci_helpers[$helper]))
			{
				show_error('Unable to load the requested file: helpers/'.$helper.'.php');
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Load Helpers 
	 * 仅仅是Helper方法的一个别名，以防开发者使用了复数形式
	 * This is simply an alias to the above function in case the
	 * user has written the plural form of this function.
	 *
	 * @param	array
	 * @return	void
	 */
	public function helpers($helpers = array())
	{
		$this->helper($helpers);
	}

	// --------------------------------------------------------------------

	/**
	 * Loads a language file
	 * 调用超对象CI中的lang属性来加载相应的language文件
	 * @param	array
	 * @param	string
	 * @return	void
	 */
	public function language($file = array(), $lang = '')
	{
		$CI =& get_instance();

		if ( ! is_array($file))
		{
			$file = array($file);
		}

		foreach ($file as $langfile)
		{
			$CI->lang->load($langfile, $lang);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Loads a config file
	 * 同上，调用超对象CI中的属性config加载相应的配置文件
	 * @param	string
	 * @param	bool
	 * @param 	bool
	 * @return	void
	 */
	public function config($file = '', $use_sections = FALSE, $fail_gracefully = FALSE)
	{
		$CI =& get_instance();
		$CI->config->load($file, $use_sections, $fail_gracefully);
	}

	// --------------------------------------------------------------------

	/**
	 * Driver
	 *
	 * Loads a driver library
	 *
	 * @param	string	the name of the class
	 * @param	mixed	the optional parameters
	 * @param	string	an optional object name
	 * @return	void
	 */
	public function driver($library = '', $params = NULL, $object_name = NULL)
	{
		if ( ! class_exists('CI_Driver_Library'))
		{
			// we aren't instantiating an object here, that'll be done by the Library itself
			require BASEPATH.'libraries/Driver.php';
		}

		if ($library == '')
		{
			return FALSE;
		}

		// We can save the loader some time since Drivers will *always* be in a subfolder,
		// and typically identically named to the library
		if ( ! strpos($library, '/'))
		{
			$library = ucfirst($library).'/'.$library;
		}

		return $this->library($library, $params, $object_name);
	}

	// --------------------------------------------------------------------

	/**
	 * Add Package Path
	 *
	 * Prepends a parent path to the library, model, helper, and config path arrays
	 * 这里可能有需要加载的第三方的东西，所以需要把父级的根目录加入到这些路径数组中去
	 * @param	string
	 * @param 	boolean
	 * @return	void
	 */
	public function add_package_path($path, $view_cascade=TRUE)
	{
		$path = rtrim($path, '/').'/';

		array_unshift($this->_ci_library_paths, $path);
		array_unshift($this->_ci_model_paths, $path);
		array_unshift($this->_ci_helper_paths, $path);

		$this->_ci_view_paths = array($path.'views/' => $view_cascade) + $this->_ci_view_paths;

		// Add config file path
		$config =& $this->_ci_get_component('config');
		array_unshift($config->_config_paths, $path);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Package Paths
	 *
	 * Return a list of all package paths, by default it will ignore BASEPATH.
	 * 返回根目录路径数组(basepath/apppath等)
	 * _ci_library_paths中包含basepath,但是_ci_model_paths不包含basepath
	 * @param	string
	 * @return	void
	 */
	public function get_package_paths($include_base = FALSE)
	{
		return $include_base === TRUE ? $this->_ci_library_paths : $this->_ci_model_paths;
	}

	// --------------------------------------------------------------------

	/**
	 * Remove Package Path
	 *
	 * Remove a path from the library, model, and helper path arrays if it exists
	 * If no path is provided, the most recently added path is removed.
	 * 去除一个package_path，如果存在
	 * 如果不存在则去除一个最近添加的
	 * @param	type
	 * @param 	bool
	 * @return	type
	 */
	public function remove_package_path($path = '', $remove_config_path = TRUE)
	{
		//获取超类CI的成员变量config
		$config =& $this->_ci_get_component('config');

		//如果没有指定path，则去除队列头部的一个，(因为添加的时候使用的是unshift)
		if ($path == '')
		{
			$void = array_shift($this->_ci_library_paths);
			$void = array_shift($this->_ci_model_paths);
			$void = array_shift($this->_ci_helper_paths);
			$void = array_shift($this->_ci_view_paths);
			$void = array_shift($config->_config_paths);
		}
		else
		{
			$path = rtrim($path, '/').'/';
			//在library/model/helper中直接去除(unset)
			foreach (array('_ci_library_paths', '_ci_model_paths', '_ci_helper_paths') as $var)
			{
				if (($key = array_search($path, $this->{$var})) !== FALSE)
				{
					unset($this->{$var}[$key]);
				}
			}

			//去除view中的package
			if (isset($this->_ci_view_paths[$path.'views/']))
			{
				unset($this->_ci_view_paths[$path.'views/']);
			}

			//去除config中的package
			if (($key = array_search($path, $config->_config_paths)) !== FALSE)
			{
				unset($config->_config_paths[$key]);
			}
		}

		// make sure the application default paths are still in the array
		// 确保这个类初始化的时候默认的package是存在的(采用先merge后unique的方式)
		$this->_ci_library_paths = array_unique(array_merge($this->_ci_library_paths, array(APPPATH, BASEPATH)));
		$this->_ci_helper_paths = array_unique(array_merge($this->_ci_helper_paths, array(APPPATH, BASEPATH)));
		$this->_ci_model_paths = array_unique(array_merge($this->_ci_model_paths, array(APPPATH)));
		$this->_ci_view_paths = array_merge($this->_ci_view_paths, array(APPPATH.'views/' => TRUE));
		$config->_config_paths = array_unique(array_merge($config->_config_paths, array(APPPATH)));
	}

	// --------------------------------------------------------------------

	/**
	 * Loader
	 *
	 * This function is used to load views and files.
	 * Variables are prefixed with _ci_ to avoid symbol collision with
	 * variables made available to view files
	 *
	 * @param	array
	 * @return	void
	 */
	protected function _ci_load($_ci_data)
	{
		// Set the default data variables
		foreach (array('_ci_view', '_ci_vars', '_ci_path', '_ci_return') as $_ci_val)
		{
			$$_ci_val = ( ! isset($_ci_data[$_ci_val])) ? FALSE : $_ci_data[$_ci_val];
		}

		$file_exists = FALSE;

		// Set the path to the requested file
		if ($_ci_path != '')
		{
			$_ci_x = explode('/', $_ci_path);
			$_ci_file = end($_ci_x);
		}
		else
		{
			$_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
			$_ci_file = ($_ci_ext == '') ? $_ci_view.'.php' : $_ci_view;

			foreach ($this->_ci_view_paths as $view_file => $cascade)
			{
				if (file_exists($view_file.$_ci_file))
				{
					$_ci_path = $view_file.$_ci_file;
					$file_exists = TRUE;
					break;
				}

				if ( ! $cascade)
				{
					break;
				}
			}
		}

		if ( ! $file_exists && ! file_exists($_ci_path))
		{
			show_error('Unable to load the requested file: '.$_ci_file);
		}

		// This allows anything loaded using $this->load (views, files, etc.)
		// to become accessible from within the Controller and Model functions.

		$_ci_CI =& get_instance();
		foreach (get_object_vars($_ci_CI) as $_ci_key => $_ci_var)
		{
			if ( ! isset($this->$_ci_key))
			{
				$this->$_ci_key =& $_ci_CI->$_ci_key;
			}
		}

		/*
		 * Extract and cache variables
		 *
		 * You can either set variables using the dedicated $this->load_vars()
		 * function or via the second parameter of this function. We'll merge
		 * the two types and cache them so that views that are embedded within
		 * other views can have access to these variables.
		 */
		if (is_array($_ci_vars))
		{
			$this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $_ci_vars);
		}
		extract($this->_ci_cached_vars);

		/*
		 * Buffer the output
		 *
		 * We buffer the output for two reasons:
		 * 1. Speed. You get a significant speed boost.
		 * 2. So that the final rendered template can be
		 * post-processed by the output class.  Why do we
		 * need post processing?  For one thing, in order to
		 * show the elapsed page load time.  Unless we
		 * can intercept the content right before it's sent to
		 * the browser and then stop the timer it won't be accurate.
		 */
		ob_start();

		// If the PHP installation does not support short tags we'll
		// do a little string replacement, changing the short tags
		// to standard PHP echo statements.

		if ((bool) @ini_get('short_open_tag') === FALSE AND config_item('rewrite_short_tags') == TRUE)
		{
			echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
		}
		else
		{
			include($_ci_path); // include() vs include_once() allows for multiple views with the same name
		}

		log_message('debug', 'File loaded: '.$_ci_path);

		// Return the file data if requested
		if ($_ci_return === TRUE)
		{
			$buffer = ob_get_contents();
			@ob_end_clean();
			return $buffer;
		}

		/*
		 * Flush the buffer... or buff the flusher?
		 *
		 * In order to permit views to be nested within
		 * other views, we need to flush the content back out whenever
		 * we are beyond the first level of output buffering so that
		 * it can be seen and included properly by the first included
		 * template and any subsequent ones. Oy!
		 *
		 */
		if (ob_get_level() > $this->_ci_ob_level + 1)
		{
			ob_end_flush();
		}
		else
		{
			$_ci_CI->output->append_output(ob_get_contents());
			@ob_end_clean();
		}
	}

	// --------------------------------------------------------------------

	/**
     * 加载类
	 * Load class
	 *
	 * This function loads the requested class.
	 * 加载某个类
	 * 这个方法的主要流程就是--先看是否是一个子类，然后按照不是子类(当然每次都需要在BASEPATH和每个apppath中搜索)
	 * 他的主要作用还是判断是:
	 * 	1--否需要增加前缀
	 *  2--同时也是最为重要的一点儿就是已经加载了相应的类文件
	 *  3--将class简化为单纯的类名，在下游实例化方法中直接使用(+前缀)即可，真正的实例化在下游的_ci_init_class
	 * 不明白的地方是为什么只搜索libraries
	 * @param	string	the item that is being loaded 需要加载的类
	 * @param	mixed	any additional parameters	参数
	 * @param	string	an optional object name  类加载完是要赋值给超类CI的,通过这个参数可以指定CI成员变量的名字,不指定则使用类名
	 * @return	void
	 */
	protected function _ci_load_class($class, $params = NULL, $object_name = NULL)
	{
		// Get the class name, and while we're at it trim any slashes.
		// The directory path can be included as part of the class name,
		// but we don't want a leading slash
		//我们可以将类的名字指定为路径，也可以用.php的类名;
		//但是在加载类的时候会统一去掉后缀,并把前后的路径符号/去掉
		$class = str_replace('.php', '', trim($class, '/'));

		// Was the path included with the class name?
		// We look for a slash to determine this
		$subdir = '';
		//如果class变量中仍然存在/，则表明这是个子路径(因为已经将两端的/去掉了)
		if (($last_slash = strrpos($class, '/')) !== FALSE)
		{
			// Extract the path
			// 取出子路径
			$subdir = substr($class, 0, $last_slash + 1);

			// Get the filename from the path
			// 获取单纯的class名称
			$class = substr($class, $last_slash + 1);
		}

		// We'll test for both lowercase and capitalized versions of the file name
		// 这里会遍历全部小写的class和首字母大写的class
		// ucfirst--将字符串首字母大写
		foreach (array(ucfirst($class), strtolower($class)) as $class)
		{
			// 先假定class是application/libraries/subdir/config_item('subclass_prefix').$class.php
			// 即这是一个 继承了system中的某个library类的子类
			$subclass = APPPATH.'libraries/'.$subdir.config_item('subclass_prefix').$class.'.php';

			// Is this a class extension request?
			// 的确是library的子类
			if (file_exists($subclass))
			{
				// 获取system中library类的全路径
				$baseclass = BASEPATH.'libraries/'.ucfirst($class).'.php';

				// 既然是子类，父类必须存在; 如果不存在则显示error日志和内容
				if ( ! file_exists($baseclass))
				{
					log_message('error', "Unable to load the requested class: ".$class);
					show_error("Unable to load the requested class: ".$class);
				}

				// Safety:  Was the class already loaded by a previous call?
				// 如果子类的路径经被添加过, 如果是指定了一个新的object_name, 可以重新加载，其他清空不再重新加载
				if (in_array($subclass, $this->_ci_loaded_files))
				{
					// Before we deem this to be a duplicate request, let's see
					// if a custom object name is being supplied.  If so, we'll
					// return a new instance of the object
					// 如果指定了要赋值给超类CI的属性名称, 这种情况需要判断超类CI是否已经包含了这个属性；如果没有包含，则重新加载并赋值给这个属性
                    // 猜测
					if ( ! is_null($object_name))
					{
						$CI =& get_instance();
						if ( ! isset($CI->$object_name))
						{
							//实例化类
							return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
						}
					}

					// 如果没有指定属性名称，则表明已经加载过这个子类；直接返回即可
					$is_duplicate = TRUE;
					log_message('debug', $class." class already loaded. Second attempt ignored.");
					return;
				}

				// 如果还没有加载这个子类，则把子类和父类文件都包含进来
				include_once($baseclass);
				include_once($subclass);
				// 将子类文件路径添加到已经加载的类路径数组
				$this->_ci_loaded_files[] = $subclass;

				//实例化类
				return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
			} //如果是library子类的情况结束

			// Lets search for the requested library file and load it.
			// 下面的情况是这个类不是继承了system中library的子类;而是自己定义的一个全新的类
			$is_duplicate = FALSE;
			// 分别在BASEPATH和APPATH还有各个新的application中的library中查找这个类文件
			foreach ($this->_ci_library_paths as $path)
			{
				$filepath = $path.'libraries/'.$subdir.$class.'.php';

				// Does the file exist?  No?  Bummer...
				// 类文件不存在这个文件夹中则直接跳过此次循环
				if ( ! file_exists($filepath))
				{
					continue;
				}

				// 下面的情况时在这个文件夹中找到了这个类文件
				// Safety:  Was the class already loaded by a previous call?
				// 如果已经加载过这个类文件
				if (in_array($filepath, $this->_ci_loaded_files))
				{
					// Before we deem this to be a duplicate request, let's see
					// if a custom object name is being supplied.  If so, we'll
					// return a new instance of the object
					// 虽然已经加载过这个路径下的类文件，但是如果超类CI并没有这个属性，则仍然要将这个类实例化并赋值给这个属性
					if ( ! is_null($object_name))
					{
						$CI =& get_instance();
						if ( ! isset($CI->$object_name))
						{
							return $this->_ci_init_class($class, '', $params, $object_name);
						}
					}

                    // 重复加载
					$is_duplicate = TRUE;
					log_message('debug', $class." class already loaded. Second attempt ignored.");
					return;
				}

				// 如果没有加载过这个类文件，则将其包含进来, 并加入到已经加载的类文件(包含路径)数组
				include_once($filepath);
				$this->_ci_loaded_files[] = $filepath;
				// 实例化这个类(不带前缀)(因为这不是子类)
				return $this->_ci_init_class($class, '', $params, $object_name);
			}

		} // END FOREACH

		// One last attempt.  Maybe the library is in a subdirectory, but it wasn't specified?
		// 这里是最后的尝试。如果前面没有加载成功,并且subdir是空，有可能是因为开发者的路径是这样的APPPATH/library/class/class
		// 所以这里做了这样的修正，并且递归调用，（当然不用担心会递归死循环,因为下面会判断如果subdir不为空，且没找到则报错了，第一次递归就已经是subdir!=''了，所以不会死循环）
		if ($subdir == '')
		{
			$path = strtolower($class).'/'.$class;
			return $this->_ci_load_class($path, $params);
		}

		// If we got this far we were unable to find the requested class.
		// We do not issue errors if the load call failed due to a duplicate request
		// 如果仍然没有找到需要加载的类，那么基本就是开发者调用错误了,但是这里的is_duplicate不是很明白
		if ($is_duplicate == FALSE)
		{
			log_message('error', "Unable to load the requested class: ".$class);
			show_error("Unable to load the requested class: ".$class);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Instantiates a class
	 *
	 * 实例化一个类
	 * 总体来说这个方法的主要过程就是确定两个变量$name(需要加载的类的名称);$classvar(需要记录的已经加载的类的数组)
	 * 这个方法不需要关心类文件，因为在上游已经将需要加载的类文件包含进来了
	 * 这个方法不需要关心类名的带路径，因为上游方法已经将类名充分简化至只有类名(前缀不带)
	 * @param	string	需要实例化的类名(可能带有.php后缀)
	 * @param	string
	 * @param	bool
	 * @param	string	an optional object name 
	 * @return	null
	 */
	protected function _ci_init_class($class, $prefix = '', $config = FALSE, $object_name = NULL)
	{
		// Is there an associated config file for this class?  Note: these should always be lowercase
		// 如果指定config为null，需要到各个application/config文件夹下查找是否有相应的配置文件(配置文件小写或者首字母大写都会被检查)
		// 找到配置文件则跳出
		if ($config === NULL)
		{
			// Fetch the config paths containing any package paths
			// 获取超类CI的成员变量config
			$config_component = $this->_ci_get_component('config');

			//_config_paths返回的是各个application的路径数组
			if (is_array($config_component->_config_paths))
			{
				// Break on the first found file, thus package files
				// are not overridden by default paths
				foreach ($config_component->_config_paths as $path)
				{
					// We test for both uppercase and lowercase, for servers that
					// are case-sensitive with regard to file names. Check for environment
					// first, global next
					// 查找是否有相应的配置文件
					if (defined('ENVIRONMENT') AND file_exists($path .'config/'.ENVIRONMENT.'/'.strtolower($class).'.php'))
					{
						include($path .'config/'.ENVIRONMENT.'/'.strtolower($class).'.php');
						break;
					}
					elseif (defined('ENVIRONMENT') AND file_exists($path .'config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php'))
					{
						include($path .'config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php');
						break;
					}
					elseif (file_exists($path .'config/'.strtolower($class).'.php'))
					{
						include($path .'config/'.strtolower($class).'.php');
						break;
					}
					elseif (file_exists($path .'config/'.ucfirst(strtolower($class)).'.php'))
					{
						include($path .'config/'.ucfirst(strtolower($class)).'.php');
						break;
					}
				}
			}
		}// 指定config为null结束

		//判定是否需要增加类名前缀
		if ($prefix == '')
		{
			if (class_exists('CI_'.$class))
			{
				$name = 'CI_'.$class;
			}
			elseif (class_exists(config_item('subclass_prefix').$class))
			{
				$name = config_item('subclass_prefix').$class;
			}
			else
			{
				$name = $class;
			}
		}
		else
		{
			$name = $prefix.$class;
		}

		// Is the class name valid?
		// 需要加载的类必须存在
		if ( ! class_exists($name))
		{
			log_message('error', "Non-existent class: ".$name);
			show_error("Non-existent class: ".$class);
		}

		// Set the variable name we will assign the class to
		// Was a custom class name supplied?  If so we'll use it
		$class = strtolower($class);

		//是否指定了属性名字
		if (is_null($object_name))
		{
			// 没指定属性名字，则取_ci_varmap中查找是否已经使用过$class, 如果没有使用则使用$class, 否则使用已经加载的
			$classvar = ( ! isset($this->_ci_varmap[$class])) ? $class : $this->_ci_varmap[$class];
		}
		//指定了属性名字，则直接使用
		else
		{
			$classvar = $object_name;
		}

		// Save the class name and object name
		// 将即将加载的类(小写), 加入到_ci_classes
		$this->_ci_classes[$class] = $classvar;

		// Instantiate the class
		// 实例化类，并将属性赋值给CI超类
		$CI =& get_instance();
		if ($config !== NULL)
		{
			$CI->$classvar = new $name($config);
		}
		else
		{
			$CI->$classvar = new $name;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Autoloader
	 *
	 * The config/autoload.php file contains an array that permits sub-systems,
	 * libraries, and helpers to be loaded automatically.
	 * 
	 * 这个方法是被CI_Controller调用的，自动加载配置文件中指定的类
	 * 下游方法:
	 * 	add_package_path
	 * 	helper
	 * 	language
	 * 	library
	 * 	model
	 * 	database
	 * 
	 *
	 * @param	array
	 * @return	void
	 */
	private function _ci_autoloader()
	{
		
// 		$autoload['packages'] = array();
// 		$autoload['libraries'] = array();
// 		$autoload['helper'] = array();
// 		$autoload['config'] = array();
// 		$autoload['language'] = array();
// 		$autoload['model'] = array();

		
		//包含autoload.php文件
		if (defined('ENVIRONMENT') AND file_exists(APPPATH.'config/'.ENVIRONMENT.'/autoload.php'))
		{
			include(APPPATH.'config/'.ENVIRONMENT.'/autoload.php');
		}
		else
		{
			include(APPPATH.'config/autoload.php');
		}

		//如果没有把配置文件包含进来；或者配置文件中没有包含$autoload数组，报错
		if ( ! isset($autoload))
		{
			return FALSE;
		}

		// Autoload packages
		// 第三方路径
		if (isset($autoload['packages']))
		{
			foreach ($autoload['packages'] as $package_path)
			{
				$this->add_package_path($package_path);
			}
		}

		// Load any custom config file
		// 加载配置文件
		if (count($autoload['config']) > 0)
		{
			// 通过获取超对象CI，并将需要加载的配置文件的名字传递给config变量,通过调用core/Config.php->load加载相应配置文件
			$CI =& get_instance();
			foreach ($autoload['config'] as $key => $val)
			{
				$CI->config->load($val);
			}
		}

		// Autoload helpers and languages
		// 加载helper和languages
		foreach (array('helper', 'language') as $type)
		{
			if (isset($autoload[$type]) AND count($autoload[$type]) > 0)
			{
				$this->$type($autoload[$type]);
			}
		}

		// A little tweak to remain backward compatible
		// The $autoload['core'] item was deprecated
		// 完全为了向前兼容core数组
		if ( ! isset($autoload['libraries']) AND isset($autoload['core']))
		{
			$autoload['libraries'] = $autoload['core'];
		}

		// Load libraries
		// 加载library
		if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)
		{
			// Load the database driver.
			// 加载数据库驱动
			if (in_array('database', $autoload['libraries']))
			{
				$this->database();
				//加载完毕后,去除database类库
				$autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
			}

			// Load all other libraries
			//加载其他的类
			foreach ($autoload['libraries'] as $item)
			{
				$this->library($item);
			}
		}

		// Autoload models
		// 加载models
		if (isset($autoload['model']))
		{
			$this->model($autoload['model']);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 * 将对象转化为数组
	 * @param	object
	 * @return	array
	 */
	protected function _ci_object_to_array($object)
	{
		return (is_object($object)) ? get_object_vars($object) : $object;
	}

	// --------------------------------------------------------------------

	/**
	 * Get a reference to a specific library or model
	 * 返回超类CI的某个成员变量(library或者model)
	 * @param 	string 成员变量的名称
	 * @return	bool
	 */
	protected function &_ci_get_component($component)
	{
		//获取超类实例
		$CI =& get_instance();
		return $CI->$component;
	}

	// --------------------------------------------------------------------

	/**
	 * Prep filename
	 *
	 * This function preps the name of various items to make loading them more reliable.
	 * 将一组文件名的后缀和.php后缀去掉，并加上原来的后缀，最后返回小写字符串
	 * 没明白这个方法想做什么
	 * @param	mixed
	 * @param 	string
	 * @return	array
	 */
	protected function _ci_prep_filename($filename, $extension)
	{
		if ( ! is_array($filename))
		{
			return array(strtolower(str_replace('.php', '', str_replace($extension, '', $filename)).$extension));
		}
		else
		{
			foreach ($filename as $key => $val)
			{
				$filename[$key] = strtolower(str_replace('.php', '', str_replace($extension, '', $val)).$extension);
			}

			return $filename;
		}
	}
}

/* End of file Loader.php */
/* Location: ./system/core/Loader.php */