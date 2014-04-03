<?php
require('../_includes/config.php');
include_mailer();

$return = trim($_GET['return']);

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		if ($activation_key = trim($_GET['activation_key'])) {	
			if ($result = $mysqli->query('SELECT
			id,
			IF(date_confirmed = "0000-00-00 00:00:00",0,1) AS activated
			FROM 
			customer
			WHERE
			activation_key = "'.$mysqli->escape_string($activation_key).'"
			LIMIT 1')) {
				if ($result->num_rows) {
					$row = $result->fetch_assoc();	
					
					if ($row['activated']) $already_activated = 1;
					else {
						$current_datetime = date('Y-m-d H:i:s');
						
						if (!$mysqli->query('UPDATE
						customer
						SET
						date_confirmed = "'.$mysqli->escape_string($current_datetime).'",
						active = 1
						WHERE
						activation_key = "'.$mysqli->escape_string($activation_key).'"
						LIMIT 1')) {
							throw new Exception('An error occured while trying to activate account.'."\r\n\r\n".$mysqli->mysqli->error);	
						}	
						
						header('Location: login?success=activation'.($return?'&return='.urlencode($return):'').'');
						exit;				
					}
				} else {
					$invalid_activation_key = 1; 	
				}
			}
		}	
		break;
	case 'POST':
		if (isset($_POST['activate_account'])) {
			// validation rules
			$validation = array(
				'activation_key' => array(
					'required' => 1,
				),
			);		
			
			if (!sizeof($errors = validate_fields($_POST['form_values'], $validation))) {				
				// loop through each post value and trim
				foreach ($_POST['form_values'] as $key => $value) {
					$$key = $value;
				}
						
				header('Location: ?activation_key='.$activation_key);
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
<body>
<?php include("../_includes/template/top.php");?>
<div id="main">
	<!-- ADDS(Publicity) -->
	<?php include("../_includes/template/pub.php");?>
    <!-- END ADDS -->
	<div id="content">
        <h1><?php echo language('account/activation','TITLE_PAGE');?></h1>
	    <?php				
			if ($already_activated) {
				echo '<div class="error">'.language('account/activation','ERROR_ALREADY_ACTIVATE').'</div>';
		?>
        <div style="margin-top:10px;">
        	<a href="login"><?php echo language('account/activation','LINK_LOGIN_PAGE');?></a>
        </div>
        <?php					
			} else { 
				if ($invalid_activation_key ) {
					echo '<div class="error">'.language('account/activation','ERROR_INVALID_KEY').'</div>';				
				}
 		?>
        <div>
            <form method="post" enctype="multipart/form-data">
                <div class="title"><?php echo language('account/activation','LABEL_ACTIVATE_YOUR_ACCOUNT');?></div>
                <div style="border: 1px solid #CCC; padding: 20px;">
                    <div style="margin-bottom:5px;">
                    <strong><?php echo language('account/activation','LABEL_ACTIVATION_KEY');?></strong><br />
                    <input type="text" name="form_values[activation_key]" style="width:250px;" value="<?php echo htmlspecialchars($_POST['form_values']['activation_key']); ?>" <?php echo $errors['activation_key'] ? 'class="error"':''; ?> />      
                    <br /><span class="error"><?php echo $errors['activation_key']; ?></span>                                                        
                    </div>                                                                          
                    
                    <div style="margin-top:10px;">
                        <br />
                        <input type="submit" value="<?php echo language('account/activation','BTN_ACTIVATE_ACCOUNT');?>" class="button" name="activate_account" />
                    </div>                                                          
                </div>    
                
               
            </form>
        </div>
        <?php } ?>
	</div>        
</div>

<?php include("../_includes/template/bottom.php");?>
</body>
</html>