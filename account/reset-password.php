<?php
require('../_includes/config.php');

if ($_SESSION['customer']['id']) {
	header('Location: /account');
	exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		$reset_password_key = trim($_GET['reset_password_key']);
		break;
	case 'POST':
		if (isset($_POST['reset_password'])) {
			// validation rules
			$validation = array(
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
				'reset_password_key' => array(
					'required' => 1,
				),
			);		
			
			// loop through each post value and trim
			foreach ($_POST['form_values'] as $key => $value) {
				$$key = $value;
			}					
			
			if (!sizeof($errors = validate_fields($_POST['form_values'], $validation))) {	
				if ($result = $mysqli->query('SELECT 
				id 
				FROM 
				customer
				WHERE
				reset_password_key = "'.$mysqli->escape_string($reset_password_key).'"
				LIMIT 1')) {
					if ($result->num_rows) { 
						$row = $result->fetch_assoc();
						
						$password = generate_password($row['id'], $password);
																						
						if (!$mysqli->query('UPDATE
						customer
						SET
						password = "'.$mysqli->escape_string($password).'",
						reset_password_key = ""
						WHERE
						reset_password_key = "'.$mysqli->escape_string($reset_password_key).'"
						LIMIT 1')) {
							throw new Exception('An error occured while trying to reset password.'."\r\n\r\n".$mysqli->mysqli->error);	
						}	
				
						header('Location: login?success=reset_password');
						exit;									
					} else {
						$invalid_reset_password_key = 1;	
					}
					
					$result->close();
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
</head>
<body>
<?php include("../_includes/template/top.php");?>
<div id="main">
	<!-- ADDS(Publicity) -->
	<?php include("../_includes/template/pub.php");?>
    <!-- END ADDS -->
	<div id="content">
    <div class="breadcrumbs" style="margin-bottom: 10px;">
        <div style="float:left;"><a href="/"><?php echo language('global', 'BREADCRUMBS_HOME');?></a> > <a href="login"><?php echo language('_include/template/top', 'LINK_SIGN_IN');?></a> > <?php echo language('account/reset-password', 'TITLE_RESET_PASSWORD');?></div>
        <div class="cb"></div>
        </div>
        <?php
			if ($invalid_reset_password_key) {
				echo '<div class="error">'.language('account/reset-password', 'ERROR_INVALID_KEY').'</div>';		
			}
		?>
        <div>
            <form method="post" enctype="multipart/form-data">
                <div class="title_bg title_bg_1"><div class=" title_bg_text title_bg_text_ffffff"><?php echo language('account/reset-password', 'TITLE_RESET_PASSWORD');?></div></div>
                    <div class="title_bg_text_box">
                    <div style="margin-bottom:5px;">
                    <strong><?php echo language('account/reset-password', 'LABEL_PASSWORD');?> *</strong><br />
                    <input type="password" name="form_values[password]" style="width:250px;" <?php echo $errors['password'] ? 'class="error"':''; ?> />  
                    <br /><span class="error"><?php echo $errors['password']; ?></span>                 
                    </div>
                    <div style="margin-bottom:5px;">
                    <strong><?php echo language('account/reset-password', 'LABEL_CONFIRM_PASSWORD');?> *</strong><br />
                    <input type="password" name="form_values[cpassword]" style="width:250px;" <?php echo $errors['cpassword'] ? 'class="error"':''; ?> />     
                    <br /><span class="error"><?php echo $errors['cpassword']; ?></span>              
                    </div>            
                
                    <div style="margin-bottom:5px;">
                    <strong><?php echo language('account/reset-password', 'LABEL_RESET_PASSWORD_KEY');?> *</strong><br />
                    <input type="text" name="form_values[reset_password_key]" style="width:250px;" value="<?php echo htmlspecialchars($reset_password_key); ?>" <?php echo $errors['reset_password_key'] ? 'class="error"':''; ?> />      
                    <br /><span class="error"><?php echo $errors['reset_password_key']; ?></span>                                                        
                    </div>                                                                          
                    
                    <div style="margin-top:10px;">
                        <div class="button_regular"><input type="submit" value="<?php echo language('global', 'BTN_SUBMIT');?>" class="regular" name="reset_password" /></div>
                        <div class="cb"></div> 
                    </div>                                                          
                </div>                 
            </form>
        </div>
	</div>        
</div>

<?php include("../_includes/template/bottom.php");?>
</body>
</html>