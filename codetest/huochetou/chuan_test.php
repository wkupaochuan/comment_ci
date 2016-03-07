<?php
error_reporting(-1);
set_time_limit(-1);
class TopicParser{
    /**
     * 发送curl请求
     * @param $url
     * @return mixed
     */
    private function get($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
//        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $ret = curl_exec($ch);
        $http_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno     = curl_errno($ch);
        curl_close($ch);
        if (200 != $http_code) {
            return '';
        }
        return $ret;
    }

    public function test()
    {
        $html = file_get_contents('http://0594666.com/index.html');
        if(empty($html))
        {
            return ;
        }
        $html = iconv('gbk',"UTF-8",$html);

//        $html = iconv(mb_detect_encoding($html),"UTF-8",$html);

        $topic_item_patterns = '#<div id="htmlTreeMenu" class="htmlMenu">([\s\S]*)</div>#U';
//        $topic_item_patterns = '#<div id="htmlTreeMenu" class="htmlMenu">([\s\S]*)<\/ul>
//<\/div>#';
        preg_match($topic_item_patterns, $html, $matches);
        $tree = array();
        if(!empty($matches))
        {
            $str = $matches[0];
            $pattern = '#<b>([\s\S]*)</b>#U';
            preg_match_all($pattern, $str, $matches);
            !empty($matches) && $first_tree = $matches[1];

            $pattern = '#<ul>([\s\S]*)</ul>#U';
            preg_match_all($pattern, $str, $matches);
            !empty($matches) && $second_tree = $matches[1];

            for($i = 0; $i < count($first_tree); ++$i)
            {
                $tree[] = array(
                    'name' => $first_tree[$i],
                    'children' => $this->getSecondTree($second_tree[$i])
                );

                if($i > 0)
                {
                    break;
                }
            }
        }

        return $tree;
    }

    /**
     * 获取店铺详情
     * @param $url
     * @return array
     */
    public function getContent($url)
    {
        $ret = array();
        $html = file_get_contents($url);
//        $html = iconv(mb_detect_encoding($html),"UTF-8",$html);
        $html = iconv('gbk','UTF-8',$html);
        $pattern = '#店名片开始([\s\S]*)店名片结束#';
        preg_match($pattern, $html, $matches);
        if(!empty($matches))
        {
            $str = $matches[1];
            $pattern = '#<table width="100%" height="339"([\s\S]*)</table>#U';
            preg_match($pattern, $str, $matches);
            $str = $matches[1];

            $str_array = explode('</tr>', $str);
//            print_r($str_array);

            // 店铺名称
            $pattern = '# <td width="51%">([\s\S]*)</td>#U';
            preg_match($pattern, $str_array[0], $matches);
            !empty($matches) && $ret['shop_name'] = $matches[1];

            // 网址1
            $pattern = '#<a href=([\s\S]*)target="_blank">([\s\S]*)</a>#U';
            preg_match($pattern, $str_array[1], $matches);
            !empty($matches) && $ret['url_1'] = $matches[1];

            // 网址2
            $pattern = '#<a href=([\s\S]*)target="_blank">([\s\S]*)</a>#U';
            preg_match($pattern, $str_array[2], $matches);
            !empty($matches) && $ret['url_2'] = $matches[1];

            // 搜服
            $pattern = '#<a href="([\s\S]*)">([\s\S]*)</a>#U';
            preg_match($pattern, $str_array[3], $matches);
            !empty($matches) && $ret['so_fu'] = $matches[1];


            // qq1
            $pattern = '#<td>([\s\S]*)<a#U';
            preg_match($pattern, $str_array[4], $matches);
            !empty($matches) && $ret['qq_1'] = $matches[1];

            // qq2
            $pattern = '#<td>([\s\S]*)<a#U';
            preg_match($pattern, $str_array[5], $matches);
            !empty($matches) && $ret['qq_2'] = $matches[1];


            // 微信
            $pattern = '#<td>([\s\S]*)<a#U';
            preg_match($pattern, $str_array[6], $matches);
            !empty($matches) && $ret['wechat'] = $matches[1];


            // 联系方式
            $pattern = '#<td>([\s\S]*)</td>#U';
            preg_match($pattern, $str_array[7], $matches);
            !empty($matches) && $ret['phone'] = $matches[1];

            // 地址
            $pattern = '#<td>([\s\S]*)</td>#U';
            preg_match($pattern, $str_array[8], $matches);
            !empty($matches) && $ret['address'] = $matches[1];

            // 主营产品
            $pattern = '#<div style="width:370px;">([\s\S]*)</div>#U';
            preg_match($pattern, $str_array[9], $matches);
            !empty($matches) && $ret['goods'] = $matches[1];

        }

        return $ret;
    }

    /**
     * 获取二级分类
     * @param $str
     * @return array
     */
    public function getSecondTree($str)
    {
        $ret = array();
        $tree_array = explode('</li>', $str);
        foreach($tree_array as $row)
        {
            $pattern = '#<a href="([\s\S]*)">([\s\S]*)</a>#';
            preg_match($pattern, $row, $matches);
            if(!empty($matches) && strpos($matches[1], '"') === false)
            {
                !empty($matches) && $ret[] = array(
                    'name' => $matches[2],
                    'url' => 'http://0594666.com/' . $matches[1],
                    'shop_list' => $this->getShopList('http://0594666.com/' . $matches[1])
                );
            }
        }
        return $ret;
    }

    /**
     * 获取二级分类下的商店列表
     * @param $url
     * @return array
     */
    public function getShopList($url)
    {
        $ret = array();
        $html = file_get_contents($url);
//        $html = iconv(mb_detect_encoding($html),"UTF-8",$html);
        $html = iconv('gbk','UTF-8',$html);
        $pattern = '#<div class="list_shop_top([\s\S]*)</div>#U';
        preg_match_all($pattern, $html, $matches);

        $item_array = $matches[0];
        foreach($item_array as $row)
        {
            $pattern = '#<a href="/shop/([\s\S]*)" title="([\s\S]*)</a>#U';
            preg_match($pattern, $row, $matches);
            $ret[] = array(
                'url' => 'http://0594666.com/shop/' . $matches[1],
                'content' => $this->getContent('http://0594666.com/shop/' . $matches[1])
            );
        }

        return $ret;
    }
}

$parser = new TopicParser();
$x = $parser->test();
print_r($x);
exit();