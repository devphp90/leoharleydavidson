<?php
class SC_Cart
{
	public $mysqli='';
	public $messages=array();

	
	// constructor
	public function __construct($mysqli) {
		if (!$mysqli instanceof MySQLi) throw new Exception('Invalid mysqli object');
		$this->mysqli=$mysqli;
		
		// initialize cart
		$this->init();
	}	
	
	public function init()
	{	
		global $cart_expiration_time;
		
		// update the current cart under the current session
		$expiry_datetime = date('Y-m-d H:i:s',strtotime('+'.$cart_expiration_time.' sec',time()));
				
		if (!$this->mysqli->query('UPDATE
		cart
		SET
		date_expired = "'.$this->mysqli->escape_string($expiry_datetime).'"
		WHERE
		session_id = "'.$this->mysqli->escape_string(session_id()).'"
		LIMIT 1')) throw new Exception('An error occured while trying to create cart.'."\r\n\r\n".$this->mysqli->error);	
		
		// check if we have an existing cart under the current session		
		if ($result = $this->mysqli->query('SELECT 
		*,
		billing_state.name AS billing_state,
		billing_country.name AS billing_country,
		shipping_state.name AS shipping_state,
		shipping_country.name AS shipping_country,
		local_pickup_state.name AS local_pickup_state,
		local_pickup_country.name AS local_pickup_country
		FROM
		cart
		LEFT JOIN 
		state_description AS billing_state
		ON
		(cart.billing_state_code = billing_state.state_code AND billing_state.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		LEFT JOIN 
		country_description AS billing_country
		ON
		(cart.billing_country_code = billing_country.country_code AND billing_country.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		LEFT JOIN 
		state_description AS shipping_state
		ON
		(cart.shipping_state_code = shipping_state.state_code AND shipping_state.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		LEFT JOIN
		country_description AS shipping_country
		ON
		(cart.shipping_country_code = shipping_country.country_code AND shipping_country.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		
		LEFT JOIN 
		state_description AS local_pickup_state
		ON
		(cart.local_pickup_state_code = local_pickup_state.state_code AND local_pickup_state.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		LEFT JOIN 
		country_description AS local_pickup_country
		ON
		(cart.local_pickup_country_code = local_pickup_country.country_code AND local_pickup_country.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		
		
		WHERE
		session_id = "'.$this->mysqli->escape_string(session_id()).'"
		LIMIT 1')){
			// if we do, populate our object with properties and values from the cart
			if ($result->num_rows) {
				$row = $result->fetch_assoc();
				
				foreach ($row as $key => $value) {
					$this->$key = $value;
				}
			} 
		}					
		
		// check customer type if taxable
		if (!$result = $this->mysqli->query('SELECT
		IF(customer_type.id IS NOT NULL,customer_type.taxable,1) AS taxable,
		customer_type.apply_on_rebate
		FROM 
		cart
		LEFT JOIN
		customer_type
		ON
		(cart.id_customer_type = customer_type.id)
		WHERE
		cart.id = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to check if customer type is taxable.'."\r\n\r\n".$this->mysqli->error);
		$row = $result->fetch_assoc();
		$result->free();
				
		$this->taxable = $row['taxable'];
		$this->apply_on_rebate = $row['apply_on_rebate'] ? 1:0;
	}
	
	public function new_cart()
	{	
		global $config_site;
					
		if (!$this->id) {
			$now = time();
			$current_datetime = date('Y-m-d H:i:s');
			
			// create cart	
			/* Prepare the statement */		
			if (!$this->mysqli->query('INSERT INTO
			cart
			SET
			session_id = "'.$this->mysqli->escape_string(session_id()).'",
			language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'",
			id_customer_type = "'.$this->mysqli->escape_string($_SESSION['customer']['id_customer_type']).'",
			id_customer = "'.$this->mysqli->escape_string($_SESSION['customer']['id']).'",
			date_created = "'.$this->mysqli->escape_string($current_datetime).'",
			date_modified = "'.$this->mysqli->escape_string($current_datetime).'"')) throw new Exception('An error occured while trying to create cart.'."\r\n\r\n".$this->mysqli->error);	
						
			// get cart id
			$this->id = $this->mysqli->insert_id;
			$this->id_customer = $_SESSION['customer']['id'];
			
			// if we logged in
			if ($this->id_customer) {
				// get default billing
				if (!$result = $this->mysqli->query('SELECT 
				*
				FROM
				customer_address 
				WHERE
				id_customer = "'.$this->mysqli->escape_string($this->id_customer).'" 
				AND
				default_billing = 1
				LIMIT 1')) throw new Exception('An error occured while trying to get default billing address.'."\r\n\r\n".$this->mysqli->error);	
				if ($result->num_rows) {
					$row = $result->fetch_assoc();
					
					if (!$this->mysqli->query('UPDATE 
					cart
					SET
					billing_id = "'.$this->mysqli->escape_string($row['id']).'",
					billing_firstname = "'.$this->mysqli->escape_string($row['firstname']).'",
					billing_lastname = "'.$this->mysqli->escape_string($row['lastname']).'",
					billing_company = "'.$this->mysqli->escape_string($row['company']).'",
					billing_address = "'.$this->mysqli->escape_string($row['address']).'",
					billing_city = "'.$this->mysqli->escape_string($row['city']).'",
					billing_country_code = "'.$this->mysqli->escape_string($row['country_code']).'",
					billing_state_code = "'.$this->mysqli->escape_string($row['state_code']).'",
					billing_zip = "'.$this->mysqli->escape_string($row['zip']).'",
					billing_telephone = "'.$this->mysqli->escape_string($row['telephone']).'",
					billing_fax = "'.$this->mysqli->escape_string($row['fax']).'"
					WHERE
					id = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to update billing info.'."\r\n\r\n".$this->mysqli->error);														
				}
				$result->free();
				
				// get default shipping
				if (!$result = $this->mysqli->query('SELECT 
				*
				FROM
				customer_address 
				WHERE
				id_customer = "'.$this->mysqli->escape_string($this->id_customer).'" 
				AND
				default_shipping = 1
				LIMIT 1')) throw new Exception('An error occured while trying to get default shipping address.'."\r\n\r\n".$this->mysqli->error);	
				
				if ($result->num_rows) {
					$row = $result->fetch_assoc();
					
					if(!$config_site['enable_shipping'] and $config_site['enable_local_pickup']){
						//Find the tax rule
						$id_tax_rule = $this->get_id_tax_rule($config_site['company_country_code'],$config_site['company_state_code']);
						$local_pickup = 1;
					}else{
						//Find the tax rule
						$id_tax_rule = $this->get_id_tax_rule($row['country_code'],$row['state_code'],$row['zip']);	
						$local_pickup = 0;
					}
					
					
					/* Prepare the statement */		
					if (!$this->mysqli->query('UPDATE 
					cart
					SET
					shipping_id = "'.$this->mysqli->escape_string($row['id']).'",
					shipping_firstname = "'.$this->mysqli->escape_string($row['firstname']).'",
					shipping_lastname = "'.$this->mysqli->escape_string($row['lastname']).'",
					shipping_company = "'.$this->mysqli->escape_string($row['company']).'",
					shipping_address = "'.$this->mysqli->escape_string($row['address']).'",
					shipping_city = "'.$this->mysqli->escape_string($row['city']).'",
					shipping_country_code = "'.$this->mysqli->escape_string($row['country_code']).'",
					shipping_state_code = "'.$this->mysqli->escape_string($row['state_code']).'",
					shipping_zip = "'.$this->mysqli->escape_string($row['zip']).'",
					shipping_telephone = "'.$this->mysqli->escape_string($row['telephone']).'",
					shipping_fax = "'.$this->mysqli->escape_string($row['fax']).'",
					id_tax_rule = "'.$this->mysqli->escape_string($id_tax_rule).'",
					local_pickup = "'.$this->mysqli->escape_string($local_pickup).'"
					WHERE
					id = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to update shipping info.'."\r\n\r\n".$this->mysqli->error);														
				}	
				$result->free();	
			}							
		}
		
		$this->init();		
	}
	
	public function empty_cart()
	{
		// get files to delete
		if (!$result_tmp_files = $this->mysqli->query('SELECT 
		cart_item_option.filename_tmp
		FROM
		cart_item
		INNER JOIN
		cart_item_option
		ON
		(cart_item.id = cart_item_option.id_cart_item)
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'" 
		AND
		cart_item_option.filename_tmp != ""')) throw new Exception('An error occured while trying to get list of tmp uploaded files in cart.'."\r\n\r\n".$this->mysqli->error);	
		
		if ($result_tmp_files->num_rows) {
			$tmp_uploads_dir = realpath(dirname(__FILE__).'/../../tmp_uploads/').'/';
			
			while ($row_tmp_file = $result_tmp_files->fetch_assoc()) {
				if (is_file($tmp_uploads_dir.$row_tmp_file['filename_tmp'])) unlink($tmp_uploads_dir.$row_tmp_file['filename_tmp']);
			}
		}		
		
		$result_tmp_files->free();
				
		if (!$this->mysqli->query('DELETE FROM
		cart,
		cart_discount,
		cart_discount_item_product,
		cart_discount_item_option,
		cart_item,
		cart_item_option,
		cart_item_option_tax,
		cart_item_product,
		cart_item_product_tax,
		sp,
		spt,
		cart_gift_certificate,
		cart_shipping_tax		
		USING		
		cart
		
		LEFT JOIN
		cart_discount
		ON
		(cart.id = cart_discount.id_cart)
		
		LEFT JOIN
		cart_discount_item_product
		ON
		(cart_discount.id = cart_discount_item_product.id_cart_discount)			
		
		LEFT JOIN
		cart_discount_item_option
		ON
		(cart_discount.id = cart_discount_item_option.id_cart_discount)			
				
		LEFT JOIN
		cart_item
		ON
		(cart.id = cart_item.id_cart)
		
		LEFT JOIN
		cart_item_option
		ON
		(cart_item.id = cart_item_option.id_cart_item)
		
		LEFT JOIN
		cart_item_option_tax
		ON
		(cart_item_option.id = cart_item_option_tax.id_cart_item_option)
		
		LEFT JOIN
		cart_item_product 
		ON
		(cart_item.id = cart_item_product.id_cart_item)
		
		LEFT JOIN
		cart_item_product_tax
		ON
		(cart_item_product.id = cart_item_product_tax.id_cart_item_product)
		
		LEFT JOIN 
		cart_item_product AS sp
		ON
		(cart_item_product.id = sp.id_cart_item_product) 
		
		LEFT JOIN
		cart_item_product_tax AS spt
		ON
		(sp.id = spt.id_cart_item_product)
		
		LEFT JOIN
		cart_gift_certificate
		ON
		(cart.id = cart_gift_certificate.id_cart)
				
		LEFT JOIN
		cart_shipping_tax
		ON
		(cart.id = cart_shipping_tax.id_cart)
				
		WHERE
		cart.id = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to empty cart.'."\r\n\r\n".$this->mysqli->error);	
	}
	
	// this function is only used in the cart index.php page.
	// in other steps, we should not have to update product prices since its the price they have accepted before checkout
	public function refresh_cart()
	{
		$this->calculate_subtotal();

		$this->init();				
	}
	
	public function add_product($id_product,$id_product_variant=0,$qty=1,$id_product_related=0,$products=array(),$skip_min_qty_check=0)
	{
		// if cart doesn't exist, create cart
		if (!$this->id) $this->new_cart();
		
		$id_product=(int)$id_product;
		$id_product_variant=(int)$id_product_variant;
		$qty=(int)$qty;
		$qty=$qty?$qty:1;
		$id_product_related=(int)$id_product_related;		
		
		if ($row_product = $this->get_product_info($id_product,$id_product_variant)) {	
			// check min qty
			if (!$skip_min_qty_check && !$this->min_qty_met($id_product,$qty)) return false;
			
			// check product type
			switch ($row_product['product_type']) {
				// single
				case 0:		
					// if product is available, proceed
					if ($this->check_product_availability($id_product,$id_product_variant,$qty)) {			
						// check if product is already in cart, if yes, update qty instead
						if (!$result_in_cart = $this->mysqli->query('SELECT 
						cart_item.id
						FROM
						cart_item
						INNER JOIN
						cart_item_product
						ON
						(cart_item.id = cart_item_product.id_cart_item)
						WHERE
						cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
						AND
						cart_item.id_cart_discount = 0
						AND
						cart_item_product.id_product = "'.$this->mysqli->escape_string($id_product).'" 
						AND
						cart_item_product.id_product_variant = "'.$this->mysqli->escape_string($id_product_variant).'" 
						LIMIT 1
						')) throw new Exception('An error occured while trying to check if product is in cart.'."\r\n\r\n".$this->mysqli->error);
							
						if ($result_in_cart->num_rows) {
							$row_in_cart = $result_in_cart->fetch_assoc();
							
							$id_cart_item = $row_in_cart['id'];
							
							$result_in_cart->free();
							
							// check option availability
							if (!$result = $this->mysqli->query('SELECT
							cart_item_option.id_options,
							cart_item_option.qty,
							option_qty_in_stock(cart_item_option.id_options) AS qty_in_stock,
							CONCAT(options_group_description.name,": ",options_description.name) AS name
							FROM
							cart_item_option
							
							INNER JOIN
							(options_description CROSS JOIN options_group_description)
							ON
							(cart_item_option.id_options = options_description.id_options AND options_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'" AND cart_item_option.id_options_group = options_group_description.id_options_group AND options_group_description.language_code = options_description.language_code)							
							WHERE
							cart_item_option.id_cart_item = "'.$this->mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to get total qty in cart.'."\r\n\r\n".$this->mysqli->error);	
							
							while ($row = $result->fetch_assoc()) {
								if ($qty*$row['qty'] > $row['qty_in_stock']) $this->messages[] = $row['name'].' '. language('product','ALERT_NOT_ENOUGH_IN_STOCK');
							}		
							$result->free();		
							
							if (sizeof($this->messages)) return false;		
							
							// update qty
							if (!$this->mysqli->query('UPDATE 
							cart_item
							INNER JOIN
							cart_item_product
							ON
							(cart_item.id = cart_item_product.id_cart_item)
							SET 
							cart_item.qty = cart_item.qty+'.$this->mysqli->escape_string($qty).',
							cart_item_product.qty = cart_item.qty,
							cart_item_product.subtotal = (cart_item.qty*cart_item_product.sell_price)
							WHERE
							cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to update item qty.'."\r\n\r\n".$this->mysqli->error);										
						} else {
							$subtotal = $qty*$row_product['sell_price'];
									
							// add item
							/* Prepare the statement */		
							if (!$this->mysqli->query('INSERT INTO
							cart_item
							SET
							id_cart = "'.$this->mysqli->escape_string($this->id).'",
							qty = "'.$this->mysqli->escape_string($qty).'"')) throw new Exception('An error occured while trying to add item.'."\r\n\r\n".$this->mysqli->error);	
							
							// get item id
							$id_cart_item = $this->mysqli->insert_id;								
							
							// add product
							/* Prepare the statement */		
							if (!$this->mysqli->query('INSERT INTO
							cart_item_product
							SET
							id_cart_item = "'.$this->mysqli->escape_string($id_cart_item).'",
							id_product = "'.$this->mysqli->escape_string($id_product).'",
							id_product_variant = "'.$this->mysqli->escape_string($id_product_variant).'",
							id_product_related = "'.$this->mysqli->escape_string($id_product_related).'",				
							qty = "'.$this->mysqli->escape_string($qty).'",
							cost_price = "'.$this->mysqli->escape_string($row_product['cost_price']).'",
							use_shipping_price = "'.$this->mysqli->escape_string($row_product['use_shipping_price']).'",
							price = "'.$this->mysqli->escape_string($row_product['price']).'",
							sell_price = "'.$this->mysqli->escape_string($row_product['sell_price']).'",
							special_price_start_date = "'.$this->mysqli->escape_string($row_product['special_price_from_date']).'",
							special_price_end_date = "'.$this->mysqli->escape_string($row_product['special_price_to_date']).'",
							subtotal = "'.$this->mysqli->escape_string($subtotal).'"')) throw new Exception('An error occured while trying to add item product.'."\r\n\r\n".$this->mysqli->error);										
						}								
					} else {
						//$this->messages[] = $row_product['name'].($id_product_variant ? ' ('.$row_product['variant'].')':'').' is not available.';
						
						return false;	
					}
					break;
				// combo
				case 1:
					// check if exists in cart
					if (!$result_qty_in_cart = $this->mysqli->query('SELECT 
					SUM(cart_item.qty) AS qty
					FROM 
					cart_item
					INNER JOIN
					cart_item_product
					ON
					(cart_item.id = cart_item_product.id_cart_item)
					WHERE
					cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
					AND
					cart_item.id_cart_discount = 0
					AND
					cart_item_product.id_product = "'.$this->mysqli->escape_string($id_product).'"')) throw new Exception('An error occured while trying to get qty in cart.'."\r\n\r\n".$this->mysqli->error);
					
					$row_qty_in_cart = $result_qty_in_cart->fetch_assoc();
					$result_qty_in_cart->free();
					
					// check if over max qty allowed
					if ($row_product['max_qty'] > 0 && ($qty+$row_qty_in_cart['qty']) > $row_product['max_qty']) {
						$this->messages[] = $row_product['name'].' ' . language('_include/classes/SC_Cart','LABEL_MAX_PER_CUSTOMER',array(0=>$row_product['max_qty']));
						return false;
					}						
				
					// get product discount
					$discount_type = $row_product['discount_type'];
					$discount = $row_product['discount'];
					$discount_pc = 0;							
					$cost_price = 0;
					$price = 0;
					$sell_price = 0;
					$subtotal = 0;		
					
					/* Prepare the statement */
					if (!$stmt_in_stock = $this->mysqli->prepare('SELECT
					is_product_in_stock(?,?,0) AS in_stock')) throw new Exception('An error occured while trying to check product stock statement.'."\r\n\r\n".$this->mysqli->error);		

					/* Prepare the statement */		
					if (!$stmt_combo_product = $this->mysqli->prepare('SELECT 
					product_combo.id_combo_product				
					FROM
					product_combo 
					WHERE
					product_combo.id = ?')) throw new Exception('An error occured while trying to prepare get product id statement.'."\r\n\r\n".$this->mysqli->error);								
					
					/* Prepare the statement */		
					if (!$stmt_combo_product_variant = $this->mysqli->prepare('SELECT 
					product_combo.id_combo_product,
					product_combo_variant.id_product_variant
					FROM
					product_combo_variant 
					INNER JOIN 
					product_combo
					ON
					(product_combo_variant.id_product_combo = product_combo.id)
					WHERE
					product_combo_variant.id = ?')) throw new Exception('An error occured while trying to prepare get product variant id statement.'."\r\n\r\n".$this->mysqli->error);	
					
					foreach ($products as $id_product_combo => $row_sub_product) {
						if ($row_sub_product['id_product_combo_variant']) {
							if (!$stmt_combo_product_variant->bind_param("i", $row_sub_product['id_product_combo_variant'])) throw new Exception('An error occured while trying to bind params to get product variant id statement.'."\r\n\r\n".$this->mysqli->error);			
							
							/* Execute the statement */
							if (!$stmt_combo_product_variant->execute()) throw new Exception('An error occured while trying to get product variant id statement.'."\r\n\r\n".$this->mysqli->error);	
							
							/* store result */
							$stmt_combo_product_variant->store_result();													
												
							/* bind result variables */
							$stmt_combo_product_variant->bind_result($id_combo_product, $id_combo_product_variant);																											
								
							$stmt_combo_product_variant->fetch();
						} else {
							if (!$stmt_combo_product->bind_param("i", $row_sub_product['id'])) throw new Exception('An error occured while trying to bind params to get product id statement.'."\r\n\r\n".$this->mysqli->error);
							
							/* Execute the statement */
							if (!$stmt_combo_product->execute()) throw new Exception('An error occured while trying to get product id statement.'."\r\n\r\n".$this->mysqli->error);				

							/* store result */
							$stmt_combo_product->store_result();																											
									
							/* bind result variables */
							$stmt_combo_product->bind_result($id_combo_product);	
															
							$stmt_combo_product->fetch();		
														
							$id_combo_product_variant = 0;
						}												
						
						$sub_product_info = $this->get_product_info($id_combo_product, $id_combo_product_variant);	
						
						if ($sub_product_info['has_variants'] && !$id_combo_product_variant) {
							$this->messages[] = $sub_product_info['name'].' '. language('_include/classes/SC_Cart','LABEL_VARIANT_NOT_SELECTED');
						}
						
						$qty_remaining = get_qty_remaining($id_combo_product, $id_combo_product_variant);
						
						if (!$stmt_in_stock->bind_param("ii", $id_combo_product, $id_combo_product_variant)) throw new Exception('An error occured while trying to bind params to check product stock statement.'."\r\n\r\n".$this->mysqli->error);			
						
						/* Execute the statement */
						if (!$stmt_in_stock->execute()) throw new Exception('An error occured while trying to check product stock.'."\r\n\r\n".$this->mysqli->error);	
						
						/* store result */
						$stmt_in_stock->store_result();													
											
						/* bind result variables */
						$stmt_in_stock->bind_result($in_stock);																											
							
						$stmt_in_stock->fetch();						

						// check if qty available for combo
						if (!$in_stock || $qty_remaining >= 0 && $sub_product_info['track_inventory'] && $qty*$row_sub_product['qty'] > $qty_remaining) {
							$this->messages[] = $sub_product_info['name'].($id_combo_product_variant ? ' ('.$sub_product_info['variant'].')':'').' '. language('product','ALERT_NOT_ENOUGH_IN_STOCK');
						} else if (!$in_stock || $qty_remaining >= 0 && $sub_product_info['track_inventory'] && $row_sub_product['add_qty'] > ($qty_remaining - ($qty*$row_sub_product['qty']))) {
							$this->messages[] = $sub_product_info['name'].($id_combo_product_variant ? ' ('.$sub_product_info['variant'].')':'').' '. language('_include/classes/SC_Cart','LABEL_ADDITIONAL_QTY_NOT_AVAILABLE');
						}
						
						$products[$id_product_combo]['info'] = $sub_product_info; 
						
						$cost_price += $row_sub_product['qty']*$sub_product_info['cost_price'];
						$price += $row_sub_product['qty']*$sub_product_info['price'];
						$sell_price += $row_sub_product['qty']*$sub_product_info['price'];
					}
					
					/* close statement */
					$stmt_combo_product->close();
					$stmt_combo_product_variant->close();
					
					if (sizeof($this->messages)) return false;											
					
					$joins = array();
					$where = array();
					
					$i=1;
					foreach ($products as $id_product_combo => $row_sub_product) {
						$joins[] = 'INNER JOIN 	
						cart_item_product AS cip'.$i.'
						ON
						(cart_item_product.id = cip'.$i.'.id_cart_item_product)';
						
						$where[] = 'cip'.$i.'.id_product = "'.$this->mysqli->escape_string($row_sub_product['info']['id']).'" 
						AND 
						cip'.$i.'.id_product_variant = "'.$this->mysqli->escape_string($row_sub_product['info']['id_product_variant']).'"
						AND
						cip'.$i.'.id_product_combo_product = "'.$this->mysqli->escape_string($id_product_combo).'"';
						
						++$i;
					}
					
					$sql = 'SELECT 
					cart_item.id
					FROM 
					cart_item
					INNER JOIN 
					cart_item_product
					ON
					(cart_item.id = cart_item_product.id_cart_item)	'.
					(sizeof($joins) ? implode(' ',$joins):'').'
					WHERE
					cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'" '.
					(sizeof($where) ? ' AND '.implode(' AND ',$where):'').'	
					LIMIT 1';			
					
					// check if product is already in cart, if yes, update qty instead
					if ($result_in_cart = $this->mysqli->query($sql)) {																
						if ($result_in_cart->num_rows) {
							$row_in_cart = $result_in_cart->fetch_assoc();
							
							$id_cart_item = $row_in_cart['id'];
							
							$result_in_cart->free();
							
							// update qty
							if (!$this->mysqli->query('UPDATE 
							cart_item
							INNER JOIN
							cart_item_product
							ON
							(cart_item.id = cart_item_product.id_cart_item)
							SET 
							cart_item.qty = cart_item.qty+'.$this->mysqli->escape_string($qty).',
							cart_item_product.qty = cart_item.qty,
							cart_item_product.subtotal = (cart_item.qty*cart_item_product.sell_price)
							WHERE
							cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to update item qty.'."\r\n\r\n".$this->mysqli->error);		
						} else {
							// calculate discount
							switch ($discount_type) {
								// fixed
								case 0:								
									$discount_pc = $discount/$price;
									$sell_price = $price-$discount;
									break;
								// percent
								case 1:
									$discount_pc = $discount/100;
									$sell_price = $price-round($price*$discount_pc,2);
									break;
							}
							
							$subtotal = $qty*$sell_price;
							
							// add item
							/* Prepare the statement */		
							if (!$this->mysqli->query('INSERT INTO
							cart_item
							SET
							id_cart = "'.$this->mysqli->escape_string($this->id).'",
							qty = "'.$this->mysqli->escape_string($qty).'"')) throw new Exception('An error occured while trying to add item.'."\r\n\r\n".$this->mysqli->error);	
							
							// get item id
							$id_cart_item = $this->mysqli->insert_id;								
							
							// add product
							if (!$this->mysqli->query('INSERT INTO
							cart_item_product
							SET
							id_cart_item = "'.$this->mysqli->escape_string($id_cart_item).'",
							id_product = "'.$this->mysqli->escape_string($id_product).'",
							id_product_related = "'.$this->mysqli->escape_string($id_product_related).'",
							qty = "'.$this->mysqli->escape_string($qty).'",
							cost_price = "'.$this->mysqli->escape_string($row_product['cost_price']).'",
							price = "'.$this->mysqli->escape_string($row_product['price']).'",
							sell_price = "'.$this->mysqli->escape_string($row_product['sell_price']).'",
							subtotal = "'.$this->mysqli->escape_string($qty*$row_product['sell_price']).'"')) throw new Exception('An error occured while trying to add product.'."\r\n\r\n".$this->mysqli->error);
							
							$id_cart_item_product = $this->mysqli->insert_id;	
							
							// add sub product
							/* Prepare the statement */		
							if (!$stmt_product = $this->mysqli->prepare('INSERT INTO
							cart_item_product
							SET
							id_cart_item_product = ?,
							id_product = ?,
							id_product_variant = ?,
							id_product_combo_product = ?,				
							qty = ?,
							cost_price = ?,
							price = ?,
							sell_price = ?,
							subtotal = ?')) throw new Exception('An error occured while trying to prepare add sub product statement.'."\r\n\r\n".$this->mysqli->error);																				
																													
																		
							// get all sub products 
							foreach ($products as $id_product_combo => $row_sub_product) {
								$product_sell_price = $row_sub_product['info']['price'];
								//$product_sell_price = $row_sub_product['info']['sell_price'] - round($row_sub_product['info']['sell_price']*$discount_pc,4);
								$product_subtotal = $row_sub_product['qty']*$product_sell_price;
														
								if (!$stmt_product->bind_param("iiiiidddd", $id_cart_item_product, $row_sub_product['info']['id'], 
								$row_sub_product['info']['id_product_variant'], $id_product_combo, $row_sub_product['qty'], $row_sub_product['info']['cost_price'],
								$row_sub_product['info']['price'], $product_sell_price, $product_subtotal)) throw new Exception('An error occured while trying to bind params to add sub product statement.'."\r\n\r\n".$this->mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_product->execute()) throw new Exception('An error occured while trying to add sub product.'."\r\n\r\n".$this->mysqli->error);
							}						
							
							/* close statement */
							$stmt_product->close();	
						}
					}
					
					// add qty			
					// check if product is already in cart, if yes, update qty instead
					/* Prepare statement */
					if (!$result_in_cart_add_qty = $this->mysqli->prepare('SELECT 
					cart_item.id
					FROM
					cart_item
					INNER JOIN
					cart_item_product
					ON
					(cart_item.id = cart_item_product.id_cart_item)
					WHERE
					cart_item.id_cart = ?
					AND
					cart_item_product.id_product = ?
					AND
					cart_item_product.id_product_variant = ?
					LIMIT 1')) throw new Exception('An error occured while trying to prepare product in cart statement.'."\r\n\r\n".$this->mysqli->error);													
												
					// add additional sub product
					// add additional item
					/* Prepare the statement */		
					if (!$stmt_add_item = $this->mysqli->prepare('INSERT INTO
					cart_item
					SET
					id_cart = ?,
					qty = ?')) throw new Exception('An error occured while trying to prepare add additional item statement.'."\r\n\r\n".$this->mysqli->error);											
					
					// add additional item
					/* Prepare the statement */		
					if (!$stmt_add_item_product = $this->mysqli->prepare('INSERT INTO
					cart_item_product
					SET
					id_cart_item = ?,
					id_product = ?,
					id_product_variant = ?,
					qty = ?,
					cost_price = ?,
					price = ?,
					sell_price = ?,
					subtotal = ?')) throw new Exception('An error occured while trying to prepare add additional item product statement.'."\r\n\r\n".$this->mysqli->error);									
					
					foreach ($products as $id_product_combo => $row_sub_product) {
						if ($row_sub_product['add_qty'] > 0) $this->add_product($row_sub_product['info']['id'], $row_sub_product['info']['id_product_variant'], $row_sub_product['add_qty']);
					}
			
					/* close statement */
					$stmt_add_item->close();
					$stmt_add_item_product->close();						
					break;
				// bundle
				case 2:
					// check if exists in cart
					if (!$result_qty_in_cart = $this->mysqli->query('SELECT 
					SUM(cart_item.qty) AS qty
					FROM 
					cart_item
					INNER JOIN
					cart_item_product
					ON
					(cart_item.id = cart_item_product.id_cart_item)
					WHERE
					cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
					AND
					cart_item.id_cart_discount = 0
					AND
					cart_item_product.id_product = "'.$this->mysqli->escape_string($id_product).'"')) throw new Exception('An error occured while trying to get qty in cart.'."\r\n\r\n".$this->mysqli->error);
					
					$row_qty_in_cart = $result_qty_in_cart->fetch_assoc();
					$result_qty_in_cart->free();
					
					// check if over max qty allowed
					if ($row_product['max_qty'] > 0 && ($qty+$row_qty_in_cart['qty']) > $row_product['max_qty']) {
						$this->messages[] = $row_product['name'].' ' . language('_include/classes/SC_Cart','LABEL_MAX_PER_CUSTOMER',array(0=>$row_product['max_qty']));
						return false;
					}							
				
					// check product availability, different from single and combo
				
					$cost_price = 0;
					$price = 0;
					$sell_price = 0;
					$subtotal = 0;						
				
					// get required group and return false if not present
					
					if ($result = $this->mysqli->query('SELECT 
					product_bundled_product_group.id,
					product_bundled_product_group_description.name
					FROM
					product_bundled_product_group
					INNER JOIN 
					product_bundled_product_group_description
					ON
					(product_bundled_product_group.id = product_bundled_product_group_description.id_product_bundled_product_group AND product_bundled_product_group_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
					WHERE
					product_bundled_product_group.id_product = "'.$this->mysqli->escape_string($id_product).'"
					AND
					product_bundled_product_group.required = 1')) {
						while ($row = $result->fetch_assoc()) {
							if (!isset($products[$row['id']])) {
								$this->messages[] = $row['name'].' '.language('_include/classes/SC_Cart','LABEL_IS_REQUIRED');	
							}
						}
						
						if (sizeof($this->messages)) return false;
					}					
					
					/* Prepare the statement */
					if (!$stmt_in_stock = $this->mysqli->prepare('SELECT
					is_product_in_stock(?,?,0) AS in_stock')) throw new Exception('An error occured while trying to check product stock statement.'."\r\n\r\n".$this->mysqli->error);		
					
					// loop through each product and check availability
					if (sizeof($products)) {
						// get price 
						/* Prepare the statement */		
						if (!$stmt_sub_product_info = $this->mysqli->prepare('SELECT 
						(product.cost_price+IF(product_variant.id IS NOT NULL,product_variant.cost_price,0)) AS cost_price,
						calc_sell_price(product.price,product_variant.price_type,product_variant.price,0,0,0,0) AS price,
						get_bundle_product_current_price(product_bundled_product_group_product.id,"'.$this->mysqli->escape_string($this->id_customer_type).'") AS sell_price,
						product_bundled_product_group_product.id_product,
						product_bundled_product_group_product.id_product_variant,
						product_description.name,
						GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
						product.track_inventory
						FROM
						
						product_bundled_product_group_product
						INNER JOIN
						product_bundled_product_group
						ON
						(product_bundled_product_group_product.id_product_bundled_product_group = product_bundled_product_group.id)
						INNER JOIN
						(product CROSS JOIN product_description)
						ON
						(product_bundled_product_group_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
						LEFT JOIN
						(product_variant
						CROSS JOIN product_variant_option 
						CROSS JOIN product_variant_group 
						CROSS JOIN product_variant_group_option 
						CROSS JOIN product_variant_group_option_description
						CROSS JOIN product_variant_group_description)						
						ON
						(product_bundled_product_group_product.id_product_variant = product_variant.id
						AND product_variant.id = product_variant_option.id_product_variant 
						AND product_variant_option.id_product_variant_group = product_variant_group.id 
						AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
						AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
						AND product_variant_group_option_description.language_code = product_description.language_code
						AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
						AND product_variant_group_description.language_code = product_description.language_code)
						
						INNER JOIN
						product AS product_info
						ON
						(product_bundled_product_group.id_product = product_info.id)						
						
						WHERE
						product_bundled_product_group_product.id = ?
						GROUP BY 
						product_bundled_product_group_product.id')) throw new Exception('An error occured while trying to prepare get product price statement.'."\r\n\r\n".$this->mysqli->error);																																						
						
						foreach ($products as $id_product_bundled_product_group => $rows) {
							if (sizeof($rows['id'])) {
								foreach ($rows['id'] as $key => $id_product_bundled_product_group_product) {		
									if (!$stmt_sub_product_info->bind_param("i", $id_product_bundled_product_group_product)) throw new Exception('An error occured while trying to bind params to get sub product info statement.'."\r\n\r\n".$this->mysqli->error);
	
									/* Execute the statement */
									$stmt_sub_product_info->execute();
									
									/* store result */
									$stmt_sub_product_info->store_result();		
									
									if ($stmt_sub_product_info->num_rows) {																																				
										/* bind result variables */
										$stmt_sub_product_info->bind_result($sub_product_cost_price, $sub_product_price, $sub_product_sell_price, $sub_product_id_product,
										$sub_product_id_product_variant, $sub_product_name, $sub_product_variant_name, $sub_product_track_inventory);	
																		
										$stmt_sub_product_info->fetch();
										
										$sub_product_qty = $rows['qty'][$id_product_bundled_product_group_product] > 0 ? $rows['qty'][$id_product_bundled_product_group_product]:1;							
										
										$qty_remaining = get_qty_remaining($sub_product_id_product, $sub_product_id_product_variant);
						
										if (!$stmt_in_stock->bind_param("ii", $sub_product_id_product, $sub_product_id_product_variant)) throw new Exception('An error occured while trying to bind params to check product stock statement.'."\r\n\r\n".$this->mysqli->error);			
										
										/* Execute the statement */
										if (!$stmt_in_stock->execute()) throw new Exception('An error occured while trying to check product stock.'."\r\n\r\n".$this->mysqli->error);	
										
										/* store result */
										$stmt_in_stock->store_result();													
															
										/* bind result variables */
										$stmt_in_stock->bind_result($in_stock);																											
											
										$stmt_in_stock->fetch();	
										
										// check if available		
										if (!$in_stock || $qty_remaining >= 0 && $sub_product_track_inventory && $qty*$sub_product_qty > $qty_remaining) {
											$this->messages[] = $sub_product_name.($sub_product_id_product_variant ? ' ('.$sub_product_variant_name.')':'').' '. language('product','ALERT_NOT_ENOUGH_IN_STOCK');
										} else {								
											$products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product] = array(
												'id_product' => $sub_product_id_product,
												'id_product_variant' => $sub_product_id_product_variant,
												'qty' => $sub_product_qty,
												'cost_price' => $sub_product_cost_price,
												'price' => $sub_product_price,
												'sell_price' => $sub_product_sell_price,
												'subtotal' => $sub_product_qty*$sub_product_sell_price,
											);
											
											$cost_price += $sub_product_qty*$sub_product_cost_price;
											$price += $sub_product_qty*$sub_product_price;
											$sell_price += $sub_product_qty*$sub_product_sell_price;
										}
									// if don't exist remove from products
									} else {
										unset($products[$id_product_bundled_product_group]['id'][$key]);
									}
								}
							}
						}	
						
						if (sizeof($this->messages)) return false;											

						/* close statement */
						$stmt_sub_product_info->close();							
						
						$subtotal = $qty*$sell_price;							
						
						// add item
						if (!$this->mysqli->query('INSERT INTO
						cart_item
						SET
						id_cart = "'.$this->mysqli->escape_string($this->id).'",
						qty = "'.$this->mysqli->escape_string($qty).'"')) throw new Exception('An error occured while trying to add item.'."\r\n\r\n".$this->mysqli->error);							
						
						// get item id
						$id_cart_item = $this->mysqli->insert_id;								
						
						// add product
						/* Prepare the statement */		
						if (!$this->mysqli->query('INSERT INTO
						cart_item_product
						SET
						id_cart_item = "'.$this->mysqli->escape_string($id_cart_item).'",
						id_product = "'.$this->mysqli->escape_string($id_product).'",
						id_product_related = "'.$this->mysqli->escape_string($id_product_related).'",				
						qty = "'.$this->mysqli->escape_string($qty).'",
						cost_price = "'.$this->mysqli->escape_string($cost_price).'",
						price = "'.$this->mysqli->escape_string($price).'",
						sell_price = "'.$this->mysqli->escape_string($sell_price).'",
						subtotal = "'.$this->mysqli->escape_string($subtotal).'"')) throw new Exception('An error occured while trying to add product.'."\r\n\r\n".$this->mysqli->error);	
						
						// get item product id
						$id_cart_item_product = $this->mysqli->insert_id;		
						
						// add sub product
						/* Prepare the statement */		
						if (!$stmt_add_sub_product = $this->mysqli->prepare('INSERT INTO
						cart_item_product
						SET
						id_cart_item_product = ?,
						id_product = ?,
						id_product_variant = ?,
						id_product_bundled_product_group_product = ?,				
						qty = ?,
						cost_price = ?,
						price = ?,
						sell_price = ?,
						subtotal = ?')) throw new Exception('An error occured while trying to prepare add sub product statement.'."\r\n\r\n".$this->mysqli->error);																									
						
						foreach ($products as $id_product_bundled_product_group => $rows) {
							if (sizeof($rows['id'])) {
								foreach ($rows['id'] as $key => $id_product_bundled_product_group_product) {	
									$sub_product_id_product = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['id_product'];
									$sub_product_id_product_variant = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['id_product_variant'];
									$sub_product_qty = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['qty'];
									
									$sub_product_cost_price = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['cost_price'];
									$sub_product_price = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['price'];
									$sub_product_sell_price = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['sell_price'];
									$sub_product_subtotal = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['subtotal'];
																	
									if (!$stmt_add_sub_product->bind_param("iiiiidddd", $id_cart_item_product, $sub_product_id_product, $sub_product_id_product_variant, 
									$id_product_bundled_product_group_product, $sub_product_qty, $sub_product_cost_price, $sub_product_price, 
									$sub_product_sell_price, $sub_product_subtotal)) throw new Exception('An error occured while trying to bind params to add sub product statement.'."\r\n\r\n".$this->mysqli->error);		
									
									/* Execute the statement */
									if (!$stmt_add_sub_product->execute()) throw new Exception('An error occured while trying to add sub product.'."\r\n\r\n".$this->mysqli->error);																															
								}
							}
						}		
					}
					break;
			}
			
			// apply discount
			$this->apply_product_discount($id_cart_item);					
			
			return $id_cart_item;
		}
		
		return false;
	}
	
	public function min_qty_met($id_product, $qty, $id_cart_item=0)
	{
		// check if product has min qty
		if (!$result = $this->mysqli->query('SELECT min_qty FROM product WHERE id = "'.$id_product.'" LIMIT 1')) throw new Exception('An error cccured while trying to get min qty.');
		$row = $result->fetch_assoc();
		
		$min_qty = $row['min_qty'];
					
		// if min qty is required
		if ($row['min_qty']) {
			// get total qty in cart 
			if (!$result_qty_in_cart = $this->mysqli->query('SELECT 
			COUNT(cart_item_product.id) AS total,
			SUM(IF(cart.id IS NOT NULL,cart_item_product.qty,ci.qty*cart_item_product.qty)) AS qty
			FROM
			cart_item_product
			LEFT JOIN
			(cart_item CROSS JOIN cart)
			ON
			(cart_item_product.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
			
			LEFT JOIN
			(cart_item_product AS cip CROSS JOIN cart_item AS ci CROSS JOIN cart AS c)
			ON
			(cart_item_product.id_cart_item_product = cip.id AND cip.id_cart_item = ci.id AND ci.id_cart = c.id)
			
			WHERE
			cart_item_product.id_product = "'.$this->mysqli->escape_string($id_product).'"
			AND
			(cart.id IS NOT NULL AND cart.id = "'.$this->mysqli->escape_string($this->id).'" OR c.id IS NOT NULL AND c.id = "'.$this->mysqli->escape_string($this->id).'")')) throw new Exception('An error occured while trying to get total qty in cart.'."\r\n\r\n".$this->mysqli->error);		
			$row = $result_qty_in_cart->fetch_assoc();
			$total_in_cart = $row['total'];
			$current_qty_in_cart = $row['qty'];
			
			if ($id_cart_item) {
				// get qty in cart
				if (!$result = $this->mysqli->query('SELECT qty FROM cart_item_product WHERE id = "'.$id_cart_item.'" LIMIT 1')) throw new Exception('An error occured while trying to get qty in cart.');
				$row = $result->fetch_assoc();
				$current_qty_in_cart -= $row['qty'];				
			}
			
//			if ($qty == 0 && $total_in_cart <= 1) return true;
						
			if ($qty+$current_qty_in_cart < $min_qty) {
				if (!in_array(language('product','ALERT_MIN_QTY_NOT_MET',array('qty'=>$min_qty)),$this->messages)) $this->messages[] = language('product','ALERT_MIN_QTY_NOT_MET',array('qty'=>$min_qty));
				
				return false;	
			}
		}					
		
		return true;
	}
	
	public function get_product_info($id_product,$id_product_variant=0)
	{
		$id_product=(int)$id_product;
		$id_product_variant=(int)$id_product_variant;
		
		if (!$id_product_variant) {
			$sql = 'SELECT
			product.*,
			product_description.name,
			0 AS id_product_variant
			FROM
			product	
			INNER JOIN
			product_description
			ON
			(product.id = product_description.id_product AND product_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
			WHERE
			product.id = "'.$this->mysqli->escape_string($id_product).'"
			LIMIT 1';
		} else {
			$sql = 'SELECT
			product.*,
			product_description.name,
			product_variant.id AS id_product_variant,
			product_variant.sku AS variant_sku,
			GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
			(product.cost_price+product_variant.cost_price) AS cost_price,
			calc_sell_price(product.price,product_variant.price_type,product_variant.price,0,0,0,0) AS price,
			calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,0,0,0,0) AS sell_price
			FROM
			product		
			INNER JOIN
			product_description
			ON
			(product.id = product_description.id_product AND product_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")			
			INNER JOIN
			product_variant
			ON
			(product.id = product_variant.id_product)		
			LEFT JOIN 
			(product_variant_option CROSS JOIN product_variant_group CROSS JOIN product_variant_group_option CROSS JOIN product_variant_group_option_description CROSS JOIN product_variant_group_description)
			ON 
			(
				product_variant.id = product_variant_option.id_product_variant 
				AND product_variant_option.id_product_variant_group = product_variant_group.id 
				AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
				AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
				AND product_variant_group_option_description.language_code = product_description.language_code
				AND	product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
				AND product_variant_group_description.language_code = product_variant_group_option_description.language_code
			)
			
			WHERE
			product_variant.id_product = "'.$this->mysqli->escape_string($id_product).'"
			AND
			product_variant.id = "'.$this->mysqli->escape_string($id_product_variant).'"
			GROUP BY 
			product_variant.id
			LIMIT 1';
		}
		
		if ($result = $this->mysqli->query($sql)) {
			if ($result->num_rows) {
				$row = $result->fetch_assoc();
				
				$result->free();
				return $row;	
			} else {
				return false;
			}
		} else {
			throw new Exception('An error occured while trying to get product info.'."\r\n\r\n".$this->mysqli->error);	
		}
	}
	

	// return product qty in cart regardless of variant and not part of a bg discount
	public function get_product_qty($id_product)
	{
		$id_product=(int)$id_product;
		
		if ($result = $this->mysqli->query('SELECT
		SUM(cart_item_product.qty) AS qty
		FROM
		cart_item
		INNER JOIN
		cart_item_product
		ON
		(cart_item.id = cart_item_product.id_cart_item)
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
		AND
		cart_item.id_cart_discount = 0
		AND
		cart_item_product.id_product = "'.$this->mysqli->escape_string($id_product).'"')) {
			$row = $result->fetch_assoc();
			$result->free();
			
			return $row['qty'];
		}		
	}
	
	public function check_product_availability($id_product,$id_product_variant=0,$qty=1)
	{
		$id_product=(int)$id_product;
		$id_product_variant=(int)$id_product_variant;
		$qty=(int)$qty;
		$qty=$qty?$qty:1;
		
		// get product info
		if (!$result = $this->mysqli->query('SELECT		
		product_description.name,
		GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
		product.track_inventory,
		IF(rebate_coupon.id IS NOT NULL AND rebate_coupon.max_qty_allowed != 0 AND (product.max_qty = 0 OR product.max_qty != 0 AND rebate_coupon.max_qty_allowed < product.max_qty),rebate_coupon.max_qty_allowed,product.max_qty) AS max_qty,
		is_product_in_stock(product.id,product_variant.id,0) AS in_stock,
		qty_in_stock(product.id,product_variant.id) AS qty_in_stock
		FROM
		product 
		INNER JOIN 
		product_description
		ON
		(product.id AND product.id = product_description.id_product AND product_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
		
		LEFT JOIN
		(product_variant
		CROSS JOIN
		product_variant_option 
		CROSS JOIN 
		product_variant_group 
		CROSS JOIN 
		product_variant_group_option 
		CROSS JOIN 
		product_variant_group_option_description
		CROSS JOIN 
		product_variant_group_description)
		ON 
		(	
			product.id = product_variant.id_product
			AND product_variant.id = "'.$this->mysqli->escape_string($id_product_variant).'"		
			AND product_variant.id = product_variant_option.id_product_variant 
			AND product_variant_option.id_product_variant_group = product_variant_group.id 
			AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
			AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
			AND product_variant_group_option_description.language_code = product_description.language_code
			AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
			AND product_variant_group_description.language_code = product_description.language_code
		)		
		
		LEFT JOIN
		rebate_coupon 
		ON
		(product.id_rebate_coupon = rebate_coupon.id) 
		
		WHERE
		product.id = "'.$this->mysqli->escape_string($id_product).'"
		
		GROUP BY 
		product.id
		LIMIT 1')) throw new Exception('An error occured while trying to get product info.'."\r\n\r\n".$this->mysqli->error);	

		$row = $result->fetch_assoc();
		$result->free();
		$name = $row['name'];
		$variant = $row['variant'];
		$track_inventory = $row['track_inventory'];
		$max_qty = $row['max_qty'];						
		$in_stock = $row['in_stock'];
		$qty_in_stock = $row['qty_in_stock'];
		
		if ($max_qty > 0) {
			// get qty in cart
			if (!$result = $this->mysqli->query('SELECT
			SUM(cart_item_product.qty) AS qty
			FROM 
			cart_item_product
			INNER JOIN
			(cart_item CROSS JOIN cart)
			ON
			(cart_item_product.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
			
			WHERE
			cart_item_product.id_product = "'.$this->mysqli->escape_string($id_product).'"
			AND
			cart_item_product.id_product_variant = "'.$this->mysqli->escape_string($id_product_variant).'"
			AND
			cart.id = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to get current qty in cart.'."\r\n\r\n".$this->mysqli->error);	
			$row = $result->fetch_assoc();				
			
			if ($qty+$row['qty'] > $max_qty) {
				$this->messages[] = $name.($variant ? ' ('.$variant.')':'').' ' . language('_include/classes/SC_Cart','LABEL_MAX_PER_CUSTOMER',array(0=>$max_qty));
				return false;
			}
		}
		
		if ($track_inventory && $qty > $qty_in_stock) {
			$this->messages[] = $name.' '.language('product','ALERT_NOT_ENOUGH_IN_STOCK');
			return false;			
		}
		
		return $in_stock;	
	}
	
	public function apply_product_discount($id_cart_item)
	{
		$id_cart_item=(int)$id_cart_item;
		$current_datetime = date('Y-m-d H:i:s');
		
		if (!$this->id_customer_type || $this->id_customer_type && $this->apply_on_rebate) {	
			// get product info
			if (!$result = $this->mysqli->query('SELECT 
			cart_item_product.id,
			cart_item_product.id_product,
			cart_item_product.id_product_variant,
			cart_item_product.cost_price,
			cart_item_product.price,
			cart_item_product.sell_price,
			cart_item_product.subtotal,
			cart_item_product.qty,
			product.product_type,
			cart_item.id_cart_discount,
			product.max_qty
			FROM 
			cart_item
			INNER JOIN
			(cart_item_product CROSS JOIN product)
			ON
			(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id)
			WHERE
			cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to get product info.'."\r\n\r\n".$this->mysqli->error);	
			
			$row = $result->fetch_assoc();
			$result->free();
			
			$id_cart_item_product = $row['id'];
			$id_product = $row['id_product'];
			$id_product_variant = $row['id_product_variant'];
			$cost_price = $row['cost_price'];
			$price = $row['price'];
			$sell_price = $row['sell_price'];
			$subtotal = $row['subtotal'];		
			$qty = $row['qty'];
			$product_type = $row['product_type'];
			$max_qty = $row['max_qty'];
			
			if ($product_type == 0 && !$row['id_cart_discount']) {
				$discounts = array();
				
				/* Prepare the statement */		
				// get all variants of this product that applies to the rebate/coupon in question, skip if it already has another same rebate/coupon applied 
				if (!$stmt_variant = $this->mysqli->prepare('SELECT
				cart_item.id,
				cart_item_product.id AS id_cart_item_product,
				cart_item_product.id_product,
				cart_item_product.id_product_variant,
				cart_item_product.cost_price,
				cart_item_product.price,
				cart_item_product.sell_price,
				cart_item_product.subtotal,
				cart_item_product.qty,		
				cart_discount_item_product.id AS id_cart_discount_item_product,
				get_product_discounted_price(cart_item.id, rebate_coupon.id) AS sell_price
				FROM
				cart_item
				INNER JOIN
				cart_item_product
				ON
				(cart_item.id = cart_item_product.id_cart_item)
				
				LEFT JOIN
				rebate_coupon 
				ON
				(rebate_coupon.id = ?)			
				
				LEFT JOIN
				(cart_discount_item_product CROSS JOIN cart_discount CROSS JOIN rebate_coupon AS rc)
				ON
				(cart_item_product.id = cart_discount_item_product.id_cart_item_product AND cart_discount.id = cart_discount_item_product.id_cart_discount AND cart_discount.id_rebate_coupon = rc.id AND rebate_coupon.type = rc.type AND rebate_coupon.coupon = rc.coupon)
				WHERE
				cart_item.id_cart = ?
				AND
				cart_item.id_cart_discount = 0
				AND
				cart_item_product.id_product = ?
				AND
				cart_item_product.id_product_variant != ?
				AND
				(
					(IF((SELECT 
					rebate_coupon_product.id_rebate_coupon
					FROM
					rebate_coupon_product 
					WHERE 
					rebate_coupon_product.id_product = cart_item_product.id_product
					LIMIT 1) IS NOT NULL,1,0)) = 1        				
				
					OR 
				
					(IF((SELECT 
					rebate_coupon_category.id_rebate_coupon
					FROM
					rebate_coupon_category INNER JOIN product_category
					ON (rebate_coupon_category.id_category = product_category.id_category)
					WHERE 
					rebate_coupon_category.id_rebate_coupon = rebate_coupon.id
					AND
					product_category.id_product = cart_item_product.id_product
					LIMIT 1) IS NOT NULL,1,0)) = 1				
				)
				AND
				(
					cart_discount_item_product.id IS NULL
					OR
					(cart_discount_item_product.id IS NOT NULL AND rebate_coupon.id = rc.id)
				)
				GROUP BY
				cart_item.id
				')) throw new Exception('An error occured while trying to prepare get product variants statement.'."\r\n\r\n".$this->mysqli->error);			
				
				/* Prepare the statement */
				if (!$stmt_discount = $this->mysqli->prepare('INSERT INTO
				cart_discount
				SET
				id_cart = ?,
				id_rebate_coupon = ?')) throw new Exception('An error occured while trying to prepare add discount statement.'."\r\n\r\n".$this->mysqli->error);																																											
				
				/* Prepare the statement */
				if (!$stmt_discount_item_product = $this->mysqli->prepare('INSERT INTO
				cart_discount_item_product 
				SET
				id_cart_discount = ?,
				id_cart_item_product = ?,
				amount = ?')) throw new Exception('An error occured while trying to prepare add discount item statement.'."\r\n\r\n".$this->mysqli->error);																																							
				
				/* Prepare the statement */
				if (!$stmt_discount_item_product_upd = $this->mysqli->prepare('UPDATE
				cart_discount_item_product 
				SET				
				amount = ?
				WHERE 
				id = ?')) throw new Exception('An error occured while trying to prepare update discount item statement.'."\r\n\r\n".$this->mysqli->error);				
				
				/* Prepare the statement */
				if (!$stmt_product_qty_upd = $this->mysqli->prepare('UPDATE
				cart_item
				INNER JOIN
				cart_item_product
				ON
				(cart_item.id = cart_item_product.id_cart_item)
				SET				
				cart_item.qty = ?,
				cart_item_product.qty = ?
				WHERE 
				cart_item.id = ?')) throw new Exception('An error occured while trying to prepare update item qty statement.'."\r\n\r\n".$this->mysqli->error);					
				
				// get list of applied discounts
				if (!$result_discount = $this->mysqli->query('SELECT
				cart_discount.id,
				cart_discount.id_rebate_coupon,
				cart_discount_item_product.id AS id_cart_discount_item_product,
				(CASE
					WHEN rebate_coupon.type = 0 AND rebate_coupon.coupon = 0 THEN 0
					WHEN rebate_coupon.type = 0 AND rebate_coupon.coupon = 1 THEN 1
					WHEN rebate_coupon.type = 2 THEN 3
					WHEN (rebate_coupon.type = 1 OR rebate_coupon.type = 5) AND rebate_coupon.coupon = 0 THEN 4
					WHEN rebate_coupon.type = 1 AND rebate_coupon.coupon = 1 THEN 5			
				END) AS key_position,
				rebate_coupon.type,
				rebate_coupon.coupon,
				rebate_coupon.discount,
				rebate_coupon.discount_type,
				rebate_coupon.coupon_max_usage,
				rebate_coupon.coupon_max_usage_customer,
				rebate_coupon.applicable_on_sale,
				rebate_coupon.min_cart_value,
				IF(rebate_coupon.type=2,rebate_coupon.buy_x_qty,rebate_coupon.min_qty_required) AS min_qty_required,
				rebate_coupon.max_qty_allowed,
				rebate_coupon.buy_x_qty,
				rebate_coupon.get_y_qty				
				FROM 
				cart_discount
				INNER JOIN
				cart_discount_item_product
				ON
				(cart_discount.id = cart_discount_item_product.id_cart_discount) 
				INNER JOIN
				rebate_coupon
				ON
				(cart_discount.id_rebate_coupon = rebate_coupon.id)
				WHERE
				cart_discount_item_product.id_cart_item_product = "'.$this->mysqli->escape_string($id_cart_item_product).'"
				AND
				(rebate_coupon.type != 3 OR rebate_coupon.type != 4)')) throw new Exception('An error occured while trying to get applied discounts.'."\r\n\r\n".$this->mysqli->error);	
				
				while ($row_discount = $result_discount->fetch_assoc()) {
					$discounts[$row_discount['key_position']] = $row_discount;
					
					if ($max_qty == 0 || $max_qty != 0 && $row_discount['max_qty_allowed'] != 0 && $row_discount['max_qty_allowed'] < $max_qty) $max_qty = $row_discount['max_qty_allowed'];
				}
				$result_discount->free();
				
				// check 1 - Cash or % off product price (Rebate)
				if (!isset($discounts[0])) {			
					if (!$result_discount = $this->mysqli->query('SELECT
					rebate_coupon.id AS id_rebate_coupon,
					rebate_coupon.type,
					rebate_coupon.coupon,
					rebate_coupon.discount_type,
					rebate_coupon.discount,
					rebate_coupon.min_qty_required,
					rebate_coupon.max_qty_allowed		
					FROM 
					rebate_coupon
							
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
						"'.$this->mysqli->escape_string($current_datetime).'" BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
					)
					AND
					(
						(IF((SELECT 
						rebate_coupon_product.id_rebate_coupon
						FROM
						rebate_coupon_product 
						WHERE 
						rebate_coupon_product.id_product = "'.$id_product.'"
						LIMIT 1) IS NOT NULL,1,0)) = 1        
										
						OR 
					
						(IF((SELECT 
						rebate_coupon_category.id_rebate_coupon
						FROM
						rebate_coupon_category INNER JOIN product_category
						ON (rebate_coupon_category.id_category = product_category.id_category)
						WHERE 
						rebate_coupon_category.id_rebate_coupon = rebate_coupon.id
						AND
						product_category.id_product = "'.$id_product.'"
						LIMIT 1) IS NOT NULL,1,0)) = 1					
					)
					ORDER BY 
					(CASE rebate_coupon.discount_type
						WHEN 0 THEN
							(rebate_coupon.discount/"'.$sell_price.'")
						WHEN 1 THEN
							(rebate_coupon.discount/100)
					END) DESC
					LIMIT 1')) throw new Exception('An error occured while trying to get best %/$ rebate.'."\r\n\r\n".$this->mysqli->error);
					
					if ($result_discount->num_rows) {
						$row_discount = $result_discount->fetch_assoc();
						$discounts[0] = $row_discount;		
						
						if ($max_qty == 0 || $max_qty != 0 && $row_discount['max_qty_allowed'] != 0 && $row_discount['max_qty_allowed'] < $max_qty) $max_qty = $row_discount['max_qty_allowed'];
						
						// check if we already have this discount in cart
						if (!$result_discount_in_cart = $this->mysqli->query('SELECT
						cart_discount.id,
						cart_discount_item_product.id AS id_cart_discount_item_product
						FROM 
						cart_discount 
						LEFT JOIN
						cart_discount_item_product 
						ON
						(cart_discount.id = cart_discount_item_product.id_cart_discount AND cart_discount_item_product.id_cart_item_product = "'.$this->mysqli->escape_string($id_cart_item_product).'")
						WHERE
						cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"
						AND
						cart_discount.id_rebate_coupon = "'.$this->mysqli->escape_string($row_discount['id_rebate_coupon']).'"
						LIMIT 1')) throw new Exception('An error occured while trying to check if discount is already in cart and applied to product.'."\r\n\r\n".$this->mysqli->error);
						
						if ($result_discount_in_cart->num_rows) {
							$row_discount_in_cart = $result_discount_in_cart->fetch_assoc();
							$discounts[0]['id'] = $row_discount_in_cart['id'];
							$discounts[0]['id_cart_discount_item_product'] = $row_discount_in_cart['id_cart_discount_item_product'];
						}
						$result_discount_in_cart->free();	
					}
					$result_discount->free();
				}
				
				// validate
				$applicable_products = array(
					'qty'=>$qty,
					'products'=>array(0=>array(
						'id'=>$id_cart_item,
						'qty'=>$qty,
						'id_cart_item_product'=>$id_cart_item_product,
						'id_product'=>$id_product,
						'id_product_variant'=>$id_product_variant,
						'cost_price'=>$cost_price,
						'price'=>$price,
						'sell_price'=>$sell_price,
						'subtotal'=>$subtotal,
						'id_cart_discount_item_product'=>$discounts[0]['id_cart_discount_item_product'],
					),
				));
				
				if (isset($discounts[0])) {				
					if (isset($discounts[0]['id_cart_discount_item_product'])) $applicable_products['products'][0]['id_cart_discount_item_product'] = $discounts[0]['id_cart_discount_item_product'];
				
					if ($id_product_variant) {
						if (!$stmt_variant->bind_param("iiii", $discounts[0]['id_rebate_coupon'],$this->id,$id_product,$id_product_variant)) throw new Exception('An error occured while trying to bind params to get product variants statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_variant->execute()) throw new Exception('An error occured while trying to execute get product variants statement.'."\r\n\r\n".$this->mysqli->error);				
						
						/* store result */
						$stmt_variant->store_result();																											
						
						if ($stmt_variant->num_rows) {												
							/* bind result variables */
							$stmt_variant->bind_result($p_id, $p_id_cart_item_product, $p_id_product, $p_id_product_variant, $p_cost_price, $p_price, $p_sell_price,
							$p_subtotal, $p_qty, $p_id_cart_discount_item_product, $p_sell_price);	
								
							while ($stmt_variant->fetch()) {
								$applicable_products['qty'] += $p_qty;
								$applicable_products['products'][] = array(
									'id'=>$p_id,
									'qty'=>$p_qty,
									'id_cart_item_product'=>$p_id_cart_item_product,
									'id_product'=>$p_id_product,
									'id_product_variant'=>$p_id_product_variant,
									'cost_price'=>$p_cost_price,
									'price'=>$p_price,
									'sell_price'=>$p_sell_price,
									'subtotal'=>$p_subtotal,
									'id_cart_discount_item_product'=>$p_id_cart_discount_item_product,
								);
							}
						}
					}		
					
					// if min required qty is met
					if ($applicable_products['qty'] >= $discounts[0]['min_qty_required']) {
						if ($max_qty != 0 && $applicable_products['qty'] > $max_qty) {
						//if ($discounts[0]['max_qty_allowed'] > 0 && $applicable_products['qty'] > $discounts[0]['max_qty_allowed']) {
							// max_qty_allowed, we need to check this, but how do we control it for variants							
							//$remove_qty = $applicable_products['qty']-$discounts[0]['max_qty_allowed'];
							$remove_qty = $applicable_products['qty']-$max_qty;
							
							if ($remove_qty > 0) {
								$applicable_products['qty'] -= $remove_qty;
								
								foreach (array_reverse($applicable_products['products'],true) as $id_update_product => $row_update_product) {									
									if ($row_update_product['qty'] >= $remove_qty) {											
										$row_update_product['qty'] -= $remove_qty; 
										$remove_qty = 0;
									} else { 
										$remove_qty -= $row_update_product['qty'];
										$row_update_product['qty'] = 0;
									}
									
									// update qty
									//$this->upd_product_qty($row_update_product['id'], $row_update_product['qty']);	
	
									if (!$stmt_product_qty_upd->bind_param("iii", $row_update_product['qty'],$row_update_product['qty'],$row_update_product['id'])) throw new Exception('An error occured while trying to bind params to update product qty statement.'."\r\n\r\n".$this->mysqli->error);
									
									/* Execute the statement */
									if (!$stmt_product_qty_upd->execute()) throw new Exception('An error occured while trying to execute update product qty statement.'."\r\n\r\n".$this->mysqli->error);			
									
									if ($row_update_product['qty'] == 0) unset($applicable_products['products'][$id_update_product]);
									else $applicable_products['products'][$id_update_product]['qty'] = $row_update_product['qty'];
									
									if ($remove_qty == 0) break;
								}
							}
						}				
					
						$id_cart_discount = $discounts[0]['id'];
						
						if (!$id_cart_discount) {
							if (!$stmt_discount->bind_param("ii", $this->id,$discounts[0]['id_rebate_coupon'])) throw new Exception('An error occured while trying to bind params to add discount statement.'."\r\n\r\n".$this->mysqli->error);
						
							/* Execute the statement */
							if (!$stmt_discount->execute()) throw new Exception('An error occured while trying to execute add discount statement.'."\r\n\r\n".$this->mysqli->error);			
							$id_cart_discount = $this->mysqli->insert_id;	
							
							$discounts[0]['id'] = $id_cart_discount;
						}
		
						foreach ($applicable_products['products'] as $key => $row_product) {
							//$id_cart_item = $row_product['id'];
							//$id_cart_item_product = $row_product['id_cart_item_product'];
							
							$amount = 0;
							
							switch ($discounts[0]['discount_type']) {
								// fixed
								case 0:
									if ($discounts[0]['discount'] > $row_product['sell_price']) $amount = $row_product['sell_price'];
									else $amount = $discounts[0]['discount'];
									break;
								// percent
								case 1:
									$amount = round(($row_product['sell_price']*$discounts[0]['discount'])/100,2);
									break;											
							}
							
							$applicable_products['products'][$key]['sell_price'] -= $amount;
							$applicable_products['products'][$key]['subtotal'] = $row_product['qty']*$row_product['sell_price'];	
							
							$amount = $row_product['qty']*$amount;
							
							if (!$row_product['id_cart_discount_item_product']) {												
								if (!$stmt_discount_item_product->bind_param("iid", $id_cart_discount, $row_product['id_cart_item_product'], $amount)) throw new Exception('An error occured while trying to bind params to add discount item product statement.'."\r\n\r\n".$this->mysqli->error);	
								
								/* Execute the statement */
								if (!$stmt_discount_item_product->execute()) throw new Exception('An error occured while trying to add discount item product.'."\r\n\r\n".$this->mysqli->error);
							// update	
							} else {
								if (!$stmt_discount_item_product_upd->bind_param("di", $amount, $row_product['id_cart_discount_item_product'])) throw new Exception('An error occured while trying to bind params to update discount item product statement.'."\r\n\r\n".$this->mysqli->error);	
								
								/* Execute the statement */
								if (!$stmt_discount_item_product_upd->execute()) throw new Exception('An error occured while trying to update discount item product.'."\r\n\r\n".$this->mysqli->error);													
							}
						}				
					}
				}			
				
				// check 2 - Cash or % off product price (Coupon)
				if (!isset($discounts[1])) {			
					if (!$result_discount = $this->mysqli->query('SELECT
					rebate_coupon.id AS id_rebate_coupon,
					rebate_coupon.type,
					rebate_coupon.coupon,
					rebate_coupon.discount_type,
					rebate_coupon.discount,
					rebate_coupon.min_qty_required,
					rebate_coupon.max_qty_allowed		
					FROM 
					cart_discount
					INNER JOIN
					rebate_coupon
					ON
					(cart_discount.id_rebate_coupon = rebate_coupon.id)
							
					WHERE
					cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"
					AND
					rebate_coupon.coupon = 1
					AND
					rebate_coupon.type = 0
					AND
					rebate_coupon.active = 1
					AND
					(
						rebate_coupon.end_date = "0000-00-00 00:00:00"
						OR
						"'.$this->mysqli->escape_string($current_datetime).'" BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
					)					
					AND
					(
						(IF((SELECT 
						rebate_coupon_product.id_rebate_coupon
						FROM
						rebate_coupon_product 
						WHERE 
						rebate_coupon_product.id_product = "'.$id_product.'"
						LIMIT 1) IS NOT NULL,1,0)) = 1        					
					
						OR 
					
						(IF((SELECT 
						rebate_coupon_category.id_rebate_coupon
						FROM
						rebate_coupon_category INNER JOIN product_category
						ON (rebate_coupon_category.id_category = product_category.id_category)
						WHERE 
						rebate_coupon_category.id_rebate_coupon = rebate_coupon.id
						AND
						product_category.id_product = "'.$id_product.'"
						LIMIT 1) IS NOT NULL,1,0)) = 1					
					)
					ORDER BY 
					(CASE rebate_coupon.discount_type
						WHEN 0 THEN
							(rebate_coupon.discount/"'.$sell_price.'")
						WHEN 1 THEN
							(rebate_coupon.discount/100)
					END) DESC
					LIMIT 1')) throw new Exception('An error occured while trying to get best %/$ rebate.'."\r\n\r\n".$this->mysqli->error);
					
					if ($result_discount->num_rows) {
						$row_discount = $result_discount->fetch_assoc();
						$discounts[1] = $row_discount;		
						
						if ($max_qty == 0 || $max_qty != 0 && $row_discount['max_qty_allowed'] != 0 && $row_discount['max_qty_allowed'] < $max_qty) $max_qty = $row_discount['max_qty_allowed'];	
						
						// check if we already have this discount in cart
						if (!$result_discount_in_cart = $this->mysqli->query('SELECT
						cart_discount.id,
						cart_discount_item_product.id AS id_cart_discount_item_product
						FROM 
						cart_discount 
						LEFT JOIN
						cart_discount_item_product 
						ON
						(cart_discount.id = cart_discount_item_product.id_cart_discount AND cart_discount_item_product.id_cart_item_product = "'.$this->mysqli->escape_string($id_cart_item_product).'")
						WHERE
						cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"
						AND
						cart_discount.id_rebate_coupon = "'.$this->mysqli->escape_string($row_discount['id_rebate_coupon']).'"
						LIMIT 1')) throw new Exception('An error occured while trying to check if discount is already in cart and applied to product.'."\r\n\r\n".$this->mysqli->error);
						
						if ($result_discount_in_cart->num_rows) {
							$row_discount_in_cart = $result_discount_in_cart->fetch_assoc();
							$discounts[1]['id'] = $row_discount_in_cart['id'];
							$discounts[1]['id_cart_discount_item_product'] = $row_discount_in_cart['id_cart_discount_item_product'];
						}
						$result_discount_in_cart->free();	
					}
					$result_discount->free();
				}									
				
				// 2 - Cash or % off product price (Coupon)
				// validate
				$applicable_products = array(
					'qty'=>$applicable_products['products'][0]['qty'],
					'products'=>array(0 => $applicable_products['products'][0]),
				);			
				//$applicable_products['products'][0]['id_cart_discount_item_product'] = $discounts[1]['id_cart_discount_item_product'];
				
				if (isset($discounts[1])) {		
					if (isset($discounts[1]['id_cart_discount_item_product'])) $applicable_products['products'][0]['id_cart_discount_item_product'] = $discounts[1]['id_cart_discount_item_product'];
				
					if ($id_product_variant) {
						if (!$stmt_variant->bind_param("iiii",$discounts[1]['id'],$this->id,$id_product,$id_product_variant)) throw new Exception('An error occured while trying to bind params to get product variants statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_variant->execute()) throw new Exception('An error occured while trying to execute get product variants statement.'."\r\n\r\n".$this->mysqli->error);				
						
						/* store result */
						$stmt_variant->store_result();																											
						
						if ($stmt_variant->num_rows) {										
							/* bind result variables */
							$stmt_variant->bind_result($p_id, $p_id_cart_item_product, $p_id_product, $p_id_product_variant, $p_cost_price, $p_price, $p_sell_price,
							$p_subtotal, $p_qty, $p_id_cart_discount_item_product);	
								
							while ($stmt_variant->fetch()) {
								$applicable_products['qty'] += $p_qty;
								$applicable_products['products'][] = array(
									'id'=>$p_id,
									'qty'=>$p_qty,
									'id_cart_item_product'=>$p_id_cart_item_product,
									'id_product'=>$p_id_product,
									'id_product_variant'=>$p_id_product_variant,
									'cost_price'=>$p_cost_price,
									'price'=>$p_price,
									'sell_price'=>$p_sell_price,
									'subtotal'=>$p_subtotal,
									'id_cart_discount_item_product'=>$p_id_cart_discount_item_product,
								);
							}
						}
					}		
					
					// if min required qty is met
					if ($applicable_products['qty'] >= $discounts[1]['min_qty_required']) {
						if ($max_qty != 0 && $applicable_products['qty'] > $max_qty) {
						//if ($discounts[1]['max_qty_allowed'] > 0 && $applicable_products['qty'] > $discounts[1]['max_qty_allowed']) {
							// max_qty_allowed, we need to check this, but how do we control it for variants							
							//$remove_qty = $applicable_products['qty']-$discounts[1]['max_qty_allowed'];
							$remove_qty = $applicable_products['qty']-$max_qty;
							
							if ($remove_qty > 0) {
								$applicable_products['qty'] -= $remove_qty;
								
								foreach (array_reverse($applicable_products['products'],true) as $id_update_product => $row_update_product) {									
									if ($row_update_product['qty'] >= $remove_qty) {											
										$row_update_product['qty'] -= $remove_qty; 
										$remove_qty = 0;
									} else { 
										$remove_qty -= $row_update_product['qty'];
										$row_update_product['qty'] = 0;
									}
									
									// update qty
									//$this->upd_product_qty($row_update_product['id'], $row_update_product['qty']);	
									if (!$stmt_product_qty_upd->bind_param("iii", $row_update_product['qty'],$row_update_product['qty'],$row_update_product['id'])) throw new Exception('An error occured while trying to bind params to update product qty statement.'."\r\n\r\n".$this->mysqli->error);
									
									/* Execute the statement */
									if (!$stmt_product_qty_upd->execute()) throw new Exception('An error occured while trying to execute update product qty statement.'."\r\n\r\n".$this->mysqli->error);		
									
									if ($row_update_product['qty'] == 0) unset($applicable_products['products'][$id_update_product]);
									else $applicable_products['products'][$id_update_product]['qty'] = $row_update_product['qty'];
									
									if ($remove_qty == 0) break;
								}
							}
						}					
						
						$id_cart_discount = $discounts[1]['id'];
						
						if (!$id_cart_discount) {
							if (!$stmt_discount->bind_param("ii", $this->id,$discounts[1]['id_rebate_coupon'])) throw new Exception('An error occured while trying to bind params to add discount statement.'."\r\n\r\n".$this->mysqli->error);
						
							/* Execute the statement */
							if (!$stmt_discount->execute()) throw new Exception('An error occured while trying to execute add discount statement.'."\r\n\r\n".$this->mysqli->error);			
							$id_cart_discount = $this->mysqli->insert_id;	
							
							$discounts[1]['id'] = $id_cart_discount;
						}
		
						foreach ($applicable_products['products'] as $key => $row_product) {
							//$id_cart_item = $row_product['id'];
							//$id_cart_item_product = $row_product['id_cart_item_product'];
							
							$amount = 0;
							
							switch ($discounts[1]['discount_type']) {
								// fixed
								case 0:
									if ($discounts[1]['discount'] > $row_product['sell_price']) $amount = $row_product['sell_price'];
									else $amount = $discounts[1]['discount'];
									break;
								// percent
								case 1:
									$amount = round(($row_product['sell_price']*$discounts[1]['discount'])/100,2);
									break;											
							}
							
							$applicable_products['products'][$key]['sell_price'] -= $amount;
							$applicable_products['products'][$key]['subtotal'] = $row_product['qty']*$row_product['sell_price'];
							
							$amount = $row_product['qty']*$amount;
							
							if (!$row_product['id_cart_discount_item_product']) {												
								if (!$stmt_discount_item_product->bind_param("iid", $id_cart_discount, $row_product['id_cart_item_product'], $amount)) throw new Exception('An error occured while trying to bind params to add discount item product statement.'."\r\n\r\n".$this->mysqli->error);	
								
								/* Execute the statement */
								if (!$stmt_discount_item_product->execute()) throw new Exception('An error occured while trying to add discount item product.'."\r\n\r\n".$this->mysqli->error);
							// update	
							} else {
								if (!$stmt_discount_item_product_upd->bind_param("di", $amount, $row_product['id_cart_discount_item_product'])) throw new Exception('An error occured while trying to bind params to update discount item product statement.'."\r\n\r\n".$this->mysqli->error);	
								
								/* Execute the statement */
								if (!$stmt_discount_item_product_upd->execute()) throw new Exception('An error occured while trying to update discount item product.'."\r\n\r\n".$this->mysqli->error);													
							}
						}				
					}
				}		
				
				// 4 - Buy or Get (Rebate or Coupon, not both, if not applicable on sale, only products that do not have a special price/tier price or #1 rebate applied (customer type discount does not count)
				if (!isset($discounts[3])) {			
					if (!$result_discount = $this->mysqli->query('SELECT
					rebate_coupon.id AS id_rebate_coupon,
					rebate_coupon.type,
					rebate_coupon.coupon,
					rebate_coupon.discount_type,
					rebate_coupon.discount,
					rebate_coupon.max_qty_allowed,
					rebate_coupon.buy_x_qty AS min_qty_required,
					rebate_coupon.buy_x_qty,
					rebate_coupon.get_y_qty							
					FROM 
					rebate_coupon
							
					WHERE
					rebate_coupon.coupon = 0
					AND
					rebate_coupon.type = 2
					AND
					rebate_coupon.active = 1
					AND
					(
						(IF((SELECT 
						rebate_coupon_product.id_rebate_coupon
						FROM
						rebate_coupon_product 
						WHERE 
						rebate_coupon_product.id_product = "'.$id_product.'"
						LIMIT 1) IS NOT NULL,1,0)) = 1        					
					
						OR 
					
						(IF((SELECT 
						rebate_coupon_category.id_rebate_coupon
						FROM
						rebate_coupon_category INNER JOIN product_category
						ON (rebate_coupon_category.id_category = product_category.id_category)
						WHERE 
						rebate_coupon_category.id_rebate_coupon = rebate_coupon.id
						AND
						product_category.id_product = "'.$id_product.'"
						LIMIT 1) IS NOT NULL,1,0)) = 1					
					)
					ORDER BY 
					(CASE rebate_coupon.discount_type
						WHEN 0 THEN
							(rebate_coupon.discount/"'.$sell_price.'")
						WHEN 1 THEN
							(rebate_coupon.discount/100)
					END) DESC
					LIMIT 1')) throw new Exception('An error occured while trying to get best %/$ rebate.'."\r\n\r\n".$this->mysqli->error);
					
					if ($result_discount->num_rows) {
						$row_discount = $result_discount->fetch_assoc();
						$discounts[3] = $row_discount;	
						
						if ($max_qty == 0 || $max_qty != 0 && $row_discount['max_qty_allowed'] != 0 && $row_discount['max_qty_allowed'] < $max_qty) $max_qty = $row_discount['max_qty_allowed'];
						
						// check if we already have this discount in cart
						if (!$result_discount_in_cart = $this->mysqli->query('SELECT
						cart_discount.id,
						cart_discount_item_product.id AS id_cart_discount_item_product
						FROM 
						cart_discount 
						LEFT JOIN
						cart_discount_item_product 
						ON
						(cart_discount.id = cart_discount_item_product.id_cart_discount AND cart_discount_item_product.id_cart_item_product = "'.$this->mysqli->escape_string($id_cart_item_product).'")
						WHERE
						cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"
						AND
						cart_discount.id_rebate_coupon = "'.$this->mysqli->escape_string($row_discount['id_rebate_coupon']).'"
						LIMIT 1')) throw new Exception('An error occured while trying to check if discount is already in cart and applied to product.'."\r\n\r\n".$this->mysqli->error);
						
						if ($result_discount_in_cart->num_rows) {
							$row_discount_in_cart = $result_discount_in_cart->fetch_assoc();
							$discounts[3]['id'] = $row_discount_in_cart['id'];
							$discounts[3]['id_cart_discount_item_product'] = $row_discount_in_cart['id_cart_discount_item_product'];
						}
						$result_discount_in_cart->free();	
					}
					$result_discount->free();		
				}
			
				// validate
				$applicable_products = array(
					'qty'=>$applicable_products['products'][0]['qty'],
					'products'=>array(0 => $applicable_products['products'][0]),
				);			
				//$applicable_products['products'][0]['id_cart_discount_item_product'] = $discounts[3]['id_cart_discount_item_product'];
				
				//echo '<pre>'.print_r($applicable_products,1).'</pre>';
				
				//echo '<pre>'.print_r($discounts,1).'</pre>';
				
				// Buy and Get (Rebate or Coupon)
				if (isset($discounts[3])) {				
					if (isset($discounts[3]['id_cart_discount_item_product'])) $applicable_products['products'][0]['id_cart_discount_item_product'] = $discounts[3]['id_cart_discount_item_product'];
				
					if ($id_product_variant) {
						if (!$stmt_variant->bind_param("iiii", $discounts[3]['id_rebate_coupon'],$this->id,$id_product,$id_product_variant)) throw new Exception('An error occured while trying to bind params to get product variants statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_variant->execute()) throw new Exception('An error occured while trying to execute get product variants statement.'."\r\n\r\n".$this->mysqli->error);				
						
						/* store result */
						$stmt_variant->store_result();																											
						
						if ($stmt_variant->num_rows) {										
							/* bind result variables */
							$stmt_variant->bind_result($p_id, $p_id_cart_item_product, $p_id_product, $p_id_product_variant, $p_cost_price, $p_price, $p_sell_price,
							$p_subtotal, $p_qty, $p_id_cart_discount_item_product, $p_sell_price);	
								
							while ($stmt_variant->fetch()) {
								$applicable_products['qty'] += $p_qty;
								$applicable_products['products'][] = array(
									'id'=>$p_id,
									'qty'=>$p_qty,
									'id_cart_item_product'=>$p_id_cart_item_product,
									'id_product'=>$p_id_product,
									'id_product_variant'=>$p_id_product_variant,
									'cost_price'=>$p_cost_price,
									'price'=>$p_price,
									'sell_price'=>$p_sell_price,
									'subtotal'=>$p_subtotal,
									'id_cart_discount_item_product'=>$p_id_cart_discount_item_product,
								);
							}
						}
					}					
					
					// if min required qty is met				
					//if ($applicable_products['qty'] >= $discounts[3]['min_qty_required'] && ($max_qty == 0 || ($applicable_products['qty']+(floor($applicable_products['qty']/$discounts[3]['min_qty_required'])*$discounts[3]['get_y_qty']) <= $max_qty))) {
						
					if ($applicable_products['qty'] >= $discounts[3]['min_qty_required']) {
						/* Prepare the statement */
						if (!$stmt_existing_qty = $this->mysqli->prepare('SELECT 
						cart_item.id,
						SUM(cart_item.qty) AS qty,
						cart_discount_item_product.id AS id_cart_discount_item_product
						FROM				
						cart_item
						INNER JOIN 
						cart_item_product
						ON
						(cart_item.id = cart_item_product.id_cart_item)
						LEFT JOIN 
						cart_discount_item_product
						ON
						(cart_item.id_cart_discount = cart_discount_item_product.id_cart_discount AND cart_discount_item_product.id_cart_item_product = cart_item_product.id)
						WHERE
						cart_item.id_cart_discount = ?
						AND
						cart_item_product.id_product = ?')) throw new Exception('An error occured while trying to prepare check existing item qty in cart statement.'."\r\n\r\n".$this->mysqli->error);		
						
						/* Prepare the statement */
						if (!$stmt_cart_item_upd = $this->mysqli->prepare('UPDATE
						cart_item
						INNER JOIN
						cart_item_product
						ON
						(cart_item.id = cart_item_product.id_cart_item)
						SET				
						cart_item.qty = ?,
						cart_item_product.qty = ?
						WHERE
						cart_item.id = ?')) throw new Exception('An error occured while trying to prepare update cart item statement.'."\r\n\r\n".$this->mysqli->error);							
						
						/* Prepare the statement */
						if (!$stmt_cart_item = $this->mysqli->prepare('INSERT INTO
						cart_item
						SET
						id_cart = ?,
						id_cart_discount = ?,
						qty = ?')) throw new Exception('An error occured while trying to prepare add cart item statement.'."\r\n\r\n".$this->mysqli->error);							
												
						/* Prepare the statement */
						if (!$stmt_cart_item_product = $this->mysqli->prepare('INSERT INTO
						cart_item_product
						SET
						id_cart_item = ?,
						id_product = ?,
						id_product_variant = ?,								
						qty = ?,
						cost_price = ?,
						price = ?,
						sell_price = ?,
						subtotal = ?')) throw new Exception('An error occured while trying to prepare add cart item product statement.'."\r\n\r\n".$this->mysqli->error);																																						
									
						$max_qty = ($discounts[3]['max_qty_allowed'] != 0 && ($max_qty == 0 || $max_qty != 0 && $discounts[3]['max_qty_allowed'] < $max_qty) ? $discounts[3]['max_qty_allowed']:$max_qty);
												
						$max_buy = ceil($max_qty/($discounts[3]['buy_x_qty']+$discounts[3]['get_y_qty'])*$discounts[3]['buy_x_qty']);
						
						if ($discounts[3]['max_qty_allowed'] > 0 && $applicable_products['qty'] > $max_buy) {
							// max_qty_allowed, we need to check this, but how do we control it for variants							
							$remove_qty = $applicable_products['qty']-$max_buy;							
							
							if ($remove_qty > 0) {
								$applicable_products['qty'] -= $remove_qty;
								
								foreach (array_reverse($applicable_products['products'],true) as $id_update_product => $row_update_product) {									
									if ($row_update_product['qty'] >= $remove_qty) {											
										$row_update_product['qty'] -= $remove_qty; 
										$remove_qty = 0;
									} else { 
										$remove_qty -= $row_update_product['qty'];
										$row_update_product['qty'] = 0;
									}
									
									// update qty									
									//$this->upd_product_qty($row_update_product['id'], $row_update_product['qty']);	
									if (!$stmt_product_qty_upd->bind_param("iii", $row_update_product['qty'],$row_update_product['qty'],$row_update_product['id'])) throw new Exception('An error occured while trying to bind params to update product qty statement.'."\r\n\r\n".$this->mysqli->error);
									
									/* Execute the statement */
									if (!$stmt_product_qty_upd->execute()) throw new Exception('An error occured while trying to execute update product qty statement.'."\r\n\r\n".$this->mysqli->error);		
									
									if ($row_update_product['qty'] == 0) {
										unset($applicable_products['products'][$id_update_product]);
										$this->upd_product_qty($row_update_product['id'], $row_update_product['qty']);
									} else $applicable_products['products'][$id_update_product]['qty'] = $row_update_product['qty'];
									
									if ($remove_qty == 0) break;
								}										
							}
						}
						
						$id_cart_discount = $discounts[3]['id'];
						
						if (!$id_cart_discount) {
							if (!$stmt_discount->bind_param("ii", $this->id,$discounts[3]['id_rebate_coupon'])) throw new Exception('An error occured while trying to bind params to add discount statement.'."\r\n\r\n".$this->mysqli->error);
						
							/* Execute the statement */
							if (!$stmt_discount->execute()) throw new Exception('An error occured while trying to execute add discount statement.'."\r\n\r\n".$this->mysqli->error);			
							$id_cart_discount = $this->mysqli->insert_id;	
							
							$discounts[3]['id'] = $id_cart_discount;
						}
						
						$amount = 0;			
						foreach ($applicable_products['products'] as $key => $row_product) {
							//$id_cart_item = $row_product['id'];
							//$id_cart_item_product = $row_product['id_cart_item_product'];
												
							if (!$row_product['id_cart_discount_item_product']) {												
								if (!$stmt_discount_item_product->bind_param("iid", $id_cart_discount, $row_product['id_cart_item_product'], $amount)) throw new Exception('An error occured while trying to bind params to add discount item product statement.'."\r\n\r\n".$this->mysqli->error);	
								
								/* Execute the statement */
								if (!$stmt_discount_item_product->execute()) throw new Exception('An error occured while trying to add discount item product.'."\r\n\r\n".$this->mysqli->error);
							// update	
							} else {
								if (!$stmt_discount_item_product_upd->bind_param("di", $amount, $row_product['id_cart_discount_item_product'])) throw new Exception('An error occured while trying to bind params to update discount item product statement.'."\r\n\r\n".$this->mysqli->error);	
								
								/* Execute the statement */
								if (!$stmt_discount_item_product_upd->execute()) throw new Exception('An error occured while trying to update discount item product.'."\r\n\r\n".$this->mysqli->error);													
							}														
						}		
						
						$row_product = array_shift($applicable_products['products']);				
						$n_product = floor($applicable_products['qty']/$discounts[3]['buy_x_qty'])*$discounts[3]['get_y_qty'];	
						
						// check to see if we already have added n product in cart.
						if (!$stmt_existing_qty->bind_param("ii", $id_cart_discount, $row_product['id_product'])) throw new Exception('An error occured while trying to bind params to check existing item qty in cart statement.'."\r\n\r\n".$this->mysqli->error);												
						
						/* Execute the statement */
						if (!$stmt_existing_qty->execute()) throw new Exception('An error occured while trying to check existing item qty in cart.'."\r\n\r\n".$this->mysqli->error);					
						
						/* store result */
						$stmt_existing_qty->store_result();																											
													
						/* bind result variables */
						$stmt_existing_qty->bind_result($p_id_cart_item, $p_qty, $p_id_cart_discount_item_product);																				
				
						// fetch
						$stmt_existing_qty->fetch();		
						
						// remove existing from extra to add
						$n_product -= $p_qty;	

						if ($max_qty > 0 && $n_product > $max_qty) $n_product = $max_qty;				
						
						$qty_remaining = get_qty_remaining($id_product, $id_product_variant);											
						
						// if the n_product qty to add (get_y_qty) exceeds qty remaining, cap
						if ($qty_remaining >= 0 && $n_product > $qty_remaining) $n_product = $qty_remaining;														
							
						switch ($discounts[3]['discount_type']) {
							// fixed
							case 0:
								if ($discounts[3]['discount'] > $row_product['sell_price']) $amount = $row_product['sell_price'];
								else $amount = $discounts[3]['discount'];
								break;
							// percent
							case 1:
								$amount = round(($row_product['sell_price']*$discounts[3]['discount'])/100,2);
								break;											
						}
					
						// if we need to add extras
						if ($n_product > 0) {			
							// add number of product											
							$product_qty=1;
							$product_id_product = $row_product['id_product'];
							$product_id_product_variant = $row_product['id_product_variant'];
							$product_cost_price = $row_product['cost_price'];
							$product_price = $row_product['sell_price'];
							$product_sell_price = $product_price;
							$product_subtotal = $product_sell_price;						
							
							// if we have variants
							if ($product_id_product_variant) {				
								for ($i=0; $i<$n_product; ++$i) {																	
									if (!$stmt_cart_item->bind_param("iii", $this->id, $id_cart_discount, $product_qty)) throw new Exception('An error occured while trying to bind params to add cart item statement.'."\r\n\r\n".$this->mysqli->error);		
																
									/* Execute the statement */
									if (!$stmt_cart_item->execute()) throw new Exception('An error occured while trying to add cart item.'."\r\n\r\n".$this->mysqli->error);
									
									$product_id_cart_item = $this->mysqli->insert_id;		
									
									if (!$stmt_cart_item_product->bind_param("iiiidddd", $product_id_cart_item, $product_id_product, $product_id_product_variant, $product_qty, $product_cost_price, $product_price, $product_sell_price, $product_subtotal)) throw new Exception('An error occured while trying to bind params to add cart item product statement.'."\r\n\r\n".$this->mysqli->error);	
															
									/* Execute the statement */
									if (!$stmt_cart_item_product->execute()) throw new Exception('An error occured while trying to add cart item product.'."\r\n\r\n".$this->mysqli->error);		
									
									$p_id_cart_item_product = $this->mysqli->insert_id;
									
									if (!$stmt_discount_item_product->bind_param("iid", $id_cart_discount, $p_id_cart_item_product, $amount)) throw new Exception('An error occured while trying to bind params to add discount item product statement.'."\r\n\r\n".$this->mysqli->error);	
									
									/* Execute the statement */
									if (!$stmt_discount_item_product->execute()) throw new Exception('An error occured while trying to add discount item product.'."\r\n\r\n".$this->mysqli->error);								
								}				
							// if we dont but have already an added product in cart for this discount
							} else if ($p_id_cart_item) {	
								$p_qty = $p_qty ? $p_qty:0;
								$qty_upd = $p_qty+$n_product;
												
								if (!$stmt_cart_item_upd->bind_param("iii", $qty_upd, $qty_upd, $p_id_cart_item)) throw new Exception('An error occured while trying to bind params to update cart item statement.'."\r\n\r\n".$this->mysqli->error);	
								
								/* Execute the statement */
								if (!$stmt_cart_item_upd->execute()) throw new Exception('An error occured while trying to update cart item.'."\r\n\r\n".$this->mysqli->error);	
								
								// update	
								if (!$stmt_discount_item_product_upd->bind_param("di", $amount, $p_id_cart_discount_item_product)) throw new Exception('An error occured while trying to bind params to update discount item product statement.'."\r\n\r\n".$this->mysqli->error);	
								
								/* Execute the statement */
								if (!$stmt_discount_item_product_upd->execute()) throw new Exception('An error occured while trying to update discount item product.'."\r\n\r\n".$this->mysqli->error);									
							// add a new
							} else {
								if (!$stmt_cart_item->bind_param("iii", $this->id, $id_cart_discount, $n_product)) throw new Exception('An error occured while trying to bind params to add cart item statement.'."\r\n\r\n".$this->mysqli->error);		
															
								/* Execute the statement */
								if (!$stmt_cart_item->execute()) throw new Exception('An error occured while trying to add cart item.'."\r\n\r\n".$this->mysqli->error);
								
								$product_id_cart_item = $this->mysqli->insert_id;															
								
								if (!$stmt_cart_item_product->bind_param("iiiidddd", $product_id_cart_item, $product_id_product, $product_id_product_variant, $n_product, $product_cost_price, $product_price, $product_sell_price, $product_subtotal)) throw new Exception('An error occured while trying to bind params to add cart item product statement.'."\r\n\r\n".$this->mysqli->error);	
													
								/* Execute the statement */
								if (!$stmt_cart_item_product->execute()) throw new Exception('An error occured while trying to add cart item product.'."\r\n\r\n".$this->mysqli->error);
								
								$p_id_cart_item_product = $this->mysqli->insert_id;
								
								if (!$stmt_discount_item_product->bind_param("iid", $id_cart_discount, $p_id_cart_item_product, $amount)) throw new Exception('An error occured while trying to bind params to add discount item product statement.'."\r\n\r\n".$this->mysqli->error);	
								
								/* Execute the statement */
								if (!$stmt_discount_item_product->execute()) throw new Exception('An error occured while trying to add discount item product.'."\r\n\r\n".$this->mysqli->error);
								
							}
						// if p_qty in cart over n_product, remove	
						} else if ($n_product < 0) {													
							$remove_qty = abs($n_product);
							
							if ($remove_qty > 0) {
								if ($row_product['id_product_variant']) {
									// get list of extras
									if (!$result_extra_product = $this->mysqli->query('SELECT 
									cart_item.id
									FROM
									cart_item
									WHERE
									cart_item.id_cart_discount = "'.$this->mysqli->escape_string($id_cart_discount).'"
									ORDER BY
									cart_item.id DESC
									LIMIT '.$this->mysqli->escape_string($remove_qty))) throw new Exception('An error occured while trying to get extra products.'."\r\n\r\n".$this->mysqli->error);
									
									while ($row_extra_product = $result_extra_product->fetch_assoc()) {
										$this->del_product($row_extra_product['id']);
									}
									$result_extra_product->free();
								// single products
								} else {
									if (!$result_extra_product = $this->mysqli->query('SELECT 
									id,
									qty
									FROM
									cart_item
									WHERE
									id_cart_discount = "'.$id_cart_discount.'"')) throw new Exception('An error occured while trying to get extra products.'."\r\n\r\n".$this->mysqli->error);
									$row_extra_product = $result_extra_product->fetch_assoc();
									if ($row_extra_product['qty']-$remove_qty <= 0) $this->del_product($row_extra_product['id']);
									else $this->upd_product_qty($row_extra_product['id'],$row_extra_product['qty']-$remove_qty);
								}
							}
						}	
					} else {
						// remove extras
						if ($discounts[3]['id']) { 
							// get list of extras
							if (!$result_extra_product = $this->mysqli->query('SELECT 
							cart_item.id
							FROM
							cart_item
							INNER JOIN
							cart_item_product
							ON
							(cart_item.id = cart_item_product.id_cart_item)
							WHERE
							cart_item.id_cart_discount = "'.$this->mysqli->escape_string($discounts[3]['id']).'"
							AND
							cart_item_product.id_product = "'.$this->mysqli->escape_string($id_product).'"
							ORDER BY
							cart_item.id DESC')) throw new Exception('An error occured while trying to get extra products.'."\r\n\r\n".$this->mysqli->error);
							
							while ($row_extra_product = $result_extra_product->fetch_assoc()) {
								$this->del_product($row_extra_product['id']);
							}	
							$result_extra_product->free();		
						}
					}
					
					// check if we have products linked to this discount
					if ($discounts[3]['id']) {
						if (!$result_count = $this->mysqli->query('SELECT 
						COUNT(id) AS total
						FROM
						cart_item
						WHERE
						id_cart_discount = "'.$this->mysqli->escape_string($discounts[3]['id']).'"')) throw new Exception('An error occured while trying to count products linked to this discount.'."\r\n\r\n".$this->mysqli->error);
						
						$row_count = $result_count->fetch_assoc();
						$result_count->free();
						
						// if no other products is linked to this discount, delete it
						if (!$row_count['total']) $this->del_discount($discounts[3]['id']);
					}
				}	
			}
		}
	}
	
	public function del_product($id_cart_item)
	{		
		$id_cart_item=(int)$id_cart_item;	
				
		if (!$result = $this->mysqli->query('SELECT
		cart_item_product.id,
		cart_item_product.id_product,
		cart_item_product.id_product_variant
		FROM 
		cart_item
		INNER JOIN
		cart_item_product
		ON
		(cart_item.id = cart_item_product.id_cart_item)
		WHERE
		cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to get product info.'."\r\n\r\n".$this->mysqli->error);	
		
		if (!$result->num_rows) return false;
		$row = $result->fetch_assoc();
		$id_cart_item_product = $row['id'];
		$id_product = $row['id_product'];
		$id_product_variant = $row['id_product_variant'];
		$result->free();			
		
		// get discounts
		$discounts=array();
		if (!$result = $this->mysqli->query('SELECT
		cart_discount.id,
		rebate_coupon.type,
		rebate_coupon.buy_x_qty,
		rebate_coupon.get_y_qty
		FROM
		cart_discount 
		INNER JOIN
		cart_discount_item_product
		ON
		(cart_discount.id = cart_discount_item_product.id_cart_discount)
		INNER JOIN
		rebate_coupon
		ON
		(cart_discount.id_rebate_coupon = rebate_coupon.id)
		WHERE
		cart_discount_item_product.id_cart_item_product = "'.$this->mysqli->escape_string($id_cart_item_product).'"')) throw new Exception('An error occured while trying to get product discounts.'."\r\n\r\n".$this->mysqli->error);		
		
		while ($row = $result->fetch_assoc()) {
			$discounts[$row['id']] = $row;	
		}
		$result->free();
				
		// delete options
		if (!$result_option = $this->mysqli->query('SELECT
		cart_item_option.id 
		FROM
		cart_item
		INNER JOIN
		cart_item_option
		ON
		(cart_item.id = cart_item_option.id_cart_item) 
		WHERE
		cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to get product options.'."\r\n\r\n".$this->mysqli->error);	

		if ($result_option->num_rows) {
			while ($row_option = $result_option->fetch_assoc()) {
				$this->del_option($row_option['id']);	
			}
		}		
		$result_option->free();
				
		if (!$this->mysqli->query('DELETE FROM
		cart_item,
		cart_item_product,
		cart_item_product_tax,
		cart_discount_item_product,
		sp,
		sp_tax,
		sp_discount
		USING
		cart_item
		INNER JOIN
		cart_item_product
		ON
		(cart_item.id = cart_item_product.id_cart_item)
		
		LEFT JOIN 
		cart_item_product_tax
		ON
		(cart_item_product.id = cart_item_product_tax.id_cart_item_product)
		
		LEFT JOIN 
		cart_discount_item_product
		ON
		(cart_item_product.id = cart_discount_item_product.id_cart_item_product) 

		LEFT JOIN 
		cart_item_product AS sp
		ON
		(cart_item_product.id = sp.id_cart_item_product)
		
		LEFT JOIN 
		cart_item_product_tax AS sp_tax
		ON
		(sp.id = sp_tax.id_cart_item_product)
		
		LEFT JOIN 
		cart_discount_item_product AS sp_discount
		ON
		(sp.id = sp_discount.id_cart_item_product) 	
		
		WHERE
		cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to delete product from cart.'."\r\n\r\n".$this->mysqli->error);		
		
		// delete discounts that no longer have products associated
		if (sizeof($discounts)) {
			/* Prepare the statement */
			if (!$stmt_count_linked_product = $this->mysqli->prepare('SELECT
			COUNT(cart_discount_item_product.id) AS total
			FROM
			cart_discount_item_product
			
			INNER JOIN
			(cart_item_product CROSS JOIN cart_item)
			ON
			(cart_discount_item_product.id_cart_item_product = cart_item_product.id AND cart_item_product.id_cart_item = cart_item.id)
			
			WHERE
			cart_discount_item_product.id_cart_discount = ?
			AND
			cart_item.id_cart_discount = 0')) throw new Exception('An error occured while trying to prepare count linked products statement.'."\r\n\r\n".$this->mysqli->error);																													
					
			/* Prepare the statement */
			if (!$stmt_product = $this->mysqli->prepare('SELECT
			cart_item.id
			FROM
			cart_item
			WHERE
			cart_item.id_cart_discount = ?
			ORDER BY 
			cart_item.id DESC
			LIMIT ?')) throw new Exception('An error occured while trying to prepare get buy and get products statement.'."\r\n\r\n".$this->mysqli->error);				
			
			/* Prepare the statement */
			if (!$stmt_count_buy_get_product = $this->mysqli->prepare('SELECT
			SUM(cart_item.qty) AS total
			FROM
			cart_item			
			INNER JOIN cart_item_product
			ON (cart_item.id = cart_item_product.id_cart_item)
			
			WHERE
			cart_item_product.id_product = ?
			AND
			cart_item.id_cart = "'.$this->id.'"
			AND
			cart_item.id_cart_discount = 0')) throw new Exception('An error occured while trying to prepare count linked products statement.'."\r\n\r\n".$this->mysqli->error);		
			
			/* Prepare the statement */
			if (!$stmt_count_product = $this->mysqli->prepare('SELECT
			COUNT(cart_item.id) AS total
			FROM
			cart_item
			WHERE
			cart_item.id_cart_discount = ?')) throw new Exception('An error occured while trying to prepare count extra product statement.'."\r\n\r\n".$this->mysqli->error);													
									
			foreach ($discounts as $row) {
				if (!$stmt_count_linked_product->bind_param("i", $row['id'])) throw new Exception('An error occured while trying to bind params to count linked products statement.'."\r\n\r\n".$this->mysqli->error);												
				
				/* Execute the statement */
				if (!$stmt_count_linked_product->execute()) throw new Exception('An error occured while trying to count linked products.'."\r\n\r\n".$this->mysqli->error);					
				
				/* store result */
				$stmt_count_linked_product->store_result();																											
											
				/* bind result variables */
				$stmt_count_linked_product->bind_result($total_linked);																				
		
				// fetch
				$stmt_count_linked_product->fetch();		
								
				// if buy and get
				if ($row['type'] == 2) {				
					// get how many of the product we bought in cart							
					if (!$stmt_count_buy_get_product->bind_param("i", $id_product)) throw new Exception('An error occured while trying to bind params to count linked products statement.'."\r\n\r\n".$this->mysqli->error);												
					
					/* Execute the statement */
					if (!$stmt_count_buy_get_product->execute()) throw new Exception('An error occured while trying to count linked products.'."\r\n\r\n".$this->mysqli->error);					
					
					/* store result */
					$stmt_count_buy_get_product->store_result();																											
												
					/* bind result variables */
					$stmt_count_buy_get_product->bind_result($total_qty);																				
			
					// fetch
					$stmt_count_buy_get_product->fetch();																	
					
					// if total qty meets min buy qty
					if ($total_qty >= $row['buy_x_qty']) {
						// get max y qty 
						$max_y_qty = floor($total_qty/$row['buy_x_qty'])*$row['get_y_qty'];
						
						// count how many y qty in cart
						if (!$stmt_count_product->bind_param("i", $row['id'])) throw new Exception('An error occured while trying to bind params to count extra products statement.'."\r\n\r\n".$this->mysqli->error);												
						
						/* Execute the statement */
						if (!$stmt_count_product->execute()) throw new Exception('An error occured while trying to count extra products.'."\r\n\r\n".$this->mysqli->error);					
						
						/* store result */
						$stmt_count_product->store_result();																											
													
						/* bind result variables */
						$stmt_count_product->bind_result($total_y_qty);																				
				
						// fetch
						$stmt_count_product->fetch();								
						
						// if we have extras
						$extra_y_qty = $total_y_qty-$max_y_qty;
						
						// remove them
						if ($extra_y_qty > 0) {											
							if (!$stmt_product->bind_param("ii", $row['id'], $extra_y_qty)) throw new Exception('An error occured while trying to bind params to get buy and get products statement.'."\r\n\r\n".$this->mysqli->error);												
							
							/* Execute the statement */
							if (!$stmt_product->execute()) throw new Exception('An error occured while trying to get buy and get products.'."\r\n\r\n".$this->mysqli->error);					
							
							/* store result */
							$stmt_product->store_result();																											
														
							/* bind result variables */
							$stmt_product->bind_result($p_id_cart_item);																				

							while ($stmt_product->fetch()) {
								$this->del_product($p_id_cart_item);
							}
						}
					// remove discount
					} else {
						$this->del_discount($row['id']);
					}
				} else if (!$total_linked) $this->del_discount($row['id']);
			}
			
			$stmt_count_linked_product->close();
			$stmt_product->close();
		}	
		
		// min qty check
		if (!$this->min_qty_met($id_product,0, $id_cart_item)) {
			if (!$result = $this->mysqli->query('SELECT id_cart_item FROM cart_item_product WHERE id_product = "'.$id_product.'" AND id != "'.$id_cart_item.'"')) throw new Exception('An error occured while trying to get products.');
			
			while ($row = $result->fetch_assoc()) $this->del_product($row['id_cart_item']);
		}					
	}
	
	public function upd_product($id_cart_item, $id_product,$id_product_variant=0,$qty=1,$products=array())
	{
		$id_cart_item=(int)$id_cart_item;
		$id_product=(int)$id_product;
		$id_product_variant=(int)$id_product_variant;
		$qty=(int)$qty;
		$qty=$qty?$qty:1;
		
		if (!$result = $this->mysqli->query('SELECT
		cart_item.id,
		cart_item.qty,
		cart_item.id_cart_discount,
		cart_item_product.id AS id_cart_item_product
		FROM 
		cart_item 
		INNER JOIN
		cart_item_product
		ON
		(cart_item.id = cart_item_product.id_cart_item)
		WHERE
		cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"
		LIMIT 1')) throw new Exception('An error occured while trying to get cart item.'."\r\n\r\n".$this->mysqli->error);
		
		if (!$result->num_rows) throw new Exception('An error occured while trying to get cart item.'."\r\n\r\n".$this->mysqli->error);
		$row = $result->fetch_assoc();
		$result->free();
		
		$extra_qty = ($qty > $row['qty']) ? $qty-$row['qty']:0;
				
		if ($row_product = $this->get_product_info($id_product,$id_product_variant)) {		
			// min qty check
			if (!$this->min_qty_met($id_product,$qty,$id_cart_item)) return false;
		
			// check product type
			switch ($row_product['product_type']) {
				// single
				case 0:		
					// if product is available, proceed
					if ($extra_qty && !$this->check_product_availability($id_product,$id_product_variant,$extra_qty)) return false;						
										
					// check if product is already in cart, if yes, update qty instead
					if (!$result_in_cart = $this->mysqli->query('SELECT 
					cart_item.id
					FROM
					cart_item
					INNER JOIN
					cart_item_product
					ON
					(cart_item.id = cart_item_product.id_cart_item)
					WHERE
					cart_item.id != "'.$this->mysqli->escape_string($id_cart_item).'"
					AND
					cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
					AND
					cart_item.id_cart_discount = 0
					AND
					cart_item_product.id_product = "'.$this->mysqli->escape_string($id_product).'" 
					AND
					cart_item_product.id_product_variant = "'.$this->mysqli->escape_string($id_product_variant).'" 
					LIMIT 1
					')) throw new Exception('An error occured while trying to check if product is in cart.'."\r\n\r\n".$this->mysqli->error);					
					
					// if we want to merge even discounted products, remove the id_cart_discount check
					if (!$row['id_cart_discount'] && $result_in_cart->num_rows) {
						$row_in_cart = $result_in_cart->fetch_assoc();
						$result_in_cart->free();
						
						$id_cart_item_exist = $row_in_cart['id'];
						
						// update qty
						if (!$this->mysqli->query('UPDATE 
						cart_item
						INNER JOIN
						cart_item_product
						ON
						(cart_item.id = cart_item_product.id_cart_item)
						SET 
						cart_item.qty = cart_item.qty+'.$this->mysqli->escape_string($qty).',
						cart_item_product.qty = cart_item.qty,
						cart_item_product.subtotal = (cart_item.qty*cart_item_product.sell_price)
						WHERE
						cart_item.id = "'.$this->mysqli->escape_string($id_cart_item_exist).'"')) throw new Exception('An error occured while trying to update item qty.'."\r\n\r\n".$this->mysqli->error);			
														
						// delete previous item 
						// if we delete the previous item from cart, we need to stop processing further.
						$this->del_product($id_cart_item);
					
						$id_cart_item = $id_cart_item_exist;					
					} else {
						// update qty
						if (!$this->mysqli->query('UPDATE 
						cart_item
						INNER JOIN
						cart_item_product
						ON
						(cart_item.id = cart_item_product.id_cart_item)
						SET 
						cart_item.qty = "'.$this->mysqli->escape_string($qty).'",
						cart_item_product.qty = cart_item.qty,
						cart_item_product.subtotal = (cart_item.qty*cart_item_product.sell_price),
						cart_item_product.id_product = "'.$this->mysqli->escape_string($id_product).'",
						cart_item_product.id_product_variant = "'.$this->mysqli->escape_string($id_product_variant).'"
						WHERE
						cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to update item.'."\r\n\r\n".$this->mysqli->error);										
					}		
					break;
				// combo
				case 1:
					// get product discount
					$discount_type = $row_product['discount_type'];
					$discount = $row_product['discount'];
					$discount_pc = 0;							
					$cost_price = 0;
					$price = 0;
					$sell_price = 0;
					$subtotal = 0;		

					/* Prepare the statement */		
					if (!$stmt_combo_product = $this->mysqli->prepare('SELECT 
					product_combo.id_combo_product				
					FROM
					product_combo 
					WHERE
					product_combo.id = ?')) throw new Exception('An error occured while trying to prepare get product id statement.'."\r\n\r\n".$this->mysqli->error);								
					
					/* Prepare the statement */		
					if (!$stmt_combo_product_variant = $this->mysqli->prepare('SELECT 
					product_combo.id_combo_product,
					product_combo_variant.id_product_variant
					FROM
					product_combo_variant 
					INNER JOIN 
					product_combo
					ON
					(product_combo_variant.id_product_combo = product_combo.id)
					WHERE
					product_combo_variant.id = ?')) throw new Exception('An error occured while trying to prepare get product variant id statement.'."\r\n\r\n".$this->mysqli->error);	
					
					foreach ($products as $id_product_combo => $row_sub_product) {
						if ($row_sub_product['id_product_combo_variant']) {
							if (!$stmt_combo_product_variant->bind_param("i", $row_sub_product['id_product_combo_variant'])) throw new Exception('An error occured while trying to bind params to get product variant id statement.'."\r\n\r\n".$this->mysqli->error);			
							
							/* Execute the statement */
							if (!$stmt_combo_product_variant->execute()) throw new Exception('An error occured while trying to get product variant id statement.'."\r\n\r\n".$this->mysqli->error);	
							
							/* store result */
							$stmt_combo_product_variant->store_result();													
												
							/* bind result variables */
							$stmt_combo_product_variant->bind_result($id_combo_product, $id_combo_product_variant);																											
								
							$stmt_combo_product_variant->fetch();
						} else {
							if (!$stmt_combo_product->bind_param("i", $row_sub_product['id'])) throw new Exception('An error occured while trying to bind params to get product id statement.'."\r\n\r\n".$this->mysqli->error);
							
							/* Execute the statement */
							if (!$stmt_combo_product->execute()) throw new Exception('An error occured while trying to get product id statement.'."\r\n\r\n".$this->mysqli->error);				

							/* store result */
							$stmt_combo_product->store_result();																											
									
							/* bind result variables */
							$stmt_combo_product->bind_result($id_combo_product);	
															
							$stmt_combo_product->fetch();		
														
							$id_combo_product_variant = 0;
						}												
						
						$sub_product_info = $this->get_product_info($id_combo_product, $id_combo_product_variant);	
						
						$qty_remaining = get_qty_remaining($id_combo_product, $id_combo_product_variant);

						// check if qty available for combo
						if ($qty_remaining >= 0 && $extra_qty && $extra_qty*$row_sub_product['qty'] > $qty_remaining) {
							$this->messages[] = $sub_product_info['name'].($id_combo_product_variant ? ' ('.$sub_product_info['variant'].')':'').' '. language('_include/classes/SC_Cart','LABEL_IN_COMBO_NOT_AVAILABLE');
						} else if ($qty_remaining >= 0 && $row_sub_product['add_qty'] > ($qty_remaining - ($extra_qty ? ($extra_qty*$row_sub_product['qty']):0))) {
							$this->messages[] = $sub_product_info['name'].($id_combo_product_variant ? ' ('.$sub_product_info['variant'].')':'').' '. language('_include/classes/SC_Cart','LABEL_ADDITIONAL_QTY_NOT_AVAILABLE');
						}
						
						$products[$id_product_combo]['info'] = $sub_product_info; 
						
						$cost_price += $row_sub_product['qty']*$sub_product_info['cost_price'];
						$price += $row_sub_product['qty']*$sub_product_info['price'];
						$sell_price += $row_sub_product['qty']*$sub_product_info['price'];
					}
					
					/* close statement */
					$stmt_combo_product->close();
					$stmt_combo_product_variant->close();
					
					if (sizeof($this->messages)) return false;											
					
					$joins = array();
					$where = array();
					
					$i=1;
					foreach ($products as $id_product_combo => $row_sub_product) {
						$joins[] = 'INNER JOIN 	
						cart_item_product AS cip'.$i.'
						ON
						(cart_item_product.id = cip'.$i.'.id_cart_item_product)';
						
						$where[] = 'cip'.$i.'.id_product = "'.$this->mysqli->escape_string($row_sub_product['info']['id']).'" 
						AND 
						cip'.$i.'.id_product_variant = "'.$this->mysqli->escape_string($row_sub_product['info']['id_product_variant']).'"
						AND
						cip'.$i.'.id_product_combo_product = "'.$this->mysqli->escape_string($id_product_combo).'"';
						
						++$i;
					}
												
					// check if product is already in cart, if yes, update qty instead
					if (!$result_in_cart = $this->mysqli->query('SELECT 
					cart_item.id
					FROM 
					cart_item
					INNER JOIN 
					cart_item_product
					ON
					(cart_item.id = cart_item_product.id_cart_item)	'.
					(sizeof($joins) ? implode(' ',$joins):'').'
					WHERE
					cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'" 
					AND
					cart_item.id != "'.$this->mysqli->escape_string($id_cart_item).'" '.
					(sizeof($where) ? ' AND '.implode(' AND ',$where):'').'	
					LIMIT 1')) throw new Exception('An error occured while trying to check if combination already in cart.'."\r\n\r\n".$this->mysqli->error);																				
					
					if ($result_in_cart->num_rows) {
						$row_in_cart = $result_in_cart->fetch_assoc();
						$result_in_cart->free();
						
						$id_cart_item_exist = $row_in_cart['id'];														
						
						// update qty
						if (!$this->mysqli->query('UPDATE 
						cart_item
						INNER JOIN
						cart_item_product
						ON
						(cart_item.id = cart_item_product.id_cart_item)
						SET 
						cart_item.qty = cart_item.qty+'.$this->mysqli->escape_string($qty).',
						cart_item_product.qty = cart_item.qty,
						cart_item_product.subtotal = (cart_item.qty*cart_item_product.sell_price)
						WHERE
						cart_item.id = "'.$this->mysqli->escape_string($id_cart_item_exist).'"')) throw new Exception('An error occured while trying to update item qty.'."\r\n\r\n".$this->mysqli->error);		
						
						$id_cart_item = $id_cart_item_exist;
					} else {
						// calculate discount
						switch ($discount_type) {
							// fixed
							case 0:								
								$discount_pc = $discount/$price;
								$sell_price = $price-$discount;
								break;
							// percent
							case 1:
								$discount_pc = $discount/100;
								$sell_price = $price-round($price*$discount_pc,2);
								break;
						}
						
						$subtotal = $qty*$sell_price;

						// upd product
						if (!$this->mysqli->query('UPDATE
						cart_item
						INNER JOIN							
						cart_item_product
						ON
						(cart_item.id = cart_item_product.id_cart_item)
						SET
						cart_item.qty = "'.$this->mysqli->escape_string($qty).'",
						cart_item_product.qty = cart_item.qty,
						cart_item_product.cost_price = "'.$this->mysqli->escape_string($cost_price).'",
						cart_item_product.price = "'.$this->mysqli->escape_string($price).'",
						cart_item_product.sell_price = "'.$this->mysqli->escape_string($sell_price).'",
						cart_item_product.subtotal = "'.$this->mysqli->escape_string($subtotal).'"
						WHERE
						cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to update product.'."\r\n\r\n".$this->mysqli->error);
						
						// update sub product
						/* Prepare the statement */		
						if (!$stmt_product = $this->mysqli->prepare('UPDATE
						cart_item_product
						SET							
						id_product_variant = ?,
						qty = ?,
						cost_price = ?,
						price = ?,
						sell_price = ?,
						subtotal = ?
						WHERE
						id = ?')) throw new Exception('An error occured while trying to prepare update sub product statement.'."\r\n\r\n".$this->mysqli->error);																																																	
																	
						// get all sub products 
						foreach ($products as $id_product_combo => $row_sub_product) {
							//$product_sell_price = $row_sub_product['info']['price'] - round($row_sub_product['info']['price']*$discount_pc,4);
							$product_sell_price = $row_sub_product['info']['price'];
							$product_subtotal = $row_sub_product['qty']*$product_sell_price;
													
							if (!$stmt_product->bind_param("iiddddi", $row_sub_product['info']['id_product_variant'], $row_sub_product['qty'], 
							$row_sub_product['info']['cost_price'], $row_sub_product['info']['price'], $product_sell_price, $product_subtotal, 
							$row_sub_product['id_cart_item_product'])) throw new Exception('An error occured while trying to bind params to update sub product statement.'."\r\n\r\n".$this->mysqli->error);
							
							/* Execute the statement */
							if (!$stmt_product->execute()) throw new Exception('An error occured while trying to update sub product.'."\r\n\r\n".$this->mysqli->error);
						}						
						
						/* close statement */
						$stmt_product->close();	
					}
					
					// add qty			
					// check if product is already in cart, if yes, update qty instead
					/* Prepare statement */
					if (!$result_in_cart_add_qty = $this->mysqli->prepare('SELECT 
					cart_item.id
					FROM
					cart_item
					INNER JOIN
					cart_item_product
					ON
					(cart_item.id = cart_item_product.id_cart_item)
					WHERE
					cart_item.id_cart = ?
					AND
					cart_item.id_cart_discount = 0
					AND
					cart_item_product.id_product = ?
					AND
					cart_item_product.id_product_variant = ?
					LIMIT 1')) throw new Exception('An error occured while trying to prepare product in cart statement.'."\r\n\r\n".$this->mysqli->error);													
												
					// add additional sub product
					// add additional item
					/* Prepare the statement */		
					if (!$stmt_add_item = $this->mysqli->prepare('INSERT INTO
					cart_item
					SET
					id_cart = ?,
					qty = ?')) throw new Exception('An error occured while trying to prepare add additional item statement.'."\r\n\r\n".$this->mysqli->error);											
					
					// add additional item
					/* Prepare the statement */		
					if (!$stmt_add_item_product = $this->mysqli->prepare('INSERT INTO
					cart_item_product
					SET
					id_cart_item = ?,
					id_product = ?,
					id_product_variant = ?,
					qty = ?,
					cost_price = ?,
					price = ?,
					sell_price = ?,
					subtotal = ?')) throw new Exception('An error occured while trying to prepare add additional item product statement.'."\r\n\r\n".$this->mysqli->error);									
					
					foreach ($products as $id_product_combo => $row_sub_product) {
						if ($row_sub_product['add_qty'] > 0) {
							if (!$result_in_cart_add_qty->bind_param("iii", $this->id, $row_sub_product['info']['id'], 
							$row_sub_product['info']['id_product_variant'])) throw new Exception('An error occured while trying to bind params to product in cart statement.'."\r\n\r\n".$this->mysqli->error);
							
							/* Execute the statement */
							if (!$result_in_cart_add_qty->execute()) throw new Exception('An error occured while trying to execute product in cart statement.'."\r\n\r\n".$this->mysqli->error);				

							/* store result */
							$result_in_cart_add_qty->store_result();																											
							
							if ($result_in_cart_add_qty->num_rows) {										
								/* bind result variables */
								$result_in_cart_add_qty->bind_result($id_cart_item_add_qty);	
																
								$result_in_cart_add_qty->fetch();		
								
								if (!$this->mysqli->query('UPDATE
								cart_item
								INNER JOIN
								cart_item_product
								ON
								(cart_item.id = cart_item_product.id_cart_item)
								SET 
								cart_item.qty = cart_item.qty+'.$this->mysqli->escape_string($row_sub_product['add_qty']).',
								cart_item_product.qty = cart_item.qty,
								cart_item_product.subtotal = (cart_item.qty*cart_item_product.sell_price)								
								WHERE
								cart_item.id = "'.$this->mysqli->escape_string($id_cart_item_add_qty).'"')) throw new Exception('An error occured while trying to update item add qty.'."\r\n\r\n".$this->mysqli->error);			
							} else {
								$add_product_subtotal = $row_sub_product['add_qty']*$row_sub_product['info']['sell_price'];		
								
								if (!$stmt_add_item->bind_param("ii", $this->id, $row_sub_product['add_qty'])) throw new Exception('An error occured while trying to bind params to add additional item statement.'."\r\n\r\n".$this->mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_add_item->execute()) throw new Exception('An error occured while trying to add additional item.'."\r\n\r\n".$this->mysqli->error);												
								
								$add_product_id_cart_item = $this->mysqli->insert_id;
								
								if (!$stmt_add_item_product->bind_param("iiiidddd", $add_product_id_cart_item, $row_sub_product['info']['id'], 
								$row_sub_product['info']['id_product_variant'], $row_sub_product['add_qty'], $row_sub_product['info']['cost_price'], 
								$row_sub_product['info']['price'], $row_sub_product['info']['sell_price'], $add_product_subtotal)) throw new Exception('An error occured while trying to bind params to add additional item product statement.'."\r\n\r\n".$this->mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_add_item_product->execute()) throw new Exception('An error occured while trying to add additional item product.'."\r\n\r\n".$this->mysqli->error);	
							}													
						}
					}
			
					/* close statement */
					$stmt_add_item->close();
					$stmt_add_item_product->close();						
					break;
				// bundle
				case 2:
					// check product availability, different from single and combo
				
					$cost_price = 0;
					$price = 0;
					$sell_price = 0;
					$subtotal = 0;						
				
					// get required group and return false if not present
					
					if (!$result = $this->mysqli->query('SELECT 
					product_bundled_product_group.id,
					product_bundled_product_group_description.name
					FROM
					product_bundled_product_group
					INNER JOIN 
					product_bundled_product_group_description
					ON
					(product_bundled_product_group.id = product_bundled_product_group_description.id_product_bundled_product_group AND product_bundled_product_group_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
					WHERE
					product_bundled_product_group.id_product = "'.$this->mysqli->escape_string($id_product).'"
					AND
					product_bundled_product_group.required = 1')) throw new Exception('An error occured while trying to get required groups.'."\r\n\r\n".$this->mysqli->error);	
					while ($row_group = $result->fetch_assoc()) {
						if (!isset($products[$row_group['id']])) {
							$this->messages[] = $row_group['name'].' '. language('_include/classes/SC_Cart','LABEL_IS_REQUIRED');	
						}
					}
					$result->free();
					
					if (sizeof($this->messages)) return false;													
										
					// loop through each product and check availability
					if (sizeof($products)) {
						// get price 
						/* Prepare the statement */		
						if (!$stmt_sub_product_info = $this->mysqli->prepare('SELECT 
						(CASE 
							WHEN product_variant.id IS NOT NULL
								THEN
									product.cost_price+product_variant.cost_price
							ELSE
								product.cost_price
						END) AS cost_price,
						(CASE 
							WHEN product_variant.id IS NOT NULL
								THEN (CASE product_variant.price_type
									WHEN 0 THEN
										product.price+product_variant.price
									WHEN 1 THEN
										product.price+ROUND((product.price*product_variant.price)/100,2)
								END)									
							ELSE
								product.price
						END) AS price,
						get_bundle_product_current_price(product_bundled_product_group_product.id,"'.$this->mysqli->escape_string($this->id_customer_type).'") AS sell_price,
						product_bundled_product_group_product.id_product,
						product_bundled_product_group_product.id_product_variant,
						product_description.name,
						GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant_name
						FROM
						
						product_bundled_product_group_product
						INNER JOIN
						product_bundled_product_group
						ON
						(product_bundled_product_group_product.id_product_bundled_product_group = product_bundled_product_group.id)
						INNER JOIN
						(product CROSS JOIN product_description)
						ON
						(product_bundled_product_group_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
						LEFT JOIN
						(product_variant
						CROSS JOIN product_variant_option 
						CROSS JOIN product_variant_group 
						CROSS JOIN product_variant_group_option 
						CROSS JOIN product_variant_group_option_description)						
						ON
						(product_bundled_product_group_product.id_product_variant = product_variant.id
						AND product_variant.id = product_variant_option.id_product_variant 
						AND product_variant_option.id_product_variant_group = product_variant_group.id 
						AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
						AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
						AND product_variant_group_option_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
						
						INNER JOIN
						product AS product_info
						ON
						(product_bundled_product_group.id_product = product_info.id)						
						
						WHERE
						product_bundled_product_group_product.id = ?
						GROUP BY 
						product_bundled_product_group_product.id')) throw new Exception('An error occured while trying to prepare get product price statement.'."\r\n\r\n".$this->mysqli->error);																																								
						
						foreach ($products as $id_product_bundled_product_group => $rows) {
							if (isset($rows['id']) && sizeof($rows['id'])) { 
								foreach ($rows['id'] as $key => $id_product_bundled_product_group_product) {		
									if (!$stmt_sub_product_info->bind_param("i", $id_product_bundled_product_group_product)) throw new Exception('An error occured while trying to bind params to get sub product info statement.'."\r\n\r\n".$this->mysqli->error);
	
									/* Execute the statement */
									$stmt_sub_product_info->execute();
									
									/* store result */
									$stmt_sub_product_info->store_result();		
									
									if ($stmt_sub_product_info->num_rows) {																																				
										/* bind result variables */
										$stmt_sub_product_info->bind_result($sub_product_cost_price, $sub_product_price, $sub_product_sell_price, $sub_product_id_product,
										$sub_product_id_product_variant, $sub_product_name, $sub_product_variant_name);	
																		
										$stmt_sub_product_info->fetch();
										
										$sub_product_qty = $rows['qty'][$id_product_bundled_product_group_product] > 0 ? $rows['qty'][$id_product_bundled_product_group_product]:1;		
										$sub_product_qty = $qty*$sub_product_qty;			
										
										// check if available			
										if (!$this->check_product_availability($sub_product_id_product,$sub_product_id_product_variant,$sub_product_qty)) {
											//$this->messages[] = $sub_product_name.($sub_product_id_product_variant ? ' ('.$sub_product_variant_name.')':'').' is not available for requested qty.';								
										} else {								
											$products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product] = array(
												'id_product' => $sub_product_id_product,
												'id_product_variant' => $sub_product_id_product_variant,
												'qty' => $sub_product_qty,
												'cost_price' => $sub_product_cost_price,
												'price' => $sub_product_price,
												'sell_price' => $sub_product_sell_price,
												'subtotal' => $sub_product_qty*$sub_product_sell_price,
											);
											
											$cost_price += $sub_product_qty*$sub_product_cost_price;
											$price += $sub_product_qty*$sub_product_price;
											$sell_price += $sub_product_qty*$sub_product_sell_price;
										}
									// if don't exist remove from products
									} else {
										unset($products[$id_product_bundled_product_group]['id'][$key]);
									}
								}
							}
						}	
						
						if (sizeof($this->messages)) return false;											

						/* close statement */
						$stmt_sub_product_info->close();							
						
						$subtotal = $qty*$sell_price;												
						
						// update
						if (!$this->mysqli->query('UPDATE
						cart_item
						INNER JOIN
						cart_item_product
						ON
						(cart_item.id = cart_item_product.id_cart_item)
						SET
						cart_item.qty = "'.$this->mysqli->escape_string($qty).'",
						cart_item_product.qty = cart_item.qty,
						cart_item_product.cost_price = "'.$this->mysqli->escape_string($cost_price).'",
						cart_item_product.price = "'.$this->mysqli->escape_string($price).'",
						cart_item_product.sell_price = "'.$this->mysqli->escape_string($sell_price).'",
						cart_item_product.subtotal = "'.$this->mysqli->escape_string($subtotal).'"
						WHERE
						cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"')) throw new Exception('An error occured while trying to add product.'."\r\n\r\n".$this->mysqli->error);		
						
						// update sub
						if (!$stmt_upd_sub_product = $this->mysqli->prepare('UPDATE
						cart_item_product
						SET
						id_product = ?,
						id_product_variant = ?,
						id_product_bundled_product_group_product = ?,				
						qty = ?,
						cost_price = ?,
						price = ?,
						sell_price = ?,
						subtotal = ?
						WHERE
						id = ?')) throw new Exception('An error occured while trying to prepare update sub product statement.'."\r\n\r\n".$this->mysqli->error);																									
						
						// add sub
						if (!$stmt_add_sub_product = $this->mysqli->prepare('INSERT INTO
						cart_item_product
						SET
						id_cart_item_product = ?,
						id_product = ?,
						id_product_variant = ?,
						id_product_bundled_product_group_product = ?,				
						qty = ?,
						cost_price = ?,
						price = ?,
						sell_price = ?,
						subtotal = ?')) throw new Exception('An error occured while trying to prepare add sub product statement.'."\r\n\r\n".$this->mysqli->error);												
						
						foreach ($products as $id_product_bundled_product_group => $rows) {
							if (isset($rows['id']) && sizeof($rows['id'])) { 
								foreach ($rows['id'] as $key => $id_product_bundled_product_group_product) {	
									$sub_id_cart_item_product =  $products[$id_product_bundled_product_group]['id_cart_item_product'][$id_product_bundled_product_group_product];
								
									$sub_product_id_product = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['id_product'];
									$sub_product_id_product_variant = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['id_product_variant'];
									$sub_product_qty = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['qty'];
									
									$sub_product_cost_price = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['cost_price'];
									$sub_product_price = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['price'];
									$sub_product_sell_price = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['sell_price'];
									$sub_product_subtotal = $products[$id_product_bundled_product_group]['info'][$id_product_bundled_product_group_product]['subtotal'];
									if ($sub_id_cart_item_product) {								
										if (!$stmt_upd_sub_product->bind_param("iiiiddddi", $sub_product_id_product, $sub_product_id_product_variant, 
										$id_product_bundled_product_group_product, $sub_product_qty, $sub_product_cost_price, $sub_product_price, 
										$sub_product_sell_price, $sub_product_subtotal, $sub_id_cart_item_product)) throw new Exception('An error occured while trying to bind params to update sub product statement.'."\r\n\r\n".$this->mysqli->error);		
										
										/* Execute the statement */
										if (!$stmt_upd_sub_product->execute()) throw new Exception('An error occured while trying to update sub product.'."\r\n\r\n".$this->mysqli->error);					
									} else {
										if (!$stmt_add_sub_product->bind_param("iiiiidddd", $row['id_cart_item_product'], $sub_product_id_product, 
										$sub_product_id_product_variant, $id_product_bundled_product_group_product, $sub_product_qty, 
										$sub_product_cost_price, $sub_product_price, $sub_product_sell_price, $sub_product_subtotal)) throw new Exception('An error occured while trying to bind params to add sub product statement.'."\r\n\r\n".$this->mysqli->error);		
										
										/* Execute the statement */
										if (!$stmt_add_sub_product->execute()) throw new Exception('An error occured while trying to add sub product.'."\r\n\r\n".$this->mysqli->error);						
									}
								}
							}
						}
						
						// get current bundle product options in cart which don't appear in the submitted products and remove them
						if (!$result = $this->mysqli->query('SELECT 
						cart_item_product.id AS id_cart_item_product,
						product_bundled_product_group_product.id,
						product_bundled_product_group_product.id_product_bundled_product_group
						FROM 
						cart_item_product
						INNER JOIN 
						product_bundled_product_group_product
						ON
						(cart_item_product.id_product_bundled_product_group_product = product_bundled_product_group_product.id) 
						WHERE
						cart_item_product.id_cart_item_product = "'.$this->mysqli->escape_string($row['id_cart_item_product']).'"')) throw new Exception('An error occured while trying to get current bundle product options.'."\r\n\r\n".$this->mysqli->error);	
												
						/* Prepare statement */
						if (!$stmt_del_sp = $this->mysqli->prepare('DELETE FROM
						cart_item_product,
						cart_item_product_tax,
						cart_discount_item_product
						USING
						cart_item_product
						LEFT JOIN
						cart_item_product_tax
						ON
						(cart_item_product.id = cart_item_product_tax.id_cart_item_product)
						LEFT JOIN
						cart_discount_item_product
						ON
						(cart_item_product.id = cart_discount_item_product.id_cart_item_product)
						WHERE
						cart_item_product.id = ?')) throw new Exception('An error occured while trying to prepare delete sub product statement.'."\r\n\r\n".$this->mysqli->error);					
						
						$current_bundle_product = array();
						while ($row = $result->fetch_assoc()) {
							if (!isset($products[$row['id_product_bundled_product_group']]['id']) || (isset($products[$row['id_product_bundled_product_group']]['id']) && is_array($products[$row['id_product_bundled_product_group']]['id']) && !in_array($row['id'],$products[$row['id_product_bundled_product_group']]['id']))) {
								if (!$stmt_del_sp->bind_param("i", $row['id_cart_item_product'])) throw new Exception('An error occured while trying to bind params to delete sub product statement.'."\r\n\r\n".$this->mysqli->error);		
								
								/* Execute the statement */
								if (!$stmt_del_sp->execute()) throw new Exception('An error occured while trying to delete sub product.'."\r\n\r\n".$this->mysqli->error);									
							}
						}
						$result->free();	
						$stmt_del_sp->close();								
					}
					break;
			}
					
			// apply discount
			$this->apply_product_discount($id_cart_item);			
			
			return $id_cart_item;
		}
		
		return false;
	}	
	
	public function upd_option($id_cart_item, $id_options_group, $options=array())
	{		
		$id_cart_item=(int)$id_cart_item;
		$id_options_group=(int)$id_options_group;
		$current_datetime = date('Y-m-d H:i:s');
		
		if (isset($options['id']) && is_array($options['id']) && sizeof($options['id'])) {
			/* Prepare the statement */		
			if (!$stmt_product_info = $this->mysqli->prepare('SELECT
			cart_item_product.id_product,
			cart_item_product.id_product_variant,
			get_product_cart_price(product.sell_price, product_variant.price_type, product_variant.price, product.id, product.product_type, cart_item.id_cart, 1) AS sell_price,
			cart_item.qty
			FROM
			cart_item
			INNER JOIN
			(cart_item_product CROSS JOIN product)
			ON
			(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id)
			LEFT JOIN
			product_variant
			ON
			(cart_item_product.id_product_variant = product_variant.id)
			INNER JOIN 
			cart
			ON
			(cart_item.id_cart = cart.id)
			WHERE
			cart_item.id = ?
			LIMIT 1')) throw new Exception('An error occured while trying to prepare get product info statement.'."\r\n\r\n".$this->mysqli->error);		
			
			/* Prepare the statement */		
			if (!$stmt_option_info = $this->mysqli->prepare('SELECT 
			options.cost_price,
			options.price,
			options.price_type,
			IF("'.$this->mysqli->escape_string($current_datetime).'" BETWEEN options.special_price_from_date AND options.special_price_to_date,options.special_price,options.price) AS sell_price,
			options.special_price_from_date,
			options.special_price_to_date,
			options_group_description.name AS option_group_name,
			options_description.name AS option_name,
			product_options_group.id AS id_product_options_group,
			IF(options_group.input_type < 5,options.track_inventory,0) AS track_inventory,
			option_qty_in_stock(options.id) AS qty_in_stock,
			cart_item_option.qty
			FROM 
			options 
			INNER JOIN
			options_description
			ON
			(options.id = options_description.id_options AND options_description.language_code = ?)
			INNER JOIN
			(options_group CROSS JOIN options_group_description)
			ON
			(options.id_options_group = options_group.id AND options_group.id = options_group_description.id_options_group AND options_group_description.language_code = options_description.language_code)
			INNER JOIN
			product_options_group 
			ON
			(options_group.id = product_options_group.id_options_group)
			
			LEFT JOIN 
			cart_item_option
			ON
			(cart_item_option.id_options = options.id AND cart_item_option.id = ?)
			
			WHERE
			options.id = ?
			AND
			product_options_group.id_product = ?
			LIMIT 1')) throw new Exception('An error occured while trying to prepare get option info statement.'."\r\n\r\n".$this->mysqli->error);		
			
			/* Prepare the statement */	
			if (!$stmt_add_option = $this->mysqli->prepare('INSERT INTO
			cart_item_option 
			SET
			id_cart_item = ?,
			id_product_options_group = ?,
			id_options_group = ?,
			id_options = ?,
			qty = ?,
			cost_price = ?,
			price = ?,
			sell_price = ?,
			special_price_start_date = ?,
			special_price_end_date = ?,
			textfield = ?,
			textarea = ?,
			filename_tmp = ?,
			filename = ?,
			date_start = ?,
			date_end = ?,
			datetime_start = ?,
			datetime_end = ?,
			time_start = ?,
			time_end = ?,						
			subtotal = ?')) throw new Exception('An error occured while trying to prepare add option statement.'."\r\n\r\n".$this->mysqli->error);									
			
			/* Prepare the statement */	
			if (!$stmt_upd_option = $this->mysqli->prepare('UPDATE
			cart_item_option 
			SET
			id_options = ?,
			qty = ?,
			cost_price = ?,
			price = ?,
			sell_price = ?,
			special_price_start_date = ?,
			special_price_end_date = ?,
			textfield = ?,
			textarea = ?,
			filename_tmp = ?,
			filename = ?,
			date_start = ?,
			date_end = ?,
			datetime_start = ?,
			datetime_end = ?,
			time_start = ?,
			time_end = ?,						
			subtotal = ?
			WHERE
			id = ?')) throw new Exception('An error occured while trying to prepare update option statement.'."\r\n\r\n".$this->mysqli->error);						
			
			foreach ($options['id'] as $id_options) {
				$id_cart_item_option = $options['id_cart_item_option'][$id_options];
				
				$textfield='';
				$textarea='';
				$file=array();
				$file['tmp_name']='';
				$file['name']='';
				$date='';
				$date_to='';
				$datetime='';
				$datetime_to='';
				$time='';
				$time_to='';
				
				// option type
				switch ($options['input_type']) {
					// dropdown 
					case 0:
						$qty = 1;
						break;
					// radio
					case 1:
					// checkbox
					case 3:					
						$qty = $options['qty'][$id_options] ? $options['qty'][$id_options]:1;
						break;
					// multiselect
					case 4:
						$qty = 1;
						break;					
					// textfield
					case 5:
						$qty = 1;
						$textfield = $options['textfield'][$id_options];
						break;
					// textarea
					case 6:
						$qty = 1;
						$textarea = $options['textarea'][$id_options];
						break;
					// file
					case 7:
						$qty = 1;
						$file = $options['file'][$id_options];
						break;
					// date
					case 8:
						$qty = 1;
						$date = $options['date'][$id_options];
						$date_to = $options['date_to'][$id_options];					
						break;
					// date & time
					case 9:
						$qty = 1;
						$datetime = $options['datetime'][$id_options];
						$datetime_to = $options['datetime'][$id_options];
						break;
					// time
					case 10:
						$qty = 1;
						$time = $options['time'][$id_options];
						$time_to = $options['time_to'][$id_options];
						break;				
				}
				
				// get product info
				if (!$stmt_product_info->bind_param("i", $id_cart_item)) throw new Exception('An error occured while trying to bind params to get product info statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_product_info->execute()) throw new Exception('An error occured while trying to get product info statement.'."\r\n\r\n".$this->mysqli->error);	
				
				/* store result */
				$stmt_product_info->store_result();					
				
				if (!$stmt_product_info->num_rows) throw new Exception('An error occured while trying to get product info.'."\r\n\r\n".$this->mysqli->error);		
					
				/* bind result variables */
				$stmt_product_info->bind_result($id_product, $id_product_variant, $product_sell_price, $product_qty);		
				
				$stmt_product_info->fetch();	
				
				// get options info
				if (!$stmt_option_info->bind_param("siii", $_SESSION['customer']['language'], $id_cart_item_option, $id_options, $id_product)) throw new Exception('An error occured while trying to bind params to get option info statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_option_info->execute()) throw new Exception('An error occured while trying to get option info.'."\r\n\r\n".$this->mysqli->error);	
				
				/* store result */
				$stmt_option_info->store_result();					
				
				if (!$stmt_option_info->num_rows) throw new Exception('An error occured while trying to get option info.'."\r\n\r\n".$this->mysqli->error);		
					
				/* bind result variables */
				$stmt_option_info->bind_result($cost_price, $price, $price_type, $sell_price, $special_price_from_date, $special_price_to_date,
				$option_group_name, $option_name, $id_product_options_group, $track_inventory, $qty_in_stock, $current_qty);		
				
				$stmt_option_info->fetch();		
									
				// check availability				
				if ($this->check_option_availability($id_cart_item,$id_product,$id_options,$qty) && (!$track_inventory || $track_inventory && ($id_cart_item_option && $qty_in_stock >= 0 || !$id_cart_item_option && $qty_in_stock > 0))) {
					switch ($price_type) {
						// percent
						case 1:
							$price = round(($product_sell_price*$price)/100,2);
							$sell_price = round(($product_sell_price*$sell_price)/100,2);
							break;	
					}
					
					$subtotal = $product_qty*$qty*$sell_price;
					
					if (!$id_cart_item_option) { 
						// add option
						if (!$stmt_add_option->bind_param("iiiiidddssssssssssssd", $id_cart_item, $id_product_options_group, $id_options_group,
						$id_options, $qty, $cost_price, $price, $sell_price, $special_price_from_date, $special_price_to_date, $textfield,
						$textarea, $file['tmp_name'], $file['name'], $date, $date_to, $datetime, $datetime_to, $time, $time_to, $subtotal)) throw new Exception('An error occured while trying to bind params to add option statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_add_option->execute()) throw new Exception('An error occured while trying to add option.'."\r\n\r\n".$this->mysqli->error);		
					} else {
						
						// update option
						if (!$stmt_upd_option->bind_param("iidddssssssssssssdi", $id_options, $qty, $cost_price, $price, $sell_price, 
						$special_price_from_date, $special_price_to_date, $textfield, $textarea, $file['tmp_name'], $file['name'], $date, $date_to, 
						$datetime, $datetime_to, $time, $time_to, $subtotal, $id_cart_item_option)) throw new Exception('An error occured while trying to bind params to update option statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_upd_option->execute()) throw new Exception('An error occured while trying to update option.'."\r\n\r\n".$this->mysqli->error);	
					}
				} else {
					//$this->messages[] = $option_group_name.', '.$option_name.' '. language('product','ALERT_NOT_ENOUGH_IN_STOCK'));
					
					return false;					
				}				
			}
		}
			
		return false;		
	}
	
	public function upd_product_qty($id_cart_item, $qty=1)
	{
		$id_cart_item=(int)$id_cart_item;
		$qty=(int)$qty;
		
		if (!$qty) {			
			$this->del_product($id_cart_item);					
			
			return true;	
		} else {
			// get current qty in cart			
			if ($result = $this->mysqli->query('SELECT
			cart_item.qty,
			cart_item_product.id,
			cart_item_product.id_product,
			cart_item_product.id_product_variant,
			cart_item_product.id_product_related,
			product.product_type,			
			product_description.name,
			GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
			product.max_qty,
			(SELECT 
				MIN(rebate_coupon.max_qty_allowed)
				FROM 
				cart_discount 
				INNER JOIN
				cart_discount_item_product
				ON
				(cart_discount.id = cart_discount_item_product.id_cart_discount)
				INNER JOIN
				rebate_coupon
				ON
				(cart_discount.id_rebate_coupon = rebate_coupon.id) 
				WHERE
				cart_discount.id_cart = cart.id
				AND
				cart_discount_item_product.id_cart_item_product = cart_item_product.id
				AND
				(rebate_coupon.type = 0 OR rebate_coupon.type = 2)
				AND
				rebate_coupon.max_qty_allowed > 0) AS max_qty_allowed
			FROM
			cart
			INNER JOIN			
			cart_item
			ON
			(cart.id = cart_item.id_cart)
			INNER JOIN
			(cart_item_product CROSS JOIN product CROSS JOIN product_description)
			ON
			(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
			
			LEFT JOIN
			(product_variant
			CROSS JOIN
			product_variant_option 
			CROSS JOIN 
			product_variant_group 
			CROSS JOIN 
			product_variant_group_option 
			CROSS JOIN 
			product_variant_group_option_description)
			ON 
			(
				cart_item_product.id_product_variant = product_variant.id
				AND
				product_variant.id = product_variant_option.id_product_variant 
				AND 
				product_variant_option.id_product_variant_group = product_variant_group.id 
				AND 
				product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
				AND 
				product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
				AND 
				product_variant_group_option_description.language_code = product_description.language_code
			)		
			WHERE
			cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"
			GROUP BY 
			cart_item.id
			LIMIT 1')) {
				if ($result->num_rows) {
					$row = $result->fetch_assoc();
					$result->free();
					
					// min qty check
					if (!$this->min_qty_met($row['id_product'],$qty,$id_cart_item)) return false;					
					
					// check how many qty more we are requesting
					$extra_qty = $row['qty']-$qty;
					$extra_qty = $extra_qty < 0 ? abs($extra_qty):0;
					
					// if we have an extra then check if available
					if ($extra_qty > 0) {
						switch ($row['product_type']) {
							// single
							case 0:
								if (!$this->check_product_availability($row['id_product'],$row['id_product_variant'],$extra_qty)) return false;
								break;
							// combo deal
							case 1:
							// bundle
							case 2:
								if ($result_product = $this->mysqli->query('SELECT 
								cart_item_product.id_product,
								cart_item_product.id_product_variant,
								cart_item_product.qty,
								product.track_inventory,
								product_description.name,
								GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant
								FROM
								cart_item_product								
								INNER JOIN
								(product CROSS JOIN product_description)
								ON
								(cart_item_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
								
								LEFT JOIN 
								(product_variant 
								CROSS JOIN product_variant_option 
								CROSS JOIN product_variant_group 
								CROSS JOIN product_variant_group_option 
								CROSS JOIN product_variant_group_option_description
								CROSS JOIN product_variant_group_description)
								
								ON
								(cart_item_product.id_product = product_variant.id_product 
								AND
								cart_item_product.id_product_variant = product_variant.id
								AND product_variant.id = product_variant_option.id_product_variant 
								AND product_variant_option.id_product_variant_group = product_variant_group.id 
								AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
								AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
								AND product_variant_group_option_description.language_code = product_description.language_code
								AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
								AND product_variant_group_description.language_code = product_description.language_code)
								WHERE
								cart_item_product.id_cart_item_product = "'.$this->mysqli->escape_string($row['id']).'"
								GROUP BY 
								cart_item_product.id')) {
									if ($result_product->num_rows) {
										/* Prepare the statement */
										if (!$stmt_in_stock = $this->mysqli->prepare('SELECT
										is_product_in_stock(?,?,0) AS in_stock')) throw new Exception('An error occured while trying to check product stock statement.'."\r\n\r\n".$this->mysqli->error);												
										
										while ($row_product = $result_product->fetch_assoc()) {
											//if (!$this->check_product_availability($row_product['id_product'],$row_product['id_product_variant'],$extra_qty*$row_product['qty'])) return false;	

											if (!$stmt_in_stock->bind_param("ii", $row_product['id_product'], $row_product['id_product_variant'])) throw new Exception('An error occured while trying to bind params to check product stock statement.'."\r\n\r\n".$this->mysqli->error);			
											
											/* Execute the statement */
											if (!$stmt_in_stock->execute()) throw new Exception('An error occured while trying to check product stock.'."\r\n\r\n".$this->mysqli->error);	
											
											/* store result */
											$stmt_in_stock->store_result();													
																
											/* bind result variables */
											$stmt_in_stock->bind_result($in_stock);																											
												
											$stmt_in_stock->fetch();											
											
											$qty_remaining = get_qty_remaining($row_product['id_product'], $row_product['id_product_variant']);
											
											// if sub product is in stock, and we track inventory and the desired qty exceeds qty remaining in stock
											if (!$in_stock || $in_stock && $qty_remaining >= 0 && $row_product['track_inventory'] && $extra_qty*$row_product['qty'] > $qty_remaining) {
												$this->messages[] = $row_product['name'].($row_product['id_product_variant'] ? ' ('.$row_product['variant'].')':'').' '. language('product','ALERT_NOT_ENOUGH_IN_STOCK');
											}
										}
										
										if (sizeof($this->messages)) return false;
										
										$stmt_in_stock->close();
									} else {
										throw new Exception('An error occured while trying to get sub products.'."\r\n\r\n".$this->mysqli->error);		
									}
								} else {
									throw new Exception('An error occured while trying to get sub products.'."\r\n\r\n".$this->mysqli->error);			
								}							
								break;
						}			
							
						// check if max qty 
						if ($row['max_qty'] > 0 || $row['max_qty_allowed'] > 0) {
							// get total qty in cart 
							if (!$result_qty_in_cart = $this->mysqli->query('SELECT 
							SUM(IF(cart.id IS NOT NULL,cart_item_product.qty,ci.qty*cart_item_product.qty)) AS qty
							FROM
							cart_item_product
							LEFT JOIN
							(cart_item CROSS JOIN cart)
							ON
							(cart_item_product.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
							
							LEFT JOIN
							(cart_item_product AS cip CROSS JOIN cart_item AS ci CROSS JOIN cart AS c)
							ON
							(cart_item_product.id_cart_item_product = cip.id AND cip.id_cart_item = ci.id AND ci.id_cart = c.id)
							
							WHERE
							cart_item_product.id_product = "'.$this->mysqli->escape_string($row['id_product']).'"
							AND
							cart_item_product.id_product_variant = "'.$this->mysqli->escape_string($row['id_product_variant']).'"
							AND
							(cart.id IS NOT NULL AND cart.id = "'.$this->mysqli->escape_string($this->id).'" OR c.id IS NOT NULL AND c.id = "'.$this->mysqli->escape_string($this->id).'")')) throw new Exception('An error occured while trying to get total qty in cart.'."\r\n\r\n".$this->mysqli->error);			
							
							$row_qty_in_cart = $result_qty_in_cart->fetch_assoc();					
							$result_qty_in_cart->free();
							$total_qty = $row_qty_in_cart['qty']+$extra_qty;	
							$max_qty = $row['max_qty'];
							$max_qty = ($row['max_qty_allowed'] != 0 && (!$max_qty || $max_qty != 0 && $row['max_qty_allowed'] < $max_qty)) ? $row['max_qty_allowed']:$max_qty;
							
							if ($total_qty > $max_qty) {
								$this->messages[] = $row['name'].($row['id_product_variant'] ? ' ('.$row['variant'].')':'').' ' .language('_include/classes/SC_Cart','LABEL_MAX_PER_CUSTOMER',array(0=>$max_qty));
								return false;
							}													
						}	
						
						// check option availability
						if (!$result = $this->mysqli->query('SELECT
						cart_item_option.id_options,
						cart_item_option.qty,
						option_qty_in_stock(cart_item_option.id_options) AS qty_in_stock,
						CONCAT(options_group_description.name,": ",options_description.name) AS name,
						options.track_inventory
						FROM
						cart_item_option
						
						INNER JOIN 
						options
						ON
						(cart_item_option.id_options = options.id)
						
						INNER JOIN
						(options_description CROSS JOIN options_group_description)
						ON
						(cart_item_option.id_options = options_description.id_options AND options_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'" AND cart_item_option.id_options_group = options_group_description.id_options_group AND options_group_description.language_code = options_description.language_code)							
						WHERE
						cart_item_option.id_cart_item = "'.$this->mysqli->escape_string($id_cart_item).'"
						AND
						options.track_inventory = 1')) throw new Exception('An error occured while trying to get total qty in cart.'."\r\n\r\n".$this->mysqli->error);	
						
						while ($row = $result->fetch_assoc()) {
							if ($extra_qty*$row['qty'] > $row['qty_in_stock']) $this->messages[] = $row['name'].' '. language('product','ALERT_NOT_ENOUGH_IN_STOCK');
						}		
						$result->free();		
						
						if (sizeof($this->messages)) return false;											
					}				

					// update qty
					if (!$this->mysqli->query('UPDATE
					cart_item
					INNER JOIN 
					cart_item_product
					ON
					(cart_item.id = cart_item_product.id_cart_item)
					SET
					cart_item.qty = "'.$this->mysqli->escape_string($qty).'",
					cart_item_product.qty = "'.$this->mysqli->escape_string($qty).'",
					cart_item_product.subtotal = ("'.$this->mysqli->escape_string($qty).'"*cart_item_product.sell_price)
					WHERE
					cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"')) {
						throw new Exception('An error occured while trying to update product qty.'."\r\n\r\n".$this->mysqli->error);		
					}			
					
					$this->apply_product_discount($id_cart_item);
							
					return true;		
				} 
			} else {
				throw new Exception('An error occured while trying to check current product qty in cart.'."\r\n\r\n".$this->mysqli->error);		
			}
		}
		
		return false;
	}	

	public function add_option($id_cart_item, $id_options_group, $options=array())
	{		
		$id_cart_item=(int)$id_cart_item;
		$id_options_group=(int)$id_options_group;
		$current_datetime = date('Y-m-d H:i:s');
		
		if (isset($options['id']) && is_array($options['id']) && sizeof($options['id'])) {
			foreach ($options['id'] as $id_options) {
				// option type
				switch ($options['input_type']) {
					// dropdown 
					case 0:
						$qty = 1;
						break;
					// radio
					case 1:
					// checkbox
					case 3:					
						$qty = $options['qty'][$id_options] ? $options['qty'][$id_options]:1;
						break;
					// multiselect
					case 4:
						$qty = 1;
						break;					
					// textfield
					case 5:
						$qty = 1;
						$textfield = $options['textfield'][$id_options];
						break;
					// textarea
					case 6:
						$qty = 1;
						$textarea = $options['textarea'][$id_options];
						break;
					// file
					case 7:
						$qty = 1;
						$file = $options['file'][$id_options];
						break;
					// date
					case 8:
						$qty = 1;
						$date = $options['date'][$id_options];
						$date_to = $options['date_to'][$id_options];					
						break;
					// date & time
					case 9:
						$qty = 1;
						$datetime = $options['datetime'][$id_options];
						$datetime_to = $options['datetime'][$id_options];
						break;
					// time
					case 10:
						$qty = 1;
						$time = $options['time'][$id_options];
						$time_to = $options['time_to'][$id_options];
						break;				
				}
				
				// get product info
				if (!$result = $this->mysqli->query('SELECT
				cart_item_product.id_product,
				cart_item_product.id_product_variant,
				get_product_cart_price(product.sell_price, product_variant.price_type, product_variant.price, product.id, product.product_type, cart_item.id_cart, 1) AS sell_price,
				product_options_group.id AS id_product_options_group,
				cart_item.qty
				FROM
				cart
				INNER JOIN
				cart_item
				ON
				(cart.id = cart_item.id_cart)
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
				INNER JOIN 
				product_options_group
				ON
				(cart_item_product.id_product = product_options_group.id_product)
				WHERE
				cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"
				AND
				product_options_group.id_options_group = "'.$this->mysqli->escape_string($id_options_group).'"
				LIMIT 1')) throw new Exception('An error occured while trying to get product info.'."\r\n\r\n".$this->mysqli->error);	

				if ($result->num_rows) {
					$row = $result->fetch_assoc();	
					$result->free();
					
					$id_product = $row['id_product'];
					$id_product_variant = $row['id_product_variant'];
					$product_sell_price = $row['sell_price'];
					$id_product_options_group = $row['id_product_options_group'];
					$product_qty = $row['qty'];
				} else {
					throw new Exception('An error occured while trying to get product info.'."\r\n\r\n".$this->mysqli->error);		
				}
				
				// get options info
				if (!$result = $this->mysqli->query('SELECT 
				options.use_shipping_price,
				options.cost_price,
				options.price,
				options.price_type,
				IF("'.$this->mysqli->escape_string($current_datetime).'" BETWEEN options.special_price_from_date AND options.special_price_to_date,options.special_price,options.price) AS sell_price,
				options.special_price_from_date,
				options.special_price_to_date,
				options_group_description.name AS option_group_name,
				options_description.name AS option_name
				FROM 
				options 
				INNER JOIN
				options_description
				ON
				(options.id = options_description.id_options AND options_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
				INNER JOIN
				(options_group CROSS JOIN options_group_description)
				ON
				(options.id_options_group = options_group.id AND options_group.id = options_group_description.id_options_group AND options_group_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
				WHERE
				options.id = "'.$this->mysqli->escape_string($id_options).'"
				LIMIT 1')) throw new Exception('An error occured while trying to get option.'."\r\n\r\n".$this->mysqli->error);		
				
				if ($result->num_rows) {							
					$row_option = $result->fetch_assoc();
					$result->free();				
					
					// check availability
					if ($this->check_option_availability($id_cart_item,$id_product,$id_options,$qty)) {
						switch ($row_option['price_type']) {
							// fixed
							case 0:
								$price = $row_option['price'];
								$sell_price = $row_option['sell_price'];
								break;
							// percent
							case 1:
								$price = round(($product_sell_price*$row_option['price'])/100,2);
								$sell_price = round(($product_sell_price*$row_option['sell_price'])/100,2);
								break;	
						}
						
						if (!$this->mysqli->query('INSERT INTO
						cart_item_option 
						SET
						id_cart_item = "'.$this->mysqli->escape_string($id_cart_item).'",
						id_product_options_group = "'.$this->mysqli->escape_string($id_product_options_group).'",
						id_options_group = "'.$this->mysqli->escape_string($id_options_group).'",
						id_options = "'.$this->mysqli->escape_string($id_options).'",
						use_shipping_price = "'.$this->mysqli->escape_string($row_option['use_shipping_price']).'",
						qty = "'.$this->mysqli->escape_string($qty).'",
						cost_price = "'.$this->mysqli->escape_string($row_option['cost_price']).'",
						price = "'.$this->mysqli->escape_string($price).'",
						sell_price = "'.$this->mysqli->escape_string($sell_price).'",
						special_price_start_date = "'.$this->mysqli->escape_string($row_option['special_price_from_date']).'",
						special_price_end_date = "'.$this->mysqli->escape_string($row_option['special_price_to_date']).'",
						textfield = "'.$this->mysqli->escape_string($textfield).'",
						textarea = "'.$this->mysqli->escape_string($textarea).'",
						filename_tmp = "'.$this->mysqli->escape_string($file['tmp_name']).'",
						filename = "'.$this->mysqli->escape_string($file['name']).'",
						date_start = "'.$this->mysqli->escape_string($date).'",
						date_end = "'.$this->mysqli->escape_string($date_to).'",
						datetime_start = "'.$this->mysqli->escape_string($datetime).'",
						datetime_end = "'.$this->mysqli->escape_string($datetime_to).'",
						time_start = "'.$this->mysqli->escape_string($time).'",
						time_end = "'.$this->mysqli->escape_string($time_to).'",						
						subtotal = "'.$this->mysqli->escape_string($product_qty*$qty*$sell_price).'"')) throw new Exception('An error occured while trying to add option.'."\r\n\r\n".$this->mysqli->error);
							 
					} else {
						//$this->messages[] = $row_option['option_group_name'].', '.$row_option['option_name'].' '. language('_include/classes/SC_Cart','LABEL_IS_NOT_AVAILABLE');
						
						return false;					
					}
				} else {
					throw new Exception('An error occured while trying to get option.'."\r\n\r\n".$this->mysqli->error);		
				}					
			}
		}
			
		return false;		
	}
	
	public function del_option($id_cart_item_option)
	{ 
		$id_cart_item_option=(int)$id_cart_item_option;
		
		if (!$result = $this->mysqli->query('SELECT 
		filename_tmp
		FROM
		cart_item_option
		WHERE
		id = "'.$this->mysqli->escape_string($id_cart_item_option).'"')) throw new Exception('An error occured while trying to get option info.'."\r\n\r\n".$this->mysqli->error);		
		
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			$result->free();
			
			$tmp_uploads_dir = dirname(__FILE__).'/../../tmp_uploads/';	
			
			if ($row['filename_tmp'] && is_file($tmp_uploads_dir.$row['filename_tmp'])) {
				unlink($tmp_uploads_dir.$row['filename_tmp']);
			}
			
			
			if (!$this->mysqli->query('DELETE FROM
			cart_item_option,
			cart_item_option_tax
			USING
			cart_item_option
			LEFT JOIN
			cart_item_option_tax
			ON
			(cart_item_option.id = cart_item_option_tax.id_cart_item_option)
			WHERE
			cart_item_option.id = "'.$this->mysqli->escape_string($id_cart_item_option).'"')) throw new Exception('An error occured while trying to delete option.'."\r\n\r\n".$this->mysqli->error);		
		}
	}
	
	public function upd_option_qty($id_cart_item_option, $qty=1)
	{
		$id_cart_item_option=(int)$id_cart_item_option;
		$qty=(int)$qty;
		$qty=$qty>=0?$qty:1;
		
		if (!$qty) {
			$this->del_option($id_cart_item_option);
			
			return true;
		} else {
			// get current option qty in cart 
			if (!$result = $this->mysqli->query('SELECT 		
			cart_item.id,				
			cart_item_option.qty,
			cart_item_option.id_options,
			cart_item_product.id_product
			FROM
			cart_item_option
			INNER JOIN 
			(cart_item CROSS JOIN cart_item_product)
			ON
			(cart_item_option.id_cart_item = cart_item.id AND cart_item.id = cart_item_product.id_cart_item)
			WHERE
			cart_item_option.id = "'.$this->mysqli->escape_string($id_cart_item_option).'"	
			LIMIT 1')) throw new Exception('An error occured while trying to check current option qty in cart.'."\r\n\r\n".$this->mysqli->error);		

			$row = $result->fetch_assoc();
			$result->free();
			
			// check how many qty more we are requesting
			$extra_qty = $row['qty']-$qty;
			$extra_qty = $extra_qty < 0 ? abs($extra_qty):0;		
			
			// if we have an extra then check if available
			if ($extra_qty > 0) {
				if (!$this->check_option_availability($row['id'],$row['id_product'],$row['id_options'],$extra_qty)) return false;		
			}		
			
			if (!$this->mysqli->query('UPDATE
			cart_item_option
			SET
			qty = "'.$this->mysqli->escape_string($qty).'"
			WHERE
			id = "'.$this->mysqli->escape_string($id_cart_item_option).'"
			LIMIT 1')) throw new Exception('An error occured while trying to update option qty.'."\r\n\r\n".$this->mysqli->error);		
				
			return true;		
		}
		
		return false;
	}
		
	public function check_option_availability($id_cart_item,$id_product,$id_option,$qty=1)
	{
		$id_cart_item=(int)$id_cart_item;
		$id_option=(int)$id_option;
		$qty=(int)$qty;
		$qty=$qty>=0?$qty:1;
		$current_datetime = date('Y-m-d H:i:s');
		
		if (!$result = $this->mysqli->query('SELECT
		options.id,
		is_option_in_stock(options.id) AS in_stock,
		option_qty_in_stock(options.id) AS qty_in_stock,
		CONCAT(options_group_description.name,": ",options_description.name) AS name,
		IF(options_group.input_type < 5,options.track_inventory,0) AS track_inventory,
		options_group.max_qty
		FROM 
		product_options_group
		INNER JOIN
		(options_group CROSS JOIN options)
		ON
		(product_options_group.id_options_group = options_group.id AND options_group.id = options.id_options_group)	
		INNER JOIN
		(options_group_description
		CROSS JOIN
		options_description)
		ON
		(options.id_options_group = options_group_description.id_options_group AND options_group_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'" 
		AND 
		options.id = options_description.id_options AND options_description.language_code = options_group_description.language_code)
		
		WHERE
		product_options_group.id_product = "'.$this->mysqli->escape_string($id_product).'"
		AND		
		options.id = "'.$this->mysqli->escape_string($id_option).'"	
		LIMIT 1')) throw new Exception('An error occured while trying to check option availability.'."\r\n\r\n".$this->mysqli->error);	
		
		$row = $result->fetch_assoc();
		$result->free();
		$name = $row['name'];
		$in_stock = $row['in_stock'];
		$qty_in_stock = $row['qty_in_stock'];
		$track_inventory = $row['track_inventory'];
		//$max_qty = $row['max_qty'];			
		
		// get qty in cart
		
		if (!$result = $this->mysqli->query('SELECT
		cart_item.qty AS product_qty,
		cart_item_option.qty
		FROM 
		cart_item_option
		INNER JOIN
		(cart_item CROSS JOIN cart)
		ON
		(cart_item_option.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
		
		WHERE
		cart_item_option.id_cart_item = "'.$this->mysqli->escape_string($id_cart_item).'"
		AND
		cart_item_option.id_options = "'.$this->mysqli->escape_string($id_option).'"
		AND
		cart.id = "'.$this->mysqli->escape_string($this->id).'"
		LIMIT 1')) throw new Exception('An error occured while trying to get current qty in cart.'."\r\n\r\n".$this->mysqli->error);	
		$row = $result->fetch_assoc();				
		
		/*if ($max_qty > 0) {
			if ($qty > $max_qty) {
				$this->messages[] = $name.' ' . language('_include/classes/SC_Cart','LABEL_MAX_PER_CUSTOMER',array(0=>$max_qty));
				return false;
			}
		}*/				
				
		if ($track_inventory && $row['product_qty']*$qty > $qty_in_stock) {
			$this->messages[] = $name.' '.language('product','ALERT_NOT_ENOUGH_IN_STOCK');
			return false;
		}

		return $in_stock;	
	}	
	
	public function add_gift($id_rebate_coupon, $id_product, $id_product_variant=0)
	{
		$id_rebate_coupon=(int)$id_rebate_coupon;
		$id_product=(int)$id_product;
		$id_product_variant=(int)$id_product_variant;
		 
		// check if discount applied to cart
		if (!$result_rebate = $this->mysqli->query('SELECT
		id
		FROM 
		cart_discount
		WHERE
		id_cart = "'.$this->mysqli->escape_string($this->id).'"
		AND
		id_rebate_coupon = "'.$this->mysqli->escape_string($id_rebate_coupon).'"
		LIMIT 1')) throw new Exception('An error occured while trying to get rebate info.'."\r\n\r\n".$this->mysqli->error);	
		
		$row_rebate = $result_rebate->fetch_assoc();
		$result_rebate->free();
		
		$id_cart_discount = $row_rebate['id'];
		
		if (!$result = $this->mysqli->query('SELECT 
		rebate_coupon_product.id,
		rebate_coupon_product.id_product,
		product_description.name,
		GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant_name,
		(product.cost_price+IF(product_variant.id IS NOT NULL,product_variant.cost_price,0)) AS cost_price,		
		get_product_cart_price(product.sell_price, product_variant.price_type, product_variant.price, product.id, product.product_type, "'.$this->mysqli->escape_string($this->id).'", 1) AS price
		FROM 
		rebate_coupon_product
		INNER JOIN 
		(product CROSS JOIN product_description)
		ON
		(rebate_coupon_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
		LEFT JOIN 
		(product_variant 
		CROSS JOIN product_variant_option 
		CROSS JOIN product_variant_group 
		CROSS JOIN product_variant_group_option 
		CROSS JOIN product_variant_group_option_description)
		ON 
		(product_variant.id = product_variant_option.id_product_variant 
		AND product_variant_option.id_product_variant_group = product_variant_group.id 
		AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
		AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
		AND product_variant_group_option_description.language_code = product_description.language_code
		AND product_variant.id = "'.$this->mysqli->escape_string($id_product_variant).'")
		WHERE
		rebate_coupon_product.id_rebate_coupon = "'.$this->mysqli->escape_string($id_rebate_coupon).'"
		AND
		rebate_coupon_product.id_product = "'.$this->mysqli->escape_string($id_product).'"
		AND
		is_product_in_stock(rebate_coupon_product.id_product,IF(product_variant.id IS NOT NULL,product_variant.id,0),0) = 1
		GROUP BY 
		rebate_coupon_product.id')) throw new Exception('An error occured while trying to get gift info.'."\r\n\r\n".$this->mysqli->error);		
		
		if ($result->num_rows) {
			$row = $result->fetch_assoc();		
			
			if (!$id_cart_discount) {
				if (!$this->mysqli->query('INSERT INTO 
				cart_discount 
				SET
				id_cart = "'.$this->mysqli->escape_string($this->id).'",
				id_rebate_coupon = "'.$this->mysqli->escape_string($id_rebate_coupon).'"')) throw new Exception('An error occured while trying to add cart discount.'."\r\n\r\n".$this->mysqli->error);	
				
				$id_cart_discount = $this->mysqli->insert_id;				
			}						
			
			if (!$this->mysqli->query('INSERT INTO 
			cart_item 
			SET
			id_cart = "'.$this->mysqli->escape_string($this->id).'",
			id_cart_discount = "'.$this->mysqli->escape_string($id_cart_discount).'", 
			qty = 1')) throw new Exception('An error occured while trying to add cart discount item.'."\r\n\r\n".$this->mysqli->error);	
			
			$id_cart_item = $this->mysqli->insert_id;
			
			if (!$this->mysqli->query('INSERT INTO 
			cart_item_product
			SET
			id_cart_item = "'.$this->mysqli->escape_string($id_cart_item).'",
			id_product = "'.$this->mysqli->escape_string($id_product).'",
			id_product_variant = "'.$this->mysqli->escape_string($id_product_variant).'",
			qty = 1,
			cost_price = "'.$this->mysqli->escape_string($row['cost_price']).'",
			price = "'.$this->mysqli->escape_string($row['price']).'"')) throw new Exception('An error occured while trying to add cart discount item product.'."\r\n\r\n".$this->mysqli->error);	
			
			$id_cart_item_product = $this->mysqli->insert_id;			
			
			if (!$this->mysqli->query('INSERT INTO
			cart_discount_item_product
			SET
			id_cart_discount = "'.$this->mysqli->escape_string($id_cart_discount).'",
			id_cart_item_product = "'.$this->mysqli->escape_string($id_cart_item_product).'"')) throw new Exception('An error occured while trying to add cart discount item.'."\r\n\r\n".$this->mysqli->error);			
			
			return $id_cart_item;			
		}
		$result->free();
		
		return false;
	}
	
	public function get_gifts()
	{				
		$array=array();
		$image_base_path=realpath(dirname(__FILE__).'/../../images/products/').'/';
		$current_datetime = date('Y-m-d H:i:s');
	
		// check for free gift rebate that can be applied to cart.
		if (!$result_rebate_gift = $this->mysqli->query('SELECT
		rebate_coupon.id,
		rebate_coupon.all_product
		FROM 
		rebate_coupon
		WHERE
		rebate_coupon.active = 1
		AND
		"'.$this->mysqli->escape_string($current_datetime).'" BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
		AND
		rebate_coupon.coupon = 0
		AND
		rebate_coupon.type = 3 
		AND
		rebate_coupon.min_cart_value <= "'.$this->mysqli->escape_string($this->subtotal).'"
		AND 
		(SELECT 
			COUNT(cart_discount.id) 
			FROM 
			cart_discount 
			INNER JOIN 
			rebate_coupon AS rc
			ON 
			(cart_discount.id_rebate_coupon = rc.id) 
			WHERE 
			cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"
			AND
			rc.type = 3 
			AND 
			rc.id != rebate_coupon.id
			) = 0
		ORDER BY 
		rebate_coupon.min_cart_value DESC
		LIMIT 1')) throw new Exception('An error occured while trying to get rebate info.'."\r\n\r\n".$this->mysqli->error);	
		
		// if we have a rebate
		if ($result_rebate_gift->num_rows) {
			$row_rebate_gift = $result_rebate_gift->fetch_assoc();
			
			// get list of gifts	
			if (!$result_gift = $this->mysqli->query('SELECT 
			rebate_coupon_product.id,
			rebate_coupon_product.id_product,
			product.has_variants,
			product.used,
			product_description.name,
			product_description.alias,
			product_image.filename
			FROM 
			rebate_coupon_product
			INNER JOIN 
			(product CROSS JOIN product_description)
			ON
			(rebate_coupon_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
			LEFT JOIN 
			product_image 
			ON 
			(product.id = product_image.id_product AND product_image.cover = 1)	
			LEFT JOIN
			(cart_discount CROSS JOIN cart_item CROSS JOIN cart_item_product) 
			ON
			(rebate_coupon_product.id_rebate_coupon = cart_discount.id_rebate_coupon AND cart_discount.id = cart_item.id_cart_discount AND cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'" AND cart_item.id = cart_item_product.id_cart_item '.($row_rebate_gift['all_product'] ? ' AND rebate_coupon_product.id_product = cart_item_product.id_product':'').')
			WHERE
			rebate_coupon_product.id_rebate_coupon = "'.$this->mysqli->escape_string($row_rebate_gift['id']).'"
			AND
			is_product_in_stock(rebate_coupon_product.id_product,0,0) = 1
			AND
			cart_item.id IS NULL
			GROUP BY 
			rebate_coupon_product.id_product
			ORDER BY 
			rebate_coupon_product.id ASC')) throw new Exception('An error occured while trying to get gifts.'."\r\n\r\n".$this->mysqli->error);	
			
			if ($result_gift->num_rows) {
				$array = array(
					'id'=>$row_rebate_gift['id'],
					'all_product'=>$row_rebate_gift['all_product'],
				);
								
				if (!$stmt_product_variant = $this->mysqli->prepare('SELECT
				product_variant.id AS id_product_variant,
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
	            AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
				AND product_variant_group_description.language_code = product_variant_group_option_description.language_code)
				WHERE
				product_variant.id_product = ?
				AND
				is_product_in_stock(product_variant.id_product,product_variant.id,0) = 1
				GROUP BY
				product_variant.id
				ORDER BY
				product_variant.sort_order ASC')) throw new Exception('An error occured while trying to prepare get product variants statement.'."\r\n\r\n".$this->mysqli->error);				
				
				while ($row_gift = $result_gift->fetch_assoc()) {
					$cover_image = $row_gift['filename'] ? (is_file($image_base_path.'thumb/'.$row_gift['filename'])?'/images/products/thumb/'.$row_gift['filename']:get_blank_image('thumb')):get_blank_image('thumb');
					
					$array['products'][$row_gift['id']] = $row_gift;
					$array['products'][$row_gift['id']]['cover'] = $cover_image;
					$array['products'][$row_gift['id']]['id_rebate_coupon'] = $row_rebate_gift['id'];					
																
					// get list of variants available
					if ($row_gift['has_variants']) {
						if (!$stmt_product_variant->bind_param("si", $_SESSION['customer']['language'], $row_gift['id_product'])) throw new Exception('An error occured while trying to bind params to get product variants statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_product_variant->execute()) throw new Exception('An error occured while trying to get product variants statement.'."\r\n\r\n".$this->mysqli->error);	
						
						/* store result */
						$stmt_product_variant->store_result();		
						
						if ($stmt_product_variant->num_rows) {																						
							/* bind result variables */
							$stmt_product_variant->bind_result($gift_id_product_variant, $gift_name);																											
							
							while ($stmt_product_variant->fetch()) {
								$array['products'][$row_gift['id']]['variants'][$gift_id_product_variant] = $gift_name;
							}
						}
					}
				}
			}
		}
		
		$result_rebate_gift->free();	
		
		return $array;
	}
	
	public function add_coupon($code)
	{
		global $config_site;
		
		$code=trim($code);
		$current_datetime = date('Y-m-d H:i:s');
		
		// check if coupon is valid 
		if (!$result = $this->mysqli->query('SELECT
		rebate_coupon.id,
		rebate_coupon.type,
		rebate_coupon.applicable_on_sale,
		rebate_coupon.min_cart_value,
		IF(rebate_coupon.type=2,rebate_coupon.buy_x_qty,rebate_coupon.min_qty_required) AS min_qty_required,
		rebate_coupon.max_qty_allowed,
		rebate_coupon.buy_x_qty,
		rebate_coupon.get_y_qty,
		rebate_coupon.discount_type,
		rebate_coupon.discount,
		rebate_coupon.coupon,
		rebate_coupon.coupon_max_usage,
		rebate_coupon.coupon_max_usage_customer,
		rebate_coupon.max_weight,
		cart_discount.id AS id_cart_discount,
		IF(erc.id IS NOT NULL,1,0) AS another_cart_coupon_exist		
		FROM
		rebate_coupon
		LEFT JOIN 
		cart_discount
		ON
		(rebate_coupon.id = cart_discount.id_rebate_coupon AND cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'")
		LEFT JOIN
		(rebate_coupon AS erc CROSS JOIN cart_discount AS ecd)
		ON
		(rebate_coupon.id != erc.id AND rebate_coupon.type = erc.type AND rebate_coupon.coupon = erc.coupon AND erc.id = ecd.id_rebate_coupon AND ecd.id_cart = "'.$this->mysqli->escape_string($this->id).'")
		WHERE
		rebate_coupon.coupon = 1
		AND
		rebate_coupon.coupon_code = "'.$this->mysqli->escape_string($code).'" 
		AND
		rebate_coupon.active = 1
		AND
		(
			rebate_coupon.end_date = "0000-00-00 00:00:00"
			OR
			"'.$this->mysqli->escape_string($current_datetime).'" BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date		
		)
		LIMIT 1')) throw new Exception('An error occured while trying to validate coupon.'."\r\n\r\n".$this->mysqli->error);
		
		if (!$result->num_rows) {
			$this->messages[] = language('_include/classes/SC_Cart','LABEL_COUPON_IS_NOT_AVAILABLE');
			return false;
		}
	
		$row = $result->fetch_assoc();				
		$result->free();
		
		// if coupon already in cart
		if ($row['id_cart_discount']) {
			$this->messages[] = language('_include/classes/SC_Cart','LABEL_COUPON_IS_ALREADY_CART',array('{0}'=>$code));
			return false;
		}
		
		// if coupon, check if there is a max use
		if ($row['coupon_max_usage'] > 0 || $row['coupon_max_usage_customer'] > 0) {
			// check number of usage
			// gotta check if currently in cart and add that to number of usage
			if (!$result_check = $this->mysqli->query('SELECT
			IFNULL((SELECT 
			COUNT(orders.id)
			FROM
			cart
			INNER JOIN					
			(orders CROSS JOIN orders_discount)
			ON
			(cart.id_customer = orders.id_customer AND orders.id = orders_discount.id_orders)
			WHERE
			cart.id = "'.$this->mysqli->escape_string($this->id).'"
			AND
			orders_discount.id_rebate_coupon = "'.$this->mysqli->escape_string($row['id']).'"),0) AS nbr_use,
			IFNULL((SELECT 
			COUNT(orders.id)
			FROM
			orders 
			INNER JOIN 
			orders_discount
			ON
			(orders.id = orders_discount.id_orders)
			WHERE
			orders_discount.id_rebate_coupon = "'.$this->mysqli->escape_string($row['id']).'"),0) AS total_use')) throw new Exception('An error occured while trying to check if coupon has already been used.'."\r\n\r\n".$this->mysqli->error);
			
			if ($result_check->num_rows) {
				$row_check = $result_check->fetch_assoc();
				$result_check->free();
				
				if ($row['coupon_max_usage'] && $row_check['total_use'] >= $row['coupon_max_usage']) {
					$this->messages[] = language('_include/classes/SC_Cart','LABEL_COUPON_EXCEEDED_MAX',array(0=>$code));
					return false; 
				} else if ($row_check['coupon_max_usage_customer'] && $row['nbr_use'] >= $row['coupon_max_usage_customer']) {
					$this->messages[] = language('_include/classes/SC_Cart','LABEL_COUPON_EXCEEDED_MAX',array(0=>$code));
					return false;
				}
			}					
		}		
		
		if ($row['type'] == 1 || $row['type'] == 3 || $row['type'] == 4) {	
			// Cash or % off cart subtotal
			if ($row['another_cart_coupon_exist']) {		
				$this->messages[] = language('_include/classes/SC_Cart','LABEL_COUPON_SAME_TYPE_ALREADY_APPLIED');
				return false;		
			}		
										
			// check if min cart value is met
			if ($this->subtotal < $row['min_cart_value']) {
				$this->messages[] = language('_include/classes/SC_Cart','LABEL_MIN_PURCHASE_REQUIRED',array(0=>nf_currency($row['min_cart_value'])));
				return false;
			}			
			
			// if free shipping check for product exceptions
			if ($row['type'] == 4) {
				if (!$result_check_product = $this->mysqli->query('SELECT 
				COUNT(cip.id) AS total
				FROM
				cart_item_product AS cip
				LEFT JOIN
				cart_item AS ci
				ON
				(cip.id_cart_item = ci.id)
				
				LEFT JOIN
				(cart_item_product AS cip2 CROSS JOIN cart_item AS ci2) 
				ON
				(cip.id_cart_item_product = cip2.id AND cip2.id_cart_item = ci2.id)
				
				INNER JOIN 
				config_free_shipping_product_exceptions
				ON
				(cip.id_product = config_free_shipping_product_exceptions.id_product)
				
				WHERE
				ci.id_cart = "'.$this->id.'"
				OR
				ci2.id_cart = "'.$this->id.'"')) throw new Exception('An error occured while trying to get products list.'); 
				$row_check_product = $result_check_product->fetch_assoc();
				
				if ($row_check_product['total']) {
					$this->messages[] = language('_include/classes/SC_Cart','LABEL_FREE_SHIPPING_PRODUCT_EXCEPTION');
					return false;	
				}
			}
			
			// if free shipping check for max weight
			if ($row['type'] == 4 && $row['max_weight'] > 0 && $row['max_weight'] < $this->get_products_total_weight()) {
				$this->messages[] = language('_include/classes/SC_Cart','LABEL_FREE_SHIPPING_ORDERS_UNDER',array(0=>$row['max_weight'],1=>($config_site['weighing_unit'] ? 'kg':'lb')));
				return false;
			}		
		} 
		
		$where=array();
		
		if ($row['type'] == 0 || $row['type'] == 2) {
			if (!$row['applicable_on_sale']) $where[] = 'cart_item_product.price = cart_item_product.sell_price';
			$where[] = 'product.product_type = 0';
			$where[] = '(rebate_coupon_category.id_rebate_coupon IS NOT NULL OR rebate_coupon_product.id_rebate_coupon IS NOT NULL)';
		}
		
		// check if we have products that are applicable 
		// can only have 1 rebate or coupon on a product at all times
		// applicable on sale is only for special price or tier price
		if (!$result_product = $this->mysqli->query('SELECT 
		cart_item_product.id,
		cart_item.id AS id_cart_item,
		cart_item.qty,
		cart_item_product.id_product,
		cart_item_product.id_product_variant,
		cart_item_product.cost_price,
		cart_item_product.price,
		get_product_discounted_price(cart_item.id, rebate_coupon.id) AS sell_price,
		product.product_type				
		FROM
		cart_item
		INNER JOIN
		(cart_item_product CROSS JOIN product)
		ON
		(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id)
		
		LEFT JOIN
		rebate_coupon 
		ON
		(rebate_coupon.id = "'.$this->mysqli->escape_string($row['id']).'")
		
		LEFT JOIN
		rebate_coupon_product
		ON
		(cart_item_product.id_product = rebate_coupon_product.id_product AND rebate_coupon.id = rebate_coupon_product.id_rebate_coupon)
		
		LEFT JOIN
		(product_category CROSS JOIN rebate_coupon_category)
		ON
		(cart_item_product.id_product = product_category.id_product AND product_category.id_category = rebate_coupon_category.id_category AND rebate_coupon.id = rebate_coupon_category.id_rebate_coupon)
						
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
		AND
		cart_item.id_cart_discount = 0 '.
		(sizeof($where) ? ' AND '.implode(' AND ',$where):'').'				
		GROUP BY 
		cart_item.id')) throw new Exception('An error occured while trying to check if we have applicable products.'."\r\n\r\n".$this->mysqli->error);		
		
		$applicable_products = array();
		while ($row_product = $result_product->fetch_assoc()) {
			$applicable_products[$row_product['id_product']]['qty'] += $row_product['qty'];
			$applicable_products[$row_product['id_product']]['products'][] = $row_product;
		}					
		
		if ($row['type'] == 0 || $row['type'] == 2) {				
			// validate min qty
			foreach ($applicable_products as $key => $row_applicable) {
				// if min required qty is not met, remove discount from this product
				if ($row_applicable['qty'] < $row['min_qty_required']) {
					// remove product from applicable products
					unset($applicable_products[$key]);
				} else {	
					if ($row['max_qty_allowed'] > 0 && $row_applicable['qty'] > $row['max_qty_allowed']) {
						// max_qty_allowed, we need to check this, but how do we control it for variants							
						$remove_qty = $row_applicable['qty']-$row['max_qty_allowed'];
						
						if ($remove_qty > 0) {
							foreach (array_reverse($row_applicable['products'],true) as $id_update_product => $row_update_product) {									
								if ($row_update_product['qty'] >= $remove_qty) {											
									$row_update_product['qty'] -= $remove_qty; 
									$remove_qty = 0;
								} else { 
									$remove_qty -= $row_update_product['qty'];
									$row_update_product['qty'] = 0;
								}
								
								// update qty
								$this->upd_product_qty($row_update_product['id'], $row_update_product['qty']);	
								
								if ($row_update_product['qty'] == 0) unset($applicable_products[$key]['products'][$id_update_product]);
								else $applicable_products[$key]['products'][$id_update_product] = $row_update_product['qty'];
								
								if ($remove_qty == 0) break;
							}
	
							$applicable_products[$key]['qty'] -= $remove_qty;
						}
					}							
				}
			}	
			
			if (!sizeof($applicable_products)) {
				$this->messages[] = language('_include/classes/SC_Cart','LABEL_NO_PRODUCT_APPLICABLE_COUPON');
				return false;
			}			
		}
		
		/* Prepare the statement */
		if (!$stmt_discount = $this->mysqli->prepare('INSERT INTO
		cart_discount
		SET
		id_cart = ?,
		id_rebate_coupon = ?')) throw new Exception('An error occured while trying to prepare add discount statement.'."\r\n\r\n".$this->mysqli->error);																				
		
		/* Prepare the statement */
		if (!$stmt_discount_item_product = $this->mysqli->prepare('INSERT INTO
		cart_discount_item_product
		SET
		id_cart_discount = ?,
		id_cart_item_product = ?,
		amount = ?')) throw new Exception('An error occured while trying to prepare add discount item product statement.'."\r\n\r\n".$this->mysqli->error);									
						
		switch ($row['type']) {
			// percent/fixed amount off product
			case 0:						
				if (!$stmt_discount->bind_param("ii", $this->id, $row['id'])) throw new Exception('An error occured while trying to bind params to add discount statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_discount->execute()) throw new Exception('An error occured while trying to add discount.'."\r\n\r\n".$this->mysqli->error);
				
				$id_cart_discount = $this->mysqli->insert_id;
				
				foreach ($applicable_products as $key => $row_applicable) {
					foreach ($row_applicable['products'] as $row_product) {
						$id_cart_item_product = $row_product['id'];
						
						$amount = 0;
						
						switch ($row['discount_type']) {
							// fixed
							case 0:
								if ($row['discount'] > $row_product['sell_price']) $amount = $row_product['qty']*$row_product['sell_price'];
								else $amount = $row_product['qty']*$row['discount'];
								break;
							// percent
							case 1:
								$amount = $row_product['qty']*round(($row_product['sell_price']*$row['discount'])/100,2);
								break;											
						}
						
						if (!$stmt_discount_item_product->bind_param("iid", $id_cart_discount, $id_cart_item_product, $amount)) throw new Exception('An error occured while trying to bind params to add discount item product statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_discount_item_product->execute()) throw new Exception('An error occured while trying to add discount item product.'."\r\n\r\n".$this->mysqli->error);
					}
				}

				/* close statement */
				$stmt_discount->close();			
				$stmt_discount_item_product->close();											
				break;
			// buy and get
			case 2:									
				/* Prepare the statement */
				if (!$stmt_cart_item = $this->mysqli->prepare('INSERT INTO
				cart_item
				SET
				id_cart = ?,
				id_cart_discount = ?,
				qty = ?')) throw new Exception('An error occured while trying to prepare add cart item statement.'."\r\n\r\n".$this->mysqli->error);													
				
				/* Prepare the statement */
				if (!$stmt_cart_item_product = $this->mysqli->prepare('INSERT INTO
				cart_item_product
				SET
				id_cart_item = ?,
				id_product = ?,
				id_product_variant = ?,								
				qty = ?,
				cost_price = ?,
				price = ?,
				sell_price = ?,
				subtotal = ?')) throw new Exception('An error occured while trying to prepare add cart item product statement.'."\r\n\r\n".$this->mysqli->error);																													
				
				if (!$stmt_discount->bind_param("ii", $this->id, $row['id'])) throw new Exception('An error occured while trying to bind params to add discount statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_discount->execute()) throw new Exception('An error occured while trying to add discount.'."\r\n\r\n".$this->mysqli->error);
				
				$id_cart_discount = $this->mysqli->insert_id;
												
				foreach ($applicable_products as $key => $row_applicable) {
					$n_product = floor($row_applicable['qty']/$row['buy_x_qty'])*$row['get_y_qty'];
					
					$amount=0;
					foreach ($row_applicable['products'] as $row_product) {
						$id_cart_item_product = $row_product['id'];		
						
						if (!$stmt_discount_item_product->bind_param("iid", $id_cart_discount, $id_cart_item_product, $amount)) throw new Exception('An error occured while trying to bind params to add discount item product statement.'."\r\n\r\n".$this->mysqli->error);								
						
						/* Execute the statement */
						if (!$stmt_discount_item_product->execute()) throw new Exception('An error occured while trying to add discount item product.'."\r\n\r\n".$this->mysqli->error);
					}
														
					$row_product = array_shift($row_applicable['products']);									
					
					switch ($row['discount_type']) {
						// fixed
						case 0:
							if ($row['discount'] > $row_product['sell_price']) $amount = $row_product['sell_price'];
							else $amount = $row['discount'];
							break;
						// percent
						case 1:
							$amount = round(($row_product['sell_price']*$row['discount'])/100,2);
							break;											
					}
					
					// add number of product
					$qty=1;
					$id_product = $row_product['id_product'];
					$id_product_variant = $row_product['id_product_variant'];
					$cost_price = $row_product['cost_price'];
					$price = $row_product['sell_price'];
					$sell_price = $price-$amount;
					$subtotal = $sell_price;									
					
					for ($i=0; $i<$n_product; ++$i) {	
						if (!$stmt_cart_item->bind_param("iii", $this->id, $id_cart_discount, $qty)) throw new Exception('An error occured while trying to bind params to add cart item statement.'."\r\n\r\n".$this->mysqli->error);	
															
						/* Execute the statement */
						if (!$stmt_cart_item->execute()) throw new Exception('An error occured while trying to add cart item.'."\r\n\r\n".$this->mysqli->error);
						
						$id_cart_item = $this->mysqli->insert_id;	
						
						if (!$stmt_cart_item_product->bind_param("iiiidddd", $id_cart_item, $id_product, $id_product_variant, $qty, $cost_price, $price, $sell_price, $subtotal)) throw new Exception('An error occured while trying to bind params to add cart item product statement.'."\r\n\r\n".$this->mysqli->error);	
														
						/* Execute the statement */
						if (!$stmt_cart_item_product->execute()) throw new Exception('An error occured while trying to add cart item product.'."\r\n\r\n".$this->mysqli->error);
						
						$p_id_cart_item_product = $this->mysqli->insert_id;
						
						
						if (!$stmt_discount_item_product->bind_param("iid", $id_cart_discount, $p_id_cart_item_product, $amount)) throw new Exception('An error occured while trying to bind params to add discount item product statement.'."\r\n\r\n".$this->mysqli->error);
						
						/* Execute the statement */
						if (!$stmt_discount_item_product->execute()) throw new Exception('An error occured while trying to add discount item product.'."\r\n\r\n".$this->mysqli->error);						
					}									
				}
				
				/* close statement */
				$stmt_discount->close();			
				$stmt_discount_item_product->close();
				$stmt_cart_item->close();
				$stmt_cart_item_product->close();				
				break;												
			// percent/fixed amount off cart
			case 1:					
			// free gift
			case 3:					
			// free shipping
			case 4:
				if (!$this->mysqli->query('UPDATE 
						cart
						SET
						free_shipping = 1
						WHERE
						id = '.$this->id.'
						LIMIT 1')){
					throw new Exception('An error occured while trying to update the cart.'."\r\n\r\n".$this->mysqli->error);		
				}
				
			// percent/fixed amount off cart on first purchase
			case 5:
				if ($row['type'] == 1 || $row['type'] == 5) {
					/* Prepare statement */
					if (!$stmt_option = $this->mysqli->prepare('SELECT
					cart_item_option.id,
					cart_item_option.qty,
					cart_item_option.price,
					cart_item_option.sell_price,
					cart_item_option.subtotal
					FROM 
					cart_item
					INNER JOIN
					cart_item_option
					ON
					(cart_item.id = cart_item_option.id_cart_item)
					WHERE
					cart_item.id_cart = ?
					ORDER BY 
					cart_item_option.id ASC')) throw new Exception('An error occured while trying to prepare get product options statement.'."\r\n\r\n".$this->mysqli->error);			
					// check if we have any applicable products or options					
					if (!$stmt_option->bind_param("i", $this->id)) throw new Exception('An error occured while trying to bind params to get product options statement.'."\r\n\r\n".$this->mysqli->error);	
	
					/* Execute the statement */
					if (!$stmt_option->execute()) throw new Exception('An error occured while trying to get product options.'."\r\n\r\n".$this->mysqli->error);	
					
					/* store result */
					$stmt_option->store_result();																											
					
					if (!sizeof($applicable_products) && !$stmt_option->num_rows) {
						$this->messages[] = language('_include/classes/SC_Cart','LABEL_NO_PRODUCT_OPTION_APPLICABLE_COUPON');
						return false;
					}			
					
					// get sub products
					/* Prepare the statement */
					if (!$stmt_product = $this->mysqli->prepare('SELECT 
					cart_item_product.id,
					cart_item_product.subtotal
					FROM 
					cart_item_product
					WHERE
					cart_item_product.id_cart_item_product = ?
					ORDER BY 
					cart_item_product.id ASC
					')) throw new Exception('An error occured while trying to prepare get sub products statement.'."\r\n\r\n".$this->mysqli->error);			
				
					/* Prepare the statement */
					if (!$stmt_discount_item_option = $this->mysqli->prepare('INSERT INTO
					cart_discount_item_option
					SET
					id_cart_discount = ?,
					id_cart_item_option = ?,
					amount = ?')) throw new Exception('An error occured while trying to prepare add discount item product statement.'."\r\n\r\n".$this->mysqli->error);																							
				}					
				
				if (!$stmt_discount->bind_param("ii", $this->id, $row['id'])) throw new Exception('An error occured while trying to bind params to add discount statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_discount->execute()) throw new Exception('An error occured while trying to add discount.'."\r\n\r\n".$this->mysqli->error);
				
				$id_cart_discount = $this->mysqli->insert_id;			
				
				// not free shipping or free gift
				if ($row['type'] == 1 || $row['type'] == 5) {				
					$discount_pc = 0;
					$discount_amount = 0;
					
					switch ($row['discount_type']) {
						// fixed
						case 0:
							$discount_pc = $row['discount']/$this->subtotal;
							break;
						// percent
						case 1:
							$discount_pc = $row['discount'];
							break;											
					}	
					
					$discount_amount = round($this->subtotal*$discount_pc,2);
				
					// loop products
					if (sizeof($applicable_products)) {
						foreach ($applicable_products as $key => $row_applicable) {												
							foreach ($row_applicable['products'] as $row_product) {
								$id_cart_item_product = $row_product['id'];
								$total_amount = 0;											
								
								if (!$stmt_product->bind_param("i", $id_cart_item_product)) throw new Exception('An error occured while trying to bind params to get products statement.'."\r\n\r\n".$this->mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_product->execute()) throw new Exception('An error occured while trying to get products.'."\r\n\r\n".$this->mysqli->error);	
								
								/* store result */
								$stmt_product->store_result();																														
								
								// if we have other variants
								if ($stmt_product->num_rows) {			
									/* bind result variables */
									$stmt_product->bind_result($p_id,$p_subtotal);
			
									// loop through variants						
									while ($stmt_product->fetch()) {		
										$amount = round($p_subtotal*$discount_pc,2);
										
										if (!$stmt_discount_item_product->bind_param("iid", $id_cart_discount, $p_id, $amount)) throw new Exception('An error occured while trying to bind params to add discount item product statement.'."\r\n\r\n".$this->mysqli->error);
								
										/* Execute the statement */
										if (!$stmt_discount_item_product->execute()) throw new Exception('An error occured while trying to add discount item product.'."\r\n\r\n".$this->mysqli->error);
										
										$total_amount += $amount;
									}									
								}
								
								if (!$stmt_discount_item_product->bind_param("iid", $id_cart_discount, $id_cart_item_product, $total_amount)) throw new Exception('An error occured while trying to bind params to add discount item product statement.'."\r\n\r\n".$this->mysqli->error);
								
								/* Execute the statement */
								if (!$stmt_discount_item_product->execute()) throw new Exception('An error occured while trying to add discount item product.'."\r\n\r\n".$this->mysqli->error);
							}
						}
						
						// close statements
						$stmt_product->close();
					}					
							
					// loop options			
					if ($stmt_option->num_rows) {													
						/* bind result variables */
						$stmt_option->bind_result($option_id, $option_qty, $option_price, $option_sell_price, $option_subtotal);										
				
						// fetch
						while ($stmt_option->fetch()) {
							$amount = round($option_subtotal*$discount_pc,2);						
							
							if (!$stmt_discount_item_option->bind_param("iid", $id_cart_discount, $option_id, $amount)) throw new Exception('An error occured while trying to bind params to add discount item product statement.'."\r\n\r\n".$this->mysqli->error);
							
							/* Execute the statement */
							if (!$stmt_discount_item_option->execute()) throw new Exception('An error occured while trying to add discount item product.'."\r\n\r\n".$this->mysqli->error);								
						}					
					}
				}
				break;
		}
		
		return false;
	}
	
	public function get_coupons()
	{
		$array=array();
		
		if (!$result = $this->mysqli->query('SELECT
		cart_discount.id,
		rebate_coupon.coupon_code,
		rebate_coupon_description.description
		FROM 
		cart_discount
		INNER JOIN
		(rebate_coupon CROSS JOIN rebate_coupon_description)
		ON
		(cart_discount.id_rebate_coupon = rebate_coupon.id AND rebate_coupon.id = rebate_coupon_description.id_rebate_coupon AND rebate_coupon_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
		WHERE
		cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"
		AND
		rebate_coupon.coupon = 1
		ORDER BY 
		cart_discount.id ASC')) throw new Exception('An error occured while trying to get coupons.'."\r\n\r\n".$this->mysqli->error);								
		
		while ($row = $result->fetch_assoc()) {
			$array[$row['id']] = array(
				'id'=>$row['id'],
				'coupon_code'=>$row['coupon_code'],
				'description'=>$row['description'],				
			);
		}
		
		$result->free();
		
		return $array;	
	}
	
	public function del_discount($id_cart_discount)
	{		
		$id_cart_discount=(int)$id_cart_discount;
		
		// Verify if coupon or rebate apply for free shipping in the cart to remove it		
		if (!$result_free_shipping_coupon = $this->mysqli->query('SELECT
		cart_discount.id
		FROM 
		cart_discount
		INNER JOIN
		(rebate_coupon)
		ON
		(cart_discount.id_rebate_coupon = rebate_coupon.id)
		WHERE
		cart_discount.id_cart = "'.$this->id.'"
		AND
		rebate_coupon.type = 4')) throw new Exception('An error occured while trying to get coupons.'."\r\n\r\n".$this->mysqli->error);
		if($result_free_shipping_coupon->num_rows){
			if (!$this->mysqli->query('UPDATE 
					cart
					SET
					free_shipping = 0
					WHERE
					id = '.$this->id.'
					LIMIT 1')){
				throw new Exception('An error occured while trying to update the cart.'."\r\n\r\n".$this->mysqli->error);		
			}
		}

		if (!$this->mysqli->query('DELETE FROM
		cart_discount,
		cart_discount_item_product,
		cart_discount_item_option,
		cart_item,
		cart_item_product,
		cart_item_product_tax,
		cart_item_option,
		cart_item_option_tax
		USING
		cart_discount
		LEFT JOIN
		cart_discount_item_product
		ON
		(cart_discount.id = cart_discount_item_product.id_cart_discount)
		LEFT JOIN 
		cart_discount_item_option
		ON
		(cart_discount.id = cart_discount_item_option.id_cart_discount)
		
		LEFT JOIN
		(cart_item CROSS JOIN cart_item_product)
		ON
		(cart_discount.id = cart_item.id_cart_discount AND cart_item.id = cart_item_product.id_cart_item)
		LEFT JOIN
		cart_item_product_tax
		ON
		(cart_item_product.id = cart_item_product_tax.id_cart_item_product)
		LEFT JOIN
		cart_item_option
		ON
		(cart_item.id = cart_item_option.id_cart_item)
		LEFT JOIN
		cart_item_option_tax
		ON
		(cart_item_option.id = cart_item_option_tax.id_cart_item_option)
		WHERE
		cart_discount.id = "'.$this->mysqli->escape_string($id_cart_discount).'"')) throw new Exception('An error occured while trying to delete discount.'."\r\n\r\n".$this->mysqli->error);		
		
		return true;
	}
	
	public function add_gift_certificate($code)
	{
		$code=trim($code);
		
		// check if gift certificate exists
		if (!$result = $this->mysqli->query('SELECT
		gift_certificate.id,
		(gift_certificate.price-IFNULL((SELECT 
			SUM(amount)
			FROM
			orders_gift_certificate
			INNER JOIN
			orders
			ON
			(orders_gift_certificate.id_orders = orders.id)
			WHERE
			orders_gift_certificate.code = gift_certificate.code
			AND
			orders.status NOT IN (-1,0)),0)) AS amount,
		IF(cart_gift_certificate.id IS NOT NULL,1,0) AS in_cart,
		IF(cart_other.id IS NOT NULL,1,0) AS in_other_cart,
		IF(orders_gift_certificate.id IS NOT NULL,1,0) AS used_other_customer
		FROM
		gift_certificate
		LEFT JOIN
		cart_gift_certificate
		ON
		(gift_certificate.code = cart_gift_certificate.code AND cart_gift_certificate.id_cart = "'.$this->mysqli->escape_string($this->id).'")
		LEFT JOIN
		cart_gift_certificate AS cart_other
		ON
		(gift_certificate.code = cart_gift_certificate.code AND cart_gift_certificate.id_cart != "'.$this->mysqli->escape_string($this->id).'")	
		LEFT JOIN
		(orders_gift_certificate CROSS JOIN orders)
		ON
		(gift_certificate.code = orders_gift_certificate.code AND orders_gift_certificate.id_orders = orders.id AND orders.id_customer != "'.$this->mysqli->escape_string($this->id_customer).'")
		WHERE
		gift_certificate.code = "'.$this->mysqli->escape_string($code).'"
		AND
		gift_certificate.active = 1 
		LIMIT 1')) throw new Exception('An error occured while trying to get gift certificate info.'."\r\n\r\n".$this->mysqli->error);
		
		if (!$result->num_rows) {
			$this->messages[] = language('_include/classes/SC_Cart','LABEL_INVALID_EXPIRED_GIFT_CERTIFICATE');
			return false;	
		}
		
		$row = $result->fetch_assoc();
		$result->free();
		
		if ($row['in_cart']) {
			$this->messages[] = language('_include/classes/SC_Cart','LABEL_ALREADY_CART_GIFT_CERTIFICATE');
			return false;		
		} else if ($row['in_other_cart']) {
			$this->messages[] = language('_include/classes/SC_Cart','LABEL_ALREADY_USE_GIFT_CERTIFICATE');
			return false;	
		} else if ($row['used_other_customer']) {
			$this->messages[] = language('_include/classes/SC_Cart','LABEL_ALREADY_USED_CUSTOMER_GIFT_CERTIFICATE');
			return false;				
		}
		
		$amount = $row['amount'];
		
		if ($amount == 0) {
			$this->messages[] = language('_include/classes/SC_Cart','LABEL_NO_MONEY_LEFT_GIFT_CERTIFICATE');
			return false;	
		} else if ($this->grand_total == 0) {
			$this->messages[] = language('_include/classes/SC_Cart','LABEL_NO_BALANCE_LEFT_GIFT_CERTIFICATE');
			return false;				
		}
		
		// add to cart
		if ($amount > $this->grand_total) $amount = $this->grand_total;
		
		if (!$this->mysqli->query('INSERT INTO 
		cart_gift_certificate
		SET
		id_cart = "'.$this->mysqli->escape_string($this->id).'",
		code = "'.$this->mysqli->escape_string($code).'",
		amount = "'.$this->mysqli->escape_string($amount).'"')) throw new Exception('An error occured while trying to add gift certificate to cart.'."\r\n\r\n".$this->mysqli->error);
	}
	
	public function upd_gift_certificates()
	{
		// cart total
		$balance = $this->total;
		
		// list of gift certificates in cart
		if (!$result = $this->mysqli->query('SELECT 
		cart_gift_certificate.id,
		(gift_certificate.price-IFNULL((SELECT 
			SUM(amount)
			FROM
			orders_gift_certificate
			INNER JOIN
			orders
			ON
			(orders_gift_certificate.id_orders = orders.id)
			WHERE
			orders_gift_certificate.code = gift_certificate.code
			AND
			orders.status NOT IN (-1,0)),0)) AS amount_left			
		FROM
		cart_gift_certificate
		INNER JOIN
		gift_certificate
		ON
		(cart_gift_certificate.code = gift_certificate.code)
		WHERE
		cart_gift_certificate.id_cart = "'.$this->mysqli->escape_string($this->id).'"
		ORDER BY 
		cart_gift_certificate.id ASC')) throw new Exception('An error occured while trying to get gift certificates in cart.'."\r\n\r\n".$this->mysqli->error);
		
		if ($result->num_rows) {
			/* Prepare statement */
			if (!$stmt_upd_gift_certificate = $this->mysqli->prepare('UPDATE				
			cart_gift_certificate
			SET
			cart_gift_certificate.amount = ?
			WHERE
			cart_gift_certificate.id = ?')) throw new Exception('An error occured while trying to prepare update gift certificate amount statement.'."\r\n\r\n".$this->mysqli->error);							
			
			while ($row = $result->fetch_assoc()) {
				if ($balance > 0) {									
					if ($row['amount_left'] > $balance) $amount = $balance;
					else $amount = $row['amount_left'];					
					
					$balance -= $amount;					
					//echo 'test '.$balance.' '.$amount.'<br />';
					
					if (!$stmt_upd_gift_certificate->bind_param("di", $amount, $row['id'])) throw new Exception('An error occured while trying to bind params to update gift certificate amount statement.'."\r\n\r\n".$this->mysqli->error);
					
					/* Execute the statement */
					if (!$stmt_upd_gift_certificate->execute()) throw new Exception('An error occured while trying to update gift certificate amount.'."\r\n\r\n".$this->mysqli->error);								
				} else {
					$this->del_gift_certificate($row['id']);	
				}
			}
		}
	
		$result->free();
		
		// update gift certificates total
		if (!$this->mysqli->query('UPDATE
		cart
		SET
		gift_certificates = (SELECT SUM(amount) FROM cart_gift_certificate WHERE id_cart = cart.id)
		WHERE
		id = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to update gift certificates total.'."\r\n\r\n".$this->mysqli->error);			
		
		// get total
		if (!$result = $this->mysqli->query('SELECT 
		gift_certificates
		FROM
		cart
		WHERE
		id = "'.$this->mysqli->escape_string($this->id).'"
		LIMIT 1')) throw new Exception('An error occured while trying to get gift certificates total.'."\r\n\r\n".$this->mysqli->error);			
		$row = $result->fetch_assoc();
		$result->free();
		
		$this->gift_certificates = $row['gift_certificates'];	
	}
	
	public function del_gift_certificate($id_cart_gift_certificate)
	{
		$id_cart_gift_certificate=(int)$id_cart_gift_certificate;
		
		if (!$this->mysqli->query('DELETE FROM
		cart_gift_certificate 
		WHERE
		id = "'.$this->mysqli->escape_string($id_cart_gift_certificate).'"
		LIMIT 1')) throw new Exception('An error occured while trying to delete gift certificate.'."\r\n\r\n".$this->mysqli->error);		
	}
	
	public function get_gift_certificates()
	{
		$array=array();
		
		if (!$result = $this->mysqli->query('SELECT
		cart_gift_certificate.id,
		cart_gift_certificate.code,
		cart_gift_certificate.amount,
		(gift_certificate.price-cart_gift_certificate.amount-IFNULL((SELECT
			SUM(orders_gift_certificate.amount)
			FROM 
			orders_gift_certificate
			INNER JOIN
			orders
			ON
			(orders_gift_certificate.id_orders = orders.id)
			WHERE
			orders_gift_certificate.code = gift_certificate.code
			AND
			orders.status NOT IN (-1,0)),0)) AS amount_left
		FROM 
		cart_gift_certificate 
		INNER JOIN
		gift_certificate
		ON
		(cart_gift_certificate.code = gift_certificate.code)
		WHERE
		cart_gift_certificate.id_cart = "'.$this->mysqli->escape_string($this->id).'"
		ORDER BY
		cart_gift_certificate.id ASC')) throw new Exception('An error occured while trying to get gift certificates.'."\r\n\r\n".$this->mysqli->error);			
		
		while ($row = $result->fetch_assoc()) {
			$array[$row['id']] = $row;	
		}
		
		return $array;	
	}
	
	public function get_products()	
	{
		$array=array();		
		
		// get cart products				
		if (!$result = $this->mysqli->query('SELECT
		cart_item.id,
		cart_item.qty,
		product_description.name,
		product_description.alias,
		GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
		cart_item_product.id AS id_cart_item_product,
		cart_item_product.id_product,
		cart_item_product.use_shipping_price,
		cart_item_product.id_product_variant,
		cart_item_product.price,
		cart_item_product.sell_price,
		cart_item_product.subtotal,
		cart_item.id_cart_discount,
		product.product_type,
		product.used,
		IF(product_variant.id IS NOT NULL AND product_variant.weight > 0,product_variant.weight,product.weight) AS weight,
		IF(product_variant.id IS NOT NULL AND product_variant.length > 0,product_variant.length,product.length) AS length,
		IF(product_variant.id IS NOT NULL AND product_variant.width > 0,product_variant.width,product.width) AS width,
		IF(product_variant.id IS NOT NULL AND product_variant.height > 0,product_variant.height,product.height) AS height,
		IF(product.use_shipping_price=1,
		IFNULL((SELECT
		product_price_shipping_region.price
		FROM
		product_price_shipping_region
		WHERE
		product_price_shipping_region.id_product = cart_item_product.id_product
		AND
		(
			(
				product_price_shipping_region.country_code = cart.shipping_country_code
				AND
				product_price_shipping_region.state_code = cart.shipping_state_code
			)
			OR 
			(
				product_price_shipping_region.country_code = cart.shipping_country_code
				AND
				product_price_shipping_region.state_code = ""
			)			
			OR 
			(
				product_price_shipping_region.country_code = ""
				AND
				product_price_shipping_region.state_code = ""
			)
		)
		ORDER BY 
		(CASE 
			WHEN product_price_shipping_region.country_code = cart.shipping_country_code AND product_price_shipping_region.state_code = cart.shipping_state_code THEN 0
			WHEN product_price_shipping_region.country_code = cart.shipping_country_code AND product_price_shipping_region.state_code = "" THEN 1
			WHEN product_price_shipping_region.country_code = "" AND product_price_shipping_region.state_code = "" THEN 2
		END) ASC
		LIMIT 1),0),0) AS shipping_price,
		product.downloadable,
		product.extra_care,
		product.heavy_weight
		FROM
		cart_item 
		INNER JOIN
		(cart_item_product CROSS JOIN product CROSS JOIN product_description)
		ON
		(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
		LEFT JOIN 
		(product_variant CROSS JOIN product_variant_option CROSS JOIN product_variant_group CROSS JOIN product_variant_group_option CROSS JOIN product_variant_group_option_description CROSS JOIN product_variant_group_description)
		ON 
		(
			cart_item_product.id_product_variant = product_variant.id
			AND
			product_variant.id = product_variant_option.id_product_variant 
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
		INNER JOIN
		cart
		ON
		(cart_item.id_cart = cart.id)	
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'" 
		GROUP BY 
		cart_item.id
		ORDER BY 
		cart_item.id ASC')) throw new Exception('An error occured while trying to get products.'."\r\n\r\n".$this->mysqli->error);	
		if ($result->num_rows) {
			// get sub products
			/* Prepare statement */
			if (!$stmt_sub_products = $this->mysqli->prepare('SELECT
			cart_item_product.id,
			cart_item_product.id_product,
			cart_item_product.qty,
			product_description.name,
			GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
			IF(product_variant.id IS NOT NULL AND product_variant.weight > 0,product_variant.weight,product.weight) AS weight,
			IF(product_variant.id IS NOT NULL AND product_variant.length > 0,product_variant.length,product.length) AS length,
			IF(product_variant.id IS NOT NULL AND product_variant.width > 0,product_variant.width,product.width) AS width,
			IF(product_variant.id IS NOT NULL AND product_variant.height > 0,product_variant.height,product.width) AS height,
			IF(product.use_shipping_price=1,
			(SELECT
			product_price_shipping_region.price
			FROM
			product_price_shipping_region
			WHERE
			product_price_shipping_region.id_product = cart_item_product.id_product
			AND
			(
				product_price_shipping_region.country_code = cart.shipping_country_code
				AND
				product_price_shipping_region.state_code = cart.shipping_state_code
			)
			OR 
			(
				product_price_shipping_region.country_code = cart.shipping_country_code
				AND
				product_price_shipping_region.state_code = ""
			)			
			OR 
			(
				product_price_shipping_region.country_code = ""
				AND
				product_price_shipping_region.state_code = ""
			)
			ORDER BY 
			(CASE 
				WHEN product_price_shipping_region.country_code = cart.shipping_country_code AND product_price_shipping_region.state_code = cart.shipping_state_code THEN 0
				WHEN product_price_shipping_region.country_code = cart.shipping_country_code AND product_price_shipping_region.state_code = "" THEN 1
				WHEN product_price_shipping_region.country_code = "" AND product_price_shipping_region.state_code = "" THEN 2
			END) ASC
			LIMIT 1),0) AS shipping_price,
			product.extra_care,
			product.heavy_weight				
			FROM
			cart_item_product
			INNER JOIN
			(cart_item_product AS cip_parent CROSS JOIN cart_item CROSS JOIN cart)
			ON
			(cart_item_product.id_cart_item_product = cip_parent.id AND cip_parent.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
			INNER JOIN
			(product CROSS JOIN product_description)
			ON
			(cart_item_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = ?)
			LEFT JOIN 
			(product_variant 
			CROSS JOIN product_variant_option 
			CROSS JOIN product_variant_group 
			CROSS JOIN product_variant_group_option 
			CROSS JOIN product_variant_group_option_description
			CROSS JOIN product_variant_group_description)
			ON 
			(
				cart_item_product.id_product_variant = product_variant.id 
				AND
				product_variant.id = product_variant_option.id_product_variant 
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
			LEFT JOIN 
			product_combo
			ON
			(cart_item_product.id_product_combo_product = product_combo.id) 
			LEFT JOIN
			(product_bundled_product_group_product AS pg_product CROSS JOIN product_bundled_product_group AS pg)
			ON
			(cart_item_product.id_product_bundled_product_group_product = pg_product.id AND pg_product.id_product_bundled_product_group = pg.id)
			WHERE
			cart_item_product.id_cart_item_product = ?
			GROUP BY 
			cart_item_product.id				
			ORDER BY 
			(CASE 
				WHEN product_combo.id IS NOT NULL THEN
					 product_combo.sort_order
				WHEN pg_product.id IS NOT NULL THEN
					pg.sort_order
			END) ASC')) throw new Exception('An error occured while trying to prepare get sub products statement.'."\r\n\r\n".$this->mysqli->error);							
			
			while ($row = $result->fetch_assoc()) {
				$id_cart_discount = $row['id_cart_discount'];
				
				$sub_products=array();
				switch ($row['product_type']) {
					// combo
					case 1:
					// bundle
					case 2:
						if (!$stmt_sub_products->bind_param("si", $_SESSION['customer']['language'], $row['id_cart_item_product'])) throw new Exception('An error occured while trying to bind params to get sub products statement.'."\r\n\r\n".$this->mysqli->error);
					
						/* Execute the statement */
						if (!$stmt_sub_products->execute()) throw new Exception('An error occured while trying to get sub products.'."\r\n\r\n".$this->mysqli->error);								
						/* store result */
						$stmt_sub_products->store_result();	
						
						if ($stmt_sub_products->num_rows) {
							/* bind result variables */
							$stmt_sub_products->bind_result($sub_product_id, $sub_product_id_product, $sub_product_qty, $sub_product_name, $sub_product_variant, $sub_product_weight, $sub_product_length, $sub_product_width, $sub_product_height, $sub_product_shipping_price, $sub_product_extra_care, $heavy_weight);									
							
							while ($stmt_sub_products->fetch()) {								
								$sub_products[$sub_product_id] = array(
									'id' => $sub_product_id,
									'id_product'=>$sub_product_id_product,
									'qty' => $sub_product_qty,
									'name' => $sub_product_name.(!empty($sub_product_variant) ? ' ('.$sub_product_variant.')':''),
									'weight' => $sub_product_weight,
									'length' => $sub_product_length,
									'width' => $sub_product_width,
									'height' => $sub_product_height,
									'shipping_price' => $sub_product_shipping_price,
									'extra_care' => $sub_product_extra_care,
									'heavy_weight' => $heavy_weight,
								);
							}
						}
						break;						
				}
									
				$array[$row['id']] = array(
					'id'=>$row['id'],
					'id_cart_discount'=>$row['id_cart_discount'],
					'id_cart_item_product'=>$row['id_cart_item_product'],
					'id_product'=>$row['id_product'],
					'id_product_variant'=>$row['id_product_variant'],
					'product_type' => $row['product_type'],
					'used' => $row['used'],
					'use_shipping_price'=>$row['use_shipping_price'],
					'weight'=>$row['weight'],
					'length'=>$row['length'],
					'width'=>$row['width'],
					'height'=>$row['height'],
					'shipping_price'=>$row['shipping_price'],
					'qty'=>$row['qty'],
					'name'=>$row['name'],
					'alias'=>$row['alias'],
					'variant'=>$row['variant'],
					'price'=>$row['price'],
					'sell_price'=>$row['sell_price'],
					'subtotal'=>$row['subtotal'],
					'sub_products'=>$sub_products,
					'downloadable' =>$row['downloadable'],
					'extra_care' =>$row['extra_care'],
					'heavy_weight' =>$row['heavy_weight'],
					//'discount'=>$row_discount,
				);	
			}				
		
			/* close statement */
			$stmt_sub_products->close();
			//$stmt_discount->close();								
		}
		
		return $array;
	}
	
	public function get_product_discounts($id_cart_item_product)
	{
		$array=array();
		
		if (!$result = $this->mysqli->query('SELECT
		rebate_coupon_description.description,
		cart_discount_item_product.amount,
		rebate_coupon.type,
		rebate_coupon.start_date,
		rebate_coupon.end_date
		FROM
		cart_discount
		INNER JOIN
		cart_discount_item_product
		ON
		(cart_discount.id = cart_discount_item_product.id_cart_discount)
		INNER JOIN
		(rebate_coupon CROSS JOIN rebate_coupon_description)
		ON
		(cart_discount.id_rebate_coupon = rebate_coupon.id AND rebate_coupon.id = rebate_coupon_description.id_rebate_coupon AND rebate_coupon_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
		WHERE
		cart_discount_item_product.id_cart_item_product = "'.$this->mysqli->escape_string($id_cart_item_product).'"
		ORDER BY 
		(CASE
			WHEN rebate_coupon.type = 0 AND rebate_coupon.coupon = 0 THEN 0
			WHEN rebate_coupon.type = 0 AND rebate_coupon.coupon = 1 THEN 1
			WHEN rebate_coupon.type = 2 THEN 3
			WHEN (rebate_coupon.type = 1 OR rebate_coupon.type = 5) AND rebate_coupon.coupon = 0 THEN 4
			WHEN rebate_coupon.type = 1 AND rebate_coupon.coupon = 1 THEN 5		
		END) ASC')) throw new Exception('An error occured while trying to get product discounts.'."\r\n\r\n".$this->mysqli->error);	

		while ($row = $result->fetch_assoc()){
			$array[] = $row;				
		}		
		$result->free();
		
		return $array;
	}			
	
	public function get_product_options($id_cart_item)
	{
		$id_cart_item=(int)$id_cart_item;
				
		$array=array();
		
		if (!$result_group = $this->mysqli->query('SELECT
		options_group.id,
		options_group.input_type,
		options_group.user_defined_qty,
		options_group_description.name,
		options_group_description.description
		FROM
		cart_item
		INNER JOIN
		(cart_item_option CROSS JOIN product_options_group CROSS JOIN options_group CROSS JOIN options_group_description)
		ON
		(cart_item.id = cart_item_option.id_cart_item AND cart_item_option.id_product_options_group = product_options_group.id AND product_options_group.id_options_group = options_group.id AND options_group.id = options_group_description.id_options_group AND options_group_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'") 
		WHERE
		cart_item.id = "'.$this->mysqli->escape_string($id_cart_item).'"
		GROUP BY 
		options_group.id
		ORDER BY 
		product_options_group.sort_order ASC')) throw new Exception('An error occured while trying to get product option groups.'."\r\n\r\n".$this->mysqli->error);			
		
		if ($result_group->num_rows) {
			if (!$stmt_option = $this->mysqli->prepare('SELECT
			options.id AS id_options,
			options_description.name,
			options_description.description,
			cart_item_option.use_shipping_price,
			cart_item_option.id,			
			cart_item_option.qty,
			cart_item_option.sell_price,
			cart_item_option.subtotal,
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
			options.weight,
			options.length,
			options.width,
			options.height,
			IF(options.use_shipping_price=1,
			(SELECT
			options_price_shipping_region.price
			FROM
			options_price_shipping_region
			WHERE
			options_price_shipping_region.id_options = cart_item_option.id_options
			AND
			(
				(
					options_price_shipping_region.country_code = cart.shipping_country_code
					AND
					options_price_shipping_region.state_code = cart.shipping_state_code
				)
				OR 
				(
					options_price_shipping_region.country_code = cart.shipping_country_code
					AND
					options_price_shipping_region.state_code = ""
				)			
				OR 
				(
					options_price_shipping_region.country_code = ""
					AND
					options_price_shipping_region.state_code = ""
				)
			)
			ORDER BY 
			(CASE 
				WHEN options_price_shipping_region.country_code = cart.shipping_country_code AND options_price_shipping_region.state_code = cart.shipping_state_code THEN 0
				WHEN options_price_shipping_region.country_code = cart.shipping_country_code AND options_price_shipping_region.state_code = "" THEN 1
				WHEN options_price_shipping_region.country_code = "" AND options_price_shipping_region.state_code = "" THEN 2
			END) ASC
			LIMIT 1),0) AS shipping_price,
			options.extra_care
			FROM
			cart_item_option
			INNER JOIN
			(options CROSS JOIN options_description)
			ON
			(cart_item_option.id_options = options.id AND options.id = options_description.id_options AND options_description.language_code = ?) 
			INNER JOIN
			(cart_item CROSS JOIN cart)
			ON
			(cart_item_option.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
			WHERE
			cart_item_option.id_cart_item = ?
			AND
			cart_item_option.id_options_group = ?	
			ORDER BY 
			options.sort_order ASC')) throw new Exception('An error occured while trying to prepare product options statement.'."\r\n\r\n".$this->mysqli->error);
			
			while ($row_group = $result_group->fetch_assoc()) {
				$array[$row_group['id']] = array(
					'input_type'=>$row_group['input_type'],			
					'user_defined_qty'=>$row_group['user_defined_qty'],		
					'name'=>$row_group['name'],
					'description'=>$row_group['group_description'],					
				);				
				
				if (!$stmt_option->bind_param("sii", $_SESSION['customer']['language'], $id_cart_item, $row_group['id'])) throw new Exception('An error occured while trying to bind params to product options statement.'."\r\n\r\n".$this->mysqli->error);	
				
				/* Execute the statement */
				if (!$stmt_option->execute()) throw new Exception('An error occured while trying to get product options.'."\r\n\r\n".$this->mysqli->error);	
				
				/* store result */
				$stmt_option->store_result();																											
							
				/* bind result variables */
				$stmt_option->bind_result($id_options, $name, $description, $use_shipping_price, $id_cart_item_option, $qty, $sell_price, $subtotal, $textfield, 
				$textarea, $filename_tmp, $filename, $date_start, $date_end, $datetime_start, $datetime_end, $time_start, $time_end,
				$weight, $length, $width, $height, $shipping_price, $extra_care);														
				
				// fetch
				while ($stmt_option->fetch()) {					
					$array[$row_group['id']]['options'][$id_cart_item_option] = array(
						'id_options'=>$id_options,
						'name'=>$name,
						'description'=>$description,
						'use_shipping_price'=>$use_shipping_price,
						'id'=>$id_cart_item_option,
						'qty'=>$qty,
						'sell_price'=>$sell_price,
						'subtotal'=>$subtotal,
						'textfield'=>$textfield,
						'textarea'=>$textarea,
						'filename_tmp'=>$filename_tmp,
						'filename'=>$filename,
						'date_start'=>$date_start,
						'date_end'=>$date_end,
						'datetime_start'=>$datetime_start,
						'datetime_end'=>$datetime_end,
						'time_start'=>$time_start,
						'time_end'=>$time_end,
						'weight'=>$weight,
						'length'=>$length,
						'width'=>$width,
						'height'=>$height,
						'shipping_price'=>$shipping_price,
						'extra_care' => $extra_care,
					);	
				}
			}
			
			/* close statement */
			$stmt_option->close();
		}	
		
		$result_group->free();
		
		return $array;	
	}
	
	public function get_product_option_discounts($id_cart_item_option)
	{
		$array=array();
		
		if (!$result = $this->mysqli->query('SELECT
		rebate_coupon_description.description,
		cart_discount_item_option.amount,
		rebate_coupon.type,
		rebate_coupon.start_date,
		rebate_coupon.end_date
		FROM
		cart_discount
		INNER JOIN
		cart_discount_item_option
		ON
		(cart_discount.id = cart_discount_item_option.id_cart_discount)
		INNER JOIN
		(rebate_coupon CROSS JOIN rebate_coupon_description)
		ON
		(cart_discount.id_rebate_coupon = rebate_coupon.id AND rebate_coupon.id = rebate_coupon_description.id_rebate_coupon AND rebate_coupon_description.language_code = "'.$this->mysqli->escape_string($_SESSION['customer']['language']).'")
		WHERE
		cart_discount_item_option.id_cart_item_option = "'.$this->mysqli->escape_string($id_cart_item_option).'"
		ORDER BY 
		(CASE
			WHEN (rebate_coupon.type = 1 OR rebate_coupon.type = 5) AND rebate_coupon.coupon = 0 THEN 4
			WHEN rebate_coupon.type = 1 AND rebate_coupon.coupon = 1 THEN 5		
		END) ASC')) throw new Exception('An error occured while trying to get product option discounts.'."\r\n\r\n".$this->mysqli->error);
		
		while ($row = $result->fetch_assoc()){
			$array[] = $row;				
		}		
		$result->free();
		
		return $array;
	}			
	
	public function get_products_total_weight()	
	{
		// get cart products				
		if (!$result = $this->mysqli->query('SELECT SUM(IFNULL((SELECT
			SUM(IF(product_variant.id IS NOT NULL AND product_variant.weight > 0,product_variant.weight,product.weight)) 
			FROM
			cart_item 
			INNER JOIN
			(cart_item_product CROSS JOIN product)
			ON
			(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id)
			LEFT JOIN 
			product_variant
			ON 
			(cart_item_product.id_product_variant = product_variant.id)
			WHERE
			cart_item.id_cart = cart.id
		),0)+IFNULL((SELECT
			SUM(options.weight)
			FROM
			cart_item
			INNER JOIN
			(cart_item_option CROSS JOIN options)
			ON
			(cart_item.id = cart_item_option.id_cart_item AND cart_item_option.id_options = options.id)
			WHERE
			cart_item.id_cart = cart.id),0)) AS total
		FROM
		cart 
		WHERE
		cart.id = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to get cart total weight.'."\r\n\r\n".$this->mysqli->error);

		$row = $result->fetch_assoc();
		$result->free();
		
		return $row['total'] > 0 ? $row['total']:0;
	}	
	
	public function count_cart_items()
	{
		if (!$result = $this->mysqli->query('SELECT 
		COUNT(cart_item.id) AS total
		FROM
		cart_item
		INNER JOIN
		cart_item_product
		ON
		(cart_item.id = cart_item_product.id_cart_item)
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to get total products in cart.'."\r\n\r\n".$this->mysqli->error);				
		$row = $result->fetch_assoc();
		
		return $row['total'];	
	}
	
	
	public function calculate_subtotal()
	{
		$current_datetime = date('Y-m-d H:i:s');
		
		/*
			- update cart product cost price
			- update cart product price
			- update cart product sell price
			
			if product is a combo or bundle
			- update cart sub products cost price
			- update cart sub products price
			- update cart sub products sell price
		*/
		
		// update simple product prices
		if (!$this->mysqli->query('UPDATE 
		cart_item
		INNER JOIN
		(cart_item_product CROSS JOIN product)
		ON
		(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id)
		LEFT JOIN
		product_variant
		ON
		(cart_item_product.id_product_variant = product_variant.id)
		INNER JOIN
		cart
		ON
		(cart_item.id_cart = cart.id)		
		SET 
		cart_item_product.price = calc_sell_price(product.price, product_variant.price_type, product_variant.price,0,0,0,0),
		cart_item_product.sell_price = get_product_cart_price(product.sell_price, product_variant.price_type, product_variant.price, product.id, product.product_type, cart_item.id_cart, 0),
		cart_item_product.subtotal = (cart_item_product.qty*get_product_cart_price(product.sell_price, product_variant.price_type, product_variant.price, product.id, product.product_type, cart_item.id_cart, 0))
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
		AND
		cart_item.id_cart_discount = 0
		AND
		product.product_type = 0')) throw new Exception('An error occured while trying to update item price.'."\r\n\r\n".$this->mysqli->error);			
		
		// update combo and product bundle prices		
		// update each product for our selection
		if (!$this->mysqli->query('UPDATE 
		cart_item_product
		INNER JOIN 
		product
		ON
		(cart_item_product.id_product = product.id)		
		INNER JOIN
		(cart_item_product AS cip CROSS JOIN cart_item CROSS JOIN cart)
		ON
		(cart_item_product.id_cart_item_product = cip.id AND cip.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
		LEFT JOIN
		product_variant
		ON
		(cart_item_product.id_product_variant = product_variant.id)
		LEFT JOIN 
		(cart_item_product AS parent_cart_product CROSS JOIN product AS parent_product)
		ON
		(cart_item_product.id_cart_item_product = parent_cart_product.id AND parent_cart_product.id_product = parent_product.id)
		SET 
		cart_item_product.price = calc_sell_price(product.price, product_variant.price_type, product_variant.price,0,0,0,0),
		cart_item_product.sell_price = get_combo_product_cart_price(cart_item_product.id),
		cart_item_product.subtotal = (cart_item_product.qty*get_combo_product_cart_price(cart_item_product.id))
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
		AND
		cart_item_product.id_cart_item_product != 0')) throw new Exception('An error occured while trying to update item price.'."\r\n\r\n".$this->mysqli->error);			
		
		// update parent product based on the sum of our selection
		if (!$result = $this->mysqli->query('SELECT
		cart_item_product.id,
		(SELECT SUM(cip.qty*cip.price) FROM cart_item_product AS cip WHERE cip.id_cart_item_product = cart_item_product.id) AS price,
		(SELECT SUM(cip.qty*cip.sell_price) FROM cart_item_product AS cip WHERE cip.id_cart_item_product = cart_item_product.id) AS sell_price,
		(SELECT cart_item.qty*ROUND(SUM(cip.subtotal),2) FROM cart_item_product AS cip WHERE cip.id_cart_item_product = cart_item_product.id) AS subtotal
		FROM
		cart_item
		INNER JOIN
		(cart_item_product CROSS JOIN product)
		ON
		(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id)
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
		AND
		cart_item.id_cart_discount = 0
		AND
		product.product_type != 0 
		ORDER BY
		cart_item.id ASC')) throw new Exception('An error occured while trying to get parent combo/bundle products.'."\r\n\r\n".$this->mysqli->error);	
		
		if ($result->num_rows) {	
			/* Prepare the statement */
			if (!$stmt_product_upd = $this->mysqli->prepare('UPDATE
			cart_item_product
			SET
			price = ?,
			sell_price = ?,
			subtotal = ?
			WHERE
			id = ?')) throw new Exception('An error occured while trying to prepare update item product statement.'."\r\n\r\n".$this->mysqli->error);									
				
			while ($row = $result->fetch_assoc()) {
				if (!$stmt_product_upd->bind_param("dddi", $row['price'], $row['sell_price'], $row['subtotal'], $row['id'])) throw new Exception('An error occured while trying to bind params to update item product statement.'."\r\n\r\n".$this->mysqli->error);
								
				/* Execute the statement */
				if (!$stmt_product_upd->execute()) throw new Exception('An error occured while trying to update item product.'."\r\n\r\n".$this->mysqli->error);							
			}
		}
		$result->free();	
		
		// if we cant apply rebates on this customer type, delete all
		if ($this->id_customer_type && !$this->apply_on_rebate) {
			if (!$result_discount = $this->mysqli->query('SELECT
			cart_discount.id
			FROM
			cart_discount
			INNER JOIN
			rebate_coupon
			ON
			(cart_discount.id_rebate_coupon = rebate_coupon.id)
			WHERE
			cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to get expired discounts.'."\r\n\r\n".$this->mysqli->error);	
			
			while ($row_discount = $result_discount->fetch_assoc()) {
				$this->del_discount($row_discount['id']);	
			}	
		}		
		
		if (!$this->id_customer_type || $this->id_customer_type && $this->apply_on_rebate) {								
			// check for expired discounts and delete them if any
			if (!$result_discount = $this->mysqli->query('SELECT
			cart_discount.id
			FROM
			cart_discount
			INNER JOIN
			rebate_coupon
			ON
			(cart_discount.id_rebate_coupon = rebate_coupon.id)
			WHERE
			cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"
			AND
			(
				rebate_coupon.active = 0 
				OR 
				rebate_coupon.end_date != "0000-00-00 00:00:00" AND rebate_coupon.end_date <= "'.$this->mysqli->escape_string(date('Y-m-d H:i:s')).'"
			)')) throw new Exception('An error occured while trying to get expired discounts.'."\r\n\r\n".$this->mysqli->error);	
			
			while ($row_discount = $result_discount->fetch_assoc()) {
				$this->del_discount($row_discount['id']);	
			}		
			
			// update product discount prices
			if (!$result_discount = $this->mysqli->query('SELECT 
			cart_discount.id,
			rebate_coupon.discount_type,
			rebate_coupon.discount
			FROM 
			cart_discount 
			INNER JOIN 
			rebate_coupon
			ON
			(cart_discount.id_rebate_coupon = rebate_coupon.id)
			WHERE
			cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"
			AND
			rebate_coupon.type = 0
			ORDER BY 
			rebate_coupon.coupon ASC')) throw new Exception('An error occured while trying to get product discounts.'."\r\n\r\n".$this->mysqli->error);
			
			if ($result_discount->num_rows) {
				/* Prepare the statement */					
				if (!$stmt_discount = $this->mysqli->prepare('SELECT
				cart_discount_item_product.id,
				cart_item_product.qty,
				get_product_discounted_price(cart_item.id,cart_discount.id_rebate_coupon) AS sell_price
				FROM
				cart_discount_item_product
				INNER JOIN
				(cart_item_product CROSS JOIN cart_item)
				ON
				(cart_discount_item_product.id_cart_item_product = cart_item_product.id AND cart_item_product.id_cart_item = cart_item.id)
				INNER JOIN
				cart_discount 
				ON
				(cart_discount_item_product.id_cart_discount = cart_discount.id)
				WHERE
				cart_discount_item_product.id_cart_discount = ?
				ORDER BY
				cart_discount_item_product.id ASC')) throw new Exception('An error occured while trying to prepare get product variants statement.'."\r\n\r\n".$this->mysqli->error);				
				
				/* Prepare statement */
				if (!$stmt_discount_upd = $this->mysqli->prepare('UPDATE
				cart_discount_item_product
				SET
				cart_discount_item_product.amount = ?
				WHERE
				cart_discount_item_product.id = ?
				')) throw new Exception('An error occured while trying to prepare update discount price statement.'."\r\n\r\n".$this->mysqli->error);				
				
				while ($row_discount = $result_discount->fetch_assoc()) {							
					if (!$stmt_discount->bind_param("i", $row_discount['id'])) throw new Exception('An error occured while trying to bind params to get discount products statement.'."\r\n\r\n".$this->mysqli->error);
					
					/* Execute the statement */
					if (!$stmt_discount->execute()) throw new Exception('An error occured while trying to get discount products statement.'."\r\n\r\n".$this->mysqli->error);	
					
					/* store result */
					$stmt_discount->store_result();		
					
					if ($stmt_discount->num_rows) {	
						/* bind result variables */
						$stmt_discount->bind_result($id_cart_discount_item_product, $p_qty, $p_sell_price);																											
						
						while ($stmt_discount->fetch()) {					
							$amount = 0;
							
							switch ($row_discount['discount_type']) {
								// fixed
								case 0:
									$amount = $p_qty*($row_discount['discount'] > $p_sell_price ? $p_sell_price:$row_discount['discount']);
									break;	
								// percent
								case 1:
									$amount = $p_qty*round(($p_sell_price*$row_discount['discount'])/100,2);
									break;
							}
											
							if (!$stmt_discount_upd->bind_param("di", $amount, $id_cart_discount_item_product)) throw new Exception('An error occured while trying to bind params to update product discount statement.'."\r\n\r\n".$this->mysqli->error);	
							
							/* Execute the statement */
							if (!$stmt_discount_upd->execute()) throw new Exception('An error occured while trying to update product discount.'."\r\n\r\n".$this->mysqli->error);	
						}
					}
				}
				
				$stmt_discount->close();
				$stmt_discount_upd->close();
			}
			$result_discount->free();
			
			// update buy and get product prices
			if (!$this->mysqli->query('UPDATE 
			cart_item
			INNER JOIN
			(cart_item_product CROSS JOIN product)
			ON
			(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id)
			INNER JOIN
			(cart_discount CROSS JOIN rebate_coupon)
			ON
			(cart_item.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = rebate_coupon.id)			
			LEFT JOIN
			product_variant
			ON
			(cart_item_product.id_product_variant = product_variant.id)
			SET 						
			cart_item_product.price = get_product_cart_price(product.sell_price, product_variant.price_type, product_variant.price, product.id, product.product_type, cart_item.id_cart, 1),
			cart_item_product.sell_price = get_product_cart_price(product.sell_price, product_variant.price_type, product_variant.price, product.id, product.product_type, cart_item.id_cart, 1),
			cart_item_product.subtotal = get_product_cart_price(product.sell_price, product_variant.price_type, product_variant.price, product.id, product.product_type, cart_item.id_cart, 1)
			WHERE
			cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
			AND
			cart_item.id_cart_discount != 0
			AND
			rebate_coupon.type = 2
			AND
			product.product_type = 0')) throw new Exception('An error occured while trying to update item price.'."\r\n\r\n".$this->mysqli->error);	
			
			// update buy and get discounts
			if (!$this->mysqli->query('UPDATE
			cart_discount_item_product
			INNER JOIN
			(cart_discount CROSS JOIN rebate_coupon)
			ON
			(cart_discount_item_product.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = rebate_coupon.id)
			INNER JOIN
			(cart_item_product CROSS JOIN cart_item)
			ON
			(cart_discount_item_product.id_cart_item_product = cart_item_product.id AND cart_item_product.id_cart_item = cart_item.id)
			SET
			cart_discount_item_product.amount = (CASE rebate_coupon.discount_type 
				WHEN 0 THEN
					IF(rebate_coupon.discount > cart_item_product.subtotal,cart_item_product.subtotal,cart_item_product.qty*rebate_coupon.discount)
				WHEN 1 THEN
					ROUND((cart_item_product.subtotal*rebate_coupon.discount)/100,2)
			END)
			WHERE
			cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"
			AND
			rebate_coupon.type = 2
			AND
			cart_item.id_cart_discount != 0
			')) throw new Exception('An error occured while trying to update item discount price.'."\r\n\r\n".$this->mysqli->error);			
			
			// update free gift product price
			if (!$this->mysqli->query('UPDATE 
			cart_item
			INNER JOIN
			(cart_item_product CROSS JOIN product)
			ON
			(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id)
			INNER JOIN
			(cart_discount CROSS JOIN rebate_coupon)
			ON
			(cart_item.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = rebate_coupon.id)
			LEFT JOIN
			product_variant
			ON
			(cart_item_product.id_product_variant = product_variant.id)
			SET 			
			cart_item_product.price = get_product_cart_price(product.sell_price, product_variant.price_type, product_variant.price, product.id, product.product_type, cart_item.id_cart, 1)
			WHERE
			cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
			AND
			cart_item.id_cart_discount != 0
			AND
			rebate_coupon.type = 3
			AND
			product.product_type = 0')) throw new Exception('An error occured while trying to update item price.'."\r\n\r\n".$this->mysqli->error);				
		}
		
		// update option prices
		if (!$this->mysqli->query('UPDATE
		cart_item
		INNER JOIN
		(cart_item_option CROSS JOIN options)
		ON
		(cart_item.id = cart_item_option.id_cart_item AND cart_item_option.id_options = options.id)
		INNER JOIN
		cart_item_product
		ON
		(cart_item.id = cart_item_product.id_cart_item)		
		SET 
		cart_item_option.price = options.price,
		cart_item_option.sell_price = IF("'.$this->mysqli->escape_string($current_datetime).'" BETWEEN options.special_price_from_date AND options.special_price_to_date,options.special_price,options.price),
		cart_item_option.subtotal = (cart_item.qty*cart_item_option.qty*cart_item_option.sell_price)
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to update item option price.'."\r\n\r\n".$this->mysqli->error);				
		
		// update cart subtotal, include product and option discounts
		if (!$this->mysqli->query('UPDATE
		cart
		SET
		cart.subtotal = IFNULL((SELECT 
		SUM(cart_item_product.subtotal)
		FROM
		cart_item
		INNER JOIN
		cart_item_product
		ON
		(cart_item.id = cart_item_product.id_cart_item)
		WHERE
		cart_item.id_cart = cart.id),0)+IFNULL((SELECT
		SUM(cart_item_option.subtotal)
		FROM 
		cart_item
		INNER JOIN
		cart_item_option
		ON
		(cart_item.id = cart_item_option.id_cart_item)
		WHERE
		cart_item.id_cart = cart.id),0)-IFNULL((SELECT
		SUM(cart_discount_item_product.amount)
		FROM
		cart_discount_item_product 
		INNER JOIN
		(cart_discount CROSS JOIN rebate_coupon)
		ON
		(cart_discount_item_product.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = rebate_coupon.id)
		WHERE
		cart_discount.id_cart = cart.id
		AND
		(rebate_coupon.type = 0 OR rebate_coupon.type = 2)),0)
		WHERE
		cart.id = "'.$this->mysqli->escape_string($this->id).'"')) {
			throw new Exception('An error occured while trying to update cart subtotal.'."\r\n\r\n".$this->mysqli->error);			
		}		
		
		// get subtotal
		if (!$result = $this->mysqli->query('SELECT 
		subtotal
		FROM
		cart
		WHERE
		id = "'.$this->mysqli->escape_string($this->id).'"
		LIMIT 1')) throw new Exception('An error occured while trying to get cart subtotal.'."\r\n\r\n".$this->mysqli->error);
		
		$row = $result->fetch_assoc();
		$this->subtotal = $row['subtotal'];	
		$subtotal = $this->subtotal;
		
		$result->free();		
		
		if (!$this->id_customer_type || $this->id_customer_type && $this->apply_on_rebate) {		
			// Cash or % off cart subtotal or first purchase (Rebate) 
			// check if already in cart, if not check if we have one applicable
			
			if (!$result_discount = $this->mysqli->query('SELECT
			cart_discount.id,
			IF(cart.subtotal < rebate_coupon.min_cart_value OR IF(rebate_coupon.type = 5 AND (SELECT COUNT(orders.id) FROM orders WHERE orders.id_customer = cart.id_customer) > 0,1,0) = 1,1,0) AS remove_from_cart
			FROM
			cart_discount 
			INNER JOIN
			rebate_coupon
			ON
			(cart_discount.id_rebate_coupon = rebate_coupon.id)
			INNER JOIN
			cart
			ON
			(cart_discount.id_cart = cart.id)
			WHERE
			cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"
			AND
			(rebate_coupon.type = 1 OR rebate_coupon.type = 5)
			')) throw new Exception('An error occured while trying to check if we have a cart rebate applied.'."\r\n\r\n".$this->mysqli->error);		
			
			$id_cart_discount = 0;
			if (!$result_discount->num_rows) {
				// check if we have a cart rebate we can apply
				if ($subtotal > 0) {
					if (!$result_get_discount = $this->mysqli->query('SELECT
					rebate_coupon.id			
					FROM
					rebate_coupon
					WHERE
					(rebate_coupon.type = 1 OR (rebate_coupon.type = 5 AND (SELECT COUNT(orders.id) FROM orders WHERE orders.id_customer = "'.$this->mysqli->escape_string($this->id_customer).'") = 0))
					AND
					rebate_coupon.coupon = 0
					AND
					rebate_coupon.active = 1
					AND
					(
						rebate_coupon.end_date = "0000-00-00 00:00:00"
						AND
						"'.$this->mysqli->escape_string($current_datetime).'" BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
					)
					AND
					rebate_coupon.min_cart_value < "'.$this->mysqli->escape_string($subtotal).'"
					ORDER BY 
					(CASE rebate_coupon.discount_type
						WHEN 0 THEN
							(rebate_coupon.discount/"'.$this->mysqli->escape_string($subtotal).'")
						WHEN 1 THEN
							(rebate_coupon.discount/100)
					END) DESC, (CASE rebate_coupon.type
						WHEN 1 THEN 1				
						WHEN 5 THEN 0
					END) ASC
					LIMIT 1')) throw new Exception('An error occured while trying to check if we have a cart rebate applicable.'."\r\n\r\n".$this->mysqli->error);		
					
					if ($result_get_discount->num_rows) {
						$row_get_discount = $result_get_discount->fetch_assoc();
						
						/* Prepare statement */
						if (!$stmt_discount = $this->mysqli->prepare('INSERT INTO 
						cart_discount
						SET 
						cart_discount.id_cart = ?,
						cart_discount.id_rebate_coupon = ?		
						')) throw new Exception('An error occured while trying to prepare add discount statement.'."\r\n\r\n".$this->mysqli->error);											
						
						if (!$stmt_discount->bind_param("ii", $this->id, $row_get_discount['id'])) throw new Exception('An error occured while trying to bind params to add discount statement.'."\r\n\r\n".$this->mysqli->error);
					
						/* Execute the statement */
						if (!$stmt_discount->execute()) throw new Exception('An error occured while trying to add discount.'."\r\n\r\n".$this->mysqli->error);									
						
						$id_cart_discount = $this->mysqli->insert_id;					
					}
					$result_get_discount->free();
				}				
			} else {
				$row_discount = $result_discount->fetch_assoc();
				
				if ($row_discount['remove_from_cart']) { 
					$this->del_discount($row_discount['id']); 
				} else {
					$id_cart_discount = $row_discount['id'];
				}
			}
			$result_discount->free();			
					
			// update subtotal with cart discounts 
			if (!$result_discount = $this->mysqli->query('SELECT 
			cart_discount.id,
			rebate_coupon.id AS id_rebate_coupon,
			rebate_coupon.discount_type,
			rebate_coupon.discount,
			rebate_coupon.coupon
			FROM 
			cart_discount 
			INNER JOIN 
			rebate_coupon
			ON
			(cart_discount.id_rebate_coupon = rebate_coupon.id)
			WHERE
			cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"
			AND
			(rebate_coupon.type = 1 OR rebate_coupon.type = 5)
			ORDER BY rebate_coupon.coupon ASC')) throw new Exception('An error occured while trying to get cart discounts.'."\r\n\r\n".$this->mysqli->error);
			
			if ($result_discount->num_rows) {
				/* Prepare statement */
				if (!$stmt_product = $this->mysqli->prepare('SELECT
				cart_item_product.id,
				cart_item.qty,
				get_product_discounted_price(cart_item.id,?) AS sell_price,
				cart_discount_item_product.id AS id_cart_discount_item_product
				FROM
				cart_item
				INNER JOIN
				cart_item_product
				ON
				(cart_item.id = cart_item_product.id_cart_item)
				
				LEFT JOIN						
				(cart_discount_item_product CROSS JOIN cart_discount CROSS JOIN rebate_coupon)
				ON
				(cart_item_product.id = cart_discount_item_product.id_cart_item_product AND cart_discount_item_product.id_cart_discount = ? AND cart_discount_item_product.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = rebate_coupon.id)	
				
				LEFT JOIN 
				(cart_discount AS cd CROSS JOIN rebate_coupon AS rc)
				ON
				(cart_item.id_cart_discount = cd.id AND cd.id_rebate_coupon = rc.id)
								
				WHERE
				cart_item.id_cart = ?
				AND
				(
					cd.id IS NULL
					OR
					(cd.id IS NOT NULL AND rc.type = 2 AND (rc.discount_type = 0 OR (rc.discount_type = 1 AND rc.discount < 100)))
				)
				ORDER BY
				cart_item.id ASC
				')) throw new Exception('An error occured while trying to prepare get products statement.'."\r\n\r\n".$this->mysqli->error);	
				
				/* Prepare statement */
				if (!$stmt_product_discount_upd = $this->mysqli->prepare('UPDATE
				cart_discount_item_product
				SET
				cart_discount_item_product.amount = ?
				WHERE
				cart_discount_item_product.id = ?
				')) throw new Exception('An error occured while trying to prepare update product discount statement.'."\r\n\r\n".$this->mysqli->error);	
				
				/* Prepare statement */
				if (!$stmt_product_discount = $this->mysqli->prepare('INSERT INTO
				cart_discount_item_product
				SET
				cart_discount_item_product.id_cart_discount = ?,
				cart_discount_item_product.id_cart_item_product = ?,
				cart_discount_item_product.amount = ?
				')) throw new Exception('An error occured while trying to prepare add product discount statement.'."\r\n\r\n".$this->mysqli->error);	
				
				
				/* Prepare the statement */
				if (!$stmt_sub_product = $this->mysqli->prepare('SELECT 
				cart_item_product.id,
				cart_item_product.qty,
				(cart_item_product.sell_price-IF(? = 0,0,IFNULL((SELECT 
					SUM(cdip.amount/cart_item_product.qty)
					FROM 
					cart_discount_item_product AS cdip
					INNER JOIN
					(cart_discount AS cd CROSS JOIN rebate_coupon AS rc)
					ON
					(cdip.id_cart_discount = cd.id AND cd.id_rebate_coupon = rc.id)
					WHERE
					cdip.id_cart_item_product = cart_item_product.id 
					AND
					rc.coupon = 0
					),0))) AS sell_price,
				cart_discount_item_product.id AS id_cart_discount_item_product
				FROM 
				cart_item_product
				LEFT JOIN
				(cart_discount_item_product CROSS JOIN cart_discount)
				ON
				(cart_item_product.id = cart_discount_item_product.id_cart_item_product AND cart_discount_item_product.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = ?)
				WHERE
				cart_item_product.id_cart_item_product = ?
				ORDER BY 
				cart_item_product.id ASC
				')) throw new Exception('An error occured while trying to prepare get sub products statement.'."\r\n\r\n".$this->mysqli->error);	
				
				/* Prepare the statement */
				if (!$stmt_option = $this->mysqli->prepare('SELECT 
				cart_item_option.id,
				cart_item_option.subtotal,
				cart_discount_item_option.id AS id_cart_discount_item_option
				FROM 
				cart_item
				INNER JOIN
				cart_item_option
				ON
				(cart_item.id = cart_item_option.id_cart_item)
				
				LEFT JOIN						
				(cart_discount_item_option CROSS JOIN cart_discount CROSS JOIN rebate_coupon)
				ON
				(cart_item_option.id = cart_discount_item_option.id_cart_item_option AND cart_discount_item_option.id_cart_discount = ? AND cart_discount_item_option.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = rebate_coupon.id)			
				WHERE
				cart_item.id_cart = ?
				ORDER BY 
				cart_item.id ASC,
				cart_item_option.id ASC
				')) throw new Exception('An error occured while trying to prepare get product options statement.'."\r\n\r\n".$this->mysqli->error);	
				
				
				/* Prepare statement */
				if (!$stmt_option_discount_upd = $this->mysqli->prepare('UPDATE
				cart_discount_item_option
				SET
				cart_discount_item_option.amount = ?
				WHERE
				cart_discount_item_option.id = ?
				')) throw new Exception('An error occured while trying to prepare update product option discount statement.'."\r\n\r\n".$this->mysqli->error);	
				
				/* Prepare statement */
				if (!$stmt_option_discount = $this->mysqli->prepare('INSERT INTO
				cart_discount_item_option
				SET
				cart_discount_item_option.id_cart_discount = ?,
				cart_discount_item_option.id_cart_item_option = ?,
				cart_discount_item_option.amount = ?
				')) throw new Exception('An error occured while trying to prepare add product option discount statement.'."\r\n\r\n".$this->mysqli->error);	
				
				//echo $subtotal.'<br />';
				
				// loop through each discount
				while ($row_discount = $result_discount->fetch_assoc()) {
					$amount = 0;
					$discount_pc = 0;
					$discount = 0;
					$discount_sum = 0;
					
					$tmp_discount = array();
					
					// get discount percentage
					// calculate discount amount
					switch ($row_discount['discount_type']) {
						// fixed
						case 0:						
							$discount_pc = ($subtotal > 0) ? $row_discount['discount']/$subtotal:0;
							$discount = ($row_discount['discount'] > $subtotal ? $subtotal:$row_discount['discount']);
							break;	
						// percent
						case 1:						
							$discount_pc = $row_discount['discount']/100;
							$discount = round(($subtotal*$row_discount['discount'])/100,2);
							break;
					}
					
					// remove discount amount from subtotal for next discount
					$subtotal -= $discount;		
					
					if (!$stmt_product->bind_param("iii", $row_discount['id_rebate_coupon'], $row_discount['id'], $this->id)) throw new Exception('An error occured while trying to bind params to get products statement.'."\r\n\r\n".$this->mysqli->error);
				
					/* Execute the statement */
					if (!$stmt_product->execute()) throw new Exception('An error occured while trying to get products.'."\r\n\r\n".$this->mysqli->error);								
					
					/* store result */
					$stmt_product->store_result();	
					
					// get list of all products 
					if ($stmt_product->num_rows) {
						/* bind result variables */
						$stmt_product->bind_result($p_id_cart_item_product, $p_qty, $p_sell_price, $p_id_cart_discount_item_product);									
											
						while ($stmt_product->fetch()) {
							// calculate discount amount on product sell price
							$amount = $p_qty*round($p_sell_price*$discount_pc,2);
							// add amount to discount sum
							$discount_sum += $amount;	
							
							//echo $p_qty.'x '.$p_sell_price.' '.$amount.' '.$discount_pc.'<br />';
													
							if ($p_id_cart_discount_item_product) { 						
								if (!$stmt_product_discount_upd->bind_param("di", $amount, $p_id_cart_discount_item_product)) throw new Exception('An error occured while trying to bind params to update product discount statement.'."\r\n\r\n".$this->mysqli->error);
							
								/* Execute the statement */
								if (!$stmt_product_discount_upd->execute()) throw new Exception('An error occured while trying to update product discount.'."\r\n\r\n".$this->mysqli->error);						
							} else {
								if (!$stmt_product_discount->bind_param("iid", $row_discount['id'], $p_id_cart_item_product, $amount)) throw new Exception('An error occured while trying to bind params to add product discount statement.'."\r\n\r\n".$this->mysqli->error);
							
								/* Execute the statement */
								if (!$stmt_product_discount->execute()) throw new Exception('An error occured while trying to add product discount.'."\r\n\r\n".$this->mysqli->error);				
								$p_id_cart_discount_item_product = $this->mysqli->insert_id;							
							}
							
							// if amount is bigger than amount stored in tmp_discount array, then update with current
							if ($amount > $tmp_discount['amount']) {
								$tmp_discount = array(
									'id' => $p_id_cart_discount_item_product,
									'amount' => $amount,
									'sub_product' => array(),
								);
							}						
													
							if (!$stmt_sub_product->bind_param("iii", $row_discount['coupon'],$row_discount['id_rebate_coupon'],$p_id_cart_item_product)) throw new Exception('An error occured while trying to bind params to get product discounts statement.'."\r\n\r\n".$this->mysqli->error);
						
							/* Execute the statement */
							if (!$stmt_sub_product->execute()) throw new Exception('An error occured while trying to get product discounts.'."\r\n\r\n".$this->mysqli->error);								
							
							/* store result */
							$stmt_sub_product->store_result();	
							
							// get sub products if combo or bundle
							if ($stmt_sub_product->num_rows) {
								/* bind result variables */
								$stmt_sub_product->bind_result($sub_id_cart_item_product, $sub_qty, $sub_sell_price, $sub_id_cart_discount_item_product);													
								
								$sub_discount_sum = 0;
								$tmp_sub_discount = array();
								while ($stmt_sub_product->fetch()) {
									$sub_amount = $p_qty*$sub_qty*round($sub_sell_price*$discount_pc,2);
									
									$sub_discount_sum += $sub_amount;
									
									//echo $p_qty.'x '.$sub_qty.'x '.$sub_sell_price.' '.$discount_pc.' = '.$sub_amount.'<br />';
									
									if ($sub_id_cart_discount_item_product) {								
										if (!$stmt_product_discount_upd->bind_param("di", $sub_amount, $sub_id_cart_discount_item_product)) throw new Exception('An error occured while trying to bind params to update sub product discount statement.'."\r\n\r\n".$this->mysqli->error);
									
										/* Execute the statement */
										if (!$stmt_product_discount_upd->execute()) throw new Exception('An error occured while trying to update sub product discount.'."\r\n\r\n".$this->mysqli->error);												
									} else {
										if (!$stmt_product_discount->bind_param("iid", $row_discount['id'], $sub_id_cart_item_product, $sub_amount)) throw new Exception('An error occured while trying to bind params to add sub product discount statement.'."\r\n\r\n".$this->mysqli->error);
									
										/* Execute the statement */
										if (!$stmt_product_discount->execute()) throw new Exception('An error occured while trying to add sub product discount.'."\r\n\r\n".$this->mysqli->error);						
										$sub_id_cart_discount_item_product = $this->mysqli->insert_id;						
									}
									
									if ($sub_amount > $tmp_sub_discount['amount']) {
										$tmp_sub_discount = array(
											'id' => $sub_id_cart_discount_item_product,
											'amount' => $sub_amount,
										);									
									}	
									
									if ($tmp_discount['id'] == $p_id_cart_discount_item_product) {
										if ($sub_amount > $tmp_discount['sub_product']['amount']) {
											$tmp_discount['sub_product'] = array(
												'id' => $sub_id_cart_discount_item_product,
												'amount' => $sub_amount
											);
										}
										
										// add sub product discount sum to tmp array
										$tmp_discount['sub_product_amount'] += $sub_amount;
									}
								}		
								
								// if discount amount is not equal to sub discount amount
								if ($amount != $sub_discount_sum) {
									$diff = $amount-$sub_discount_sum;															
									
									// add or remove difference
									$tmp_sub_discount['amount'] += $diff;				
									
									if (!$stmt_product_discount_upd->bind_param("di", $tmp_sub_discount['amount'], $tmp_sub_discount['id'])) throw new Exception('An error occured while trying to bind params to update product discount statement.'."\r\n\r\n".$this->mysqli->error);
								
									/* Execute the statement */
									if (!$stmt_product_discount_upd->execute()) throw new Exception('An error occured while trying to update product discount.'."\r\n\r\n".$this->mysqli->error);		
									
									$sub_discount_sum += $diff;									
								}														
							}												
						}					
					}
					
					if (!$stmt_option->bind_param("ii", $row_discount['id'], $this->id)) throw new Exception('An error occured while trying to bind params to get product options statement.'."\r\n\r\n".$this->mysqli->error);
				
					/* Execute the statement */
					if (!$stmt_option->execute()) throw new Exception('An error occured while trying to get product options.'."\r\n\r\n".$this->mysqli->error);								
					
					/* store result */
					$stmt_option->store_result();	
					
					if ($stmt_option->num_rows) {
						/* bind result variables */
						$stmt_option->bind_result($p_id_cart_item_option, $p_subtotal, $p_id_cart_discount_item_option);									
	
						while ($stmt_option->fetch()) {	
							$amount = round($p_subtotal*$discount_pc,2);
							$discount_sum += $amount;		
							
							//echo $p_subtotal.' '.$discount_pc.' = '.$amount.'<br />';
													
							if ($p_id_cart_discount_item_option) { 						
								if (!$stmt_option_discount_upd->bind_param("di", $amount, $p_id_cart_discount_item_option)) throw new Exception('An error occured while trying to bind params to update product option discount statement.'."\r\n\r\n".$this->mysqli->error);
							
								/* Execute the statement */
								if (!$stmt_option_discount_upd->execute()) throw new Exception('An error occured while trying to update product option discount.'."\r\n\r\n".$this->mysqli->error);						
							} else {
								if (!$stmt_option_discount->bind_param("iid", $row_discount['id'], $p_id_cart_item_option, $amount)) throw new Exception('An error occured while trying to bind params to add product option discount statement.'."\r\n\r\n".$this->mysqli->error);
							
								/* Execute the statement */
								if (!$stmt_option_discount->execute()) throw new Exception('An error occured while trying to add product option discount.'."\r\n\r\n".$this->mysqli->error);				
								$sub_id_cart_discount_item_product = $this->mysqli->insert_id;							
							}					
						}					
					}	
					
					// check if discount and discount_sum match		
					// this is to fix the 0.01 differences that might occur								
					if ($discount != $discount_sum) {					
						$diff = $discount-$discount_sum;	
						
						// add or remove difference
						$tmp_discount['amount'] += $diff;				
						
						if (!$stmt_product_discount_upd->bind_param("di", $tmp_discount['amount'], $tmp_discount['id'])) throw new Exception('An error occured while trying to bind params to update product discount statement.'."\r\n\r\n".$this->mysqli->error);
					
						/* Execute the statement */
						if (!$stmt_product_discount_upd->execute()) throw new Exception('An error occured while trying to update product discount.'."\r\n\r\n".$this->mysqli->error);				
						
						
						if (sizeof($tmp_discount['sub_product']) && $tmp_discount['amount'] != $tmp_discount['sub_product_amount']) {
							$diff = $tmp_discount['amount']-$tmp_discount['sub_product_amount'];
							
							// add or remove difference
							$tmp_discount['sub_product']['amount'] += $diff;	
							
							//echo '<pre>'.print_r($tmp_discount,1).'</pre>';	
							
							if (!$stmt_product_discount_upd->bind_param("di", $tmp_discount['sub_product']['amount'], $tmp_discount['sub_product']['id'])) throw new Exception('An error occured while trying to bind params to update product discount statement.'."\r\n\r\n".$this->mysqli->error);
					
							/* Execute the statement */
							if (!$stmt_product_discount_upd->execute()) throw new Exception('An error occured while trying to update product discount.'."\r\n\r\n".$this->mysqli->error);			
						}
					}													
				}
				
				$stmt_option->close();
				$stmt_product->close();
				$stmt_product_discount->close();
				$stmt_product_discount_upd->close();
			}
			$result_discount->free();			
			
			// update cart subtotal, include product and option discounts and cart discounts
			if (!$this->mysqli->query('UPDATE
			cart
			SET
			cart.subtotal = IFNULL((SELECT 
			SUM(cart_item_product.subtotal)
			FROM
			cart_item
			INNER JOIN
			cart_item_product
			ON
			(cart_item.id = cart_item_product.id_cart_item)
			WHERE
			cart_item.id_cart = cart.id),0)+IFNULL((SELECT
			SUM(cart_item_option.subtotal)
			FROM 
			cart_item
			INNER JOIN
			cart_item_option
			ON
			(cart_item.id = cart_item_option.id_cart_item)
			WHERE
			cart_item.id_cart = cart.id),0)-IFNULL((SELECT
			SUM(cart_discount_item_product.amount)
			FROM
			cart_discount_item_product 
			INNER JOIN
			(cart_item_product CROSS JOIN cart_item)
			ON
			(cart_discount_item_product.id_cart_item_product = cart_item_product.id AND cart_item_product.id_cart_item = cart_item.id)
			INNER JOIN
			(cart_discount CROSS JOIN rebate_coupon)
			ON
			(cart_discount_item_product.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = rebate_coupon.id)
			WHERE
			cart_discount.id_cart = cart.id),0)-IFNULL((SELECT
			SUM(cart_discount_item_option.amount)
			FROM
			cart_discount_item_option
			INNER JOIN
			(cart_discount CROSS JOIN rebate_coupon)
			ON
			(cart_discount_item_option.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = rebate_coupon.id)
			WHERE
			cart_discount.id_cart = cart.id),0)
			WHERE
			cart.id = "'.$this->mysqli->escape_string($this->id).'"')) {
				throw new Exception('An error occured while trying to update cart subtotal.'."\r\n\r\n".$this->mysqli->error);			
			}		
		}
		
		// get subtotal
		if (!$result = $this->mysqli->query('SELECT 
		subtotal
		FROM
		cart
		WHERE
		id = "'.$this->mysqli->escape_string($this->id).'"
		LIMIT 1')) throw new Exception('An error occured while trying to get cart subtotal.'."\r\n\r\n".$this->mysqli->error);
		
		$row = $result->fetch_assoc();
		$this->subtotal = $row['subtotal'];	
		$subtotal = $this->subtotal;
		
		$result->free();		
		
		if (!$this->id_customer_type || $this->id_customer_type && $this->apply_on_rebate) {				
			// check if we have any free gift or free shipping rebate/coupon in cart and validate with subtotal
			if (!$result = $this->mysqli->query('SELECT 
			cart_discount.id,
			rebate_coupon.type,
			rebate_coupon.min_cart_value
			FROM
			cart_discount
			INNER JOIN
			rebate_coupon 
			ON
			(cart_discount.id_rebate_coupon = rebate_coupon.id)
			WHERE
			cart_discount.id_cart = "'.$this->mysqli->escape_string($this->id).'"
			AND
			(rebate_coupon.type = 3 OR rebate_coupon.type = 4)')) throw new Exception('An error occured while trying to get cart discounts.'."\r\n\r\n".$this->mysqli->error);	
			
			// check if free shipping applies
			if ($this->shipping_country_code) {
				$free_shipping = $this->get_free_shipping_yes_no($this->shipping_country_code, $this->shipping_state_code);
			}
			
			while ($row = $result->fetch_assoc()) {			
				if ($subtotal < $row['min_cart_value']) $this->del_discount($row['id']);	
				// if we have a free shipping rebate or coupon applied and free shipping no longer applies, remove it
				else if ($row['type'] == 4 && !$free_shipping) $this->del_discount($row['id']);
			}		 
			$result->free();
			
			// if free shipping is set in cart, and free shipping no longer applies, remove it
			if ($this->free_shipping && !$free_shipping) {
				if (!$this->mysqli->query('UPDATE 
				cart
				SET
				free_shipping = 0
				WHERE
				id = "'.$this->mysqli->escape_string($this->id).'" 
				LIMIT 1')) throw new Exception('An error occured while trying to update cart free shipping.'."\r\n\r\n".$this->mysqli->error);					
			}			
		}
	}
	
	// return id_tax_rule
	public function get_id_tax_rule($country_code,$state_code="",$zip="")
	{
		
		$id_tax_rule = 0;
		
		if ($result = $this->mysqli->query('SELECT 
			id
			FROM
			tax_rule
			WHERE
			active = 1
			AND
			country_code = "'.$this->mysqli->escape_string($country_code).'"
			AND
			(
				state_code = "'.$this->mysqli->escape_string($state_code).'"
				OR 
				state_code = ""
			) 
			AND 
			(
				("'.$this->mysqli->escape_string($zip).'" BETWEEN zip_from AND zip_to)
				OR
				(zip_from = "" AND zip_to = "")
			)		
			ORDER BY 
			IF(state_code!="",0,1) ASC
			LIMIT 1')) {
			
			if($result->num_rows){
				$row = $result->fetch_assoc();
				$id_tax_rule = $row['id'];
			}else{
				$id_tax_rule = 0;
			}
			
			
			$result->free();
		}else {
			throw new Exception('An error occured while trying to get id tax rule.'."\r\n\r\n".$this->mysqli->error);		
		}
		return $id_tax_rule;
	}
	
	public function calculate_taxes()
	{
		// get applicable taxe
		$id_tax_rule = $this->id_tax_rule;
		
		// get tax rule taxes 
		// delete previous tax rates
		if (!$this->mysqli->query('DELETE FROM
		cart_item_product_tax,
		cipt,
		cart_item_option_tax
		USING
		cart_item 
		LEFT JOIN 
		cart_item_product
		ON
		(cart_item.id = cart_item_product.id_cart_item)
		LEFT JOIN
		cart_item_product_tax
		ON
		(cart_item_product.id = cart_item_product_tax.id_cart_item_product)
		
		LEFT JOIN 
		(cart_item_product AS cip CROSS JOIN cart_item_product_tax AS cipt)
		ON
		(cart_item_product.id = cip.id_cart_item_product AND cip.id = cipt.id_cart_item_product)		
		
		LEFT JOIN
		(cart_item_option CROSS JOIN cart_item_option_tax) 
		ON
		(cart_item.id = cart_item_option.id_cart_item AND cart_item_option.id = cart_item_option_tax.id_cart_item_option)
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"')) 
		throw new Exception('An error occured while trying to delete previous tax rates.'."\r\n\r\n".$this->mysqli->error);
		
		if (!$this->mysqli->query('DELETE FROM
		cart_shipping_tax		
		WHERE
		cart_shipping_tax.id_cart = "'.$this->mysqli->escape_string($this->id).'"')) 
		throw new Exception('An error occured while trying to delete previous shipping tax rates.'."\r\n\r\n".$this->mysqli->error);		
		
		// Reinit taxes from cart_item_product
		if (!$this->mysqli->query('UPDATE
		cart_item_product
		INNER JOIN 
		cart_item 
		ON 
		(cart_item_product.id_cart_item = cart_item.id)
		INNER JOIN
		cart
		ON
		(cart_item.id_cart = cart.id)
		SET 
		cart_item_product.taxes = 0,
		cart.taxes = 0
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to delete previous tax rates.'."\r\n\r\n".$this->mysqli->error);
		
		// Reinit taxes from cart_item_option
		if (!$this->mysqli->query('UPDATE
		cart_item_option
		INNER JOIN 
		cart_item 
		ON 
		(cart_item_option.id_cart_item = cart_item.id)
		INNER JOIN
		cart
		ON
		(cart_item.id_cart = cart.id)
		SET 
		cart_item_option.taxes = 0
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to delete previous tax rates.'."\r\n\r\n".$this->mysqli->error);			
		
		if (!$this->taxable) return false;
		
		$applicable_taxes=array();
		if ($id_tax_rule) {
			if (!$result = $this->mysqli->query('SELECT
			id,
			rate,
			stacked
			FROM 
			tax_rule_rate
			WHERE
			id_tax_rule =  "'.$this->mysqli->escape_string($id_tax_rule).'"
			ORDER BY 
			sort_order ASC')) throw new Exception('An error occured while trying to get applicable tax rule rates.'."\r\n\r\n".$this->mysqli->error);		

			if ($result->num_rows) {
				while($row = $result->fetch_assoc()){
					$applicable_taxes[] = $row;
				}
			}
			$result->free();
		}	
		
		// if we have applicable taxes
		if (sizeof($applicable_taxes)) {
			// get sub products for Combo or Bundled Product
			/* Prepare statement */
			if (!$stmt_sub_products = $this->mysqli->prepare('SELECT
			cart_item_product.id,
			(cart_item_product.subtotal-IFNULL((SELECT SUM(cart_discount_item_product.amount) FROM cart_discount_item_product WHERE cart_discount_item_product.id_cart_item_product = cart_item_product.id),0)) AS subtotal,
			product.taxable
			FROM
			cart_item_product 
			INNER JOIN 
			product
			ON
			(cart_item_product.id_product = product.id)
			WHERE
			cart_item_product.id_cart_item_product = ?
			AND
			product.taxable = 1
			ORDER BY 
			cart_item_product.id ASC')) 
			throw new Exception('An error occured while trying to prepare get sub products statement.'."\r\n\r\n".$this->mysqli->error);	
			
			
			// get applicable tax exception
			/* Prepare statement */
			if (!$stmt_tax_exception = $this->mysqli->prepare('SELECT
			tax_rule_exception.id
			FROM
			tax_rule_exception
			INNER JOIN
			tax_rule
			ON
			(tax_rule_exception.id_tax_rule = tax_rule.id)
			WHERE
			tax_rule.id = ?
			AND 
			(
				(tax_rule_exception.id_customer_type = 0 AND tax_rule_exception.id_tax_group = ?)
				OR
				(tax_rule_exception.id_customer_type = ? AND tax_rule_exception.id_tax_group = 0)
				OR
				(tax_rule_exception.id_customer_type = ? AND tax_rule_exception.id_tax_group = ?)
			)
			ORDER BY 
			CASE 
				WHEN tax_rule_exception.id_customer_type != 0 AND tax_rule_exception.id_tax_group != 0 THEN 0
				WHEN tax_rule_exception.id_customer_type != 0 AND tax_rule_exception.id_tax_group = 0 THEN 1
				WHEN tax_rule_exception.id_customer_type = 0 AND tax_rule_exception.id_tax_group != 0 THEN 2
			END
			LIMIT 1')) throw new Exception('An error occured while trying to prepare get tax exception statement.'."\r\n\r\n".$this->mysqli->error);	
				
			
			/* Prepare statement */
			if (!$stmt_tax_exception_rates = $this->mysqli->prepare('SELECT
			tax_rule_exception_rate.rate,
			tax_rule_rate.id,
			tax_rule_rate.stacked
			FROM
			tax_rule
			INNER JOIN
			tax_rule_rate
			ON
			(tax_rule.id = tax_rule_rate.id_tax_rule)
			LEFT JOIN
			tax_rule_exception_rate
			ON
			(tax_rule_rate.id = tax_rule_exception_rate.id_tax_rule_rate)
			WHERE
			tax_rule_exception_rate.id_tax_rule_exception = ?
			ORDER BY 
			tax_rule_rate.sort_order ASC')) throw new Exception('An error occured while trying to prepare get tax exception rates statement.'."\r\n\r\n".$this->mysqli->error);	
			
			
			/* Prepare statement */
			if (!$stmt_add_tax = $this->mysqli->prepare('INSERT INTO 
			cart_item_product_tax
			SET
			id_cart_item_product = ?,
			id_tax_rule_rate = ?,
			amount = ?')) throw new Exception('An error occured while trying to prepare add tax statement.'."\r\n\r\n".$this->mysqli->error);	
				
			
			/* Prepare statement */
			if (!$stmt_upd_taxes = $this->mysqli->prepare('UPDATE
			cart_item_product
			SET
			taxes = ?
			WHERE
			id = ? 
			LIMIT 1')) throw new Exception('An error occured while trying to prepare update taxes statement.'."\r\n\r\n".$this->mysqli->error);	
				
			
			// get cart products				
			if (!$result = $this->mysqli->query('SELECT
			cart_item.id,
			cart_item_product.id AS id_cart_item_product,
			(cart_item_product.subtotal-IFNULL((SELECT SUM(cart_discount_item_product.amount) FROM cart_discount_item_product WHERE cart_discount_item_product.id_cart_item_product = cart_item_product.id),0)) AS subtotal,
			product.taxable,
			product.product_type,
			product.id_tax_group
			FROM
			cart_item 
			INNER JOIN
			(cart_item_product CROSS JOIN product)
			ON
			(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id)
			WHERE
			cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'" 
			AND
			(product.taxable = 1 OR product.product_type != 0)
			AND
			cart_item_product.subtotal > 0
			ORDER BY 
			cart_item.id ASC')) throw new Exception('An error occured while trying to get cart products.'."\r\n\r\n".$this->mysqli->error);	
			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) {	
					// if subtotal is 0 skip 
					if ($row['subtotal'] <= 0) continue;
						
					$id_cart_item_product = $row['id_cart_item_product'];
					$id_tax_group = $row['id_tax_group'];
					$product_type = $row['product_type'];
					$subtotal = $row['subtotal'];
					$id_tax_rule_exception = 0;
							
					switch ($product_type) {
						// single
						case 0:
							// check if we have an applicable tax exception

							/* Execute the statement */
							if (!$stmt_tax_exception->bind_param("iiiii", $id_tax_rule, $id_tax_group, $_SESSION['customer']['id_customer_type'], $_SESSION['customer']['id_customer_type'], $id_tax_group)) throw new Exception('An error occured while trying to bind params to get tax exception statement.'."\r\n\r\n".$this->mysqli->error);
							if (!$stmt_tax_exception->execute()) throw new Exception('An error occured while trying to get tax exception.'."\r\n\r\n".$this->mysqli->error);
							
							/* store result */
							$stmt_tax_exception->store_result();																											
														
							/* bind result variables */
							$stmt_tax_exception->bind_result($id_tax_rule_exception);										
					
							// fetch
							$stmt_tax_exception->fetch();
							
							// if we have an exception
							if ($id_tax_rule_exception) {
								
								/* Execute the statement */
								if (!$stmt_tax_exception_rates->bind_param("i", $id_tax_rule_exception)) throw new Exception('An error occured while trying to bind params to get tax exception rates statement.'."\r\n\r\n".$this->mysqli->error);	
								if (!$stmt_tax_exception_rates->execute()) throw new Exception('An error occured while trying to get tax exception rates.'."\r\n\r\n".$this->mysqli->error);
								/* store result */
								$stmt_tax_exception_rates->store_result();
								
								/* bind result variables */
								$stmt_tax_exception_rates->bind_result($tax_rule_exception_rate_rate,$id_tax_rule_rate,$tax_rule_rate_stacked);

								$tax_total=0;
								$amount=0;
								while ($stmt_tax_exception_rates->fetch()) {
									
									// calculate tax 
									if ($tax_rule_rate_stacked) {
										$amount = ($subtotal+$tax_total)*$tax_rule_exception_rate_rate;	
									} else {
										$amount = $subtotal*$tax_rule_exception_rate_rate;
									}
									
									
									/* Execute the statement */
									if (!$stmt_add_tax->bind_param("iid", $id_cart_item_product, $id_tax_rule_rate, $amount)) throw new Exception('An error occured while trying to bind params to add tax statement.'."\r\n\r\n".$this->mysqli->error);
									if (!$stmt_add_tax->execute()) throw new Exception('An error occured while trying to add tax.'."\r\n\r\n".$this->mysqli->error);
									
									$tax_total += $amount;
								}							
								
								// update taxes for this product
								/* Execute the statement */
								if (!$stmt_upd_taxes->bind_param("di", $tax_total, $id_cart_item_product)) throw new Exception('An error occured while trying to bind params to update taxes statement.'."\r\n\r\n".$this->mysqli->error);
								if (!$stmt_upd_taxes->execute()) throw new Exception('An error occured while trying to update taxes.'."\r\n\r\n".$this->mysqli->error);												
							} else {
								$tax_total=0;
								$amount=0;
								foreach ($applicable_taxes as $row_tax) {
									$id_tax_rule_rate = $row_tax['id'];
									
									// calculate tax 
									if ($row_tax['stacked']) {
										$amount = ($subtotal+$tax_total)*$row_tax['rate'];	
									} else {
										$amount = $subtotal*$row_tax['rate'];
									}
									
									/* Execute the statement */
									if (!$stmt_add_tax->bind_param("iid", $id_cart_item_product, $id_tax_rule_rate, $amount)) throw new Exception('An error occured while trying to bind params to add tax statement.'."\r\n\r\n".$this->mysqli->error);
									if (!$stmt_add_tax->execute()) throw new Exception('An error occured while trying to add tax.'."\r\n\r\n".$this->mysqli->error);
									
									$tax_total += $amount;
								}							
								
								// update taxes for this product
								/* Execute the statement */
								if (!$stmt_upd_taxes->bind_param("di", $tax_total, $id_cart_item_product)) throw new Exception('An error occured while trying to bind params to update taxes statement.'."\r\n\r\n".$this->mysqli->error);
								if (!$stmt_upd_taxes->execute()) throw new Exception('An error occured while trying to update taxes.'."\r\n\r\n".$this->mysqli->error);							
							}					
							break;
						// combo
						case 1:
						// bundle
						case 2:
							/* Execute the statement */
							if (!$stmt_sub_products->bind_param("i", $id_cart_item_product)) throw new Exception('An error occured while trying to bind params to get sub products statement.'."\r\n\r\n".$this->mysqli->error);
							if (!$stmt_sub_products->execute()) throw new Exception('An error occured while trying to get sub products.'."\r\n\r\n".$this->mysqli->error);
							
							/* store result */
							$stmt_sub_products->store_result();
							
							/* bind result variables */
							$stmt_sub_products->bind_result($sb_id_cart_item_product,$subtotal_bundled,$taxable);

							
							if ($stmt_sub_products->num_rows) { 
								$taxes=0;								
								while ($stmt_sub_products->fetch()) {																																																								
									// check if we have an applicable tax exception
									/* Execute the statement */
									if (!$stmt_tax_exception->bind_param("iiiii", $id_tax_rule, $id_tax_group, $_SESSION['customer']['id_customer_type'], $_SESSION['customer']['id_customer_type'], $id_tax_group)) throw new Exception('An error occured while trying to bind params to get tax exception statement.'."\r\n\r\n".$this->mysqli->error);
									if (!$stmt_tax_exception->execute()) throw new Exception('An error occured while trying to get tax exception.'."\r\n\r\n".$this->mysqli->error);									
									
									
									/* store result */
									$stmt_tax_exception->store_result();																											
																
									/* bind result variables */
									$stmt_tax_exception->bind_result($id_tax_rule_exception);										
							
									// fetch
									$stmt_tax_exception->fetch();

									
									// if we have an exception
									if ($id_tax_rule_exception) {
										/* Execute the statement */
										if (!$stmt_tax_exception_rates->bind_param("i", $id_tax_rule_exception)) throw new Exception('An error occured while trying to bind params to get tax exception rates statement.'."\r\n\r\n".$this->mysqli->error);	
										if (!$stmt_tax_exception_rates->execute()) throw new Exception('An error occured while trying to get tax exception rates.'."\r\n\r\n".$this->mysqli->error);
										/* store result */
										$stmt_tax_exception_rates->store_result();
										
										/* bind result variables */
										$stmt_tax_exception_rates->bind_result($tax_rule_exception_rate_rate,$id_tax_rule_rate,$tax_rule_rate_stacked);
	
										$tax_total=0;
										$amount=0;
										while ($stmt_tax_exception_rates->fetch()) {
											
											// calculate tax 
											if ($tax_rule_rate_stacked) {
												$amount = ($subtotal_bundled+$tax_total)*$tax_rule_exception_rate_rate;	
											} else {
												$amount = $subtotal_bundled*$tax_rule_exception_rate_rate;
											}
											
											/* Execute the statement */			
											if (!$stmt_add_tax->bind_param("iid", $sb_id_cart_item_product, $id_tax_rule_rate, $amount)) throw new Exception('An error occured while trying to bind params to add tax statement.'."\r\n\r\n".$this->mysqli->error);								
											if (!$stmt_add_tax->execute()) throw new Exception('An error occured while trying to add tax.'."\r\n\r\n".$this->mysqli->error);
											
											$tax_total += $amount;
										}							
										
										/* Execute the statement */
										if (!$stmt_upd_taxes->bind_param("di", $tax_total, $sb_id_cart_item_product)) throw new Exception('An error occured while trying to bind params to update taxes statement.'."\r\n\r\n".$this->mysqli->error);
										if (!$stmt_upd_taxes->execute()) throw new Exception('An error occured while trying to update taxes.'."\r\n\r\n".$this->mysqli->error);												
									} else {
										$tax_total=0;
										$amount=0;
										foreach ($applicable_taxes as $row_tax) {
											$id_tax_rule_rate = $row_tax['id'];
											
											// calculate tax 
											if ($row_tax['stacked']) {
												$amount = ($subtotal_bundled+$tax_total)*$row_tax['rate'];	
											} else {
												$amount = $subtotal_bundled*$row_tax['rate'];
											}
											
											/* Execute the statement */
											if (!$stmt_add_tax->bind_param("iid", $sb_id_cart_item_product, $id_tax_rule_rate, $amount)) throw new Exception('An error occured while trying to bind params to add tax statement.'."\r\n\r\n".$this->mysqli->error);
											if (!$stmt_add_tax->execute()) throw new Exception('An error occured while trying to add tax.'."\r\n\r\n".$this->mysqli->error);
											
											//echo $subtotal_bundled.' '.$amount.'<br />';
											
											$tax_total += $amount;
										}						
										
										//echo $tax_total.'<br /><br />';	
										
										// update taxes for this product
										/* Execute the statement */
										if (!$stmt_upd_taxes->bind_param("di", $tax_total, $sb_id_cart_item_product)) throw new Exception('An error occured while trying to bind params to update taxes statement.'."\r\n\r\n".$this->mysqli->error);
										if (!$stmt_upd_taxes->execute()) throw new Exception('An error occured while trying to update taxes.'."\r\n\r\n".$this->mysqli->error);	
									}
									
									$taxes += $tax_total;																			
								}
																
								// update taxes for parent product
								$tax_total = $taxes;
								
								/* Execute the statement */
								if (!$stmt_upd_taxes->bind_param("di", $tax_total, $id_cart_item_product)) throw new Exception('An error occured while trying to bind params to update taxes statement.'."\r\n\r\n".$this->mysqli->error);
								if (!$stmt_upd_taxes->execute()) throw new Exception('An error occured while trying to update taxes.'."\r\n\r\n".$this->mysqli->error);																			
							}															
							break;						
					}
				}
			}	
			$result->free();					

			/* close statement */
			$stmt_sub_products->close();
			$stmt_upd_taxes->close();
			$stmt_add_tax->close();
			
			/* Prepare statement */
			if (!$stmt_add_tax = $this->mysqli->prepare('INSERT INTO 
			cart_item_option_tax
			SET
			id_cart_item_option = ?,
			id_tax_rule_rate = ?,
			amount = ?')) throw new Exception('An error occured while trying to prepare add tax statement.'."\r\n\r\n".$this->mysqli->error);	
				
			
			/* Prepare statement */
			if (!$stmt_upd_taxes = $this->mysqli->prepare('UPDATE
			cart_item_option
			SET
			taxes = ?
			WHERE
			id = ? 
			LIMIT 1')) throw new Exception('An error occured while trying to prepare update taxes statement.'."\r\n\r\n".$this->mysqli->error);	
							
			
			// get cart options				
			if (!$result = $this->mysqli->query('SELECT
			cart_item.id,
			cart_item_option.id AS id_cart_item_option,
			(cart_item_option.subtotal-IFNULL((SELECT SUM(cart_discount_item_option.amount) FROM cart_discount_item_option WHERE cart_discount_item_option.id_cart_item_option = cart_item_option.id),0)) AS subtotal,
			options.taxable,
			options.id_tax_group
			FROM
			cart_item 
			INNER JOIN
			(cart_item_option CROSS JOIN options)
			ON
			(cart_item.id = cart_item_option.id_cart_item AND cart_item_option.id_options = options.id)
			WHERE
			cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'" 
			AND
			options.taxable = 1
			AND
			cart_item_option.subtotal > 0			
			ORDER BY 
			cart_item_option.id ASC')) throw new Exception('An error occured while trying to get products.'."\r\n\r\n".$this->mysqli->error);
			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) {			
					$id_cart_item_option = $row['id_cart_item_option'];
					$id_tax_group = $row['id_tax_group'];
					$subtotal = $row['subtotal'];
					$id_tax_rule_exception = 0;
					

					// check if we have an applicable tax exception
					/* Execute the statement */
					if (!$stmt_tax_exception->bind_param("iiiii", $id_tax_rule, $id_tax_group, $_SESSION['customer']['id_customer_type'], $_SESSION['customer']['id_customer_type'], $id_tax_group)) throw new Exception('An error occured while trying to bind params to get tax exception statement.'."\r\n\r\n".$this->mysqli->error);
					if (!$stmt_tax_exception->execute()) throw new Exception('An error occured while trying to get tax exception.'."\r\n\r\n".$this->mysqli->error);
					
					/* store result */
					$stmt_tax_exception->store_result();																											
												
					/* bind result variables */
					$stmt_tax_exception->bind_result($id_tax_rule_exception);										
			
					// fetch
					$stmt_tax_exception->fetch();						
					
					// if we have an exception
					if ($id_tax_rule_exception) {
						/* Execute the statement */
						if (!$stmt_tax_exception_rates->bind_param("i", $id_tax_rule_exception)) throw new Exception('An error occured while trying to bind params to get tax exception rates statement.'."\r\n\r\n".$this->mysqli->error);	
						if (!$stmt_tax_exception_rates->execute()) throw new Exception('An error occured while trying to get tax exception rates.'."\r\n\r\n".$this->mysqli->error);
						/* store result */
						$stmt_tax_exception_rates->store_result();
						
						/* bind result variables */
						$stmt_tax_exception_rates->bind_result($tax_rule_exception_rate_rate,$id_tax_rule_rate,$tax_rule_rate_stacked);
						$tax_total=0;
						$amount=0;
						while ($stmt_tax_exception_rates->fetch()) {
							
							// calculate tax 
							if ($tax_rule_rate_stacked) {
								$amount = ($subtotal+$tax_total)*$tax_rule_exception_rate_rate;	
							} else {
								$amount = $subtotal*$tax_rule_exception_rate_rate;
							}
							
							/* Execute the statement */
							if (!$stmt_add_tax->bind_param("iid", $id_cart_item_option, $id_tax_rule_rate, $amount)) throw new Exception('An error occured while trying to bind params to add tax statement.'."\r\n\r\n".$this->mysqli->error);
							if (!$stmt_add_tax->execute()) throw new Exception('An error occured while trying to add tax.'."\r\n\r\n".$this->mysqli->error);
							
							$tax_total += $amount;
						}							
						
						// update taxes for this product
						/* Execute the statement */
						if (!$stmt_upd_taxes->bind_param("di", $tax_total, $id_cart_item_option)) throw new Exception('An error occured while trying to bind params to update taxes statement.'."\r\n\r\n".$this->mysqli->error);
						if (!$stmt_upd_taxes->execute()) throw new Exception('An error occured while trying to update taxes.'."\r\n\r\n".$this->mysqli->error);												
					} else {
						$tax_total=0;
						$amount=0;
						foreach ($applicable_taxes as $row_tax) {
							$id_tax_rule_rate = $row_tax['id'];
							
							// calculate tax 
							if ($row_tax['stacked']) {
								$amount = ($subtotal+$tax_total)*$row_tax['rate'];	
							} else {
								$amount = $subtotal*$row_tax['rate'];
							}
							
							/* Execute the statement */
							if (!$stmt_add_tax->bind_param("iid", $id_cart_item_option, $id_tax_rule_rate, $amount)) throw new Exception('An error occured while trying to bind params to add tax statement.'."\r\n\r\n".$this->mysqli->error);
							if (!$stmt_add_tax->execute()) throw new Exception('An error occured while trying to add tax.'."\r\n\r\n".$this->mysqli->error);
							
							$tax_total += $amount;
						}				
						
						// update taxes for this product
						/* Execute the statement */
						if (!$stmt_upd_taxes->bind_param("di", $tax_total, $id_cart_item_option)) throw new Exception('An error occured while trying to bind params to update taxes statement.'."\r\n\r\n".$this->mysqli->error);
						if (!$stmt_upd_taxes->execute()) throw new Exception('An error occured while trying to update taxes.'."\r\n\r\n".$this->mysqli->error);						
					}
				}
			}
			$result->free();
			
			/* close statement */	
			$stmt_tax_exception_rates->close();
			$stmt_tax_exception->close();					
		}

		// get shipping and calculate taxes
		if (!$result = $this->mysqli->query('SELECT 
		cart.shipping
		FROM 
		cart
		WHERE
		cart.id = "'.$this->mysqli->escape_string($this->id).'"
		')) throw new Exception('An error occured while trying to get shipping and calculate taxes.'."\r\n\r\n".$this->mysqli->error);
								
		$row = $result->fetch_assoc();		
		$result->free();
		$shipping = $row['shipping'];		
		
		// product tax
		if (!$result = $this->mysqli->query('SELECT
		cart_item_product_tax.id_tax_rule_rate,
		SUM(cart_item_product_tax.amount) AS taxes
		FROM
		cart_item
		INNER JOIN
		(cart_item_product CROSS JOIN cart_item_product_tax)
		ON
		(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id = cart_item_product_tax.id_cart_item_product)
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
		GROUP BY
		cart_item_product_tax.id_tax_rule_rate')) throw new Exception('An error occured while trying to get product taxes.'."\r\n\r\n".$this->mysqli->error);
		
		$taxes = array();
		while ($row = $result->fetch_assoc()) {			
			$taxes[$row['id_tax_rule_rate']] += $row['taxes'];
		}
		$result->free();
		
		// sub product tax
		if (!$result = $this->mysqli->query('SELECT
		cart_item_product_tax.id_tax_rule_rate,
		SUM(cart_item_product_tax.amount) AS taxes
		FROM
		cart_item
		INNER JOIN
		(cart_item_product CROSS JOIN cart_item_product AS cip CROSS JOIN cart_item_product_tax)
		ON
		(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id = cip.id_cart_item_product AND cip.id = cart_item_product_tax.id_cart_item_product)
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
		GROUP BY
		cart_item_product_tax.id_tax_rule_rate')) throw new Exception('An error occured while trying to get product taxes.'."\r\n\r\n".$this->mysqli->error);

		while ($row = $result->fetch_assoc()) {			
			$taxes[$row['id_tax_rule_rate']] += $row['taxes'];
		}
		$result->free();		
		
		// option tax
		if (!$result = $this->mysqli->query('SELECT
		cart_item_option_tax.id_tax_rule_rate,
		SUM(cart_item_option_tax.amount) AS taxes
		FROM
		cart_item
		INNER JOIN
		(cart_item_option CROSS JOIN cart_item_option_tax)
		ON
		(cart_item.id = cart_item_option.id_cart_item AND cart_item_option.id = cart_item_option_tax.id_cart_item_option)
		WHERE
		cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
		GROUP BY
		cart_item_option_tax.id_tax_rule_rate')) throw new Exception('An error occured while trying to get product taxes.'."\r\n\r\n".$this->mysqli->error);
		
		while ($row = $result->fetch_assoc()) {			
			$taxes[$row['id_tax_rule_rate']] += $row['taxes'];
		}
		$result->free();
		
		if ($shipping > 0 && sizeof($applicable_taxes)) {
			/* Prepare statement */
			if (!$stmt_add_tax = $this->mysqli->prepare('INSERT INTO 
			cart_shipping_tax
			SET
			id_cart = ?,
			id_tax_rule_rate = ?,
			amount = ?')) throw new Exception('An error occured while trying to prepare add shipping tax statement.'."\r\n\r\n".$this->mysqli->error);								
			
			$tax_total=0;
			$amount=0;
			foreach ($applicable_taxes as $row_tax) {
				$id_tax_rule_rate = $row_tax['id'];
				
				// calculate tax 
				if ($row_tax['stacked']) {
					$amount = ($shipping+$tax_total)*$row_tax['rate'];	
				} else {
					$amount = $shipping*$row_tax['rate'];
				}
				
				// add shipping taxes
				/* Execute the statement */
				if (!$stmt_add_tax->bind_param("iid", $this->id, $id_tax_rule_rate, $amount)) throw new Exception('An error occured while trying to bind params to add shipping tax statement.'."\r\n\r\n".$this->mysqli->error);
				if (!$stmt_add_tax->execute()) throw new Exception('An error occured while trying to add shipping tax.'."\r\n\r\n".$this->mysqli->error);					
				
				$taxes[$id_tax_rule_rate] += $amount;
								
				$tax_total += $amount;
			}
			
			$stmt_add_tax->close();
		}		
		
		$tax_total = 0;
		foreach ($taxes as $id_tax_rule_rate => $amount) {
			$tax_total += round($amount,2);	
		}
		
		// update cart taxes
		if (!$this->mysqli->query('UPDATE 
		cart	
		SET
		taxes = "'.$this->mysqli->escape_string($tax_total).'"
		WHERE
		id = "'.$this->mysqli->escape_string($this->id).'"')) throw new Exception('An error occured while trying to update cart taxes.'."\r\n\r\n".$this->mysqli->error);
		
		//return $taxes;
	}
	
	public function get_taxes()
	{
		// get applicable taxe
		$id_tax_rule = $this->id_tax_rule;
		$arr_taxes = array();
		
		if ($id_tax_rule) {
			if (!$result = $this->mysqli->query('SELECT
			tax_rule_rate.id,
			tax_rule_rate.rate,
			tax_rule_rate.stacked,
			tax_description.name,
			IFNULL((SELECT
				SUM(cart_item_product_tax.amount)
				FROM
				cart_item_product_tax
				INNER JOIN
				cart_item_product	
				ON
				(cart_item_product_tax.id_cart_item_product = cart_item_product.id)
				INNER JOIN
				cart_item
				ON
				(cart_item_product.id_cart_item = cart_item.id)
				WHERE
				cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
				AND
				cart_item_product_tax.id_tax_rule_rate = tax_rule_rate.id),0)+IFNULL((SELECT
				SUM(cart_item_product_tax.amount)
				FROM
				cart_item_product_tax
				INNER JOIN
				cart_item_product	
				ON
				(cart_item_product_tax.id_cart_item_product = cart_item_product.id)
				INNER JOIN
				(cart_item_product AS cip CROSS JOIN cart_item AS ci)
				ON
				(cart_item_product.id_cart_item_product = cip.id AND cip.id_cart_item = ci.id)
				WHERE
				ci.id_cart = "'.$this->mysqli->escape_string($this->id).'"
				AND
				cart_item_product_tax.id_tax_rule_rate = tax_rule_rate.id),0)+IFNULL((SELECT
				SUM(cart_item_option_tax.amount)
				FROM
				cart_item_option_tax
				INNER JOIN
				cart_item_option	
				ON
				(cart_item_option_tax.id_cart_item_option = cart_item_option.id)
				INNER JOIN
				cart_item	
				ON
				(cart_item_option.id_cart_item = cart_item.id)
				WHERE
				cart_item.id_cart = "'.$this->mysqli->escape_string($this->id).'"
				AND
				cart_item_option_tax.id_tax_rule_rate = tax_rule_rate.id),0)+IFNULL((SELECT
				SUM(cart_shipping_tax.amount)
				FROM
				cart_shipping_tax
				WHERE
				cart_shipping_tax.id_cart = "'.$this->mysqli->escape_string($this->id).'"
				AND
				cart_shipping_tax.id_tax_rule_rate = tax_rule_rate.id),0) AS total_taxes
			FROM 
			tax_rule_rate
			INNER JOIN
			(tax CROSS JOIN tax_description)
			ON
			(tax_rule_rate.id_tax = tax.id AND tax_description.id_tax = tax.id AND tax_description.language_code = "'.$_SESSION['customer']['language'].'")
			WHERE
			tax_rule_rate.id_tax_rule =  "'.$this->mysqli->escape_string($id_tax_rule).'"
			ORDER BY 
			tax_rule_rate.sort_order ASC')) throw new Exception('An error occured while trying to get applicable tax rule rates.'."\r\n\r\n".$this->mysqli->error);		
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
	
	public function calculate_total()
	{	
		// update total
		if (!$this->mysqli->query('UPDATE 
		cart
		SET
		total = (subtotal+shipping+taxes) 
		WHERE
		id = "'.$this->mysqli->escape_string($this->id).'"
		LIMIT 1')) throw new Exception('An error occured while trying to update cart total.'."\r\n\r\n".$this->mysqli->error);
				
		// get total
		if (!$result = $this->mysqli->query('SELECT 
		total
		FROM 
		cart
		WHERE
		id = "'.$this->mysqli->escape_string($this->id).'"
		LIMIT 1')) throw new Exception('An error occured while trying to get cart total.'."\r\n\r\n".$this->mysqli->error);
		
		$row = $result->fetch_assoc();
		$result->free();
		
		$this->total = $row['total'];		
		
		// update gift certifcates
		$this->upd_gift_certificates();
		
		// update grand total		
		$this->grand_total = $this->total-$this->gift_certificates;
		
		// update total
		if (!$this->mysqli->query('UPDATE 
		cart
		SET
		grand_total = "'.$this->mysqli->escape_string($this->grand_total).'"
		WHERE
		id = "'.$this->mysqli->escape_string($this->id).'"
		LIMIT 1')) throw new Exception('An error occured while trying to update cart grand total.'."\r\n\r\n".$this->mysqli->error);		
	}
	
	public function get_free_shipping_yes_no($country_code, $state_code)
	{
		global $config_site;
		$free_shipping = 0;
		$total_weight = $this->get_products_total_weight();
		$current_datetime = date('Y-m-d H:i:s');
		
		// check if we have products that can't get free shipping
		if (!$result_check_product = $this->mysqli->query('SELECT
		COUNT(cip.id) AS total
		FROM
		cart_item_product AS cip
		LEFT JOIN
		cart_item AS ci
		ON
		(cip.id_cart_item = ci.id)
		
		LEFT JOIN
		(cart_item_product AS cip2 CROSS JOIN cart_item AS ci2) 
		ON
		(cip.id_cart_item_product = cip2.id AND cip2.id_cart_item = ci2.id)
		
		INNER JOIN 
		config_free_shipping_product_exceptions
		ON
		(cip.id_product = config_free_shipping_product_exceptions.id_product)
		
		WHERE
		ci.id_cart = "'.$this->id.'"
		OR
		ci2.id_cart = "'.$this->id.'"')) throw new Exception('An error occured while trying to get products list.'); 
		$row_check_product = $result_check_product->fetch_assoc();
		
		if ($row_check_product['total']) {
			return $free_shipping;
		}		
		$result_check_product->free();
		
		if (!$result = $this->mysqli->query('SELECT 
		id 
		FROM 
		
		((SELECT 
		rebate_coupon.id 
		FROM 
		rebate_coupon
		WHERE
		(SELECT 
		COUNT(id) 
		FROM 
		config_free_shipping_region
		WHERE 
		(country_code = "'.$this->mysqli->escape_string($country_code).'" AND 
		state_code = "'.$this->mysqli->escape_string($state_code).'") OR 
		(country_code = "'.$this->mysqli->escape_string($country_code).'" 
		AND state_code = "") OR
		(country_code = "" AND state_code = "")) > 0 AND
		rebate_coupon.type = 4 AND
		rebate_coupon.coupon = 0 AND
		rebate_coupon.active = 1 AND
		(
			rebate_coupon.end_date = "0000-00-00 00:00:00"
			OR
			"'.$this->mysqli->escape_string($current_datetime).'" BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
		) AND
		rebate_coupon.min_cart_value <= '.$this->mysqli->escape_string($this->subtotal).' AND
		rebate_coupon.max_weight  >= '.$this->mysqli->escape_string($total_weight).' LIMIT 1)
		
		UNION
		
		(SELECT 
		rebate_coupon.id 
		FROM 
		rebate_coupon
		INNER JOIN cart_discount ON rebate_coupon.id = cart_discount.id_rebate_coupon 
		AND cart_discount.id_cart = '.$this->mysqli->escape_string($this->id).'
		WHERE 
		(SELECT 
		COUNT(id) 
		FROM 
		config_free_shipping_region
		WHERE 
		(country_code = "'.$this->mysqli->escape_string($country_code).'" AND 
		state_code = "'.$this->mysqli->escape_string($state_code).'") OR 
		(country_code = "'.$this->mysqli->escape_string($country_code).'" 
		AND state_code = "") OR
		(country_code = "" AND state_code = "")) > 0 AND
		rebate_coupon.type = 4 AND
		rebate_coupon.coupon = 1 AND
		rebate_coupon.active = 1 AND
		(
			rebate_coupon.end_date = "0000-00-00 00:00:00"
			OR
			"'.$this->mysqli->escape_string($current_datetime).'" BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
		) 
		AND
		rebate_coupon.min_cart_value <= '.$this->mysqli->escape_string($this->subtotal).' AND
		rebate_coupon.max_weight  >= '.$this->mysqli->escape_string($total_weight).' LIMIT 1)				
		
		) AS t')) throw new Exception('An error occured while trying to check if we have free shipping.'."\r\n\r\n".$this->mysqli->error);		
		
		if($result->num_rows){
			$free_shipping = 1;
		}
		$result->free();

		return $free_shipping;
	}
	
	public function get_not_shipping_in_region_yes_no($id_product=0, $id_options=0, $country_code, $state_code)
	{
		global $config_site;

		// SHIP ONLY INTO THIS REGION
		//Options
		if($id_options){
			// Ship only into this region by option
			// Verify if there is someting in the table : options_ship_only_region
			if (!$result = $this->mysqli->query('SELECT 
			sor.id
			FROM 
			options_ship_only_region
			LEFT JOIN 
			options_ship_only_region AS sor
			ON
			(options_ship_only_region.id_options = sor.id_options 
			AND
			sor.country_code = "'.$this->mysqli->escape_string($country_code).'" AND (sor.state_code = "" OR sor.state_code = "'.$this->mysqli->escape_string($state_code).'"))
			WHERE
			options_ship_only_region.id_options = "'.$this->mysqli->escape_string($id_options).'"
			LIMIT 1')) throw new Exception('An error occured while trying to check if we can ship into this region.'."\r\n\r\n".$this->mysqli->error);					
			if($result->num_rows){
				$row = $result->fetch_assoc();
				$result->free();
				
				if (!$row['id']) return 1;
			// General Settings
			}else{
				// Verify if there is someting in the table : config_ship_only_region
				if (!$result = $this->mysqli->query('SELECT 
				sor.id 
				FROM 
				config_ship_only_region
				LEFT JOIN 
				config_ship_only_region AS sor
				ON
				(sor.country_code = "'.$this->mysqli->escape_string($country_code).'" AND (sor.state_code = "" OR sor.state_code = "'.$this->mysqli->escape_string($state_code).'"))
				LIMIT 1')) throw new Exception('An error occured while trying to check if we can ship into this region.'."\r\n\r\n".$this->mysqli->error);		
				if($result->num_rows){
					$row = $result->fetch_assoc();
					$result->free();
				
					if (!$row['id']) return 1;
				}
			}
			
			// Do not ship into this region by option
			if (!$result = $this->mysqli->query('SELECT 
			id 
			FROM 
			options_do_not_ship_region
			WHERE
			id_options = "'.$this->mysqli->escape_string($id_options).'" AND
			country_code = "'.$this->mysqli->escape_string($country_code).'" AND (state_code = "" OR state_code = "'.$this->mysqli->escape_string($state_code).'")
			LIMIT 1')) throw new Exception('An error occured while trying to check if we can ship into this region.'."\r\n\r\n".$this->mysqli->error);		
			$row = $result->fetch_assoc();
			$result->free();
			
			if ($row['id']) return 1;

			// Do not ship into this region general config
			if (!$result = $this->mysqli->query('SELECT 
			id 
			FROM 
			config_do_not_ship_region
			WHERE
			country_code = "'.$this->mysqli->escape_string($country_code).'" AND (state_code = "" OR state_code = "'.$this->mysqli->escape_string($state_code).'")
			LIMIT 1')) throw new Exception('An error occured while trying to check if we can ship into this region.'."\r\n\r\n".$this->mysqli->error);		
			$row = $result->fetch_assoc();
			$result->free();
			
			if ($row['id']) return 1;			
		//Product
		}else{
			// Ship only into this region by product
			// Verify if there is someting in the table : product_ship_only_region
			if (!$result = $this->mysqli->query('SELECT 
			sor.id
			FROM 
			product_ship_only_region
			LEFT JOIN 
			product_ship_only_region AS sor
			ON
			(product_ship_only_region.id_product = sor.id_product 
			AND
			sor.country_code = "'.$this->mysqli->escape_string($country_code).'" AND (sor.state_code = "" OR sor.state_code = "'.$this->mysqli->escape_string($state_code).'"))
			WHERE
			product_ship_only_region.id_product = "'.$this->mysqli->escape_string($id_product).'"
			LIMIT 1')) throw new Exception('An error occured while trying to check if we can ship into this region.'."\r\n\r\n".$this->mysqli->error);					
			if($result->num_rows){
				$row = $result->fetch_assoc();
				$result->free();
				
				if (!$row['id']) return 1;
			// General Settings
			}else{
				// Verify if there is someting in the table : config_ship_only_region
				if (!$result = $this->mysqli->query('SELECT 
				sor.id 
				FROM 
				config_ship_only_region
				LEFT JOIN 
				config_ship_only_region AS sor
				ON
				(sor.country_code = "'.$this->mysqli->escape_string($country_code).'" AND (sor.state_code = "" OR sor.state_code = "'.$this->mysqli->escape_string($state_code).'"))
				LIMIT 1')) throw new Exception('An error occured while trying to check if we can ship into this region.'."\r\n\r\n".$this->mysqli->error);		
				if($result->num_rows){
					$row = $result->fetch_assoc();
					$result->free();
				
					if (!$row['id']) return 1;
				}
			}
			
			// Do not ship into this region by product
			if (!$result = $this->mysqli->query('SELECT 
			id 
			FROM 
			product_do_not_ship_region
			WHERE
			id_product = "'.$this->mysqli->escape_string($id_product).'" AND
			country_code = "'.$this->mysqli->escape_string($country_code).'" AND (state_code = "" OR state_code = "'.$this->mysqli->escape_string($state_code).'")
			LIMIT 1')) throw new Exception('An error occured while trying to check if we can ship into this region.'."\r\n\r\n".$this->mysqli->error);		
			$row = $result->fetch_assoc();
			$result->free();
			
			if ($row['id']) return 1;

			// Do not ship into this region general config
			if (!$result = $this->mysqli->query('SELECT 
			id 
			FROM 
			config_do_not_ship_region
			WHERE
			country_code = "'.$this->mysqli->escape_string($country_code).'" AND (state_code = "" OR state_code = "'.$state_code.'")
			LIMIT 1')) throw new Exception('An error occured while trying to check if we can ship into this region.'."\r\n\r\n".$this->mysqli->error);		
			$row = $result->fetch_assoc();
			$result->free();
			
			if ($row['id']) return 1;			
		}

		return 0;
	}
	
	
	
	
	public function add_shipping_info($company,$service,$rate,$estimated,$freeshipping=0){

		if (!$this->mysqli->query('UPDATE
		cart
		SET
		shipping_gateway_company = "'.$this->mysqli->escape_string($company).'",
		shipping_service = "'.$this->mysqli->escape_string($service).'",
		shipping = "'.$this->mysqli->escape_string($rate).'",
		shipping_estimated = "'.$this->mysqli->escape_string($estimated).'",
		free_shipping = "'.$this->mysqli->escape_string($freeshipping).'"
		WHERE
		id = '.$this->id.'
		LIMIT 1')) throw new Exception('An error occured while trying to add item.'."\r\n\r\n".$this->mysqli->error);	
		
		$this->calculate_taxes();
		$this->calculate_total();
		$this->init();
		
		return true;
		
	}        	
	
	public function get_messages()
	{
		return $this->messages;	
	}
	
	
	public function get_cart_item_info($id_cart_item){		
		$id_cart_item = (int)$id_cart_item;
		
		$output = array();
	
		if (!$result = $this->mysqli->query('SELECT 
		cart_item.id,
		cart_item.id_cart_discount,
		cart_item.qty,
		cart_item_product.id AS id_cart_item_product,
		cart_item_product.id_product,
		cart_item_product.id_product_variant,
		product_variant.variant_code
		FROM 
		cart_item 
		INNER JOIN (cart_item_product CROSS JOIN product) ON (cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id) 
		LEFT JOIN product_variant ON (cart_item_product.id_product_variant = product_variant.id)
		WHERE
		cart_item.id = "'.$$id_cart_item.'"')) throw new Exception('An error occured while trying to get cart item info.'."\r\n\r\n".$this->mysqli->error);	
		
		if ($result->num_rows) { 
			$row = $result->fetch_assoc();
			
			$id_cart_item_product = $row['id_cart_item_product'];
			
			$output['qty'] = $row['qty'];
			
			// if id_cart_discount has a value, it means the product is part of a rebate or coupon.
			// the customer can't change the qty
			$output['id_cart_discount'] = $row['id_cart_discount'];
			
			$variants_options = array();
			
			if (!empty($row['variant_code'])) {
				$variant_code = explode(';',$row['variant_code']);	
				$variants_options = array();
				
				foreach ($variant_code as $value) {
					list($id_group, $id_option) = explode(':',$value);
					
					$variants_options[$id_group] = $id_option;	
				}				
			}

			$output['variants_options'] = $variants_options;			
			
			switch ($row['product_type']) {
				// single
				case 0:
					break;	
				// combo
				case 1:
					if (!$result = $this->mysqli->query('SELECT
					cart_item_product.id,
					cart_item_product.id_product_variant,
					cart_item_product.id_product_combo_product
					FROM 
					cart_item_product
					WHERE
					cart_item_product.id_cart_item_product = "'.$id_cart_item_product.'" 
					AND
					cart_item_product.id_product_combo_product != 0
					AND 
					cart_item_product.id_product_variant != 0')) throw new Exception('An error occured while trying to get combo product variants.'."\r\n\r\n".$this->mysqli->error);	
					
					while ($row = $result->fetch_assoc()) {
						$output['products'][$row['id_product_combo_product']][$row['id_product_variant']] = array(
							'id' => $row['id_product_variant'],
							'id_cart_item_product' => $row['id'],							
						);
					}
					
					$result->free();
					break;
				// bundle
				case 2:
					if (!$result = $mysqli->query('SELECT
					cart_item_product.id,
					cart_item_product.qty,
					cart_item_product.id_product_bundled_product_group_product,
					pg_product.id_product_bundled_product_group
					FROM 
					cart_item_product
					INNER JOIN product_bundled_product_group_product AS pg_product ON (cart_item_product.id_product_bundled_product_group_product = pg_product.id)
					WHERE
					cart_item_product.id_cart_item_product = "'.$id_cart_item_product.'"')) throw new Exception('An error occured while trying to get bundle products.'."\r\n\r\n".$this->mysqli->error);	
					
					while ($row = $result->fetch_assoc()) {
						$output['products'][$row['id_product_bundled_product_group']][$row['id_product_bundled_product_group_product']] = array(
							'id' => $row['id_product_bundled_product_group_product'],
							'id_cart_item_product' => $row['id'],							
							'qty' => $row['qty'],
						);
					}
					
					$result->free();			
					break;
			}
			
			// get options
			if (!$result = $this->mysqli->query('SELECT 
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
			INNER JOIN options_group ON (cart_item_option.id_options_group = options_group.id)
			WHERE
			cart_item_option.id_cart_item = "'.$id_cart_item.'"')) throw new Exception('An error occured while trying to get product options.'."\r\n\r\n".$this->mysqli->error);	
				
			while ($row = $result->fetch_assoc()) {				
				$output['options'][$row['id_options_group']][$row['id_options']] = array(
					'id' => $row['id_options'],
					'id_cart_item_option' => $row['id'],
					'qty' => $row['qty'],					
				);
				
				switch ($row['input_type']) {
					// textfield
					case 5:
						$output['options'][$row['id_options_group']][$row['id_options']]['value'] = $row['textfield'];
						break;
					// textarea
					case 6:
						$output['options'][$row['id_options_group']][$row['id_options']]['value'] = $row_option['textarea'];
						break;
					// file
					case 7:				
						$output['options'][$row['id_options_group']][$row['id_options']]['value'] = array(
							'file_path'=> dirname(__FILE__).'/../../tmp_uploads/'.$row['filename_tmp'],						
							'file_rel_path'=> '/tmp_uploads/'.$row['filename_tmp'],
							'name'=>$row['filename'],							
						);
						break;
					// date
					case 8:
						$output['options'][$row['id_options_group']][$row['id_options']]['value'] = array(
							'start' => $row['date_start'],
							'end' => $row['date_end'],
						);
						break;
					// date & time
					case 9:
						$output['options'][$row['id_options_group']][$row['id_options']]['value'] = array(
							'start' => $row['datetime_start'],
							'end' => $row['datetime_end'],
						);
						break;
					// time
					case 10:
						$output['options'][$row['id_options_group']][$row['id_options']]['value'] = array(
							'start' => $row['time_start'],
							'end' => $row['time_end'],
						);					
						break;
				}			
			}			
		}
		
		return $output;
	}
	
}
?>