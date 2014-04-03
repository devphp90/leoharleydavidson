<?php
class CanparShipping
{	
	public $url='http://www.canpar.com/XML/BaseRateXML.jsp';
	public $sender_city;
	public $sender_state_code;
	public $sender_country_code;
	public $sender_zip;
	public $products=array();		
	public $address=array();
	public $sub_total;
	public $output=array();
	public $error=0;

	// constructor
	public function __construct($sender_city,$sender_state_code,$sender_country_code,$sender_zip) {
		$this->sender_city=$sender_city;
		$this->sender_state_code=$sender_state_code;
		$this->sender_country_code=$sender_country_code;
		$this->sender_zip=$sender_zip;
	}	
	
    public function run()
    {
		
		($this->address['country_code']=='US') ? $service = 2 : $service = 1;
		
		$quantity = 0;
		$unit = "L";
		$weight = 0;
		$origin = $this->sender_zip;
		$dest = $this->address['zip'];
		$cod = 0;
		$dec = ($this->sub_total>20000)?20000:$this->sub_total;
		$put = 0;
		$xc = 0;

		foreach ($this->products as $row) {		
			$quantity += $row['qty'];
			$weight += ($row['weight']*$row['qty']);
			$unit = $row['weight_unit_symbol'];
			$row['extra_care'] ? $xc+=($row['qty']) :'';
		}
		
		$this->url = $this->url . '?service='.$service.'&quantity='.$quantity.'&unit='.$unit.'&weight='.$weight.'&origin='.$origin.'&dest='.$dest.'&cod='.$cod.'&dec='.$dec.'&put='.$put.'&xc='.$xc;
		//echo $this->url;
	
		// create a new cURL resource
		$ch = curl_init();
		
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		// grab URL and pass it to the browser
		if ($response = curl_exec($ch)) {
			$xml = new SimpleXMLElement($response);

			//echo $response;
			//echo $xml->error->statusCode;
			//$this->output['source'] = $xml;
			
			// if there is an error
			if (isset($xml->CanparRateErrors)) {
				$this->error = 1;
				/*$this->error = array(
					'error'=>(string)$xml->error->statusCode.' - '.(string)$xml->error->statusMessage,
				);*/
			// output
			} else { 
					$total_rate = 0;
					$ShippingDate = "-";
					if(isset($xml->EstimatedDeliveryDate))$ShippingDate = $xml->EstimatedDeliveryDate;
					if(isset($xml->CanparCharges->PUTCharge))$total_rate += (float)$xml->CanparCharges->PUTCharge;
					if(isset($xml->CanparCharges->BaseRate))$total_rate += (float)$xml->CanparCharges->BaseRate;
					if(isset($xml->CanparCharges->ExtraCareCharge))$total_rate += (float)$xml->CanparCharges->ExtraCareCharge;
					if(isset($xml->CanparCharges->DeclaredValueCharge))$total_rate += (float)$xml->CanparCharges->DeclaredValueCharge;
					
					
					$this->output[] = array(
						'name' => "Livraison",
						'rate' => (string)$total_rate,
						'deliveryDate' => (string)$ShippingDate,
					);
			} 
			
		}
		
		// close cURL resource, and free up system resources
		curl_close($ch);
    }
}
?>