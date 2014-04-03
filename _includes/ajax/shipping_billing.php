<?php
include(dirname(__FILE__) . "/../../_includes/config.php");

if(isset($_GET['task']) and !empty($_GET['task'])){
	$task = $_GET['task'];
}else{
	$task = "";
	echo 'false';
	exit();
}
//page = 0 (step_shipping.php) page = 1 (modify_address.php)
if(isset($_GET['page']) and !empty($_GET['page'])){
	$page = $_GET['page'];
}else{
	$page = "0";
}

switch($task){
	case 'save':
		$data = array();
		$data['list1'] = '';
		$data['list2'] = '';
		$data['rep'] = 'true';
		$data['refresh_page'] = false;

		$validation = array(
			
			'firstname' => array(
				'required' => 1,
			),
			'lastname' => array(
				'required' => 1,
			),	
			'address' => array(
				'required' => 1,
			),
			'city' => array(
				'required' => 1,					
			),
			'country_code' => array(
				'required' => 1,
			),
			'zip' => array(
				'required' => 1,
			),
			'telephone' => array(
				'required' => 1,
			)
		);		
		
		if (!sizeof($errors = validate_fields($_POST['form_values'], $validation))) {				
			// current time
			$current_datetime = date('Y-m-d H:i:s');
			
			
			// loop through each post value and trim
			foreach ($_POST['form_values'] as $key => $value) {
				$$key = $value;
			}
			
			
			$zip = preg_replace("/[^ \w]+/", "", $zip);
			$zip = strtoupper(str_replace(' ','',$zip));			
				
			if((isset($default_billing) and $default_billing)){
				if (!$mysqli->query('UPDATE
				customer_address 
				SET 
				default_billing = 0
				WHERE id_customer = ' . $_SESSION['customer']['id'])) {
					throw new Exception('An error occured while trying to update default_billing.'."\r\n\r\n".$mysqli->mysqli->error);		
				}
			}
			if((isset($default_shipping) and $default_shipping)){
				if (!$mysqli->query('UPDATE
				customer_address 
				SET 
				default_shipping = 0
				WHERE id_customer = ' . $_SESSION['customer']['id'])) {
					throw new Exception('An error occured while trying to update default_shipping.'."\r\n\r\n".$mysqli->mysqli->error);		
				}
			}
			
			// add address as default billing
			if (!$mysqli->query(($id ? 'UPDATE' : 'INSERT INTO') . '
			customer_address 
			SET 
			'.(isset($default_billing) ?'default_billing = "'.$mysqli->escape_string($default_billing).'",':'').'
			'.(isset($default_shipping)?'default_shipping = "'.$mysqli->escape_string($default_shipping).'",':'').'
			address_type = "'.$mysqli->escape_string($address_type).'",
			use_in = "'.$mysqli->escape_string($use_in).'",
			id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'",
			firstname = "'.$mysqli->escape_string($firstname).'",
			lastname = "'.$mysqli->escape_string($lastname).'",
			company = "'.$mysqli->escape_string($company).'",
			address = "'.$mysqli->escape_string($address).'",
			city = "'.$mysqli->escape_string($city).'",
			country_code = "'.$mysqli->escape_string($country_code).'",
			state_code = "'.$mysqli->escape_string($state_code).'",
			zip = "'.$mysqli->escape_string($zip).'",
			telephone = "'.$mysqli->escape_string($telephone).'",
			fax = "'.$mysqli->escape_string($fax).'"
			'.($id ? 'WHERE id = ' . $id : ',date_created = "'.$mysqli->escape_string($current_datetime).'"').' 
			')) {
				throw new Exception('An error occured while trying to add address information.'."\r\n\r\n".$mysqli->mysqli->error);		
			}else{
				
				if(!empty($id) and $id == $cart->shipping_id){
					//Find the tax rule
					$id_tax_rule = $cart->get_id_tax_rule($country_code,$state_code,$zip);
					
					// Verify if free shipping is enable and if it's applicable
					$free_shipping = $cart->get_free_shipping_yes_no($country_code,$state_code);
					
					
					// Update Table Cart
					if (!$mysqli->query('UPDATE 
							cart
							SET
							id_tax_rule = "'.$mysqli->escape_string($id_tax_rule).'",
							shipping_id = "'.$mysqli->escape_string($id).'",
							shipping_firstname = "'.$mysqli->escape_string($firstname).'",
							shipping_lastname = "'.$mysqli->escape_string($lastname).'",
							shipping_company = "'.$mysqli->escape_string($company).'",
							shipping_address = "'.$mysqli->escape_string($address).'",
							shipping_city = "'.$mysqli->escape_string($city).'",
							shipping_country_code = "'.$mysqli->escape_string($country_code).'",
							shipping_state_code = "'.$mysqli->escape_string($state_code).'",
							shipping_zip = "'.$mysqli->escape_string($zip).'",
							shipping_telephone = "'.$mysqli->escape_string($telephone).'",
							shipping_fax = "'.$mysqli->escape_string($fax).'",
							local_pickup = 0,
							local_pickup_id = 0,
							local_pickup_address = "",
							local_pickup_city = "",
							local_pickup_country_code = "",
							local_pickup_state_code = "",
							local_pickup_zip = "",
							free_shipping = "'.$free_shipping.'",
							shipping_gateway_company = "",
							shipping_service = "",
							shipping = "",
							shipping_estimated = ""
							WHERE
							id = '.$cart->id.'
							LIMIT 1')){
						throw new Exception('An error occured while trying to set remember me.'."\r\n\r\n".$mysqli->mysqli->error);		
					}

					// Refresh the page if we use a Shipping Gateway
					if($shipping_gateway){
						$data['refresh_page'] = true;
					}
				}
				
				if(!empty($id) and $id == $cart->billing_id){
					// Update Table Cart
					if (!$mysqli->query('UPDATE 
							cart
							SET
							billing_id = "'.$mysqli->escape_string($id).'",
							billing_firstname = "'.$mysqli->escape_string($firstname).'",
							billing_lastname = "'.$mysqli->escape_string($lastname).'",
							billing_company = "'.$mysqli->escape_string($company).'",
							billing_address = "'.$mysqli->escape_string($address).'",
							billing_city = "'.$mysqli->escape_string($city).'",
							billing_country_code = "'.$mysqli->escape_string($country_code).'",
							billing_state_code = "'.$mysqli->escape_string($state_code).'",
							billing_zip = "'.$mysqli->escape_string($zip).'",
							billing_telephone = "'.$mysqli->escape_string($telephone).'",
							billing_fax = "'.$mysqli->escape_string($fax).'"
							WHERE
							id = '.$cart->id.'
							LIMIT 1')){
						throw new Exception('An error occured while trying to set remember me.'."\r\n\r\n".$mysqli->mysqli->error);		
					}
				}
				
				if(!$data['refresh_page']){
					$data['list1'] = list_address($address_type,$page);
					if($address_type == 'billing'){
						$data['list2'] = list_address("shipping",$page);
					}else{
						$data['list2'] = list_address("billing",$page);
					}
					$cart->calculate_taxes();
					$cart->init();
				}
			}
		}else{
			$data = $errors;
			$data['rep'] = 'false';
		}
		header('Content-Type: text/javascript; charset=UTF-8'); //set header
		echo json_encode($data); //display records in json format using json_encode
		exit;
	break;
	case 'edit':
		$data = array();
		if(isset($_POST['id']) and is_numeric($_POST['id'])){
			$id = $_POST['id'];
			if ($result = $mysqli->query('SELECT customer_address.*,COUNT(ca.id) AS total FROM `customer_address` LEFT JOIN (customer_address AS ca) ON (customer_address.id_customer = ca.id_customer) WHERE customer_address.id = "' . $mysqli->escape_string($id).'"')) {
				$row = $result->fetch_assoc();
				$data = $row;
				$result->close();
				
			}else{
				$data = 'false';	
			}
		}
		header('Content-Type: text/javascript; charset=UTF-8'); //set header
		echo json_encode($data); //display records in json format using json_encode
		exit;
	break;
	case 'delete':
		if(isset($_POST['id']) and is_numeric($_POST['id'])){
			$data = array();
			$data['list1'] = '';
			$data['list2'] = '';
			$data['refresh_page'] = false;
			$id = $_POST['id'];
			$use_in = $_POST['use_in'];
			$address_type = $_POST['address_type'];	
			$shipping_gateway = $_POST['shipping_gateway'];			
			if ($result = $mysqli->query('DELETE FROM customer_address WHERE id = ' . $id)) {

				if($cart->shipping_id == $id){
					if ($result = $mysqli->query('SELECT 
						*
						FROM				
						customer_address 
						WHERE
						id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'"
						AND 
						default_shipping = 1
						LIMIT 1')) {
							if ($result->num_rows) {
								
								$row = $result->fetch_assoc();
								
								//Find the tax rule
								$id_tax_rule = $cart->get_id_tax_rule($row['country_code'],$row['state_code'],$row['zip']);
								
								// Verify if free shipping is enable and if it's applicable
								$free_shipping = $cart->get_free_shipping_yes_no($row['country_code'],$row['state_code']);

								// Update Table Cart
								if (!$mysqli->query('UPDATE 
										cart
										SET
										id_tax_rule = "'.$mysqli->escape_string($id_tax_rule).'",
										shipping_id = "'.$mysqli->escape_string($row['id']).'",
										shipping_firstname = "'.$mysqli->escape_string($row['firstname']).'",
										shipping_lastname = "'.$mysqli->escape_string($row['lastname']).'",
										shipping_company = "'.$mysqli->escape_string($row['company']).'",
										shipping_address = "'.$mysqli->escape_string($row['address']).'",
										shipping_city = "'.$mysqli->escape_string($row['city']).'",
										shipping_country_code = "'.$mysqli->escape_string($row['country_code']).'",
										shipping_state_code = "'.$mysqli->escape_string($row['state_code']).'",
										shipping_zip = "'.$mysqli->escape_string($row['zip']).'",
										shipping_telephone = "'.$mysqli->escape_string($row['telephone']).'",
										shipping_fax = "'.$mysqli->escape_string($row['fax']).'",
										free_shipping = "'.$free_shipping.'",
										shipping_gateway_company = "",
										shipping_service = "",
										shipping = "",
										shipping_estimated = ""
										WHERE
										id = '.$cart->id.'
										LIMIT 1')){
									throw new Exception('An error occured while trying to set remember me.'."\r\n\r\n".$mysqli->mysqli->error);		
								}
								
								// Refresh the page if we use a Shipping Gateway
								if($shipping_gateway){
									$data['refresh_page'] = true;
								}
							}
					}
				}
				if(!$data['refresh_page']){
					$data['list1'] = list_address($address_type,$page);
					if($use_in == 0){
						if($address_type == 'billing'){
							$data['list2'] = list_address("shipping",$page);
						}else{
							$data['list2'] = list_address("billing",$page);
						}
					}
					$cart->calculate_taxes();
					$cart->init();
				}
							
			}else{
				$data = 'false';	
			}
			header('Content-Type: text/javascript; charset=UTF-8'); //set header
			echo json_encode($data); //display records in json format using json_encode
			exit;
		}
		
	break;
	case 'add_shipping_info':
		$data = array();
		$company = $_POST['company'];
		$service = $_POST['service'];
		$rate = $_POST['rate'];
		$estimated = $_POST['estimated'];
		$shipping_choice = $_POST['shipping_choice'];
		if($shipping_choice == 'freeshipping'){
			$data['shipping'] = nf_currency(0);
			$freeshipping = 1;
		}else{
			$data['shipping'] = nf_currency($rate);
			$freeshipping = 0;
		}
		if ($cart->add_shipping_info($company,$service,$rate,$estimated,$freeshipping)) {

			if(sizeof($cart->get_taxes())){
				$taxes_text = '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
				foreach($cart->get_taxes() as $value){
					$taxes_text .= '<tr>
							<th align="right" style="padding-bottom:3px; padding-right:6px"><strong>'.$value['name'].'</strong></th>
							<td align="right" width="60%" style="padding-bottom:3px">'.$value['total_taxes'].'</td>
						</tr>';
				}
				$taxes_text .= '</table>';
			}else{
				$taxes_text = '';	
			}
			$data['taxes'] = $taxes_text;
			$data['total'] = nf_currency($cart->total);
		}else{
			$data = 'false';	
		}
		header('Content-Type: text/javascript; charset=UTF-8'); //set header
		echo json_encode($data); //display records in json format using json_encode
		exit;
	break;
	case 'add_local_pickup_info':
		$data = array();
		$local_pickup_choice = $_POST['local_pickup_choice'];

		$query = 'SELECT 
		*	
		FROM 
		config_address_pickup
		WHERE id = "'.$local_pickup_choice.'"
		LIMIT 1';
		if ($result = $mysqli->query($query)) {
			if($result->num_rows){
				$row = $result->fetch_assoc();
		
				//Find the tax rule
				$id_tax_rule = $cart->get_id_tax_rule($row['country_code'],$row['state_code']);
			
				if (!$mysqli->query('UPDATE 
					cart
					SET
					local_pickup = 1,
					local_pickup_id = "'.$mysqli->escape_string($row['id']).'",
					local_pickup_address = "'.$mysqli->escape_string($row['address']).'",
					local_pickup_city = "'.$mysqli->escape_string($row['city']).'",
					local_pickup_country_code = "'.$mysqli->escape_string($row['country_code']).'",
					local_pickup_state_code = "'.$mysqli->escape_string($row['state_code']).'",
					local_pickup_zip = "'.$mysqli->escape_string($row['zip']).'",
					id_tax_rule = "'.$mysqli->escape_string($id_tax_rule).'",
					shipping_id = 0,
					shipping_firstname = "",
					shipping_lastname = "",
					shipping_company = "",
					shipping_address = "",
					shipping_city = "",
					shipping_country_code = "",
					shipping_state_code = "",
					shipping_zip = "",
					shipping_telephone = "",
					shipping_fax = "",
					shipping_gateway_company = "",
					shipping_service = "",
					shipping = 0,
					shipping_estimated = "",
					free_shipping = 0,
					shipping_validated = 0
					WHERE
					id = '.$cart->id.'
					LIMIT 1')){
				throw new Exception('An error occured while trying to update cart.'."\r\n\r\n".$mysqli->mysqli->error);}
			}
			$cart->init();
			$cart->calculate_subtotal();
			$cart->calculate_taxes();
			$cart->calculate_total();
			$cart->init();


			if(sizeof($cart->get_taxes())){
				$taxes_text = '<table border="0" cellpadding="0" cellspacing="0">';
				foreach($cart->get_taxes() as $value){
					$taxes_text .= '<tr>
							<th align="right" style="padding-bottom:3px; padding-right:6px"><strong>'.$value['name'].'</strong></th>
							<td align="right" width="110" style="padding-bottom:3px">'.$value['total_taxes'].'</td>
						</tr>';
				}
				$taxes_text .= '</table>';
			}else{
				$taxes_text = '';	
			}
			$data['taxes'] = $taxes_text;
			$data['total'] = nf_currency($cart->total);
		}else{
			$data = 'false';	
		}
		header('Content-Type: text/javascript; charset=UTF-8'); //set header
		echo json_encode($data); //display records in json format using json_encode
		exit;
	break;
}
?>