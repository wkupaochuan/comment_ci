<?php

class curlTool{

    const POST = 'POST';
    const GET = 'GET';

    /******************************** public methods *****************************************************/


    protected function makeRequest($method, $url, $params)
    {
        $method = $this->checkRequestMethod($method);
        if($method = self::POST)
        {
            $requestResult = $this->post($url, $params);
        }
        else{
            $requestResult = $this->get($url, $params);
        }

        if(!empty($requestResult['err']))
        {
            echo '请求出错', PHP_EOL;
            var_dump($requestResult['err']);
            exit;
        }
    }



    /******************************** private methods *****************************************************/


    /**
     * 打印url
     * @param $url
     * @param $params
     */
    private function _echoUrl($url, $params)
    {
        $queryString = $this->_getQueryString($params);
        echo $url . '?' . $queryString;
    }

    /**
     * 获取qeurystring
     * @param $params
     * @return string
     */
    private function _getQueryString($params)
    {
        $queryString = '';
        foreach($params as $key => $v)
        {
            $queryString .= $key . '=' . $v;
        }

        return $queryString;
    }


    /**
     * 校验请求方式
     * @param $method
     * @return string
     */
    private function checkRequestMethod($method)
    {
        if(empty($method))
        {
            exit('请求方式不能为空');
        }
        $method = strtoupper($method);
        if(!in_array($method, array(self::POST, self::GET)))
        {
            exit('请求方式不存在:' . $method);
        }
        return $method;
    }


    /**
     * 发送post请求
     * @param $url
     * @param $params
     * @return mixed
     */
    private function post($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $ret = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        return array(
            'ret' => $ret
            , 'err' => $err
        );
    }

    /**
     * 发送get请求
     * @param $url
     * @param $params
     * @return array
     */
    private function get($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $ret = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        return array(
            'ret' => $ret
            , 'err' => $err
        );
    }

}