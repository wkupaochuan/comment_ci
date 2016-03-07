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
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $ret = curl_exec($ch);
        $http_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno     = curl_errno($ch);
        curl_close($ch);
        if (200 != $http_code) {
            return '';
        }
        return $ret;
    }

    /**
     * 分页获取文章url列表
     * @param int $page
     * @return array
     */
    private function getTopicList($page = 1)
    {
        $topic_url_array = array();
        if($page == 1)
        {
            $topic_list_url = 'http://china.huanqiu.com/hot/index.html';
        }else{
            $topic_list_url = 'http://china.huanqiu.com/hot/'. $page .'.html';
        }
        $html = $this->get($topic_list_url);
        if(empty($html))
        {
            return ;
        }
        $topic_item_patterns = '#<h3>([\s\S]*)<\/a><\/h3>#';
        preg_match($topic_item_patterns, $html, $matches);
        if(!empty($matches))
        {
            $topic_item_string = $matches[1];
            $topic_item_string_array = explode('</li>', $topic_item_string);
            if(!empty($topic_item_string_array))
            {
                foreach($topic_item_string_array as &$row)
                {
                    $topic_url_array[] = $this->getTopicUrlByHtml($row);
                }
            }
        }
        return $topic_url_array;
    }

    /**
     * 根据html元素， 获取文章url
     * @param $str
     */
    private function getTopicUrlByHtml($str)
    {
        if(empty($str))
        {
            return;
        }

        $pattern = '#<a href="([\s\S]*)" title#';
        preg_match($pattern, $str, $matches);
        return $matches[1];
    }


    /**
     * 获取文章的url
     * @return mixed
     */
    private function getTopicUrl()
    {
        $page = rand(1, 10);
        $topic_url_list = $this->getTopicList($page);
        if(empty($topic_url_list))
        {
            return '';
        }
        $sequence = rand(0, 50);
        return isset($topic_url_list[$sequence])? $topic_url_list[$sequence]:'';
    }

    /**
     * 获取文章内容
     * @return array|void
     */
    public function getTopicContent()
    {
        $url = $this->getTopicUrl();
        if(empty($url))
        {
            return ;
        }
        $html = file_get_contents($url);
        $title_pattern = '#<h1>([\s\S]*)<\/h1>#';
        $content_pattern = '#<div class="text" id="text"([\s\S]*)<!-- 责任编辑&版权 begin-->#';
        preg_match($title_pattern, $html, $matches);
        if(!empty($matches))
        {
            $title = $matches[0];
        }
        preg_match($content_pattern, $html, $matches);
        if(!empty($matches))
        {
            $content = $matches[0] . '</div>';
        }

        return array(
            'title' => $title,
            'content' => $content
        );
    }
}

$parser = new TopicParser();
$x = $parser->getTopicContent();
print_r($x);
exit();