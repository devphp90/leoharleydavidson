<?php
if ($_GET['task'] == 'payment_completed') {
	if (!$session_id = trim($_GET['PHPSESSID'])) throw new Exception('Session not found!');
	session_id($session_id);
}

include(dirname(__FILE__) . "/../config.php");
include_once(dirname(__FILE__).'/../classes/SC_Order.php');
//Payment Gateway
require(dirname(__FILE__) . "/current_payment_gateway.php");

$task = trim($_GET['task']);
$id_orders = trim($_POST['x_invoice_num']);
$comments = trim($_POST['trnComments']);

$order = new SC_Order($mysqli);
$cart = new SC_Cart($mysqli);

if (!$id_orders) {
	// create order
	$id_orders = $order->new_order($comments,0);
	$id_orders = str_pad($id_orders,10,'0',STR_PAD_LEFT);	
} else {
	$order->load($id_orders);
}

$transactionKey =  $available_payment_methods[0]['extra']['TransactionKey'];

if ($task == 'payment_completed') {
	// ALL VARIABLE THAT THE RESPONSE RETURN
	
	// validate response key 
	$fingerprint = md5($available_payment_methods[0]['extra']['ResponseKey'].$available_payment_methods[0]['merchant_id'].$_POST['x_trans_id'].$order->grand_total);
	
	if ($fingerprint != $_POST['x_MD5_Hash']) throw new Exception('Invalid request');
	
	
	//0 = Transaction refused, 1 = Transaction approved
	$arr_invoice['SCtrnApproved'] = $_POST['x_response_code'] ? 1:0;
	
	//Unique id number used to identify an individual transaction. (10000012)
	$arr_invoice['SCtrnId'] = $_POST['x_trans_id'];
	
	//The message id references a detailed approved/declined transaction response message. Review our gateway response message table for a full description of each message.
	$arr_invoice['SCmessageId'] = $_POST['x_response_code'] == 1 ? 1:3;
	
	//This field will return a basic approved/declined message which may be displayed to the customer on a confirmation page. Review our gateway response message table for details.
	$arr_invoice['SCmessageText'] = $_POST['x_response_reason_text'].(!empty($_POST['Bank_Message']) ? '<br />'.$_POST['Bank_Message']:'');
	
	// interac issuer name
	$arr_invoice['SCissname'] = $_POST['exact_issname'];
	
	// interac confirmation code
	$arr_invoice['SCissconf'] = $_POST['exact_issconf'];
	
	//The value of trnOrderNumber submitted in the transaction request.
	$arr_invoice['SCtrnOrderNumber'] = $id_orders;
	
	//If the transaction is approved this parameter will contain a unique bank-issued code.
	$arr_invoice['SCauthCode'] = $_POST['x_auth_code'];
	
	//The amount of the transaction.
	$arr_invoice['SCtrnAmount'] = $order->grand_total;
	
	//The date of the transaction. (11/14/2011 1:22:37 PM)
	$arr_invoice['SCtrnDate'] = date('Y-m-d H:i:s');
	
	//The type of card used in the transaction. VI=Visa, MC=MasterCard, AM=American Express NN=Discover, DI=Diners, JB=JCB, IO=INTERAC Online, ET=Direct Debit/Direct Payments/ACH
	$arr_invoice['SCcardType'] = $_POST['TransactionCardType'];
	
	//The original value sent to indicate the type of transaction to perform (i.e. P,R,VP,VR, PA, PAC, Q).
	$arr_invoice['SCtrnType'] = $_POST['x_type'];
	
	$arr_invoice['SClastFourDigits'] = str_replace('#','',$_POST['Card_Number']);
		
	//Keep the payment gateway company name
	$arr_invoice['SCcompanyName'] = 'all';	
	
	$arr_invoice['response_values'] = $_POST;
	
	if ($_POST['x_response_code'] != 1) {		
		// update transaction details
		$order->update_transaction_details(-1,base64_encode(serialize($arr_invoice)));	
	
		header('Location: /cart/error_payment?id_orders='.$id_orders);
		exit;	
	} else {
		$cart->empty_cart();
	
		// update transaction details
		$order->update_transaction_details(($config_site['enable_auto_completed_order']?7:1),base64_encode(serialize($arr_invoice)));
		
		if ($arr_invoice['SCcardType'] == 'Interac Online Debit') $mysqli->query('UPDATE orders SET payment_method = 1 WHERE id = "'.$id_orders.'"');	
		
		// Load Order after Update
		$order->load($id_orders);
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<script type="text/javascript">
window.location.href='<?php echo urldecode($_POST['x_receipt_link_url']); ?>';
</script> 
</body>
</html>
<?php		
//		header('Location: /cart/step_completed?id_orders='.$id_orders.'&sendmail='.$sendmail_failed);
//		exit;		
	}
} else {
	include(dirname(__FILE__) . "/../validate_session.php");
		
	$sequence = rand(1, 9999); 
	$timeStamp = time(); 
	$currency_code = "CAD"; 
	$test_mode = $available_payment_methods[0]['extra']['test_mode']; 
	
	if( phpversion() >= '5.1.2' ){ 
		$fingerprint = hash_hmac("md5", $available_payment_methods[0]['merchant_id'] . "^" . $sequence . "^" . $timeStamp . "^" . $order->grand_total . "^" . $currency_code , $transactionKey); 
	} else { 
		$fingerprint = bin2hex(mhash(MHASH_MD5, $available_payment_methods[0]['merchant_id'] . "^" . $sequence . "^" . $timeStamp . "^" . $order->grand_total . "^" . $currency_code, $transactionKey)); 
	} 
	
	if($test_mode){ 
		$url = "https://rpm-demo.e-xact.com/payment"; 
		$x_test_request = "TRUE"; // Process payment in test mode. Case-sensitive. 
	} else { 
		$url = "https://checkout.e-xact.com/payment"; 
		$x_test_request = "FALSE"; // Process payment in test mode. Case-sensitive. 
	} 	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body onLoad="myfunc()">
<form id="form" method='post' action='<?php echo $url; ?>' > 
<input type='hidden' name='x_login' value='<? echo $available_payment_methods[0]['merchant_id']; ?>' /> 
<input type='hidden' name='x_amount' value='<? echo $order->grand_total; ?>' /> 
<input type='hidden' name='x_invoice_num' value='<? echo $id_orders; ?>' /> 
<input type='hidden' name='x_fp_sequence' value='<? echo $sequence; ?>' /> 
<input type='hidden' name='x_fp_timestamp' value='<? echo $timeStamp; ?>' /> 
<input type='hidden' name='x_fp_hash' value='<? echo $fingerprint; ?>' /> 
<input type='hidden' name='x_test_request' value='<?=$x_test_request?>' /> 
<input type='hidden' name='x_show_form' value='PAYMENT_FORM' /> 
<input type='hidden' name='x_type' value='AUTH_CAPTURE' /> 
<input type='hidden' name='x_receipt_link_method' VALUE='LINK'> 
<input type="hidden" name="x_receipt_link_text" value="Go to Invoice" />
<input type='hidden' name='x_receipt_link_url' VALUE='<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/cart/step_completed?id_orders='.$id_orders; ?>'>
<input type="hidden" name="x_relay_response" value="TRUE" />
<input type="hidden" name="x_relay_url" value="<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/_includes/transaction/exact_hosted_checkout.php?task=payment_completed&PHPSESSID='.session_id(); ?>" />
<input type='hidden' name='x_currency_code' value ='<?=$currency_code;?>'> 
<input type='hidden' name='x_first_name' value='<?php echo $order->billing_firstname; ?>'> 
<input type='hidden' name='x_last_name' value='<?php echo $order->billing_lastname; ?>'> 
<input type='hidden' name='x_company' value='<?php echo $order->billing_company; ?>'> 
<input type='hidden' name='x_address' value='<?php echo $order->billing_address; ?>'> 
<input type='hidden' name='x_city' value='<?php echo $order->billing_city; ?>'> 
<input type='hidden' name='x_state' value='<?php echo $order->billing_state_code; ?>'> 
<input type='hidden' name='x_country' value='<?php echo $order->billing_country_code; ?>'> 
<input type='hidden' name='x_phone' value='<?php echo $order->billing_telephone; ?>'> 
<input type='hidden' name='x_zip' value='<?php echo $order->billing_zip; ?>'> 
<input type='hidden' name='x_email' value='<?php echo $order->email; ?>'> 
<input type='hidden' name='x_cust_id' value='<?php echo $order->id_customer; ?>'> 
<input type='hidden' name='x_ship_to_first_name' value='<?php echo $order->shipping_firstname; ?>'> 
<input type='hidden' name='x_ship_to_last_name' value='<?php echo $order->shipping_lastname; ?>'> 
<input type='hidden' name='x_ship_to_company' value='<?php echo $order->shipping_company; ?>'> 
<input type='hidden' name='x_ship_to_address' value='<?php echo $order->shipping_address; ?>'> 
<input type='hidden' name='x_ship_to_city' value='<?php echo $order->shipping_city; ?>'> 
<input type='hidden' name='x_ship_to_state' value='<?php echo $order->shipping_state_code; ?>'> 
<input type='hidden' name='x_ship_to_country' value='<?php echo $order->shipping_country_code; ?>'> 
<input type='hidden' name='x_ship_to_zip' value='<?php echo $order->shipping_zip; ?>'> 
<input type="hidden" name="x_logo_url" value="<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/_images/logo_paypal.jpg'; ?>" />
</form> 

<script type="text/javascript">
function myfunc () {
	var frm = document.getElementById("form");
	frm.submit();
}
</script> 
</body>
</html>
<?php
}