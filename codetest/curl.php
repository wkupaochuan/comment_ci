<?php

//
//$str = <<<SQL
//{"uri":"http:\/\/localhost:8088\/emma\/pingguapi\/addOrder","res":"{\"result_code\":2000,\"result_msg\":\";\\u4e0d\\u5b58\\u5728\\u7684\\u8d26\\u53f7:2\",\"result_data\":[]}"}
//SQL;
//print_r(json_decode($str, true));
//exit;

//$str = "\xe6\x93\x8d\xe4\xbd\x9c\xe6\x88\x90\xe5\x8a\x9f1";
//print_r(unserialize($str));
//exit;

//
//
//class curlTool{
//
//    /******************************** public methods *****************************************************/
//
//
//    protected function makeRequest($url, $params)
//    {
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
//        curl_setopt($ch, CURLOPT_HEADER, false);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
//        $ret = curl_exec($ch);
//        $err = curl_error($ch);
//        curl_close($ch);
//
//        if(!empty($err))
//        {
//            echo '出错';
//            print_r($err);
//            echo PHP_EOL;
//            exit;
//        }
//
//        return $ret;
//    }
//
//
//
//
//    /**
//     * 打印url
//     * @param $url
//     * @param $params
//     */
//    private function _echoUrl($url, $params)
//    {
//        $queryString = $this->_getQueryString($params);
//        echo $url . '?' . $queryString;
//    }
//
//
//    /******************************** private methods *****************************************************/
//
//
//
//    /**
//     * 获取qeurystring
//     * @param $params
//     * @return string
//     */
//    private function _getQueryString($params)
//    {
//        $queryString = '';
//        foreach($params as $key => $v)
//        {
//            $queryString .= $key . '=' . $v;
//        }
//
//        return $queryString;
//    }
//
//}
//

// 取数据接口
$fetchNum = 20000;
$eventTime = strtotime('2015-08-27 11:00:00');
//$eventTime = time();
$fetchDataTypeDic = array('user_status', 'account_info', 'account_tag', 'campaign_info', 'order_status'
    , 'order_exec_info', 'order_share_client_info', 'order_qc_info');
$activeDataType = $fetchDataTypeDic[3];
$fetchDataParams = array(
    'emma_data_request' =>
        json_encode(
            array(
                'data_type' => $activeDataType
                ,'fetch_num' => $fetchNum
                ,'skip_num' => 0
                ,'field_list' => ''
                , 'start_time' => $eventTime - 60*60*3
                , 'end_time' => $eventTime
                , 'event_time' => $eventTime
                , 'sign' => sha1($activeDataType . $fetchNum . $eventTime . 'EMMAORDERPLANNER')
            )
    )
);

// 创建订单参数
$pushCampaignId = 61;
$pushDataParams = array(
    'emma_account_plan' =>
        json_encode(
            array(
                'batch_id' => $eventTime
                , 'campaign_id' => $pushCampaignId
                , 'event_time' => $eventTime
                , 'sign' => sha1($eventTime . $pushCampaignId . $eventTime . 'EMMAORDERPLANNER')
                , 'accounts' => array(
                    array(
                        'account_id' => 692
                        , 'plan_id' => $eventTime
                        , 'bid_price' => 1000
                        , 'profit_rate' => 0.1
                        , 'order_start_time' => $eventTime + 10
                        , 'order_end_time' => $eventTime + 1000
                    )
                )
            )
        )
);

$testPushDataParams = array(
    'emma_account_plan' =>
        json_encode(
            array(
                'batch_id' => $eventTime
                , 'campaign_id' => $pushCampaignId
                , 'event_time' => $eventTime
                , 'sign' => sha1($eventTime . $pushCampaignId . $eventTime . 'EMMAORDERPLANNER')
                , 'accounts' => array(
                    array(
                        'account_id' => 692
                        , 'plan_id' => $eventTime
                        , 'bid_price' => 10
                        , 'profit_rate' => 0.1
                        , 'order_start_time' => $eventTime + 10
                        , 'order_end_time' => $eventTime + 1000
                    )
                )
            )
        )
);

// 活动叫停参数
$cancelCampaignParams = array(
    'emma_campaign_stopped' => json_encode(array(
        'campaign_id' => $pushCampaignId
        , 'status' => 5
        , 'event_time' => $eventTime
        , 'sign' => sha1($pushCampaignId . $eventTime . 'EMMAORDERPLANNER')
    )
    )
);


$developParams = array(
    'fetch_data' => array(
        'url' => 'http://192.168.100.25:8088/emma/pingguapi/fetchdata'
        , 'params' => $fetchDataParams
    )
    , 'push_data' => array(
        'url' => 'http://192.168.100.25:8088/emma/pingguapi/addOrder'
        , 'params' => $pushDataParams
    )
    , 'cancel_campaign' => array(
        'url' => 'http://192.168.100.25:8088/emma/pingguapi/cancelCampaign'
        , 'params' => $cancelCampaignParams
    )
);


$testParams = array(
    'fetch_data' => array(
        'url' => 'http://192.168.100.10:8088/emma/pingguapi/fetchdata'
        , 'params' => $fetchDataParams
    )
    , 'push_data' => array(
        'url' => 'http://192.168.100.10:8088/emma/pingguapi/addOrder'
        , 'params' => $testPushDataParams
    )
    , 'cancel_campaign' => array(
        'url' => 'http://192.168.100.10:8088/emma/pingguapi/cancelCampaign'
        , 'params' => $cancelCampaignParams
    )
);

$productionParams = array(
    'fetch_data' => array(
        'url' => 'http://192.168.0.35:8088/emma/pingguapi/fetchdata'
        , 'params' => $fetchDataParams
    )
);

$candidateParams = array(
    'dev' => $developParams
    , 'test' => $testParams
    , 'production' => $productionParams
);


// 获取当前请求的参数
$activeParams = $candidateParams['dev']['fetch_data'];

// 输出请求的url
foreach($activeParams['params'] as $key => $v)
{
    echo '当前url:' . $activeParams['url'] . '?' . $key . '=' . $v . PHP_EOL;
}


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $activeParams['url']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $activeParams['params']);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
$ret = curl_exec($ch);
$err = curl_error($ch);

$ret = json_decode($ret, true);
//echo $ret['result_data']['total_count'] . PHP_EOL;
//$ret = $ret['result_data']['data_list'];
//echo count($ret).PHP_EOL;
print_r($ret);

exit;