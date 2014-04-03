<?php
include(dirname(__FILE__) . "/../config.php");
include(dirname(__FILE__) . "/../validate_session.php");
include_once(dirname(__FILE__).'/../classes/SC_Order.php');
require_once ("paypalfunctions.php");

$task = trim($_GET['task']);
$id_orders = trim($_GET['id_orders']);
$comments = trim($_POST['comments']);

$order = new SC_Order($mysqli);

if (!$id_orders) {
	// create order
	$id_orders = $order->new_order($comments,4);
	$id_orders = str_pad($id_orders,10,'0',STR_PAD_LEFT);	
} else {
	$order->load($id_orders);
}

if ($task == 'cancel') {
	// update transaction details
	$order->update_transaction_details(-1,base64_encode(serialize(array())));	

	header('Location: /cart/error_payment?id_orders='.$id_orders);
	exit;	
} else {
   // ==================================
	// PayPal Express Checkout Module
	// ==================================

	//'------------------------------------
	//' The paymentAmount is the total value of 
	//' the shopping cart, that was set 
	//' earlier in a session variable 
	//' by the shopping cart page
	//'------------------------------------
	$paymentAmount = $order->grand_total;

	//'------------------------------------
	//' When you integrate this code 
	//' set the variables below with 
	//' shipping address details 
	//' entered by the user on the 
	//' Shipping page.
	//'------------------------------------
	$shipToName = trim($order->shipping_firstname.' '.$order->shipping_lastname);
	$shipToStreet = $order->shipping_address;
	$shipToStreet2 = ''; //Leave it blank if there is no value
	$shipToCity = $order->shipping_city;
	$shipToState = $order->shipping_state_code;
	$shipToCountryCode = $order->shipping_country_code; // Please refer to the PayPal country codes in the API documentation
	$shipToZip = $order->shipping_zip;
	$phoneNum = $order->shipping_telephone;

	//'------------------------------------
	//' The currencyCodeType and paymentType 
	//' are set to the selections made on the Integration Assistant 
	//'------------------------------------
	$currencyCodeType = $config_site['currency'];
	$paymentType = "Sale";

	//'------------------------------------
	//' The returnURL is the location where buyers return to when a
	//' payment has been succesfully authorized.
	//'
	//' This is set to the value entered on the Integration Assistant 
	//'------------------------------------
	$returnURL = 'http://'.$_SERVER['HTTP_HOST'].'/cart/paypal_confirm?id_orders='.$id_orders;

	//'------------------------------------
	//' The cancelURL is the location buyers are sent to when they hit the
	//' cancel button during authorization of payment during the PayPal flow
	//'
	//' This is set to the value entered on the Integration Assistant 
	//'------------------------------------
	$cancelURL = 'http://'.$_SERVER['HTTP_HOST'].'/_includes/transaction/expresscheckout.php?id_orders='.$id_orders.'&task=cancel';

	//'------------------------------------
	//' Calls the SetExpressCheckout API call
	//'
	//' The CallMarkExpressCheckout function is defined in the file PayPalFunctions.php,
	//' it is included at the top of this file.
	//'-------------------------------------------------
	$resArray = CallMarkExpressCheckout ($paymentAmount, $currencyCodeType, $paymentType, $returnURL,
																			  $cancelURL, $order->local_pickup, $shipToName, $shipToStreet, $shipToCity, $shipToState,
																			  $shipToCountryCode, $shipToZip, $shipToStreet2, $phoneNum
	);

	$ack = strtoupper($resArray["ACK"]);
	if($ack=="SUCCESS" || $ack=="SUCCESSWITHWARNING")
	{
			$token = urldecode($resArray["TOKEN"]);
			$_SESSION['reshash']=$token;
			RedirectToPayPal ( $token );
	} 
	else  
	{
			//Display a user friendly Error on the page using any of the following error information returned by PayPal
			$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
			$ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
			$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
			$ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
			
			echo "SetExpressCheckout API call failed. ";
			echo "Detailed Error Message: " . $ErrorLongMsg;
			echo "Short Error Message: " . $ErrorShortMsg;
			echo "Error Code: " . $ErrorCode;
			echo "Error Severity Code: " . $ErrorSeverityCode;
	}
}
?>