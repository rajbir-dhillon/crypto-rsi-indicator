<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	include '../classes/class.binance.php';
	include "../classes/class.database.php";
	$binance = new Binance\API();
	$db = new Database();
	$symbols = $binance->symbol();

	$ts_start = 1483243199000;
	$ts_time = time("GMT");
	$record_count = 0;
	$sym_count = 0;

	if (isset($_GET['symbolBtn']) || isset($_GET['nextBtn']) || isset($_GET['uploadBtn'])) {
		$ts_start = 1483243199000;
		$ts_time = time("GMT");
		$record_count = 0;

		$curr_symbol = $_GET['symbol'];
		echo $curr_symbol;

		$count = $db->select_symbol_data($curr_symbol);
		//echo $count;

		if (!$count >= 1) {
			echo "</br> count: " . $count . "</br>";
			echo "no current data exists for " . $curr_symbol;
			echo "</br>";
			echo "attempt to add to database here";

			$klines = $binance->candlesticks($curr_symbol, $interval = "1m", $limit = 1, $startTime = $ts_start);
			$insert = $db->insert_symbol_data($klines[0], $curr_symbol);

		} else { 
			//if records for $curr_symbol exists in database
			echo "</br>Data already exists";
			echo "</br>Number of records: " . $count;
			

			if (isset($_GET['nextBtn'])) {
				$ts_start = $_GET['time'] + 1;
			}
			
			$klines = $binance->candlesticks($curr_symbol, $interval = "1m", $limit = 1000, $startTime = $ts_start);
			$compare = $db->compare_data($klines, $curr_symbol);
			foreach ($klines as $kline) {
				$ts_start = $kline[0];
				$record_count++;
			}
			$last_rec = end($klines);
			$compare = $db->compare_data($klines, $curr_symbol);
			echo "<br> Showing: " . gmdate("d-m-Y\ H:i:s", $klines[0][0]/1000) . " - " . gmdate("d-m-Y\   H:i:s", $last_rec[0]/1000);
			echo "<br>".$record_count;


			echo "<br>Total records for this set already in database: " . $compare;
			echo "<form action='' method='get'>
					<input type='hidden' name='symbol' value='". $curr_symbol ."'>
					<input type='hidden' name='time' value='". $last_rec[0] ."'>
					<button type='submit' name='nextBtn'>next</button>
				  </form>";
			echo "<form action='' method='get'>
					<input type='hidden' name='symbol' value='". $curr_symbol ."'>
					<input type='hidden' name='time' value='". $ts_start ."'>
					<button type='submit' name='uploadBtn'>Upload</button><br>
				  </form>";
		}
	}
	
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
	<body>
		<form action="" method="get">
			<select name="symbol">
				<?php
					foreach($symbols AS $option){
					    echo '<option value="'.$option.'">'.$option.'</option>';
					}
				?>
			</select>
			<button type="submit" name="symbolBtn">Go</button>
		</form>
	</body>
</html>






<!-- Code to print one record of API data -->
<!-- 		print_r("</br>Open time: " . gmdate("d-m-Y\ H:i:s", $klines[0][0]/1000) . 
				"<br>Open: " . $klines[0][1]. 
				"<br>High: " . $klines[0][2].
				"<br>Low: " . $klines[0][3].
				"<br>Close: " . $klines[0][4].
				"<br>Volume: " . $klines[0][5].
				"<br>Close Time: " . gmdate("d-m-Y\ H:i:s", $klines[0][6]/1000).
				"<br>Quote asset volume: " . $klines[0][7].
				"<br>Number of trades: " . $klines[0][8].
				"<br>Taker buy asset volume: " . $klines[0][9].
				"<br>taker buy quote volume: " . $klines[0][10]); -->




