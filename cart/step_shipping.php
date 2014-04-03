<?php 
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");

if(count($cart->get_products()) == 0) {
  header("Location: /cart");
  exit;	
}

//Payment Gateway
include(dirname(__FILE__) . "/../_includes/classes/shipping/current_shipping_gateway.php");

$free_shipping = $cart->get_free_shipping_yes_no($cart->shipping_country_code,$cart->shipping_state_code);

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		$task = trim($_GET['task']);
		$error = trim($_GET['error']);
		switch ($task) {
			case 'get_state_list':
				$country_code = trim($_GET['country_code']);
				echo json_encode(get_state_list($country_code));
				exit;
				break;
			case 'change_shipping_address':
				if(isset($_GET['shipping_address']) and is_numeric($_GET['shipping_address'])){
					$shipping_address = trim($_GET['shipping_address']);
					// Load the price for the customer default shipping address
					if($shipping_address and $shipping_address > 0){
						$query = 'SELECT * FROM customer_address WHERE id_customer = "'.$_SESSION['customer']['id'].'" AND id = "'.$shipping_address.'"';
						if ($result = $mysqli->query($query)) {
							if($result->num_rows){
								$row = $result->fetch_assoc();
								
								//Find the tax rule
								$id_tax_rule = $cart->get_id_tax_rule($row['country_code'],$row['state_code'],$row['zip']);

								// Verify if free shipping is enable and if it's applicable
								$free_shipping = $cart->get_free_shipping_yes_no($row['country_code'],$row['state_code']);

								// Update Table Cart
								if (!$mysqli->query('UPDATE 
										cart
										SET
										shipping_id = "'.$mysqli->escape_string($row['id']).'",
										shipping_firstname = "'.$mysqli->escape_string($row['firstname']).'",
										shipping_lastname = "'.$mysqli->escape_string($row['lastname']).'",
										shipping_company = "'.$mysqli->escape_string($row['company']).'",
										shipping_address = "'.$mysqli->escape_string($row['address']).'",
										shipping_city = "'.$mysqli->escape_string($row['city']).'",
										shipping_country_code = "'.$mysqli->escape_string($row['country_code']).'",
										shipping_state_code = "'.$mysqli->escape_string($row['state_code']).'",
										shipping_zip = "'.$mysqli->escape_string($row['zip']).'",
										shipping_telephone = "'.$mysqli->escape_string($row['telephone']).'",
										shipping_fax = "'.$mysqli->escape_string($row['fax']).'",
										local_pickup = 0,
										local_pickup_id = 0,
										local_pickup_address = "",
										local_pickup_city = "",
										local_pickup_country_code = "",
										local_pickup_state_code = "",
										local_pickup_zip = "",
										id_tax_rule = "'.$mysqli->escape_string($id_tax_rule).'",
										shipping_gateway_company = "",
										shipping_service = "",
										shipping = 0,
										shipping_estimated = "",
										shipping_validated = 0
										WHERE
										id = "'.$mysqli->escape_string($cart->id).'"
										LIMIT 1')){
									throw new Exception('An error occured while trying to update the cart.'."\r\n\r\n".$mysqli->mysqli->error);		
								}
								header('Location: step_shipping');
								exit;
							}
						}
					}else if($shipping_address == -1){
						
						//Find the tax rule
						$id_tax_rule = $cart->get_id_tax_rule($config_site['company_country_code'],$config_site['company_state_code']);
						
						if (!$mysqli->query('UPDATE 
								cart
								SET
								local_pickup = 1,
								id_tax_rule = "'.$mysqli->escape_string($id_tax_rule).'",
								shipping_id = 0,
								shipping_firstname = "",
								shipping_lastname = "",
								shipping_company = "",
								shipping_address = "",
								shipping_city = "",
								shipping_country_code = "",
								shipping_state_code = "",
								shipping_zip = "",
								shipping_telephone = "",
								shipping_fax = "",
								shipping_gateway_company = "",
								shipping_service = "",
								shipping = 0,
								shipping_estimated = "",
								free_shipping = 0,
								shipping_validated = 0
								WHERE
								id = '.$cart->id.'
								LIMIT 1')){
							throw new Exception('An error occured while trying to update cart.'."\r\n\r\n".$mysqli->mysqli->error);		
						}
						
						header('Location: step_shipping');
						exit;
					}
				}
				break;
			default:
				// Update Table Cart
				if (!$mysqli->query('UPDATE 
						cart
						SET
						shipping_gateway_company = "",
						shipping_service = "",
						shipping = 0,
						shipping_estimated = "",
						free_shipping = 0,
						shipping_validated = 0
						WHERE
						id = "'.$mysqli->escape_string($cart->id).'"
						LIMIT 1')){
					throw new Exception('An error occured while trying to update cart.'."\r\n\r\n".$mysqli->mysqli->error);	
					//$cart->init();	
				}
				break;
		}		
		break;
	case 'POST':
		if (isset($_POST['btn_next_step'])) {
			//echo '<pre>'.print_r($_POST,1).'</pre>';
			if(isset($_POST['shipping_method'])){
				$shipping_method = $_POST['shipping_method'];
				$shipping_choice = $_POST['shipping_choice'];		
				$local_pickup = $_POST['local_pickup'];
				$local_pickup_choice = $_POST['local_pickup_choice'];
				$query = 'SELECT 
				*	
				FROM 
				config_address_pickup
				LIMIT 1';
				if ($result = $mysqli->query($query)) {
					$total_local_pickup = $result->num_rows;
				}
						
				
				if(($shipping_method == 1 && $shipping_choice=='') || ($shipping_method == 0 && $local_pickup && !$local_pickup_choice && $total_local_pickup)){
					if($local_pickup){
						$error = 'local_pickup_service';
					}else{
						$error = 'shipping_service';
					}
					
				}else{
					// free shipping
					if (!$shipping_method) {
						if (!$mysqli->query('UPDATE 
						cart
						SET
						free_shipping = 1
						WHERE
						id = "'.$mysqli->escape_string($cart->id).'" 
						LIMIT 1')) throw new Exception('An error occured while trying to update cart free shipping.'."\r\n\r\n".$mysqli->error);	
					}
					
					// update billing
					if ($billing_address = (int)$_POST['billing_address']) {					
						if (!$mysqli->query('UPDATE 
						cart
						INNER JOIN
						customer_address
						ON
						(customer_address.id = "'.$mysqli->escape_string($billing_address).'")						
						SET
						cart.billing_id = customer_address.id,
						cart.billing_firstname = customer_address.firstname,
						cart.billing_lastname = customer_address.lastname,
						cart.billing_company = customer_address.company,
						cart.billing_address = customer_address.address,
						cart.billing_city = customer_address.city,
						cart.billing_country_code = customer_address.country_code,
						cart.billing_state_code = customer_address.state_code,
						cart.billing_zip = customer_address.zip,
						cart.billing_telephone = customer_address.telephone,
						cart.billing_fax = customer_address.fax,
						cart.shipping_validated = 1
						WHERE
						cart.id = "'.$mysqli->escape_string($cart->id).'"')) throw new Exception('An error occured while trying to update cart billing address.'."\r\n\r\n".$mysqli->mysqli->error);		
					
						header('Location: step_validation');
						exit;						
					} else {
						$error = 'billing_address';	
					}
				}
			}else{
				header('Location: step_validation');
				exit;	
			}
		}
		break;
}


// used for fixed shipping price
$subtract_from_subtotal = 0;
$heavy_weight = 0;
if(sizeof($cart_products = $cart->get_products())){
	$shipping_price_total = 0;
	$array_product_cannot_be_ship = array();	
	
	foreach ($cart_products as $row_product) { 
		if(sizeof($row_product['sub_products'])){
			foreach ($row_product['sub_products'] as $row_sub_product) {
				if ($row_sub_product['heavy_weight']) $heavy_weight = 1;
				else {
					// Verify if this product can be ship into the shipping region
					if(!$cart->get_not_shipping_in_region_yes_no($row_sub_product['id_product'], 0, $cart->shipping_country_code, $cart->shipping_state_code)){
						if($row_sub_product['shipping_price']==0 and $row_sub_product['weight']>0 and !$row_sub_product['use_shipping_price']){
							$products[] = array(
								'name' => $row_sub_product['name'],	
								'qty' => $row_sub_product['qty'],
								'weight' => convert_lb_to_kg($convert_yes_no,$row_sub_product['weight']),
								'weight_unit_symbol' => $unit_weight,
								'length' => convert_in_to_cm($convert_yes_no,$row_sub_product['length']),
								'width' => convert_in_to_cm($convert_yes_no,$row_sub_product['width']),
								'height' => convert_in_to_cm($convert_yes_no,$row_sub_product['height']),
								'measurement_unit_symbol' => $unit_measurement,
								'extra_care' => $row_sub_product['extra_care'],
							);
						}else if ($row_sub_product['use_shipping_price']){
							// Total of shipping price by product
							$shipping_price_total += ($row_sub_product['shipping_price']*$row_sub_product['qty']);
							
							// remove product amount from subtotal
							$product_price = $row_sub_product['subtotal'];
							
							foreach ($cart->get_product_discounts($row_sub_product['id']) as $discount) $product_price -= $discount['amount']; 			
							$subtract_from_subtotal += $product_price;
						}
					}else{
						$array_product_cannot_be_ship[] = $row_sub_product['name'];
					}
				}
			}
		}else{
			if ($row_product['heavy_weight']) $heavy_weight = 1;
			else {
				//echo ' - '.$row_product['id_product'] . ' - ';
				if(!$cart->get_not_shipping_in_region_yes_no($row_product['id_product'], 0, $cart->shipping_country_code, $cart->shipping_state_code)){
					if($row_product['shipping_price']==0 and $row_product['weight']>0 and !$row_product['use_shipping_price']){
						$products[] = array(
							'name' => $row_product['name'],	
							'qty' => $row_product['qty'],
							'weight' => convert_lb_to_kg($convert_yes_no,$row_product['weight']),
							'weight_unit_symbol' => $unit_weight,
							'length' => convert_in_to_cm($convert_yes_no,$row_product['length']),
							'width' => convert_in_to_cm($convert_yes_no,$row_product['width']),
							'height' => convert_in_to_cm($convert_yes_no,$row_product['height']),
							'measurement_unit_symbol' => $unit_measurement,
							'extra_care' => $row_product['extra_care'],
						);
					}else if ($row_product['use_shipping_price']){
						// Total of shipping price by product
						$shipping_price_total += ($row_product['shipping_price']*$row_product['qty']);
						
						// remove product amount from subtotal
						$product_price = $row_product['subtotal'];
						
						foreach ($cart->get_product_discounts($row_product['id']) as $discount) $product_price -= $discount['amount']; 			
						$subtract_from_subtotal += $product_price;			
					}
				}else{
					$array_product_cannot_be_ship[] = $row_product['name'];
				}
			}
		}

		if (sizeof($product_options = $cart->get_product_options($row_product['id']))) {		
			foreach ($product_options as $row_product_option_group) { 
				foreach ($row_product_option_group['options'] as $row_product_option) {
					if(!$cart->get_not_shipping_in_region_yes_no(0, $row_product_option['id_options'], $cart->shipping_country_code, $cart->shipping_state_code)){  

						if($row_product_option['shipping_price']==0 and $row_product_option['weight']>0 and !$row_product_option['use_shipping_price']){
							$products[] = array(
								'name' => $row_product_option['name'],	
								'qty' => $row_product_option['qty'],
								'weight' => convert_lb_to_kg($convert_yes_no,$row_product_option['weight']),
								'weight_unit_symbol' => $unit_weight,
								'length' => convert_in_to_cm($convert_yes_no,$row_product_option['length']),
								'width' => convert_in_to_cm($convert_yes_no,$row_product_option['width']),
								'height' => convert_in_to_cm($convert_yes_no,$row_product_option['height']),
								'measurement_unit_symbol' => $unit_measurement,
								'extra_care' => $row_product_option['extra_care'],
							);
						}else if ($row_product_option['use_shipping_price']){
							// Total of shipping price by product
							$shipping_price_total += ($row_product_option['shipping_price']*$row_product_option['qty']);
							
							// remove product amount from subtotal
							$product_price = $row_product_option['subtotal'];
							
							foreach ($cart->get_product_option_discounts($row_product['id']) as $discount) $product_price -= $discount['amount']; 			
							$subtract_from_subtotal += $product_price;
						}
					}else{
						$array_product_cannot_be_ship[] = $row_product_option['name'];
					}
				}
			}
		}
		
		
	}	
}else{
	header("Location: /cart");
	exit;	
}

$cart->calculate_subtotal();
$cart->calculate_taxes();
$cart->calculate_total();
$cart->init();

// this var is used for the fixed shipping price
$cart_subtotal = $cart->subtotal-$subtract_from_subtotal;
$use_fixed_shipping_price = 0;

// check for fixed shipping price
if ($cart_subtotal > 0) {
	if (!$result = $mysqli->query('SELECT * FROM config_fixed_shipping_price WHERE max_cart_price >= "'.$cart_subtotal.'" ORDER BY max_cart_price ASC LIMIT 1')) throw new Exception('An error occured while trying to get fixed shipping price.');
	if ($row = $result->fetch_assoc()) { 
		$result->free();
		
		$shipping_price_total += $row['price'];		
		$use_fixed_shipping_price = 1;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow" />
<title><?php echo $config_site['site_name']; ?></title>
<?php include(dirname(__FILE__) . "/../_includes/template/header.php");?>
<script>
<!--
jQuery(function(){
	jQuery("#country_code_billing").change(function(){
		get_state_list('','billing')
	});	
	jQuery("#country_code_shipping").change(function(){
		get_state_list('','shipping')
	});
	
	jQuery("#form_shipping_list").on("click", "input[name='shipping_address']", function(event){
		current_shipping_address = jQuery("input[name='shipping_address']:checked").val();
		document.location.href='?task=change_shipping_address&shipping_address='+current_shipping_address;
	});
	
	jQuery("input[name='btn_next_step']").on("click",function(){		
		jQuery("#billing_address").val(jQuery("input[name='billing_address']:checked").val());
		return true;
	});
	
	jQuery("input[name='shipping_choice']").click(function(){
		add_shipping_info();
	});
	
	jQuery("input[name='local_pickup_choice']").click(function(){
		add_local_pickup_info();
	});
	
	
	//jQuery.scrollTo( 0 );

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
		url: "/_includes/ajax/shipping_billing?task=edit",
		type: "POST",
		dataType: "json",
		data: { "id":id },
		success: function(data) {
			switch (data) {					
				case 'false':
					alert('<?php echo language('global', 'ERROR_OCCURED');?>');
					break;					
				default:
					//$.scrollTo( "#address", 1000);
					jQuery("#"+form+" input[name='form_values[id]']").val(data.id);
					jQuery("#"+form+" select[name='form_values[use_in]']").val(data.use_in);
					if (data.total == 1) jQuery("#"+form+" select[name='form_values[use_in]']").prop('disabled',true);
					else jQuery("#"+form+" select[name='form_values[use_in]']").prop('disabled',false);
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
						jQuery("#"+form+" input[name='form_values[default_billing]']").val(data.default_billing);
					}else if(form == "form_shipping"){
						jQuery("#"+form+" input[name='form_values[default_shipping]']").val(data.default_shipping);
					}

					jQuery('#'+div_id).slideDown(500,'linear');
					
					break;
			}								
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert('<?php echo language('global', 'ERROR_OCCURED');?>');	
		}
	});			
}

function save_address (form,div_id,address_type){
	jQuery("#"+form+" .error").removeClass("error"); 
	jQuery.ajax({
		url: "/_includes/ajax/shipping_billing?task=save",
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
		error: function(jqXHR, textStatus, errorThrown) {
			alert('<?php echo language('global', 'ERROR_OCCURED');?>');	
		}
	});			
}

function delete_address (form,id,address_type,use_in){
	if (confirm("<?php echo language('cart/step_shipping', 'CONFIRM_DELETE');?>")) {		
		jQuery.ajax({
			url: "/_includes/ajax/shipping_billing?task=delete",
			type: "POST",
			dataType: "json",
			data: { "id":id,"address_type":address_type,"use_in":use_in,"shipping_gateway":<?php echo $shipping_gateway;?> },
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
			error: function(jqXHR, textStatus, errorThrown) {
				alert('<?php echo language('global', 'ERROR_OCCURED');?>');	
			}
		});	
	}
}

function add_shipping_info (){
	shipping_choice = jQuery("input[name='shipping_choice']:checked").val();
	if(shipping_choice == 'freeshipping'){
		company = "";
		service = "";
		rate = 0;
		estimated = "";
	}else{
		company = jQuery("input[name='shipping_gateway_company["+shipping_choice+"]']").val();
		service = jQuery("input[name='shipping_service["+shipping_choice+"]']").val();
		rate = jQuery("input[name='shipping["+shipping_choice+"]']").val();
		estimated = jQuery("input[name='shipping_estimated["+shipping_choice+"]']").val();
	}
	
	jQuery.ajax({
		url: "/_includes/ajax/shipping_billing?task=add_shipping_info",
		type: "POST",
		dataType: "json",
		data: {"shipping_choice":shipping_choice,"company":company,"service":service,"rate":rate,"estimated":estimated},
		success: function(data) {
			switch (data) {					
				case 'false':
					alert('<?php echo language('global', 'ERROR_OCCURED');?>');
				break;					
				default:
					jQuery("#shipping_cost_display").html('').append(data.shipping);
					jQuery("#total_tax_display").html('').append(data.taxes);
					jQuery("#total_display").html('').append(data.total);
				break;
			}								
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert('<?php echo language('global', 'ERROR_OCCURED');?>');	
		}
	});	
}

function add_local_pickup_info (){
	local_pickup_choice = jQuery("input[name='local_pickup_choice']:checked").val();
	jQuery.ajax({
		url: "/_includes/ajax/shipping_billing?task=add_local_pickup_info",
		type: "POST",
		dataType: "json",
		data: {"local_pickup_choice":local_pickup_choice},
		success: function(data) {
			switch (data) {					
				case 'false':
					alert('<?php echo language('global', 'ERROR_OCCURED');?>');
				break;					
				default:
					jQuery("#total_tax_display").html('').append(data.taxes);
					jQuery("#total_display").html('').append(data.total);
				break;
			}								
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert('<?php echo language('global', 'ERROR_OCCURED');?>');	
		}
	});	
}

function clear_btn (form,div_id){
	jQuery("#"+form+" .error").removeClass("error"); 
	clear_form(form);
	jQuery('#'+form+' input[name="form_values[id]"]').val("");
	jQuery('#'+div_id).slideUp(500,'linear');
	//$.scrollTo( "#address", 1000);	
}
function add_new_btn (form,div_id){
	jQuery("#"+form+" .error").removeClass("error"); 
	clear_form(form);
	jQuery('#'+form+' input[name="form_values[id]"]').val("");
	jQuery("#"+form+" select[name='form_values[use_in]']").prop('disabled',false);
	jQuery('#'+div_id).slideDown(500,'linear');		
}
-->
</script>
<style>
input[type="radio"], input[type="checkbox"] {
margin-top: 4px;
}
</style>
</head>
<body>
<?php 
	$returnToCart = true;//sert pour la supression depuis le mini panier
	include(dirname(__FILE__) . "/../_includes/template/top.php");
?>
<div class="main-container">
<div class="main">	
    <div class="container">
    <div class="main-content withblock">
    	<?php
        // Indicate wich Step to follow, to completed the transaction
        $step = 2;
        include(dirname(__FILE__) . "/../_includes/step.php");
        ?>
        <?php 
        $error_txt = '';
		switch ($error) {
			case 'shipping_service':
				$error_txt = language('cart/step_shipping', 'ERROR_SHIPPING_SERVICE');
				break;
			case 'billing_address':
				$error_txt = language('cart/step_shipping', 'ERROR_BILLING_ADDRESS');
				break;			
			case 'local_pickup_service':
				$error_txt = language('cart/step_shipping', 'ERROR_LOCAL_PICKUP');
				break;
							
		}
		if($error_txt != '') {?>
		<div class="alert alert-danger error-msg">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<ul><li><span><?php echo $error_txt;?></span></li></ul>
		</div>
		<?php }?>
	<div id="onepagecheckout_orderform">
	<div class="onepagecheckout_datafields">	
    <div class="row">
        <div id="billing" class="col-sm-6 iwd-1" style="overflow: hidden;">
        	<div id="bill_address_block" class="onepagecheckout_block">
              <div class="op_block_title">		
            	<?php echo language('cart/step_shipping', 'TITLE_BILLING_ADDRESS');?>
            	<div style="float:right;margin-right:5px;">
            	<input type="button" value="<?php echo language('global', 'BTN_ADD_NEW');?>" class="button" name="btn_add_billing_address" onclick="add_new_btn('form_billing','display_form_billing')" />
            	</div>
              </div>
              <div class="step op_block_detail">
                  <div id="display_form_billing" style="display:none;">
                      <form id="form_billing">
                          <input type="hidden" name="form_values[id]" value="">
                          <input type="hidden" name="form_values[address_type]" value="billing">
                          <input type="hidden" name="form_values[default_billing]" value="">
                          <div style="margin-bottom:5px;">
                              <strong><?php echo language('global', 'ADDRESS_USE_FOR');?></strong><br />
                               <select name="form_values[use_in]" style="width:98%;" id="use_in_billing">
                                  <option value="1"><?php echo language('global', 'ADDRESS_BILLING_ONLY');?></option>
                                  <option value="0"><?php echo language('global', 'ADDRESS_BILLING_SHIPPING');?></option>					
                              </select>                            
                          </div>
                          <div style="margin-bottom:5px;">
                              <strong><?php echo language('global', 'ADDRESS_FIRST_NAME');?> *</strong><br />
                              <input type="text" name="form_values[firstname]" style="width:98%;" value="" id="firstname_billing"/>
                          </div>                            
                          <div style="margin-bottom:5px;">
                              <strong><?php echo language('global', 'ADDRESS_LAST_NAME');?> *</strong><br />
                              <input type="text" name="form_values[lastname]" style="width:98%;" value="" id="lastname_billing" />     
                          </div>                                  
                          <div style="margin-bottom:5px;">
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
                              	<input type="button" value="<?php echo language('global', 'BTN_SAVE');?>" class="button" name="btn_save_billing" onclick="save_address('form_billing','display_form_billing','billing');" />
                              </div>
                              <div style="float:right">
                                  <input type="button" value="<?php echo language('global', 'BTN_CANCEL');?>" class="button" name="btn_cancel_billing" onclick="clear_btn('form_billing','display_form_billing')" />
                              </div>
                             	<div class="cb"></div>
                          </div>
                       </form>
                 </div>
                 <div id="form_billing_list">
  	   			<?php echo list_address('billing');?>
                 </div>
              </div>  
        	</div>
        </div>
        <div id="shipping" class="col-sm-6 iwd-1">
        	<div id="ship_address_block" class="onepagecheckout_block">
        	<div class="op_block_title">
            	<?php echo language('cart/step_shipping', 'TITLE_SHIPPING_ADDRESS');?>
                <div style="float:right;"><?php echo($config_site['enable_shipping']?'<input type="button" value="'.language('global', 'BTN_ADD_NEW').'" class="button" name="btn_add_billing_address" onclick="add_new_btn(\'form_shipping\',\'display_form_shipping\')" />':'');?></div>
            </div>
            <div class="op_block_detail">
           		<div id="display_form_shipping" style="display:none;">
                    <form id="form_shipping">
                        <input type="hidden" name="form_values[id]" value="">
                        <input type="hidden" name="form_values[address_type]" value="shipping">
                        <input type="hidden" name="form_values[default_shipping]" value="">
                        <input type="hidden" name="form_values[shipping_gateway]" value="<?php echo $shipping_gateway;?>">
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
                            <div style="margin-bottom:5px;">
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
                            	<input type="button" value="<?php echo language('global', 'BTN_SAVE');?>" class="button" name="create_account" onclick="save_address('form_shipping','display_form_shipping','shipping');" />
                            </div>
                            <div style="float:right">
                            	<input type="button" value="<?php echo language('global', 'BTN_CANCEL');?>" class="button" name="btn_cancel_shipping" onclick="clear_btn('form_shipping','display_form_shipping')" />
                            </div>
                            <div class="cb"></div>
                        </div>
                        </form>
           		</div>
               	<div id="form_shipping_list">
               		<?php echo list_address('shipping');?>
               	</div>
            </div>
            </div>
        </div>
    </div>
    <form method="post" name="form_shipping_info" id="form_shipping_info">
            <div id="shipping_company">
            <div class="op_block_title" style="overflow:hidden;">
                	<div style="float:left"><?php echo language('global', 'TITLE_SHIPPING_SERVICE');?></div>
                	<div style="float:right; padding-top:5px;"><?php echo($shipping_gateway && $shipping_gateway_logo && sizeof($products) && !$cart->local_pickup)?'<img src="/_images/'.$shipping_gateway_logo.'" height="25" alt="" />':'';?></div>
              </div>
               <div style="padding: 10px;" class="op_block_detail<?php echo($error=='shipping_service'?' title_bg_text_box_error':'');?>">
                	<?php 				
					$shipping_gateway_company = "";
					$shipping = 0;
					$shipping_service = "";
					$shipping_estimated = "";
					
					$counter_shipping = 0;
					$not_shipping = 0;
					// Verify if there is a shipping gateway and if there is product that cannot be ship into the shipping region
					if($shipping_gateway and sizeof($products) && !sizeof($array_product_cannot_be_ship) && !$use_fixed_shipping_price && !$heavy_weight){
						
						if(!empty($cart->shipping_country_code)){
							// Load the price for the customer default shipping address
							$shipping_address_default = array(
								'city'=>$cart->shipping_city,
								'state_code'=>$cart->shipping_state_code,
								'country_code'=>$cart->shipping_country_code,
								'zip'=>$cart->shipping_zip,
							);
							$shipping_class->address = $shipping_address_default;
							$shipping_class->products = $products;
							$shipping_class->sub_total = $cart->subtotal;
							$shipping_class->run();
		
							//echo '<pre>'.print_r($shipping_class->output,1) . '</pre>';
							if(sizeof($shipping_class->output) && !isset($shipping_class->output['error'])){
								echo '<input name="shipping_method" id="shipping_method" type="hidden" value="1" />
								<table border="0" cellpadding="3" cellspacing="2" width="100%">
									<tr>
										<th style="text-align:left; width: 30px;"></th>
										<th style="text-align:left"><strong>'.language('cart/step_shipping', 'TITLE_SHIPPING_SERVICE').'</strong></th>
										<th style="text-align:right"><strong>'.language('cart/step_shipping', 'TITLE_SHIPPING_RATE').'</strong></th>
										'.($hide_arrival_date?'':'<th style="text-align:right"><strong>'.language('cart/step_shipping', 'TITLE_SHIPPING_ESTIMATED').'</strong></th>').'
									</tr>
								';

								//$free_shipping = $cart->get_free_shipping_yes_no($cart->shipping_country_code,$cart->shipping_state_code);
								$i=0;
								if($free_shipping){
									echo '
									<tr>
										<td><input name="shipping_choice" type="radio" value="freeshipping" id="shipping_choice_'.$i.'" /></td>
										<td><label for="shipping_choice_'.$i.'" style="cursor:pointer;">'.language('global', 'TITLE_FREE_SHIPPING').'</label></td>
										<td style="text-align:right">'.nf_currency(0).'</td>
										'.($hide_arrival_date?'':'<td style="text-align:right">-</td>').'
									</tr>';
									
									++$i;
								}
								$counter_shipping=0;
								// This variable session validate if the customer had selected a shipping price to correct a bug
								$_SESSION['customer']['verify_shipping_price_check'] = array();
								foreach($shipping_class->output as $key=>$arr_option){
									
									$shipping_gateway_company = $shipping_name;
									$shipping = ($arr_option['rate']+$shipping_price_total);
									$shipping_service = $arr_option['name'];
									$shipping_estimated = $arr_option['deliveryDate'];
									
									$_SESSION['customer']['verify_shipping_price_check'][] = $shipping;
									
									echo '
									<tr>
										<td>
										<input name="shipping_choice" type="radio" value="'.$counter_shipping.'" id="shipping_choice_'.$i.'" />										
										<input name="shipping_gateway_company['.$counter_shipping.']" id="shipping_gateway_company['.$counter_shipping.']" type="hidden" value="'.$shipping_gateway_company.'" />
										<input name="shipping_service['.$counter_shipping.']" id="shipping_service['.$counter_shipping.']" type="hidden" value="'.$shipping_service.'" />
										<input name="shipping['.$counter_shipping.']" id="shipping['.$counter_shipping.']" type="hidden" value="'.$shipping.'" />
										<input name="shipping_estimated['.$counter_shipping.']" id="shipping_estimated['.$counter_shipping.']" type="hidden" value="'.$shipping_estimated.'" />
										
										</td>
										<td><label for="shipping_choice_'.$i.'" style="cursor:pointer;">'.$shipping_service.'</label></td>
										<td style="text-align:right">'.nf_currency($shipping).'</td>
										'.($hide_arrival_date?'':'<td style="text-align:right">'.$shipping_estimated).'</td>'.'
									</tr>';
									$counter_shipping++;
									
									++$i;
								}
								echo '</table>';
							}else {
								echo '<div class="error" style="float:left">'.language('cart/step_shipping', 'ERROR_SHIPPING_GATEWAY').'</div><div class="cb"></div>';
							}
						}else if($cart->local_pickup){
							echo '<div style="margin-bottom:10px; font-weight: bold; font-size: 14px;">'.language('global', 'TITLE_LOCAL_PICKUP').'</div><input name="shipping_method" id="shipping_method" type="hidden" value="0" /><input name="local_pickup" type="hidden" value="1" />';
							$query = 'SELECT 
							config_address_pickup.id,
							config_address_pickup.address,
							config_address_pickup.city,
							config_address_pickup.zip,
							country_description.name AS country,
							state_description.name AS state		
							FROM 
							config_address_pickup 
							LEFT JOIN 
							country_description
							ON
							(config_address_pickup.country_code = country_description.country_code AND country_description.language_code = "'.$_SESSION['customer']['language'].'")
							LEFT JOIN 
							state_description
							ON
							(config_address_pickup.state_code = state_description.state_code AND state_description.language_code = "'.$_SESSION['customer']['language'].'")';
							if ($result = $mysqli->query($query)) {
								if($result->num_rows){
									$counter_local_pickup=0;
									echo '
									<table border="0" cellpadding="3" cellspacing="2" width="100%">
										
									';
									while($row = $result->fetch_assoc()){
										
										echo '
										<tr>
											<td>
											<input name="local_pickup_choice" type="radio" value="'.$row['id'].'" id="local_pickup_choice_'.$counter_local_pickup.'"/>										
											</td>
											<td><label for="local_pickup_choice_'.$counter_local_pickup.'" style="cursor:pointer;">'.$row['address'].' '. $row['city'].' '.$row['state'].' '.$row['country'].' '.$row['zip'].'</label></td>
											
										</tr>';
										$counter_local_pickup++;
									}
									echo '</table>';
								}
							}
						}
					}else if($cart->local_pickup){
						echo '<div style="margin-bottom:10px; font-weight: bold; font-size: 14px;">'.language('global', 'TITLE_LOCAL_PICKUP').'</div><input name="shipping_method" id="shipping_method" type="hidden" value="0" /><input name="local_pickup" type="hidden" value="1" />';
						$query = 'SELECT 
						config_address_pickup.id,
						config_address_pickup.address,
						config_address_pickup.city,
						config_address_pickup.zip,
						country_description.name AS country,
						state_description.name AS state		
						FROM 
						config_address_pickup 
						LEFT JOIN 
						country_description
						ON
						(config_address_pickup.country_code = country_description.country_code AND country_description.language_code = "'.$_SESSION['customer']['language'].'")
						LEFT JOIN 
						state_description
						ON
						(config_address_pickup.state_code = state_description.state_code AND state_description.language_code = "'.$_SESSION['customer']['language'].'")';
						if ($result = $mysqli->query($query)) {
							if($result->num_rows){
								$counter_local_pickup=0;
								echo '
								<table border="0" cellpadding="3" cellspacing="2" width="100%">
									
								';
								while($row = $result->fetch_assoc()){
									
									echo '
									<tr>
										<td>
										<input name="local_pickup_choice" type="radio" value="'.$row['id'].'" id="local_pickup_choice_'.$counter_local_pickup.'"/>										
										</td>
										<td><label for="local_pickup_choice_'.$counter_local_pickup.'" style="cursor:pointer;">'.$row['address'].' '. $row['city'].' '.$row['state'].' '.$row['country'].' '.$row['zip'].'</label></td>
										
									</tr>';
									$counter_local_pickup++;
								}
								echo '</table>';
							}
						}
					}else if(sizeof($array_product_cannot_be_ship)){
						$not_shipping = 1;
						echo '<div id="product_shipping"><div class="error" style="margin-bottom: 15px;margin-top:0;">'.language('cart/step_shipping','ERROR_SHIPPING_REGION').'</div>';
						echo '<ul style="padding-left:20px;">';	
						foreach($array_product_cannot_be_ship as $key=>$value){
							
							echo '<li style="font-weight: bold;">' . $value . '</li>';	
						}
						echo '</ul></div>';
			
					}else if($free_shipping){
						echo '<div>'.language('global', 'TITLE_FREE_SHIPPING').'</div><input name="shipping_method" id="shipping_method" type="hidden" value="0" />';
						
					}else if ($heavy_weight) {
						echo '<input name="shipping_choice" type="radio" value="0" id="shipping_choice_0" />&nbsp;&nbsp;<label for="shipping_choice_0" style="cursor:pointer;">'.language('product', 'ALERT_HEAVY_WEIGHT').'</label>
						<input name="shipping_method" id="shipping_method" type="hidden" value="1" />
						<input name="shipping_gateway_company[0]" id="shipping_gateway_company[0]" type="hidden" value="'.$config_site['company_name'].'" />
						<input name="shipping_service[0]" id="shipping_service[0]" type="hidden" value="" />
						<input name="shipping[0]" id="shipping[0]" type="hidden" value="'.$shipping_price_total.'" />
						<input name="shipping_estimated[0]" id="shipping_estimated[0]" type="hidden" value="" />';	
					}else{
						
						// This variable session validate if the customer had selected a shipping price to correct a bug
						$_SESSION['customer']['verify_shipping_price_check'] = array();
						$_SESSION['customer']['verify_shipping_price_check'][] = $shipping_price_total;
						
						echo '<input name="shipping_choice" type="radio" value="0" id="shipping_choice_0" />&nbsp;&nbsp;<label for="shipping_choice_0" style="cursor:pointer;">'.language('global', 'TITLE_SHIPPING_FEES').'<strong style="font-size:14px">'.nf_currency($shipping_price_total).'</strong></label>
						<input name="shipping_method" id="shipping_method" type="hidden" value="1" />
						<input name="shipping_gateway_company[0]" id="shipping_gateway_company[0]" type="hidden" value="'.$config_site['company_name'].'" />
						<input name="shipping_service[0]" id="shipping_service[0]" type="hidden" value="" />
						<input name="shipping[0]" id="shipping[0]" type="hidden" value="'.$shipping_price_total.'" />
						<input name="shipping_estimated[0]" id="shipping_estimated[0]" type="hidden" value="" />';	
					}
					?>
                </div>
            </div>
		  <?php include(dirname(__FILE__) . "/../_includes/checkout_cart.php");?>
          <?php
          if(!$shipping_class->error && !$not_shipping){
          ?>
          <div style="clear:both; padding-top:15px;">
          	<input type="button" value="<?php echo language('global', 'BTN_PREVIOUS_STEP');?>" class="button" name="btn_previous_step" onclick="document.location.href='/cart'" />
          	<input type="submit" value="<?php echo language('global', 'BTN_NEXT_STEP');?>" class="button button-inverse" name="btn_next_step" style="float:right;" />
          </div>
          <?php
          }
          ?>            
          <input type="hidden" name="billing_address" id="billing_address" value="" />
     	</form>
  	
  	
    </div>
  	</div>         
    
    
    
    
    
   
    </div>
    </div>
    
</div>
</div>
<?php include(dirname(__FILE__) . "/../_includes/template/bottom.php");?>
</body>
</html>