<?php

	/**
	 * 
	 */
	class Request
	{
		private $base;//base address
		private $address;//address with params
		private $apik;//store api key
		private $apis;//store api secret
		private $outpt;//store request output;

		public function __construct($base)
		{
			if (!isset($base)) {
      			throw new Exception("Error: Address not provided.");
    		}
    		$this->base = $base;
  		}

  		/*
  		
  		 */
  		public function setAddress($adrs)
  		{
  			$this->address = $this->base . $adrs;
  		}

  		/*
  		
  		 */
  		public function setApiKey($apik)
  		{
  			$this->apik = $apik;
  		}

  		/*
  		
  		 */
  		public function setApiSecret($apis)
  		{
  			$this->apis = $apis;
  		}


  		/*
  		
		 */
		public function execute()
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $this->address);
			$output = curl_exec($curl);
			$json = json_decode($output, true);
			return $json;
		}

	}

?>
