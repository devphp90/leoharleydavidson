<?php
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':

		break;
	case 'POST':
		if (isset($_POST['modify_email'])) {
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
						email = "'.$mysqli->escape_string($field_value).'" and id <> "'.$_SESSION['customer']['id'].'"
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
			);	
			// validation rules
			$message = array(
				'email' => array(
					'callback' => "Already exist!"
				),
			);		
			
			// loop through each post value and trim
			foreach ($_POST['form_values'] as $key => $value) {
				$$key = $value;
			}					
			
			if (!sizeof($errors = validate_fields($_POST['form_values'], $validation, $message))) {	
																		
					if (!$mysqli->query('UPDATE
					customer
					SET
					email = "'.$mysqli->escape_string($email).'"
					WHERE
					id = '.$mysqli->escape_string($_SESSION['customer']['id']).'
					LIMIT 1')) {
						throw new Exception('An error occured while trying to update email.'."\r\n\r\n".$mysqli->mysqli->error);	
					}	
					$_SESSION['customer']['email'] = $mysqli->escape_string($email);
					header('Location: /account?success=modify_email');
					exit;									
					
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
            	<strong><?php echo language('global', 'BREADCRUMBS_MODIFY_EMAIL');?></strong>
            </li>
          </ul>
      	</div>            
    </div>	
	<div class="main">
    <div class="container">
            <form method="post" enctype="multipart/form-data">
                	<h2 class="subtitle"><?php echo language('account/modify-email', 'TITLE_NEW_EMAIL');?></h2>                
                <div class="title_bg title_bg_3">
           			<div class="title_bg_text_box">
                    <?php
						if (sizeof($errors)) {
							echo '<div class="error" style="margin-top:0;">'.language('global', 'ERROR_OCCURED').'</div>';		
						}
					?>
                    <div style="margin-bottom:10px"><?php echo language('account/modify-email', 'TITLE_NEW_EMAIL_DESCRIPTION');?></div>
                        <div style="margin-bottom:5px;">
                        <strong><?php echo language('account/modify-email', 'LABEL_NEW_EMAIL');?> *</strong><br />
                        <input type="text" name="form_values[email]" style="width:250px;" value="<?php echo htmlspecialchars($_POST['form_values']['email']); ?>" <?php echo $errors['email'] ? 'class="error"':''; ?> />      
                        <br /><span class="error"><?php echo $errors['email']; ?></span>                                                        
                        </div>
                        <div style="margin-bottom:5px;">
                        <strong><?php echo language('account/modify-email', 'LABEL_CONFIRM_EMAIL');?> *</strong><br />
                        <input type="text" name="form_values[cemail]" style="width:250px;" value="<?php echo htmlspecialchars($_POST['form_values']['cemail']); ?>" <?php echo $errors['cemail'] ? 'class="error"':''; ?> />        
                        <br /><span class="error"><?php echo $errors['cemail']; ?></span>                                                
                        </div>                 
                
                <div style="margin-top:10px;">
                        <div class="button_regular"><input type="submit" value="<?php echo language('global', 'BTN_SAVE');?>" class="button" name="modify_email" /></div>
                         <div class="cb"></div>                               
                </div> 
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