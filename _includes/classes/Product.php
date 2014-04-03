<?php
class Product {
	// constructor
	public function __construct() {}	
	
	public function load_id($id_product, $id_product_variant=0, $variant_code=''){
		global $mysqli, $cart, $is_admin;
		
		$id_product = (int)$id_product;
		$id_product_variant = (int)$id_product_variant;
		$current_datetime = date('Y-m-d H:i:s');
		
		if (!$result = $mysqli->query('SELECT
		product.id,
		product.product_type,
		IF(product_variant.id IS NOT NULL AND product_variant.sku !="",product_variant.sku,product.sku) AS sku,
		calc_sell_price(product.price,product_variant.price_type,product_variant.price,0,0,0,0) AS price,
		calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,0,0,0,0) AS sell_price,
		calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,customer_type.percent_discount,customer_type.apply_on_rebate,rebate_coupon.discount_type,rebate_coupon.discount) AS current_price,
		product.special_price,
		IF("'.$current_datetime.'" BETWEEN product.special_price_from_date AND product.special_price_to_date,1,0) AS is_special_price,
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
		product.discount_type,
		product.discount,
		product.use_product_current_price,
		product.user_defined_qty,
		product.used,
		product.min_qty,
		product.featured,
		product.track_inventory,
		product.allow_backorders,
		is_product_in_stock(product.id,product_variant.id,0) AS in_stock,
		qty_in_stock(product.id,product_variant.id) AS qty_in_stock,
		product_variant.price_type AS variant_price_type,
		product_variant.price AS variant_price,
		get_max_qty_allowed(product.id,1,calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,customer_type.percent_discount,customer_type.apply_on_rebate,rebate_coupon.discount_type,rebate_coupon.discount),"'.$cart->id.'") AS max_qty,
		product.has_variants,
		product_variant.id AS id_product_variant,
		product_variant.variant_code,
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
		LEFT JOIN product_rating_count ON product.id = product_rating_count.id_product '.
		($variant_code ? '		
		LEFT JOIN 
		product_variant
		ON
		(product.id = product_variant.id_product AND product_variant.variant_code = "'.$variant_code.'") ':'		
		LEFT JOIN 
		product_variant
		ON
		(product.id = product_variant.id_product AND product_variant.id = "'.$id_product_variant.'") ').'
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
		product.id = "'.$id_product.'" '.(!$is_admin ?'
		AND
		product.active = 1
		AND
		product.display_in_catalog = 1
		AND
		product.date_displayed <= "'.$current_datetime.'" ':'').'
		LIMIT 1')) throw new Exception('An error occured while trying to get product info.'."\r\n\r\n".$mysqli->error);	
			
		if ($result->num_rows) {		
			$row = $result->fetch_assoc();		
					
			foreach ($row as $k => $v) $this->$k = $v;
						
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
			
			$output = array();
			$sell_price = $this->sell_price;
			
			// check if we have special price or discount
			switch ($this->product_type) {
				case 0:					
					if ($this->is_special_price) {
						$output[] = array(
							'type' => 'special_price',
							'special_price' => $sell_price,
							'amount' => $this->price-$sell_price,	
							'end_date' => $this->special_price_to_date != '0000-00-00 00:00:00' ? df_date($this->special_price_to_date):'',
						);
					}		
					break;
				case 1:
					$discount = 0;
					switch ($this->discount_type) {
						// fixed
						case 0:	
							$discount = $this->discount;
							break;
						// percent
						case 1:						
							$discount = round($this->price*$this->discount/100,2);
							break;											
					}			
					
					$output[] = array(
						'type' => 'combo',
						'amount' => $discount,
					);				
					break;
			}
		
			if (!$_SESSION['customer']['id_customer_type'] || $_SESSION['customer']['id_customer_type'] && $_SESSION['customer']['apply_on_rebate'] == 1) {		
				
				// check for customer type discount
				if (!$result_discount = $mysqli->query('SELECT 
				customer_type.id,
				customer_type.percent_discount
				FROM 
				customer_type 
				WHERE
				customer_type.id = "'.(int)$_SESSION['customer']['id_customer_type'].'"
				AND
				customer_type.percent_discount > 0')) throw new Exception('An error occured while trying to get customer discount.'."\r\n\r\n".$mysqli->error);	
				
				$row = $result_discount->fetch_assoc();
				if ($row['id']) {				
					$discount = round($this->sell_price*$row['percent_discount']/100,2);
					$sell_price -= $discount;
					
					$output[] = array(
						'type' => 'customer',
						'amount' => $discount,
					);
				}
				$result_discount->free();
				
				if ($this->product_type == 0) {		
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
					(rebate_coupon.id = rebate_coupon_product.id_rebate_coupon AND rebate_coupon_product.id_product = "'.$this->id.'")
					
					LEFT JOIN
					(product_category CROSS JOIN rebate_coupon_category)
					ON
					(rebate_coupon.id = rebate_coupon_category.id_rebate_coupon AND product_category.id_product = "'.$this->id.'" AND rebate_coupon_category.id_category = product_category.id_category)
					
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
						"'.$current_datetime.'" BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
					)
					AND
					(rebate_coupon_product.id_rebate_coupon IS NOT NULL OR rebate_coupon_category.id_rebate_coupon IS NOT NULL)
					ORDER BY 
					(CASE rebate_coupon.discount_type
					WHEN 0 THEN
					(rebate_coupon.discount/"'.$sell_price.'")
					WHEN 1 THEN
					(rebate_coupon.discount/100)
					END) DESC
					LIMIT 1')) throw new Exception('An error occured while trying to get best %/$ rebate.'."\r\n\r\n".$mysqli->error);
					
					if ($result_discount->num_rows) {
						$row = $result_discount->fetch_assoc();	
						
						if ($row['max_qty_allowed'] != 0 && ($this->max_qty == 0 || $this->max_qty != 0 && $row['max_qty_allowed'] < $this->max_qty)) {
							$this->max_qty = $this->max_qty_allowed;
							$this->max_qty_price_tier = $this->max_qty_allowed;
						}
						
						switch ($row['discount_type']) {
							// fixed
							case 0:
								$discount = ($row['discount'] > $sell_price ? $sell_price:$row['discount']);
								break;
							// percent
							case 1:									
								$discount = round(($sell_price*$row['discount'])/100,2);
								break;	
						}
						
						$sell_price -= $discount;					
						
						$output[] = array(
							'type' => 'rebate',
							'amount' => $discount,
							'end_date' => $row['end_date'] != '0000-00-00 00:00:00' ? df_date($row['end_date']):'',
						);	
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
					(rebate_coupon.id = rebate_coupon_product.id_rebate_coupon AND rebate_coupon_product.id_product = "'.$this->id.'")
					
					LEFT JOIN
					(product_category CROSS JOIN rebate_coupon_category)
					ON
					(rebate_coupon.id = rebate_coupon_category.id_rebate_coupon AND product_category.id_product = "'.$this->id.'" AND rebate_coupon_category.id_category = product_category.id_category)
							
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
							(rebate_coupon.discount/"'.$sell_price.'")
						WHEN 1 THEN
							(rebate_coupon.discount/100)
					END) DESC
					LIMIT 1')) throw new Exception('An error occured while trying to get buy and get rebate.'."\r\n\r\n".$mysqli->error);
					
					if ($result_discount->num_rows) {
						$row = $result_discount->fetch_assoc();	
						
						$max_buy = ceil($row['max_qty_allowed']/($row['buy_x_qty']+$row['get_y_qty'])*$row['buy_x_qty']);
						
						if ($max_buy != 0 && ($this->max_qty == 0 || $this->max_qty != 0 && $max_buy < $this->max_qty)) {
							$this->max_qty = $this->max_qty_allowed;
							$this->max_qty_price_tier = $max_buy;
						}
						
						switch ($row['discount_type']) {
							// fixed
							case 0:
								$discount = $row['discount'] > $sell_price ? $sell_price:$row['discount'];
								break;
							// percent
							case 1:			
								$discount = round($sell_price*$row['discount']/100,2);
								break;	
						}
						
						$output[] = array(
							'type' => 'buy_and_get',
							'amount' => $discount,
							'buy' => $row['buy_x_qty'],
							'get' => $row['get_y_qty'],
							'end_date' => $row['end_date'] != '0000-00-00 00:00:00' ? df_date($row['end_date']):'',
						);
					}
					$result_discount->free();
				}
			}				
			
			$this->discounts = $output;
			
			return true;
		}
		
		return false;
	}
	
	public function get_product_type(){
		switch ($this->product_type) {			
			case 0:
				return 'single';
				break;
			case 1:
				return 'combo';
				break;
			case 2:
				return 'bundle';
				break;	
		}
	}
	
	public function get_name(){
		return $this->name;
	}
	
	public function get_short_desc(){
		return $this->short_desc;	
	}
	
	public function get_description(){
		return $this->description;	
	}
	
	public function get_meta_description(){
		return $this->meta_description;
	}
	
	public function get_meta_keywords(){
		return $this->meta_keywords;	
	}
	
	public function get_url($language_code=''){		
		global $mysqli;
	
		$language_code = $language_code ? $language_code:$_SESSION['customer']['language'];
		
		if (!$result = $mysqli->query('SELECT 
		product_description.alias
		FROM
		product_description
		WHERE
		product_description.id_product = "'.$this->id.'"
		AND
		product_description.language_code = "'.$mysqli->escape_string($language_code).'" 
		LIMIT 1')) throw new Exception('An error occured while trying to get product alias.'."\r\n\r\n".$mysqli->error);		
		
		$row = $result->fetch_assoc();

		$query_string = array();
		if ($_SERVER['QUERY_STRING']) { 
			$query_string = parse_str($_SERVER['QUERY_STRING']);
			unset($query_string['_lang']);
			unset($query_string['alias']);					
		}		
		$query_string = http_build_query($query_string);
	
		return array(
			'url' => '/'.$language_code.'/product/'.$row['alias'],
			'query_string' => $query_string,
		);			
	}

	public function get_sku(){
		return $this->sku;	
	}
	
	public function get_original_price(){
		return $this->price;	
	}
	
	public function get_current_price(){
		return $this->current_price;	
	}	
	
	public function get_special_price(){
		return array(
			'has_special_price' => $this->is_special_price,
			'special_price' => $this->special_price,
			'start_date' => df_date($this->special_price_start_date),
			'end_date' => df_date($this->special_price_end_date),
		);	
	}
	
	public function get_discounts(){
		return $this->discounts;
	}
	
	public function get_price_tiers(){
		global $mysqli;
		
		$output = array();
		
		if (!$result = $mysqli->query('SELECT 
		product_price_tier.qty,
		calc_sell_price(product_price_tier.price,product_variant.price_type,product_variant.price,customer_type.percent_discount,customer_type.apply_on_rebate,rebate_coupon.discount_type,rebate_coupon.discount) AS price
		FROM 			            
		product_price_tier
		
		LEFT JOIN
		product_variant
		ON
		(product_variant.id = "'.$this->id_product_variant.'")
		
		LEFT JOIN
		customer_type
		ON
		(customer_type.id = "'.(int)$_SESSION['customer']['id_customer_type'].'")
		
		LEFT JOIN
		(product CROSS JOIN rebate_coupon)
		ON
		(product_price_tier.id_product = product.id AND product.id_rebate_coupon = rebate_coupon.id)
		
		WHERE
		product_price_tier.id_product = "'.$this->id.'"
		AND
		(product_price_tier.id_customer_type = 0 OR product_price_tier.id_customer_type = customer_type.id) '.
		($this->max_qty_price_tier != 0 ? ' AND product_price_tier.qty <= "'.$this->max_qty_price_tier.'" ':'').'
		
		ORDER BY 
		product_price_tier.qty ASC')) throw new Exception('An error occured while trying to get tier prices.'."\r\n\r\n".$mysqli->error);	

		while ($row = $result->fetch_assoc()) {
			if (isset($output[$row['qty']]) && $output[$row['qty']]['price'] > $row['price'] || !isset($output[$row['qty']])) {
				
				// if the total price is higher than the current price special price, remove it
				if ($this->current_price > $row['price'] && $row['price'] > 0) {
					$output[$row['qty']] = array(
						'qty' => $row['qty'],
						'price' => $row['price'],
						'amount' => $this->current_price-$row['price'],
					);
				} else if (isset($output[$row['qty']])) unset($output[$row['qty']]);
			} 
		}

		$result->free();
		
		return $output;	
	}
	
	public function get_brand(){
		return $this->brand;	
	}
	
	public function get_model(){
		return $this->model;	
	}
	
	public function is_used(){
		return $this->used;	
	}
	
	public function is_featured(){
		return $this->featured;
	}
	
	public function is_in_stock(){
		global $mysqli;
		
		if (!$stmt_in_stock = $mysqli->prepare('SELECT
		is_product_in_stock(?,?,?)
		')) throw new Exception('An error occured while trying to prepare check if product is in stock statement.'."\r\n\r\n".$mysqli->error);			
		
		if (!$stmt_variants_in_stock = $mysqli->prepare('SELECT
		COUNT(product_variant.id)
		FROM
		product_variant
		WHERE
		product_variant.variant_code LIKE CONCAT(?,"%")
		AND
		is_product_in_stock(product_variant.id_product,product_variant.id,1) = 1
		AND 
		product_variant.id_product = ?
		')) throw new Exception('An error occured while trying to prepare check if product variants is in stock statement.'."\r\n\r\n".$mysqli->error);		
		
		if (!$stmt_variants_in_stock_no_variant_code = $mysqli->prepare('SELECT
		COUNT(product_variant.id)
		FROM
		product_variant
		WHERE
		is_product_in_stock(product_variant.id_product,product_variant.id,1) = 1
		AND 
		product_variant.id_product = ?
		')) throw new Exception('An error occured while trying to prepare check if product variants is in stock statement.'."\r\n\r\n".$mysqli->error);				
		
		if (!$stmt_single_variant = $mysqli->prepare('SELECT
		COUNT(product_variant.id)
		FROM
		product_variant
		WHERE
		product_variant.variant_code = ?
		AND
		product_variant.active = 1
		AND 
		product_variant.id_product = ?
		')) throw new Exception('An error occured while trying to prepare check if product is a single variant statement.'."\r\n\r\n".$mysqli->error);		
	
		// check if product is in stock
		$qty=1;
		if (!$this->has_variants) {		
			$id_product_variant = 0;
				
			if (!$stmt_in_stock->bind_param("iii", $this->id, $id_product_variant, $qty)) throw new Exception('An error occured while trying to bind params to check if product is in stock statement.'."\r\n\r\n".$mysqli->error);
			
			/* Execute the statement */
			if (!$stmt_in_stock->execute()) throw new Exception('An error occured while trying to execute check if product is in stock statement.'."\r\n\r\n".$mysqli->error);				
			
			/* store result */
			$stmt_in_stock->store_result();																											
			
			/* bind result variables */
			$stmt_in_stock->bind_result($in_stock);	
				
			$stmt_in_stock->fetch();
		// check if at least one variant is in stock
		} else {
			if (!empty($this->variant_code)) {					
				if (!$stmt_variants_in_stock->bind_param("si", $this->variant_code, $this->id)) throw new Exception('An error occured while trying to bind params to check if product variants is in stock statement.'."\r\n\r\n".$mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_variants_in_stock->execute()) throw new Exception('An error occured while trying to execute check if product variants is in stock statement.'."\r\n\r\n".$mysqli->error);				
				
				/* store result */
				$stmt_variants_in_stock->store_result();																											
				
				/* bind result variables */
				$stmt_variants_in_stock->bind_result($in_stock);	
					
				$stmt_variants_in_stock->fetch();
			} else {
				if (!$stmt_variants_in_stock_no_variant_code->bind_param("i", $this->id)) throw new Exception('An error occured while trying to bind params to check if product variants is in stock statement.'."\r\n\r\n".$mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_variants_in_stock_no_variant_code->execute()) throw new Exception('An error occured while trying to execute check if product variants is in stock statement.'."\r\n\r\n".$mysqli->error);				
				
				/* store result */
				$stmt_variants_in_stock_no_variant_code->store_result();																											
				
				/* bind result variables */
				$stmt_variants_in_stock_no_variant_code->bind_result($in_stock);	
					
				$stmt_variants_in_stock_no_variant_code->fetch();										
			}
			
			$in_stock = $in_stock ? 1:0;
			
			if (!$stmt_single_variant->bind_param("si", $this->variant_code, $this->id)) throw new Exception('An error occured while trying to bind params to check if product is single variant statement.'."\r\n\r\n".$mysqli->error);
			
			/* Execute the statement */
			if (!$stmt_single_variant->execute()) throw new Exception('An error occured while trying to execute check if product is single variant statement.'."\r\n\r\n".$mysqli->error);				
			
			/* store result */
			$stmt_single_variant->store_result();																											
			
			/* bind result variables */
			$stmt_single_variant->bind_result($single_variant);	
				
			$stmt_single_variant->fetch();					
			
			$single_variant = $single_variant ? 1:0;
		}		
		
		return $in_stock;		
	}
	
	public function get_qty_in_stock(){
		return $this->qty_in_stock;	
	}
	
	public function is_downloadable(){
		return $this->downloadable;	
	}
	
	public function get_min_qty(){
		return $this->min_qty;	
	}
	
	public function get_variants_options($variant_code=''){
		global $mysqli;
		
		$output = array();
		
		if ($this->has_variants) {
			// get all variants in stock
			if (!$result = $mysqli->query('SELECT 
			product_variant_group.id,
			product_variant_group.input_type,
			product_variant_group_description.name AS group_name,
			product_variant_group_description.description AS group_desc,			
			product_variant_group_option_description.id_product_variant_group_option,
			product_variant_group_option.swatch_type,
			product_variant_group_option.color,
			product_variant_group_option.color2,
			product_variant_group_option.color3,
			product_variant_group_option.filename,
			product_variant_group_option_description.name AS option_name			
			FROM product_variant
			INNER JOIN product_variant_option ON (product_variant.id = product_variant_option.id_product_variant)
			INNER JOIN product_variant_group ON (product_variant_option.id_product_variant_group = product_variant_group.id)
			INNER JOIN product_variant_group_description ON (product_variant_option.id_product_variant_group = product_variant_group_description.id_product_variant_group AND product_variant_group_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
			INNER JOIN product_variant_group_option ON (product_variant_option.id_product_variant_group_option = product_variant_group_option.id)
			INNER JOIN product_variant_group_option_description ON (product_variant_option.id_product_variant_group_option = product_variant_group_option_description.id_product_variant_group_option AND product_variant_group_option_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
			 
			WHERE 
			product_variant.id_product = "'.$this->id.'"
			AND
			product_variant.active = 1
			AND
			product_variant.in_stock = 1
			AND
			is_product_in_stock(product_variant.id_product,product_variant.id,1) = 1 '.(!empty($variant_code) ? ' AND product_variant.variant_code LIKE "'.$mysqli->escape_string($variant_code).'%" ':'').' 
			ORDER BY product_variant.sort_order ASC,
			product_variant_group.sort_order ASC,
			product_variant_group_option.sort_order ASC')) throw new Exception('An error occured while trying to get variants.'."\r\n\r\n".$mysqli->error);
			
			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) {
					if (!isset($output[$row['id']])) {
						$input_type = '';
						switch ($row['input_type']) {
							// dropdown
							case 0:
								$input_type = 'dropdown';
								break;
							// radio
							case 1:
								$input_type = 'radio';
								break;
							// swatch
							case 2:
								$input_type = 'swatch';
								break;	
						}
						
						$output[$row['id']] = array(
							'id' => $row['id'],
							'input_type' => $input_type,
							'name' => $row['group_name'],
							'description' => $row['group_desc'],
						);	
					}
					
					if ($row['input_type'] == 2) {
						switch ($row['swatch_type']) {
							// one color
							case 0:
								$swatch_type = 'one color';
								break;
							// two colors
							case 1:
								$swatch_type = 'two colors';
								break;
							// three colors
							case 2: 
								$swatch_type = 'three colors';
								break;
							// file	
							case 3:
								$swatch_type = 'filename';
								$filename = '/images/products/swatch/'.$row['filename'];
								break;
						}
					}
					
					$output[$row['id']]['options'][$row['id_product_variant_group_option']] = array(
						'id' => $row['id_product_variant_group_option'],
						'name' => $row['option_name'],
						'swatch_type' => $swatch_type,
						'color1' => $row['color'],
						'color2' => $row['color2'],
						'color3' => $row['color3'],
						'filename' => $filename,
					);
				}
			}
			
			$result->free();
		} 
		
		return $output;
	}
	
	public function get_options(){
		global $mysqli;
		
		$current_datetime = date('Y-m-d H:i:s');
		$output = array();
				
		if ($result = $mysqli->query('SELECT
		options_group.id,
		options_group.input_type,
		options_group.maxlength,
		options_group.from_to,
		options_group.user_defined_qty,
		options_group_description.name,
		options_group_description.description,
		options.id AS id_options,
		options.sku,
		options.price,
		options.special_price,
		options.special_price_from_date,
		options.special_price_to_date,
		options_description.name AS option_name,
		options_description.description AS option_desc,
		IF("'.$current_datetime.'" BETWEEN options.special_price_from_date AND options.special_price_to_date,1,0) AS is_special_price,
		IF("'.$current_datetime.'" BETWEEN options.special_price_from_date AND options.special_price_to_date,options.special_price,options.price) AS current_price
		FROM
		options
		INNER JOIN options_description ON (options.id = options_description.id_options)
		INNER JOIN (options_group CROSS JOIN options_group_description) ON (options.id_options_group = options_group.id AND options_group.id = options_group_description.id_options_group AND options_group_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
		INNER JOIN
		product_options_group ON (options_group.id = product_options_group.id_options_group)
		WHERE
		product_options_group.id_product = "'.$this->id.'"
		AND
		options.active = 1
		AND
		(options.track_inventory = 1 AND options.in_stock = 1 OR options.track_inventory = 0)
		AND
		is_option_in_stock(options.id) = 1		
		ORDER BY
		product_options_group.sort_order ASC,
		options.sort_order ASC'));	
		
		while ($row = $result->fetch_assoc()) {
			if (!isset($output[$row['id']])) {
				$input_type = '';
				
				switch ($row['input_type']){
					// dropdown
					case 0: 
						$input_type = 'dropdown';
						break;
					// radio
					case 1: 
						$input_type = 'radio';
						break;
					// swatch
					case 2: 
						$input_type = 'swatch';
						break;
					// checkbox
					case 3:
						$input_type = 'checkbox';
						break;
					// multi-select
					case 4:
						$input_type = 'multi-select';
						break;
					// textfield
					case 5: 
						$input_type = 'textfield';
						break;
					// textarea
					case 6: 
						$input_type = 'textarea';
						break;
					// file
					case 7:
						$input_type = 'file';
						break;
					// date
					case 8:
						$input_type = 'date';
						break; 
					// date & time
					case 9:
						$input_type = 'date-time';
						break;
					// time
					case 10:
						$input_type = 'time';
						break;	
				}
				
				$output[$row['id']] = array(
					'id' => $row['id'],
					'input_type' => $input_type,
					'from_to' => $row['from_to'],
					'user_defined_qty' => $row['user_defined_qty'],
					'name' => $row['name'],
					'description' => $row['description'],
				);	
			}
			
			$output[$row['id']]['options'][$row['id_options']] = array(
				'id' => $row['id_options'],
				'name' => $row['option_name'],
				'description' => $row['option_desc'],
				'sku' => $row['sku'],
				'current_price' => $row['current_price'],
				'price' => $row['price'],
				'special_price' => $row['special_price'],
				'start_date' => $row['special_price_from_date'],
				'end_date' => $row['special_price_to_date'],
				'is_special_price' => $row['is_special_price'],
			);
		}	
		
		return $output;
	}
	
	public function get_suggested_products(){
		global $mysqli;
		
		require_once(dirname(__FILE__).'/Products.php');
		$Products = new Products;
		
		$total_products = $Products->count_products(array('suggested_products'=>$this->id));
		$output = $Products->get_products(array('suggested_products'=>$this->id),0,0,0);	

		return $output;
	}
	
	public function get_related_products(){
		global $mysqli;
		
		require_once(dirname(__FILE__).'/Products.php');
		$Products = new Products;		
		
		$total_products = $Products->count_products(array('related_products'=>$this->id));
		$output = $Products->get_products(array('related_products'=>$this->id),0,0,0);	

		return $output;
	}
	
	public function get_avg_rating(){
		return $this->average_rated;	
	}
	
	public function get_total_rating(){
		return $this->total_rated;	
	}
	
	public function display_multiple_variants(){
		return $this->display_multiple_variants_form;	
	}
	
	public function display_price(){
		global $config_site;
		
		return $config_site['display_price'] && !$this->display_price_exception ? 1:0;
	}
	
	public function display_add_to_cart(){
		global $config_site;
		
		return $config_site['allow_add_to_cart'] && !$this->allow_add_to_cart_exceptions ? 1:0;
	}
	
	public function get_images($variant_code=''){
		global $mysqli;
		
		$output = array();
		
		if (!empty($variant_code)) {
			if (!$stmt_variant_image = $mysqli->prepare('SELECT 
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
			WHERE
			product_image_variant.id_product = ?
			AND
			product_image_variant.variant_code LIKE CONCAT(?,"%")
			ORDER BY 
			product_image_variant_image.cover DESC,
			product_image_variant_image.sort_order ASC
			LIMIT 1')) throw new Exception('An error occured while trying to prepare get variant cover image statement.'."\r\n\r\n".$mysqli->error);	
			
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
				if (!$stmt_variant_image->bind_param("is", $this->id, $row_variant_code)) throw new Exception('An error occured while trying to bind params to get variant cover image statement.'."\r\n\r\n".$mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_variant_image->execute()) throw new Exception('An error occured while trying to get variant cover image.'."\r\n\r\n".$mysqli->error);	
				
				/* store result */
				$stmt_variant_image->store_result();		

				/* bind result variables */
				$stmt_variant_image->bind_result($id, $original, $filename, $cover);																											
				
				while ($stmt_variant_image->fetch()) {
					$output[] = array(
						'id' => $id,
						'original' => '/images/products/original/'.$original,
						'zoom' => '/images/products/zoom/'.$filename,												
						'cover' => '/images/products/cover/'.$filename,
						'listing' => '/images/products/listing/'.$filename,						
						'suggest' => '/images/products/suggest/'.$filename,						
						'thumb' => '/images/products/thumb/'.$filename,												
						'cover' => $cover,
					);	
				}
				
				if (sizeof($output)) break;
			}			
			
			$stmt_variant_image->close();				
		} 
	
		if (!sizeof($output)) {			
			if (!$result = $mysqli->query('SELECT 
			id,
			original,
			filename,
			cover
			FROM
			product_image 
			WHERE
			id_product = "'.$this->id.'"
			ORDER BY
			IF(cover=1,-1,sort_order) ASC')) throw new Exception('An error occured while trying to get images.'."\r\n\r\n".$mysqli->error);	
			while ($row = $result->fetch_assoc()) {
				$output[] = array(
						'id' => $row['id'],
						'original' => '/images/products/original/'.$row['original'],
						'zoom' => '/images/products/zoom/'.$row['filename'],												
						'cover' => '/images/products/cover/'.$row['filename'],
						'listing' => '/images/products/listing/'.$row['filename'],						
						'suggest' => '/images/products/suggest/'.$row['filename'],						
						'thumb' => '/images/products/thumb/'.$row['filename'],												
						'cover' => $row['cover'],
				);
			}
			
			$result->free();
		}	
		
		return $output;	
	}
}
