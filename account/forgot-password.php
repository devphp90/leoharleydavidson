<?php
require('../_includes/config.php');

include_mailer();

if ($_SESSION['customer']['id']) {
	header('Location: '.$url_prefix);
	exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
	case 'POST':
		if (isset($_POST['reset_password'])) {
			// validation rules
			$validation = array(
				'email' => array(
					'required' => 1,
					'email' => 1,
				),
			);		
			
			if (!sizeof($errors = validate_fields($_POST['form_values'], $validation))) {			
				// current time
				$current_datetime = date('Y-m-d H:i:s');			
				
				// loop through each post value and trim
				foreach ($_POST['form_values'] as $key => $value) {
					$$key = $value;
				}
				
				if ($result = $mysqli->query('SELECT
				id,
				firstname,
				lastname
				FROM
				customer
				WHERE
				email = "'.$mysqli->escape_string($email).'" 
				AND 
				active = 1
				LIMIT 1')) {
					if ($result->num_rows) {
						$row = $result->fetch_assoc();
					
						// generate activation key
						$reset_password_key = md5($row['id'].$email.$current_datetime);								
						
						// update
						if (!$mysqli->query('UPDATE 
						customer 
						SET
						reset_password_key = "'.$mysqli->escape_string($reset_password_key).'"
						WHERE
						id = "'.$mysqli->escape_string($row['id']).'"
						LIMIT 1')) {
							throw new Exception('An error occured while trying to update account.'."\r\n\r\n".$mysqli->mysqli->error);		
						}							
	
						
						// send email to customer with activation link
						$mail = new PHPMailer(); // defaults to using php "mail()"
						$mail->CharSet = 'UTF-8';
						
						// text only
						//$mail->IsHTML(false);
					
						$mail->SetFrom($config_site['no_reply_email'], $config_site['site_name']);
	
						$customer_name = $row['firstname'].' '.$row['lastname'];
				
						$mail->AddAddress($email, $customer_name);
						
						$mail->Subject = language('emails', 'FORGOT_PASSWORD_SUBJECT');
						
						$mail->AltBody = language('emails', 'FORGOT_PASSWORD_PLAIN',array(0=>$customer_name,1=>$config_site['site_name'],2=>"http://".$_SERVER['HTTP_HOST']."/account/reset-password?reset_password_key=".$reset_password_key,
						'signature'=>get_company_signature(0)));
						
						$mail->MsgHTML(language('emails', 'FORGOT_PASSWORD_HTML',array(0=>$customer_name,1=>$config_site['site_name'],2=>"http://".$_SERVER['HTTP_HOST']."/account/reset-password?reset_password_key=".$reset_password_key,
						'signature'=>get_company_signature(1))));

						$sendmail_failed = $mail->Send() ? 0:1;
						
						$_POST['form_values'] = array();		
						
						$success = 1;		
					} else {
						$invalid_email = 1;	
					}
				} else {
					throw new Exception('An error occured while trying to get account information.'."\r\n\r\n".$mysqli->mysqli->error);	
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
<title>Forgot your password - <?php echo $config_site['site_name'];?></title>
<?php include("../_includes/template/header.php");?>
</head>
<body>
<?php include("../_includes/template/top.php");?>

<div class="main-container">
	<div class="main nobc">
    	<div class="container">
        	<div class="main-content">
				<div class="page-title">
                    <h1><?php echo language('account/forgot-password', 'TITLE_PASSWORD_RECOVERY');?></h1>
                </div>
                <?php
                    $r_msg = '';
                    $r_class= '';
                    if ($success) {
        				$r_msg = language('account/forgot-password', 'SUCCESS_RESET');
        				$r_class = 'alert-success';
                    }
        			
        			if ($invalid_email) {
        				$r_msg = language('account/forgot-password', 'ERROR_NO_MATCH');
        				$r_class = 'alert-danger';
        			} else if ($sendmail_failed) {
        				$r_msg = language('account/forgot-password', 'ERROR_SEND_EMAIL');
        				$r_class = 'alert-danger';
        			}
        			
        			if($r_msg != '') {
                ?>                
                <div class="messages">
                	<div class="alert <?php echo $r_class;?>">
                		<button type="button" class="close" data-dismiss="alert">×</button>
                		<ul><li><span><?php echo $r_msg;?></span></li></ul>
                	</div>
                </div>
                <?php }?>
                <form method="post" id="form-validate">
                    <div class="fieldset">
                        <h2 class="legend"><?php echo language('account/forgot-password', 'LABEL_EMAIL_ADDRESS_DESCRIPTION');?></h2>
                        <p><?php echo language('account/forgot-password', 'LABEL_EMAIL_ADDRESS_DESCRIPTION_BIS');?></p>
                        <ul class="form-list">
                            <li>
                                <label for="email_address" class="required"><i class="icon-email"></i><?php echo language('account/forgot-password', 'LABEL_EMAIL_ADDRESS');?><em>*</em></label>
                                <div class="input-box">
                                    <input type="text" name="form_values[email]" alt="<?php echo htmlspecialchars($_POST['form_values']['email']); ?>" id="email_address" class="input-text required-entry validate-email <?php if($errors['email']) echo 'validation-failed';?>" value="<?php echo htmlspecialchars($_POST['form_values']['email']); ?>">
                                </div>                               
                            </li>
                    	</ul>
                    	 <?php if($errors['email']) echo '<span style="color:red">'.$errors['email'].'</span>';?>
                    </div>
                    <div class="buttons-set">                        
                        <p class="back-link"><a href="/account/login<?php echo (!empty($return_url) ? '?return='.$return_url:'');?>"><small>« </small><?php echo language('account/forgot-password', 'LABEL_BACK_TO_LOGIN');?></a></p>
                        <button type="submit" title="<?php echo language('global', 'BTN_SUBMIT');?>" class="button" name="reset_password"><span><span><?php echo language('global', 'BTN_SUBMIT');?></span></span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("../_includes/template/bottom.php");?>
</body>
</html>