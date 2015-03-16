<?php


$postFields = array(
'mobile' => '15210875749',
'uPass' => '000000',
'versionInfo' => " ",
'deviceInfo' => " "
);	

$url = "http://182.92.130.192:8080/wechat/login.do";
        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
$result = curl_exec($ch);
curl_close($ch);

print_r($result);

?>
