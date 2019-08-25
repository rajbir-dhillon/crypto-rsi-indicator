<?php 
include "db_config.php";
/**
 * databse class to connect and interact with the database
 */
class Database
{
	public $pdo;
	
	function __construct()
	{
		$this->pdo = "mysql:host=" . DB_SERVER . ";dbname=" . DB_DATABASE . ";";
	 		$options = [
	     		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	     		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	     		PDO::ATTR_EMULATE_PREPARES   => false,
	 		];
	 	
	 	try {
	      $this->pdo = new PDO($this->pdo, DB_USERNAME, DB_PASSWORD, $options);
	 	} catch (\PDOException $e) {
	      throw new \PDOException($e->getMessage(), (int)$e->getCode());
	 	}
	}

	public function select_symbol_data($symbol)
	{
		$sql = "SELECT * FROM symbol_data WHERE symbol = '$symbol'";
		$stmt = $this->pdo->prepare($sql);
		$result = $stmt->execute();
		$count = $stmt->rowCount();

		return $count;
	}

	public function select_opentime_data($time)
	{
		$sql = "SELECT * FROM symbol_data WHERE symbol = 'ETHBTC' AND open_time = '$time'";
		$stmt = $this->pdo->prepare($sql);
		$result = $stmt->execute();
		$count = $stmt->rowCount();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return $row;
	}

	public function last_symbol_time($symbol)
	{
		$sql = "SELECT * 
				FROM  `symbol_data` 
				WHERE  `symbol` =  '$symbol'
				ORDER BY `open_time` DESC 
				LIMIT 1";
		$stmt = $this->pdo->prepare($sql);
		$result = $stmt->execute();
		$row = $stmt->fetch();
		$count = $stmt->rowCount();

		return $row['open_time'];
	}

	public function compare_data($klines, $sym)
	{
		$tempCount = 0;
		$indb = 0;
		$err = "";
		foreach ($klines as $kline) {
			$data = [
				'symbol' => $sym,
				'open_time' => $kline[0],
				'open' => $kline[1],
				'high' => $kline[2],
				'low' => $kline[3],
				'close' => $kline[4],
				'volume' => $kline[5],
				'close_time' => $kline[6],
				'quote_asset_volume' => $kline[7],
				'no_of_trades' => $kline[8],
				'taker_buy_asset_volume' => $kline[9],
				'taker_buy_quote_volume' => $kline[10],
			];
			
			$sql = "SELECT * FROM `symbol_data` WHERE `symbol` = '" . $data['symbol'] . "' AND `open_time` = " . $data['open_time'] . " AND `open_price` = " . $data['open'] . " AND `high` = " . $data['high'] . " AND `low` = " . $data['low'] . " AND `close_price` = " . $data['close'] . " AND `volume` = " . $data['volume'] . " AND `close_time` = " . $data['close_time'] . " AND `quote_asset_volume` = " . $data['quote_asset_volume'] . " AND `no_of_trades` = " . $data['no_of_trades'] . " AND `taker_buy_asset_volume` = " . $data['taker_buy_asset_volume'] . " AND `taker_buy_quote_volume` = " . $data['taker_buy_quote_volume'] . "";
			$stmt = $this->pdo->prepare($sql);
			$result = $stmt->execute();
			$count = $stmt->rowCount();

			
			if ($count >= 1) {
				//if record is in the db
				// record found
				$tempCount++;
			} else {
				//if record is not in db
				//insert data
				if (isset($_GET['uploadBtn'])) {
					# insert to db
					$insert = $this->insert_symbol_data($kline, $sym);
					$indb++;
				}
			}
		}
		return $tempCount . "<br>added to db: " . $indb . $err;
	}

	public function insert_data($klines, $sym)
	{
		$tempCount = 0;
		$indb = 0;
		$err = "";
		foreach ($klines as $kline) {
			$data = [
				'symbol' => $sym,
				'open_time' => $kline[0],
				'open' => $kline[1],
				'high' => $kline[2],
				'low' => $kline[3],
				'close' => $kline[4],
				'volume' => $kline[5],
				'close_time' => $kline[6],
				'quote_asset_volume' => $kline[7],
				'no_of_trades' => $kline[8],
				'taker_buy_asset_volume' => $kline[9],
				'taker_buy_quote_volume' => $kline[10],
			];
			
			$sql = "SELECT * FROM `symbol_data` WHERE `symbol` = '" . $data['symbol'] . "' AND `open_time` = " . $data['open_time'] . " AND `open_price` = " . $data['open'] . " AND `high` = " . $data['high'] . " AND `low` = " . $data['low'] . " AND `close_price` = " . $data['close'] . " AND `volume` = " . $data['volume'] . " AND `close_time` = " . $data['close_time'] . " AND `quote_asset_volume` = " . $data['quote_asset_volume'] . " AND `no_of_trades` = " . $data['no_of_trades'] . " AND `taker_buy_asset_volume` = " . $data['taker_buy_asset_volume'] . " AND `taker_buy_quote_volume` = " . $data['taker_buy_quote_volume'] . "";
			$stmt = $this->pdo->prepare($sql);
			$result = $stmt->execute();
			$count = $stmt->rowCount();

			
			if ($count >= 1) {
				//if record is in the db
				// record found
				$tempCount++;
			} else {
				//if record is not in db
				//insert data	
				$insert = $this->insert_symbol_data($kline, $sym);
				$indb++;
			}
		}
		return $tempCount . "<br>added to db: " . $indb . $err;

		
	}

	public function insert_symbol_data($klines, $sym)
	{	
		$data = [
			'symbol' => $sym,
			'open_time' => $klines[0],
			'open' => $klines[1],
			'high' => $klines[2],
			'low' => $klines[3],
			'close' => $klines[4],
			'volume' => $klines[5],
			'close_time' => $klines[6],
			'quote_asset_volume' => $klines[7],
			'no_of_trades' => $klines[8],
			'taker_buy_asset_volume' => $klines[9],
			'taker_buy_quote_volume' => $klines[10],
		];
		 $sql = "INSERT INTO symbol_data (symbol, open_time, open_price, high, low, close_price, volume, close_time, quote_asset_volume, no_of_trades, taker_buy_asset_volume, taker_buy_quote_volume) VALUES (:symbol, :open_time, :open, :high, :low, :close, :volume, :close_time, :quote_asset_volume, :no_of_trades, :taker_buy_asset_volume, :taker_buy_quote_volume)";
		 $stmt= $this->pdo->prepare($sql);
		 $stmt->execute($data);

		 // print_r("</br>Open time: " . $open_time . "</br>Open time: " . $open_time_date ."</br>Open: " . $open . "</br>High: " . $high . "</br>Low: " . $low . "</br>close: " . $volume . "</br>Close Time: " . $close_time . "</br>Close Time date: " . $close_time_date . "</br>Quote asset volume: " . $quote_asset_volume . "</br>Number of trade: " . $no_of_trades . "</br>Taker buy asset volume: " . $taker_buy_asset_volume . "</br>Taker buy quote volume: " . $taker_buy_quote_volume);
		//print_r("record added to database"); 

		
	}

}

?>

















