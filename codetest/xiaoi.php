<?php








        $msg= $_SERVER['argv'][1];//这里是传递到小i机器人的语句
        $app_key="NVKEaT3VtVUD";//这里填入你的小i机器人key
        $app_secret="SAKrSyJrV4YkE3QBGhvc";//这里填入你的小i机器人secret
        $realm = "xiaoi.com";
        $method = "POST";
        $uri = "/robot/ask.do";
        $nonce="";
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        for ( $i = 0; $i < 40; $i++)
            $nonce .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        $HA1 = sha1($app_key . ":" . $realm . ":" . $app_secret);
        $HA2 = sha1($method . ":" . $uri);
        $sign = sha1($HA1 . ":" . $nonce . ":" . $HA2);
    $msg=urlencode($msg);
    $openid=urlencode($openid);
    $url="http://nlp.xiaoi.com/robot/ask.do";
    $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Auth:    app_key="'.$app_key.'", nonce="'.$nonce.'", signature="'.$sign.'"'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "question=".$msg."&userId=".$openid."&type=0");
         $data = curl_exec($ch);
         echo  $data;












?>
