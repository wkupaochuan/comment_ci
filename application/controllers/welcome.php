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
		$a = array(
				'a'=>'dd',
				'b'=>'bb',
				'c'=>'cc'
				);
		$b = array_rand($a);
		var_dump($b);
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
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */