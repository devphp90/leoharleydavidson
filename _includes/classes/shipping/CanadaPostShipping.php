<?php
class CanadaPostShipping
{	
	public $url='http://sellonline.canadapost.ca:30000';
	public $merchant_id='CPC_SEBCHINPETE_INC';
	public $products=array();		
	public $address=array();
	public $output=array();
	public $error=0;

	// constructor
	public function __construct($merchant_id) {
		$this->merchant_id=$merchant_id;
	}	
	
    public function run()
    {		
		$xml = '<?xml version="1.0" ?>
<!-- 
***********************************************************************
This file is an example of XML file sent to the Sell Online server
to get the shipping rates and delivery dates of a shopping bag.

It is sent to Sell Online by opening a TCP socket port on:
     IP address : sellonline.canadapost.ca 
     TCP Port : 30000

You can use an HTTP POST and submit this XML document to the server
(same IP and same TCP Port). We highly recommand you to open this url 
to see an interactive example of the HTTP POST: 
	http://sellonline.canadapost.ca/DevelopersResources/protocolV3/HTTPInterface.html

Note: The address below is for test purpose. If your web site 
      is going live, please contact sellonline@canadapost.ca to get the
      IP address of our production server (or look at the FAQ)
***********************************************************************
-->
<!DOCTYPE eparcel SYSTEM "eParcel.dtd" >
<eparcel>
   <!-- 
   ************************************ 
   * Language of choice :
   *      en=ENGLISH
   *      fr=FRENCH
   * Note: This parameter is OPTIONAL (english = default)
   ************************************ -->
   <language>'.$_SESSION['customer']['language'].'</language>

   <ratesAndServicesRequest>
      <!-- 
      ************************************ 
      * Merchant ID assigned by Canada Post
      * If you don\'t have one, send a request
      * to sellonline@canadapost.ca
      * Note: The merchant ID is used by the Sell Online
      * server to retrieve the :
      *      - list of boxes used to pack the items
      *      - origin postal code 
      *      - ...
      ************************************ -->
      <merchantCPCID>'.$this->merchant_id.'</merchantCPCID>
		<!-- 
		  **********************************
		  From Postal Code.
		  This parameter will overwrite the one 
		  defined in the merchant\'s profile
		  Note: This parameter is OPTIONAL
		  **********************************
		<fromPostalCode></fromPostalCode>  
		--> 	  
		 		
		<!-- 
		  **********************************
		  Turnaroundtime in hours
		  If declared here, this parameter will 
		  overwrite the one 
		  defined in the merchant\'s profile
		  Note: This parameter is OPTIONAL
		  **********************************
		<turnAroundTime></turnAroundTime> 
		--> 
			
		     
		<!-- 
		
		  ************************************ 
		  Total price of the items in this request.
		  Price is in CA$ and should not include 
		  taxes.
		  The items price is used to calculate
		  the insurance and signature fees.
		  Note: This parameter is OPTIONAL
		  ************************************ 
		<itemsPrice></itemsPrice>
		--> 		      

      <!-- 
      ************************************ 
      * Insert here the items in the shopping basket
      * The example below show 2 different items
      * Each item has the following attributes:
      *         - Quantity
      *         - Weight in kg
      *         - Length, Width and Height in cm
      *         - Description
      ************************************ 
      -->
      <lineItems>';
	  
		foreach ($this->products as $row) {		
			$xml .= '<item>
			<quantity>'.$row['qty'].'</quantity>
			<weight>'.$row['weight'].'</weight>
			<length>'.$row['length'].'</length>
			<width>'.$row['width'].'</width>
			<height>'.$row['height'].'</height>
			<description>'.$row['name'].'</description>
			</item>';
		}
		
	  $xml .= ' 
      </lineItems>

      <!-- 
      ************************************ 
      Address of destination

      (*) For a canadian destination, \'country\' and \'postalCode\' have 
      to be valid (\'city\', \'provOrState\' are not used).

      (*) For a US destination, \'country\' and \'provOrState\' have 
      to be valid. (\'city\', \'postalCode\' are not used for now but zip
      code will be used in the future). State can be either full state 
      name or state code.

      (*) For an International destination, only \'country\' has
      to be valid (\'city\', \'postalCode\' and \'provOrState\' are not used).

      Country can be fullname or 2-letters country code (ISO 3166)		
      ************************************ 
      -->
      <city>'.$this->address['city'].'</city>
      <provOrState>'.$this->address['state_code'].'</provOrState>
      <country>'.$this->address['country_code'].'</country>
      <postalCode>'.$this->address['zip'].'</postalCode>

   </ratesAndServicesRequest>

</eparcel>';

//echo '<pre>'.$xml.'</pre>';
		
		// POSTE CANADA
		// create a new cURL resource
		$ch = curl_init();
		
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);			
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('XMLRequest'=>$xml));
		
		// grab URL and pass it to the browser
		if ($response = curl_exec($ch)) {
			$xml = new SimpleXMLElement($response);
			//echo $response . '<br />';
			//echo $xml->error->statusCode;
			//$this->output['source'] = $xml;
			
			// if there is an error
			if (isset($xml->error)) {
				$this->error = 1;
				/*$this->error = array(
					'error'=>(string)$xml->error->statusCode.' - '.(string)$xml->error->statusMessage,
				);*/
			// output
			} else if (isset($xml->ratesAndServicesResponse->statusCode) && $xml->ratesAndServicesResponse->statusCode == 1)  { 
				
				foreach ($xml->ratesAndServicesResponse->product as $row) {

					$this->output[] = array(
						'name' => (string)$row->name,
						'rate' => (string)((float)$row->rate+(float)$xml->ratesAndServicesResponse->handling),
						'deliveryDate' => (string)$row->deliveryDate,
					);
				}
			} else {
				$this->output = array(
					'error'=>(string)$xml->ratesAndServicesResponse->statusCode.' - '.(string)$xml->ratesAndServicesResponse->statusMessage,
				);
			}
			
		}
		
		// close cURL resource, and free up system resources
		curl_close($ch);
    }
}
?>