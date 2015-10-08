<?php

$a = 'abcdef';
echo substr($a, 0, -2);
exit;

$str = 'oooooddd//';
//$preg = '/(o+?)(.+?)/';
$preg = '#(.+?)/*$#';

preg_match($preg, $str, $match);
print_r($match);

exit();


function my_base64($str)
{
    if(empty($str))
    {
        return $str;
    }
    $base64_map = array(0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E', 5 => 'F', 6 => 'G', 7 => 'H',
        8 => 'I', 9 => 'J', 10 => 'K', 11 => 'L', 12 => 'M', 13 => 'N', 14 => 'O',
        15 => 'P', 16 => 'Q', 17 => 'R', 18 => 'S', 19 => 'T', 20 => 'U', 21 => 'V',
        22 => 'W', 23 => 'X', 24 => 'Y', 25 => 'Z', 26 => 'a', 27 => 'b', 28 => 'c',
        29 => 'd', 30 => 'e', 31 => 'f', 32 => 'g', 33 => 'h', 34 => 'i', 35 => 'j',
        36 => 'k', 37 => 'l', 38 => 'm', 39 => 'n', 40 => 'o', 41 => 'p', 42 => 'q',
        43 => 'r', 44 => 's', 45 => 't', 46 => 'u', 47 => 'v', 48 => 'w', 49 => 'x',
        50 => 'y', 51 => 'z', 52 => 0, 53 => 1, 54 => 2, 55 => 3, 56 => 4, 57 => 5, 58 => 6,
        59 => 7, 60 => 8, 61 => 9, 62 => '+', 63 => '/'
    );

    $array_str = str_split($str);

    // 补齐3的倍数
    $mode = 3 - intval(count($array_str)%3);

    foreach($array_str as $k => $v)
    {
        $array_str[$k] = str_pad(decbin(ord($v)), 8, '0', STR_PAD_LEFT);
    }

    $str = implode('', $array_str);

    $res = '';

    for($j = 0; ; ++$j)
    {
        $str_bin_6 = substr($str, $j * 6, 6);
        if(empty($str_bin_6))
        {
            break;
        }
        $str_bin_6 = str_pad($str_bin_6, 6, '0', STR_PAD_RIGHT);

        $res .= $base64_map[bindec($str_bin_6)];
    }

    if($mode === 1)
    {
        $res .= '=';
    }
    else if($mode === 2)
    {
        $res .= '==';
    }

    return $res;
}

function _my_base64_decode($str)
{
    if(empty($str))
    {
        return $str;
    }
    $base64_map = array(0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E', 5 => 'F', 6 => 'G', 7 => 'H',
        8 => 'I', 9 => 'J', 10 => 'K', 11 => 'L', 12 => 'M', 13 => 'N', 14 => 'O',
        15 => 'P', 16 => 'Q', 17 => 'R', 18 => 'S', 19 => 'T', 20 => 'U', 21 => 'V',
        22 => 'W', 23 => 'X', 24 => 'Y', 25 => 'Z', 26 => 'a', 27 => 'b', 28 => 'c',
        29 => 'd', 30 => 'e', 31 => 'f', 32 => 'g', 33 => 'h', 34 => 'i', 35 => 'j',
        36 => 'k', 37 => 'l', 38 => 'm', 39 => 'n', 40 => 'o', 41 => 'p', 42 => 'q',
        43 => 'r', 44 => 's', 45 => 't', 46 => 'u', 47 => 'v', 48 => 'w', 49 => 'x',
        50 => 'y', 51 => 'z', 52 => 0, 53 => 1, 54 => 2, 55 => 3, 56 => 4, 57 => 5, 58 => 6,
        59 => 7, 60 => 8, 61 => 9, 62 => '+', 63 => '/'
    );
    $base64_map = array_flip($base64_map);

    // 去除补全的等号
    $str = trim($str, '=');

    $array_str = str_split($str);
    foreach($array_str as $k => $v)
    {
        $array_str[$k] = str_pad(decbin($base64_map[$v]), 6, '0', STR_PAD_LEFT);
    }

    $str = implode('', $array_str);
    $mode = strlen($str)%8;
    $str = substr($str, 0, strlen($str) - $mode);

    $res = '';
    for($i = 0; ; ++$i)
    {
        $tmp_str = substr($str, $i * 8, 8);
        if(empty($tmp_str))
        {
            break;
        }
        $tmp_str = str_pad($tmp_str, 8, '0', STR_PAD_LEFT);
        $res .= chr(bindec($tmp_str));
    }

    return $res;
}


$str = 'kdkdk';
echo my_base64($str).PHP_EOL;
echo base64_encode($str).PHP_EOL;
echo _my_base64_decode(my_base64($str)).PHP_EOL;
