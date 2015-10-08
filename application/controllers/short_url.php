<?php


/***
 * 要点：长变短--保证唯一性
 * Class short_url
 */
class short_url extends  CI_Controller{

    public function __construct()
    {
        parent::__construct();
    }

    function base62($x)
    {
        $show = '';
        while ($x > 0) {
            $s = $x % 62;
            if ($s > 35) {
                $s = chr($s + 61);
            } elseif ($s > 9 && $s <= 35) {
                $s = chr($s + 55);
            }
            $show .= $s;
            $x = floor($x / 62);
        }
        return $show;
    }

    function urlShort($url)
    {
        $url = crc32($url);
        echo $url.PHP_EOL;
        $result = sprintf("%u", $url);echo $result.PHP_EOL;
        return $this->base62($result);
    }

    public function test()
    {
//        echo $this->urlShort("http://hi.baidu.com/cubeking/");
        print_r($this->shorturl("http://hi.baidu.com/cubeking/"));
    }


    /**
     * 标准短链接生成方法
     * @param $input
     * @return array
     */
    function shorturl($input) {
        // 32二进制
        $base32 = array (
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
            'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
            'y', 'z', '0', '1', '2', '3', '4', '5'
        );

        $hex = md5($input); // 输出32位16进制字符串
        $hexLen = strlen($hex);
        $subHexLen = $hexLen / 8;
        $output = array();

        // 每次取8位字符 32位2进制
        for ($i = 0; $i < $subHexLen; $i++) {
            $subHex = substr ($hex, $i * 8, 8);
            // 去除高于30位的, 输出30位的二进制
            $int = 0x3FFFFFFF & (1 * ('0x'.$subHex));
            $out = '';

            // 分为6段，每次取5位(0x1f相与)
            for ($j = 0; $j < 6; $j++) {
                // 获取5位的二进制
                $val = 0x0000001F & $int;

                // 从码表中去除对应字符，并拼接
                $out .= $base32[$val];

                // 右移5位
                $int = $int >> 5;
            }

            $output[] = $out;
        }

        return $output;
    }


    /**
     * 纯随机算法(不好保证唯一性)
     * @param $length
     * @param string $pool
     * @return string
     */
    function random($length, $pool = '')
    {
        $random = '';

        if (empty($pool)) {
            $pool    = 'abcdefghkmnpqrstuvwxyz';
            $pool   .= '23456789';
        }

        srand ((double)microtime()*1000000);

        for($i = 0; $i < $length; $i++)
        {
            $random .= substr($pool,(rand()%(strlen ($pool))), 1);
        }

        return $random;
    }





} 
