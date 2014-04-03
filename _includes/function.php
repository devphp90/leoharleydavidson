<?php

//--------------------------Fonctions non utilisées
function write_month($monthNumber, $monthLang = 'fr') {
	$month['en'][1] = 'january';
	$month['en'][2] = 'february';
	$month['en'][3] = 'march';
	$month['en'][4] = 'april';
	$month['en'][5] = 'may';
	$month['en'][6] = 'june';
	$month['en'][7] = 'jully';
	$month['en'][8] = 'august';
	$month['en'][9] = 'september';
	$month['en'][10] = 'october';
	$month['en'][11] = 'november';
	$month['en'][12] = 'december';
	
	$month['fr'][1] = 'janvier';
	$month['fr'][2] = 'février';
	$month['fr'][3] = 'mars';
	$month['fr'][4] = 'avril';
	$month['fr'][5] = 'mai';
	$month['fr'][6] = 'juin';
	$month['fr'][7] = 'juillet';
	$month['fr'][8] = 'août';
	$month['fr'][9] = 'septembre';
	$month['fr'][10] = 'octobre';
	$month['fr'][11] = 'novembre';
	$month['fr'][12] = 'décembre';
	
	return $month[$monthLang][$monthNumber];
}
			
function write_error_loc ($msg = '') {
	if (!empty($msg)) {
		foreach ($msg as $key => $value) {
			echo '<br /><span class="erreur_loc">* ' . $value . '</span>';
		}
	}
}

function write_error ($msg = '') {
	if (!empty($msg)) { echo '<div class="msg_erreur">' . $msg . '</div>'; }
}

function write_success ($msg = '') {

	if (!empty($msg)) { echo '<div class="msg_success">' . $msg . '</div>'; }
	
}

function write_mysql_date ($mysql_date, $lang = 'fr', $format=0, $complet=0) {
	$jour = date('d', strtotime($mysql_date));
	$mois = date('m', strtotime($mysql_date));
	$annee = date('Y', strtotime($mysql_date));
	
	if($complet){
		$heure = ' ' . date('H', strtotime($mysql_date)) . ':' . date('i', strtotime($mysql_date));
	}else{
		$heure = '';
	}
	
	if(!$format){
		$mois_en = date('M', strtotime($mysql_date));
		$moisArray["01"] = "jan";
		$moisArray["02"] = "fév";
		$moisArray["03"] = "mars";
		$moisArray["04"] = "avril";
		$moisArray["05"] = "mai";
		$moisArray["06"] = "juin";
		$moisArray["07"] = "juil";
		$moisArray["08"] = "août";
		$moisArray["09"] = "sept";
		$moisArray["10"] = "oct";
		$moisArray["11"] = "nov";
		$moisArray["12"] = "déc";
	}else{
		$mois_en = date('F', strtotime($mysql_date));
		$moisArray["01"] = "janvier";
		$moisArray["02"] = "février";
		$moisArray["03"] = "mars";
		$moisArray["04"] = "avril";
		$moisArray["05"] = "mai";
		$moisArray["06"] = "juin";
		$moisArray["07"] = "juillet";
		$moisArray["08"] = "août";
		$moisArray["09"] = "septembre";
		$moisArray["10"] = "octobre";
		$moisArray["11"] = "novembre";
		$moisArray["12"] = "décembre";
	}
	
	if ($lang == 'en') {
		return $mois_en . ' ' . $jour . ', ' . $annee . $heure;
	} else {
		return $jour . ' ' . $moisArray[$mois] . ' ' . $annee . $heure;
	}

}

function createRandomPassword() {

    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= 7) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }

    return $pass;
}

function curPageURLNosecure($secure=0) {
	$pageURL = 'http';
	if($secure){
		if ($_SERVER["HTTPS"] != "on") {
			$pageURL .= "s://";
			if ($_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $_SERVER["HTTP_HOST"]. $_SERVER["REQUEST_URI"];
			} else {
				$pageURL .= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
			}
			header('Location: ' . $pageURL);
		}
	}else{
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["HTTP_HOST"]. $_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		}
		if(strstr($pageURL, 'https:')){
			$newURL = str_replace('https:','http:',$pageURL);
			header('Location: ' . $newURL);
		}
	}
}

function mround($number, $precision=0) { 
    
    $precision = ($precision == 0 ? 1 : $precision);    
    $pow = pow(10, $precision); 
    
    $ceil = ceil($number * $pow)/$pow; 
    $floor = floor($number * $pow)/$pow; 
    
    $pow = pow(10, $precision+1); 
    
    $diffCeil     = $pow*($ceil-$number); 
    $diffFloor     = $pow*($number-$floor)+($number < 0 ? -1 : 1); 
    
    if($diffCeil >= $diffFloor) return $floor; 
    else return $ceil; 
} 

function makesafetitle($string) {
	$title = trim($string);
	$title = str_replace(' ','-',$title);
	$title = str_replace('\'','',$title);
	$title = strtolower($title);
	$title = str_replace(array('à','À','â','Â','é','É','è','È','ê','Ê','î','Î','ô','Ô','û','Û',' ','&','.','_','\'','/','"',':','(',')','!',',','+','%','?','#','ù','ã','º',chr(0xe2) . chr(0x80) . chr(0x98),chr(0xe2) . chr(0x80) . chr(0x99),chr(0xe2) . chr(0x80) . chr(0x9c),chr(0xe2) . chr(0x80) . chr(0x9d),chr(0xe2) . chr(0x80) . chr(0x93),chr(0xe2) . chr(0x80) . chr(0x94),'','','®','™','',''),array('a','a','a','a','e','e','e','e','e','e','i','i','o','o','u','u','-','et','-','-','','','','-','-','','','','','','','','u','a','o','','','','','','','','','','','',''),$title);	
	
	return $title;
}

//--------------------------END Fonctions non utilisées

function language($page,$word,$arr = array(),$language=""){
	$arr_langue = include(dirname(__FILE__) . "/../_language/".(!empty($language)?$language:$_SESSION['customer']['language'])."/" . $page . ".php");
	$arr_langue_modif = @include(dirname(__FILE__) . "/../_language/".(!empty($language)?$language:$_SESSION['customer']['language'])."/" . $page . "_custom.php");
	$arr_langue_modif = is_array($arr_langue_modif) ? $arr_langue_modif:array();
	
	if (isset($arr_langue_modif[$word]) && !empty($arr_langue_modif[$word])) $new_word = $arr_langue_modif[$word];
	else $new_word = $arr_langue[$word];
	
	if(sizeof($arr)){
		foreach($arr as $key=>$value){
			$new_word = str_replace('{'.$key.'}',$value,$new_word);
		}
	}
	return($new_word);	
}


function get_country_list()
{
	global $mysqli;

	$array=array();
    
    if ($result = $mysqli->query('SELECT
    country.code,
    country_description.name
    FROM
    country
    INNER JOIN
    country_description
    ON
    (country.code = country_description.country_code AND country_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
    ORDER BY
    country_description.name ASC')) {
		while ($row = $result->fetch_assoc()) {
	    	$array[$row['code']] = $row['name'];
		}
    }   
	
	$result->close();

	return $array;
}

function get_state_list($country_code)
{
	global $mysqli;

	$array=array();
    
    if ($result = $mysqli->query('SELECT
    state.code,
    state_description.name
    FROM
    state
    INNER JOIN
    state_description
    ON
    (state.code = state_description.state_code AND state_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
	WHERE
	state.country_code = "'.$mysqli->escape_string($country_code).'"
    ORDER BY
    state.code ASC')) {
		while ($row = $result->fetch_assoc()) {
	    	$array[$row['code']] = $row['name'];
		}
    }   
	
	$result->close();

	return $array;
}

function validate_fields(&$fields=array(),$validation=array(),$messages=array()) {
	$errors=array();

	if (sizeof($fields) && sizeof($validation)) {
		// loop through each post value and trim
		foreach ($fields as $key => $value) {
			$fields[$key] = is_array($value) ? $value:trim($value);
		}		
		
    	foreach ($validation as $key => $rules) {
			$field_value = $fields[$key];
			
        	foreach ($rules as $rule => $value) {
            	switch ($rule) {
                	case 'required':
                    	if (empty($field_value)) {
							$errors[$key] = isset($messages[$key][$rule]) ? $messages[$key][$rule]:language('_include/function','ERROR_REQUIRED'); 
							break 2;
						}
                    	break;
                    case 'minlen':
                    	$minlen = (int)$value;
                        
                        if ($minlen > 0 && strlen($field_value) < $minlen) {
							$errors[$key] = isset($messages[$key][$rule]) ? $messages[$key][$rule]:language('_include/function','ERROR_MIN_LENGTH').' '.$minlen; 
							break 2;
						}
                    	break;
                    case 'maxlen':
                    	$maxlen = (int)$value;
                        
                        if ($maxlen > 0 && strlen($field_value) > $maxlen) {
							$errors[$key] = isset($messages[$key][$rule]) ? $messages[$key][$rule]:language('_include/function','ERROR_MAX_LENGTH').' '.$maxlen; 
							break 2;                    
						}
                    	break;
                    case 'alpha':
						// validate value must be alphanumeric only or absolutely alphanumeric?
                    	break;
					case 'numeric':
						if (!is_numeric($field_value)) {
							$errors[$key] = isset($messages[$key][$rule]) ? $messages[$key][$rule]:language('_include/function','ERROR_NOT_NUMERIC'); 
							break 2;            
						}
						break;
					case 'email':
						/*
						if (!filter_var($field_value, FILTER_VALIDATE_EMAIL)) {
							$errors[$key] = isset($messages[$key][$rule]) ? $messages[$key][$rule]:'Invalid email.'; break 2;
						}*/
					
                    	if (!preg_match("/^[_a-zA-Z0-9-.]+@[_a-zA-Z0-9-.]+\.[a-z]+/i", $field_value)) {
							$errors[$key] = isset($messages[$key][$rule]) ? $messages[$key][$rule]:language('_include/function','ERROR_INVALID_EMAIL');
							break 2;
						}
                    	break;
                    case 'equal':
                        if ($field_value != $fields[$value]) {
							$errors[$key] = isset($messages[$key][$rule]) ? $messages[$key][$rule]:language('_include/function','ERROR_DONT_MATCH'); 
							//$errors[$key] = $errors[$value];
							break 2;                    
						}
                    	break;		
					case 'required_if_empty':
						if (empty($fields[$value]) && empty($field_value)) {
							$errors[$key] = isset($messages[$key][$rule]) ? $messages[$key][$rule]:language('_include/function','ERROR_REQUIRED'); 
							break 2;            
						}
						break;	
					case 'date':
						if (!empty($field_value)) { 
							$stamp = strtotime($field_value); 
							
							if (!is_numeric($stamp)) {
								$errors[$key] = isset($messages[$key][$rule]) ? $messages[$key][$rule]:language('_include/function','ERROR_INVALID_DATE'); 
								break 2;            
							}
							
							$month = date( 'm', $stamp ); 
							$day   = date( 'd', $stamp ); 
							$year  = date( 'Y', $stamp ); 
							
							if (!checkdate($month, $day, $year)) {
								$errors[$key] = isset($messages[$key][$rule]) ? $messages[$key][$rule]:language('_include/function','ERROR_INVALID_DATE'); 
								break 2; 
							}
						}
						break;		
					case 'callback':
						if ($value($field_value)) {
							$errors[$key] = isset($messages[$key][$rule]) ? $messages[$key][$rule]:language('_include/function','ERROR'); 	
							break 2;
						}
						break;
                }
            }
        }
    }
	
	return $errors;
}

function include_mailer()
{
	include('classes/PHPMailer_v5.1/class.phpmailer.php');
}

function login($email, $password, $remember_me=0, $no_password=0)
{
	unset($_SESSION['customer']['id'],
	$_SESSION['customer']['name'],
	$_SESSION['customer']['email'],
	$_SESSION['customer']['dob'],
	$_SESSION['customer']['id_customer_type'],
	$_SESSION['customer']['apply_on_rebate']);
	
	global $mysqli, $cart, $config_site;
	
	if ($result = $mysqli->query('SELECT 
	customer.id,
	customer.firstname,
	customer.lastname,
	customer.email,
	customer.dob,
	customer.password,
	customer.id_customer_type,
	customer_type.apply_on_rebate,
	IF(customer_type.id IS NOT NULL,customer_type.taxable,1) AS taxable,
	customer_shipping_address.id AS customer_shipping_address_id,
	customer_shipping_address.firstname AS customer_shipping_address_firstname,
	customer_shipping_address.lastname AS customer_shipping_address_lastname,
	customer_shipping_address.company AS customer_shipping_address_company,
	customer_shipping_address.telephone AS customer_shipping_address_telephone,
	customer_shipping_address.fax AS customer_shipping_address_fax,
	customer_shipping_address.id AS customer_shipping_address_id,
	customer_shipping_address.address AS customer_shipping_address_address,
	customer_shipping_address.city AS customer_shipping_address_city,
	customer_shipping_address.country_code AS customer_shipping_address_country_code,
	customer_shipping_address.state_code AS customer_shipping_address_state_code,
	customer_shipping_address.zip AS customer_shipping_address_zip,
	customer_billing_address.id AS customer_billing_address_id,
	customer_billing_address.firstname AS customer_billing_address_firstname,
	customer_billing_address.lastname AS customer_billing_address_lastname,
	customer_billing_address.company AS customer_billing_address_company,
	customer_billing_address.telephone AS customer_billing_address_telephone,
	customer_billing_address.fax AS customer_billing_address_fax,
	customer_billing_address.id AS customer_billing_address_id,
	customer_billing_address.address AS customer_billing_address_address,
	customer_billing_address.city AS customer_billing_address_city,
	customer_billing_address.country_code AS customer_billing_address_country_code,
	customer_billing_address.state_code AS customer_billing_address_state_code,
	customer_billing_address.zip AS customer_billing_address_zip
	FROM				
	customer 
	LEFT JOIN
	customer_type 
	ON
	(customer.id_customer_type = customer_type.id)
	LEFT JOIN customer_address AS customer_shipping_address ON customer.id = customer_shipping_address.id_customer AND customer_shipping_address.default_shipping = 1
	LEFT JOIN customer_address AS customer_billing_address ON customer.id = customer_billing_address.id_customer AND customer_billing_address.default_billing = 1
	WHERE
	email = "'.$mysqli->escape_string($email).'"
	AND 
	active = 1
	LIMIT 1')) {
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			
			$password = md5($row['id'].$password);
			
			if ($password == $row['password'] || $no_password) {
				$_SESSION['customer']['id'] = $row['id'];
				$_SESSION['customer']['name'] = $row['firstname'].' '.$row['lastname'];
				$_SESSION['customer']['email'] = $row['email'];
				$_SESSION['customer']['dob'] = $row['dob'];
				$_SESSION['customer']['id_customer_type'] = $row['id_customer_type'];
				$_SESSION['customer']['apply_on_rebate'] = $row['apply_on_rebate'];
				
				// Verify if a cart already exist and put Shipping and billing default address in it
				if($cart->id){
					
					// Remove customer Infos in cart table
					if (!$mysqli->query('UPDATE 
							cart
							SET
							id_customer = 0,
							id_customer_type = 0,
							billing_id = 0,
							billing_firstname = "",
							billing_lastname = "",
							billing_company = "",
							billing_address = "",
							billing_city = "",
							billing_country_code = "",
							billing_state_code = "",
							billing_zip = "",
							billing_telephone = "",
							billing_fax = "",
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
							taxes = 0,
							gift_certificates 	 = 0,
							total = 0,
							grand_total = 0,
							free_shipping = 0,
							id_tax_rule = 0,
							local_pickup = 0
							WHERE
							id = "'.$cart->id.'"
							LIMIT 1')){
						throw new Exception('An error occured while trying to update cart.'."\r\n\r\n".$mysqli->mysqli->error);		
					}
					
					// Verify if free shipping is enable and if it's applicable
					$free_shipping = $cart->get_free_shipping_yes_no($row['customer_shipping_address_country_code'],$row['customer_shipping_address_state_code']);
					
					if(!$config_site['enable_shipping'] and $config_site['enable_local_pickup']){
						//Find the tax rule
						$id_tax_rule = $row['taxable'] ? $cart->get_id_tax_rule($config_site['company_country_code'],$config_site['company_state_code']):0;
						$local_pickup = 1;
					}else{
						//Find the tax rule
						$id_tax_rule = $row['taxable'] ? $cart->get_id_tax_rule($row['customer_shipping_address_country_code'],$row['customer_shipping_address_state_code'],$row['customer_shipping_address_zip']):0;	
						$local_pickup = 0;
					}

					
					if (!$mysqli->query('UPDATE 
							cart
							SET
							id_customer = "'.$row['id'].'",
							id_customer_type = "'.$row['id_customer_type'].'",
							billing_id = "'.$row['customer_billing_address_id'].'",
							billing_firstname = "'.$row['customer_billing_address_firstname'].'",
							billing_lastname = "'.$row['customer_billing_address_lastname'].'",
							billing_company = "'.$row['customer_billing_address_company'].'",
							billing_address = "'.$row['customer_billing_address_address'].'",
							billing_city = "'.$row['customer_billing_address_city'].'",
							billing_country_code = "'.$row['customer_billing_address_country_code'].'",
							billing_state_code = "'.$row['customer_billing_address_state_code'].'",
							billing_zip = "'.$row['customer_billing_address_zip'].'",
							billing_telephone = "'.$row['customer_billing_address_telephone'].'",
							billing_fax = "'.$row['customer_billing_address_fax'].'",
							shipping_id = "'.$row['customer_shipping_address_id'].'",
							shipping_firstname = "'.$row['customer_shipping_address_firstname'].'",
							shipping_lastname = "'.$row['customer_shipping_address_lastname'].'",
							shipping_company = "'.$row['customer_shipping_address_company'].'",
							shipping_address = "'.$row['customer_shipping_address_address'].'",
							shipping_city = "'.$row['customer_shipping_address_city'].'",
							shipping_country_code = "'.$row['customer_shipping_address_country_code'].'",
							shipping_state_code = "'.$row['customer_shipping_address_state_code'].'",
							shipping_zip = "'.$row['customer_shipping_address_zip'].'",
							shipping_telephone = "'.$row['customer_shipping_address_telephone'].'",
							shipping_fax = "'.$row['customer_shipping_address_fax'].'",
							free_shipping = "'.$free_shipping.'",
							id_tax_rule = "'.$id_tax_rule.'",
							local_pickup = "'.$local_pickup.'"
							WHERE
							id = '.$cart->id.'
							LIMIT 1')){
						throw new Exception('An error occured while trying to set remember me.'."\r\n\r\n".$mysqli->mysqli->error);		
					}
				}

				if ($remember_me) {
					$remember_me_key = md5($row['id'].$row['email'].time());
					
					if ($mysqli->query('UPDATE
					customer
					SET
					remember_me_key = "'.$mysqli->escape_string($remember_me_key).'"
					WHERE
					id = "'.$mysqli->escape_string($row['id']).'"
					LIMIT 1')) {
						set_cookie_value('remember_me_key', $remember_me_key);	
					} else {
						throw new Exception('An error occured while trying to set remember me.'."\r\n\r\n".$mysqli->mysqli->error);		
					}
				}				
				return true;
			}	
		}	
		
		$result->free();
	} else {
		throw new Exception('An error occured while trying to login.'."\r\n\r\n".$mysqli->mysqli->error);			
	}		
	
	return false;		
}

function logout()
{
	global $mysqli, $cart;
	
	if ($remember_me_key = get_cookie_value('remember_me_key')) {
		if (!$mysqli->query('UPDATE
		customer
		SET
		remember_me_key = ""
		WHERE
		id = "'.$mysqli->escape_string($_SESSION['customer']['id']).'" 
		LIMIT 1')) {
			throw new Exception('An error occured while trying to update account.'."\r\n\r\n".$mysqli->mysqli->error);		
		}	
		unset_cookie_value('remember_me_key');	
	}
	
	// Remove customer Infos in cart table
	if (!$mysqli->query('UPDATE 
			cart
			SET
			id_customer = 0,
			id_customer_type = 0,
			billing_id = 0,
			billing_firstname = "",
			billing_lastname = "",
			billing_company = "",
			billing_address = "",
			billing_city = "",
			billing_country_code = "",
			billing_state_code = "",
			billing_zip = "",
			billing_telephone = "",
			billing_fax = "",
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
			taxes = 0,
			gift_certificates 	 = 0,
			total = 0,
			grand_total = 0,
			free_shipping = 0,
			id_tax_rule = 0,
			local_pickup = 0
			WHERE
			id = "'.$cart->id.'"
			LIMIT 1')){
		throw new Exception('An error occured while trying to update cart.'."\r\n\r\n".$mysqli->mysqli->error);		
	}
	
	
	
	unset($_SESSION['customer']['id'], 
	$_SESSION['customer']['name'], 
	$_SESSION['customer']['email'], 
	$_SESSION['customer']['dob'], 
	$_SESSION['customer']['id_customer_type'],
	$_SESSION['customer']['id_customer_courses_scorm']);
	
}

function set_cookie_value($key, $value)
{
	$array=array();	
	
	if (isset($_COOKIE['sc_setting']) && !empty($_COOKIE['sc_setting'])) $array = unserialize(base64_decode($_COOKIE["sc_setting"]));		

	$array[$key] = $value;
	
	$_COOKIE['sc_setting'] = base64_encode(serialize($array));
	
	setcookie ("sc_setting", $_COOKIE['sc_setting'], time()+60*60*24*30,"/");
	
	
}

function unset_cookie_value($key)
{
	$array=array();	
	
	if (isset($_COOKIE['sc_setting']) && !empty($_COOKIE['sc_setting'])) $array = unserialize(base64_decode($_COOKIE["sc_setting"]));		

	if (isset($array[$key])) unset($array[$key]);
	
	setcookie ("sc_setting", base64_encode(serialize($array)), time()+60*60*24*30,"/");
}

function get_cookie_value($key)
{
	$value='';
	
	if (isset($_COOKIE['sc_setting']) && !empty($_COOKIE['sc_setting'])) {
		$array = unserialize(base64_decode($_COOKIE["sc_setting"]));	
		$value = isset($array[$key]) ? $array[$key]:'';
	}
	
	return $value;
}

function generate_password($id_customer, $password)
{
	global $mysqli;
	
	if (empty($password)) throw new Exception('An error occured, password can\'t be empty.'."\r\n\r\n".$mysqli->mysqli->error);		
	
	if (!$result = $mysqli->query('SELECT 
	email
	FROM
	customer
	WHERE
	id = "'.$mysqli->escape_string($id_customer).'"
	LIMIT 1')) throw new Exception('An error occured while trying to get account information.'."\r\n\r\n".$mysqli->mysqli->error);	
	
	if (!$result->num_rows) throw new Exception('An error occured while trying to get account information, invalid customer.'."\r\n\r\n".$mysqli->error);	
	
	$row = $result->fetch_assoc();
	
	$result->close();
	
	return md5($id_customer.$password);
}

function get_images_sizes()
{
	$array=array();
	
	global $config_site;
	
	switch ($config_site['images_orientation']) {
		case 'portrait':
			$array = array(
				'thumb_width' => $config_site['portrait_thumb_width'],
				'thumb_height' => $config_site['portrait_thumb_height'],
				'suggest_width' => $config_site['portrait_suggest_width'],
				'suggest_height' => $config_site['portrait_suggest_height'],
				'listing_width' => $config_site['portrait_listing_width'],
				'listing_height' => $config_site['portrait_listing_height'],
				'cover_width' => $config_site['portrait_cover_width'],
				'cover_height' => $config_site['portrait_cover_height'],
				'zoom_width' => $config_site['portrait_zoom_width'],
				'zoom_height' => $config_site['portrait_zoom_height'],				
			);
			break;
		case 'landscape':
			$array = array(
				'thumb_width' => $config_site['landscape_thumb_width'],
				'thumb_height' => $config_site['landscape_thumb_height'],
				'suggest_width' => $config_site['landscape_suggest_width'],
				'suggest_height' => $config_site['landscape_suggest_height'],
				'listing_width' => $config_site['landscape_listing_width'],
				'listing_height' => $config_site['landscape_listing_height'],
				'cover_width' => $config_site['landscape_cover_width'],
				'cover_height' => $config_site['landscape_cover_height'],
				'zoom_width' => $config_site['landscape_zoom_width'],
				'zoom_height' => $config_site['landscape_zoom_height'],				
			);		
			break;	
	}
	
	return $array;	
}

function get_rated_star($rating, $size=""){
	$total_display = 5;
	$result = '';
	$int_part = floor($rating);
	
	for($x=0;$x<$total_display;$x++){
		if($x < $int_part){
			$result .= '<div class="rating_star'.$size.' rating_star_full'.$size.'"><img src="/_images/star_rating_full.png" alt="" /></div>';
		}else{
			$result .= '<div class="rating_star'.$size.' rating_star_empty'.$size.'"><img src="/_images/star_rating_empty.png" alt="" /></div>';
		}
	}
	return $result;
}

function get_product_price($id_product, $id_product_variant=0)
{
	global $mysqli;	
	
	if ($result = $mysqli->query('SELECT
	get_product_current_price(product.id,product_variant.id) AS sell_price
	FROM
	product
	LEFT JOIN
	product_variant 
	ON
	(product.id = product_variant.id_product AND product_variant.id = "'.$mysqli->escape_string($id_product_variant).'")
	WHERE
	product.id = "'.$mysqli->escape_string($id_product).'" 
	LIMIT 1')) {
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			$result->close();
			
			return $row['sell_price'];
		} else {
			throw new Exception('An error occured while trying to get product price. Invalid product.');			
		}
	} else {
		throw new Exception('An error occured while trying to get product price.'."\r\n\r\n".$mysqli->error);		
	}
}

function get_qty_remaining($id_product, $id_product_variant=0)
{
	global $mysqli, $config_site;
	
	$qty=0;
	
	if ($result_qty_remaining = $mysqli->query('SELECT 
	qty_in_stock("'.$mysqli->escape_string($id_product).'","'.$mysqli->escape_string($id_product_variant).'") AS qty')) {
		$row_qty_remaining = $result_qty_remaining->fetch_assoc();
		
		$result_qty_remaining->free();

		$qty=$row_qty_remaining['qty'];
	}		
	
	return $qty;
}

function nf_currency_obj()
{
	global $config_site;
	
	return new NumberFormatter($_SESSION['customer']['language'].'_'.$config_site['company_country_code'], NumberFormatter::CURRENCY); 
}

function nf_currency($number=0,$display_currency=1)
{
	$nf = nf_currency_obj();
	return $nf->formatCurrency(round($number,2), $_SESSION['customer']['currency']).($display_currency?' '.$_SESSION['customer']['currency']:'');
}

function nf_currency_js($str)
{
	$nf = nf_currency_obj();
	$before_prefix = $nf->getTextAttribute(NumberFormatter::PAD_BEFORE_PREFIX);	
	$after_prefix = $nf->getTextAttribute(NumberFormatter::PAD_AFTER_PREFIX);
	
	return (!empty($before_prefix) ? '"'.$before_prefix.'"+':'').'number_format('.$str.',2,"'.$nf->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL).'","'.$nf->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL).'")'.(!empty($after_prefix) ? '+"'.$after_prefix.'"':'').'+" '.$_SESSION['customer']['currency'].'"';
}

function df_date_obj($format_date, $format_time)
{
	global $config_site;
	/*
	-1=IntlDateFormatter::NONE
	0=IntlDateFormatter::FULL
	1=IntlDateFormatter::LONG
	2=IntlDateFormatter::MEDIUM
	3=IntlDateFormatter::SHORT
	*/
	return new IntlDateFormatter( $_SESSION['customer']['language'].'_'.$config_site['company_country_code'] ,$format_date, $format_time,'',IntlDateFormatter::GREGORIAN  );

}

function df_date($date, $format_date=1, $format_time=3)
{
	$df = df_date_obj($format_date, $format_time);
	return $df->format(strtotime(date($date)));

}

function get_variant_groups($id_product)
{
	global $mysqli;
	
	$groups=array();
	
	// build sql 
	// get all groups for this product
	if ($result_group = $mysqli->query('SELECT
	product_variant_group.id,
	product_variant_group.input_type,
	product_variant_group_description.name
	FROM
	product_variant
	INNER JOIN
	(product_variant_option CROSS JOIN product_variant_group CROSS JOIN product_variant_group_description)
	ON
	(product_variant.id = product_variant_option.id_product_variant AND product_variant_option.id_product_variant_group = product_variant_group.id AND product_variant_group.id = product_variant_group_description.id_product_variant_group AND product_variant_group_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
	WHERE
	product_variant.id_product = "'.$mysqli->escape_string($id_product).'"
	GROUP BY 
	product_variant_group.id
	ORDER BY
	product_variant_group.sort_order ASC')) {
		if ($result_group->num_rows) {
			while ($row_group = $result_group->fetch_assoc()) {
				$groups[$row_group['id']] = $row_group;	
			}
		} 
	} else { 
		throw new Exception('An error occured while trying to get variant groups.'."\r\n\r\n".$mysqli->error);	
	}
	
	$result_group->free();
	
	return $groups;	
}

// get variant id
function get_variant_id($id_product, $variant_options=array())
{
	global $mysqli;
	
	$groups = get_variant_groups($id_product);
	
	// build sql 
	// get all groups for this product
	if (sizeof($groups) && sizeof($groups) != sizeof($variant_options)) {
		return 0;
	} else {	
		$joins = array();	
		$where = array();		
		$group_by = array();	
		
		$i=1;
		foreach ($groups as $id_product_variant_group => $row_group) {	
			$where_str = array();
			
			$joins[] = 'INNER JOIN
			(product_variant_option AS pvo'.$i.')
			ON
			(product_variant.id = pvo'.$i.'.id_product_variant)';
		
			$where_str[] = 'pvo'.$i.'.id_product_variant_group = "'.$mysqli->escape_string($id_product_variant_group).'"';		
			$where_str[] = 'pvo'.$i.'.id_product_variant_group_option = "'.$mysqli->escape_string($variant_options[$id_product_variant_group]).'"';
			
			$where[] = implode(' AND ',$where_str);
			
			++$i;
		}
		
		$joins = implode("\r\n",$joins);
		$where = implode(' AND ',$where);
		$group_by = implode(',',$group_by);
		
		$array=array();
						
		if ($result = $mysqli->query('SELECT
		product_variant.id
		FROM
		product_variant
		INNER JOIN
		product
		ON
		(product_variant.id_product = product.id)
		
		'.$joins.'
		
		WHERE
		product_variant.id_product = "'.$mysqli->escape_string($id_product).'"
		AND
		'.$where)) {		
			// if we find variant images
			$row = $result->fetch_assoc();
			$result->free();
				
			return $row['id'];			
		} else { 
			throw new Exception('An error occured while trying to get variant id.'."\r\n\r\n".$mysqli->error);	
		}	
	}
}

function display_product($row, $image_width, $image_height, $url_review_return, $column='full', $image_by_row=1, $counter=1, $display_type = 'listing_vertical'){
	global $url_prefix;
	/*
	display_product($row, $image_width, $image_height, $image_by_row=1, $counter=1, $display_type = 'listing')
	$row = product infos (array)
	$image_width = width of the image
	$image_height = height of the image
	$url_review_return = If you click on the Review Link (Stars) and you are not logged, after you log, you will return to this URL
	$image_by_row = Number of image by row
	$counter = Counter to know if we are at the end of the row to remove de margin padding
	$display_type = CSS that we want to use
	*/
	global $config_site, $mysqli;

	switch($display_type){
		case 'listing_vertical':// product are listed vertically from left to right
			if (!empty($row['image'])){
			   $image_src = $row['image'];
			   $image_size = getimagesize(dirname(__FILE__).'/../'.$image_src);
			}else{ 
				$image_src = get_blank_image('listing');
				$image_size[3] = ' width="'.$image_width.'" height="'.$image_height.'"';
			}

			$url_review_return .= (strstr($url_review_return,'?') ? '&':'?'). 'review_on='.$row['id'];
			$url_review_return = urlencode($url_review_return);

			switch($column){
				case 'full':	
					$style_padding_right = ' image_listing_padding_right';
				break;
				case 'center':	
					$style_padding_right = ' image_listing_center_column_padding_right';
				break;
				case 'center_right':	
					$style_padding_right = ' image_listing_center_right_column_padding_right';
				break;
				case 'left':	
					$style_padding_right = ' image_listing_left_column_padding_right';
				break;
				case 'right':	
					$style_padding_right = ' image_listing_right_column_padding_right';
				break;
			}			
			
			$produit .= '<div class="image_listing'.$style_padding_right.'"'.((!($counter % $image_by_row)) ? ' style="margin-right:0"':'').'>
			<div class="image"><a href="'.$row['url'];
					
			$produit .= '" title="'.htmlspecialchars($row['name'].' '.($row['variant_name'] ? $row['variant_name']:'')).'">'.((isset($row['in_stock']) and !$row['in_stock'])?'<div class="out_of_stock">'.language('catalog', 'TITLE_OUT_STOCK').'</div>':'').'<img src="'.$image_src.'" alt="'.$row['name'].'" '.$image_size[3].' /></a></div>
			<div class="image_listing_name_product"><a href="'.$row['url'].'">'.$row['name'].'</a>'.($row['variant_name'] ? '<div style="margin-top:5px; font-size:11px;">'.$row['variant_name'].'</div>':'').'</div>'.($config_site['show_short_desc_listing']?'<div class="image_listing_short_desc">'.substr($row['short_desc'],0,100).(strlen($row['short_desc'])>100?'...':'').'</div>':'').'
			
			
			'.($config_site['display_menu_rate_product']?'
			<div class="star_rated">
			<div style="float:right">
			<a href="javascript:void(0);" onclick="javascript: open_review('.$row['id'].',\'\',\''.$url_review_return.'\');"><div style="float:right; cursor: pointer">'. get_rated_star($row['avg_rating']).'&nbsp;('.($row['total_rating'] ? $row['total_rating']:0).')
			</div></a>
			</div>
			</div>':'').'
			<div class="cb"></div>
			'.($config_site['display_price'] && !$row['display_price_exception'] || !$config_site['display_price'] && $row['display_price_exception']?'
			<div class="price_special_date">'.($row['on_sale_end_date'] != '0000-00-00 00:00:00'?language('catalog', 'TITLE_UNTIL').'<br />'.df_date($row['on_sale_end_date'],1):'').'</div>
			
			<div class="price'.($row['on_sale']?' special_price':'').'">'.($row['has_variants'] && !$row['single_variant']?'<div style="font-size:9px; margin-top:5px; line-height:5px; color: #000">'.language('catalog', 'LABEL_STARING_AT').'</div>':'<div style="font-size:9px; margin-top:5px; line-height:5px;">&nbsp;</div>').nf_currency($row['sell_price']).'</div>'.($row['min_qty']?' <span style="font-size: 9px;">('.language('global', 'TITLE_QTY_MINIMUM').' '.$row['min_qty'].')</span>':''):'').'
			</div>';		
			break;
		case 'listing_horizontal':// product are listed horizontaly from top to bottom
			if(is_file(dirname(__FILE__) . '/../images/products/listing/'.$row['filename'])){
				$image_src = '/images/products/listing/'.$row['filename'];
			}else{
				$image_src = get_blank_image('listing');
			}
			
			break;
		case 'suggest':// suggested product
			if (is_file(dirname(__FILE__) . '/..'.$row['image_suggest'])){
			   $image_src = $row['image_suggest'];
			   $image_size = getimagesize(dirname(__FILE__).'/../'.$image_src);
			}else{ 
				$image_src = get_blank_image('suggest');
				$image_size[3] = ' width="'.$image_width.'" height="'.$image_height.'"';
			}
			
			
			$produit .= '<div class="image_listing image_suggest">
			<div class="image image_suggest_listing"><a href="'.$row['url'].'">'.((isset($row['in_stock']) and !$row['in_stock'])?'<div class="out_of_stock">'.language('catalog', 'TITLE_OUT_STOCK').'</div>':'').'<img src="'.$image_src.'" alt="'.$row['name'].'" '.$image_size[3].' /></a></div>
			<div class="image_listing_name_product"><a href="'.$row['url'].'">'.$row['name'].'</a></div>
			
			<div class="star_rated">
			<div style="float:right">
			<!--<div style="float:right;">
			<div style="float:right; margin-left: 3px">'.language('catalog', 'TITLE_COMPARE').'</div>
			<div style="float:right"><input name="compare" type="checkbox" value="1" style="margin:0;width:13px;height:13px;overflow:hidden;" /></div>
			<div class="cb"></div>
			</div><div class="cb"></div>-->
			'.($config_site['display_menu_rate_product']?'
			<a href="javascript:void(0);" onclick="javascript: open_review('.$row['id'].',\'\',\''.$url_review_return.'\');"><div style="float:right; cursor: pointer">'. get_rated_star($row['average_rated']).'&nbsp;('.($row['total_rated'] ? $row['total_rated']:0).')
			</div></a>':'').'
			</div>
			</div><div class="cb"></div>
			'.($config_site['display_price'] && !$row['display_price_exception'] || !$config_site['display_price'] && $row['display_price_exception']?'
			<div class="price_special_date">'.(!empty($row['on_sale_end_date']) && $row['on_sale_end_date'] != '0000-00-00 00:00:00'?language('catalog', 'TITLE_UNTIL').'<br />'.df_date($row['on_sale_end_date'],2):'').'</div>
			
			<div class="price'.($row['price'] != $row['sell_price']?' special_price':'').'">'.($row['has_variants']?'<div style="font-size:9px; margin-top:5px; line-height:5px; color: #000">'.language('_include/function', 'LABEL_STARING_AT').'</div>':'<div style="font-size:9px; margin-top:5px; line-height:5px;">&nbsp;</div>').nf_currency($row['sell_price']).'</div>':'').'
			</div>';
			
			break;
	}
	
	
	
	return $produit;
}

//Page = 0 (step_shipping) Page = 1 (modify_address)
function list_address($address_type,$page=0){
	global $mysqli, $config_site, $shipping_address, $cart;
	
	$list = '<ul>';
				if(($config_site['enable_shipping'] and $address_type == 'shipping') or $address_type == 'billing'){
				  $query = 'SELECT 
				  				customer_address.*,
								country_description.name AS country,
								state_description.name AS state
								FROM 
								customer_address 
								INNER JOIN country_description ON customer_address.country_code = country_description.country_code AND country_description.language_code = "'.$_SESSION['customer']['language'].'"
								LEFT JOIN state_description ON customer_address.state_code = state_description.state_code AND state_description.language_code = "'.$_SESSION['customer']['language'].'"
								WHERE id_customer = "'.$_SESSION['customer']['id'].'" AND (address_type = "' . $address_type . '" OR use_in = 0)
								ORDER BY default_'.$address_type.' DESC, customer_address.address
								';
								
								
					if ($result = $mysqli->query($query)) {
						$counter = 0;
						while($row = $result->fetch_assoc()){
							$counter++;
							$list .= '
							<li class="address'.(($counter==$result->num_rows)?' last"':'').'">';
							
							// $shipping_gateway is a variable use in step_shipping.php
							if($address_type == 'shipping'){
								if($cart->shipping_id == $row['id'] or (empty($cart->shipping_id) and $row['default_shipping'])){
									$checked = 1;
								}else{
									$checked = 0;
								}
							}else if($row['default_'.$address_type]){
								$checked = 1;
							}else{
								$checked = 0;
							}
							
							
								
							$list .= !$page?'<div style="float: left;"><input name="'.$address_type.'_address" type="radio" value="'.$row['id'].'" '.($checked?'checked':'').' id="'.$address_type.'_address_'.$row['id'].'" /></div>':'';
							
							$list .= '<div style="float: left; '.(!$page?'margin-left: 10px;':'').'"><label for="'.$address_type.'_address_'.$row['id'].'">
							'.($row['company']?'<strong>'.$row['company'].'</strong>'.($row['default_'.$address_type.'']?' (<em>'.language('global', 'TITLE_DEFAULT_ADDRESS').'</em>)':'').'<br />':'').'
							'.($row['company']?$row['firstname']. ' ' .$row['lastname'].'<br />':'<strong>'.$row['firstname']. ' ' .$row['lastname'].'</strong>'.($row['default_'.$address_type.'']?' (<em>'.language('global', 'TITLE_DEFAULT_ADDRESS').'</em>)':'').'<br />').'
							
							'.$row['address'].'<br />
							'.$row['city'].
							($row['state']?' '.$row['state']:'').'<br />
							'.$row['country'].' '.strtoupper($row['zip']).'
							</label>
							</div>
							<div style="float: right; margin-left: 30px;">
							<div><input type="button" value="'.language('global', 'BTN_MODIFY').'" class="button" name="btn_modify_'.$address_type.'_address" onclick="edit_address(\'form_'.$address_type.'\',\'display_form_'.$address_type.'\','.$row['id'].',\''.$address_type.'\');" /></div>'.(($row['default_shipping'] or $row['default_billing'])?'':'<div style="margin-top: 5px;"><input type="button" value="'.language('global', 'BTN_DELETE').'" class="title_bg_button title_bg_button_inbox title_bg_button_delete title_bg_button_small" name="btn_delete_'.$address_type.'_address" onclick="delete_address(\'form_'.$address_type.'\','.$row['id'].',\''.$address_type.'\','.$row['use_in'].');" /></div>').'
							</div><div class="cb"></div>
							</li>';
						} 
						
						
						
						
						$result->close();
					}
				}
					
			// check if we allow local pickup			
		// get cart products	
		if (!$page) {
			$display_local_pickup = 0;
			$enable_local_pickup = 1;		
			
			if (!$result = $mysqli->query('SELECT
			product.product_type,
			IF(product.enable_local_pickup = -1 AND 1="'.$mysqli->escape_string($config_site['enable_local_pickup']).'" OR product.enable_local_pickup = 1,1,0) AS enable_local_pickup
			FROM
			cart_item 
			INNER JOIN
			(cart_item_product CROSS JOIN product)
			ON
			(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id)
			WHERE
			cart_item.id_cart = "'.$mysqli->escape_string($cart->id).'"')) throw new Exception('An error occured while trying to get products.'."\r\n\r\n".$mysqli->error);	
			while ($row = $result->fetch_assoc()) {
				// get sub products
				/* Prepare statement */
				if (!$stmt_sub_products = $mysqli->prepare('SELECT
				IF(product.enable_local_pickup = -1 AND 1="'.$mysqli->escape_string($config_site['enable_local_pickup']).'" OR product.enable_local_pickup = 1,1,0) AS enable_local_pickup
				FROM
				cart_item_product
				INNER JOIN
				(cart_item_product AS cip_parent CROSS JOIN cart_item CROSS JOIN cart)
				ON
				(cart_item_product.id_cart_item_product = cip_parent.id AND cip_parent.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
				INNER JOIN
				product
				ON
				(cart_item_product.id_product = product.id)			
				WHERE
				cart_item_product.id_cart_item_product = ?')) throw new Exception('An error occured while trying to prepare get sub products statement.'."\r\n\r\n".$mysqli->error);							
	
				switch ($row['product_type']) {
					// single				
					case 0:
						if (!$row['enable_local_pickup']) $enable_local_pickup = 0;
						else $display_local_pickup = 1;
						break;
					// combo
					case 1:
					// bundle
					case 2:
						if (!$stmt_sub_products->bind_param("i", $row['id_cart_item_product'])) throw new Exception('An error occured while trying to bind params to get sub products statement.'."\r\n\r\n".$mysqli->error);
					
						/* Execute the statement */
						if (!$stmt_sub_products->execute()) throw new Exception('An error occured while trying to get sub products.'."\r\n\r\n".$mysqli->error);								
						/* store result */
						$stmt_sub_products->store_result();	
						
						if ($stmt_sub_products->num_rows) {
							/* bind result variables */
							$stmt_sub_products->bind_result($sub_enable_local_pickup);									
							
							while ($stmt_sub_products->fetch()) {								
								if (!$sub_enable_local_pickup) $enable_local_pickup = 0;
								else $display_local_pickup = 1;
							}
						}
						$stmt_sub_products->close();
						break;						
				}
			}
			$result->free();
			
			if ($address_type == 'shipping') {			
				if($enable_local_pickup){
					  $list .= '
					  <li class="address '.(!$config_site['enable_shipping']?'last':'pickup').'">
					  <div style="float: left;"><input name="shipping_address" type="radio" value="-1" '.((!$config_site['enable_shipping'] or $cart->local_pickup)?'checked':'').' id="shipping_address" /></div>
					  <div style="float: left; margin-left: 10px;">
					  <label for="shipping_address"><strong>'.language('global', 'TITLE_LOCAL_PICKUP').'</strong></label>
					  </div>
					  <div class="cb"></div>
					  </li>';
					  
				}else if ($display_local_pickup){
					$list .= '
					  <li class="address '.(!$config_site['enable_shipping']?'last':'pickup').'">
					  <div style="float: left;"><input type="radio" disabled /></div>
					  <div style="float: left; margin-left: 10px; width:250px;">
					  <div><strong style="color:#CCC">'.language('global', 'TITLE_LOCAL_PICKUP').'</strong></div>
					  <div>(<em>'.language('global', 'DESCRIPTION_LOCAL_PICKUP_NO').'</em>)</div>
					  </div>
					  <div class="cb"></div>
					  </li>';
				}
			}
		}
	$list .= '</ul>';
	
	
	return $list;
	
	
		
}

function convert_lb_to_kg($convert=0,$unit){
	if($convert){
		return ($unit * 0.45359237);
	}else{
		return $unit;	
	}
}
function convert_in_to_cm($convert=0,$unit){
	if($convert){
		return round($unit / 0.393700787);
	}else{
		return $unit;	
	}
}

function get_time_difference( $start, $end ) {
	/**
	 * Function to calculate date or time difference.
	 * 
	 * Function to calculate date or time difference. Returns an array or
	 * false on error.
	 *
	 * @author       J de Silva                             <giddomains@gmail.com>
	 * @copyright    Copyright &copy; 2005, J de Silva
	 * @link         http://www.gidnetwork.com/b-16.html    Get the date / time difference with PHP
	 * @param        string                                 $start
	 * @param        string                                 $end
	 * @return       array
	 */
    $uts['start']      =    strtotime( $start );
    $uts['end']        =    strtotime( $end );
    if( $uts['start']!==-1 && $uts['end']!==-1 )
    {
        if( $uts['end'] >= $uts['start'] )
        {
            $diff    =    $uts['end'] - $uts['start'];
            if( $days=intval((floor($diff/86400))) )
                $diff = $diff % 86400;
            if( $hours=intval((floor($diff/3600))) )
                $diff = $diff % 3600;
            if( $minutes=intval((floor($diff/60))) )
                $diff = $diff % 60;
            $diff    =    intval( $diff );            
            return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
        }
        else
        {
            trigger_error( language('_include/function','ERROR_ENDING_DATE_BIGGER'), E_USER_WARNING );
        }
    }
    else
    {
        trigger_error( language('_include/function','ERROR_INVALID_DATE_TIME_DETECTED'), E_USER_WARNING );
    }
    return( false );
}


function is_date($date){
	$date = str_replace(array('\'', '-', '.', ','), '/', $date);
	$date = explode('/', $date);

	if(    count($date) == 1 // No tokens
		and    is_numeric($date[0])
		and    $date[0] < 20991231 and
		(    checkdate(substr($date[0], 4, 2)
					, substr($date[0], 6, 2)
					, substr($date[0], 0, 4)))
	)
	{
		return true;
	}
   
	if(    count($date) == 3
		and    is_numeric($date[0])
		and    is_numeric($date[1])
		and is_numeric($date[2]) and
		(    checkdate($date[0], $date[1], $date[2]) //mmddyyyy
		or    checkdate($date[1], $date[0], $date[2]) //ddmmyyyy
		or    checkdate($date[1], $date[2], $date[0])) //yyyymmdd
	)
	{
		return true;
	}
   
	return false;
} 

function get_blank_image($size)
{
	return '/_images/blank_image.php?size='.$size;
}

function multidimensional_search($parents, $searched) { 
  if (empty($searched) || empty($parents)) { 
    return false; 
  } 
  
  foreach ($parents as $key => $value) { 
    $exists = true; 
    foreach ($searched as $skey => $svalue) { 
      $exists = ($exists && IsSet($parents[$key][$skey]) && $parents[$key][$skey] == $svalue);
     } 
    if($exists){ return $key; } 
  } 
  
  return false; 
} 

function get_company_signature($type=0,$language_code='')
{
	global $mysqli, $config_site;

	$language_code = $language_code ? $language_code:$_SESSION['customer']['language'];
	
	//Find the country and state of the company
	$country_name = '';
	$state_name = ''; 
	if($config_site['company_country_code']){
		$query = 'SELECT 
			country_description.name
			FROM country_description
			WHERE country_description.country_code = "'.$mysqli->escape_string($config_site['company_country_code']).'" AND country_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"';
	
			if ($result = $mysqli->query($query)) {
				$row = $result->fetch_assoc();
				$country_name = $row['name'];						
			}
			$result->free();
			
	}
	if($config_site['company_state_code']){
		$query = 'SELECT 
			state_description.name
			FROM state_description
			WHERE state_description.state_code = "'.$mysqli->escape_string($config_site['company_state_code']).'" AND state_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"';
	
			if ($result = $mysqli->query($query)) {
				$row = $result->fetch_assoc();
				$state_name = $row['name'];						
			}
			$result->free();
	}	
	
	switch ($type){
		// plain
		case 0:
			return $config_site['company_company']."\r\n".
			$config_site['company_address']."\r\n".
			(!empty($config_site['company_city'])?$config_site['company_city']. ', ':'').($state_name).' '.$config_site['company_zip'].' '.$country_name."\r\n".
			($config_site['company_telephone'] ? language('global','LABEL_CONTACT_US_T',array(),$language_code).' '.$config_site['company_telephone']."\r\n":'').
			($config_site['company_fax'] ? language('global','LABEL_CONTACT_US_F',array(),$language_code).' '.$config_site['company_fax']."\r\n":'').
			($config_site['company_email'] ? language('global','LABEL_CONTACT_US_E',array(),$language_code).' '.$config_site['company_email']."\r\n":'').
			"\r\n".language('global','LABEL_FOLLOW_US',array(),$language_code)."\r\n
			Web http://".$_SERVER['HTTP_HOST']."\r\n".
			($config_site['facebook'] ? 'Facebook '.$config_site['facebook']."\r\n":'').
			($config_site['twitter'] ? 'Twitter '.$config_site['twitter']."\r\n":'');
			break;
		// html
		case 1:
			return '<table border="0" cellpadding="0" cellspacing="0">
			
			<tr>
			<td valign="top"><strong>'.$config_site['company_company'].'</strong><br />'.
			$config_site['company_address'].'<br />'.
			(!empty($config_site['company_city'])?$config_site['company_city']. ', ':'').($state_name).' '.$config_site['company_zip'].' '.$country_name.'<br />'.
			($config_site['company_telephone'] ? '<strong>'.language('global','LABEL_CONTACT_US_T',array(),$language_code).'</strong> '.$config_site['company_telephone'].'<br />':'').
			($config_site['company_fax'] ? '<strong>'.language('global','LABEL_CONTACT_US_F',array(),$language_code).'</strong> '.$config_site['company_fax'].'<br />':'').
			($config_site['company_email'] ? '<strong>'.language('global','LABEL_CONTACT_US_E',array(),$language_code).'</strong> '.$config_site['company_email'].'<br />':'').
			'</td>
			</tr>
			<tr>
			<td valign="top"><br />'.language('global','LABEL_FOLLOW_US',array(),$language_code).' <a href="http://'.$_SERVER['HTTP_HOST'].'">Web</a> '.
			($config_site['facebook'] ? '| <a href="'.$config_site['facebook'].'">Facebook</a> ':'').
			($config_site['twitter'] ? '| <a href="'.$config_site['twitter'].'">Twitter</a>':'').
			'<br /><br /><a href="http://'.$_SERVER['HTTP_HOST'].'"><img border="0" src="http://'.$_SERVER['HTTP_HOST'].'/_images/'.$config_site['company_logo_file'].'" alt="'.$config_site['site_name'].'" /></a></td>
			</tr>
			</table>';
			break;
	}
}

function get_category_id_by_alias($alias, $id_parent=0)
{
	global $mysqli;
	
	$id_parent = (int)$id_parent;
	
	if (!$result = $mysqli->query('SELECT 
	category.id 
	FROM 
	category
	INNER JOIN
	category_description
	ON
	(category.id = category_description.id_category AND category_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
	WHERE
	category.id_parent = "'.$id_parent.'"
	AND
	category_description.alias = "'.$mysqli->escape_string($alias).'"
	LIMIT 1')) throw new Exception('An error occured while trying to get category id.'."\r\n\r\n".$mysqli->error);
	$row = $result->fetch_assoc();
	$result->free();
	
	return (int)$row['id'];
}

function get_category_path_alias($id){		
	global $mysqli,$alias_category_path;
	$query = 'SELECT 
	category.id_parent,
	category_description.alias
	FROM 
	category
	INNER JOIN 
	category_description ON (category.id_parent = category_description.id_category AND category_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
	WHERE 
	category.id = "'.$mysqli->escape_string($id).'"';

	if ($result = $mysqli->query($query)) {
		if($result->num_rows){
			$row = $result->fetch_assoc();
			$alias_category_path = '/'.$row['alias'].$alias_category_path;
			get_category_path_alias($row['id_parent']);
			$result->free();
		}
	}
}

function get_languages(){
	global $mysqli;
	
	$output = array();
	
	$query = 'SELECT * FROM language WHERE active = 1 ORDER BY default_language DESC';
	if ($result = $mysqli->query($query)) {
		while($row = $result->fetch_assoc()){
			$output[$row['code']] = $row;
		} 
		$result->free();
	}	
	
	return $output;
}

function get_accepted_cc(){
	global $mysqli;
	
	$output = array();
	
	$query = 'SELECT 
	image,
	name
	FROM 
	config_credit_card
	WHERE active = 1';
	if ($result = $mysqli->query($query)) {
		while($row = $result->fetch_assoc()){
			$output[] = array(
				'name' => $row['name'],
				'image' => '/admin/images/'.$row['image'],			
			);
		}
		$result->free();		
	}
	
	return $output;
}

function get_company_info(){
	global $config_site, $mysqli;
	
	if (!$result = $mysqli->query('SELECT 
	(SELECT name FROM country_description WHERE country_code = "'.$config_site['company_country_code'].'" AND language_code = "'.$_SESSION['customer']['language'].'") AS country,
	(SELECT name FROM state_description WHERE state_description.state_code = "'.$config_site['company_state_code'].'" AND state_description.language_code = "'.$_SESSION['customer']['language'].'") AS state')) throw new Exception('An error occured while trying to get country name and state name.');
	$row = $result->fetch_assoc();
	
	$output = array(
		'name' => $config_site['company_company'],	
		'address' => $config_site['company_address'],
		'city' => $config_site['company_city'],
		'state' => $row['state'],
		'zip' => $config_site['company_zip'],
		'country' => $row['country'],
		'logo' => '/_images/'.$config_site['company_logo_file'],
		'logo_footer' => $config_site['reseller'] ? '/includes/reseller/SimpleCommerce/logo_bottom_powered.png':'/includes/reseller/SimpleCommerce/logo_bottom.png',
		'logo_footer_link' => 'http://www.simplecommerce.com',
		'logo_footer_alt' => 'Simple Commerce',
		'logo_footer_reseller' => $config_site['reseller'] ? '/includes/reseller/'.$config_site['reseller'].'/logo_bottom.png':'',
		'logo_footer_reseller_link' => $config_site['reseller'] ? $config_site['reseller_website']:'',
		'logo_footer_reseller_alt' => $config_site['reseller'],
		'white_label' => $config_site['white_label'],
	);
	
	return $output;
}

function get_custom_fields($form){
	global $mysqli;
	
	// get custom fields
	$output = array();
	
	if ($result = $mysqli->query('SELECT 
	custom_fields.id,
	custom_fields.type,
	custom_fields.required,
	custom_fields_description.name					
	FROM 
	custom_fields 
	INNER JOIN
	custom_fields_description
	ON
	(custom_fields.id = custom_fields_description.id_custom_fields AND custom_fields_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
	WHERE
	custom_fields.form = '.$form.'
	ORDER BY 
	custom_fields.sort_order ASC')) {
		if ($result->num_rows) {
			// custom fields options
			if (!$stmt_custom_fields_option = $mysqli->prepare('SELECT 
			custom_fields_option.id,
			custom_fields_option.add_extra,
			custom_fields_option.extra_required,
			custom_fields_option.selected,
			custom_fields_option_description.name
			FROM 
			custom_fields_option
			INNER JOIN 
			custom_fields_option_description
			ON
			(custom_fields_option.id = custom_fields_option_description.id_custom_fields_option AND custom_fields_option_description.language_code = ?) 
			WHERE
			custom_fields_option.id_custom_fields = ?
			ORDER BY
			custom_fields_option.sort_order ASC')) throw new Exception('An error occured while trying to prepare list of custom fields options statement');	
			
			while ($row = $result->fetch_assoc()) {
				$output[$row['id']] = $row;
					
				if (!$stmt_custom_fields_option->bind_param("si", $_SESSION['customer']['language'], $row['id'])) throw new Exception('An error occured while trying to bind params to list of custom fields options statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_custom_fields_option->execute()) throw new Exception('An error occured while trying to list custom fields options.'."\r\n\r\n".$mysqli->error);	
				
				/* store result */
				$stmt_custom_fields_option->store_result();																														
				
				// if we have other variants
				if ($stmt_custom_fields_option->num_rows) {			
					/* bind result variables */
					$stmt_custom_fields_option->bind_result($id_custom_fields_option,$add_extra,$extra_required,$selected,$option_name);
		
					while ($stmt_custom_fields_option->fetch()) {		
						$output[$row['id']]['options'][$id_custom_fields_option] = array(
							'id' => $id_custom_fields_option,
							'add_extra' => $add_extra,
							'extra_required' => $extra_required,
							'selected' => $selected,
							'name' => $option_name,
						);
					}			
				}
			}
			
			$stmt_custom_fields_option->close();
		}	
	}		

	return $output;
}

// New functions
function dsp_product($row) {
  global $config_site;
  if (!empty($row['image'])){
    $image_src = $row['image'];
  }else{ 
    $image_src = get_blank_image('listing');
  }
  $product_name = $row['name'].' '.($row['variant_name'] ? '<br /><small>'.$row['variant_name'].'</small>':'');
  $product_name_title = htmlspecialchars($row['name'].' '.($row['variant_name'] ? $row['variant_name']:''));
  
  $output  = '<div class="item-inner">';
  $output .= '<div class="product-image"><div style="position:relative;">';
  $output .= '<a href="'.$row["url"].'" title="'.$product_name_title.'">';
  $output .= '<img class="primary-image" src="'.$image_src.'" alt="'.$product_name_title.'">';
  $output .= '<img class="hover-image" src="'.$image_src.'" alt="'.$product_name_title.'">';                       
  //<!--<div class="labels top-left"><div class="new rect">New</div><div class="sales rect">-5%</div></div>    -->                 
  $output .= '</a>';
  
  if($config_site['display_price'] && !$row['display_price_exception']) {
	  $output .= '<div class="price-box">';
	  if ($row['price'] != $row['sell_price']) {
		  $output .= '<p class="old-price">';
		  $output .= '<span class="price-label">'.language('index', 'LABEL_PRICE_REGULAR').':</span>';
		  $output .= '<span class="price" id="old-price-'.$row['id'].'-featured-slider">';
		  $output .= nf_currency($row["price"]);
		  $output .= '</span></p>';
		  $output .= '<p class="special-price">';
		  $output .= '<span class="price-label">'.language('index', 'LABEL_SPECIAL_PRICE').'</span>';
		  $output .= '<span class="price" id="product-price-'.$row['id'].'-featured-slider">';
		  $output .= nf_currency($row["sell_price"]);
		  $output .= '</span></p>';
	  } else {
		  $output .= '<span class="regular-price" id="product-price-'.$row['id'].'"><span class="price">';
		  $output .= nf_currency($row['sell_price']);
		  $output .= '</span></span>';
	  }
	  $output .= '</div>';
  }
  $output .= '</div>';
  
  $output .= '</div><div class="product-shop">';  
   	  
  $output .= '<div class="reviews-wrap"><div class="ratings">';
  if($config_site['display_menu_rate_product'] && $row['avg_rating']>0) { 
      $output .= '<div class="rating-box"><div class="rating" style="width:'.($row['avg_rating']*100/5).'%"></div></div>';
	  $output .= '<span class="amount" style="width: 0px; display: none; overflow: hidden; padding-left: 0px; margin-left: -3px;">';
	  $output .= $row['avg_rating'] .' '.language('product', 'LABEL_NB_REVIEWS').'</span>';
  }   
  $output .= '</div></div>';
  
  $output .= '<h3 class="product-name"><a href="'.$row["url"].'" title="'.$product_name_title.'">'.$product_name.'</a></h3>';
  $output .= '<div class="actions clearfix">';
  
  if($config_site['allow_add_to_cart'] && !$row['allow_add_to_cart_exceptions']) {
	  $output .= '<button type="button" title="'.language('index', 'LABEL_ADD_TO_CART').'" class="button btn-cart" onclick="setLocation(\''.$row["url"].'\')" style="overflow: hidden;">';
	  $output .= '<span><span>'.language('index', 'LABEL_ADD_TO_CART').'</span></span>';
	  $output .= '</button>';
  } else {
	  $output .= '<button type="button" title="'.language('index', 'LABEL_ADD_TO_CART').'" class="button btn-cart" onclick="setLocation(\''.$row["url"].'\')" style="overflow: hidden;">';
	  $output .= '<span><span>'.language('global', 'LABEL_MORE_DETAILS').'</span></span>';
	  $output .= '</button>';
  }
  
  $output .= '<form action="'.$row["url"].'" method="post" id="w-'.$row['id'].'" style="display:none;"><input type="submit" name="_add_to_wishlist" style="display:none"></form>';
  if($config_site['display_menu_add_wishlist']){
	  $output .= '<span class="add-to-links" style="width: 0px; display: none; overflow: hidden; padding-left: 0px; margin-left: -3px;">';
	  $output .= '<a href="javascript:void(0)" onclick="jQuery(\'#w-'.$row['id'].' input\').click();" class="button link-wishlist" title="'.language('index', 'LABEL_ADD_TO_WISHLIST').'" onclick="">';
	  $output .= language('index', 'LABEL_ADD_TO_WISHLIST');
	  $output .= '</a>';
	  $output .= '</span>';
  }
  $output .= '</div></div></div>';
  return $output;
}

?>