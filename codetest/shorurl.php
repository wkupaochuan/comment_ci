<?php
/**
 * 短连接算法
 */

function get_62_dic()
{
    $dic = array();
    for($i = 0; $i < 10; ++$i)
    {
        $dic[] = $i;
    }

    for($i = ord('a'); $i <= ord('z'); ++$i)
    {
        $dic[] = chr(($i));
    }

    for($i = ord('A'); $i <= ord('Z'); ++$i)
    {
        $dic[] = chr(($i));
    }

    return $dic;
}


/**
 * 十进制转62进制
 * @param $int_num
 * @return null|string
 */
function dec62($int_num)
{
    if(!is_int($int_num) || $int_num < 0)
    {
        return null;
    }
    $dic = get_62_dic();
    $res = '';
    while(true)
    {
        $mode = intval($int_num%62);
        $res = $dic[$mode] . $res;
        $int_num = intval(floor($int_num/62));
        if($int_num === 0)
        {
            break;
        }
    }

    return $res;
}

function my_xdec($str, $x)
{
    $dic = get_62_dic();
    $dic = array_flip($dic);

    $sum = 0;
    $array_str = str_split($str);
    for($i = count($array_str) - 1, $j = 0; $i >= 0; --$i, ++$j)
    {
        $sum += $dic[$array_str[$i]] * pow($x, $j);
    }

    return $sum;
}

function my_decx($int_num, $base)
{
    if(!is_int($int_num) || $int_num < 0)
    {
        return null;
    }
    $dic = get_62_dic();
    $res = '';
    while(true)
    {
        $mode = intval($int_num%$base);
        $res = $dic[$mode] . $res;
        $int_num = intval(floor($int_num/$base));
        if($int_num === 0)
        {
            break;
        }
    }

    return $res;
}

function my_base_convert($str, $from_base, $to_base)
{
    $dec = my_xdec($str, $from_base);
    $res = my_decx($dec, $to_base);
    return $res;
}



function my_hash($str)
{
    if(empty($str))
    {
        return null;
    }
    $str = md5($str);echo $str.PHP_EOL;
    $dic = get_62_dic();
    $res = array();

    for($i = 0; $i < 4; ++$i)
    {
        $tmp_str = substr($str, $i * 8, 8);
        $tmp_str = 0x3FFFFFFF & (1 * ('0x'.$tmp_str));// 30个1, 取低位30位

        $out = '';
        for($j = 0; $j < 6; ++$j)
        {
            $index = 0x0000003D & $tmp_str;
            $out .= $dic[$index];
            $tmp_str = $tmp_str>>5;
        }
        $res[] = $out;
    }

    print_r($res);
}




$str = 'http://www.chinabaike.com/z/jd/2012/0428/1105973.html';
my_hash($str);

//$str = 1;
//echo my_decx($str, 3).PHP_EOL;

//echo my_xdec(my_decx($str, 3), 3).PHP_EOL;

//echo my_base_convert($str, 4, 10);