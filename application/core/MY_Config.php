<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------


/**
 * 自己的配置类
 * Class MY_Config
 */

class MY_Config extends CI_Config {

    public function __construct()
    {
        parent::__construct();
    }


    function set_item($item, $value, $index = '')
    {
        if($index == '')
        {
            $this->config[$item] = $value;
        }
        else{
            $this->config[$index][$item] = $value;
        }
    }

    // --------------------------------------------------------------------

    function _assign_to_config($items = array())
    {
        if (is_array($items))
        {
            foreach ($items as $val)
            {
                $this->set_item($val['item'], $val['value'], $val['index']);
            }
        }
    }




    function my_item($file, $item)
    {
        $file = ($file == '') ? 'config' : str_replace('.php', '', $file);

        if(!$this->item($item, $file))
        {
            $this->load($file, true);
        }
        return $this->item($item, $file);
    }

}
