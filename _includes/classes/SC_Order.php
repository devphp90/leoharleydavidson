<?php
class SC_Order
{
	public $mysqli='';
	public $messages=array();

	
	// constructor
	public function __construct($mysqli) {
		if (!$mysqli instanceof MySQLi) throw new Exception('Invalid mysqli object');
		$this->mysqli=$mysqli;
	}	
	
	public function load($id_orders)
	{	
		$id_orders=(int)$id_orders;
	
		// get order
		if (!$result = $this->mysqli->query('SELECT 
		*,
		billing_state.name AS billing_state,
		billing_country.name AS billing_country,
		shipping_state.name AS shipping_state,
		shipping_country.name AS shipping_country,
		local_pickup_state.name AS local_pickup_state,
		local_pickup_country.name AS local_pickup_country
		FROM
		orders
		LEFT JOIN 
		state_description AS billing_state
		ON
		(orders.billing_state_code = billing_state.state_code AND billing_state.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		LEFT JOIN 
		country_description AS billing_country
		ON
		(orders.billing_country_code = billing_country.country_code AND billing_country.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		LEFT JOIN 
		state_description AS shipping_state
		ON
		(orders.shipping_state_code = shipping_state.state_code AND shipping_state.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		LEFT JOIN
		country_description AS shipping_country
		ON
		(orders.shipping_country_code = shipping_country.country_code AND shipping_country.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		
		LEFT JOIN 
		state_description AS local_pickup_state
		ON
		(orders.local_pickup_state_code = local_pickup_state.state_code AND local_pickup_state.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		LEFT JOIN 
		country_description AS local_pickup_country
		ON
		(orders.local_pickup_country_code = local_pickup_country.country_code AND local_pickup_country.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		
		WHERE
		orders.id = "'.$this->mysqli->escape_string($id_orders).'"
		AND
		orders.id_customer = "'.$this->mysqli->escape_string($_SESSION['customer']['id']).'" 
		LIMIT 1')) throw new Exception('An error occured while trying to get order info.'."\r\n\r\n".$this->mysqli->error);	
		// if we do, populate our object with properties and values from the cart
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			
			foreach ($row as $key => $value) {
				$this->$key = $value;
			}
		} else { return false; }
		$result->free();
		
		return true;
	}	
	
	public function new_order($trnComments="",$payment_method=0)
	{	
		global $config_site, $cart;

		$current_datetime = date('Y-m-d H:i:s');
		$tmp_uploads_dir = dirname(__FILE__).'/../../tmp_uploads/';
		$file_uploads_dir = dirname(__FILE__).'/../../file_uploads/';
		
		// create order	
		if (!$this->mysqli->query('INSERT INTO
		orders
		SET
		id_customer = "'.$this->mysqli->escape_string($cart->id_customer).'",
		id_customer_type = "'.$this->mysqli->escape_string($cart->id_customer_type).'",
		date_order = "'.$this->mysqli->escape_string($current_datetime).'",
		email = "'.$this->mysqli->escape_string($_SESSION['customer']['email']).'",
		language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'",
		id_tax_rule = "'.$this->mysqli->escape_string($cart->id_tax_rule).'",
		billing_firstname = "'.$this->mysqli->escape_string($cart->billing_firstname).'",
		billing_lastname = "'.$this->mysqli->escape_string($cart->billing_lastname).'",
		billing_company = "'.$this->mysqli->escape_string($cart->billing_company).'",
		billing_address = "'.$this->mysqli->escape_string($cart->billing_address).'",
		billing_city = "'.$this->mysqli->escape_string($cart->billing_city).'",
		billing_country_code = "'.$this->mysqli->escape_string($cart->billing_country_code).'",
		billing_state_code = "'.$this->mysqli->escape_string($cart->billing_state_code).'",
		billing_zip = "'.$this->mysqli->escape_string($cart->billing_zip).'",
		billing_telephone = "'.$this->mysqli->escape_string($cart->billing_telephone).'",
		billing_fax = "'.$this->mysqli->escape_string($cart->billing_fax).'",
		shipping_firstname = "'.$this->mysqli->escape_string($cart->shipping_firstname).'",
		shipping_lastname = "'.$this->mysqli->escape_string($cart->shipping_lastname).'",
		shipping_company = "'.$this->mysqli->escape_string($cart->shipping_company).'",
		shipping_address = "'.$this->mysqli->escape_string($cart->shipping_address).'",
		shipping_city = "'.$this->mysqli->escape_string($cart->shipping_city).'",
		shipping_country_code = "'.$this->mysqli->escape_string($cart->shipping_country_code).'",
		shipping_state_code = "'.$this->mysqli->escape_string($cart->shipping_state_code).'",
		shipping_zip = "'.$this->mysqli->escape_string($cart->shipping_zip).'",
		shipping_telephone = "'.$this->mysqli->escape_string($cart->shipping_telephone).'",
		shipping_fax = "'.$this->mysqli->escape_string($cart->shipping_fax).'",
		subtotal = "'.$this->mysqli->escape_string($cart->subtotal).'",
		local_pickup = "'.$this->mysqli->escape_string($cart->local_pickup).'",
		
		local_pickup_address = "'.$this->mysqli->escape_string($cart->local_pickup_address).'",
		local_pickup_city = "'.$this->mysqli->escape_string($cart->local_pickup_city).'",
		local_pickup_country_code = "'.$this->mysqli->escape_string($cart->local_pickup_country_code).'",
		local_pickup_state_code = "'.$this->mysqli->escape_string($cart->local_pickup_state_code).'",
		local_pickup_zip = "'.$this->mysqli->escape_string($cart->local_pickup_zip).'",
		
		free_shipping = "'.$this->mysqli->escape_string($cart->free_shipping).'",
		shipping_gateway_company = "'.$this->mysqli->escape_string($cart->shipping_gateway_company).'",
		shipping_service = "'.$this->mysqli->escape_string($cart->shipping_service).'",
		shipping = "'.$this->mysqli->escape_string($cart->shipping).'",
		shipping_estimated = "'.$this->mysqli->escape_string($cart->shipping_estimated).'",
		taxes = "'.$this->mysqli->escape_string($cart->taxes).'",
		total = "'.$this->mysqli->escape_string($cart->total).'",
		gift_certificates = "'.$this->mysqli->escape_string($cart->gift_certificates).'",
		grand_total = "'.$this->mysqli->escape_string($cart->grand_total).'",
		status = 0,
		payment_method = "'.$this->mysqli->escape_string($payment_method).'",
		date_created = "'.$this->mysqli->escape_string($current_datetime).'"')) throw new Exception('An error occured while trying to create the order.'."\r\n\r\n".$this->mysqli->error);	
		
		$id_orders = $this->mysqli->insert_id;
		
		// insert comments for this order
		if($trnComments){
			if (!$this->mysqli->query('INSERT INTO
			orders_comment
			SET
			id_orders = "'.$this->mysqli->escape_string($id_orders).'",
			comments = "'.$this->mysqli->escape_string($trnComments).'",
			date_created = "'.$this->mysqli->escape_string($current_datetime).'"')) throw new Exception('An error occured while trying to add order comments.'."\r\n\r\n".$this->mysqli->error);
		}
		
		// insert gift certificate for this order
		if (!$result = $this->mysqli->query('SELECT 
		code,
		amount,
		id_user_created,
		date_created
		FROM
		cart_gift_certificate
		WHERE 
		id_cart = "'.$this->mysqli->escape_string($cart->id).'"')) throw new Exception('An error occured while trying to get gift certificates in cart.'."\r\n\r\n".$this->mysqli->error);	
			
		if ($result->num_rows) {
			/* Prepare the statement */		
			if (!$stmt_add_gift_certificate = $this->mysqli->prepare('INSERT INTO
			orders_gift_certificate 
			SET
			id_orders = ?,
			code = ?,
			amount = ?,
			id_user_created = ?,
			date_created = ?')) throw new Exception('An error occured while trying to prepare add gift certificate statement.'."\r\n\r\n".$this->mysqli->error);				
					
			while ($row = $result->fetch_assoc()) {
				if (!$stmt_add_gift_certificate->bind_param("isdis",$id_orders,$row['code'],$row['amount'],$row['id_user_created'],$row['date_created'])) throw new Exception('An error occured while trying to bind params to add gift certificate statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_add_gift_certificate->execute()) throw new Exception('An error occured while trying to add gift certificate.'."\r\n\r\n".$this->mysqli->error);								
			}
			
			$stmt_add_gift_certificate->close();
		}		
		$result->free();		
		
		// insert tax rule for this order
		if (!$result = $this->mysqli->query('SELECT
		tax_rule_rate.id,
		tax_rule_rate.id_tax,
		tax_rule_rate.rate,
		tax_rule_rate.stacked,
		tax_rule_rate.sort_order,
		tax.code,
		tax.tax_number
		FROM
		tax_rule_rate
		INNER JOIN
		tax 
		ON
		(tax_rule_rate.id_tax = tax.id)
		WHERE
		tax_rule_rate.id_tax_rule = "'.$this->mysqli->escape_string($cart->id_tax_rule).'"
		ORDER BY 
		tax_rule_rate.sort_order ASC')) throw new Exception('An error occured while trying to get tax rule rates.'."\r\n\r\n".$this->mysqli->error);								
		
		$orders_tax = array();
		if ($result->num_rows) {
			/* Prepare the statement */		
			if (!$stmt_add_tax = $this->mysqli->prepare('INSERT INTO
			orders_tax
			SET
			id_orders = ?,
			id_tax = ?,
			code = ?,
			tax_number = ?,
			rate = ?,
			stacked = ?,
			sort_order = ?')) throw new Exception('An error occured while trying to prepare add tax statement.'."\r\n\r\n".$this->mysqli->error);				
			
			/* Prepare the statement */		
			if (!$stmt_tax_description = $this->mysqli->prepare('SELECT
			language_code,
			name
			FROM 
			tax_description
			WHERE 
			id_tax = ?')) throw new Exception('An error occured while trying to prepare get tax description statement.'."\r\n\r\n".$this->mysqli->error);	
			
			/* Prepare the statement */		
			if (!$stmt_add_tax_description = $this->mysqli->prepare('INSERT INTO
			orders_tax_description
			SET
			id_orders_tax = ?,
			language_code = ?,
			name = ?')) throw new Exception('An error occured while trying to prepare add tax description statement.'."\r\n\r\n".$this->mysqli->error);				
			
			while ($row = $result->fetch_assoc()) {
				// insert tax 
				if (!$stmt_add_tax->bind_param("iissdii",$id_orders,$row['id_tax'],$row['code'],$row['tax_number'],$row['rate'],
				$row['stacked'],$row['sort_order'])) throw new Exception('An error occured while trying to bind params to add tax statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_add_tax->execute()) throw new Exception('An error occured while trying to add tax.'."\r\n\r\n".$this->mysqli->error);							
				
				// get orders tax id
				$id_orders_tax = $this->mysqli->insert_id;	
				
				// add to array
				$orders_tax[$row['id']] = $id_orders_tax;
				
				// get tax description
				if (!$stmt_tax_description->bind_param("i", $row['id_tax'])) throw new Exception('An error occured while trying to bind params to get tax description statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_tax_description->execute()) throw new Exception('An error occured while trying to get tax description.'."\r\n\r\n".$this->mysqli->error);				
				
				/* store result */
				$stmt_tax_description->store_result();																											
				
				if ($stmt_tax_description->num_rows) {												
					/* bind result variables */
					$stmt_tax_description->bind_result($language_code, $name);	
						
					while ($stmt_tax_description->fetch()) {
						// add tax description
						if (!$stmt_add_tax_description->bind_param("iss", $id_orders_tax,$language_code,$name)) throw new Exception('An error occured while trying to bind params to add tax description statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_add_tax_description->execute()) throw new Exception('An error occured while trying to add tax description.'."\r\n\r\n".$this->mysqli->error);									
					}
				}					
			}
		}
		$result->free();		 		
		
		// insert discount for this order
		if (!$result = $this->mysqli->query('SELECT 
		cart_discount.id,
		cart_discount.amount,
		cart_discount.id_rebate_coupon,
		rebate_coupon.type,
		rebate_coupon.coupon,
		rebate_coupon.coupon_code,
		rebate_coupon.start_date,
		rebate_coupon.end_date,
		rebate_coupon.min_cart_value,
		rebate_coupon.discount_type,
		rebate_coupon.discount,
		rebate_coupon.min_qty_required,
		rebate_coupon.max_qty_allowed,
		rebate_coupon.buy_x_qty,
		rebate_coupon.get_y_qty
		FROM
		cart_discount 
		INNER JOIN
		rebate_coupon 
		ON
		(cart_discount.id_rebate_coupon = rebate_coupon.id)
		WHERE
		cart_discount.id_cart = "'.$this->mysqli->escape_string($cart->id).'"
		ORDER BY 
		cart_discount.id ASC')) throw new Exception('An error occured while trying to get discounts in cart.'."\r\n\r\n".$this->mysqli->error);	
		
		$orders_discount = array();
		if ($result->num_rows) {
			/* Prepare the statement */		
			if (!$stmt_add_discount = $this->mysqli->prepare('INSERT INTO
			orders_discount 
			SET
			id_orders = ?,
			id_rebate_coupon = ?,
			type = ?,
			coupon = ?,
			coupon_code = ?,
			start_date = ?,
			end_date = ?,
			min_cart_value = ?,
			discount_type = ?,
			discount = ?,
			min_qty_required = ?,
			max_qty_allowed = ?,
			buy_x_qty = ?,
			get_y_qty = ?')) throw new Exception('An error occured while trying to prepare add discount statement.'."\r\n\r\n".$this->mysqli->error);	
			
			/* Prepare the statement */		
			if (!$stmt_discount_description = $this->mysqli->prepare('SELECT 
			language_code,
			description
			FROM
			rebate_coupon_description  
			WHERE 
			id_rebate_coupon = ?')) throw new Exception('An error occured while trying to prepare get discount description statement.'."\r\n\r\n".$this->mysqli->error);						
			
			/* Prepare the statement */		
			if (!$stmt_add_discount_description = $this->mysqli->prepare('INSERT INTO
			orders_discount_description 
			SET
			id_orders_discount = ?,
			language_code = ?,
			description = ?')) throw new Exception('An error occured while trying to prepare add discount description statement.'."\r\n\r\n".$this->mysqli->error);				
													
			// loop through discounts
			while ($row = $result->fetch_assoc()) {
				// insert discount 
				if (!$stmt_add_discount->bind_param("iiiisssdidiiii",$id_orders,$row['id_rebate_coupon'],$row['type'],$row['coupon'],$row['coupon_code'],
				$row['start_date'],$row['end_date'],$row['min_cart_value'],$row['discount_type'],$row['discount'],$row['min_qty_required'],
				$row['max_qty_allowed'],$row['buy_x_qty'],$row['get_y_qty'])) throw new Exception('An error occured while trying to bind params to add discount statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_add_discount->execute()) throw new Exception('An error occured while trying to add gift certificate.'."\r\n\r\n".$this->mysqli->error);							
				
				// get discount id
				$id_orders_discount = $this->mysqli->insert_id;		
				
				// add to arrray
				$orders_discount[$row['id']] = $id_orders_discount;		
				
				// get discount description
				if (!$stmt_discount_description->bind_param("i", $row['id_rebate_coupon'])) throw new Exception('An error occured while trying to bind params to get discount description statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_discount_description->execute()) throw new Exception('An error occured while trying to get discount description.'."\r\n\r\n".$this->mysqli->error);				
				
				/* store result */
				$stmt_discount_description->store_result();																											
				
				if ($stmt_discount_description->num_rows) {												
					/* bind result variables */
					$stmt_discount_description->bind_result($language_code, $description);	
						
					while ($stmt_discount_description->fetch()) {
						// add discount description
						if (!$stmt_add_discount_description->bind_param("iss", $id_orders_discount,$language_code,$description)) throw new Exception('An error occured while trying to bind params to add discount description statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_add_discount_description->execute()) throw new Exception('An error occured while trying to add discount description.'."\r\n\r\n".$this->mysqli->error);									
					}
				}
			}
		}
		
		// get list of option groups 
		if (!$result = $this->mysqli->query('SELECT
		options_group.id,
		product_options_group.sort_order,
		options_group.input_type
		FROM 
		cart_item
		INNER JOIN
		cart_item_option
		ON
		(cart_item.id = cart_item_option.id_cart_item)
		
		INNER JOIN
		product_options_group
		ON
		(cart_item_option.id_product_options_group = product_options_group.id)
		
		INNER JOIN
		options_group 
		ON
		(cart_item_option.id_options_group = options_group.id)
		
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($cart->id).'" 
		GROUP BY 
		options_group.id 
		')) throw new Exception('An error occured while trying to get option groups.'."\r\n\r\n".$this->mysqli->error);									
		
		if ($result->num_rows) {
			/* Prepare the statement */		
			if (!$stmt_add_option_group = $this->mysqli->prepare('INSERT INTO
			orders_options_group 
			SET
			id_orders = ?,
			id_options_group = ?,
			sort_order = ?,
			input_type = ?')) throw new Exception('An error occured while trying to prepare add options group statement.'."\r\n\r\n".$this->mysqli->error);					
			
			/* Prepare the statement */		
			if (!$stmt_option_group_description = $this->mysqli->prepare('SELECT 
			language_code,
			name,
			description
			FROM
			options_group_description  
			WHERE 
			id_options_group = ?')) throw new Exception('An error occured while trying to prepare get options group description statement.'."\r\n\r\n".$this->mysqli->error);						
			
			/* Prepare the statement */		
			if (!$stmt_add_option_group_description = $this->mysqli->prepare('INSERT INTO
			orders_options_group_description 
			SET
			id_orders_options_group = ?,
			id_options_group = ?,
			language_code = ?,
			name = ?,
			description = ?')) throw new Exception('An error occured while trying to prepare add options group description statement.'."\r\n\r\n".$this->mysqli->error);				
			
			while ($row = $result->fetch_assoc()) {
				// add options group
				if (!$stmt_add_option_group->bind_param("iiii", $id_orders,$row['id'],$row['sort_order'],$row['input_type'])) throw new Exception('An error occured while trying to bind params to add options group statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_add_option_group->execute()) throw new Exception('An error occured while trying to add options group.'."\r\n\r\n".$this->mysqli->error);									
				
				$id_orders_options_group = $this->mysqli->insert_id;
				
				// get options group description
				if (!$stmt_option_group_description->bind_param("i", $row['id'])) throw new Exception('An error occured while trying to bind params to get discount description statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_option_group_description->execute()) throw new Exception('An error occured while trying to get options group description.'."\r\n\r\n".$this->mysqli->error);				
				
				/* store result */
				$stmt_option_group_description->store_result();																											
				
				if ($stmt_option_group_description->num_rows) {												
					/* bind result variables */
					$stmt_option_group_description->bind_result($language_code, $name, $description);	
						
					while ($stmt_option_group_description->fetch()) {
						// add discount description
						if (!$stmt_add_option_group_description->bind_param("iisss", $id_orders_options_group,$row['id'],$language_code,$name,$description)) throw new Exception('An error occured while trying to bind params to add options group description statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_add_option_group_description->execute()) throw new Exception('An error occured while trying to add options group description.'."\r\n\r\n".$this->mysqli->error);									
					}
				}								
			}		
			
			$stmt_add_option_group->close();
			$stmt_option_group_description->close();
			$stmt_add_option_group_description->close();
		}
		$result->free();
		
		// get shipping taxes
		if (!$result = $this->mysqli->query('SELECT
		cart_shipping_tax.id_tax_rule_rate,
		cart_shipping_tax.amount
		FROM
		cart_shipping_tax
		WHERE
		cart_shipping_tax.id_cart = "'.$this->mysqli->escape_string($cart->id).'"
		')) throw new Exception('An error occured while trying to get shipping taxes.'."\r\n\r\n".$this->mysqli->error);	
		
		if ($result->num_rows) {
			/* Prepare the statement */		
			if (!$stmt_add_shipping_tax = $this->mysqli->prepare('INSERT INTO
			orders_shipping_tax 
			SET
			id_orders = ?,
			id_orders_tax = ?,
			amount = ?')) throw new Exception('An error occured while trying to prepare add shipping tax statement.'."\r\n\r\n".$this->mysqli->error);				
			
			while ($row = $result->fetch_assoc()) {
				$id_orders_tax = isset($orders_tax[$row['id_tax_rule_rate']]) ? $orders_tax[$row['id_tax_rule_rate']]:0;
				
				// add options group
				if (!$stmt_add_shipping_tax->bind_param("iid", $id_orders,$id_orders_tax,$row['amount'])) throw new Exception('An error occured while trying to bind params to add shipping tax statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_add_shipping_tax->execute()) throw new Exception('An error occured while trying to add shipping tax.'."\r\n\r\n".$this->mysqli->error);																			
			}		
			
			$stmt_add_shipping_tax->close();			
		}
		$result->free();
		
		// get list of products
		if (!$result = $this->mysqli->query('SELECT
		cart_item.id,
		cart_item.id_cart_discount,
		cart_item.qty,
		cart_item_product.id AS id_cart_item_product,
		cart_item_product.id_product,
		cart_item_product.id_product_variant,
		cart_item_product.id_product_combo_product,
		cart_item_product.id_product_bundled_product_group_product,
		cart_item_product.id_product_related,
		cart_item_product.qty AS product_qty,
		cart_item_product.cost_price,
		cart_item_product.price,
		cart_item_product.sell_price,
		cart_item_product.special_price_start_date,
		cart_item_product.special_price_end_date,
		cart_item_product.subtotal,
		cart_item_product.taxes,
		product.product_type,
		product.used,
		product.sku,
		product.heavy_weight,
		IF(product_variant.id IS NOT NULL,product_variant.sku,"") AS variant_sku,
		IF(product_bundled_product_group_product.id IS NOT NULL,product_bundled_product_group_product.id_product_bundled_product_group,0) AS id_product_bundled_product_group,
		cart_item_product.id_tax_rule_exception,
		IF(cart_item_product.id_tax_rule_exception != 0,1,0) AS tax_exception,
		product.downloadable
		FROM 
		cart_item
		INNER JOIN
		cart_item_product
		ON
		(cart_item.id = cart_item_product.id_cart_item)
		INNER JOIN
		product
		ON
		(cart_item_product.id_product = product.id)
		LEFT JOIN
		product_variant
		ON
		(cart_item_product.id_product_variant = product_variant.id)
		LEFT JOIN 
		product_bundled_product_group_product
		ON
		(cart_item_product.id_product_bundled_product_group_product = product_bundled_product_group_product.id)
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($cart->id).'"
		ORDER BY 
		cart_item.id ASC')) throw new Exception('An error occured while trying to get list of products.'."\r\n\r\n".$this->mysqli->error);								
		
		if ($result->num_rows) {		
			/* Prepare the statement */		
			if (!$stmt_product_description = $this->mysqli->prepare('SELECT
			product_description.language_code,
			product_description.name,
			IF(product_variant.id IS NOT NULL,GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", "),"") AS variant_name
			FROM
			product
			INNER JOIN
			product_description
			ON
			(product.id = product_description.id_product)
			
			LEFT JOIN
			(product_variant
			CROSS JOIN product_variant_option 
			CROSS JOIN product_variant_group 
			CROSS JOIN product_variant_group_option 
			CROSS JOIN product_variant_group_option_description
			CROSS JOIN product_variant_group_description)						
			ON
			(product.id = product_variant.id_product
			AND product_variant.id = ?			
			AND product_variant.id = product_variant_option.id_product_variant 
			AND product_variant_option.id_product_variant_group = product_variant_group.id 
			AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
			AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option
			AND product_variant_group_option_description.language_code = product_description.language_code
			AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
            AND product_variant_group_description.language_code = product_description.language_code)
			
			WHERE
			product.id = ?
			GROUP BY 
			product.id,
			product_description.language_code')) throw new Exception('An error occured while trying to prepare get product description statement.'."\r\n\r\n".$this->mysqli->error);																		
			
			/* Prepare the statement */		
			if (!$stmt_product = $this->mysqli->prepare('SELECT
			cart_item_product.id AS id_cart_item_product,
			cart_item_product.id_product,
			cart_item_product.id_product_variant,
			cart_item_product.id_product_combo_product,
			cart_item_product.id_product_bundled_product_group_product,
			cart_item_product.id_product_related,
			cart_item_product.qty AS product_qty,
			cart_item_product.cost_price,
			cart_item_product.price,
			cart_item_product.sell_price,
			cart_item_product.special_price_start_date,
			cart_item_product.special_price_end_date,
			cart_item_product.subtotal,
			cart_item_product.taxes,
			product.product_type,
			product.used,
			product.sku,
			product.heavy_weight,
			IF(product_variant.id IS NOT NULL,product_variant.sku,"") AS variant_sku,
			IF(product_bundled_product_group_product.id IS NOT NULL,product_bundled_product_group_product.id_product_bundled_product_group,0) AS id_product_bundled_product_group,
			cart_item_product.id_tax_rule_exception,
			IF(cart_item_product.id_tax_rule_exception != 0,1,0) AS tax_exception,
			product.downloadable
			FROM
			cart_item_product
			INNER JOIN
			product
			ON
			(cart_item_product.id_product = product.id)
			LEFT JOIN
			product_variant
			ON
			(cart_item_product.id_product_variant = product_variant.id)
			LEFT JOIN 
			product_bundled_product_group_product
			ON
			(cart_item_product.id_product_bundled_product_group_product = product_bundled_product_group_product.id)
			WHERE
			cart_item_product.id_cart_item_product = ?
			ORDER BY 
			cart_item_product.id ASC')) throw new Exception('An error occured while trying to prepare get sub products statement.'."\r\n\r\n".$this->mysqli->error);				
			
			/* Prepare the statement */		
			if (!$stmt_product_downloadable_videos = $this->mysqli->prepare('SELECT
			product_downloadable_videos.id,
			product_downloadable_videos.embed_code,
			product_downloadable_videos.no_days_expire,
			product_downloadable_videos.no_downloads,
			product_downloadable_videos.sort_order
			FROM
			product_downloadable_videos
			WHERE
			product_downloadable_videos.id_product = ?
			AND
			(product_downloadable_videos.id_product_variant = 0 OR product_downloadable_videos.id_product_variant = ?)
			ORDER BY 
			product_downloadable_videos.id ASC')) throw new Exception('An error occured while trying to prepare get product downloadable videos statement.'."\r\n\r\n".$this->mysqli->error);	
			
			/* Prepare the statement */		
			if (!$stmt_product_downloadable_videos_description = $this->mysqli->prepare('SELECT
			product_downloadable_videos_description.language_code,
			product_downloadable_videos_description.name
			FROM
			product_downloadable_videos_description
			WHERE
			product_downloadable_videos_description.id_product_downloadable_videos = ?
			ORDER BY 
			product_downloadable_videos_description.language_code ASC')) throw new Exception('An error occured while trying to prepare get product downloadable video description statement.'."\r\n\r\n".$this->mysqli->error);				
			
			/* Prepare the statement */		
			if (!$stmt_product_downloadable_files = $this->mysqli->prepare('SELECT
			product_downloadable_files.id,
			product_downloadable_files.filename,
			product_downloadable_files.source,
			product_downloadable_files.no_days_expire,
			product_downloadable_files.no_downloads,
			product_downloadable_files.sort_order
			FROM
			product_downloadable_files
			WHERE
			product_downloadable_files.id_product = ?
			AND
			(product_downloadable_files.id_product_variant = 0 OR product_downloadable_files.id_product_variant = ?)
			ORDER BY 
			product_downloadable_files.id ASC')) throw new Exception('An error occured while trying to prepare get product downloadable files statement.'."\r\n\r\n".$this->mysqli->error);			
			
			/* Prepare the statement */		
			if (!$stmt_product_downloadable_files_description = $this->mysqli->prepare('SELECT
			product_downloadable_files_description.language_code,
			product_downloadable_files_description.name
			FROM
			product_downloadable_files_description
			WHERE
			product_downloadable_files_description.id_product_downloadable_files = ?
			ORDER BY 
			product_downloadable_files_description.language_code ASC')) throw new Exception('An error occured while trying to prepare get product downloadable file description statement.'."\r\n\r\n".$this->mysqli->error);																					
			
			/* Prepare the statement */		
			if (!$stmt_add_item = $this->mysqli->prepare('INSERT INTO
			orders_item 
			SET
			orders_item.id_orders = ?,
			orders_item.id_orders_discount = ?,
			orders_item.qty = ?')) throw new Exception('An error occured while trying to prepare add product statement.'."\r\n\r\n".$this->mysqli->error);				
			
			/* Prepare the statement */		
			if (!$stmt_add_product = $this->mysqli->prepare('INSERT INTO
			orders_item_product 
			SET
			orders_item_product.id_orders_item = ?,
			orders_item_product.id_orders_item_product = ?,
			orders_item_product.id_product = ?,
			orders_item_product.id_product_related = ?,
			orders_item_product.id_product_variant = ?,
			orders_item_product.id_product_combo_product = ?,
			orders_item_product.id_product_bundled_product_group = ?,
			orders_item_product.id_product_bundled_product_group_product = ?,
			orders_item_product.product_type = ?,
			orders_item_product.used = ?,
			orders_item_product.sku = ?,
			orders_item_product.heavy_weight = ?,
			orders_item_product.variant_sku = ?,
			orders_item_product.qty = ?,
			orders_item_product.cost_price = ?,
			orders_item_product.price = ?,
			orders_item_product.sell_price = ?,
			orders_item_product.special_price_start_date = ?,
			orders_item_product.special_price_end_date = ?,
			orders_item_product.subtotal = ?,
			orders_item_product.taxes = ?,
			orders_item_product.tax_exception = ?')) throw new Exception('An error occured while trying to prepare add product statement.'."\r\n\r\n".$this->mysqli->error);			
			
			/* Prepare the statement */		
			if (!$stmt_add_product_description = $this->mysqli->prepare('INSERT INTO
			orders_item_product_description 
			SET
			orders_item_product_description.id_orders_item_product = ?,
			orders_item_product_description.language_code = ?,
			orders_item_product_description.name = ?,
			orders_item_product_description.variant_name = ?')) throw new Exception('An error occured while trying to prepare add product statement.'."\r\n\r\n".$this->mysqli->error);							
			
			/* Prepare the statement */		
			if (!$stmt_product_tax = $this->mysqli->prepare('SELECT
			cart_item_product_tax.id_tax_rule_rate,			
			cart_item_product_tax.amount,
			IF(tax_rule_exception_rate.id IS NOT NULL,tax_rule_exception_rate.rate,tax_rule_rate.rate) AS rate
			FROM
			cart_item_product_tax
			
			INNER JOIN
			tax_rule_rate
			ON
			(cart_item_product_tax.id_tax_rule_rate = tax_rule_rate.id)
						
			LEFT JOIN
			tax_rule_exception_rate
			ON
			(tax_rule_rate.id = tax_rule_exception_rate.id_tax_rule_rate AND tax_rule_exception_rate.id_tax_rule_exception = ?)
			WHERE
			cart_item_product_tax.id_cart_item_product = ?
			ORDER BY 
			cart_item_product_tax.id ASC')) throw new Exception('An error occured while trying to prepare get product tax statement.'."\r\n\r\n".$this->mysqli->error);									
			
			/* Prepare the statement */		
			if (!$stmt_add_product_tax = $this->mysqli->prepare('INSERT INTO
			orders_item_product_tax 
			SET
			orders_item_product_tax.id_orders_item_product = ?,
			orders_item_product_tax.id_orders_tax = ?,
			orders_item_product_tax.rate = ?,
			orders_item_product_tax.amount = ?')) throw new Exception('An error occured while trying to prepare add product tax statement.'."\r\n\r\n".$this->mysqli->error);										
			
			/* Prepare the statement */		
			if (!$stmt_discount_item_product = $this->mysqli->prepare('SELECT
			id_cart_discount,
			amount
			FROM
			cart_discount_item_product
			WHERE
			id_cart_item_product = ?
			ORDER BY 
			id ASC')) throw new Exception('An error occured while trying to prepare get item product discount statement.'."\r\n\r\n".$this->mysqli->error);															
			
			/* Prepare the statement */		
			if (!$stmt_add_discount_item_product = $this->mysqli->prepare('INSERT INTO
			orders_discount_item_product 
			SET
			id_orders_item_product = ?,
			id_orders_discount = ?,
			amount = ?')) throw new Exception('An error occured while trying to prepare add item product discount statement.'."\r\n\r\n".$this->mysqli->error);				
			
			/* Prepare the statement */		
			if (!$stmt_add_product_downloadable_videos = $this->mysqli->prepare('INSERT INTO
			orders_item_product_downloadable_videos 
			SET
			orders_item_product_downloadable_videos.id_orders_item_product = ?,
			orders_item_product_downloadable_videos.id_product_downloadable_videos = ?,
			orders_item_product_downloadable_videos.embed_code = ?,
			orders_item_product_downloadable_videos.no_days_expire = ?,
			orders_item_product_downloadable_videos.no_downloads = ?,
			orders_item_product_downloadable_videos.sort_order = ?')) throw new Exception('An error occured while trying to prepare add product downloadable video statement.'."\r\n\r\n".$this->mysqli->error);			
			
			/* Prepare the statement */		
			if (!$stmt_add_product_downloadable_videos_description = $this->mysqli->prepare('INSERT INTO
			orders_item_product_downloadable_videos_description 
			SET
			orders_item_product_downloadable_videos_description.id_orders_item_product_downloadable_videos = ?,
			orders_item_product_downloadable_videos_description.language_code = ?,
			orders_item_product_downloadable_videos_description.name = ?')) throw new Exception('An error occured while trying to prepare add product downloadable video statement.'."\r\n\r\n".$this->mysqli->error);		
			
			/* Prepare the statement */		
			if (!$stmt_add_product_downloadable_files = $this->mysqli->prepare('INSERT INTO
			orders_item_product_downloadable_files
			SET
			orders_item_product_downloadable_files.id_orders_item_product = ?,
			orders_item_product_downloadable_files.id_product_downloadable_files = ?,
			orders_item_product_downloadable_files.no_days_expire = ?,
			orders_item_product_downloadable_files.no_downloads = ?,
			orders_item_product_downloadable_files.sort_order = ?')) throw new Exception('An error occured while trying to prepare add product downloadable file statement.'."\r\n\r\n".$this->mysqli->error);			
			
			/* Prepare the statement */		
			if (!$stmt_add_product_downloadable_files_description = $this->mysqli->prepare('INSERT INTO
			orders_item_product_downloadable_files_description 
			SET
			orders_item_product_downloadable_files_description.id_orders_item_product_downloadable_files = ?,
			orders_item_product_downloadable_files_description.language_code = ?,
			orders_item_product_downloadable_files_description.name = ?')) throw new Exception('An error occured while trying to prepare add product downloadable file statement.'."\r\n\r\n".$this->mysqli->error);						
			
			/* Prepare the statement */		
			if (!$stmt_option = $this->mysqli->prepare('SELECT
			cart_item_option.id,
			cart_item_option.id_product_options_group,
			cart_item_option.id_options_group,
			cart_item_option.id_options,
			cart_item_option.id_tax_rule_exception,
			cart_item_option.qty,
			options.sku,
			cart_item_option.cost_price,
			cart_item_option.price,
			cart_item_option.sell_price,
			cart_item_option.textfield,
			cart_item_option.textarea,
			cart_item_option.filename_tmp,
			cart_item_option.filename,
			cart_item_option.date_start,
			cart_item_option.date_end,
			cart_item_option.datetime_start,
			cart_item_option.datetime_end,
			cart_item_option.time_start,
			cart_item_option.time_end,
			cart_item_option.subtotal,
			cart_item_option.taxes,
			cart_item_option.id_tax_rule_exception,
			IF(cart_item_option.id_tax_rule_exception != 0,1,0) AS tax_exception
			FROM
			cart_item_option
			INNER JOIN
			options
			ON
			(cart_item_option.id_options = options.id)
			
			WHERE
			cart_item_option.id_cart_item = ?
			ORDER BY 
			cart_item_option.id ASC')) throw new Exception('An error occured while trying to prepare get options statement.'."\r\n\r\n".$this->mysqli->error);																		
			
			/* Prepare the statement */		
			if (!$stmt_option_description = $this->mysqli->prepare('SELECT
			options_description.language_code,
			options_description.name,
			options_description.description
			FROM
			options_description
			WHERE
			options_description.id_options = ?')) throw new Exception('An error occured while trying to prepare get option description statement.'."\r\n\r\n".$this->mysqli->error);											
			
			/* Prepare the statement */		
			if (!$stmt_add_option = $this->mysqli->prepare('INSERT INTO
			orders_item_option 
			SET
			orders_item_option.id_orders_item = ?,
			orders_item_option.id_product_options_group = ?,
			orders_item_option.id_options_group = ?,
			orders_item_option.id_options = ?,
			orders_item_option.sku = ?,
			orders_item_option.qty = ?,
			orders_item_option.cost_price = ?,
			orders_item_option.price = ?,
			orders_item_option.sell_price = ?,
			orders_item_option.textfield = ?,
			orders_item_option.textarea = ?,
			orders_item_option.date_start = ?,
			orders_item_option.date_end = ?,
			orders_item_option.datetime_start = ?,
			orders_item_option.datetime_end = ?,
			orders_item_option.time_start = ?,
			orders_item_option.time_end = ?,
			orders_item_option.subtotal = ?,
			orders_item_option.taxes = ?,
			orders_item_option.tax_exception = ?')) throw new Exception('An error occured while trying to prepare add option statement.'."\r\n\r\n".$this->mysqli->error);		
			
			/* Prepare the statement */		
			if (!$stmt_upd_option_filename = $this->mysqli->prepare('UPDATE
			orders_item_option
			SET
			orders_item_option.filename = ?
			WHERE
			id = ?')) throw new Exception('An error occured while trying to prepare update option filename.'."\r\n\r\n".$this->mysqli->error);							
			
			/* Prepare the statement */		
			if (!$stmt_add_option_description = $this->mysqli->prepare('INSERT INTO
			orders_item_option_description 
			SET			
			orders_item_option_description.id_orders_item_option = ?,
			orders_item_option_description.language_code = ?,
			orders_item_option_description.name = ?,
			orders_item_option_description.description = ?')) throw new Exception('An error occured while trying to prepare add option description statement.'."\r\n\r\n".$this->mysqli->error);								
			
			/* Prepare the statement */		
			if (!$stmt_option_tax = $this->mysqli->prepare('SELECT
			cart_item_option_tax.id_tax_rule_rate,
			cart_item_option_tax.amount,
			IF(tax_rule_exception_rate.id IS NOT NULL,tax_rule_exception_rate.rate,tax_rule_rate.rate) AS rate
			FROM
			cart_item_option_tax
			
			INNER JOIN
			tax_rule_rate
			ON
			(cart_item_option_tax.id_tax_rule_rate = tax_rule_rate.id)
						
			LEFT JOIN
			tax_rule_exception_rate
			ON
			(tax_rule_rate.id = tax_rule_exception_rate.id_tax_rule_rate AND tax_rule_exception_rate.id_tax_rule_exception = ?)
			WHERE
			cart_item_option_tax.id_cart_item_option = ?
			ORDER BY 
			cart_item_option_tax.id ASC')) throw new Exception('An error occured while trying to prepare get option tax statement.'."\r\n\r\n".$this->mysqli->error);									
			
			/* Prepare the statement */		
			if (!$stmt_add_option_tax = $this->mysqli->prepare('INSERT INTO
			orders_item_option_tax 
			SET
			orders_item_option_tax.id_orders_item_option = ?,
			orders_item_option_tax.id_orders_tax = ?,
			orders_item_option_tax.rate = ?,
			orders_item_option_tax.amount = ?')) throw new Exception('An error occured while trying to prepare add option tax statement.'."\r\n\r\n".$this->mysqli->error);					
			
			/* Prepare the statement */		
			if (!$stmt_discount_item_option = $this->mysqli->prepare('SELECT
			id_cart_discount,
			amount
			FROM
			cart_discount_item_option
			WHERE
			id_cart_item_option = ?
			ORDER BY 
			id ASC')) throw new Exception('An error occured while trying to prepare get item option discount statement.'."\r\n\r\n".$this->mysqli->error);															
			
			/* Prepare the statement */		
			if (!$stmt_add_discount_item_option = $this->mysqli->prepare('INSERT INTO
			orders_discount_item_option 
			SET
			id_orders_item_option = ?,
			id_orders_discount = ?,
			amount = ?')) throw new Exception('An error occured while trying to prepare add item option discount statement.'."\r\n\r\n".$this->mysqli->error);				
						
			while ($row = $result->fetch_assoc()) {
				$id_orders_discount = isset($orders_discount[$row['id_cart_discount']]) ? $orders_discount[$row['id_cart_discount']]:0;
				
				// insert item 
				if (!$stmt_add_item->bind_param("iii",$id_orders,$id_orders_discount,$row['qty'])) throw new Exception('An error occured while trying to bind params to add item statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_add_item->execute()) throw new Exception('An error occured while trying to add item.'."\r\n\r\n".$this->mysqli->error);							
				
				// get item id
				$id_orders_item = $this->mysqli->insert_id;		
				$id_orders_item_product = 0;
				
				// insert product 
				if (!$stmt_add_product->bind_param("iiiiiiiiiisisidddssddi",$id_orders_item,$id_orders_item_product,$row['id_product'],$row['id_product_related'],
				$row['id_product_variant'],$row['id_product_combo_product'],$row['id_product_bundled_product_group'],
				$row['id_product_bundled_product_group_product'],$row['product_type'],$row['used'],$row['sku'],$row['heavy_weight'],$row['variant_sku'],$row['product_qty'],
				$row['cost_price'],$row['price'],$row['sell_price'],$row['special_price_start_date'],$row['special_price_end_date'],
				$row['subtotal'],$row['taxes'],$row['tax_exception'])) throw new Exception('An error occured while trying to bind params to add product statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_add_product->execute()) throw new Exception('An error occured while trying to add product.'."\r\n\r\n".$this->mysqli->error);							
				
				$id_orders_item_product = $this->mysqli->insert_id;
				
				// insert product description
				if (!$stmt_product_description->bind_param("ii",$row['id_product_variant'],$row['id_product'])) throw new Exception('An error occured while trying to bind params to get product description statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_product_description->execute()) throw new Exception('An error occured while trying to get product description.'."\r\n\r\n".$this->mysqli->error);	
				
				/* store result */
				$stmt_product_description->store_result();																											
				
				if ($stmt_product_description->num_rows) {												
					/* bind result variables */
					$stmt_product_description->bind_result($language_code, $name, $variant_name);	
						
					while ($stmt_product_description->fetch()) {
						// add discount description
						if (!$stmt_add_product_description->bind_param("isss", $id_orders_item_product,$language_code,$name,$variant_name)) throw new Exception('An error occured while trying to bind params to add product description statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_add_product_description->execute()) throw new Exception('An error occured while trying to add product description.'."\r\n\r\n".$this->mysqli->error);									
					}
				}						
				
				// insert product tax
				if (!$stmt_product_tax->bind_param("ii",$row['id_tax_rule_exception'],$row['id_cart_item_product'])) throw new Exception('An error occured while trying to bind params to get product tax statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_product_tax->execute()) throw new Exception('An error occured while trying to get product tax.'."\r\n\r\n".$this->mysqli->error);	
				
				/* store result */
				$stmt_product_tax->store_result();																											
				
				if ($stmt_product_tax->num_rows) {												
					/* bind result variables */
					$stmt_product_tax->bind_result($id_tax_rule_rate, $amount, $rate);	
						
					while ($stmt_product_tax->fetch()) {
						$id_orders_tax = isset($orders_tax[$id_tax_rule_rate]) ? $orders_tax[$id_tax_rule_rate]:0;
						
						// add discount description
						if (!$stmt_add_product_tax->bind_param("iidd", $id_orders_item_product,$id_orders_tax,$rate,$amount)) throw new Exception('An error occured while trying to bind params to add product tax statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_add_product_tax->execute()) throw new Exception('An error occured while trying to add product tax.'."\r\n\r\n".$this->mysqli->error);									
					}
				}	
				
				// insert discount
				if (!$stmt_discount_item_product->bind_param("i",$row['id_cart_item_product'])) throw new Exception('An error occured while trying to bind params to get product discount statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_discount_item_product->execute()) throw new Exception('An error occured while trying to get product discount.'."\r\n\r\n".$this->mysqli->error);	
				
				/* store result */
				$stmt_discount_item_product->store_result();																											
				
				if ($stmt_discount_item_product->num_rows) {												
					/* bind result variables */
					$stmt_discount_item_product->bind_result($id_cart_discount, $amount);	
						
					while ($stmt_discount_item_product->fetch()) {
						$id_orders_discount = isset($orders_discount[$id_cart_discount]) ? $orders_discount[$id_cart_discount]:0;
						
						// add discount description
						if (!$stmt_add_discount_item_product->bind_param("iid", $id_orders_item_product,$id_orders_discount,$amount)) throw new Exception('An error occured while trying to bind params to add product discount statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_add_discount_item_product->execute()) throw new Exception('An error occured while trying to add product discount.'."\r\n\r\n".$this->mysqli->error);									
					}
				}		
				
				// check if downloadable product
				if ($row['downloadable']) {
					// get downloadable videos
					if (!$stmt_product_downloadable_videos->bind_param("ii",$row['id_product'],$row['id_product_variant'])) throw new Exception('An error occured while trying to bind params to get product downloadable videos statement.'."\r\n\r\n".$this->mysqli->error);
					
					/* Execute the statement */
					if (!$stmt_product_downloadable_videos->execute()) throw new Exception('An error occured while trying to get product downloadable videos.'."\r\n\r\n".$this->mysqli->error);	
					
					/* store result */
					$stmt_product_downloadable_videos->store_result();																											
					
					if ($stmt_product_downloadable_videos->num_rows) {	
						/* bind result variables */
						$stmt_product_downloadable_videos->bind_result($id_product_downloadable_videos, $embed_code, $no_days_expire, $no_downloads,$sort_order);	
					
						while ($stmt_product_downloadable_videos->fetch()) {
							// add downlodable video
							if (!$stmt_add_product_downloadable_videos->bind_param("iisiii", $id_orders_item_product,$id_product_downloadable_videos,$embed_code,$no_days_expire,$no_downloads,$sort_order)) throw new Exception('An error occured while trying to bind params to add product downloadable video statement.'."\r\n\r\n".$this->mysqli->error);
							
							/* Execute the statement */
							if (!$stmt_add_product_downloadable_videos->execute()) throw new Exception('An error occured while trying to add product downloadable video.'."\r\n\r\n".$this->mysqli->error);		
							
							$id_orders_item_product_downloadable_videos = $this->mysqli->insert_id;
							
							// get downloadable videos description
							if (!$stmt_product_downloadable_videos_description->bind_param("i",$id_product_downloadable_videos)) throw new Exception('An error occured while trying to bind params to get product downloadable video description statement.'."\r\n\r\n".$this->mysqli->error);
							
							/* Execute the statement */
							if (!$stmt_product_downloadable_videos_description->execute()) throw new Exception('An error occured while trying to get product downloadable video description.'."\r\n\r\n".$this->mysqli->error);	
							
							/* store result */
							$stmt_product_downloadable_videos_description->store_result();																											
							
							if ($stmt_product_downloadable_videos_description->num_rows) {	
								/* bind result variables */
								$stmt_product_downloadable_videos_description->bind_result($language_code, $name);	
							
								while ($stmt_product_downloadable_videos_description->fetch()) {
									// add downlodable video
									if (!$stmt_add_product_downloadable_videos_description->bind_param("iss", $id_orders_item_product_downloadable_videos,$language_code,$name)) throw new Exception('An error occured while trying to bind params to add product downloadable video description statement.'."\r\n\r\n".$this->mysqli->error);
									
									/* Execute the statement */
									if (!$stmt_add_product_downloadable_videos_description->execute()) throw new Exception('An error occured while trying to add product downloadable video description.'."\r\n\r\n".$this->mysqli->error);	
								}
							}
						}
					}
					
					
					
					
					// get downloadable files
					if (!$stmt_product_downloadable_files->bind_param("ii",$row['id_product'],$row['id_product_variant'])) throw new Exception('An error occured while trying to bind params to get product downloadable files statement.'."\r\n\r\n".$this->mysqli->error);
					
					/* Execute the statement */
					if (!$stmt_product_downloadable_files->execute()) throw new Exception('An error occured while trying to get product downloadable files.'."\r\n\r\n".$this->mysqli->error);	
					
					/* store result */
					$stmt_product_downloadable_files->store_result();																											
					
					if ($stmt_product_downloadable_files->num_rows) {	
						/* bind result variables */
						$stmt_product_downloadable_files->bind_result($id_product_downloadable_files, $filename, $source, $no_days_expire, $no_downloads,$sort_order);	
					
						while ($stmt_product_downloadable_files->fetch()) {
							// add downlodable video
							if (!$stmt_add_product_downloadable_files->bind_param("iiiii", $id_orders_item_product,$id_product_downloadable_files,$no_days_expire,$no_downloads,$sort_order)) throw new Exception('An error occured while trying to bind params to add product downloadable file statement.'."\r\n\r\n".$this->mysqli->error);
							
							/* Execute the statement */
							if (!$stmt_add_product_downloadable_files->execute()) throw new Exception('An error occured while trying to add product downloadable file.'."\r\n\r\n".$this->mysqli->error);		
							
							$id_orders_item_product_downloadable_files = $this->mysqli->insert_id;
							
							// get downloadable videos description
							if (!$stmt_product_downloadable_files_description->bind_param("i",$id_product_downloadable_files)) throw new Exception('An error occured while trying to bind params to get product downloadable file description statement.'."\r\n\r\n".$this->mysqli->error);
							
							/* Execute the statement */
							if (!$stmt_product_downloadable_files_description->execute()) throw new Exception('An error occured while trying to get product downloadable file description.'."\r\n\r\n".$this->mysqli->error);	
							
							/* store result */
							$stmt_product_downloadable_files_description->store_result();																											
							
							if ($stmt_product_downloadable_files_description->num_rows) {	
								/* bind result variables */
								$stmt_product_downloadable_files_description->bind_result($language_code, $name);	
							
								while ($stmt_product_downloadable_files_description->fetch()) {
									// add downlodable video
									if (!$stmt_add_product_downloadable_files_description->bind_param("iss", $id_orders_item_product_downloadable_files,$language_code,$name)) throw new Exception('An error occured while trying to bind params to add product downloadable video description statement.'."\r\n\r\n".$this->mysqli->error);
									
									/* Execute the statement */
									if (!$stmt_add_product_downloadable_files_description->execute()) throw new Exception('An error occured while trying to add product downloadable video description.'."\r\n\r\n".$this->mysqli->error);	
								}
							}
						}
					}					
				}
				
						
				
				// insert option
				if (!$stmt_option->bind_param("i",$row['id'])) throw new Exception('An error occured while trying to bind params to get product options statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_option->execute()) throw new Exception('An error occured while trying to get product options.'."\r\n\r\n".$this->mysqli->error);	
				
				/* store result */
				$stmt_option->store_result();																											
				
				if ($stmt_option->num_rows) {			
					/* bind result variables */
					$stmt_option->bind_result($o_id_cart_item_option,$o_id_product_options_group,$o_id_options_group,$o_id_options,$o_id_tax_rule_exception,
					$o_qty,$o_sku,$o_cost_price,$o_price,$o_sell_price,$o_textfield,$o_textarea,$o_filename_tmp,$o_filename,$o_date_start,$o_date_end,
					$o_datetime_start,$o_datetime_end,$o_time_start,$o_time_end,$o_subtotal,$o_taxes,$o_id_tax_rule_exception,$o_tax_exception);	
						
					while ($stmt_option->fetch()) {
						// add option
						if (!$stmt_add_option->bind_param("iiiisidddssssssssddi", $id_orders_item,$o_id_product_options_group,$o_id_options_group,
						$o_id_options,$o_sku,$o_qty,$o_cost_price,$o_price,$o_sell_price,$o_textfield,$o_textarea,$o_date_start,$o_date_end,
						$o_datetime_start,$o_datetime_end,$o_time_start,$o_time_end,$o_subtotal,$o_taxes,$o_tax_exception)) throw new Exception('An error occured while trying to bind params to add product option statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_add_option->execute()) throw new Exception('An error occured while trying to add product option.'."\r\n\r\n".$this->mysqli->error);							
						
						$id_orders_item_option = $this->mysqli->insert_id;
						
						if ($o_filename_tmp && $o_filename && is_file($tmp_uploads_dir.$o_filename_tmp)) {
							$o_filename = $id_orders_item_option.'-'.$o_filename;
							
							copy($tmp_uploads_dir.$o_filename_tmp,$file_uploads_dir.$o_filename);
							
							// update option filename
							if (!$stmt_upd_option_filename->bind_param("si", $o_filename,$id_orders_item_option)) throw new Exception('An error occured while trying to bind params to update product option filename statement.'."\r\n\r\n".$this->mysqli->error);
							
							/* Execute the statement */
							if (!$stmt_upd_option_filename->execute()) throw new Exception('An error occured while trying to update product option filename.'."\r\n\r\n".$this->mysqli->error);	
						}												
						
						// insert option description
						if (!$stmt_option_description->bind_param("i",$o_id_options)) throw new Exception('An error occured while trying to bind params to get product description statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_option_description->execute()) throw new Exception('An error occured while trying to get product description.'."\r\n\r\n".$this->mysqli->error);	
						
						/* store result */
						$stmt_option_description->store_result();																											
						
						if ($stmt_option_description->num_rows) {												
							/* bind result variables */
							$stmt_option_description->bind_result($language_code, $name, $description);	
								
							while ($stmt_option_description->fetch()) {
								// add discount description
								if (!$stmt_add_option_description->bind_param("isss", $id_orders_item_option,$language_code,$name,$description)) throw new Exception('An error occured while trying to bind params to add option description statement.'."\r\n\r\n".$this->mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_add_option_description->execute()) throw new Exception('An error occured while trying to add option description.'."\r\n\r\n".$this->mysqli->error);							
							}
						}		
						
						
						// insert option tax
						if (!$stmt_option_tax->bind_param("ii",$o_id_tax_rule_exception,$o_id_cart_item_option)) throw new Exception('An error occured while trying to bind params to get option tax statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_option_tax->execute()) throw new Exception('An error occured while trying to get option tax.'."\r\n\r\n".$this->mysqli->error);	
						
						/* store result */
						$stmt_option_tax->store_result();																											
						
						if ($stmt_option_tax->num_rows) {												
							/* bind result variables */
							$stmt_option_tax->bind_result($id_tax_rule_rate, $amount, $rate);	
								
							while ($stmt_option_tax->fetch()) {
								$id_orders_tax = isset($orders_tax[$id_tax_rule_rate]) ? $orders_tax[$id_tax_rule_rate]:0;
								
								// add option tax
								if (!$stmt_add_option_tax->bind_param("iidd", $id_orders_item_option,$id_orders_tax,$rate,$amount)) throw new Exception('An error occured while trying to bind params to add option tax statement.'."\r\n\r\n".$this->mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_add_option_tax->execute()) throw new Exception('An error occured while trying to add option tax.'."\r\n\r\n".$this->mysqli->error);									
							}
						}	
						
						// insert discount
						if (!$stmt_discount_item_option->bind_param("i",$o_id_cart_item_option)) throw new Exception('An error occured while trying to bind params to get option discount statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_discount_item_option->execute()) throw new Exception('An error occured while trying to get option discount.'."\r\n\r\n".$this->mysqli->error);	
						
						/* store result */
						$stmt_discount_item_option->store_result();																											
						
						if ($stmt_discount_item_option->num_rows) {												
							/* bind result variables */
							$stmt_discount_item_option->bind_result($id_cart_discount, $amount);	
								
							while ($stmt_discount_item_option->fetch()) {
								$id_orders_discount = isset($orders_discount[$id_cart_discount]) ? $orders_discount[$id_cart_discount]:0;
								
								// add discount description
								if (!$stmt_add_discount_item_option->bind_param("iid", $id_orders_item_option,$id_orders_discount,$amount)) throw new Exception('An error occured while trying to bind params to add product option discount statement.'."\r\n\r\n".$this->mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_add_discount_item_option->execute()) throw new Exception('An error occured while trying to add product option discount.'."\r\n\r\n".$this->mysqli->error);									
							}
						}														
					}
				}				
					
								
				// insert sub products
				if (!$stmt_product->bind_param("i",$row['id_cart_item_product'])) throw new Exception('An error occured while trying to bind params to get sub products statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_product->execute()) throw new Exception('An error occured while trying to get product discount.'."\r\n\r\n".$this->mysqli->error);	
				
				/* store result */
				$stmt_product->store_result();																											
				
				if ($stmt_product->num_rows) {												
					$p_id_orders_item = 0;
				
					/* bind result variables */
					$stmt_product->bind_result($p_id_cart_item_product, $p_id_product, $p_id_product_variant, $p_id_product_combo_product, 
					$p_id_product_bundled_product_group_product, $p_id_product_related, $p_product_qty, $p_cost_price, $p_price, $p_sell_price,
					$p_special_price_start_date, $p_special_price_end_date, $p_subtotal, $p_taxes, $p_product_type, $p_used, $p_sku, $p_heavy_weight, $p_variant_sku,
					$p_id_product_bundled_product_group, $p_id_tax_rule_exception, $p_tax_exception, $p_downloadable);					
					
					while ($stmt_product->fetch()) {
						// insert product 
						if (!$stmt_add_product->bind_param("iiiiiiiiiisisidddssddi",$p_id_orders_item,$id_orders_item_product,
						$p_id_product,$p_id_product_related,$p_id_product_variant,$p_id_product_combo_product,$p_id_product_bundled_product_group,
						$p_id_product_bundled_product_group_product,$p_product_type,$p_used,$p_sku,$p_heavy_weight,$p_variant_sku,$p_product_qty,
						$p_cost_price,$p_price,$p_sell_price,$p_special_price_start_date,$p_special_price_end_date,
						$p_subtotal,$p_taxes,$p_tax_exception)) throw new Exception('An error occured while trying to bind params to add product statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_add_product->execute()) throw new Exception('An error occured while trying to add product.'."\r\n\r\n".$this->mysqli->error);							
						
						$p_id_orders_item_product = $this->mysqli->insert_id;
						
						// insert product description
						if (!$stmt_product_description->bind_param("ii",$p_id_product_variant,$p_id_product)) throw new Exception('An error occured while trying to bind params to get product description statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_product_description->execute()) throw new Exception('An error occured while trying to get product description.'."\r\n\r\n".$this->mysqli->error);	
						
						/* store result */
						$stmt_product_description->store_result();																											
						
						if ($stmt_product_description->num_rows) {												
							/* bind result variables */
							$stmt_product_description->bind_result($language_code, $name, $variant_name);	
								
							while ($stmt_product_description->fetch()) {
								// add discount description
								if (!$stmt_add_product_description->bind_param("isss", $p_id_orders_item_product,$language_code,$name,$variant_name)) throw new Exception('An error occured while trying to bind params to add product description statement.'."\r\n\r\n".$this->mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_add_product_description->execute()) throw new Exception('An error occured while trying to add product description.'."\r\n\r\n".$this->mysqli->error);									
							}
						}						
						
						// insert product tax
						if (!$stmt_product_tax->bind_param("ii",$p_id_tax_rule_exception,$p_id_cart_item_product)) throw new Exception('An error occured while trying to bind params to get product tax statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_product_tax->execute()) throw new Exception('An error occured while trying to get product tax.'."\r\n\r\n".$this->mysqli->error);	
						
						/* store result */
						$stmt_product_tax->store_result();																											
						
						if ($stmt_product_tax->num_rows) {												
							/* bind result variables */
							$stmt_product_tax->bind_result($id_tax_rule_rate, $amount, $rate);	
								
							while ($stmt_product_tax->fetch()) {
								$id_orders_tax = isset($orders_tax[$id_tax_rule_rate]) ? $orders_tax[$id_tax_rule_rate]:0;
								
								// add discount description
								if (!$stmt_add_product_tax->bind_param("iidd", $p_id_orders_item_product,$id_orders_tax,$rate,$amount)) throw new Exception('An error occured while trying to bind params to add product tax statement.'."\r\n\r\n".$this->mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_add_product_tax->execute()) throw new Exception('An error occured while trying to add product tax.'."\r\n\r\n".$this->mysqli->error);									
							}
						}	
						
						// insert discount
						if (!$stmt_discount_item_product->bind_param("i",$p_id_cart_item_product)) throw new Exception('An error occured while trying to bind params to get product discount statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_discount_item_product->execute()) throw new Exception('An error occured while trying to get product discount.'."\r\n\r\n".$this->mysqli->error);	
						
						/* store result */
						$stmt_discount_item_product->store_result();																											
						
						if ($stmt_discount_item_product->num_rows) {												
							/* bind result variables */
							$stmt_discount_item_product->bind_result($id_cart_discount, $amount);	
								
							while ($stmt_discount_item_product->fetch()) {
								$id_orders_discount = isset($orders_discount[$id_cart_discount]) ? $orders_discount[$id_cart_discount]:0;
								
								// add discount description
								if (!$stmt_add_discount_item_product->bind_param("iid", $p_id_orders_item_product,$id_orders_discount,$amount)) throw new Exception('An error occured while trying to bind params to add product discount statement.'."\r\n\r\n".$this->mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_add_discount_item_product->execute()) throw new Exception('An error occured while trying to add product discount.'."\r\n\r\n".$this->mysqli->error);									
							}
						}
						
						// check if downloadable product
						if ($p_downloadable) {
							// get downloadable videos
							if (!$stmt_product_downloadable_videos->bind_param("ii",$p_id_product,$p_id_product_variant)) throw new Exception('An error occured while trying to bind params to get product downloadable videos statement.'."\r\n\r\n".$this->mysqli->error);
							
							/* Execute the statement */
							if (!$stmt_product_downloadable_videos->execute()) throw new Exception('An error occured while trying to get product downloadable videos.'."\r\n\r\n".$this->mysqli->error);	
							
							/* store result */
							$stmt_product_downloadable_videos->store_result();																											
							
							if ($stmt_product_downloadable_videos->num_rows) {	
								/* bind result variables */
								$stmt_product_downloadable_videos->bind_result($id_product_downloadable_videos, $embed_code, $no_days_expire, $no_downloads);	
							
								while ($stmt_product_downloadable_videos->fetch()) {
									// add downlodable video
									if (!$stmt_add_product_downloadable_videos->bind_param("iisii", $p_id_orders_item_product,$id_product_downloadable_videos,$embed_code,$no_days_expire,$no_downloads)) throw new Exception('An error occured while trying to bind params to add product downloadable video statement.'."\r\n\r\n".$this->mysqli->error);
									
									/* Execute the statement */
									if (!$stmt_add_product_downloadable_videos->execute()) throw new Exception('An error occured while trying to add product downloadable video.'."\r\n\r\n".$this->mysqli->error);		
									
									$id_orders_item_product_downloadable_videos = $this->mysqli->insert_id;
									
									// get downloadable videos description
									if (!$stmt_product_downloadable_videos_description->bind_param("i",$id_product_downlodable_videos)) throw new Exception('An error occured while trying to bind params to get product downloadable video description statement.'."\r\n\r\n".$this->mysqli->error);
									
									/* Execute the statement */
									if (!$stmt_product_downloadable_videos_description->execute()) throw new Exception('An error occured while trying to get product downloadable video description.'."\r\n\r\n".$this->mysqli->error);	
									
									/* store result */
									$stmt_product_downloadable_videos_description->store_result();																											
									
									if ($stmt_product_downloadable_videos_description->num_rows) {	
										/* bind result variables */
										$stmt_product_downloadable_videos_description->bind_result($language_code, $name);	
									
										while ($stmt_product_downloadable_videos_description->fetch()) {
											// add downlodable video
											if (!$stmt_add_product_downloadable_videos_description->bind_param("iss", $id_orders_item_product_downloadable_videos,$language_code,$name)) throw new Exception('An error occured while trying to bind params to add product downloadable video description statement.'."\r\n\r\n".$this->mysqli->error);
											
											/* Execute the statement */
											if (!$stmt_add_product_downloadable_videos_description->execute()) throw new Exception('An error occured while trying to add product downloadable video description.'."\r\n\r\n".$this->mysqli->error);	
										}
									}
								}
							}
							
							
							
							
							// get downloadable files
							if (!$stmt_product_downloadable_files->bind_param("ii",$p_id_product,$p_id_product_variant)) throw new Exception('An error occured while trying to bind params to get product downloadable files statement.'."\r\n\r\n".$this->mysqli->error);
							
							/* Execute the statement */
							if (!$stmt_product_downloadable_files->execute()) throw new Exception('An error occured while trying to get product downloadable files.'."\r\n\r\n".$this->mysqli->error);	
							
							/* store result */
							$stmt_product_downloadable_files->store_result();																											
							
							if ($stmt_product_downloadable_files->num_rows) {	
								/* bind result variables */
								$stmt_product_downloadable_files->bind_result($id_product_downloadable_files, $filename, $source, $no_days_expire, $no_downloads);	
							
								while ($stmt_product_downloadable_files->fetch()) {
									// add downlodable video
									if (!$stmt_add_product_downloadable_files->bind_param("iiii", $p_id_orders_item_product,$id_product_downloadable_files,$no_days_expire,$no_downloads)) throw new Exception('An error occured while trying to bind params to add product downloadable file statement.'."\r\n\r\n".$this->mysqli->error);
									
									/* Execute the statement */
									if (!$stmt_add_product_downloadable_files->execute()) throw new Exception('An error occured while trying to add product downloadable file.'."\r\n\r\n".$this->mysqli->error);		
									
									$id_orders_item_product_downloadable_files = $this->mysqli->insert_id;
									
									// get downloadable videos description
									if (!$stmt_product_downloadable_files_description->bind_param("i",$id_product_downloadable_files)) throw new Exception('An error occured while trying to bind params to get product downloadable file description statement.'."\r\n\r\n".$this->mysqli->error);
									
									/* Execute the statement */
									if (!$stmt_product_downloadable_files_description->execute()) throw new Exception('An error occured while trying to get product downloadable file description.'."\r\n\r\n".$this->mysqli->error);	
									
									/* store result */
									$stmt_product_downloadable_files_description->store_result();																											
									
									if ($stmt_product_downloadable_files_description->num_rows) {	
										/* bind result variables */
										$stmt_product_downloadable_files_description->bind_result($language_code, $name);	
									
										while ($stmt_product_downloadable_files_description->fetch()) {
											// add downlodable video
											if (!$stmt_add_product_downloadable_files_description->bind_param("iss", $id_orders_item_product_downloadable_files,$language_code,$name)) throw new Exception('An error occured while trying to bind params to add product downloadable video description statement.'."\r\n\r\n".$this->mysqli->error);
											
											/* Execute the statement */
											if (!$stmt_add_product_downloadable_files_description->execute()) throw new Exception('An error occured while trying to add product downloadable video description.'."\r\n\r\n".$this->mysqli->error);	
										}
									}
								}
							}					
						}															
					}
				}										
			}
			
			$stmt_add_item->close();
			$stmt_add_product->close();
			$stmt_add_product_description->close();
			$stmt_add_product_tax->close();
			$stmt_add_discount_item_product->close();
			$stmt_add_product_downloadable_videos->close();
			$stmt_add_product_downloadable_videos_description->close();			
			$stmt_add_product_downloadable_files->close();
			$stmt_add_product_downloadable_files_description->close();
			$stmt_product_downloadable_videos->close();
			$stmt_product_downloadable_videos_description->close();
			$stmt_product_downloadable_files->close();			
			$stmt_product_downloadable_files_description->close();
			$stmt_product->close();
			$stmt_product_description->close();
			$stmt_product_tax->close();			
			$stmt_discount_item_product->close();
			
			$stmt_add_option->close();
			$stmt_add_option_description->close();
			$stmt_add_option_tax->close();
			$stmt_add_discount_item_option->close();
			$stmt_option->close();
			$stmt_option_description->close();
			$stmt_option_tax->close();			
			$stmt_discount_item_option->close();			
		}
		$result->free();
		
		$this->load($id_orders);
		
		return $id_orders;
	}
	
	public function update_transaction_details($status=0,$trans_str)
	{
		$status=(int)$status;
		
		if (!$this->mysqli->query('UPDATE 		
		orders 
		SET
		transaction_details = "'.$this->mysqli->escape_string($trans_str).'",
		status = "'.$this->mysqli->escape_string($status).'",
		date_payment = "'.(($status==1 || $status==7)?date('Y-m-d'):'').'"
		WHERE
		id = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to update transaction details.'."\r\n\r\n".$this->mysqli->error);
		
		$this->load($id_orders);									
	}
	
	//To update inventory after a successfully transaction
	public function update_product_inventory()
	{
		/* Prepare statement */
		if (!$stmt_product_info = $this->mysqli->prepare('SELECT 
		product.out_of_stock,
		product.out_of_stock_enabled,
		IF(product_variant.id IS NOT NULL,product_variant.qty,product.qty) AS qty
		FROM
		product 
		LEFT JOIN
		product_variant
		ON
		(product.id = product_variant.id_product AND product_variant.id = ?)
		WHERE
		product.id = ?		
		AND
		product.track_inventory=1
		LIMIT 1')) throw new Exception('An error occured while trying to prepare get product track inventory info statement.'."\r\n\r\n".$this->mysqli->error);	
		
		/* Prepare statement */
		if (!$stmt_upd_product_variant = $this->mysqli->prepare('UPDATE 
		product_variant 
		SET
		qty = ?,
		active = ?
		WHERE
		id = ?')) throw new Exception('An error occured while trying to prepare update product variant qty statement.'."\r\n\r\n".$this->mysqli->error);
		
		/* Prepare statement */
		if (!$stmt_upd_product = $this->mysqli->prepare('UPDATE 
		product
		SET
		qty = ?,
		active = ?
		WHERE
		id = ?')) throw new Exception('An error occured while trying to prepare update product qty statement.'."\r\n\r\n".$this->mysqli->error);		
		
		/* Prepare statement */
		if (!$stmt_option_info = $this->mysqli->prepare('SELECT 
		out_of_stock
		FROM
		options
		WHERE
		id = ?
		AND
		track_inventory=1
		LIMIT 1')) throw new Exception('An error occured while trying to prepare get options track inventory info.'."\r\n\r\n".$this->mysqli->error);		
		
		/* Prepare statement */
		if (!$stmt_upd_option = $this->mysqli->prepare('UPDATE 
		options
		SET
		qty = IF(qty <= ?,0,qty - ?)
		WHERE
		id = ?')) throw new Exception('An error occured while trying to prepare update options qty statement.'."\r\n\r\n".$this->mysqli->error);
		
		$products = $this->get_products();				
        foreach ($products as $row_product) {
            // Update Sub_product ELSE Update Product
			if (sizeof($row_product['sub_products'])) {
                foreach ($row_product['sub_products'] as $row_sub_product) {
					// sub product qty multipled by parent product qty
					$qty = $row_product['qty']*$row_sub_product['qty'];
					
					if (!$stmt_product_info->bind_param("ii",$row_sub_product['id_product_variant'], $row_sub_product['id_product'])) throw new Exception('An error occured while trying to bind params to get product info statement.'."\r\n\r\n".$this->mysqli->error);

					/* Execute the statement */
					$stmt_product_info->execute();
					
					/* store result */
					$stmt_product_info->store_result();		
					
					if ($stmt_product_info->num_rows) {				
						/* bind result variables */
						$stmt_product_info->bind_result($out_of_stock,$out_of_stock_enabled,$qty_remaining);	
														
						$stmt_product_info->fetch();					
						
						$qty_in_stock = $qty_remaining-$qty;
						if ($qty_in_stock < 0) $qty_in_stock = 0;
						
						$active = $out_of_stock_enabled && $qty_in_stock == $out_of_stock ? 0:1;
						
						if($row_sub_product['id_product_variant']){
							if (!$stmt_upd_product_variant->bind_param("iii", $qty_in_stock, $active,$row_sub_product['id_product_variant'])) throw new Exception('An error occured while trying to bind params to update product variant qty statement.'."\r\n\r\n".$this->mysqli->error);			
							
							/* Execute the statement */
							if (!$stmt_upd_product_variant->execute()) throw new Exception('An error occured while trying to update product variant qty.'."\r\n\r\n".$this->mysqli->error);	
						}else{
							if (!$stmt_upd_product->bind_param("iii", $qty_in_stock, $active,$row_sub_product['id_product'])) throw new Exception('An error occured while trying to bind params to update product qty statement.'."\r\n\r\n".$this->mysqli->error);			
							
							/* Execute the statement */
							if (!$stmt_upd_product->execute()) throw new Exception('An error occured while trying to update product qty.'."\r\n\r\n".$this->mysqli->error);	
							
						}
					}
                }
            }else{
				// product qty
				$qty = $row_product['qty'];
				
				if (!$stmt_product_info->bind_param("ii", $row_product['id_product_variant'], $row_product['id_product'])) throw new Exception('An error occured while trying to bind params to get product info statement.'."\r\n\r\n".$this->mysqli->error);

				/* Execute the statement */
				$stmt_product_info->execute();
				
				/* store result */
				$stmt_product_info->store_result();		
				
				if ($stmt_product_info->num_rows) {																																				
					/* bind result variables */
					$stmt_product_info->bind_result($out_of_stock,$out_of_stock_enabled, $qty_remaining);	
													
					$stmt_product_info->fetch();					
					
					$qty_in_stock = $qty_remaining-$qty;
					if ($qty_in_stock < 0) $qty_in_stock = 0;
					
					$active = $out_of_stock_enabled && $qty_in_stock == $out_of_stock ? 0:1;
					
					if($row_product['id_product_variant']){
						if (!$stmt_upd_product_variant->bind_param("iii", $qty_in_stock, $active,$row_product['id_product_variant'])) throw new Exception('An error occured while trying to bind params to update product variant qty statement.'."\r\n\r\n".$this->mysqli->error);			
						
						/* Execute the statement */
						if (!$stmt_upd_product_variant->execute()) throw new Exception('An error occured while trying to update product variant qty.'."\r\n\r\n".$this->mysqli->error);	
					}else{
						if (!$stmt_upd_product->bind_param("iii",$qty_in_stock, $active,$row_product['id_product'])) throw new Exception('An error occured while trying to bind params to update product qty statement.'."\r\n\r\n".$this->mysqli->error);			
						
						/* Execute the statement */
						if (!$stmt_upd_product->execute()) throw new Exception('An error occured while trying to update product qty.'."\r\n\r\n".$this->mysqli->error);	
						
					}
				}
			}
			
            // Update Options
			if(sizeof($this->get_product_options($row_product['id']))){
                $options = $this->get_product_options($row_product['id']);
                foreach ($options as $row_product_option_group) { 
                    foreach ($row_product_option_group['options'] as $row_product_option) {
						// sub product qty multipled by parent product qty
						$qty = $row_product['qty']*$row_product_option['qty'];
						
						if (!$stmt_option_info->bind_param("i", $row_product_option['id_options'])) throw new Exception('An error occured while trying to bind params to get option info statement.'."\r\n\r\n".$this->mysqli->error);
		
						/* Execute the statement */
						$stmt_option_info->execute();
						
						/* store result */
						$stmt_option_info->store_result();		
						
						if ($stmt_option_info->num_rows) {																																				
							/* bind result variables */
							$stmt_option_info->bind_result($out_of_stock);	
															
							$stmt_option_info->fetch();					
							
							if (!$stmt_upd_option->bind_param("iii", $qty, $qty,$row_product_option['id_options'])) throw new Exception('An error occured while trying to bind params to update option qty statement.'."\r\n\r\n".$this->mysqli->error);			
							
							/* Execute the statement */
							if (!$stmt_upd_option->execute()) throw new Exception('An error occured while trying to update option qty.'."\r\n\r\n".$this->mysqli->error);	
						}	
					}
                }
            }
      	}
		
		$stmt_product_info->close();
		$stmt_upd_product_variant->close();
		$stmt_upd_product->close();
		$stmt_option_info->close();
		$stmt_upd_option->close();
											
	}
	
	public function get_products()	
	{
		$array=array();		
		
		// get products				
		if (!$result = $this->mysqli->query('SELECT
		orders_item.id,
		orders_item.qty,
		orders_item_product_description.name,
		orders_item_product_description.variant_name,
		orders_item_product.id AS id_orders_item_product,
		orders_item_product.id_product,
		orders_item_product.id_product_variant,
		orders_item_product.price,
		orders_item_product.sell_price,
		orders_item_product.subtotal,
		orders_item_product.product_type,
		orders_item_product.used,
		orders_item_product.sku,
		orders_item_product.heavy_weight,
		orders_item_product.variant_sku,
		orders_item_product.price,
		orders_item_product.sell_price,
		orders_item_product.subtotal				
		FROM
		orders_item 
		INNER JOIN
		(orders_item_product CROSS JOIN orders_item_product_description)
		ON
		(orders_item.id = orders_item_product.id_orders_item AND orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")		
		WHERE
		orders_item.id_orders = "'.$this->mysqli->escape_string($this->id).'" 
		ORDER BY 
		orders_item.id ASC')) throw new Exception('An error occured while trying to get products.'."\r\n\r\n".$this->mysqli->error);
		if ($result->num_rows) {
			// get sub products
			/* Prepare statement */
			if (!$stmt_sub_products = $this->mysqli->prepare('SELECT
			orders_item_product.id,
			orders_item_product.qty,
			orders_item_product_description.name,
			orders_item_product_description.variant_name,
			orders_item_product.id AS id_orders_item_product,
			orders_item_product.id_product,
			orders_item_product.id_product_variant,
			orders_item_product.price,
			orders_item_product.sell_price,
			orders_item_product.subtotal,
			orders_item_product.product_type,
			orders_item_product.used,
			orders_item_product.sku,
			orders_item_product.heavy_weight,
			orders_item_product.variant_sku,
			orders_item_product.price,
			orders_item_product.sell_price,
			orders_item_product.subtotal				
			FROM
			orders_item_product
			INNER JOIN 
			orders_item_product_description
			ON
			(orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = ?)		
			WHERE
			orders_item_product.id_orders_item_product = ?
			ORDER BY 
			orders_item_product.id ASC')) throw new Exception('An error occured while trying to prepare get sub products statement.'."\r\n\r\n".$this->mysqli->error);						
			
			while ($row = $result->fetch_assoc()) {
				$sub_products=array();
				switch ($row['product_type']) {
					// combo
					case 1:
					// bundle
					case 2:
						if (!$stmt_sub_products->bind_param("si", $_SESSION['customer']['language'], $row['id_orders_item_product'])) throw new Exception('An error occured while trying to bind params to get sub products statement.'."\r\n\r\n".$this->mysqli->error);
					
						/* Execute the statement */
						if (!$stmt_sub_products->execute()) throw new Exception('An error occured while trying to get sub products.'."\r\n\r\n".$this->mysqli->error);								
						/* store result */
						$stmt_sub_products->store_result();	
						
						if ($stmt_sub_products->num_rows) {
							/* bind result variables */
							$stmt_sub_products->bind_result($p_id,$p_qty,$p_name,$p_variant_name,$p_id_orders_item_product,$p_id_product,$p_id_product_variant,$p_price,$p_sell_price,$p_subtotal,$p_product_type,$p_used,$p_sku,$p_heavy_weight,$p_variant_sku,$p_price,$p_sell_price,$p_subtotal);									
							
							while ($stmt_sub_products->fetch()) {								
								$sub_products[$p_id] = array(
									'id' => $p_id,
									'id_product' => $p_id_product,
									'id_product_variant' => $p_id_product_variant,
									'qty' => $p_qty,
									'name' => $p_name.(!empty($p_variant_name) ? ' ('.$p_variant_name.')':''),
								);
							}
						}
						break;						
				}
											
				$array[$row['id']] = array(
					'id'=>$row['id'],
					'id_orders_discount'=>$row['id_orders_discount'],
					'id_orders_item_product'=>$row['id_orders_item_product'],
					'id_product'=>$row['id_product'],
					'id_product_variant'=>$row['id_product_variant'],
					'product_type' => $row['product_type'],
					'used' => $row['used'],
					'qty'=>$row['qty'],
					'name'=>$row['name'],
					'variant_name'=>$row['variant_name'],
					'sku'=>$row['sku'],
					'heavy_weight'=>$row['heavy_weight'],
					'variant_sku'=>$row['variant_sku'],
					'price'=>$row['price'],
					'sell_price'=>$row['sell_price'],
					'subtotal'=>$row['subtotal'],
					'sub_products'=>$sub_products,
				);	
			}				
		
			/* close statement */
			$stmt_sub_products->close();
		}
		$result->free();
		
		return $array;
	}
	
	public function get_product_discounts($id_orders_item_product)
	{
		$id_orders_item_product=(int)$id_orders_item_product;
		
		$array=array();
		
		if (!$result = $this->mysqli->query('SELECT
		orders_discount_description.description,
		orders_discount_item_product.amount,
		orders_discount.type,
		orders_discount.coupon,
		orders_discount.coupon_code,
		orders_discount.start_date,
		orders_discount.end_date
		FROM
		orders_discount_item_product
		INNER JOIN
		(orders_discount CROSS JOIN orders_discount_description)
		ON
		(orders_discount_item_product.id_orders_discount = orders_discount.id AND orders_discount.id = orders_discount_description.id_orders_discount AND orders_discount_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
		WHERE
		orders_discount_item_product.id_orders_item_product = "'.$this->mysqli->escape_string($id_orders_item_product).'"
		ORDER BY 
		(CASE
			WHEN orders_discount.type = 0 AND orders_discount.coupon = 0 THEN 0
			WHEN orders_discount.type = 0 AND orders_discount.coupon = 1 THEN 1
			WHEN orders_discount.type = 2 THEN 3
			WHEN (orders_discount.type = 1 OR orders_discount.type = 5) AND orders_discount.coupon = 0 THEN 4
			WHEN orders_discount.type = 1 AND orders_discount.coupon = 1 THEN 5		
		END) ASC')) throw new Exception('An error occured while trying to get product discounts.'."\r\n\r\n".$this->mysqli->error);	

		while ($row = $result->fetch_assoc()){
			$array[] = $row;				
		}		
		$result->free();
		
		return $array;
	}			
	
	public function get_product_options($id_orders_item)
	{
		$id_orders_item=(int)$id_orders_item;
				
		$array=array();
		
		if (!$result_group = $this->mysqli->query('SELECT
		orders_options_group.id_options_group,
		orders_options_group.input_type,
		orders_options_group_description.name,
		orders_options_group_description.description
		FROM
		orders_item
		INNER JOIN
		(orders_item_option CROSS JOIN orders_options_group CROSS JOIN orders_options_group_description)
		ON
		(orders_item.id = orders_item_option.id_orders_item AND orders_item.id_orders = orders_options_group.id_orders AND orders_item_option.id_options_group = orders_options_group.id_options_group AND orders_options_group.id = orders_options_group_description.id_orders_options_group AND orders_options_group_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		WHERE
		orders_item.id = "'.$this->mysqli->escape_string($id_orders_item).'"
		GROUP BY 
		orders_options_group.id_options_group
		ORDER BY 
		orders_options_group.sort_order ASC')) throw new Exception('An error occured while trying to get product option groups.'."\r\n\r\n".$this->mysqli->error);			
		
		if ($result_group->num_rows) {
			if (!$stmt_option = $this->mysqli->prepare('SELECT
			orders_item_option_description.name,
			orders_item_option_description.description,
			orders_item_option.id,
			orders_item_option.id_options,		
			orders_item_option.sku,	
			orders_item_option.qty,
			orders_item_option.sell_price,
			orders_item_option.subtotal,
			orders_item_option.textfield,
			orders_item_option.textarea,
			orders_item_option.filename,
			orders_item_option.date_start,
			orders_item_option.date_end,
			orders_item_option.datetime_start,
			orders_item_option.datetime_end,
			orders_item_option.time_start,
			orders_item_option.time_end			
			FROM
			orders_item_option
			INNER JOIN
			orders_item_option_description
			ON
			(orders_item_option.id = orders_item_option_description.id_orders_item_option AND orders_item_option_description.language_code = ?) 			
			WHERE
			orders_item_option.id_orders_item = ?
			AND
			orders_item_option.id_options_group = ?	
			ORDER BY 
			orders_item_option.id ASC')) throw new Exception('An error occured while trying to prepare product options statement.'."\r\n\r\n".$this->mysqli->error);
			
			while ($row_group = $result_group->fetch_assoc()) {
				$array[$row_group['id_options_group']] = array(
					'name'=>$row_group['name'],
					'description'=>$row_group['description'],	
					'input_type'=>$row_group['input_type'],				
				);				
				
				if (!$stmt_option->bind_param("sii", $_SESSION['customer']['language'], $id_orders_item, $row_group['id_options_group'])) throw new Exception('An error occured while trying to bind params to product options statement.'."\r\n\r\n".$this->mysqli->error);	
				
				/* Execute the statement */
				if (!$stmt_option->execute()) throw new Exception('An error occured while trying to get product options.'."\r\n\r\n".$this->mysqli->error);	
				
				/* store result */
				$stmt_option->store_result();																											
							
				/* bind result variables */
				$stmt_option->bind_result($name, $description, $id_orders_item_option, $id_options, $sku, $qty, $sell_price, $subtotal, $textfield, 
				$textarea, $filename, $date_start, $date_end, $datetime_start, $datetime_end, $time_start, $time_end);														
				
				// fetch
				while ($stmt_option->fetch()) {					
					$array[$row_group['id_options_group']]['options'][$id_orders_item_option] = array(
						'name'=>$name,
						'description'=>$description,
						'id'=>$id_orders_item_option,
						'id_options'=>$id_options,
						'sku'=>$sku,
						'qty'=>$qty,
						'sell_price'=>$sell_price,
						'subtotal'=>$subtotal,
						'textfield'=>$textfield,
						'textarea'=>$textarea,
						'filename'=>$filename,
						'date_start'=>$date_start,
						'date_end'=>$date_end,
						'datetime_start'=>$datetime_start,
						'datetime_end'=>$datetime_end,
						'time_start'=>$time_start,
						'time_end'=>$time_end,
					);	
				}
			}
			
			/* close statement */
			$stmt_option->close();
		}	
		
		$result_group->free();
		
		return $array;	
	}
	
	public function get_product_option_discounts($id_orders_item_option)
	{
		$id_orders_item_option=(int)$id_orders_item_option;
		
		$array=array();
		
		if (!$result = $this->mysqli->query('SELECT
		orders_discount_description.description,
		orders_discount_item_option.amount,
		orders_discount.type,
		orders_discount.start_date,
		orders_discount.end_date
		FROM
		orders_discount_item_option
		INNER JOIN
		(orders_discount CROSS JOIN orders_discount_description)
		ON
		(orders_discount_item_option.id_orders_discount = orders_discount.id AND orders_discount.id = orders_discount_description.id_orders_discount AND orders_discount_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
		WHERE
		orders_discount_item_option.id_orders_item_option = "'.$this->mysqli->escape_string($id_orders_item_option).'"
		ORDER BY 
		(CASE
			WHEN (orders_discount.type = 1 OR orders_discount.type = 5) AND orders_discount.coupon = 0 THEN 4
			WHEN orders_discount.type = 1 AND orders_discount.coupon = 1 THEN 5		
		END) ASC')) throw new Exception('An error occured while trying to get product option discounts.'."\r\n\r\n".$this->mysqli->error);
		
		while ($row = $result->fetch_assoc()){
			$array[] = $row;				
		}		
		$result->free();
		
		return $array;
	}		
	
	public function get_taxes()
	{
		// get applicable taxe
		$id_tax_rule = $this->id_tax_rule;
		$arr_taxes = array();
		
		if ($id_tax_rule) {
			if (!$result = $this->mysqli->query('SELECT
			orders_tax.id,
			orders_tax.rate,
			orders_tax.stacked,
			orders_tax_description.name,
			IFNULL((SELECT
				SUM(orders_item_product_tax.amount)
				FROM
				orders_item_product_tax
				INNER JOIN
				orders_item_product	
				ON
				(orders_item_product_tax.id_orders_item_product = orders_item_product.id)
				INNER JOIN
				orders_item
				ON
				(orders_item_product.id_orders_item = orders_item.id)
				WHERE
				orders_item.id_orders = orders_tax.id_orders
				AND
				orders_item_product_tax.id_orders_tax = orders_tax.id),0)+IFNULL((SELECT
				SUM(orders_item_product_tax.amount)
				FROM
				orders_item_product_tax
				INNER JOIN
				orders_item_product	
				ON
				(orders_item_product_tax.id_orders_item_product = orders_item_product.id)
				INNER JOIN
				(orders_item_product AS cip CROSS JOIN orders_item AS ci)
				ON
				(orders_item_product.id_orders_item_product = cip.id AND cip.id_orders_item = ci.id)
				WHERE
				ci.id_orders = orders_tax.id_orders
				AND
				orders_item_product_tax.id_orders_tax = orders_tax.id),0)+IFNULL((SELECT
				SUM(orders_item_option_tax.amount)
				FROM
				orders_item_option_tax
				INNER JOIN
				orders_item_option	
				ON
				(orders_item_option_tax.id_orders_item_option = orders_item_option.id)
				INNER JOIN
				orders_item	
				ON
				(orders_item_option.id_orders_item = orders_item.id)
				WHERE
				orders_item.id_orders = orders_tax.id_orders
				AND
				orders_item_option_tax.id_orders_tax = orders_tax.id),0)+IFNULL((SELECT
				SUM(orders_shipping_tax.amount)
				FROM
				orders_shipping_tax
				WHERE
				orders_shipping_tax.id_orders = orders_tax.id_orders
				AND
				orders_shipping_tax.id_orders_tax = orders_tax.id),0) AS total_taxes
			FROM 
			orders_tax
			INNER JOIN
			(orders_tax_description)
			ON
			(orders_tax.id = orders_tax_description.id_orders_tax AND orders_tax_description.language_code = "'.$_SESSION['customer']['language'].'")
			WHERE
			orders_tax.id_orders =  "'.$this->mysqli->escape_string($this->id).'"
			ORDER BY 
			orders_tax.sort_order ASC')) throw new Exception('An error occured while trying to get taxes.'."\r\n\r\n".$this->mysqli->error);		
			if ($result->num_rows) {
				while($row = $result->fetch_assoc()){
					$arr_taxes[$row['name']]['name'] = $row['name'];
					$arr_taxes[$row['name']]['rate'] = $row['rate'];
					$arr_taxes[$row['name']]['total_taxes'] = nf_currency($row['total_taxes'],2);
				}								
			}	
			$result->free();		
		}	
		
		
		return $arr_taxes;
	}			
}
?>