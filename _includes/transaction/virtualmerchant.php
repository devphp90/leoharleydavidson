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

$arr_value['ssl_merchant_id'] = $available_payment_methods[0]['merchant_id'];
$arr_value['ssl_user_id'] = $available_payment_methods[0]['user_id'];
$arr_value['ssl_pin'] = $available_payment_methods[0]['pin'];
$arr_value['ssl_card_number'] = urlencode(trim($_POST['trnCardNumber']));
$arr_value['ssl_exp_date'] = urlencode(trim($_POST['trnExpMonth']).trim($_POST['trnExpYear']));
$arr_value['ssl_amount'] = urlencode($cart->grand_total);

$arr_value['ssl_cvv2cvc2'] = urlencode($_POST['trnCardCvd']);

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

$arr_value['ssl_invoice_number'] = $id_orders;




// Array that contain all the value that we need to our Invoice
$arr_invoice = array();

if ($arr_value['trnAmount'] > 0) {	
	$data_to_send = 'xmldata=<txn>
	<ssl_merchant_id>'.$arr_value['ssl_merchant_id'].'</ssl_merchant_id>
	<ssl_user_id>'.$arr_value['ssl_user_id'].'</ssl_user_id>
	<ssl_pin>'.$arr_value['ssl_pin'].'</ssl_pin>
	<ssl_test_mode>false</ssl_test_mode>
	<ssl_transaction_type>ccsale</ssl_transaction_type>
	<ssl_card_number>'.$arr_value['trnCardNumber'].'</ssl_card_number>
	<ssl_cvv2cvc2>'.$arr_value['ssl_cvv2cvc2'].'</ssl_cvv2cvc2>
	<ssl_exp_date>'.$arr_value['trnExpMonth'].$arr_value['trnExpYear'].'</ssl_exp_date>
	<ssl_amount>'.$arr_value['trnAmount'].'</ssl_amount>
	<ssl_invoice_number>'.$arr_value['ssl_invoice_number'].'</ssl_invoice_number>
	<ssl_first_name>'.$cart->billing_firstname .'</ssl_first_name>
	<ssl_last_name>'.$cart->billing_lastname .'</ssl_last_name>
	<ssl_email>'.$arr_value['ordEmailAddress'] .'</ssl_email>
	</txn>';

	// Initialize curl
	$ch = curl_init();
	// Get curl to POST
	curl_setopt( $ch, CURLOPT_POST, 1 );
	// Instruct curl to suppress the output from Beanstream, and to directly
	// return the transfer instead. (Output will be stored in $txResult.)
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	// This is the location of the payment gateway
	
	//TEST URL
	//curl_setopt( $ch, CURLOPT_URL, "https://demo.myvirtualmerchant.com/VirtualMerchantDemo/processxml.do" );
	
	// Production URL
	curl_setopt( $ch, CURLOPT_URL, "https://www.myvirtualmerchant.com/VirtualMerchant/processxml.do" );
	
	

	// These are the transaction parameters that we will POST
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_to_send);
	// Now POST the transaction. $txResult will contain Beanstream's response

	$txResult = curl_exec( $ch );
	
	
	// Array that contain transaction response
	$output = array();
	$xml = simplexml_load_string($txResult);
	$json = json_encode($xml);
	$output = json_decode($json,1);
	//echo '<pre>'.print_r($output,1).'</pre>';
	//exit;
	

	//VARIABLE TO KEEP IF ERROR TO RETURN IN  PAYMENT PAGE
	$arr_invoice['SCtrnCardOwner'] = $arr_value['trnCardOwner'];
	$arr_invoice['SCtrnExpMonth'] = $arr_value['trnExpMonth'];
	$arr_invoice['SCtrnExpYear'] = $arr_value['trnExpYear'];
		
	// ALL VARIABLE THAT THE RESPONSE RETURN
	
	//0 = Transaction refused, 1 = Transaction approved
	$arr_invoice['SCtrnApproved'] = ($output['ssl_result_message'] == 'APPROVED' || $output['ssl_result_message'] == 'APPROVAL') && !isset($output['errorCode']) ? 1:0;
	
	//Unique id number used to identify an individual transaction. (10000012)
	$arr_invoice['SCtrnId'] = $output['ssl_txn_id'];
	
	//The message id references a detailed approved/declined transaction response message. Review our gateway response message table for a full description of each message.
	$arr_invoice['SCmessageId'] = ($output['ssl_result_message'] == 'APPROVED' || $output['ssl_result_message'] == 'APPROVAL') && !isset($output['errorCode']) ? 1:3;
	
	//This field will return a basic approved/declined message which may be displayed to the customer on a confirmation page. Review our gateway response message table for details.
	$arr_invoice['SCmessageText'] = $output['ssl_result_message'];
	
	
	//If the transaction is approved this parameter will contain a unique bank-issued code.
	$arr_invoice['SCauthCode'] = $output['ssl_approval_code'];
	
	//This field will return the value N, S, or U.
	$arr_invoice['SCerrorType'] = $output['errorCode'];
	
	//In the case of a user generated error, this variable will include a list of fields that failed form validation. You will wish to notify the customer that they must correct these fields before the transaction can be completed.
	$arr_invoice['SCerrorFields'] = $output['errorName'];
	
	//Set to the value of 'T' to indicate a transaction completion response. If VBV is enabled on the merchant account a value of 'R' may be returned to indicate a VBV redirection response.
	//$arr_invoice['SCresponseType'] = $output['responseType'];
	
	//The amount of the transaction.
	$arr_invoice['SCtrnAmount'] = $output['ssl_amount'];
	
	//The date of the transaction. (11/14/2011 1:22:37 PM)
	$arr_invoice['SCtrnDate'] = $output['ssl_txn_time'];
	
	//1 if the issuing bank has successfully processed an AVS check on the transaction. 0 if no AVS check has been performed.
	//$arr_invoice['SCavsProcessed'] = $output['avsProcessed'];
	
	//An ID number referencing a specific AVS response message. Review Appendix A for details.
	//$arr_invoice['SCavsId'] = $output['avsId'];
	
	//1 if AVS has been validated with both a match against address and a match against postal/ZIP code.
	$arr_invoice['SCavsResult'] = $output['ssl_avs_response'];
	
	//1 = Address match. The ordAddress1 parameter matches the address on file at the issuing bank. 0= Address mismatch. The address submitted with the order does not match information on file at the issuing bank.
	//$arr_invoice['SCavsAddrMatch'] = $output['avsAddrMatch'];
	
	//1 if the ordPostalCode parameter matches the consumers address records at the issuing bank. 0 if the ordPostalCode parameter does not match the customer's address records or if AVS was not processed for the transaction.
	//$arr_invoice['SCavsPostalMatch'] = $output['avsPostalMatch'];
	
	//Address Verification not performed for this transaction.
	//$arr_invoice['SCavsMessage'] = $output['avsMessage'];
	
	//1=CVD Match 4=CVD Should have been present 2=CVD Mismatch 5=CVD Issuer unable to process request 3=CVD Not Verified 6=CVD Not Provided
	$arr_invoice['SCcvdId'] = $output['ssl_cvv2_response'];
	
	//IO=INTERAC Online transaction CC=Credit Card transaction
	//$arr_invoice['SCpaymentMethod'] = $output['paymentMethod'];
	$arr_invoice['SCpaymentMethod'] = 'CC';
	
	
	//Keep the payment gateway company name
	$arr_invoice['SCcompanyName'] = 'virtualmerchant';
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