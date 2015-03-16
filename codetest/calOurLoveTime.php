<?php

// 计算我们一起的时间
$startTime = strtotime("2013-11-27");

$now = strtotime(date('Y-m-d'));

$dayCount =  ($now-$startTime)/(24*60*60);

print_r('你们相爱的时间已经达到:'.$dayCount."天了.继续加油啊!!!!\n");



//echo strtotime(date('Y-m-d',strtotime('-1 month')));

?>
