<?php
$a = 1;
static $c = 3;


function test()
{
    static $b = 12;
    global $a;
    echo $a.PHP_EOL;
    global $c;
    echo $c.PHP_EOL;
}

test();
echo $b.PHP_EOL;