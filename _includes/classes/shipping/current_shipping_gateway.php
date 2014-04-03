<?php 
//Shipping Gateway
$query = 'SELECT * FROM shipping_gateway WHERE active = 1';
$shipping_gateway = 0;
$unit_measurement = ($config_site['measurement_unit']?'cm':'in');
if ($result = $mysqli->query($query)) {
	if($result->num_rows){
		$shipping_gateway = 1;
		$row = $result->fetch_assoc();
		$hide_arrival_date = $row['hide_arrival_date'];
		switch($row['class']){
			case 'UPSShipping':
				include_once(dirname(__FILE__).'/UPSShipping.php');
				$shipping_class = new UPSShipping($row['access_key'],$row['merchant_id'],$row['merchant_password'],$config_site['shipping_sender_city'],$config_site['shipping_sender_state_code'],$config_site['shipping_sender_country_code'],$config_site['shipping_sender_zip']);
				$shipping_name = $row['name'];
				if($row['logo'] and is_file(dirname(__FILE__).'/../../../_images/'.$row['logo'])){
					$shipping_gateway_logo = $row['logo'];
				}else{
					$shipping_gateway_logo = '';
				}
				$unit_weight = ($config_site['weighing_unit']?'kg':'lb');	
				$unit_measurement =	($config_site['weighing_unit']?'cm':'in');					
				break;
			case 'CanadaPostShipping':
				include_once(dirname(__FILE__).'/CanadaPostShipping.php');
				$shipping_class = new CanadaPostShipping($row['merchant_id']);
				$shipping_name = $row['name'];
				if($row['logo'] and is_file(dirname(__FILE__).'/../../../_images/'.$row['logo'])){
					$shipping_gateway_logo = $row['logo'];
				}else{
					$shipping_gateway_logo = '';
				}
				$unit_weight = 'kg';
				$unit_measurement = 'cm';
				$convert_yes_no = 0;
				// If $config_site['weighing_unit'] = 0, we have to convert the unit in kg and cm because Canada Post accept measure in kg and cm only so the variable $convert_yes_no tell to the function to convert into kg and cm
				if(!$config_site['weighing_unit']){
					$convert_yes_no = 1;
				}
				break;
			case 'CanparShipping':
				include_once(dirname(__FILE__).'/CanparShipping.php');
				$shipping_class = new CanparShipping($config_site['shipping_sender_city'],$config_site['shipping_sender_state_code'],$config_site['shipping_sender_country_code'],$config_site['shipping_sender_zip']);
				$shipping_name = $row['name'];
				if($row['logo'] and is_file(dirname(__FILE__).'/../../../_images/'.$row['logo'])){
					$shipping_gateway_logo = $row['logo'];
				}else{
					$shipping_gateway_logo = '';
				}
				$unit_weight = ($config_site['weighing_unit']?'K':'L');
				$unit_measurement =	($config_site['weighing_unit']?'cm':'in');		
				break;
			case 'FedExShipping':
				include_once(dirname(__FILE__).'/FedExShipping.php');
				$shipping_class = new FedExShipping($row['access_key'],$row['merchant_id'],$row['merchant_password'],$row['meter_number'],$config_site['shipping_sender_state_code'],$config_site['shipping_sender_country_code'],$config_site['shipping_sender_zip']);
				$shipping_name = $row['name'];
				if($row['logo'] and is_file(dirname(__FILE__).'/../../../_images/'.$row['logo'])){
					$shipping_gateway_logo = $row['logo'];
				}else{
					$shipping_gateway_logo = '';
				}
				$unit_weight = ($config_site['weighing_unit']?'kg':'lb');		
				$unit_measurement =	($config_site['weighing_unit']?'cm':'in');			
				break;				
				
		}
		$result->close();
	}
}
// End Shipping Gateway
?>