<?php
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");

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
//echo '<pre>'.print_r($custom_fields,1).'</pre>';
switch ($_SERVER['REQUEST_METHOD']) {

	case 'POST':
		if (isset($_POST['modify_account'])) {	
		//echo '<pre>'.print_r($_POST['form_values'],1).'</pre>';	
			// validation rules
			$validation = array();	
			
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
				
			if (!sizeof($errors = validate_fields($_POST['form_values'], $validation))) {				
				$mysqli->query('DELETE FROM customer_custom_fields_value WHERE id_customer = "'.$_SESSION['customer']['id'].'"');
				
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
								
								if (!$stmt_add_custom_fields_value->bind_param("iiis", $_SESSION['customer']['id'], $id_custom_fields, $id_custom_fields_option, $value)) throw new Exception('An error occured while trying to bind params to add custom fields value statement.'."\r\n\r\n".$mysqli->error);			
								
								/* Execute the statement */
								if (!$stmt_add_custom_fields_value->execute()) throw new Exception('An error occured while trying to add custom fields value statement.'."\r\n\r\n".$mysqli->error);																					
							}								
						} else {
							switch ($custom_fields[$id_custom_fields]['type']) {
								// single checkbox
								case 0:	
									$id_custom_fields_option = $id_custom_fields;
									$value = $id_custom_fields;
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
								// radio button
								case 5:
									$id_custom_fields_option = $row_custom_field['value'];
									$value = $row_custom_field['extra'] ? $row_custom_field['extra']:'';
									break;
							}					
												
							if (!$stmt_add_custom_fields_value->bind_param("iiis", $_SESSION['customer']['id'], $id_custom_fields, $id_custom_fields_option, $value)) throw new Exception('An error occured while trying to bind params to add custom fields value statement.'."\r\n\r\n".$mysqli->error);			
							
							/* Execute the statement */
							if (!$stmt_add_custom_fields_value->execute()) throw new Exception('An error occured while trying to add custom fields value statement.'."\r\n\r\n".$mysqli->error);										
						}
					}
					
					$stmt_add_custom_fields_value->close();
				}				

				header('Location: /account?success=modify_account');
				exit;
			}
		}
		break;
	default:
	
		if (!$result = $mysqli->query('SELECT 
		*
		FROM				
		customer_custom_fields_value
		WHERE
		id_customer = "'.$_SESSION['customer']['id'].'"')) throw new Exception('An error occured while trying to get infos.'."\r\n\r\n".$mysqli->error);	
		
		while ($row = $result->fetch_assoc()) {
			if ($row['id_custom_fields_option'] && ($row['id_custom_fields_option']!=$row['value'])) {
				//echo $row['id_custom_fields_option'] . ' - ici';
				switch ($custom_fields[$row['id_custom_fields']]['type']) {
					default:
						$_POST['form_values']['custom_fields'][$row['id_custom_fields']]['options'][$row['id_custom_fields_option']] = array(
							'value' => $row['id_custom_fields_option'],
							'extra' => $row['value'],
						);
						break;
					case 2:
						$_POST['form_values']['custom_fields'][$row['id_custom_fields']] = array(
							'value' => $row['id_custom_fields_option'],
							'extra' => $row['value'],
						);					
						break;
					case 5:
						$_POST['form_values']['custom_fields'][$row['id_custom_fields']] = array(
							'value' => $row['id_custom_fields_option'],
							'extra' => $row['value'],
							'options' => array(
								$row['id_custom_fields_option'] => array(
									'extra' => $row['value'])),
						);					
						break;
						
						//$_POST['form_values']['custom_fields'][$row['id']]['options'][$row_option['id']]['extra']
				}				
			} else {
				$_POST['form_values']['custom_fields'][$row['id_custom_fields']]['value'] = $row['value'];
			}
		}
		break;
}
//echo '<pre>'.print_r($_POST['form_values'],1).'</pre>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow" />
<title><?php echo $config_site['site_name'];?></title>
<?php include("../_includes/template/header.php");?>
<script type="text/javascript">
$(function(){
	$("body").on("change",".custom_fields_dropdown",function(){
		var id = $(this).prop("id");
		var i = id.replace("custom_fields_dropdown_","");
		var selected = $(":selected",this);
		var extra = $("#custom_fields_dropdown_extra_"+i);
		
		if (selected.length && selected.prop("class") == "add_extra" && extra.length) extra.prop("disabled",false).show();			
		else extra.val("").hide().prop("disabled",true);
	});
	
	$("body").on("click",".custom_fields_checkbox",function(){
		var id = $(this).prop("id");
		var i = id.replace("custom_fields_checkbox_","");
		var extra = $("#custom_fields_checkbox_extra_"+i);
		
		if ($(this).prop("checked") && extra.length) extra.prop("disabled",false);
		else extra.val("").prop("disabled",true);
	});
	
	$("body").on("click",".custom_fields_radio",function(){
		var id = $(this).prop("id");
		var i = id.replace("custom_fields_radio_","");
		var extra = $("#custom_fields_radio_extra_"+i);
		var ids = i.split("_");
		
		// disable extras
		$("input[name^='form_values[custom_fields]["+ids[0]+"][options]']:input").prop("disabled",true);		
		$("input[name^='form_values[custom_fields]["+ids[0]+"][options]']:input").val("");

		if (extra.length) extra.prop("disabled",false);
	});	

});
</script>
</head>
<body>
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
            	<strong><?php echo language('global', 'BREADCRUMBS_MODIFY_ADDITIONAL_INFO');?></strong>
            </li>
          </ul>
      	</div>            
    </div>
    <div class="main">
    <div class="container">
    <div class="main-content withblock" style="overflow: hidden;">   
        <form method="post">                   
            <h2 class="subtitle"><?php echo language('account/modify-additional-info', 'TITLE_ADDITIONAL_INFORMATION');?></h2>
            <div class="title_bg_text_box"> 
             <?php if(sizeof($errors)) {?>
            <div class="messages">
              <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <ul><li><span><?php echo language('global', 'ERROR_OCCURED');?></span></li></ul>
              </div>
            </div>
            <?php }?>
          <?php			
			// get list of custom fields					
			if (isset($custom_fields) && is_array($custom_fields) && sizeof($custom_fields)) {
				//echo '<pre>'.print_r($custom_fields,1).'</pre>';
				foreach ($custom_fields as $row) {	
				?>
					<div style="margin-bottom:5px;">
						<strong><?php echo $row['name'].($row['required'] ? ' *':''); ?> </strong>
						<?php
						switch ($row['type']) {
										// single checkbox
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
											echo '<div style="margin-bottom:10px;"><input type="text" name="form_values[custom_fields]['.$row['id'].'][value]" style="width:50%;" value="'.htmlspecialchars($_POST['form_values']['custom_fields'][$row['id']]['value']).'" '.($errors['custom_fields_'.$row['id']] ? 'class="error"':'').' /></div>';
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
			}		
			?>
                
                
                <div style="margin-top:10px;">
                        <div class="button_regular"><input type="submit" value="<?php echo language('global', 'BTN_SAVE');?>" class="regular button" name="modify_account" /></div>
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