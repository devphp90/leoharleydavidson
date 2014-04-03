<?php
class UPSShipping
{			
	public $url='https://wwwcie.ups.com/ups.app/xml/Rate';
	public $access_key;
	public $merchant_id;
	public $merchant_password;
	public $sender_city;
	public $sender_state_code;
	public $sender_country_code;
	public $sender_zip;
	public $products=array();		
	public $address=array();
	public $output=array();
	public $error=0;

	// constructor
	public function __construct($access_key,$merchant_id,$merchant_password,$sender_city,$sender_state_code,$sender_country_code,$sender_zip) {
		$this->access_key=$access_key;
		$this->merchant_id=$merchant_id;
		$this->merchant_password=$merchant_password;
		$this->sender_city=$sender_city;
		$this->sender_state_code=$sender_state_code;
		$this->sender_country_code=$sender_country_code;
		$this->sender_zip=$sender_zip;
	}

	function getServiceType($service_type)
	{
		
		// United States Domestic Shipments
		if ($this->sender_country_code == 'US' && $this->address['country_code'] == 'US') {
			switch ($service_type) {
				case '01': 
					return 'UPS Next Day Air';
					break;
				case '02':
					return 'UPS Second Day Air';
					break;
				case '03':
					return 'UPS Ground';
					break;
				case '12':
					return 'UPS Three-Day Select';
					break;
				case '13': 
					return 'UPS Next Day Air Saver';
					break;
				case '14':
					return 'UPS Next Day Air Early A.M. SM';
					break;
				case '59':
					return 'UPS Second Day Air A.M.';
					break;
				case '65':
					return 'UPS Saver';
					break;
			}		
		// Shipments Originating in United States
		} else if ($this->sender_country_code == 'US') {
			switch ($service_type) {
				case '01': 
					return 'UPS Next Day Air';
					break;
				case '02':
					return 'UPS Second Day Air';
					break;
				case '03':
					return 'UPS Ground';
					break;
				case '07':
					return 'UPS Worldwide ExpressSM';
					break;
				case '08':
					return 'UPS Worldwide ExpeditedSM';
					break;
				case '11':
					return 'UPS Standard';
					break;
				case '12':
					return 'UPS Three-Day Select';
					break;
				case '14':
					return 'UPS Next Day Air Early A.M. SM';
					break;
				case '54':
					return 'UPS Worldwide Express PlusSM';
					break;					
				case '59':
					return 'UPS Second Day Air A.M.';
					break;
				case '65':
					return 'UPS Saver';
					break;
			}				
		// Shipments Originating in Puerto Rico	
		} else if ($this->sender_country_code == 'PR') {
			switch ($service_type) {
				case '01':
					return 'UPS Next Day Air';
					break;
				case '02':
					return 'UPS Second Day Air';
					break;
				case '03':
					return 'UPS Ground';
					break;
				case '07':
					return 'UPS Worldwide ExpressSM';
					break;
				case '08':
					return 'UPS Worldwide ExpeditedSM';
					break;
				case '14':
					return 'UPS Next Day Air® Early A.M. SM';
					break;
				case '54':
					return 'UPS Worldwide Express PlusSM';
					break;
				case '65':
					return 'UPS Saver';
					break;
			}		
		// Shipments Originating in Canada
		} else if ($this->sender_country_code == 'CA') {
			switch ($service_type) {
				case '01':
					return 'UPS Express';
					break;
				case '02':
					return 'UPS ExpeditedSM';
					break;
				case '07':
					return 'UPS Worldwide ExpressSM';
					break;
				case '08':
					return 'UPS Worldwide ExpeditedSM';
					break;
				case '11':
					return 'UPS Standard';
					break;
				case '12':
					return 'UPS Three-Day Select';
					break;
				case '13':
					return 'UPS Saver SM';
					break;
				case '14':
					return 'UPS Express Early A.M. SM';
					break;
				case '54':
					return 'UPS Worldwide Express PlusSM';
					break;
				case '65':
					return 'UPS Saver';
					break;
			}				
		// Shipments Originating in Mexico
		} else if ($this->sender_country_code == 'MX') {
			switch ($service_type) {
				case '07':
					return 'UPS Express';
					break;
				case '08':
					return 'UPS ExpeditedSM';
					break;
				case '11':
					return 'UPS Standard';
					break;
				case '54':
					return 'UPS Express Plus';
					break;
				case '65':
					return 'UPS Saver';
					break;
			}							
		// Polish Domestic Shipments
		} else if ($this->sender_country_code == 'PL' && $this->address['country_code'] == 'PL') {
			switch ($service_type) {
				case '07':
					return 'UPS Express';
					break;
				case '08':
					return 'UPS ExpeditedSM';
					break;
				case '11':
					return 'UPS Standard';
					break;
				case '54':
					return 'UPS Worldwide Express';
					break;
				case '65':
					return 'UPS Saver';
					break;
				case '82':
					return 'UPS Today StandardSM';
					break;
				case '83':
					return 'UPS Today Dedicated CourrierSM';
					break;
				case '85':
					return 'UPS Today Express';
					break;
				case '86':
					return 'UPS Today Express Saver';
					break;
			}					
		// Shipments Originating in the European Union
		/*
			Belgium  (BE)  France  (FR)  Austria  (AT)  
			Bulgaria  (BG)  Italy  (IT)  Poland  (PL)  
			Czech Republic  (CZ)  Cyprus  (CY)  Portugal  (PT)  
			Denmark  (DK)  Latvia  (LV)  Romania  (RO)  
			Germany  (DE)  Lithuania  (LT)  Slovenia  (SI)  
			Estonia  (EE)  Luxembourg  (LU)  Slovakia  (SK)  
			Ireland  (IE)  Hungary  (HU)  Finland  (FI)  
			Greece  (EL)  Malta  (MT)  Sweden  (SE)  
			Spain  (ES)  Netherlands  (NL)  United Kingdom  (UK)  		
		*/
		} else if ($this->sender_country_code == 'BE' || 
				$this->sender['country_code'] == 'BG' || 
				$this->sender['country_code'] == 'CZ' || 
				$this->sender['country_code'] == 'DK' || 
				$this->sender['country_code'] == 'DE' || 
				$this->sender['country_code'] == 'EE' || 
				$this->sender['country_code'] == 'IE' || 
				$this->sender['country_code'] == 'EL' || 
				$this->sender['country_code'] == 'ES' || 
				$this->sender['country_code'] == 'FR' || 
				$this->sender['country_code'] == 'IT' || 
				$this->sender['country_code'] == 'CY' || 
				$this->sender['country_code'] == 'LV' || 
				$this->sender['country_code'] == 'LT' || 
				$this->sender['country_code'] == 'LU' || 
				$this->sender['country_code'] == 'HU' || 
				$this->sender['country_code'] == 'MT' || 
				$this->sender['country_code'] == 'NL' || 
				$this->sender['country_code'] == 'AT' || 
				$this->sender['country_code'] == 'PL' || 
				$this->sender['country_code'] == 'PT' || 
				$this->sender['country_code'] == 'RO' || 
				$this->sender['country_code'] == 'SI' || 
				$this->sender['country_code'] == 'SK' || 
				$this->sender['country_code'] == 'FI' || 
				$this->sender['country_code'] == 'SE' || 
				$this->sender['country_code'] == 'UK') {
			switch ($service_type) {
				case '07':
					return 'UPS Express';
					break;
				case '08':
					return 'UPS ExpeditedSM';
					break;
				case '11':
					return 'UPS Standard';
					break;
				case '54':
					return 'UPS Worldwide Express PlusSM';
					break;
				case '65':
					return 'UPS Saver';
					break;
			}				
		// Shipments Originating in Other Countries
		} else {
			switch ($service_type) {
				case '07':
					return 'UPS Express';
					break;
				case '08':
					return 'UPS Worldwide ExpeditedSM';
					break;
				case '11':
					return 'UPS Standard';
					break;
				case '54':
					return 'UPS Worldwide Express PlusSM';
					break;
				case '65':
					return 'UPS Saver';
					break;
			}				
		}
		
		switch ($service_type) {
			case 'TDCB':
				return 'Trade Direct Cross Border';
				break;
			case 'TDA':
				return 'Trade Direct Air';
				break;
			case 'TDO':
				return 'Trade Direct Ocean';
				break;
			case '308':
				return 'UPS Freight LTL';
				break;
			case '309':
				return 'UPS Freight LTL Guaranteed';
				break;
			case '310':
				return 'UPS Freight LTL Urgent';
				break;			
		}
	}	
	
    public function run()
    {		
		$xml = '<?xml version="1.0" encoding="utf-8"?>
<AccessRequest>
	<AccessLicenseNumber>'.$this->access_key.'</AccessLicenseNumber>
	<UserId>'.$this->merchant_id.'</UserId>
	<Password>'.$this->merchant_password.'</Password>
</AccessRequest>
<?xml version="1.0"?>
<RatingServiceSelectionRequest xml:lang="en-US">
  <Request>
    <TransactionReference>
      <CustomerContext>Rating and Service</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
	<RequestAction>Rate</RequestAction>
	<RequestOption>Shop</RequestOption>
  </Request>
  <PickupType>
	<Code>01</Code>
	<Description>Daily Pickup</Description>
  </PickupType>
  <Shipment>
	<Description>Rate Description</Description>
    <Shipper>
      <Address>
        <City>'.$this->sender_city.'</City>
        <StateProvinceCode>'.$this->sender_state_code.'</StateProvinceCode>
        <PostalCode>'.$this->sender_zip.'</PostalCode>
        <CountryCode>'.$this->sender_country_code.'</CountryCode>
      </Address>
    </Shipper>
    <ShipTo>
      <Address>
        <City>'.$this->address['city'].'</City>
		<StateProvinceCode>'.$this->address['state_code'].'</StateProvinceCode>
        <PostalCode>'.$this->address['zip'].'</PostalCode> 
        <CountryCode>'.$this->address['country_code'].'</CountryCode>
      </Address>
    </ShipTo>';

	foreach ($this->products as $row) {
		for ($i=0; $i<$row['qty']; ++$i) {								
			$xml .= '<Package>		
			<PackagingType>
				<Code>02</Code>
				<Description>Customer Supplied</Description>
			</PackagingType>			
			<PackageWeight>
				<UnitOfMeasurement>
				  <Code>'.strtoupper($row['weight_unit_symbol'].'s').'</Code>
				</UnitOfMeasurement>
				<Weight>'.$row['weight'].'</Weight>
			</PackageWeight>  
			<Dimensions>
				<UnitOfMeasurement>
				  <Code>'.strtoupper($row['measurement_unit_symbol']).'</Code>
				</UnitOfMeasurement>
				<Length>'.round($row['length']).'</Length>
				<Width>'.round($row['width']).'</Width>
				<Height>'.round($row['height']).'</Height>
			</Dimensions> 		
			</Package>';
		}
	}	
	
	$xml .= ' 
  </Shipment>
</RatingServiceSelectionRequest>';

//echo '<pre>'.$xml.'</pre>';
	
		// create a new cURL resource
		$ch = curl_init();
		
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $this->url);
		// curl_setopt($ch, CURLOPT_URL, 'https://onlinetools.ups.com/ups.app/xml/Rate');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);			
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		
		// grab URL and pass it to the browser
		if ($response = curl_exec($ch)) {
			//echo $response;
			$xml = new SimpleXMLElement($response);
			
			/*echo '<pre>';
			
			print_r($response);
			
			echo '</pre>';*/
			
			
			// errors
			if (isset($xml->Response->Error)) {
				$this->error = 1;
				/*$this->error = array(
					'error'=>(string)$xml->Response->Error->ErrorCode.' - '.(string)$xml->Response->Error->ErrorDescription,
				);*/
			// output
			} else if ($xml->Response->ResponseStatusCode == 1)  { 
			
				foreach ($xml->RatedShipment as $row) {
					if(!empty($row->GuaranteedDaysToDelivery)){
						$estimated_delivery = $row->GuaranteedDaysToDelivery . ' ' . language('cart/step_shipping', 'LABEL_DAYS');
					}else{
						$estimated_delivery = 'na';
					}
					$this->output[] = array(
						'name' => $this->getServiceType((string)$row->Service->Code),
						'rate' => (string)$row->TotalCharges->MonetaryValue,
						'deliveryDate' => (string)$estimated_delivery,
					);
					
				}
			} else {
				$this->output = array(
					'error'=>(string)$xml->Response->ResponseStatusCode.' - '.(string)$xml->Response->ResponseStatusDescription,
				);
			}
		}
		
		// close cURL resource, and free up system resources
		curl_close($ch);	
    }
}
?>