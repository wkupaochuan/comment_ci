<?php

if(true)
{
    require_once 'file_for_require.php';
}

echo $a.PHP_EOL;
$a = 2;
require_once 'file_for_require.php';
echo $a.PHP_EOL;


if(true)
{
    include 'file_for_include.php';
}

echo $b.PHP_EOL;
$b = 2;
include 'file_for_include.php';
echo $b.PHP_EOL;

