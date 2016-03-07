<?php
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
        $html = iconv(mb_detect_encoding($html),"UTF-8",$html);

        $topic_item_patterns = '#<div id="htmlTreeMenu" class="htmlMenu">([\s\S]*)<\/ul>
<\/div>#';
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
            }
        }

        unset($tree[0]['children'][0]);
        unset($tree[0]['children'][1]);

        return $tree;
    }

    public function getContent($url)
    {
        $ret = array();
        $html = file_get_contents($url);
        $html = iconv(mb_detect_encoding($html),"UTF-8",$html);
        $pattern = '#店名片开始([\s\S]*)店名片结束#';
        preg_match($pattern, $html, $matches);
        if(!empty($matches))
        {
            $str = $matches[1];
            $pattern = '#<table width="100%" height="339"([\s\S]*)</table>#U';
            preg_match($pattern, $str, $matches);
            $str = $matches[1];

            $str_array = explode('<tr>', $str);


            $pattern = '# <td width="51%">([\s\S]*)</td>#U';
            preg_match($pattern, $str_array[0], $matches);
            !empty($matches) && $ret['shop_name'] = $matches[1];

            $pattern = '# <td width="51%">([\s\S]*)</td>#U';
            preg_match($pattern, $str_array[0], $matches);
            !empty($matches) && $ret['shop_name'] = $matches[1];
        }

    }

    public function getSecondTree($str)
    {
        $ret = array();
        $tree_array = explode('</li>', $str);
        foreach($tree_array as $row)
        {
            $pattern = '#<a href="([\s\S]*)">([\s\S]*)</a>#';
            preg_match($pattern, $row, $matches);
            !empty($matches) && $ret[] = array('name' => $matches[2], 'url' => $matches[1]);
        }
        return $ret;
    }

}

$parser = new TopicParser();
$url  = 'http://0594666.com/shop/shop7169.html';
$x = $parser->getContent($url);
print_r($x);
exit();