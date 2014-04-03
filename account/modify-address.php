<?php
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");

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
			if(isset($_GET['error'])){
				$error = $_GET['error'];
			}else{
				$error = '';
			}
		break;
	case 'POST':
		
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
<script>
<!--
jQuery(function(){
	jQuery("#country_code_billing").change(function(){
		get_state_list('','billing')
	});	
	jQuery("#country_code_shipping").change(function(){
		get_state_list('','shipping')
	});

});

function get_state_list(selected,address_type) {

	var country_code = jQuery("#country_code_"+address_type).val();	
	if (!country_code) { 
		alert("Please select a country."); 
		return false;
	} else {
		jQuery.ajax({
			url: "<?php echo $_SERVER['PHP_SELF']; ?>",
			data: { "task":"get_state_list","country_code":country_code },
			dataType: "json",
			error: function(jqXHR, textStatus, errorThrown) { 
				alert(jqXHR.responseText);
			},
			success: function( data ) {
				jQuery("#state_code_"+address_type).html("").append('<option value="">-- <?php echo language('global', 'SELECT_STATE_PROVINCE');?> --</option>');
				
				if (data) {
					jQuery.each(data,function(key, value){
							
						if(selected == key){
							check = 'selected="selected"';
						}else{
							check = '';	
						}
						
						jQuery("#state_code_"+address_type).append('<option value="'+key+'" '+check+'>'+value+'</option>');
					});
				}
			}
		});
	}
}

function edit_address (form,div_id,id,address_type){
	jQuery("#"+form+" .error").removeClass("error"); 
	jQuery.ajax({
		url: "/_includes/ajax/shipping_billing?task=edit&page=1",
		type: "POST",
		dataType: "json",
		data: { "id":id },
		success: function(data) {
			switch (data) {					
				case 'false':
					alert('<?php echo language('global', 'ERROR_OCCURED');?>');
					break;					
				default:
					jQuery("#"+form+" input[name='form_values[id]']").val(data.id);
					jQuery("#"+form+" select[name='form_values[use_in]']").val(data.use_in);
					jQuery("#"+form+" input[name='form_values[firstname]']").val(data.firstname);
					jQuery("#"+form+" input[name='form_values[lastname]']").val(data.lastname);
					jQuery("#"+form+" input[name='form_values[company]']").val(data.company);
					jQuery("#"+form+" input[name='form_values[address]']").val(data.address);
					jQuery("#"+form+" input[name='form_values[city]']").val(data.city);
					jQuery("#"+form+" select[name='form_values[country_code]']").val(data.country_code);
					get_state_list(data.state_code,address_type);
					jQuery("#"+form+" input[name='form_values[zip]']").val(data.zip);
					jQuery("#"+form+" input[name='form_values[telephone]']").val(data.telephone);
					
					
					if(form == "form_billing"){
						jQuery("#"+form+" select[name='form_values[default_billing]']").val(data.default_billing);
						if(data.default_billing==1){
							jQuery("#display_default_billing").hide();	
						}else{
							jQuery("#display_default_billing").show();	
						}
					}else if(form == "form_shipping"){
						jQuery("#"+form+" select[name='form_values[default_shipping]']").val(data.default_shipping);
						if(data.default_shipping==1){
							jQuery("#display_default_shipping").hide();	
						}else{
							jQuery("#display_default_shipping").show();		
						}
					}

					jQuery('#'+div_id).slideDown(500,'linear');
					
					break;
			}								
		},
		error: function(e, xhr, settings, exception) {
			alert('<?php echo language('global', 'ERROR_OCCURED');?>');	
		}
	});			
}

function save_address (form,div_id,address_type){
	jQuery("#"+form+" .error").removeClass("error"); 
	jQuery.ajax({
		url: "/_includes/ajax/shipping_billing?task=save&page=1",
		type: "POST",
		dataType: "json",
		data: jQuery("#"+form).serialize(),
		success: function(data) {
			switch (data.rep) {					
				case "false":
					alert('<?php echo language('global', 'ERROR_OCCURED');?>');
					
					jQuery.each(data,function(key, value){
						if(key != 'rep'){
							jQuery("#"+key+"_"+address_type).addClass("error");
						}
					});
					break;					
				default:
					if(data.refresh_page){
						location.reload();
					}else{
						jQuery('#'+div_id).slideUp(500,'linear');
						jQuery("#"+form+"_list").html('').append(data.list1);
						if(data.list2!=''){
							if(form == 'form_billing'){
								jQuery("#form_shipping_list").html('').append(data.list2);
							}else{
								jQuery("#form_billing_list").html('').append(data.list2);
							}
						}
						clear_form(form);
						//Clear Hidden field: form_values[id]
						jQuery("#"+form+" input[name='form_values[id]']").val();
					}
					break;
			}								
		},
		error: function(e, xhr, settings, exception) {
			alert('<?php echo language('global', 'ERROR_OCCURED');?>');	
		}
	});			
}

function delete_address (form,id,address_type,use_in){
	if (confirm("<?php echo language('cart/step_shipping', 'CONFIRM_DELETE');?>")) {		
		jQuery.ajax({
			url: "/_includes/ajax/shipping_billing?task=delete&page=1",
			type: "POST",
			dataType: "json",
			data: { "id":id,"address_type":address_type,"use_in":use_in,"shipping_gateway":"" },
			success: function(data) {
				switch (data) {					
					case 'false':
						alert('<?php echo language('global', 'ERROR_OCCURED');?>');
						break;					
					default:
						if(data.refresh_page){
							location.reload();
						}else{
							jQuery("#"+form+"_list").html('').append(data.list1);
							if(data.list2!=''){
								if(form == 'form_billing'){
									jQuery("#form_shipping_list").html('').append(data.list2);
								}else{
									jQuery("#form_billing_list").html('').append(data.list2);
								}
							}
						}
						break;
				}								
			},
			error: function(e, xhr, settings, exception) {
				alert('<?php echo language('global', 'ERROR_OCCURED');?>');	
			}
		});	
	}
}

function clear_btn (form,div_id){
	jQuery("#"+form+" .error").removeClass("error"); 
	clear_form(form);
	jQuery('#'+form+' input[name="form_values[id]"]').val("");
	jQuery('#'+div_id).slideUp(500,'linear');
}
function add_new_btn (form,div_id){
	if (jQuery("#display_error").css("display") == "block"){
		jQuery("#display_error").hide("blind", { direction: "vertical" }, 1000);
	}
	
	jQuery("#"+form+" .error").removeClass("error"); 
	clear_form(form);
	jQuery('#'+form+' input[name="form_values[id]"]').val("");
	jQuery("#display_default_shipping").show();	
	jQuery("#display_default_billing").show();
	jQuery('#'+div_id).slideDown(500,'linear');		
}
-->
</script>
<style>
.withblock .op_block_detail .button {
	margin-right:0 !important;
}
</style>
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
            	<strong><?php echo language('global', 'BREADCRUMBS_MODIFY_ADDRESS');?></strong>
            </li>
          </ul>
      	</div>            
    </div>	
	<div class="main">
    <div class="container">
    <h2 class="subtitle"><?php echo language('account/modify-address', 'TITLE_MANAGE_ADDRESS');?></h2>
    <div class="main-content withblock" style="overflow: hidden; margin-top:30px;">	

                    <?php 
					switch ($error) {
						case 'default_address':
							echo '<div class="error" style="margin-top:0" id="display_error">'.language('account/modify-address', 'ERROR_DEFAULT_ADDRESS_MISSING').'</div>';
							break;
						
					}?>

               <div id="billing" class="<?php echo $config_site['enable_shipping']?'col-sm-6 iwd-1"':'';?>;">
                <div class="title_bg title_bg_3">
                	<div class="op_block_title">
                  	  <?php echo language('global', 'TITLE_BILLING_ADDRESS');?>
                      <div style="float:right;margin-right:5px;"><input type="button" value="<?php echo language('global', 'BTN_ADD_NEW');?>" class="button" name="btn_add_billing_address" onclick="add_new_btn('form_billing','display_form_billing')" /></div>
                    </div>
                </div>
                <div class="title_bg_text_box op_block_detail">
                    <div id="display_form_billing">
                        <form id="form_billing">
                            <input type="hidden" name="form_values[id]" value="">
                            <input type="hidden" name="form_values[address_type]" value="billing">
                             <div style="margin-bottom:5px;" id="display_default_billing">
                                <strong>Défault </strong><br />
                                <select name="form_values[default_billing]" style="width:80px;">
                                    <option value="1"><?php echo language('global', 'TITLE_YES');?></option>
                                    <option value="0"><?php echo language('global', 'TITLE_NO');?></option>					
                                </select>                                  
                            </div>
                            <?php if($config_site['enable_shipping']){?>     
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_USE_FOR');?></strong><br />
                                 <select name="form_values[use_in]" style="width:98%;" id="use_in_billing">
                                    <option value="1"><?php echo language('global', 'ADDRESS_BILLING_ONLY');?></option>
                                    <option value="0"><?php echo language('global', 'ADDRESS_BILLING_SHIPPING');?></option>					
                                </select>                            
                            </div>
                            <?php
							}else{
								echo '<input type="hidden" name="form_values[use_in]" value="0">';	
							}
							?>
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_FIRST_NAME');?> *</strong><br />
                                <input type="text" name="form_values[firstname]" style="width:98%;" value="" id="firstname_billing"/>
                            </div>                            
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_LAST_NAME');?> *</strong><br />
                                <input type="text" name="form_values[lastname]" style="width:98%;" value="" id="lastname_billing" />     
                            </div>                                  
                            <div style="margin-bottom:5px;" class="hide_field_form_company">
                                <strong><?php echo language('global', 'ADDRESS_COMPANY');?></strong><br />
                                <input type="text" name="form_values[company]" style="width:98%;" value="" id="company_billing" />   
                            </div>                                                 
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_ADDRESS');?> *</strong><br />
                                <input type="text" name="form_values[address]" style="width:98%;" value="" id="address_billing" />
                            </div>     
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_CITY');?> *</strong><br />
                                <input type="text" name="form_values[city]" style="width:98%;" value="" id="city_billing" />
                            </div>
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_COUNTRY');?> *</strong><br />
                                <select name="form_values[country_code]" id="country_code_billing" style="width:98%;">
                                    <option value="">-- <?php echo language('global', 'SELECT_COUNTRY');?> --</option>
                                    <?php
                                    if (sizeof($countries = get_country_list())) {
                                        foreach ($countries as $country_code => $name) {
                                            echo '<option value="'.$country_code.'" '.($_POST['form_values']['country_code'] == $country_code ? 'selected="selected"':'').'>'.$name.'</option>';
                                        }
                                    }
                                    ?>							
                                </select>                                                        
                            </div>        
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_STATE');?> *</strong><br />
                                <select name="form_values[state_code]" id="state_code_billing" style="width:98%;">
                                <option value="">-- <?php echo language('global', 'SELECT_STATE_PROVINCE');?> --</option>
                                <?php
                                    if ($_POST['form_values']['country_code'] && sizeof($states = get_state_list($_POST['form_values']['country_code']))) {
                                        foreach ($states as $state_code => $name) {
                                            echo '<option value="'.$state_code.'" '.($_POST['form_values']['state_code'] == $state_code ? 'selected="selected"':'').'>'.$name.'</option>';
                                        }
                                    }
                                ?>
                                </select>
                            </div>   
                                                 
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_ZIP');?> *</strong><br />
                                <input type="text" name="form_values[zip]" style="width:98%;" value="" id="zip_billing" />
                            </div>     
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_TELEPHONE');?> *</strong><br />
                                <input type="text" name="form_values[telephone]" style="width:98%;" value="" id="telephone_billing" /> 
                            </div>                  
                            <div style="margin-top:10px; margin-bottom: 10px">
                                <div style="float:left">
                                	<input type="button" value="<?php echo language('global', 'BTN_SAVE');?>" class="button button-inverse" name="btn_save_billing" onclick="save_address('form_billing','display_form_billing','billing');" />
                                </div>
                                <div style="float:right">
                                    <input type="button" value="<?php echo language('global', 'BTN_CANCEL');?>" class="button" name="btn_cancel_billing" onclick="clear_btn('form_billing','display_form_billing')" />
                                </div>
                               	<div class="cb"></div>
                            </div>
                         </form>
                   </div>
                   <div id="form_billing_list">
			   			<?php echo list_address('billing',1);?>
                   </div>
                </div>  
            </div>
            
            <?php 
			if($config_site['enable_shipping']){
			?>
            <div id="shipping"  class="col-sm-6 iwd-1">
            	<div class="title_bg title_bg_3">
                	<div class="op_block_title">
                  	  <?php echo language('global', 'TITLE_SHIPPING_ADDRESS');?>
                      <div style="float:right;"><?php echo($config_site['enable_shipping']?'<input type="button" value="'.language('global', 'BTN_ADD_NEW').'" class="button" name="btn_add_billing_address" onclick="add_new_btn(\'form_shipping\',\'display_form_shipping\')" />':'');?></div>
                    </div>
                </div>
                <div class="title_bg_text_box op_block_detail">
               		<div id="display_form_shipping">
                        <form id="form_shipping">
                            <input type="hidden" name="form_values[id]" value="">
                            <input type="hidden" name="form_values[address_type]" value="shipping">
                            <div style="margin-bottom:5px;" id="display_default_shipping">
                                <strong>Défault </strong><br />
                                <select name="form_values[default_shipping]" style="width:80px;">
                                    <option value="1"><?php echo language('global', 'TITLE_YES');?></option>
                                    <option value="0"><?php echo language('global', 'TITLE_NO');?></option>					
                                </select>                                  
                            </div> 
                              
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_USE_FOR');?></strong><br />
                                 <select name="form_values[use_in]" style="width:98%;" id="use_in_shipping">
                                    <option value="2"><?php echo language('global', 'ADDRESS_SHIPPING_ONLY');?></option>
                                    <option value="0"><?php echo language('global', 'ADDRESS_BILLING_SHIPPING');?></option>                            					
                                </select> 
                            </div>
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_FIRST_NAME');?> *</strong><br />
                                <input type="text" name="form_values[firstname]" style="width:98%;" value="" id="firstname_shipping" />  
                            </div>
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_LAST_NAME');?> *</strong><br />
                                <input type="text" name="form_values[lastname]" style="width:98%;" value="" id="lastname_shipping" />     
                            </div>                                  
                                <div style="margin-bottom:5px;" class="hide_field_form_company">
                                <strong><?php echo language('global', 'ADDRESS_COMPANY');?></strong><br />
                                <input type="text" name="form_values[company]" style="width:98%;" value="" id="company_shipping" />   
                            </div>                                                 
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_ADDRESS');?> *</strong><br />
                                <input type="text" name="form_values[address]" style="width:98%;" value="" id="address_shipping" />  
                            </div>     
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_CITY');?> *</strong><br />
                                <input type="text" name="form_values[city]" style="width:98%;" value="" id="city_shipping" />        
                            </div>   
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_COUNTRY');?> *</strong><br />
                                <select name="form_values[country_code]" id="country_code_shipping" style="width:98%;">
                                    <option value="">-- <?php echo language('global', 'SELECT_COUNTRY');?> --</option>
                                    <?php
                                    if (sizeof($countries = get_country_list())) {
                                        foreach ($countries as $country_code => $name) {
                                            echo '<option value="'.$country_code.'" '.($_POST['form_values']['country_code'] == $country_code ? 'selected="selected"':'').'>'.$name.'</option>';
                                        }
                                    }
                                    ?>							
                                </select>             
                            </div>        
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_STATE');?> *</strong><br />
                                <select name="form_values[state_code]" id="state_code_shipping" style="width:98%;">
                                <option value="">-- <?php echo language('global', 'SELECT_STATE_PROVINCE');?> --</option>
                                <?php
                                    if ($_POST['form_values']['country_code'] && sizeof($states = get_state_list($_POST['form_values']['country_code']))) {
                                        foreach ($states as $state_code => $name) {
                                            echo '<option value="'.$state_code.'" '.($_POST['form_values']['state_code'] == $state_code ? 'selected="selected"':'').'>'.$name.'</option>';
                                        }
                                    }
                                ?>
                                </select>
                            </div>   
                                                
                            <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_ZIP');?> *</strong><br />
                                <input type="text" name="form_values[zip]" style="width:98%;" value="" id="zip_shipping" /> 
                            </div>
                             <div style="margin-bottom:5px;">
                                <strong><?php echo language('global', 'ADDRESS_TELEPHONE');?> *</strong><br />
                                <input type="text" name="form_values[telephone]" style="width:98%;" value="" id="telephone_shipping" /> 
                            </div>
                            
                                                      
                            <div style="margin-top:10px; margin-bottom: 10px">
                                <div style="float:left">
                                	<input type="button" value="<?php echo language('global', 'BTN_SAVE');?>" class="button button-inverse" name="create_account" onclick="save_address('form_shipping','display_form_shipping','shipping');" />
                                </div>
                                <div style="float:right">
                                	<input type="button" value="<?php echo language('global', 'BTN_CANCEL');?>" class="button" name="btn_cancel_shipping" onclick="clear_btn('form_shipping','display_form_shipping')" />
                                </div>
                                <div class="cb"></div>
                            </div>
                            </form>
               		</div>
                   	<div id="form_shipping_list">
                   		<?php echo list_address('shipping',1);?>
                   	</div>
                </div>
            </div>
            <?php }?>       
                <p>&nbsp;</p>        
                
	</div>
	</div>
	</div>
	       
</div>

<?php include("../_includes/template/bottom.php");?>
</body>
</html>