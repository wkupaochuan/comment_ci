<?php

require 'curl.php';


class PostMan extends curlTool{

    private $url;

    /******************************** public methods *****************************************************/

    /**
     * 执行请求
     */
    public function run()
    {
        parent::makeRequest($method, $this->url, array());
    }


    public function setUrl($url)
    {
        $this->url = $url;
    }


    /******************************** private methods *****************************************************/

}

$exexutor = new PostMan();
$exexutor->run();