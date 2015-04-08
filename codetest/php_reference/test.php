<?php
class a {
    public $name;
}

$a = new a();
$a->name = 'a';

$b = $a;
$b->name = 'b';

print_r($a);

$b = array(1,2);
$a =$b;
$b[]= 3;
print_r($a);
print_r($b);
