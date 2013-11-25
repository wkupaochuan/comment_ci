<?php
class Bubble_sort extends CI_Model{
	
	/**
	 * construct function
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * original bubble sort
	 * @param unknown $array
	 */
	public function original_b_s($array)
	{
		$compare_count = 0;
		$exchange_count = 0;
		$count = count($array);
		
		// sort body
		$start_time = microtime(true);
		for ($i = 1; $i < $count; ++$i)
		{
			for($j = 0; $j < $count - $i; ++$j)
			{
				if($array[$j] > $array[$j + 1])
				{
					list($array[$j] , $array[$j + 1]) = array($array[$j + 1], $array[$j]) ;
					++$exchange_count;
				}
				++$compare_count;
			}
		}
		$end_time = microtime(true);
		
		
		// echo expend
		$message = 'in function '.__FUNCTION__.':  elapsed_time=>'.($end_time - $start_time).'s;';
		$message .= 'exchange_count=>'.($exchange_count);
		$message .= 'compare_count=>'.($compare_count).'<br>';
		echo $message;
		echo 'sort result:';
		// check sort result
		var_dump(array_slice($array, 0, 10)) ;
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
	
}

?>