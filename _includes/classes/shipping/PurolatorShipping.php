<?php
class PurolatorShipping
{	
	public $billing_account_number='9999999999';
	public $merchant_id='83968a1efca9472984ccea91976c0b5f';
	public $merchant_password='+qf)TPwm';
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
		//'firstname'=>'Kheang Hok',
		//'lastname'=>'Chin',
		//'middlename'=>'',
		'city'=>'Gatineau',
		'state_code'=>'QC',
		'country_code'=>'CA',
		'zip'=>'J8Y5T4',
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
		'module_name'=>'Purolator',
	);
	
	// service availability
	public function createPWSSOAPClient_sa()
	{
	  $client = new SoapClient( dirname(__FILE__)."/wsdl/purolator/ServiceAvailabilityService.wsdl", 
								array	(
										'trace'			=>	true,
										'location'	=>	'https://devwebservices.purolator.com/PWS/V1/ServiceAvailability/ServiceAvailabilityService.asmx',
										//'location'	=>	"https://webservices.purolator.com/PWS/V1/ServiceAvailability/ServiceAvailabilityService.asmx",
										'uri'				=>	"http://purolator.com/pws/datatypes/v1",
										'login'			=>	$this->merchant_id,
										'password'	=>	$this->merchant_password,
									  )
							  );
	  //Define the SOAP Envelope Headers
	  $headers[] = new SoapHeader ( 'http://purolator.com/pws/datatypes/v1', 
									'RequestContext', 
									array (
											'Version'           =>  '1.2',
											'Language'          =>  'en',
											'GroupID'           =>  'xxx',
											'RequestReference'  =>  'Rating Example'
										  )
								  ); 
	  //Apply the SOAP Header to your client                            
	  $client->__setSoapHeaders($headers);
	
	  return $client;
	}
	
	// estimating service
	public function createPWSSOAPClient_es()
	{
	  /** Purpose : Creates a SOAP Client in Non-WSDL mode with the appropriate authentication and 
		*           header information
	  **/
	  //Set the parameters for the Non-WSDL mode SOAP communication with your Development/Production credentials
	  $client = new SoapClient( dirname(__FILE__)."/wsdl/purolator/EstimatingService.wsdl", 
								array	(
										'trace'			=>	true,
										'location'	=>	'https://devwebservices.purolator.com/PWS/V1/Estimating/EstimatingService.asmx',
										//'location'	=>	'https://webservices.purolator.com/PWS/V1/Estimating/EstimatingService.asmx',
										'uri'				=>	'http://purolator.com/pws/datatypes/v1',
										'login'			=>	$this->merchant_id,
										'password'	=>	$this->merchant_password,
									  )
							  );
	  //Define the SOAP Envelope Headers
	  $headers[] = new SoapHeader ( 'http://purolator.com/pws/datatypes/v1', 
									'RequestContext', 
									array (
											'Version'           =>  '1.3',
											'Language'          =>  'en',
											'GroupID'           =>  'xxx',
											'RequestReference'  =>  'Rating Example',
										  )
								  ); 
	  //Apply the SOAP Header to your client                            
	  $client->__setSoapHeaders($headers);
	
	  return $client;
	}
	
	function getServiceType($service_type)
	{
		switch ($service_type) {
			case 'PurolatorExpress9AM':
				return 'Purolator Express 9AM';
				break;
			case 'PurolatorExpress10:30AM':
				return 'Purolator Express 10:30AM';
				break;
			case 'PurolatorExpress':
				return 'Purolator Express';
				break;
			case 'PurolatorExpressEvening':
				return 'Purolator Express Evening';
				break;
			case 'PurolatorExpressEnvelope9AM':
				return 'Purolator Express Envelope 9AM';
				break;
			case 'PurolatorExpressEnvelope10:30AM':
				return 'Purolator Express Envelope 10:30AM';
				break;
			case 'PurolatorExpressEnvelope':
				return 'Purolator Express Envelope';				
				break;
			case 'PurolatorExpressEnvelopeEvening':
				return 'Purolator Express Envelope Evening';
				break;
			case 'PurolatorExpressPack9AM':
				return 'Purolator Express Pack 9AM';
				break;
			case 'PurolatorExpressPack10:30AM':
				return 'Purolator Express Pack 9AM';
				break;
			case 'PurolatorExpressPack':
				return 'Purolator Express Pack';
				break;
			case 'PurolatorExpressPackEvening':
				return 'Purolator Express Pack Evening';
				break;
			case 'PurolatorExpressBox9AM':
				return 'Purolator Express Box 9AM';
				break;
			case 'PurolatorExpressBox10:30AM':
				return 'Purolator Express Box 10:30AM';
				break;
			case 'PurolatorExpressBox':
				return 'Purolator Express Box';
				break;
			case 'PurolatorExpressBoxEvening':
				return 'Purolator Express Box Evening';
				break;
			case 'PurolatorGround':
				return 'Purolator Ground';
				break;
			case 'PurolatorGround9AM':
				return 'Purolator Ground 9AM';
				break;
			case 'PurolatorGround10:30AM':
				return 'Purolator Ground 10:30AM';
				break;
			case 'PurolatorGroundEvening':
				return 'Purolator Ground Evening';
				break;
			case 'PurolatorExpressU.S.':
				return 'Purolator Express U.S.';
				break;
			case 'PurolatorExpressU.S.9AM':
				return 'Purolator Express U.S. 9AM';
				break;
			case 'PurolatorExpressU.S.10:30AM':
				return 'Purolator Express U.S. 10:30AM';
				break;
			case 'PurolatorExpressU.S.12:00':
				return 'Purolator Express U.S. 12PM';
				break;
			case 'PurolatorExpressEnvelopeU.S.':
				return 'Purolator Express Envelope U.S.';
				break;
			case 'PurolatorExpressU.S.Envelope9AM':
				return 'Purolator Express Envelope U.S. 9AM';
				break;
			case 'PurolatorExpressU.S.Envelope10:30AM':
				return 'Purolator Express Envelope U.S. 10:30AM';
				break;
			case 'PurolatorExpressU.S.Envelope12:00':
				return 'Purolator Express Envelope U.S. 12PM';
				break;
			case 'PurolatorExpressPackU.S.':
				return 'Purolator Express Pack U.S.';
				break;
			case 'PurolatorExpressU.S.Pack9AM':
				return 'Purolator Express Pack U.S. 9AM';
				break;
			case 'PurolatorExpressU.S.Pack10:30AM':
				return 'Purolator Express Pack U.S. 10:30AM';
				break;
			case 'PurolatorExpressU.S.Pack12:00':
				return 'Purolator Express Pack U.S. 12PM';
				break;
			case 'PurolatorExpressBoxU.S.':
				return 'Purolator Express Box U.S.';
				break;
			case 'PurolatorExpressU.S.Box9AM':
				return 'Purolator Express Box 9AM';
				break;
			case 'PurolatorExpressU.S.Box10:30AM':
				return 'Purolator Express Box 10:30AM';
				break;
			case 'PurolatorExpressU.S.Box12:00':
				return 'Purolator Express Box U.S. 12PM';
				break;
			case 'PurolatorGroundU.S.':
				return 'Purolator Ground U.S.';
				break;
			case 'PurolatorExpressInternational':
				return 'Purolator Express International';
				break;
			case 'PurolatorExpressInternational9AM':
				return 'Purolator Express International 9AM';
				break;
			case 'PurolatorExpressInternational10:30AM':
				return 'Purolator Express International 10:30AM';
				break;
			case 'PurolatorExpressInternational12:00':
				return 'Purolator Express International 12PM';
				break;
			case 'PurolatorExpressEnvelopeInternational':
				return 'Purolator Express Envelope International';
				break;
			case 'PurolatorExpressInternationalEnvelope9AM':
				return 'Purolator Express Envelope International 9AM';
				break;
			case 'PurolatorExpressInternationalEnvelope10:30AM':
				return 'Purolator Express Envelope International 10:30AM';
				break;
			case 'PurolatorExpressInternationalEnvelope12:00':
				return 'Purolator Express Envelope International 12PM';
				break;
			case 'PurolatorExpressPackInternational':
				return 'Purolator Express Pack International';
				break;
			case 'PurolatorExpressInternationalPack9AM':
				return 'Purolator Express Pack International 9AM';
				break;
			case 'PurolatorExpressInternationalPack10:30AM':
				return 'Purolator Express Pack International 10:30AM';
				break;				
			case 'PurolatorExpressInternationalPack12:00':
				return 'Purolator Express Pack International 12PM';
				break;
			case 'PurolatorExpressBoxInternational':
				return 'Purolator Express Box International';
				break;
			case 'PurolatorExpressInternationalBox9AM':
				return 'Purolator Express Box International 9AM';
				break;
			case 'PurolatorExpressInternationalBox10:30AM':
				return 'Purolator Express Box International 10:30AM';
				break;
			case 'PurolatorExpressInternationalBox12:00':
				return 'Purolator Express Box International 12PM';
				break;
			case 'PurolatorGroundDistribution':
				return 'Purolator Ground Distribution';
				break;
		}
	}	
	
    public function run()
    {		
		/*
			1.	Validate that the sender and receiver addresses are valid (ValidateCityPostalCodeZip)
			2.	For the sender and receiver address, determine which services are available (GetServicesOptions)
			3.	Calculate the shipping cost(s) (GetFullEstimate		
		*/
		
		// service availability
		$client = $this->createPWSSOAPClient_sa();
		
		//Populate the Origin Information
		
		$request->Addresses->ShortAddress->City = $this->address['city'];
		$request->Addresses->ShortAddress->Province = $this->address['state_code'];
		$request->Addresses->ShortAddress->Country = $this->address['country_code'];
		$request->Addresses->ShortAddress->PostalCode = $this->address['zip'];    
		
		$request->Addresses->ShortAddress->City = '';
		$request->Addresses->ShortAddress->Province = '';
		$request->Addresses->ShortAddress->Country = '';
		$request->Addresses->ShortAddress->PostalCode = '';  
		
		//Execute the request and capture the response
		if ($response = $client->ValidateCityPostalCodeZip($request)) {
			// if we have errors
			if (sizeof($response->ResponseInformation->Errors) > 1) {
				foreach ($response->ResponseInformation->Errors as $error) {
					$this->output['error'] .= $error->Code.' - '.$error->Description.' '.$error->AdditionalInformation.'<br />';	
				}
			}
		}
		
		$service_id='';
		
		// if we dont have any errors
		// proceed to 2nd step, get services
		if (!$this->output['error']) {
			//Populate the Origin Information
			$request->SenderAddress->City = $this->sender['city'];
			$request->SenderAddress->Province = $this->sender['state_code'];
			$request->SenderAddress->Country = $this->sender['country_code'];
			$request->SenderAddress->PostalCode = $this->sender['zip'];    
			//Populate the Desination Information
			$request->ReceiverAddress->City = $this->address['city'];
			$request->ReceiverAddress->Province = $this->address['state_code'];
			$request->ReceiverAddress->Country = $this->address['country_code'];
			$request->ReceiverAddress->PostalCode = $this->address['zip'];  
			
			//Future Dated Shipments - YYYY-MM-DD format
			$request->ShipmentDate = date('Y-m-d',strtotime('+2 day',time()));
			
			//Populate the Payment Information
			$request->BillingAccountNumber = $this->billing_account_number;
			
			//Execute the request and capture the response
			if ($response = $client->GetServicesOptions($request)) {
				// if we have errors
				if (sizeof($response->ResponseInformation->Errors) > 1) {
					foreach ($response->ResponseInformation->Errors as $error) {
						$this->output['error'] .= $error->Code.' - '.$error->Description.' '.$error->AdditionalInformation.'<br />';	
					}
				} else {
					$service_id = $response->Services->Service[0]->ID;
				}
			}			
		}
		
		// if we don't have any errors
		// proceed to 3rd step, get estimates
		if (!$this->output['error']) {				
			// estimating service		
			$client = $this->createPWSSOAPClient_es();
			
			//Populate the Origin Information
			$request->Shipment->SenderInformation->Address->Name = $this->sender['firstname'].($this->sender['middlename'] ? ' '.$this->sender['middlename']:'').' '.$this->sender['lastname'];
			$request->Shipment->SenderInformation->Address->City = $this->sender['city'];
			$request->Shipment->SenderInformation->Address->Province = $this->sender['state_code'];
			$request->Shipment->SenderInformation->Address->Country = $this->sender['country_code'];
			$request->Shipment->SenderInformation->Address->PostalCode = $this->sender['zip']; 
	
			//Populate the Desination Information
			$request->Shipment->ReceiverInformation->Address->Name = $this->address['firstname'].($this->address['middlename'] ? ' '.$this->address['middlename']:'').' '.$this->address['lastname'];
			$request->Shipment->ReceiverInformation->Address->City = $this->address['city'];
			$request->Shipment->ReceiverInformation->Address->Province = $this->address['state_code'];
			$request->Shipment->ReceiverInformation->Address->Country = $this->address['country_code'];
			$request->Shipment->ReceiverInformation->Address->PostalCode = $this->address['zip'];
			
			//Future Dated Shipments - YYYY-MM-DD format
			$request->Shipment->ShipmentDate = date('Y-m-d',strtotime('+2 day',time()));
			
			//Populate the Package Information
			$i=0;
			foreach ($this->products as $row) {
				$request->Shipment->PackageInformation->TotalWeight->Value += round($row['weight']);		
							
				++$i;
			}	
			
			$request->Shipment->PackageInformation->TotalWeight->WeightUnit = "lb";
			$request->Shipment->PackageInformation->TotalPieces = 1;
			$request->Shipment->PackageInformation->ServiceID = $service_id;
			//Populate the Payment Information
			$request->Shipment->PaymentInformation->PaymentType = "Sender";
			$request->Shipment->PaymentInformation->BillingAccountNumber = $this->billing_account_number;
			$request->Shipment->PaymentInformation->SenderAccountNumber = $this->billing_account_number;
			//Populate the Pickup Information
			$request->Shipment->PickupInformation->PickupType = "DropOff";
			$request->ShowAlternativeServicesIndicator = "true";		
			
			//ResidentialSignatureDomestic
			if ($this->address['country_code'] == 'CA') {
				$request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair->ID = "ResidentialSignatureDomestic";
				$request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair->Value = "true";	
			}
			
			//echo '<pre>';
			
			//print_r($request);
			
			//echo '</pre>';
			
			//Execute the request and capture the response
			if ($response = $client->GetFullEstimate($request)) {
				
				//echo '<pre>';
				
				//print_r($response);
				
				//echo '</pre>';			
				
				if($response && $response->ShipmentEstimates->ShipmentEstimate)
				{
				  //Loop through each Service returned and display the ID and TotalPrice
				  foreach($response->ShipmentEstimates->ShipmentEstimate as $estimate)
				  {
						$this->output['options'][] = array(
							'name' => $this->getServiceType((string)$estimate->ServiceID),
							'rate' => (string)$estimate->BasePrice, // does not include surcharge and taxes
							'shippingDate' => (string)$estimate->ShipmentDate,
							'deliveryDate' => (string)$estimate->ExpectedDeliveryDate,
						);				
				  }
				} else {
					foreach ($response->ResponseInformation->Errors as $error) {
						$this->output['error'] .= $error->Code.' - '.$error->Description.'<br />'.$error->AdditionalInformation;	
					}			
				}	
			}				
		}		
    }
}
?>