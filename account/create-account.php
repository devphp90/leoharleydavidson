<?php
require('../_includes/config.php');
include_mailer();

$return = trim($_GET['return']);

if ($_SESSION['customer']['id']) {
	if ($return) { 
		header('Location: '.urldecode($return)); 
		exit; 
	} else { 
		header('Location: '.$url_prefix); 
		exit;
	}
}

// get custom fields
$custom_fields = array();
if ($result = $mysqli->query('SELECT 
custom_fields.id,
custom_fields.type,
custom_fields.required,
custom_fields_description.name,
custom_fields_description.description						
FROM 
custom_fields 
INNER JOIN
custom_fields_description
ON
(custom_fields.id = custom_fields_description.id_custom_fields AND custom_fields_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
WHERE
custom_fields.form = 0
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
			$custom_fields[$row['id']] = $row;
				
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
					$custom_fields[$row['id']]['options'][$id_custom_fields_option] = array(
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

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		$task = trim($_GET['task']);
		
		switch ($task) {
			case 'get_state_list':
				$country_code = trim($_GET['country_code']);
			
				echo json_encode(get_state_list($country_code));
				exit;
				break;
		}		
		break;
	case 'POST':
		if (isset($_POST['create_account'])) {				
			// validation rules
			$validation = array(
				'email' => array(
					'required' => 1,
					'email' => 1,
					'callback' => function($field_value)
					{
						global $mysqli;
						
						// check if email already is in database
						if ($result = $mysqli->query('SELECT 
						id
						FROM 
						customer
						WHERE
						email = "'.$mysqli->escape_string($field_value).'" 
						LIMIT 1')) {
							if ($result->num_rows) return true;
							return false;	
						} else {
							throw new Exception('An error occured while trying to check if email already is in use.'."\r\n\r\n".$mysqli->error);	
						}
					}
				),
				'cemail' => array(
					'required' => 1,
					'equal' => 'email',
				),
				'password' => array(
					'required' => 1,
					'minlen' => 6,
					'maxlen' => 12,
					'alpha' => 1,					
				),
				'cpassword' => array(
					'required' => 1,
					'equal' => 'password',
				),
				
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
				),
				//'dob' => array(
				//	'required' => 1,
					//'date' => 1,
				//),
			);		
			
			// validate custom fields
			if (isset($custom_fields) && is_array($custom_fields) && sizeof($custom_fields)) {
				//echo '<pre>'.print_r($custom_fields,1).'</pre>';
				//echo '<pre>'.print_r($_POST['form_values']['custom_fields'],1).'</pre>';
				foreach ($custom_fields as $row) {
					// required
					if ($row['required']) {
						$validation['custom_fields_'.$row['id']] = array( 'required' => 1 );
						
						switch ($row['type']) {
							// multiple checkboxes
							case 1:
								if (isset($row['options']) && is_array($row['options'])) {
									$value='';
									foreach ($row['options'] as $row_option) {
										if (!empty($_POST['form_values']['custom_fields'][$row['id']]['options'][$row_option['id']]['value'])) $value = $_POST['form_values']['custom_fields'][$row['id']]['options'][$row_option['id']]['value'];
									}
									$_POST['form_values']['custom_fields_'.$row['id']] = $value;
								}
								break;	
							default:
								$_POST['form_values']['custom_fields_'.$row['id']] = $_POST['form_values']['custom_fields'][$row['id']]['value'];								
								break;
						}												
					}
					
					
					
					if (isset($row['options']) && is_array($row['options'])) {
						
						foreach ($row['options'] as $row_option) {
							// if add extra and extra is required
							if (($_POST['form_values']['custom_fields'][$row['id']]['value'] == $row_option['id'] || $_POST['form_values']['custom_fields'][$row['id']]['options'][$row_option['id']]['value']) && $row_option['add_extra'] && $row_option['extra_required']) {								
								
								// multiple checkboxes
								if ($row['type'] == 1 || $row['type'] == 5) {	
									$validation['custom_fields_'.$row['id'].'_'.$row_option['id'].'_extra'] = array( 'required' => 1 );
								
									$_POST['form_values']['custom_fields_'.$row['id'].'_'.$row_option['id'].'_extra'] = $_POST['form_values']['custom_fields'][$row['id']]['options'][$row_option['id']]['extra'];	
								// radio button									
								} else if ($row['type'] == 5) { 
									if ($_POST['form_values']['custom_fields'][$row['id']]['value'] == $row_option['id']) {
										$validation['custom_fields_'.$row['id'].'_'.$row_option['id'].'_extra'] = array( 'required' => 1 );
								
										$_POST['form_values']['custom_fields_'.$row['id'].'_'.$row_option['id'].'_extra'] = $_POST['form_values']['custom_fields'][$row['id']]['options'][$row_option['id']]['extra'];								
									}																		
								} else {
									$validation['custom_fields_'.$row['id'].'_extra'] = array( 'required' => 1 );
									
									$_POST['form_values']['custom_fields_'.$row['id'].'_extra'] = $_POST['form_values']['custom_fields'][$row['id']]['extra'];	
								}									
							}
						}
					}
				}
			}	
					
			
			if (!sizeof($errors = validate_fields($_POST['form_values'], $validation, array(
			'email' => array(
				'callback' => language('account/create-account', 'ERROR_EMAIL_IN_USE'),
			),
			)))) {				
				// current time
				$current_datetime = date('Y-m-d H:i:s');
				
				$firstname = trim($_POST['form_values']['firstname']);
				$lastname = trim($_POST['form_values']['lastname']);
				$language_code = trim($_POST['form_values']['language_code']);
				$email = trim($_POST['form_values']['email']);
				$dob = trim($_POST['form_values']['dob']);
				$gender = trim($_POST['form_values']['gender']);
				$password = trim($_POST['form_values']['password']);
				$company = trim($_POST['form_values']['company']);
				$address = trim($_POST['form_values']['address']);
				$city = trim($_POST['form_values']['city']);
				$country_code = trim($_POST['form_values']['country_code']);
				$state_code = trim($_POST['form_values']['state_code']);				
				$zip = trim($_POST['form_values']['zip']);
				$telephone = trim($_POST['form_values']['telephone']);				
				
				if ($mysqli->query('INSERT INTO 
				customer
				SET 
				firstname = "'.$mysqli->escape_string(ucfirst(strtolower($firstname))).'",
				lastname = "'.$mysqli->escape_string(ucfirst(strtolower($lastname))).'",
				language_code = "'.$mysqli->escape_string(strtolower($language_code)).'",
				email = "'.$mysqli->escape_string($email).'",
				dob = "'.$mysqli->escape_string($dob).'",
				gender = "'.$mysqli->escape_string($gender).'",
				date_created = "'.$mysqli->escape_string($current_datetime).'"')) {
					$id_customer = $mysqli->insert_id;
				
					// generate activation key
					$activation_key = md5($id_customer.$current_datetime);
					
					// encrypt password
					$password = generate_password($id_customer, $password);
					
					// update
					if (!$mysqli->query('UPDATE 
					customer 
					SET
					password = "'.$mysqli->escape_string($password).'",
					activation_key = "'.$mysqli->escape_string($activation_key).'"
					WHERE
					id = "'.$mysqli->escape_string($id_customer).'"
					LIMIT 1')) {
						throw new Exception('An error occured while trying to update account.'."\r\n\r\n".$mysqli->error);		
					}	
					
					$zip = preg_replace("/[^ \w]+/", "", $zip);
					$zip = strtoupper(str_replace(' ','',$zip));				
					
					// add address as default billing
					if (!$mysqli->query('INSERT INTO
					customer_address 
					SET 
					id_customer = "'.$mysqli->escape_string($id_customer).'",
					firstname = "'.$mysqli->escape_string(ucfirst(strtolower($firstname))).'",
					lastname = "'.$mysqli->escape_string(ucfirst(strtolower($lastname))).'",
					company = "'.$mysqli->escape_string($company).'",
					address = "'.$mysqli->escape_string($address).'",
					city = "'.$mysqli->escape_string($city).'",
					country_code = "'.$mysqli->escape_string($country_code).'",
					state_code = "'.$mysqli->escape_string($state_code).'",
					zip = "'.$mysqli->escape_string($zip).'",
					telephone = "'.$mysqli->escape_string($telephone).'",
					default_billing = 1,
					default_shipping = 1,
					date_created = "'.$mysqli->escape_string($current_datetime).'"')) {
						throw new Exception('An error occured while trying to add address information.'."\r\n\r\n".$mysqli->error);		
					}			
					
					// add custom fields
					if (isset($_POST['form_values']['custom_fields']) && sizeof($_POST['form_values']['custom_fields'])) {
						// prepare insert 
						if (!$stmt_add_custom_fields_value = $mysqli->prepare('INSERT INTO
						customer_custom_fields_value
						SET 
						id_customer = ?,
						id_custom_fields = ?,
						id_custom_fields_option = ?,
						value = ?')) throw new Exception('An error occured while trying to prepare add custom fields value statement.'."\r\n\r\n".$mysqli->error);		
						
						foreach ($_POST['form_values']['custom_fields'] as $id_custom_fields => $row_custom_field) {
							if (isset($row_custom_field['options']) && sizeof($row_custom_field['options'])) {
								foreach ($row_custom_field['options'] as $id_custom_fields_option => $row_custom_fields_option) {
									$value = $row_custom_fields_option['extra'] ? $row_custom_fields_option['extra']:'';
									
									if (!$stmt_add_custom_fields_value->bind_param("iiis", $id_customer, $id_custom_fields, $id_custom_fields_option, $value)) throw new Exception('An error occured while trying to bind params to add custom fields value statement.'."\r\n\r\n".$mysqli->error);			
									
									/* Execute the statement */
									if (!$stmt_add_custom_fields_value->execute()) throw new Exception('An error occured while trying to add custom fields value statement.'."\r\n\r\n".$mysqli->error);																					
								}								
							} else {
								switch ($custom_fields[$id_custom_fields]['type']) {
									// single checkbox
									case 0:	
										$id_custom_fields_option = 0;
										$value = '';
										break;
									// dropdown
									case 2:
										$id_custom_fields_option = $row_custom_field['value'];
										$value = $row_custom_field['extra'] ? $row_custom_field['extra']:'';
										break;
									// textfield
									case 3:
										$id_custom_fields_option = 0;
										$value = $row_custom_field['value'] ? $row_custom_field['value']:'';
										break;
									// textarea
									case 4:
										$id_custom_fields_option = 0;
										$value = $row_custom_field['value'] ? $row_custom_field['value']:'';
										break;
									// radio
									case 5:
										$id_custom_fields_option = $row_custom_field['value'];
										$value = '';
										break;
								}					
													
								if (!$stmt_add_custom_fields_value->bind_param("iiis", $id_customer, $id_custom_fields, $id_custom_fields_option, $value)) throw new Exception('An error occured while trying to bind params to add custom fields value statement.'."\r\n\r\n".$mysqli->error);			
								
								/* Execute the statement */
								if (!$stmt_add_custom_fields_value->execute()) throw new Exception('An error occured while trying to add custom fields value statement.'."\r\n\r\n".$mysqli->error);										
							}
						}
						
						$stmt_add_custom_fields_value->close();
					}
					
					// send email to customer with activation link
					$mail = new PHPMailer(); // defaults to using php "mail()"
					$mail->CharSet = 'UTF-8';
					
					// text only
					//$mail->IsHTML(false);
				
					$mail->SetFrom($config_site['no_reply_email'], $config_site['site_name']);

					$customer_name = ucfirst(strtolower($firstname)).' '.ucfirst(strtolower($lastname));
			
					$mail->AddAddress($email, $customer_name);
					
					$mail->Subject = language('emails', 'CREATE_ACCOUNT_SUBJECT');
					
					$mail->AltBody = language('emails', 'CREATE_ACCOUNT_PLAIN',array(0=>$customer_name,1=>$config_site['site_name'],2=>"http://".$_SERVER['HTTP_HOST']."/account/activation?activation_key=".$activation_key.($return?"&return=".urlencode($return):''),
					'signature'=>get_company_signature(1)));

					
					$mail->MsgHTML(language('emails', 'CREATE_ACCOUNT_HTML',array(0=>$customer_name,1=>$config_site['site_name'],2=>"http://".$_SERVER['HTTP_HOST']."/account/activation?activation_key=".$activation_key.($return?"&return=".urlencode($return):""),
					'signature'=>get_company_signature(1))));

					$sendmail_failed = $mail->Send() ? 0:1;
								
					if ($sendmail_failed) {
						// update
						if (!$mysqli->query('UPDATE 
						customer 
						SET
						sendmail_failed = 1
						WHERE
						id = "'.$mysqli->escape_string($id_customer).'"
						LIMIT 1')) {
							throw new Exception('An error occured while trying to update account.'."\r\n\r\n".$mysqli->error);		
						}	
					}
					
					$_POST['form_values'] = array();		
					$success = 1;
				} else {
					throw new Exception('An error occured while trying to create account.'."\r\n\r\n".$mysqli->error);	
				}
			}
		}
		break;	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow" />
<title><?php echo $config_site['site_name'];?></title>
<?php include("../_includes/template/header.php");?>

<script type="text/javascript" language="javascript">
<!--
jQuery(function(){
	jQuery("#country_code").bind({
		change: get_state_list
	});
	
	// load calendars if we have any
	jQuery("#dob").datepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: "/_images/icons/date-calendar.png",
			buttonImageOnly: true,
			buttonText: "Calendar",
			changeMonth: true,
      		changeYear: true,
			yearRange: "<?php echo (date('Y')-100)?>:<?php echo (date('Y'))?>"
	});		
	
	jQuery("body").on("change",".custom_fields_dropdown",function(){
		var id = jQuery(this).prop("id");
		var i = id.replace("custom_fields_dropdown_","");
		var selected = jQuery(":selected",this);
		var extra = jQuery("#custom_fields_dropdown_extra_"+i);
		
		if (selected.length && selected.prop("class") == "add_extra" && extra.length) extra.prop("disabled",false).show();			
		else extra.val("").hide().prop("disabled",true);
	});
	
	jQuery("body").on("click",".custom_fields_checkbox",function(){
		var id = jQuery(this).prop("id");
		var i = id.replace("custom_fields_checkbox_","");
		var extra = jQuery("#custom_fields_checkbox_extra_"+i);
		
		if (jQuery(this).prop("checked") && extra.length) extra.prop("disabled",false);
		else extra.val("").prop("disabled",true);
	});
	
	jQuery("body").on("click",".custom_fields_radio",function(){
		var id = jQuery(this).prop("id");
		var i = id.replace("custom_fields_radio_","");
		var extra = jQuery("#custom_fields_radio_extra_"+i);
		var ids = i.split("_");
		
		// disable extras
		jQuery("input[name^='form_values[custom_fields]["+ids[0]+"][options]']:input").prop("disabled",true);		
		jQuery("input[name^='form_values[custom_fields]["+ids[0]+"][options]']:input").val("");

		if (extra.length) extra.prop("disabled",false);
	});	
});

function get_state_list() {
	var country_code = jQuery("#country_code").val();	
	
	if (!country_code) { 
		alert("Choisissez pays"); 
		return false;
	} else {
		jQuery.ajax({
			url: "/account/create-account.php",
			data: { "task":"get_state_list","country_code":country_code },
			dataType: "json",
			error: function(jqXHR, textStatus, errorThrown) { 
				alert(jqXHR.responseText);
			},
			success: function( data ) {
				jQuery("#state_code").html("").append('<option value="">-- Choisissez province/état --</option>');
				
				if (data) {
					jQuery.each(data,function(key, value){
						jQuery("#state_code").append('<option value="'+key+'">'+value+'</option>');
					});
				}
			}
		});
	}
}
-->
</script>

</head>
<body class="customer-account-create 1column green bv3">
<?php include("../_includes/template/top.php");?>
<div class="main-container">
 <div class="main nobc">
  <div class="container">
   <div class="main-content" style="overflow:hidden;">
    <div class="account-create">
	 <div class="page-title">
      <h1><?php echo language('account/create-account', 'LABEL_CREATE_ACCOUNT');?></h1>
	 </div>
	    <?php
		if ($success) {
			echo '<div class="success">'.language('account/create-account', 'SUCCESS_ACCOUNT_CREATED').'</div>';
			
			if($return && strstr($return,'cart')){ 
				echo '<p><a href="login?return='.$return.'">'.language('account/create-account', 'LINK_LOGIN_AFTER_ACTIVATION').'</a></p>';
			}
		} else {
		
		if ($sendmail_failed) {
			echo '<div class="error">'.language('account/create-account', 'ERROR_SEND_EMAIL').'</div>';
		}?>
        <form method="post" enctype="multipart/form-data" action="<?php echo $return?'?return='.urlencode($return):''?>">
          <div style="float:left; width:58%;">                    
            <h2><?php echo language('account/create-account', 'TITLE_PERSONAL_INFORMATION');?></h2>
            <div class="title_bg_text_box">                           
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_FIRST_NAME');?> *</strong><br />
                <input type="text" name="form_values[firstname]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['firstname']); ?>" <?php echo $errors['firstname'] ? 'class="error"':''; ?> />  
                <br /><span class="error"><?php echo $errors['firstname']; ?></span>                                                            
                </div>                                     
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_LAST_NAME');?> *</strong><br />
                <input type="text" name="form_values[lastname]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['lastname']); ?>" <?php echo $errors['lastname'] ? 'class="error"':''; ?> />     
                <br /><span class="error"><?php echo $errors['lastname']; ?></span>                                                         
                </div>                                         
                <div style="margin-bottom:5px;" class="hide_field_form_company">
                <strong><?php echo language('global', 'ADDRESS_COMPANY');?></strong><br />
                <input type="text" name="form_values[company]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['company']); ?>" <?php echo $errors['company'] ? 'class="error"':''; ?> />   
                <br /><span class="error"><?php echo $errors['company']; ?></span>                                                           
                </div>                                                 
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_ADDRESS');?> *</strong><br />
                <input type="text" name="form_values[address]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['address']); ?>" <?php echo $errors['address'] ? 'class="error"':''; ?> />  
                <br /><span class="error"><?php echo $errors['address']; ?></span>                                                            
                </div>     
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_CITY');?> *</strong><br />
                <input type="text" name="form_values[city]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['city']); ?>" <?php echo $errors['city'] ? 'class="error"':''; ?> />        
                <br /><span class="error"><?php echo $errors['city']; ?></span>                                                      
                </div>   
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_COUNTRY');?> *</strong><br />
                <select name="form_values[country_code]" id="country_code" style="width:100%;" <?php echo $errors['country_code'] ? 'class="error"':''; ?>>
                    <option value="">-- <?php echo language('global', 'SELECT_COUNTRY');?> --</option>
                    <?php
                    if (sizeof($countries = get_country_list())) {
                        foreach ($countries as $country_code => $name) {
                            echo '<option value="'.$country_code.'" '.((($_POST['form_values']['country_code'] == $country_code) || (!isset($_POST['form_values']['country_code']) && $config_site['company_country_code']==$country_code)) ? 'selected="selected"':'').'>'.$name.'</option>';
                        }
                    }
                    ?>							
                </select>             
                <br /><span class="error"><?php echo $errors['country_code']; ?></span>                                            
                </div>        
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_STATE');?> *</strong><br />
                <select name="form_values[state_code]" id="state_code" style="width:100%;" <?php echo $errors['state_code'] ? 'class="error"':''; ?>>
                <option value="">-- <?php echo language('global', 'SELECT_STATE_PROVINCE');?> --</option>
                <?php
                    if (($_POST['form_values']['country_code'] && sizeof($states = get_state_list($_POST['form_values']['country_code']))) || (!isset($_POST['form_values']['country_code']) && sizeof($states = get_state_list($config_site['company_country_code'])))) {
                        foreach ($states as $state_code => $name) {
                            echo '<option value="'.$state_code.'" '.((($_POST['form_values']['state_code'] == $state_code)|| (!isset($_POST['form_values']['state_code']) && $config_site['company_state_code']==$state_code)) ? 'selected="selected"':'').'>'.$name.'</option>';
                        }
                    }
                ?>
                </select>
                <br /><span class="error"><?php echo $errors['state_code']; ?></span>                    
                </div>   
                                     
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_ZIP');?> *</strong><br />
                <input type="text" name="form_values[zip]" style="width:30%;" value="<?php echo htmlspecialchars($_POST['form_values']['zip']); ?>" <?php echo $errors['zip'] ? 'class="error"':''; ?> /> 
                <br /><span class="error"><?php echo $errors['zip']; ?></span>                                                             
                </div> 
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_TELEPHONE');?> *</strong><br />
                <input type="text" name="form_values[telephone]" style="width:30%;" value="<?php echo htmlspecialchars($_POST['form_values']['telephone']); ?>" <?php echo $errors['telephone'] ? 'class="error"':''; ?> />    
                <br /><span class="error"><?php echo $errors['telephone']; ?></span>                                                          
                </div>      
                
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_DATE_OF_BIRTH');?></strong><br />
                <input type="text" name="form_values[dob]" id="dob" style="width:30%;" maxlength="10" value="<?php echo htmlspecialchars($_POST['form_values']['dob']); ?>" <?php echo $errors['dob'] ? 'class="error"':''; ?> />        
                <br /><span class="error"><?php echo $errors['dob']; ?></span>                                                      
                </div>
                
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_GENDER');?> </strong><br />
                <input type="radio" name="form_values[gender]" value="0" <?php echo(!$_POST['form_values']['gender']?'checked="checked"':'');?> />&nbsp;<?php echo language('global', 'ADDRESS_MALE');?>&nbsp;&nbsp;<input type="radio" name="form_values[gender]" value="1" <?php echo($_POST['form_values']['gender']?'checked="checked"':'');?> />&nbsp;<?php echo language('global', 'ADDRESS_FEMALE');?><br /><span class="error"><?php echo $errors['gender']; ?></span>                                  
                </div>
                
                
                 <div style="margin-bottom:5px;">
                  <strong><?php echo language('global', 'ADDRESS_CORRESPONDENCE_LANGUAGE');?> </strong><br />
                  <select name="form_values[language_code]">
                  <?php
                  $query = 'SELECT * FROM language WHERE active = 1 ORDER BY default_language DESC';
                    if ($result = $mysqli->query($query)) {
                        while($row = $result->fetch_assoc()){
                            echo '<option value="'.$row['code'].'" '. (($row['code'] == $_POST['form_values']['language_code'] or (!isset($_POST['form_values']['language_code']) and $row['code'] == $_SESSION['customer']['language'])) ? 'selected="selected"':'').'>'.$row['name'].'</option>';
                        } 
                        $result->close();
                    }
                  ?>
                  </select>
                </div>                                                
            </div>    
            </div>                
                
             <div style="float:right; width:40%">
                 <h2><?php echo language('account/create-account', 'TITLE_LOGIN_INFORMATION');?></h2>
                  <div class="title_bg_text_box">
                      <div style="margin-bottom:5px;">
                      <strong><?php echo language('account/create-account', 'LABEL_EMAIL_ADDRESS');?> *</strong><br />
                      <input type="text" name="form_values[email]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['email']); ?>" <?php echo $errors['email'] ? 'class="error"':''; ?> />      
                      <br /><span class="error"><?php echo $errors['email']; ?></span>                                                        
                      </div>
                      <div style="margin-bottom:5px;">
                      <strong><?php echo language('account/create-account', 'LABEL_CONFIRM_EMAIL_ADDRESS');?> *</strong><br />
                      <input type="text" name="form_values[cemail]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['cemail']); ?>" <?php echo $errors['cemail'] ? 'class="error"':''; ?> />        
                      <br /><span class="error"><?php echo $errors['cemail']; ?></span>                                                
                      </div>                            
                      <div style="margin-bottom:5px;">
                      <strong><?php echo language('account/create-account', 'LABEL_PASSWORD');?> *</strong><br />
                      <input type="password" name="form_values[password]" value="" style="width:100%;" <?php echo $errors['password'] ? 'class="error"':''; ?> />  
                      <br /><span class="error"><?php echo $errors['password']; ?></span>                 
                      </div>
                      <div style="margin-bottom:5px;">
                      <strong><?php echo language('account/create-account', 'LABEL_CONFIRM_PASSWORD');?> *</strong><br />
                      <input type="password" name="form_values[cpassword]" style="width:100%;" <?php echo $errors['cpassword'] ? 'class="error"':''; ?> />     
                      <br /><span class="error"><?php echo $errors['cpassword']; ?></span>              
                      </div>                            
                  </div> 
                </div>  

                    <?php					
					// get list of custom fields					
					if (isset($custom_fields) && is_array($custom_fields) && sizeof($custom_fields)) {
						?>
                        <div style="float:right; width:40%;">                    
                            <h2><?php echo language('account/create-account', 'TITLE_ADDITIONAL_INFORMATION');?></h2>
                            <div class="title_bg_text_box">                           
							<?php                            
							foreach ($custom_fields as $row) {	
							?>
                                <div style="margin-bottom:5px;">
                                    <strong><?php echo $row['name'].($row['required'] ? ' *':''); ?> </strong>
                                    <?php echo ($row['description'] ? '<div style="font-style:italic">'.$row['description'].'</div>':''); ?>
                                    <?php
									switch ($row['type']) {
										// single textbox
										case 0:	
											echo '&nbsp;&nbsp;<input type="checkbox" name="form_values[custom_fields]['.$row['id'].'][value]"  value="'.$row['id'].'" '.(isset($_POST['form_values']['custom_fields'][$row['id']]) ? 'checked="checked"':'').' '.($errors['custom_fields_'.$row['id']] ? 'class="error"':'').' /><div style="margin-bottom:10px;"></div>';
											break;
										// multiple checkbox
										case 1:	
											if (isset($row['options']) && sizeof($row['options'])) {																								
												echo '<div style="padding:10px; margin-bottom:10px;">';													
						
												// loop through		
												foreach ($row['options'] as $row_option) {
													echo '<input type="checkbox" name="form_values[custom_fields]['.$row['id'].'][options]['.$row_option['id'].'][value]" value="'.$row_option['id'].'" id="custom_fields_checkbox_'.$row['id'].'_'.$row_option['id'].'" '.(isset($_POST['form_values']['custom_fields'][$row['id']]['options'][$row_option['id']]) ? 'checked="checked"':'').' class="custom_fields_checkbox" />&nbsp;<label for="custom_fields_checkbox_'.$row['id'].'_'.$row_option['id'].'">'.$row_option['name'].'</label>';
													
													if ($row_option['add_extra']) {
														echo '&nbsp;&nbsp;<input type="text" name="form_values[custom_fields]['.$row['id'].'][options]['.$row_option['id'].'][extra]" size="25" value="'.htmlspecialchars($_POST['form_values']['custom_fields'][$row['id']]['options'][$row_option['id']]['extra']).'" '.(!isset($_POST['form_values']['custom_fields'][$row['id']]['options'][$row_option['id']]) ? 'disabled="disabled" ':'').($errors['custom_fields_'.$row['id'].'_'.$row_option['id'].'_extra'] ? 'class="error"':'').' id="custom_fields_checkbox_extra_'.$row['id'].'_'.$row_option['id'].'" />';
													}
													
													echo '<br />';
													
													++$x;
												}
												
												echo '</div>';
											}												
											break;
										// dropdown
										case 2:
											if (isset($row['options']) && sizeof($row['options'])) {		
												echo '<div style="margin-bottom:10px;"><select name="form_values[custom_fields]['.$row['id'].'][value]" id="custom_fields_dropdown_'.$row['id'].'" class="custom_fields_dropdown '.($errors['custom_fields_'.$row['id']] ? 'error':'').'">
												<option value="">--</option>';													
						
												// loop through variants	
												$extra = '';	
												foreach ($row['options'] as $row_option) {
													echo '<option value="'.$row_option['id'].'" '.($_POST['form_values']['custom_fields'][$row['id']]['value'] == $row_option['id'] ? 'selected="selected"':'').($row_option['add_extra'] ? ' class="add_extra"':'').'>'.$row_option['name'].'</option>';
													++$x;
													
													// add extra
													if ($row_option['add_extra'] && empty($extra)) {
														$extra = '<input type="text" name="form_values[custom_fields]['.$row['id'].'][extra]" id="custom_fields_dropdown_extra_'.$row['id'].'" value="'.htmlspecialchars($_POST['form_values']['custom_fields'][$row['id']]['extra']).'" size="25" style="display:none;" disabled="disabled" />';
													}
												}
												
												if ($row['options'][$_POST['form_values']['custom_fields'][$row['id']]['value']]['add_extra']) $extra = str_replace('style="display:none;" disabled="disabled"',($row['options'][$_POST['form_values']['custom_fields'][$row['id']]['value']]['extra_required'] && empty($_POST['form_values']['custom_fields'][$row['id']]['extra']) ? 'class="error"':''),$extra);
												
												echo '</select>'.($extra ? '&nbsp;&nbsp;'.$extra:'').'</div>';
											}													
											break;
										// textfield
										case 3:
											echo '<div style="margin-bottom:10px;"><input type="text" name="form_values[custom_fields]['.$row['id'].'][value]" style="width:100%;" value="'.htmlspecialchars($_POST['form_values']['custom_fields'][$row['id']]['value']).'" '.($errors['custom_fields_'.$row['id']] ? 'class="error"':'').' /></div>';
											break;
										// textarea
										case 4:
											echo '<div style="margin-bottom:10px;"><textarea name="form_values[custom_fields]['.$row['id'].'][value]" style="width:100%;" rows="4" '.($errors['custom_fields_'.$row['id']] ? 'class="error"':'').'>'.$_POST['form_values']['custom_fields'][$row['id']]['value'].'</textarea></div>';
											break;
										// radio
										case 5:
											if (isset($row['options']) && sizeof($row['options'])) {		
												echo '<div style="padding:10px; margin-bottom:10px;">';													
						
												// loop through		
												foreach ($row['options'] as $row_option) {
													$selected = $_POST['form_values']['custom_fields'][$row['id']]['value'] ? $_POST['form_values']['custom_fields'][$row['id']]['value']:($row_option['selected'] ? $row_option['id']:0);
													
													echo '<input type="radio" name="form_values[custom_fields]['.$row['id'].'][value]" value="'.$row_option['id'].'" id="custom_fields_radio_'.$row['id'].'_'.$row_option['id'].'" '.($selected == $row_option['id'] ? 'checked="checked"':'').' class="custom_fields_radio" />&nbsp;<label for="custom_fields_radio_'.$row['id'].'_'.$row_option['id'].'">'.$row_option['name'].'</label>';
													
													if ($row_option['add_extra']) {
														echo '&nbsp;&nbsp;<input type="text" name="form_values[custom_fields]['.$row['id'].'][options]['.$row_option['id'].'][extra]" size="25" value="'.htmlspecialchars($_POST['form_values']['custom_fields'][$row['id']]['options'][$row_option['id']]['extra']).'" '.(!isset($_POST['form_values']['custom_fields'][$row['id']]['options'][$row_option['id']]) ? 'disabled="disabled" ':'').($errors['custom_fields_'.$row['id'].'_'.$row_option['id'].'_extra'] ? 'class="error"':'').' id="custom_fields_radio_extra_'.$row['id'].'_'.$row_option['id'].'" />';
													}
													
													echo '<br />';
													
													++$x;
												}
												
												echo '</div>';
											}	
											break;											
									}
									?>                                      
                                    <span class="error"><?php echo $errors['custom_fields_'.$row['id']]; ?></span>                                                            
                                </div>                                              
                            <?php
								++$i;								
							}
						?>
                            </div>
                        </div>                      
                        <?php
					}
					?>
                    
                    <div style="clear:right;"></div>
                    <div class="buttons-set" style="clear:none; margin-top:20px; float:right;">
    					<button type="submit" name="create_account" value="<?php echo language('account/create-account', 'BTN_CREATE_ACCOUNT');?>" title="<?php echo language('account/create-account', 'BTN_CREATE_ACCOUNT');?>" class="button">
    						<span><span><?php echo language('account/create-account', 'BTN_CREATE_ACCOUNT');?></span></span>
    					</button>
					</div>                      
                    <div class="cb"></div>                                        
                </form>
                <?php } ?> 
            </div>
        </div>
        </div>           
	</div>        
</div>

<?php include("../_includes/template/bottom.php");?>
</body>
</html>