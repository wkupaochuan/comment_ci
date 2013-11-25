<?php

class Algrithm extends CI_Controller{
	
	private $array = array();
	
	function __construct()
	{
		set_time_limit(0);
		parent::__construct();
		
		// initial array to be sorted
		for($i = 0; $i < 10000; ++$i)
		{
			array_push($this->array, rand(1, 1000000));
		}
		
		// load modles
		$this->load->model('service/bubble_sort');
		$this->load->model('service/quick_sort');
	}
	
	public function index()
	{
		
		//$this->bubble_sort->original_b_s($this->array);
		$this->quick_sort->original_q_s($this->array);
		$this->quick_sort->original_q_s_1($this->array);
		$this->quick_sort->original_q_s_2($this->array);
	}
	
	/***************************************************快速排序*************************************************/
	
	function quickSort($arr) {
		if (count($arr) > 1) {
			$k = $arr[0];
			$x = array();
			$y = array();
			$_size = count($arr);
			for ($i=1; $i<$_size; $i++) {
				if ($arr[$i] <= $k) {
					$x[] = $arr[$i];
					++$this->count;
				} else {
					++$this->count;
					$y[] = $arr[$i];
				}
			}
			$x = $this->quickSort($x);
			$y = $this->quickSort($y);
			return array_merge($x, array($k), $y);
		} else {
			return $arr;
		}
	}
	
	
	
	
	

}

?>