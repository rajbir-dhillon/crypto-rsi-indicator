<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	include '../classes/class.binance.php';
	include "../classes/class.database.php";
	$db = new Database();

	$count = $db->select_symbol_data("ETHBTC");
	echo $count;

	$time = $db->last_symbol_time("ETHBTC");
	echo "<br>" . gmdate("d-m-Y\ H:i:s", $time/1000);
?>