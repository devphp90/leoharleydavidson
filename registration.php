<?php 
include(dirname(__FILE__) . "/_includes/config.php");
include_mailer();
if(isset($_GET['id']) and is_numeric($_GET['id'])){
	$id = (int)$_GET['id'];

	if (!$result = $mysqli->query('SELECT 
	subscription_contest.*,
	subscription_contest_description.name AS title,
	subscription_contest_description.description,
	rebate_coupon_description.description AS coupon_description
	FROM subscription_contest
	INNER JOIN 
	subscription_contest_description 
	ON 
	subscription_contest.id = subscription_contest_description.id_subscription_contest AND subscription_contest_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"
	LEFT JOIN 
	rebate_coupon_description 
	ON 
	subscription_contest.id_rebate_coupon = rebate_coupon_description.id_rebate_coupon AND rebate_coupon_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"
	WHERE 
	'.(!$is_admin ? ' subscription_contest.active = 1 AND ':'').' 
	subscription_contest.id = "' . $mysqli->escape_string($id).'"')) throw new Exception('An error occured while trying to get page id.'."\r\n\r\n".$mysqli->error);
	
	if(!$result->num_rows){				
		header("HTTP/1.0 404 Not Found");
		header('Location: /404.php?error=invalid_page');
		exit;	
	}		
	

	$row = $result->fetch_assoc();
	$description = $row['description'];
	$name = $row['title'];
	$customer_only = $row['customer_only'];
	$include_form_address = $row['include_form_address'];
	$include_form_telephone = $row['include_form_telephone'];
	$coupon_code = $row['coupon_code'];
	$coupon_description = $row['coupon_description'];
}else{
	header('Location: /index.php');
	exit;
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
		if (isset($_POST['registration_btn'])) {		
			// validation rules
			$validation = array(
				'email' => array(
					'required' => 1,
					'email' => 1,
					'callback' => function($field_value)
					{
						global $mysqli;
						global $id;
						
						// check if email already is in database
						if ($result = $mysqli->query('SELECT 
						id
						FROM 
						subscription_contest_person
						WHERE
						email = "'.$mysqli->escape_string($field_value).'"
						AND
						id_subscription_contest = "'.$mysqli->escape_string($id).'" 
						LIMIT 1')) {
							if ($result->num_rows) return true;
							return false;	
						} else {
							throw new Exception('An error occured while trying to check if email already is in use.'."\r\n\r\n".$mysqli->mysqli->error);	
						}
					}
				),
				'cemail' => array(
					'required' => 1,
					'equal' => 'email',
				),
				
				'firstname' => array(
					'required' => 1,
				),
				'lastname' => array(
					'required' => 1,
				)
			);
			
			
			if($include_form_address){
				$validation['address'] = array('required' => 1);
				$validation['city'] = array('required' => 1);
				$validation['country_code'] = array('required' => 1);
				$validation['zip'] = array('required' => 1);
			}
			
			
			if($include_form_telephone){
				$validation['telephone'] = array('required' => 1);
			}
			
			if (!sizeof($errors = validate_fields($_POST['form_values'], $validation, array(
			'email' => array(
				'callback' => language('registration', 'ERROR_EMAIL_IN_USE'),
			),
			)))) {	
		
				// current time
				$current_datetime = date('Y-m-d H:i:s');
				
				// loop through each post value and trim
				foreach ($_POST['form_values'] as $key => $value) {
					$$key = $value;
				}
				$zip = strtoupper(str_replace(' ','',$zip));
				if ($mysqli->query('INSERT INTO 
				subscription_contest_person
				SET 
				id_subscription_contest = "'.$mysqli->escape_string($id).'",
				firstname = "'.$mysqli->escape_string(ucfirst(strtolower($firstname))).'",
				lastname = "'.$mysqli->escape_string(ucfirst(strtolower($lastname))).'",
				language_code = "'.$mysqli->escape_string(strtolower($language_code)).'",
				email = "'.$mysqli->escape_string($email).'",
				gender = "'.$mysqli->escape_string($gender).'",
				address = "'.$mysqli->escape_string($address).'",
				city = "'.$mysqli->escape_string($city).'",
				country_code = "'.$mysqli->escape_string($country_code).'",
				state_code = "'.$mysqli->escape_string($state_code).'",
				zip = "'.$mysqli->escape_string($zip).'",
				telephone = "'.$mysqli->escape_string($telephone).'",
				date_created = "'.$mysqli->escape_string($current_datetime).'"')) {
					$id_subscription_contest_person = $mysqli->insert_id;
				
					// send email to customer with activation link
					$mail = new PHPMailer(); // defaults to using php "mail()"
					$mail->CharSet = 'UTF-8';
					
					// text only
					//$mail->IsHTML(false);
				
					$mail->SetFrom($config_site['no_reply_email'], $config_site['site_name']);

					$customer_name = ucfirst(strtolower($firstname)).' '.ucfirst(strtolower($lastname));
			
					$mail->AddAddress($email, $customer_name);
					
					$mail->Subject = language('emails', 'REGISTRATION_FORM_SUBJECT');
					
					$mail->AltBody = language('emails', 'REGISTRATION_FORM_PLAIN',array(0=>$customer_name,1=>$config_site['site_name'],'coupon_description'=>($coupon_description?language('registration', 'LABEL_COUPON_REBATE_DESCRIPTION').$coupon_description:''),2=>($coupon_code?language('registration', 'LABEL_COUPON_REBATE').'<strong>'.$coupon_code.'</strong>':''),'name'=>$name,'signature'=>get_company_signature(1)));

					
					$mail->MsgHTML(language('emails', 'REGISTRATION_FORM_HTML',array(0=>$customer_name,1=>$config_site['site_name'],'coupon_description'=>($coupon_description?language('registration', 'LABEL_COUPON_REBATE_DESCRIPTION').$coupon_description:''),2=>($coupon_code?language('registration', 'LABEL_COUPON_REBATE').'<strong>'.$coupon_code.'</strong>':''),'name'=>$name,'signature'=>get_company_signature(1))));

					$sendmail_failed = $mail->Send() ? 0:1;
								
					if ($sendmail_failed) {
						// update
						if (!$mysqli->query('UPDATE 
						subscription_contest_person 
						SET
						sendmail_failed = 1
						WHERE
						id = "'.$mysqli->escape_string($id_subscription_contest_person).'"
						LIMIT 1')) {
							throw new Exception('An error occured while trying to update account.'."\r\n\r\n".$mysqli->mysqli->error);		
						}	
					}else{
						$_POST['form_values'] = array();
						$success = 1;
					}
			
				} else {
					throw new Exception('An error occured while trying to create account.'."\r\n\r\n".$mysqli->mysqli->error);	
				}
			}
		}else{
			if(!$_SESSION['customer']['id']){
				header("Location: /account/login.php?return=".urlencode('/registration?id='.$id));
				exit;
			}else{
					if (!$result = $mysqli->query('SELECT 
					id
					FROM 
					subscription_contest_person
					WHERE
					id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'"
					AND
					id_subscription_contest = "'.$mysqli->escape_string($id).'" 
					LIMIT 1')) {
						throw new Exception('An error occured while trying to check if email already is in use.'."\r\n\r\n".$mysqli->mysqli->error);	
					}
					if (!$result->num_rows){
								
						// current time
						$current_datetime = date('Y-m-d H:i:s');
						
						
						if ($mysqli->query('INSERT INTO 
						subscription_contest_person
						SET 
						id_subscription_contest = "'.$mysqli->escape_string($id).'",
						id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'",
						date_created = "'.$mysqli->escape_string($current_datetime).'"')) {
							
							
							$id_subscription_contest_person = $mysqli->insert_id;
	
						
							// send email to customer with activation link
							$mail = new PHPMailer(); // defaults to using php "mail()"
							$mail->CharSet = 'UTF-8';
							
							// text only
							//$mail->IsHTML(false);
						
							$mail->SetFrom($config_site['no_reply_email'], $config_site['site_name']);
		
							$customer_name = $_SESSION['customer']['name'];
					
							$mail->AddAddress($_SESSION['customer']['email'], $_SESSION['customer']['name']);
							
							$mail->Subject = language('emails', 'REGISTRATION_FORM_SUBJECT');
							
							$mail->AltBody = language('emails', 'REGISTRATION_FORM_PLAIN',array(0=>$customer_name,1=>$config_site['site_name'],'coupon_description'=>($coupon_description?language('registration', 'LABEL_COUPON_REBATE_DESCRIPTION').$coupon_description:''),2=>($coupon_code?language('registration', 'LABEL_COUPON_REBATE').'<strong>'.$coupon_code.'</strong>':''),'name'=>$name,'signature'=>get_company_signature(1)));
		
							
							$mail->MsgHTML(language('emails', 'REGISTRATION_FORM_HTML',array(0=>$customer_name,1=>$config_site['site_name'],'coupon_description'=>($coupon_description?language('registration', 'LABEL_COUPON_REBATE_DESCRIPTION').$coupon_description:''),2=>($coupon_code?language('registration', 'LABEL_COUPON_REBATE').'<strong>'.$coupon_code.'</strong>':''),'name'=>$name,'signature'=>get_company_signature(1))));
		
							$sendmail_failed = $mail->Send() ? 0:1;
										
							if ($sendmail_failed) {
								// update
								if (!$mysqli->query('UPDATE 
								subscription_contest_person 
								SET
								sendmail_failed = 1
								WHERE
								id = "'.$mysqli->escape_string($id_subscription_contest_person).'"
								LIMIT 1')) {
									throw new Exception('An error occured while trying to update account.'."\r\n\r\n".$mysqli->mysqli->error);		
								}	
							}else{
								$success = 1;
							}
							
											
						} else {
							throw new Exception('An error occured while trying to create account.'."\r\n\r\n".$mysqli->mysqli->error);	
						}
					}else{
						$error_exist = 1;
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
<title><?php echo $config_site['site_name']; ?></title>
<?php include(dirname(__FILE__) . "/_includes/template/header.php");?>
<script type="text/javascript" language="javascript">
<!--
$(function(){
	$("#country_code").bind({
		change: get_state_list
	});	
});

function get_state_list() {
	var country_code = $("#country_code").val();	
	
	if (!country_code) { 
		alert("<?php echo language('global', 'SELECT_COUNTRY');?>"); 
		return false;
	} else {
		$.ajax({
			url: "/registration?id=<?php echo $id;?>",
			data: { "task":"get_state_list","country_code":country_code },
			dataType: "json",
			error: function(jqXHR, textStatus, errorThrown) { 
				alert(jqXHR.responseText);
			},
			success: function( data ) {
				$("#state_code").html("").append('<option value="">-- <?php echo language('global', 'SELECT_STATE_PROVINCE');?> --</option>');
				
				if (data) {
					$.each(data,function(key, value){
						$("#state_code").append('<option value="'+key+'">'+value+'</option>');
					});
				}
			}
		});
	}
}
-->
</script>
</head>
<body>
<?php include(dirname(__FILE__) . "/_includes/template/top.php");?>
<div id="main">
	<!-- ADDS(Publicity) -->
	<?php include(dirname(__FILE__) . "/_includes/template/pub.php");?>
    <!-- END ADDS -->
	<div id="content">
    <div class="cms_content">
        <h1 style="margin-top:10px;"><?php echo $name;?></h1>
        
        
        
        
        
        <?php
		if ($success) {
			echo '<div class="success">'.language('registration', 'SUCCESS_REGISTRATION').'</div>';
		}
		
		if ($sendmail_failed) {
			echo '<div class="error">'.language('registration', 'ERROR_SEND_EMAIL').'</div>';
		}
		
		if (sizeof($errors)) {
			echo '<div class="error">'.language('global', 'ERROR_OCCURED').'</div>';
		}
		?><?php echo $description;?>
		<?php if($customer_only){?>
        <form method="post" enctype="multipart/form-data">  
        	<div style="margin-top:10px; text-align:center">
            <input type="submit" name="registration_btn_customer" value="<?php echo language('registration', 'BTN_GET_REGISTERED');?>" class="button"  style="padding:3px; font-size:15px;" />
                           
            </div> 
        </form>
        <?php }else{?> 
                <form method="post" enctype="multipart/form-data">     
                    <div style="width:98%;">                    
                        <div class="title_bg title_bg_1"><div class=" title_bg_text title_bg_text_ffffff"><?php echo language('registration', 'TITLE_PERSONAL_INFORMATION');?></div></div>
                        <div class="title_bg_text_box">
                        <div style="margin-bottom:5px;">
                            <strong><?php echo language('global', 'ADDRESS_GENDER');?> </strong><br />
                            <input type="radio" name="form_values[gender]" value="0" <?php echo(!$_POST['form_values']['gender']?'checked="checked"':'');?> />&nbsp;<?php echo language('global', 'ADDRESS_MALE');?>&nbsp;&nbsp;<input type="radio" name="form_values[gender]" value="1" <?php echo($_POST['form_values']['gender']?'checked="checked"':'');?> />&nbsp;<?php echo language('global', 'ADDRESS_FEMALE');?><br /><span class="error"><?php echo $errors['gender']; ?></span>                                  
                            </div>                           
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
                            <div style="margin-bottom:5px;">
                            <strong><?php echo language('registration', 'LABEL_EMAIL_ADDRESS');?> *</strong><br />
                            <input type="text" name="form_values[email]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['email']); ?>" <?php echo $errors['email'] ? 'class="error"':''; ?> />      
                            <br /><span class="error"><?php echo $errors['email']; ?></span>                                                        
                            </div>
                            <div style="margin-bottom:5px;">
                            <strong><?php echo language('registration', 'LABEL_CONFIRM_EMAIL_ADDRESS');?> *</strong><br />
                            <input type="text" name="form_values[cemail]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['cemail']); ?>" <?php echo $errors['cemail'] ? 'class="error"':''; ?> />        
                            <br /><span class="error"><?php echo $errors['cemail']; ?></span>                                                
                            </div> 
                        
                                                                
                        <?php if($include_form_address){?>                                            
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
                            <input type="text" name="form_values[zip]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['zip']); ?>" <?php echo $errors['zip'] ? 'class="error"':''; ?> /> 
                            <br /><span class="error"><?php echo $errors['zip']; ?></span>                                                             
                            </div>
                        <?php }?>
                        
                        
                        <?php if($include_form_telephone){?> 
                            <div style="margin-bottom:5px;">
                            <strong><?php echo language('global', 'ADDRESS_TELEPHONE');?> *</strong><br />
                            <input type="text" name="form_values[telephone]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['telephone']); ?>" <?php echo $errors['telephone'] ? 'class="error"':''; ?> />    
                            <br /><span class="error"><?php echo $errors['telephone']; ?></span>                                                          
                            </div>      
                        <?php }?>
                        
                        
                        <div style="margin-top:10px;">
                                <div class="button_regular"><input type="submit" value="<?php echo language('registration', 'BTN_REGISTRATION');?>" class="regular" name="registration_btn" /></div>
                                 <div class="cb"></div>                               
                        </div> 
                                                                                 
                    </div>    
                    </div>
               
                </form>
        <?php }?>
    </div>
    </div>
</div>
<?php include(dirname(__FILE__) . "/_includes/template/bottom.php");?>
</body>
</html>