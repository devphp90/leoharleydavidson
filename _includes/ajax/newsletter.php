<?php
include(dirname(__FILE__) . "/../../_includes/config.php");
include_mailer();

$validation = array(
				'email' => array(
					'required' => 1,
					'email' => 1,
				),
			);		
			
if (!sizeof($errors = validate_fields($_POST['form_values'], $validation))) {	
	// loop through each post value and trim
	foreach ($_POST['form_values'] as $key => $value) {
		$$key = $value;
	}
	
	if (!$result = $mysqli->query('SELECT 
	COUNT(id) AS total
	FROM 
	newsletter_subscription
	WHERE
	email = "'.$mysqli->escape_string($email).'" 
	LIMIT 1')) throw new Exception('An error occured while trying to check if email exists.'."\r\n\r\n".$mysqli->mysqli->error);		
	$row = $result->fetch_assoc();
	
	if (!$row['total']) {
		
		$query = 'INSERT INTO 
				newsletter_subscription
				SET email = "'.$mysqli->escape_string($email).'",
				language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'",
				date_created = "'.$mysqli->escape_string($current_datetime).'"';
		if (!$mysqli->query($query)) {
			$arr_save['error'] = 'true';
			$arr_save['message'] = language('_include/ajax/newsletter','ALERT_ERROR');
		}else{
			$arr_save['message'] = language('_include/ajax/newsletter','ALERT_SUCCESS');
		}
		
	
		/*$mail = new PHPMailer(); // defaults to using php "mail()"
		$mail->CharSet = 'UTF-8';
		
		//$body = file_get_contents('contents.html');
		//$body  = eregi_replace("[\]",'',$body);
		$body = 'Cette adresse c\'est inscrite à la newsletter ' . ($langue=='e'?'ANGLAISE':'FRANÇAISE') . ' : ' . $email;
		
		$mail->SetFrom($email, 'Newsletter');
		//$mail->AddReplyTo($courriel,$nom);
		
		$address = "pierre@simplecommerce.com";
		$mail->AddAddress($address, $config_site['company_company']);
		
		$mail->Subject    = "Abonnement newsletter";
		
		//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		
		$mail->MsgHTML($body);
		
		//$mail->AddAttachment("images/phpmailer.gif");      // attachment
		//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
		
		if($mail->Send()) {
			$arr_save['message'] = 'Thank you for your interest!';
		}*/
	} else {
		$arr_save['error'] = 'true';
		$arr_save['message'] = language('_include/ajax/newsletter','ALERT_ERROR_EXISTING_SUBSCRIPTION');		
	}
}else{
	$arr_save['error'] = 'true';
	$arr_save['message'] = language('_include/ajax/newsletter','ALERT_ERROR_EMAIL');
}

header('Content-Type: text/javascript; charset=UTF-8'); //set header
echo json_encode($arr_save); //display records in json format using json_encode
exit;
?>