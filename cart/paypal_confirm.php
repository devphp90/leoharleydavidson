<?php 
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");
include_once(dirname(__FILE__).'/../_includes/classes/SC_Order.php');
require_once (dirname(__FILE__).'/../_includes/transaction/paypalfunctions.php');

$id_orders = (int)$_GET['id_orders'];
$token = trim($_GET['token']);

$order = new SC_Order($mysqli);

if (!$order->load($id_orders) || empty($token)) {
	header("HTTP/1.0 404 Not Found");
	
	header('Location: /404');
	exit;	
}

if (isset($_GET['task']) && $_GET['task'] == 'confirm_payment') {		
	/*
	'------------------------------------
	' The paymentAmount is the total value of 
	' the shopping cart, that was set 
	' earlier in a session variable 
	' by the shopping cart page
	'------------------------------------
	*/
	
	$finalPaymentAmount =  $order->grand_total;
		
	/*
	'------------------------------------
	' Calls the DoExpressCheckoutPayment API call
	'
	' The ConfirmPayment function is defined in the file PayPalFunctions.jsp,
	' that is included at the top of this file.
	'-------------------------------------------------
	*/

	$resArray = ConfirmPayment ( $finalPaymentAmount );
	$ack = strtoupper($resArray["ACK"]);
	if( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" )
	{				
		/*
		'********************************************************************************************************************
		'
		' THE PARTNER SHOULD SAVE THE KEY TRANSACTION RELATED INFORMATION LIKE 
		'                    transactionId & orderTime 
		'  IN THEIR OWN  DATABASE
		' AND THE REST OF THE INFORMATION CAN BE USED TO UNDERSTAND THE STATUS OF THE PAYMENT 
		'
		'********************************************************************************************************************
		*/

		$transactionId		= $resArray["PAYMENTINFO_0_TRANSACTIONID"]; // ' Unique transaction ID of the payment. Note:  If the PaymentAction of the request was Authorization or Order, this value is your AuthorizationID for use with the Authorization & Capture APIs. 
		$transactionType 	= $resArray["PAYMENTINFO_0_TRANSACTIONTYPE"]; //' The type of transaction Possible values: l  cart l  express-checkout 
		$paymentType		= $resArray["PAYMENTINFO_0_PAYMENTTYPE"];  //' Indicates whether the payment is instant or delayed. Possible values: l  none l  echeck l  instant 
		$orderTime 			= $resArray["PAYMENTINFO_0_ORDERTIME"];  //' Time/date stamp of payment
		$amt				= $resArray["PAYMENTINFO_0_AMT"];  //' The final amount charged, including any shipping and taxes from your Merchant Profile.
		$currencyCode		= $resArray["PAYMENTINFO_0_CURRENCYCODE"];  //' A three-character currency code for one of the currencies listed in PayPay-Supported Transactional Currencies. Default: USD. 
		$feeAmt				= $resArray["PAYMENTINFO_0_FEEAMT"];  //' PayPal fee amount charged for the transaction
		$settleAmt			= $resArray["PAYMENTINFO_0_SETTLEAMT"];  //' Amount deposited in your PayPal account after a currency conversion.
		$taxAmt				= $resArray["PAYMENTINFO_0_TAXAMT"];  //' Tax charged on the transaction.
		$exchangeRate		= $resArray["PAYMENTINFO_0_EXCHANGERATE"];  //' Exchange rate if a currency conversion occurred. Relevant only if your are billing in their non-primary currency. If the customer chooses to pay with a currency other than the non-primary currency, the conversion occurs in the customer's account.
		
		/*
		' Status of the payment: 
				'Completed: The payment has been completed, and the funds have been added successfully to your account balance.
				'Pending: The payment is pending. See the PendingReason element for more information. 
		*/
		
		$paymentStatus	= $resArray["PAYMENTINFO_0_PAYMENTSTATUS"]; 

		/*
		'The reason the payment is pending:
		'  none: No pending reason 
		'  address: The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile. 
		'  echeck: The payment is pending because it was made by an eCheck that has not yet cleared. 
		'  intl: The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview. 		
		'  multi-currency: You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment. 
		'  verify: The payment is pending because you are not yet verified. You must verify your account before you can accept this payment. 
		'  other: The payment is pending for a reason other than those listed above. For more information, contact PayPal customer service. 
		*/
		
		$pendingReason	= $resArray["PAYMENTINFO_0_PENDINGREASON"];  

		/*
		'The reason for a reversal if TransactionType is reversal:
		'  none: No reason code 
		'  chargeback: A reversal has occurred on this transaction due to a chargeback by your customer. 
		'  guarantee: A reversal has occurred on this transaction due to your customer triggering a money-back guarantee. 
		'  buyer-complaint: A reversal has occurred on this transaction due to a complaint about the transaction from your customer. 
		'  refund: A reversal has occurred on this transaction because you have given the customer a refund. 
		'  other: A reversal has occurred on this transaction due to a reason not listed above. 
		*/
		
		$reasonCode		= $resArray["PAYMENTINFO_0_REASONCODE"];   
				
		$cart->empty_cart();
	
		// update transaction details
		$transaction_details = $order->transaction_details ? unserialize(base64_decode($order->transaction_details)):array();
		$transaction_details = array_merge_recursive($transaction_details,$resArray);
		
		$order->update_transaction_details(($config_site['enable_auto_completed_order']?7:1),base64_encode(serialize($transaction_details)));
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
		
		$transaction_details = array();
		ob_start();
		$show_company_header = 0;
		include("../_includes/transaction/email_receipt.php");
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
	else  
	{
		//Display a user friendly Error on the page using any of the following error information returned by PayPal
		$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
		$ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
		$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
		$ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
		
		echo "GetExpressCheckoutDetails API call failed. ";
		echo "Detailed Error Message: " . $ErrorLongMsg;
		echo "Short Error Message: " . $ErrorShortMsg;
		echo "Error Code: " . $ErrorCode;
		echo "Error Severity Code: " . $ErrorSeverityCode;
	}	
} else {
	if ($order->status == 1 || $order->status == 7) {
		header('Location: /cart/step_completed?id_orders='.$id_orders);
		exit;				
	}
	
	$resArray = GetShippingDetails( $token );
	$ack = strtoupper($resArray["ACK"]);
	if( $ack == "SUCCESS" || $ack == "SUCESSWITHWARNING") 
	{
		/*
		' The information that is returned by the GetExpressCheckoutDetails call should be integrated by the partner into his Order Review 
		' page		
		*/
/* 		$email 				= $resArray["EMAIL"]; // ' Email address of payer.
		$payerId 			= $resArray["PAYERID"]; // ' Unique PayPal customer account identification number.
		$payerStatus		= $resArray["PAYERSTATUS"]; // ' Status of payer. Character length and limitations: 10 single-byte alphabetic characters.
		$salutation			= $resArray["SALUTATION"]; // ' Payer's salutation.
		$firstName			= $resArray["FIRSTNAME"]; // ' Payer's first name.
		$middleName			= $resArray["MIDDLENAME"]; // ' Payer's middle name.
		$lastName			= $resArray["LASTNAME"]; // ' Payer's last name.
		$suffix				= $resArray["SUFFIX"]; // ' Payer's suffix.
		$cntryCode			= $resArray["COUNTRYCODE"]; // ' Payer's country of residence in the form of ISO standard 3166 two-character country codes.
		$business			= $resArray["BUSINESS"]; // ' Payer's business name.
		$shipToName			= $resArray["PAYMENTREQUEST_0_SHIPTONAME"]; // ' Person's name associated with this address.
		$shipToStreet		= $resArray["PAYMENTREQUEST_0_SHIPTOSTREET"]; // ' First street address.
		$shipToStreet2		= $resArray["PAYMENTREQUEST_0_SHIPTOSTREET2"]; // ' Second street address.
		$shipToCity			= $resArray["PAYMENTREQUEST_0_SHIPTOCITY"]; // ' Name of city.
		$shipToState		= $resArray["PAYMENTREQUEST_0_SHIPTOSTATE"]; // ' State or province
		$shipToCntryCode	= $resArray["PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE"]; // ' Country code. 
		$shipToZip			= $resArray["PAYMENTREQUEST_0_SHIPTOZIP"]; // ' U.S. Zip code or other country-specific postal code.
		$addressStatus 		= $resArray["ADDRESSSTATUS"]; // ' Status of street address on file with PayPal   
		$invoiceNumber		= $resArray["INVNUM"]; // ' Your own invoice or tracking number, as set by you in the element of the same name in SetExpressCheckout request .
		$phonNumber			= $resArray["PHONENUM"]; // ' Payer's contact telephone number. Note:  PayPal returns a contact telephone number only if your Merchant account profile settings require that the buyer enter one. 
		*/
	
		$order->update_transaction_details(4,base64_encode(serialize($resArray)));
	} 
	else  
	{
		//Display a user friendly Error on the page using any of the following error information returned by PayPal
		$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
		$ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
		$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
		$ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
		
		echo "GetExpressCheckoutDetails API call failed. ";
		echo "Detailed Error Message: " . $ErrorLongMsg;
		echo "Short Error Message: " . $ErrorShortMsg;
		echo "Error Code: " . $ErrorCode;
		echo "Error Severity Code: " . $ErrorSeverityCode;
	}	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="none" />
<meta http-equiv="Pragma" content="no-cache">
<title><?php echo $config_site['site_name']; ?></title>
<?php include(dirname(__FILE__) . "/../_includes/template/header.php");?>
<link rel="stylesheet" href="/includes/js/jquery/etalage/_fancybox_plugin/jquery.fancybox-1.3.4.css">
<script type="text/javascript" src="/includes/js/jquery/etalage/_fancybox_plugin/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" language="javascript">
$(function(){	
	//Preload image
	$.fn.preload = function() {
		this.each(function(){
			$('<img/>')[0].src = this;
		});
	}
	$(['<?php echo ($_SERVER['HTTPS'] == 'on' ? 'https://'.$_SERVER['HTTP_HOST']:'http://'.$_SERVER['HTTP_HOST']).'/_images/ajax-loader.gif';  ?>']).preload();

});	

function please_wait_display(){
	$.fancybox({
			'overlayShow'	:	true,
			'modal'			: 	true,
			'autoScale'		:	true,
			'hideOnOverlayClick'	:	false,
			'hideOnContentClick'	:	false,
			'showCloseButton'		:	false,
			'showNavArrows'			:	false,
			'enableEscapeButton'	:	false,
			'content'		:	'<div style="padding:3px; min-width:120px;"><div class="fl"><img src="<?php echo ($_SERVER['HTTPS'] == 'on' ? 'https://'.$_SERVER['HTTP_HOST']:'http://'.$_SERVER['HTTP_HOST']).'/_images/ajax-loader.gif';  ?>" /></div><div class="fl" style="margin-left:10px; margin-top:5px; font-size:18px; text-transform:uppercase;"><?php echo htmlspecialchars(language('global', 'MESSAGE_PLEASE_WAIT')); ?></div><div class="cb"></div></div>'
	});	
	// Use to give time to show image please wait
	setTimeout('document.forms["formPaiement"].submit();', 1000);
}
</script>
</head>
<body>
<?php include(dirname(__FILE__) . "/../_includes/template/top.php");?>
<div class="main-container">
<div class="main">	
    <div class="container">
    <div class="main-content" style="overflow: hidden;">	
	<?php
    // Indicate wich Step to follow, to completed the transaction
	$step = 4;
	if(!$config_site['enable_shipping'])$step = 3;
	include(dirname(__FILE__) . "/../_includes/step.php");
	?>
   
        <?php 
		switch ($error) {
			case 'empty':
				echo '<div class="error" style="margin-top: 0px">'.language('cart/step_payment', 'ERROR_EMPTY_FIELD').'</div>';
				break;
		}?>
        <div id="address" style="float:left; width:670px">
            <div id="billing" style="float:left; width:330px;">
                <div class="title_bg title_bg_1"><div class="title_bg_text title_bg_text_ffffff" style="float:left"><?php echo language('global', 'TITLE_BILLING_ADDRESS');?></div><div class="cb"></div></div>
                <div class="title_bg_text_box">
                <?php
                echo 
					($cart->billing_company?'<strong>'.$cart->billing_company.'</strong><br />':'').
					($cart->billing_company?$cart->billing_firstname. ' ' .$cart->billing_lastname.'<br />':'<strong>'.$cart->billing_firstname. ' ' .$cart->billing_lastname.'</strong><br />').
					$cart->billing_address.'<br />'.
					$cart->billing_city.
					($cart->billing_state?' '.$cart->billing_state:'').'<br />'.$cart->billing_country.' '.strtoupper($cart->billing_zip);
				?> 
                </div>  
            </div>
            <div id="shipping" style="float:left; margin-left:10px; width:330px;">
            	<div class="title_bg title_bg_1"><div class="title_bg_text title_bg_text_ffffff" style="float:left"><?php echo language('global', 'TITLE_SHIPPING_ADDRESS');?></div><div class="cb"></div></div>
                <div class="title_bg_text_box">
               <?php
			   if(!$cart->local_pickup){
				   echo 
						($cart->shipping_company?'<strong>'.$cart->shipping_company.'</strong><br />':'').
						($cart->shipping_company?$cart->shipping_firstname. ' ' .$cart->shipping_lastname.'<br />':'<strong>'.$cart->shipping_firstname. ' ' .$cart->billing_lastname.'</strong><br />').
						$cart->shipping_address.'<br />'.
						$cart->shipping_city.
						($cart->shipping_state?' '.$cart->shipping_state:'').'<br />'.$cart->shipping_country.' '.strtoupper($cart->shipping_zip);
			   }else{
					echo '<div>'.language('global', 'TITLE_LOCAL_PICKUP').'</div>';   
			   }
				?> 
                </div>
            </div>
            <div class="cb"></div>

        
        <div id="payment">
        
        		<form action="?id_orders=<?php echo $id_orders; ?>&token=<?php echo $token; ?>&task=confirm_payment" method="post" id="formPaiement" name="formPaiement">
            	<div class="title_bg title_bg_2"><div class=" title_bg_text title_bg_text_ffffff"><?php echo language('cart/step_payment', 'TITLE_PAYMENT');?></div></div>
                <div class="title_bg_text_box<?php echo($error=='empty'?' title_bg_text_box_error':'');?>" style="padding-bottom:5px; margin-bottom:0">
                  <div style="float:left; border-right: solid 1px #CCC; margin-right:15px; padding-right:15px; width:350px">
                  
   					<div><?php echo language('cart/step_payment','MESSAGE_PLACE_ORDER'); ?></div>
                    
                    <?php 
					if (!$result_comments = $mysqli->query('SELECT 
					orders_comment.comments
					FROM
					orders_comment
					WHERE
					orders_comment.id_orders = "'.$mysqli->escape_string($order->id).'" 
					AND
					orders_comment.hidden_from_customer = 0
					AND
					orders_comment.id_user_created = 0
					ORDER BY
					id ASC
					LIMIT 1')) throw new Exception('An error occured while trying to get order comments.'."\r\n\r\n".$mysqli->error);	
					
					if ($row_comment = $result_comments->fetch_assoc()) {					
					?>
	                  
                  <div style="margin-top:20px;">
					<ul>
                      <li>
                       <label><?php echo language('cart/step_payment', 'LABEL_COMMENTS');?></label>
                      	<?php echo $row_comment['comments']; ?>
                      </li>
					</ul>                                                            
                  </div>
                  <?php } ?>
                  
                  
				<div style="margin-top:20px">
                  <div class="button_previous_step fl"><input type="button" value="<?php echo language('global', 'BTN_PREVIOUS_STEP');?>" class="previous_step" name="btn_previous_step" onclick="document.location.href='step_validation'" /></div>
                  
                  <div class="button_next_step fr payment_method_0"><input type="button" value="<?php echo language('global', 'BTN_CHECKOUT');?>" name="confirm_payment" class="next_step button_checkout" onclick="javascript:please_wait_display();" /></div>
                    
                  <div class="cb"></div>
                  </div>
                  </div>
                  
                  <div style="float:left;">
                  <h2><?php echo language('cart/step_payment', 'LABEL_CONTACT_US');?></h2>

                  <div style="margin-bottom: 8px; width:250px;"><?php echo language('cart/step_payment', 'LABEL_CONTACT_US_DESCRIPTION');?></div>
				  	<strong><?php echo $config_site['company_company'];?></strong><br />
                    <?php echo $config_site['company_address']?' ' . $config_site['company_address']:'';?>
                    <?php echo $config_site['company_city']?'<br />' . $config_site['company_city']:'';?>
                    <?php
                    $country_name = '';
                    $state_name = ''; 
                    if($config_site['company_country_code']){
                        $query = 'SELECT 
                            country_description.name
                            FROM country_description
                            WHERE country_description.country_code = "'.$config_site['company_country_code'].'" AND country_description.language_code = "'.$_SESSION['customer']['language'].'"';
                    
                            if ($result = $mysqli->query($query)) {
                                $row = $result->fetch_assoc();
                                $country_name = $row['name'];
                            }
                    }
                    if($config_site['company_state_code']){
                        $query = 'SELECT 
                            state_description.name
                            FROM state_description
                            WHERE state_description.state_code = "'.$config_site['company_state_code'].'" AND state_description.language_code = "'.$_SESSION['customer']['language'].'"';
                    
                            if ($result = $mysqli->query($query)) {
                                $row = $result->fetch_assoc();
                                $state_name = $row['name'];
                            }
                    }
                    echo $state_name?' ' . $state_name:'';?>
                    <?php echo $country_name?' ' . '<br />'.$country_name:'';?>
                    <?php echo $config_site['company_zip']?' ' . $config_site['company_zip']:'';?>
<div>&nbsp;</div>
                    <?php echo $config_site['company_telephone']?'<strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_T').'</strong> ' . $config_site['company_telephone']:'';?>
                    <?php echo $config_site['company_fax']?'<br /><strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_F').'</strong> ' . $config_site['company_fax']:'';?>
                    <?php echo $config_site['company_email']?'<br /><strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_E').'</strong> <a href="mailto:' . $config_site['company_email'].'">' . $config_site['company_email'] . '</a>':'';?>
                  </div>  
                  <div class="cb"></div>
                </div>
                </form>              
            </div>
        </div>
        
        <div style="float:right;">
    
    <?php 
	$step = 'payment';
	include(dirname(__FILE__) . "/../_includes/checkout_cart.php");?>
    <div class="cb"></div>
    </div>
    <div class="cb"></div>
    </div>
    </div>
</div>
</div>

<?php include(dirname(__FILE__) . "/../_includes/template/review.php");?>
<?php include(dirname(__FILE__) . "/../_includes/template/bottom.php");?>
</body>
</html>