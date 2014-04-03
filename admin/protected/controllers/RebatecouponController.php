<?php

class RebatecouponController extends Controller
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
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'rebate_coupon.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// code
		if (isset($filters['coupon_code']) && !empty($filters['coupon_code'])) {
			$where[] = 'rebate_coupon.coupon_code LIKE CONCAT("%",:coupon_code,"%")';
			$params[':coupon_code']=$filters['coupon_code'];
		}		
		
		// start date start
		if (isset($filters['start_date_start']) && !empty($filters['start_date_start'])) {
			$where[] = 'rebate_coupon.start_date >= :start_date_start';
			$params[':start_date_start']=$filters['start_date_start'];
		}	
		
		// start date end
		if (isset($filters['start_date_end']) && !empty($filters['start_date_end'])) {
			$where[] = 'rebate_coupon.start_date <= :start_date_end';
			$params[':start_date_end']=$filters['start_date_end'];
		}						
		
		// end date start
		if (isset($filters['end_date_start']) && !empty($filters['end_date_start'])) {
			$where[] = 'rebate_coupon.end_date >= :end_date_start';
			$params[':end_date_start']=$filters['end_date_start'];
		}	
		
		// end date end
		if (isset($filters['end_date_end']) && !empty($filters['end_date_end'])) {
			$where[] = 'rebate_coupon.end_date <= :end_date_end';
			$params[':end_date_end']=$filters['end_date_end'];
		}		
		
		// discount type
		if (isset($filters['discount_type'])) {
			$where[] = 'rebate_coupon.type = :discount_type';				
			$params[':discount_type']=$filters['discount_type'];
		}						
		
		// discount
		if (isset($filters['discount']) && !empty($filters['discount'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['discount'])) {
				$where[] = 'rebate_coupon.discount <= :discount';
				$params[':discount']=ltrim($filters['discount'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['discount'])) {
				$where[] = 'rebate_coupon.discount >= :discount';
				$params[':discount']=ltrim($filters['discount'],'>=');
			// <N
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['discount'])) {		
				$where[] = 'rebate_coupon.discount < :discount';
				$params[':discount']=ltrim($filters['discount'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['discount'])) {		
				$where[] = 'rebate_coupon.discount > :discount';
				$params[':discount']=ltrim($filters['discount'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['discount'])) {		
				$where[] = 'rebate_coupon.discount = :discount';
				$params[':coupon']=ltrim($filters['discount'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['discount'])) {
				$search = explode('..',$filters['discount']);
				$where[] = 'rebate_coupon.discount BETWEEN :discount_start AND :discount_end';
				$params[':discount_start']=$search[0];
				$params[':discount_end']=$search[1];
			// N				
			} else {
				$where[] = 'rebate_coupon.discount = :discount';
				$params[':discount']=$filters['discount'];
			}
		}		
				
		// status
		if (isset($filters['active'])) {
			switch ($filters['active']) {
				case 0:
				case 1:					
					$where[] = 'rebate_coupon.active = :active';				
					$params[':active']=$filters['active'];
					break;
			}
		}
		
		// Type
		if (isset($filters['type'])) {
			switch ($filters['type']) {
				case 0:
				case 1:					
					$where[] = 'rebate_coupon.coupon = :type';				
					$params[':type']=$filters['type'];
					break;
			}
		}								
		
		$sql = "SELECT 
		COUNT(rebate_coupon.id) AS total 
		FROM 
		rebate_coupon 		
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
		rebate_coupon.id,
		rebate_coupon.name,
		rebate_coupon.coupon_code,
		rebate_coupon.coupon,
		rebate_coupon.start_date,
		rebate_coupon.end_date,
		rebate_coupon.discount_type,
		rebate_coupon.discount,
		rebate_coupon.active,
		rebate_coupon.type
		FROM 
		rebate_coupon 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// type
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY rebate_coupon.coupon ".$direct;
		// discount type
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY rebate_coupon.type ".$direct;				
		// nom
		} else if (isset($sort_col[3]) && !empty($sort_col[3])) {	
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY rebate_coupon.name ".$direct;
		// code
		} else if (isset($sort_col[4]) && !empty($sort_col[4])) {
			$direct = $sort_col[4] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY rebate_coupon.coupon_code ".$direct;
		// start date
		} else if (isset($sort_col[5]) && !empty($sort_col[5])) {
			$direct = $sort_col[5] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY rebate_coupon.start_date ".$direct;	
		// end date
		} else if (isset($sort_col[6]) && !empty($sort_col[6])) {
			$direct = $sort_col[6] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY rebate_coupon.end_date ".$direct;	
					
		// percent
		} else if (isset($sort_col[7]) && !empty($sort_col[7])) {
			$direct = $sort_col[7] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY rebate_coupon.discount ".$direct;		
		// active
		} else if (isset($sort_col[8]) && !empty($sort_col[8])) {
			$direct = $sort_col[8] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY rebate_coupon.active ".$direct;												
		} else {
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(rebate_coupon.name LIKE CONCAT(:name,'%'),0,1) ASC, rebate_coupon.name ASC";
			} else if (isset($filters['coupon_code']) && !empty($filters['coupon_code'])) {
				$sql.=" ORDER BY IF(rebate_coupon.coupon_code LIKE CONCAT(:coupon_code,'%'),0,1) ASC, rebate_coupon.name ASC";
			} else {
				$sql.=" ORDER BY rebate_coupon.id ASC";
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
			switch($row['type']){
				case 0:
					$type_name = Yii::t('controllers/RebatecouponController','LABEL_REBATE_0');
				break;
				case 1:
					$type_name = Yii::t('controllers/RebatecouponController','LABEL_REBATE_1');
				break;
				case 2:
					$type_name = Yii::t('controllers/RebatecouponController','LABEL_REBATE_2');
				break;
				case 3:
					$type_name = Yii::t('controllers/RebatecouponController','LABEL_REBATE_3');
				break;
				case 4:
					$type_name = Yii::t('controllers/RebatecouponController','LABEL_REBATE_4');
				break;
				case 5:
					$type_name = Yii::t('controllers/RebatecouponController','LABEL_REBATE_5');
				break;
				
			}
			echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
			<cell type="ch" />
			<cell type="ro"><![CDATA['.($row['coupon']?Yii::t('global','LABEL_COUPON'):Yii::t('global','LABEL_REBATE')).']]></cell>
			<cell type="ro"><![CDATA['.$type_name.']]></cell>
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['coupon_code'].']]></cell>
			<cell type="ro"><![CDATA['.(($row['start_date'] != '0000-00-00 00:00:00') ? $row['start_date']:'').']]></cell>
			<cell type="ro"><![CDATA['.(($row['end_date'] != '0000-00-00 00:00:00') ? $row['end_date']:'').']]></cell>			
			<cell type="ro"><![CDATA['.(!$row['discount_type'] ? Html::nf($row['discount']):$row['discount'] . "%").']]></cell>
			<cell type="ch">'.$row['active'].'</cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	/**
	 * This is the action to edit or create a rebate
	 */ 
	public function actionEdit($container, $id=0)	
	{
		$model=new RebateCouponForm;	
		
		$id = (int)$id;
		
		if ($id) {
			if ($rebate_coupon = Tbl_RebateCoupon::model()->findByPk($id)) {
				$model->id = $rebate_coupon->id;
				$model->type = $rebate_coupon->type;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}
				
		$this->renderPartial('edit',array('model'=>$model, 'container'=>$container));	
	}	
	
	/**
	 * This is the action to edit each section
	 */ 
	public function actionEdit_section($container, $containerJS)	
	{						
		$id = trim($_POST['id']);
		$id_rebate_coupon = (int)$_POST['id_rebate_coupon'];
						
		$this->renderPartial($id,array('id'=>$id_rebate_coupon,'container'=>$container,'containerJS'=>$containerJS));	
	}
	
	public function actionEdit_info_options($container, $containerLayout, $id=0)
	{
		$model = new RebateCouponForm;
		
		$id = (int)$id;		
		
		if ($id) {
			if ($rebate_coupon = Tbl_RebateCoupon::model()->findByPk($id)) {
				$model->id = $rebate_coupon->id;
				$model->type = $rebate_coupon->type;
				$model->coupon = $rebate_coupon->coupon;
				$model->coupon_code = $rebate_coupon->coupon_code;
				$model->name = $rebate_coupon->name;
				$model->start_date = ($rebate_coupon->start_date != '0000-00-00 00:00:00') ? $rebate_coupon->start_date:'';
				$model->end_date = ($rebate_coupon->end_date != '0000-00-00 00:00:00') ? $rebate_coupon->end_date:'';
				$model->coupon_max_usage_customer = $rebate_coupon->coupon_max_usage_customer;
				$model->coupon_max_usage = $rebate_coupon->coupon_max_usage;
				$model->all_product = $rebate_coupon->all_product;
				$model->applicable_on_sale = $rebate_coupon->applicable_on_sale;
				$model->min_cart_value = $rebate_coupon->min_cart_value;
				$model->max_weight = $rebate_coupon->max_weight;
				$model->discount_type = $rebate_coupon->discount_type;
				$model->discount = $rebate_coupon->discount;
				$model->min_qty_required = $rebate_coupon->min_qty_required;
				$model->max_qty_allowed = $rebate_coupon->max_qty_allowed;
				$model->buy_x_qty = $rebate_coupon->buy_x_qty;
				$model->get_y_qty = $rebate_coupon->get_y_qty;
				$model->active = $rebate_coupon->active;	

				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_info_options',array('model'=>$model, 'container'=>$container, 'containerLayout'=>$containerLayout));		
	}	
	
	public function actionXml_rebate_coupon_description($container, $id=0)
	{
		$model = new RebateCouponForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($rebate_coupon = Tbl_RebateCoupon::model()->findByPk($id)) {							
				// grab description information 
				foreach ($rebate_coupon->tbl_rebate_coupon_description as $row) {
					$model->rebate_coupon_description[$row->language_code]['description'] = $row->description;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_RebateCouponDescription::tableName());		
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';
		
		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:hidden;">	
					<div style="padding:10px;">
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong>'.
						(isset($columns['description']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_rebate_coupon_description['.$value->code.'][description]_maxlength">'.($columns['description']-strlen($model->rebate_coupon_description[$value->code]['description'])).'</span>)</em>':'').'
						<div>'.
						CHtml::activeTextArea($model,'rebate_coupon_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'rows' => 3,'maxlength'=>$columns['description'], 'id'=>$container.'_rebate_coupon_description['.$value->code.'][description]')).'
						<br /><span id="'.$container.'_rebate_coupon_description['.$value->code.'][description]_errorMsg" class="error"></span>
						</div>
					</div>   
					</div>
					</div>
				]]>
			</content>
		</tab>';
		
			++$i;
		}
		
		echo '</row></tabbar>';
	}					
	
	/**
	 * This is the action to save 
	 */
	public function actionSave_info()
	{
		$model = new RebateCouponForm;

		// collect user input data
		if(isset($_POST['RebateCouponForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['RebateCouponForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			
			$output = array('id'=>$model->id,'type'=>$model->type);
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);	
		}
	}	
	
	public function actionEdit_products_search($container, $id=0)
	{
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (!Tbl_RebateCoupon::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}			
		
		$this->renderPartial('edit_products_search',array('id'=>$id, 'container'=>$container));	
	}				
	
	/**
	 * This is the action to delete a product
	 */
	public function actionDelete_product($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id); 					
				
				// delete all
				Tbl_RebateCouponProduct::model()->deleteAll($criteria);					
			}
		}
	}	

	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_products($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		$search_by=(int)$_GET['search_by'];		
		
		$where=array('rebate_coupon_product.id_rebate_coupon = :id_rebate_coupon');
		$params=array(':id_rebate_coupon'=>$id,':language_code'=>Yii::app()->language);

		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
			
		
		// sku
		if (isset($filters['sku']) && !empty($filters['sku'])) {
			$where[] = 'sku LIKE CONCAT("%",:sku,"%")';
			$params[':sku']=$filters['sku'];
		}	
		
		// price
		if (isset($filters['price']) && !empty($filters['price'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['price'])) {
				$where[] = 'price <= :price';
				$params[':price']=ltrim($filters['price'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['total'])) {
				$where[] = 'price >= :price';
				$params[':price']=ltrim($filters['price'],'>=');
			// < N 
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['price'])) {		
				$where[] = 'price < :price';
				$params[':price']=ltrim($filters['price'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['price'])) {		
				$where[] = 'price > :price';
				$params[':price']=ltrim($filters['price'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['price'])) {		
				$where[] = 'price = :price';
				$params[':price']=ltrim($filters['price'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['price'])) {
				$search = explode('..',$filters['price']);
				$where[] = 'price BETWEEN :price_start AND :price_end';
				$params[':price_start']=$search[0];
				$params[':price_end']=$search[1];
			// N				
			} else {
				$where[] = 'price = :price';
				$params[':price']=$filters['price'];
			}
		}												
				
		$sql = 'SELECT 
		COUNT(rebate_coupon_product.id) AS total
		FROM
		rebate_coupon_product
		INNER JOIN
		product CROSS JOIN product_description
		ON
		(rebate_coupon_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = :language_code)
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = 'SELECT 
		rebate_coupon_product.id, 
		product.sku,
		product_description.name,
		product.price
		FROM
		rebate_coupon_product
		INNER JOIN
		product CROSS JOIN product_description
		ON
		(rebate_coupon_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = :language_code)
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		//echo $sql;
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY name ".$direct;
		// sku
		} else if (isset($sort_col[2]) && !empty($sort_col[3])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY sku ".$direct;	
		// price
		} else if (isset($sort_col[3]) && !empty($sort_col[4])) {
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY price ".$direct;																						
		} else {
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(name LIKE CONCAT(:name,'%'),0,1) ASC, name ASC";
			} else if (isset($filters['sku']) && !empty($filters['sku'])) { 
				$sql.=" ORDER BY IF(sku LIKE CONCAT(:sku,'%'),0,1) ASC, sku ASC";
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
						
			echo '<row id="'.$row['id'].':'.$row['id_product_variant'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>			
			<cell type="ro"><![CDATA['.Html::nf($row['price']).']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_product_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		$search_by=(int)$_GET['search_by'];		
		
		$id_rebate_coupon=(int)$_GET['id_rebate_coupon'];	
		
		$where=array('rebate_coupon_product.id IS NULL AND product.product_type = 0');
		$params=array(':language_code'=>Yii::app()->language,':id_rebate_coupon'=>$id_rebate_coupon);

		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		// name
		if (isset($filters['category']) && !empty($filters['category'])) {
			$where[] = 'category LIKE CONCAT("%",:category,"%")';
			$params[':category']=$filters['category'];
		}				
		
		// sku
		if (isset($filters['sku']) && !empty($filters['sku'])) {
			$where[] = 'sku LIKE CONCAT("%",:sku,"%")';
			$params[':sku']=$filters['sku'];
		}	
		
		// price
		if (isset($filters['price']) && !empty($filters['price'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['price'])) {
				$where[] = 'price <= :price';
				$params[':price']=ltrim($filters['price'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['total'])) {
				$where[] = 'price >= :price';
				$params[':price']=ltrim($filters['price'],'>=');
			// < N 
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['price'])) {		
				$where[] = 'price < :price';
				$params[':price']=ltrim($filters['price'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['price'])) {		
				$where[] = 'price > :price';
				$params[':price']=ltrim($filters['price'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['price'])) {		
				$where[] = 'price = :price';
				$params[':price']=ltrim($filters['price'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['price'])) {
				$search = explode('..',$filters['price']);
				$where[] = 'price BETWEEN :price_start AND :price_end';
				$params[':price_start']=$search[0];
				$params[':price_end']=$search[1];
			// N				
			} else {
				$where[] = 'price = :price';
				$params[':price']=$filters['price'];
			}
		}												
				
		$sql = 'SELECT 
		COUNT(product.id) AS total
		FROM
		product
		INNER JOIN
		product_description
		ON
		(product.id = product_description.id_product AND product_description.language_code = :language_code)
		LEFT JOIN 
		rebate_coupon_product  
		ON
		(product.id = rebate_coupon_product.id_product AND rebate_coupon_product.id_rebate_coupon = :id_rebate_coupon)
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	

		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		
		$sql = 'SELECT 
		product.id, 
		product.sku,
		product_description.name,
		product.sell_price AS price
		FROM 
		product
		INNER JOIN
		product_description
		ON
		(product.id = product_description.id_product AND product_description.language_code = :language_code)
		LEFT JOIN 
		rebate_coupon_product  
		ON
		(product.id = rebate_coupon_product.id_product AND rebate_coupon_product.id_rebate_coupon = :id_rebate_coupon)
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY name ".$direct;
		// sku
		} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY sku ".$direct;	
		// price
		} else if (isset($sort_col[4]) && !empty($sort_col[4])) {
			$direct = $sort_col[4] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY price ".$direct;																						
		} else {
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(name LIKE CONCAT(:name,'%'),0,1) ASC, name ASC";
			} else if (isset($filters['sku']) && !empty($filters['sku'])) { 
				$sql.=" ORDER BY IF(sku LIKE CONCAT(:sku,'%'),0,1) ASC, sku ASC";
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
						
			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>			
			<cell type="ro"><![CDATA['.Html::nf($row['price']).']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	
	/**
	 * This is the action to add a product
	 */
	public function actionAdd_product($id=0)
	{

		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$id_rebate_coupon = (int)$id;

		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id_rebate_coupon); 			
		
		if (!Tbl_RebateCoupon::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}						
		
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {			
			foreach ($ids as $id) {
				$rebate_coupon_product = new Tbl_RebateCouponProduct;
				$rebate_coupon_product->id_rebate_coupon = $id_rebate_coupon;
				$rebate_coupon_product->id_product = $id;
				$rebate_coupon_product->save();			
			}
			
		}
	}
	
	
	public function actionToggle_active_variant()
	{
		$id = (int)$_POST['id'];
		$active = ($_POST['active']=='true'?1:0);
		
		if ($p = Tbl_RebateCouponProductVariant::model()->findByPk($id)) {
			$p->active = $active;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
	
	
	/**
	 * This is the action to delete a product
	 */
	public function actionDelete()
	{
		// current category
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			// rebate
			$command=$connection->createCommand('DELETE FROM 
			rebate_coupon,
			rebate_coupon_product,
			rebate_coupon_category,
			rebate_coupon_description 
			USING 
			rebate_coupon 
			LEFT JOIN 
			rebate_coupon_category 
			ON
			(rebate_coupon.id = rebate_coupon_category.id_rebate_coupon) 
			LEFT JOIN 
			rebate_coupon_product 
			ON 
			(rebate_coupon.id = rebate_coupon_product.id_rebate_coupon)
			LEFT JOIN 
			rebate_coupon_description 
			ON
			(rebate_coupon.id = rebate_coupon_description.id_rebate_coupon) 
			WHERE 
			rebate_coupon.id=:id');					
			
			foreach ($ids as $id) {									
				$command->execute(array(':id'=>$id));						
			}
		}
	}
	
	/**
	 * This is the action to toggle product active status
	 */
	public function actionToggle_active()
	{
		$id = (int)$_POST['id'];
		$active = ($_POST['active']=='true'?1:0);
		
		if ($p = Tbl_RebateCoupon::model()->findByPk($id)) {
			$p->active = $active;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
	
	/**
	 * This is the action to save categories
	 */
	public function actionSave_categories($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (!Tbl_RebateCoupon::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_rebate_coupon=:id_rebate_coupon'; 
		$criteria->params=array(':id_rebate_coupon'=>$id); 
		
		Tbl_RebateCouponCategory::model()->deleteAll($criteria);
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_category) {
				$model = new Tbl_RebateCouponCategory;
				$model->id_rebate_coupon = $id;
				$model->id_category = $id_category;
				if (!$model->save()){
					throw new CException('unable to save category');	
				}
			}
		}
	}	
	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_categories($id=0)
	{		
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (!Tbl_RebateCoupon::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}			
	
		// xml header
		header("content-type: text/xml");	
		
		$eol = "\r\n";
		
		echo '<?xml version="1.0" encoding="utf-8"?>'.$eol;
		echo '<tree id="0">'.$eol;		
		
		$this->get_categories($id);		
		
		echo '</tree>'.$eol;
	}	
	
	//FREE SHIPPING
	
	public function actionXml_list_free_shipping($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':language_code'=>Yii::app()->language);
									
		
		$sql = "SELECT 
		COUNT(config_free_shipping_region.id) AS total 
		FROM 
		config_free_shipping_region 
		LEFT JOIN 
		country_description
		ON
		(config_free_shipping_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(config_free_shipping_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
		config_free_shipping_region.id,
		country_description.name AS country,
		state_description.name AS state		
		FROM 
		config_free_shipping_region 
		LEFT JOIN 
		country_description
		ON
		(config_free_shipping_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(config_free_shipping_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
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
			<cell type="ro"><![CDATA['.($row['country'] ? $row['country']:Yii::t('global','LABEL_PROMPT')).']]></cell>
			<cell type="ro"><![CDATA['.($row['state'] ? $row['state']:Yii::t('global','LABEL_PROMPT')).']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	public function actionDelete_region()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_config_free_shipping_region) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_config_free_shipping_region);					
				
				// delete all
				Tbl_ConfigFreeShippingRegion::model()->deleteAll($criteria);						
			}
		}
	}
	public function actionEdit_regions_options($container, $id=0)
	{
		$model = new FreeShippingRegionsForm;
		
		$id = (int)$_POST["id"];		
		if ($id) {
			if ($free_shipping_region = Tbl_ConfigFreeShippingRegion::model()->findByPk($id)) {
				$model->id = $free_shipping_region->id;
				$model->country_code = $free_shipping_region->country_code;
				$model->state_code = $free_shipping_region->state_code;	
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_regions_options',array('model'=>$model, 'container'=>$container));		
	}
	
	public function actionSave_regions_options()
	{
		$model = new FreeShippingRegionsForm;
		
		// collect user input data
		if(isset($_POST['FreeShippingRegionsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['FreeShippingRegionsForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
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

	public function actionGet_province_list()
	{
		$model=new FreeShippingRegionsForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		$container = trim($_POST['container']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>$container . '_state_code'));
	}
	
	//---------END FREE SHIPPING
	
	
	
	
	
	
	
	
		
	
	/**
	 * This is a function to get a list of the categories and sub categories recursively
	 */
	public function get_categories($id_rebate_coupon=0,$id_parent=0)
	{
		$id_product = (int)$id_product;
		$id_parent = (int)$id_parent;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent'; 
		$criteria->params=array(':id_parent'=>$id_parent); 				
		$criteria->order='sort_order ASC';	
		
		$eol = "\r\n";
			
		foreach (Tbl_Category::model()->getDescription()->findAll($criteria) as $row) {		
			$criteria2=new CDbCriteria; 
			$criteria2->condition='id_parent=:id_parent'; 
			$criteria2->params=array(':id_parent'=>$row->id); 				
			$criteria2->order='sort_order ASC';			
			
			$child = Tbl_Category::model()->count($criteria2) ? 1:0;	

			$criteria2->condition='id_rebate_coupon=:id_rebate_coupon AND id_category=:id_category'; 
			$criteria2->params=array(':id_rebate_coupon'=>$id_rebate_coupon,':id_category'=>$row->id); 	
			$criteria2->order='';	
			
			$checked = Tbl_RebateCouponCategory::model()->count($criteria2) ? 1:'';
		
			echo '<item text="'.CHtml::encode($row->tbl_category_description[0]->name).'" id="'.$row->id.'" child="'.$child.'" checked="'.$checked.'" call="true" open="1">'.$eol;
			
			if ($child) { $this->get_categories($id_rebate_coupon, $row->id); }
			
			echo '</item>'.$eol;
		}					
	}	
	
	/**
	 * This is the action to get an XML list of the rebate menu
	 */
	public function actionXml_list_section($id=0)
	{
		$id = (int)$id;
		
		$disabled = '';
		
		if (!$id) { 
			$disabled = '<disabled><![CDATA[1]]></disabled>';
		}
			
		//set content type and xml tag
		header("Content-type:text/xml");		
		
		echo '<data>
			<item id="edit_info">
				<Title><![CDATA['.Yii::t('controllers/RebatecouponController','LABEL_INFORMATION').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/RebatecouponController','LABEL_INFORMATION_DESCRIPTION').']]></Description>
			</item>
			<item id="edit_products">
				<Title><![CDATA['.Yii::t('controllers/RebatecouponController','LABEL_PRODUCTS').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/RebatecouponController','LABEL_PRODUCTS_DESCRIPTION').']]></Description>
				'.$disabled.'
			</item>
			<item id="edit_categories">
				<Title><![CDATA['.Yii::t('controllers/RebatecouponController','LABEL_CATEGORIES').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/RebatecouponController','LABEL_CATEGORIES_DESCRIPTION').']]></Description>
				'.$disabled.'
			</item>
		</data>';
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