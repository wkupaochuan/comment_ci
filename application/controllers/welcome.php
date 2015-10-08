<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
//        return 'ahh';
        $this->load->database();

        $sql = <<<SQL
INSERT INTO `test` (`id`, `name`) VALUES (xx, ddd)
SQL;

        $res = $this->db->query($sql, 12);

        var_dump($res);exit;



//        $a = null;
//        var_dump($a);
//        show_error('i am testing');
//        ob_start();
//        echo 'hello';//此处并不会在页面中输出
//        $a = ob_get_level();
//        $b = ob_get_contents();//获得缓存结果,赋予变量
//        ob_clean();
//        ob_start();
//        echo 'world';//此处并不会在页面中输出
//        $c = ob_get_level();
//        $d = ob_get_contents();//获得缓存结果,赋予变量
//        ob_clean();
//        ob_start();
//        echo 'hi';//此处并不会在页面中输出
//        $e = ob_get_level();
//        $f = ob_get_contents();//获得缓存结果,赋予变量
//        ob_clean();
//
//        echo 'level:'.$a.',ouput:'.$b.'<br>';
//        echo 'level:'.$c.',ouput:'.$d.'<br>';
//        echo 'level:'.$e.',ouput:'.$f.'<br>';
//        $this->load->library('log');
//        $this->log->write_log('debug', 'i am debug!');
//        $this->log->my_write_log('info', 'i am info!', true);
//        $this->config->load('test_folder/test.php', true);
//        echo $this->config->item('my_name', 'test_folder/test');
//		echo ip2long('000.000.000.100'); exit;
//		$a = array(
//				'a'=>'dd',
//				'b'=>'bb',
//				'c'=>'cc'
//				);
//		$b = array_rand($a);
//		var_dump($b);
	}
	
	public function bubble_sort()
	{
		$array = array();
		for($i = 0; $i < 100000; ++$i)
		{
			$array[] = rand(1, 1000000);
		}
		var_dump(array_slice($array, 2, 5)) ;
	}

    public function test_md5()
    {
        echo md5('http://hi.baidu.com/cubeking/item/815369445be2162a11ee1e03');
//        echo strlen(md5('dddd'));
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */