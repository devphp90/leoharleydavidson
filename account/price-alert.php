<?php
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");

$task = isset($_GET['task']) ? trim($_GET['task']):'';

switch ($task) {
	case 'save':
		$error = array();
		$error_fields = array();
	
		$price_alert = $_GET['price_alert'];
		
		if (is_array($price_alert) && sizeof($price_alert)) {			
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
						if (!is_numeric($value['price']) || empty($value['price']) || $value['price'] > $value['original_price']) {								
							$error_fields[$key]['price'] = 'price';	
							$error['price'] = language('account/price-alert','ERRRO_INVALID_PRICE');
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

if ($task) {
	if (!$result = $mysqli->query('SELECT 
	customer_price_alert.*,
	product_image.filename,
	product_description.name,
	product_description.alias,
	IF(product_variant.id IS NOT NULL,GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", "),"") AS variant_name,
	calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,customer_type.percent_discount,customer_type.apply_on_rebate,rebate_coupon.type,rebate_coupon.discount) AS sell_price,
	product.id AS product_id
	FROM 
	customer_price_alert
	INNER JOIN 				
	(product CROSS JOIN product_description)
	ON
	(customer_price_alert.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")			
	 LEFT JOIN
	product_image
	ON
	(product.id = product_image.id_product AND product_image.cover = 1 AND product_image.force_crop = 0)
	LEFT JOIN
	(product_variant
	CROSS JOIN product_variant_option 
	CROSS JOIN product_variant_group 
	CROSS JOIN product_variant_group_option 
	CROSS JOIN product_variant_group_option_description
	CROSS JOIN product_variant_group_description)						
	ON
	(customer_price_alert.id_product_variant = product_variant.id
	AND product_variant.id = product_variant_option.id_product_variant 
	AND product_variant_option.id_product_variant_group = product_variant_group.id 
	AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
	AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option
	AND product_variant_group_option_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"
	AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
	AND product_variant_group_description.language_code = product_variant_group_option_description.language_code
	AND product_variant.active = 1)
	
	LEFT JOIN
	customer_type
	ON
	(customer_type.id = "'.$mysqli->escape_string($_SESSION['customer']['id_customer_type']).'")
	
	LEFT JOIN
	rebate_coupon 
	ON
	(product.id_rebate_coupon = rebate_coupon.id) 
		
	WHERE
	customer_price_alert.id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'"
	GROUP BY 
	customer_price_alert.id
	ORDER BY 
	customer_price_alert.id ASC')) throw new Exception('An error occured while trying to get price alerts'."\r\n\r\n".$mysqli->error);
	
	if ($result->num_rows) {
		
		if (isset($error) && is_array($error) && sizeof($error)) {
			echo '<div class="error" style="margin:0;">'.implode('<br />',$error).'</div>';	
		} else {
			switch ($success) {
				case 'save':
					echo '<div class="success">'.language('account/price-alert', 'SUCCESS_ADD_PRICE_ALERT').'</div>';	
					break;
				case 'delete':
					echo '<div class="success">'.language('account/price-alert', 'SUCCESS_DELETE_PRICE_ALERT').'</div>';	
					break;
			}
		}	
		echo '<div style="margin-bottom:10px">'.language('account/price-alert', 'TITLE_PAGE_DESCRIPTION').'</div>';
		echo '<table width="100%" border="0" cellpadding="3" cellspacing="0" class="standard">';
		
		$couleur = 2;
		while ($row = $result->fetch_assoc()) {
			if ($couleur == 1) $couleur = 2;
			else $couleur = 1;
			
			echo '<tr>	
					<td valign="top" class="td'.$couleur.'" colspan="4">
					<input type="hidden" name="price_alert['.$row['id'].'][id_customer_price_alert]" value="'.$row['id'].'" />
					<div style="font-weight:bold; font-size:14px"><a href="'.$url_prefix.'product/'.$row['alias'].'">'.$row['name'].'&nbsp;';
					if ($row['variant_name']) {
						echo '<br />'.$row['variant_name'].'&nbsp;&nbsp;('.nf_currency($row['sell_price']).')';
					} else {
						echo nf_currency($row['sell_price']);
					}
			$cover_image = $row['filename'] ? '/images/products/thumb/'.$row['filename']:get_blank_image('thumb');
			echo '</a></div></td></tr><tr>
					<td class="td'.$couleur.'">
					
					<img src="'.$cover_image.'" width="'.$images_sizes['thumb_width'].'" height="'.$images_sizes['thumb_height'].'" style="display:block;border:1px solid #ddd;" />
					</td>
			<td class="td'.$couleur.'">
			<input type="hidden" name="price_alert['.$i.'][id_product_variant]" value="'.$row['id_product_variant'].'" />	
			<div class="fl"><input type="radio" id="price_alert_'.$row['id'].'_type_0" name="price_alert['.$row['id'].'][type]" value="0" '.(isset($price_alert[$row['id']]['type']) && !$price_alert[$row['id']]['type'] || $row['id'] && !$row['type'] ? 'checked="checked"':'').' class="price_alert_type" /></div>
					<div class="fl '.(isset($price_alert[$row['id']]['type']) && $price_alert[$row['id']]['type'] == 1 || $row['id'] && $row['type'] == 1 ? 'disabled_text':'').'" style="margin-left:3px;"><label for="price_alert_'.$row['id'].'_type_0" style="display:inline;">'.language('account/price-alert', 'LABEL_ANY_PRICE_REDUCTTION').'</label></div>
					<div class="fl" style="margin-left:20px;"><input type="radio" id="price_alert_'.$row['id'].'_type_1" name="price_alert['.$row['id'].'][type]" value="1" '.(isset($price_alert[$row['id']]['type']) && $price_alert[$row['id']]['type'] == 1 || $row['id'] && $row['type'] == 1 ? 'checked="checked"':'').' class="price_alert_type" /></div>
					<div style="margin-left:3px;" class="fl '.((!isset($price_alert[$row['id']]['type']) || isset($price_alert[$row['id']]['type']) && $price_alert[$row['id']]['type'] == 0) && (!$row['id'] || $row['id'] && $row['type'] == 0) ? 'disabled_text':'').'"><label for="price_alert_'.$row['id'].'_type_1" style="display:inline;">'.language('account/price-alert', 'LABEL_PRICE_LOWER_THAN').'</label></div>
                    <div class="fl" style="margin-left:3px;"><input type="text" name="price_alert['.$row['id'].'][price]" id="price_alert_'.$row['id'].'_price" onkeyup="rewrite_number(\'price_alert_'.$row['id'].'_price\')" value="'.(isset($price_alert[$row['id']]) && $price_alert[$row['id']]['type'] == 1 ? $price_alert[$row['id']]['price']:($row['id'] && $row['type'] == 1 ? $row['price']:'')).'" size="10" class="'.(isset($error_fields[$row['id']]['price']) ? 'error ':'').'" '.((!isset($price_alert[$row['id']]['type']) || isset($price_alert[$row['id']]['type']) && $price_alert[$row['id']]['type'] == 0) && (!$row['id'] || $row['id'] && $row['type'] == 0) ? 'disabled="disabled"':'').' /></div>
			<input type="hidden" name="price_alert['.$row['id'].'][original_price]" value="'.$row['sell_price'].'" size="10" />
			</td>';
			echo '
			<td class="td'.$couleur.'"><strong>'.language('account/price-alert', 'LABEL_ORIGINAL_PRICE').'</strong> '.nf_currency($row['original_price']).'</td>
			<td class="td'.$couleur.'" align="right"><div style="float:right"><div class="button_regular button_delete"><input type="button" name="_remove" value="'.language('account/price-alert', 'BTN_REMOVE').'" class="button regular" onclick="javascript:remove_price_alert('.$row['id'].');" /></div></div></td></tr>';	
		}
		
		echo '</table>
		<div class="button_regular">
			<input type="button" name="_save" id="_save" value="'.language('account/price-alert', 'BTN_SAVE').'" class="button regular" />
		</div>
		<div class="cb">&nbsp;</div>';
	} else {
		echo '<div style="margin-bottom:10px">'.language('account/price-alert', 'TITLE_PAGE_DESCRIPTION').'</div>';
		echo language('account/price-alert','LABEL_NO_PRICE_ALERT');	
	}
	$result->free();	
?>
<script type="text/javascript" language="javascript">
jQuery(function(){
	jQuery("#_save").on("click",function(){
		jQuery.ajax({
			url: "?task=save",
			data: jQuery("#form_price_alert").serialize(),
			success: function(data) {
				jQuery("#form_price_alert").html("").append(data);
				jQuery.scrollTo( ".breadcrumbs", 1000);
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
			url: "?task=delete",
			data: { "id_customer_price_alert":id_customer_price_alert },
			success: function(data) {
				jQuery("#form_price_alert").html("").append(data);
				
			}
		});		
	}
}
</script>            
<?php	
	
	exit;					
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow" />
<title><?php echo $config_site['site_name'];?></title>
<?php include("../_includes/template/header.php");?>
<style>
table.standard td.td2 {
background: #dadada;
}
table.standard td.td1 {
background: #f0f0f0;
}
input.price_alert_type {
    float: left;
    margin-top: 2px;
}
.fl > input[type="text"] {
    margin-top: -5px;
}
.regular.button {
    margin: 10px;
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
            	<strong><?php echo language('global', 'BREADCRUMBS_MY_PRICE_ALERTS');?></strong>
            </li>
          </ul>
      	</div>            
    </div>
    <div class="main">
    <div class="container">
    <div class="main-content"> 
    	<h2 class="subtitle"><?php echo language('account/price-alert', 'TITLE_PAGE');;?></h2> 
    	
    	<div class="title_bg_text_box">
        
            <form method="post" id="form_price_alert" enctype="multipart/form-data">
            <?php
            if (!$result = $mysqli->query('SELECT 
			customer_price_alert.*,
			product_image.filename,
			product_description.name,
			product_description.alias,
            IF(product_variant.id IS NOT NULL,GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", "),"") AS variant_name,
			          calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,customer_type.percent_discount,customer_type.apply_on_rebate,rebate_coupon.type,rebate_coupon.discount) AS sell_price,
			product.id AS product_id
            FROM 
			customer_price_alert
			INNER JOIN 				
            (product CROSS JOIN product_description)
			ON
			(customer_price_alert.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")			
            LEFT JOIN
			product_image
			ON
			(product.id = product_image.id_product AND product_image.cover = 1 AND product_image.force_crop = 0)
			LEFT JOIN
            (product_variant
            CROSS JOIN product_variant_option 
            CROSS JOIN product_variant_group 
            CROSS JOIN product_variant_group_option 
            CROSS JOIN product_variant_group_option_description
            CROSS JOIN product_variant_group_description)						
            ON
            (customer_price_alert.id_product_variant = product_variant.id
            AND product_variant.id = product_variant_option.id_product_variant 
            AND product_variant_option.id_product_variant_group = product_variant_group.id 
            AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
            AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option
            AND product_variant_group_option_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"
            AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
            AND product_variant_group_description.language_code = product_variant_group_option_description.language_code
            AND product_variant.active = 1)
			
			LEFT JOIN
			customer_type
			ON
			(customer_type.id = "'.$mysqli->escape_string($_SESSION['customer']['id_customer_type']).'")
			
			LEFT JOIN
			rebate_coupon 
			ON
			(product.id_rebate_coupon = rebate_coupon.id) 
			            
            WHERE
            customer_price_alert.id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'"
            GROUP BY 
            customer_price_alert.id
            ORDER BY 
            customer_price_alert.id ASC')) throw new Exception('An error occured while trying to get price alerts'."\r\n\r\n".$mysqli->error);
            
            if ($result->num_rows) {
                
                if (isset($error) && is_array($error) && sizeof($error)) {
                    echo '<div class="error" style="margin:0;">'.implode('<br />',$error).'</div>';	
                } else {
                    switch ($success) {
                        case 'save':
                            echo '<div class="success" style="margin:0;">'.language('account/price-alert', 'SUCCESS_ADD_PRICE_ALERT').'</div>';	
                            break;
                        case 'delete':
                            echo '<div class="success" style="margin:0;">'.language('account/price-alert', 'SUCCESS_DELETE_PRICE_ALERT').'</div>';	
                            break;
                    }
                }	
                echo '<div style="margin-bottom:10px">'.language('account/price-alert', 'TITLE_PAGE_DESCRIPTION').'</div>';
                echo '<table width="100%" border="0" cellpadding="3" cellspacing="0" class="standard">';
                
                $couleur = 2;
                while ($row = $result->fetch_assoc()) {
                    if ($couleur == 1) $couleur = 2;
                    else $couleur = 1;
                    
                    echo '<tr>	
                            <td valign="top" class="td'.$couleur.'" colspan="4">
                            <div style="font-weight:bold; font-size:14px">
							<input type="hidden" name="price_alert['.$row['id'].'][id_customer_price_alert]" value="'.$row['id'].'" />
							<a href="'.$url_prefix.'product/'.$row['alias'].'">'.$row['name'].'&nbsp;';

					   
                    if ($row['variant_name']) {
                        echo '<br />'.$row['variant_name'].'&nbsp;&nbsp;('.nf_currency($row['sell_price']).')';
                    } else {
                        echo nf_currency($row['sell_price']);
                    }
                    $cover_image = $row['filename'] ? '/images/products/thumb/'.$row['filename']:get_blank_image('thumb');
                    echo '</a></div></td></tr><tr>
					<td class="td'.$couleur.'">
					
					<img src="'.$cover_image.'" width="'.$images_sizes['thumb_width'].'" height="'.$images_sizes['thumb_height'].'" style="display:block;border:1px solid #ddd;" />
					</td>
					<td class="td'.$couleur.'">
                    <input type="hidden" name="price_alert['.$i.'][id_product_variant]" value="'.$row['id_product_variant'].'" />	
                    <div class="fl"><input type="radio" id="price_alert_'.$row['id'].'_type_0" name="price_alert['.$row['id'].'][type]" value="0" '.(isset($price_alert[$row['id']]['type']) && !$price_alert[$row['id']]['type'] || $row['id'] && !$row['type'] ? 'checked="checked"':'').' class="price_alert_type" /></div>
					<div class="fl '.(isset($price_alert[$row['id']]['type']) && $price_alert[$row['id']]['type'] == 1 || $row['id'] && $row['type'] == 1 ? 'disabled_text':'').'" style="margin-left:3px;"><label for="price_alert_'.$row['id'].'_type_0" style="display:inline;">'.language('account/price-alert', 'LABEL_ANY_PRICE_REDUCTTION').'</label></div>
					<div class="fl" style="margin-left:20px;"><input type="radio" id="price_alert_'.$row['id'].'_type_1" name="price_alert['.$row['id'].'][type]" value="1" '.(isset($price_alert[$row['id']]['type']) && $price_alert[$row['id']]['type'] == 1 || $row['id'] && $row['type'] == 1 ? 'checked="checked"':'').' class="price_alert_type" /></div>
					<div style="margin-left:3px;" class="fl '.((!isset($price_alert[$row['id']]['type']) || isset($price_alert[$row['id']]['type']) && $price_alert[$row['id']]['type'] == 0) && (!$row['id'] || $row['id'] && $row['type'] == 0) ? 'disabled_text':'').'"><label for="price_alert_'.$row['id'].'_type_1" style="display:inline;">'.language('account/price-alert', 'LABEL_PRICE_LOWER_THAN').'</label></div>
                    <div class="fl" style="margin-left:3px;"><input type="text" name="price_alert['.$row['id'].'][price]" id="price_alert_'.$row['id'].'_price" onkeyup="rewrite_number(\'price_alert_'.$row['id'].'_price\')" value="'.(isset($price_alert[$row['id']]) && $price_alert[$row['id']]['type'] == 1 ? $price_alert[$row['id']]['price']:($row['id'] && $row['type'] == 1 ? $row['price']:'')).'" size="10" class="'.(isset($error_fields[$row['id']]['price']) ? 'error ':'').'" '.((!isset($price_alert[$row['id']]['type']) || isset($price_alert[$row['id']]['type']) && $price_alert[$row['id']]['type'] == 0) && (!$row['id'] || $row['id'] && $row['type'] == 0) ? 'disabled="disabled"':'').' /></div>
                    <input type="hidden" name="price_alert['.$row['id'].'][original_price]" value="'.$row['sell_price'].'" size="10" />
					</td>';
                    
                        echo '<td class="td'.$couleur.'"><strong>'.language('account/price-alert', 'LABEL_ORIGINAL_PRICE').'</strong> '.nf_currency($row['original_price']).'</td>
						<td class="td'.$couleur.'"><div style="float:right">
                            <div style="margin:0px;" class="button_regular  button_delete">
                                <input type="button" name="_remove" value="'.language('account/price-alert', 'BTN_REMOVE').'" class="regular button" onclick="javascript:remove_price_alert('.$row['id'].');" />
                            </div>
							</div>

                        </td>';
            
                    echo '
                    </tr>';	
                }
                
                echo '</table>
                <div class="button_regular">
                    <input type="button" name="_save" id="_save" value="'.language('account/price-alert', 'BTN_SAVE').'" class="regular button" />
                </div>
                <div class="cb">&nbsp;</div>';
            } else {
				echo '<div style="margin-bottom:10px">'.language('account/price-alert', 'TITLE_PAGE_DESCRIPTION').'</div>';
				echo language('account/price-alert','LABEL_NO_PRICE_ALERT');	
			}
            $result->free();
            ?>
			<script type="text/javascript" language="javascript">
            jQuery(function(){
                jQuery("#_save").on("click",function(){
                    jQuery.ajax({
                        url: "?task=save",
                        data: jQuery("#form_price_alert").serialize(),
                        success: function(data) {
                            jQuery("#form_price_alert").html("").append(data);
							jQuery.scrollTo( ".breadcrumbs", 1000);
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
                        url: "?task=delete",
                        data: { "id_customer_price_alert":id_customer_price_alert },
                        success: function(data) {
                            jQuery("#form_price_alert").html("").append(data);
                        }
                    });		
                }
            }
            </script>            
            </form>
        </div>
		<div class="cb"></div>
	</div>        
</div>
</div>
</div>

<?php include(dirname(__FILE__) . "/../_includes/template/review.php");?>
<?php include(dirname(__FILE__) . "/../_includes/template/bottom.php");?>
</body>
</html>