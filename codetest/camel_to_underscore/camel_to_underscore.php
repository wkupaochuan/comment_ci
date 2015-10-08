<?php

$array = array(3,4,5,333,44);
chuan_quick_sort($array);
print_r($array);
exit;


$a = array(1,2,3);
xdebug_debug_zval('a');

exit;

$str = 'lastUpdatedTime';
echo camel_to_underscore($str);exit;
$test_time = 1000000;

$php_test_time_start = microtime(true);
php_test($test_time, $str);
$php_test_time_stop = microtime(true);
echo "php test ext time is ". ($php_test_time_stop - $php_test_time_start). "\n";

$c_test_time_start = microtime(true);
c_test($test_time, $str);
$c_test_time_stop = microtime(true);
echo "c test time is ". ($c_test_time_stop - $c_test_time_start). "\n";



function php_test($test_time, $test_data){
    for($i=0; $i<$test_time; $i++){
        strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $test_data));
    }
}

function c_test($test_time, $test_data){
    for($i=0; $i<$test_time; $i++){
        camel_to_underscore($test_data);
    }
}


exit;