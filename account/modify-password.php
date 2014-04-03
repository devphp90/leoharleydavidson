<?php
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':

		break;
	case 'POST':
		if (isset($_POST['modify_password'])) {
			// validation rules
			$validation = array(
				'apassword' => array(
					'required' => 1,
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
			);		
			
			// loop through each post value and trim
			foreach ($_POST['form_values'] as $key => $value) {
				$$key = $value;
			}					
			
			if (!sizeof($errors = validate_fields($_POST['form_values'], $validation))) {	
				$apassword = md5($_SESSION['customer']['id'].$apassword);
				if ($result = $mysqli->query('SELECT 
				id 
				FROM 
				customer
				WHERE
				password = "'.$mysqli->escape_string($apassword).'" and id = '.$_SESSION['customer']['id'].'
				LIMIT 1')) {
					if ($result->num_rows) { 
						$password = md5($_SESSION['customer']['id'].$password);															
						if (!$mysqli->query('UPDATE
						customer
						SET
						password = "'.$mysqli->escape_string($password).'"
						WHERE
						id = '.$mysqli->escape_string($_SESSION['customer']['id']).'
						LIMIT 1')) {
							throw new Exception('An error occured while trying to reset password.'."\r\n\r\n".$mysqli->mysqli->error);	
						}	
				
						header('Location: /account?success=modify_password');
						exit;									
					}else{
						$errors['apassword']='Wrong password';
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
<body class="bv3">
<?php include("../_includes/template/top.php");?>
<div class="main-container">
	<div class="breadcrumbs">   	
		<div class="container">
          <ul>
          	<li>
            	<a href="/" title="<?php echo language('global', 'BREADCRUMBS_HOME');?>"><?php echo language('global', 'BREADCRUMBS_HOME');?></a>
            	<span>&gt;</span>
            </li>
            <li>
            	<a href="/account" title="<?php echo language('global', 'BREADCRUMBS_YOUR_ACCOUNT');?>"><?php echo language('global', 'BREADCRUMBS_YOUR_ACCOUNT');?></a>
            	<span>&gt;</span>
            </li>
            <li>
            	<strong><?php echo language('global', 'BREADCRUMBS_MODIFY_PASSWORD');?></strong>
            </li>
          </ul>
      	</div>            
    </div>	
	<div class="main">
    <div class="container">   
            <form method="post" enctype="multipart/form-data">
                	<h2 class="subtitle"><?php echo language('account/modify-password', 'TITLE_NEW_PASSWORD');?></h2>                
               
           			<div class="title_bg_text_box">
                     <?php
						if (sizeof($errors)) {
							echo '<div class="error" style="margin-top:0;">'.language('global', 'ERROR_OCCURED').'</div>';		
						}
					?>
                    <div style="margin-bottom:10px"><?php echo language('account/modify-password', 'TITLE_NEW_PASSWORD_DESCRIPTION');?></div>
                    <strong><?php echo language('account/modify-password', 'LABEL_CURRENT_PASSWORD');?> *</strong><br />
                    <input type="password" name="form_values[apassword]" style="width:250px;" <?php echo $errors['apassword'] ? 'class="error"':''; ?> />  
                    <br /><span class="error"><?php echo $errors['apassword']; ?></span> 
                    <div style="margin-bottom:5px;">
                    <strong><?php echo language('account/modify-password', 'LABEL_NEW_PASSWORD');?> *</strong><br />
                    <input type="password" name="form_values[password]" style="width:250px;" <?php echo $errors['password'] ? 'class="error"':''; ?> />     
                    <br /><span class="error"><?php echo $errors['password']; ?></span>              
                    </div>         
                                    
                    <div style="margin-bottom:5px;">
                    <strong><?php echo language('account/modify-password', 'LABEL_CONFIRM_PASSWORD');?> *</strong><br />
                    <input type="password" name="form_values[cpassword]" style="width:250px;" <?php echo $errors['cpassword'] ? 'class="error"':''; ?> />     
                    <br /><span class="error"><?php echo $errors['cpassword']; ?></span>              
                    </div>            

                    <div style="margin-top:10px;">
                        <div class="button_regular"><input type="submit" value="<?php echo language('global', 'BTN_SAVE');?>" name="modify_password" class="button"/></div>
                         <div class="cb"></div>                             
                	</div> 
                </div>                
            </form>
	</div>
	</div> 
    <p>&nbsp;</p>         
</div>

<?php include("../_includes/template/bottom.php");?>
</body>
</html>