<?php
$conf = $argv[1];
$confArray = explode('|', $conf);
$sqlCmd = '';
$i = 0;
for($i = 0; $i < 4; ++$i)
{
    $kV = explode('=', $confArray[$i]);
    switch($i)
    {
        case 0:
            $sqlCmd .= ('mysql -h' . $kV[1]);
            break;
        case 1:
            $sqlCmd .= (' -P' . $kV[1]);
            break;
        case 2:
            $sqlCmd .= (' -u' . $kV[1]);
            break;
        case 3:
            $sqlCmd .= (' -p' . $kV[1]);
            break;
        default:
            break;
    }
}

echo $sqlCmd;
exit;