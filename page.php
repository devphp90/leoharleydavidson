<?php 
include(dirname(__FILE__) . "/_includes/config.php");
if(isset($_GET['id_page']) and is_numeric($_GET['id_page'])){
	$id_page = (int)$_GET['id_page'];

	if (!$result = $mysqli->query('SELECT 
	cmspage_description.name,
	cmspage_description.description,
	cmspage_description.meta_description,
	cmspage_description.meta_keywords,
	cmspage.indexing
	FROM cmspage
	INNER JOIN cmspage_description ON cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"
	WHERE '.(!$is_admin ? 'cmspage.active = 1 AND ':'').'cmspage.id = "' . $mysqli->escape_string($id_page).'"')) throw new Exception('An error occured while trying to get page id.'."\r\n\r\n".$mysqli->error);
	
	if(!$result->num_rows){				
		header("HTTP/1.0 404 Not Found");
		header('Location: /404.php?error=invalid_page');
		exit;	
	}		
	

	$row = $result->fetch_assoc();
	$description = $row['description'];
	$name = $row['name'];	
	$indexing = $row['indexing'];
	$meta_description = $row['meta_description'];
	$meta_keywords = $row['meta_keywords'];
} else if(isset($_GET['alias']) and !empty($_GET['alias'])){
	$alias = trim($_GET['alias']);
	$id_page = (int)array_pop(explode('-',$alias));

	if (!$result = $mysqli->query('SELECT 
	cmspage_description.id_cmspage,
	cmspage_description.name,
	cmspage_description.description,
	cmspage_description.meta_description,
	cmspage_description.meta_keywords,	
	cmspage.indexing
	FROM cmspage
	INNER JOIN cmspage_description ON cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"
	WHERE '.(!$is_admin ? 'cmspage.active = 1 AND ':'').'cmspage_description.alias = "' . $mysqli->escape_string($alias).'" AND cmspage.indexing = 1')) throw new Exception('An error occured while trying to get page id.'."\r\n\r\n".$mysqli->error);
	
	if(!$result->num_rows){			
		// if we can't find by alias but have a id_page try to load
		if ($id_page) {
			if (!$result = $mysqli->query('SELECT 
			cmspage_description.id_cmspage,
			cmspage_description.name,
			cmspage_description.description,
			cmspage_description.meta_description,
			cmspage_description.meta_keywords,	
			cmspage.indexing
			FROM cmspage
			INNER JOIN cmspage_description ON cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"
			WHERE '.(!$is_admin ? 'cmspage.active = 1 AND ':'').'cmspage.id = "' . $mysqli->escape_string($id_page).'" AND cmspage.indexing = 0')) throw new Exception('An error occured while trying to get page id.'."\r\n\r\n".$mysqli->error);
		}
			
		if(!$result->num_rows){	
			
			header("HTTP/1.0 404 Not Found");
			header('Location: /404.php?error=invalid_page');
			exit;	
		}
	}		
	

	$row = $result->fetch_assoc();
	$description = $row['description'];
	$name = $row['name'];	
	$id_page = $row['id_cmspage'];
	$indexing = $row['indexing'];
	$meta_description = $row['meta_description'];
	$meta_keywords = $row['meta_keywords'];	
}else{
	header('Location: /index.php');
	exit;
}




$breadcrumb = '';
//Breadcrumbs
function get_breadcrumbs($id_page){
	global $breadcrumb, $mysqli, $is_admin;

	$query = 'SELECT 
				cmspage.id,
				cmspage.id_parent,
				cmspage.header_only,
				cmspage_description.name,
				cmspage_description.alias,
				cmspage.indexing
				FROM cmspage
				INNER JOIN cmspage_description ON cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$_SESSION['customer']['language'].'"
				WHERE '.(!$is_admin ? 'cmspage.active = 1 AND ':'').'cmspage.id = '.$id_page.' 
				ORDER BY sort_order ASC';
		
	if ($result = $mysqli->query($query)) {
		if($result->num_rows){
			$row = $result->fetch_assoc();
			if(!empty($breadcrumb)){
				if($row['header_only']){
					$breadcrumb = '<li><span>&gt;</span>
            	<strong>'.$row['name'].'</strong>
            </li>'. $breadcrumb;
				}else{
					$breadcrumb = '<li>
            	<span>&gt;</span><a href="/'.$_SESSION['customer']['language'].'/page/'.$row['alias'].'">'.$row['name'].'</a>
                </li>'. $breadcrumb;
				}
			}else{
				$breadcrumb =  '<li><span>&gt;</span>
            	<strong>'.$row['name'].'</strong>
            </li>';
			}
			if($row['id_parent']){
				get_breadcrumbs($row['id_parent']);
			}
			
		}
	}
}

get_breadcrumbs($id_page);
//END Breadcrumbs

// contact form
if ($id_page == 18) {
	// get custom fields
	$custom_fields = array();
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
	custom_fields.form = 1
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
}

switch ($_SERVER['REQUEST_METHOD']) {
	case 'POST':
	
		// check for language post
		if (isset($_POST['language_main_site']) && $_POST['language_main_site']) {
			if (!$result = $mysqli->query('SELECT 
			cmspage_description.name,
			cmspage_description.alias
			FROM
			cmspage_description
			WHERE
			cmspage_description.id_cmspage = "'.$mysqli->escape_string($id_page).'"
			AND
			cmspage_description.language_code = "'.$mysqli->escape_string($_POST['language_main_site']).'" 
			LIMIT 1')) throw new Exception('An error occured while trying to get page.'."\r\n\r\n".$mysqli->error);
			if (!$row_switch = $result->fetch_assoc()) {
				header("HTTP/1.0 404 Not Found");
				header('Location: /404.php?error=invalid_page');
				exit;			
			}		
			
			if ($indexing) header('Location: /'.$_POST['language_main_site'].'/page/'.$row_switch['alias']);
			else header('Location: /'.$_POST['language_main_site'].'/page/'.makesafetitle($row_switch['name']).'-'.$id_page);
			exit;
		}else if (isset($_POST['registration_btn'])) {	
			include_mailer();	
			// validation rules
			$validation = array(
				'email' => array(
					'required' => 1,
					'email' => 1,
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
				),
				'comments' => array(
					'required' => 1,
				)
			);
			
			// validate custom fields
			if (isset($custom_fields) && is_array($custom_fields) && sizeof($custom_fields)) {
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
							if (($_POST['form_values']['custom_fields'][$row['id']]['value'] || $_POST['form_values']['custom_fields'][$row['id']]['options'][$row_option['id']]['value']) && $row_option['add_extra'] && $row_option['extra_required']) {								
								
								// multiple checkboxes
								if ($row['type'] == 1) {	
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
			
			if (!sizeof($errors = validate_fields($_POST['form_values'], $validation))) {	
		
				// current time
				$current_datetime = date('Y-m-d H:i:s');
				
				// loop through each post value and trim
				foreach ($_POST['form_values'] as $key => $value) {
					if ($key != 'custom_fields') $$key = $value;
				}
				
			
				// send email to customer with activation link
				$mail = new PHPMailer(); // defaults to using php "mail()"
				$mail->CharSet = 'UTF-8';
				
				// text only
				//$mail->IsHTML(false);
				
				$customer_name = ucfirst(strtolower($firstname)).' '.ucfirst(strtolower($lastname));
				
				$mail->SetFrom($email, $customer_name);

				
				
				$mail->Subject = language('emails', 'CONTACT_FORM_SUBJECT',array(1=>$config_site['site_name']));
				
				$content = nl2br($comments)."<br /><br />";
				
				// custom fields
				if (is_array($custom_fields) && sizeof($custom_fields)) {
					foreach ($custom_fields as $id_custom_fields => $row) {
						$content .= '<strong>'.$row['name'].'</strong>';
						
						switch ($custom_fields[$id_custom_fields]['type']) {
							// single checkbox
							case 0:
								$content .= '<strong>:</strong> '.(!empty($_POST['form_values']['custom_fields'][$id_custom_fields]['value']) ? language('global', 'LABEL_YES'):language('global', 'LABEL_NO'))."<br /><br />";
								break;	
							// multiple checkbox
							case 1:
								$content .= "<ul>";
								foreach ($row['options'] as $id_custom_fields_option => $row_option) {
									$checked = !empty($_POST['form_values']['custom_fields'][$id_custom_fields]['options'][$id_custom_fields_option]['value']) ? 1:0;
									$extra = $_POST['form_values']['custom_fields'][$id_custom_fields]['options'][$id_custom_fields_option]['extra'];
									
									$content .= '<li>'.$row_option['name'].': '.($checked ? language('global', 'LABEL_YES'):language('global', 'LABEL_NO'));
									
									if ($row_option['add_extra'] && !empty($extra)) {
										$content .= ', '.$extra;
									}
									
									$content .= "</li>";
								}
								$content .= "</ul><br />";
								break;
							// dropdown
							case 2:
								
								
								
								
								
								$content .= "<br />";
								
								if (!empty($_POST['form_values']['custom_fields'][$id_custom_fields]['value'])) {
									$id_custom_fields_option = $_POST['form_values']['custom_fields'][$id_custom_fields]['value'];
									
									
									// This is the field Departement to decide to witch email address this comment will go
									if($row['id']==1){
										switch($id_custom_fields_option){
											//Location
											case 1:
												$email_department = 'location@leoharleydavidson.com';
											break;
											//Pieces
											case 2:
												$email_department = 'pieces@leoharleydavidson.com';
											break;
											//Boutique
											case 3:
												$email_department = 'boutique@leoharleydavidson.com';
											break;
											//Moto neuve
											case 4:
												$email_department = 'moto.neuve@leoharleydavidson.com';
											break;
											//Moto usagee
											case 5:
												$email_department = 'david.boileau@leoharleydavidson.com';
											break;
											//Service
											case 6:
												$email_department = 'service@leoharleydavidson.com';
											break;
											//Information generale
											case 7:
												$email_department = 'infos@leoharleydavidson.com';
											break;
										}
									}
									
									
									$extra = $_POST['form_values']['custom_fields'][$id_custom_fields]['extra'];
									
									$content .= $row['options'][$id_custom_fields_option]['name'];
									
									if ($row['options'][$id_custom_fields_option]['add_extra'] && !empty($extra)) {
										$content .= ': '.$extra;		
									}
								}
								
								$content .= "<br /><br />";
								break;
							// textfield
							case 3:
								$content .= "<br />".$_POST['form_values']['custom_fields'][$id_custom_fields]['value']."<br /><br />";
								break;
							// textarea
							case 4:
								$content .= "<br />".nl2br($_POST['form_values']['custom_fields'][$id_custom_fields]['value'])."<br /><br />";
								break;	
							// radio
							case 5:
								$content .= "<br />";
								
								if (!empty($_POST['form_values']['custom_fields'][$id_custom_fields]['value'])) {
									$id_custom_fields_option = $_POST['form_values']['custom_fields'][$id_custom_fields]['value'];
									$extra = $_POST['form_values']['custom_fields'][$id_custom_fields]['options'][$id_custom_fields_option]['extra'];
									
									$content .= $row['options'][$id_custom_fields_option]['name'];
									
									if ($row['options'][$id_custom_fields_option]['add_extra'] && !empty($extra)) {
										$content .= ': '.$extra;		
									}
								}
								
								$content .= "<br /><br />";
								break;
						}
					}
				}
				
				$mail->AddAddress($config_site['company_email'], $config_site['site_name']);

				$mail->MsgHTML(stripslashes($content));

				$sendmail_failed = $mail->Send() ? 0:1;
							
				if (!$sendmail_failed) {
					$_POST['form_values'] = array();
					$success = 1;
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
<?php echo !$indexing ? '<meta name="robots" content="noindex" />':''; ?>

<meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>" />
<meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>" />
<link rel="canonical" href="<?php echo 'http://'.$_SERVER['HTTP_HOST'].$url_prefix.'page/'.($indexing ? $alias:makesafetitle($name).'-'.$id_page); ?>" /> 
<meta property="og:title" content="<?php echo $name;?> - <?php echo $config_site['site_name']; ?>"/>
<meta property="og:url" content="<?php echo 'http://'.$_SERVER['HTTP_HOST'].$url_prefix.'page/'.($indexing ? $alias:makesafetitle($name).'-'.$id_page); ?>"/>
<meta property="og:site_name" content="<?php echo $config_site['site_name']; ?>"/>

<title><?php echo $name;?> - <?php echo $config_site['site_name']; ?></title>
<?php include(dirname(__FILE__) . "/_includes/template/header.php");?>
<?php include(dirname(__FILE__) . "/_includes/template/google_analytics.php");?>
<?php /* <base href="<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/'; ?>" />*/ ?>
<?php
// contact 
if ($id_page == 18) {
?>
<script type="text/javascript">
jQuery(function(){
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
</script>
<?php	
}
?>
</head>
<body class="bv3">
<?php include(dirname(__FILE__) . "/_includes/template/top.php");?>
<div class="main-container">
	<div class="breadcrumbs">   	
		<div class="container">
          <ul>
          	<li class="Home">
            	<a href="/" title="<?php echo language('global', 'BREADCRUMBS_HOME');?>"><?php echo language('global', 'BREADCRUMBS_HOME');?></a>
            </li>
            <?php echo $breadcrumb;?>            
          </ul>
      	</div>            
    </div>
    <div class="main">
     <div class="container">
      <div class="main-content col-sm-9">
      	<div class="page-title">
        	<h1 style="margin:0 auto;"><?php echo $name;?></h1>
        </div>
        <?php 
          $r_msg = '';
          $r_class = '';
          if($success) {
            $r_msg = language('contact-form', 'SUCCESS_REGISTRATION');
            $r_class = 'alert-success';
          }
          if(sizeof($errors)) {
            $r_msg = language('global', 'ERROR_OCCURED');
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
        <?php
		echo $description;?>        
        <?php
        //Contact Form
		if($id_page == 18){?>
		
        <form method="post" enctype="multipart/form-data">     
                    <div style="width:98%;">                    
                        <div class="title_bg title_bg_1"><h2><?php echo language('contact-form', 'TITLE_COMMENTS_FORM');?></h2></div>
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
                            <div style="margin-bottom:5px;">
                            <strong><?php echo language('contact-form', 'LABEL_EMAIL_ADDRESS');?> *</strong><br />
                            <input type="text" name="form_values[email]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['email']); ?>" <?php echo $errors['email'] ? 'class="error"':''; ?> />      
                            <br /><span class="error"><?php echo $errors['email']; ?></span>                                                        
                            </div>
                            <div style="margin-bottom:5px;">
                            <strong><?php echo language('contact-form', 'LABEL_CONFIRM_EMAIL_ADDRESS');?> *</strong><br />
                            <input type="text" name="form_values[cemail]" style="width:100%;" value="<?php echo htmlspecialchars($_POST['form_values']['cemail']); ?>" <?php echo $errors['cemail'] ? 'class="error"':''; ?> />        
                            <br /><span class="error"><?php echo $errors['cemail']; ?></span>                                                
                            </div> 
                            
							<?php					
                            // get list of custom fields					
                            if (isset($custom_fields) && is_array($custom_fields) && sizeof($custom_fields)) {
								foreach ($custom_fields as $row) {	
								?>
									<div style="margin-bottom:5px;">
										<strong><?php echo $row['name'].($row['required'] ? ' *':''); ?> </strong>
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
													$selected = $_POST['form_values']['custom_fields'][$row['id']]['value'] ? $_POST['form_values']['custom_fields'][$row['id']]['value']:($row['options']['selected'] ? $row['id']:0);
																																		
													echo '<div style="padding:10px; margin-bottom:10px;">';													
							
													// loop through		
													foreach ($row['options'] as $row_option) {
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
                            }
                            ?>                            
                            
                            <div style="margin-bottom:5px;">
                            <strong><?php echo language('contact-form', 'LABEL_COMMENTS');?> *</strong><br />
                            <textarea name="form_values[comments]" style="width:100%; height:100px"><?php echo htmlspecialchars($_POST['form_values']['comments']); ?></textarea>
                            <br /><span class="error"><?php echo $errors['comments']; ?></span>                                                        
                            </div>
                      
 
                        <div class="buttons-set">
                            <p class="required">* Required Fields</p>
                            <input type="text" name="hideit" id="hideit" value="" style="display:none !important;">
                            <button name="registration_btn" type="submit" title="<?php echo language('contact-form', 'BTN_REGISTRATION');?>" class="button"><span><span><?php echo language('contact-form', 'BTN_REGISTRATION');?></span></span></button>
                        </div>                                                        
                    </div>    
                    </div>
               
                </form>
		
		
			
		<?php
        }
		?>
        
        </div>
        <div class="col-sm-3 sidebar sidebar-right">
			<?php if ($config_site['show_newsletter_form']) { ?>
    		<!-- Block newsletter -->
    		<div class="block block-subscribe">
              <div class="block-title">
                  <strong><span><?php echo language('index', 'LABEL_NL_BLOCK_TITLE');?></span></strong>
              </div>
              <form id="newsletter-validate-detail" method="post" action="" onsubmit="return false">
                  <div class="alert alert-success success-msg" style="display:none;" id="nl_email_text_container">
                  	<button type="button" class="close" data-dismiss="alert">×</button>
                  	<div id="newsletter_email_text"></div>
                  </div>
                  <div class="block-content">
                      <div class="form-subscribe-header">
                          <label for="newsletter"><?php echo language('index', 'LABEL_NL_BLOCK_TEXT');?></label>
                      </div>
                      <div class="input-box">
                         <input type="text" placeholder="<?php echo language('index', 'LABEL_NL_EMAIL_PLACEHOLDER');?>" class="input-text required-entry validate-email" title="<?php echo language('index', 'LABEL_NL_EMAIL_TITLE');?>" id="newsletter" name="form_values[email]" style="text-align:center;">
                      </div>
                      <div class="actions">
                          <button class="button button-inverse btn-lg btn-large" title="<?php echo language('index', 'LABEL_NL_SUBMIT');?>" type="submit" onclick="register_newsletter()"><span><span><?php echo language('index', 'LABEL_NL_SUBMIT');?></span></span></button>
                      </div>
                  </div>
              </form>                   
          	</div>          	
          	<!-- END Block newsletter -->          	
          	<?php }?>
          	<div class="block block-pub">
          	<?php 
            	$page_home = false;
            	include("_includes/template/pub.php");
          	?>
          	</div>
    	</div>        
    </div>
</div>
</div>
<?php include(dirname(__FILE__) . "/_includes/template/bottom.php");?>
</body>
</html>