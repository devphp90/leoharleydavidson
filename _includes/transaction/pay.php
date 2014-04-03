<?php
include(dirname(__FILE__) . "/../config.php");
include(dirname(__FILE__) . "/../validate_session.php");
include_once(dirname(__FILE__).'/../classes/SC_Order.php');

// Array of Values to send
$arr_value = array();

$arr_value['trnAmount'] = $cart->grand_total; // can't be greater than 1000 for test

// END Verify if one of the post field is empty...if yes return to step_payment.php
$arr_value['trnComments'] = trim($_POST['trnComments']);
$arr_value['payment_method'] = (int)$_POST['payment_method'];

// Verify if one of the post field is empty...if yes return to step_payment.php
if ($arr_value['payment_method'] != 2 && $arr_value['payment_method'] != 3 && $arr_value['payment_method'] != 5) {
	header('Location: /cart/step_payment?SCtrnApproved=0&erreur_empty_field=1');
	exit;
}

// END POST Values
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

// Array that contain all the value that we need to our Invoice
$arr_invoice = array();
// amount is 0 so appprove
$arr_invoice['SCtrnApproved'] = 1;

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
?>