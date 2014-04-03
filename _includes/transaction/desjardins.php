<?php
include(dirname(__FILE__) . "/../config.php");
include(dirname(__FILE__) . "/../validate_session.php");
include_once(dirname(__FILE__).'/../classes/SC_Order.php');
//Payment Gateway
include(dirname(__FILE__) . "/current_payment_gateway.php");

$id_orders = trim($_GET['id_orders']);

// 3d secure response post
if (isset($_GET['task']) && $_GET['task'] == 'secure3d_response') {		
	$PaRes = trim($_POST['PaRes']);
	$MD = trim($_POST['MD']);
	$arr_value = $_SESSION['trans_data'][$id_orders];	
	$confirmationNumber = trim($arr_value['confirmationNumber']);
	
	if (!empty($PaRes) && !empty($confirmationNumber)) {
		// send authentication request
		//$client = new SoapClient('https://webservices.test.optimalpayments.com/creditcardWS/CreditCardService/v1?wsdl', array('trace' => 1));
		$client = new SoapClient('https://webservices.optimalpayments.com/creditcardWS/CreditCardService/v1?wsdl', array('trace' => 1));
		$request = array();
		$request['ccAuthenticateRequestV1'] = array(
			'merchantAccount' => array(
				'accountNum' => $arr_value['merchant_id'],
				'storeID' => $arr_value['user_id'],
				'storePwd' => $arr_value['pin'],
			),		
			'confirmationNumber' => $confirmationNumber,
			'paymentResponse' => $PaRes,
		);
		
		$response = $client->ccTDSAuthenticate($request);
		
		if ($response->ccTxnResponseV1->decision == 'ACCEPTED' && $response->ccTxnResponseV1->code == 0) {
			if ($response->ccTxnResponseV1->tdsAuthenticateResponse->status == 'Y') {
				$_SESSION['trans_data'][$id_orders]['authentication'] = array(
					'approved' => 1,
					'indicator' => $response->ccTxnResponseV1->tdsAuthenticateResponse->eci,
					'cavv'=> $response->ccTxnResponseV1->tdsAuthenticateResponse->cavv,
					'xid' => $response->ccTxnResponseV1->tdsAuthenticateResponse->xid,
				);
			} else $_SESSION['trans_data'][$id_orders]['authentication']['approved'] = 0;
		} else $_SESSION['trans_data'][$id_orders]['authentication']['approved'] = 0;
	} else $_SESSION['trans_data'][$id_orders]['authentication']['approved'] = 0;
}

// Array of Values to send
$arr_value = array();

// Array that contain all the value that we need to our Invoice
$arr_invoice = array();

if ($id_orders && isset($_SESSION['trans_data'][$id_orders])) $arr_value = $_SESSION['trans_data'][$id_orders];
else {	
	// POST Values
	$arr_value['trnCardOwner'] = trim($_POST['trnCardOwner']);
	$arr_value['trnCardNumber'] = trim($_POST['trnCardNumber']);
	$arr_value['trnExpMonth'] = trim($_POST['trnExpMonth']);
	$arr_value['trnExpYear'] = trim($_POST['trnExpYear']);
	$arr_value['trnCardCvd'] = trim($_POST['trnCardCvd']);
	if(isset($_POST['trnCardtype'])){
		$arr_value['trnCardtype'] = trim($_POST['trnCardtype']);//You Must verify in the payment page if select card name is present
	}
		
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

	$arr_value['requestType'] = 'BACKEND';
	$arr_value['merchant_id'] = $available_payment_methods[0]['merchant_id'];
	$arr_value['user_id'] = $available_payment_methods[0]['user_id'];
	$arr_value['pin'] = $available_payment_methods[0]['pin'];
	
	$arr_value['ordEmailAddress'] = $_SESSION['customer']['email'];
	$arr_value['ordFirstName'] = $cart->billing_firstname;
	$arr_value['ordLastName'] = $cart->billing_lastname;
	$arr_value['ordName'] = $cart->billing_firstname . ' ' . $cart->billing_lastname;
	$arr_value['ordPhoneNumber'] = $cart->billing_telephone;
	$arr_value['ordAddress1'] = $cart->billing_address;
	$arr_value['ordAddress2'] = '';
	$arr_value['ordCity'] = $cart->billing_city;
	$arr_value['ordProvince'] = $cart->billing_state_code;
	$arr_value['ordPostalCode'] = $cart->billing_zip;
	$arr_value['ordCountry'] = $cart->billing_country_code;
}
	
//VARIABLE TO KEEP IF ERROR TO RETURN IN  PAYMENT PAGE
$arr_invoice['SCtrnCardOwner'] = $arr_value['trnCardOwner'];
$arr_invoice['SCtrnExpMonth'] = $arr_value['trnExpMonth'];
$arr_invoice['SCtrnExpYear'] = $arr_value['trnExpYear'];
	
// create order
$order = new SC_Order($mysqli);
if ($id_orders) {
	if (!$order->load($id_orders)) throw new Exception('An error occured while loading order.'); 
} else {
	$id_orders = $order->new_order($arr_value['trnComments'],$arr_value['payment_method']);
	$id_orders = str_pad($id_orders,10,'0',STR_PAD_LEFT);

	$arr_value['trnOrderNumber'] = $id_orders;

	// for vbv and sc
	$arr_value['termURL'] = 'http://'.$_SERVER['HTTP_HOST'].'/_includes/transaction/desjardins.php?id_orders='.$id_orders.'&task=secure3d_response';

	$_SESSION['trans_data'][$id_orders] = $arr_value;
}

if ($arr_value['trnAmount'] > 0) {	
	if (!isset($arr_value['authentication'])) {
	
		//$client = new SoapClient('https://webservices.test.optimalpayments.com/creditcardWS/CreditCardService/v1?wsdl', array('trace' => 1));
		$client = new SoapClient('https://webservices.optimalpayments.com/creditcardWS/CreditCardService/v1?wsdl', array('trace' => 1));
		
		// Enrollment lookup request, check if 3d secure card
		$request = array();
		$request['ccEnrollmentLookupRequestV1'] = array(
			'merchantAccount' => array(
				'accountNum' => $arr_value['merchant_id'],
				'storeID' => $arr_value['user_id'],
				'storePwd' => $arr_value['pin'],
			),
			'merchantRefNum' => $arr_value['trnOrderNumber'],	
			'amount' => $arr_value['trnAmount'],
			'card' => array(
				'cardNum' => $arr_value['trnCardNumber'],
				'cardExpiry' => array(
					'month' => $arr_value['trnExpMonth'],
					'year' => $arr_value['trnExpYear'],			
				),
	//			'cardType' => 'VI',
				'cardTypeSpecified' => false,
				'cvdIndicator' => 1,
				'cvdIndicatorSpecified' => true,
				'cvd' => $arr_value['trnCardCvd'],
			),	
		);
		
		try 
		{
			//echo '<pre>'.print_r($request,1).'</pre>';
			
			$response = $client->ccTDSLookup($request);	
		
			if ($response->ccTxnResponseV1->decision == 'ACCEPTED' && $response->ccTxnResponseV1->code == 0) {
				if ($response->ccTxnResponseV1->tdsResponse->enrollmentStatus == 'Y') {
					$_SESSION['trans_data'][$id_orders]['confirmationNumber'] = $response->ccTxnResponseV1->confirmationNumber;
					
					// update transaction details
					$order->update_transaction_details(0,base64_encode(serialize($arr_invoice)));					
		?>
	<html>
	<body onLoad="document.frmLaunch.submit();">
	<form name="frmLaunch" method="POST" action="<?php echo $response->ccTxnResponseV1->tdsResponse->acsURL; ?>">
	<input type="hidden" name="PaReq" value="<?php echo $response->ccTxnResponseV1->tdsResponse->paymentRequest; ?>" />
	<input type="hidden" name="TermUrl" value="<?php echo $arr_value['termURL']; ?>" />
	<input type="hidden" name="MD" value="<?php echo $arr_value['trnOrderNumber']; ?>" />
	</form>
	</body>
	</html>
		<?php		
					exit;
				}
			} 		
		} catch (SoapFault $exception) {
			echo $exception->faultcode.' - '.$exception->faultstring.'<br /><pre>'.$client->__getLastResponse().'</pre>';;
		}	
	}
	
	// proceed with purchase
	//$client = new SoapClient('https://webservices.test.optimalpayments.com/creditcardWS/CreditCardService/v1?wsdl', array('trace' => 1));
	$client = new SoapClient('https://webservices.optimalpayments.com/creditcardWS/CreditCardService/v1?wsdl', array('trace' => 1));

	// proceed with pruchase
	$request = array();
	$request['ccAuthRequestV1'] = array(
		'merchantAccount' => array(
			'accountNum' => $arr_value['merchant_id'],
			'storeID' => $arr_value['user_id'],
			'storePwd' => $arr_value['pin'],
		),
		'merchantRefNum' => $arr_value['trnOrderNumber'],	
		'amount' => $arr_value['trnAmount'],
		'card' => array(
			'cardNum' => $arr_value['trnCardNumber'],
			'cardExpiry' => array(
				'month' => $arr_value['trnExpMonth'],
				'year' => $arr_value['trnExpYear'],			
			),
//			'cardType' => 'VI',
			'cardTypeSpecified' => false,
			'cvdIndicator' => 1,
			'cvdIndicatorSpecified' => true,
			'cvd' => $arr_value['trnCardCvd'],
		),
		'billingDetails' => array(
			'cardPayMethod' => 'WEB', //WEB = Card Number Provided
			'cardPayMethodSpecified' => true,
			'firstName' => $arr_value['ordFirstName'],
			'lastName' => $arr_value['ordLastName'],
			'street' => $arr_value['ordAddress1'],
			'city' => $arr_value['ordCity'],
			'Item' => $arr_value['ordProvince'], // Québec
			'country' => $arr_value['ordCountry'], // Canada
			'countrySpecified' => true,
			'zip' => $arr_value['ordPostalCode'],
			'phone' => $arr_value['ordPhoneNumber'],
			'email' => $_SESSION['customer']['email'],
		),
		'customerIP' => $_SERVER['REMOTE_ADDR'],
		'productType' => 'M',
		//M = Both Digital and Physical(e.g., software downloaded followed by media shipment)
		'productTypeSpecified' => true,
	);
	
	// if we have a 3dsecure response and it was approved or we don't have a 3d response
	if (isset($arr_value['authentication']) && $arr_value['authentication']['approved'] || !isset($arr_value['authentication'])) {																								
		if (isset($arr_value['authentication'])) { 
			unset($arr_value['authentication']['approved']);
		
			$request['ccAuthRequestV1']['authentication'] = $arr_value['authentication'];
		}

		try 
		{					
			$response = $client->ccPurchase($request);
			//echo '<pre>'.print_r($response,1).'</pre>';
			//exit;
			if ($response->ccTxnResponseV1->decision == 'ACCEPTED' && $response->ccTxnResponseV1->code == 0) {
				$arr_value['trnApproved'] = 1;
				$arr_value['confirmationNumber'] = $response->ccTxnResponseV1->confirmationNumber;
				$arr_value['authCode'] = $response->ccTxnResponseV1->authCode;				
				$arr_value['errorType'] = $response->ccTxnResponseV1->code;
				$arr_value['trnDate'] = $response->ccTxnResponseV1->txnTime;
				
				foreach ($response->ccTxnResponseV1->addendumResponse->detail as $value) {
					$arr_invoice[$value->tag] = $value->value;
				}		
				
				//$arr_value['trnId'] = $response->ccTxnResponseV1->confirmationNumber;
				//$arr_value['trnId'] = $arr_invoice['SEQ_NUMBER'].' '.$arr_invoice['TERMINAL_ID'];
				$arr_value['trnId'] = $arr_value['trnOrderNumber'];
			} else {$arr_value['trnApproved'] = 0; $error = $response->ccTxnResponseV1->decision.$response->ccTxnResponseV1->code;}
		} catch (SoapFault $exception) {
			echo $exception->faultcode.' - '.$exception->faultstring.'<br /><pre>'.$client->__getLastResponse().'</pre>';;
		}		
	} else {$arr_value['trnApproved'] = 0; $error = 'authentication';}
	
	// ALL VARIABLE THAT THE RESPONSE RETURN
	
	//0 = Transaction refused, 1 = Transaction approved
	$arr_invoice['SCtrnApproved'] = $arr_value['trnApproved'];
	
	//Unique id number used to identify an individual transaction. (10000012)
	$arr_invoice['SCtrnId'] = $arr_value['confirmationNumber'];
	
	$arr_invoice['SClastFourDigits'] = 'XXXX XXXX XXXX '.substr($arr_value['trnCardNumber'], -4);
	
	//The message id references a detailed approved/declined transaction response message. Review our gateway response message table for a full description of each message.
	$arr_invoice['SCmessageId'] = $arr_value['trnApproved'];
	
	//The value of trnOrderNumber submitted in the transaction request.
	$arr_invoice['SCtrnOrderNumber'] = $arr_value['trnOrderNumber'];
	
	//If the transaction is approved this parameter will contain a unique bank-issued code.
	$arr_invoice['SCauthCode'] = $arr_value['authCode'];
	
	//This field will return the value N, S, or U.
	$arr_invoice['SCerrorType'] = $arr_value['errorType'];
	
	//The amount of the transaction.
	$arr_invoice['SCtrnAmount'] = $arr_value['trnAmount'];
	
	//The date of the transaction. (11/14/2011 1:22:37 PM)
	$arr_invoice['SCtrnDate'] = $arr_value['trnDate'];
	
	$arr_invoice['SCcardType'] = $arr_value['trnCardtype'];
	
	$arr_invoice['SCtrnType'] = language('transaction/all','PURCHASE_TYPE');
	
	//Keep the payment gateway company name
	$arr_invoice['SCcompanyName'] = 'all';
} else {
	// amount is 0 so appprove
	$arr_invoice['SCtrnApproved'] = 1;
}

unset($_SESSION['trans_data'][$id_orders]);

if(!$arr_invoice['SCtrnApproved']){
	// update transaction details
	$order->update_transaction_details(-1,base64_encode(serialize($arr_invoice)));	

	header('Location: /cart/error_payment?id_orders='.$id_orders.'&error='.$error);
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