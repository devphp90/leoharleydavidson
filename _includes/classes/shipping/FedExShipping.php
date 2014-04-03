<?php
class FedExShipping
{	
	//https://gatewaybeta.fedex.com
	
	public $account_number='510087585';
	public $meter_number='118527899';
	public $merchant_id='Ra8hEtV8XofqlYiO';
	public $merchant_password='t6oDriPoMH5oeg9aIFevUWEaa';
	public $language='en';

	public $products=array();
	/*
	$products=array('
		0 => array(
			'id' => 0,
			'qty' => 0,
			'name' => 'product name and or description',
			'weight' => 0,
			'weight_unit_symbol' => 'lb or kg',
			'length' => 0,
			'width' => 0,
			'height' => 0,
			'dimension_unit_symbol' => 'in or cm',
		),	
	');	
	*/	
	
	public $unit_conversion=array(
		'kg_to_lb' => 2.20462262,
	);
	
	public $sender=array(
		//'firstname'=>'',
		//'lastname'=>'',
		//'middlename'=>'',
		//'city'=>'',
		'state_code'=>'QC',
		'country_code'=>'CA',
		'zip'=>'J8X1P8',
	);	
	
	public $address=array(
		'firstname'=>'',
		'lastname'=>'',
		'middlename'=>'',
		'city'=>'',
		'state_code'=>'',
		'country_code'=>'',
		'zip'=>'',
	);		
	
	public $output=array(
	);
	
	// constructor
	public function __construct($account_number, $merchant_id, $merchant_password, $meter_number, $state, $country, $zip) {
		$this->account_number=$account_number;
		$this->merchant_id=$merchant_id;
		$this->merchant_password=$merchant_password;
		$this->meter_number=$meter_number;
		
		$this->sender['state_code']=$state;
		$this->sender['country_code']=$country;
		$this->sender['zip']=$zip;
	}	
		

	function printNotifications($notes){
		$errors = '';
		foreach($notes as $noteKey => $note){
			$errors .= $note->Code . ' - ' . $note->Message . '<br />';
		}
		
		return $errors;
	}	
	
	function getServiceType($service_type)
	{
		switch ($service_type) {
			case 'EUROPE_FIRST_INTERNATIONAL_PRIORITY':
				return 'Europe First International Priority';
				break;
			case 'FEDEX_1_DAY_FREIGHT':
				return 'FedEx 1 Day Freight';
				break;
			case 'FEDEX_2_DAY':
				return 'FedEx 2 Day';
				break;
			case 'FEDEX_2_DAY_FREIGHT':
				return 'FedEx 2 Day Freight';
				break;
			case 'FEDEX_3_DAY_FREIGHT':
				return 'FedEx 3 Day Freight';
				break;
			case 'FEDEX_EXPRESS_SAVER':
				return 'FedEx Express Saver';
				break;
			case 'FEDEX_FREIGHT':
				return 'FedEx Freight';
				break;
			case 'FEDEX_GROUND':
				return 'FedEx Ground';
				break;
			case 'FEDEX_NATIONAL_FREIGHT':
				return 'FedEx National Freight';
				break;
			case 'FIRST_OVERNIGHT':
				return 'First Overnight';
				break;
			case 'GROUND_HOME_DELIVERY':
				return 'Ground Home Delivery';
				break;
			case 'INTERNATIONAL_ECONOMY':
				return 'FedEx International Economy';
				break;
			case 'INTERNATIONAL_ECONOMY_FREIGHT':
				return 'International Economy Freight';
				break;
			case 'INTERNATIONAL_FIRST':
				return 'FedEx International First';
				break;
			case 'INTERNATIONAL_GROUND':
				return 'FedEx International Ground';
				break;
			case 'INTERNATIONAL_PRIORITY':
				return 'International Priority';
				break;
			case 'INTERNATIONAL_PRIORITY_FREIGHT':
				return 'International Priority Freight';
				break;
			case 'PRIORITY_OVERNIGHT':
				return 'Priority Overnight';
				break;
			case 'SMART_POST':
				return 'Smart Post';
				break;
			case 'STANDARD_OVERNIGHT':
				return 'Standard Overnight';
				break;
		}
	}
	
    public function run()
    {		
		$client = new SoapClient('/var/www/html/includes/wsdl/fedex/RateService_v13.wsdl', array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information
		//$client = new SoapClient(dirname(__FILE__).'/wsdl/fedex/RateService_v13.wsdl', array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information
		
		$request['WebAuthenticationDetail'] = array(
			'UserCredential' => array(
				'Key' => $this->merchant_id, 
				'Password' => $this->merchant_password,
			)
		); 
		$request['ClientDetail'] = array(
			'AccountNumber' => $this->account_number, 
			'MeterNumber' => $this->meter_number,
		);
		$request['TransactionDetail'] = array(
			'CustomerTransactionId' => ' *** Rate Request v13 using PHP ***',
			'Localization' => array(
				'LanguageCode' => strtoupper($this->language),			
			),
		);
		$request['Version'] = array(
			'ServiceId' => 'crs', 
			'Major' => '13', 
			'Intermediate' => '0', 
			'Minor' => '0',
		);
		$request['ReturnTransitAndCommit'] = true;
		$request['RequestedShipment']['DropoffType'] = 'DROP_BOX'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
		$request['RequestedShipment']['ShipTimestamp'] = date('c',strtotime('+2 day',time()));
		//$request['RequestedShipment']['TotalInsuredValue']=array('Ammount'=>100,'Currency'=>'USD');
		$request['RequestedShipment']['Shipper'] = array(
			'Address' => array(
			//	'City'=>$this->sender['city'],
				'StateOrProvinceCode'=>$this->sender['state_code'],
				'PostalCode'=>$this->sender['zip'],
				'CountryCode'=>$this->sender['country_code'],
			),
		);
		$request['RequestedShipment']['Recipient'] = array(
			'Address' => array(
				'City'=>$this->address['city'],
				'StateOrProvinceCode'=>$this->address['state_code'],
				'PostalCode'=>$this->address['zip'],
				'CountryCode'=>$this->address['country_code'],
			)
		);
		
		$request['RequestedShipment']['CurrencyType'] = 'CAD';
		
		/*
		$request['RequestedShipment']['ShippingChargesPayment'] = array(
			'PaymentType' => 'SENDER',
			'Payor' => array(
				'AccountNumber' => $this->account_number,
				'CountryCode' =>'CA'
			),
		);*/
																		 
		$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; 
		//$request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
		//$request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGE';  //  Or PACKAGE_SUMMARY
		
		$x=0;
		$i=0;
		foreach ($this->products as $row) {		
			$i += $row['qty'];	
//			for ($i=0; $i<$row['qty']; ++$i) {							
				$request['RequestedShipment']['RequestedPackageLineItems'][] = array(
					'SequenceNumber' => $i+1,
					'GroupPackageCount' => $row['qty'],
					'Weight' => array(
						'Value' => round($row['weight']),
						'Units' => strtoupper($row['weight_unit_symbol']),
					),
					'Dimensions' => array(
						'Length' => round($row['length']),
						'Width' => round($row['width']),
						'Height' => round($row['height']),
						'Units' => strtoupper($row['measurement_unit_symbol']),
					),
				);				
//			}
		}
		
		$request['RequestedShipment']['PackageCount'] = $i;
				
		//echo '------- FEDEX ---------';		
																										
		try 
		{
			//echo '<pre>'.print_r($request,1).'</pre>';
			
			$response = $client->getRates($request);
			
			//echo '<pre>'.print_r($response,1).'</pre>';
			
		
		/*echo '<pre>';
		echo 'Request : <br/><xmp>', 
		  $client->__getLastRequest(), 
		  '</xmp>';
		
		echo 'test';
		echo '</pre>';*/
		
				
			if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR')
			{  	
				/*echo '<pre>';
				
				print_r($response);
				
				//echo 'test '.sizeof($response->RateReplyDetails);
				
				echo '</pre>';*/
				
				//echo '------- FEDEX ---------';
				
				if (sizeof($response->RateReplyDetails) > 1) {
					foreach ($response->RateReplyDetails as $rateReply) {   
						$rates = isset($rateReply->RatedShipmentDetails->ShipmentRateDetail) ? $rateReply->RatedShipmentDetails->ShipmentRateDetail:$rateReply->RatedShipmentDetails[1]->ShipmentRateDetail;
						
						$deliveryDate = (string)$rateReply->DeliveryTimestamp;
						if ($deliveryDate) { $deliveryDate = date('Y-m-d H:i',strtotime($deliveryDate)); }
						
						$this->output[] = array(
							'name' => $this->getServiceType((string)$rateReply->ServiceType),
							'rate' => (string)$rates->TotalBaseCharge->Amount, // does not include surcharge and taxes
						//	'shippingDate' => (string),
							'deliveryDate' => $deliveryDate,
						);								
					}
				} else {
						$rates = isset($response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail) ? $response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail:$response->RateReplyDetails->RatedShipmentDetails[1]->ShipmentRateDetail;
						
						//$deliveryDate = (string)(isset($response->RateReplyDetails->DeliveryTimestamp) ? $response->RateReplyDetails->DeliveryTimestamp:$response->RateReplyDetails->TransitTime);
						
						$deliveryDate = (string)$response->RateReplyDetails->DeliveryTimestamp;
						if ($deliveryDate) { $deliveryDate = date('Y-m-d H:i',strtotime($deliveryDate)); }
						
												
						$this->output[] = array(
							'name' => $this->getServiceType((string)$response->RateReplyDetails->ServiceType),
							'rate' => (string)$rates->TotalBaseCharge->Amount, // does not include surcharge and taxes
						//	'shippingDate' => (string),
							'deliveryDate' => $deliveryDate,
						);	
				}
									
				/*
				echo '<table border="1">';
				echo '<tr><td>Service Type</td><td>Amount</td><td>Delivery Date</td></tr><tr>';
				$serviceType = '<td>'.$rateReply -> ServiceType . '</td>';
				$amount = '<td>$' . number_format($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount,2,".",",") . '</td>';
				if(array_key_exists('DeliveryTimestamp',$rateReply)){
					$deliveryDate= '<td>' . $rateReply->DeliveryTimestamp . '</td>';
				}else if(array_key_exists('TransitTime',$rateReply)){
					$deliveryDate= '<td>' . $rateReply->TransitTime . '</td>';
				}else {
					$deliveryDate='';
				}
				echo $serviceType . $amount. $deliveryDate;
				echo '</tr>';
				echo '</table>';*/
			}
			else
			{
				return $this->output['error'] =  $this->printNotifications($response->Notifications);
			} 
		} catch (SoapFault $exception) {
			return $this->output['error'] = $exception->faultcode.' - '.$exception->faultstring.'<br /><pre>'.$client->__getLastResponse().'</pre>';;
		}		
    }
}
?>