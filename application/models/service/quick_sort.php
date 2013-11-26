<?php

class quick_sort extends CI_Model{
	
	/**
	 * construct function
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	

	
	/**
	 * original quick sort 
	 * @param unknown $array
	 */
	public function original_q_s($array)
	{
		$compare_count = 0;
		$exchange_count = 0;
		
		// sort body
		$start_time = microtime(true);
		$this->quick_sort($array, 0, count($array) - 1, $compare_count, $exchange_count);
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
	
	public function quick_sort(&$array, $low, $high, &$compare_count, &$exchange_count)
	{
		if($low < $high)
		{
			$patition = $this->partition_1($array, $low, $high, $compare_count, $exchange_count);
			$this->quick_sort($array, $low, $patition - 1, $compare_count, $exchange_count);
			$this->quick_sort($array, $patition + 1, $high, $compare_count, $exchange_count);
		}
	}
	
	public function partition(&$array, $low, $high, &$compare_count, &$exchange_count)
	{
		$pivot = $array[$low];
		while($low < $high)
		{
			while($low < $high && $array[$high] > $pivot)
			{
				--$high;
				++$compare_count;
			}	
			$array[$low] = $array[$high];
			while($low < $high && $array[$low] <= $pivot)
			{
				++$low;
				++$compare_count;
			}
			$array[$high] = $array[$low];
			$exchange_count += 2;
		}
		$array[$low] = $pivot;
		return $low;
	}
	
	
	/**
	 * original quick sort
	 * @param unknown $array
	 */
	public function original_q_s_1($array)
	{
		$compare_count = 0;
		$exchange_count = 0;
	
		// sort body
		$start_time = microtime(true);
		$this->quick_sort_1($array, 0, count($array) - 1, $compare_count, $exchange_count);
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
	
	public function quick_sort_1(&$array, $low, $high, &$compare_count, &$exchange_count)
	{
		if($low < $high)
		{
			$patition = $this->partition_1($array, $low, $high, $compare_count, $exchange_count);
			$this->quick_sort_1($array, $low, $patition - 1, $compare_count, $exchange_count);
			$this->quick_sort_1($array, $patition + 1, $high, $compare_count, $exchange_count);
		}
	}
	
	
	public function partition_1(&$array, $low, $high, &$compare_count, &$exchange_count)
	{
		$pivot = $array[rand($low, $high)];
		while($low < $high)
		{
			while($low < $high && $array[$high] > $pivot)
			{
				--$high;
				++$compare_count;
			}
			list($array[$low], $array[$high])=array($array[$high], $array[$low]);
			while($low < $high && $array[$low] <= $pivot)
			{
				++$low;
				++$compare_count;
			}
			list($array[$low], $array[$high])=array($array[$high], $array[$low]);
			$exchange_count += 2;
		}
		$array[$low] = $pivot;
		return $low;
	}
	
	
	/**
	 * original quick sort
	 * @param unknown $array
	 */
	public function original_q_s_2($array)
	{
		$compare_count = 0;
		$exchange_count = 0;
	
		// sort body
		$start_time = microtime(true);
		$this->quick_sort_1($array, 0, count($array) - 1, $compare_count, $exchange_count);
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
	
	public function quick_sort_2(&$array, $low, $high, &$compare_count, &$exchange_count)
	{
		if($low < $high)
		{
			$patition = $this->partition_1($array, $low, $high, $compare_count, $exchange_count);
			$this->quick_sort_2($array, $low, $patition - 1, $compare_count, $exchange_count);
			$this->quick_sort_2($array, $patition + 1, $high, $compare_count, $exchange_count);
		}
	}
	
	
	public function partition_2(&$array, $low, $high, &$compare_count, &$exchange_count)
	{
		$array1 = array(
				$low=>$array[$low],
				$high=>$array[$high],
				($low + $high)/2=>$array[($low + $high)/2]
		);
		$pivot = $this->get_middle($array1);
		while($low < $high)
		{
			while($low < $high && $array[$high] > $pivot)
			{
				--$high;
				++$compare_count;
			}
			list($array[$low], $array[$high])=array($array[$high], $array[$low]);
			while($low < $high && $array[$low] <= $pivot)
			{
				++$low;
				++$compare_count;
			}
			list($array[$low], $array[$high])=array($array[$high], $array[$low]);
			$exchange_count += 2;
		}
		$array[$low] = $pivot;
		return $low;
	}
	
	private function get_middle($array)
	{
		ksort($array);
		return key(next($array));
	}
	
	
	
	
}

?>