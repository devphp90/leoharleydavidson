<?php
require('../_includes/config.php');

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		if (isset($_GET['_remove_coupon'])) {
			if ($id_cart_discount = $_GET['id_cart_discount']) {
				if ($cart->del_discount($id_cart_discount)) {
					header('Location: /cart');
					exit;	
				}
			}
		}
		if (isset($_GET['page'])) {
			$page = 1;
		}else{
			$page = 0;
		}
        if (isset($_GET['_remove'])) {
			if (isset($_GET['delete_product'])) {
				$cart->del_product($_GET['delete_product']);				
			}
			if (isset($_GET['delete_option'])) {
				$cart->del_option($_GET['delete_option']);				
			}						
		}
		break;
	case 'POST':
		// remove from cart
		if (isset($_POST['_remove'])) {
			if (sizeof($cart_item = $_POST['delete_product'])) {
				foreach ($cart_item as $id_cart_item) {
					$cart->del_product($id_cart_item);
				}
			}

			if (sizeof($cart_item_option = $_POST['delete_option'])) {
				foreach ($cart_item_option as $id_cart_item_option) {
					$cart->del_option($id_cart_item_option);
				}
			}						
		}
		
		if (isset($_POST['_update_qty'])) {
			// update cart items
			if (sizeof($cart_item = $_POST['cart_item'])) {
				foreach ($cart_item as $row_cart_item) {
					$cart->upd_product_qty($row_cart_item['id'], $row_cart_item['qty']);
				}
			}
			
			// update cart item options
			if (sizeof($cart_item_option = $_POST['cart_item_option'])) {
				foreach ($cart_item_option as $row_cart_item_option) {
					$cart->upd_option_qty($row_cart_item_option['id'], $row_cart_item_option['qty']);
				}
			}
		}
		
		if (isset($_POST['_apply_coupon'])) {
			if ($coupon_code = $_POST['coupon_code']) {
				$cart->add_coupon($coupon_code);
				if (!sizeof($cart->get_messages())) {
					header('Location: /cart');
					exit;
				}
			} else { 
				$errors_fields['coupon_code'] = language('cart/index', 'ERROR_APPLY_COUPON');
			}
		}
		
		if (isset($_POST['_add_gift'])) {
			if (sizeof($add_gift = $_POST['add_gift'])) {				
				foreach ($add_gift as $id_rebate_coupon => $rows) {
					// Check if error
					foreach ($rows as $id_product) {			
						if (isset($_POST['add_gift_variant'][$id_product])) { 			
							$id_product_variant = $_POST['add_gift_variant'][$id_product];
							if (!$id_product_variant) $errors_fields['add_gift'][$id_product] = language('cart/index', 'ERROR_SELECT_VARIANT');
						}
					}
					// Add to cart if no error
					if(!sizeof($errors_fields)){
						foreach ($rows as $id_product) {			
							if (isset($_POST['add_gift_variant'][$id_product])) { 			
								$id_product_variant = $_POST['add_gift_variant'][$id_product];
								$cart->add_gift($id_rebate_coupon, $id_product, $id_product_variant);
							} else {
								$cart->add_gift($id_rebate_coupon, $id_product, 0);
							}
						}
					}
				}
				if(!sizeof($errors_fields)){
					header('Location: /cart');
					exit;
				}
			}
		}
		
		if (isset($_POST['_empty_cart'])) {
			$cart->empty_cart();	
		}
		break;	
}
function get_product_cover_image($id_product, $id_product_variant=0)
{
	global $mysqli;
	
	// get product image
	$cover_image = '';

	if (!$stmt_variant_image = $mysqli->prepare('SELECT 
	product_image_variant_image.filename
	FROM 
	product_image_variant
	INNER JOIN
	product_image_variant_image
	ON
	(product_image_variant.id = product_image_variant_image.id_product_image_variant)
	WHERE
	product_image_variant.id_product = ?
	AND
	product_image_variant.variant_code = ?
	AND
	product_image_variant_image.cover = 1
	ORDER BY 
	product_image_variant_image.cover DESC,
	product_image_variant_image.sort_order ASC
	LIMIT 1')) throw new Exception('An error occured while trying to prepare get variant cover image statement.'."\r\n\r\n".$mysqli->error);		
	
	if (!$stmt_image = $mysqli->prepare('SELECT
	product_image.filename
	FROM
	product_image
	WHERE
	product_image.id_product = ?
	AND
	product_image.cover = 1
	LIMIT 1')) throw new Exception('An error occured while trying to prepare get variant image statement.'."\r\n\r\n".$mysqli->error);	
	
	// if variant get variant code
	if ($id_product_variant) {
		if (!$result = $mysqli->query('SELECT variant_code FROM product_variant WHERE id = "'.$id_product_variant.'" LIMIT 1')) throw new Exception('An error occured while trying to get variant code.');
		$row = $result->fetch_assoc();
		
		$variant_code = $row['variant_code'];
	}

	// check if variant
	if (!empty($variant_code)) {
		/*
			This code below outputs this result (example)
			12:25,13:27,14:32
			12:25,13:27,14
			12:25,13,14					
			
			In that order, it allows us to get the variant codes of product image variants and loop through each to find an image 
		*/
		$i = sizeof(explode(',',$variant_code));				
		$variant_codes = array();
		$tmp_array = explode(',',$variant_code);
		for ($x=0; $x<$i; ++$x) {
			$tmpstr = implode(',',$tmp_array);	
			if (!in_array($tmpstr,$variant_codes)) $variant_codes[] = $tmpstr;
			
			$z=1;
			foreach (array_reverse($tmp_array,1) as $k => $v) {
				// skip the last array (the first one we do not split)
				if ($z == $i) break;
				
				if (strstr($v,':')) {
					$v = array_shift(explode(':',$v));
					$tmp_array[$k] = $v;
					break;
				}
								
				++$z;		
			}
		}		
		
		foreach ($variant_codes as $row_variant_code) {
			// check if we have a cover image for this variant code
			if (!$stmt_variant_image->bind_param("is", $id_product, $row_variant_code)) throw new Exception('An error occured while trying to bind params to get variant cover image statement.'."\r\n\r\n".$mysqli->error);
			
			/* Execute the statement */
			if (!$stmt_variant_image->execute()) throw new Exception('An error occured while trying to get variant cover image.'."\r\n\r\n".$mysqli->error);	
			
			/* store result */
			$stmt_variant_image->store_result();		

			/* bind result variables */
			$stmt_variant_image->bind_result($cover_image);																											
			
			$stmt_variant_image->fetch();
			
			// if an image was found
			if (!empty($cover_image)) break;
		}
	}
	
	if (!$cover_image) {
		if (!$stmt_image->bind_param("i", $id_product)) throw new Exception('An error occured while trying to bind params to get image statement.'."\r\n\r\n".$this->mysqli->error);
		
		/* Execute the statement */
		if (!$stmt_image->execute()) throw new Exception('An error occured while trying to execute get image statement.'."\r\n\r\n".$mysqli->error);				
		
		/* store result */
		$stmt_image->store_result();																											
		
		/* bind result variables */
		$stmt_image->bind_result($cover_image);	
			
		$stmt_image->fetch();
	}
	
	$stmt_variant_image->close();
	$stmt_image->close();
		
	return $cover_image;
}
// update cart
$cart->refresh_cart();


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow" />
<title><?php echo $config_site['site_name']; ?></title>
<?php include("../_includes/template/header.php");?>
<style type="text/css">
tr.trrow1 {
	background-color: #CCC;
}

</style>
<script type="text/javascript">
jQuery(function(){
	jQuery("#checkall").click(function(){
		jQuery(".cart_item:checkbox").prop("checked",jQuery(this).prop("checked"));
	});
});
<?php if(isset($_GET["returnback"])) {?>
history.go(-1);
<?php }?>
</script>
</head>
<body>
<?php 
	$returnToCart = true;//sert pour la supression depuis le mini panier
	include("../_includes/template/top.php");
?>
<div class="main-container">
	<div class="breadcrumbs">   	
		<div class="container">
          <ul>
          	<li>
            	<a href="/" title="<?php echo language('global', 'BREADCRUMBS_HOME');?>"><?php echo language('global', 'BREADCRUMBS_HOME');?></a>
            	<span>&gt;</span>
            </li>
            <li>
            	<strong><?php echo language('cart/index', 'LABEL_YOUR_CART');?></strong>
            </li>
          </ul>
      	</div>            
    </div>	
	<div class="main">
    <div class="container">
    <div class="main-content">
    <?php
    $msg_errors = '';
    if (sizeof($errors = $cart->get_messages())) {	
    	foreach ($errors as $error) {
    		$msg_errors .= '<li><span>'.$error.'</span></li>';	
    	}	
    }elseif (sizeof($errors_fields)) {
      $msg_errors .= '<li><span>'.language('global', 'ERROR_OCCURED').'</span></li>';
    }
    if(!empty($msg_errors)) {?>
      <div class="messages">
        <div class="alert alert-danger">
          <button type="button" class="close" data-dismiss="alert">×</button>
          <ul><?php echo $msg_errors;?></ul>
        </div>
      </div>
    <?php }?>

<?php 
$itemsCount = $cart->count_cart_items();

if ($itemsCount) {
	/*
	echo '<pre>';
	
	print_r($cart->get_products());
	echo '</pre>';*/
?>
<form method="post">
<?php

// get applicable gifts
if (!$_SESSION['customer']['id_customer_type'] || $_SESSION['customer']['id_customer_type'] && $_SESSION['customer']['apply_on_rebate']) {
	$total_free_product = sizeof($gifts = $cart->get_gifts());
	if ($total_free_product) {
		$first_pass = 0;
	
		foreach ($gifts['products'] as $row_gift) { 
			// Verify if it's the first pas in the loop to put the text and top of the box
			if(!$first_pass){
				$result_rebate_coupon = $mysqli->query('SELECT
					description
					FROM
					rebate_coupon_description
					WHERE
					id_rebate_coupon = "'.$mysqli->escape_string($row_gift['id_rebate_coupon']).'"
					AND
					language_code = "'.$_SESSION['customer']['language'].'"
					LIMIT 1');		
	
				if ($result_rebate_coupon->num_rows) {
					$row_rebate_coupon = $result_rebate_coupon->fetch_assoc();
					$rebate_description = $row_rebate_coupon['description'];
				}else{
					$rebate_description = '';
				}
				echo '<div class="title_bg title_bg_2"><div class=" title_bg_text title_bg_text_ffffff">'.($gifts['all_product'] ? language('cart/index', 'LABEL_FREE_GIFT'):language('cart/index', 'LABEL_FREE_GIFT_ONE')).'</div></div>
					<div class="title_bg_text_box'.(sizeof($errors_fields['add_gift'])?' title_bg_text_box_error':' title_bg_text_box_different').'" style="padding-bottom:5px; margin-bottom:10px">
					<div style="margin-bottom:10px">'.$rebate_description.'</div>
			<table border="0" cellpadding="4" cellspacing="0" width="100%">';
				$first_pass = 1;
			}
			
			$image_size = getimagesize(dirname(__FILE__).'/../'.$row_gift['cover']);
			
			echo '<tr>
			<td valign="top" align="center" width="1%">'.($gifts['all_product'] ? '<input type="checkbox" name="add_gift['.$gifts['id'].'][]" value="'.$row_gift['id_product'].'" '.((isset($_POST['add_gift'][$row_gift['id_rebate_coupon']]) && is_array($_POST['add_gift'][$gifts['id']]) && in_array($row_gift['id_product'],$_POST['add_gift'][$gifts['id']])) ? 'checked="checked"':'').' />':'<input type="radio" name="add_gift['.$gifts['id'].'][]" value="'.$row_gift['id_product'].'" '.((isset($_POST['add_gift'][$gifts['id']]) && is_array($_POST['add_gift'][$gifts['id']]) && in_array($row_gift['id_product'],$_POST['add_gift'][$gifts['id']])) ? 'checked="checked"':'').' />').'				
			</td>
			<td valign="top" width="75%">		
				<div class="fl" style="margin-right:5px;">
					<img src="'.$row_gift['cover'].'" '.$image_size[3].' style="display:block;border:1px solid #ddd;" />
				</div>
				<div class="fl" style="width: 525px;">
				<a href="'.$url_prefix.'product/'.$row_gift['alias'].'">'.$row_gift['name'].'</a>' . ($row_gift['used']?'<span style="font-style:italic;">'.language('global', 'LABEL_USED').'</span>':'').'';
				
				// get list of variants available
				if (isset($row_gift['variants']) && is_array($row_gift['variants']) && sizeof($row_gift['variants'])) {
					echo '<div style="margin-top:5px;"><select name="add_gift_variant['.$row_gift['id_product'].']">
					<option value="">--</option>';
	
					foreach ($row_gift['variants'] as $gift_id_product_variant => $gift_name) {
						echo '<option value="'.$gift_id_product_variant.'">'.$gift_name.'</option>';
					}
					
					echo '</select></div>';
				}
				
				if (isset($errors_fields['add_gift'][$row_gift['id_product']])) echo '<p><span class="error">'.$errors_fields['add_gift'][$row_gift['id_product']].'</span></p>';
				
				echo (!empty($row_gift['short_desc']) ? '<p>'.$row_gift['short_desc'].'</p>':'').'
				</div>
				<div class="cb"></div>
			</td>
			<td valign="top" align="right" width="24%"><div class="special_price" style="font-size: 16px">'.nf_currency(0).'</div></td>
			</tr><tr><td colspan="3"><hr /></td></tr>';		
		}
		
		echo '</table>
		<div class="button_different"><input type="submit" value="'.language('cart/index', 'BTN_ADD_CART').'" class="different" name="_add_gift" /></div><div class="cb"></div>	
		
		</div>';
	}
}
?>
<div class="title_bg title_bg_1">
	<div class="page-title">
        <h1><?php echo language('cart/index', 'LABEL_YOUR_CART');?></h1>
    </div>
	<div style="margin-bottom: 15px;">
    <input type="submit" value="<?php echo language('cart/index', 'BTN_REMOVE_SELECTED');?>" class="button" name="_remove" />
    <input type="submit" style="float:right;" value="<?php echo language('cart/index', 'BTN_UPDATE_QTY');?>" class="button" name="_update_qty" />
    </div>    

<table border="0" cellpadding="0" cellspacing="0" width="100%" class="data-table">
<thead>
<tr>
    <th style="width:1%;"><input type="checkbox" id="checkall" /></th>
    <th>&nbsp;</th>
    <th style="width:10%; text-align:center;"><?php echo language('global', 'TITLE_CHECKOUT_QTY');?></th>
    <th style="text-align:right; width:10%" nowrap="nowrap"><?php echo language('global', 'TITLE_CHECKOUT_PRICE');?></th>
    <th style="text-align:right; width:10%" nowrap="nowrap"><?php echo language('global', 'TITLE_CHECKOUT_TOTAL');?></th>
</tr>
</thead>

<?php 
$i=0;
$total_save=0;
$products = $cart->get_products();
foreach ($products as $row_product) { 
    $cover_image = get_product_cover_image($row_product['id_product'], $row_product['id_product_variant']);
	
	if ($cover_image){
	   $cover_image = '/images/products/thumb/'.$cover_image;
	   if(is_file(dirname(__FILE__).'/../'.$cover_image)){
	   	$image_size = getimagesize(dirname(__FILE__).'/../'.$cover_image);
	   }
	}else{ 
		$cover_image = '/_images/blank_image?size=thumb';
		$image_size[3] = ' width="'.$image_thumb_width.'" height="'.$image_thumb_height.'"';
	}
	
    echo '<tr>
    <td valign="top">
		<input type="checkbox" name="delete_product[]" class="cart_item" value="'.$row_product['id'].'" />
		<input type="hidden" name="cart_item['.$row_product['id'].'][id]" value="'.$row_product['id'].'" />
		<br />
		<div style="margin-top:10px;"><a href="'.$url_prefix.'product/'.$row_product['alias'].'?id_cart_item='.$row_product['id'].'"><img src="/_images/edit.png" width="16" height="16" border="0" title="'.language('cart/index', 'LABEL_EDIT').'" /></a></div>
	</td>
    <td valign="top">
            <div class="fl" style="margin-right:10px; width:'.$image_thumb_width.'px">
                <img src="'.$cover_image.'" '.$image_size[3].' style="display:block; border:1px solid #ddd;" />
            </div>
            <div class="fl" style="width:300px;">
                <div><a href="'.$url_prefix.'product/'.$row_product['alias'].($row_product['id_product_variant'] ? '?id_product_variant='.$row_product['id_product_variant']:'').'"><strong>'.$row_product['name'].'</strong></a>' . ($row_product['used']?'<span style="font-style:italic;">'.language('global', 'LABEL_USED').'</span>':'').'</div>'.
                (!empty($row_product['variant']) ? '<div style="font-size:10px;">'.$row_product['variant'].'</div>':'');
    
    if (sizeof($row_product['sub_products'])) {
        echo '<div style="font-size:10px; margin-left: 10px;">';
        
        foreach ($row_product['sub_products'] as $row_sub_product) {
            echo '<div style="margin-bottom:2px;">'.$row_sub_product['qty'].'x '.$row_sub_product['name'].'</div>';
        }			
        
        echo '</div>';
    }					
                
    echo '
            <div class="cb"></div>
        </div>
    </td>
    <td align="center" valign="top" nowrap="nowrap">'.
	(!$row_product['id_cart_discount'] && !$row_product['downloadable'] ? '<input type="text" name="cart_item['.$row_product['id'].'][qty]" value="'.$row_product['qty'].'" style="text-align:center; width:30px" />':'<input type="hidden" name="cart_item['.$row_product['id'].'][qty]" value="'.$row_product['qty'].'" />'.$row_product['qty'])
	.'</td>
   
    <td align="right" valign="top" nowrap="nowrap">';
	
	switch ($row_product['product_type']) {
		// single
		case 0:
		// combo deal
		case 1:				
			if ($row_product['price'] > $row_product['sell_price']) {
				echo '<div style="text-decoration:line-through;">'.nf_currency($row_product['price']).'</div>';
			}
			break;
	}
	
	echo nf_currency($row_product['sell_price']);
	switch ($row_product['product_type']) {
		// single
		case 0:
		// combo deal
		case 1:
			$save = $row_product['qty']*($row_product['price']-$row_product['sell_price']);
			
			if ($save > 0) {
				echo '<div style="color:#090; margin-top:10px; font-size:11px">'.language('cart/index', 'LABEL_SAVING').' '.nf_currency($save).'</div>';
			}
			
			$total_save += $save;
			break;	
	}
	
	echo'</td>
    <td align="right" valign="top" nowrap="nowrap">'.nf_currency($row_product['subtotal']).'</td>
    </tr>';
	
	// get discounts applied to this product
	if (sizeof($product_discounts = $cart->get_product_discounts($row_product['id_cart_item_product']))) {
		foreach ($product_discounts as $row_product_discount) {
			echo '<tr>
			<td>&nbsp;</td>
			<td valign="top" colspan="3">
				<div style="color:#090;">'.
				$row_product_discount['description'].($row_product_discount['end_date'] != "0000-00-00 00:00:00" ? '<br />
				<span style="color:#F00;">'.language('cart/index', 'LABEL_OFFER_UNTIL').' '.df_date($row_product_discount['end_date']).'</span>':'').'
				</div>
			</td>
			
			<td valign="top" align="right" nowrap="nowrap">'.($row_product_discount['amount'] > 0 ? '<span style="color:#F00;">-'.nf_currency($row_product_discount['amount']).'</span>':'&nbsp;').'
			</td>
			</tr>';
			
			$total_save += $row_product_discount['amount'];
		}
	}	
	
	// get product options
	if (sizeof($product_options = $cart->get_product_options($row_product['id']))) {
		
		
		echo '<tr><td colspan="6" class="options">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			
			<th colspan="2">Options ('.$row_product['name'].')</th>
			<th style="width:10%; text-align:center">'.language('global', 'TITLE_CHECKOUT_QTY').'</th>
			<th style="width:10%;text-align:right;">'.language('global', 'TITLE_CHECKOUT_PRICE').'</th>
			<th style="text-align:right; width:10%;">'.language('global', 'TITLE_CHECKOUT_TOTAL').'</th>
		</tr>';
		
		
		
		$total_option = count($product_options);
		foreach ($product_options as $row_product_group) {
			$option_group_name = '';
			$counter_option++;
						
			foreach ($row_product_group['options'] as $row_product_option) {
				if($option_group_name != $row_product_group['name']){
					echo '<tr><td>&nbsp;</td><td colspan="5">'.$row_product_group['name'].(!empty($row_product_group['description']) ? '<br />'.$row_product_group['description']:'').'</td></tr>';
					$option_group_name = $row_product_group['name'];
				}
				 
				echo '<tr>
				<td valign="top" align="center" style="width:1%;">
					<input type="checkbox" name="delete_option[]" class="cart_item" value="'.$row_product_option['id'].'" />
					<input type="hidden" name="cart_item_option['.$row_product_option['id'].'][id]" value="'.$row_product_option['id'].'" />
				</td>
				<td valign="top"><div><em>'.$row_product_option['name'].'</em></div>'.
					(!empty($row_product_option['description']) ? '<div style="margin-bottom:5px;">'.$row_product_option['description'].'</div>':'');
				
				
				switch ($row_product_group['input_type']) {
						// dropwdown
						case 0:											
							break;
						// radio
						case 1:
							break;
						// checkbox
						case 3:
							break;
						// multi select
						case 4:
							break;
						// textfield
						case 5:
							echo $row_product_option['textfield'];
							break;
						// textarea
						case 6:
							echo nl2br($row_product_option['textarea']);
							break;
						// file
						case 7:
							$tmp_uploads_dir = dirname(__FILE__).'/../../tmp_uploads/';		
							
							echo '<div style="margin-top:5px;"><a href="/tmp_uploads/'.$row_product_option['filename_tmp'].'" target="_blank">'.$row_product_option['filename'].'</a></div>';
							break;
						// date
						case 8:
							echo (!empty($row_product_option['date_end']) ? ''.language('cart/index', 'LABEL_START_DATE').' '.$row_product_option['date_start']:''.language('cart/index', 'LABEL_DATE').' '.$row_product_option['date_start']).'<br />';
							echo !empty($row_product_option['date_end']) ? ''.language('cart/index', 'LABEL_END_DATE').' '.$row_product_option['date_end']:'';
							break;
						// datetime
						case 9:
							echo (!empty($row_product_option['datetime_end']) ? ''.language('cart/index', 'LABEL_START_DATE_TIME').' '.$row_product_option['datetime_start']:''.language('cart/index', 'LABEL_DATE_TIME').' '.$row_product_group['datetime_start']).'<br />';
							echo !empty($row_product_option['datetime_end']) ? ''.language('cart/index', 'LABEL_END_DATE_TIME').' '.$row_product_option['datetime_end']:'';						
							break;
						// time
						case 10:
							echo (!empty($row_product_option['time_end']) ? ''.language('cart/index', 'LABEL_START_TIME').' '.$row_product_option['datetime_start']:''.language('cart/index', 'LABEL_TIME').' '.$row_product_option['datetime_start']).'<br />';
							echo !empty($row_product_option['time_end']) ? ''.language('cart/index', 'LABEL_END_TIME').' '.$row_product_option['datetime_end']:'';								
							break;
						
					}
				
				
				
				
				echo '</td>
				<td align="center" valign="top">'.($row_product_group['user_defined_qty'] ? '<input type="text" name="cart_item_option['.$row_product_option['id'].'][qty]" value="'.$row_product_option['qty'].'" style="text-align:center; width:30px" />':'<input type="hidden" name="cart_item_option['.$row_product_option['id'].'][qty]" value="'.$row_product_option['qty'].'" />'.$row_product_option['qty']).'</td>
				<td valign="top" align="right" nowrap="nowrap">'.nf_currency($row_product_option['sell_price']).'</td> 
				<td valign="top" align="right" nowrap="nowrap">'.nf_currency($row_product_option['subtotal']).'</td> 
				</tr>';
				
				
				// get discounts applied to this option
				if (sizeof($product_option_discounts = $cart->get_product_option_discounts($row_product_option['id']))) {
					foreach ($product_option_discounts as $row_product_option_discount) {
						echo '<tr>
						<td>&nbsp;</td>
						<td valign="top" colspan="3">
							<div style="color:#090;">
							
							'.
				$row_product_option_discount['description'].($row_product_option_discount['end_date'] != "0000-00-00 00:00:00" ? '<br />
				<span style="color:#F00;">'.language('cart/index', 'LABEL_OFFER_UNTIL').' '.df_date($row_product_option_discount['end_date']).'</span>':'').'
							
							
							
							</div>
						</td>
						
						<td valign="top" align="right" nowrap="nowrap">'.($row_product_option_discount['amount'] > 0 ? '<span style="color:#F00;">-'.nf_currency($row_product_option_discount['amount']).'</span>':'&nbsp;').'
						</td>
						</tr>';
						
						$total_save += $row_product_option_discount['amount'];
					}
				}					
			}			
		}
		echo '</table></td></tr>';
	}
	
} 
?>
</table>
<div align="right" style="font-size:16px; padding:5px; margin-top:5px;">
<strong><?php echo language('global', 'TITLE_CHECKOUT_SUBTOTAL');?>&nbsp;&nbsp;&nbsp;<?php echo nf_currency($cart->subtotal); ?></strong>
</div>
<?php
if ($total_save > 0) {
	echo '
	<div align="right" style="font-size:16px; padding:5px; margin-bottom: 10px">
	<span style="color:#090;">'.language('cart/index', 'LABEL_CHECKOUT_NOW_SAVE').' <strong>'.nf_currency($total_save).'</strong></span>
	</div>		
	';
}
?>

</div>

<?php if (!$_SESSION['customer']['id_customer_type'] || $_SESSION['customer']['id_customer_type'] && $_SESSION['customer']['apply_on_rebate']) { ?>
<div style="margin-top:20px;">
    <div id="coupon" <?php echo (isset($errors_fields['coupon_code'])?' class="title_bg_text_box_error"':'');?>>
    	
       
        
        
	    	<strong><?php echo language('cart/index', 'LABEL_APPLY_COUPON');?></strong><br />
    	    <input type="text" name="coupon_code" value="" style="width:220px;" /><div style="padding-top:5px;"><input type="submit" name="_apply_coupon" value="<?php echo language('cart/index', 'BTN_APPLY_COUPON');?>" class="button"   /></div>
            <?php 
			if (isset($errors_fields['coupon_code'])) echo '<div style="margin-top:15px"><span class="error">'.$errors_fields['coupon_code'].'</span></div>';
			?>
		
         	<?php
			if (sizeof($coupons = $cart->get_coupons())) {
				echo '<div style="margin-top:15px;"><ul>';
				foreach ($coupons as $row_coupon) {
					echo '<li style="padding-bottom:5px;"><strong>'.$row_coupon['coupon_code'].'</strong> (<a href="?_remove_coupon=1&id_cart_discount='.$row_coupon['id'].'">'.language('cart/index', 'LINK_REMOVE_COUPON').'</a>)<br />'.$row_coupon['description'].'</li>';
				}
				echo '</ul></div>';
			}
			?>          
    </div>
</div>
<?php } ?>
<div style="margin-top:20px;">
	<input type="button" value="<?php echo language('cart/index', 'BTN_CONTINUE_SHOPPING');?>" class="button"  onclick="javascript:window.location.href='<?php echo($_SESSION['link_continue_shopping']?$_SESSION['link_continue_shopping']:$url_prefix.'catalog');?>';" />
    <input type="button" style="float:right;" class="button button-inverse" value="<?php echo language('cart/index', 'BTN_CHECKOUT');?>" onclick="document.location.href='/account/login?return=<?php echo $config_site['enable_shipping']?urlencode('/cart/step_shipping'):urlencode('/cart/step_validation');?>'" />
</div>
<input type="hidden" id="id_cart_item_option" name="id_cart_item_option" value="" />
</form>
<?php	
} else {?>

 <h2 class="subtitle"><?php echo language('cart/index', 'LABEL_YOUR_CART');?></h2>	
	<p><?php echo language('cart/index', 'TITLE_YOUR_CART_IS_EMPTY');?></p>
	<p><a href="/"><?php echo language('cart/index', 'LINK_GOTO_HOME_PAGE');?></a> </p>   
<?php
}
?>
</div>
</div>
</div>
</div>
        


<?php include("../_includes/template/bottom.php");?>
</body>
</html>
