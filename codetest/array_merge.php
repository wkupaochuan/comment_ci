<?php

$a = array(1,2,3);
$b = null;

$c = array_merge($a, $b);
$d = array_merge($b, $a);

echo 'c';
var_dump($c);

echo 'd';
var_dump($d);

?>
