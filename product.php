<?php
require('_includes/config.php');
$is_product_page = true;

if(isset($_POST["save_review"])) {
  $_GET['action'] = 'save';
  include("_includes/ajax/review.php");  
  if(!empty($arr_save['rated']) || !empty($arr_save['title']) || !empty($arr_save['review'])) {
  	$err_review_requied_fields = true;
  }
}

$products_class = new Products;

// get images 
$tmp_uploads_dir = dirname(__FILE__).'/tmp_uploads/';

// id_product
// id_product_variant
// id_product_related

// type
// single, combo deal, bundle

// options

// suggestions
// related

// form unique id used for files and other stuff
$form_uid = (isset($_POST['form_uid']) && $_POST['form_uid']) ? $_POST['form_uid']:md5(session_id().time());

$id_product = (int)$_GET['id_product'];
$id_product_variant = (int)$_GET['id_product_variant'];
$variant_code = trim($_GET['variant_code']);
$variant_options = array();

// alias
if (!$id_product && $alias = trim($_GET['alias'])) {
	if (!$result = $mysqli->query('SELECT
	product.id
	FROM
	product
	INNER JOIN
	product_description
	ON
	(product.id = product_description.id_product AND product_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")	
	WHERE
	product_description.alias = "'.$mysqli->escape_string($alias).'"
	LIMIT 1')) throw new Exception('An error occured while trying to get product id.'."\r\n\r\n".$mysqli->mysqli->error);	
	if ($row = $result->fetch_assoc()) {
		$result->free();
		$id_product = $row['id'];
	} else {
		header("HTTP/1.0 404 Not Found");
		header('Location: /404?error=invalid_product');
		exit;	
	}
}

// check for language post
if (isset($_POST['language_main_site']) && $_POST['language_main_site']) {
	if (!$result = $mysqli->query('SELECT 
	product_description.alias
	FROM
	product_description
	WHERE
	product_description.id_product = "'.$mysqli->escape_string($id_product).'"
	AND
	product_description.language_code = "'.$mysqli->escape_string($_POST['language_main_site']).'" 
	LIMIT 1')) throw new Exception('An error occured while trying to get product.'."\r\n\r\n".$mysqli->error);
	if (!$row_switch = $result->fetch_assoc()) {
		header("HTTP/1.0 404 Not Found");
		header('Location: /404?error=invalid_product');
		exit;			
	}		
	
	if ($_SERVER['QUERY_STRING']) { 
		$_SERVER['QUERY_STRING'] = explode('&',$_SERVER['QUERY_STRING']);
		foreach ($_SERVER['QUERY_STRING'] as $key => $value) {
			if (stristr($value,'_lang') || stristr($value,'alias')) unset($_SERVER['QUERY_STRING'][$key]);
		}
		$_SERVER['QUERY_STRING'] = implode('&',$_SERVER['QUERY_STRING']);
	}
	
	header('Location: /'.$_POST['language_main_site'].'/product/'.$row_switch['alias'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING']:''));
	exit;
}

// get selected variant options if any
if (isset($_GET['variant_options'])) $variant_options = $_GET['variant_options'];
if (isset($_POST['variant_options'])) $variant_options = $_POST['variant_options'];

// if we have an id_product_variant, set that one, if not, get variant id from variant options if any
$id_product_variant = $id_product_variant ? $id_product_variant:(sizeof($variant_options) ? get_variant_id($id_product, $variant_options):0);

if (!$id_product_variant && $variant_code && (!is_array($variant_options) || is_array($variant_options) && !sizeof($variant_options))) {
	$variant_options = array();
	foreach (explode(',',$variant_code) as $value) {
		if (strstr($value,':')) {
			list($key,$val) = explode(':',$value);
			$variant_options[$key] = $val;				
		} else $variant_options[$value] = 0;
	}		
	
	$id_product_variant = get_variant_id($id_product, $variant_options);
}


// modify
if ($id_cart_item = (int)$_GET['id_cart_item']) {
	extract(modify_product($id_cart_item));
}

function modify_product($id_cart_item)
{
	global $mysqli, $form_uid, $id_product_variant, $variant_options;
	
	if (!$result = $mysqli->query('SELECT 
	cart_item.id,
	cart_item.id_cart_discount,
	cart_item.qty,
	cart_item_product.id AS id_cart_item_product,
	cart_item_product.id_product,
	cart_item_product.id_product_variant,
	product.product_type
	FROM 
	cart_item 
	INNER JOIN
	cart_item_product CROSS JOIN product
	ON
	(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id) 
	WHERE
	cart_item.id = "'.$mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to get cart item info.'."\r\n\r\n".$this->mysqli->error);	
	
	if ($result->num_rows) { 
		$row = $result->fetch_assoc();
		$id_cart_discount = $row['id_cart_discount'];
		$id_product_variant = $id_product_variant ? $id_product_variant:$row['id_product_variant'];
	} else {
		$errors[] = language('product', 'ALERT_PRODUCT_DOES_NOT_EXIST');	
	}	
	
	$result->free();	
	
	//$id_product = $row['id_product'];
	//$id_product_variant = $row['id_product_variant'];
	$modify_product_info['qty'] = $row['qty'];
	$modify_product = 1;
	
	switch ($row['product_type']) {
		// single
		case 0:
			break;	
		// combo
		case 1:
			if (!$result_sp = $mysqli->query('SELECT
			cart_item_product.id AS id_cart_item_product,
			cart_item_product.id_product,
			cart_item_product.id_product_variant,
			product_combo.id,			
			product_combo_variant.id AS id_product_combo_variant
			FROM 
			cart_item_product
			INNER JOIN
			(product_combo CROSS JOIN product_combo_variant)
			ON
			(cart_item_product.id_product_combo_product = product_combo.id AND product_combo.id = product_combo_variant.id_product_combo AND cart_item_product.id_product_variant = product_combo_variant.id_product_variant)
			WHERE
			cart_item_product.id_cart_item_product = "'.$mysqli->escape_string($row['id_cart_item_product']).'" 
			AND 
			cart_item_product.id_product_variant != 0')) throw new Exception('An error occured while trying to get combo product variants.'."\r\n\r\n".$this->mysqli->error);	
			
			while ($row_sp = $result_sp->fetch_assoc()) {
				$modify_product_info['combo_product'][$row_sp['id']]['id_product_combo_variant'] = $row_sp['id_product_combo_variant'];
				$modify_product_info['combo_product'][$row_sp['id']]['id_cart_item_product'] = $row_sp['id_cart_item_product'];
			}
			
			$result_sp->free();			
			break;
		// bundle
		case 2:
			if (!$result_sp = $mysqli->query('SELECT
			cart_item_product.id AS id_cart_item_product,
			cart_item_product.qty,
			cart_item_product.id_product_bundled_product_group_product,
			pg_product.id_product_bundled_product_group
			FROM 
			cart_item_product
			INNER JOIN 
			product_bundled_product_group_product AS pg_product
			ON
			(cart_item_product.id_product_bundled_product_group_product = pg_product.id)
			WHERE
			cart_item_product.id_cart_item_product = "'.$mysqli->escape_string($row['id_cart_item_product']).'"')) throw new Exception('An error occured while trying to get bundle products.'."\r\n\r\n".$this->mysqli->error);	
			
			while ($row_sp = $result_sp->fetch_assoc()) {
				$modify_product_info['bundle_product'][$row_sp['id_product_bundled_product_group']]['id'][] = $row_sp['id_product_bundled_product_group_product'];
				$modify_product_info['bundle_product'][$row_sp['id_product_bundled_product_group']]['qty'][$row_sp['id_product_bundled_product_group_product']] = $row_sp['qty'];
				$modify_product_info['bundle_product'][$row_sp['id_product_bundled_product_group']]['id_cart_item_product'][$row_sp['id_product_bundled_product_group_product']] = $row_sp['id_cart_item_product'];
			}
			
			$result_sp->free();			
			break;
	}
	
	// get options
	if (!$result_option = $mysqli->query('SELECT 
	cart_item_option.id,	
	cart_item_option.id_options,
	cart_item_option.id_options_group,
	cart_item_option.qty,
	options_group.input_type,
	cart_item_option.textfield,
	cart_item_option.textarea,
	cart_item_option.filename_tmp,
	cart_item_option.filename,
	cart_item_option.date_start,
	cart_item_option.date_end,
	cart_item_option.datetime_start,
	cart_item_option.datetime_end,
	cart_item_option.time_start,
	cart_item_option.time_end 
	FROM 
	cart_item_option 
	INNER JOIN 
	options_group
	ON
	(cart_item_option.id_options_group = options_group.id)
	WHERE
	cart_item_option.id_cart_item = "'.$mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to get product options.'."\r\n\r\n".$this->mysqli->error);	
		
	while ($row_option = $result_option->fetch_assoc()) {
		$modify_product_info['add_option'][$row_option['id_options_group']] = $row_option['id_options_group'];
		$modify_product_info['options'][$row_option['id_options_group']]['id'][$row_option['id_options']] = $row_option['id_options'];		
		$modify_product_info['options'][$row_option['id_options_group']]['qty'][$row_option['id_options']] = $row_option['qty'];		
		$modify_product_info['options'][$row_option['id_options_group']]['id_cart_item_option'][$row_option['id_options']] = $row_option['id'];	
		
		switch ($row_option['input_type']) {
			// dropdown
			case 0:
				break;
			// radio
			case 1: 
				break;
			// checkbox
			case 3:
				break;
			// multi-select
			case 4:
				break;
			// textfield
			case 5:
				$modify_product_info['options'][$row_option['id_options_group']]['textfield'][$row_option['id_options']] = $row_option['textfield'];
				break;
			// textarea
			case 6:
				$modify_product_info['options'][$row_option['id_options_group']]['textarea'][$row_option['id_options']] = $row_option['textarea'];
				break;
			// file
			case 7:				
				$key = $form_uid.'-'.$row_option['id_options_group'].'-'.$row_option['id_options'];
			
				$_SESSION['customer']['tmp_uploads'][$key] = array(
					'tmp_name'=>$row_option['filename_tmp'],
					'name'=>$row_option['filename'],
				);
				break;
			// date
			case 8:
				$modify_product_info['options'][$row_option['id_options_group']]['date'][$row_option['id_options']] = $row_option['date_start'];
				$modify_product_info['options'][$row_option['id_options_group']]['date_to'][$row_option['id_options']] = $row_option['date_end'];
				break;
			// date & time
			case 9:
				$modify_product_info['options'][$row_option['id_options_group']]['datetime'][$row_option['id_options']] = $row_option['datetime_start'];
				$modify_product_info['options'][$row_option['id_options_group']]['datetime_to'][$row_option['id_options']] = $row_option['datetime_end'];			
				break;
			// time
			case 10:
				$modify_product_info['options'][$row_option['id_options_group']]['time'][$row_option['id_options']] = $row_option['time_start'];
				$modify_product_info['options'][$row_option['id_options_group']]['time_to'][$row_option['id_options']] = $row_option['time_end'];						
				break;
		}			
	}
	
	return compact(array(
		'id_product_variant',
		'id_cart_discount',
		'modify_product',
		'modify_product_info',
	));	
}

if (!$result = $mysqli->query('SELECT
product.id,
product.product_type,
IF(product_variant.id IS NOT NULL AND product_variant.sku !="",product_variant.sku,product.sku) AS sku,
calc_sell_price(product.cost_price,0,product_variant.cost_price,0,0,0,0) AS cost_price,
calc_sell_price(product.price,product_variant.price_type,product_variant.price,0,0,0,0) AS price,
calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,0,0,0,0) AS sell_price,
calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,customer_type.percent_discount,customer_type.apply_on_rebate,rebate_coupon.discount_type,rebate_coupon.discount) AS current_price,
product.special_price,
IF("'.$mysqli->escape_string($current_datetime).'" BETWEEN product.special_price_from_date AND product.special_price_to_date,1,0) AS is_special_price,
product.special_price_from_date,
product.special_price_to_date,
product_description.name,
product_description.description,
product_description.short_desc,
product_description.alias,
product_description.meta_description,
product_description.meta_keywords,
product.brand,
product.model,
product.year,
product.mileage,
product.color,
product.discount_type,
product.discount,
product.use_product_current_price,
product.user_defined_qty,
product.used,
product.min_qty,
product.featured,
product.heavy_weight,
product.track_inventory,
product.allow_backorders,
is_product_in_stock(product.id,product_variant.id,0) AS in_stock,
qty_in_stock(product.id,product_variant.id) AS qty_in_stock,
product_variant.price_type AS variant_price_type,
product_variant.price AS variant_price,
get_max_qty_allowed(product.id,1,calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,customer_type.percent_discount,customer_type.apply_on_rebate,rebate_coupon.discount_type,rebate_coupon.discount),"'.$mysqli->escape_string($cart->id).'") AS max_qty,
product.has_variants,
product_variant.id AS id_product_variant,
IF(product_rating_count.avg_rating IS NOT NULL,product_rating_count.avg_rating,0) AS average_rated,
IF(product_rating_count.total_rating IS NOT NULL,product_rating_count.total_rating,0) AS total_rated,
rebate_coupon.discount_type AS rebate_discount_type,
rebate_coupon.discount AS rebate_discount,
product.downloadable,
IF(config_display_price_exceptions.id_product IS NOT NULL,1,0) AS display_price_exception,
IF(config_allow_add_to_cart_exceptions.id_product IS NOT NULL,1,0) AS allow_add_to_cart_exceptions,
product.display_multiple_variants_form
FROM
product 
INNER JOIN
product_description
ON
(product.id = product_description.id_product AND product_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
LEFT JOIN product_rating_count ON product.id = product_rating_count.id_product
LEFT JOIN 
product_variant
ON
(product.id = product_variant.id_product AND product_variant.id = "'.$mysqli->escape_string($id_product_variant).'")
LEFT JOIN product_review ON product.id = product_review.id_product


LEFT JOIN
customer_type
ON
(customer_type.id = "'.$mysqli->escape_string($_SESSION['customer']['id_customer_type']).'")

LEFT JOIN
rebate_coupon 
ON
(product.id_rebate_coupon = rebate_coupon.id) 

LEFT JOIN
config_display_price_exceptions
ON
(product.id = config_display_price_exceptions.id_product)

LEFT JOIN
config_allow_add_to_cart_exceptions
ON
(product.id = config_allow_add_to_cart_exceptions.id_product)

WHERE
product.id = "'.$mysqli->escape_string($id_product).'" '.(!$is_admin ?'
AND
product.active = 1
AND
product.display_in_catalog = 1
AND
product.date_displayed <= "'.$mysqli->escape_string($current_datetime).'" ':'').'
LIMIT 1')) throw new Exception('An error occured while trying to get variant options.'."\r\n\r\n".$mysqli->mysqli->error);	
	
if ($result->num_rows) {		
	$row = $result->fetch_assoc();		
	$product = $row;		
	$product['images']=array();				
	$product_discount = 0;
	$product['max_qty_price_tier'] = $product['max_qty'];
	$alias = $row['alias'];
	$config_site['display_multiple_variants_form'] = ($product['display_multiple_variants_form'] == 1 || $product['display_multiple_variants_form'] == 2 ? $product['display_multiple_variants_form']:$config_site['display_multiple_variants_form']);
	
	// calculate the total sell price with discount off if any
	switch ($product['product_type']) {
		// single
		case 0:
		// combo
		case 1:			
			if ($product['price'] > $product['sell_price']) {
				$product_discount = $product['price']-$product['sell_price'];			
			}
			break;
	}
	
	$product['total_discount'] = $product_discount;
	
	$result->free();

	// get images
	if (!$result_images = $mysqli->query('SELECT 
	id,
	original,
	filename,
	cover
	FROM
	product_image 
	WHERE
	id_product = "'.$mysqli->escape_string($id_product).'"
	ORDER BY
	IF(cover=1,-1,sort_order) ASC')) throw new Exception('An error occured while trying to get images.'."\r\n\r\n".$mysqli->mysqli->error);	
	while ($row_image = $result_images->fetch_assoc()) {
		$product['images'][] = $row_image;
	}
	
	//$product['images'] = array_values($product['images']);
	
	$result_images->free();
	
	// if we have a variant
	if ($id_product_variant && (!is_array($variant_options) || is_array($variant_options) && !sizeof($variant_options))) {
		$variant_options = array();

		if (!$result_variant = $mysqli->query('SELECT 
		product_variant_option.id_product_variant_group,
		product_variant_option.id_product_variant_group_option
		FROM 
		product_variant
		INNER JOIN
		product_variant_option
		ON
		(product_variant.id = product_variant_option.id_product_variant) 
		INNER JOIN
		product_variant_group 
		ON
		(product_variant_option.id_product_variant_group = product_variant_group.id)			
		WHERE
		product_variant.id = "'.$mysqli->escape_string($id_product_variant).'"
		ORDER BY 
		product_variant_group.sort_order ASC')) throw new Exception('An error occured while trying to get variant options.'."\r\n\r\n".$mysqli->mysqli->error);	
		
		while ($row_variant = $result_variant->fetch_assoc()) {
			$variant_options[$row_variant['id_product_variant_group']] = $row_variant['id_product_variant_group_option'];
		}
		
		$result_variant->free();
	}
	
	if ($product['has_variants']) {
		// if we have specified a variant, check if in stock
		if ($id_product_variant) {
			if (!$result = $mysqli->query('SELECT 
			is_product_in_stock(product_variant.id_product,product_variant.id,0) AS in_stock
			FROM 
			product_variant 
			WHERE 
			product_variant.id = "'.$mysqli->escape_string($id_product_variant).'"')) throw new Exception('An error occured while trying to check if product variant is in stock.'."\r\n\r\n".$mysqli->error);
			$row_in_stock = $result->fetch_assoc();
			$result->free();
			
			$product['in_stock'] = $row_in_stock['in_stock'];
		// if we have specified variant options	or check if at least one variant is in stock
		} else { 
			$joins = array();
			$where = array();
			
			// if we have variant options
			if (sizeof($variant_options)) {
				$i=1;
				foreach ($variant_options as $id_product_variant_group => $id_product_variant_group_option) {
					$joins[] = 'INNER JOIN product_variant_option AS pvo'.$i.' ON (product_variant.id = pvo'.$i.'.id_product_variant)';
					$where[] = '(pvo'.$i.'.id_product_variant_group = "'.$mysqli->escape_string($id_product_variant_group).'" '.($id_product_variant_group_option > 0 ? ' AND pvo'.$i.'.id_product_variant_group_option = "'.$mysqli->escape_string($id_product_variant_group_option).'"':'').')';
					
					++$i;
				}
			}
			
			if (!$result = $mysqli->query('SELECT 
			SUM(is_product_in_stock(product_variant.id_product,product_variant.id,0)) AS in_stock
			FROM
			product_variant '.
			(sizeof($joins) ? implode(' ',$joins):'').'
			WHERE 
			product_variant.id_product = "'.$mysqli->escape_string($row['id']).'" 
			'.
			(sizeof($where) ? ' AND '.implode(' AND ',$where):''))) throw new Exception('An error occured while trying to check if product variant options are in stock.'."\r\n\r\n".$mysqli->error);			
			$row_in_stock = $result->fetch_assoc();
			$result->free();
			
			$product['in_stock'] = $row_in_stock['in_stock'] > 0 ? 1:0;
		}
	}		
	
} else {
	//header("HTTP/1.0 404 Not Found");
	//header('Location: /404?error=invalid_product');
	exit;	
}

$url_alias = '/'.$_SESSION['customer']['language'].'/product/'.$product['alias'];

if ($task = trim($_GET['task'])) {
	switch ($task) {
		case 'get_variant_options':
			$output = array();
			$output['images'] = get_variant_images($id_product, $variant_options);
			if (!sizeof($output['images'])) $output['images'] = $product['images'];	
			
			foreach ($output['images'] as $key => $row_image) {
				$image_size = getimagesize(dirname(__FILE__).'/images/products/cover/'.$row_image['filename']);	
				$output['images'][$key]['image_size'] = $image_size;
			}
			
			$output['images'] = array_values($output['images']);
			
			$output['variant_options'] = get_variant_options($id_product, $variant_options);						
			$output['info'] = $product;
			$output['info']['rebate_info'] = get_applicable_rebate_text();
			$output['tier_prices'] = get_tier_prices($id_product, $id_product_variant);
			$output['info']['add_to_cart_top'] = get_add_cart_button_top();
			$output['info']['add_to_cart_bottom'] = get_add_cart_button_bottom();
			$output['info']['max_qty_info'] = get_max_qty();
			$output['info']['qty_remaining_info'] = show_qty_remaining($id_product, $id_product_variant);
			
			echo json_encode($output);	
					
			exit;
			break;
	}
}

// THIS IS WHERE WE WILL ADD TO CART
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//echo '<pre>'.print_r($_POST,1).'</pre>';
	$errors = array();
	$errors_fields = array();
	$qty = 1;
	
	$products = array();
	
	if (isset($_POST['_add_to_cart']) || isset($_POST['_add_selected_to_cart'])) {
		if (isset($_POST['_add_to_cart'])) $qty = (int)$_POST['add_to_cart_qty'];
		else if (isset($_POST['_add_selected_to_cart'])) $qty = (int)$_POST['add_selected_to_cart_qty'];
		
		switch ($product['product_type']) {
			// single 
			case 0:					
				$variants = $_POST['variants'];				
			
				if ($product['has_variants'] && !$id_product_variant && !$config_site['display_multiple_variants_form'] || $product['has_variants'] && $config_site['display_multiple_variants_form'] && !array_sum($variants)) {
					$errors[] = language('product', 'ALERT_CHOOSE_VARIANT'); 
					$error_variant = 1;
				}else if ($product['track_inventory'] && ($qty > $product['qty_in_stock'])) $errors[] = language('product', 'ALERT_NOT_ENOUGH_IN_STOCK');
				
				// if multiple variants
				if ($product['has_variants'] && $config_site['display_multiple_variants_form']) {
					$qty = 0;
					
					foreach ($variants as $value) {
						$qty += $value;
					}
					
					if (!$cart->min_qty_met($id_product, $qty)) {
						$errors[] = implode('<br />',$cart->get_messages());
					}
				}											
				break;
			// combo deal
			case 1:
				$products = $_POST['combo_product'];
				break;
			// bundle
			case 2:		
				// bundle product
				$products = $_POST['bundle_product'];
				break;
		}
		
		// options
		if (sizeof($add_option = $_POST['add_option'])) {
			$options = $_POST['options'];
			
			// loop through each option group
			foreach ($add_option as $id_group => $row_group) {
				$input_type = $options[$id_group]['input_type'];
				
				// check if we have sent an id, if not error
				if (!isset($options[$id_group]['id']) || isset($options[$id_group]['id']) && !sizeof($options[$id_group]['id'])) $errors_fields['add_option'][$id_group] = language('product', 'ALERT_CHOSSE_OPTION');			
				else {
					// loop thruogh each option id				
					foreach ($options[$id_group]['id'] as $id_option) {
						switch ($input_type) {
							// dropdown
							case 0:
								$option_qty = isset($options[$id_group]['qty'][$id_option]) ? $options[$id_group]['qty'][$id_option]:1;
								
								if (!$options[$id_group]['id'][0]) $errors_fields['add_option'][$id_group] = language('product', 'ALERT_CHOSSE_OPTION');							
								break;
							// radio
							case 1:
								$option_qty = isset($options[$id_group]['qty'][$id_option]) ? $options[$id_group]['qty'][$id_option]:1;
								
								break;
							// checkbox
							case 3:
								$option_qty = isset($options[$id_group]['qty'][$id_option]) ? $options[$id_group]['qty'][$id_option]:1;
														
								break;
							// multi select
							case 4:
								
								
								break;
							// textfield
							case 5:
								$textfield = isset($options[$id_group]['textfield'][$id_option]) ? $options[$id_group]['textfield'][$id_option]:'';
								
								if (empty($textfield)) $errors_fields['add_option'][$id_group][$id_option] = language('product', 'ALERT_FIELD_CANNOT_BE_EMPTY');
								break;
							// textarea
							case 6:
								$textarea = isset($options[$id_group]['textarea'][$id_option]) ? $options[$id_group]['textarea'][$id_option]:'';
								
								if (empty($textarea)) $errors_fields['add_option'][$id_group][$id_option] = language('product', 'ALERT_FIELD_CANNOT_BE_EMPTY');							
								break;
							// file
							case 7:
								$key = $form_uid.'-'.$id_group.'-'.$id_option;
								
								if (!isset($_SESSION['customer']['tmp_uploads'][$key]) && !is_uploaded_file($_FILES['options']['tmp_name'][$id_group]['file'][$id_option])) $errors_fields['add_option'][$id_group][$id_option] = language('product', 'ALERT_SELECT_FILE_TO_UPLOAD');
								// validate the file
								else {			
									if (is_uploaded_file($_FILES['options']['tmp_name'][$id_group]['file'][$id_option])) {
										// check if previous file was uploaded, remove
										if (!empty($_SESSION['customer']['tmp_uploads'][$key]['tmp_name']) && is_file($tmp_uploads_dir.$_SESSION['customer']['tmp_uploads'][$key]['tmp_name'])) unlink($tmp_uploads_dir.$_SESSION['customer']['tmp_uploads'][$key]['tmp_name']);
										
										$filename = mb_strtolower($_FILES['options']['name'][$id_group]['file'][$id_option]);									
										$ext = pathinfo($filename, PATHINFO_EXTENSION);
										$tmp_filename = $key.'.'.$ext;
										
										$_SESSION['customer']['tmp_uploads'][$key] = array(
											'name' => $filename,
											'tmp_name' => $tmp_filename,
										);
										
										move_uploaded_file($_FILES['options']['tmp_name'][$id_group]['file'][$id_option],$tmp_uploads_dir.$tmp_filename);																				
									} 
									
									$options[$id_group]['file'][$id_option] = $_SESSION['customer']['tmp_uploads'][$key];
								}										
								break;
							// date
							case 8:
								if (empty($options[$id_group]['date'][$id_option])) $errors_fields['add_option'][$id_group][$id_option]['date'] = language('product', 'ALERT_INVALID_DATE');		
								if (isset($options[$id_group]['date_to'][$id_option]) && empty($options[$id_group]['date_to'][$id_option])) $errors_fields['add_option'][$id_group][$id_option]['date_to'] = language('product', 'ALERT_INVALID_DATE');		
								break;
							// date & time
							case 9:
								if (empty($options[$id_group]['datetime'][$id_option])) $errors_fields['add_option'][$id_group][$id_option]['datetime'] = language('product', 'ALERT_INVALID_DATE_TIME');		
								if (isset($options[$id_group]['datetime_to'][$id_option]) && empty($options[$id_group]['datetime_to'][$id_option])) $errors_fields['add_option'][$id_group][$id_option]['datetime_to'] = language('product', 'ALERT_INVALID_DATE_TIME');	
								break;
							// time
							case 10:
								if (empty($options[$id_group]['time'][$id_option])) $errors_fields['add_option'][$id_group][$id_option]['time'] = 'Invalid time.';		
								if (isset($options[$id_group]['time_to'][$id_option]) && empty($options[$id_group]['time_to'][$id_option])) $errors_fields['add_option'][$id_group][$id_option]['time_to'] = language('product', 'ALERT_INVALID_TIME');							
								break;
						}
					}
				}
			}
			
			if (sizeof($errors_fields)) $errors[] = language('product', 'ALERT_ERROR_EXTRA');
		}
		
		// related products
		if (sizeof($add_related_product = $_POST['add_related_product'])) {		
			// loop through each related product
			// check if product has variants, if yes, check if a variant was selected
			if (!$stmt_related_product = $mysqli->prepare('SELECT 
			product.id,
			product.has_variants
			FROM 
			product_related 
			INNER JOIN
			product 
			ON
			(product_related.id_product_related = product.id) 
			WHERE
			product_related.id = ?')) throw new Exception('An error occured while trying to prepare related product statement.'."\r\n\r\n".$this->mysqli->error);	
			
			foreach ($add_related_product as $id_product_related => $row_product) {
				if (!$stmt_related_product->bind_param("i", $id_product_related)) throw new Exception('An error occured while trying to bind params to related product statement.'."\r\n\r\n".$this->mysqli->error);	
				
				/* Execute the statement */
				if (!$stmt_related_product->execute()) throw new Exception('An error occured while trying to get related product.'."\r\n\r\n".$this->mysqli->error);	
				
				/* store result */
				$stmt_related_product->store_result();																											
											
				/* bind result variables */
				$stmt_related_product->bind_result($related_product_id_product, $related_product_has_variants);										
		
				// fetch
				$stmt_related_product->fetch();
				
				if ($related_product_has_variants && !$row_product['id_product_variant']) $errors_fields['add_related_product'][$id_product_related] = language('product', 'ALERT_CHOOSE_VARIANT');	
				else if (!$related_product_has_variants) $add_related_product[$id_product_related]['id_product_variant'] = 0;
				
				$add_related_product[$id_product_related]['id_product'] = $related_product_id_product;
			}		
			
			$stmt_related_product->close();
		}
			
		if (!sizeof($errors)) {
			// if add multiple variants
			if ($product['has_variants'] && $config_site['display_multiple_variants_form']) {
				$remove_files = array();
						
				foreach ($variants as $id_product_variant => $qty) {
					// add product to cart
					if ($qty && $id_cart_item = $cart->add_product($id_product, $id_product_variant, $qty, 0, array(), 1)) {
						if (sizeof($add_option)) {
							// add options								
							foreach ($add_option as $id_options_group) {
								$tmp_options = $options[$id_options_group];
								
								if (isset($tmp_options['file']) && sizeof($tmp_options['file'])) {
									foreach ($tmp_options['file'] as $id_option => $row_file) {
										$remove_files[$row_file['tmp_name']] = $row_file['tmp_name'];
										
										$ext = pathinfo($row_file['tmp_name'], PATHINFO_EXTENSION);
										$tmp_filename = md5(time().rand(0,99999)).'.'.$ext;	
										copy($tmp_uploads_dir.$row_file['tmp_name'],$tmp_uploads_dir.$tmp_filename);
										$tmp_options['file'][$id_option]['tmp_name'] = $tmp_filename;
									}
								}
								
								$cart->add_option($id_cart_item, $id_options_group, $tmp_options);
							}						
						}
					}					
				}	
				
				foreach ($remove_files as $file) @unlink($tmp_uploads_dir.$file);
				
				if (sizeof($add_related_product)) {
					// add related
					foreach ($add_related_product as $row_product) $cart->add_product($row_product['id_product'], $row_product['id_product_variant'], 1, $row_product['id']);			
				}
			} else {				
				// add product to cart
				if (!$id_cart_item) $id_cart_item = $cart->add_product($id_product, $id_product_variant, $qty, 0, $products);
		
				// options
				if ($id_cart_item && sizeof($add_option)) {
					foreach ($add_option as $id_options_group) {
						$cart->add_option($id_cart_item, $id_options_group, $options[$id_options_group]);
					}
				}
				
				// related product
				if ($id_cart_item && sizeof($add_related_product)) {
					foreach ($add_related_product as $row_product) $cart->add_product($row_product['id_product'], $row_product['id_product_variant'], 1, $row_product['id']);
				}		
			}

			if (sizeof($cart->messages)) {
				$errors[] = implode('<br />',$cart->get_messages());
				if ($id_cart_item) extract(modify_product($id_cart_item));
			}
			
			if (!sizeof($errors)) {
				header('Location: /cart?page=product');
				exit;
			}			
		}
	} else if (isset($_POST['_upd_selected'])) {
		$qty = (int)$_POST['add_selected_to_cart_qty'];
		
		switch ($product['product_type']) {
			// single 
			case 0:					
				if ($product['has_variants'] && !$id_product_variant) $errors[] = language('product', 'ALERT_CHOOSE_VARIANT');
				break;
			// combo deal
			case 1:
				$products = $_POST['combo_product'];
				break;
			// bundle
			case 2:		
				// bundle product
				$products = $_POST['bundle_product'];
				break;
		}
		
		// options
		if (sizeof($add_option = $_POST['add_option'])) {
			$options = $_POST['options'];
			
			// loop through each option group
			foreach ($add_option as $id_group => $row_group) {
				$input_type = $options[$id_group]['input_type'];
				
				// check if we have sent an id, if not error
				if (!isset($options[$id_group]['id']) || isset($options[$id_group]['id']) && !sizeof($options[$id_group]['id'])) $errors_fields['add_option'][$id_group] = language('product', 'ALERT_CHOSSE_OPTION');			
				else {
					// loop thruogh each option id				
					foreach ($options[$id_group]['id'] as $id_option) {
						switch ($input_type) {
							// dropdown
							case 0:
								$option_qty = isset($options[$id_group]['qty'][$id_option]) ? $options[$id_group]['qty'][$id_option]:1;
								
								if (!$options[$id_group]['id'][0]) $errors_fields['add_option'][$id_group] = language('product', 'ALERT_CHOSSE_OPTION');							
								break;
							// radio
							case 1:
								$option_qty = isset($options[$id_group]['qty'][$id_option]) ? $options[$id_group]['qty'][$id_option]:1;
								
								break;
							// checkbox
							case 3:
								$option_qty = isset($options[$id_group]['qty'][$id_option]) ? $options[$id_group]['qty'][$id_option]:1;
														
								break;
							// multi select
							case 4:
								
								
								break;
							// textfield
							case 5:
								$textfield = isset($options[$id_group]['textfield'][$id_option]) ? $options[$id_group]['textfield'][$id_option]:'';
								
								if (empty($textfield)) $errors_fields['add_option'][$id_group][$id_option] = language('product', 'ALERT_FIELD_CANNOT_BE_EMPTY');
								break;
							// textarea
							case 6:
								$textarea = isset($options[$id_group]['textarea'][$id_option]) ? $options[$id_group]['textarea'][$id_option]:'';
								
								if (empty($textarea)) $errors_fields['add_option'][$id_group][$id_option] = language('product', 'ALERT_FIELD_CANNOT_BE_EMPTY');							
								break;
							// file
							case 7:
								$key = $form_uid.'-'.$id_group.'-'.$id_option;
								
								if (!isset($_SESSION['customer']['tmp_uploads'][$key]) && !is_uploaded_file($_FILES['options']['tmp_name'][$id_group]['file'][$id_option])) $errors_fields['add_option'][$id_group][$id_option] = language('product', 'ALERT_SELECT_FILE_TO_UPLOAD');
								// validate the file
								else {			
									if (is_uploaded_file($_FILES['options']['tmp_name'][$id_group]['file'][$id_option])) {
										// check if previous file was uploaded, remove
										if (!empty($_SESSION['customer']['tmp_uploads'][$key]['tmp_name']) && is_file($tmp_uploads_dir.$_SESSION['customer']['tmp_uploads'][$key]['tmp_name'])) unlink($tmp_uploads_dir.$_SESSION['customer']['tmp_uploads'][$key]['tmp_name']);
										
										$filename = mb_strtolower($_FILES['options']['name'][$id_group]['file'][$id_option]);									
										$ext = pathinfo($filename, PATHINFO_EXTENSION);
										$tmp_filename = $key.'.'.$ext;
										
										$_SESSION['customer']['tmp_uploads'][$key] = array(
											'name' => $filename,
											'tmp_name' => $tmp_filename,
										);
										
										move_uploaded_file($_FILES['options']['tmp_name'][$id_group]['file'][$id_option],$tmp_uploads_dir.$tmp_filename);
									}
									
									$options[$id_group]['file'][$id_option] = $_SESSION['customer']['tmp_uploads'][$key];
								}														
								break;
							// date
							case 8:
								if (empty($options[$id_group]['date'][$id_option])) $errors_fields['add_option'][$id_group][$id_option]['date'] = 'Invalid date.';		
								if (isset($options[$id_group]['date_to'][$id_option]) && empty($options[$id_group]['date_to'][$id_option])) $errors_fields['add_option'][$id_group][$id_option]['date_to'] = language('product', 'ALERT_INVALID_DATE');		
								break;
							// date & time
							case 9:
								if (empty($options[$id_group]['datetime'][$id_option])) $errors_fields['add_option'][$id_group][$id_option]['datetime'] = 'Invalid date & time.';		
								if (isset($options[$id_group]['datetime_to'][$id_option]) && empty($options[$id_group]['datetime_to'][$id_option])) $errors_fields['add_option'][$id_group][$id_option]['datetime_to'] = language('product', 'ALERT_INVALID_DATE_TIME');	
								break;
							// time
							case 10:
								if (empty($options[$id_group]['time'][$id_option])) $errors_fields['add_option'][$id_group][$id_option]['time'] = 'Invalid time.';		
								if (isset($options[$id_group]['time_to'][$id_option]) && empty($options[$id_group]['time_to'][$id_option])) $errors_fields['add_option'][$id_group][$id_option]['time_to'] = language('product', 'ALERT_INVALID_TIME');							
								break;
						}
					}
				}
			}
			
			if (sizeof($errors_fields)) $errors[] = language('product', 'ALERT_ERROR_EXTRA');
		}
		
		// related products
		if (sizeof($add_related_product = $_POST['add_related_product'])) {		
			// loop through each related product
			// check if product has variants, if yes, check if a variant was selected
			if (!$stmt_related_product = $mysqli->prepare('SELECT 
			product.id,
			product.has_variants
			FROM 
			product_related 
			INNER JOIN
			product 
			ON
			(product_related.id_product_related = product.id) 
			WHERE
			product_related.id = ?')) throw new Exception('An error occured while trying to prepare related product statement.'."\r\n\r\n".$this->mysqli->error);	
			
			foreach ($add_related_product as $id_product_related => $row_product) {
				if (!$stmt_related_product->bind_param("i", $id_product_related)) throw new Exception('An error occured while trying to bind params to related product statement.'."\r\n\r\n".$this->mysqli->error);	
				
				/* Execute the statement */
				if (!$stmt_related_product->execute()) throw new Exception('An error occured while trying to get related product.'."\r\n\r\n".$this->mysqli->error);	
				
				/* store result */
				$stmt_related_product->store_result();																											
											
				/* bind result variables */
				$stmt_related_product->bind_result($related_product_id_product, $related_product_has_variants);										
		
				// fetch
				$stmt_related_product->fetch();
				
				if ($related_product_has_variants && !$row_product['id_product_variant']) $errors_fields['add_related_product'][$id_product_related] = language('product', 'ALERT_CHOOSE_VARIANT');	
				else if (!$related_product_has_variants) $add_related_product[$id_product_related]['id_product_variant'] = 0;
				
				$add_related_product[$id_product_related]['id_product'] = $related_product_id_product;
			}		
			
			$stmt_related_product->close();
		}
			
		if (!sizeof($errors)) {
			// upd product 
			$id_cart_item_upd = $cart->upd_product($id_cart_item, $id_product, $id_product_variant, $qty, $products);
		
			// options
			if ($id_cart_item == $id_cart_item_upd && sizeof($add_option)) {
				// remove options that we didn't take
				if (!$result_option = $mysqli->query('SELECT
				id,
				id_options_group,
				id_options
				FROM 
				cart_item_option
				WHERE
				id_cart_item = "'.$mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to get options.'."\r\n\r\n".$mysqli->error);				
				
				while ($row_option = $result_option->fetch_assoc()) {
					if (!isset($add_option[$row_option['id_options_group']]) || isset($add_option[$row_option['id_options_group']]) && isset($options[$row_option['id_options_group']]['id']) && is_array($options[$row_option['id_options_group']]['id']) && !in_array($row_option['id_options'],$options[$row_option['id_options_group']]['id'])) $cart->del_option($row_option['id']);
				}		
				
				$result_option->free();						
				
				foreach ($add_option as $id_options_group) $cart->upd_option($id_cart_item,$id_options_group,$options[$id_options_group]);
			// remove all options
			} else {
				// remove options that we didn't take
				if (!$result_option = $mysqli->query('SELECT
				id
				FROM 
				cart_item_option
				WHERE
				id_cart_item = "'.$mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to get options.'."\r\n\r\n".$mysqli->error);				
				
				while ($row_option = $result_option->fetch_assoc()) {
					$cart->del_option($row_option['id']);
				}		
				
				$result_option->free();			
			}
			
			// related product
			if ($id_cart_item_upd && sizeof($add_related_product)) {
				foreach ($add_related_product as $row_product) $cart->add_product($row_product['id_product'], $row_product['id_product_variant'], 1, $id_cart_item_upd);
			}		
			
			if (sizeof($cart->messages)) $errors[] = implode('<br />',$cart->get_messages());
			
			if (!sizeof($errors)) {
				header('Location: /cart?page=product');
				exit;
			}
		}		
	} 
}

if (isset($_REQUEST['_add_to_wishlist'])) {
	$filter_url = http_build_query((isset($variant_options) ? array('variant_options'=>$variant_options):array()),'flags_');
	
	// if not logged in, login
	if (!isset($_SESSION['customer']['id']) || !$_SESSION['customer']['id']) {
		header('Location: /account/login?return='.urlencode(urldecode($url_alias.($filter_url ? '?'.$filter_url.'&':'?').'_add_to_wishlist=1')));
		exit;
	}
	
	// check if wishlist is created
	if (!$result_wishlist = $mysqli->query('SELECT
	customer_wishlist.id
	FROM 
	customer_wishlist
	WHERE
	customer_wishlist.id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'"
	ORDER BY
	customer_wishlist.id ASC
	LIMIT 1')) throw new Exception('An error occured while trying to get wishlist.'."\r\n\r\n".$mysqli->error);				
	
	if ($result_wishlist->num_rows) {
		$row_wishlist = $result_wishlist->fetch_assoc();	
		$id_wishlist = $row_wishlist['id'];			
	} else if (!$mysqli->query('INSERT INTO
	customer_wishlist
	SET
	id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'",
	date_created = "'.$mysqli->escape_string($current_datetime).'"')) throw new Exception('An error occured while trying to create wishlist.'."\r\n\r\n".$mysqli->error);				
	
	if (!$id_wishlist) $id_wishlist = $mysqli->insert_id;			
	$result_wishlist->free();
	
	// check if already exists 
	if (!$result_wishlist_product = $mysqli->query('SELECT 
	COUNT(id) AS total
	FROM
	customer_wishlist_product
	WHERE
	id_customer_wishlist = "'.$mysqli->escape_string($id_wishlist).'"
	AND
	id_product = "'.$mysqli->escape_string($id_product).'"
	AND
	id_product_variant = "'.$mysqli->escape_string($id_product_variant).'"')) throw new Exception('An error occured while trying to check if product already exists in wishlist.'."\r\n\r\n".$mysqli->error);			
	$row_wishlist_product = $result_wishlist_product->fetch_assoc();
	$result_wishlist_product->free();
	
	if (!$row_wishlist_product['total']) { 			
		// insert into wishlist
		if (!$mysqli->query('INSERT INTO 
		customer_wishlist_product
		SET
		id_customer_wishlist = "'.$mysqli->escape_string($id_wishlist).'",
		id_product = "'.$mysqli->escape_string($id_product).'",
		id_product_variant = "'.$mysqli->escape_string($id_product_variant).'",
		date_created = "'.$mysqli->escape_string($current_datetime).'"')) throw new Exception('An error occured while trying to add product to wishlist.'."\r\n\r\n".$mysqli->error);					
		
		header('Location: ?success=add_wishlist&'.$filter_url);
	} else {
		header('Location: ?error=product_exist_wishlist&'.$filter_url);
	}
	exit;
}

// get one set of options based on group option selection
function get_variant_options($id_product, $variant_options=array())
{
	global $mysqli, $id_cart_discount;
	
	$output='';
	
	$groups=get_variant_groups($id_product);
	
	// build sql 
	// get all groups for this product
	if (sizeof($groups)) {		
		end($groups); 
		$id_product_variant_group = key($groups); 
	
		if (isset($variant_options[$id_product_variant_group]) && $variant_options[$id_product_variant_group]) return false;
	
		$joins = array();	
		$where = array();		
		$group_by = array();	
		
		$i=1;
		foreach ($groups as $id_product_variant_group => $row_group) {	
			$where_str = array();
			
			$joins[] = 'INNER JOIN
			(product_variant_option AS pvo'.$i.' CROSS JOIN product_variant_group_option AS pvgo'.$i.' CROSS JOIN product_variant_group_option_description AS pvo'.$i.'_desc)
			ON
			(product_variant.id = pvo'.$i.'.id_product_variant AND pvo'.$i.'.id_product_variant_group_option = pvgo'.$i.'.id AND pvgo'.$i.'.id = pvo'.$i.'_desc.id_product_variant_group_option AND pvo'.$i.'_desc.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")';
		
			$where_str[] = 'pvo'.$i.'.id_product_variant_group = "'.$mysqli->escape_string($id_product_variant_group).'"';
			
			$stop=0;
			if (isset($variant_options[$id_product_variant_group]) && $variant_options[$id_product_variant_group]) {
				$id_product_variant_group_option = $variant_options[$id_product_variant_group];
				
				$where_str[] = 'pvo'.$i.'.id_product_variant_group_option = "'.$mysqli->escape_string($id_product_variant_group_option).'"';
			} else { 
				$stop=1;
			}
			
			if ($id_cart_discount) {
				$joins[] = 'LEFT JOIN
				(rebate_coupon_product AS rcp'.$i.' CROSS JOIN rebate_coupon AS rcpr'.$i.' CROSS JOIN cart_discount AS rcd'.$i.') 
				ON
				(product_variant.id_product = rcp'.$i.'.id_product AND rcp'.$i.'.id_rebate_coupon = rcpr'.$i.'.id ANd rcpr'.$i.'.id = rcd'.$i.'.id_rebate_coupon AND rcd'.$i.'.id = "'.$mysqli->escape_string($id_cart_discount).'")';
				
				$joins[] = 'LEFT JOIN
				(product_category AS pc'.$i.' CROSS JOIN rebate_coupon_category AS rcc'.$i.' CROSS JOIN rebate_coupon AS rccr'.$i.' CROSS JOIN cart_discount AS rccd'.$i.') 
				ON
				(product_variant.id_product = pc'.$i.'.id_product AND pc'.$i.'.id_category = rcc'.$i.'.id_category AND rcc'.$i.'.id_rebate_coupon = rccr'.$i.'.id ANd rccr'.$i.'.id = rcd'.$i.'.id_rebate_coupon AND rcd'.$i.'.id = "'.$mysqli->escape_string($id_cart_discount).'")';
				
				$where_str[] = '(rcp'.$i.'.id_rebate_coupon IS NOT NULL OR rcc'.$i.'.id_rebate_coupon IS NOT NULL)';
			}			
			
			$select = 'pvo'.$i.'.id_product_variant_group_option,
			pvo'.$i.'.id_product_variant_group,
			pvo'.$i.'_desc.name,
			pvgo'.$i.'.swatch_type,
			pvgo'.$i.'.color,
			pvgo'.$i.'.color2,
			pvgo'.$i.'.color3,
			pvgo'.$i.'.filename';
			
			$where[] = implode(' AND ',$where_str);
			
			$group_by[] = 'pvo'.$i.'.id_product_variant_group_option';
			
			$order_by = 'pvgo'.$i.'.sort_order ASC';
			
			$i++;
			
			if ($stop) break;
		}
		
		$joins = implode("\r\n",$joins);
		$where = implode(' AND ',$where);
		$group_by = implode(',',$group_by);
					
		if ($result_options = $mysqli->query('SELECT
		'.$select.'
		FROM		
		product_variant
		
		'.$joins.'
		
		WHERE
		product_variant.active = 1 
		AND
		product_variant.id_product = "'.$mysqli->escape_string($id_product).'"
		AND
		'.$where.'
		GROUP BY '.
		$group_by.'
		ORDER BY '.
		$order_by)) {			
			if ($result_options->num_rows) {
				$input_type = $groups[$id_product_variant_group]['input_type'];
	
				$output .= '<div style="margin-top:10px; margin-bottom:10px;" id="variant_group_'.$id_product_variant_group.'">
				<div style="margin-bottom:5px;"><strong>'.$groups[$id_product_variant_group]['name'].'</strong></div>';
	
				if (!$input_type) {
					$output .= '<select name="variant_options['.$id_product_variant_group.']">
					<option value="0">--</option>';
				}
				
				$i=0;
				while ($row_option = $result_options->fetch_assoc()) {
					$id_product_variant_group_option = $row_option['id_product_variant_group_option'];
					
					switch ($input_type) {
						// dropdown
						case 0:
							$output .= '<option value="'.$id_product_variant_group_option.'">'.$row_option['name'].'</option>';
							break;
						// radio
						case 1:
							$output .= '<div><input type="radio" name="variant_options['.$id_product_variant_group.']" id="product_variant_'.$id_product_variant_group.'_'.$id_product_variant_group_option.'" value="'.$id_product_variant_group_option.'">&nbsp;<label for="product_variant_'.$id_product_variant_group.'_'.$id_product_variant_group_option.'">'.$row_option['name'].'</label></div>';
							break;
						// swatch
						case 2:
							$swatch_type = $row_option['swatch_type'];
							$color = $row_option['color'];
							$color2 = $row_option['color2'];
							$color3 = $row_option['color3'];
							$filename = $row_option['filename'];
							
							if ($i==8) { $output .= '<div class="cb"></div>'; $i=0; }
						
							switch ($swatch_type) {
								// 1 color
								case 0:
									$output .= '<a href="javascript:void(0);" class="fl variant_color_outer_border"><input type="radio" id="variant_color_'.$id_product_variant_group.'_'.$id_product_variant_group_option.'" name="variant_options['.$id_product_variant_group.']" value="'.$id_product_variant_group_option.'" /><div class="variant_color_inner_border"><div style="background-color: '.$color.';" class="variant_color"></div></div></a>';
									break;
								// 2 color
								case 1:
									$output .= '<a href="javascript:void(0);" class="fl variant_color_outer_border">
									<input type="radio" id="variant_color_'.$id_product_variant_group.'_'.$id_product_variant_group_option.'" name="variant_options['.$id_product_variant_group.']" value="'.$id_product_variant_group_option.'" />
									<div class="variant_color_inner_border"><div class="variant_color">
									<div class="fl" style="width:10px; height:20px; background-color: '.$color.';"></div>
									<div class="fl" style="width:10px; height:20px; background-color: '.$color2.';"></div>																
									<div class="cb"></div>
									</div></div>
									</a>';
									break;
								// 3 color
								case 2:
									$output .= '<a href="javascript:void(0);" class="fl variant_color_outer_border">
									<input type="radio" id="variant_color_'.$id_product_variant_group.'_'.$id_product_variant_group_option.'" name="variant_options['.$id_product_variant_group.']" value="'.$id_product_variant_group_option.'" />	
									<div class="variant_color_inner_border"><div class="variant_color">															
									<div class="fl" style="width:7px; height:20px; background-color: '.$color.';"></div>
									<div class="fl" style="width:6px; height:20px; background-color: '.$color2.';"></div>
									<div class="fl" style="width:7px; height:20px; background-color: '.$color3.';"></div>
									<div class="cb"></div>
									</div></div>
									</a>';
									break;
								// file
								case 3:
									$output .= '<a href="javascript:void(0);" class="fl variant_color_outer_border">
									<input type="radio" id="variant_color_'.$id_product_variant_group.'_'.$id_product_variant_group_option.'" name="variant_options['.$id_product_variant_group.']" value="'.$id_product_variant_group_option.'" />	
									<div class="variant_color_inner_border"><div class="variant_color">		
									<img src="/images/products/swatch/'.$filename.'" width="20" height="20" border="0" hspace="0" vspace="0" />
									</div></div>
									</a>';
									break;																												
							}				
		
							++$i;					
							break;	
					}	
				}
				
				switch ($input_type) {
					// dropdown
					case 0:
						$output .= '</select>
						<script type="text/javascript">
						/* <![CDATA[ */
	
	
						jQuery(function(){
							jQuery("select[name=\'variant_options['.$id_product_variant_group.']\']").change(function(){
								// get values
								// ajax query to get new variants
								load_variant('.$id_product_variant_group.', jQuery(this).val());
								if (jQuery("#display_error").css("display") == "block"){
jQuery("#display_error").hide("blind", { direction: "vertical" }, 1000);
}
							});
						});
						
						/* ]]> */
						</script>';	
						break;
					// radio
					case 1:
						$output .= '<script type="text/javascript">
						/* <![CDATA[ */
	
						jQuery(function(){
							jQuery("input[name=\'variant_options['.$id_product_variant_group.']\']").change(function(){
	
								// get values
								// ajax query to get new variants
								load_variant('.$id_product_variant_group.', jQuery(this).val());
								if (jQuery("#display_error").css("display") == "block"){
jQuery("#display_error").hide("blind", { direction: "vertical" }, 1000);
}
							});
						});
						/* ]]> */
						</script>';				
						break;
					// swatch
					case 2:				
						$output .= '<script type="text/javascript">
						/* <![CDATA[ */
	
						jQuery(function(){
							jQuery("a.variant_color_outer_border").click(function(){
								if (!jQuery("input[id^=\'variant_color_\']:checked",this).length) { 
									jQuery("input[id^=\'variant_color_\']",this).attr("checked",true);
									
									jQuery("a.variant_color_outer_border").removeClass("selected");
									jQuery(this).addClass("selected");
									
									// get values
									// ajax query to get new variants
									load_variant('.$id_product_variant_group.', jQuery("input[id^=\'variant_color_\']",this).val());
									if (jQuery("#display_error").css("display") == "block"){
jQuery("#display_error").hide("blind", { direction: "vertical" }, 1000);
}							
								}
							});
						});
						/* ]]> */
						</script>';
						break;
				}
				
				$output .= '<div class="cb"></div></div>';
			} else {
				$output .= 'No options found.';
			}
		} else {
			throw new Exception('An error occured while trying to get variant group options.'."\r\n\r\n".$mysqli->error);		
		}
	}
	
	return $output;
}

// get all variant options based on selection
function get_variant_options_selected($id_product, $variant_options=array())
{
	global $mysqli, $id_cart_discount;
		
	$output='';
		
	$groups=get_variant_groups($id_product);
	
	// build sql 
	// get all groups for this product
	if (sizeof($groups)) {	
		$id_product_variant_group = key($groups);
		
		$stop = 0;
		$i=1;
		foreach ($groups as $id_product_variant_group => $row_group) {			
			$joins = array();	
			$where = array();		
			$group_by = array();	
			
			$i=1;
			foreach ($groups as $id_product_variant_group2 => $row_group2) {	
				$where_str = array();
				
				$joins[] = 'INNER JOIN
				(product_variant_option AS pvo'.$i.' CROSS JOIN product_variant_group_option AS pvgo'.$i.' CROSS JOIN product_variant_group_option_description AS pvo'.$i.'_desc)
				ON
				(product_variant.id = pvo'.$i.'.id_product_variant AND pvo'.$i.'.id_product_variant_group_option = pvgo'.$i.'.id AND pvgo'.$i.'.id = pvo'.$i.'_desc.id_product_variant_group_option AND pvo'.$i.'_desc.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")';
			
				$where_str[] = 'pvo'.$i.'.id_product_variant_group = "'.$mysqli->escape_string($id_product_variant_group2).'"';
				
				$stop=0;
				if ($id_product_variant_group != $id_product_variant_group2 && isset($variant_options[$id_product_variant_group2]) && $variant_options[$id_product_variant_group2]) {
					$id_product_variant_group_option = $variant_options[$id_product_variant_group2];
					
					$where_str[] = 'pvo'.$i.'.id_product_variant_group_option = "'.$mysqli->escape_string($id_product_variant_group_option).'"';
				} else { 
					$stop=1;
				}
				
				/*
				if ($id_cart_discount) {
					$joins[] = 'LEFT JOIN
					(rebate_coupon_product AS rcp'.$i.' CROSS JOIN rebate_coupon AS rcpr'.$i.' CROSS JOIN cart_discount AS rcd'.$i.') 
					ON
					(product_variant.id_product = rcp'.$i.'.id_product AND rcp'.$i.'.id_rebate_coupon = rcpr'.$i.'.id ANd rcpr'.$i.'.id = rcd'.$i.'.id_rebate_coupon AND rcd'.$i.'.id = "'.$mysqli->escape_string($id_cart_discount).'")';
					
					$joins[] = 'LEFT JOIN
					(product_category AS pc'.$i.' CROSS JOIN rebate_coupon_category AS rcc'.$i.' CROSS JOIN rebate_coupon AS rccr'.$i.' CROSS JOIN cart_discount AS rccd'.$i.') 
					ON
					(product_variant.id_product = pc'.$i.'.id_product AND pc'.$i.'.id_category = rcc'.$i.'.id_category AND rcc'.$i.'.id_rebate_coupon = rccr'.$i.'.id ANd rccr'.$i.'.id = rcd'.$i.'.id_rebate_coupon AND rcd'.$i.'.id = "'.$mysqli->escape_string($id_cart_discount).'")';
					
					$where_str[] = '(rcp'.$i.'.id_rebate_coupon IS NOT NULL OR rcc'.$i.'.id_rebate_coupon IS NOT NULL)';
				}*/
				
				$select = 'pvo'.$i.'.id_product_variant_group_option,
				pvo'.$i.'.id_product_variant_group,
				pvo'.$i.'_desc.name,
				pvgo'.$i.'.swatch_type,
				pvgo'.$i.'.color,
				pvgo'.$i.'.color2,
				pvgo'.$i.'.color3,
				pvgo'.$i.'.filename';
				
				$where[] = implode(' AND ',$where_str);
				
				$group_by[] = 'pvo'.$i.'.id_product_variant_group_option';
				
				$order_by = 'pvgo'.$i.'.sort_order ASC';
				
				$i++;
				
				if ($stop) break;
			}
			
			$joins = implode("\r\n",$joins);
			$where = implode(' AND ',$where);
			$group_by = implode(',',$group_by);
		
		
			// we need to list all main group options
			if ($result_group_option = $mysqli->query('SELECT
			'.$select.'
			FROM
			product_variant
			
			'.$joins.'
			
			WHERE
			product_variant.active = 1
			AND
			product_variant.id_product = "'.$mysqli->escape_string($id_product).'"
			AND
			'.$where.'
			GROUP BY '.
			$group_by.'
			ORDER BY '.
			$order_by)) {
				if ($result_group_option->num_rows) {
					$input_type = $groups[$id_product_variant_group]['input_type'];
					
					$output .= '<div style="margin-top:10px; margin-bottom:10px;" id="variant_group_'.$id_product_variant_group.'">
					<div style="margin-bottom:5px;"><strong>'.$row_group['name'].'</strong></div>';
		
					if (!$input_type) {
						$output .= '<select name="variant_options['.$id_product_variant_group.']">
						<option value="">--</option>';
					}			
					
					$current_selected = isset($variant_options[$id_product_variant_group]) ? $variant_options[$id_product_variant_group]:0;
					
					$i=0;
					while ($row_group_option = $result_group_option->fetch_assoc()) {
						$id_product_variant_group_option = $row_group_option['id_product_variant_group_option'];
						
						switch ($input_type) {
							// dropdown
							case 0:
								$output .= '<option value="'.$id_product_variant_group_option.'" '.(($current_selected == $id_product_variant_group_option) ? 'selected="selected"':'').'>'.$row_group_option['name'].'</option>';
								break;
							// radio
							case 1:
								$output .= '<div>
								<input type="radio" name="variant_options['.$id_product_variant_group.']" id="product_variant_'.$id_product_variant_group.'_'.$id_product_variant_group_option.'" value="'.$id_product_variant_group_option.'" '.(($current_selected == $id_product_variant_group_option) ? 'checked="checked"':'').'>&nbsp;<label for="product_variant_'.$id_product_variant_group.'_'.$id_product_variant_group_option.'">'.$row_group_option['name'].'</label>
								</div>';
								break;
							// swatch
							case 2:
								$swatch_type = $row_group_option['swatch_type'];
								$color = $row_group_option['color'];
								$color2 = $row_group_option['color2'];
								$color3 = $row_group_option['color3'];
								$filename = $row_group_option['filename'];
								
								if ($i==8) { $output .= '<div class="cb"></div>'; $i=0; }
							
								switch ($swatch_type) {
									// 1 color
									case 0:
										$output .= '<a href="javascript:void(0);" class="fl variant_color_outer_border '.(($current_selected == $id_product_variant_group_option) ? 'selected':'').'">
										<input type="radio" id="variant_color_'.$id_product_variant_group.'_'.$id_product_variant_group_option.'" name="variant_options['.$id_product_variant_group.']" value="'.$id_product_variant_group_option.'" '.(($current_selected == $id_product_variant_group_option) ? 'checked="checked"':'').' />
										<div class="variant_color_inner_border">
										<div style="background-color: '.$color.';" class="variant_color"></div>
										</div>
										</a>';
										break;
									// 2 color
									case 1:
										$output .= '<a href="javascript:void(0);" class="fl variant_color_outer_border '.(($current_selected == $id_product_variant_group_option) ? 'selected':'').'">
										<input type="radio" id="variant_color_'.$id_product_variant_group.'_'.$id_product_variant_group_option.'" name="variant_options['.$id_product_variant_group.']" value="'.$id_product_variant_group_option.'" '.(($current_selected == $id_product_variant_group_option) ? 'checked="checked"':'').' />
										<div class="variant_color_inner_border"><div class="variant_color">
										<div class="fl" style="width:10px; height:20px; background-color: '.$color.';"></div>
										<div class="fl" style="width:10px; height:20px; background-color: '.$color2.';"></div>																
										<div class="cb"></div>
										</div></div>
										</a>';
										break;
									// 3 color
									case 2:
										$output .= '<a href="javascript:void(0);" class="fl variant_color_outer_border '.(($current_selected == $id_product_variant_group_option) ? 'selected':'').'">
										<input type="radio" id="variant_color_'.$id_product_variant_group.'_'.$id_product_variant_group_option.'" name="variant_options['.$id_product_variant_group.']" value="'.$id_product_variant_group_option.'" '.(($current_selected == $id_product_variant_group_option) ? 'checked="checked"':'').' />	
										<div class="variant_color_inner_border"><div class="variant_color">															
										<div class="fl" style="width:7px; height:20px; background-color: '.$color.';"></div>
										<div class="fl" style="width:6px; height:20px; background-color: '.$color2.';"></div>
										<div class="fl" style="width:7px; height:20px; background-color: '.$color3.';"></div>
										<div class="cb"></div>
										</div></div>
										</a>';
										break;
									// file
									case 3:
										$output .= '<a href="javascript:void(0);" class="fl variant_color_outer_border '.(($current_selected == $id_product_variant_group_option) ? 'selected':'').'">
										<input type="radio" id="variant_color_'.$id_product_variant_group.'_'.$id_product_variant_group_option.'" name="variant_options['.$id_product_variant_group.']" value="'.$id_product_variant_group_option.'" '.(($current_selected == $id_product_variant_group_option) ? 'checked="checked"':'').' />	
										<div class="variant_color_inner_border"><div class="variant_color">		
										<img src="/images/products/swatch/'.$filename.'" width="20" height="20" border="0" hspace="0" vspace="0" />
										</div></div>
										</a>';
										break;																												
								}				
			
								++$i;					
								break;	
						}	
					}
									
					switch ($input_type) {
						// dropdown
						case 0:
							$output .= '</select>
							<script type="text/javascript">
							/* <![CDATA[ */
		
		
							jQuery(function(){
								jQuery("select[name=\'variant_options['.$id_product_variant_group.']\']").change(function(){
									
									// get values
									// ajax query to get new variants
									load_variant('.$id_product_variant_group.', jQuery(this).val());
									if (jQuery("#display_error").css("display") == "block"){
jQuery("#display_error").hide("blind", { direction: "vertical" }, 1000);
}
								});
							});
							
							/* ]]> */
							</script>';	
							break;
						// radio
						case 1:
							$output .= '<script type="text/javascript">
							/* <![CDATA[ */
		
							jQuery(function(){
								jQuery("input[name=\'variant_options['.$id_product_variant_group.']\']").change(function(){
		
									// get values
									// ajax query to get new variants
									load_variant('.$id_product_variant_group.', jQuery(this).val());
									if (jQuery("#display_error").css("display") == "block"){
jQuery("#display_error").hide("blind", { direction: "vertical" }, 1000);
}
								});
							});
							/* ]]> */
							</script>';				
							break;
						// swatch
						case 2:				
							$output .= '<script type="text/javascript">
							/* <![CDATA[ */
		
							jQuery(function(){
								jQuery("a.variant_color_outer_border").click(function(){
									if (!jQuery("input[id^=\'variant_color_\']:checked",this).length) { 
										jQuery("input[id^=\'variant_color_\']",this).attr("checked",true);
										
										jQuery("a.variant_color_outer_border").removeClass("selected");
										jQuery(this).addClass("selected");
										
										// get values
										// ajax query to get new variants
										load_variant('.$id_product_variant_group.', jQuery("input[id^=\'variant_color_\']",this).val());	
										if (jQuery("#display_error").css("display") == "block"){
jQuery("#display_error").hide("blind", { direction: "vertical" }, 1000);
}						
									}
								});
							});
							/* ]]> */
							</script>';
							break;
					}				
					
					$output .= '<div class="cb"></div></div>';
				}
				
				$result_group_option->free();
			} else {
				throw new Exception('An error occured while trying to get variant group options.'."\r\n\r\n".$mysqli->error);		
			}
			
			if (isset($variant_options[$id_product_variant_group]) && $variant_options[$id_product_variant_group] == 0) break;
		}	
	}
	
	return $output;	
}

// get images based on variant choices 
function get_variant_images($id_product, $variant_options=array())
{
	global $mysqli, $product;
	
	$groups=get_variant_groups($id_product);
	
	// build sql 
	// get all groups for this product
	if (sizeof($groups)) {
		$joins = array();	
		$where = array();		
		$group_by = array();	
		
		$i=1;
		foreach ($groups as $id_product_variant_group => $row_group) {	
			$where_str = array();
			
			$joins[] = 'INNER JOIN
			(product_image_variant_option AS pvo'.$i.')
			ON
			(product_image_variant.id = pvo'.$i.'.id_product_image_variant)';
		
			$where_str[] = 'pvo'.$i.'.id_product_variant_group = "'.$mysqli->escape_string($id_product_variant_group).'"';
			
			$stop=0;
			if (isset($variant_options[$id_product_variant_group]) && $variant_options[$id_product_variant_group]) {
				$id_product_variant_group_option = $variant_options[$id_product_variant_group];
				
				$where_str[] = 'pvo'.$i.'.id_product_variant_group_option = "'.$mysqli->escape_string($id_product_variant_group_option).'"';
			} else { 
				$where_str[] = 'pvo'.$i.'.id_product_variant_group_option = 0';
			}
			
			$where[] = implode(' AND ',$where_str);
			
			$i++;
			
			//if ($stop) break;
		}
		
		$joins = implode("\r\n",$joins);
		$where = implode(' AND ',$where);
		$group_by = implode(',',$group_by);
		
		$array=array();
						
		if ($result_options = $mysqli->query('SELECT
		product_image_variant_image.id, 
		product_image_variant_image.original, 
		product_image_variant_image.filename,
		product_image_variant_image.cover
		FROM
		product_image_variant
		INNER JOIN 
		product_image_variant_image
		ON
		(product_image_variant.id = product_image_variant_image.id_product_image_variant)
		
		'.$joins.'
		
		WHERE
		product_image_variant.id_product = "'.$mysqli->escape_string($id_product).'"
		AND
		'.$where.'
		ORDER BY 
		IF(product_image_variant_image.cover=1,-1,product_image_variant_image.sort_order) ASC')) {		
			// if we find variant images
			if ($result_options->num_rows) {
				while ($row_option = $result_options->fetch_assoc()) {
					$array[$row_option['id']] = $row_option;	
				}
			// if no images for specific variant
			} else {
				// loop through variant options, remove last until we find images for this variant 
				do {
					if (!sizeof($variant_options)) break;
					array_pop($variant_options);
				} while (!sizeof($array = get_variant_images($id_product, $variant_options)));
				
				// if none is found, load default product image
				if (!sizeof($array)) return $product['images'];
			}
		}
	}
	
	return $array;
}

function get_tier_prices($id_product, $id_product_variant=0)
{
	global $mysqli, $product;
	
	$tier_prices=array();
	$output='';				
	
	if ($result_tier_price = $mysqli->query('SELECT 
	product_price_tier.qty,
	calc_sell_price(product_price_tier.price,product_variant.price_type,product_variant.price,customer_type.percent_discount,customer_type.apply_on_rebate,rebate_coupon.discount_type,rebate_coupon.discount) AS price
	FROM 			            
	product_price_tier
	
	LEFT JOIN
	product_variant
	ON
	(product_variant.id = "'.$mysqli->escape_string($id_product_variant).'")
	
	LEFT JOIN
	customer_type
	ON
	(customer_type.id = "'.$mysqli->escape_string($_SESSION['customer']['id_customer_type']).'")
	
	LEFT JOIN
	(product CROSS JOIN rebate_coupon)
	ON
	(product_price_tier.id_product = product.id AND product.id_rebate_coupon = rebate_coupon.id)
	
	WHERE
	product_price_tier.id_product = "'.$mysqli->escape_string($id_product).'"
	AND
	(product_price_tier.id_customer_type = 0 OR product_price_tier.id_customer_type = customer_type.id) '.
	($product['max_qty_price_tier'] != 0 ? ' AND product_price_tier.qty <= "'.$mysqli->escape_string($product['max_qty_price_tier']).'" ':'').'
	
	ORDER BY 
	product_price_tier.qty ASC')) {
		while ($row_tier_price = $result_tier_price->fetch_assoc()) {
			if (isset($tier_prices[$row_tier_price['qty']]) && $tier_prices[$row_tier_price['qty']]['price'] > $row_tier_price['price'] || !isset($tier_prices[$row_tier_price['qty']])) {
				
				// if the total price is higher than the current price special price, remove it
				if ($product['current_price'] > $row_tier_price['price'] && $row_tier_price['price'] > 0) $tier_prices[$row_tier_price['qty']] = $row_tier_price;
				else if (isset($tier_prices[$row_tier_price['qty']])) unset($tier_prices[$row_tier_price['qty']]);
			} 
		}

		$result_tier_price->free();
	} else {
		throw new Exception('An error occured while trying to get tier prices.'."\r\n\r\n".$mysqli->error);	
	}
	
	if (sizeof($tier_prices)) {
		$output .= '<div class="tier_price_box">';
		$tier_prices_counter=0;
		foreach ($tier_prices as $row_tier_price) {
			$save = $product['current_price']-$row_tier_price['price'];
			$tier_prices_counter++;
			$output .= '<div'.(sizeof($tier_prices)>$tier_prices_counter?' style="margin-bottom:5px;"':'').'>'.language('product', 'LABEL_BUY').' <strong>'.$row_tier_price['qty'].'</strong> '.language('product', 'LABEL_FOR').' <span class="special_price"><strong>'.nf_currency($row_tier_price['price']).'</strong></span> '.language('product', 'LABEL_EACH_AND_SAVE_TOTAL').' <span class="saving_price"><strong>'.nf_currency($row_tier_price['qty']*$save).'</strong></span>
			</div>';
		}
		
		$output .= '</div><div class="cb"></div>';
	}	
	
	return $output;
}

function get_product_cover_image($id_product, $id_product_variant=0)
{
	global $mysqli;
	
	// get product image
	$cover_image = '';
	
	if ($id_product_variant) {
		$variant_sub_options = array();
		
		if ($result_variant_options = $mysqli->query('SELECT 
		product_variant_option.id_product_variant_group,
		product_variant_option.id_product_variant_group_option
		FROM
		product_variant_option
		INNER JOIN 
		product_variant_group
		ON
		(product_variant_option.id_product_variant_group = product_variant_group.id)
		WHERE
		product_variant_option.id_product_variant = "'.$mysqli->escape_string($id_product_variant).'"
		ORDER BY 
		product_variant_group.sort_order ASC')) {
			while ($row_variant_option = $result_variant_options->fetch_assoc()){
				$variant_options[$row_variant_option['id_product_variant_group']] = $row_variant_option['id_product_variant_group_option'];
			}
		}
		
		$result_variant_options->free();
		
		$images = get_variant_images($id_product, $variant_options);
		
		foreach ($images as $row_image) {
			$cover_image = $row_image['cover'] ? $row_image['filename']:$cover_image;	
		}									
	} else {
		if ($result_image = $mysqli->query('SELECT 
		id,
		original, 
		filename
		FROM 
		product_image
		WHERE
		id_product = "'.$mysqli->escape_string($id_product).'"
		AND 
		cover = 1
		LIMIT 1')) {
			if ($result_image->num_rows) {
				$row_image = $result_image->fetch_assoc();	
				
				$cover_image = $row_image['filename'];
			}
			
			$result_image->free();
		}													
	}
		
	return $cover_image;
}

function get_applicable_rebate_text()
{
	global $product, $mysqli, $current_datetime;
	
	/*
	Product price order and calculation
	===================================
	1 - Product price (base price, special price or tier price)
	2 - Customer type % off if any.
	
	Discounts order and calculation
	===============================
	1 - Cash or % off product price (Rebate)
	2 - Cash or % off product price (Coupon - if not applicable on sale, only products that do not have a special price/tier price or #1 rebate applied (customer type discount does not count))
	3 - Cash or % off product price (Related) - If we do it one day.
	4 - Buy or Get (Rebate or Coupon, not both, if not applicable on sale, only products that do not have a special price/tier price or #1 rebate applied (customer type discount does not count)
	5 - Cash or % off cart subtotal or first purchase (Rebate)
	6 - Cash or % off cart subtotal (Coupon, only if first purchase rebate is not applied.)*/	
	
	$output ='';
	$sell_price = $product['sell_price'];
	
	// check if we have special price or discount
	switch ($product['product_type']) {
		case 0:					
			if ($product['is_special_price']) {
				$output .= '<br /><div class="saving_price_size">'.language('product', 'LABEL_SPECIAL_PRICE').' <span id="reg_price">'.nf_currency($product['sell_price']).'</span></div><div class="saving_price_size saving_price">'.language('product', 'LABEL_SAVE').' <strong>'.nf_currency($product['price']-$product['sell_price']).'</strong></div>'.($product['special_price_to_date']!='0000-00-00 00:00:00'?'<div class="saving_price_size" style="margin-bottom:10px;"> '.language('product', 'LABEL_END_OF_REBATE').' <span class="special_price" style="font-weight:normal">'.df_date($product['special_price_to_date']).'</span></div>':'');
			}		
			break;
		case 1:
			$discount = 0;
			switch ($product['discount_type']) {
				// fixed
				case 0:	
					$discount = nf_currency($product['discount']);
					break;
				// percent
				case 1:
					$discount = nf_currency(($product['price']*$product['discount'])/100,2);
					$discount_pc = round($product['discount']).'%';
					break;											
			}			
			
			$output .= '<br /><div class="saving_price_size saving_price" style="margin-bottom:10px;">'.language('product', 'LABEL_SAVE_COMBO_DEAL',array('percent'=>'<strong>'.$discount.($discount_pc ? ' ('.$discount_pc.')':'').'</strong>')).'</div>';								
			break;
	}

	if (!$_SESSION['customer']['id_customer_type'] || $_SESSION['customer']['id_customer_type'] && $_SESSION['customer']['apply_on_rebate'] == 1) {		
		
		// check for customer type discount
		if (!$result_discount = $mysqli->query('SELECT 
		customer_type.percent_discount
		FROM 
		customer_type 
		WHERE
		customer_type.id = "'.$mysqli->escape_string($_SESSION['customer']['id_customer_type']).'"
		AND
		customer_type.percent_discount > 0')) throw new Exception('An error occured while trying to get customer discount.'."\r\n\r\n".$mysqli->error);	
		
		if ($result_discount->num_rows) {
			$row = $result_discount->fetch_assoc();
			
			$discount = round(($product['sell_price']*$row['percent_discount'])/100,2);
			$sell_price -= $discount;
			
			$output .= '<div class="saving_price_size saving_price" style="margin-bottom:10px;">'.language('product', 'LABEL_CUSTOMER_REBATE',array('percent'=>'<strong>'.nf_currency($discount).' ('.$row['percent_discount'].'%)</strong>')).'</div>';
		}
		$result_discount->free();
		
		if ($product['product_type'] == 0) {		
			// 1 - Cash or % off product price (Rebate)
			if (!$result_discount = $mysqli->query('SELECT
			rebate_coupon.discount_type,
			rebate_coupon.discount,
			rebate_coupon.end_date,
			rebate_coupon.max_qty_allowed
			FROM 
			rebate_coupon
			
			LEFT JOIN
			rebate_coupon_product
			ON
			(rebate_coupon.id = rebate_coupon_product.id_rebate_coupon AND rebate_coupon_product.id_product = "'.$mysqli->escape_string($product['id']).'")
			
			LEFT JOIN
			(product_category CROSS JOIN rebate_coupon_category)
			ON
			(rebate_coupon.id = rebate_coupon_category.id_rebate_coupon AND product_category.id_product = "'.$mysqli->escape_string($product['id']).'" AND rebate_coupon_category.id_category = product_category.id_category)
			
			WHERE
			rebate_coupon.coupon = 0 
			AND
			rebate_coupon.type = 0
			AND
			rebate_coupon.active = 1
			AND
			(
				rebate_coupon.end_date = "0000-00-00 00:00:00"
				OR
				"'.$mysqli->escape_string($current_datetime).'" BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
			)
			AND
			(rebate_coupon_product.id_rebate_coupon IS NOT NULL OR rebate_coupon_category.id_rebate_coupon IS NOT NULL)
			ORDER BY 
			(CASE rebate_coupon.discount_type
			WHEN 0 THEN
			(rebate_coupon.discount/"'.$mysqli->escape_string($sell_price).'")
			WHEN 1 THEN
			(rebate_coupon.discount/100)
			END) DESC
			LIMIT 1')) throw new Exception('An error occured while trying to get best %/$ rebate.'."\r\n\r\n".$mysqli->error);
			
			if ($result_discount->num_rows) {
				$row = $result_discount->fetch_assoc();	
				
				if ($row['max_qty_allowed'] != 0 && ($product['max_qty'] == 0 || $product['max_qty'] != 0 && $row['max_qty_allowed'] < $product['max_qty'])) {
					$product['max_qty'] = $row['max_qty_allowed'];
					$product['max_qty_price_tier'] = $row['max_qty_allowed'];
				}
				
				switch ($row['discount_type']) {
					// fixed
					case 0:
						$discount = ($row['discount'] > $sell_price ? $sell_price:$row['discount']);
						$discount_pc = round(($discount/$sell_price)*100).'%';
						$sell_price -= $discount;
						break;
					// percent
					case 1:									
						$discount = round(($sell_price*$row['discount'])/100,2);
						$discount_pc = round($row['discount']).'%';
						$sell_price -= $discount;
						break;	
				}
				
				$output .= '<div class="saving_price_size saving_price">'.language('product', 'LABEL_SAVE_ADDITIONAL',array('percent'=>'<strong>'.nf_currency($discount).' ('.$discount_pc.')</strong>')).'</div>'.($row['end_date'] && $row['end_date'] != '0000-00-00 00:00:00' ?'
				<div class="saving_price_size" style="margin-bottom:10px;">'.language('product', 'LABEL_END_REBATE').' <span class="special_price" style="font-weight:normal">'.df_date($row['end_date']).'</span></div>':'');	
			}
			$result_discount->free();
			
			// 4 - Buy or Get (Rebate or Coupon, not both, if not applicable on sale, only products that do not have a special price/tier price or #1 rebate applied (customer type discount does not count)
			if (!$result_discount = $mysqli->query('SELECT
			rebate_coupon.discount_type,
			rebate_coupon.discount,
			rebate_coupon.end_date,
			rebate_coupon.buy_x_qty,
			rebate_coupon.get_y_qty,
			rebate_coupon.max_qty_allowed					
			FROM 
			rebate_coupon
			
			LEFT JOIN
			rebate_coupon_product
			ON
			(rebate_coupon.id = rebate_coupon_product.id_rebate_coupon AND rebate_coupon_product.id_product = "'.$mysqli->escape_string($product['id']).'")
			
			LEFT JOIN
			(product_category CROSS JOIN rebate_coupon_category)
			ON
			(rebate_coupon.id = rebate_coupon_category.id_rebate_coupon AND product_category.id_product = "'.$mysqli->escape_string($product['id']).'" AND rebate_coupon_category.id_category = product_category.id_category)
					
			WHERE
			rebate_coupon.coupon = 0
			AND
			rebate_coupon.type = 2
			AND
			rebate_coupon.active = 1
			AND
			(rebate_coupon_product.id_rebate_coupon IS NOT NULL OR rebate_coupon_category.id_rebate_coupon IS NOT NULL)
			ORDER BY 
			(CASE rebate_coupon.discount_type
				WHEN 0 THEN
					(rebate_coupon.discount/"'.$mysqli->escape_string($sell_price).'")
				WHEN 1 THEN
					(rebate_coupon.discount/100)
			END) DESC
			LIMIT 1')) throw new Exception('An error occured while trying to get buy and get rebate.'."\r\n\r\n".$mysqli->error);
			
			if ($result_discount->num_rows) {
				$row = $result_discount->fetch_assoc();	
				
				$max_buy = ceil($row['max_qty_allowed']/($row['buy_x_qty']+$row['get_y_qty'])*$row['buy_x_qty']);
				
				if ($max_buy != 0 && ($product['max_qty'] == 0 || $product['max_qty'] != 0 && $max_buy < $product['max_qty'])) {
					$product['max_qty'] = $row['max_qty_allowed'];
					$product['max_qty_price_tier'] = $max_buy;
				}
				
				switch ($row['discount_type']) {
					// fixed
					case 0:
						$discount = nf_currency(($row['discount'] > $sell_price) ? $sell_price:$row['discount']);
						break;
					// percent
					case 1:			
						$discount = round($row['discount']).'%';
						break;	
				}
				
				$output .= '<div class="saving_price_size saving_price">'.language('product', 'LABEL_BUY').' <strong>'.$row['buy_x_qty'].'</strong> '.language('product', 'LABEL_AND_GET').' <strong>'.$row['get_y_qty'].'</strong> '.language('product', 'LABEL_AT').' <strong>'.$discount.'</strong> '.language('product', 'LABEL_OFF_EACH').'.</div>'.($row['end_date']!='0000-00-00 00:00:00'?'<div class="saving_price_size" style="margin-bottom:10px;">'.language('product', 'LABEL_END_OF_REBATE').' <span class="special_price" style="font-weight:normal">'.df_date($row['end_date']).'</span></div>':'');
			}
			$result_discount->free();
		}
	}
		
	return $output;
}

function get_max_qty()
{
	global $product;
	
	$output='';
	
	if ($product['in_stock'] && (!$product['has_variants'] || $product['has_variants'] && $product['id_product_variant']) && $product['max_qty']) {
		$output .= '<div class="limited_qty">'.language('product', 'LABEL_LIMIT_OF_PER_CUSTOMER',array(0=>$product['max_qty'])).'</div>';		
	}
	
	return $output;
}

function show_qty_remaining($id_product, $id_product_variant)
{
	global $config_site, $product;
	
	$output	='';
	
	switch ($product['product_type']) {
		case 0:
			if ($product['in_stock'] && $product['qty_in_stock'] <= $config_site['enable_show_qty_remaining_start_at'] && (($product['has_variants'] && $id_product_variant) || !$product['has_variants'])) {
				$output .= '<p class="availability in-stock">'.language('product', 'LABEL_IN_STOCK').' <span>'.$product['qty_in_stock'].'</span></p>';								
			}	
			break;			
	}
	
	return $output;
}

function get_add_cart_button_top()
{
	global $product, $modify_product, $_POST, $modify_product_info, $variant_options, $error_variant, $config_site, $mysqli,$url_prefix;
	
	$output = '';
	if($config_site['allow_add_to_cart'] && !$product['allow_add_to_cart_exceptions'] || !$config_site['allow_add_to_cart'] && $product['allow_add_to_cart_exceptions']){
		if (!$modify_product && $product['in_stock']) { 	
			if ($product['in_stock'] && !$product['qty_in_stock'] && $product['track_inventory'] && (!$product['has_variants'] || $product['has_variants'] && $product['id_product_variant'])) { 
				$output .= '<div class="fl special_price" style="margin-bottom:10px; font-size:20px;">'.language('product', 'ALERT_OUT_OF_STOCK').'</div>';
			} else if (!$product['has_variants'] || $product['has_variants'] && $product['id_product_variant'] || $product['has_variants'] && $config_site['display_multiple_variants_form']) {
				if ($product['downloadable']) {
					$output .= '<div class="fl"><strong>'.language('product', 'LABEL_QTY').'</strong>&nbsp;<input type="hidden" name="add_to_cart_qty" value="1" style="text-align:center; width: 30px;" class="check_qty default_one" />1</div>
					
				<div class="button_regular" style="margin-top:0px; margin-left:10px;"><input type="submit" value="'.language('product', 'BTN_ADD_TO_CART').'" class="button" name="_add_to_cart"'.($product['heavy_weight']?' onclick="javascript: alert(\''.language('product', 'ALERT_HEAVY_WEIGHT').'\');"':'').' /></div>';
				} else {							
					if ($product['has_variants'] && $config_site['display_multiple_variants_form']) {
						$output_array = array();
						
						if (!$result = $mysqli->query('SELECT COUNT(id) AS total FROM product_variant_group 
						WHERE id_product = "'.$product['id'].'"')) throw new Exception('An error occured while trying to get group count.');
						$row = $result->fetch_assoc();
						$total_groups = $row['total'];
						
						if (!$result = $mysqli->query('SELECT product_variant.id, product_variant_option.id_product_variant_group,
						product_variant_option.id_product_variant_group_option,
						product_variant_group.input_type, product_variant_group_option.swatch_type, 
						product_variant_group_option.color, product_variant_group_option.color2,
						product_variant_group_option.color3, product_variant_group_option.filename,
						product_variant_group_option_description.name AS option_name
						FROM product_variant 
						INNER JOIN product_variant_option ON (product_variant.id = product_variant_option.id_product_variant)
						INNER JOIN (product_variant_group CROSS JOIN product_variant_group_description)
						ON (product_variant_option.id_product_variant_group = product_variant_group.id AND product_variant_group.id = product_variant_group_description.id_product_variant_group AND product_variant_group_description.language_code = "'.$_SESSION['customer']['language'].'")
						INNER JOIN (product_variant_group_option CROSS JOIN product_variant_group_option_description)
						ON (product_variant_option.id_product_variant_group_option = product_variant_group_option.id AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option AND product_variant_group_option_description.language_code = "'.$_SESSION['customer']['language'].'")
						WHERE product_variant.id_product = "'.$product['id'].'" AND product_variant.active = 1 AND is_product_in_stock(product_variant.id_product,product_variant.id,0) = 1 ORDER BY product_variant.sort_order ASC, product_variant_group.sort_order ASC, product_variant_group_option.sort_order ASC')) throw new Exception('An error occured while trying to get variants.'); 
						
						$current_variant = '';
						$current_group = array();
						$tmparray = array();
						$i=1;
						while ($row = $result->fetch_assoc()) {												
							if ($current_variant != $row['id']) {
								$current_variant = $row['id'];	
							}
							
							if ($i < $total_groups) {
								//$output_array[$current_variant]['name'][] = $row;
								$current_group[] = $row['id_product_variant_group'].':'.$row['id_product_variant_group_option'];							
								$tmp_array[] = $row;	
							} else if ($i == $total_groups) {
								$key = implode(',',$current_group);
								
								if (!isset($output_array[$key])) $output_array[$key]['name'] = $tmp_array;
								
								$output_array[$key]['options'][] = $row;
								
								$i=0;	
								$tmp_array = array();
								$current_group = array();
							}
														
							++$i;
						}
						
						//echo '<pre>'.print_r($output_array,1).'</pre>';
						
						if (sizeof($output_array)) {
							$output .= '<div style="margin-bottom:10px;">';
							
							foreach ($output_array as $row) {
								if ($total_groups > 1) {								
									$output .= '<div style="margin-bottom:1px; padding: 3px;">';
									
									$output .= '<div class="fl" style="margin-right:10px;">';
									
									$tmparray = array();;
									foreach ($row['name'] as $row_name) {
										if ($row_name['input_type'] == 2) {
											switch ($row_name['swatch_type']) {
												// 1 color
												case 0:
													$tmparray[] = '<div class="variant_color_inner_border fl" style="margin-right:10px;"><div style="background-color: '.$row_name['color'].';" class="variant_color"></div></div>';
													break;
												// 2 color
												case 1:
													$tmparray[] = '<div class="variant_color_inner_border fl" style="margin-right:10px;"><div class="variant_color">
													<div class="fl" style="width:10px; height:20px; background-color: '.$row_name['color'].';"></div>
													<div class="fl" style="width:10px; height:20px; background-color: '.$row_name['color2'].';"></div>																
													<div class="cb"></div>
													</div></div>';
													break;
												// 3 color
												case 2:
													$tmparray[] = '<div class="variant_color_inner_border fl" style="margin-right:10px;"><div class="variant_color">															
													<div class="fl" style="width:7px; height:20px; background-color: '.$row_name['color'].';"></div>
													<div class="fl" style="width:6px; height:20px; background-color: '.$row_name['color2'].';"></div>
													<div class="fl" style="width:7px; height:20px; background-color: '.$row_name['color3'].';"></div>
													<div class="cb"></div>
													</div></div>';
													break;
												// file
												case 3:
													if ($row_name['filename']) {													
														$tmparray[] = '<div class="variant_color_inner_border fl" style="margin-right:10px;"><div class="variant_color">		
														<img src="/images/products/swatch/'.$row_name['filename'].'" width="20" height="20" border="0" hspace="0" vspace="0" />
														</div></div>';
													} else $tmparray[] = '<div class="fl" style="margin-right:10px; margin-top:5px;"><strong>'.$row_name['option_name'].'</strong></div>';
													break;																												
											}													
										} else $tmparray[] = '<div class="fl" style="margin-right:10px; margin-top:5px;"><strong>'.$row_name['option_name'].'</strong></div>';
									}
									
									$output .= implode(' ',$tmparray);
									
									$output .= '<div class="cb"></div></div>									
									<div style="width: 320px;margin:5px 0 10px -70px;"><div class="fr">';
									
									foreach ($row['options'] as $row_option) {
										$output .= '<div class="fl" style="margin-right: 5px; min-width: 100px; text-align: right;"><div class="fr" style="margin-left:5px;margin-top: -5px;"><input type="text" name="variants['.$row_option['id'].']" value="'.(isset($_POST['variants'][$row_option['id']]) ? $_POST['variants'][$row_option['id']]:0).'" style="text-align:center; width: 35px; margin-bottom: 5px; padding:3px;" class="check_qty" /></div>';
										
										if ($row_option['input_type'] == 2) {
											switch ($row_name['swatch_type']) {
												// 1 color
												case 0:
													$output .= '<div class="variant_color_inner_border fr"><div style="background-color: '.$row_option['color'].';" class="variant_color"></div></div>';
													break;
												// 2 color
												case 1:
													$output .= '<div class="variant_color_inner_border fr"><div class="variant_color">
													<div class="fl" style="width:10px; height:20px; background-color: '.$row_option['color'].';"></div>
													<div class="fl" style="width:10px; height:20px; background-color: '.$row_option['color2'].';"></div>																
													<div class="cb"></div>
													</div></div>';
													break;
												// 3 color
												case 2:
													$output .= '<div class="variant_color_inner_border fr"><div class="variant_color">															
													<div class="fl" style="width:7px; height:20px; background-color: '.$row_option['color'].';"></div>
													<div class="fl" style="width:6px; height:20px; background-color: '.$row_option['color2'].';"></div>
													<div class="fl" style="width:7px; height:20px; background-color: '.$row_option['color3'].';"></div>
													<div class="cb"></div>
													</div></div>';
													break;
												// file
												case 3:
													if ($row_option['filename']) {
														$output .= '<div class="variant_color_inner_border fr"><div class="variant_color">		
													<img src="/images/products/swatch/'.$row_option['filename'].'" width="20" height="20" border="0" hspace="0" vspace="0" />
													</div></div>';
													} else $row_option['option_name']; 
													break;																												
											}													
										} else $output .= $row_option['option_name'];
										
										$output .= '<div class="cb"></div></div>';
									}
									
									$output .= '</div><div class="cb"></div></div><div class="cb"></div></div>';
								} else {
									foreach ($row['options'] as $row_option) {
										$output .= '<div class="fl" style="line-height: 24px; margin-right: 5px; min-width: 70px; text-align: left;"><div class="fr" style="margin-left:5px;"><input type="text" name="variants['.$row_option['id'].']" value="'.(isset($_POST['variants'][$row_option['id']]) ? $_POST['variants'][$row_option['id']]:0).'" style="text-align:center; width: 30px; height: 24px; margin-bottom: 5px" class="check_qty" /></div>';
										
										if ($row_option['input_type'] == 2) {
											switch ($row_option['swatch_type']) {
												// 1 color
												case 0:
													$output .= '<div class="variant_color_inner_border fr"><div style="background-color: '.$row_option['color'].';" class="variant_color"></div></div>';
													break;
												// 2 color
												case 1:
													$output .= '<div class="variant_color_inner_border fr"><div class="variant_color">
													<div class="fl" style="width:10px; height:20px; background-color: '.$row_option['color'].';"></div>
													<div class="fl" style="width:10px; height:20px; background-color: '.$row_option['color2'].';"></div>																
													<div class="cb"></div>
													</div></div>';
													break;
												// 3 color
												case 2:
													$output .= '<div class="variant_color_inner_border fr"><div class="variant_color">															
													<div class="fl" style="width:7px; height:20px; background-color: '.$row_option['color'].';"></div>
													<div class="fl" style="width:6px; height:20px; background-color: '.$row_option['color2'].';"></div>
													<div class="fl" style="width:7px; height:20px; background-color: '.$row_option['color3'].';"></div>
													<div class="cb"></div>
													</div></div>';
													break;
												// file
												case 3:
													if ($row_option['filename']) {
														$output .= '<div class="variant_color_inner_border fr"><div class="variant_color">		
													<img src="/images/products/swatch/'.$row_option['filename'].'" width="20" height="20" border="0" hspace="0" vspace="0" />
													</div></div>';
													} else $output .= $row_option['option_name']; 
													break;																												
											}													
										} else $output .= $row_option['option_name'];
										
										$output .= '<div class="cb"></div></div>';
									}																		
								}
							}
							
							if ($total_groups == 1) $output .= '<div class="cb"></div>';
							
							$output .= '</div>
							<div class="button_regular" style="margin-top:0px; margin-left:5px;"><input type="submit" value="'.language('product', 'BTN_ADD_TO_CART').'" class="regular button" name="_add_to_cart"'.($product['heavy_weight']?' onclick="javascript: alert(\''.language('product', 'ALERT_HEAVY_WEIGHT').'\');"':'').' /></div>';
						}
					} else {				 
						$output .= '<div class="fl"><strong>'.language('product', 'LABEL_QTY').'</strong>&nbsp;<input type="text" name="add_to_cart_qty" value="'.(isset($_POST['add_to_cart_qty']) && $_POST['add_to_cart_qty'] > 0 ? $_POST['add_to_cart_qty']:(isset($modify_product_info['qty']) ? $modify_product_info['qty']:1)).'" style="text-align:center; width: 30px;" class="check_qty default_one" /></div>
					
				<div class="button_regular" style="margin-top:0px; margin-left:5px;"><input type="submit" value="'.language('product', 'BTN_ADD_TO_CART').'" class="regular button" name="_add_to_cart"'.($product['heavy_weight']?' onclick="javascript: alert(\''.language('product', 'ALERT_HEAVY_WEIGHT').'\');"':'').' /></div>';
					}
				}
			}
		} else if (!$product['in_stock'] && !$error_variant) {
			 $output .= '<div class="fl special_price" style="margin-bottom:10px; font-size:20px;">'.language('product', 'ALERT_OUT_OF_STOCK').'</div>';
		} 
	} else{
		
		
	
                $query = 'SELECT 
                            cmspage.id,
                            cmspage_description.alias,
                            cmspage_description.name
                            FROM cmspage
                            INNER JOIN cmspage_description ON cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$_SESSION['customer']['language'].'"
                            WHERE cmspage.id = 18';
                    
                if ($result = $mysqli->query($query)) {
                    $row = $result->fetch_assoc();
					$alias_contact = $row['alias'];
                }
				$result->free();

		
		
		
		$output .= '
		<div class="button_regular" style="margin-top:0px; margin-left:5px;"><input type="button" value="'.language('product', 'BTN_CONTACT_US').'" class="regular button" onclick="javascript:document.location.href=\''.$url_prefix.'page/'.$alias_contact.'\'" /></div>
		';
	}
		
	$output .= '<div class="cb"></div>';
	
	return $output;
}

function get_add_cart_button_bottom()
{
	global $product, $modify_product, $_POST, $modify_product_info, $variant_options, $id_cart_discount, $config_site, $mysqli,$url_prefix;
	
	$output = '';
	if($config_site['allow_add_to_cart'] && !$product['allow_add_to_cart_exceptions'] || !$config_site['allow_add_to_cart'] && $product['allow_add_to_cart_exceptions']){
		if ($modify_product || $product['in_stock']) { 	
			if (!$product['has_variants'] || ($product['has_variants'] && $product['id_product_variant'])) {
				if ($product['downloadable']) {
					$output .= '
				<div style="width:200px;">
					<div class="fr">
						<span style="font-size:16px;">'.language('product', 'LABEL_QTY').'</span>&nbsp;<input type="hidden" name="add_selected_to_cart_qty" value="1" /><span style="font-size:16px;">1</span>
					</div>
					<div class="cb"></div>
					<div class="button_add_update_item_cart">'.
					(!$modify_product ? '<input type="submit" name="_add_selected_to_cart" value="'.language('product', 'BTN_ADD_ITEM_TO_CART').'" class="previous_step button" />':'<input type="submit" name="_upd_selected" value="'.language('product', 'BTN_UPDATE_ITEM').'" class="previous_step button" />').'
					</div>
				</div>
				';
				} else {
					$output .= '
				<div style="width:200px;">
					<div class="fr">
						<span style="font-size:16px;">'.language('product', 'LABEL_QTY').'</span>&nbsp;'.($id_cart_discount ? '<input type="hidden" name="add_selected_to_cart_qty" value="'.$modify_product_info['qty'].'" /><span style="font-size:16px;">'.$modify_product_info['qty'].'</span>':'<input type="text" name="add_selected_to_cart_qty" value="'.((isset($_POST['add_selected_to_cart_qty']) && $_POST['add_selected_to_cart_qty'] > 0) ? $_POST['add_selected_to_cart_qty']:(isset($modify_product_info['qty']) && $modify_product_info['qty'] > 0 ? $modify_product_info['qty']:1)).'" style="text-align:center; width:35px" class="total_qty check_qty default_one" />').'
					</div>
					<div class="cb"></div>
					<div class="button_add_update_item_cart">'.
					(!$modify_product ? '<input type="submit" name="_add_selected_to_cart" value="'.language('product', 'BTN_ADD_ITEM_TO_CART').'" class="previous_step button" />':'<input type="submit" name="_upd_selected" value="'.language('product', 'BTN_UPDATE_ITEM').'" class="previous_step button" />').'
					</div>
				</div>
				';
				}
			}
		}
	}
		
	$output .= '<div class="cb"></div>';
	
	return $output;
}

$prices_js = array();

if ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) {
	$prices_js['product'] = array(
		'cost_price' => $product['cost_price'],
		'price' => $product['price'],
		'sell_price' => $product['current_price'],
		'discount_pc' => ($product['discount_type'] ? ($product['discount']/100):($product['price'] > 0 ? $product['discount']/$product['price']:0)),
	);
} else {
	$prices_js['product'] = array(
		'cost_price' => 0,
		'price' => 0,
		'sell_price' => 0,
		'discount_pc' => 0,
	);
}
$prices_js['additional_products'] = 0;

$qReviews = 'SELECT 
			product_review.title,
			product_review.rated,
			product_review.review,
			product_review.date_created,
			product_review.anonymous,
			product_review.approved,
			orders_item_product.id AS verify,
			CONCAT(customer.firstname," ",customer.lastname) AS customer_name
			FROM product_review
			INNER JOIN product ON product_review.id_product = product.id
			INNER JOIN customer ON product_review.id_customer = customer.id
			LEFT JOIN 
			(orders_item_product CROSS JOIN 
			orders_item CROSS JOIN 
			orders)
			ON 
			(product.id = orders_item_product.id_product AND 
			orders_item_product.id_orders_item = orders_item.id AND 
			orders_item.id_orders = orders.id AND orders.id_customer = customer.id)
			WHERE product_review.id_product = '.$product["id"].'
			GROUP BY product_review.id_customer
			ORDER BY product_review.date_created DESC';
$productReviews = $mysqli->query($qReviews);

$has_reviews = false;
$queryHasReview = 'SELECT id
		  FROM product_review
		  WHERE product_review.id_product = '.$product["id"].' AND product_review.id_customer = '.(int)$_SESSION['customer']['id'];
if ($result = $mysqli->query($queryHasReview)) {
  if($result->num_rows) $has_reviews = true;	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="<?php echo htmlspecialchars($product['meta_description']); ?>" />
<meta name="keywords" content="<?php echo htmlspecialchars($product['meta_keywords']); ?>" />
<link rel="canonical" href="<?php echo 'http://'.$_SERVER['HTTP_HOST'].$url_prefix.'product/'.$alias; ?>" /> 
<meta property="og:title" content="<?php echo $product['name']; ?>"/>
<meta property="og:image" content="http://<?php echo $_SERVER['HTTP_HOST'];?>/images/products/cover/<?php echo $product['images'][0]['filename']?>"/>
<meta property="og:title" content="<?php echo $product['name']; ?>"/>
<meta property="og:url" content="<?php echo 'http://'.$_SERVER['HTTP_HOST'].$url_prefix.'product/'.$alias; ?>"/>
<meta property="og:site_name" content="<?php echo $config_site['site_name']; ?>"/>

<title><?php echo $product['name'].' - '.$config_site['site_name'];?></title>
<?php include("_includes/template/header.php");?>

<script type="text/javascript">
<!--
var product_price = <?php echo $product['current_price']; ?>;
var add_products_price = 0;

jQuery(function(){	
	
	jQuery("input.additional_items_checkbox").change(function(){
		if (jQuery(this).prop("checked")) {
			jQuery(":disabled",jQuery(this).parents("div.additional_items_parent")).removeAttr("disabled");
			jQuery(this).parents("div.additional_items_parent").addClass("selected");			
		} else {
			jQuery(".additional_items_option",jQuery(this).parents("div.additional_items_parent")).attr("disabled","disabled");
			jQuery(this).parents("div.additional_items_parent").removeClass("selected");
			jQuery("span.error",jQuery(this).parents("div.additional_items_parent")).parent('p').remove();
			jQuery(".additional_items_option",jQuery(this).parents("div.additional_items_parent")).removeClass("error");
		}		
		
		update_selected_price();
	});

	jQuery(".additional_items_option").bind({
		change: update_selected_price	
	});
	
	// load calendars if we have any
	jQuery(".calendar_date").datepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: "/includes/js/dhtmlx/imgs/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar",
			constrainInput: true,
			beforeShow: function(input, inst) { if (jQuery(input).prop("disabled")) return false; }			
	});	
	
	jQuery(".calendar_datetime").datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm",
			showOn: "button",
			buttonImage: "/includes/js/dhtmlx/imgs/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar",
			constrainInput: true,
			beforeShow: function(input, inst) { if (jQuery(input).prop("disabled")) return false; }			
	});		
	
	jQuery(".calendar_time").timepicker({
			timeFormat: "hh:mm",
			showOn: "button",
			buttonImage: "/includes/js/dhtmlx/imgs/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar",
			constrainInput: true,
			beforeShow: function(input, inst) { if (jQuery(input).prop("disabled")) return false; }			
	});
	
	bind_checkqty();	
	
	jQuery(".related_product_variant").on("change",function(){
		var id_related_product = parseInt(jQuery(".additional_items_checkbox",jQuery(this).parents("div.additional_items_parent")).val()); 
		id_related_product = isNaN(id_related_product) ? 0:id_related_product;
		
		var id_product_variant = parseInt(jQuery(this).val());
		id_product_variant = isNaN(id_product_variant) ? 0:id_product_variant;
		
		if (id_product_variant) {			
			var sell_price = parseFloat(product_prices.related_products[id_related_product].variants[id_product_variant].sell_price);
			sell_price = isNaN(sell_price) ? 0:sell_price;
			var save = parseFloat(product_prices.related_products[id_related_product].variants[id_product_variant].save);
			save = isNaN(save) ? 0:save;
		} else {
			var sell_price = parseFloat(product_prices.related_products[id_related_product].sell_price);
			sell_price = isNaN(sell_price) ? 0:sell_price;
			var save = parseFloat(product_prices.related_products[id_related_product].save);
			save = isNaN(save) ? 0:save;
		}
		
		jQuery(".related_product_price",jQuery(this).parents("div.additional_items_parent")).html("").append(<?php echo nf_currency_js('sell_price'); ?>);
		//jQuery(".related_product_save",jQuery(this).parents("div.additional_items_parent")).html("").append(<?php echo nf_currency_js('save'); ?>);		 
		
		update_selected_price();
	});
	
	
	<?php
	if(($_GET['review_on'] and is_numeric($_GET['review_on'])) and $_SESSION['customer']['id']){
		echo 'open_review('.$_GET['review_on'].',\'login\',\'\');';
	}
	?>
	
});

<?php switch ($product['product_type']) { 
// combo
case 1:
?>
jQuery(function(){
	jQuery(".combo_product_variant,.additional_qty").bind({
		change: update_combo_price
	});				
});

function update_combo_price()
{
	var price = 0;
	var discount = 0;
	var discount_pc = parseFloat(product_prices.product.discount_pc);
	var total = 0;
	var itemCount=0;
	var add_total=0;
	
	jQuery.each(jQuery(".combo_product"),function(index,value){				
		var id = parseInt(jQuery(this).attr("id").replace(/combo_product_/,''));
		id = isNaN(id) ? 0:id;
		var id_product_variant = parseInt(jQuery("[id='combo_product_variant_"+id+"']").val());
		id_product_variant = isNaN(id_product_variant) ? 0:id_product_variant;
		var qty = parseInt(jQuery("input:hidden[name='combo_product["+id+"][qty]']").val());				
		qty = isNaN(qty) ? 0:qty;
		
		if (id_product_variant) {
			var current_price = qty*parseFloat(product_prices.combo_products[id][id_product_variant].price);
			var current_sell_price = parseFloat(product_prices.combo_products[id][id_product_variant].sell_price);
		} else {
			var current_price = qty*parseFloat(product_prices.combo_products[id].price);
			var current_sell_price = parseFloat(product_prices.combo_products[id].sell_price);
			
		}
		
		price += current_price;
		
		if (jQuery("input[name='combo_product["+id+"][add_qty]']").length) {
			var add_qty = parseInt(jQuery("input[name='combo_product["+id+"][add_qty]']").val());
			if (!isNaN(add_qty) && add_qty > 0) {
				add_total += add_qty*current_sell_price;
			}
		}		
		
		jQuery("#combo_product_price_"+id).html("").append(<?php echo nf_currency_js('current_price'); ?>);
		jQuery("#combo_product_additional_price_"+id).html("").append(<?php echo nf_currency_js('current_sell_price'); ?>);
	});
	
	discount = parseFloat(number_format(price*discount_pc,2,'.',''));
	total = price-discount;
	
	jQuery("#combo_subtotal").html("").append(<?php echo nf_currency_js('price'); ?>);
	jQuery("#combo_discount").html("").append(<?php echo nf_currency_js('discount'); ?>);
	jQuery("#combo_total").html("").append(<?php echo nf_currency_js('total'); ?>);
	jQuery("#combo_additional_total").html("").append(<?php echo nf_currency_js('add_total'); ?>);
	
	product_price = total;	
	product_prices.product.sell_price = total;
	add_products_price = add_total;
	update_selected_price();
}
<?php
	break;
// bundle
case 2: 
?>
jQuery(function(){
	jQuery(".bundle_option,.bundle_option_qty").bind({
		change: update_bundle_price
	});		
});

function update_bundle_price()
{
	var price = 0;
	var itemCount=0;
	
	jQuery.each(jQuery(".bundle_group"),function(index,value){				
		var id = parseInt(jQuery(this).attr("id").replace(/id_product_bundle_group_/,''));
		id = isNaN(id) ? 0:id;
		var type = jQuery("input:hidden[name='bundle_product["+id+"][input_type]']").val();
		var current_price = 0;
		
		switch (type) {
			// dropdown
			case '0':
				break;	
			// multi select
			case '3':
				break;
			// radio
			case '1':
				if (jQuery("input[name^='bundle_product["+id+"][id]']:checked").length) {
					var selected_id = jQuery("input[name^='bundle_product["+id+"][id]']:checked").val();	
					
					if (selected_id) {
						var qty = parseInt(jQuery("input[name='bundle_product["+id+"][qty]["+selected_id+"]']").length ? jQuery("input[name='bundle_product["+id+"][qty]["+selected_id+"]']").val():1);
						qty = isNaN(qty) ? 1:qty;
						current_price = qty*parseFloat(product_prices.bundle_products[id][selected_id].sell_price);
					}
					
					price += current_price;					
				}	
				
				jQuery("#bundle_product_"+id+"_price").html("").append(<?php echo nf_currency_js('current_price'); ?>);				
				break;
			// checkbox
			case '2':	
				if (jQuery("input[name^='bundle_product["+id+"][id]']:checked").length) {				
					jQuery.each(jQuery("input[name^='bundle_product["+id+"][id]']:checked"),function(index,value){ 					
						var selected_id = jQuery(this).val();	
					
						if (selected_id) {
							var qty = parseInt(jQuery("input[name='bundle_product["+id+"][qty]["+selected_id+"]']").length ? jQuery("input[name='bundle_product["+id+"][qty]["+selected_id+"]']").val():1);
							qty = isNaN(qty) ? 1:qty;
							current_price += qty*parseFloat(product_prices.bundle_products[id][selected_id].sell_price);
						}
					});
					
					price += current_price;					
				}	

				jQuery("#bundle_product_"+id+"_price").html("").append(<?php echo nf_currency_js('current_price'); ?>);				
				break;			
		}		
	});
	
	jQuery("#bundle_price").html("").append(<?php echo nf_currency_js('price'); ?>);
	
	product_price = price;
	product_prices.product.sell_price = price;
	update_selected_price();
}
<?php 
	break;
} ?>

function update_selected_price()
{
	var featured_product_price = parseFloat(product_prices.product.sell_price);
	var extra_total = 0;
	var add_products_total = parseFloat(product_prices.additional_products);
	var grand_total = 0;
	
	jQuery.each(jQuery("input.additional_items_checkbox"),function(index,value){
		var id = parseInt(jQuery(this).val());
		id = isNaN(id) ? 0:id;
		var checked = jQuery(this).prop("checked");
		
		// options
		if (jQuery(this).prop('name').search(/add_option/i) != -1) {
			var input_type = jQuery("input:hidden[name='options["+id+"][input_type]']").val();
			
			switch (input_type) {
				// dropdown
				case '0':
					if (jQuery("select[name^='options["+id+"][id]'] option:selected").length) {
						var selected_id = jQuery("select[name^='options["+id+"][id]'] option:selected").val();	
						
						if (selected_id && checked) {
							var current_price = parseFloat(product_prices.options[id][selected_id].sell_price);
							current_price = isNaN(current_price) ? 0:current_price;
							extra_total += current_price;
						}
					}
					break;	
				// radio
				case '1':
					if (jQuery("input[name^='options["+id+"][id]']:checked").length) {
						var selected_id = jQuery("input[name^='options["+id+"][id]']:checked").val();	
						
						if (selected_id && checked) {
							var current_price = parseFloat(product_prices.options[id][selected_id].sell_price);
							current_price = isNaN(current_price) ? 0:current_price;
							
							if (jQuery("input[name='options["+id+"][qty]["+selected_id+"]']").length) {
								var qty = parseInt(jQuery("input[name='options["+id+"][qty]["+selected_id+"]']").val());
								qty = isNaN(qty) ? 0:qty;
								
								current_price = qty*current_price;
							}
							
							extra_total += current_price;
						}
					}					
					break;
				// checkbox	
				case '3':						
					if (jQuery("input[name^='options["+id+"][id]']:checked").length) {
						jQuery.each(jQuery("input[name^='options["+id+"][id]']:checked"),function(index,value){ 
							var selected_id = jQuery(this).val();	
						
							if (selected_id && checked) {
								var current_price = parseFloat(product_prices.options[id][selected_id].sell_price);
								current_price = isNaN(current_price) ? 0:current_price;
								
								if (jQuery("input[name='options["+id+"][qty]["+selected_id+"]']").length) {
									var qty = parseInt(jQuery("input[name='options["+id+"][qty]["+selected_id+"]']").val());
									qty = isNaN(qty) ? 0:qty;
									
									current_price = qty*current_price;
								}								
								
								extra_total += current_price;
							}
						});
					}						
					break;
				// multi-select
				case '4':						
					if (jQuery("select[name^='options["+id+"][id]'] option").length) {
						jQuery.each(jQuery("select[name^='options["+id+"][id]'] option:selected"),function(index,value){ 
							var selected_id = jQuery(this).val();	
						
							if (selected_id && checked) {
								var current_price = parseFloat(product_prices.options[id][selected_id].sell_price);
								current_price = isNaN(current_price) ? 0:current_price;
								extra_total += current_price;
							}
						});
					}						
					break;
				// textfield
				case '5': 
				// textarea
				case '6':
				// file
				case '7':
				// date
				case '8':
				// datetime
				case '9':			
				// time
				case '10':
					if (jQuery("input[name^='options["+id+"][id]']").length) {
						var selected_id = jQuery("input[name^='options["+id+"][id]']").val();	
						
						if (selected_id && checked) {
							var current_price = parseFloat(product_prices.options[id][selected_id].sell_price);
							current_price = isNaN(current_price) ? 0:current_price;
							extra_total += current_price;
						}
					}						
					break;
				
			}				
		// related products
		} else {
			if (checked) {
				var current_price = parseFloat(product_prices.related_products[id].sell_price);
				
				// if we have variants
				if (product_prices.related_products[id].variants) {
					var id_product_variant = parseInt(jQuery("select[name='add_related_product["+id+"][id_product_variant]']").val());
					id_product_variant = isNaN(id_product_variant) ? 0:id_product_variant;
						
					if (id_product_variant) {
						var sell_price = parseFloat(product_prices.related_products[id].variants[id_product_variant].sell_price);
						sell_price = isNaN(sell_price) ? 0:sell_price;
						var save = parseFloat(product_prices.related_products[id].variants[id_product_variant].save);
						save = isNaN(save) ? 0:save;	
						
						//current_price = sell_price-save;					
						current_price = sell_price;
					} else {
						current_price = 0;	
					}
				}
								
				current_price = isNaN(current_price) ? 0:current_price;
				add_products_total += current_price;	
			}
		}
	});
	
	grand_total += featured_product_price+extra_total+add_products_total;
	
	jQuery("#featured_product_price").html("").append(<?php echo nf_currency_js('featured_product_price'); ?>);
	jQuery("#extra_total").html("").append(<?php echo nf_currency_js('extra_total'); ?>);
	jQuery("#add_products_total").html("").append(<?php echo nf_currency_js('add_products_total'); ?>);
	jQuery("#grand_total").html("").append(<?php echo nf_currency_js('grand_total'); ?>);			
}


function load_variant(id_product_variant_group, id_product_variant_group_option)
{	
	// remove sub variants when selecting another variant option
	remove=0;
	jQuery("[id^='variant_group_']").each(function(){
		var id = jQuery(this).attr("id");
		id = id.replace(/variant_group_/,'');
		
		if (remove) jQuery(this).remove();
		
		if (id_product_variant_group == id) remove = 1
	});
	
	if (id_product_variant_group_option > 0) {	
		// get variant options
		jQuery.ajax({
			url: "<?php echo $url_alias.($id_cart_item ? '?id_cart_item='.$id_cart_item:''); ?>",
			data: jQuery("#product_form [id^='variant_group_'] *").serialize()+"&task=get_variant_options",		
			dataType: "json",
			error: function(jqXHR, textStatus, errorThrown) { 
				alert(jqXHR.responseText);
			},
			success: function( data ) {
				if(data) {															
					// variant options
					if (data.variant_options.length) jQuery("#variant_group_"+id_product_variant_group).after(data.variant_options);
	
					// images
					if(data.images!=""){
						var i=0;
						var html_output='<ul class="elastislide-list" id="moreimages-slider">';
						jQuery.each(data.images, function(key, value){
							if(i == 0) {
								jQuery('.product-image a.cloud-zoom img').attr('src','/images/products/zoom/'+value.filename);
							}
							
							html_output +='<li style="width: 100%; max-width: 97px; max-height: 155px;">';				                
							html_output +='<a rel="useZoom:\'product-zoom-168\', smallImage: \'/images/products/cover/'+value.filename+'" title="" class="cloud-zoom-gallery" data-lightbox="lightboxgroup-168" href="/images/products/zoom/'+value.filename+'">';
							html_output +='<img alt="" src="/images/products/cover/'+value.filename+'"></a></li>';
							i++;
						});
						html_output += '<ul>';		
						jQuery('.more-images').html(html_output);
						if(i<=1) {
							jQuery('.more-images').hide();
						} else {
							jQuery('.more-images').show();
							image_es = jQuery('#moreimages-slider').elastislide(
							        {
					            orientation : 'vertical',
					            minItems: Math.min(3,i)
					        });
					        jQuery('#moreimages-slider li a').hover(function() {
						        var rel = jQuery(this).attr('rel') + "'";
						        eval("var opts = {"+rel+"}");  
						        var img = opts.smallImage;
						        jQuery('#wrap .cloud-zoom img').attr('src',img);
						    });
						}
							
					}
					
					// show regular price, used when on sale
					if (jQuery("#reg_price").length) jQuery("#reg_price").html("").append(<?php echo nf_currency_js('data.info.price'); ?>);
					else jQuery("#reg_price").html("");
					
					// always show sell price
					if (jQuery("#sell_price").length) jQuery("#sell_price").html("").append(<?php echo nf_currency_js('data.info.current_price'); ?>);
					else jQuery("#sell_price").html("");
					
					// always show sku
					if (jQuery("#sku_current").length) jQuery("#sku_current").html("").append(data.info.sku);
					else jQuery("#sku_current").html("");
					
					// show total discount on current price if on sale													
					if (data.info.total_discount) jQuery("#saving").html("").append(<?php echo nf_currency_js('data.info.total_discount'); ?>);
					else jQuery("#saving").html("");								
					
					// show additional rebate info if any
					if (data.info.rebate_info && data.info.rebate_info.length) jQuery("#rebate").html("").append(data.info.rebate_info);
					else jQuery("#rebate").html("");
					
					// show max qty allowed in cart if any
					if (data.info.max_qty_info && data.info.max_qty_info.length) jQuery("#max_qty").html("").append(data.info.max_qty_info);
					else jQuery("#max_qty").html("");
					
					if (data.info.qty_remaining_info && data.info.qty_remaining_info.length) jQuery("#qty_remaining").html("").append(data.info.qty_remaining_info);
					else jQuery("#qty_remaining").html("");
					
					// show tier prices if any
					if (data.tier_prices && data.tier_prices.length) jQuery("#tier_prices").html("").append(data.tier_prices);			
					else jQuery("#tier_prices").html("");
					
					// show add to cart top
					if (data.info.add_to_cart_top && data.info.add_to_cart_top.length) jQuery("#add_to_cart_top").html("").append(data.info.add_to_cart_top);
					else jQuery("#add_to_cart_top").html("");
					
					// show add to cart bottom
					if (data.info.add_to_cart_bottom && data.info.add_to_cart_bottom.length) jQuery("#add_to_cart_bottom").html("").append(data.info.add_to_cart_bottom);
					else jQuery("#add_to_cart_bottom").html("");			
					
					bind_checkqty();
					
					// update product price
					product_prices.product.price = parseFloat(data.info.price);
					product_prices.product.sell_price = parseFloat(data.info.current_price);
					
					update_selected_price();
				}
			}
		});	
	} else jQuery("#add_to_cart_top, #add_to_cart_bottom").html("");
}

function bind_checkqty(){
	jQuery(".check_qty").keyup(function(){
		jQuery(this).val(check_qty(jQuery(this).val()));	
		jQuery(this).trigger("change");
	});	
	jQuery(".default_one").keyup(function(){
		jQuery(".default_one").val(check_qty(jQuery(this).val()));	
		jQuery(this).trigger("change");
	});	
	jQuery(".default_one").blur(function(){
		if(jQuery(this).val()==""){
			jQuery(".default_one").val(1);
		}	
	});		
}

function check_qty(str){
	var new_number = str.replace(/[^0-9]/g, "");
	return (isNaN(new_number) && new_number != "") ? 1:new_number;
}

jQuery.noConflict();
</script>
<script type="text/javascript" src="/_includes/js/prototype-misc.js"></script>
<script type="text/javascript" src="/_includes/js/jquery/jquery-ui/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="/_includes/js/jquery/timepicker/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="/_includes/js/jquery/i18n/jquery.ui.datepicker-<?php echo $_SESSION['customer']['language'];?>.js"></script>

<?php 
$google_analytics_content = '_gaq.push(["_setCustomVar",1,"SKU","'.$product['sku'].'", 3]);'."\r\n".
($product['brand'] ? '_gaq.push(["_setCustomVar",1,"BRAND","'.$product['brand'].'", 3]);'."\r\n":'');

include("_includes/template/google_analytics.php");?>

<style>
	input[name=_add_to_cart] {margin-left: 10px;}	
	.cb {clear:both;}
</style>
</head>
<body class="bv3">
<?php include("_includes/template/top.php");?>
<div class="main-container">
	<div class="breadcrumbs">
    	<div class="container">
        	<ul>
            	<li class="home">
                	<a title="<?php echo language('global', 'BREADCRUMBS_HOME');?>" href="<?php echo $url_prefix;?>"><?php echo language('global', 'BREADCRUMBS_HOME');?></a>
                    <span>&gt; </span>
                </li>
                <li class="product">
                	<strong><?php echo $product['name'];?></strong>                	
                </li>
            </ul>
	    </div>
	</div>
    <div class="main">
    	<div class="container" style="position:relative;">
        <div style="position:absolute; top: 10px; right: 10px;"><strong>SKU :</strong> <?php echo $product['sku'];?></div>	
           <div class="main-content" id="product_detail">
           
           		           		
           		<?php if($_GET['success'] == 'add_wishlist') {?>
           		<div class="messages">
           			<div class="alert alert-success success-msg">
           			<button type="button" class="close" data-dismiss="alert">×</button>
           			<ul><li><span><?php echo language('product', 'ALERT_ADDED_WISHLIST');?></span></li></ul>
           			</div>
           		</div> 
           		<?php }?> 
           		
           		<?php if($_GET['error'] == 'product_exist_wishlist') {?>
           		<div class="messages">
           			<div class="alert alert-danger">
           			<button type="button" class="close" data-dismiss="alert">×</button>
           			<ul><li><span><?php echo language('product', 'ALERT_ALREADY_ADDED_WISHLIST');?></span></li></ul>
           			</div>
           		</div> 
           		<?php }?>            		
           		
           		<?php if (sizeof($errors)) { ?>  
           		<div class="messages">
           			<div class="alert alert-danger">
	           			<button type="button" class="close" data-dismiss="alert">×</button>
	           			<ul>              
		                    <?php foreach ($errors as $error) {?> 
		                    <li><span>               
		                    	<?php echo $error;?>
		                    </span></li>               
		                    <?php }?>
	                	</ul>
           			</div>
           		</div> 
                <?php } ?>    
                                                 
				<script type="text/javascript">
				//<![CDATA[
				var ETERNAL_AJAXCART_PROCESSING = "<?php echo language('global', 'LABEL_ETERNAL_AJAXCART_PROCESSING');?>";
				var ETERNAL_AJAXCART_SOMETHING_BAD = "<?php echo language('global', 'LABEL_ETERNAL_AJAXCART_SOMETHING_BAD');?>";
				var ETERNAL_AJAXCART_SELECT_OPTIONS = "Please Select Options";
				var ETERNAL_AJAXCART_ERROR = { bg_color: '#F66F82', color: '#000'};
				//var ETERNAL_AJAXCART_INFO = { bg_color: '#7bae23', color: '#ffffff'};
				var ETERNAL_AJAXCART_INFO = { bg_color: '#444645', color: '#e8e8e8'};
				var ETERNAL_AJAXCART_WARN = { bg_color: '#444645', color: '#e8e8e8'};
				var ETERNAL_AJAX_CART = "Add to Cart";
				var ETERNAL_AJAX_WISHLIST = "Add to Wishlist";
				var ETERNAL_AJAX_COMPARE = "Add to Compare";
				//]]>
				</script>
				
				<div id="messages_product_view"></div>
				<form method="post" enctype="multipart/form-data" id="product_form" action="<?php echo $url_alias.($id_cart_item ? '?id_cart_item='.$id_cart_item:''); ?>">
				<div class="product-view">
				    <div class="product-essential">
				        <input type="hidden" name="form_uid" value="<?php echo $form_uid; ?>" />
				        <div class="no-display">
				            <input name="product" value="168" type="hidden">
				            <input name="related_product" id="related-products-field" value="" type="hidden">
				        </div>				        
				        <div class="product-essential-inner row">
				        	<!-- start: product-img-box -->
				            <div class="col-sm-7 product-img-box">				                
								<div class="clearfix">	
									<!-- start: more-images -->	   
									<?php 
										if (isset($variant_options) && is_array($variant_options) && sizeof($variant_options)) $product['images'] = get_variant_images($id_product, $variant_options);
					        		?>     						
				        			<div class="more-images" <?php if(count($product['images'])<=1){?> style="display:none;" <?php }?>>        
				        				<ul class="elastislide-list" id="moreimages-slider">
					        				<?php 
					        				// if we have variants already selected, load images if any
                            				$countImg = 0;
					        				if (sizeof($product['images'])) { 
			                                	foreach ($product['images'] as $row_image) { 
			                                    	if(is_file(dirname(__FILE__).'/images/products/cover/'.$row_image['filename'])){
			                                    		$countImg++;
			                                ?>
					        				<li style="width: 100%; max-width: 97px; max-height: 155px;">				                
							                    <a rel="useZoom:'product-zoom-168', smallImage: '/images/products/cover/<?php echo $row_image['filename']; ?>'" title="" class="cloud-zoom-gallery" data-lightbox="lightboxgroup-168" href="/images/products/zoom/<?php echo $row_image['filename']; ?>">
							                        <img alt="" src="/images/products/cover/<?php echo $row_image['filename']; ?>">
							                    </a>				                    
					                		</li>
					                		<?php } } } else {?>	
					                			<li style="width: 100%; max-width: 97px; max-height: 155px;">				                
    							                    <a rel="useZoom:'product-zoom-168', smallImage: '<?php echo get_blank_image('cover'); ?>'" title="" class="cloud-zoom-gallery" data-lightbox="lightboxgroup-168" href="<?php echo get_blank_image('cover'); ?>">
    							                        <img src="<?php echo get_blank_image('cover'); ?>" title=""/>
					                				</a>			                    
    					                		</li>
					                		<?php }?>				                		
				                		</ul>
				   					</div>				   					
				   					<!-- end: more-images -->	
				   					
				   					<!-- start: product-image -->			
					    			<div class="product-image">				            
					            		<div id="wrap" style="top:0px;z-index:9999;position:relative;" onclick="javascript:event.preventDefault();jQuery('.more-images .cloud-zoom-gallery').first().click();">
					            			<a style="position: relative; display: block;" href="" class="cloud-zoom" id="product-zoom-168" rel="position:'inside',lensOpacity:0.5,smoothMove:3,showTitle:1,titleOpacity:0.5,zoomWidth:500,zoomHeight:300,adjustX:0,adjustY:0,tint:'1',tintOpacity:0.5">
					                			<img style="display: block;" src="" alt="<?php echo $product['name']; ?>" title="<?php echo $product['name']; ?>">
					            			</a>
					            			<div class="mousetrap" style="width: 430px; height: 602px; top: 0px; left: 0px; position: absolute; z-index: 9999;"></div>
					            		</div>
					            		<?php
										 	if ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) {
										?>
					            		<div class="price-box"> 
					            			<?php if ($product['price'] != $product['current_price']) {?>
					                    	<p class="old-price">
					                			<span class="price-label"><?php echo language('product', 'LABEL_REGULAR_PRICE');?>:</span>
					                			<span class="price" id="sell_price">
					                    			<?php echo nf_currency($product['price']);?>
					                    			<!-- <span class="sub">.11</span> -->
					                    		</span>
					            			</p>
					                        <p class="special-price">
								                <span class="price-label"><?php echo language('product', 'LABEL_ON_SALE')?>:</span>
								                <span class="price" id="reg_price">
								                    <?php echo nf_currency($product['current_price']);?>
								                    <!-- <span class="sub">.99</span> -->
								                </span>
								            </p>
								            <?php } else {?>
		                    				<span class="regular-price">	                                    		
	                                    		<span class="price" id="reg_price">
	                                    			<?php echo nf_currency($product['current_price']);?>		                                    		
		                                    		<!--  span class="sub">.50</span>-->
	                                    		</span>
	                                   		</span>
	                                   		<?php }?>			                        
					        			</div>	
					        			<?php }?>			
				        			</div>
				        			 <!-- end: product-image --> 
								</div>				
								<script type="text/javascript">
								//<![CDATA[
								jQuery(function($) {
								    var image_es;
								    var zoom_timer;
								        
								    function resize_cloudzoom() {
								        jQuery(".more-images .cloud-zoom-gallery").first().trigger('mouseenter'); 
								        jQuery(".more-images .cloud-zoom-gallery").first().trigger('touchstart'); 
								        if (image_es) image_es.destroy();								        					        
								        image_es = jQuery('#moreimages-slider').elastislide(
										        {
								            orientation : 'vertical',
								            minItems: <?php echo min(3,count($product['images']));?>
								        });								        				       
								        if (zoom_timer) clearTimeout(zoom_timer);
								    }
								    jQuery(window).load(resize_cloudzoom);
								    jQuery(window).resize(function() {
								        clearTimeout(zoom_timer);
								        zoom_timer = setTimeout(resize_cloudzoom, 200); 
								    });
								});
								//]]>															
								</script>
				            </div>
				            <!-- end: product-img-box -->
				            
				            <div class="col-sm-5 product-shop">
				                <div class="product-name">
				                    <h1><?php echo $product['name'] . ($product['used']?'<span style="font-size:18px; font-style:italic;">'.language('global', 'LABEL_USED').'</span>':''); ?></h1>
				                </div>
				                
				                <!-- start: reviews -->
				                <?php if($config_site['display_menu_rate_product']){?>                
				                <div class="ratings">
            				        <div class="rating-box">
            				            <div class="rating" style="width:<?php echo (int)$product['average_rated']*100/5;?>%"></div>
            				        </div>
            				        <p class="rating-links no-rating">
            				        	<?php 
            				        	$urlAddReview = '#customer-reviews';
            				        	if(!isset($_SESSION['customer']['id'])){
                    				        $theURL = urlencode(urldecode($url_alias.(sizeof($variant_options) ? '?'.http_build_query(array('variant_options'=>$variant_options),'flags_').'&addReview':'?addReview')));                  				                      				        	
                      				        $urlAddReview = '/account/login?return='.$theURL;
            				        	}
                    				    ?> 
            				        	<?php if ($product['total_rated'] > 0) {?>
            				        	<a id="goto-reviews" href="#customer-reviews"><?php echo $product['total_rated'];?> <?php echo language('product', 'LABEL_NB_REVIEWS');?></a>
										<span class="separator">|</span>
										<a id="goto-reviews-form" href="<?php echo $urlAddReview;?>"><?php echo language('product', 'LABEL_ADD_YOUR_REVIEW');?></a>
										<?php } else {?>
            				        	<a id="goto-reviews-form" href="<?php echo $urlAddReview;?>"><?php echo language('product', 'LABEL_BE_FIRST_REVIEWS');?></a>
            				        	<?php }?>
            				        </p>            				        
            				    </div>
            				    <?php } ?>
            				    <!-- end: reviews -->
            				    
            				    <?php if ($product['track_inventory'] && $config_site['enable_show_qty_remaining'] && $product['in_stock']) {?>
            				    <p class="availability in-stock" id="qty_remaining">
                                  <?php echo show_qty_remaining($id_product, $id_product_variant);?>                                   
                                 </p>
                                <?php } ?>	
                                
                                <!-- check if allow backorder and not in stock -->
                                <?php  if ($product['track_inventory'] && !$product['in_stock'] && $product['allow_backorders']) {?>
	                               <p class="availability in-stock" style="color:red;">
	                               <?php echo language('product', 'ALERT_AVAILABLE_NOT_IN_STOCK');?>
	                               </p>
                                <?php }?> 
                                 
                                <?php                             
                                // single product, check if we have variants
                                if (!$product['product_type'] && $product['has_variants'] && !$config_site['display_multiple_variants_form'] || !$product['product_type'] && $product['has_variants'] && $modify_product) {
                                    if (isset($variant_options) && is_array($variant_options) && sizeof($variant_options)) $output = get_variant_options_selected($id_product, $variant_options);
                                    else $output = get_variant_options($id_product,array());
                                   
                                    if (!empty($output)) {
										echo '<div class="variant_box noprint">';
										echo '<div style="font-size:14px; font-weight: bold">'.language('product', 'LABEL_CHOOSE_FOLLOWING_VARIANT').'</div>';
                                        echo $output;
                                        	
										echo '</div>';
										echo '<div class="cb"></div>';
                                    }
                                }
                                ?> 
                                 
                               
                                
				                <div class="add-to-box">
				                	<?php 
	                                    if ($product['product_type'] != 2) {
	                                ?>
	                                <div id="tier_prices">
	                                <?php 
	                                    echo get_tier_prices($id_product, $id_product_variant);	
	                                ?>
	                                </div>                                      
	                                <div class="noprint" id="add_to_cart_top">
										<?php 
										echo get_add_cart_button_top();
	                                    ?>
	                                </div>
	                                <?php } ?>
	                                
	                                <?php if($product['min_qty']) {?>
	                                <div><?php echo '('.language('global', 'TITLE_QTY_MINIMUM').' '.$product['min_qty'].')'?></div>
									<?php }?>                                
	                                
	                                <?php if ($product['in_stock']) {?> 
	                                <div id="max_qty" style="float:left;  margin: 5px 0 20px 0;"><?php get_max_qty()?></div>
	                                <?php }?>
	                                
	                                <!-- 
				                	<div class="add-to-cart">        		
		                                <label for="qty">Qty:</label>
                  				        <input name="qty" id="qty" maxlength="12" value="1" title="Qty" class="input-text qty" type="text">
                  				        <button type="button" class="button-arrow button-up">Increase</button>
                  				        <button type="button" class="button-arrow button-down">Decrease</button>
                  				        <script type="text/javascript">
                  				        //<![CDATA[
                  				        jQuery(function($) {
                  				            jQuery('.add-to-cart .button-up').click(function() {
                  				                $qty = jQuery(this).parent().find('.qty');
                  				                qty = parseInt($qty.val()) + 1;
                  				                $qty.val(qty);
                  				            });
                  				            jQuery('.add-to-cart .button-down').click(function() {
                  				                $qty = jQuery(this).parent().find('.qty');
                  				                qty = parseInt($qty.val()) - 1;
                  				                if (qty < 0)
                  				                    qty = 0;
                  				                $qty.val(qty);
                  				            });
                  				        });
                  				        //]]>
                  				        </script>
				                		<button type="button" title="Add to Cart" class="button btn-cart "><span><span>Add to Cart</span></span></button>
				    				</div>
				    				-->
				    				<div class="add-to-cart-extra"></div>
				                    <div class="add-links-wrap clearfix" style="margin-top: 15px; clear: both;">         
										<ul class="add-to-links" style="float:left;">
											<?php if($config_site['display_menu_add_wishlist']){?>
                          				    <li><input type="submit" name="_add_to_wishlist" class="button link-wishlist" title="<?php echo language('product', 'BTN_ADD_WISHLIST');?>"></li>
                          				    <?php } ?>
                          				    <!-- 
                          				    <li><a onclick="" href="" class="button link-compare" title="Add to Compare">Add to Compare</a><span>Compare</span></li>
                          					 -->
                          				</ul>
                          				
                          				<!-- start: price Alert -->
						 				<?php 
		    							if ($product['product_type'] == 0 and ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) and $config_site['display_menu_price_alert']) {?>
		                                <div class="ico_price_alert noprint" style="margin-left:10px; float:left;"><a href="javascript:void(0);" id="add_price_alert"><input type="button" class="button link-friend" title="<?php echo language('product', 'BTN_PRICE_ALERT');?>"></a></div>
		                                <?php } ?>
		                                <!-- end: price Alert -->
		                                
                          				<!-- 
				                        <span class="email-friend">
				                        	<a href="" class="button link-friend" title="Email to a Friend">To Friend</a>
				                        	<span>To Friend</span>
				                        </span>
				                        -->
				                        <!-- Social bookmarks from http://www.addthis.com/get/sharing  -->
		                                <div class="addthis-icons clearfix">
		                                    <span><?php echo language('global','LABEL_SHARE');?>: </span>
		                                    <!-- AddThis Button BEGIN -->
                                            <div class="addthis_toolbox addthis_default_style addthis_32x32_style">
                                            <a class="addthis_button_preferred_1"></a>
                                            <a class="addthis_button_preferred_2"></a>
                                            <a class="addthis_button_preferred_3"></a>
                                            <a class="addthis_button_preferred_4"></a>
                                            <a class="addthis_button_compact"></a>
                                            <a class="addthis_counter addthis_bubble_style"></a>
                                            </div>
                                            <script type="text/javascript">var addthis_config = {"data_track_addressbar":true};</script>
                                            <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-52e30bd47068ebfa"></script>
                                            <!-- AddThis Button END -->
		                                </div>
                                        
                                         <?php echo $product['short_desc']?'<div style="clear:both"></div><div style="border-top: 1px solid #CCC; padding-top: 10px; margin-top: 10px;">'.$product['short_desc'].'</div>':'';?>
                                        
				                    </div>
				                </div>
				                <!-- end: add-to-box -->                                
							</div>
							<!-- end: product-shop -->
				        </div>				    				    
				    </div>
				    <!-- end: product-essential -->
				
				    <div class="product-collateral">				
				        <div class="product-info row">
				        	<div class="col-sm-12 product-tabs">
				                <div class="box-additional box-tabs">
				                    <dl style="height: 372px;" id="product-tabs" class="product-tabs-inner">				
                                                                <?php if ($product['product_type'] or !empty($product['description'])) { ?>
				        				<dt class="tab-title open" id="tab-short_description"><?php echo language('product', 'LABEL_OVERVIEW');?></dt>
                                                                        <dd style="display: block; bottom: auto;" class="tab-section">    
                                                                            <!-- <h2></h2> -->
                                                                            <div class="std">
                                                                                <?php if ($product['brand'] || $product['model'] || $product['year'] || $product['mileage'] || $product['color']) { ?>
                                                                                    <div style="margin-top:0; padding:0; margin-bottom:10px;">
                                                                                        <?php echo $product['brand'] ? '<div style="margin:0; padding:0; line-height: normal;"><strong>' . language('product', 'LABEL_BRAND') . '</strong> ' . $product['brand'] . '</div>' : ''; ?>
                                                                                        <?php echo $product['model'] ? '<div style="margin:0; padding:0; line-height: normal;"><strong>' . language('product', 'LABEL_MODEL') . '</strong> ' . $product['model'] . '</div>' : ''; ?>
                                                                                        <?php echo $product['year'] ? '<div style="margin:0; padding:0; line-height: normal;"><strong>' . language('product', 'LABEL_YEAR') . '</strong> ' . $product['year'] . '</div>' : ''; ?>
                                                                                        <?php echo $product['mileage'] ? '<div style="margin:0; padding:0; line-height: normal;"><strong>' . language('product', 'LABEL_MILEAGE') . '</strong> ' . number_format($product['mileage'], 0, ',', ' ') . '<strong> KM</strong></div>' : ''; ?>
                                                                                        <?php echo $product['color'] ? '<div style="margin:0; padding:0; line-height: normal;"><strong>' . language('product', 'LABEL_COLOR') . '</strong> ' . $product['color'] . '</div>' : ''; ?>
                                                                                    </div>
                                                                                <?php } ?>

                                                                                <?php echo $product['description'] ?>
                                                                            </div>
                                                                        </dd>
                                                                <?php }?>			
										
              							<!-- start: box-reviews -->
              							<?php if($config_site['display_menu_rate_product']) {?>
                        				<dt class="tab-title" id="tab-tabreviews"><?php echo language('product','LABEL_REVIEWS');?></dt>
                        				<dd style="display: none;" class="tab-section">
                          				<div class="box-collateral box-reviews" id="customer-reviews">
                          				    <h2>Customer Reviews</h2>
                          				    <h3 class="review-title"><span class="review-label"> <?php echo language('product','LABEL_REVIEW');?> </span><strong>"<?php echo $product["name"]?>"</strong></h3>
                      				        <?php if ($product['total_rated'] > 0) {?>
                                                            <div class="rating-box">
                                                                        <div class="rating" style="width:<?php echo (int) $product['average_rated'] * 100 / 5; ?>%;"></div>
                                                                    </div>
                                            <?php if(isset($arr_save['success'])) {?>
                                     		<div class="messages" style="margin-top: 15px !important;">
                                     			<div class="alert alert-success success-msg">
                                     			<button type="button" class="close" data-dismiss="alert">×</button>
                                     			<ul><li><span><?php echo $arr_save['success'];?></span></li></ul>
                                     			</div>
                                     		</div> 
                                     		<?php }?> 
                                            <?php while($row = $productReviews->fetch_assoc()){?>
                                            <dl>
                                                <dt>
                                            	<?php echo $row['title']?>	            
                                        		</dt>
                                                <dd>
                                                    <span class="author"><?php echo ($row['anonymous'])?language('_include/ajax/review','TITLE_ANONYMOUS'):$row['customer_name'];?> </span>
                                                    <span class="date"><?php echo df_date($row['date_created']);?></span>
                                                    <table class="ratings-table">
                                                        <colgroup>
                                                          <col width="1">
                                                          <col>
                                                        </colgroup>
                                                        <tbody>
                                                        <tr>
                                                            <th><?php echo language('product','LABEL_REVIEW_RATE');?></th>
                                                            <td>
                                                                <div class="rating-box">
                                                                    <div class="rating" style="width:<?php echo (int)$row['rated']*100/5;?>%;"></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                         </tbody>
                                                    </table>
                                                    <?php echo nl2br($row['review']);?>         
                                            	</dd>
                                            </dl>
                                            <?php }?>                                            
                                            <?php }?>
                      				        <div class="form-add">  
                      				        	<?php if(!isset($_SESSION['customer']['id'])){?>
                      				        		<?php $theURL = $url_alias.(sizeof($variant_options) ? '?'.http_build_query(array('variant_options'=>$variant_options),'flags_').'&addReview':'?addReview'); ?>                  				                      				        	
                        				        	<a href="/account/login?return=<?php echo urlencode($theURL);?>" style="margin-top:20px" class="button">
                        				        	<?php echo language('product','LABEL_WRITE_REVIEW');?>
                        				        	</a>
                      				        	<?php }?>   
                      				        	<?php if($has_reviews && !isset($arr_save['success'])) { ?>                      				        	
                      				        	<div class="messages">
                                         			<div class="alert alert-success">
                                         			<button type="button" class="close" data-dismiss="alert">×</button>
                                         			<ul><li><span><?php echo language('_include/ajax/review','TITLE_ALREADY_ADDED');?></span></li></ul>
                                         			</div>
                                         		</div>                      				        	
                      				        	<?php }?>   
                      				        	<?php if(isset($_SESSION['customer']['id']) && !$has_reviews){?>           				        	
                      				        	<!-- <form action="" method="post" id="review-form"> -->
                      				        		<?php if(!empty($err_review_requied_fields)) {?>
                      				        		<br>
                      				        		<div class="messages">
	                                         			<div class="alert alert-danger">
	                                         			<button type="button" class="close" data-dismiss="alert">×</button>
	                                         			<ul><li><span><?php echo language('global','LABEL_ENTER_REQUIRED_FIELDS');?></span></li></ul>
	                                         			</div>
	                                         		</div>     
                      				        		<?php }?>
                      				        		<input name="id" type="hidden" value="<?php echo $product["id"];?>" id="id">
                      				        		<input name="validate_rating" class="validate-rating" value="0" type="hidden" id="validate-rating">
                            				        <h3><?php echo language('product','LABEL_WRITE_REVIEW');?></h3>
                            				        <fieldset>
                            				        	<h4><?php echo language('product','LABEL_HOW_RATE_PRODUCT');?> <em class="required">*</em></h4>
                        				                <span id="input-message-box"></span>
                        				                <table class="data-table" id="product-review-table">
                        				                    <colgroup><col>
                        				                    <col width="1">
                        				                    <col width="1">
                        				                    <col width="1">
                        				                    <col width="1">
                        				                    <col width="1">
                        				                    </colgroup><thead>
                        				                        <tr class="first last">
                        				                            <th>&nbsp;</th>
                        				                            <th><span class="nobr">1<br><?php echo language('product','LABEL_STAR');?></span></th>
                        				                            <th><span class="nobr">2<br><?php echo language('product','LABEL_STARS');?></span></th>
                        				                            <th><span class="nobr">3<br><?php echo language('product','LABEL_STARS');?></span></th>
                        				                            <th><span class="nobr">4<br><?php echo language('product','LABEL_STARS');?></span></th>
                        				                            <th><span class="nobr">5<br><?php echo language('product','LABEL_STARS');?></span></th>
                        				                        </tr>
                        				                    </thead>
                        				                    <tbody>
                        				                    	<tr class="first odd">
                          				                            <th><?php echo language('product','LABEL_REVIEW_RATE');?></th>
                                                                    <td class="value"><input name="rated" id="Value_1" value="1" class="radio" type="radio"></td>
                                                                    <td class="value"><input name="rated" id="Value_2" value="2" class="radio" type="radio"></td>
                                                                    <td class="value"><input name="rated" id="Value_3" value="3" class="radio" type="radio"></td>
                                                                    <td class="value"><input name="rated" id="Value_4" value="4" class="radio" type="radio"></td>
                                                                    <td class="value last"><input name="rated" id="Value_5" value="5" class="radio" type="radio"></td>
                                                                </tr>                        				                                          
                        				                	</tbody>
                        				                </table>                        				                
                        				                <script type="text/javascript">decorateTable('product-review-table')</script>
                        				                <ul class="form-list">
                        				                	<!-- 
                        				                    <li>
                        				                        <label for="nickname_field" class="required"><i class="icon-people"></i>Nickname<em>*</em></label>
                        				                        <div class="input-box">
                        				                            <input name="nickname" id="nickname_field" class="input-text required-entry" type="text">
                        				                        </div>
                        				                    </li>
                        				                     -->
                        				                    <li>
                        				                        <label for="summary_field" class="required"><i class="icon-subject"></i><?php echo language('_include/ajax/review','LABEL_TITLE');?><em>*</em></label>
                        				                        <div class="input-box">
                        				                            <input name="review_title" id="summary_field" class="input-text required-entry" type="text">
                        				                        </div>
                        				                    </li>
                        				                    <li>
                        				                        <label for="review_field" class="required label-wide"><i class="icon-comment"></i><?php echo language('_include/ajax/review','LABEL_COMMENTS');?><em>*</em></label>
                        				                        <div class="input-box">
                        				                            <textarea name="review_review" id="review_field" cols="5" rows="3" class="required-entry"></textarea>
                        				                        </div>
                        				                    </li>
                        				                    <li>
                        				                    	<input type="checkbox" name="anonymous" id="anonymous" value="1" />
																<span><?php echo language('_include/ajax/review','LABEL_ANONYMOUS');?></span>
                        				                    </li>
                        				                </ul>
                        				            </fieldset>
                        				            <div class="buttons-set">
                        				                <button type="submit" name="save_review" title="<?php echo language('_include/ajax/review','BTN_SAVE');?>" class="button"><span><span><?php echo language('_include/ajax/review','BTN_SAVE');?></span></span></button>
                        				            </div>
                      				    		<!-- </form> -->
                      				    		<?php }?>
                            				    <script type="text/javascript">                            				    	
                            				    //<![CDATA[
                            				        var dataForm = new VarienForm('review-form');
                            				        Validation.addAllThese(
                            				        [
                            				               ['validate-rating', LABEL_RATING_REQUIRED, function(v) {
                            				                    var trs = jQuery('product-review-table').select('tr');
                            				                    var inputs;
                            				                    var error = 1;
                            				    
                            				                    for( var j=0; j < trs.length; j++ ) {
                            				                        var tr = trs[j];
                            				                        if( j > 0 ) {
                            				                            inputs = tr.select('input');
                            				    
                            				                            for( i in inputs ) {
                            				                                if( inputs[i].checked == true ) {
                            				                                    error = 0;
                            				                                }
                            				                            }
                            				    
                            				                            if( error == 1 ) {
                            				                                return false;
                            				                            } else {
                            				                                error = 1;
                            				                            }
                            				                        }
                            				                    }
                            				                    return true;
                            				                }]
                            				        ]
                            				        );
                            				    //]]>
                            				    </script>
                      				    	</div>
                          				</div>                          				
                        				</dd>
                        				<?php }?>
                        				<!-- end: box-reviews -->               				                        				
                        			</dl>
              				
              				    <script type="text/javascript">
              				
              				    //<![CDATA[
              				
              				    var crosssell_slider;
              				
              				        
              				
              				    jQuery(function($) {
              				
              				        var venedor_ptabs_timer;
              				
              				        var ptabs_width = 0;
              				
              				        jQuery('#product-tabs > dt').click(function() {
              				
              				            old_tab = jQuery('#product-tabs > dt.open').attr('id');
              				
              				            f = false;
              				
              				            if (jQuery(this).hasClass('open'))
              				
              				                f = true;
              				
              				            w = jQuery(window).width();
              				
              				            if (f && w == ptabs_width)
              				
              				                return;
              				
              				            ptabs_width = w;
              				
              				            $parent = jQuery(this).parent();
              				
              				            $parent.find('> dt').removeClass('open');
              				
              				            $parent.find('> dd').stop().hide();
              				
              				            jQuery(this).next().stop().show();
              				
              				            
              				
              				            $self = jQuery(this);
              				
              				            $self.addClass('open');
              				
              				            $cur = $self.next();
              				
              				            $parent.stop().css('height', 'auto');
              				
              				            $cur.css('bottom', 'auto');
              				
              				            h = $parent.height() + 60;
              				
              				            c = $cur.height() + 60 + 3;
              				
              				            if (c > h) {
              				
              				                $parent.css({'height': c + 'px'});
              				
              				                $parent.find('> dt').last().css('border-bottom-width', '1px');
              				
              				            } else {
              				
              				                $cur.css('bottom', '0');
              				
              				                $parent.find('> dt').last().css('border-bottom-width', '0');
              				
              				            }
              				
              				            
              				
              				            if ( old_tab != 'tab-crosssell' && jQuery(this).attr('id') == 'tab-crosssell') {
              				
              				                var slider = jQuery('#crosssell-products-list').data('flexslider');
              				
              				                if (slider) {
              				
              				                    slider.resize();
              				
              				                    setTimeout(function() {
              				
              				                        ptabs_width = 0;
              				
              				                        venedor_ptabs_resize();
              				
              				                    }, 800);
              				
              				                }
              				
              				            }
              				
              				        });
              				
              				        
              				
              				        function venedor_ptabs_resize() {
              				
              				            jQuery('#product-tabs > dt.open').click();
              				
              				            if (venedor_ptabs_timer) clearTimeout(venedor_ptabs_timer);
              				
              				        }
              				
              				        
              				
              				        jQuery(window).resize(function() {
              				
              				            clearTimeout(venedor_ptabs_timer);
              				
              				            venedor_ptabs_timer = setTimeout(venedor_ptabs_resize, 200); 
              				
              				        });
              				
              				        
              				
              				        setTimeout(function() {
              				
              				            if (jQuery('#product-tabs > dt.active').length)
              				
              				                jQuery('#product-tabs > dt.active').first().click();
              				
              				            else
              				
              				                jQuery('#product-tabs > dt').first().click();
              				
              				        }, 800);
              				
              				    });
              				
              				    //]]>    
              				
              				    </script>                </div>
              				            </div>
              				            
              				                        <script type="text/javascript">
              				            //<![CDATA[
              				            jQuery(function($){
              				                var venedor_product_timer;
              				                
              				                function venedor_product_resize() {
              				                    if (VENEDOR_RESPONSIVE) {
              				                        var winWidth = jQuery(window).innerWidth();
              				
              				                                                if (winWidth > 750 && ((!jQuery('body').hasClass('bv3') && winWidth < 963) || (jQuery('body').hasClass('bv3') && winWidth < 975))) {
              				                            jQuery('.product-related').removeClass('col-sm-3');
              				                            jQuery('.product-related').addClass('col-sm-12');
              				                            jQuery('.product-tabs').removeClass('col-sm-9');
              				                            jQuery('.product-tabs').addClass('col-sm-12');
              				                        } else {
              				                            //jQuery('.product-related').removeClass('col-sm-12');
              				                            jQuery('.product-related').addClass('col-sm-3');
              				                            jQuery('.product-tabs').removeClass('col-sm-12');
              				                            //jQuery('.product-tabs').addClass('col-sm-9');
              				                        }
              				                                            }
              				                    if (venedor_product_timer) clearTimeout(venedor_product_timer);
              				                }
              				                
              				                                jQuery("#goto-reviews, #goto-reviews-form").click(function() {
              				                    jQuery("#product-tabs #tab-tabreviews").click();
              				                });
              				                
              				                jQuery(window).load(venedor_product_resize);
              				
              				                jQuery(window).resize(function() {
              				                    clearTimeout(venedor_product_timer); 
              				                    venedor_product_timer = setTimeout(venedor_product_resize, 200); 
              				                });
              				            });
              				            //]]>
              				            </script>
              				        </div>
              				        <?php 
              				        switch ($product['product_type']) {
                        // combo
                        case 1:
                            // user_defined_qty						
                            if ($result_sub_product = $mysqli->query('SELECT
                            product_combo.id,
                            product_combo.qty,
                            product_combo.id_combo_product,
                            product_combo_variant.id_product_variant,
                            product_combo_variant.id AS id_product_combo_variant,
                            product_description.name,
							product_description.short_desc,
                            GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant_name,
                            product_variant.sku AS variant_sku, 
                            calc_sell_price(product.price,product_variant.price_type,product_variant.price,0,0,0,0) AS price,
                            get_product_current_price(product.id,product_variant.id,'.$mysqli->escape_string($_SESSION['customer']['id_customer_type']).') AS sell_price,
                            is_product_in_stock(product.id,product_variant.id,product_combo.qty) AS in_stock,
							IF(config_display_price_exceptions.id_product IS NOT NULL,1,0) AS display_price_exception
                            FROM
                            product_combo
                            INNER JOIN 
                            (product CROSS JOIN product_description)
                            ON
                            (product_combo.id_combo_product = product.id AND product.id = product_description.id_product AND product_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
                            LEFT JOIN 
                            (product_combo_variant 
                            CROSS JOIN product_variant 
                            CROSS JOIN product_variant_option 
                            CROSS JOIN product_variant_group 
                            CROSS JOIN product_variant_group_option 
                            CROSS JOIN product_variant_group_option_description
							CROSS JOIN product_variant_group_description)
                            ON 
                            (product_combo.id = product_combo_variant.id_product_combo 
                            AND product_combo_variant.default_variant = 1 
                            AND product_combo_variant.id_product_variant = product_variant.id 
                            AND product_variant.id = product_variant_option.id_product_variant 
                            AND product_variant_option.id_product_variant_group = product_variant_group.id 
                            AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
                            AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
                            AND product_variant_group_option_description.language_code = product_description.language_code
							AND	product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
							AND product_variant_group_description.language_code = product_variant_group_option_description.language_code)
							
							LEFT JOIN
							config_display_price_exceptions
							ON
							(product.id = config_display_price_exceptions.id_product)
                            WHERE
                            product_combo.id_product = "'.$mysqli->escape_string($id_product).'"
                            GROUP BY 
                            product_combo.id
                            ')) {
                                if ($result_sub_product->num_rows) {	
                                    if (!$stmt_sub_product_variant = $mysqli->prepare('SELECT
                                    product_combo_variant.id,
                                    GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant_name,
                                    calc_sell_price(product.price,product_variant.price_type,product_variant.price,0,0,0,0) AS price,
                                    get_product_current_price(product_combo.id_combo_product,product_combo_variant.id_product_variant,'.$mysqli->escape_string($_SESSION['customer']['id_customer_type']).') AS sell_price
									
                                    FROM 
                                    product_combo
									INNER JOIN
                                    product
                                    ON
                                    (product_combo.id_combo_product = product.id)
                                    INNER JOIN
                                    product_combo_variant
                                    ON
                                    (product_combo.id = product_combo_variant.id_product_combo)
                                    INNER JOIN
                                    (product_variant 
                                    CROSS JOIN product_variant_option 
                                    CROSS JOIN product_variant_group 
                                    CROSS JOIN product_variant_group_option 
                                    CROSS JOIN product_variant_group_option_description
									CROSS JOIN product_variant_group_description)
                                    ON 
                                    (product_combo_variant.default_variant = 0 
                                    AND product_combo_variant.id_product_variant = product_variant.id 
                                    AND product_variant.id = product_variant_option.id_product_variant 
									AND product_variant_option.id_product_variant_group = product_variant_group.id 
									AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
									AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
									AND product_variant_group_option_description.language_code = ?
									AND	product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
									AND product_variant_group_description.language_code = product_variant_group_option_description.language_code)
									
                                    WHERE
                                    product_combo_variant.id_product_combo = ?
                                    AND
                                    is_product_in_stock(product_combo.id_combo_product,product_combo_variant.id_product_variant,product_combo.qty) = 1
                                    GROUP BY 
                                    product_variant.id
                                    ORDER BY 
                                    product_variant.sort_order ASC')) throw new Exception('An error occured while trying to prepare get sub product variants statement.'."\r\n\r\n".$this->mysqli->error);									
                                
                                    echo '<div style="border:1px solid #ccc; background-color: #f7f7f7; margin-bottom:20px; padding:5px;overflow: hidden;">';
                                    // variable to contain the combo price before discount
                                    $product_price = 0;							
                                    // variable to contain the combo discount amount 
                                    $discount = 0;									
                                    // variable to contact total of additional products to add
                                    $add_products_total = 0;							
                                    while ($row_sub_product = $result_sub_product->fetch_assoc()) {																				
                                        // if we have a variant check if we have other variants 
                                        $sub_product_variants=array();																							
        
                                        if ($row_sub_product['id_product_variant']) {
                                            // check what variant is selected by default, if we posted or by default from db
                                            $selected = isset($_POST['combo_product'][$row_sub_product['id']]['id_product_combo_variant']) ? $_POST['combo_product'][$row_sub_product['id']]['id_product_combo_variant']:(isset($modify_product_info['combo_product'][$row_sub_product['id']]['id_product_combo_variant']) ? $modify_product_info['combo_product'][$row_sub_product['id']]['id_product_combo_variant']:$row_sub_product['id_product_combo_variant']);
                                            
                                            // if selected is current one add qty * product price to product price variable
                                            if ($selected == $row_sub_product['id_product_combo_variant']) $product_price += $row_sub_product['qty']*$row_sub_product['price'];
                                            
                                            // build sub variants array
                                            $sub_product_variants[$row_sub_product['id_product_variant']] = array(
                                                'id'=>$row_sub_product['id_product_combo_variant'],
                                                'name'=>$row_sub_product['variant_name'],
                                                'price'=>$row_sub_product['price'],
                                                'sell_price'=>$row_sub_product['sell_price'],
                                            );				
                                            
                                            // add prices to javascript array
                                            $prices_js['combo_products'][$row_sub_product['id']][$row_sub_product['id_product_combo_variant']] = array(
                                                'price' => $row_sub_product['price'],
                                                'sell_price'=>$row_sub_product['sell_price'],
                                            );
                                            
                                            // look for other variants available 																										
                                            if (!$stmt_sub_product_variant->bind_param("si", $_SESSION['customer']['language'], $row_sub_product['id'])) throw new Exception('An error occured while trying to bind params to get sub product variants statement.'."\r\n\r\n".$mysqli->error);
                                            
                                            /* Execute the statement */
                                            $stmt_sub_product_variant->execute();
                                            
                                            /* store result */
                                            $stmt_sub_product_variant->store_result();																											
                                            
                                            // if we have other variants
                                            if ($stmt_sub_product_variant->num_rows) {											
                                                /* bind result variables */
                                                $stmt_sub_product_variant->bind_result($sub_product_variant_id, $sub_product_variant_name, $sub_product_variant_price,
                                                $sub_product_variant_sell_price);										
                    
                                                // loop through variants							
                                                while ($stmt_sub_product_variant->fetch()) {
													if ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) {
														// if selected is current variant, add price to product total
														if ($selected == $sub_product_variant_id) $product_price += $row_sub_product['qty']*$sub_product_variant_price;
														
														// build sub variants array
														$sub_product_variants[$sub_product_variant_id] = array(
															'id'=>$sub_product_variant_id,
															'name'=>$sub_product_variant_name,
															'price'=>$sub_product_variant_price,
															'sell_price'=>$sub_product_variant_sell_price,
														);
														
														// add prices to javascript array
														$prices_js['combo_products'][$row_sub_product['id']][$sub_product_variant_id] = array(
															'price' => $sub_product_variant_price,
															'sell_price'=>$sub_product_variant_sell_price,
														);											
													} else {
														// build sub variants array
														$sub_product_variants[$sub_product_variant_id] = array(
															'id'=>$sub_product_variant_id,
															'name'=>$sub_product_variant_name,
															'price'=>0,
															'sell_price'=>0,
														);
														
														// add prices to javascript array
														$prices_js['combo_products'][$row_sub_product['id']][$sub_product_variant_id] = array(
															'price' => 0,
															'sell_price'=>0,
														);	
													}
                                                }
                                            }									
                                        } else {
											if ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) {
												$product_price += $row_sub_product['qty']*$row_sub_product['price'];
												
												$prices_js['combo_products'][$row_sub_product['id']] = array(
													'price' => $row_sub_product['price'],
													'sell_price'=>$row_sub_product['sell_price'],
												);										
											} else {
												$prices_js['combo_products'][$row_sub_product['id']] = array(
													'price' => 0,
													'sell_price'=>0,
												);										
												
											}
                                        }
                                        
                                        // get cover image for product
                                        $cover_image = get_product_cover_image($row_sub_product['id_combo_product'], $row_sub_product['id_product_variant']);	
                                        // if no cover, put blank image
                                        
										if ($cover_image){
										   $cover_image = '/images/products/thumb/'.$cover_image;
										   $image_size = getimagesize(dirname(__FILE__).$cover_image);
										}else{ 
											$cover_image = get_blank_image('thumb');
											$image_size[3] = ' width="'.$image_thumb_width.'" height="'.$image_thumb_height.'"';
										}
                                                        
                                        echo '<div style="border:1px solid #ccc; background-color: #FFF; padding:5px; margin:5px; margin-bottom:10px; overflow:hidden;">
                                        <div class="fl" style="margin-right:5px; width:'.$image_thumb_width.'px"><img src="'.$cover_image.'" '.$image_size[3].' style="display:block;border:1px solid #ddd;" /></div>
                                        
                                        <div class="fl" style="width:680px;"><span style="font-size: 16px; font-weight: bold; color: #900;">'.$row_sub_product['qty'].'&nbsp;X</span> <strong>'.$row_sub_product['name'] . '</strong><div style="margin-top: 3px; margin-bottom: 8px">'.$row_sub_product['short_desc'].'</div>';
        
                                        // get discount percent based on discount type and combo product total price before discount
                                        $discount_pc = $product['discount_type'] ? $product['discount']/100:($product_price > 0 ? $product['discount']/$product_price:0);
                                        // variable containing current price for each sub product
                                        $current_sell_price = 0;	
                                        
                                        // if we have a variant
                                        if ($row_sub_product['id_product_variant']) {	
                                            // check if we have more than 1 variant	
                                            $total_variants = sizeof($sub_product_variants);
                                                                    
                                            // if we have more than 1, it will display as a dropdown
                                            if ($total_variants > 1) {
                                                echo '&nbsp;<select name="combo_product['.$row_sub_product['id'].'][id_product_combo_variant]" id="combo_product_variant_'.$row_sub_product['id'].'" class="combo_product_variant">';
                                            }
                                                
                                            // loop through each variant
                                            foreach ($sub_product_variants as $row_sub_product_variant) {
                                                // add option to select
                                                if ($total_variants > 1) {
                                                    echo '<option value="'.$row_sub_product_variant['id'].'" '.($selected == $row_sub_product_variant['id'] ? 'selected="selected"':'').'>'.$row_sub_product_variant['name'].($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' ('.nf_currency($row_sub_product_variant['price']).')':'').'</option>';
                                                // display variant add hidden option
                                                } else {
                                                    echo ' ('.$row_sub_product['variant_name'].')
                                                    <input type="hidden" name="combo_product['.$row_sub_product['id'].'][id_product_combo_variant]" value="'.$row_sub_product['id_product_combo_variant'].'" class="combo_product_variant" id="combo_product_variant_'.$row_sub_product['id'].'" />';
                                                }
                                                
												if ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) {
													
													// if current selected add price to total
													if ($selected == $row_sub_product_variant['id']) {
														$current_price = $row_sub_product['qty']*$row_sub_product_variant['price'];
														$current_sell_price = $row_sub_product_variant['sell_price'];
													}
												} else {
													$current_price = 0;
													$current_sell_price = 0;
												}
                                            }	
                                            
                                            if ($total_variants > 1) echo '</select>';						
                                        } else {
											if ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) {
												$current_price = $row_sub_product['qty']*$row_sub_product['price'];															
												$current_sell_price = $row_sub_product['sell_price'];
											} else {
												$current_price = 0;
												$current_sell_price = 0;
											}
                                        }
                                        
                                        echo '<input type="hidden" name="combo_product['.$row_sub_product['id'].'][id]" value="'.$row_sub_product['id'].'" class="combo_product" id="combo_product_'.$row_sub_product['id'].'" />
                                        <input type="hidden" name="combo_product['.$row_sub_product['id'].'][qty]" value="'.$row_sub_product['qty'].'" />
                                        <input type="hidden" name="combo_product['.$row_sub_product['id'].'][id_cart_item_product]" value="'.(isset($_POST['combo_product'][$row_sub_product['id']]['id_cart_item_product']) ? $_POST['combo_product'][$row_sub_product['id']]['id_cart_item_product']:(isset($modify_product_info['combo_product'][$row_sub_product['id']]['id_cart_item_product']) ? $modify_product_info['combo_product'][$row_sub_product['id']]['id_cart_item_product']:'')).'" />
                                        </div>'.
                                        
                                        // Verify if display_price is true
						  				($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']?'<div class="fr"><span id="combo_product_price_'.$row_sub_product['id'].'">'.nf_currency($current_price).'</span></div><div class="cb" style="clear: both;"></div>':'<div class="cb" style="clear: both;"></div>');
                                        
                                        // if user defined qty
                                        if ($product['user_defined_qty'] && $row_sub_product['in_stock']) {
                                            $qty = isset($_POST['combo_product'][$row_sub_product['id']]['add_qty']) ? $_POST['combo_product'][$row_sub_product['id']]['add_qty']:'';
                                            
                                            // Verify if display_price is true 
                                          // if($config_site['display_price'] || !$config_site['display_price'] && $row_sub_product['display_price_exception']){
										    echo '<div class="fl" style="margin-top:10px;">
                                            '.language('product', 'LABEL_ADDITIONAL_QTY').'&nbsp;<input type="text" name="combo_product['.$row_sub_product['id'].'][add_qty]" style="text-align:center; width:30px;" value="'.$qty.'" class="additional_qty check_qty" />'.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? '&nbsp;(<span id="combo_product_additional_price_'.$row_sub_product['id'].'">'.nf_currency($current_sell_price).'</span>/'.language('product', 'LABEL_EACH').')':'').'									
                                            </div>
                                            <div class="cb" style="clear: both;"></div>';
										   //}
                                            
                                            $add_products_total += ($qty > 0 ? $qty*$current_sell_price:0);
                                        }
                                        
                                        if (!$row_sub_product['in_stock']) {
                                            echo '<div class="fl special_price" style="margin-top:5px;">'.language('product', 'ALERT_OUT_OF_STOCK').'</div><div class="cb" style="clear: both;"></div>';	
                                        }
                                        
                                        echo '</div>';
                                    }		
                                    
                                    $stmt_sub_product_variant->close();
                                    
                                    $discount = round($product_price*$discount_pc,2);
                                    
                                    $product['total_product'] = $product_price-$discount;
                                    
                                    // Verify if display_price is true 
                                 	if($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']){
									
										echo '<br /><br />
										<div class="fr" style="padding:5px;">
											<div class="fl" style="width:200px">'.language('product', 'LABEL_COMBINED_TOTAL').'</div>
											<div class="fl" style="width:150px; text-align:right"><span id="combo_subtotal">'.nf_currency($product_price).'</span></div>
											<div class="cb" style="clear: both;"></div>
										</div>
										<div class="cb" style="clear: both;"></div>
										<div class="fr" style="padding:5px;">
											<div class="fl" style="width:200px">'.language('product', 'LABEL_COMBO_DISCOUNT').'</div>
											<div class="fl" style="width:150px; text-align:right"><span id="combo_discount">'.nf_currency(-$discount).'</span></div>
											<div class="cb" style="clear: both;"></div>
										</div>
										<div class="cb" style="clear: both;"></div>
										<div class="fr" style="padding:5px; font-weight:bold; font-size:16px;">
											<div class="fl" style="width:200px">'.language('product', 'LABEL_COMBO_PRICE').'</div>
											<div class="fl" style="width:150px; text-align:right"><span id="combo_total">'.nf_currency($product['total_product']).'</span></div>
											<div class="cb" style="clear: both;"></div>
										</div>
										<div class="cb" style="clear: both;"></div>';
										
										if ($product['user_defined_qty']) {
											echo '<div class="fr" style="padding:5px; font-weight:bold; font-size:16px;">
												<div class="fl" style="width:200px">'.language('product', 'LABEL_ADDITIONAL_PRODUCT').'</div>
												<div class="fl" style="width:150px; text-align:right"><span id="combo_additional_total">'.nf_currency($add_products_total).'</span></div>
												<div class="cb" style="clear: both;"></div>
											</div>
											<div class="cb" style="clear: both;"></div>
											';
										}
										
										echo '</div>';
									}
                                    
                                    $prices_js['additional_products'] += $add_products_total;
                                }
                            }					
                            break;
                        // bundle
                        case 2:
                            if ($result_groups = $mysqli->query('SELECT
                            product_bundled_product_group.id,
                            product_bundled_product_group.input_type,
                            product_bundled_product_group.required,
                            product_bundled_product_group_description.name 
                            FROM
                            product_bundled_product_group
                            INNER JOIN 
                            product_bundled_product_group_description
                            ON
                            (product_bundled_product_group.id = product_bundled_product_group_description.id_product_bundled_product_group AND product_bundled_product_group_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")					
                            WHERE
                            product_bundled_product_group.id_product = "'.$mysqli->escape_string($id_product).'"
                            ORDER BY 
                            product_bundled_product_group.sort_order ASC
                            ')) {
                                if ($result_groups->num_rows) {
                                    if (!$stmt_sub_product = $mysqli->prepare('SELECT 
                                    pg_product.id,
                                    pg_product.id_product,
                                    pg_product.id_product_variant,
                                    pg_product.selected,
                                    product_description.name,
									product_description.short_desc,
                                    GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant_name,
                                    pg_product.qty,
                                    pg_product.user_defined_qty,
                                    get_bundle_product_current_price(pg_product.id,"'.$mysqli->escape_string($_SESSION['customer']['id_customer_type']).'") AS sell_price,
                                    is_product_in_stock(t.id, t_variant.id,pg_product.qty) AS in_stock
                                    FROM 
                                    product_bundled_product_group_product AS pg_product
                                    INNER JOIN 
                                    (product_bundled_product_group AS pg CROSS JOIN product AS parent_product)
                                    ON
                                    (pg_product.id_product_bundled_product_group = pg.id AND pg.id_product = parent_product.id)
                                    INNER JOIN 
                                    (product AS t CROSS JOIN product_description)
                                    ON
                                    (pg_product.id_product = t.id AND t.id = product_description.id_product AND product_description.language_code = ?)
                                    LEFT JOIN
                                    (
                                        product_variant AS t_variant
                                        CROSS JOIN
                                        product_variant_option 
                                        CROSS JOIN 
                                        product_variant_group 
                                        CROSS JOIN 
                                        product_variant_group_option 
                                        CROSS JOIN 
                                        product_variant_group_option_description
										CROSS JOIN
										product_variant_group_description
                                    )
                                    ON 
                                    (																
                                        pg_product.id_product_variant = t_variant.id
                                        AND
                                        t_variant.id = product_variant_option.id_product_variant 
                                        AND 
                                        product_variant_option.id_product_variant_group = product_variant_group.id 
                                        AND 
                                        product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
                                        AND 
                                        product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
                                        AND 
                                        product_variant_group_option_description.language_code = product_description.language_code
										AND	
										product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
										AND
										product_variant_group_description.language_code = product_description.language_code
                                    )								
                                    WHERE
                                    pg_product.id_product_bundled_product_group = ?
                                    GROUP BY
                                    pg_product.id
                                    ORDER BY 
                                    pg_product.sort_order ASC')) throw new Exception('An error occured while trying to prepare get sub product statement.'."\r\n\r\n".$mysqli->error);															
                                    
                                    
                                    echo '<div style="border:1px solid #ccc; background-color: #f7f7f7; margin-bottom:20px; padding:5px;" class="row">';
                                    
                                    $product_price = 0;
									$group_products = array();
                                    
                                    while ($row_group = $result_groups->fetch_assoc()) {																							
        
                                        // look for sub products
                                        if (!$stmt_sub_product->bind_param("si", $_SESSION['customer']['language'], $row_group['id'])) throw new Exception('An error occured while trying to bind params to get sub product statement.'."\r\n\r\n".$mysqli->error);
                                        
                                        /* Execute the statement */
                                        $stmt_sub_product->execute();
                                        
                                        /* store result */
                                        $stmt_sub_product->store_result();																																															
                                        
                                        // if we have other variants
                                        if ($stmt_sub_product->num_rows) {										
                                            /* bind result variables */
                                            $stmt_sub_product->bind_result($sub_product_id, $sub_product_id_product, $sub_product_id_product_variant,
											$sub_product_selected, $sub_product_name, $sub_product_short_desc, $sub_product_variant_name, $sub_product_qty, 
											$sub_product_user_defined_qty, $sub_product_sell_price, $sub_product_in_stock);										
                
                                            $output = '';
                                            
                                            $output .= '<div><strong>'.$row_group['name'].'</strong></div>
                                            <div style="border:1px solid #ccc; background-color: #FFF; padding:5px; margin:5px; margin-bottom:10px;overflow:hidden;" class="bundle_group" id="id_product_bundle_group_'.$row_group['id'].'">
                                            <input type="hidden" name="bundle_product['.$row_group['id'].'][input_type]" value="'.$row_group['input_type'].'" />
                                            <div class="fl">
                                            ';																				
                                            
                                            $current_price = '';
                                            $current_selected = '';		
                
                                            // loop through sub products							
                                            while ($stmt_sub_product->fetch()) {
												// if product is in stock
												if ($sub_product_in_stock || isset($modify_product_info['bundle_product'][$row_group['id']]['id']) && is_array($modify_product_info['bundle_product'][$row_group['id']]['id']) && in_array($sub_product_id,$modify_product_info['bundle_product'][$row_group['id']]['id'])) {	
													$group_products[$row_group['id']][] = $sub_product_id;
																								
													$qty = $sub_product_qty;	
													
													// get currently selected bundle product option
													// if none, make sure the selected one by default is checked	
													
													// if we do a post and the id of this product is selected	
													if (isset($_POST['bundle_product'][$row_group['id']]['id']) && is_array($_POST['bundle_product'][$row_group['id']]['id']) && in_array($sub_product_id,$_POST['bundle_product'][$row_group['id']]['id'])) {
														// if we have a custom qty
														if (isset($_POST['bundle_product'][$row_group['id']]['qty'][$sub_product_id])) $qty = $_POST['bundle_product'][$row_group['id']]['qty'][$sub_product_id];
														if ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) $current_price += $qty*$sub_product_sell_price;
														
														
														$selected = 1;
													// if we modify a bundle and the id of this product is selected
													} else if (isset($modify_product_info['bundle_product'][$row_group['id']]['id']) && is_array($modify_product_info['bundle_product'][$row_group['id']]['id']) && in_array($sub_product_id,$modify_product_info['bundle_product'][$row_group['id']]['id'])) {
														
														if (isset($modify_product_info['bundle_product'][$row_group['id']]['qty'][$sub_product_id])) $qty = $modify_product_info['bundle_product'][$row_group['id']]['qty'][$sub_product_id];
														
														if ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) $current_price += $qty*$sub_product_sell_price;
														$selected = 1;
													// if we arent posting or modifying a product in cart and we are simply loading the product and is required and selected
													} else if (!isset($_POST['_add_to_cart']) && !isset($_POST['_add_selected_to_cart']) && !$modify_product && $sub_product_selected){
														if ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) $current_price += $qty*$sub_product_sell_price;
														$selected = 1;																																							
													} else {
														$selected = 0;																																																			
													}
													
													
																																		
													$cover_image = get_product_cover_image($sub_product_id_product, $sub_product_id_product_variant);							
													
													if ($cover_image){
													   $cover_image = '/images/products/thumb/'.$cover_image;
													   $image_size = getimagesize(dirname(__FILE__).$cover_image);
													}else{ 
														$cover_image = get_blank_image('thumb');
														$image_size[3] = ' width="'.$image_thumb_width.'" height="'.$image_thumb_height.'"';
													}
																								
													$output .= '<div style="margin-bottom:5px;"><div class="fl" style="margin-right:5px; width:'.$image_thumb_width.'px"><img src="'.$cover_image.'" '.$image_size[3].' style="display:block;border:1px solid #ddd;" /></div>';
													
													if ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) {
														
														$prices_js['bundle_products'][$row_group['id']][$sub_product_id] = array(
															'sell_price'=>$sub_product_sell_price,
														);			
													} else {
														$prices_js['bundle_products'][$row_group['id']][$sub_product_id] = array(
															'sell_price'=>0,
														);	
													}
													
													$id_cart_item_product = isset($modify_product_info['bundle_product'][$row_group['id']]['id_cart_item_product'][$sub_product_id]) ? $modify_product_info['bundle_product'][$row_group['id']]['id_cart_item_product'][$sub_product_id]:(isset($_POST['bundle_product'][$row_group['id']]['id_cart_item_product'][$sub_product_id]) ? $_POST['bundle_product'][$row_group['id']]['id_cart_item_product'][$sub_product_id]:'');
													
													echo '<input type="hidden" name="bundle_product['.$row_group['id'].'][id_cart_item_product]['.$sub_product_id.']" value="'.$id_cart_item_product.'" />';										
													
													switch ($row_group['input_type']) {
														// radio
														case 1:
															$output .= '<div class="fl">
															<input type="radio" name="bundle_product['.$row_group['id'].'][id][]" id="bundle_product_'.$row_group['id'].'_'.$sub_product_id.'" value="'.$sub_product_id.'" '.($selected ? 'checked="checked"':'').' class="bundle_option"/></div>';
																										
															if ($sub_product_user_defined_qty) {
																$output .= '<div class="fl" style="margin-left: 3px;"><input type="text" name="bundle_product['.$row_group['id'].'][qty]['.$sub_product_id.']" size="1" style="text-align: center;" value="'.$qty.'" class="bundle_option_qty check_qty" /></div>';
															} else {
																$output .= '<input type="hidden" name="bundle_product['.$row_group['id'].'][qty]['.$sub_product_id.']"  value="'.$qty.'" />';
															}
															
															$output .= '<div class="fl" style="margin-left: 3px; width:680px;"><label for="bundle_product_'.$row_group['id'].'_'.$sub_product_id.'">'.$sub_product_name.($sub_product_id_product_variant ? ' ('.$sub_product_variant_name.')':'').($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']?'('.nf_currency($sub_product_sell_price).')':'').' <div style="margin-top: 3px; margin-bottom: 8px">'.$sub_product_short_desc.'</div></label>
															</div>';
															break;
														// checkbox
														case 2:
															$output .= '<div class="fl">
															<input type="checkbox" name="bundle_product['.$row_group['id'].'][id][]" id="bundle_product_'.$row_group['id'].'_'.$sub_product_id.'" value="'.$sub_product_id.'" '.($selected ? 'checked="checked"':'').' class="bundle_option"/></div>';
															
															if ($sub_product_user_defined_qty) {
																$output .= '<div class="fl" style="margin-left: 3px;"><input type="text" name="bundle_product['.$row_group['id'].'][qty]['.$sub_product_id.']" size="1" style="text-align: center;" value="'.$qty.'" class="bundle_option_qty check_qty" /></div>';
															} else {
																$output .= '<input type="hidden" name="bundle_product['.$row_group['id'].'][qty]['.$sub_product_id.']"  value="'.$qty.'" />';
															}
															
															$output .= '<div class="fl" style="margin-left: 3px; width:680px;"><label for="bundle_product_'.$row_group['id'].'_'.$sub_product_id.'">'.$sub_product_name.($sub_product_id_product_variant ? ' ('.$sub_product_variant_name.')':'').($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']?'('.nf_currency($sub_product_sell_price).')':'').' <div style="margin-top: 3px; margin-bottom: 8px">'.$sub_product_short_desc.'</div></label></div>';
															break;												
													}	
													
													$output .= '<div class="cb"  style="clear: both;"></div></div>';
												}
                                            }	
                                                
											if (isset($group_products[$row_group['id']])) {		
												
												switch ($row_group['input_type']) {
													// radio
													case 1:
													
														if (!$row_group['required']) {
															$output .= '<div style="margin-bottom:5px; margin-right:5px; width:'.$images_sizes['thumb_width'].'px; display:block;" class="fl"></div>
															<div class="fl">
															<input type="radio" name="bundle_product['.$row_group['id'].'][id][]" id="bundle_product_'.$row_group['id'].'_0" value="" class="bundle_option"/>&nbsp;<label for="bundle_product_'.$row_group['id'].'_0">'.language('product', 'LABEL_NO_OPTION').'</label>
															</div>';													
														}
														break;
												}				
												
																					
												$product_price += $current_price;							
												
												$output .= '</div>
												'.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']?'<div class="fr"><span id="bundle_product_'.$row_group['id'].'_price">'.($current_price ? nf_currency($current_price):'').'</span></div>':'').'
												<div class="cb"></div>																				
												</div>';                                            
											
	                                            echo $output;
											}
                                        }
                                    }
                                    
                                    $product['current_price'] = $product_price;
									$prices_js['product']['sell_price'] = $product_price;
                                            
                                    echo ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']?'<br /><br />
                                    <div class="fr" style="padding:5px; font-weight:bold; font-size:18px;">
                                        <div class="fl">'.language('product', 'LABEL_BUNDLE_PRICE').'</div>
                                        <div class="fl" id="bundle_price" style="margin-left: 5px;">'.nf_currency($product['current_price']).'</div>
                                        <div class="cb"></div>
                                    </div>':'').'
                                    <div class="cb"></div>
                                    </div>';	
                                    
                                    $stmt_sub_product->close();
                                }
                                
                                $result_groups->free();
                            }					
                        
                            break;	
                    }
                    // get options
                    $show_options = 0;
                    
                    if ($result_option_groups = $mysqli->query('SELECT
                    options_group.id,
                    options_group.input_type,
                    options_group.maxlength,
                    options_group.from_to,
                    options_group.user_defined_qty,
                    options_group_description.name,
                    options_group_description.description			
                    FROM
                    product_options_group
                    INNER JOIN 
                    (options_group CROSS JOIN options_group_description)
                    ON
                    (product_options_group.id_options_group = options_group.id AND options_group.id = options_group_description.id_options_group AND options_group_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
                    WHERE
                    product_options_group.id_product = "'.$mysqli->escape_string($id_product).'"
                    AND
                    (SELECT 
                    COUNT(options.id) 
                    FROM
                    options
                    WHERE
                    options.id_options_group = options_group.id 
					AND 
					is_option_in_stock(options.id) = 1) > 0			
                    ORDER BY
                    product_options_group.sort_order ASC
                    ')) {
                        $total_extras = $result_option_groups->num_rows;
						if ($total_extras) {
                            if (!$stmt_options = $mysqli->prepare('SELECT 
                            options.id,
                            options.sku,
                            options.cost_price,
                            options.price,
                            options.price_type,
                            options.special_price,
                            IF("'.$mysqli->escape_string($current_datetime).'" BETWEEN options.special_price_from_date AND options.special_price_to_date,options.special_price,options.price) AS sell_price,
                            options_description.name			
                            FROM 
                            options
                            INNER JOIN
                            options_description
                            ON
                            (options.id = options_description.id_options AND options_description.language_code = ?)
                            WHERE
                            options.id_options_group = ?
                            AND
							(is_option_in_stock(options.id) = 1 OR options.id IN (?))
                            ORDER BY 
                            options.sort_order ASC')) throw new Exception('An error occured while trying to prepare get options statement.'."\r\n\r\n".$mysqli->error);
                            
                            
                            $show_options = 1;
                            echo '<h2 class="subtitle"><span class="inline-title">'.language('product', 'LABEL_GET_THE_MOST_EXTRA').'</span></h2>
                            <p class="desc">'.language('product', 'LABEL_CUSTOMER_WHO_BOUGHT_ALSO_BOUGHT').'</p>
                            ';
                            
                            $add_extra_total = 0;
                            while ($row_option_group = $result_option_groups->fetch_assoc()) {
                                $selected = (isset($_POST['add_option'][$row_option_group['id']]) || isset($modify_product_info['add_option'][$row_option_group['id']])) ? 1:0;
                                
                                echo '<div class="additional_items_parent '.($selected ? 'selected':'').'">																				
                                    <div class="fl"><strong>'.$row_option_group['name'].'</strong>'.(!empty($row_option_group['description']) ? '<div style="margin-top:5px;"><em>'.$row_option_group['description'].'</em></div>':'').'</div>
                                    
									
									
                                    <div class="fr" style="background-color:#F2F2F2; width:25px;">
                                        <div style="padding: 5px;">
                                            <input type="checkbox" value="'.$row_option_group['id'].'" name="add_option['.$row_option_group['id'].']" class="additional_items_checkbox" '.($selected  ? 'checked="checked"':'').' />
                                            <input type="hidden" name="options['.$row_option_group['id'].'][input_type]" value="'.$row_option_group['input_type'].'" />
                                        </div>
                                        <div class="cb"></div>
                                    </div>';
                            
                                echo '<div class="fr" style="margin-right:20px;">';
								
								$options_ids = isset($modify_product_info['options'][$row_option_group['id']]['id']) && is_array($modify_product_info['options'][$row_option_group['id']]['id']) ? implode(',',$modify_product_info['options'][$row_option_group['id']]['id']):0;
                                
                                // look for sub products
                                if (!$stmt_options->bind_param("sis", $_SESSION['customer']['language'], $row_option_group['id'],$options_ids)) throw new Exception('An error occured while trying to bind params to get sub product statement.'."\r\n\r\n".$mysqli->error);
                                
                                /* Execute the statement */
                                $stmt_options->execute();
                                
                                /* store result */
                                $stmt_options->store_result();																											
                                
                                // if we have options
                                if ($stmt_options->num_rows) {										
                                    /* bind result variables */
                                    $stmt_options->bind_result($options_id, $options_sku, $options_cost_price, $options_price, $options_price_type, 
                                    $options_special_price, $options_sell_price, $options_name);	
									
									if ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) {
									} else {
										$options_cost_price = 0;
										$options_price = 0;
										$options_special_price = 0;
										$options_sell_price = 0;
									}
        
                                    $output = '';
                                                                    
                                    switch ($row_option_group['input_type']) {
                                        // dropdown									
                                        case 0:
                                            $output .= '<div align="right"><select name="options['.$row_option_group['id'].'][id][]" '.(!$selected ? 'disabled="disabled"':'').' class="additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']]) ? 'error':'').'"><option value="">--</option>';
                                            break;
                                        // multi-select
                                        case 4:
                                            $output .= '<div align="right"><select name="options['.$row_option_group['id'].'][id][]" multiple="multiple" size="10" '.(!$selected ? 'disabled="disabled"':'').' class="additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']]) ? 'error':'').'">';
                                            break;
                                    }														
                                    
                                    $prices = '';										
                                    while ($stmt_options->fetch()) {
										switch ($options_price_type){
											// fixed
											case 0:
												break;
											// percent
											case 1:
												$options_price = round(($product['current_price']*$options_price)/100,2);
												$options_sell_price = round(($product['current_price']*$options_sell_price)/100,2);
												break;	
										}
										
									
										
										$prices_js['options'][$row_option_group['id']][$options_id] = array(
											'price' => $options_price,
											'sell_price' => $options_sell_price,
										);
										
                                        echo '<input type="hidden" name="options['.$row_option_group['id'].'][id_cart_item_option]['.$options_id.']" value="'.(isset($modify_product_info['options'][$row_option_group['id']]['id_cart_item_option'][$options_id]) ? $modify_product_info['options'][$row_option_group['id']]['id_cart_item_option'][$options_id]:'').'" />';
                                        
                                        $selected_id = ((isset($_POST['options'][$row_option_group['id']]['id']) && is_array($_POST['options'][$row_option_group['id']]['id']) && in_array($options_id,$_POST['options'][$row_option_group['id']]['id'])) || (isset($modify_product_info['options'][$row_option_group['id']]['id']) && is_array($modify_product_info['options'][$row_option_group['id']]['id']) && in_array($options_id,$modify_product_info['options'][$row_option_group['id']]['id']))) ? 1:0;
                                        
                                        $qty=1; 
                                        
                                        if ($selected && $selected_id) {
                                            if (isset($_POST['options'][$row_option_group['id']]['qty'][$options_id])) $qty = $_POST['options'][$row_option_group['id']]['qty'][$options_id];
                                            else if (isset($modify_product_info['options'][$row_option_group['id']]['qty'][$options_id])) $qty = $modify_product_info['options'][$row_option_group['id']]['qty'][$options_id];
                                        }
                                        
                                        $add_extra_total += ($selected && $selected_id) ? $qty*$options_sell_price:0;		
                                                                                                                                            
                                        switch ($row_option_group['input_type']) {
                                            // dropdown
                                            case 0:																			
                                                $output .= '<option value="'.$options_id.'" '.($selected && $selected_id ? 'selected="selected"':'').'>'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' - '.nf_currency($options_sell_price):'').'</option>';
                                                break;
                                            // radio
                                            case 1:
                                                $output .= '<div align="right" style="padding-bottom:5px;"><label>'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ?' - '.nf_currency($options_sell_price):'').'</label>';
                                                
                                                if ($row_option_group['user_defined_qty']) {																						
                                                    $output .= '&nbsp;<input type="text" name="options['.$row_option_group['id'].'][qty]['.$options_id.']" size="1" style="text-align: center;" value="'.$qty.'" class="additional_items_option check_qty" '.(!$selected ? 'disabled="disabled"':'').' />';
                                                }											
                                                
                                                $output .= '&nbsp;&nbsp;<input type="radio" name="options['.$row_option_group['id'].'][id][]" value="'.$options_id.'" '.(!$selected ? 'disabled="disabled"':'').' class="additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']]) ? 'error':'').'" '.($selected && $selected_id ? 'checked="checked"':'').' />
                                                </div>';
                                                break;
                                            // checkbox
                                            case 3:
                                                $output .= '<div align="right" style="padding-bottom:5px;"><label>'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' - '.nf_currency($options_sell_price):'').'</label>';
                                                                                        
                                                if ($row_option_group['user_defined_qty']) {																						
                                                    $output .= '&nbsp;<input type="text" name="options['.$row_option_group['id'].'][qty]['.$options_id.']" size="1" style="text-align: center;" value="'.$qty.'" class="additional_items_option check_qty" '.(!$selected ? 'disabled="disabled"':'').' />';
                                                }		
                                                
                                                $output .= '&nbsp;&nbsp;<input type="checkbox" name="options['.$row_option_group['id'].'][id][]" value="'.$options_id.'" '.(!$selected ? 'disabled="disabled"':'').' class="additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']]) ? 'error':'').'" '.($selected && $selected_id ? 'checked="checked"':'').' />
                                                </div>';
                                                break;
                                            // multi-select
                                            case 4:										
                                                $output .= '<option value="'.$options_id.'" '.($selected && $selected_id ? 'selected="selected"':'').'>'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' - '.nf_currency($options_sell_price):'').'</option>';
                                                break;
                                            // textfield
                                            case 5:
                                                $value = '';
                                                
                                                if ($selected) {
                                                    if (isset($_POST['options'][$row_option_group['id']]['textfield'][$options_id])) $value = htmlspecialchars($_POST['options'][$row_option_group['id']]['textfield'][$options_id]);
                                                    else if (isset($modify_product_info['options'][$row_option_group['id']]['textfield'][$options_id])) $value = htmlspecialchars($modify_product_info['options'][$row_option_group['id']]['textfield'][$options_id]);
                                                }
                                            
                                                $output .= '<div align="right" style="padding-bottom:5px;">
                                                <div style="margin-bottom:5px;">'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' - '.nf_currency($options_sell_price):'').'</div>
                                                <input type="text" name="options['.$row_option_group['id'].'][textfield]['.$options_id.']" value="'.$value.'" style="width:325px;" '.(!$selected ? 'disabled="disabled"':'').' class="additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]) ? 'error':'').'" />'.
                                                (isset($errors_fields['add_option'][$row_option_group['id']][$options_id]) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']][$options_id].'</span></p>':'').'
                                                <input type="hidden" name="options['.$row_option_group['id'].'][id][]" value="'.$options_id.'" />											
                                                </div>';
                                                break; 
                                            // textarea
                                            case 6:										
                                                $value = '';
                                                
                                                if ($selected) {
                                                    if (isset($_POST['options'][$row_option_group['id']]['textarea'][$options_id])) $value = htmlspecialchars($_POST['options'][$row_option_group['id']]['textarea'][$options_id]);
                                                    else if (isset($modify_product_info['options'][$row_option_group['id']]['textarea'][$options_id])) $value = htmlspecialchars($modify_product_info['options'][$row_option_group['id']]['textarea'][$options_id]);
                                                }										
                                            
                                                $output .= '<div align="right" style="padding-bottom:5px;">
                                                <div style="margin-bottom:5px;">'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' - '.nf_currency($options_sell_price):'').'</div>
                                                <textarea name="options['.$row_option_group['id'].'][textarea]['.$options_id.']" rows="5" style="width:325px;" '.(!$selected ? 'disabled="disabled"':'').' class="additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]) ? 'error':'').'">'.$value.'</textarea>'.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']][$options_id].'</span></p>':'').'
                                                <input type="hidden" name="options['.$row_option_group['id'].'][id][]" value="'.$options_id.'" />
            
                                                </div>';										
                                                break;
                                            // file
                                            case 7:
                                                $key = $form_uid.'-'.$row_option_group['id'].'-'.$options_id;
                                            
                                                $output.= '<div align="right" style="padding-bottom:5px;">
                                                <div style="margin-bottom:5px;">'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' - '.nf_currency($options_sell_price):'').'</div>
												
                                                <input type="file" name="options['.$row_option_group['id'].'][file]['.$options_id.']" '.(!$selected ? 'disabled="disabled"':'').' class="additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]) ? 'error':'').'" size="40" />'.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']][$options_id].'</span></p>':'').
                                                (isset($_SESSION['customer']['tmp_uploads'][$key]) && is_file($tmp_uploads_dir.$_SESSION['customer']['tmp_uploads'][$key]['tmp_name']) ? '<div style="margin-top:5px;">Uploaded file: <a href="/tmp_uploads/'.$_SESSION['customer']['tmp_uploads'][$key]['tmp_name'].'" target="_blank">'.$_SESSION['customer']['tmp_uploads'][$key]['name'].'</a></div>':'')
                                                .'
                                                <input type="hidden" name="options['.$row_option_group['id'].'][id][]" value="'.$options_id.'" />
                                                
                                                </div>';
                                                break;
                                            // date
                                            case 8:																				
                                                $value = '';
                                                $value_to = '';
                                                
                                                if ($selected) {
                                                    if (isset($_POST['options'][$row_option_group['id']]['date'][$options_id])) $value = $_POST['options'][$row_option_group['id']]['date'][$options_id];
                                                    else if (isset($modify_product_info['options'][$row_option_group['id']]['date'][$options_id])) $value = $modify_product_info['options'][$row_option_group['id']]['date'][$options_id];
                                                    
                                                    if (isset($_POST['options'][$row_option_group['id']]['date_to'][$options_id])) $value_to = $_POST['options'][$row_option_group['id']]['date_to'][$options_id];
                                                    else if (isset($modify_product_info['options'][$row_option_group['id']]['date_to'][$options_id])) $value_to = $modify_product_info['options'][$row_option_group['id']]['date_to'][$options_id];
                                                }											
                                            
                                                // no range
                                                if (!$row_option_group['from_to']) {											
                                                    $output.= '<div align="right" style="padding-bottom:5px;">
                                                    <div style="margin-bottom:5px;">'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' - '.nf_currency($options_sell_price):'').'</div>
                                                    <input type="text" name="options['.$row_option_group['id'].'][date]['.$options_id.']" size="10" '.(!$selected ? 'disabled="disabled"':'').' class="calendar_date additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['date']) ? 'error':'').'" readonly="readonly" value="'.$value.'" />'.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['date']) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']][$options_id]['date'].'</span></p>':'').'
                                                    <input type="hidden" name="options['.$row_option_group['id'].'][id][]" value="'.$options_id.'" />
                                                    
                                                    </div>';																					
                                                // yes
                                                } else {
                                                    $output.= '<div align="right" style="padding-bottom:5px;">
                                                    <div style="margin-bottom:5px;">'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' - '.nf_currency($options_sell_price):'').'</div>
                                                    <div style="margin-bottom:5px;">'.language('product', 'LABEL_FROM').'&nbsp;<input type="text" name="options['.$row_option_group['id'].'][date]['.$options_id.']" size="10" '.(!$selected ? 'disabled="disabled"':'').' class="calendar_date additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['date']) ? 'error':'').'" readonly="readonly" value="'.$value.'" /></div>'.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['date']) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']][$options_id]['date'].'</span></p>':'').'
                                                    <div style="margin-bottom:5px;">'.language('product', 'LABEL_TO').'&nbsp;<input type="text" name="options['.$row_option_group['id'].'][date_to]['.$options_id.']" size="10" '.(!$selected ? 'disabled="disabled"':'').' class="calendar_date additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['date_to']) ? 'error':'').'" readonly="readonly" value="'.$value_to.'" /></div>'.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['date_to']) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']][$options_id]['date_to'].'</span></p>':'').'
                                                    <input type="hidden" name="options['.$row_option_group['id'].'][id][]" value="'.$options_id.'" />
                                                    
                                                    </div>';									
                                                }
                                                break;
                                            // date & time
                                            case 9:		
                                                $value = '';
                                                $value_to = '';									
                                                                            
                                                if ($selected) {
                                                    if (isset($_POST['options'][$row_option_group['id']]['datetime'][$options_id])) $value = $_POST['options'][$row_option_group['id']]['datetime'][$options_id];
                                                    else if (isset($modify_product_info['options'][$row_option_group['id']]['datetime'][$options_id])) $value = $modify_product_info['options'][$row_option_group['id']]['datetime'][$options_id];
                                                    
                                                    if (isset($_POST['options'][$row_option_group['id']]['datetime_to'][$options_id])) $value_to = $_POST['options'][$row_option_group['id']]['datetime_to'][$options_id];
                                                    else if (isset($modify_product_info['options'][$row_option_group['id']]['datetime_to'][$options_id])) $value_to = $modify_product_info['options'][$row_option_group['id']]['datetime_to'][$options_id];
                                                }													
                                            
                                                // no range
                                                if (!$row_option_group['from_to']) {											
                                                    $output.= '<div align="right" style="padding-bottom:5px;">
                                                    <div style="margin-bottom:5px;">'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' - '.nf_currency($options_sell_price):'').'</div>
                                                    <input type="text" name="options['.$row_option_group['id'].'][datetime]['.$options_id.']" size="20" '.(!$selected ? 'disabled="disabled"':'').' class="calendar_datetime additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['datetime']) ? 'error':'').'" readonly="readonly" value="'.$value.'" />'.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['datetime']) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']][$options_id]['datetime'].'</span></p>':'').'
                                                    <input type="hidden" name="options['.$row_option_group['id'].'][id][]" value="'.$options_id.'" />
                                                    
                                                    </div>';
                                                // yes
                                                } else {
                                                    $output.= '<div align="right" style="padding-bottom:5px;">
                                                    <div style="margin-bottom:5px;">'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' - '.nf_currency($options_sell_price):'').'</div>
                                                    <div style="margin-bottom:5px;">'.language('product', 'LABEL_FROM').'&nbsp;<input type="text" name="options['.$row_option_group['id'].'][datetime]['.$options_id.']" size="10" '.(!$selected ? 'disabled="disabled"':'').' class="calendar_datetime additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['datetime']) ? 'error':'').'" readonly="readonly" value="'.$value.'" /></div>'.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['datetime']) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']][$options_id]['datetime'].'</span></p>':'').'
                                                    <div style="margin-bottom:5px;">'.language('product', 'LABEL_TO').'&nbsp;<input type="text" name="options['.$row_option_group['id'].'][datetime]['.$options_id.']" size="10" '.(!$selected ? 'disabled="disabled"':'').' class="calendar_datetime additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['datetime_to']) ? 'error':'').'" readonly="readonly" value="'.$value_to.'" /></div>'.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['datetime_to']) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']][$options_id]['datetime_to'].'</span></p>':'').'
                                                    <input type="hidden" name="options['.$row_option_group['id'].'][id][]" value="'.$options_id.'" />
                                                    
                                                    </div>';											
                                                }								
                                                break;
                                            // time
                                            case 10:
                                                $value = '';
                                                $value_to = '';									
                                            
                                                if ($selected) {
                                                    if (isset($_POST['options'][$row_option_group['id']]['time'][$options_id])) $value = $_POST['options'][$row_option_group['id']]['time'][$options_id];
                                                    else if (isset($modify_product_info['options'][$row_option_group['id']]['time'][$options_id])) $value = $modify_product_info['options'][$row_option_group['id']]['time'][$options_id];
                                                    
                                                    if (isset($_POST['options'][$row_option_group['id']]['time_to'][$options_id])) $value_to = $_POST['options'][$row_option_group['id']]['time_to'][$options_id];
                                                    else if (isset($modify_product_info['options'][$row_option_group['id']]['time_to'][$options_id])) $value_to = $modify_product_info['options'][$row_option_group['id']]['time_to'][$options_id];
                                                }	
                                                
                                                // no range
                                                if (!$row_option_group['from_to']) {											
                                                    $output.= '<div align="right" style="padding-bottom:5px;">
                                                    <div style="margin-bottom:5px;">'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' - '.nf_currency($options_sell_price):'').'</div>
                                                    <input type="text" name="options['.$row_option_group['id'].'][time]['.$options_id.']" size="10" '.(!$selected ? 'disabled="disabled"':'').' class="calendar_time additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['time']) ? 'error':'').'" readonly="readonly" value="'.$value.'" />'.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['time']) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']][$options_id]['time'].'</span></p>':'').'
                                                    <input type="hidden" name="options['.$row_option_group['id'].'][id][]" value="'.$options_id.'" />
                                                    
                                                    </div>';
                                                // yes
                                                } else {
                                                    $output.= '<div align="right" style="padding-bottom:5px;">
                                                    <div style="margin-bottom:5px;">'.$options_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' - '.nf_currency($options_sell_price):'').'</div>
                                                    <div style="margin-bottom:5px;">'.language('product', 'LABEL_FROM').'&nbsp;<input type="text" name="options['.$row_option_group['id'].'][time]['.$options_id.']" size="10" '.(!$selected ? 'disabled="disabled"':'').' class="calendar_time additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['time']) ? 'error':'').'" readonly="readonly" value="'.$value.'" /></div>'.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['time']) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']][$options_id]['time'].'</span></p>':'').'
                                                    <div style="margin-bottom:5px;">'.language('product', 'LABEL_TO').'&nbsp;<input type="text" name="options['.$row_option_group['id'].'][time_to]['.$options_id.']" size="10" '.(!$selected ? 'disabled="disabled"':'').' class="calendar_time additional_items_option '.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['time_to']) ? 'error':'').'" readonly="readonly" value="'.$value_to.'" /></div>'.(isset($errors_fields['add_option'][$row_option_group['id']][$options_id]['time_to']) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']][$options_id]['time_to'].'</span></p>':'').'
                                                    <input type="hidden" name="options['.$row_option_group['id'].'][id][]" value="'.$options_id.'" />
                                                    
                                                    </div>';										
                                                }										
                                                break;
                                        }																													
                                    }
                                    
                                    switch ($row_option_group['input_type']) {
                                        // dropdown									
                                        case 0:
                                            $output.= '</select>'.(isset($errors_fields['add_option'][$row_option_group['id']]) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']].'</span></p>':'').'</div>';
                                            break;
                                        // radio
                                        case 1:
                                            $output .= (isset($errors_fields['add_option'][$row_option_group['id']]) ? '<p align="right"><span class="error">'.$errors_fields['add_option'][$row_option_group['id']].'</span></p>':'');
                                            break;
                                        // checkbox
                                        case 3:
                                            $output .= (isset($errors_fields['add_option'][$row_option_group['id']]) ? '<p align="right"><span class="error">'.$errors_fields['add_option'][$row_option_group['id']].'</span></p>':'');
                                            break;
                                        // multi-select
                                        case 4:
                                            $output.= '</select>'.(isset($errors_fields['add_option'][$row_option_group['id']]) ? '<p><span class="error">'.$errors_fields['add_option'][$row_option_group['id']].'</span></p>':'').'</div>';
                                            break;										
                                    }								
                                }
                                
								echo $output;
                                
                                echo '</div>
                                        
                                    <div class="cb"></div>
                                </div>';
                            }
                            
                            echo '</div>';
                            
                            $stmt_options->close();
                        }
                        
                        $result_option_groups->free();
                    }
                    //Related Product
					$total_related = $products_class->count_products(array('related_products'=>$id_product));
					$results = $products_class->get_products(array('related_products'=>$id_product),0,0,0);	
					if ($total_related) {
                            /* Prepare the statement */					
                            if (!$stmt_related_product_variant = $mysqli->prepare('SELECT
                            product_variant.id,
                            
							calc_sell_price(product.price,product_variant.price_type,product_variant.price,0,0,0,0) AS price,
							calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,customer_type.percent_discount,customer_type.apply_on_rebate,rebate_coupon.discount_type,rebate_coupon.discount) AS sell_price,			
                            GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS name
                            FROM
                            product_variant
                            INNER JOIN 
                            (product_variant_option 
                            CROSS JOIN product_variant_group 
                            CROSS JOIN product_variant_group_option 
                            CROSS JOIN product_variant_group_option_description
							CROSS JOIN product_variant_group_description)
                            ON 
                            (product_variant.id = product_variant_option.id_product_variant 
                            AND product_variant_option.id_product_variant_group = product_variant_group.id 
                            AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
                            AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
                            AND product_variant_group_option_description.language_code = ?
							AND	product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
							AND product_variant_group_description.language_code = product_variant_group_option_description.language_code)
							
							INNER JOIN
							product
							ON
							(product_variant.id_product = product.id)
							
							LEFT JOIN
							customer_type
							ON
							(customer_type.id = "'.$mysqli->escape_string($_SESSION['customer']['id_customer_type']).'")
							
							LEFT JOIN
							rebate_coupon 
							ON
							(product.id_rebate_coupon = rebate_coupon.id) 							
							
                            WHERE
                            product_variant.id_product = ?
                            AND
                            is_product_in_stock(product_variant.id_product,product_variant.id,0) = 1
                            GROUP BY
                            product_variant.id
                            ORDER BY
                            product_variant.sort_order ASC')) throw new Exception('An error occured while trying to prepare get related product variants statement.'."\r\n\r\n".$mysqli->error);						
                        
                            $show_options = 1;
                                    
                            echo '<h2 class="subtitle" style="margin-top: 20px;"><span class="inline-title">'.language('product', 'LABEL_FREQUENTLY_BOUGHT_TOGETHER').'</span><div style="left: 321px;" class="line"></div></h2>';
                           foreach ($results as $row_related) {
                                $save = $row_related['price']-$row_related['sell_price'];
                                
								if ($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) {
									
									$prices_js['related_products'][$row_related['id']] = array(
										'sell_price' => $row_related['sell_price'],
										'save' => $save,
										'total_price' => ($row_related['sell_price']),
									);																
								} else {
									$prices_js['related_products'][$row_related['id']] = array(
										'sell_price' => 0,
										'save' => 0,
										'total_price' => 0,
									);																									
								}
                                
                                // need to fix with variant
                                //if (isset($_POST['add_related_product'][$row_related['id']]['id']) && !$row_related['has_variants']) $add_products_total += ($row_related['sell_price']-$save);
                                if (isset($_POST['add_related_product'][$row_related['id']]['id']) && !$row_related['has_variants']) $add_products_total += $row_related['sell_price'];
                                        
                                
								if ($row_related['image_thumb']){
								   $cover_image = $row_related['image_thumb'];
								   $image_size = getimagesize(dirname(__FILE__).$row_related['image_thumb']);
								}else{ 
									$cover_image = get_blank_image('thumb');
									$image_size[3] = ' width="'.$image_thumb_width.'" height="'.$image_thumb_height.'"';
								}
                                        
                                echo '<div class="additional_items_parent '.(isset($_POST['add_related_product'][$row_related['id']]['id']) ? 'selected':'').'">													
                                    <div class="fl" style="margin-right:5px; width:'.$image_thumb_width.'px"><a href="'.$row_related['url'].'"><img src="'.$cover_image.'" '.$image_size[3].' style="display:block;border:1px solid #ddd;" /></a></div>
                                    
                                    <div class="fl" style="width: 525px;"><a href="'.$row_related['url'].'">'.$row_related['name'].'</a>';
                                    
                                    // get list of variants available
                                    if ($row_related['has_variants']) {
                                        if (!$stmt_related_product_variant->bind_param("si", $_SESSION['customer']['language'], $row_related['id'])) throw new Exception('An error occured while trying to bind params to get related product variants statement.'."\r\n\r\n".$this->mysqli->error);
                                        
                                        /* Execute the statement */
                                        if (!$stmt_related_product_variant->execute()) throw new Exception('An error occured while trying to get related product variants statement.'."\r\n\r\n".$this->mysqli->error);	
                                        
                                        /* store result */
                                        $stmt_related_product_variant->store_result();		
                                        
                                        if ($stmt_related_product_variant->num_rows) {	
                                            $selected = isset($_POST['add_related_product'][$row_related['id']]['id']) && isset($_POST['add_related_product'][$row_related['id']]['id_product_variant']) ? $_POST['add_related_product'][$row_related['id']]['id_product_variant']:'';	
                                                                                
                                            // Verifiy if display_price is true
											if($config_site['display_price']){
											                                  
												echo '<div style="margin-top:5px;"><select name="add_related_product['.$row_related['id'].'][id_product_variant]" class="related_product_variant additional_items_option" '.(!isset($_POST['add_related_product'][$row_related['id']]['id']) ? 'disabled="disabled"':'').'>
												<option value="">--</option>';
																	
												/* bind result variables */
												$stmt_related_product_variant->bind_result($related_product_variant_id, $related_product_variant_price, $related_product_variant_sell_price, $related_product_variant_name);																											
												
												$save_variant = 0;
												while ($stmt_related_product_variant->fetch()) {																						
													$save_variant = $related_product_variant_price-$related_product_variant_sell_price;
													
													$prices_js['related_products'][$row_related['id']]['variants'][$related_product_variant_id] = array(
														'sell_price' => $related_product_variant_sell_price,
														'save' => $save_variant,
														'total_price' => ($related_product_variant_sell_price)
													);	
													
													echo '<option value="'.$related_product_variant_id.'" '.($selected == $related_product_variant_id ? 'selected="selected"':'').'>'.$related_product_variant_name.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception'] ? ' ('.nf_currency($related_product_variant_sell_price).')':'').'</option>';
													
													if (isset($_POST['add_related_product'][$row_related['id']]) && $selected == $related_product_variant_id) {
														$save = $save_variant;
														$add_products_total += $related_product_variant_sell_price;
														//$add_products_total += ($related_product_variant_sell_price-$save_variant);
														$row_related['sell_price'] = $related_product_variant_sell_price;                                                    
													}
												}
												
												echo '</select></div>';
											
											}
                                        }
                                    }
                                    
                                    if (isset($errors_fields['add_related_product'][$row_related['id']])) echo '<p><span class="error">'.$errors_fields['add_related_product'][$row_related['id']].'</span></p>';
                                    
                                     echo (!empty($row_related['short_desc']) ? '<p>'.$row_related['short_desc'].'</p>':'').'</div>																											
                                    <div class="fr" style="background-color:#F2F2F2; width:25px;">
                                        <div style="padding: 5px;">
                                            <input type="checkbox" value="'.$row_related['id'].'" name="add_related_product['.$row_related['id'].'][id]" class="additional_items_checkbox" '.(isset($_POST['add_related_product'][$row_related['id']]['id']) ? 'checked="checked"':'').' />
                                        </div>
                                        <div class="cb"></div>
                                    </div>
                                    
                                    <div class="fr" style="margin-right:20px;">								
                                        '.
									// Verifiy if display_price is true
									($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']?'<div align="right" style="font-size:16px;" class="related_product_price'.($row_related['price'] != $row_related['sell_price'] ?' special_price':'').'">'.nf_currency($row_related['sell_price']).'</div>':'');
										
																		
                                        /*
                                        if ($save > 0) {									
                                            echo '<div align="right" class="saving_price">
                                            Save an extra <span class="related_product_save">'.nf_currency($save).'</span> when purchased together.
                                            </div>';
                                        }*/
                                        
                                echo '</div>
								
									<div class="cb"></div>																		
                                        
                                    <div class="cb"></div>
                                </div>';																			
                            }
                            
                            echo '</div>';
                            
                            $stmt_related_product_variant->close();
                    } 
                        
				// PRICE AT THE BOTTOM
				if ($config_site['allow_add_to_cart'] && !$product['allow_add_to_cart_exceptions'] || !$config_site['allow_add_to_cart'] && $product['allow_add_to_cart_exceptions'] || $config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']) {
                    
                   // make this float			
                    echo '<div class="noprint" style="padding:10px; border-top:1px solid #ccc; margin-bottom:20px; background-color:#FFF; overflow: hidden;">				
                        
						<div class="fr" id="add_to_cart_bottom">'.get_add_cart_button_bottom().'</div>
						
                        '.($config_site['display_price'] && !$product['display_price_exception'] || !$config_site['display_price'] && $product['display_price_exception']?'    
                        <div class="fr" style="margin-right:20px; font-size:16px; width: 300px">
                            		
                                <div class="fl">'.language('product', 'LABEL_FEATURED_PRODUCT').'</div>
								<div class="fr" align="right" id="featured_product_price" style="width:140px">'.nf_currency($product['current_price']).'</div>
								<div class="cb"></div>
                                <div '.($total_extras?'':'style="display:none"').'>
                                <div class="fl">'.language('product', 'LABEL_EXTRAS').'</div>
								<div class="fr" align="right" id="extra_total" style="width:140px;">'.nf_currency($add_extra_total).'</div>
                               	<div class="cb"></div>
								</div>
								<div '.($total_related?'':'style="display:none"').'>
							    <div class="fl">'.language('product', 'LABEL_ADDITIONAL_PRODUCT').'</div>
							    <div class="fr" align="right" id="add_products_total" style="width:140px;">'.nf_currency($add_products_total).'</div>
								
                                <div class="cb"></div>
								</div>
								<div class="fl" style="font-weight:bold; margin-top:10px;">'.language('product', 'LABEL_TOTAL').'</div>
								<div class="fr" align="right" id="grand_total" style="font-weight:bold; margin-top:10px;width:140px;">'.nf_currency($product['current_price']+$add_extra_total+$add_products_total).'</div>
                            
                        </div>':'').'			
                        <div class="cb"></div>
                    </div>';	
				}
                   ?>    
              				        <?php 
              				        $total_products = $products_class->count_products(array('suggested_products'=>$id_product));
					                $countUpsells = $total_products;
              				        $results = $products_class->get_products(array('suggested_products'=>$id_product),0,0,0);	
					                if ($total_products) {	
              				        ?>
              				        <div class="box-collateral box-up-sell">
              				    		<h2 class="subtitle"><span class="inline-title"><?php echo language('product', 'LABEL_WE_ALSO_SUGGEST');?></span><div style="left: 157px;" class="line"></div></h2>
              				    		<!-- <p class="desc">You may also be interested in the following product(s).</p> -->
              				    		<div id="upsell-products-list" class="upsell-products products-grid flexslider large-icons">
                        				    <div style="overflow: hidden; position: relative;" class="flex-viewport">
                        				    	<ul style="width: 1200%; transition-duration: 0s; transform: translate3d(-570px, 0px, 0px);" class="slides last odd">
                        				    		<?php foreach ($results as $row) {?>
                        				    		<li style="width: 285px; float: left; display: block;" class="item"><div class="item-inner">
                        				            	<?php echo dsp_product($row);?>
                        				            </li>
                        				            <?php }?>
                        				    	</ul>
                        				    </div>
                        				    <ul class="flex-direction-nav"><li><a class="flex-prev" href="#">Previous</a></li><li><a class="flex-next flex-disabled" href="#">Next</a></li></ul>
                        				</div>
              				    		<script type="text/javascript">decorateList('upsell-products-list', 'none-recursive')</script>
                    				    <script type="text/javascript">
                    				    //<![CDATA[
                    				    jQuery(function($) {
                    				        var bp = 963;
                    				        if (jQuery('body').hasClass('bv3'))
                    				            bp = 975;
                    				        jQuery('#upsell-products-list').flexslider({
                    				            controlNav: false,
                    				            animation: 'slide',
                    				            animationLoop: false,
                    				            minItems: 4,
                    				            maxItems: 4,
                    				            itemWidth: 228
                    				        })
                    				        .data("break_default", [4, 228])
                    				        .data("break_points", [ [bp, 3, 190], [750, 2, 190], [530, 1, 228] ] );
                    				    });
                    				    //]]>
                    				    </script>
                    				</div>
                    				<?php }?>
				
				        
				    </div>
				</div>
				</form>
        	</div>
     	</div>
    </div>
</div>
<script type="text/javascript">
var product_prices = <?php echo json_encode($prices_js); ?>;

jQuery(function($) {
	<?php if(isset($_GET["addReview"]) || isset($arr_save['success']) || !empty($err_review_requied_fields)) {?>
	setTimeout(function() {
    jQuery('#goto-reviews-form').click();
    jQuery(document).scrollTop( jQuery("#customer-reviews").offset().top);},801);
	<?php }?>
	jQuery( "#price_alert" ).dialog({
		autoOpen: false,
		height: 600,
		width: 900,
		show: "fade",
		hide: "fade",
		position: 'top',
		resizable: false,
		modal: true
	});	
		
	jQuery("#add_price_alert").on("click",function(){
		<?php if (isset($_SESSION['customer']['id']) && $_SESSION['customer']['id']) { ?>				
		jQuery.ajax({
			url: "/_includes/ajax/price_alert",
			data: { "id":"<?php echo $id_product; ?>" },
			cache:false,
			success: function(data) {
				jQuery("#price_alert").html("").append(data).dialog( "open" );
			}
		});
		<?php } else { ?>
		window.location.href="<?php echo '/account/login?return='.urlencode(urldecode($_SERVER['REQUEST_URI'].(strstr($_SERVER['REQUEST_URI'],'?') ? '&':'?').'task=add_price_alert'));?>";
		<?php } ?>
	});
	
	<?php echo (isset($_GET['task']) && $_GET['task'] == 'add_price_alert') ? 'jQuery("#add_price_alert").trigger("click");':''; ?>	
});
</script>
<style>
.mousetrap {
    cursor: pointer !important;
}
<?php if($countUpsells <4) {?>
@media (min-width: 1200px) {
#upsell-products-list .flex-direction-nav li {display:none};
}
<?php }?>
</style>
<?php include(dirname(__FILE__)."/_includes/template/bottom.php");?>
<div id="price_alert" title="<?php echo language('product', 'TITLE_WINDOW_PRICE_ALERT');?>" style="display:none;font-size: 14px;"></div>
<script type="text/javascript">
if (typeof addthis_config !== "undefined") {
addthis_config.services_exclude = 'print'
} else {
var addthis_config = {
services_exclude: 'print'
};
}
</script>
</body>
</html>