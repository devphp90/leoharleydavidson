<?php 
$available_payment_methods = array();

if ($config_site['enable_payment']) {	
	//Payment Gateway	
	$query = 'SELECT * FROM payment_gateway WHERE active = 1 LIMIT 1';
	if (!$result = $mysqli->query('SELECT * FROM payment_gateway WHERE active = 1 LIMIT 1')) throw new Exception('An error occured while trying to get available payment gateways.');
	if($result->num_rows){
		$row = $result->fetch_assoc();
		$result->free();	
		
		$payment_gateway_name = $row['name'];
		$payment_gateway_page = str_replace('.php','',$row['page']);
		
		
		$available_payment_methods[0] = array(
			'merchant_id' => $row['merchant_id'],
			'user_id' => $row['user_id'],
			'pin' => $row['pin'],
			'payment_gateway_name' => $payment_gateway_name,
			'payment_gateway_page' => $payment_gateway_page,
			'format_to_send' => $format_to_send,		
			'hosted_checkout_button_include' => $row['hosted_checkout_button_include'],
		);
		
		// get extra 
		if (!$result = $mysqli->query('SELECT * FROM payment_gateway_extra WHERE id_payment_gateway = "'.$row['id'].'"')) throw new Exception('An error occur while trying to get payment gateway extra.');
		while ($row_extra = $result->fetch_assoc()) $available_payment_methods[0]['extra'][$row_extra['name']] = $row_extra['value'];
	}
	// End Payment Gateway
	
	// if paypal
	if ($config_site['enable_paypal']) $available_payment_methods[4] = 1;
	
	// if cash payments
	if ($config_site['enable_cash_payments'] || $is_admin) $available_payment_methods[5] = 1;
	
	// if cheque payments
	if ($config_site['enable_check_payments']) $available_payment_methods[2] = $config_site['check_payment_description'];
	
	if (!sizeof($available_payment_methods)) {
		header('Location: /404?error=payment_gateway');
        exit;
	}
}
?>