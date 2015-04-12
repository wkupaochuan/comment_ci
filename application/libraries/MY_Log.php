<?php

class MY_Log extends CI_Log{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 如果强制写入log， 则先把threshold调高，写入Log后恢复
     * @param string $level
     * @param $msg
     * @param bool $force
     */
    public function my_write_log($level = 'error', $msg, $force = false)
    {
        if($force)
        {
            $original_threshold = $this->_threshold;
            $this->_threshold = 4;
            $this->write_log($level, $msg);
            $this->_threshold = $original_threshold;
        }
        else{
            $this->write_log($level, $msg);
        }

    }

} 