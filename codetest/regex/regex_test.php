<?php

$a = 1; echo ~$a;
exit;

function xss_hash()
{
    mt_srand();
    $_xss_hash = md5(time() + mt_rand(0, 1999999999));

    return $_xss_hash;
}

$str = "https://www.baidu.com?a=a&b=b&c=c";
$str = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-]+)|i', xss_hash()."\\1=\\2", $str);
echo $str.PHP_EOL;
exit;