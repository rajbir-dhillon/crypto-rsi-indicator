<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	include '../classes/class.binance.php';
	include "../classes/class.database.php";
	$binance = new Binance\API();
	$db = new Database();
	$symbols = $binance->symbol();

	$ts_time = time("GMT");
	$record_count = 0;
	$symbol = "ETHBTC";
	$opentime = $db->last_symbol_time($symbol);
	echo $opentime;

	$klines = $binance->candlesticks($symbol, $interval = "1m", $limit = 150, $startTime = $opentime);
	$insert = $db->insert_data($klines, $symbol);

	foreach ($klines as $kline) {
		$record_count++;
	}

	echo "</br>" . $record_count;
?>