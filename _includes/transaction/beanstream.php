<?php
include(dirname(__FILE__) . "/../config.php");
include(dirname(__FILE__) . "/../validate_session.php");
include_once(dirname(__FILE__).'/../classes/SC_Order.php');

// Array of Values to send
$arr_value = array();


// POST Values
$arr_value['trnCardOwner'] = trim($_POST['trnCardOwner']);
$arr_value['trnCardNumber'] = trim($_POST['trnCardNumber']);
$arr_value['trnExpMonth'] = trim($_POST['trnExpMonth']);
$arr_value['trnExpYear'] = trim($_POST['trnExpYear']);
$arr_value['trnCardCvd'] = trim($_POST['trnCardCvd']);
//$arr_value['trnAmount'] = trim($_POST['trnAmount']); // can't be greater than 1000 for test
$arr_value['trnAmount'] = $cart->grand_total; // can't be greater than 1000 for test

// Verify if one of the post field is empty...if yes return to step_payment.php
if ($arr_value['trnAmount'] > 0) {
	$erreur_empty_field = 0;
	$erreur_empty_field_string = '';
	foreach($arr_value as $key=>$value){
		if(!$value){
			$erreur_empty_field = 1;
		}
		// Verify if the field is credit card number or cvd number because we dont want to return the real value
		if(($key=='trnCardNumber' and $value) or ($key=='trnCardCvd' and $value)){
			$value = 1;
		}
		$erreur_empty_field_string .= '&' . $key . '=' . urlencode($value); 
	}
	
	if($erreur_empty_field){
		header('Location: /cart/step_payment?payment_method='.$_POST['payment_method'].'&SCtrnApproved=0&erreur_empty_field=1'.$erreur_empty_field_string);
		exit;
	}
}
// END Verify if one of the post field is empty...if yes return to step_payment.php
$arr_value['trnComments'] = trim($_POST['trnComments']);
$arr_value['payment_method'] = trim($_POST['payment_method']);

// END POST Values
//Payment Gateway
include(dirname(__FILE__) . "/current_payment_gateway.php");

$arr_value['requestType'] = 'BACKEND';
$arr_value['merchant_id'] = $available_payment_methods[0]['merchant_id'];

$arr_value['ordEmailAddress'] = $_SESSION['customer']['email'];
$arr_value['ordName'] = $cart->billing_firstname . ' ' . $cart->billing_lastname;
$arr_value['ordPhoneNumber'] = $cart->billing_telephone;
$arr_value['ordAddress1'] = $cart->billing_address;
$arr_value['ordAddress2'] = '';
$arr_value['ordCity'] = $cart->billing_city;
$arr_value['ordProvince'] = $cart->billing_state_code;
$arr_value['ordPostalCode'] = $cart->billing_zip;
$arr_value['ordCountry'] = $cart->billing_country_code;
	
// create order
$order = new SC_Order($mysqli);
$id_orders = $order->new_order($arr_value['trnComments'],$arr_value['payment_method']);
$id_orders = str_pad($id_orders,10,'0',STR_PAD_LEFT);

$arr_value['trnOrderNumber'] = $id_orders;

// for vbv and sc
$arr_value['termURL'] = 'http://'.$_SERVER['HTTP_HOST'].'/_includes/transaction/beanstream-auth.php?id_orders='.$id_orders;

// Array that contain all the value that we need to our Invoice
$arr_invoice = array();

if ($arr_value['trnAmount'] > 0) {	
	// Initialize curl
	$ch = curl_init();
	// Get curl to POST
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	// Instruct curl to suppress the output from Beanstream, and to directly
	// return the transfer instead. (Output will be stored in $txResult.)
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	// This is the location of the Beanstream payment gateway
	curl_setopt( $ch, CURLOPT_URL, "https://www.beanstream.com/scripts/process_transaction.asp" );
	// These are the transaction parameters that we will POST
	curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($arr_value, "", "&"));
	// Now POST the transaction. $txResult will contain Beanstream's response
	$txResult = curl_exec( $ch );
	
	// Array that contain transaction response
	$output = array();
	parse_str($txResult,$output);		
	
	//VARIABLE TO KEEP IF ERROR TO RETURN IN  PAYMENT PAGE
	$arr_invoice['SCtrnCardOwner'] = $arr_value['trnCardOwner'];
	$arr_invoice['SCtrnExpMonth'] = $arr_value['trnExpMonth'];
	$arr_invoice['SCtrnExpYear'] = $arr_value['trnExpYear'];
	
	// if vbv or sc
	if (strtolower($output['responseType']) == 'r') {
		// update transaction details
		$order->update_transaction_details(0,base64_encode(serialize($arr_invoice)));	
		
		echo $output['pageContents'];
		exit;	
	}	
	
	// ALL VARIABLE THAT THE RESPONSE RETURN
	
	//0 = Transaction refused, 1 = Transaction approved
	$arr_invoice['SCtrnApproved'] = $output['trnApproved'];
	
	//Unique id number used to identify an individual transaction. (10000012)
	$arr_invoice['SCtrnId'] = $output['trnId'];
	
	//The message id references a detailed approved/declined transaction response message. Review our gateway response message table for a full description of each message.
	$arr_invoice['SCmessageId'] = $output['messageId'];
	
	//This field will return a basic approved/declined message which may be displayed to the customer on a confirmation page. Review our gateway response message table for details.
	$arr_invoice['SCmessageText'] = $output['messageText'];
	
	//The value of trnOrderNumber submitted in the transaction request.
	$arr_invoice['SCtrnOrderNumber'] = $output['trnOrderNumber'];
	
	//If the transaction is approved this parameter will contain a unique bank-issued code.
	$arr_invoice['SCauthCode'] = $output['authCode'];
	
	//This field will return the value N, S, or U.
	$arr_invoice['SCerrorType'] = $output['errorType'];
	
	//In the case of a user generated error, this variable will include a list of fields that failed form validation. You will wish to notify the customer that they must correct these fields before the transaction can be completed.
	$arr_invoice['SCerrorFields'] = $output['errorFields'];
	
	//Set to the value of 'T' to indicate a transaction completion response. If VBV is enabled on the merchant account a value of 'R' may be returned to indicate a VBV redirection response.
	$arr_invoice['SCresponseType'] = $output['responseType'];
	
	//The amount of the transaction.
	$arr_invoice['SCtrnAmount'] = $output['trnAmount'];
	
	//The date of the transaction. (11/14/2011 1:22:37 PM)
	$arr_invoice['SCtrnDate'] = $output['trnDate'];
	
	//1 if the issuing bank has successfully processed an AVS check on the transaction. 0 if no AVS check has been performed.
	$arr_invoice['SCavsProcessed'] = $output['avsProcessed'];
	
	//An ID number referencing a specific AVS response message. Review Appendix A for details.
	$arr_invoice['SCavsId'] = $output['avsId'];
	
	//1 if AVS has been validated with both a match against address and a match against postal/ZIP code.
	$arr_invoice['SCavsResult'] = $output['avsResult'];
	
	//1 = Address match. The ordAddress1 parameter matches the address on file at the issuing bank. 0= Address mismatch. The address submitted with the order does not match information on file at the issuing bank.
	$arr_invoice['SCavsAddrMatch'] = $output['avsAddrMatch'];
	
	//1 if the ordPostalCode parameter matches the consumers address records at the issuing bank. 0 if the ordPostalCode parameter does not match the customer's address records or if AVS was not processed for the transaction.
	$arr_invoice['SCavsPostalMatch'] = $output['avsPostalMatch'];
	
	//Address Verification not performed for this transaction.
	$arr_invoice['SCavsMessage'] = $output['avsMessage'];
	
	//1=CVD Match 4=CVD Should have been present 2=CVD Mismatch 5=CVD Issuer unable to process request 3=CVD Not Verified 6=CVD Not Provided
	$arr_invoice['SCcvdId'] = $output['cvdId'];
	
	//The type of card used in the transaction. VI=Visa, MC=MasterCard, AM=American Express NN=Discover, DI=Diners, JB=JCB, IO=INTERAC Online, ET=Direct Debit/Direct Payments/ACH
	$arr_invoice['SCcardType'] = $output['cardType'];
	
	//The original value sent to indicate the type of transaction to perform (i.e. P,R,VP,VR, PA, PAC, Q).
	$arr_invoice['SCtrnType'] = $output['trnType'];
	
	//IO=INTERAC Online transaction CC=Credit Card transaction
	$arr_invoice['SCpaymentMethod'] = $output['paymentMethod'];
	
	//The value of the ref1 field submitted in the transaction request.
	$arr_invoice['SCref1'] = $output['ref1'];
	
	//The value of the ref2 field submitted in the transaction request.
	$arr_invoice['SCref2'] = $output['ref2'];
	
	//The value of the ref3 field submitted in the transaction request.
	$arr_invoice['SCref3'] = $output['ref3'];
	
	//The value of the ref4 field submitted in the transaction request.
	$arr_invoice['SCref4'] = $output['ref4'];
	
	//The value of the ref5 field submitted in the transaction request.
	$arr_invoice['SCref5'] = $output['ref5'];
	
	//Keep the payment gateway company name
	$arr_invoice['SCcompanyName'] = 'beanstream';
	
	// Build querystring to send to our Invoice
	/*
	$query_string = '';
	foreach($arr_invoice as $key=>$value){
		if(empty($query_string)){
			$query_string = '?';
		}else{
			$query_string .= '&';
		}
		$query_string .= $key.'='.$value;
	}
	curl_close( $ch );
	*/
	
	/*
	echo '<pre>';
	print_r($arr_invoice);
	echo '</pre>';
	exit;*/
} else {
	// amount is 0 so appprove
	$arr_invoice['SCtrnApproved'] = 1;
}

if(!$arr_invoice['SCtrnApproved']){
	// update transaction details
	$order->update_transaction_details(-1,base64_encode(serialize($arr_invoice)));	

	header('Location: /cart/error_payment?id_orders='.$id_orders);
	exit;
}else{
	$cart->empty_cart();

	// update transaction details
	$order->update_transaction_details(($config_site['enable_auto_completed_order']?7:1),base64_encode(serialize($arr_invoice)));
	// Load Order after Update
	$order->load($order->id);
	//Update the inventory
	$order->update_product_inventory();
	
	include_mailer();
				
	// send email to customer with activation link
	$mail = new PHPMailer(); // defaults to using php "mail()"
	$mail->CharSet = 'UTF-8';
	
	// text only
	//$mail->IsHTML(false);

	$mail->SetFrom($config_site['no_reply_email'], $config_site['site_name']);

	$mail->AddAddress($_SESSION['customer']['email']);
	
	$mail->Subject = language('transaction/all', 'TEXT_EMAIL_TITLE', array(0=>$order->id));
	
	$transaction_details = $order->transaction_details ? unserialize(base64_decode($order->transaction_details)):array();
	ob_start();
	$show_company_header = 0;
	include("email_receipt.php");
	$html_invoice = ob_get_clean();
	
	$mail->MsgHTML($html_invoice.get_company_signature(1));

	$sendmail_failed = $mail->Send() ? 0:1;
	
	// notification email
	if ($config_site['enable_order_email_notification'] && !empty($config_site['order_email_notification_email'])) {
		// send email to customer with activation link
		$mail = new PHPMailer(); // defaults to using php "mail()"
		$mail->CharSet = 'UTF-8';
		
		// text only
		//$mail->IsHTML(false);
	
		$mail->SetFrom($config_site['no_reply_email'], $config_site['site_name']);
	
		$mail->AddAddress($config_site['order_email_notification_email']);
		
		$mail->Subject = language('transaction/all', 'TEXT_EMAIL_ORDER_NOTIFICATION_TITLE');
		
		$mail->MsgHTML(language('transaction/all','EMAIL_ORDER_NOTIFICATION_CONTENT',array(0=>$order->id)).get_company_signature(1));
	
		$mail->Send();
	}
	
	header('Location: /cart/step_completed?id_orders='.$id_orders.'&sendmail='.$sendmail_failed);
	exit;
}
?>