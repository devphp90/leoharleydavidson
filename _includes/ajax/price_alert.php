<?php
include(dirname(__FILE__) . "/../../_includes/config.php");

$id = (int)$_GET['id'];
if (!$result = $mysqli->query('SELECT 
product_description.name,
product_description.short_desc,
product_image.filename
FROM 
product 
INNER JOIN product_description ON product.id = product_description.id_product AND product_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"
LEFT JOIN product_image ON product.id = product_image.id_product AND product_image.cover = 1
WHERE
product.id = "'.$mysqli->escape_string($id).'"
AND
product.active = 1
AND
product.display_in_catalog = 1
AND
product.date_displayed <= NOW()')) throw new Exception('An error occured while trying to get product info.'."\r\n\r\n".$mysqli->error);

if ($result->num_rows) {
	$row = $result->fetch_assoc();	
	$result->free();
	
	$task = isset($_GET['task']) ? trim($_GET['task']):'';
	
	switch ($task) {
		case 'save':
			$error = array();
			$error_fields = array();
		
			$price_alert = $_GET['price_alert'];
			
			if (is_array($price_alert) && sizeof($price_alert)) {
				if (!$stmt_insert = $mysqli->prepare('INSERT INTO
				customer_price_alert
				SET
				id_customer = ?,
				id_product = ?,
				id_product_variant = ?,
				type = ?,
				original_price = ?,
				price = ?,
				date_created = NOW()')) throw new Exception('An error occured while trying to prepare add price alert statement.'."\r\n\r\n".$mysqli->error);
				
				if (!$stmt_update = $mysqli->prepare('UPDATE
				customer_price_alert
				SET
				type = ?,
				original_price = ?,
				price = ?
				WHERE
				id = ?
				AND
				id_customer = ?')) throw new Exception('An error occured while trying to prepare update price alert statement.'."\r\n\r\n".$mysqli->error);
				
				$selected = 0;
				foreach ($price_alert as $key => $value) {
					if (isset($value['type'])) {
						$id_customer_price_alert = (int)$value['id_customer_price_alert'];
						if ($value['type'] == 1) {
							if (!is_numeric($value['price']) || empty($value['price']) || ($value['price'] > $value['original_price'])) {								
								$error_fields[$key]['price'] = 'price';	
								$error['price'] = language('account/price-alert', 'ERRRO_INVALID_PRICE') . $value['price'] . ' - ' . $value['original_price'];
							} 
						}
						
						if (!isset($error_fields[$key])) {
							$value['original_price'] = $value['original_price'] > 0 ? $value['original_price']:0;
							$value['price'] = $value['price'] > 0 ? $value['price']:0;		
							$value['id_product_variant'] = $value['id_product_variant'] ? $value['id_product_variant']:0;					
							
							if ($id_customer_price_alert) {
								if (!$stmt_update->bind_param("iddii", $value['type'], $value['original_price'], $value['price'], 
								$id_customer_price_alert, $_SESSION['customer']['id'])) throw new Exception('An error occured while trying to bind params to update price alert statement.'."\r\n\r\n".$mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_update->execute()) throw new Exception('An error occured while trying to update price alert.'."\r\n\r\n".$mysqli->error);	
							} else {								
								if (!$stmt_insert->bind_param("iiiidd", $_SESSION['customer']['id'], $id, $value['id_product_variant'], 
								$value['type'], $value['original_price'], $value['price'])) throw new Exception('An error occured while trying to bind params to add price alert statement.'."\r\n\r\n".$this->mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_insert->execute()) throw new Exception('An error occured while trying to add price alert.'."\r\n\r\n".$mysqli->error);	
								
							}								
						}
						$selected = 1;
					}
				}
				
				if (!$selected) $error[] = language('account/price-alert', 'ERRRO_NO_PRICE_ALERT_SAVED');	
			} else {
				$error[] = language('account/price-alert', 'ERRRO_PRICE_ALERT_ERROR');	
			}
			
			if (!sizeof($error)) $success = 'save';
			break;	
		case 'delete':
			$id_customer_price_alert = (int)$_GET['id_customer_price_alert'];
			
			if ($id_customer_price_alert) {
				if (!$mysqli->query('DELETE FROM 
				customer_price_alert
				WHERE
				id = "'.$mysqli->escape_string($id_customer_price_alert).'"
				AND 
				id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'"')) throw new Exception('An error occured while trying to remove price alert.'."\r\n\r\n".$mysqli->error);	
				
				$success = 'delete';
			}
			break;
	}
	
	
	
	$filename = $row['filename'];
	
	if(is_file(dirname(__FILE__) . '/../../images/products/thumb/'.$filename)){
		$image_src = '/images/products/thumb/'.$filename;
	}else{
		$image_src = get_blank_image('thumb');
	}	
?>
<div class="title_product" style="overflow: hidden;">
    <div style="float:left"><img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" width="<?php echo $images_sizes['thumb_width']; ?>" height="<?php echo $images_sizes['thumb_height']; ?>" border="0" hspace="0" vspace="0" /></div>
    <div style="float:left; margin-left: 5px; max-width: 650px;"><h2><?php echo $row['name']; ?></h2><div><?php echo $row['short_desc']; ?></div></div>
    <div class="cb"></div>
</div>

<div style="margin-top:10px; border-top: 1px solid #CCC; padding-top:10px; clear:both;">
Cette fonctionnalité vous permet d'être alerté, par courriel, de la diminution du prix de ce produit.
</div>
<style>
input.price_alert_type {margin-top: 2px;}
.ui-dialog {left:300px !important; top:100px !important; z-index:10000000 !important;}
table.standard td.td2 {
background: #dadada;
}
table.standard td.td1 {
background: #f0f0f0;
}
#price_alert {height: auto !important;}
</style>
<div class="title_product" style="padding-top:5px;">
<form method="post" id="form_price_alert" enctype="multipart/form-data">
<?php
if (!$result = $mysqli->query('SELECT 
product.id,
product_variant.id AS id_product_variant,
customer_price_alert.id AS id_customer_price_alert,
IF(product_variant.id IS NOT NULL,GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", "),"") AS variant_name,
customer_price_alert.type,
customer_price_alert.original_price,
customer_price_alert.price,
calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,customer_type.percent_discount,customer_type.apply_on_rebate,rebate_coupon.discount_type,rebate_coupon.discount) AS sell_price
FROM 
product
LEFT JOIN
(product_variant
CROSS JOIN product_variant_option 
CROSS JOIN product_variant_group 
CROSS JOIN product_variant_group_option 
CROSS JOIN product_variant_group_option_description
CROSS JOIN product_variant_group_description)						
ON
(product.id = product_variant.id_product
AND product_variant.id = product_variant_option.id_product_variant 
AND product_variant_option.id_product_variant_group = product_variant_group.id 
AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option
AND product_variant_group_option_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"
AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
AND product_variant_group_description.language_code = product_variant_group_option_description.language_code
AND product_variant.active = 1)
LEFT JOIN
customer_price_alert
ON
(product.id = customer_price_alert.id_product AND IF(product_variant.id IS NOT NULL,product_variant.id = customer_price_alert.id_product_variant,1=1) AND customer_price_alert.id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'")

LEFT JOIN
customer_type
ON
(customer_type.id = "'.$mysqli->escape_string($_SESSION['customer']['id_customer_type']).'")

LEFT JOIN
rebate_coupon 
ON
(product.id_rebate_coupon = rebate_coupon.id) 

WHERE
product.id = "'.$mysqli->escape_string($id).'"
GROUP BY 
product.id,
product_variant.id
ORDER BY 
product_variant.sort_order ASC')) throw new Exception('An error occured while trying to get product price alerts'."\r\n\r\n".$mysqli->error);

if ($result->num_rows) {
	echo '<div style="margin-top: 5px;">';
	
	if (isset($error) && is_array($error) && sizeof($error)) {
		echo '<div class="messages"><div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><ul><li><span>'.implode('<br />',$error).'</span></li></ul></div></div>';
	} else {
		switch ($success) {
			case 'save':
				echo '<div class="messages"><div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><ul><li><span>'.language('account/price-alert', 'SUCCESS_ADD_PRICE_ALERT').'</span></li></ul></div></div>';	
				break;
			case 'delete':
				echo '<div class="messages"><div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><ul><li><span>'.language('account/price-alert', 'SUCCESS_DELETE_PRICE_ALERT').'</span></li></ul></div></div>';	
				break;
		}
	}	
	
	echo '<table width="100%" border="0" cellpadding="3" cellspacing="0" class="standard">';
	
	$couleur = 2;
	$i=0;
	while ($row = $result->fetch_assoc()) {
		if ($couleur == 1) $couleur = 2;
		else $couleur = 1;
		
		echo '<tr>	
				<td valign="top" class="td'.$couleur.'" colspan="3">
				<input type="hidden" name="price_alert['.$i.'][id_customer_price_alert]" value="'.$row['id_customer_price_alert'].'" />';
				
		if ($row['variant_name']) {
			echo '<div class="fl" style="margin-right:5px; font-weight:bold;">'.$row['variant_name'].'&nbsp;&nbsp;('.nf_currency($row['sell_price']).')</div><div class="cb"></div>
			<input type="hidden" name="price_alert['.$i.'][id_product_variant]" value="'.$row['id_product_variant'].'" />';
		} else {
			echo '<div class="fl"><strong>'.language('account/price-alert', 'LABEL_ORIGINAL_PRICE').' '.nf_currency($row['sell_price']).'</strong></div><div class="cb"></div>';
		}
		
		echo '	
		</td>
		</tr>
		<td class="td'.$couleur.'">
		<div class="fl"><input type="radio" id="price_alert_'.$i.'_type_0" name="price_alert['.$i.'][type]" value="0" '.(isset($price_alert[$i]['type']) && !$price_alert[$i]['type'] || $row['id_customer_price_alert'] && !$row['type'] ? 'checked="checked"':'').' class="price_alert_type" /></div>
		<div class="fl '.(isset($price_alert[$i]['type']) && $price_alert[$i]['type'] == 1 || $row['id_customer_price_alert'] && $row['type'] == 1 ? 'disabled_text':'').'" style="margin-left: 3px;"><label for="price_alert_'.$i.'_type_0" style="display:inline;">'.language('account/price-alert', 'LABEL_ANY_PRICE_REDUCTTION').'</label></div>
		<div class="fl" style="margin-left: 20px;"><input type="radio" id="price_alert_'.$i.'_type_1" name="price_alert['.$i.'][type]" value="1" '.(isset($price_alert[$i]['type']) && $price_alert[$i]['type'] == 1 || $row['id_customer_price_alert'] && $row['type'] == 1 ? 'checked="checked"':'').' class="price_alert_type" /></div>
		<div class="fl '.(isset($price_alert[$i]['type']) && !$price_alert[$i]['type'] || $row['id_customer_price_alert'] && !$row['type'] ? 'disabled_text"':'').'" style="margin-left: 3px;"><label for="price_alert_'.$i.'_type_1" style="display:inline;">'.language('account/price-alert', 'LABEL_PRICE_LOWER_THAN').'</label></div>
		<div class="fl" style="margin-left: 3px;margin-top:-5px;"><input type="text" name="price_alert['.$i.'][price]" id="price_alert_'.$i.'_price" onkeyup="rewrite_number(\'price_alert_'.$i.'_price\')" value="'.(isset($price_alert[$i]) && $price_alert[$i]['type'] == 1 ? $price_alert[$i]['price']:($row['id_customer_price_alert'] && $row['type'] == 1 ? $row['price']:'')).'" size="10" '.(isset($error_fields[$i]['price']) ? 'class="error"':'').' '.(isset($price_alert[$i]['type']) && $price_alert[$i]['type'] == 1 || $row['id_customer_price_alert'] && $row['type'] == 1 ? '':'disabled="disabled"').' /></div>
		<input type="hidden" name="price_alert['.$i.'][original_price]" value="'.$row['sell_price'].'" size="10" />
		</td>';
		
		if ($row['id_customer_price_alert']) {
			echo '	
			<td class="td'.$couleur.'"><strong>'.language('account/price-alert', 'LABEL_ORIGINAL_PRICE').'</strong> '.nf_currency($row['original_price']).'</td>
				
			<td class="td'.$couleur.'" align="right">
				<div style="float:right;margin: -10px 5px 0 0;">
				<div class="button_regular">
					<input type="button" class="button" name="_remove" value="'.language('account/price-alert', 'BTN_REMOVE').'" class="regular" onclick="javascript:remove_price_alert('.$row['id_customer_price_alert'].');" />
				</div>
				</div>
			</td>';
		}else{
			echo '	
			<td class="td'.$couleur.'">&nbsp;</td><td class="td'.$couleur.'">&nbsp;</td>';
		}

		echo '</tr>';	
		
		++$i;
	}
	
	echo '</table></div><br>
	<div class="button_regular" style="text-align:center;">
		<input type="button" name="_save" id="_save" value="'.language('account/price-alert', 'BTN_SAVE').'" class="button regular" />
	</div>
	<div class="cb">&nbsp;</div>';
}
$result->free();
?>
</form>
<script type="text/javascript" language="javascript">
jQuery(function(){
	jQuery("#_save").on("click",function(){
		jQuery.ajax({
			url: "/_includes/ajax/price_alert.php?id=<?php echo $id; ?>&task=save",
			data: jQuery("#form_price_alert").serialize(),
			success: function(data) {
				jQuery("#price_alert").html("").append(data);
				jQuery("#price_alert").scrollTo( 0,800 );
			}
		});		
		
		return false;
	});		
	
	jQuery(".price_alert_type").on("change",function(){
		if (jQuery(this).val() == 1){ 
			jQuery(this).parent().next().removeClass('disabled_text');
			jQuery(this).parent().prev().addClass('disabled_text');
			jQuery(this).parent().next().next().children(":input").prop("disabled",false).val("");
		}else{
			jQuery(this).parent().next().next().next().addClass('disabled_text');
			jQuery(this).parent().next().removeClass('disabled_text');
			jQuery(this).parent().next().next().next().next().children(":input").prop("disabled",true).val("");
		}
	});
});

function remove_price_alert(id_customer_price_alert)
{
	if (id_customer_price_alert && confirm("<?php echo language('account/price-alert','LABEL_CONFIRM_DELETE'); ?>")) {
		jQuery.ajax({
			url: "/_includes/ajax/price_alert.php?id=<?php echo $id; ?>&task=delete",
			data: { "id_customer_price_alert":id_customer_price_alert },
			success: function(data) {
				jQuery("#price_alert").html("").append(data);
			}
		});		
	}
}
</script>
<?php
}
?>