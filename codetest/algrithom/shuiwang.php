<?php

$array = array(3,4,4,5,5,6,6,6,6,6,6,6,6,6,5,4,5,5);


for($i = 0, $buf1 = $buf2 = $buf3 =  0, $candidate1 = $candidate2 = $candidate3 = null, $len = count($array); $i < $len; ++$i)
{
    if($buf1 === 0)
    {
        $candidate1 = $array[$i];
        $buf1++;
    }
    elseif($buf2 === 0)
    {
        $candidate2 = $array[$i];
        $buf2++;
    }
    elseif($buf3 === 0)
    {
        $candidate3 = $array[$i];
        $buf3++;
    }
}

echo '结果:', $candidate;


