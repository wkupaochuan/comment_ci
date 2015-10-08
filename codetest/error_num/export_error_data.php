<?php


$allErrorData = array();


$fileDir = '/Users/jixiaofeng/Downloads/error_company/';

$allErrorFiles = array();
for($i = 25; $i <= 30; ++$i)
{
    $allErrorFiles[] = $fileDir . $i . '.log';
}


foreach($allErrorFiles as $errorFile)
{
    $handle = fopen($errorFile, 'r');
    while($line = fgets($handle))
    {
        $line = substr($line, 12);
        $lineData = json_decode($line);
        $allErrorData[$lineData->name] = array(
            'name' => $lineData->name
            , 'username' => $lineData->username
            , 'password' => $lineData->password
            , 'repassword' => $lineData->repassword
            , 'contact_name' => $lineData->contact_name
            , 'contact_cell_phone' => $lineData->contact_cell_phone
            , 'website' => $lineData->website
            , 'receive' => 'on'
        );
    }
    fclose($handle);
}

print_r($allErrorData);exit;

foreach($allErrorData as $v)
{
    echo "'".$v['name']."',";
}

//header("Content-type:application/vnd.ms-excel");
//header("content-Disposition:filename=downloaded.pdf ");
//$fp = fopen('/Users/jixiaofeng/Downloads/注册失败公司.csv', 'w');
//
//foreach($allErrorData as $errorData)
//{
//    foreach($errorData as $k=>$v)
//    {
//        $errorData[$k] = iconv(mb_detect_encoding($v), 'gb2312', $v);
//    }
//    if(!empty($errorData['name']))
//    {
//        fputcsv($fp, array_values($errorData));
//    }
//}
//fclose($fp);

echo '完成';
exit;





