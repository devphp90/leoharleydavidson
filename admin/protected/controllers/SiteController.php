<?php

class SiteController extends Controller
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
		/**
		 * renders the view file 
		 * 'protected/themes/theme/views/site/index.php'
		 * 'protected/views/site/index.php'
		 */
		 				
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index');	
	
	}
	
	public function actionIs_logged_in()
	{
		echo Yii::app()->user->isGuest ? 'false':'true';
		exit;
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
				
	    	else
	        	$this->render('error', $error);
	    }
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		// auth_key
		if (isset($_GET['auth_key']) && isset($_GET['domain']) && ($auth_key = trim($_GET['auth_key'])) && ($domain = trim($_GET['domain']))) {		
			// check if domain is allowed
			if (Tbl_LinkedStore::model()->count('domain=:domain',array(':domain'=>$domain))) {
				// check if auth key exists
				if ($user = Tbl_User::model()->active()->find('auth_key=:auth_key',array(':auth_key'=>$auth_key))) {
					$identity = new UserIdentity('','');
					$identity->setId($user->id);
					$identity->username=$user->username;
					Yii::app()->user->setState('fullname',$user->firstname.' '.$user->lastname);
					Yii::app()->user->setState('gender',$user->gender);			
					$user->lastlogin = date('Y-m-d H:i:s');
					$user->save();						
					
					$identity->setState('_lang',$_GET['_lang'] ? $_GET['_lang']:Yii::app()->language);
					Yii::app()->user->login($identity);
				}
			}
		}
		
		// if logged in
		if (!Yii::app()->user->isGuest) { $this->redirect('index'); }
		
		$model=new LoginForm;
		
		// collect user input data
		if(isset($_POST['LoginForm']))
		{				
			// loop through each attribute and set it in our model
			foreach($_POST['LoginForm'] as $name=>$value)
			{
				$model->$name=$value;
				
			}
			
			
			// validate user input and redirect to the previous page if valid
			if(!$model->validate() || !$model->login()) {	
				// display the login form
				$model->password='';
				$this->renderPartial('login_form',array('model'=>$model));		
			} else {
				// empty expired carts
				$this->empty_expired_carts();	
			}
		} else {			
			// display the login form
			$this->render('login',array('model'=>$model));
		}
	}
	
	/**
	 * Validate if auth_key exists
	 */
	public function actionCheck_auth_key($auth_key, $domain)
	{
		// check if domain is allowed
		if (Tbl_LinkedStore::model()->count('domain=:domain',array(':domain'=>$domain)) && Tbl_User::model()->active()->count('auth_key=:auth_key',array(':auth_key'=>$auth_key))) echo 'true';
		else echo 'false';

		exit;
	}
	
	/**
	 * Validate if the search orders exist
	 */
	public function actionSearch_orders_validate()	
	{
		$id = (int)$_POST['id'];

		if ($id) { 
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 		
			
			if (!Tbl_Orders::model()->count($criteria)) {
				echo false;
				exit;
			}else{
				echo true;	
			}
		}else{
			echo false;
			exit;
		}
	}
	
	/**
	 * Validate if the search customers exist
	 */
	public function actionSearch_customers_validate()	
	{
		$id = (int)$_POST['id'];

		if ($id) { 
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 		
			
			if (!Tbl_Customer::model()->count($criteria)) {
				echo false;
				exit;
			}else{
				echo true;	
			}
		}else{
			echo false;
			exit;
		}
	}
	
	
	public function actionXml_list_product_out_of_stock($posStart=0, $count=20, array $filters=array(), array $sort_col=array())
	{						
			
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$params=array(':language_code'=>Yii::app()->language);	
		
		//create query 
		
		$sql = 'SELECT 
		id, 
		id_product_variant,
		sku,
		name,
		variant,
		qty
		FROM 
		(
			(
				SELECT 
				product.id,
				product_variant.id AS id_product_variant,
				product.sku,
				product_description.name,
				NULL AS variant,
				product.qty
				FROM 
				product
				INNER JOIN
				product_description
				ON
				(product.id = product_description.id_product AND product_description.language_code = :language_code)
				LEFT JOIN
				product_variant
				ON
				(product.id = product_variant.id_product)
				
				WHERE
				product.active=1
				AND
				product.product_type=0 
				AND 
				product_variant.id IS NULL
				AND 
				product.track_inventory = 1 
				AND 
				(product.in_stock = 0 OR (product.out_of_stock>0 AND product.qty<=product.out_of_stock))
			)
			UNION
			(
				SELECT 
				product.id,
				product_variant.id AS id_product_variant,
				product_variant.sku,
				product_description.name,
				GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
				product_variant.qty
				FROM 
				product
				INNER JOIN
				product_description
				ON
				(product.id = product_description.id_product AND product_description.language_code = :language_code)
				INNER JOIN 
				(
					product_variant 
					CROSS JOIN 
					product_variant_option 
					CROSS JOIN 
					product_variant_group 
					CROSS JOIN 
					product_variant_group_option 
					CROSS JOIN 
					product_variant_group_option_description
				)
				ON 
				(
					product.id = product_variant.id_product 
					AND 
					product_variant.id = product_variant_option.id_product_variant 
					AND 
					product_variant_option.id_product_variant_group = product_variant_group.id 
					AND 
					product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
					AND 
					product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
					AND 
					product_variant_group_option_description.language_code = :language_code
				)
				
				WHERE
				product.product_type=0 		
				AND
				product.active=1
				AND
				product_variant.active=1
				AND 
				product.track_inventory = 1 
				AND 
				(product_variant.in_stock = 0 OR (product.out_of_stock>0 AND product_variant.qty<=product.out_of_stock))									
				GROUP BY 
				product.id,
				product_variant.id
			)
		) AS t
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
			
		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		//echo $sql . " id_product_bundled_product_group : " . $id_product_bundled_product_group . " id_product : " . $id . " name : " . $filters['name'];
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].':'.$row['id_product_variant'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['variant'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>	
			<cell type="ro"><![CDATA['.$row['qty'].']]></cell>				
			</row>';
		}
		
		echo '</rows>';

	}
	
	public function actionXml_list_product_low_inventory($posStart=0, $count=20, array $filters=array(), array $sort_col=array())
	{						
			
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$params=array(':language_code'=>Yii::app()->language);	
							
		//create query 
		
		$sql = 'SELECT 
		id, 
		id_product_variant,
		sku,
		name,
		variant,
		qty
		FROM 
		(
			(
				SELECT 
				product.id,
				product_variant.id AS id_product_variant,
				product.sku,
				product_description.name,
				NULL AS variant,
				product.qty
				FROM 
				product
				INNER JOIN
				product_description
				ON
				(product.id = product_description.id_product AND product_description.language_code = :language_code)
				LEFT JOIN
				product_variant
				ON
				(product.id = product_variant.id_product)
				
				WHERE
				product.active=1
				AND
				product.product_type=0 
				AND 
				product_variant.id IS NULL
				AND 
				product.track_inventory = 1 
				AND 
				(product.notify = 1 AND (product.qty<=product.notify_qty))
			)
			UNION
			(
				SELECT 
				product.id,
				product_variant.id AS id_product_variant,
				product_variant.sku,
				product_description.name,
				GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
				product_variant.qty
				FROM 
				product
				INNER JOIN
				product_description
				ON
				(product.id = product_description.id_product AND product_description.language_code = :language_code)
				INNER JOIN 
				(
					product_variant 
					CROSS JOIN 
					product_variant_option 
					CROSS JOIN 
					product_variant_group 
					CROSS JOIN 
					product_variant_group_option 
					CROSS JOIN 
					product_variant_group_option_description
				)
				ON 
				(
					product.id = product_variant.id_product 
					AND 
					product_variant.id = product_variant_option.id_product_variant 
					AND 
					product_variant_option.id_product_variant_group = product_variant_group.id 
					AND 
					product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
					AND 
					product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
					AND 
					product_variant_group_option_description.language_code = :language_code
				)
				
				WHERE
				product.product_type=0 		
				AND
				product.active=1
				AND
				product_variant.active=1
				AND 
				product.track_inventory = 1 
				AND 
				product_variant.qty<=product_variant.notify_qty										
				GROUP BY 
				product.id,
				product_variant.id
			)
		) AS t
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
			
		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		//echo $sql . " id_product_bundled_product_group : " . $id_product_bundled_product_group . " id_product : " . $id . " name : " . $filters['name'];
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].':'.$row['id_product_variant'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['variant'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>	
			<cell type="ro"><![CDATA['.$row['qty'].']]></cell>				
			</row>';
		}
		
		echo '</rows>';

	}
	
	public function actionXml_list_products_best_selling($posStart=0, $count=20, array $filters=array(), array $sort_col=array())
	{						
			
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$params=array(':language_code'=>Yii::app()->language);	

		//create query 
		
		$sql = 'SELECT 
		orders_item_product.id_product AS id,
		orders_item_product.id_product_variant,
		IF(orders_item_product.id_product_variant != 0,orders_item_product.variant_sku,orders_item_product.sku) AS sku,
		orders_item_product_description.name,
		orders_item_product_description.variant_name AS variant,
		SUM(orders_item_product.qty) AS qty
		FROM 
		orders_item
		INNER JOIN
		(orders_item_product CROSS JOIN orders_item_product_description)
		ON
		(orders_item.id = orders_item_product.id_orders_item AND orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = :language_code)
		GROUP BY 
		orders_item_product.id_product,
		orders_item_product.id_product_variant
		ORDER BY 
		qty DESC';
			
		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		//echo $sql . " id_product_bundled_product_group : " . $id_product_bundled_product_group . " id_product : " . $id . " name : " . $filters['name'];
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].':'.$row['id_product_variant'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['variant'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>	
			<cell type="ro"><![CDATA['.$row['qty'].']]></cell>				
			</row>';
		}
		
		echo '</rows>';

	}
	
	public function actionXml_list_orders($posStart=0, $count=30, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':language_code'=>Yii::app()->language);
		
		//create query 
		$sql = "SELECT 
		orders.id,
		orders.date_order,		
		orders.grand_total,
		orders.status,
		orders.priority
		FROM 
		orders				
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').
		'ORDER BY orders.id DESC';			
		
		//add limits to query to get only rows necessary for the output
		//$sql.= " LIMIT ".$posStart.",".$count;
		$sql.= " LIMIT ".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			switch ($row['priority']) {
				case 0:
					$font_style = "";
					$priority = Yii::t('controllers/OrdersController','LABEL_PRIORITY_NORMAL');
					break;
				case 1:
					$font_style = "color:#E839D7;";
					$priority =  '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_ATTENTION').'</span>';
					break;
				case 2:	
					$font_style = "color:#F00;font-weight:bold";
					$priority =  '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_URGENT').'</span>';
					break;					
			}
			
			switch ($row['status']) {
				case -1:
					$font_style = empty($font_style) ? "color:#A8A8A8;" : $font_style;
					$status = '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_STATUS_CANCELLED').'</span>';
					break;
				case 0:
					$font_style = empty($font_style) ? "" : $font_style;
					$status = '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_STATUS_INCOMPLETE').'</span>';
					break;					
				case 1:
					$font_style = empty($font_style) ? "font-weight:bold" : $font_style;
					$status = '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_STATUS_PENDING').'</span>';
					break;
				case 2:
					$font_style = empty($font_style) ? "" : $font_style;
					$status = '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_STATUS_PAYMENT_REVIEW').'</span>';
					break;
				case 3:
					$font_style = empty($font_style) ? "" : $font_style;
					$status = '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_STATUS_SUSPECTED_FRAUD').'</span>';
					break;
				case 4:
					$font_style = empty($font_style) ? "" : $font_style;
					$status = '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_STATUS_DECLINED').'</span>';
					break;
				case 5:
					$font_style = empty($font_style) ? "" : $font_style;
					$status = '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_STATUS_PROCESSING').'</span>';
					break;
				case 6:
					$font_style = empty($font_style) ? "" : $font_style;
					$status = '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_STATUS_ON_HOLD').'</span>';
					break;
				case 7:
					$font_style = "color:#090;";
					$status = '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_STATUS_COMPLETED').'</span>';
					break;
			}
			switch ($row['priority']) {
				case 0:
					
					$priority = '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_NORMAL').'</span>';
					break;
				case 1:
					$font_style = "color:#E839D7;";
					$priority =  '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_ATTENTION').'</span>';
					break;
				case 2:	
					$font_style = $row['status']!=7 ? "color:#F00;font-weight:bold;" : $font_style;
					$priority =  '<span style="'.$font_style.'">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_URGENT').'</span>';
					break;					
			}
			
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.$row['id'].'</span>]]></cell>
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.$row['date_order'].'</span>]]></cell>	
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.Html::nf($row['grand_total']).'</span>]]></cell>
			<cell type="ro"><![CDATA['.$status.']]></cell>
			<cell type="ro"><![CDATA['.$priority.']]></cell>			
			</row>';
		}
		
		echo '</rows>';
	}
	
	public function actionXml_list_orders_comments($posStart=0, $count=30, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':language_code'=>Yii::app()->language);

		//create query 
		$sql = "SELECT 
		orders.id,
		orders_comment.date_created,
		orders_comment.comments
		FROM 
		orders
		INNER JOIN
		orders_comment
		ON
		orders.id = orders_comment.id_orders AND orders_comment.read_comment = 0 AND orders_comment.id_user_created = 0					
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').
		'ORDER BY orders_comment.date_created ASC';			
		
		//add limits to query to get only rows necessary for the output
		//$sql.= " LIMIT ".$posStart.",".$count;
		$sql.= " LIMIT ".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.$row['id'].'</span>]]></cell>
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.$row['date_created'].'</span>]]></cell>	
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.$row['comments'].'</span>]]></cell>		
			</row>';
		}
		
		echo '</rows>';
	}
	

	public function actionXml_list_biggest_spenders($posStart=0, $count=20, array $filters=array(), array $sort_col=array())
	{						
			
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$params=array();	
							
		//create query 
		
		$sql = 'SELECT 
		orders.id_customer,
		customer.firstname,
		customer.lastname,
		SUM(orders.total) AS total
		FROM 
		orders
		INNER JOIN
		customer
		ON
		(orders.id_customer = customer.id)
		WHERE orders.status = 7
		GROUP BY 
		orders.id_customer 
		ORDER BY 
		total DESC';
			
		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		//echo $sql . " id_product_bundled_product_group : " . $id_product_bundled_product_group . " id_product : " . $id . " name : " . $filters['name'];
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id_customer'].'">
			<cell type="ro"><![CDATA['.$row['firstname'].' '.$row['lastname'].']]></cell>
			<cell type="ro"><![CDATA['.Html::nf($row['total']).']]></cell>
			</row>';
		}
		
		echo '</rows>';

	}	
	
	
	/**
	 * This is the action to get a list of invoice for the dhtmlxCombo in every page
	 */ 
	public function actionXml_list_invoice($pos=0,$mask='')
	{
		$pos = (int)$pos;

		header ("content-type: text/xml");
	
		echo '<?xml version="1.0" encoding="utf-8"?>';
		$criteria=new CDbCriteria; 
		$criteria->select='id';
		$criteria->condition='id LIKE CONCAT("%",:id)'; 
		$criteria->params=array(':id'=>$mask);
		
		echo '<complete>';
		
		foreach (Tbl_Orders::model()->findAll($criteria) as $row) {
			echo '<option value="'.$row->id.'">'.$row->id.'</option>';
		}
		
		echo '</complete>';

	}
	
	/**
	 * This is the action to get a list of invoice for the dhtmlxCombo in every page
	 */ 
	public function actionXml_list_customer($pos=0,$mask='')
	{
		$pos = (int)$pos;

		header ("content-type: text/xml");
	
		echo '<?xml version="1.0" encoding="utf-8"?>';
		$criteria=new CDbCriteria; 
		$criteria->select='id, lastname, firstname';
		$criteria->order='lastname ASC, firstname ASC';
		$criteria->condition='CONCAT(firstname," ",lastname) LIKE CONCAT("%",:name,"%")'; 
		$criteria->params=array(':name'=>$mask);
		$criteria->order='IF(CONCAT(firstname," ",lastname) LIKE CONCAT(:name,"%"),0,1) ASC, CONCAT(firstname," ",lastname) ASC';
		
		echo '<complete>';
		
		foreach (Tbl_Customer::model()->findAll($criteria) as $row) {
			echo '<option value="'.$row->id.'">'.$row->firstname.' '.$row->lastname.'</option>';
		}
		
		echo '</complete>';

	}
	
	/**
	 * This is the function to empty all expired carts when admin logs in
	 */
	public function empty_expired_carts()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$current_datetime = date('Y-m-d H:i:s');
		$tmp_uploads_dir = Yii::app()->params['root_url'].'tmp_uploads/';
		
		// get tmp uploaded files to delete
		$sql='SELECT 
		cart_item_option.filename_tmp
		FROM
		cart
		INNER JOIN		
		(cart_item CROSS JOIN cart_item_option)
		ON
		(cart.id = cart_item.id_cart AND cart_item.id = cart_item_option.id_cart_item)
		WHERE
		cart.date_expired <= :date
		AND
		cart_item_option.filename_tmp != ""';
		$command=$connection->createCommand($sql);
		
		foreach ($command->queryAll(true, array(':date'=>$current_datetime)) as $row) {			
			if (is_file($tmp_uploads_dir.$row['filename_tmp'])) unlink($tmp_uploads_dir.$row['filename_tmp']);
		}		
				
		$sql='DELETE FROM
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
		cart.date_expired <= :date';
		$command=$connection->createCommand($sql);
		
		$command->execute(array(':date'=>$current_datetime));
	}
	
	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex2()
	{
		/**
		 * renders the view file 
		 * 'protected/themes/theme/views/site/index.php'
		 * 'protected/views/site/index.php'
		 */
		 				
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index2');	
	
	}	
	
	public function actionGet_revenue_stats()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$current_datetime = date('Y-m-d');
		
		$revenue_option = (int)$_POST['revenue_option'];
		$revenue_start_date = trim($_POST['revenue_start_date']);
		$revenue_end_date = trim($_POST['revenue_end_date']);
				
		require_once '/var/www/html/includes/php/gapi.class.php';
		
		$ga = new gapi(Yii::app()->params['google_analytics_email'],Yii::app()->params['google_analytics_password']);
		
		// ecommerce - products
		//$ga->requestReportData(59802615,array('productSku','productName','productCategory'),array('itemQuantity','itemRevenue'));
		
		switch ($revenue_option) {
			// This week
			case 0:
				$revenue_start_date = date('Y-m-d',strtotime('last Sunday'));;
				$revenue_end_date = $current_datetime;				
				break;
			// This month
			case 1:
				$revenue_start_date = date('Y-m-').'01';
				$revenue_end_date = date('Y-m-d',strtotime('-1 day',strtotime('+1 month',strtotime($revenue_start_date))));			
				break;
			// Last 3 months
			case 2:
				$revenue_start_date = date('Y-m-d',strtotime('-3 month',strtotime($current_datetime)));						
				$revenue_end_date = $current_datetime;				
				break;
			// Last 6 months
			case 3:
				$revenue_start_date = date('Y-m-d',strtotime('-6 month',strtotime($current_datetime)));									
				$revenue_end_date = $current_datetime;				
				break;
			// This year
			case 4:
				$revenue_start_date = date('Y-').'01-01';	
				$revenue_end_date = $current_datetime;				
				break;
			// Last 5 years
			case 5:
				$revenue_start_date = date('Y-m-d',strtotime('-5 year',strtotime($current_datetime)));
				$revenue_end_date = $current_datetime;			
				break;
			// Last 10 years
			case 6:
				$revenue_start_date = date('Y-m-d',strtotime('-10 year',strtotime($current_datetime)));
				$revenue_end_date = $current_datetime;						
				break;
			// Custom
			case 7:
				if ($revenue_start_date && $revenue_end_date && strtotime($revenue_end_date) < strtotime($revenue_start_date)) {
					echo 'error in dates';
					exit;
				} else if (!$revenue_start_date || !$revenue_end_date) { exit; }
				break;
		}
				
		// visits
		try {		
			$ga->requestReportData(Yii::app()->params['google_analytics_profile_id'],array('date','customVarName1','customVarValue1'),array('visitors','newVisits'),array('date'),NULL,$revenue_start_date,$revenue_end_date,1,31);
			//'ga:customVarName1 == "LANGUAGE" && ga:customVarValue1 == "fr"'
			//echo '<pre>'.print_r($ga->getResults(),1).'</pre>';

		} catch (Exception $e) {
			echo $e->getMessage();
			exit;
		}
		
		//echo '<pre>'.print_r($ga->getResults(),1).'</pre>';  
		$results = array();			
		
		
		$total_visits = $ga->getVisitors();
		$total_newvisits = $ga->getnewVisits();
		$total_sales = 0;
		$total_revenues = 0;
		$total_profits = 0;
		
		// get total orders
		$sql='SELECT 
		COUNT(orders.id) AS total_sales,
		SUM(orders.grand_total) AS total_revenues
		FROM
		orders 
		
		WHERE 
		orders.status NOT IN (-1, 0, 4)
		AND
		orders.date_order BETWEEN CONCAT(:start_date," 00:00:00") AND CONCAT(:end_date," 23:59:59") ';
		$command=$connection->createCommand($sql);					
		
		$row_order = $command->queryRow(true,array(':start_date'=>$revenue_start_date ? $revenue_start_date:'0000-00-00',':end_date'=>$revenue_end_date));		
		
		$total_sales = $row_order['total_sales'];
		$total_revenues = $row_order['total_revenues'];

		// get total profits
		$sql='SELECT 
		SUM(orders_item_product.cost_price) AS total_cost_products,
		SUM(IF(orders_item_option.id IS NOT NULL,orders_item_option.cost_price,0)) AS total_cost_options			
		FROM
		orders 
		INNER JOIN
		(orders_item CROSS JOIN orders_item_product)
		ON
		(orders.id = orders_item.id_orders AND orders_item.id = orders_item_product.id_orders_item)
		LEFT JOIN 
		orders_item_option
		ON
		(orders_item.id = orders_item_option.id_orders_item)
		
		WHERE 
		orders.status NOT IN (-1, 0, 4)
		AND
		orders.date_order BETWEEN CONCAT(:start_date," 00:00:00") AND CONCAT(:end_date," 23:59:59") ';
		$command=$connection->createCommand($sql);					
		
		$row_order = $command->queryRow(true,array(':start_date'=>$revenue_start_date ? $revenue_start_date:'0000-00-00',':end_date'=>$revenue_end_date));		
		
		$total_profits = $total_revenues-($row_order['total_cost_products']+$row_order['total_cost_options']);		
		
		// get total customers
		$sql='SELECT 
		COUNT(customer.id) AS total
		FROM
		customer 
		
		WHERE 
		customer.active = 1';
		$command=$connection->createCommand($sql);					
		
		$row_customer = $command->queryRow(true);		
		
		$avg_revenue_customer = $total_revenues/$row_customer['total'];
		
		echo '<table border="0" cellpadding="5" cellspacing="0" width="100%">
		<tr>
			<td valign="top"><strong style="font-size:18px;">Total Visits</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'total-visits').'</td>
			<td align="center" valign="top" style="font-size:18px;">'.$total_visits.'</td>
		</tr>
		<tr>
			<td valign="top"><strong style="font-size:18px;">Total New Visitors</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'total-new-visits').'</td>
			<td align="center" valign="top" style="font-size:18px;">'.$total_newvisits.'</td>
		</tr>		
		<tr>
			<td valign="top"><strong style="font-size:18px;">Total Sales</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'total-sales').'</td>
			<td align="center" valign="top" style="font-size:18px;"><a href="javascript:void(0);" class="sales_information">'.$total_sales.'</a></td>
		</tr>
		<tr>
			<td valign="top"><strong style="font-size:18px;">Total Revenues</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'total-revenues').'</td>
			<td align="right" valign="top" style="font-size:18px;"><a href="javascript:void(0);" class="sales_information">'.Html::nf($total_revenues).'</a></td>
		</tr>
		<tr>
			<td valign="top"><strong style="font-size:18px;">Total Profits</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'total-profits').'</td>
			<td align="right" valign="top" style="font-size:18px; '.($total_profits < 0 ? 'color: #F00;':($total_profits > 0 ? 'color: #090;':'')).'"><a href="javascript:void(0);" class="sales_information">'.Html::nf($total_profits).'</a></td>
		</tr>
		<tr>
			<td valign="top"><strong style="font-size:18px;">Avg. Revenue Per Customer</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'avg-revenue-per-customer').'</td>
			<td align="right" valign="top" style="font-size:18px;">'.Html::nf($avg_revenue_customer).'</td>
		</tr>		
		<tr>
			<td valign="top"><strong style="font-size:18px;">Conversion Rate</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'conversion-rate').'</td>
			<td align="center" valign="top" style="font-size:18px;">'.($total_visits ? round(($total_sales/$total_visits)*100):0).'%</td>
		</tr>
		</table>';
	}
	
	public function actionXml_list_products_low_inventory($posStart=0, $count=20, array $filters=array(), array $sort_col=array())
	{						
			
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$params=array(':language_code'=>Yii::app()->language);									
		
		//create query 
		
		$sql = 'SELECT 
		id, 
		id_product_variant,
		sku,
		name,
		variant,
		qty,
		sort_order
		FROM 
		(
			(
				SELECT 
				product.id,
				0 AS id_product_variant,
				product.sku,
				product_description.name,
				NULL AS variant,
				product.qty,
				0 AS sort_order
				FROM 
				product
				INNER JOIN
				product_description
				ON
				(product.id = product_description.id_product AND product_description.language_code = :language_code)
				
				WHERE
				product.active=1
				AND
				product.product_type=0 
				AND
				product.has_variants = 0
				AND 
				product.track_inventory = 1 				
				AND 
				(
					(product.in_stock = 1 AND product.notify = 1 AND product.notify_qty > 0 AND product.qty <= product.notify_qty)
					OR
					(product.in_stock = 0 OR (product.in_stock = 1 AND product.qty<=product.out_of_stock))
				)
			)
			UNION
			(
				SELECT 
				product.id,
				product_variant.id AS id_product_variant,
				product_variant.sku,
				product_description.name,
				GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
				product_variant.qty,
				product_variant.sort_order
				FROM 
				product
				INNER JOIN
				product_description
				ON
				(product.id = product_description.id_product AND product_description.language_code = :language_code)
				INNER JOIN 
				(
					product_variant 
					CROSS JOIN 
					product_variant_option 
					CROSS JOIN 
					product_variant_group 
					CROSS JOIN 
					product_variant_group_option 
					CROSS JOIN 
					product_variant_group_option_description
				)
				ON 
				(
					product.id = product_variant.id_product 
					AND 
					product_variant.id = product_variant_option.id_product_variant 
					AND 
					product_variant_option.id_product_variant_group = product_variant_group.id 
					AND 
					product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
					AND 
					product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
					AND 
					product_variant_group_option_description.language_code = :language_code
				)
				
				WHERE
				product.product_type=0 		
				AND
				product.active=1
				AND
				product_variant.active=1
				AND 
				product.track_inventory = 1 
				AND 
				(
					(product.in_stock = 1 AND product.notify = 1 AND product_variant.notify_qty > 0 AND product_variant.qty <= product_variant.notify_qty)
					OR
					(product.in_stock = 0 OR product_variant.in_stock = 0 OR (product.in_stock = 1 AND product_variant.in_stock = 1 AND product_variant.qty<=product.out_of_stock))
				)									
				GROUP BY 
				product.id,
				product_variant.id
			)
		) AS t
		ORDER BY 
		qty ASC,
		sort_order ASC';
			
		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		//echo $sql . " id_product_bundled_product_group : " . $id_product_bundled_product_group . " id_product : " . $id . " name : " . $filters['name'];
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			$id = $row['id'].':'.($row['id_product_variant'] ? $row['id_product_variant']:0);
			
			echo '<row id="'.$row['id'].':'.($row['id_product_variant'] ? $row['id_product_variant']:0).'">
			<cell type="ro"><![CDATA['.$row['name'].($row['variant'] ? ' ('.$row['variant'].')':'').']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>	
			<cell type="ro"><![CDATA['.$row['qty'].']]></cell>
			<cell type="ro"><![CDATA[<input type="text" name="qty['.$id.']" id="'.$id.'" value="'.$row['qty'].'" size="5" style="text-align:center;" />&nbsp;<input type="button" value="Apply" onclick="javascript:apply_product_qty(\''.$id.'\');" />]]></cell>
			</row>';
		}
		
		echo '</rows>';

	}	
	
	public function actionApply_product_qty($qty, $id_product, $id_product_variant=0)
	{		
		if ($id_product_variant) $row = Tbl_ProductVariant::model()->findByPk($id_product_variant);		
		else $row = Tbl_Product::model()->findByPk($id_product);
		
		$row->qty = $qty;
		$row->update();
	}
	
	public function actionGet_products_low_inventory_count()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
								
		$sql = 'SELECT 
		COUNT(id) AS total
		FROM 
		(
			(
				SELECT 
				product.id,
				0 AS id_product_variant
				FROM 
				product
				
				WHERE
				product.active=1
				AND
				product.product_type=0 
				AND
				product.has_variants = 0
				AND 
				product.track_inventory = 1 				
				AND 
				(
					(product.in_stock = 1 AND product.notify = 1 AND product.notify_qty > 0 AND product.qty <= product.notify_qty)
					OR
					(product.in_stock = 0 OR (product.in_stock = 1 AND product.qty<=product.out_of_stock))
				)
			)
			UNION
			(
				SELECT 
				product.id,
				product_variant.id AS id_product_variant
				FROM 
				product
				INNER JOIN 
				product_variant 
				ON 
				(product.id = product_variant.id_product)
				
				WHERE
				product.product_type=0 		
				AND
				product.active=1
				AND
				product_variant.active=1
				AND 
				product.track_inventory = 1 
				AND 
				(
					(product.in_stock = 1 AND product.notify = 1 AND product_variant.notify_qty > 0 AND product_variant.qty <= product_variant.notify_qty)
					OR
					(product.in_stock = 0 OR product_variant.in_stock = 0 OR (product.in_stock = 1 AND product_variant.in_stock = 1 AND product_variant.qty<=product.out_of_stock))
				)									
				GROUP BY 
				product.id,
				product_variant.id
			)
		) AS t';
		/* Select queries return a resultset */
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true);

		echo $row['total'] ? $row['total']:0;
		exit;
	}
	
	public function actionXml_list_options_low_inventory($posStart=0, $count=20, array $filters=array(), array $sort_col=array())
	{						
			
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$params=array(':language_code'=>Yii::app()->language);	
								
		$sql = 'SELECT 
		COUNT(options.id) AS total
		FROM 
		options
		WHERE
		options.active=1
		AND 
		options.track_inventory = 1 				
		AND 
		(
			(options.in_stock = 1 AND options.notify = 1 AND options.notify_qty > 0 AND options.qty <= options.notify_qty)
			OR
			(options.in_stock = 0 OR (options.in_stock = 1 AND options.qty<=options.out_of_stock))
		)';
		
			
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true);
			$totalCount = $row['total'];		
		}
		
		
		//create query 
		
		$sql = 'SELECT 
		options.id, 
		options.sku,
		CONCAT(options_group_description.name," - ",options_description.name) AS name,
		options.qty
		FROM 
		options		
		INNER JOIN
		options_description
		ON
		(options.id = options_description.id_options AND options_description.language_code = :language_code)		
		
		INNER JOIN
		options_group_description
		ON
		(options.id_options_group = options_group_description.id_options_group AND options_group_description.language_code = options_description.language_code)
		
		WHERE
		options.active=1
		AND 
		options.track_inventory = 1 				
		AND 
		(
			(options.in_stock = 1 AND options.notify = 1 AND options.notify_qty > 0 AND options.qty <= options.notify_qty)
			OR
			(options.in_stock = 0 OR (options.in_stock = 1 AND options.qty<=options.out_of_stock))
		)
		ORDER BY 
		qty ASC,
		name ASC';
			
		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		//echo $sql . " id_product_bundled_product_group : " . $id_product_bundled_product_group . " id_product : " . $id . " name : " . $filters['name'];
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {			
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>	
			<cell type="ro"><![CDATA['.$row['qty'].']]></cell>
			<cell type="ro"><![CDATA[<input type="text" name="qty['.$row['id'].']" id="'.$row['id'].'" value="'.$row['qty'].'" size="5" style="text-align:center;" />&nbsp;<input type="button" value="Apply" onclick="javascript:apply_option_qty(\''.$row['id'].'\');" />]]></cell>
			</row>';
		}
		
		echo '</rows>';

	}	
	
	public function actionApply_option_qty($qty, $id_options)
	{		
		$row = Tbl_Options::model()->findByPk($id_options);		
		$row->qty = $qty;
		$row->update();
	}	
	
	public function actionGet_options_low_inventory_count()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
								
		$sql = 'SELECT 
		COUNT(options.id) AS total
		FROM 
		options
		WHERE
		options.active=1
		AND 
		options.track_inventory = 1 				
		AND 
		(
			(options.in_stock = 1 AND options.notify = 1 AND options.notify_qty > 0 AND options.qty <= options.notify_qty)
			OR
			(options.in_stock = 0 OR (options.in_stock = 1 AND options.qty<=options.out_of_stock))
		)';
		/* Select queries return a resultset */
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true);

		echo $row['total'] ? $row['total']:0;
		exit;
	}		
	
	public function actionXml_list_orders_unread_comments($posStart=0, $count=30, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;														
		
		//create query 
		$sql = "SELECT 
		orders.id,
		orders_comment.id AS id_orders_comment,
		orders_comment.date_created,
		orders_comment.comments,
		CONCAT(customer.firstname,' ',customer.lastname) AS name
		FROM 
		orders
		INNER JOIN
		orders_comment
		ON
		(orders.id = orders_comment.id_orders AND orders_comment.read_comment = 0 AND orders_comment.id_user_created = 0)
		INNER JOIN 
		customer
		ON
		(orders.id_customer = customer.id)
		ORDER BY 
		date_created DESC,
		name ASC ";			
		
		//add limits to query to get only rows necessary for the output
		//$sql.= " LIMIT ".$posStart.",".$count;
		$sql.= " LIMIT ".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true) as $row) {
			echo '<row id="'.$row['id_orders_comment'].'">
			<cell type="ch"/>
			<cell type="ro"><![CDATA['.$row['date_created'].']]></cell>	
			<cell type="ro"><![CDATA['.$row['id'].']]></cell>		
			<cell type="ro"><![CDATA['.$row['name'].'<p>'.$row['comments'].'</p>]]></cell>		
			<cell type="ro"><![CDATA[<input type="button" value="Reply" onclick="javascript:add_reply_orders_comments('.$row['id_orders_comment'].','.$row['id'].');" />]]></cell>					
			</row>';
		}
		
		echo '</rows>';
	}		
	
	public function actionGet_order_comments($id_orders_comment)
	{
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		// get current comment
		$sql = 'SELECT 
		orders_comment.id_orders,
		CONCAT(customer.firstname," ",customer.lastname) AS name,
		orders_comment.date_created,
		orders_comment.comments
		FROM 
		orders_comment 
		INNER JOIN
		(orders CROSS JOIN customer)
		ON
		(orders_comment.id_orders = orders.id AND orders.id_customer = customer.id)		
		WHERE
		orders_comment.id = :id
		LIMIT 1';
		
		$command_comments=$connection->createCommand($sql);
		
		$row_comment = $command_comments->queryRow(true, array(':id'=>$id_orders_comment));
		
		$output = array();
		
		$output['current_comment'] = '<div style="padding-bottom:10px;">
			<div><strong>'.Yii::t('views/orders/edit_info_options','LABEL_FROM').':</strong> '.$row_comment['name'].' <strong>'.Yii::t('views/orders/edit_info_options','LABEL_DATE').':</strong> '.$row_comment['date_created'].'</div>
			<div style="margin-top:5px; float: left;">'.nl2br($row_comment['comments']).'</div>
			
			<div style="clear:both"></div>
			</div>';
		
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
		
		foreach ($command_comments->queryAll(true, array(':id'=>$row_comment['id_orders'])) as $row) {
			$output['comments'] .= '<div style="padding:10px; background-color:'.($row['id'] == $id_orders_comment?'#EBEBEB':'#FFF;').'" '.($row['id'] == $id_orders_comment ? 'class="current_comment"':'').'>
			<div><strong>'.Yii::t('views/orders/edit_info_options','LABEL_FROM').':</strong> '.$row['name'].' <strong>'.Yii::t('views/orders/edit_info_options','LABEL_DATE').':</strong> '.$row['date_created'].($row['hidden_from_customer'] ? ' (<span style="color:#F00;">'.Yii::t('views/orders/edit_info_options','LABEL_HIDDEN_FROM_CUSTOMER').'</span>)':'').'</div>
			<div style="margin-top:5px; float: left;">'.nl2br($row['comments']).'</div>			
			<div style="clear:both"></div>
			</div>';
		}			
		
		echo json_encode($output);			
	}
	
	public function actionAdd_comment($id,$id_orders_comment)
	{
		$id = (int)$id;
		$id_orders_comment = (int)$id_orders_comment;
		$comment = htmlspecialchars(trim($_POST['comments']));
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
			orders_comment.date_created = :date_created';
			
			$command=$connection->createCommand($sql);
			$command->execute(array(':id'=>$id,':id_user_created'=>$app->user->id,':comment'=>$comment,':date_created'=>$current_datetime));
			
			// mark as read
			Tbl_OrdersComment::model()->updateAll(array('read_comment'=>1),'id=:id',array(':id'=>$id_orders_comment));
			
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
		exit;
	}	
	
	
	/**
	 * This is the action to mark comments as read
	 */
	public function actionMark_comment_as_read()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id) {
				Tbl_OrdersComment::model()->updateAll(array('read_comment'=>1),'id=:id',array(':id'=>$id));
			}
		}
	}			
	
	public function actionGet_orders_unread_comments_count()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
								
		$sql = 'SELECT 
		COUNT(orders.id) AS total
		FROM 
		orders
		INNER JOIN
		orders_comment
		ON
		(orders.id = orders_comment.id_orders AND orders_comment.read_comment = 0 AND orders_comment.id_user_created = 0)';
		/* Select queries return a resultset */
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true);

		echo $row['total'] ? $row['total']:0;
		exit;							
	}
	
	public function actionXml_list_top_buyers($posStart=0, $count=20, array $filters=array(), array $sort_col=array(), $top=5)
	{						
			
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$top = (int)$top;		
		
		//create query 
		
		$sql = 'SELECT 
		customer.id,
		customer.firstname,
		customer.lastname,
		customer.email,
		SUM(orders.grand_total) AS total
		FROM 
		customer
		INNER JOIN
		orders 
		ON
		(customer.id = orders.id_customer) 
		WHERE
		orders.status NOT IN (-1, 0, 4)
		GROUP BY 
		customer.id
		ORDER BY 
		total DESC
		LIMIT '.$top;					
		
		$command=$connection->createCommand($sql);
		//echo $sql . " id_product_bundled_product_group : " . $id_product_bundled_product_group . " id_product : " . $id . " name : " . $filters['name'];
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, array()) as $row) {
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['firstname'].' '.$row['lastname'].']]></cell>
			<cell type="ro"><![CDATA['.$row['email'].']]></cell>	
			<cell type="ro"><![CDATA['.Html::nf($row['total']).']]></cell>
			</row>';
		}
		
		echo '</rows>';

	}		
	
	
	public function actionXml_list_top_selling_products($posStart=0, $count=20, array $filters=array(), array $sort_col=array(), $top=5)
	{						
			
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$top = (int)$top;		
		
		//create query 
		
		// get top selling product
		$sql = 'SELECT 
		product.id,
		product_description.name,
		SUM(orders_item_product.qty) AS qty
		FROM
		product
		INNER JOIN
		product_description
		ON
		(product.id = product_description.id_product AND product_description.language_code = :language_code)
		
		INNER JOIN
		orders_item_product
		ON
		(product.id = orders_item_product.id_product)
		
		INNER JOIN
		(orders_item CROSS JOIN orders)
		ON
		(orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)

		
		WHERE
		orders.status NOT IN (-1, 0, 4)
		GROUP BY 
		product.id
		ORDER BY 
		qty DESC								
		LIMIT '.$top;
		$command=$connection->createCommand($sql);			
		
		// get total amount
		$sql = 'SELECT (IFNULL((SELECT 
		SUM(orders_item_product.subtotal+orders_item_product.taxes)
		FROM
		orders_item_product
		
		INNER JOIN
		(orders_item CROSS JOIN orders)
		ON
		(orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
		
		WHERE
		orders_item_product.id_product = :id_product
		AND				
		orders.status NOT IN (-1, 0, 4)),0)-IFNULL((SELECT
		SUM(orders_discount_item_product.amount) 				
		FROM
		orders_discount_item_product
		INNER JOIN
		(orders_item_product CROSS JOIN orders_item CROSS JOIN orders)
		ON
		(orders_discount_item_product.id_orders_item_product = orders_item_product.id AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
		WHERE
		orders_item_product.id_product = :id_product
		AND				
		orders.status NOT IN (-1, 0, 4)),0)) AS total				
		';
		$command_total=$connection->createCommand($sql);	
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, array(':language_code'=>Yii::app()->language)) as $row) {
			$row_total = $command_total->queryRow(true,array(':id_product'=>$row['id']));												
			
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['qty'].']]></cell>	
			<cell type="ro"><![CDATA['.Html::nf($row_total['total']).']]></cell>
			</row>';
		}
		
		echo '</rows>';

	}		
	
	public function actionXml_list_sales_information($posStart=0, $count=20, array $filters=array(), array $sort_col=array())
	{						
			
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$current_datetime = date('Y-m-d');
		$revenue_option = (int)$_GET['revenue_option'];
		$revenue_start_date = trim($_GET['revenue_start_date']);
		$revenue_end_date = trim($_GET['revenue_end_date']);
		
		switch ($revenue_option) {
			// This week
			case 0:
				$revenue_start_date = date('Y-m-d',strtotime('last Sunday'));;
				$revenue_end_date = $current_datetime;				
				break;
			// This month
			case 1:
				$revenue_start_date = date('Y-m-').'01';
				$revenue_end_date = date('Y-m-d',strtotime('-1 day',strtotime('+1 month',strtotime($revenue_start_date))));			
				break;
			// Last 3 months
			case 2:
				$revenue_start_date = date('Y-m-d',strtotime('-3 month',strtotime($current_datetime)));						
				$revenue_end_date = $current_datetime;				
				break;
			// Last 6 months
			case 3:
				$revenue_start_date = date('Y-m-d',strtotime('-6 month',strtotime($current_datetime)));									
				$revenue_end_date = $current_datetime;				
				break;
			// This year
			case 4:
				$revenue_start_date = date('Y-').'01-01';	
				$revenue_end_date = $current_datetime;				
				break;
			// Last 5 years
			case 5:
				$revenue_start_date = date('Y-m-d',strtotime('-5 year',strtotime($current_datetime)));
				$revenue_end_date = $current_datetime;			
				break;
			// Last 10 years
			case 6:
				$revenue_start_date = date('Y-m-d',strtotime('-10 year',strtotime($current_datetime)));
				$revenue_end_date = $current_datetime;						
				break;
			// Custom
			case 7:
				if ($revenue_start_date && $revenue_end_date && strtotime($revenue_end_date) < strtotime($revenue_start_date)) {
					echo 'error in dates';
					exit;
				} else if (!$revenue_start_date || !$revenue_end_date) { exit; }
				break;
		}		
		
		$params=array(':start_date'=>$revenue_start_date ? $revenue_start_date:'0000-00-00',':end_date'=>$revenue_end_date);
							
		
		// get total orders
		$sql='SELECT 
		COUNT(orders.id) AS total
		FROM
		orders 
		
		WHERE 
		orders.status NOT IN (-1, 0, 4)
		AND
		orders.date_order BETWEEN CONCAT(:start_date," 00:00:00") AND CONCAT(:end_date," 23:59:59")';
		$command=$connection->createCommand($sql);						
			
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		
		//create query 
		// get total orders
		$sql='SELECT 
		orders.id,
		orders.date_order,
		orders.grand_total,
		SUM(orders_item_product.cost_price) AS total_cost_products,
		SUM(IF(orders_item_option.id IS NOT NULL,orders_item_option.cost_price,0)) AS total_cost_options,
		customer.firstname,
		customer.lastname		
		FROM
		orders 
		INNER JOIN
		(orders_item CROSS JOIN orders_item_product)
		ON
		(orders.id = orders_item.id_orders AND orders_item.id = orders_item_product.id_orders_item)
		LEFT JOIN 
		orders_item_option
		ON
		(orders_item.id = orders_item_option.id_orders_item)
		
		INNER JOIN 
		customer
		ON
		(orders.id_customer = customer.id)
		
		WHERE 
		orders.status NOT IN (-1, 0, 4)
		AND
		orders.date_order BETWEEN CONCAT(:start_date," 00:00:00") AND CONCAT(:end_date," 23:59:59") 
		GROUP BY 
		orders.id 
		ORDER BY
		orders.date_order DESC ';
		$command=$connection->createCommand($sql);						
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		//echo $sql . " id_product_bundled_product_group : " . $id_product_bundled_product_group . " id_product : " . $id . " name : " . $filters['name'];
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['id'].']]></cell>
			<cell type="ro"><![CDATA['.$row['date_order'].']]></cell>
			<cell type="ro"><![CDATA['.$row['firstname'].' '.$row['lastname'].']]></cell>
			<cell type="ro"><![CDATA['.Html::nf($row['grand_total']).']]></cell>	
			<cell type="ro"><![CDATA['.Html::nf($row['grand_total']-($row['total_cost_products']+$row['total_cost_options'])).']]></cell>				
			</row>';
		}
		
		echo '</rows>';

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
	
}