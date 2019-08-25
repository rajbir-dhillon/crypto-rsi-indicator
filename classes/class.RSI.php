<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../classes/class.database.php";
/**
 * 
 */
class RSI
{
	private $ts_time;
	private $period;
	public $rsiPeriod;
	private $numberOfDaysUnix;
	private $db;
	private $RSIarray;
	function __construct($time)
	{
		$this->db = new Database();
		$this->ts_time = $time;
		$this->period = 14;
		$this->rsiPeriod = 14;
		$this->RSIarray = array();
		$numberOfDaysUnix = $this->period * 60000;
		$avg_Gain = $this->AvgGain();
		$avg_Loss = $this->AvgLoss();
		for ($i=0; $i < count($avg_Gain)-1; $i++) { 
			$rs = $avg_Gain[$i] / $avg_Loss[$i];
			$RSI = 100 - (100 / (1 + $rs));
			array_push($this->RSIarray, $RSI);
		}
	}

	function get(){
		return $this->RSIarray;
	}

	public function AvgGain()
	{
		$curr_time = $this->ts_time - (14 * 60000);
		$gainData = array();
		$change = 0;
		$avg = $this->calcFirstGain($curr_time);

		$data = $this->db->select_opentime_data($curr_time);
		$curr_price = $data['close_price'];
		$curr_time = $data['open_time'];

		# select previous record (curr record time - 60000)
		$prev_data = $this->db->select_opentime_data($curr_time - 60000);
		$prev_price = $prev_data['close_price'];

		if ($curr_price > $prev_price) {
			$change = $curr_price - $prev_price;
		}
				
		$avgGain = (($avg) * ($this->rsiPeriod - 1) + $change) / 14;
		array_push($gainData, $avgGain);

		while ($curr_time <= $this->ts_time) {
			// echo "while loop";
			$data = $this->db->select_opentime_data($curr_time);
			$curr_price = $data['close_price'];
			$curr_time = $data['open_time'];

			# select previous record (curr record time - 60000)
			$prev_data = $this->db->select_opentime_data($curr_time - 60000);
			$prev_price = $prev_data['close_price'];

			if ($curr_price > $prev_price) {
				$change = $curr_price - $prev_price;
			}
			// echo "</br>avg: " . $avg;
			// echo "</br>rsiPeriod: " . $this->rsiPeriod;
			// echo "</br>change: " . $change;
			$avgGain = (($avg) * ($this->rsiPeriod - 1) + $change) / 14;
			array_push($gainData, $avgGain);

			$curr_time = $curr_time + 60000;
		}
		// echo "</br> curr_time: " . $curr_time;
		// echo "</br> gain data: "; 
		// print_r($gainData);
		// echo "</br> change:" . $change;
		// echo "</br> avg: " . $avg; 
		return $gainData;
		//previous [(prevavggain) * 13 + current gain] / 14
	}

	public function AvgLoss(){
		$curr_time = $this->ts_time - ($this->rsiPeriod * 60000);
		$lossData = array();
		$change = 0;
		$avg = $this->calcFirstLoss($curr_time);

		$data = $this->db->select_opentime_data($curr_time);
		$curr_price = $data['close_price'];
		$curr_time = $data['open_time'];

		# select previous record (curr record time - 60000)
		$prev_data = $this->db->select_opentime_data($curr_time - 60000);
		$prev_price = $prev_data['close_price'];

		if ($curr_price > $prev_price) {
			$change = $prev_price - $curr_price;
		}

		$avgLoss = (($avg) * ($this->rsiPeriod - 1) + $change) / 14;
		array_push($lossData, $avgLoss);

		while ($curr_time <= $this->ts_time) {
			$data = $this->db->select_opentime_data($curr_time);
			$curr_price = $data['close_price'];
			$curr_time = $data['open_time'];

			# select previous record (curr record time - 60000)
			$prev_data = $this->db->select_opentime_data($curr_time - 60000);
			$prev_price = $prev_data['close_price'];

			if ($curr_price > $prev_price) {
				$change = $curr_price - $prev_price;
			}

			$avgLoss = (($avg) * ($this->rsiPeriod - 1) + $change) / 14;
			array_push($lossData, $avgLoss);

			$curr_time = $curr_time + 60000;
		}

		return $lossData;
	}

	public function calcFirstGain($curr_time)
	{
		$curr_time = $curr_time - $this->numberOfDaysUnix;
		$gainData = array();
		$curr_price;
		$prev_price;
		for ($i=0; $i < ($this->period - 1); $i++) { 
			# code...
			# select current record
			$data = $this->db->select_opentime_data($curr_time);
			$curr_price = $data['close_price'];
			$curr_time = $data['open_time'];

			# select previous record (curr record time - 60000)
			$prev_data = $this->db->select_opentime_data($curr_time - 60000);
			$prev_price = $prev_data['close_price'];
			if ($curr_price > $prev_price) {
				$change = $curr_price - $prev_price;
				array_push($gainData, $change);
			}

			$curr_time = $curr_time + 60000;
		}
		// echo "<br>data: ";
		// print_r($curr_price);
		$firstEma = (array_sum($gainData)) / $this->period;
		
		return $firstEma;
	}

	public function calcFirstLoss($curr_time)
	{
		$curr_time = $curr_time - $this->numberOfDaysUnix;
		$lossData = array();
		$curr_price;
		$prev_price;
		for ($i=0; $i < ($this->period - 1); $i++) { 
			# code...
			# select current record
			$data = $this->db->select_opentime_data($curr_time);
			$curr_price = $data['close_price'];
			$curr_time = $data['open_time'];

			# select previous record (curr record time - 60000)
			$prev_data = $this->db->select_opentime_data($curr_time - 60000);
			$prev_price = $prev_data['close_price'];

			if ($curr_price < $prev_price) {
				$change = $prev_price - $curr_price;
				array_push($lossData, $change);
			}

			$curr_time = $curr_time + 60000;
		}

		$firstEma = (array_sum($lossData)) / $this->period;
		
		return $firstEma;
	}
}
?>


