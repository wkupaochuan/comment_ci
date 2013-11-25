<?php

class Algrithm extends CI_Controller{
	
	private $array = array();
	private $count = 0;
	
	function __construct()
	{
		set_time_limit(0);
		parent::__construct();
		
		
		for($i = 0; $i < 1000; ++$i)
		{
			array_push($this->array, rand(1, 1000000));
		}
	}
	
	public function index()
	{
		
		$this->original_b_s($this->array);
		
		$start_time = microtime(true);
		
		$this->quickSort($this->array);
		$end_time = microtime(true);
		$message = 'eplaposed timie in '.__FUNCTION__.':'.($end_time - $start_time).'<br>';
		$message .= 'compare count in '.__FUNCTION__.':'.($this->count).'<br>';
		
		echo $message;
	
		$this->count = 0;
		$start_time = microtime(true);
		
		$this->quick_sort($this->array, 0, count($this->array) - 1);
		$end_time = microtime(true);
		$message = 'eplaposed timie in '.__FUNCTION__.':'.($end_time - $start_time).'<br>';
		$message .= 'compare count in '.__FUNCTION__.':'.($this->count).'<br>';
		
		echo $message;
		var_dump(array_slice($this->array, 0, 10));
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
	
	/**
	 * 快速排序
	 * @param unknown $array
	 */
	public function quick_sort(&$array, $low, $high)
	{
		if($low < $high)
		{
			$partition = $this->partition($array, $low, $high);
			$this->quick_sort($array, $low, $partition - 1);
			$this->quick_sort($array, $partition + 1, $high);
		}
	}
	
	
	/**
	 * 获取分割点
	 * @param unknown $array
	 * @param unknown $low
	 * @param unknown $high
	 */
	public function partition(&$array, $low, $high)
	{
		$pivot = $array[$low];
		while ($low < $high)
		{
			while ($low < $high && $array[$high] > $pivot) 
			{
				$high--;
				++$this->count;
			}
			$array[$low] = $array[$high];
			while ($low < $high && $array[$low] <= $pivot) 
			{
				++$this->count;
				$low++;
			}
			$array[$high] = $array[$low];
		}
		$array[$low] = $pivot;
		return $low;
	}
	
	/***************************************************快速排序*************************************************/
	
	
	
	
	
	/*************************冒泡排序************************************/

	
	/**
	 * original bubble sort
	 * @param unknown $array
	 */
	public function original_b_s($array)
	{
		$compare_count = 0;
		$count = count($array);
		$start_time = microtime(true);
		for ($i = 1; $i < $count; ++$i)
		{
			for($j = 0; $j < $count - $i; ++$j)
			{
				if($array[$j] > $array[$j + 1])
				{
					list($array[$j] , $array[$j + 1]) = array($array[$j + 1], $array[$j]) ;
				}
				++$compare_count;
			}
		}
		$end_time = microtime(true);
		$message = 'eplaposed timie in '.__FUNCTION__.':'.($end_time - $start_time).'<br>';
		$message .= 'compare count in '.__FUNCTION__.':'.($compare_count).'<br>';
		
		echo $message;
		//var_dump(array_slice($array, 0, 10)) ;
	}
	
	/**
	 * original bubble sort
	 * @param unknown $array
	 */
	public function faster_b_s($array)
	{
		$compare_count = 0;
		$count = count($array);
		$start_time = microtime(true);
		for ($i = 1; $i < $count; ++$i)
		{
			$exchange = false;
			for($j = 0; $j < $count - $i; ++$j)
			{
				if($array[$j] > $array[$j + 1])
				{
					$exchange = true;
					list($array[$j] , $array[$j + 1]) = array($array[$j + 1], $array[$j]) ;
				}
				++$compare_count;
			}
			if(!$exchange) break;
		}
		$end_time = microtime(true);
		$message = 'eplaposed timie in '.__FUNCTION__.':'.($end_time - $start_time).'<br>';
		$message .= 'compare count in '.__FUNCTION__.':'.($compare_count).'<br>';
	
		echo $message;
		//var_dump(array_slice($array, 0, 10)) ;
	}
	
	/**
	 * original bubble sort
	 * @param unknown $array
	 */
	public function fatest_b_s($array)
	{
		$compare_count = 0;
		$count = count($array);
		$start_time = microtime(true);
		$mark = $count - 1;
		for ($i = 1; $i < $count; ++$i)
		{
			for($j = 0; $j < $mark; ++$j)
			{
				if($array[$j] > $array[$j + 1])
				{
					list($array[$j] , $array[$j + 1]) = array($array[$j + 1], $array[$j]) ;
					$index = $j;
				}
				++$compare_count;
			}
			$mark = $index;
		}
		$end_time = microtime(true);
		$message = 'eplaposed timie in '.__FUNCTION__.':'.($end_time - $start_time).'<br>';
		$message .= 'compare count in '.__FUNCTION__.':'.($compare_count).'<br>';
	
		echo $message;
		var_dump(array_slice($array, 0, 10)) ;
	}
	
	/*************************冒泡排序************************************/
}

?>