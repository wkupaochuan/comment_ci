<?php
/*
$a = array(
        array('accountId'=>60159, 'isVip'=>3, 'isBluevip'=>3, 'isDaren'=>3, 'introduction'=>'午安哈哈哈哈哈哈'),
        array('accountId'=>60160, 'isVip'=>3, 'isBluevip'=>3, 'isDaren'=>3),
	
);
*/
/*
$a = array(
        array('accountId'=>60159, 'followersCount'=>3, 'isShield'=>3, 'isSensitive'=>3),
        array('accountId'=>60160, 'followersCount'=>3, 'isShield'=>3, 'isSensitive'=>3)
	
);
*/
$a = '';
$postFields = array('changeItems'=>json_encode($a)
);



$postFields = array(
'accountId'=>1222,
'isFake'=>1
);	

//$postFields = 'asdf';;
//$postFields = json_encode( $postFields, true);

//$url = "http://192.168.100.25:8078/account/info/batchUpdateForPinggu";
//$url = "http://192.168.100.25:8078/account/info/updateVipInfoForPinggu";
$url = "http://192.168.100.25:8078/account/info/updateForPinggu";
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
