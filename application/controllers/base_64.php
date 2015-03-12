<?php

/**
 * 实现base_64
 * Class base_64
 */
class base_64
{

    public function index()
    {
        $str = "abcd/dd";
        $x =  $this->_my_base64_encode($str);
        $y =  base64_encode($str);
        echo $this->_my_base64_decode($y);
        echo "<br>";
        if($x == $y)
        {
            echo 'yes';
        }
        else{
            echo 'no';
        }
    }


    /**
     * base64解码
     * @param $str
     */
    private function _my_base64_decode($str)
    {
        $base64_config = array(0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E', 5 => 'F', 6 => 'G', 7 => 'H',
            8 => 'I', 9 => 'J', 10 => 'K', 11 => 'L', 12 => 'M', 13 => 'N', 14 => 'O',
            15 => 'P', 16 => 'Q', 17 => 'R', 18 => 'S', 19 => 'T', 20 => 'U', 21 => 'V',
            22 => 'W', 23 => 'X', 24 => 'Y', 25 => 'Z', 26 => 'a', 27 => 'b', 28 => 'c',
            29 => 'd', 30 => 'e', 31 => 'f', 32 => 'g', 33 => 'h', 34 => 'i', 35 => 'j',
            36 => 'k', 37 => 'l', 38 => 'm', 39 => 'n', 40 => 'o', 41 => 'p', 42 => 'q',
            43 => 'r', 44 => 's', 45 => 't', 46 => 'u', 47 => 'v', 48 => 'w', 49 => 'x',
            50 => 'y', 51 => 'z', 52 => 0, 53 => 1, 54 => 2, 55 => 3, 56 => 4, 57 => 5, 58 => 6,
            59 => 7, 60 => 8, 61 => 9, 62 => '+', 63 => '/'
        );
        $base64_config = array_flip($base64_config);
        $str = trim($str, '=');
        $array_str = str_split($str);

        $array_x = array();
        foreach($array_str as $row)
        {
            $bin = decbin($base64_config[$row]);
            // 获取对应编号，取得二进制字符串，去除高位两个0
            $array_x[] = str_pad($bin, 6, '0', STR_PAD_LEFT);
        }

        $str_8 = implode('', $array_x);

        $mode = strlen($str_8)%8;
        $str_8 = substr($str_8, 0, strlen($str_8) - $mode);

        $res = '';
        for($i = 0; ; ++$i)
        {
            $bin = substr($str_8, $i * 8, 8);
            if(empty($bin))
            {
                break;
            }
            $dec = bindec($bin);
            $res .= chr($dec);
        }

        return $res;
    }


    private function _my_base64_encode($str)
    {
        // 字符串转化为数组, 不足3位的用\0补齐(ascii码为0)
        $array_str = str_split($str);
        $mode = count($array_str)%3;

        $arr = '';
        for($i = 0; ; ++$i)
        {
            $tmp = array_slice($array_str, $i * 3, 3);
            if(empty($tmp)) break;
            $arr .= $this->_3to4($tmp);
        }

        if($mode == 1) $arr.= '==';
        else if($mode == 2) $arr .= '=';

        return $arr;
    }



    private function _3to4($a_str3)
    {
        //64位编码表
        $base64_config = array(0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E', 5 => 'F', 6 => 'G', 7 => 'H',
            8 => 'I', 9 => 'J', 10 => 'K', 11 => 'L', 12 => 'M', 13 => 'N', 14 => 'O',
            15 => 'P', 16 => 'Q', 17 => 'R', 18 => 'S', 19 => 'T', 20 => 'U', 21 => 'V',
            22 => 'W', 23 => 'X', 24 => 'Y', 25 => 'Z', 26 => 'a', 27 => 'b', 28 => 'c',
            29 => 'd', 30 => 'e', 31 => 'f', 32 => 'g', 33 => 'h', 34 => 'i', 35 => 'j',
            36 => 'k', 37 => 'l', 38 => 'm', 39 => 'n', 40 => 'o', 41 => 'p', 42 => 'q',
            43 => 'r', 44 => 's', 45 => 't', 46 => 'u', 47 => 'v', 48 => 'w', 49 => 'x',
            50 => 'y', 51 => 'z', 52 => 0, 53 => 1, 54 => 2, 55 => 3, 56 => 4, 57 => 5, 58 => 6,
            59 => 7, 60 => 8, 61 => 9, 62 => '+', 63 => '/'
        );
        $bit = '';
        //求得字符的2进制表示
        foreach($a_str3 as $v)
        {
            // 获取ascii码，转化为二进制，填充字符串长度为8，左填充
            $bit .= str_pad(decbin(ord($v)),8,'0',STR_PAD_LEFT);
        }

        $len = strlen($bit);
        $len = ceil($len/6);
        $per = 6;
        $arr = '';
        for($i = 0; $i < $len; $i++) //3个八进制转成4个六进制
        {
            if(($i + 1)*$per > strlen($bit))
            {

                $tmp6 = substr($bit,$i*$per);
            }
            else{
                $tmp6 = substr($bit,$i*$per,$per);
            }
            $tmp6 = str_pad($tmp6,6,0,STR_PAD_RIGHT);
            $tmp8 = str_pad($tmp6,8,0,STR_PAD_LEFT);
            $arr .= $base64_config[bindec($tmp8)];
        }

        return $arr;
    }


}