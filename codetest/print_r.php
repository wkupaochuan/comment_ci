<?php

$a = array(1,2,3,4);
function test()
{
global $a;
var_dump($a);
}

test();exit;

echo fgets(STDIN);exit;

$a = "hhhahhh";
var_dump(strpos($a, 'ah'));exit;


echo CASE_UPPER;exit;
print_r($GLOBALS);exit;
$time = date('Y-m-d H:i:s', strtotime('-1 week last monday'));

echo $time;
?>
