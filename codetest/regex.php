<?php
$pattern = "/^[0-9A-Za-z\(\)\（\）\,\，\\x{4e00}-\\x{9fa5}]{4,60}$/u";
$str =  '中国人';

preg_match($pattern, $str, $match);
print_r($match);exit();


?>
