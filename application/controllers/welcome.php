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
        $this->config->load('test_folder/test.php', true);
        echo $this->config->item('my_name', 'test_folder/test');
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