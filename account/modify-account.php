<?php
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");

switch ($_SERVER['REQUEST_METHOD']) {

	case 'POST':
		if (isset($_POST['modify_account'])) {		
			// validation rules
			$validation = array(
				'firstname' => array(
					'required' => 1,
				),
				'lastname' => array(
					'required' => 1,
				),	
				'dob' => array(
//					'required' => 1,
					'date' => 1,
				),
			);	
				
			// loop through each post value and trim
			foreach ($_POST['form_values'] as $key => $value) {
				$$key = $value;
			}
			if (!sizeof($errors = validate_fields($_POST['form_values'], $validation))) {				
				if ($mysqli->query('UPDATE 
				customer
				SET 
				firstname = "'.$mysqli->escape_string($firstname).'",
				lastname = "'.$mysqli->escape_string($lastname).'",
				language_code = "'.$mysqli->escape_string($language_code).'",
				dob = "'.$mysqli->escape_string($dob).'",
				gender = "'.$mysqli->escape_string($gender).'"
				WHERE id = "'.$_SESSION['customer']['id'].'"')) {			
				} else {
					throw new Exception('An error occured while trying to create account.'."\r\n\r\n".$mysqli->mysqli->error);	
				}
				
				// update session
				$_SESSION['customer']['name'] = $firstname.' '.$lastname;
				$_SESSION['customer']['dob'] = $dob;		
				
				header('Location: /account?success=modify_account');
				exit;
			}
		}
		break;
	default:
	
		if (!$result = $mysqli->query('SELECT 
			*
			FROM				
			customer 
			WHERE
			id = "'.$_SESSION['customer']['id'].'"
			AND 
			active = 1
			LIMIT 1')) {
				throw new Exception('An error occured while trying to get infos.'."\r\n\r\n".$mysqli->mysqli->error);	
			}
		
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			foreach ($row as $key => $value) {
				$$key = $value;
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
            	<strong><?php echo language('global', 'BREADCRUMBS_MODIFY_PERSONNAL_INFOS');?></strong>
            </li>
          </ul>
      	</div>            
    </div>
    <div class="main">
    <div class="container">
    <div class="main-content"> 
     <h2 class="subtitle"><?php echo language('account/modify-account', 'TITLE_PERSONNAL_INFORMATION');?></h2>  
      
        <form method="post">                   
            <div class="title_bg_text_box"> 
            <?php echo language('account/modify-account', 'TITLE_PERSONNAL_INFORMATION_DESCRIPTION');?>
             <?php
			if (sizeof($errors)) {
				echo '<div class="error" style="margin-top:0;">'.language('global', 'ERROR_OCCURED').'</div>';		
			}
		?>                          
                <div style="margin-bottom:5px; margin-top:10px;">
                <strong><?php echo language('global', 'ADDRESS_FIRST_NAME');?> *</strong><br />
                <input type="text" name="form_values[firstname]" style="width:100%;" value="<?php echo htmlspecialchars($firstname); ?>" <?php echo $errors['firstname'] ? 'class="error"':''; ?> />  
                <br /><span class="error"><?php echo $errors['firstname']; ?></span>                                                            
                </div>                                     
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_LAST_NAME');?> *</strong><br />
                <input type="text" name="form_values[lastname]" style="width:100%;" value="<?php echo htmlspecialchars($lastname); ?>" <?php echo $errors['lastname'] ? 'class="error"':''; ?> />     
                <br /><span class="error"><?php echo $errors['lastname']; ?></span>                                                         
                </div>                                         
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_DATE_OF_BIRTH');?></strong><br />
                <input type="text" name="form_values[dob]" style="width:100%;" maxlength="10" value="<?php echo $dob != '0000-00-00' ? htmlspecialchars($dob):''; ?>" <?php echo $errors['dob'] ? 'class="error"':''; ?> />        
                <br /><span class="error"><?php echo $errors['dob']; ?></span>                                                      
                </div>
                
                <div style="margin-bottom:5px;">
                <strong><?php echo language('global', 'ADDRESS_GENDER');?> </strong><br />
                <input type="radio" name="form_values[gender]" value="0" <?php echo (!$gender?'checked="checked"':''); ?> />&nbsp;<?php echo language('global', 'ADDRESS_MALE');?>&nbsp;&nbsp;<input type="radio" name="form_values[gender]" value="1" <?php echo ($gender?'checked="checked"':''); ?> />&nbsp;<?php echo language('global', 'ADDRESS_FEMALE');?><br /><span class="error"><?php echo $errors['gender']; ?></span>                                  
                </div>                                                                
                
                <div style="margin-bottom:5px;">
                  <strong><?php echo language('global', 'ADDRESS_CORRESPONDENCE_LANGUAGE');?> </strong><br />
                  <select name="form_values[language_code]">
                  <?php
                  $query = 'SELECT * FROM language WHERE active = 1 ORDER BY default_language DESC';
                    if ($result = $mysqli->query($query)) {
                        while($row = $result->fetch_assoc()){
                            echo '<option value="'.$row['code'].'" '. ($row['code'] == $language_code ? 'selected="selected"':'').'>'.$row['name'].'</option>';
                        } 
                        $result->close();
                    }
                  ?>
                  </select>
                </div>
                
                
                <div style="margin-top:10px;">
                        <div class="button_regular"><input type="submit" value="<?php echo language('global', 'BTN_SAVE');?>" class="button regular" name="modify_account" /></div>
                         <div class="cb"></div>                               
                </div> 
                                                                    
            </div>    
           
        </form>
	</div>        
</div>
</div>
</div>

<?php include("../_includes/template/bottom.php");?>
</body>
</html>