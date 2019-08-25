<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../classes/class.RSI.php";
$RSI = new RSI('1556536440000');
$RSIdata = $RSI->get();
echo "1556536440000";

echo "RSI</br>";
echo "------------</br>";
for ($i=0; $i < count($RSIdata)-1; $i++) {
	echo $i+1 . " - ";
	print_r($RSIdata[$i]);
	echo "</br>";
} 
?>