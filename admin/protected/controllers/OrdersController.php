<?php

class OrdersController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{					
		// display the form
		$this->render('index');	
	}
	
	/**
	 * This is the action to get an XML list of orders
	 */
	public function actionXml_list($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $unsettled=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':language_code'=>Yii::app()->language);
		
		// filters
		
		// order_no
		if (isset($filters['id']) && !empty($filters['id'])) {
			$where[] = 'orders.id LIKE CONCAT("%",:id,"%")';
			$params[':id']=$filters['id'];
		}
		
		// date_order start
		if (isset($filters['date_order_start']) && !empty($filters['date_order_start'])) {
			$where[] = 'orders.date_order >= :date_order_start';
			$params[':date_order_start']=$filters['date_order_start'];
		}	
		
		// date_order end
		if (isset($filters['date_order_end']) && !empty($filters['date_order_end'])) {
			$where[] = 'orders.date_order <= :date_order_end';
			$params[':date_order_end']=$filters['date_order_end'];
		}	
		
		// bill_to
		if (isset($filters['bill_to']) && !empty($filters['bill_to'])) {
			$where[] = '((CONCAT(orders.billing_firstname," ",orders.billing_lastname) LIKE CONCAT("%",:bill_to,"%"))
			OR (orders.billing_address LIKE CONCAT("%",:bill_to,"%")) 
			OR (orders.billing_city LIKE CONCAT("%",:bill_to,"%"))
			OR (orders.billing_zip LIKE CONCAT("%",:bill_to,"%"))
			OR (orders.billing_telephone LIKE CONCAT("%",:bill_to,"%")) 
			OR (country_bill_to.name LIKE CONCAT("%",:bill_to,"%"))
			OR (state_bill_to.name LIKE CONCAT("%",:bill_to,"%")))';
			$params[':bill_to']=$filters['bill_to'];
		}		
		
		// ship_to
		if (isset($filters['ship_to']) && !empty($filters['ship_to'])) {
			$where[] = '((CONCAT(orders.shipping_firstname," ",orders.shipping_lastname) LIKE CONCAT("%",:ship_to,"%"))
			OR (orders.shipping_address LIKE CONCAT("%",:ship_to,"%")) 
			OR (orders.shipping_city LIKE CONCAT("%",:ship_to,"%"))
			OR (orders.shipping_zip LIKE CONCAT("%",:ship_to,"%"))
			OR (orders.shipping_telephone LIKE CONCAT("%",:ship_to,"%")) 
			OR (country_ship_to.name LIKE CONCAT("%",:ship_to,"%"))
			OR (state_ship_to.name LIKE CONCAT("%",:ship_to,"%")))';
			$params[':ship_to']=$filters['ship_to'];
		}			
		
		// total
		if (isset($filters['total']) && !empty($filters['total'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['total'])) {
				$where[] = 'orders.grandtotal <= :total';
				$params[':total']=ltrim($filters['total'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['total'])) {
				$where[] = 'orders.grandtotal >= :total';
				$params[':total']=ltrim($filters['total'],'>=');
			// <N
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['total'])) {		
				$where[] = 'orders.grandtotal < :total';
				$params[':total']=ltrim($filters['total'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['total'])) {		
				$where[] = 'orders.grandtotal > :total';
				$params[':total']=ltrim($filters['total'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['total'])) {		
				$where[] = 'orders.grandtotal = :total';
				$params[':total']=ltrim($filters['total'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['total'])) {
				$search = explode('..',$filters['total']);
				$where[] = 'orders.grandtotal BETWEEN :total_start AND :total_end';
				$params[':total_start']=$search[0];
				$params[':total_end']=$search[1];
			// N				
			} else {
				$where[] = 'orders.grandtotal = :total';
				$params[':total']=$filters['total'];
			}
		}					
		
		// priority
		if (isset($filters['priority'])) {
			if (is_numeric($filters['priority'])) {
				$where[] = 'orders.priority = :priority';				
				$params[':priority']=$filters['priority'];
			}
		}
		
		// payment_method
		if (isset($filters['payment_method'])) {
			if (is_numeric($filters['payment_method'])) {
				$where[] = 'orders.payment_method = :payment_method';				
				$params[':payment_method']=$filters['payment_method'];
			}
		}	
		
		// status
		if (isset($filters['status'])) {
			if (is_numeric($filters['status'])) {
				$where[] = 'orders.status = :status';				
				$params[':status']=$filters['status'];
			}
		}					
		
		// unsettled
		if ($unsettled) {
			$where[] = 'orders.status NOT IN (-1, 0, 4, 7)';			
		}
		
		$sql = "SELECT 
		COUNT(orders.id) AS total 
		FROM 
		orders
		LEFT JOIN
		country_description AS country_bill_to
		ON 
		(orders.billing_country_code = country_bill_to.country_code AND country_bill_to.language_code = :language_code)	
		LEFT JOIN
		state_description AS state_bill_to
		ON 
		(orders.billing_state_code = state_bill_to.state_code AND state_bill_to.language_code = country_bill_to.language_code)	
		LEFT JOIN
		country_description AS country_ship_to
		ON 
		(orders.shipping_country_code = country_ship_to.country_code AND country_ship_to.language_code = country_bill_to.language_code)	
		LEFT JOIN
		state_description AS state_ship_to
		ON 
		(orders.shipping_state_code = state_ship_to.state_code AND state_ship_to.language_code = country_bill_to.language_code)					
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT 
		orders.id,
		orders.date_order,
		orders.billing_firstname,
		orders.billing_lastname,
		orders.billing_company,
		orders.billing_address,
		orders.billing_city,
		country_bill_to.name AS billing_country,
		state_bill_to.name AS billing_state,
		orders.billing_zip,
		orders.billing_telephone,
		orders.shipping_firstname,
		orders.shipping_lastname,
		orders.shipping_company,
		orders.shipping_address,
		orders.shipping_city,
		orders.local_pickup,
		orders.free_shipping,
		country_ship_to.name AS shipping_country,
		state_ship_to.name AS shipping_state,
		orders.shipping_zip,
		orders.shipping_telephone,
		orders.shipping_service,			
		orders.grand_total,
		orders.status,
		orders.priority,
		orders.payment_method
		FROM 
		orders
		LEFT JOIN
		country_description AS country_bill_to
		ON 
		(orders.billing_country_code = country_bill_to.country_code AND country_bill_to.language_code = :language_code)	
		LEFT JOIN
		state_description AS state_bill_to
		ON 
		(orders.billing_state_code = state_bill_to.state_code AND state_bill_to.language_code = country_bill_to.language_code)	
		LEFT JOIN
		country_description AS country_ship_to
		ON 
		(orders.shipping_country_code = country_ship_to.country_code AND country_ship_to.language_code = country_bill_to.language_code)	
		LEFT JOIN
		state_description AS state_ship_to
		ON 
		(orders.shipping_state_code = state_ship_to.state_code AND state_ship_to.language_code = country_bill_to.language_code)					
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// order_no
		if (isset($sort_col[0]) && !empty($sort_col[0])) {	
			$direct = $sort_col[0] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders.id ".$direct;
		// date_order
		} else if (isset($sort_col[1]) && !empty($sort_col[1])) {
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders.date_order ".$direct;	
		// total
		} else if (isset($sort_col[4]) && !empty($sort_col[4])) {
			$direct = $sort_col[4] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders.grand_total ".$direct;				
		// priority
		} else if (isset($sort_col[7]) && !empty($sort_col[7])) {
			$direct = $sort_col[7] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders.priority ".$direct;																			
		// status
		} else if (isset($sort_col[6]) && !empty($sort_col[6])) {
			$direct = $sort_col[6] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders.status ".$direct;																						
		// payment_method
		} else if (isset($sort_col[5]) && !empty($sort_col[5])) {
			$direct = $sort_col[5] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders.payment_method ".$direct;																						
		} else {
			if (isset($filters['id']) && !empty($filters['id'])) { 
				$sql.=" ORDER BY IF(orders.id LIKE CONCAT(:id,'%'),0,1) ASC, orders.id ASC";
			} else {
				$sql.=" ORDER BY orders.id DESC";
			}			
		}		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			$local_pickup = 0;
			if(empty($row['shipping_address']) and $row['local_pickup']){
				$local_pickup = 1;
			}

			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['id'].']]></cell>
			<cell type="ro"><![CDATA['.$row['date_order'].']]></cell>
			<cell type="ro"><![CDATA['.$row['billing_firstname'].' '.$row['billing_lastname'].'<br />'."\r\n".
			($row['billing_company'] ? $row['billing_company'].'<br />'."\r\n":'').
			$row['billing_address'].'<br />'."\r\n".
			$row['billing_city'].($row['billing_state'] ? ' '.$row['billing_state']:'').'<br />'."\r\n".
			$row['billing_country'].($row['billing_zip'] ? ' '.$row['billing_zip']:'').'<br />'."\r\n".
			($row['billing_telephone']? "\r\n".'<br /><strong>'.Yii::t('controllers/OrdersController','LABEL_CONTACT_US_T').'</strong> '.$row['billing_telephone']:'').']]></cell>			
			<cell type="ro"><![CDATA['.($local_pickup?Yii::t('views/orders/edit_info_options','LABEL_LOCAL_PICKUP'):$row['shipping_firstname'].' '.$row['shipping_lastname'].'<br />'."\r\n".
			($row['shipping_company'] ? $row['shipping_company'].'<br />'."\r\n":'').
			$row['shipping_address'].'<br />'."\r\n".
			$row['shipping_city'].($row['shipping_state'] ? ' '.$row['shipping_state']:'').'<br />'."\r\n".
			$row['shipping_country'].($row['shipping_zip'] ? ' '.$row['shipping_zip']:'').'<br />'."\r\n".
			($row['free_shipping']?'<strong>'.Yii::t('views/orders/edit_info_options','TITLE_FREE_SHIPPING').'</strong>'."\r\n".'<br />':'').

			($row['shipping_service']? "\r\n".'<br /><strong class="success">'.Yii::t('global','LABEL_SHIPPING_METHOD').'</strong> '.$row['shipping_service']:'').
			($row['shipping_telephone']? "\r\n".'<br /><strong class="success">'.Yii::t('controllers/OrdersController','LABEL_CONTACT_US_T').'</strong> '.$row['shipping_telephone']:'')).']]></cell>		
			<cell type="ro"><![CDATA['.Html::nf($row['grand_total']).']]></cell>
			
			<cell type="ro"><![CDATA[';
			
			
			switch ($row['payment_method']) {
				//cc
				case 0:
					echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_CREDIT_CARD');
					break;
				// interact
				case 1:
					echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_INTERACT');
					break;
				// cheque
				case 2:
					echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_CHEQUE');
					break;
				// paypal
				case 4:
					echo 'PayPal';
					break;
				// cash
				case 5:
					echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_CASH');
					break;					
			}
			
			
			echo ']]></cell>
			
			<cell type="ro"><![CDATA[';
			
			switch ($row['status']) {
				case -1:
					echo '<span style="color:#F00;">'.Yii::t('controllers/OrdersController','LABEL_STATUS_CANCELLED').'</span>';
					break;
				case 0:
					echo Yii::t('controllers/OrdersController','LABEL_STATUS_INCOMPLETE');
					break;					
				case 1:
					echo Yii::t('controllers/OrdersController','LABEL_STATUS_PENDING');
					break;
				case 2:
					echo Yii::t('controllers/OrdersController','LABEL_STATUS_PAYMENT_REVIEW');
					break;
				case 3:
					echo Yii::t('controllers/OrdersController','LABEL_STATUS_SUSPECTED_FRAUD');
					break;
				case 4:
					echo '<span style="color:#F00;">'.Yii::t('controllers/OrdersController','LABEL_STATUS_DECLINED').'</span>';
					break;
				case 5:
					echo Yii::t('controllers/OrdersController','LABEL_STATUS_PROCESSING');
					break;
				case 6:
					echo Yii::t('controllers/OrdersController','LABEL_STATUS_ON_HOLD');
					break;
				case 7:
					echo '<span style="color:#090;">'.Yii::t('controllers/OrdersController','LABEL_STATUS_COMPLETED').'</span>';
					break;
			}
			
			echo ']]></cell>
			
			<cell type="ro"><![CDATA[';
			
			switch ($row['priority']) {
				case 0:
					echo Yii::t('controllers/OrdersController','LABEL_PRIORITY_NORMAL');
					break;
				case 1:
					echo '<span style="color:#E839D7;">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_ATTENTION').'</span>';
					break;
				case 2:	
					echo '<span style="color:#F00;">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_URGENT').'</span>';
					break;					
			}

			echo ']]></cell>			
			</row>';
		}
		
		echo '</rows>';
	}	
	
	/************************************************************
	*															*
	*															*
	*						INFORMATION							*
	*															*
	*															*
	************************************************************/
		
	
	public function actionEdit_info_options($container, $containerLayout, $id=0)
	{
		$id = (int)$id;
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$order = $this->get_order_product($id);
		
		$this->renderPartial('edit_info_options',array('container'=>$container,'order'=>$order,'containerLayout'=>$containerLayout));	
	}
	
	public function get_order_product($id=0)
	{
		$id = (int)$id;
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$order = array();
		
		// get order
		$sql = 'SELECT 
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
		(orders.billing_state_code = billing_state.state_code AND billing_state.language_code = :language_code) 
		LEFT JOIN 
		country_description AS billing_country
		ON
		(orders.billing_country_code = billing_country.country_code AND billing_country.language_code = :language_code) 
		LEFT JOIN 
		state_description AS shipping_state
		ON
		(orders.shipping_state_code = shipping_state.state_code AND shipping_state.language_code = :language_code) 
		LEFT JOIN
		country_description AS shipping_country
		ON
		(orders.shipping_country_code = shipping_country.country_code AND shipping_country.language_code = :language_code) 
		
		LEFT JOIN 
		state_description AS local_pickup_state
		ON
		(orders.local_pickup_state_code = local_pickup_state.state_code AND local_pickup_state.language_code = :language_code) 
		LEFT JOIN 
		country_description AS local_pickup_country
		ON
		(orders.local_pickup_country_code = local_pickup_country.country_code AND local_pickup_country.language_code = :language_code) 
		
		WHERE
		orders.id = :id
		LIMIT 1';
		
		$command=$connection->createCommand($sql);
		
		$row = $command->queryRow(true, array(':id'=>$id,':language_code'=>Yii::app()->language));
		
		foreach ($row as $key => $value) {
			$order[$key] = $value;
		}
		
		// get products
		$sql = 'SELECT
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
		(orders_item.id = orders_item_product.id_orders_item AND orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = :language_code)		
		WHERE
		orders_item.id_orders = :id
		ORDER BY 
		orders_item.id ASC';
		
		$command=$connection->createCommand($sql);
		
		// get sub products
		$sql = 'SELECT
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
		(orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = :language_code)		
		WHERE
		orders_item_product.id_orders_item_product = :id_orders_item_product
		ORDER BY 
		orders_item_product.id ASC';
		
		$command_sub_products=$connection->createCommand($sql);
		
		// get product discounts
		$sql = 'SELECT
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
		(orders_discount_item_product.id_orders_discount = orders_discount.id AND orders_discount.id = orders_discount_description.id_orders_discount AND orders_discount_description.language_code = :language_code)
		WHERE
		orders_discount_item_product.id_orders_item_product = :id_orders_item_product
		ORDER BY 
		(CASE
			WHEN orders_discount.type = 0 AND orders_discount.coupon = 0 THEN 0
			WHEN orders_discount.type = 0 AND orders_discount.coupon = 1 THEN 1
			WHEN orders_discount.type = 2 THEN 3
			WHEN (orders_discount.type = 1 OR orders_discount.type = 5) AND orders_discount.coupon = 0 THEN 4
			WHEN orders_discount.type = 1 AND orders_discount.coupon = 1 THEN 5		
		END) ASC';
		
		$command_product_discounts=$connection->createCommand($sql);
		
		// get product option groups
		$sql = 'SELECT
		orders_options_group.id_options_group,
		orders_options_group.input_type,
		orders_options_group_description.name,
		orders_options_group_description.description
		FROM
		orders_item
		INNER JOIN
		(orders_item_option CROSS JOIN orders_options_group CROSS JOIN orders_options_group_description)
		ON
		(orders_item.id = orders_item_option.id_orders_item AND orders_item.id_orders = orders_options_group.id_orders AND orders_item_option.id_options_group = orders_options_group.id_options_group AND orders_options_group.id = orders_options_group_description.id_orders_options_group AND orders_options_group_description.language_code = :language_code) 
		WHERE
		orders_item.id = :id_orders_item
		GROUP BY 
		orders_options_group.id_options_group
		ORDER BY 
		orders_options_group.sort_order ASC';
		
		$command_product_option_groups=$connection->createCommand($sql);
		
		// get product options
		$sql = 'SELECT
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
		(orders_item_option.id = orders_item_option_description.id_orders_item_option AND orders_item_option_description.language_code = :language_code) 			
		WHERE
		orders_item_option.id_orders_item = :id_orders_item
		AND
		orders_item_option.id_options_group = :id_options_group
		ORDER BY 
		orders_item_option.id ASC';
		
		$command_product_option=$connection->createCommand($sql);
		
		// get product option discounts
		$sql = 'SELECT
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
		(orders_discount_item_option.id_orders_discount = orders_discount.id AND orders_discount.id = orders_discount_description.id_orders_discount AND orders_discount_description.language_code = :language_code)
		WHERE
		orders_discount_item_option.id_orders_item_option = :id_orders_item_option
		ORDER BY 
		(CASE
			WHEN (orders_discount.type = 1 OR orders_discount.type = 5) AND orders_discount.coupon = 0 THEN 4
			WHEN orders_discount.type = 1 AND orders_discount.coupon = 1 THEN 5		
		END) ASC';
		
		$command_product_option_discounts=$connection->createCommand($sql);
		
		// get taxes
		$sql = 'SELECT
		orders_tax.id,
		orders_tax.tax_number,
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
		(orders_tax.id = orders_tax_description.id_orders_tax AND orders_tax_description.language_code = :language_code)
		WHERE
		orders_tax.id_orders = :id
		ORDER BY 
		orders_tax.sort_order ASC';
		
		$command_taxes=$connection->createCommand($sql);
		
		// get comments
		$sql = 'SELECT 
		orders_comment.id,
		orders_comment.read_comment,
		orders_comment.id_user_created,
		IF(orders_comment.id_user_created != 0,CONCAT(user.firstname," ",user.lastname),CONCAT(customer.firstname," ",customer.lastname)) AS name,
		orders_comment.date_created,
		orders_comment.comments,
		orders_comment.hidden_from_customer
		FROM 
		orders_comment 
		INNER JOIN
		(orders CROSS JOIN customer)
		ON
		(orders_comment.id_orders = orders.id AND orders.id_customer = customer.id)
		LEFT JOIN
		user
		ON
		(orders_comment.id_user_created = user.id)
		WHERE
		orders_comment.id_orders = :id
		ORDER BY
		orders_comment.date_created DESC';
		
		$command_comments=$connection->createCommand($sql);
		
		// Cycle through results
		$array=array();
		foreach ($command->queryAll(true, array(':id'=>$id,':language_code'=>Yii::app()->language)) as $row) {
			$sub_products=array();
			switch ($row['product_type']) {
				// combo
				case 1:
				// bundle
				case 2:
					foreach ($command_sub_products->queryAll(true, array(':id_orders_item_product'=>$row['id_orders_item_product'],':language_code'=>Yii::app()->language)) as $row_sub_product) {							
						$sub_products[$row_sub_product['id']] = array(
							'id' => $row_sub_product['id'],
							'id_product' => $row_sub_product['id_product'],
							'id_product_variant' => $row_sub_product['id_product_variant'],
							'id' => $row_sub_product['id'],
							'qty' => $row_sub_product['qty'],
							'name' => $row_sub_product['name'].(!empty($row_sub_product['variant_name']) ? ' ('.$row_sub_product['variant_name'].')':''),
						);
					}
					break;						
			}
			
			$discounts=array();
			
			foreach ($command_product_discounts->queryAll(true, array(':id_orders_item_product'=>$row['id_orders_item_product'],':language_code'=>Yii::app()->language)) as $row_discount) {
				$discounts[] = $row_discount;		
			}
						
			$option_groups=array();
			
			foreach ($command_product_option_groups->queryAll(true, array(':id_orders_item'=>$row['id'],':language_code'=>Yii::app()->language)) as $row_option_group) {
				$option_groups[$row_option_group['id_options_group']] = array(
					'name'=>$row_option_group['name'],
					'description'=>$row_option_group['description'],	
					'input_type'=>$row_option_group['input_type'],				
				);		
				
				foreach ($command_product_option->queryAll(true, array(':id_orders_item'=>$row['id'],':id_options_group'=>$row_option_group['id_options_group'],':language_code'=>Yii::app()->language)) as $row_option) {
					$option_groups[$row_option_group['id_options_group']]['options'][$row_option['id']] = array(
						'name'=>$row_option['name'],
						'description'=>$row_option['description'],
						'id'=>$row_option['id'],
						'id_options'=>$row_option['id_options'],
						'sku'=>$row_option['sku'],
						'qty'=>$row_option['qty'],
						'sell_price'=>$row_option['sell_price'],
						'subtotal'=>$row_option['subtotal'],
						'textfield'=>$row_option['textfield'],
						'textarea'=>$row_option['textarea'],
						'filename'=>$row_option['filename'],
						'date_start'=>$row_option['date_start'],
						'date_end'=>$row_option['date_end'],
						'datetime_start'=>$row_option['datetime_start'],
						'datetime_end'=>$row_option['datetime_end'],
						'time_start'=>$row_option['time_start'],
						'time_end'=>$row_option['time_end'],
					);	
					
					foreach ($command_product_option_discounts->queryAll(true, array(':id_orders_item_option'=>$row_option['id'],':language_code'=>Yii::app()->language)) as $row_discount) {
						$option_groups[$row_option_group['id_options_group']]['options'][$row_option['id']]['discounts'][] = $row_discount;		
					}					
				}
			}			
										
			$array[$row['id']] = array(
				'id'=>$row['id'],
				'id_orders_discount'=>$row['id_orders_discount'],
				'id_orders_item_product'=>$row['id_orders_item_product'],
				'id_product'=>$row['id_product'],
				'id_product_variant'=>$row['id_product_variant'],
				'product_type' => $row['product_type'],
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
				'discounts'=>$discounts,
				'option_groups'=>$option_groups,
			);	
		}
		
		$order['products'] = $array;
		$order['taxes'] = array();
		
		foreach ($command_taxes->queryAll(true, array(':id'=>$id,':language_code'=>Yii::app()->language)) as $row) {
			$order['taxes'][] = $row;
		}
		
		$orders['comments'] = array();

		foreach ($command_comments->queryAll(true, array(':id'=>$id)) as $row) {
			$order['comments'][] = $row;
		}		

		return $order;	
	}		
	
	public function actionAdd_comment($id=0)
	{
		$id = (int)$id;
		$comment = htmlspecialchars(trim($_POST['comment']));
		$hidden_from_customer = (int)$_POST['hidden_from_customer'];
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		
		$connection=$app->db;   // assuming you have configured a "db" connection
		
		// get order info
		$sql = 'SELECT
		orders.id_customer
		FROM		
		orders
		WHERE
		orders.id = :id
		LIMIT 1';
		
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true,array(':id'=>$id));		
		$id_customer = $row['id_customer'];
		
		// check if comment already exist
		$sql = 'SELECT
		COUNT(orders_comment.id) AS total
		FROM		
		orders_comment 
		WHERE
		orders_comment.id_orders = :id
		AND
		orders_comment.comments = :comment';
		
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true,array(':id'=>$id,':comment'=>$comment));
		if (!$row['total']) {
			$sql = 'INSERT INTO 
			orders_comment
			SET 
			orders_comment.id_orders = :id,
			orders_comment.id_user_created = :id_user_created,
			orders_comment.comments = :comment,
			orders_comment.hidden_from_customer = :hidden_from_customer,
			orders_comment.date_created = :date_created';
			
			$command=$connection->createCommand($sql);
			$command->execute(array(':id'=>$id,':id_user_created'=>$app->user->id,':comment'=>$comment,':hidden_from_customer'=>$hidden_from_customer, ':date_created'=>$current_datetime));
			
			if (!$hidden_from_customer) { 			
				// send email
				$sql = 'SELECT
				customer.firstname,
				customer.lastname,
				customer.email,
				customer.language_code
				FROM
				customer
				WHERE
				customer.id = :id
				LIMIT 1';
				$command=$connection->createCommand($sql);
				$row = $command->queryRow(true,array(':id'=>$id_customer));
				
				$mail = new PHPMailer(); // defaults to using php "mail()"
				$mail->CharSet = 'UTF-8';
				$mail->SetLanguage($row['language_code']);	
				
				// text only
				//$mail->IsHTML(false);
			
				$mail->SetFrom($app->params['no_reply_email'], $app->params['site_name']);
		
				$mail->AddAddress($row['email'], $row['firstname'].' '.$row['lastname']);
				
				$mail->Subject = Yii::t('emails','ORDERS_ADD_COMMENT_SUBJECT',array(),NULL,$row['language_code']);				
				
				$mail->AltBody = Yii::t('emails','ORDERS_ADD_COMMENT_PLAIN', array('{person_name}'=>$row['firstname'].' '.$row['lastname'],
				'{comment}'=>$comment,
				'{id_orders}'=>str_pad($id,10,0,STR_PAD_LEFT),
				'{signature}'=>Html::get_company_signature(0,$row['language_code']),
				));
				
				$mail->MsgHTML(Yii::t('emails','ORDERS_ADD_COMMENT_HTML', array('{person_name}'=>$row['firstname'].' '.$row['lastname'],
				'{comment}'=>nl2br($comment),
				'{id_orders}'=>str_pad($id,10,0,STR_PAD_LEFT),
				'{signature}'=>Html::get_company_signature(1,$row['language_code']),
				)));
				
				$mail->Send();
			}
		}
		
		// get list of comments
		$sql = 'SELECT 
		orders_comment.id,
		IF(orders_comment.id_user_created != 0,CONCAT(user.firstname," ",user.lastname),CONCAT(customer.firstname," ",customer.lastname)) AS name,
		orders_comment.date_created,
		orders_comment.comments,
		orders_comment.hidden_from_customer,
		orders_comment.read_comment,
		orders_comment.id_user_created
		FROM 
		orders_comment 
		INNER JOIN
		(orders CROSS JOIN customer)
		ON
		(orders_comment.id_orders = orders.id AND orders.id_customer = customer.id)
		LEFT JOIN
		user
		ON
		(orders_comment.id_user_created = user.id)
		WHERE
		orders_comment.id_orders = :id
		ORDER BY
		orders_comment.date_created DESC';
		
		$command=$connection->createCommand($sql);
		
		$i=0;
		foreach ($command->queryAll(true, array(':id'=>$id)) as $row) {
			if ($i==1) $i=0;
			else $i=1;
			
			echo '<div style="padding:10px; background-color:'.((!$row['read_comment'] and !$row['id_user_created'])?'#FFFFCC':($i?'#EBEBEB':'#FFF')).';" id="mark_as_read_'.$row['id'].'_comment">
			<div><strong>'.Yii::t('views/orders/edit_info_options','LABEL_FROM').':</strong> '.$row['name'].' <strong>'.Yii::t('views/orders/edit_info_options','LABEL_DATE').':</strong> '.$row['date_created'].($row['hidden_from_customer'] ? ' (<span style="color:#F00;">'.Yii::t('views/orders/edit_info_options','LABEL_HIDDEN_FROM_CUSTOMER').'</span>)':'').'</div>
			<div style="margin-top:5px;">'.nl2br($row['comments']).'</div>
			
			'.((!$row['id_user_created'])?'<div style="float:right"><input type="button" id="mark_as_read_'.$row['id'].'" value="'.(!$row['read_comment']?Yii::t('views/orders/edit_info_options','LABEL_BTN_MARK_AS_READ'):Yii::t('views/orders/edit_info_options','LABEL_BTN_MARK_AS_UNREAD')).'" class="mark_as_read" /><input type="hidden" id="mark_as_read_'.$row['id'].'_id_comment" value="'.$row['id'].'" /></div>':'').'
			
			<div style="clear:both"></div>
			
			
			
			</div>';
		}				
		exit;
	}
	
	public function actionMark_as_read_comment()
	{
		$id_comment = trim($_POST['id_comment']);
		if($id_comment){
			$row = Tbl_OrdersComment::model()->find('id='.$id_comment);
			if($row->read_comment){
				$read_status = 0;
			}else{
				$read_status = 1;
			}
	
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id_comment); 	
			Tbl_OrdersComment::model()->updateAll(array('read_comment'=>$read_status,'id_user_read'=>Yii::app()->user->id),$criteria);
			echo $read_status;
		}
						
		exit;
	}
	
	
	public function actionChange_date_payment($id=0)
	{
		$id = (int)$id;
		$date_payment = trim($_POST['date_payment']);
		if($date_payment){
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 	
			Tbl_Orders::model()->updateAll(array('date_payment'=>$date_payment),$criteria);
			echo $read_status;
		}
						
		exit;
	}
	
	
	
	public function actionSet_order_status($container, $id=0)
	{
		$id = (int)$id;
		
		if (!$orders = Tbl_Orders::model()->findByPk($id)) {			
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}				

		$this->renderPartial('set_order_status',array('container'=>$container,'id'=>$id,'status'=>$orders->status));	
	}			
	
	/**
	 * This is the action to save order status
	 */
	public function actionSave_status($id)
	{
		$status = (int)$_POST['status'];
		$update_qty = (int)$_POST['update_qty'];
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		if (!$orders = Tbl_Orders::model()->findByPk($id)) {			
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		$orders->status = $status;
		$orders->save();
		
		$order = $this->get_order_product($id);

		if($update_qty){
			/*$criteria=new CDbCriteria; 
			$criteria->condition='id_orders=:id_orders'; 
			$criteria->params=array(':id_orders'=>$id); 	
			Tbl_OrdersGiftCertificate::model()->updateAll(array('cancel'=>1),$criteria);*/
			
			$sql = 'UPDATE 
			product_variant 
			SET
			qty = qty + :qty, 
			active = IF(qty>:out_of_stock AND active = 0,1,active),
			in_stock = IF(qty>:out_of_stock AND in_stock = 0,1,in_stock)
			WHERE
			id = :id';
			
			$command_variant=$connection->createCommand($sql);
			
			$sql = 'UPDATE 
			product 
			SET
			qty = qty + :qty, 
			active = IF(qty>:out_of_stock AND active = 0,1,active),
			in_stock = IF(qty>:out_of_stock AND in_stock = 0,1,in_stock)
			WHERE
			id = :id';
			
			$command=$connection->createCommand($sql);
			
			$sql = 'UPDATE 
			options 
			SET
			qty = qty + :qty,
			active = IF(qty>:out_of_stock AND active = 0,1,active),
			in_stock = IF(qty>:out_of_stock AND in_stock = 0,1,in_stock)
			WHERE
			id = :id';
			
			$command_option=$connection->createCommand($sql);			
			
			foreach ($order['products'] as $row_product) {
				if(sizeof($row_product['sub_products'])){
					foreach ($row_product['sub_products'] as $row_sub_product) {
						$qty = $row_product['qty']*$row_sub_product['qty'];
						
						if ($row_inventory = Tbl_Product::model()->findByPk($row_sub_product['id_product'],'track_inventory=1')) {
							if($row_sub_product['id_product_variant']){
								$command_variant->execute(array(':qty'=>$qty,':out_of_stock'=>$row_inventory->out_of_stock,
								':id'=>$row_sub_product['id_product_variant']));
							} else {
								$command->execute(array(':qty'=>$qty,':out_of_stock'=>$row_inventory->out_of_stock,
								':id'=>$row_sub_product['id_product']));
							}								
						} 
						
					}	
				}else if ($row_inventory = Tbl_Product::model()->findByPk($row_product['id_product'],'track_inventory=1')) {
					if($row_product['id_product_variant']){
						$command_variant->execute(array(':qty'=>$row_product['qty'],':out_of_stock'=>$row_inventory->out_of_stock,
						':id'=>$row_product['id_product_variant']));
					} else {
						$command->execute(array(':qty'=>$row_product['qty'],':out_of_stock'=>$row_inventory->out_of_stock,
						':id'=>$row_product['id_product']));
					}					
				}
				
				
				if(sizeof($row_product['option_groups'])){
					foreach ($row_product['option_groups'] as $row_product_option_group) {
						foreach ($row_product_option_group['options'] as $row_product_option) { 
							if ($row_inventory = Tbl_Options::model()->findByPk($row_product_option['id_options'],'track_inventory=1')) {
								$qty = $row_product['qty']*$row_product_option['qty'];
								
								$command_option->execute(array(':qty'=>$qty,':out_of_stock'=>$row_inventory->out_of_stock,
								':id'=>$row_product_option['id_options']));
							}							
						}
					}
				}
			}
		}		
	}		
	
	/**
	 * This is the action to get order status
	 */
	public function actionGet_status($id)
	{
		if (!$orders = Tbl_Orders::model()->findByPk($id)) {			
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		switch ($orders->status) {
			case -1:
				echo '<span style="color:#F00;">'.Yii::t('controllers/OrdersController','LABEL_STATUS_CANCELLED').'</span>';
				break;
			case 0:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_INCOMPLETE');
				break;					
			case 1:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_PENDING');
				break;
			case 2:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_PAYMENT_REVIEW');
				break;
			case 3:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_SUSPECTED_FRAUD');
				break;
			case 4:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_DECLINED');
				break;
			case 5:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_PROCESSING');
				break;
			case 6:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_ON_HOLD');
				break;
			case 7:
				echo '<span style="color:#090;">'.Yii::t('controllers/OrdersController','LABEL_STATUS_COMPLETED').'</span>';
				break;
		}
		exit;
	}		
	
	
	public function actionSet_order_priority($container, $id=0)
	{
		$id = (int)$id;
		
		if (!$orders = Tbl_Orders::model()->findByPk($id)) {			
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}				

		$this->renderPartial('set_order_priority',array('container'=>$container,'id'=>$id,'priority'=>$orders->priority));	
	}			
	
	/**
	 * This is the action to save order status
	 */
	public function actionSave_priority($id)
	{
		$priority = (int)$_POST['priority'];
		
		if (!$orders = Tbl_Orders::model()->findByPk($id)) {			
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		$orders->priority = $priority;
		$orders->save();		
	}		
	
	/**
	 * This is the action to get order status
	 */
	public function actionGet_priority($id)
	{
		if (!$orders = Tbl_Orders::model()->findByPk($id)) {			
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		switch ($orders->priority) {
			case 0:
				echo Yii::t('controllers/OrdersController','LABEL_PRIORITY_NORMAL');
				break;
			case 1:
				echo '<span style="color:#E839D7;">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_ATTENTION').'</span>';
				break;
			case 2:	
				echo '<span style="color:#F00;">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_URGENT').'</span>';
				break;		
		}
		exit;
	}		
	
	/************************************************************
	*															*
	*															*
	*						SHIPMENTS							*
	*															*
	*															*
	************************************************************/	
	
	/**
	 * This is the action to get an XML list of shipments
	 */
	public function actionXml_list_shipments($posStart=0, $count=100, array $filters=array(), array $sort_col=array(),$id_orders=0)
	{		
		$id_orders=(int)$id_orders;		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;		
		
		$where=array('orders_shipment.id_orders=:id_orders');
		$params=array(':id_orders'=>$id_orders);
		
		// filters
		
		// shipment_no
		if (isset($filters['shipment_no']) && !empty($filters['shipment_no'])) {
			$where[] = 'orders_shipment.shipment_no LIKE CONCAT("%",:shipment_no,"%")';
			$params[':shipment_no']=$filters['shipment_no'];
		}
		
		// date_order start
		if (isset($filters['date_shipment_start']) && !empty($filters['date_shipment_start'])) {
			$where[] = 'orders_shipment.date_shipment >= :date_shipment_start';
			$params[':date_shipment_start']=$filters['date_shipment_start'];
		}	
		
		// date_order end
		if (isset($filters['date_shipment_end']) && !empty($filters['date_shipment_end'])) {
			$where[] = 'orders_shipment.date_shipment <= :date_shipment_end';
			$params[':date_shipment_end']=$filters['date_shipment_end'];
		}	
		
		// tracking_no
		if (isset($filters['tracking_no']) && !empty($filters['tracking_no'])) {
			$where[] = 'orders_shipment.tracking_no LIKE CONCAT("%",:tracking_no,"%")';
			$params[':tracking_no']=$filters['tracking_no'];
		}		
		
		// comments
		if (isset($filters['comments']) && !empty($filters['comments'])) {
			$where[] = 'orders_shipment.comments LIKE CONCAT("%",:comments,"%")';
			$params[':comments']=$filters['comments'];
		}				
										
		
		$sql = "SELECT 
		COUNT(orders_shipment.id) AS total 
		FROM 
		orders_shipment					
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT 
		orders_shipment.id,
		orders_shipment.shipment_no,
		orders_shipment.date_shipment,
		orders_shipment.tracking_no,
		orders_shipment.comments
		FROM 
		orders_shipment			
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// shipment_no
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders_shipment.shipment_no ".$direct;
		// date_shipment
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders_shipment.date_shipment ".$direct;	
		// tracking_no
		} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders_shipment.tracking_no ".$direct;				
		// comments
		} else if (isset($sort_col[4]) && !empty($sort_col[4])) {
			$direct = $sort_col[4] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders_shipment.comments ".$direct;																			
		} else {
			$sql.=" ORDER BY orders_shipment.id DESC";
		}		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
						
			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['shipment_no'].']]></cell>
			<cell type="ro"><![CDATA['.$row['date_shipment'].']]></cell>			
			<cell type="ro"><![CDATA['.$row['tracking_no'].']]></cell>		
			<cell type="ro"><![CDATA['.nl2br($row['comments']).']]></cell>			
			</row>';
		}
		
		echo '</rows>';
	}		
	
	public function actionEdit_shipments_options($container, $id_orders=0)
	{
		$id = (int)$_POST['id'];
		$id_orders = (int)$id_orders;
		
		$model = new OrdersShipmentForm;
		$model->id_orders = $id_orders;
		
		if ($id) {
			if ($orders_shipment = Tbl_OrdersShipment::model()->findByPk($id)) {
				$model->id = $orders_shipment->id;				
				$model->shipment_no = $orders_shipment->shipment_no;
				$model->date_shipment = ($orders_shipment->date_shipment != '0000-00-00 00:00:00') ? $orders_shipment->date_shipment:'';
				$model->tracking_no = $orders_shipment->tracking_no;
				$model->tracking_url = $orders_shipment->tracking_url;
				$model->comments = $orders_shipment->comments;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		
		}
		

		$this->renderPartial('edit_shipments_options',array('container'=>$container,'model'=>$model));	
	}			
	
	/**
	 * This is the action to save the shipment info
	 */
	public function actionSave_shipment()
	{
		$model = new OrdersShipmentForm;
		
		$ids = $_POST['ids'];
		
		// collect user input data
		if(isset($_POST['OrdersShipmentForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['OrdersShipmentForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
			foreach (array_diff_key($model->products,array_flip($ids)) as $k => $v) unset($model->products[$k]);
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			
			$output = array('id'=>$model->id);
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);	
		}
	}	
	


	/**
	 * This is the action to delete a shipment
	 */
	public function actionDelete_shipment()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			$sql = 'DELETE FROM
			orders_shipment,
			orders_shipment_item
			USING
			orders_shipment
			LEFT JOIN 
			orders_shipment_item
			ON
			(orders_shipment.id = orders_shipment_item.id_orders_shipment)
			WHERE
			orders_shipment.id = :id';	
						
			$command=$connection->createCommand($sql);			
			
			foreach ($ids as $id) {
				// delete all
				$command->execute(array(':id'=>$id));
			}
		}
	}			
	

	/**
	 * This is the action to get an XML list products left to ship
	 */
	public function actionXml_list_shipments_products_left($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $container='', $id_orders=0, $id=0)
	{		
		$model = new OrdersShipmentForm;
	
		$id_orders = (int)$id_orders;
		$id = (int)$id;

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':language_code'=>Yii::app()->language,':id_orders'=>$id_orders,':id_orders_shipment'=>$id);
		
		// filters
		
		// sku
		if (isset($filters['sku']) && !empty($filters['sku'])) {
			$where[] = 'sku LIKE CONCAT("%",:sku,"%")';
			$params[':sku']=$filters['sku'];
		}
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}		
		
		// total
		if (isset($filters['qty']) && !empty($filters['qty'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['qty'])) {
				$where[] = 'qty <= :qty';
				$params[':qty']=ltrim($filters['qty'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['qty'])) {
				$where[] = 'qty >= :qty';
				$params[':qty']=ltrim($filters['qty'],'>=');
			// <N
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['qty'])) {		
				$where[] = 'qty < :qty';
				$params[':qty']=ltrim($filters['qty'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['qty'])) {		
				$where[] = 'qty > :qty';
				$params[':qty']=ltrim($filters['qty'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['qty'])) {		
				$where[] = 'qty = :qty';
				$params[':qty']=ltrim($filters['qty'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['qty'])) {
				$search = explode('..',$filters['total']);
				$where[] = 'qty BETWEEN :qty AND :qty';
				$params[':total_start']=$search[0];
				$params[':total_end']=$search[1];
			// N				
			} else {
				$where[] = 'qty = :qty';
				$params[':qty']=$filters['qty'];
			}
		}									
		
		$sql = "SELECT 
		COUNT(t.id) AS total 
		FROM
			((SELECT 
				orders_item_product.id,
				IF(orders_item_product.id_product_variant,orders_item_product.variant_sku,orders_item_product.sku) AS sku,
				IF(orders_item_product.id_product_variant,CONCAT(orders_item_product_description.name,' ',orders_item_product_description.variant_name),orders_item_product_description.name) AS name,
				(orders_item_product.qty-IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_product = orders_item_product.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment != :id_orders_shipment))),0)) AS qty,
                0 AS is_option
				FROM 
				orders_item 
				INNER JOIN 
				(orders_item_product CROSS JOIN orders_item_product_description)
				ON
				(orders_item.id = orders_item_product.id_orders_item AND orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = :language_code) 
				WHERE
				orders_item.id_orders = :id_orders
				AND 
				orders_item_product.product_type = 0
				AND
				(orders_item_product.qty-IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_product = orders_item_product.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment != :id_orders_shipment))),0)) > 0)
			
			UNION
			
			(SELECT 
				orders_item_product.id,
				IF(orders_item_product.id_product_variant,orders_item_product.variant_sku,orders_item_product.sku) AS sku,
				IF(orders_item_product.id_product_variant,CONCAT(orders_item_product_description.name,' ',orders_item_product_description.variant_name),orders_item_product_description.name) AS name,
				(orders_item_product.qty-IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_product = orders_item_product.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment != :id_orders_shipment))),0)) AS qty,
                0 AS is_option
				FROM 
				orders_item 
				INNER JOIN 
				(orders_item_product AS oip_parent
				CROSS JOIN orders_item_product
				CROSS JOIN orders_item_product_description)				
				ON
				(orders_item.id = oip_parent.id_orders_item AND oip_parent.id = orders_item_product.id_orders_item_product AND orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = :language_code) 
				WHERE
				orders_item.id_orders = :id_orders
				AND 
				oip_parent.product_type != 0
				AND
				(orders_item_product.qty-IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_product = orders_item_product.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment != :id_orders_shipment))),0)) > 0
				ORDER BY 
				oip_parent.id ASC,
				orders_item_product.id ASC) 

			UNION
            
			(SELECT 
				orders_item_option.id,
                orders_item_option.sku,
				orders_item_option_description.name,
				(orders_item_option.qty-IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_option = orders_item_option.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment != :id_orders_shipment))),0)) AS qty,
                1 AS is_option
				FROM 
				orders_item 
				INNER JOIN 
				(orders_item_option CROSS JOIN orders_item_option_description)
				ON
				(orders_item.id = orders_item_option.id_orders_item AND orders_item_option.id = orders_item_option_description.id_orders_item_option AND orders_item_option_description.language_code = :language_code) 
				WHERE
				orders_item.id_orders = :id_orders
				AND
				(orders_item_option.qty-IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_option = orders_item_option.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment != :id_orders_shipment))),0)) > 0)) AS t
			
						
				
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT 
		t.*
		FROM
			((SELECT 
				orders_item_product.id,
				IF(orders_item_product.id_product_variant,orders_item_product.variant_sku,orders_item_product.sku) AS sku,
				IF(orders_item_product.id_product_variant,CONCAT(orders_item_product_description.name,' ',orders_item_product_description.variant_name),orders_item_product_description.name) AS name,
				(orders_item_product.qty-IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_product = orders_item_product.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment != :id_orders_shipment))),0)) AS qty,
                0 AS is_option,
				IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_product = orders_item_product.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment = :id_orders_shipment))),0) AS default_selection
				FROM 
				orders_item 
				INNER JOIN 
				(orders_item_product CROSS JOIN orders_item_product_description)
				ON
				(orders_item.id = orders_item_product.id_orders_item AND orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = :language_code) 
				WHERE
				orders_item.id_orders = :id_orders
				AND 
				orders_item_product.product_type = 0
				AND
				(orders_item_product.qty-IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_product = orders_item_product.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment != :id_orders_shipment))),0)) > 0)
			
			UNION
			
			(SELECT 
				orders_item_product.id,
				IF(orders_item_product.id_product_variant,orders_item_product.variant_sku,orders_item_product.sku) AS sku,
				IF(orders_item_product.id_product_variant,CONCAT(orders_item_product_description.name,' ',orders_item_product_description.variant_name),orders_item_product_description.name) AS name,
				(orders_item_product.qty-IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_product = orders_item_product.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment != :id_orders_shipment))),0)) AS qty,
                0 AS is_option,
				IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_product = orders_item_product.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment = :id_orders_shipment))),0) AS default_selection
				FROM 
				orders_item 
				INNER JOIN 
				(orders_item_product AS oip_parent
				CROSS JOIN orders_item_product
				CROSS JOIN orders_item_product_description)				
				ON
				(orders_item.id = oip_parent.id_orders_item AND oip_parent.id = orders_item_product.id_orders_item_product AND orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = :language_code) 
				WHERE
				orders_item.id_orders = :id_orders
				AND 
				oip_parent.product_type != 0
				AND
				(orders_item_product.qty-IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_product = orders_item_product.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment != :id_orders_shipment))),0)) > 0
				ORDER BY 
				oip_parent.id ASC,
				orders_item_product.id ASC) 

			UNION
            
			(SELECT 
				orders_item_option.id,
                orders_item_option.sku,
				orders_item_option_description.name,
				(orders_item_option.qty-IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_option = orders_item_option.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment != :id_orders_shipment))),0)) AS qty,
                1 AS is_option,
				IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_option = orders_item_option.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment = :id_orders_shipment))),0) AS default_selection
				FROM 

				orders_item 
				INNER JOIN 
				(orders_item_option CROSS JOIN orders_item_option_description)
				ON
				(orders_item.id = orders_item_option.id_orders_item AND orders_item_option.id = orders_item_option_description.id_orders_item_option AND orders_item_option_description.language_code = :language_code) 
				WHERE
				orders_item.id_orders = :id_orders
				AND
				(orders_item_option.qty-IFNULL((SELECT 
				SUM(orders_shipment_item.qty)
				FROM
				orders_shipment_item
				WHERE
				orders_shipment_item.id_orders_item_option = orders_item_option.id
				AND
                (0 = :id_orders_shipment
                OR                
                (0 != :id_orders_shipment AND orders_shipment_item.id_orders_shipment != :id_orders_shipment))),0)) > 0)) AS t	
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// sku
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY sku ".$direct;
		// name
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY name ".$direct;	
		// qty
		} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY qty ".$direct;				
		} else {
			$sql.=" ORDER BY id DESC";
		}		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		$i=0;
		foreach ($command->queryAll(true, $params) as $row) {
			$model->products[$i]['id'] = $row['id'];
			$model->products[$i]['is_option'] = $row['is_option'];
			$model->products[$i]['qty'] = $row['default_selection'];
						
			echo '<row id="'.$i.'">
			<cell type="ch">'.($row['default_selection'] ? 1:'').'</cell>
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>			
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>			
			<cell type="ro"><![CDATA['.$row['qty'].']]></cell>		
			<cell type="ro"><![CDATA['.CHtml::activeDropDownList($model,'products['.$i.'][qty]',range(0,$row['qty']),array( 'id'=>$container.'_products['.$i.'][qty]','size'=>1)).CHtml::activeHiddenField($model,'products['.$i.'][id]',array( 'id'=>$container.'_products['.$i.'][id]')).CHtml::activeHiddenField($model,'products['.$i.'][is_option]',array( 'id'=>$container.'_products['.$i.'][is_option]')).']]></cell>		
			</row>';
			
			++$i;
		}
		
		echo '</rows>';
	}		


	/**
	 * This is the action to edit or create a product
	 */ 
	public function actionEdit($container, $id=0)	
	{
		$id = (int)$id;

		if ($id) { 
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 		
			
			if (!Tbl_Orders::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		
		}	
				
		$this->renderPartial('edit',array('id'=>$id, 'container'=>$container));	
	}
	
	/**
	 * This is the action to edit each section
	 */ 
	public function actionEdit_section($container, $containerJS)	
	{						
		$id = trim($_POST['id']);
		$id_order = (int)$_POST['id_order'];
		
		if ($id_order) { 
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id_order); 		
			
			if (!Tbl_Orders::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}			
		}
						
		$this->renderPartial($id,array('id'=>$id_order,'container'=>$container,'containerJS'=>$containerJS));	
	}	
		
	/**
	 * This is the action to get the list of province	
	 */
	public function actionGet_province_list()
	{
		$model=new OrdersCustomerInfoForm;
		
		$id_prefix = get_class($model);		
		
		$country = trim($_POST['country']);
		$section = trim($_POST['section']);
		
		switch ($section) {
			case 'billing':
				echo Html::generateStateList($id_prefix.'[billing_state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>'--'));
				break;
			case 'shipping':
				echo Html::generateStateList($id_prefix.'[shipping_state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>'--'));
				break;
		}
	}			
	
	public function actionEdit_info_print($id)
	{
		$id=(int)$id;
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$order = array();
		
		// get order
		$sql = 'SELECT 
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
		(orders.billing_state_code = billing_state.state_code AND billing_state.language_code = :language_code) 
		LEFT JOIN 
		country_description AS billing_country
		ON
		(orders.billing_country_code = billing_country.country_code AND billing_country.language_code = :language_code) 
		LEFT JOIN 
		state_description AS shipping_state
		ON
		(orders.shipping_state_code = shipping_state.state_code AND shipping_state.language_code = :language_code) 
		LEFT JOIN
		country_description AS shipping_country
		ON
		(orders.shipping_country_code = shipping_country.country_code AND shipping_country.language_code = :language_code) 
		LEFT JOIN 
		state_description AS local_pickup_state
		ON
		(orders.local_pickup_state_code = local_pickup_state.state_code AND local_pickup_state.language_code = :language_code) 
		LEFT JOIN 
		country_description AS local_pickup_country
		ON
		(orders.local_pickup_country_code = local_pickup_country.country_code AND local_pickup_country.language_code = :language_code)
		WHERE
		orders.id = :id
		LIMIT 1';
		
		$command=$connection->createCommand($sql);
		
		$row = $command->queryRow(true, array(':id'=>$id,':language_code'=>Yii::app()->language));
		
		foreach ($row as $key => $value) {
			$order[$key] = $value;
		}
		
		// get products
		$sql = 'SELECT
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
		orders_item_product.sku,
		orders_item_product.variant_sku,
		orders_item_product.price,
		orders_item_product.sell_price,
		orders_item_product.subtotal				
		FROM
		orders_item 
		INNER JOIN
		(orders_item_product CROSS JOIN orders_item_product_description)
		ON
		(orders_item.id = orders_item_product.id_orders_item AND orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = :language_code)		
		WHERE
		orders_item.id_orders = :id
		ORDER BY 
		orders_item.id ASC';
		
		$command=$connection->createCommand($sql);
		
		// get sub products
		$sql = 'SELECT
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
		orders_item_product.sku,
		orders_item_product.variant_sku,
		orders_item_product.price,
		orders_item_product.sell_price,
		orders_item_product.subtotal				
		FROM
		orders_item_product
		INNER JOIN 
		orders_item_product_description
		ON
		(orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = :language_code)		
		WHERE
		orders_item_product.id_orders_item_product = :id_orders_item_product
		ORDER BY 
		orders_item_product.id ASC';
		
		$command_sub_products=$connection->createCommand($sql);
		
		// get product discounts
		$sql = 'SELECT
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
		(orders_discount_item_product.id_orders_discount = orders_discount.id AND orders_discount.id = orders_discount_description.id_orders_discount AND orders_discount_description.language_code = :language_code)
		WHERE
		orders_discount_item_product.id_orders_item_product = :id_orders_item_product
		ORDER BY 
		(CASE
			WHEN orders_discount.type = 0 AND orders_discount.coupon = 0 THEN 0
			WHEN orders_discount.type = 0 AND orders_discount.coupon = 1 THEN 1
			WHEN orders_discount.type = 2 THEN 3
			WHEN (orders_discount.type = 1 OR orders_discount.type = 5) AND orders_discount.coupon = 0 THEN 4
			WHEN orders_discount.type = 1 AND orders_discount.coupon = 1 THEN 5		
		END) ASC';
		
		$command_product_discounts=$connection->createCommand($sql);
		
		// get product option groups
		$sql = 'SELECT
		orders_options_group.id_options_group,
		orders_options_group.input_type,
		orders_options_group_description.name,
		orders_options_group_description.description
		FROM
		orders_item
		INNER JOIN
		(orders_item_option CROSS JOIN orders_options_group CROSS JOIN orders_options_group_description)
		ON
		(orders_item.id = orders_item_option.id_orders_item AND orders_item.id_orders = orders_options_group.id_orders AND orders_item_option.id_options_group = orders_options_group.id_options_group AND orders_options_group.id = orders_options_group_description.id_orders_options_group AND orders_options_group_description.language_code = :language_code) 
		WHERE
		orders_item.id = :id_orders_item
		GROUP BY 
		orders_options_group.id_options_group
		ORDER BY 
		orders_options_group.sort_order ASC';
		
		$command_product_option_groups=$connection->createCommand($sql);
		
		// get product options
		$sql = 'SELECT
		orders_item_option_description.name,
		orders_item_option_description.description,
		orders_item_option.id,		
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
		(orders_item_option.id = orders_item_option_description.id_orders_item_option AND orders_item_option_description.language_code = :language_code) 			
		WHERE
		orders_item_option.id_orders_item = :id_orders_item
		AND
		orders_item_option.id_options_group = :id_options_group
		ORDER BY 
		orders_item_option.id ASC';
		
		$command_product_option=$connection->createCommand($sql);
		
		// get product option discounts
		$sql = 'SELECT
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
		(orders_discount_item_option.id_orders_discount = orders_discount.id AND orders_discount.id = orders_discount_description.id_orders_discount AND orders_discount_description.language_code = :language_code)
		WHERE
		orders_discount_item_option.id_orders_item_option = :id_orders_item_option
		ORDER BY 
		(CASE
			WHEN (orders_discount.type = 1 OR orders_discount.type = 5) AND orders_discount.coupon = 0 THEN 4
			WHEN orders_discount.type = 1 AND orders_discount.coupon = 1 THEN 5		
		END) ASC';
		
		$command_product_option_discounts=$connection->createCommand($sql);
		
		// get taxes
		$sql = 'SELECT
		orders_tax.id,
		orders_tax.tax_number,
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
		(orders_tax.id = orders_tax_description.id_orders_tax AND orders_tax_description.language_code = :language_code)
		WHERE
		orders_tax.id_orders = :id
		ORDER BY 
		orders_tax.sort_order ASC';
		
		$command_taxes=$connection->createCommand($sql);
		
		// get comments
		$sql = 'SELECT 
		orders_comment.id,
		IF(orders_comment.id_user_created != 0,CONCAT(user.firstname," ",user.lastname),CONCAT(customer.firstname," ",customer.lastname)) AS name,
		orders_comment.date_created,
		orders_comment.comments,
		orders_comment.hidden_from_customer
		FROM 
		orders_comment 
		INNER JOIN
		(orders CROSS JOIN customer)
		ON
		(orders_comment.id_orders = orders.id AND orders.id_customer = customer.id)
		LEFT JOIN
		user
		ON
		(orders_comment.id_user_created = user.id)
		WHERE
		orders_comment.id_orders = :id
		ORDER BY
		orders_comment.date_created DESC';
		
		$command_comments=$connection->createCommand($sql);
		
		// Cycle through results
		$array=array();
		foreach ($command->queryAll(true, array(':id'=>$id,':language_code'=>Yii::app()->language)) as $row) {
			$sub_products=array();
			switch ($row['product_type']) {
				// combo
				case 1:
				// bundle
				case 2:
					foreach ($command_sub_products->queryAll(true, array(':id_orders_item_product'=>$row['id_orders_item_product'],':language_code'=>Yii::app()->language)) as $row_sub_product) {							
						$sub_products[$row_sub_product['id']] = array(
							'id' => $row_sub_product['id'],
							'qty' => $row_sub_product['qty'],
							'name' => $row_sub_product['name'].(!empty($row_sub_product['variant_name']) ? ' ('.$row_sub_product['variant_name'].')':''),
						);
					}
					break;						
			}
			
			$discounts=array();
			
			foreach ($command_product_discounts->queryAll(true, array(':id_orders_item_product'=>$row['id_orders_item_product'],':language_code'=>Yii::app()->language)) as $row_discount) {
				$discounts[] = $row_discount;		
			}
						
			$option_groups=array();
			
			foreach ($command_product_option_groups->queryAll(true, array(':id_orders_item'=>$row['id'],':language_code'=>Yii::app()->language)) as $row_option_group) {
				$option_groups[$row_option_group['id_options_group']] = array(
					'name'=>$row_option_group['name'],
					'description'=>$row_option_group['description'],	
					'input_type'=>$row_option_group['input_type'],				
				);		
				
				foreach ($command_product_option->queryAll(true, array(':id_orders_item'=>$row['id'],':id_options_group'=>$row_option_group['id_options_group'],':language_code'=>Yii::app()->language)) as $row_option) {
					$option_groups[$row_option_group['id_options_group']]['options'][$row_option['id']] = array(
						'name'=>$row_option['name'],
						'description'=>$row_option['description'],
						'id'=>$row_option['id'],
						'sku'=>$row_option['sku'],
						'qty'=>$row_option['qty'],
						'sell_price'=>$row_option['sell_price'],
						'subtotal'=>$row_option['subtotal'],
						'textfield'=>$row_option['textfield'],
						'textarea'=>$row_option['textarea'],
						'filename'=>$row_option['filename'],
						'date_start'=>$row_option['date_start'],
						'date_end'=>$row_option['date_end'],
						'datetime_start'=>$row_option['datetime_start'],
						'datetime_end'=>$row_option['datetime_end'],
						'time_start'=>$row_option['time_start'],
						'time_end'=>$row_option['time_end'],
					);	
					
					foreach ($command_product_option_discounts->queryAll(true, array(':id_orders_item_option'=>$row_option['id'],':language_code'=>Yii::app()->language)) as $row_discount) {
						$option_groups[$row_option_group['id_options_group']]['options'][$row_option['id']]['discounts'][] = $row_discount;		
					}					
				}
			}			
										
			$array[$row['id']] = array(
				'id'=>$row['id'],
				'id_orders_discount'=>$row['id_orders_discount'],
				'id_orders_item_product'=>$row['id_orders_item_product'],
				'id_product'=>$row['id_product'],
				'id_product_variant'=>$row['id_product_variant'],
				'product_type' => $row['product_type'],
				'qty'=>$row['qty'],
				'name'=>$row['name'],
				'variant_name'=>$row['variant_name'],
				'sku'=>$row['sku'],
				'variant_sku'=>$row['variant_sku'],
				'price'=>$row['price'],
				'sell_price'=>$row['sell_price'],
				'subtotal'=>$row['subtotal'],
				'sub_products'=>$sub_products,
				'discounts'=>$discounts,
				'option_groups'=>$option_groups,
			);	
		}
		
		$order['products'] = $array;
		$order['taxes'] = array();
		
		foreach ($command_taxes->queryAll(true, array(':id'=>$id,':language_code'=>Yii::app()->language)) as $row) {
			$order['taxes'][] = $row;
		}
		
		$orders['comments'] = array();

		foreach ($command_comments->queryAll(true, array(':id'=>$id)) as $row) {
			$order['comments'][] = $row;
		}				
		
		// config
		$config_site=array();
		$sql='SELECT * FROM config';
		$command=$connection->createCommand($sql);
		
		foreach ($command->queryAll(true) as $key => $row) {
			$config_site[$row['name']] = $row['value'];	
		}
		
		if ($config_site['company_country_code']) {
			$criteria=new CDbCriteria; 
			$criteria->condition='country_code=:country_code AND language_code=:language_code'; 
			$criteria->params=array(':country_code'=>$config_site['company_country_code'],':language_code'=>Yii::app()->language); 		
			
			if ($country_description = Tbl_CountryDescription::model()->find($criteria)) {
				$config_site['country_name'] = $country_description->name;
			}					
		}
		
		if ($config_site['company_state_code']) {
			$criteria=new CDbCriteria; 
			$criteria->condition='state_code=:state_code AND language_code=:language_code'; 
			$criteria->params=array(':state_code'=>$config_site['company_state_code'],':language_code'=>Yii::app()->language); 		
			
			if ($state_description = Tbl_StateDescription::model()->find($criteria)) {
				$config_site['state_name'] = $state_description->name;
			}					
		}		
		
		
		// policy
		$sql = 'SELECT 
		cmspage_description.name,
		cmspage_description.description
		FROM cmspage
		INNER JOIN cmspage_description ON cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = :language_code
		WHERE cmspage.active = 1 AND cmspage.id = :id';
		$command=$connection->createCommand($sql);
		
		if ($row = $command->queryRow(true,array(':id'=>24,':language_code'=>Yii::app()->language))) {
			$config_site['policy'][] = array(
				'name'=>$row['name'],
				'description'=>$row['description'],
			);
		}
		
		if ($row = $command->queryRow(true,array(':id'=>25,':language_code'=>Yii::app()->language))) {
			$config_site['policy'][] = array(
				'name'=>$row['name'],
				'description'=>$row['description'],
			);
		}		
		
		$this->renderPartial('edit_info_print',array('id'=>$id,'order'=>$order,'config_site'=>$config_site));	
	}
	
	
	/**
	 * This is the action to get an XML list downloadable videos
	 */
	public function actionXml_list_downloadable_videos($id)
	{		
		$id = (int)$id;

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
		//create query 
		$sql = 'SELECT
		orders_item_product_downloadable_videos.id,
		orders_item_product_downloadable_videos_description.name
		FROM 
		orders_item_product_downloadable_videos 
		
		INNER JOIN
		orders_item_product_downloadable_videos_description
		ON
		(orders_item_product_downloadable_videos.id = orders_item_product_downloadable_videos_description.id_orders_item_product_downloadable_videos AND orders_item_product_downloadable_videos_description.language_code = :language_code)
		
		LEFT JOIN
		(orders_item_product CROSS JOIN orders_item CROSS JOIN orders)
		ON
		(orders_item_product_downloadable_videos.id_orders_item_product = orders_item_product.id AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
		
		LEFT JOIN
		(orders_item_product AS oip CROSS JOIN orders_item_product AS oip_parent CROSS JOIN orders_item AS oi CROSS JOIN orders AS o)
		ON
		(orders_item_product_downloadable_videos.id_orders_item_product = oip.id AND oip.id_orders_item_product = oip_parent.id AND oip_parent.id_orders_item = oi.id AND oi.id_orders = o.id)
		
		WHERE
		(orders_item.id IS NOT NULL AND orders_item.id_orders = :id)
		OR
		(oi.id IS NOT NULL AND oi.id_orders = :id)
		ORDER BY 
		orders_item_product_downloadable_videos.id ASC';		
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, array(':id'=>$id, ':language_code'=>Yii::app()->language)) as $row) {						
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>			
			</row>';
		}
		
		echo '</rows>';
	}		
	
	public function actionReset_downloadable_video_options($container, $id)
	{
		$id = (int)$id;

		$model = new OrdersDownloadableVideoForm;
		
		if ($id) {
			if ($o = Tbl_OrdersItemProductDownloadableVideos::model()->findByPk($id)) {
				$model->id = $o->id;				
				$model->no_days_expire = $o->no_days_expire;
				$model->no_downloads = $o->no_downloads;
				$model->current_no_downloads = $o->current_no_downloads;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		
		}
		

		$this->renderPartial('reset_downloadable_video_options',array('container'=>$container,'model'=>$model));	
	}		
	
	/**
	 * This is the action to save downloadable video
	 */
	public function actionSave_downloadable_video()
	{
		$model = new OrdersDownloadableVideoForm;
		
		// collect user input data
		if(isset($_POST['OrdersDownloadableVideoForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['OrdersDownloadableVideoForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			
			$output = array();
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);	
		}
	}		
	
	/**
	 * This is the action to get an XML list downloadable files
	 */
	public function actionXml_list_downloadable_files($id)
	{		
		$id = (int)$id;

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
		//create query 
		$sql = 'SELECT
		orders_item_product_downloadable_files.id,
		orders_item_product_downloadable_files_description.name
		FROM 
		orders_item_product_downloadable_files 
		
		INNER JOIN
		orders_item_product_downloadable_files_description
		ON
		(orders_item_product_downloadable_files.id = orders_item_product_downloadable_files_description.id_orders_item_product_downloadable_files AND orders_item_product_downloadable_files_description.language_code = :language_code)
		
		LEFT JOIN
		(orders_item_product CROSS JOIN orders_item CROSS JOIN orders)
		ON
		(orders_item_product_downloadable_files.id_orders_item_product = orders_item_product.id AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
		
		LEFT JOIN
		(orders_item_product AS oip CROSS JOIN orders_item_product AS oip_parent CROSS JOIN orders_item AS oi CROSS JOIN orders AS o)
		ON
		(orders_item_product_downloadable_files.id_orders_item_product = oip.id AND oip.id_orders_item_product = oip_parent.id AND oip_parent.id_orders_item = oi.id AND oi.id_orders = o.id)
		
		WHERE
		(orders_item.id IS NOT NULL AND orders_item.id_orders = :id)
		OR
		(oi.id IS NOT NULL AND oi.id_orders = :id)
		ORDER BY 
		orders_item_product_downloadable_files.id ASC';		
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, array(':id'=>$id, ':language_code'=>Yii::app()->language)) as $row) {						
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>			
			</row>';
		}
		
		echo '</rows>';
	}			
	
	public function actionReset_downloadable_file_options($container, $id)
	{
		$id = (int)$id;

		$model = new OrdersDownloadableFileForm;
		
		if ($id) {
			if ($o = Tbl_OrdersItemProductDownloadableFiles::model()->findByPk($id)) {
				$model->id = $o->id;				
				$model->no_days_expire = $o->no_days_expire;
				$model->no_downloads = $o->no_downloads;
				$model->current_no_downloads = $o->current_no_downloads;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		
		}
		

		$this->renderPartial('reset_downloadable_file_options',array('container'=>$container,'model'=>$model));	
	}			

	/**
	 * This is the action to save downloadable file
	 */
	public function actionSave_downloadable_file()
	{
		$model = new OrdersDownloadableFileForm;
		
		// collect user input data
		if(isset($_POST['OrdersDownloadableFileForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['OrdersDownloadableFileForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			
			$output = array();
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);	
		}
	}	
	
	/**
	 * This is the action to get an XML list of the product menu
	 */
	public function actionXml_list_section($id=0)
	{
		$id = (int)$id;
		
		if ($id) { 
			if (!$o = Tbl_Orders::model()->findByPk($id)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		
		}			
		
		$disabled = '';
		
		if (!$id) { 
			$disabled = '<disabled><![CDATA[1]]></disabled>';
		}
			
		//set content type and xml tag
		header("Content-type:text/xml");		
		
		// new order
		/*
		if (!$id || !$o->status) { 
			echo '<data>
				<item id="edit_customer_info">
					<Title><![CDATA[1. Customer Information]]></Title>
					<Description><![CDATA[Select or create a customer. Add Billing and Shipping information.]]></Description>
				</item>
				<item id="edit_cart">
					<Title><![CDATA[2. Products and Options]]></Title>
					<Description><![CDATA[Select products and options for this order.]]></Description>
					'.$disabled.'					
				</item>				
				<item id="edit_shipping">
					<Title><![CDATA[3. Shipping Method]]></Title>
					<Description><![CDATA[Select shipping method from available rates for this customer.]]></Description>
					'.$disabled.'
				</item>	
				<item id="edit_payment">
					<Title><![CDATA[4. Payment Information]]></Title>
					<Description><![CDATA[]]></Description>
					'.$disabled.'
				</item>		
			</data>';
		// edit order
		} else {*/
			echo '<data>
				<item id="edit_info">
					<Title><![CDATA['.Yii::t('controllers/OrdersController','LABEL_INFORMATION').']]></Title>
					<Description><![CDATA[]]></Description>
				</item>
				<item id="edit_shipments">
					<Title><![CDATA['.Yii::t('controllers/OrdersController','LABEL_SHIPMENTS').']]></Title>
					<Description><![CDATA[]]></Description>
				</item>			
			</data>';
		//}
	}	

		
	/**
	 * Filters
	 */
		
    public function filters()
    {
        return array(
            'accessControl',
        );
    }
	
	
	/**
	 * Access Rules
	 */
	
	/*
    public function accessRules()
    {
        return array(	
        );
    }*/
	
}