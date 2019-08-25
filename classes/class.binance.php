<?php
namespace Binance;
include_once 'class.request.php';
	/**
	 * 
	 */
	namespace Binance;

	class API
	{
		protected $url;
		
		public $request;

		private $base;//base address
		private $address;//address with params
		private $httpDebug = false; // /< If you enable this, curl will output debugging information
		protected $api_key; // /< API key that you created in the binance website member area

		function __construct()
		{
			$this->base = 'https://api.binance.com/api/';
			//$this->request = new Request('https://api.binance.com/api/');
		}


		/**
	     * prices get all the current prices
	     *
	     * $ticker = $api->prices();
	     *
	     * @return array with error message or array of all the currencies prices
	     * @throws \Exception
	     */
	    public function prices()
	    {
	        return $this->priceData($this->httpRequest("v3/ticker/price"));
	    }

	    /**
	     * price get the latest price of a symbol
	     *
	     * $price = $api->price( "ETHBTC" );
	     *
	     * @return array with error message or array with symbol price
	     * @throws \Exception
	     */
	    
	    public function price(string $symbol)
	    {
	        $ticker = $this->httpRequest("v3/ticker/price", "GET", ["symbol" => $symbol]);

	        return $ticker['price'];
	    }


	    /**
	     * symbol gets all the symbols and stores in an array
	     * @return array with symbol
	     */
	    public function symbol()
	    {
	    	return $this->symbolData($this->httpRequest("v3/ticker/price"));
	    }

		/**
	     * priceData Converts Price Data into an easy key/value array
	     *
	     * $array = $this->priceData($array);
	     *
	     * @param $array array of prices
	     * @return array of key/value pairs
	     */
	    private function priceData(array $array)
	    {
	        $prices = [];
	        foreach ($array as $obj) {
	            $prices[$obj['symbol']] = $obj['price'];
	        }
	        return $prices;
	    }


	    /**
	     * symbolData Converts Symbol into  
	     * @param  array  $array [description]
	     * @return [type]        [description]
	     */
	    private function symbolData(array $array)
	    {
	    	$symbols = [];
	        foreach ($array as $obj) {
	            $symbols[] = $obj['symbol'];
	        }
	        return $symbols;
	    }


	    public function candlestickStream($symbols) 
		{
			$sym = $symbols;
			$ts_last = 1483243199000; //1.1.2017 milisec
			$data = $this->httpRequest("v1/klines?symbol=".$sym."&interval=1m&endTime=".$ts_last);
			$compiledData = array($sym => $data);
			return $compiledData;
			
		}

		// public function candlestickData($symbol, $openTime)
		// {

		// 	$data = $this->httpRequest("v1/klines?symbol=".$symbol."&interval=1m&startTime=".$openTime."&limit=1");
		// 	$compiledData = array($symbol => $data);
		// 	return $compiledData;
		// }

		public function candlesticks(string $symbol, string $interval = "5m", int $limit = null, $startTime = null, $endTime = null)
	    {
	        if (!isset($this->charts[$symbol])) {
	            $this->charts[$symbol] = [];
	        }
	        $opt = [
	            "symbol" => $symbol,
	            "interval" => $interval,
	        ];

	        if ($limit) {
	            $opt["limit"] = $limit;
	        }

	        if ($startTime) {
            	$opt["startTime"] = $startTime;
        	}

	        if ($endTime) {
	            $opt["endTime"] = $endTime;
	        }

	        $response = $this->httpRequest("v1/klines", "GET", $opt);
	        
	        if (is_array($response) === false) {
	            return [];
	        }

	        if (count($response) === 0) {
	            echo "warning: v1/klines returned empty array, usually a blip in the connection or server" . PHP_EOL;
	            return [];
	        }
	        
	        //$ticks = $this->chartData($symbol, $interval, $response);
	        //$this->charts[$symbol][$interval] = $ticks;
	        return $response;
	    }


	    private function httpRequest(string $url, string $method = "GET", array $params = null, bool $signed = false)
	    {
	    	//Check to see if cURL is installed
	        if (function_exists('curl_init') === false) {
	            throw new \Exception("Sorry cURL is not installed!");
	        }

	        $curl = curl_init();
	        //set cURL option for debiggong information
	        curl_setopt($curl, CURLOPT_VERBOSE, $this->httpDebug);
	        if ($params != NULL) {
	        	$query = http_build_query($params, '', '&');
	        }
	        
	        // signed with params
	        if ($signed === true) {
	            if (empty($this->api_key)) {
	                throw new \Exception("signedRequest error: API Key not set!");
	            }

	            if (empty($this->api_secret)) {
	                throw new \Exception("signedRequest error: API Secret not set!");
	            }

	            $base = $this->base;
	            $ts = (microtime(true) * 1000) + $this->info['timeOffset'];
	            $params['timestamp'] = number_format($ts, 0, '.', '');
	            if (isset($params['wapi'])) {
	                unset($params['wapi']);
	                $base = $this->wapi;
	            }
	            $query = http_build_query($params, '', '&');
	            $signature = hash_hmac('sha256', $query, $this->api_secret);
	            $endpoint = $base . $url . '?' . $query . '&signature=' . $signature;
	            curl_setopt($curl, CURLOPT_URL, $endpoint);
	            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	                'X-MBX-APIKEY: ' . $this->api_key,
	            ));
	        }
	        // params so buildquery string and append to url
	        else if (count($params) > 0) {
	            curl_setopt($curl, CURLOPT_URL, $this->base . $url . '?' . $query);
	        }
	        // no params so just the base url
	        else {
	            curl_setopt($curl, CURLOPT_URL, $this->base . $url);
	            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	                'X-MBX-APIKEY: ' . $this->api_key,
	            ));
	        }
	        curl_setopt($curl, CURLOPT_USERAGENT, "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)");
	        // Post and postfields
	        if ($method === "POST") {
	            curl_setopt($curl, CURLOPT_POST, true);
	            // curl_setopt($curlch, CURLOPT_POSTFIELDS, $query);
	        }
	        // Delete Method
	        if ($method === "DELETE") {
	            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
	        }

	        // PUT Method
	        if ($method === "PUT") {
	            curl_setopt($curl, CURLOPT_PUT, true);
	        }

	        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	        // headers will proceed the output, json_decode will fail below
	        curl_setopt($curl, CURLOPT_HEADER, false);
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

	        $output = curl_exec($curl);
	        // Check if any error occurred
	        if (curl_errno($curl) > 0) {
	            // should always output error, not only on httpdebug
	            // not outputing errors, hides it from users and ends up with tickets on github
	            echo 'Curl error: ' . curl_error($curl) . "\n";
	            return [];
	        }
	        curl_close($curl);
	        $json = json_decode($output, true);
	        if (isset($json['msg'])) {
	            // should always output error, not only on httpdebug
	            // not outputing errors, hides it from users and ends up with tickets on github
	            echo "signedRequest error: {$output}" . PHP_EOL;
	        }
	        return $json;
	    }

	}
?>
