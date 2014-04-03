<?php

class ProductsController extends Controller
{
	public $variant_combinations=array();
	
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
	
	
	/************************************************************
	*															*
	*															*
	*							LISTE							*
	*															*
	*															*
	************************************************************/
	
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
		$where[] = 'product.archive = 0';
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'product_description.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// sku
		if (isset($filters['sku']) && !empty($filters['sku'])) {
			$where[] = 'product.sku LIKE CONCAT(:sku,"%")';
			$params[':sku']=$filters['sku'];
		}		
			
		
		// status
		if (isset($filters['product_type'])) {
			switch ($filters['product_type']) {
				case 0:
				case 1:	
				case 2:					
					$where[] = 'product.product_type = :product_type';				
					$params[':product_type']=$filters['product_type'];
					break;
			}
		}			
		
		// price
		if (isset($filters['price']) && !empty($filters['price'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['price'])) {
				$where[] = 'product.sell_price <= :price';
				$params[':price']=ltrim($filters['price'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['price'])) {
				$where[] = 'product.sell_price >= :price';
				$params[':price']=ltrim($filters['price'],'>=');
			// <N
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['price'])) {		
				$where[] = 'product.sell_price < :price';
				$params[':price']=ltrim($filters['price'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['price'])) {		
				$where[] = 'product.sell_price > :price';
				$params[':price']=ltrim($filters['price'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['price'])) {		
				$where[] = 'product.sell_price = :price';
				$params[':price']=ltrim($filters['price'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['price'])) {
				$search = explode('..',$filters['price']);
				$where[] = 'product.sell_price BETWEEN :price_start AND :price_end';
				$params[':price_start']=$search[0];
				$params[':price_end']=$search[1];
			// N				
			} else {
				$where[] = 'product.sell_price = :price';
				$params[':price']=$filters['price'];
			}
		}		
				
		// status
		if (isset($filters['active'])) {
			switch ($filters['active']) {
				case 0:
				case 1:					
					$where[] = 'product.active = :active';				
					$params[':active']=$filters['active'];
					break;
			}
		}
		
		// featured
		if (isset($filters['featured'])) {
			switch ($filters['featured']) {
				case 0:
				case 1:					
					$where[] = 'product.featured = :featured';				
					$params[':featured']=$filters['featured'];
					break;
			}
		}	
		
		// downloadable
		if (isset($filters['downloadable'])) {
			switch ($filters['downloadable']) {
				case 0:
				case 1:					
					$where[] = 'product.downloadable = :downloadable';				
					$params[':downloadable']=$filters['downloadable'];
					break;
			}
		}							
		
		$sql = "SELECT 
		COUNT(product.id) AS total 
		FROM 
		product 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
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
		product.id,
		product.product_type,
		product.downloadable,
		product.sku,
		product.active,
		product.featured,
		product_description.name,
		product.price,
		product.sell_price
		FROM 
		product 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."')  
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product_description.name ".$direct;
		// sku
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product.sku ".$direct;
		// price
		} else if (isset($sort_col[4]) && !empty($sort_col[4])) {
			$direct = $sort_col[4] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY sell_price ".$direct;			
		// featured
		} else if (isset($sort_col[5]) && !empty($sort_col[5])) {
			$direct = $sort_col[5] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product.featured ".$direct;			
		} else {
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(product_description.name LIKE CONCAT(:name,'%'),0,1) ASC, product_description.name ASC";
			} else if (isset($filters['sku']) && !empty($filters['sku'])) {
				$sql.=" ORDER BY IF(product.sku LIKE CONCAT(:sku,'%'),0,1) ASC, product.sku ASC";
			} else if (isset($filters['price']) && !empty($filters['price'])) {
				$sql.=" ORDER BY sell_price ASC";
			} else {
				$sql.=" ORDER BY product.id ASC";
			}
		}		
		
		//add limits to query to get only rows necessary for the output
		//$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			switch ($row['product_type']) {
				// product
				case 0:
					$product_type = Yii::t('global','LABEL_PRODUCT_TYPE_PRODUCT');
					break;
				// combo deal
				case 1:
					$product_type = Yii::t('global','LABEL_PRODUCT_TYPE_COMBO');
					break;
				// bundled products
				case 2:
					$product_type = Yii::t('global','LABEL_PRODUCT_TYPE_BUNDLED');
					break;	
			}	
			switch ($row['downloadable']) {
				case 0:
					$downloadable = Yii::t('global','LABEL_NO');
					break;
				case 1:
					$downloadable = Yii::t('global','LABEL_YES');
					break;	
			}		
			
			echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
			<cell type="ro"><![CDATA['.$product_type.']]></cell>
			<cell type="ro"><![CDATA['.$downloadable.']]></cell>
			<cell type="ro"><![CDATA['.Html::nf($row['sell_price']).']]></cell>
			<cell type="ch"><![CDATA['.$row['featured'].']]></cell>
			<cell type="ch"><![CDATA['.$row['active'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/**
	 * This is the action to add a product
	 */ 
	public function actionAdd_product($container, $id=0)	
	{
		$model=new ProductsForm;	
		
		$id = (int)$id;				
		
		if ($id) {
			if (!$p = Tbl_Product::model()->findByPk($id)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		

			$model->id = $id;
			$model->product_type = $p->product_type;	
		}
				
		$this->renderPartial('add_product',array('model'=>$model, 'container'=>$container));	
	}	
		
	/**
	 * This is the action to save the information section	 
	 */
	public function actionSave_product_type($id=0)
	{
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id=(int)$id;
		$product_type=(int)$_POST['product_type'];

		if (!$p = Tbl_Product::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		$current_product_type = $p->product_type;
		$p->product_type = $product_type;
		if ($has_variants = $p->has_variants && $p->product_type != 0) $p->has_variants = 0;				
		
		if (!$p->save()){
			throw new CException(Yii::t('global','ERROR_SAVING'));			
		}else{
			switch($current_product_type){
				// single
				case 0:
					// if we had variants, remove them
					if ($has_variants) {
						$sql = 'SELECT
						*
						FROM
						product_image_variant_image 
						INNER JOIN product_image_variant
						ON (product_image_variant_image.id_product_image_variant = product_image_variant.id)
						WHERE product_image_variant.id_product = :id_product';
						$command=$connection->createCommand($sql);
						
						foreach ($command->queryAll(true,array(':id_product'=>$id)) as $row) {
							if (is_file(Yii::app()->params['product_images_base_path'].'original/'.$row['original'])) @unlink(Yii::app()->params['product_images_base_path'].'original/'.$row['original']);
							if (is_file(Yii::app()->params['product_images_base_path'].'cover/'.$row['filename'])) @unlink(Yii::app()->params['product_images_base_path'].'cover/'.$row['filename']);
							if (is_file(Yii::app()->params['product_images_base_path'].'zoom/'.$row['filename'])) @unlink(Yii::app()->params['product_images_base_path'].'zoom/'.$row['filename']);
							if (is_file(Yii::app()->params['product_images_base_path'].'suggest/'.$row['filename'])) @unlink(Yii::app()->params['product_images_base_path'].'suggest/'.$row['filename']);
							if (is_file(Yii::app()->params['product_images_base_path'].'thumb/'.$row['filename'])) @unlink(Yii::app()->params['product_images_base_path'].'thumb/'.$row['filename']);
							if (is_file(Yii::app()->params['product_images_base_path'].'zoom/'.$row['filename'])) @unlink(Yii::app()->params['product_images_base_path'].'zoom/'.$row['filename']);
							if (is_file(Yii::app()->params['product_images_base_path'].'listing/'.$row['filename'])) @unlink(Yii::app()->params['product_images_base_path'].'listing/'.$row['filename']);
						}
						
						$sql = 'DELETE FROM 
						product_variant,
						product_variant_description,
						product_variant_group,
						product_variant_group_description,
						product_variant_group_option,
						product_variant_group_option_description,
						product_variant_option,
						product_image_variant,
						product_image_variant_description,
						product_image_variant_image,
						product_image_variant_option
						USING
						product_variant 
						LEFT JOIN product_variant_description
						ON (product_variant.id = product_variant_description.id_product_variant)
						LEFT JOIN product_variant_group
						ON (product_variant.id_product = product_variant_group.id_product)
						LEFT JOIN product_variant_group_description
						ON (product_variant_group.id = product_variant_group_description.id_product_variant_group)
						LEFT JOIN product_variant_group_option
						ON (product_variant_group.id = product_variant_group_option.id_product_variant_group)
						LEFT JOIN product_variant_group_option_description
						ON (product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option)
						LEFT JOIN product_variant_option
						ON (product_variant.id = product_variant_option.id_product_variant)
						LEFT JOIN product_image_variant
						ON (product_variant.id_product = product_image_variant.id_product)
						LEFT JOIN product_image_variant_description
						ON (product_image_variant.id = product_image_variant_description.id_product_image_variant)
						LEFT JOIN product_image_variant_image
						ON (product_image_variant.id = product_image_variant_image.id_product_image_variant)
						LEFT JOIN product_image_variant_option
						ON (product_image_variant.id = product_image_variant_option.id_product_image_variant)
						WHERE product_variant.id_product=:id_product';
						$command=$connection->createCommand($sql);
						$command->execute(array(':id_product'=>$id));
					}
					break;				
				case 1:
					// Delete Combo Product
					$sql = 'SELECT
					id
					FROM
					product_combo
					WHERE id_product = "' .$id . '"';
					$command=$connection->createCommand($sql);
					
					foreach ($command->queryAll(true) as $row) {
						
						$criteria=new CDbCriteria; 
						$criteria->condition='id_product_combo=:id'; 
						$criteria->params=array(':id'=>$row['id']); 					
						
						// delete all
						Tbl_ProductComboVariant::model()->deleteAll($criteria);
							
					}
					$criteria=new CDbCriteria; 
					$criteria->condition='id_product=:id'; 
					$criteria->params=array(':id'=>$id); 					
					
					// delete all
					Tbl_ProductCombo::model()->deleteAll($criteria);
				break;
				case 2:
					
					// Delete bundled product
					$sql = 'SELECT
					id
					FROM
					product_bundled_product_group
					WHERE id_product = "' .$id . '"';
					$command=$connection->createCommand($sql);
					
					foreach ($command->queryAll(true) as $row) {
						
						$criteria=new CDbCriteria; 
						$criteria->condition='id_product_bundled_product_group=:id'; 
						$criteria->params=array(':id'=>$row['id']); 					
						
						// delete all
						Tbl_ProductBundledProductGroupDescription::model()->deleteAll($criteria);
					
						// delete all
						Tbl_ProductBundledProductGroupProduct::model()->deleteAll($criteria);
							
					}
					$criteria=new CDbCriteria; 
					$criteria->condition='id_product=:id'; 
					$criteria->params=array(':id'=>$id); 					
					
					// delete all
					Tbl_ProductBundledProductGroup::model()->deleteAll($criteria);
				break;
			}
			
			
		}
	}		

	/**
	 * This is the action to edit or create a product
	 */ 
	public function actionEdit($container, $id=0)	
	{
		$model=new ProductsForm;	
		
		$id = (int)$id;
		
		if ($id) { 
			if (!$p = Tbl_Product::model()->findByPk($id)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		

			$model->id = $id;
			$model->product_type = $p->product_type;		
		}			
		
		if (isset($_GET['product_type'])) $model->product_type = (int)$_GET['product_type'];
		
		switch ($model->product_type) {
			// product
			case 0:
				$product_type = Yii::t('global','LABEL_PRODUCT_TYPE_PRODUCT');
				break;
			// combo deal
			case 1:
				$product_type = Yii::t('global','LABEL_PRODUCT_TYPE_COMBO');
				break;
			// bundled products
			case 2:
				$product_type = Yii::t('global','LABEL_PRODUCT_TYPE_BUNDLED');
				break;	
		}				
				
		$this->renderPartial('edit',array('model'=>$model, 'container'=>$container, "product_type_text"=>$product_type));	
	}
	
	/**
	 * This is the action to edit each section
	 */ 
	public function actionEdit_section($container, $containerJS)	
	{						
		$id = trim($_POST['id']);
		$id_product = (int)$_POST['id_product'];
		$product_type = (int)$_POST['product_type'];
		
		if ($id_product) { 
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id_product); 		
			
			if (!Tbl_Product::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}			
		}
						
		$this->renderPartial($id,array('id'=>$id_product,'container'=>$container,'containerJS'=>$containerJS,'product_type'=>$product_type));	
	}
	
	/************************************************************
	*															*
	*															*
	*						INFORMATION							*
	*															*
	*															*
	************************************************************/
		
	
	public function actionEdit_info_options($container, $id=0, $product_type=0)
	{
		$model = new ProductsForm;
		
		$id = (int)$id;
		$product_type = (int)$product_type;
		
		$model->product_type = $product_type;
		
		if ($id) {
			if ($product = Tbl_Product::model()->findByPk($id)) {
				$model->id = $product->id;
				$model->product_type = $product->product_type;
				$model->sku = $product->sku;
				$model->cost_price = $product->cost_price;
				$model->price = $product->price;
				$model->special_price = $product->special_price;
				$model->special_price_from_date = ($product->special_price_from_date != '0000-00-00 00:00:00') ? $product->special_price_from_date:'';
				$model->special_price_to_date = ($product->special_price_to_date != '0000-00-00 00:00:00') ? $product->special_price_to_date:'';
				$model->discount_type = $product->discount_type;
				$model->discount = $product->discount;		
				$model->use_product_current_price = $product->use_product_current_price;
				$model->use_product_special_price = $product->use_product_special_price;
				$model->user_defined_qty = $product->user_defined_qty;	
				$model->max_qty = $product->max_qty;			
				$model->taxable = $product->taxable;
				$model->id_tax_group = $product->id_tax_group;	
				$model->brand = $product->brand;	
				$model->model = $product->model;	
				$model->year = $product->year;	
				$model->mileage = $product->mileage;	
				$model->color = $product->color;	
				$model->used = $product->used;	
				$model->featured = $product->featured;	
				$model->active = $product->active;
				$model->display_in_catalog = $product->display_in_catalog;
				$model->date_displayed = ($product->date_displayed != '0000-00-00 00:00:00') ? $product->date_displayed:'';
				$model->downloadable = $product->downloadable;
				$model->min_qty = $product->min_qty;
				$model->display_multiple_variants_form = $product->display_multiple_variants_form;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}	
		
		if (isset($_POST['product_type'])) $model->product_type = (int)$_POST['product_type'];		
		
		$this->renderPartial('edit_info_options',array('model'=>$model, 'container'=>$container));	
	}		
	
	/**
	 * This is the action to save the information section	 
	 */
	public function actionSave_info()
	{
		$pass = (int)$_POST['pass'];

		// collect user input data
		if(isset($_POST['ProductsForm']))
		{
			$model = new ProductsForm;
	
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsForm'] as $name=>$value)
			{
				$model->$name=$value;
			}
			
			// validate 
			if($model->validate()) {
				$output['in_other_product'] = 0;
				// Verify if we disabled the product we must disabled bundled and combo at the same time
				if(!empty($model->id) && !$model->active){
					$connection=Yii::app()->db;   // assuming you have configured a "db" connection
					
					$id = $model->id;
					
					
					if(!$pass){
						// Verify if product is in a combo
						$sql = 'SELECT
						product_combo.id
						FROM
						product_combo
						INNER JOIN
						product
						ON
						product_combo.id_product = product.id AND product.active = 1
						WHERE product_combo.id_combo_product = "'.$id.'"
						LIMIT 1';
						$command=$connection->createCommand($sql);
						
						if($command->queryAll(true)) {
							$output['in_other_product'] = 1;
						}
						
						// Verify if product is in a bundled
						$sql = 'SELECT
						product_bundled_product_group_product.id
						FROM
						product_bundled_product_group_product
						INNER JOIN
						product
						ON
						product_bundled_product_group_product.id_product = product.id AND product.active = 1
						WHERE product_bundled_product_group_product.id_product = "'.$id.'"
						LIMIT 1';
						$command=$connection->createCommand($sql);
						
						if($command->queryAll(true)) {
							$output['in_other_product'] = 1;
						}
						header('Content-Type: text/javascript; charset=UTF-8'); //set header
						echo json_encode($output); //display records in json format using json_encode
						exit;
					}else{
						// Deactivate combo
						$criteria=new CDbCriteria; 
						$criteria->condition='id_combo_product=:id_combo_product'; 
						$criteria->params=array(':id_combo_product'=>$id); 	
						foreach (Tbl_ProductCombo::model()->findAll($criteria) as $row) {
							$criteria2=new CDbCriteria; 
							$criteria2->condition='id=:id'; 
							$criteria2->params=array(':id'=>$row['id_product']); 	
							Tbl_Product::model()->updateAll(array('active'=>$active),$criteria2);	
						}
						// Deactivate bundled
						$sql = 'SELECT
						id_product_bundled_product_group
						FROM
						product_bundled_product_group_product
						WHERE id_product = "' .$id . '"
						GROUP BY id_product_bundled_product_group';
						$command=$connection->createCommand($sql);
						
						foreach ($command->queryAll(true) as $row) {
							$sql = 'UPDATE
							product
							SET 
							active = "'.$active.'" 
							WHERE
							id IN (SELECT id_product FROM product_bundled_product_group WHERE id = "'.$row['id_product_bundled_product_group'].'")';
							$command=$connection->createCommand($sql);						
							$command->execute();
								
						}
					}
				}

				if($pass){
					$model->save();
				}
			}
			
			$output = array('id'=>$model->id,'product_type'=>$model->product_type);
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);
			
		}
	}
	
	/**
	 * This is the action to delete a product
	 */
	public function actionDelete()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$ids = $_POST['ids'];
		$pass = (int)$_POST['pass'];
		
		if (is_array($ids) && sizeof($ids)) {
			
				if(!$pass){
					$arr_result['in_other_product'] = 0;
					// Verify if product is in a combo
					foreach ($ids as $id) {
						$criteria=new CDbCriteria; 
						$criteria->condition='id_combo_product=:id_combo_product'; 
						$criteria->params=array(':id_combo_product'=>$id); 	
						if (Tbl_ProductCombo::model()->find($criteria)) {
							$arr_result['in_other_product'] = 1;
							break;
						}
						// Verify if product is in a bundled
						$criteria=new CDbCriteria; 
						$criteria->condition='id_product=:id_product'; 
						$criteria->params=array(':id_product'=>$id); 	
						if (Tbl_ProductBundledProductGroupProduct::model()->find($criteria)) {
							$arr_result['in_other_product'] = 1;
							break;
						}
					}
					header('Content-Type: text/javascript; charset=UTF-8'); //set header
					echo json_encode($arr_result); //display records in json format using json_encode
				}else{
					// prepare delete options
					$command_delete_options=$connection->createCommand('DELETE FROM 
					product_options_group 
					WHERE 
					product_options_group.id_product=:id_product');					
					
					// prepare delete variants
					$command_delete_variants=$connection->createCommand('DELETE FROM 
					product_variant_group,
					product_variant_group_description,
					product_variant_group_option,
					product_variant_group_option_description,
					product_variant,
					product_variant_option,
					product_combo_variant					
					USING 
					product_variant_group 
					LEFT JOIN 
					product_variant_group_description 
					ON
					(product_variant_group.id = product_variant_group_description.id_product_variant_group) 
					LEFT JOIN 
					product_variant_group_option
					ON 
					(product_variant_group.id = product_variant_group_option.id_product_variant_group)
					LEFT JOIN 
					product_variant_group_option_description
					ON
					(product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option)
					LEFT JOIN 
					product_variant
					ON 
					(product_variant_group.id_product = product_variant.id_product)
					LEFT JOIN 
					product_variant_option
					ON
					(product_variant.id = product_variant_option.id_product_variant)
					LEFT JOIN 
					product_combo_variant
					ON
					(product_variant.id = product_combo_variant.id_product_variant)
					WHERE 
					product_variant_group.id_product=:id_product');					
					
					foreach ($ids as $id) {
						// Criteria used below
						$criteria=new CDbCriteria; 
						$criteria->condition='id_product=:id_product'; 
						$criteria->params=array(':id_product'=>$id);
						
						//Verify if product is sold, we archive the product
						if(Tbl_OrdersItemProduct::model()->find($criteria)){
							if ($product = Tbl_Product::model()->findByPk($id)) {
								$product->archive = 1;
								$product->active = 0;
								if (!$product->save()) {
									throw new CException(Yii::t('global','LABEL_ALERT_NO_DATA_RETURN'));	
								}
							} 
						}else{
							
							// Deactivate combo
							$criteria_combo=new CDbCriteria; 
							$criteria_combo->condition='id_combo_product=:id_combo_product'; 
							$criteria_combo->params=array(':id_combo_product'=>$id); 	
							foreach (Tbl_ProductCombo::model()->findAll($criteria_combo) as $row) {
								$criteria_combo2=new CDbCriteria; 
								$criteria_combo2->condition='id=:id'; 
								$criteria_combo2->params=array(':id'=>$row['id_product']); 	
								Tbl_Product::model()->updateAll(array('active'=>'0'),$criteria_combo2);	
							}
							// Deactivate bundled
							$sql = 'SELECT
							id_product_bundled_product_group
							FROM
							product_bundled_product_group_product
							WHERE id_product = "' .$id . '"
							GROUP BY id_product_bundled_product_group';
							$command=$connection->createCommand($sql);
							
							foreach ($command->queryAll(true) as $row) {
								$sql = 'UPDATE
								product
								SET 
								active = "0" 
								WHERE
								id IN (SELECT id_product FROM product_bundled_product_group WHERE id = "'.$row['id_product_bundled_product_group'].'")';
								$command=$connection->createCommand($sql);						
								$command->execute();
									
							}
							
							if ($p = Tbl_Product::model()->findByPk($id)) {
								foreach ($p->tbl_product_image as $row) {
									$images_path = Yii::app()->params['product_images_base_path'];		
									$original = $row->original;
									$filename = $row->filename;
									
									// delete files
									if (is_file($images_path.'original/'.$original)) { @unlink($images_path.'original/'.$original); }
									if (is_file($images_path.'cover/'.$filename)) { @unlink($images_path.'cover/'.$filename); }
									if (is_file($images_path.'listing/'.$filename)) { @unlink($images_path.'listing/'.$filename); }
									if (is_file($images_path.'suggest/'.$filename)) { @unlink($images_path.'suggest/'.$filename); }
									if (is_file($images_path.'thumb/'.$filename)) { @unlink($images_path.'thumb/'.$filename); }
									if (is_file($images_path.'zoom/'.$filename)) { @unlink($images_path.'zoom/'.$filename); }			
									$row->delete();
								}
							} else {
								throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
							}
							
												
							
							// delete all
							Tbl_Product::model()->deleteByPk($id);
			
							Tbl_ProductDescription::model()->deleteAll($criteria);
			
							Tbl_ProductImage::model()->deleteAll($criteria);
							
							Tbl_ProductReview::model()->deleteAll($criteria);
							
							
							
							// product options					
							$command_delete_options->execute(array(':id_product'=>$id));							
							
							// product variants
							foreach (Tbl_ProductVariantGroup::model()->findAll($criteria) as $row) {
								foreach ($row->tbl_product_variant_group_option as $row_option) {
									$this->delete_variant_group_option_swatch_image($row_option->id);
									//$this->delete_image_variant_group_option($row_option->id);		
								}
							}		
							
							foreach (Tbl_ProductVariant::model()->findAll($criteria) as $row) {
								$this->delete_image_variant($row->id);		
							}							
												
							$command_delete_variants->execute(array(':id_product'=>$id));	
							
							
							// Delete video streaming
							$file_base_path = dirname(Yii::app()->getBasePath()).'/protected/streaming_videos/';
							// prepare delete videos
							$command_delete_video=$connection->createCommand('DELETE FROM 
							product_downloadable_videos,
							product_downloadable_videos_description
							USING
							product_downloadable_videos
							INNER JOIN
							product_downloadable_videos_description
							ON
							(product_downloadable_videos.id = product_downloadable_videos_description.id_product_downloadable_videos)
							WHERE 
							product_downloadable_videos.id_product=:id_product');								
							
							foreach (Tbl_ProductDownloadableVideos::model()->findAll($criteria) as $row) {				
								
								if (is_file($file_base_path.$row['source'])) @unlink($file_base_path.$row['source']);
								
								$command_delete_video->execute(array(':id_product'=>$id));		
							}
							
							// Delete file
							$file_base_path = dirname(Yii::app()->getBasePath()).'/protected/downloadable_files/';
							// prepare delete videos
							$command_delete_file=$connection->createCommand('DELETE FROM 
							product_downloadable_videos,
							product_downloadable_videos_description
							USING
							product_downloadable_videos
							INNER JOIN
							product_downloadable_videos_description
							ON
							(product_downloadable_videos.id = product_downloadable_videos_description.id_product_downloadable_videos)
							WHERE 
							product_downloadable_videos.id_product=:id_product');								
							
							foreach (Tbl_ProductDownloadableFiles::model()->findAll($criteria) as $row) {				
								
								if (is_file($file_base_path.$row['source'])) @unlink($file_base_path.$row['source']);
								
								$command_delete_file->execute(array(':id_product'=>$id));		
							}
							
							
							
							
							
						}
						
						// Delete even if archive
						Tbl_ProductPriceTier::model()->deleteAll($criteria);	
						Tbl_ProductRelated::model()->deleteAll($criteria);
						Tbl_ProductTag::model()->deleteAll($criteria);
						Tbl_ProductCategory::model()->deleteAll($criteria);
						Tbl_ProductPriceShippingRegion::model()->deleteAll($criteria);
						Tbl_ProductShipOnlyRegion::model()->deleteAll($criteria);
						Tbl_ProductDoNotShipRegion::model()->deleteAll($criteria);
						
						$criteria2=new CDbCriteria; 
						$criteria2->condition='id_product_related=:id_product_related'; 
						$criteria2->params=array(':id_product_related'=>$id); 					
						Tbl_ProductRelated::model()->deleteAll($criteria2);
						Tbl_ProductSuggestion::model()->deleteAll($criteria);
						
						$criteria2->condition='id_product_suggestion=:id_product_suggestion'; 
						$criteria2->params=array(':id_product_suggestion'=>$id); 					
						Tbl_ProductSuggestion::model()->deleteAll($criteria2);
						
						// Delete if product is in a bundled	
						Tbl_ProductBundledProductGroupProduct::model()->deleteAll($criteria);
						
						// Delete if product is in a combo
						$criteria->condition='id_combo_product=:id_combo_product'; 
						$criteria->params=array(':id_combo_product'=>$id); 	
						Tbl_ProductCombo::model()->deleteAll($criteria);
						

					}
				}
			}

	}		 
	
	/**
	 * This is the action to toggle product active status
	 */
	public function actionToggle_active()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id = (int)$_POST['id'];
		$active = ($_POST['active']=='true'?1:0);
		$pass = (int)$_POST['pass'];
		
		if ($p = Tbl_Product::model()->findByPk($id)) {
			if(!$pass){
				$arr_result['in_other_product'] = 0;
				// Verify if product is in a combo
				$criteria=new CDbCriteria; 
				$criteria->condition='id_combo_product=:id_combo_product'; 
				$criteria->params=array(':id_combo_product'=>$id); 	
				if (Tbl_ProductCombo::model()->find($criteria)) {
					$arr_result['in_other_product'] = 1;
				}
				// Verify if product is in a bundled
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product'; 
				$criteria->params=array(':id_product'=>$id); 	
				if (Tbl_ProductBundledProductGroupProduct::model()->find($criteria)) {
					$arr_result['in_other_product'] = 1;
				}
				header('Content-Type: text/javascript; charset=UTF-8'); //set header
				echo json_encode($arr_result); //display records in json format using json_encode
			}else{
				if(!$active){
					// Deactivate combo
					$criteria=new CDbCriteria; 
					$criteria->condition='id_combo_product=:id_combo_product'; 
					$criteria->params=array(':id_combo_product'=>$id); 	
					foreach (Tbl_ProductCombo::model()->findAll($criteria) as $row) {
						$criteria2=new CDbCriteria; 
						$criteria2->condition='id=:id'; 
						$criteria2->params=array(':id'=>$row['id_product']); 	
						Tbl_Product::model()->updateAll(array('active'=>$active),$criteria2);	
					}
					// Deactivate bundled
					$sql = 'SELECT
					id_product_bundled_product_group
					FROM
					product_bundled_product_group_product
					WHERE id_product = "' .$id . '"
					GROUP BY id_product_bundled_product_group';
					$command=$connection->createCommand($sql);
					
					foreach ($command->queryAll(true) as $row) {
						$sql = 'UPDATE
						product
						SET 
						active = "'.$active.'" 
						WHERE
						id IN (SELECT id_product FROM product_bundled_product_group WHERE id = "'.$row['id_product_bundled_product_group'].'")';
						$command=$connection->createCommand($sql);						
						$command->execute();
							
					}
				}

				$p->active = $active;
				if (!$p->save()) {
					throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
				}
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
	
	/**
	 * This is the action to toggle product featured option
	 */
	public function actionToggle_featured()
	{
		$id = (int)$_POST['id'];
		$active = ($_POST['active']=='true'?1:0);
		
		if ($p = Tbl_Product::model()->findByPk($id)) {
			$p->featured = $active;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}			
	
	/** 
	 *
	 */
	public function actionXml_list_info_description($container, $id=0)
	{
		$model = new ProductsForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($product = Tbl_Product::model()->findByPk($id)) {							
				// grab description information 
				foreach ($product->tbl_product_description as $row) {
					$model->product_description[$row->language_code]['name'] = $row->name;
					$model->product_description[$row->language_code]['short_desc'] = $row->short_desc;
					$model->product_description[$row->language_code]['description'] = $row->description;
					$model->product_description[$row->language_code]['meta_description'] = $row->meta_description;
					$model->product_description[$row->language_code]['meta_keywords'] = $row->meta_keywords;
					$model->product_description[$row->language_code]['alias'] = $row->alias;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_ProductDescription::tableName());		
		
		$help_hint_path = '/catalog/products/information/';
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';
		
		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:auto;" class="div_'.$container.'">	
					<div style="padding:10px;">			
					<div class="row">
						<strong>'.Yii::t('global','LABEL_NAME').'</strong>'.
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_product_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->product_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'name').'
						<div>'.
						CHtml::activeTextField($model,'product_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'onblur'=>'rewrite_alias($(this).attr("id"),"'.$container.'_product_description['.$value->code.'][alias]");', 'id'=>$container.'_product_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_product_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION_SHORT').'</strong>'.
						(isset($columns['short_desc']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_product_description['.$value->code.'][short_desc]_maxlength">'.($columns['short_desc']-strlen($model->product_description[$value->code]['short_desc'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'short-desc').'
						<div>'.
						CHtml::activeTextArea($model,'product_description['.$value->code.'][short_desc]',array('style' => 'width: 98%;', 'rows' => 2,'maxlength'=>$columns['short_desc'], 'id'=>$container.'_product_description['.$value->code.'][short_desc]')).'
						<br /><span id="'.$container.'_product_description['.$value->code.'][short_desc]_errorMsg" class="error"></span>
						</div>
					</div> 
					
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'description').'
						<div>'.
						CHtml::activeTextArea($model,'product_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'class' => 'editor', 'rows' => 6, 'id'=>$container.'_product_description['.$value->code.'][description]')).'
						<br /><span id="'.$container.'_product_description['.$value->code.'][description]_errorMsg" class="error"></span>
						</div>
					</div>	 
					
					<h1>'.Yii::t('global','LABEL_TITLE_SEO').'</h1>
					
					
					<div class="row">
						<strong>'.Yii::t('global','LABEL_META_DESCRIPTION').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'meta-description').
						(isset($columns['meta_description']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_product_description['.$value->code.'][meta_description]_maxlength">'.($columns['meta_description']-strlen($model->product_description[$value->code]['meta_description'])).'</span>)</em>':'').'
						<div>'.
						CHtml::activeTextArea($model,'product_description['.$value->code.'][meta_description]',array('style' => 'width: 98%;', 'rows' => 2,'maxlength'=>$columns['meta_description'], 'id'=>$container.'_product_description['.$value->code.'][meta_description]')).'
						<br /><span id="'.$container.'_product_description['.$value->code.'][meta_description]_errorMsg" class="error"></span>
						</div>
					</div>  
					<div class="row">
						<strong>'.Yii::t('global','LABEL_META_KEYWORDS').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'meta-keywords').
						(isset($columns['meta_keywords']) ? '&nbsp;<em>(Maxlength: <span id="'.$container.'_product_description['.$value->code.'][meta_keywords]_maxlength">'.($columns['meta_keywords']-strlen($model->product_description[$value->code]['meta_keywords'])).'</span>)</em>':'').'
						<div>'.
						CHtml::activeTextArea($model,'product_description['.$value->code.'][meta_keywords]',array('style' => 'width: 98%;', 'rows' => 2,'maxlength'=>$columns['meta_keywords'], 'id'=>$container.'_product_description['.$value->code.'][meta_keywords]')).'
						<br /><span id="'.$container.'_product_description['.$value->code.'][meta_keywords]_errorMsg" class="error"></span>
						</div>
					</div>   
					<div class="row">
						<strong>'.Yii::t('global','LABEL_ALIAS').'</strong> ('.Yii::t('global','LABEL_ALIAS_CHARACTERS_ALLOWED').'):'.
						(isset($columns['alias']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_product_description['.$value->code.'][alias]_maxlength">'.($columns['alias']-strlen($model->product_description[$value->code]['alias'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'alias').'
						<div>'.
						CHtml::activeTextField($model,'product_description['.$value->code.'][alias]',array('style' => 'width: 98%;','maxlength'=>$columns['alias'], 'onkeyup'=>'rewrite_alias($(this).attr("id"),"");', 'id'=>$container.'_product_description['.$value->code.'][alias]')).'
						<br /><span id="'.$container.'_product_description['.$value->code.'][alias]_errorMsg" class="error"></span>
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
	 * This is the action to get a list of brands
	 */ 
	public function actionXml_list_brand($pos=0,$mask='')
	{
		$pos = (int)$pos;
		
		header ("content-type: text/xml");
	
		echo '<?xml version="1.0" encoding="utf-8"?>';
		$criteria=new CDbCriteria; 
		$criteria->select='brand';
		$criteria->condition='brand LIKE CONCAT(:brand,"%")'; 
		$criteria->params=array(':brand'=>$mask);
		$criteria->group='brand';
		$criteria->order='brand ASC';
		
		echo '<complete>';
		
		foreach (Tbl_Product::model()->findAll($criteria) as $row) {
			echo '<option value="'.$row->brand.'">'.$row->brand.'</option>';
		}
		
		echo '</complete>';
		
	}
	
	/**
	 * This is the action to get a list of models
	 */ 
	public function actionXml_list_model($pos=0,$mask='')
	{
		$pos = (int)$pos;

		header ("content-type: text/xml");
	
		echo '<?xml version="1.0" encoding="utf-8"?>';
		$criteria=new CDbCriteria; 
		$criteria->select='model';
		$criteria->condition='model LIKE CONCAT(:model,"%")'; 
		$criteria->params=array(':model'=>$mask);
		$criteria->group='model';
		$criteria->order='model ASC';
		
		echo '<complete>';
		
		foreach (Tbl_Product::model()->findAll($criteria) as $row) {
			echo '<option value="'.$row->model.'">'.$row->model.'</option>';
		}
		
		echo '</complete>';
	
	}
	
	/************************************************************
	*															*
	*															*
	*						PRICE TIERS							*
	*															*
	*															*
	************************************************************/	
	
	public function actionEdit_price_tiers_options($container, $id_product=0)
	{
		$model = new ProductsPriceTiersForm;
		
		$id_product = (int)$id_product;
		$id = (int)$_POST['id'];
		
		$model->id_product = $id_product;
	
		if ($id) {
			if ($ppp = Tbl_ProductPriceTier::model()->findByPk($id)) {
				$model->id = $ppp->id;
				$model->id_product = $ppp->id_product;
				$model->id_customer_type = $ppp->id_customer_type;
				$model->qty = $ppp->qty;
				$model->price = $ppp->price;				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		
		}
		
		$this->renderPartial('edit_price_tiers_options',array('model'=>$model, 'container'=>$container));	
	}		
	
	/**
	 * This is the action to save
	 */
	public function actionSave_price_tier()
	{
		$model = new ProductsPriceTiersForm;
		
		// collect user input data
		if(isset($_POST['ProductsPriceTiersForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsPriceTiersForm'] as $name=>$value)
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
	
	/**
	 * This is the action to delete 
	 */
	public function actionDelete_price_tier()
	{
		// current category
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id); 					
				
				// delete all
				Tbl_ProductPriceTier::model()->deleteAll($criteria);						
			}
		}
	}		
	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_price_tiers($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id = (int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('product_price_tier.id_product=:id_product');
		$params=array(':id_product'=>$id);
		
		// filters							
		
		$sql = "SELECT 
		COUNT(product_price_tier.id) AS total 
		FROM 
		product_price_tier 
		LEFT JOIN 
		customer_type 
		ON 
		(product_price_tier.id_customer_type = customer_type.id) 
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
		product_price_tier.id,
		product_price_tier.id_product,
		product_price_tier.id_customer_type,
		product_price_tier.qty,
		product_price_tier.price,
		customer_type.name
		FROM 
		product_price_tier 
		LEFT JOIN 
		customer_type 
		ON 
		(product_price_tier.id_customer_type = customer_type.id) 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		$sql.=" ORDER BY 
		customer_type.name ASC,
		product_price_tier.qty ASC,
		product_price_tier.price ASC";
		
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
			<cell type="ro"><![CDATA['.($row['name'] ? $row['name']:Yii::t('global','LABEL_PROMPT')).']]></cell>
			<cell type="ro">'.$row['qty'].'</cell>
			<cell type="ro">'.Html::nf($row['price']).'</cell>
			</row>';
		}
		
		echo '</rows>';
	}			
	
	/************************************************************
	*															*
	*															*
	*					SUGGESTED PRODUCTS						*
	*															*
	*															*
	************************************************************/
		
	
	public function actionXml_list_suggestion_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		if(isset($_GET['id_product'])){
			$id_product = (int)$_GET['id_product'];
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array('product_suggestion.id IS NULL');
			
			// filters
			
			// name
			if (isset($filters['name']) && !empty($filters['name'])) {
				$where[] = 'product_description.name LIKE CONCAT("%",:name,"%")';
				$params[':name']=$filters['name'];
			}
			// sku
			if (isset($filters['sku']) && !empty($filters['sku'])) {
				$where[] = 'product.sku LIKE CONCAT(:sku,"%")';
				$params[':sku']=$filters['sku'];
			}
			
			// product type
			if (isset($filters['product_type']) and $filters['product_type']>-1) {
				$where[] = 'product.product_type = :product_type';
				$params[':product_type']=$filters['product_type'];
			}
			
			$where[]='product.active=:active';
			$params[':active']="1";		
			
			$where[]='product.id!=:id_product';
			$params[':id_product']=$id_product;				
			
			$sql = "SELECT 
			COUNT(product.id) AS total  
			FROM 
			product 
			INNER JOIN 
			product_description 
			ON 
			(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			product_suggestion 
			ON 
			product.id=product_suggestion.id_product_suggestion AND product_suggestion.id_product = :id_product
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
			product.id AS id_product,
			product_description.name,
			product.active,
			product.sku,
			product.qty,
			product.notify,
			product.notify_qty,
			product.product_type,
			product.sell_price AS price,
			product_suggestion.id
			FROM 
			product 
			INNER JOIN 
			product_description 
			ON 
			(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			product_suggestion 
			ON 
			product.id=product_suggestion.id_product_suggestion AND product_suggestion.id_product = :id_product
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
				
			// sorting
			if (isset($sort_col[1]) && !empty($sort_col[1])) {	
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';	
				
				$sql .= " ORDER BY 
				product_description.name ".$direct;
			}else{
				if (isset($filters['name']) && !empty($filters['name'])) { 
					$sql.=" ORDER BY IF(product_description.name LIKE CONCAT(:name,'%'),0,1) ASC, product_description.name ASC";
				} else if (isset($filters['name']) && !empty($filters['name'])) { 
					$sql.=" ORDER BY IF(product.sku LIKE CONCAT(:sku,'%'),0,1) ASC, product.sku ASC";
				} else {
					$sql.=" ORDER BY product_description.name ASC";
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
				switch ($row['product_type']) {
					// product
					case 0:
						$product_type = Yii::t('global','LABEL_PRODUCT_TYPE_PRODUCT');
						break;
					// combo deal
					case 1:
						$product_type = Yii::t('global','LABEL_PRODUCT_TYPE_COMBO');
						break;
					// bundled products
					case 2:
						$product_type = Yii::t('global','LABEL_PRODUCT_TYPE_BUNDLED');
						break;	
				}
				echo '<row id="'.$row['id_product'].'" '.($row['active']?'':'class="innactive"').'>
				<cell type="ch" />
				<cell type="ro"><![CDATA['.$row['name'].']]></cell>
				<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
				<cell type="ro"><![CDATA['.$product_type.']]></cell>
				<cell type="ro"><![CDATA['.Html::nf($row['price']).']]></cell>
				</row>';
			}
			
			echo '</rows>';
			
		}
	}		
	
	public function actionAdd_suggestion()
	{
		
		// current product
		$id_product = $_POST['id_product'];
		$ids = $_POST['ids'];
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product'; 
		$criteria->params=array(':id_product'=>$id_product);
		$criteria->order='sort_order DESC';	
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_product_suggestion) {	
				$ps = new Tbl_ProductSuggestion;
				$ps->id_product = $id_product;
				$ps->id_product_suggestion = $id_product_suggestion;
				$ps->active = 1;
				$ps->sort_order = Tbl_ProductSuggestion::model()->find($criteria)->sort_order+1;
				if (!$ps->save()){
					echo 'false';
					exit;
				}		
			}
		}		
	}
	
	
	/**
	 * This is the action to delete a product suggestion
	 */
	public function actionDelete_suggestion()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id); 					
				
				// delete all
				Tbl_ProductSuggestion::model()->deleteAll($criteria);						
			}
		}
	}			
	
	/**
	 * This is the action to save suggestion order
	 */
	public function actionSave_suggestion_sort_order($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_product_suggestion) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND id=:id_product_suggestion'; 
				$criteria->params=array(':id_product'=>$id,':id_product_suggestion'=>$id_product_suggestion); 					
								
				if ($ps = Tbl_ProductSuggestion::model()->find($criteria)) {
					$ps->sort_order = $i;
					$ps->save();
					
					++$i;
				}
			}
		}
	}
	
	/**
	 * This is the action to get an XML list of suggested products
	 */
	public function actionXml_list_suggestion($id=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
		
		$where[] = 'product_suggestion.id_product=:id_product';
		$params[':id_product'] = $id;
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'product_description.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// sku
		if (isset($filters['sku']) && !empty($filters['sku'])) {
			$where[] = 'product.sku LIKE CONCAT(:sku,"%")';
			$params[':sku']=$filters['sku'];
		}							
		
		// product type
		if (isset($filters['product_type'])) {
			$where[] = 'product.product_type = :product_type';
			$params[':product_type']=$filters['product_type'];
		}
		
		$sql = "SELECT 
		COUNT(product.id) AS total 
		FROM 
		product_suggestion 
		INNER JOIN 
		product 
		ON 
		(product_suggestion.id_product_suggestion = product.id) 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
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
		product_suggestion.id,
		product.sku,
		product.qty,
		product.notify,
		product.notify_qty,
		product.special_price,
		product.special_price_from_date,
		product.special_price_to_date,
		product_description.name,
		product.active,
		product.product_type,
		product.sell_price AS price
		FROM 
		product_suggestion 
		INNER JOIN 
		product 
		ON 
		(product_suggestion.id_product_suggestion = product.id) 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		$sql.=" ORDER BY product_suggestion.sort_order ASC";
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			switch ($row['product_type']) {
					// product
					case 0:
						$product_type = Yii::t('global','LABEL_PRODUCT_TYPE_PRODUCT');
						break;
					// combo deal
					case 1:
						$product_type = Yii::t('global','LABEL_PRODUCT_TYPE_COMBO');
						break;
					// bundled products
					case 2:
						$product_type = Yii::t('global','LABEL_PRODUCT_TYPE_BUNDLED');
						break;	
				}
			echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
			<cell type="ro"><![CDATA['.$product_type.']]></cell>
			<cell type="ro"><![CDATA['.Html::nf($row['price']).']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/************************************************************
	*															*
	*															*
	*					RELATED PRODUCTS						*
	*															*
	*															*
	************************************************************/	
				
	
	public function actionXml_list_related_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		if(isset($_GET['id_product'])){
			$id_product = (int)$_GET['id_product'];
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array('product_related.id IS NULL AND product.product_type=0');
			
			// filters
			
			// name
			if (isset($filters['name']) && !empty($filters['name'])) {
				$where[] = 'product_description.name LIKE CONCAT("%",:name,"%")';
				$params[':name']=$filters['name'];
			}
			
			// sku
			if (isset($filters['sku']) && !empty($filters['sku'])) {
				$where[] = 'product.sku LIKE CONCAT(:sku,"%")';
				$params[':sku']=$filters['sku'];
			}
			
			
			$where[]='product.active=:active';
			$params[':active']="1";		
			
			$where[]='product.id!=:id_product';
			$params[':id_product']=$id_product;				
			
			$sql = "SELECT 
			COUNT(product.id) AS total  
			FROM 
			product 
			INNER JOIN 
			product_description 
			ON 
			(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			product_related
			ON 
			product.id=product_related.id_product_related AND product_related.id_product = :id_product
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
			product.id AS id_product,
			product_description.name,
			product.active,
			product.sku,
			product.qty,
			product.notify,
			product.notify_qty,
			product_related.id,
			product.product_type,
			product.sell_price AS price
			FROM 
			product 
			INNER JOIN 
			product_description 
			ON 
			(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			product_related
			ON 
			product.id=product_related.id_product_related AND product_related.id_product = :id_product
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
			
				
			// sorting

			if (isset($sort_col[1]) && !empty($sort_col[1])) {	
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';	
				
				$sql .= " ORDER BY 
				product_description.name ".$direct;
			}else{
				if (isset($filters['name']) && !empty($filters['name'])) { 
					$sql.=" ORDER BY IF(product_description.name LIKE CONCAT(:name,'%'),0,1) ASC, product_description.name ASC";
				} else if (isset($filters['name']) && !empty($filters['name'])) { 
					$sql.=" ORDER BY IF(product.sku LIKE CONCAT(:sku,'%'),0,1) ASC, product.sku ASC";
				} else {
					$sql.=" ORDER BY product_description.name ASC";
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
				
				echo '<row id="'.$row['id_product'].'" '.($row['active']?'':'class="innactive"').'>
				<cell type="ch" />
				<cell type="ro"><![CDATA['.$row['name'].']]></cell>
				<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
				<cell type="ro"><![CDATA['.Html::nf($row['price']).']]></cell>
				</row>';
			}
			
			echo '</rows>';
			
		}
	}
	
	public function actionAdd_related()
	{
		
		// current product
		$id_product = $_POST['id_product'];
		$ids = $_POST['ids'];
		
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = Yii::app()->user->getId();
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product'; 
		$criteria->params=array(':id_product'=>$id);
		$criteria->order='sort_order DESC';	
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_product_related) {	
				$ps = new Tbl_ProductRelated;
				$ps->id_product = $id_product;
				//Discount type to -1 = No discount
				$ps->discount_type = -1;
				$ps->id_product_related = $id_product_related;
				$ps->id_user_created = $current_id_user;			
				$ps->date_created = $current_datetime;	
				$ps->active = 1;
				$ps->sort_order = Tbl_ProductRelated::model()->find($criteria)->sort_order+1;
				if (!$ps->save()){
					echo 'false';
					exit;
				}		
			}
		}		
	}
	
	
	
	/**
	 * This is the action to delete a product suggestion
	 */
	public function actionDelete_related()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_product_related) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_product_related);					
				
				// delete all
				Tbl_ProductRelated::model()->deleteAll($criteria);						
			}
		}
	}			
	
	/**
	 * This is the action to save related order
	 */
	public function actionSave_related_sort_order($id=0)
	{
		// current product
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_product_related) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND id=:id_product_related'; 
				$criteria->params=array(':id_product'=>$id,':id_product_related'=>$id_product_related); 					
				if ($ps = Tbl_ProductRelated::model()->find($criteria)) {
					$ps->sort_order = $i;
					$ps->save();
					
					++$i;
				}
			}
		}
		
	}	
	
	/**
	 * This is the action to get an XML list of suggested products
	 */
	public function actionXml_list_related($id=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('product_related.id_product=:id_product');
		$params=array(':id_product'=>$id);
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'product_description.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// sku
		if (isset($filters['sku']) && !empty($filters['sku'])) {
			$where[] = 'product.sku LIKE CONCAT(:sku,"%")';
			$params[':sku']=$filters['sku'];
		}							
		
		$sql = "SELECT 
		COUNT(product.id) AS total 
		FROM 
		product_related 
		INNER JOIN 
		product 
		ON 
		(product_related.id_product_related = product.id) 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
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
		product_related.id,
		product.active,
		product.sku,
		product.price,
		product.special_price,
		product.product_type,
		product.special_price_from_date,
		product.special_price_to_date,
		product_description.name,
		product_related.discount_type,
		product_related.discount 
		FROM 
		product_related 
		INNER JOIN 
		product 
		ON 
		(product_related.id_product_related = product.id) 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		$sql.=" ORDER BY product_related.sort_order ASC";
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			switch($row['discount_type']){
				case '1':
					$discount_type = Yii::t('global','LABEL_PERCENTAGE');
					$discount_number = $row['discount'] . '%';
				break;	
				case '0':
					$discount_type = Yii::t('global','LABEL_FIXED');
					$discount_number = Html::nf($row['discount']);
				break;
				default:
					$discount_type = "---";
					$discount_number = "---";
				break;	
			}
			
			
			echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
			<cell type="ro"><![CDATA['.(($row['special_price'] > 0 and (($row['special_price_from_date'] <= date("Y-m-d H:i:s") and $row['special_price_to_date'] > date("Y-m-d H:i:s")) or ($row['special_price_from_date'] == "0000-00-00 00:00:00"))) ? Html::nf($row['special_price']):Html::nf($row['price'])).']]></cell>
			<cell type="ro"><![CDATA['.$row['product_type'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	/************************************************************
	*															*
	*															*
	*						INVENTORY							*
	*															*
	*															*
	************************************************************/
	
	
	public function actionEdit_inventory_options($container, $id=0)
	{
		$model = new ProductsInventoryForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($product = Tbl_Product::model()->findByPk($id)) {
				$model->id = $product->id;
				$model->track_inventory = $product->track_inventory;
				$model->in_stock = $product->in_stock;
				$model->qty = $product->qty;	
				$model->out_of_stock = $product->out_of_stock;
				$model->out_of_stock_enabled = $product->out_of_stock_enabled;	
				$model->notify = $product->notify;	
				$model->notify_qty = $product->notify_qty;	
				$model->allow_backorders = $product->allow_backorders;	
				
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product'; 
				$criteria->params=array(':id_product'=>$id); 		
				$model->product_has_variant = Tbl_ProductVariant::model()->count($criteria);
					
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_inventory_options',array('model'=>$model, 'container'=>$container));	
	}			
	
	/**
	 * This is the action to save the inventory section	 
	 */
	public function actionSave_inventory()
	{
		$model = new ProductsInventoryForm;
		
		// collect user input data
		if(isset($_POST['ProductsInventoryForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsInventoryForm'] as $name=>$value)
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
	
	/************************************************************
	*															*
	*															*
	*						SHIPPING							*
	*															*
	*															*
	************************************************************/
	
	public function actionEdit_shipping_options($container, $containerLayout, $id=0)
	{
		$model = new ProductsShippingForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($product = Tbl_Product::model()->findByPk($id)) {
				$model->id = $product->id;
				$model->weight = $product->weight;
				$model->length = $product->length;
				$model->width = $product->width;
				$model->height = $product->height;
				$model->use_shipping_price = $product->use_shipping_price;
				$model->heavy_weight = $product->heavy_weight;
				$model->extra_care = $product->extra_care;
				$model->enable_local_pickup = $product->enable_local_pickup;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_shipping_options',array('model'=>$model, 'container'=>$container, 'containerLayout'=>$containerLayout));	
	}			
	
	/**
	 * This is the action to save the inventory section	 
	 */
	public function actionSave_shipping()
	{
		$model = new ProductsShippingForm;
		
		// collect user input data
		if(isset($_POST['ProductsShippingForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsShippingForm'] as $name=>$value)
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
	
	
	
	public function actionXml_list_price_shipping($id_product=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		$id_product=(int)$id_product;
		
		$where=array('product_price_shipping_region.id_product = ' . $id_product);
		$params=array(':language_code'=>Yii::app()->language);
									
		
		$sql = "SELECT 
		COUNT(product_price_shipping_region.id) AS total 
		FROM 
		product_price_shipping_region 
		LEFT JOIN 
		country_description
		ON
		(product_price_shipping_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(product_price_shipping_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
		product_price_shipping_region.id,
		product_price_shipping_region.price,
		country_description.name AS country,
		state_description.name AS state		
		FROM 
		product_price_shipping_region 
		LEFT JOIN 
		country_description
		ON
		(product_price_shipping_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(product_price_shipping_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
			<cell type="ro"><![CDATA['.Html::nf($row['price']).']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	public function actionDelete_region()
	{
		$ids = $_POST['ids'];
		$id_product = (int)$_POST['id_product'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_product_price_shipping_region) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_product_price_shipping_region);					
				
				// delete all
				Tbl_ProductPriceShippingRegion::model()->deleteAll($criteria);						
			}
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product=:id_product'; 
			$criteria->params=array(':id_product'=>$id_product); 		
			
			if (!Tbl_ProductPriceShippingRegion::model()->count($criteria)) {
				Tbl_Product::model()->updateByPk($id_product,array('use_shipping_price'=>0));
			}
		}
	}
	public function actionEdit_regions_options($container, $id=0)
	{
	
		$model = new ProductPriceShippingRegionsForm;
		$model->id_product = (int)$_POST["id_product"];
		$id = (int)$_POST["id"];		
		if ($id) {
			if ($price_shipping_region = Tbl_ProductPriceShippingRegion::model()->findByPk($id)) {
				$model->id = $price_shipping_region->id;
				$model->country_code = $price_shipping_region->country_code;
				$model->state_code = $price_shipping_region->state_code;
				$model->id_product = $price_shipping_region->id_product;	
				$model->price = $price_shipping_region->price;	
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_regions_options',array('model'=>$model,'container'=>$container));		
	}
	
	public function actionSave_regions_options()
	{
		$model = new ProductPriceShippingRegionsForm;
		
		// collect user input data
		if(isset($_POST['ProductPriceShippingRegionsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductPriceShippingRegionsForm'] as $name=>$value)
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
		$model=new ProductPriceShippingRegionsForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		$container = trim($_POST['container']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>$container.'_state_code'));
	}
	
	
	
	//Ship only into this region
	public function actionXml_list_ship_only_region($id_product=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		$id_product=(int)$id_product;
		
		$where=array('product_ship_only_region.id_product = ' . $id_product);
		$params=array(':language_code'=>Yii::app()->language);
									
		
		$sql = "SELECT 
		COUNT(product_ship_only_region.id) AS total 
		FROM 
		product_ship_only_region 
		LEFT JOIN 
		country_description
		ON
		(product_ship_only_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(product_ship_only_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
		product_ship_only_region.id,
		country_description.name AS country,
		state_description.name AS state		
		FROM 
		product_ship_only_region 
		LEFT JOIN 
		country_description
		ON
		(product_ship_only_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(product_ship_only_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
	
	public function actionDelete_ship_only_region()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_product_ship_only_region) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_product_ship_only_region);					
				
				// delete all
				Tbl_ProductShipOnlyRegion::model()->deleteAll($criteria);						
			}
		}
	}
	
	public function actionEdit_ship_only_region($container, $id=0)
	{
		$model = new ProductShipOnlyIntoThisRegionsForm;
		$model->id_product = (int)$_POST["id_product"];
		
		$id = (int)$_POST["id"];		
		if ($id) {
			if ($ship_only_region = Tbl_ProductShipOnlyRegion::model()->findByPk($id)) {
				$model->id = $ship_only_region->id;
				$model->country_code = $ship_only_region->country_code;
				$model->state_code = $ship_only_region->state_code;	
				$model->id_product = $ship_only_region->id_product;
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_ship_only_region',array('model'=>$model,'container'=>$container));
				
	}

	public function actionSave_ship_only_region()
	{
		$model = new ProductShipOnlyIntoThisRegionsForm;
		
		// collect user input data
		if(isset($_POST['ProductShipOnlyIntoThisRegionsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductShipOnlyIntoThisRegionsForm'] as $name=>$value)
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
	
	public function actionGet_province_list_ship_only_region()
	{
		$model=new ProductShipOnlyIntoThisRegionsForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		$container = trim($_POST['container']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>$container.'_state_code'));
	}
	//End Ship only into this region
	
	//Do not Ship into this region
	public function actionXml_list_do_not_ship_region($id_product=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		$id_product=(int)$id_product;
		
		$where=array('product_do_not_ship_region.id_product = ' . $id_product);
		$params=array(':language_code'=>Yii::app()->language);
									
		
		$sql = "SELECT 
		COUNT(product_do_not_ship_region.id) AS total 
		FROM 
		product_do_not_ship_region 
		LEFT JOIN 
		country_description
		ON
		(product_do_not_ship_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(product_do_not_ship_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
		product_do_not_ship_region.id,
		country_description.name AS country,
		state_description.name AS state		
		FROM 
		product_do_not_ship_region 
		LEFT JOIN 
		country_description
		ON
		(product_do_not_ship_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(product_do_not_ship_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
	
	public function actionDelete_do_not_ship_region()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_product_do_not_ship_region) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_product_do_not_ship_region);					
				
				// delete all
				Tbl_ProductDoNotShipRegion::model()->deleteAll($criteria);						
			}
		}
	}
	
	public function actionEdit_do_not_ship_region($container, $id=0)
	{
		$model = new ProductDoNotShipIntoThisRegionsForm;
		$model->id_product = (int)$_POST["id_product"];
		
		$id = (int)$_POST["id"];		
		if ($id) {
			if ($ship_only_region = Tbl_ProductDoNotShipRegion::model()->findByPk($id)) {
				$model->id = $ship_only_region->id;
				$model->country_code = $ship_only_region->country_code;
				$model->state_code = $ship_only_region->state_code;
				$model->id_product = $ship_only_region->id_product;	
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_do_not_ship_region',array('model'=>$model,'container'=>$container));	

	}
	
	public function actionSave_do_not_ship_region()
	{

		$model = new ProductDoNotShipIntoThisRegionsForm;
		
		// collect user input data
		if(isset($_POST['ProductDoNotShipIntoThisRegionsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductDoNotShipIntoThisRegionsForm'] as $name=>$value)
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
	
	public function actionGet_province_list_do_not_ship_region()
	{
		$model=new ProductDoNotShipIntoThisRegionsForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		$container = trim($_POST['container']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>$container.'_state_code'));
	}
	
	//End Do not Ship into this region
	
	
	
	
	
	
	
	
	
	/************************************************************
	*															*
	*															*
	*						IMAGES								*
	*															*
	*															*
	************************************************************/	
		
	public function actionEdit_images_upload($container, $id=0)
	{	
		$id = (int)$id;	
		$id_upload_button = $_POST['id_upload_button'];
	
		$this->renderPartial('edit_images_upload',array('container'=>$container, 'id'=>$id));	
	}		
	
	/**
	 * This is the action to upload images
	 */
	public function actionUpload_image($id=0)
	{					
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = $app->user->getId();	
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (!Tbl_Product::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		// if id is not set
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $app->params['product_images_base_path'];
			$targetFile = $_FILES['Filedata']['name'];
			$ext = strtolower(trim(pathinfo($targetFile, PATHINFO_EXTENSION)));
			$force_crop = 0;
			$allowed_ext = array(
				'gif',
				'jpeg',
				'jpg',
				'png',
			);									
			
			if (empty($ext) || !in_array($ext, $allowed_ext)) {
				echo Yii::t('global','ERROR_ALLOWED_IMAGES');				
				exit;
			} else {	
				// get current product image cover
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND cover=1'; 
				$criteria->params=array(':id_product'=>$id); 	
					
				$cover = Tbl_ProductImage::model()->count($criteria);
				
				$criteria->condition='id_product=:id_product'; 
				$criteria->order='sort_order DESC';			
						
				// insert new image
				$product_image = new Tbl_ProductImage;
				$product_image->id_product = $id;
				$product_image->sort_order = (Tbl_ProductImage::model()->find($criteria)->sort_order)+1;	
				$product_image->cover = $cover ? 0:1;	
				$product_image->id_user_created = $current_id_user;
				$product_image->id_user_modified = $current_id_user;				
				$product_image->date_created = $current_datetime;
				if (!$product_image->save()) {
					echo Yii::t('controllers/ProductsController','ERROR_SAVING');
					exit;
				}
				
				$id_image = $product_image->id;
				
				// original file renamed
				$original = md5($targetFile.time()).'.'.$ext;	
				$filename = md5($original).'.jpg';				
			
				$image = new SimpleImage();
				if (!$image->load($tempFile)) {
					// delete
					$product_image->delete();
					
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;
				}
			
				// save original
				if (!$image->save($targetPath.'original/'.$original)) {
					// delete
					$product_image->delete();
					
					echo Yii::t('global','ERROR_SAVE_ORIGINAL_FAILED');
					exit;				
				}
				
				$width = $image->getWidth();
				$height = $image->getHeight();
				
				if (!$width || !$height) {
					// delete
					$product_image->delete();
					
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;					
				}
				
				// vs
				switch ($app->params['images_orientation']) {
					case 'portrait':
						$default_zoom_width = $app->params['portrait_zoom_width'];
						$default_zoom_height = $app->params['portrait_zoom_height'];
		
						$default_cover_width = $app->params['portrait_cover_width'];
						$default_cover_height = $app->params['portrait_cover_height'];
						
						$default_listing_width = $app->params['portrait_listing_width'];
						$default_listing_height = $app->params['portrait_listing_height'];
		
						$default_suggest_width = $app->params['portrait_suggest_width'];
						$default_suggest_height = $app->params['portrait_suggest_height'];
						
						$default_thumb_width = $app->params['portrait_thumb_width'];
						$default_thumb_height = $app->params['portrait_thumb_height'];
					
						// if our image size is smaller than our min 800x600
						/*if ($width < $default_zoom_width || $height < $default_zoom_height) { 
							// delete
							$product_image->delete();				
						
							echo Yii::t('global', 'ERROR_MIN_IMAGE_RESOLUTION', array('{width}'=>$default_zoom_width,'{height}'=>$default_zoom_height));							
							exit;
						// ratio is not correct, force crop
						} else*/ if (($width/$height) != 0.75) {
							$force_crop = 1;
						}	
						break;
					case 'landscape':
						$default_zoom_width = $app->params['landscape_zoom_width'];
						$default_zoom_height = $app->params['landscape_zoom_height'];
		
						$default_cover_width = $app->params['landscape_cover_width'];
						$default_cover_height = $app->params['landscape_cover_height'];
						
						$default_listing_width = $app->params['landscape_listing_width'];
						$default_listing_height = $app->params['landscape_listing_height'];
		
						$default_suggest_width = $app->params['landscape_suggest_width'];
						$default_suggest_height = $app->params['landscape_suggest_height'];
						
						$default_thumb_width = $app->params['landscape_thumb_width'];
						$default_thumb_height = $app->params['landscape_thumb_height'];					
					
						// if our image size is smaller than our min 800x600
						/*if ($width < $default_zoom_width || $height < $default_zoom_height) { 
							// delete
							$product_image->delete();														
						
							echo Yii::t('global', 'ERROR_MIN_IMAGE_RESOLUTION', array('{width}'=>$default_zoom_width,'{height}'=>$default_zoom_height));							
							exit;
						// ratio is not correct, force crop
						} else*/ if (($height/$width) != 0.75) {
							$force_crop = 1;
						}					
						break;
				}
																		
				// save image ZOOM
				if ($width > $default_zoom_width && !$image->resizeToWidth($default_zoom_width)) {
				//if (!$image->resize($default_zoom_width,$default_zoom_height)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_ZOOM_FAILED');						
					exit;		
				} else if (!$image->save($targetPath.'zoom/'.$filename)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_SAVE_ZOOM_FAILED');						
					exit;									
				}
				
				// save image COVER
				if ($width > $default_cover_width && !$image->resizeToWidth($default_cover_width)) {
	//			if (!$image->resize($default_cover_width,$default_cover_height)) {
					// delete
					$product_image->delete();							
					
					echo Yii::t('global', 'ERROR_RESIZE_COVER_FAILED');					
					exit;									
				} else if (!$image->save($targetPath.'cover/'.$filename)) {
					// delete
					$product_image->delete();							
					
					echo Yii::t('global', 'ERROR_SAVE_COVER_FAILED');					
					exit;									
				}
				
				
				// save image LISTING
				if ($width > $height && !$image->resizeToWidth($default_listing_width)) {
					// delete
					$product_image->delete();									
					
					echo Yii::t('global', 'ERROR_RESIZE_LISTING_FAILED');				
					exit;				
				} else if ($height > $width && !$image->resizeToHeight($default_listing_height)) {															
					// delete
					$product_image->delete();									
					
					echo Yii::t('global', 'ERROR_RESIZE_LISTING_FAILED');				
					exit;			
				} else if ($width == $height && $default_listing_width < $default_listing_height && !$image->resizeToWidth($default_listing_width)) {
					// delete
					$product_image->delete();									
					
					echo Yii::t('global', 'ERROR_RESIZE_LISTING_FAILED');				
					exit;
				} else if ($width == $height && $default_listing_width > $default_listing_height && !$image->resizeToHeight($default_listing_height)) {
					// delete
					$product_image->delete();									
					
					echo Yii::t('global', 'ERROR_RESIZE_LISTING_FAILED');				
					exit;
				} else if (!$image->save($targetPath.'listing/'.$filename)) {
					// delete
					$product_image->delete();									
					
					echo Yii::t('global', 'ERROR_RESIZE_LISTING_FAILED');				
					exit;				
				}

				// save image SUGGEST
				if ($width > $height && !$image->resizeToWidth($default_suggest_width)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_SUGGEST_FAILED');				
					exit;				
				} else if ($height > $width && !$image->resizeToHeight($default_suggest_height)) {																					
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_SUGGEST_FAILED');				
					exit;			
				} else if ($width == $height && $default_suggest_width < $default_suggest_height && !$image->resizeToWidth($default_suggest_width)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_SUGGEST_FAILED');				
					exit;	
				} else if ($width == $height && $default_suggest_width > $default_suggest_height && !$image->resizeToHeight($default_suggest_height)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_SUGGEST_FAILED');				
					exit;	
				} else if (!$image->save($targetPath.'suggest/'.$filename)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_SUGGEST_FAILED');				
					exit;				
				}
				
				// save image THUMB
				if ($width > $height && !$image->resizeToWidth($default_thumb_width)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_THUMB_FAILED');				
					exit;					
				} else if ($height > $width && !$image->resizeToHeight($default_thumb_height)) {																										
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_THUMB_FAILED');				
					exit;				
				} else if ($width == $height && $default_thumb_width < $default_thumb_height && !$image->resizeToWidth($default_thumb_width)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_THUMB_FAILED');				
					exit;	
				} else if ($width == $height && $default_thumb_width > $default_thumb_height && !$image->resizeToHeight($default_thumb_height)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_THUMB_FAILED');				
					exit;	
				} else if (!$image->save($targetPath.'thumb/'.$filename)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_THUMB_FAILED');				
					exit;			
				}		

				
				// free up memory
				$image->destroy();
				
				// update image 
				$product_image->force_crop = $force_crop;
				$product_image->original = $original;
				$product_image->filename = $filename;
				$product_image->save();

				echo 'true';
				exit;
				break;
			}
			
			echo Yii::t('global','ERROR_UPLOAD_IMAGE_FAILED');
		}			
	}			
	
	/**
	 * This is the action to load the crop tool
	 */
	public function actionEdit_images_crop($container, $id_product=0)
	{
		$id_product = (int)$id_product;
		$id = (int)$_POST['id'];	
		$rotate = (int)$_POST['rotate'];
	
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product AND id=:id'; 
		$criteria->params=array(':id_product'=>$id_product,':id'=>$id); 			
		
		if (!$product_image = Tbl_ProductImage::model()->find($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}				
	
		$this->renderPartial('edit_images_crop',array('container'=>$container, 'product_image'=>$product_image, 'rotate'=>$rotate));				
	}
	
	/** 
	 * This is the action to load the image we want to crop
	 */
	public function actionEdit_images_crop_load_image($id_product=0, $id_product_image=0, $rotate=0)
	{
		$id_product = (int)$id_product;
		$id_product_image = (int)$id_product_image;
		$rotate = (int)$rotate;
		$image_base_path = Yii::app()->params['product_images_base_path'];
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product AND id=:id'; 
		$criteria->params=array(':id_product'=>$id_product,':id'=>$id_product_image); 			
		
		if (!$product_image = Tbl_ProductImage::model()->find($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
		$image = new SimpleImage();
		if (!$image->load($image_base_path.'original/'.$product_image->original)) {		
			echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
			exit;
		}
		
		if ($rotate) {
			if ($rotate == -360 || $rotate == 360) { $rotate = 0; }
			else if (!$image->rotate($rotate)) {
				throw new CException(Yii::t('global','ERROR_ROTATE_FAILED'));
			}
		}				
		
		// Set the content type header - in this case image/jpeg
		header('Content-Type: image/jpeg');
		
		// Output the image
		imagejpeg($image->image);			
		
		// Free up memory
		imagedestroy($image->image);		
	}
	
	/**
	 * This is the action to crop and save image
	 */
	public function actionCrop_and_save()
	{
		$id = (int)$_POST['id'];
		$x = (int)$_POST['x'];
		$y = (int)$_POST['y'];
		$w = (int)$_POST['w'];
		$h = (int)$_POST['h'];
		$rotate = (int)$_POST['rotate'];
		
		if ($w && ($product_image = Tbl_ProductImage::model()->findByPk($id))) {
			$app = Yii::app();
			$original = $product_image->original;
			$filename = $product_image->filename;
			$image_base_path = $app->params['product_images_base_path'];
			
			switch ($app->params['images_orientation']) {
				case 'portrait':	
					$default_zoom_width = $app->params['portrait_zoom_width'];
					$default_zoom_height = $app->params['portrait_zoom_height'];
	
					$default_cover_width = $app->params['portrait_cover_width'];
					$default_cover_height = $app->params['portrait_cover_height'];
					
					$default_listing_width = $app->params['portrait_listing_width'];
					$default_listing_height = $app->params['portrait_listing_height'];
	
					$default_suggest_width = $app->params['portrait_suggest_width'];
					$default_suggest_height = $app->params['portrait_suggest_height'];
					
					$default_thumb_width = $app->params['portrait_thumb_width'];
					$default_thumb_height = $app->params['portrait_thumb_height'];						
					break;
				case 'landscape':
					$default_zoom_width = $app->params['landscape_zoom_width'];
					$default_zoom_height = $app->params['landscape_zoom_height'];
	
					$default_cover_width = $app->params['landscape_cover_width'];
					$default_cover_height = $app->params['landscape_cover_height'];
					
					$default_listing_width = $app->params['landscape_listing_width'];
					$default_listing_height = $app->params['landscape_listing_height'];
	
					$default_suggest_width = $app->params['landscape_suggest_width'];
					$default_suggest_height = $app->params['landscape_suggest_height'];
					
					$default_thumb_width = $app->params['landscape_thumb_width'];
					$default_thumb_height = $app->params['landscape_thumb_height'];						
					break;
			}
			
			$image = new SimpleImage();
			if (!$image->load($image_base_path.'original/'.$original)) {
				echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');				
				exit;
			}		
			
			if ($rotate != -360 && $rotate != 360 && $rotate) { 
				$image->rotate($rotate);
			}
						
			// crop and save ZOOM
			/*if (!$image->crop($x, $y, $w, $h, $default_zoom_width,$default_zoom_height) || !$image->save($image_base_path.'zoom/'.$filename)) {
				echo Yii::t('global','ERROR_CROP_FAILED');	
				exit;		
			}
			
			// crop and save COVER
			if (!$image->resize($default_cover_width,$default_cover_height) || !$image->save($image_base_path.'cover/'.$filename)) {
				echo Yii::t('global','ERROR_RESIZE_COVER_FAILED');
				exit;									
			}*/
			
			// crop and save LISTING
			if (!$image->crop($x, $y, $w, $h, $default_listing_width,$default_listing_height) || !$image->save($image_base_path.'listing/'.$filename)) {
			//if (!$image->resize($default_listing_width,$default_listing_height) || !$image->save($image_base_path.'listing/'.$filename)) {
				echo Yii::t('global','ERROR_RESIZE_LISTING_FAILED');
				exit;									
			}

			// crop and save SUGGEST
			if (!$image->resize($default_suggest_width,$default_suggest_height) || !$image->save($image_base_path.'suggest/'.$filename)) {
				echo Yii::t('global','ERROR_RESIZE_SUGGEST_FAILED');		
				exit;									
			}
			
			// crop and save THUMB
			if (!$image->resize($default_thumb_width,$default_thumb_height) || !$image->save($image_base_path.'thumb/'.$filename)) {
				echo Yii::t('global','ERROR_RESIZE_THUMB_FAILED');
				exit;	
			}

			// free up memory
			$image->destroy();		
			
			$product_image->force_crop = 0;
			$product_image->save();

			echo 'true';
		} else { 
			echo Yii::t('global','ERROR_CROP_FAILED');
		}
	}	
	
	/**
	 * This is the action to set cover image
	 */
	public function actionSet_cover_image($id=0)
	{
		$id = (int)$id;
		$id_product_image = (int)$_POST['id_product_image'];
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product AND id=:id_product_image'; 
		$criteria->params=array(':id_product'=>$id,':id_product_image'=>$id_product_image); 	
		
		if ($pi = Tbl_ProductImage::model()->find($criteria)) {
			$pi->cover = 1;
			if ($pi->save()) {
				$criteria->condition='id_product=:id_product AND id!=:id_product_image'; 
				
				Tbl_ProductImage::model()->updateAll(array('cover'=>0),$criteria);
			}
		}
	}
	
	/**
	 * This is the action to save images sort order
	 */
	public function actionSave_image_sort_order($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_product_image) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND id=:id_product_image'; 
				$criteria->params=array(':id_product'=>$id,':id_product_image'=>$id_product_image); 				
								
				if ($pi = Tbl_ProductImage::model()->find($criteria)) {
					$pi->sort_order = $i;
					$pi->save();
					
					++$i;
				}
			}
		}		
	}
	
	/**
	 * This is the action to delete images
	 */
	public function actionDelete_image($id=0)
	{
		$id = (int)$id;
		// ids
		$ids = $_POST['ids'];
		
		// images base path
		$images_path = Yii::app()->params['product_images_base_path'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_product_image) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND id=:id_product_image'; 
				$criteria->params=array(':id_product'=>$id,':id_product_image'=>$id_product_image); 	
				
				if ($product_image = Tbl_ProductImage::model()->find($criteria)) {
					$id_product = $product_image->id_product;
					$original = $product_image->original;
					$filename = $product_image->filename;
					
					// delete files
					if (is_file($images_path.'original/'.$original)) { @unlink($images_path.'original/'.$original); }
					if (is_file($images_path.'cover/'.$filename)) { @unlink($images_path.'cover/'.$filename); }
					if (is_file($images_path.'listing/'.$filename)) { @unlink($images_path.'listing/'.$filename); }
					if (is_file($images_path.'suggest/'.$filename)) { @unlink($images_path.'suggest/'.$filename); }
					if (is_file($images_path.'thumb/'.$filename)) { @unlink($images_path.'thumb/'.$filename); }
					if (is_file($images_path.'zoom/'.$filename)) { @unlink($images_path.'zoom/'.$filename); }
					
					$product_image->delete();					
				} else { 
					throw new CException(Yii::t('global','ERROR_EDIT_IMAGE_FAILED'));
				}
			}
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product=:id_product AND cover=1'; 
			$criteria->params=array(':id_product'=>$id); 	
				
			if (!Tbl_ProductImage::model()->count($criteria)) {
				$criteria->condition='id_product=:id_product'; 
				$criteria->order='sort_order ASC';
				$criteria->limit=1;
					
				Tbl_ProductImage::model()->updateAll(array('cover'=>1),$criteria);
			}	
		}
	}		
	
	/**
	 * This is the action to get an XML list of the product images
	 */
	public function actionXml_list_product_images($id=0)
	{
		
		$app = Yii::app();
		
		switch ($app->params['images_orientation']) {
			case 'portrait':
				
				$default_listing_width = $app->params['portrait_listing_width'];
				$default_listing_height = $app->params['portrait_listing_height'];

				break;
			case 'landscape':
				
				$default_listing_width = $app->params['landscape_listing_width'];
				$default_listing_height = $app->params['landscape_listing_height'];
				
				break;
		}
		
		$id = (int)$id;
		
		if (!$product = Tbl_Product::model()->findByPk($id)) {			
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}			
		
			
		//set content type and xml tag
		header("Content-type:text/xml");		
		
		echo '<data>';
		
		foreach ($product->tbl_product_image(array('order'=>'sort_order ASC')) as $row) {
			if (is_file(dirname(__FILE__) . '/../../../images/products/thumb/'.$row->filename)){
			   $image_src = $row->filename;
			   list($width, $height, $type, $attr) = getimagesize(dirname(__FILE__).'/../../../images/products/thumb/'.$image_src);
			}else{ 
				$width = $default_listing_width;
				$height = $default_listing_height;
			}
			
			echo '<item id="'.$row->id.'">
				<filename><![CDATA['.$row->filename.'?'.time().']]></filename>
				<force_crop><![CDATA['.$row->force_crop.']]></force_crop>
				<cover><![CDATA['.$row->cover.']]></cover>
				<width_current><![CDATA['.$width.']]></width_current>
				<height_current><![CDATA['.$height.']]></height_current>
			</item>';	
		}
		
		echo '</data>';
	}	
	
	
	/************************************************************
	*															*
	*															*
	*						VARIANTS							*
	*															*
	*															*
	************************************************************/
	
	public function actionEdit_variants_group($container, $id_product=0)
	{
		$model = new ProductsVariantGroupForm;
		
		$id = (int)$_POST['id'];
		$id_product = (int)$id_product;
		
		$model->id_product = $id_product;
		
		if ($id) {
			if ($pvg = Tbl_ProductVariantGroup::model()->findByPk($id)) {
				$model->id = $pvg->id;
				$model->id_product = $pvg->id_product;
				$model->input_type = $pvg->input_type;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_variants_group',array('model'=>$model, 'container'=>$container));	
	}		
	
	public function actionSave_variant_group()
	{
		$model = new ProductsVariantGroupForm;
		
		// collect user input data
		if(isset($_POST['ProductsVariantGroupForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsVariantGroupForm'] as $name=>$value)
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
	
	public function actionDelete_variant_group()
	{
		// CANT DELETE GROUP IF AN ORDER WAS ALREADY MADE USING THIS VARIANT GROUP
		
		$ids = $_POST['ids'];
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$pass = (int)$_POST['pass'];
		$id_product = (int)$_POST['id_product'];

		if (is_array($ids) && sizeof($ids)) {
				if(!$pass){
					$arr_result['in_other_product'] = 0;
					// Verify if product is in a combo
					foreach ($ids as $id) {
						$sql = 'SELECT
						product_combo_variant.id
						FROM
						product_combo_variant
						INNER JOIN
						product_variant
						ON
						product_combo_variant.id_product_variant = product_variant.id
						INNER JOIN
						product_variant_option
						ON
						product_variant.id = product_variant_option.id_product_variant
						INNER JOIN
						product_variant_group
						ON
						product_variant_option.id_product_variant_group = product_variant_group.id AND product_variant_group.id = "'.$id.'"
						LIMIT 1';
						$command=$connection->createCommand($sql);
						
						if($command->queryAll(true)) {
							$arr_result['in_other_product'] = 1;
							break;	
						}
						
						// Verify if product is in a bundled
						$sql = 'SELECT
						product_bundled_product_group_product.id
						FROM
						product_bundled_product_group_product
						INNER JOIN
						product_variant
						ON
						product_bundled_product_group_product.id_product_variant = product_variant.id
						INNER JOIN
						product_variant_option
						ON
						product_variant.id = product_variant_option.id_product_variant
						INNER JOIN
						product_variant_group
						ON
						product_variant_option.id_product_variant_group = product_variant_group.id AND product_variant_group.id = "'.$id.'"
						LIMIT 1';
						$command=$connection->createCommand($sql);
						
						if($command->queryAll(true)) {
							$arr_result['in_other_product'] = 1;
							break;	
						}
					}
					header('Content-Type: text/javascript; charset=UTF-8'); //set header
					echo json_encode($arr_result); //display records in json format using json_encode
				}else{// prepare delete groups and options

					$command_delete_groups_options=$connection->createCommand('DELETE FROM 
					product_variant_group_option,
					product_variant_group_option_description
					USING 
					product_variant_group_option 
					LEFT JOIN 
					product_variant_group_option_description 
					ON
					(product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option) 
					WHERE 
					product_variant_group_option.id_product_variant_group=:id_product_variant_group');	
					
					// prepare delete variants 
					$command_delete_variants=$connection->createCommand('DELETE FROM 
					product_variant,
					product_variant_option,
					product_variant_option2,
					product_combo_variant,
					product_bundled_product_group_product
					USING 
					product_variant 
					LEFT JOIN 
					product_variant_option 
					ON
					(product_variant.id = product_variant_option.id_product_variant) 
					LEFT JOIN 
					product_variant_option AS product_variant_option2
					ON 
					(product_variant.id = product_variant_option2.id_product_variant)
					LEFT JOIN 
					product_combo_variant
					ON 
					(product_variant.id = product_combo_variant.id_product_variant)
					LEFT JOIN 
					product_bundled_product_group_product
					ON 
					(product_variant.id = product_bundled_product_group_product.id_product_variant)
					WHERE 
					product_variant_option.id_product_variant_group=:id_product_variant_group');							
					
					foreach ($ids as $id) {
						// Deactivate combo
						$sql = 'SELECT
						product_combo_variant.id_product_combo
						FROM
						product_combo_variant
						INNER JOIN
						product_variant
						ON
						product_combo_variant.id_product_variant = product_variant.id
						INNER JOIN
						product_variant_option
						ON
						product_variant.id = product_variant_option.id_product_variant
						INNER JOIN
						product_variant_group
						ON
						product_variant_option.id_product_variant_group = product_variant_group.id AND product_variant_group.id = "'.$id.'"
						GROUP BY product_combo_variant.id_product_combo';
						$command=$connection->createCommand($sql);
						
						foreach ($command->queryAll(true) as $row) {
							$sql = 'UPDATE
							product
							SET 
							active = "0" 
							WHERE
							id IN (SELECT id_product FROM product_combo WHERE id = "'.$row['id_product_combo'].'")';
							$command=$connection->createCommand($sql);						
							$command->execute();
								
						}
						// Deactivate bundled
						$sql = 'SELECT
						product_bundled_product_group_product.id_product_bundled_product_group
						FROM
						product_bundled_product_group_product
						INNER JOIN
						product_variant
						ON
						product_bundled_product_group_product.id_product_variant = product_variant.id
						INNER JOIN
						product_variant_option
						ON
						product_variant.id = product_variant_option.id_product_variant
						INNER JOIN
						product_variant_group
						ON
						product_variant_option.id_product_variant_group = product_variant_group.id AND product_variant_group.id = "'.$id.'"
						GROUP BY product_bundled_product_group_product.id_product_bundled_product_group';
						$command=$connection->createCommand($sql);
						
						foreach ($command->queryAll(true) as $row) {
							$sql = 'UPDATE
							product
							SET 
							active = "0" 
							WHERE
							id IN (SELECT id_product FROM product_bundled_product_group WHERE id = "'.$row['id_product_bundled_product_group'].'")';
							$command=$connection->createCommand($sql);						
							$command->execute();
								
						}

						// delete all
						$criteria=new CDbCriteria; 
						$criteria->condition='id=:id'; 
						$criteria->params=array(':id'=>$id); 					

						Tbl_ProductVariantGroup::model()->deleteAll($criteria);	
		
						$criteria2=new CDbCriteria; 
						$criteria2->condition='id_product_variant_group=:id_product_variant_group'; 
						$criteria2->params=array(':id_product_variant_group'=>$id); 		
						
						Tbl_ProductVariantGroupDescription::model()->deleteAll($criteria2);			
																				
						// group options	
						foreach (Tbl_ProductVariantGroupOption::model()->findAll($criteria2) as $row){
							$this->delete_variant_group_option_swatch_image($row->id);
							//$this->delete_image_variant_group_option($row->id);	
						}				
						
						// delete groups and options		
						$command_delete_groups_options->execute(array(':id_product_variant_group'=>$id));		
			
						// variants			
						foreach (Tbl_ProductVariantOption::model()->findAll($criteria2) as $row){
							$this->delete_image_variant($row->id_product_variant);	
						}
								
						// delete variants				
						$command_delete_variants->execute(array(':id_product_variant_group'=>$id));	
																			
					}
					
					// Verify if there is variant group left else put field has_variants = 0 in product table
					$criteria=new CDbCriteria; 
					$criteria->condition='id_product=:id_product'; 
					$criteria->params=array(':id_product'=>$id_product); 			
					if (!Tbl_ProductVariantGroup::model()->count($criteria)) {
						Tbl_Product::model()->updateByPk($id_product,array('has_variants'=>0));	
					}
			}
		}			
	}
	
	/**
	 * This is the action to save product variant group order
	 */
	public function actionSave_variant_group_sort_order($id=0)
	{			
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		// check if we have variants		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product'; 
		$criteria->params=array(':id_product'=>$id); 			
		
		/*if(Tbl_ProductVariant::model()->count($criteria)) { 
			echo 'Cannot change groups order while variants exist.';
		} else */if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_product_variant_group) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND id=:id_product_variant_group'; 
				$criteria->params=array(':id_product'=>$id,':id_product_variant_group'=>$id_product_variant_group); 					
								
				if ($pvg = Tbl_ProductVariantGroup::model()->find($criteria)) {
					$pvg->sort_order = $i;
					$pvg->save();
					
					++$i;
				}
			}
		}
	}			
		
	/** 
	 *
	 */
	public function actionXml_list_variant_group_description($container, $id=0)
	{
		$model = new ProductsVariantGroupForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($product_variant_group = Tbl_ProductVariantGroup::model()->findByPk($id)) {							
				// grab description information 
				foreach ($product_variant_group->tbl_product_variant_group_description as $row) {
					$model->product_variant_group_description[$row->language_code]['name'] = $row->name;
					$model->product_variant_group_description[$row->language_code]['description'] = $row->description;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}				
		
		$columns = Html::getColumnsMaxLength(Tbl_ProductVariantGroupDescription::tableName());
		
		$help_hint_path = '/catalog/products/variants/';
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';
		
		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:auto;" class="div_'.$container.'">	
					<div style="padding:10px;">			
					<div class="row">
						<strong>'.Yii::t('global','LABEL_NAME').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'group-name').
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_product_variant_group_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->product_variant_group_description[$value->code]['name'])).'</span>)</em>':'').'
						<div>'.
						CHtml::activeTextField($model,'product_variant_group_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>$container.'_product_variant_group_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_product_variant_group_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'group-description').
						(isset($columns['description']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_product_variant_group_description['.$value->code.'][description]_maxlength">'.($columns['description']-strlen($model->product_variant_group_description[$value->code]['description'])).'</span>)</em>':'').'
						<div>'.
						CHtml::activeTextArea($model,'product_variant_group_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'rows' => 3,'maxlength'=>$columns['description'], 'id'=>$container.'_product_variant_group_description['.$value->code.'][description]')).'
						<br /><span id="'.$container.'_product_variant_group_description['.$value->code.'][description]_errorMsg" class="error"></span>
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
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_variant_groups($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('product_variant_group.id_product=:id_product');
		$params=array(':id_product'=>$id);
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'product_variant_group_description.name LIKE CONCAT(:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(product_variant_group.id) AS total 
		FROM 
		product_variant_group 
		INNER JOIN 
		product_variant_group_description 
		ON 
		(product_variant_group.id = product_variant_group_description.id_product_variant_group AND product_variant_group_description.language_code = '".Yii::app()->language."') 
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
		product_variant_group.id,
		product_variant_group_description.name,
		product_variant_group.input_type
		FROM 
		product_variant_group 
		INNER JOIN 
		product_variant_group_description 
		ON 
		(product_variant_group.id = product_variant_group_description.id_product_variant_group AND product_variant_group_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product_variant_group_description.name ".$direct;		
		} else {

			$sql.=" ORDER BY product_variant_group.sort_order ASC";
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
			switch($row['input_type']){
				case 0:
					$input_type_name = Yii::t('global','LABEL_DROP_DOWN_LIST');
				break;
				case 1:
					$input_type_name = Yii::t('global','LABEL_RADIO_BUTTON');
				break;
				case 2:
					$input_type_name = Yii::t('global','LABEL_SWATCH_PALETTE');
				break;
			}
			
			
			
			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$input_type_name.']]></cell>
			<cell type="ro"><![CDATA['.$row['input_type'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	public function actionEdit_variants_group_option($container)
	{
		$model = new ProductsVariantGroupOptionForm;
		
		$id = (int)$_POST['id'];
		$id_product_variant_group = (int)$_POST['id_product_variant_group'];
		
		if (!$pvg = Tbl_ProductVariantGroup::model()->findByPk($id_product_variant_group)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));		
		}
		
		$model->id_product_variant_group = $id_product_variant_group;
		
		if ($id) {
			if ($pvgo = Tbl_ProductVariantGroupOption::model()->findByPk($id)) {
				$model->id = $pvgo->id;
				$model->swatch_type = $pvgo->swatch_type;
				$model->color = $pvgo->color;
				$model->color2 = $pvgo->color2;
				$model->color3 = $pvgo->color3;
				$model->old_filename = $pvgo->filename;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_variants_group_option',array('model'=>$model, 'input_type'=>$pvg->input_type, 'container'=>$container));	
	}		
	
	public function actionSave_variant_group_option()
	{
		$model = new ProductsVariantGroupOptionForm;
		
		$id_product_variant_group = (int)$_POST['id_product_variant_group'];
		
		// collect user input data
		if(isset($_POST['ProductsVariantGroupOptionForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsVariantGroupOptionForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
			$model->id_product_variant_group = $id_product_variant_group;
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			
			$output = array('id'=>$model->id,'filename'=>$model->filename,'old_filename'=>$model->old_filename);
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);	
		}
	}	
	
	public function actionDelete_variant_group_option()
	{
		// CANT DELETE GROUP OPTION IF ALREADY IN AN ORDER 
		
		$ids = $_POST['ids'];
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$pass = (int)$_POST['pass'];

		if (is_array($ids) && sizeof($ids)) {
				if(!$pass){
					$arr_result['in_other_product'] = 0;
					// Verify if product is in a combo
					foreach ($ids as $id) {
						$sql = 'SELECT
						product_combo_variant.id
						FROM
						product_combo_variant
						INNER JOIN
						product_variant
						ON
						product_combo_variant.id_product_variant = product_variant.id
						INNER JOIN
						product_variant_option
						ON
						product_variant.id = product_variant_option.id_product_variant
						INNER JOIN
						product_variant_group_option
						ON
						product_variant_option.id_product_variant_group_option = product_variant_group_option.id AND product_variant_group_option.id = "'.$id.'"
						LIMIT 1';
						$command=$connection->createCommand($sql);
						
						if($command->queryAll(true)) {
							$arr_result['in_other_product'] = 1;
							break;	
						}
						
						// Verify if product is in a bundled
						$sql = 'SELECT
						product_bundled_product_group_product.id
						FROM
						product_bundled_product_group_product
						INNER JOIN
						product_variant
						ON
						product_bundled_product_group_product.id_product_variant = product_variant.id
						INNER JOIN
						product_variant_option
						ON
						product_variant.id = product_variant_option.id_product_variant
						INNER JOIN
						product_variant_group_option
						ON
						product_variant_option.id_product_variant_group_option = product_variant_group_option.id AND product_variant_group_option.id = "'.$id.'"
						LIMIT 1';
						$command=$connection->createCommand($sql);
						
						if($command->queryAll(true)) {
							$arr_result['in_other_product'] = 1;
							break;	
						}
					}
					header('Content-Type: text/javascript; charset=UTF-8'); //set header
					echo json_encode($arr_result); //display records in json format using json_encode
				}else{// prepare delete groups and options

					$app = Yii::app();
					$image_base_path = $app->params['product_images_base_path'].'swatch/';		
					
					// prepare delete variants
					$command_delete_variants=$connection->createCommand('DELETE FROM 
					product_variant,
					product_variant_option,
					product_variant_option2,
					product_combo_variant,
					product_bundled_product_group_product
					USING 
					product_variant 
					LEFT JOIN 
					product_variant_option 
					ON
					(product_variant.id = product_variant_option.id_product_variant) 
					LEFT JOIN 
					product_variant_option AS product_variant_option2
					ON 
					(product_variant.id = product_variant_option2.id_product_variant)
					LEFT JOIN 
					product_combo_variant
					ON 
					(product_variant.id = product_combo_variant.id_product_variant)
					LEFT JOIN 
					product_bundled_product_group_product
					ON 
					(product_variant.id = product_bundled_product_group_product.id_product_variant)
					WHERE 
					product_variant_option.id_product_variant_group_option=:id_product_variant_group_option');	
					
					$command_delete_image_variant=$connection->createCommand('DELETE FROM
					product_image_variant,
					product_image_variant_option
					USING
					product_image_variant
					INNER JOIN
					product_image_variant_option
					ON
					(product_image_variant.id = product_image_variant_option.id_product_image_variant)
					LEFT JOIN
					product_image_variant_option AS product_image_variant_option2
					ON
					(product_image_variant.id = product_image_variant_option2.id_product_image_variant)
					WHERE
					product_image_variant_option.id_product_variant_group_option=:id_product_image_variant_option');
					
					foreach ($ids as $id) {
						// Deactivate combo
						$sql = 'SELECT
						product_combo_variant.id_product_combo
						FROM
						product_combo_variant
						INNER JOIN
						product_variant
						ON
						product_combo_variant.id_product_variant = product_variant.id
						INNER JOIN
						product_variant_option
						ON
						product_variant.id = product_variant_option.id_product_variant
						INNER JOIN
						product_variant_group_option
						ON
						product_variant_option.id_product_variant_group_option = product_variant_group_option.id AND product_variant_group_option.id = "'.$id.'"
						GROUP BY product_combo_variant.id_product_combo';
						$command=$connection->createCommand($sql);
						
						foreach ($command->queryAll(true) as $row) {
							$sql = 'UPDATE
							product
							SET 
							active = "0" 
							WHERE
							id IN (SELECT id_product FROM product_combo WHERE id = "'.$row['id_product_combo'].'")';
							$command=$connection->createCommand($sql);						
							$command->execute();
								
						}
						// Deactivate bundled
						$sql = 'SELECT
						product_bundled_product_group_product.id_product_bundled_product_group
						FROM
						product_bundled_product_group_product
						INNER JOIN
						product_variant
						ON
						product_bundled_product_group_product.id_product_variant = product_variant.id
						INNER JOIN
						product_variant_option
						ON
						product_variant.id = product_variant_option.id_product_variant
						INNER JOIN
						product_variant_group_option
						ON
						product_variant_option.id_product_variant_group_option = product_variant_group_option.id AND product_variant_group_option.id = "'.$id.'"
						GROUP BY product_bundled_product_group_product.id_product_bundled_product_group';
						$command=$connection->createCommand($sql);
						
						foreach ($command->queryAll(true) as $row) {
							$sql = 'UPDATE
							product
							SET 
							active = "0" 
							WHERE
							id IN (SELECT id_product FROM product_bundled_product_group WHERE id = "'.$row['id_product_bundled_product_group'].'")';
							$command=$connection->createCommand($sql);						
							$command->execute();
								
						}

						// delete all
						
						$this->delete_variant_group_option_swatch_image($id);			
										
						
						Tbl_ProductVariantGroupOption::model()->deleteByPk($id);	
		
						$criteria=new CDbCriteria; 
						$criteria->condition='id_product_variant_group_option=:id_product_variant_group_option'; 
						$criteria->params=array(':id_product_variant_group_option'=>$id); 		
						
						Tbl_ProductVariantGroupOptionDescription::model()->deleteAll($criteria);		
			
						// variants			
						foreach (Tbl_ProductVariantOption::model()->findAll($criteria) as $row){
							$this->delete_image_variant($row->id_product_variant);	
						}
						
						// delete image variant associated to this option
						$command_delete_image_variant->execute(array(':id_product_image_variant_option'=>$id));
						
						// delete variants associate to this option						
						$command_delete_variants->execute(array(':id_product_variant_group_option'=>$id));																						
					}
			}
		}				
	}	
	
	public function delete_variant_group_option_swatch_image($id_product_variant_group_option)
	{
		$image_base_path = Yii::app()->params['product_images_base_path'].'swatch/';						

		if ($pvgo = Tbl_ProductVariantGroupOption::model()->findByPk($id_product_variant_group_option)) {
			if ($pvgo->filename && is_file($image_base_path.$pvgo->filename)) {
				@unlink($image_base_path.$pvgo->filename);
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}	
	}	
	
	/**
	 * This is the action to save product variant group option order
	 */
	public function actionSave_variant_group_option_sort_order($id=0)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_product_variant_group_option) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product_variant_group=:id_product_variant_group AND id=:id_product_variant_group_option'; 
				$criteria->params=array(':id_product_variant_group'=>$id,':id_product_variant_group_option'=>$id_product_variant_group_option); 					
								
				if ($pvg = Tbl_ProductVariantGroupOption::model()->find($criteria)) {
					$pvg->sort_order = $i;
					$pvg->save();
					
					++$i;
				}
			}
			
			// get product id
			$sql='SELECT
			id_product
			FROM 
			product_variant_group 
			WHERE
			id = :id 
			LIMIT 1';
			$command=$connection->createCommand($sql);
	
			// reorder variants
			$this->reorder_variants($command->queryScalar(array(':id'=>$id)));
		}
	}		

	/**
	 * This is the action to upload images
	 */
	public function actionUpload_image_variant_group_option_swatch()
	{			
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = $app->user->getId();	
		
		// if id is not set
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $app->params['product_images_base_path'];
			$targetFile = $_FILES['Filedata']['name'];
			$ext = strtolower(trim(pathinfo($targetFile, PATHINFO_EXTENSION)));
			$force_crop = 0;
			$allowed_ext = array(
				'gif',
				'jpeg',
				'jpg',
				'png',
			);									
			
			if (empty($ext) || !in_array($ext, $allowed_ext)) {
				echo Yii::t('global','ERROR_ALLOWED_IMAGES');				
				exit;
			} else {	
				// original file renamed
				$original = md5($targetFile.time()).'.'.$ext;	
				$filename = md5($original).'.jpg';				
			
				$image = new SimpleImage();
				if (!$image->load($tempFile)) {
					// delete
					$product_image->delete();
					
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;
				}			
				
				$width = $image->getWidth();
				$height = $image->getHeight();
				
				if (!$width || !$height) {
					// delete
					$product_image->delete();
					
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;					
				}
															
				// save image
				if (!$image->resize(20,20)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_ZOOM_FAILED');						
					exit;		
				} else if (!$image->save($targetPath.'swatch/'.$filename)) {
					echo Yii::t('global', 'ERROR_SAVE_ZOOM_FAILED');						
					exit;									
				}

				echo 'file:'.$filename;
				exit;
				break;
			}
			
			echo Yii::t('global','ERROR_UPLOAD_IMAGE_FAILED');
		}				
	}		
	
	
	public function actionEdit_variants_group_option_image_upload($container)
	{	
		$id = (int)$_POST['id'];	
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (!Tbl_ProductVariantGroupOption::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
	
		$this->renderPartial('edit_variants_group_option_image_upload',array('container'=>$container, 'id'=>$id));	
	}		
	
	
	
		
	
		
	
						
	
	/** 
	 *
	 */
	public function actionXml_list_variant_group_option_description($container, $id=0)
	{
		$model = new ProductsVariantGroupOptionForm;
		
		$id = (int)$id;
		
		if ($id) {
			$model->id = $id;			
			if ($pvgo = Tbl_ProductVariantGroupOption::model()->findByPk($id)) {							
				// grab description information 
				foreach ($pvgo->tbl_product_variant_group_option_description as $row) {
					$model->product_variant_group_option_description[$row->language_code]['name'] = $row->name;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}
		
		$columns = Html::getColumnsMaxLength(Tbl_ProductVariantGroupOptionDescription::tableName());	
		
		$help_hint_path = '/catalog/products/variants/';					
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';
		
		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					'.($i==0 ? CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')):'').'				
					<div style="width:100%; height:100%; overflow:auto;" class="div_'.$container.'">	
					<div style="padding:10px;">			
					<div class="row">
						<strong>'.Yii::t('global','LABEL_NAME').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'option-name').
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_product_variant_group_option_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->product_variant_group_option_description[$value->code]['name'])).'</span>)</em>':'').'
						<div>'.
						CHtml::activeTextField($model,'product_variant_group_option_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>$container.'_product_variant_group_option_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_product_variant_group_option_description['.$value->code.'][name]_errorMsg" class="error"></span>
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
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_variant_group_options($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('product_variant_group_option.id_product_variant_group=:id_product_variant_group');
		$params=array(':id_product_variant_group'=>$id);
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'product_variant_group_option_description.name LIKE CONCAT(:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(product_variant_group_option.id) AS total 
		FROM 
		product_variant_group_option 
		INNER JOIN 
		product_variant_group_option_description 
		ON 
		(product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option AND product_variant_group_option_description.language_code = '".Yii::app()->language."') 
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
		product_variant_group_option.id,
		product_variant_group_option_description.name 
		FROM 
		product_variant_group_option 
		INNER JOIN 
		product_variant_group_option_description 
		ON 
		(product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option AND product_variant_group_option_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product_variant_group_option_description.name ".$direct;		
		} else {

			$sql.=" ORDER BY product_variant_group_option.sort_order ASC";
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
			</row>';
		}
		
		echo '</rows>';
	}				
	
	public function actionEdit_variants_options($container, $id_product=0)
	{
		$model = new ProductsVariantForm;
		
		$id = (int)$_POST['id'];
		$id_product = (int)$id_product;
	
		if ($product = Tbl_Product::model()->findByPk($id_product)) {
			
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));		
		}

		$model->id_product = $id_product;
		
		if ($id) {
			if ($pv = Tbl_ProductVariant::model()->findByPk($id)) {
				$model->id = $pv->id;
				$model->sku = $pv->sku;
				$model->cost_price = $pv->cost_price;
				$model->price = $pv->price;
				$model->price_type = $pv->price_type;
				$model->qty = $pv->qty;
				$model->notify_qty = $pv->notify_qty;
				$model->weight = $pv->weight;
				$model->length = $pv->length;
				$model->width = $pv->width;
				$model->height = $pv->height;				
				$model->in_stock = $pv->in_stock;
				$model->active = $pv->active;

				foreach ($pv->tbl_product_variant_option as $row) {
					$model->product_variant_option[$row->id_product_variant_group] = $row->id_product_variant_group_option;	
				}
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		} else {
			$model->qty = $product->qty;
			$model->sku = $product->sku;
			$model->notify_qty = $product->notify_qty;
			$model->weight = $product->weight;
			$model->length = $product->length;
			$model->width = $product->width;
			$model->height = $product->height;			
			$model->in_stock = $product->in_stock;
			$model->cost_price = $product->cost_price;
			$model->price = $product->price;
			$model->price_type = $product->price_type;
		}
		
		$this->renderPartial('edit_variants_options',array('model'=>$model, 'track_inventory'=>$product->track_inventory, 'use_shipping_price'=>$product->use_shipping_price, 'notify'=>$product->notify, 'container'=>$container));	
	}

	public function actionSave_variant()
	{
		$model = new ProductsVariantForm;
		
		// collect user input data
		if(isset($_POST['ProductsVariantForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsVariantForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
			// validate 
			if($model->validate()) {
				
				$output['in_other_product'] = 0;
				// Verify if we disabled the product we must disabled bundled and combo at the same time
				if(!empty($model->id) && !$model->active){
					$connection=Yii::app()->db;   // assuming you have configured a "db" connection
					
					$id = $model->id;
					$pass = (int)$_POST['pass'];
					
					if(!$pass){
						// Verify if product is in a combo
						$sql = 'SELECT
						product_combo_variant.id
						FROM
						product_combo_variant
						INNER JOIN
						product_combo
						ON
						product_combo_variant.id_product_combo = product_combo.id
						INNER JOIN
						product
						ON
						product_combo.id_product = product.id AND product.active = 1
						WHERE product_combo_variant.id_product_variant = "'.$id.'"
						LIMIT 1';
						$command=$connection->createCommand($sql);
						
						if($command->queryAll(true)) {
							$output['in_other_product'] = 1;
						}
						
						// Verify if product is in a bundled
						$sql = 'SELECT
						product_bundled_product_group_product.id
						FROM
						product_bundled_product_group_product
						INNER JOIN
						product
						ON
						product_bundled_product_group_product.id_product = product.id AND product.active = 1
						WHERE product_bundled_product_group_product.id_product_variant = "'.$id.'"
						LIMIT 1';
						$command=$connection->createCommand($sql);
						
						if($command->queryAll(true)) {
							$output['in_other_product'] = 1;
						}
						
						header('Content-Type: text/javascript; charset=UTF-8'); //set header
						echo json_encode($output); //display records in json format using json_encode
						exit;
					}else{
						// Deactivate combo
						$sql = 'SELECT
						product_combo.id_product
						FROM
						product_combo_variant
						INNER JOIN
						product_combo
						ON
						product_combo_variant.id_product_combo = product_combo.id
						WHERE id_product_variant = "' .$id . '"';
						$command=$connection->createCommand($sql);
						
						foreach ($command->queryAll(true) as $row) {
							$criteria=new CDbCriteria; 
							$criteria->condition='id=:id'; 
							$criteria->params=array(':id'=>$row['id_product']); 	
							Tbl_Product::model()->updateAll(array('active'=>$active),$criteria);	
						}
						/// Deactivate Bundled
						$sql = 'SELECT
						id_product_bundled_product_group
						FROM
						product_bundled_product_group_product
						WHERE id_product_variant = "' .$id . '"';
						$command=$connection->createCommand($sql);
						
						foreach ($command->queryAll(true) as $row) {
							$sql = 'UPDATE
							product
							SET 
							active = "'.$active.'" 
							WHERE
							id IN (SELECT id_product FROM product_bundled_product_group WHERE id = "'.$row['id_product_bundled_product_group'].'")';
							$command=$connection->createCommand($sql);						
							$command->execute();
								
						}
					}
				}

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
	
	public function actionDelete_variants($id=0)
	{
		$id=(int)$id;
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$pass = (int)$_POST['pass'];

		if(!$pass){
			$arr_result['in_other_product'] = 0;
			// Verify if product is in a combo
			$sql = 'SELECT
			product_combo_variant.id
			FROM
			product_combo_variant
			INNER JOIN
			product_variant
			ON
			product_combo_variant.id_product_variant = product_variant.id
			INNER JOIN
			product
			ON
			product_variant.id_product = product.id AND product.id = "'.$id.'"
			LIMIT 1';
			$command=$connection->createCommand($sql);
			
			if($command->queryAll(true)) {
				$arr_result['in_other_product'] = 1;	
			}
			
			// Verify if product is in a bundled
			$sql = 'SELECT
			product_bundled_product_group_product.id
			FROM
			product_bundled_product_group_product
			INNER JOIN
			product
			ON
			product_bundled_product_group_product.id_product = product.id AND product.id = "'.$id.'"
			WHERE product_bundled_product_group_product.id_product_variant > 0
			LIMIT 1';
			$command=$connection->createCommand($sql);
			
			if($command->queryAll(true)) {
				$arr_result['in_other_product'] = 1;
			}
			header('Content-Type: text/javascript; charset=UTF-8'); //set header
			echo json_encode($arr_result); //display records in json format using json_encode
		}else{
			// Deactivate combo
			$sql = 'SELECT
			product_combo_variant.id_product_combo
			FROM
			product_combo_variant
			INNER JOIN
			product_variant
			ON
			product_combo_variant.id_product_variant = product_variant.id
			INNER JOIN
			product
			ON
			product_variant.id_product = product.id AND product.id = "'.$id.'"
			GROUP BY product_combo_variant.id_product_combo';
			$command=$connection->createCommand($sql);
			
			foreach ($command->queryAll(true) as $row) {
				$sql = 'UPDATE
				product
				SET 
				active = "0" 
				WHERE
				id IN (SELECT id_product FROM product_combo WHERE id = "'.$row['id_product_combo'].'")';
				$command=$connection->createCommand($sql);						
				$command->execute();
					
			}
			// Deactivate bundled
			$sql = 'SELECT
			product_bundled_product_group_product.id_product_bundled_product_group
			FROM
			product_bundled_product_group_product
			INNER JOIN
			product_variant
			ON
			product_bundled_product_group_product.id_product_variant = product_variant.id
			INNER JOIN
			product
			ON
			product_bundled_product_group_product.id_product = product.id AND product.id = "'.$id.'"
			WHERE product_bundled_product_group_product.id_product_variant > 0
			GROUP BY product_bundled_product_group_product.id_product_bundled_product_group';
			$command=$connection->createCommand($sql);
			
			foreach ($command->queryAll(true) as $row) {
				$sql = 'UPDATE
				product
				SET 
				active = "0" 
				WHERE
				id IN (SELECT id_product FROM product_bundled_product_group WHERE id = "'.$row['id_product_bundled_product_group'].'")';
				$command=$connection->createCommand($sql);						
				$command->execute();
					
			}
			
			// delete all variants
  
			// get all variant images
			$sql='SELECT 
			id
			FROM 
			product_image_variant
			WHERE
			id_product=:id';
			$command=$connection->createCommand($sql);
			
			// loop and delete them
			foreach ($command->queryAll(true,array(':id'=>$id)) as $row) {
				$this->delete_image_variant($row['id']);	
			}
			
			$command_delete_image_variant=$connection->createCommand('DELETE FROM
			product_image_variant,
			product_image_variant_option,
			product_image_variant_description
			USING
			product_image_variant
			LEFT JOIN
			product_image_variant_option
			ON
			(product_image_variant.id = product_image_variant_option.id_product_image_variant)
			LEFT JOIN 
			product_image_variant_description
			ON
			(product_image_variant.id = product_image_variant_description.id_product_image_variant)
			WHERE
			product_image_variant.id_product=:id');		
			$command_delete_image_variant->execute(array(':id'=>$id));
			
			// delete all variants
			$command=$connection->createCommand('DELETE FROM 
			product_variant,
			product_variant_option,
			product_combo_variant,
			product_bundled_product_group_product,
			product_variant_description
			USING 
			product_variant 
			LEFT JOIN 
			product_variant_option 
			ON
			(product_variant.id = product_variant_option.id_product_variant)
			LEFT JOIN 
			product_combo_variant
			ON 
			(product_variant.id = product_combo_variant.id_product_variant)
			LEFT JOIN 
			product_bundled_product_group_product
			ON 
			(product_variant.id = product_bundled_product_group_product.id_product_variant)
			LEFT JOIN
			product_variant_description
			ON
			(product_variant.id = product_variant_description.id_product_variant)
			WHERE 
			product_variant.id_product=:id');							
			$command->execute(array(':id'=>$id));
		}				
	}		
	
	public function actionCount_variants($id)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$status = 0;

		$id = (int)$id;

		//Verify if product is sold, we cannot delete
		$sql_orders = "SELECT 
		orders_item_product.id
		FROM 
		orders_item_product 
		INNER JOIN 
		product_variant 
		ON 
		orders_item_product.id_product = '".$id."'
		AND
		orders_item_product.id_product_variant > 0
		LIMIT 1";	
		
		$command_orders=$connection->createCommand($sql_orders);
		
		$row_orders = $command_orders->queryAll(true);
		if (sizeof($row_orders)) {
			$status = 'orders';
		}else{
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product=:id_product'; 
			$criteria->params=array(':id_product'=>$id); 			
			if(Tbl_ProductVariant::model()->count($criteria)){
				$status = 'generated';
			}
		}
		echo $status;
	}
	
	public function actionCount_orders_variants($id)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$name_not_delete = "";

		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_option) {
				//Verify if product is sold, we cannot delete
				$sql_orders = "SELECT 
				orders_item_product.id,
				product_variant_group_option_description.name
				FROM 
				orders_item_product 
				INNER JOIN 
				product_variant_option 
				ON 
				orders_item_product.id_product_variant = product_variant_option.id_product_variant
				AND
				product_variant_option.id_product_variant_group_option = '".$id_option."'
				INNER JOIN product_variant_group_option CROSS JOIN product_variant_group_option_description
				ON
				product_variant_option.id_product_variant_group_option = product_variant_group_option.id AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option AND product_variant_group_option_description.language_code = '".Yii::app()->language."'
				WHERE orders_item_product.id_product = '" .$id. "'";		
				$command_orders=$connection->createCommand($sql_orders);
				if ($row_orders = $command_orders->queryRow(true)) {
					$name_not_delete .= $row_orders['name'] . ', ';
				}			
			}
			if(!empty($name_not_delete)){
				$name_not_delete = substr($name_not_delete,0,-2);
			}
		}
		echo $name_not_delete;
	}
	
	public function actionCount_variants_and_count_options_in_group($id)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id = (int)$id;
		$arr_result = array();
		

		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product'; 
		$criteria->params=array(':id_product'=>$id);		
		
		// Verify if User Create Variant
		if(Tbl_ProductVariantGroup::model()->count($criteria)){
$arr_result = array();
			// Find if variants has been generated
			$arr_result["variants"] = Tbl_ProductVariant::model()->count($criteria);
			
			// Find wich group do not have option in it
			$sql = "SELECT 
			product_variant_group.id,
			product_variant_group_description.name,
			COUNT(product_variant_group_option.id) AS total
			FROM 
			product_variant_group 
			INNER JOIN 
			product_variant_group_description 
			ON 
			(product_variant_group.id = product_variant_group_description.id_product_variant_group AND product_variant_group_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN product_variant_group_option ON product_variant_group.id = product_variant_group_option.id_product_variant_group
			WHERE id_product = " . $id . '
			GROUP BY product_variant_group.id';		
			
			$command=$connection->createCommand($sql);
			
			$group_name = "";
			// Cycle through results
			foreach ($command->queryAll(true) as $row) {
				if(!$row['total']){
					if(empty($group_name)){
						$group_name .= $row['name'];
					}else{
						$group_name .= ", " . $row['name'];
					}
				}
				
			}
			$arr_result['group'] = $group_name;
		}

		header('Content-Type: text/javascript; charset=UTF-8'); //set header
		echo json_encode($arr_result); //display records in json format using json_encode
	}
	
	
	
	/**
	 * This is the action to toggle product active status
	 */
	public function actionToggle_active_variant($id_product=0)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id = trim($_POST['id']);
		$id_product = (int)$id_product;
		$active = ($_POST['active']=='true'?1:0);
		$pass = (int)$_POST['pass'];
		
		if ($p = Tbl_ProductVariant::model()->findByPk($id)) {
			if(!$pass){
				$arr_result['in_other_product'] = 0;
				// Verify if product is in a combo
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product_variant=:id_product_variant'; 
				$criteria->params=array(':id_product_variant'=>$id); 	
				if (Tbl_ProductComboVariant::model()->find($criteria)) {
					$arr_result['in_other_product'] = 1;
				}
				// Verify if product is in a bundled
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product_variant=:id_product_variant '; 
				$criteria->params=array(':id_product_variant'=>$id); 	
				if (Tbl_ProductBundledProductGroupProduct::model()->find($criteria)) {
					$arr_result['in_other_product'] = 1;
				}
				header('Content-Type: text/javascript; charset=UTF-8'); //set header
				echo json_encode($arr_result); //display records in json format using json_encode
			}else{
				if(!$active){
					// Deactivate combo
					$sql = 'SELECT
					product_combo.id_product
					FROM
					product_combo_variant
					INNER JOIN
					product_combo
					ON
					product_combo_variant.id_product_combo = product_combo.id
					WHERE id_product_variant = "' .$id . '"';
					$command=$connection->createCommand($sql);
					
					foreach ($command->queryAll(true) as $row) {
						$criteria=new CDbCriteria; 
						$criteria->condition='id=:id'; 
						$criteria->params=array(':id'=>$row['id_product']); 	
						Tbl_Product::model()->updateAll(array('active'=>$active),$criteria);	
					}
					/// Deactivate Bundled
					$sql = 'SELECT
					id_product_bundled_product_group
					FROM
					product_bundled_product_group_product
					WHERE id_product_variant = "' .$id . '"';
					$command=$connection->createCommand($sql);
					
					foreach ($command->queryAll(true) as $row) {
						$sql = 'UPDATE
						product
						SET 
						active = "'.$active.'" 
						WHERE
						id IN (SELECT id_product FROM product_bundled_product_group WHERE id = "'.$row['id_product_bundled_product_group'].'")';
						$command=$connection->createCommand($sql);						
						$command->execute();
							
					}
				}
				
				$p->active = $active;
				if (!$p->save()) {
					throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
				}
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}

	}	
	
	/**
	 * This is the action to toggle product active status
	 */
	public function actionToggle_variant_image_displayed($id_product=0)
	{
		// current 
		$id = trim($_POST['id']);
		$id_product = (int)$id_product;
		$displayed_in_listing = ($_POST['displayed_in_listing'] == 'true') ? 1:0;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id AND id_product=:id_product'; 
		$criteria->params=array(':id'=>$id, ':id_product'=>$id_product); 		
		
		if ($pv = Tbl_ProductImageVariant::model()->find($criteria)) {
			$pv->displayed_in_listing = $displayed_in_listing;
			if (!$pv->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}		
	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_product_image_variants($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('product_image_variant.id_product=:id_product');
		$params=array(':id_product'=>$id,':language_code'=>Yii::app()->language);
		
		// filters											
		
		$sql = 'SELECT 
		COUNT(product_image_variant.id) AS total
		FROM 
		product_image_variant
		INNER JOIN
		product_image_variant_description		
		ON 
		(
			product_image_variant.id = product_image_variant_description.id_product_image_variant
			AND 
			product_image_variant_description.language_code = :language_code
		)			
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').' 
		GROUP BY 
		product_image_variant.id';	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = 'SELECT 
		product_image_variant.id, 
		product_image_variant.displayed_in_listing,
		product_image_variant_description.name
		FROM 
		product_image_variant
		INNER JOIN
		product_image_variant_description		
		ON 
		(
			product_image_variant.id = product_image_variant_description.id_product_image_variant
			AND 
			product_image_variant_description.language_code = :language_code
		)	
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').' 
		GROUP BY 
		product_image_variant.id';

		// sorting

		$sql.=" ORDER BY product_image_variant.sort_order ASC";
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		$sql = 'SELECT 
		COUNT(product_image_variant_image.id) AS total
		FROM 
		product_image_variant_image
		WHERE
		id_product_image_variant = :id_product_image_variant';		
		$command2=$connection->createCommand($sql);	
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';				
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			$row_images = $command2->queryAll(true,array(':id_product_image_variant'=>$row['id']));
			
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row_images[0]['total'].']]></cell>
			<cell type="ch"><![CDATA['.$row['displayed_in_listing'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}		
	
	public function actionEdit_variants_image_variant($container, $id_product=0)
	{	
		$id = (int)$_POST['id'];	
		$id_product = (int)$id_product;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id_product); 			
		
		if (!Tbl_Product::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));		
		}		
		
		if ($id) {
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 				
			
			if (!Tbl_ProductImageVariant::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));		
			}
		}
	
		$this->renderPartial('edit_variants_image_variant',array('container'=>$container, 'id'=>$id, 'id_product'=>$id_product));	
	}			
	
	public function actionEdit_variants_test($id=0)
	{
		$id = (int)$id;
		
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection		
		
		$array = array();		
		
		$sql = "SELECT 
		product_variant_group.id AS id_product_variant_group,
		product_variant_group_option.id,
		product_variant_group_option_description.name 
		FROM 
		product_variant_group
		INNER JOIN 
		(product_variant_group_option CROSS JOIN product_variant_group_option_description)
		ON 
		(product_variant_group.id = product_variant_group_option.id_product_variant_group AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option AND product_variant_group_option_description.language_code=:language_code)
		WHERE
		product_variant_group.id_product = :id_product
		ORDER BY 
		product_variant_group.sort_order ASC,
		product_variant_group_option.sort_order ASC";				
		$command=$connection->createCommand($sql);	

		$current_id=0;
		foreach ($command->queryAll(true, array(':id_product'=>$id,':language_code'=>Yii::app()->language)) as $row) {	
			if ($current_id != $row['id_product_variant_group']) {
				if ($current_id) {								
					$array[$row['id_product_variant_group']][] = $row['id_product_variant_group'].':0';
				}
				$current_id = $row['id_product_variant_group'];
			}
		
			$array[$row['id_product_variant_group']][] = $row['id_product_variant_group'].':'.$row['id'];
		}
		
		if (sizeof($array)) {							
			$array = array_slice($array,0,sizeof($array));

			$this->showCombinations('',$array, 0);
			
			/*
			echo '<pre>';
			print_r($this->variant_combinations);
			echo '</pre>';
			*/
		}
	}
	
	public function actionEdit_variants_get_image_variant($container, $id_product=0)
	{
		$id_product = (int)$id_product;
		$variant = $_POST['variant'];
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id_product); 			
		
		if (!Tbl_Product::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));		
		}		
		
		if (sizeof($variant)) {
			
		}	
			
		$this->renderPartial('edit_variants_image_variant',array('container'=>$container, 'id'=>$id, 'id_product'=>$id_product));	
	}
	
	public function actionEdit_variants_image_upload($container)
	{	
		$id = (int)$_POST['id'];	

		if ($id) {
			if (!Tbl_ProductImageVariant::model()->findByPk($id)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));		
			}
		}
	
		$this->renderPartial('edit_variants_image_upload',array('container'=>$container, 'id'=>$id));	
	}		
	
	/**
	 * This is the action to upload images
	 */
	public function actionUpload_image_variant($id=0)
	{			
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = $app->user->getId();	
		$id = (int)$id;

		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (!Tbl_ProductImageVariant::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}							
		
		// if id is not set
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $app->params['product_images_base_path'];
			$targetFile = $_FILES['Filedata']['name'];
			$ext = strtolower(trim(pathinfo($targetFile, PATHINFO_EXTENSION)));
			$force_crop = 0;
			$allowed_ext = array(
				'gif',
				'jpeg',
				'jpg',
				'png',
			);									
			
			if (empty($ext) || !in_array($ext, $allowed_ext)) {
				echo Yii::t('global','ERROR_ALLOWED_IMAGES');				
				exit;
			} else {	
				// get current product image cover
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product_image_variant=:id_product_image_variant AND cover=1'; 
				$criteria->params=array(':id_product_image_variant'=>$id); 	
					
				$cover = Tbl_ProductImageVariantImage::model()->count($criteria);
				
				$criteria->condition='id_product_image_variant=:id_product_image_variant'; 
				$criteria->order='sort_order DESC';			
						
				// insert new image
				$product_image = new Tbl_ProductImageVariantImage;
				$product_image->id_product_image_variant = $id;
				$product_image->sort_order = (Tbl_ProductImageVariantImage::model()->find($criteria)->sort_order)+1;	
				$product_image->cover = $cover ? 0:1;	
				$product_image->id_user_created = $current_id_user;
				$product_image->id_user_modified = $current_id_user;				
				$product_image->date_created = $current_datetime;
				if (!$product_image->save()) {
					echo Yii::t('controllers/ProductsController','ERROR_SAVING');
					exit;
				}
				
				$id_image = $product_image->id;
				
				// original file renamed
				$original = md5($targetFile.time()).'.'.$ext;	
				$filename = md5($original).'.jpg';				
			
				$image = new SimpleImage();
				if (!$image->load($tempFile)) {
					// delete
					$product_image->delete();
					
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;
				}
			
				// save original
				if (!$image->save($targetPath.'original/'.$original)) {
					// delete
					$product_image->delete();
					
					echo Yii::t('global','ERROR_SAVE_ORIGINAL_FAILED');
					exit;				
				}
				
				$width = $image->getWidth();
				$height = $image->getHeight();
				
				if (!$width || !$height) {
					// delete
					$product_image->delete();
					
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;					
				}
				
				// vs
				switch ($app->params['images_orientation']) {
					case 'portrait':
						$default_zoom_width = $app->params['portrait_zoom_width'];
						$default_zoom_height = $app->params['portrait_zoom_height'];
		
						$default_cover_width = $app->params['portrait_cover_width'];
						$default_cover_height = $app->params['portrait_cover_height'];
						
						$default_listing_width = $app->params['portrait_listing_width'];
						$default_listing_height = $app->params['portrait_listing_height'];
		
						$default_suggest_width = $app->params['portrait_suggest_width'];
						$default_suggest_height = $app->params['portrait_suggest_height'];
						
						$default_thumb_width = $app->params['portrait_thumb_width'];
						$default_thumb_height = $app->params['portrait_thumb_height'];
					
						// if our image size is smaller than our min 800x600
						if ($width < $default_width || $height < $default_heigh) { 
							// delete
							$product_image->delete();				
						
							echo Yii::t('global', 'ERROR_MIN_IMAGE_RESOLUTION', array('{width}'=>$default_zoom_width,'{height}'=>$default_zoom_height));							
							exit;
						// ratio is not correct, force crop
						} else if (($width/$height) != 0.75) {
							$force_crop = 1;
						}	
						break;
					case 'landscape':
						$default_zoom_width = $app->params['landscape_zoom_width'];
						$default_zoom_height = $app->params['landscape_zoom_height'];
		
						$default_cover_width = $app->params['landscape_cover_width'];
						$default_cover_height = $app->params['landscape_cover_height'];
						
						$default_listing_width = $app->params['landscape_listing_width'];
						$default_listing_height = $app->params['landscape_listing_height'];
		
						$default_suggest_width = $app->params['landscape_suggest_width'];
						$default_suggest_height = $app->params['landscape_suggest_height'];
						
						$default_thumb_width = $app->params['landscape_thumb_width'];
						$default_thumb_height = $app->params['landscape_thumb_height'];					
					
						// if our image size is smaller than our min 800x600
						/*if ($width < $default_zoom_width || $height < $default_zoom_height) { 
							// delete
							$product_image->delete();														
						
							echo Yii::t('global', 'ERROR_MIN_IMAGE_RESOLUTION', array('{width}'=>$default_zoom_width,'{height}'=>$default_zoom_height));							
							exit;
						// ratio is not correct, force crop
						} else*/ if (($height/$width) != 0.75) {
							$force_crop = 1;
						}					
						break;
				}
																		
				// save image ZOOM
				if ($width > $default_zoom_width && !$image->resizeToWidth($default_zoom_width)) {
				//if (!$image->resize($default_zoom_width,$default_zoom_height)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_ZOOM_FAILED');						
					exit;		
				} else if (!$image->save($targetPath.'zoom/'.$filename)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_SAVE_ZOOM_FAILED');						
					exit;									
				}
				
				// save image COVER
				if ($width > $default_cover_width && !$image->resizeToWidth($default_cover_width)) {
				//if (!$image->resize($default_cover_width,$default_cover_height)) {
					// delete
					$product_image->delete();							
					
					echo Yii::t('global', 'ERROR_RESIZE_COVER_FAILED');					
					exit;									
				} else if (!$image->save($targetPath.'cover/'.$filename)) {
					// delete
					$product_image->delete();							
					
					echo Yii::t('global', 'ERROR_SAVE_COVER_FAILED');					
					exit;									
				}
				
				// save image LISTING
				if ($width > $height && !$image->resizeToWidth($default_listing_width)) {
					// delete
					$product_image->delete();									
					
					echo Yii::t('global', 'ERROR_RESIZE_LISTING_FAILED');				
					exit;				
				} else if ($height > $width && !$image->resizeToHeight($default_listing_height)) {															
					// delete
					$product_image->delete();									
					
					echo Yii::t('global', 'ERROR_RESIZE_LISTING_FAILED');				
					exit;			
				} else if ($width == $height && $default_listing_width < $default_listing_height && !$image->resizeToWidth($default_listing_width)) {
					// delete
					$product_image->delete();									
					
					echo Yii::t('global', 'ERROR_RESIZE_LISTING_FAILED');				
					exit;
				} else if ($width == $height && $default_listing_width > $default_listing_height && !$image->resizeToHeight($default_listing_height)) {
					// delete
					$product_image->delete();									
					
					echo Yii::t('global', 'ERROR_RESIZE_LISTING_FAILED');				
					exit;
				} else if (!$image->save($targetPath.'listing/'.$filename)) {
					// delete
					$product_image->delete();									
					
					echo Yii::t('global', 'ERROR_RESIZE_LISTING_FAILED');				
					exit;				
				}

				// save image SUGGEST
				if ($width > $height && !$image->resizeToWidth($default_suggest_width)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_SUGGEST_FAILED');				
					exit;				
				} else if ($height > $width && !$image->resizeToHeight($default_suggest_height)) {																					
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_SUGGEST_FAILED');				
					exit;			
				} else if ($width == $height && $default_suggest_width < $default_suggest_height && !$image->resizeToWidth($default_suggest_width)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_SUGGEST_FAILED');				
					exit;	
				} else if ($width == $height && $default_suggest_width > $default_suggest_height && !$image->resizeToHeight($default_suggest_height)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_SUGGEST_FAILED');				
					exit;	
				} else if (!$image->save($targetPath.'suggest/'.$filename)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_SUGGEST_FAILED');				
					exit;				
				}
				
				// save image THUMB
				if ($width > $height && !$image->resizeToWidth($default_thumb_width)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_THUMB_FAILED');				
					exit;					
				} else if ($height > $width && !$image->resizeToHeight($default_thumb_height)) {																										
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_THUMB_FAILED');				
					exit;				
				} else if ($width == $height && $default_thumb_width < $default_thumb_height && !$image->resizeToWidth($default_thumb_width)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_THUMB_FAILED');				
					exit;	
				} else if ($width == $height && $default_thumb_width > $default_thumb_height && !$image->resizeToHeight($default_thumb_height)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_THUMB_FAILED');				
					exit;	
				} else if (!$image->save($targetPath.'thumb/'.$filename)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('global', 'ERROR_RESIZE_THUMB_FAILED');				
					exit;			
				}
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				// free up memory
				$image->destroy();
				
				// update image 
				$product_image->force_crop = $force_crop;
				$product_image->original = $original;
				$product_image->filename = $filename;
				$product_image->save();

				echo 'true';
				exit;
				break;
			}
			
			echo Yii::t('global','ERROR_UPLOAD_IMAGE_FAILED');
		}				
	}	
	
	/**
	 * This is the action to load the crop tool
	 */
	public function actionEdit_variants_image_crop($container)
	{
		$id_product_image_variant = (int)$_POST['id_product_image_variant'];
		$id = (int)$_POST['id'];	
		$rotate = (int)$_POST['rotate'];
	
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product_image_variant=:id_product_image_variant AND id=:id'; 
		$criteria->params=array(':id_product_image_variant'=>$id_product_image_variant,':id'=>$id); 			
		
		if (!$product_image = Tbl_ProductImageVariantImage::model()->find($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}				
	
		$this->renderPartial('edit_variants_image_crop',array('container'=>$container, 'product_image'=>$product_image,'rotate'=>$rotate));				
	}
	
	/** 
	 * This is the action to load the image we want to crop
	 */
	public function actionEdit_variants_image_crop_load_image($id_product_image_variant=0, $id_product_image=0, $rotate=0)
	{
		$id_product_image_variant = (int)$id_product_image_variant;
		$id_product_image = (int)$id_product_image;
		$rotate = (int)$rotate;
		$image_base_path = Yii::app()->params['product_images_base_path'];
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product_image_variant=:id_product_image_variant AND id=:id'; 
		$criteria->params=array(':id_product_image_variant'=>$id_product_image_variant,':id'=>$id_product_image); 			
		
		if (!$product_image = Tbl_ProductImageVariantImage::model()->find($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
		$image = new SimpleImage();
		if (!$image->load($image_base_path.'original/'.$product_image->original)) {		
			echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
			exit;
		}
		
		if ($rotate) {
			if ($rotate == -360 || $rotate == 360) { $rotate = 0; }
			else if (!$image->rotate($rotate)) {
				throw new CException(Yii::t('global','ERROR_ROTATE_FAILED'));
			}
		}				
		
		// Set the content type header - in this case image/jpeg
		header('Content-Type: image/jpeg');
		
		// Output the image
		imagejpeg($image->image);			
		
		// Free up memory
		imagedestroy($image->image);		
	}		
	
	/**
	 * This is the action to crop and save image
	 */
	public function actionCrop_and_save_variant()
	{
		$id = (int)$_POST['id'];
		$x = (int)$_POST['x'];
		$y = (int)$_POST['y'];
		$w = (int)$_POST['w'];
		$h = (int)$_POST['h'];
		$rotate = (int)$_POST['rotate'];
		
		if ($w && ($product_image = Tbl_ProductImageVariantImage::model()->findByPk($id))) {
			$app = Yii::app();
			$original = $product_image->original;
			$filename = $product_image->filename;
			$image_base_path = $app->params['product_images_base_path'];
			
			switch ($app->params['images_orientation']) {
				case 'portrait':	
					$default_zoom_width = $app->params['portrait_zoom_width'];
					$default_zoom_height = $app->params['portrait_zoom_height'];
	
					$default_cover_width = $app->params['portrait_cover_width'];
					$default_cover_height = $app->params['portrait_cover_height'];
					
					$default_listing_width = $app->params['portrait_listing_width'];
					$default_listing_height = $app->params['portrait_listing_height'];
	
					$default_suggest_width = $app->params['portrait_suggest_width'];
					$default_suggest_height = $app->params['portrait_suggest_height'];
					
					$default_thumb_width = $app->params['portrait_thumb_width'];
					$default_thumb_height = $app->params['portrait_thumb_height'];						
					break;
				case 'landscape':
					$default_zoom_width = $app->params['landscape_zoom_width'];
					$default_zoom_height = $app->params['landscape_zoom_height'];
	
					$default_cover_width = $app->params['landscape_cover_width'];
					$default_cover_height = $app->params['landscape_cover_height'];
					
					$default_listing_width = $app->params['landscape_listing_width'];
					$default_listing_height = $app->params['landscape_listing_height'];
	
					$default_suggest_width = $app->params['landscape_suggest_width'];
					$default_suggest_height = $app->params['landscape_suggest_height'];
					
					$default_thumb_width = $app->params['landscape_thumb_width'];
					$default_thumb_height = $app->params['landscape_thumb_height'];						
					break;
			}
			
			$image = new SimpleImage();
			if (!$image->load($image_base_path.'original/'.$original)) {
				echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');				
				exit;
			}		
			
			if ($rotate != -360 && $rotate != 360 && $rotate) {
				$image->rotate($rotate);
			}
						
			// crop and save ZOOM
			/*if (!$image->crop($x, $y, $w, $h, $default_zoom_width,$default_zoom_height) || !$image->save($image_base_path.'zoom/'.$filename)) {
				echo Yii::t('global','ERROR_CROP_FAILED');	
				exit;		
			}
			
			// crop and save COVER
			if (!$image->resize($default_cover_width,$default_cover_height) || !$image->save($image_base_path.'cover/'.$filename)) {
				echo Yii::t('global','ERROR_RESIZE_COVER_FAILED');
				exit;									
			}*/
			
			// crop and save LISTING
			if (!$image->crop($x, $y, $w, $h, $default_listing_width,$default_listing_height) || !$image->save($image_base_path.'listing/'.$filename)) {
			//if (!$image->resize($default_listing_width,$default_listing_height) || !$image->save($image_base_path.'listing/'.$filename)) {
				echo Yii::t('global','ERROR_RESIZE_LISTING_FAILED');
				exit;									
			}

			// crop and save SUGGEST
			if (!$image->resize($default_suggest_width,$default_suggest_height) || !$image->save($image_base_path.'suggest/'.$filename)) {
				echo Yii::t('global','ERROR_RESIZE_SUGGEST_FAILED');		
				exit;									
			}
			
			// crop and save THUMB
			if (!$image->resize($default_thumb_width,$default_thumb_height) || !$image->save($image_base_path.'thumb/'.$filename)) {
				echo Yii::t('global','ERROR_RESIZE_THUMB_FAILED');
				exit;	
			}

			// free up memory
			$image->destroy();		
			
			$product_image->force_crop = 0;
			$product_image->save();

			echo 'true';
		} else { 
			echo Yii::t('global','ERROR_CROP_FAILED');
		}
	}	
	
	/**
	 * This is the action to set cover image
	 */
	public function actionSet_cover_image_variant()
	{
		$id = (int)$_POST['id'];
		$id_product_image = (int)$_POST['id_product_image'];
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product_image_variant=:id_product_image_variant AND id=:id_product_image'; 
		$criteria->params=array(':id_product_image_variant'=>$id,':id_product_image'=>$id_product_image); 	
		
		if ($pi = Tbl_ProductImageVariantImage::model()->find($criteria)) {
			$pi->cover = 1;
			if ($pi->save()) {
				$criteria->condition='id_product_image_variant=:id_product_image_variant AND id!=:id_product_image'; 
				
				Tbl_ProductImageVariantImage::model()->updateAll(array('cover'=>0),$criteria);
			}
		}
	}
	
	/**
	 * This is the action to save images sort order
	 */
	public function actionSave_image_sort_order_variant()
	{
		$id = (int)$_POST['id'];
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_product_image) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product_image_variant=:id_product_image_variant AND id=:id_product_image'; 
				$criteria->params=array(':id_product_image_variant'=>$id,':id_product_image'=>$id_product_image); 				
								
				if ($pi = Tbl_ProductImageVariantImage::model()->find($criteria)) {
					$pi->sort_order = $i;
					$pi->save();
					
					++$i;
				}
			}
		}		
	}
	
	/**
	 * This is the action to delete images
	 */
	public function actionDelete_image_variant()
	{
		$id = (int)$_POST['id'];
		// ids
		$ids = $_POST['ids'];
		
		// images base path
		$images_path = Yii::app()->params['product_images_base_path'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_product_image) {
				$this->delete_image_variant($id, $id_product_image);
			}
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product_image_variant=:id_product_image_variant AND cover=1'; 
			$criteria->params=array(':id_product_image_variant'=>$id); 	
				
			if (!Tbl_ProductImageVariantImage::model()->count($criteria)) {
				$criteria->condition='id_product_image_variant=:id_product_image_variant'; 
				$criteria->order='sort_order ASC';
				$criteria->limit=1;
					
				Tbl_ProductImageVariantImage::model()->updateAll(array('cover'=>1),$criteria);
			}	
		}
	}		
	
	public function delete_image_variant($id_product_image_variant, $id_product_image_variant_image=0){
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product_image_variant=:id_product_image_variant'; 
		$criteria->params=array(':id_product_image_variant'=>$id_product_image_variant);
		if ($id_product_image_variant_image) { 
			$criteria->condition .= ' AND id=:id_product_image_variant_image'; 
			$criteria->params[':id_product_image_variant_image']=$id_product_image_variant_image; 
		} 	
		
		$images_path = Yii::app()->params['product_images_base_path'];
		
		foreach (Tbl_ProductImageVariantImage::model()->findAll($criteria) as $row) {			
			$original = $row->original;
			$filename = $row->filename;
			
			// delete files
			if (is_file($images_path.'original/'.$original)) { @unlink($images_path.'original/'.$original); }
			if (is_file($images_path.'cover/'.$filename)) { @unlink($images_path.'cover/'.$filename); }
			if (is_file($images_path.'listing/'.$filename)) { @unlink($images_path.'listing/'.$filename); }
			if (is_file($images_path.'suggest/'.$filename)) { @unlink($images_path.'suggest/'.$filename); }
			if (is_file($images_path.'thumb/'.$filename)) { @unlink($images_path.'thumb/'.$filename); }
			if (is_file($images_path.'zoom/'.$filename)) { @unlink($images_path.'zoom/'.$filename); }
			
			$row->delete();					
		}		
	}
	
	/**
	 * This is the action to get an XML list of the product images
	 */
	public function actionXml_list_variant_images($id=0)
	{
		
		$app = Yii::app();
		
		switch ($app->params['images_orientation']) {
			case 'portrait':
				
				$default_listing_width = $app->params['portrait_listing_width'];
				$default_listing_height = $app->params['portrait_listing_height'];

				break;
			case 'landscape':
				
				$default_listing_width = $app->params['landscape_listing_width'];
				$default_listing_height = $app->params['landscape_listing_height'];
				
				break;
		}
		
		$id = (int)$id;
		
		if (!$product = Tbl_ProductImageVariant::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}				
		
			
		//set content type and xml tag
		header("Content-type:text/xml");		
		
		echo '<data>';
		
		foreach ($product->tbl_product_image_variant_image(array('order'=>'sort_order ASC')) as $row) {
			if (is_file(dirname(__FILE__) . '/../../../images/products/thumb/'.$row->filename)){
			   $image_src = $row->filename;
			   list($width, $height, $type, $attr) = getimagesize(dirname(__FILE__).'/../../../images/products/thumb/'.$image_src);
			}else{ 
				$width = $default_listing_width;
				$height = $default_listing_height;
			}
			echo '<item id="'.$row->id.'">
				<filename><![CDATA['.$row->filename.'?'.time().']]></filename>
				<force_crop><![CDATA['.$row->force_crop.']]></force_crop>
				<cover><![CDATA['.$row->cover.']]></cover>
				<width_current><![CDATA['.$width.']]></width_current>
				<height_current><![CDATA['.$height.']]></height_current>
			</item>';	
		}
		
		echo '</data>';
	}	
	
	
	/**
	 * To Generate all possibilities of combination of variants
	 */
	public function actionGenerate_variants($id=0) 
	{
		$id = (int)$id;
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection		
		
		$array = array();		
		$array_variant_image = array();
		
		$sql = "SELECT 
		product_variant_group.id AS id_product_variant_group,
		product_variant_group_option.id,
		product_variant_group_option_description.name 
		FROM 
		product_variant_group
		INNER JOIN 
		(product_variant_group_option CROSS JOIN product_variant_group_option_description)
		ON 
		(product_variant_group.id = product_variant_group_option.id_product_variant_group AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option AND product_variant_group_option_description.language_code=:language_code)
		WHERE
		product_variant_group.id_product = :id_product
		ORDER BY 
		product_variant_group.sort_order ASC,
		product_variant_group_option.sort_order ASC";				
		$command=$connection->createCommand($sql);	

		$current_id=0;
		foreach ($command->queryAll(true, array(':id_product'=>$id,':language_code'=>Yii::app()->language)) as $row) {	
			if ($current_id != $row['id_product_variant_group']) {
				if ($current_id) {								
					$array_variant_image[$row['id_product_variant_group']][] = $row['id_product_variant_group'].':0';
				}
				$current_id = $row['id_product_variant_group'];
			}		
		
			$array[$row['id_product_variant_group']][] = $row['id_product_variant_group'].':'.$row['id'];
			$array_variant_image[$row['id_product_variant_group']][] = $row['id_product_variant_group'].':'.$row['id'];
		}				
		
		// get current variants
		$sql = "SELECT 
		product_variant.id AS id_product_variant,
		product_variant_group.id AS id_product_variant_group,
		product_variant_group_option.id,
		product_variant_group_option_description.name 
		FROM 
		product_variant
		INNER JOIN 
		product_variant_option 
		ON
		(product_variant.id = product_variant_option.id_product_variant) 
		INNER JOIN
		(product_variant_group CROSS JOIN product_variant_group_option CROSS JOIN product_variant_group_option_description)
		ON 
		(product_variant_option.id_product_variant_group = product_variant_group.id AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option AND product_variant_group_option_description.language_code=:language_code)
		WHERE
		product_variant.id_product = :id_product
		ORDER BY 
		product_variant.id ASC,
		product_variant_group.sort_order ASC,
		product_variant_group_option.sort_order ASC";				
		$command=$connection->createCommand($sql);	
	
		$variants = array();
		foreach ($command->queryAll(true, array(':id_product'=>$id,':language_code'=>Yii::app()->language)) as $row) {	
			$variants[$row['id_product_variant']][] = $row['id_product_variant_group'].':'.$row['id'];
		}	
		
		if (sizeof($array)) {
			
			$array = array_slice($array,0,sizeof($array));
	
			$this->showCombinations('',$array, 0);
			
			if (sizeof($this->variant_combinations)) {									
				// check if any variants is missing
				$variants_missing=array();
				
				// existing variants
				$variants_exist=array();
				
				// generate variant codes
				$variant_codes=array();
								
				foreach ($this->variant_combinations as $key => $row) {
					if (!in_array($row,$variants)) $variants_missing[$key] = $row;
					else $variants_exist[$key] = array_search($row,$variants);
					
					$variant_codes[$key] = implode(",",$row);
				}
				
				// add missing variants
				if (sizeof($variants_missing)) {
					$model_product_default = Tbl_Product::model()->findByPk($id);	
					
					$criteria=new CDbCriteria; 
					$criteria->condition='id_product=:id_product'; 
					$criteria->params=array(':id_product'=>$id); 
					
					$compteur_sku = Tbl_ProductVariant::model()->count($criteria)+1;							
					if (!$compteur_sku) $compteur_sku=1;						
					
					foreach ($variants_missing as $key => $rows) {
						$model = new Tbl_ProductVariant;
						$model->id_product = $id;
						$model->variant_code = isset($variant_codes[$key]) ? $variant_codes[$key]:'';
						$model->sku = $model_product_default->sku . "-" . $compteur_sku;
						$model->qty = $model_product_default->qty;
						$model->notify_qty = $model_product_default->notify_qty;
						$model->weight = $model_product_default->weight;
						$model->in_stock = $model_product_default->in_stock;
						$model->id_user_created = $current_id_user;
						$model->id_user_modified = $current_id_user;
						$model->date_created = $current_datetime;
						$model->date_modified = $current_datetime;
						if ($model->save()) {					
							foreach ($rows as $row) {
								list($id_product_variant_group, $id_product_variant_group_option) = explode(':',$row);
								
								$model_option = new Tbl_ProductVariantOption;
								$model_option->id_product_variant = $model->id;
								$model_option->id_product_variant_group = $id_product_variant_group;
								$model_option->id_product_variant_group_option = $id_product_variant_group_option;
								
								if (!$model_option->save()) {					
									throw new CException(Yii::t('global','ERROR_GENERATING_VARIANT_OPTIONS'));		
								}
							}							
						}	

						$variants[$model->id] = $rows;							

						++$compteur_sku;														
					}
				}
								 
				// existing variants
				if (sizeof($variants_exist)) {
					foreach ($variants_exist as $key => $value) {
						if ($model = Tbl_ProductVariant::model()->findByPk($value)) {
							$model->variant_code = isset($variant_codes[$key]) ? $variant_codes[$key]:'';							
							$model->save();
						}
					}
				}
				
				// reorder the variants list
				$sql = 'UPDATE
				product_variant
				SET 
				sort_order = :i 
				WHERE
				id = :id
				LIMIT 1';
				$command=$connection->createCommand($sql);						
				
				$i=0;
				foreach ($this->variant_combinations as $key => $row) {
					$id_product_variant = array_search($row,$variants);
					
					if ($id_product_variant) {
						$command->execute(array(':i'=>$i,':id'=>$id_product_variant));
						
						++$i;	
					}
				}
			} else {
				throw new CException(Yii::t('global','ERROR_GENERATING_VARIANT'));	
			}
			
			// get current variant images
			$sql = "SELECT 
			product_image_variant.id AS id_product_image_variant,
			product_variant_group.id AS id_product_variant_group,
			IF(product_variant_group_option.id IS NOT NULL,product_variant_group_option.id,0) AS id
			FROM 
			product_image_variant
			INNER JOIN 
			product_image_variant_option 
			ON
			(product_image_variant.id = product_image_variant_option.id_product_image_variant) 
			INNER JOIN
			product_variant_group
			ON 
			(product_image_variant_option.id_product_variant_group = product_variant_group.id)
			LEFT JOIN 
			product_variant_group_option 
			ON
			(product_image_variant_option.id_product_variant_group_option = product_variant_group_option.id)
			WHERE
			product_image_variant.id_product = :id_product
			ORDER BY 
			product_image_variant.id ASC,
			product_variant_group.sort_order ASC,
			product_variant_group_option.sort_order ASC";				
			$command=$connection->createCommand($sql);	
		
			$variants = array();
			foreach ($command->queryAll(true, array(':id_product'=>$id)) as $row) {	
				$variants[$row['id_product_image_variant']][] = $row['id_product_variant_group'].':'.$row['id'];
			}	
			
			if (sizeof($array_variant_image)) {
				$this->variant_combinations = array();
								
				$array_variant_image = array_slice($array_variant_image,0,sizeof($array_variant_image));
		
				$this->showCombinations('',$array_variant_image, 0);
				
				if (sizeof($this->variant_combinations)) {		
					// loop through generated list and remove invalid combinations					
					foreach ($this->variant_combinations as $key => $row_combo) {
						$i=0;
						$x=0;
						foreach ($row_combo as $k => $r) {
							list($id_group,$id_option) = explode(':',$r);
							
							if ($id_option == 0) ++$i;
							else if ($id_option != 0 && $i) {
								$x=1;
								break;	
							}
						}
						if ($x) unset($this->variant_combinations[$key]);
					}
				
					// check if any variants is missing
					$variants_missing=array();
					
					// existing variants
					$variants_exist=array();
					
					// generate variant codes
					$variant_codes=array();					
					foreach ($this->variant_combinations as $key => $row) {
						if (!in_array($row,$variants)) $variants_missing[$key] = $row;
						else $variants_exist[$key] = array_search($row,$variants);
						
						$variant_codes[$key] = str_replace(':0','',implode(",",$row));
					}		
											
					// add missing variants
					if (sizeof($variants_missing)) {
						foreach ($variants_missing as $key => $rows) {												
							$model = new Tbl_ProductImageVariant;
							$model->id_product = $id;
							$model->variant_code = isset($variant_codes[$key]) ? $variant_codes[$key]:'';
							$model->id_user_created = $current_id_user;
							$model->date_created = $current_datetime;
							
							if ($model->save()) {
								foreach ($rows as $row) {
									list($id_product_variant_group, $id_product_variant_group_option) = explode(':',$row);
									
									//if ($id_product_variant_group_option) {
										$model_option = new Tbl_ProductImageVariantOption;
										$model_option->id_product_image_variant = $model->id;
										$model_option->id_product_variant_group = $id_product_variant_group;
										$model_option->id_product_variant_group_option = $id_product_variant_group_option;
										
										if (!$model_option->save()) {					
											throw new CException(Yii::t('global','ERROR_GENERATING_VARIANT_OPTIONS'));		
										}
									//}
								}
								
							}		
							
							$variants[$model->id] = $rows;						
						}
					}
													 
					// existing variants
					if (sizeof($variants_exist)) {
						foreach ($variants_exist as $key => $value) {
							if ($model = Tbl_ProductImageVariant::model()->findByPk($value)) {
								$model->variant_code = isset($variant_codes[$key]) ? $variant_codes[$key]:'';							
								$model->save();
							}
						}
					}					
					
					// reorder the variants list
					$sql = 'UPDATE
					product_image_variant
					SET 
					sort_order = :i 
					WHERE
					id = :id
					LIMIT 1';
					$command=$connection->createCommand($sql);						
					
					$i=0;
					foreach ($this->variant_combinations as $key => $row) {
						$id_product_variant = array_search($row,$variants);
						
						if ($id_product_variant) {
							$command->execute(array(':i'=>$i,':id'=>$id_product_variant));
							
							++$i;	
						}
					}	
				}
			}		
		}					
		
		// generate variant names
		$sql = 'SELECT 
		product_variant.id,
		GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant
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
		AND product_variant_group_option_description.language_code = :language_code
		AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
		AND product_variant_group_description.language_code = product_variant_group_option_description.language_code)
		WHERE
		product_variant.id_product = :id_product
		GROUP BY 
		product_variant.id
		ORDER BY 
		product_variant.sort_order ASC';
		$command=$connection->createCommand($sql);	
		
		$sql = 'REPLACE INTO 
		product_variant_description
		SET
		id_product_variant = :id_product_variant,
		language_code = :language_code,
		name = :name';
		$command_add = $connection->createCommand($sql);	

		foreach (Tbl_Language::model()->active()->findAll() as $value) {			
			foreach ($command->queryAll(true,array(':language_code'=>$value->code,':id_product'=>$id)) as $row) {
				$command_add->execute(array(':id_product_variant'=>$row['id'],':language_code'=>$value->code,':name'=>$row['variant']));
			}
		}
		
		$sql = 'SELECT 
		product_image_variant.id,
		GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant
		FROM		
		product_image_variant
		INNER JOIN 
		(product_image_variant_option
		CROSS JOIN product_variant_group 
		CROSS JOIN product_variant_group_option 
		CROSS JOIN product_variant_group_option_description
		CROSS JOIN product_variant_group_description)						
		ON
		(product_image_variant.id = product_image_variant_option.id_product_image_variant
		AND product_image_variant_option.id_product_variant_group = product_variant_group.id 
		AND product_image_variant_option.id_product_variant_group_option = product_variant_group_option.id 
		AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
		AND product_variant_group_option_description.language_code = :language_code
		AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
		AND product_variant_group_description.language_code = product_variant_group_option_description.language_code)
		WHERE
		product_image_variant.id_product = :id_product
		GROUP BY 
		product_image_variant.id
		ORDER BY 
		product_image_variant.sort_order ASC';
		$command=$connection->createCommand($sql);	
		
		$sql = 'REPLACE INTO 
		product_image_variant_description
		SET
		id_product_image_variant = :id_product_image_variant,
		language_code = :language_code,
		name = :name';
		$command_add = $connection->createCommand($sql);	

		foreach (Tbl_Language::model()->active()->findAll() as $value) {			
			foreach ($command->queryAll(true,array(':language_code'=>$value->code,':id_product'=>$id)) as $row) {
				$command_add->execute(array(':id_product_image_variant'=>$row['id'],':language_code'=>$value->code,':name'=>$row['variant']));
			}
		}		
	}	

    function showCombinations($string, $traits, $i)
    {		
        if ($i >= sizeof($traits))
            $this->variant_combinations[] = explode(',',trim($string,' ,'));
        else
        {			
            foreach ($traits[$i] as $key => $trait) {
                $this->showCombinations("$string,$trait", $traits, $i+1);
			}
        }
		
    }
	
	public function reorder_variants($id=0)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection		

		$id=(int)$id;
		
		$sql = "SELECT 
		product_variant_group.id AS id_product_variant_group,
		product_variant_group_option.id
		FROM 
		product_variant_group
		INNER JOIN 
		product_variant_group_option
		ON 
		(product_variant_group.id = product_variant_group_option.id_product_variant_group)
		WHERE
		product_variant_group.id_product = :id_product
		ORDER BY 
		product_variant_group.sort_order ASC,
		product_variant_group_option.sort_order ASC";				
		$command=$connection->createCommand($sql);	

		$current_id=0;
		foreach ($command->queryAll(true, array(':id_product'=>$id)) as $row) {	
			if ($current_id != $row['id_product_variant_group']) {
				if ($current_id) {								
					$array_variant_image[$row['id_product_variant_group']][] = $row['id_product_variant_group'].':0';
				}
				$current_id = $row['id_product_variant_group'];
			}		
		
			$array[$row['id_product_variant_group']][] = $row['id_product_variant_group'].':'.$row['id'];
			$array_variant_image[$row['id_product_variant_group']][] = $row['id_product_variant_group'].':'.$row['id'];
		}
		
		// get current variants
		$sql = "SELECT 
		product_variant.id AS id_product_variant,
		product_variant_group.id AS id_product_variant_group,
		product_variant_group_option.id
		FROM 
		product_variant
		INNER JOIN 
		product_variant_option 
		ON
		(product_variant.id = product_variant_option.id_product_variant) 
		INNER JOIN
		(product_variant_group CROSS JOIN product_variant_group_option)
		ON 
		(product_variant_option.id_product_variant_group = product_variant_group.id AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id)
		WHERE
		product_variant.id_product = :id_product
		ORDER BY 
		product_variant.id ASC,
		product_variant_group.sort_order ASC,
		product_variant_group_option.sort_order ASC";				
		$command=$connection->createCommand($sql);	
	
		$variants = array();
		foreach ($command->queryAll(true, array(':id_product'=>$id)) as $row) {	
			$variants[$row['id_product_variant']][] = $row['id_product_variant_group'].':'.$row['id'];
		}	
		
		if (sizeof($array)) {
			
			$array = array_slice($array,0,sizeof($array));
	
			$this->showCombinations('',$array, 0);
			
			if (sizeof($this->variant_combinations)) {									
				// reorder the variants list
				$sql = 'UPDATE
				product_variant
				SET 
				sort_order = :i 
				WHERE
				id = :id
				LIMIT 1';
				$command=$connection->createCommand($sql);						
		
				$i=0;
				foreach ($this->variant_combinations as $key => $row) {
					$id_product_variant = array_search($row,$variants);
					
					if ($id_product_variant) {
						$command->execute(array(':i'=>$i,':id'=>$id_product_variant));
						
						++$i;	
					}
				}	
			}
		}
		
		// get current variant images
		$sql = "SELECT 
		product_image_variant.id AS id_product_image_variant,
		product_variant_group.id AS id_product_variant_group,
		IF(product_variant_group_option.id IS NOT NULL,product_variant_group_option.id,0) AS id
		FROM 
		product_image_variant
		INNER JOIN 
		product_image_variant_option 
		ON
		(product_image_variant.id = product_image_variant_option.id_product_image_variant) 
		INNER JOIN
		product_variant_group
		ON 
		(product_image_variant_option.id_product_variant_group = product_variant_group.id)
		LEFT JOIN 
		product_variant_group_option 
		ON
		(product_image_variant_option.id_product_variant_group_option = product_variant_group_option.id)
		WHERE
		product_image_variant.id_product = :id_product
		ORDER BY 
		product_image_variant.id ASC,
		product_variant_group.sort_order ASC,
		product_variant_group_option.sort_order ASC";				
		$command=$connection->createCommand($sql);	
	
		$variants = array();
		foreach ($command->queryAll(true, array(':id_product'=>$id)) as $row) {	
			$variants[$row['id_product_image_variant']][] = $row['id_product_variant_group'].':'.$row['id'];
		}	
		
		if (sizeof($array_variant_image)) {
			$this->variant_combinations = array();
							
			$array_variant_image = array_slice($array_variant_image,0,sizeof($array_variant_image));
	
			$this->showCombinations('',$array_variant_image, 0);
			
			if (sizeof($this->variant_combinations)) {	
				// loop through generated list and remove invalid combinations					
				foreach ($this->variant_combinations as $key => $row_combo) {
					$i=0;
					$x=0;
					foreach ($row_combo as $k => $r) {
						list($id_group,$id_option) = explode(':',$r);
						
						if ($id_option == 0) ++$i;
						else if ($id_option != 0 && $i) {
							$x=1;
							break;	
						}
					}
					if ($x) unset($this->variant_combinations[$key]);
				}			
											
				// reorder the variants list
				$sql = 'UPDATE
				product_image_variant
				SET 
				sort_order = :i 
				WHERE
				id = :id
				LIMIT 1';
				$command=$connection->createCommand($sql);						
				
				$i=0;
				foreach ($this->variant_combinations as $key => $row) {
					$id_product_variant = array_search($row,$variants);
					
					if ($id_product_variant) {
						$command->execute(array(':i'=>$i,':id'=>$id_product_variant));
						
						++$i;	
					}
				}	
			}
		}				
	}
	
	
	public function actionGenerate_variants_all()
	{
		foreach (Tbl_Product::model()->findAll() as $row) $this->actionGenerate_variants($row->id);	
	}
	
	//---------------------------------------------------------------------------
	
	
			
	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_variants($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('product_variant.id_product=:id_product');
		$params=array(':id_product'=>$id,':language_code'=>Yii::app()->language);
		
		// filters											
		
		$sql = 'SELECT 
		COUNT(product_variant.id) AS total
		FROM 
		product_variant 
		INNER JOIN 
		(
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
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').' 
		GROUP BY 
		product_variant.id';	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = 'SELECT 
		product_variant.id,
		GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS name,
		product_variant.sku,
		product_variant.qty, 
		product_variant.price,
		product_variant.price_type,
		product_variant.active,
		product.price AS product_price
		FROM 
		product_variant 
		INNER JOIN 
		(
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
		INNER JOIN 
		product 
		ON 
		(product_variant.id_product = product.id)					
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').' 
		GROUP BY 
		product_variant.id';

		// sorting

		$sql.=" ORDER BY product_variant.sort_order ASC";
		
		//add limits to query to get only rows necessary for the output
		//$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		//echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		echo '<rows>';				
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
			<cell type="ro">'.$row['qty'].'</cell>
			<cell type="ro"><![CDATA['.($row['price_type']?$row['price'].'%':Html::nf($row['price'])).']]></cell>
			<cell type="ro"><![CDATA['.($row['price_type']?(Html::nf($row['product_price']+($row['product_price']*$row['price']/100))):Html::nf($row['product_price']+$row['price'])).']]></cell>
			<cell type="ch">'.$row['active'].'</cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	public function actionAdd_variant_template($container, $id=0)
	{
		$model = new ProductsAddVariantTemplateForm;
		
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (Tbl_Product::model()->count($criteria)) {
			$model->id_product = $id;
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
		$this->renderPartial('add_variant_template',array('model'=>$model,'container'=>$container));				
	}
	
	public function actionCount_variant_groups($id)
	{
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product'; 
		$criteria->params=array(':id_product'=>$id); 			
		
		echo Tbl_ProductVariantGroup::model()->count($criteria);
	}	
	
	public function actionSave_variant_template()
	{
		$model = new ProductsAddVariantTemplateForm;
		
		// collect user input data
		if(isset($_POST['ProductsAddVariantTemplateForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsAddVariantTemplateForm'] as $name=>$value)
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
	
	public function actionLoad_variant_template($container)
	{		
		$this->renderPartial('load_variant_template',array('container'=>$container));				
	}	
	
	public function actionApply_variant_template($id=0)
	{
		$app = Yii::app();
		$id = (int)$id;
		$id_tpl_product_variant_category = (int)$_POST['id_tpl_product_variant_category'];
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();	
		$image_base_path = $app->params['product_images_base_path'].'swatch/';	
		
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection			
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (Tbl_Product::model()->count($criteria)) {
			$model->id_product = $id;
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		// delete any previous variants for this product
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		// prepare delete variant group options
		$command_delete_groups=$connection->createCommand('DELETE FROM 
		product_variant_group_option,
		product_variant_group_option_description
		USING 
		product_variant_group_option 
		LEFT JOIN 
		product_variant_group_option_description 
		ON
		(product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option) 
		WHERE 
		product_variant_group_option.id_product_variant_group=:id_product_variant_group');			
		
		// prepare delete variants
		$command_delete_variants=$connection->createCommand('DELETE FROM 
		product_variant,
		product_variant_option,
		product_variant_option2
		USING 
		product_variant 
		LEFT JOIN 
		product_variant_option 
		ON
		(product_variant.id = product_variant_option.id_product_variant) 
		LEFT JOIN 
		product_variant_option AS product_variant_option2
		ON 
		(product_variant.id = product_variant_option2.id_product_variant)
		WHERE 
		product_variant_option.id_product_variant_group=:id_product_variant_group');		
		
		foreach (Tbl_ProductVariantGroup::model()->findAll($criteria) as $row) {
			$criteria2=new CDbCriteria; 
			$criteria2->condition='id_product_variant_group=:id_product_variant_group'; 
			$criteria2->params=array(':id_product_variant_group'=>$row->id); 		
			
			Tbl_ProductVariantGroupDescription::model()->deleteAll($criteria2);			
																	
			// group options	
			foreach (Tbl_ProductVariantGroupOption::model()->findAll($criteria2) as $row_group_option){
				$this->delete_variant_group_option_swatch_image($row_group_option->id);
				//$this->delete_image_variant_group_option($row_group_option->id);	
			}				
			
			// delete variant group options						
			$command_delete_groups->execute(array(':id_product_variant_group'=>$row->id));		

			// variants			
			foreach (Tbl_ProductVariantOption::model()->findAll($criteria2) as $row_variant_option){
				$this->delete_image_variant($row_variant_option->id_product_variant);	
			}
			
			// delete variants							
			$command_delete_variants->execute(array(':id_product_variant_group'=>$row->id));						
			
			$row->delete();
		}
		
		// Delete image variant
		$command_delete_image_variant=$connection->createCommand('DELETE FROM
		product_image_variant,
		product_image_variant_option,
		product_image_variant_description
		USING
		product_image_variant
		LEFT JOIN
		product_image_variant_option
		ON
		(product_image_variant.id = product_image_variant_option.id_product_image_variant)
		LEFT JOIN 
		product_image_variant_description
		ON
		(product_image_variant.id = product_image_variant_description.id_product_image_variant)
		WHERE
		product_image_variant.id_product=:id');		
		$command_delete_image_variant->execute(array(':id'=>$id));
		
		// apply template
		$criteria=new CDbCriteria; 
		$criteria->condition='id_tpl_product_variant_category=:id_tpl_product_variant_category'; 
		$criteria->params=array(':id_tpl_product_variant_category'=>$id_tpl_product_variant_category); 			
		
		foreach (Tbl_TplProductVariantGroup::model()->findAll($criteria) as $row){
			$model = new Tbl_ProductVariantGroup;
			$model->id_product = $id;
			$model->input_type = $row->input_type;
			$model->sort_order = $row->sort_order;
			$model->id_user_created = $current_id_user;
			$model->id_user_modified = $current_id_user;
			$model->date_created = $current_datetime;
			
			if ($model->save()){
				foreach ($row->tbl_tpl_product_variant_group_description as $row_description) {
					$model_description = new Tbl_ProductVariantGroupDescription;
					$model_description->id_product_variant_group = $model->id;
					$model_description->language_code = $row_description->language_code;
					$model_description->name = $row_description->name;
					$model_description->description = $row_description->description;
					
					if (!$model_description->save()){
						$model->delete();
						
						throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));		
					}
				}
				
				foreach ($row->tbl_tpl_product_variant_group_option as $row_option) {
					$model_option = new Tbl_ProductVariantGroupOption;
					$model_option->id_product_variant_group = $model->id;
					$model_option->swatch_type = $row_option->swatch_type;
					$model_option->color = $row_option->color;
					$model_option->color2 = $row_option->color2;
					$model_option->color3 = $row_option->color3;
					$model_option->filename = $row_option->filename;
					$model_option->sort_order = $row_option->sort_order;
					$model_option->id_user_created = $current_id_user;
					$model_option->id_user_modified = $current_id_user;
					$model_option->date_created = $current_datetime;					
					
					if ($model_option->save()){
						if (!empty($row_option->filename)) {
							$new_filename = md5($model_option->id.time().$row_option->filename).'.jpg';
							
							if (is_file($image_base_path.$row_option->filename)) {
								copy($image_base_path.$row_option->filename,$image_base_path.$new_filename);
							}													
							
							$model_option->filename = $new_filename;								
							$model_option->save();								
						}
						
						foreach ($row_option->tbl_tpl_product_variant_group_option_description as $row_option_description) {
							$model_option_description = new Tbl_ProductVariantGroupOptionDescription;	
							$model_option_description->id_product_variant_group_option = $model_option->id;
							$model_option_description->language_code = $row_option_description->language_code;
							$model_option_description->name = $row_option_description->name;
							
							if (!$model_option_description->save()) {
								throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));	
							}
						}						
					} else {
						$model->delete();
						
						throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));		
					}	
				}
				
				$criteria2=new CDbCriteria; 
				$criteria2->condition='id_tpl_product_variant_category=:id_tpl_product_variant_category'; 
				$criteria2->params=array(':id_tpl_product_variant_category'=>$id_tpl_product_variant_category); 					
				
			} else {
				throw new CException(Yii::t('global','ERROR_SAVING'));	
			}
		}
		
		Tbl_Product::model()->updateByPk($id,array('has_variants'=>1));	
				
	}
	
	/**

	 * This is the action to get a list of products
	 */ 
	public function actionXml_list_search_variant_template($pos=0,$mask='')
	{
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection		
		
		$pos = (int)$pos;
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?>';
		
		if (!empty($mask)) {
			//create query 
			$sql = "SELECT 
			tpl_product_variant_category.id,
			tpl_product_variant_category.name 
			FROM 
			tpl_product_variant_category
			WHERE 			
			tpl_product_variant_category.name LIKE CONCAT('%',:mask,'%')
			ORDER BY 
			IF(tpl_product_variant_category.name LIKE CONCAT(:mask,'%'),1,0) DESC, tpl_product_variant_category.name ASC";				
			$command=$connection->createCommand($sql);
						
			echo '<complete>';
			
			foreach ($command->queryAll(true, array(':mask'=>$mask)) as $row) {
				echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
			}
			
			echo '</complete>';
		}
	}	
	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_load_variant_group_template($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		// filters	
		
		$where=array();
		$params=array();
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'tpl_product_variant_category.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}						
		
		$sql = "SELECT 
		COUNT(tpl_product_variant_category.id) AS total 
		FROM 
		tpl_product_variant_category
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	;	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT 
		tpl_product_variant_category.id,
		tpl_product_variant_category.name
		FROM 
		tpl_product_variant_category
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	;		

		
		// sorting

		if (isset($filters['name']) && !empty($filters['name'])) {
			$sql.=" ORDER BY 
			IF(tpl_product_variant_category.name LIKE CONCAT(:name,'%'),1,0) DESC, tpl_product_variant_category.name ASC";
		}else{
			$sql.=" ORDER BY tpl_product_variant_category.name ASC";
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
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_load_variant_template($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id = (int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('tpl_product_variant_group.id_tpl_product_variant_category=:id_tpl_product_variant_category');
		$params=array(':id_tpl_product_variant_category'=>$id);
		
		// filters							
		
		$sql = "SELECT 
		COUNT(tpl_product_variant_group_option.id) AS total 
		FROM 
		tpl_product_variant_group_option 
		INNER JOIN 
		tpl_product_variant_group_option_description 
		ON 
		(tpl_product_variant_group_option.id = tpl_product_variant_group_option_description.id_tpl_product_variant_group_option AND tpl_product_variant_group_option_description.language_code = '".Yii::app()->language."') 
		INNER JOIN
		tpl_product_variant_group
		ON 
		(tpl_product_variant_group_option.id_tpl_product_variant_group = tpl_product_variant_group.id) 
		INNER JOIN 
		tpl_product_variant_group_description
		ON
		(tpl_product_variant_group.id = tpl_product_variant_group_description.id_tpl_product_variant_group AND tpl_product_variant_group_description.language_code = '".Yii::app()->language."') 
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
		tpl_product_variant_group_option.id,
		tpl_product_variant_group_option_description.name,
		tpl_product_variant_group_description.name AS group_name
		FROM 
		tpl_product_variant_group_option 
		INNER JOIN 
		tpl_product_variant_group_option_description 
		ON 
		(tpl_product_variant_group_option.id = tpl_product_variant_group_option_description.id_tpl_product_variant_group_option AND tpl_product_variant_group_option_description.language_code = '".Yii::app()->language."') 
		INNER JOIN
		tpl_product_variant_group
		ON 
		(tpl_product_variant_group_option.id_tpl_product_variant_group = tpl_product_variant_group.id) 
		INNER JOIN 
		tpl_product_variant_group_description
		ON
		(tpl_product_variant_group.id = tpl_product_variant_group_description.id_tpl_product_variant_group AND tpl_product_variant_group_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		$sql.=" ORDER BY 
		tpl_product_variant_group.sort_order ASC,
		tpl_product_variant_group_option.sort_order ASC";
		
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
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['group_name'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	

	/************************************************************
	*															*
	*															*
	*					COMBO PRODUCTS							*
	*															*
	*															*
	************************************************************/
		
	
	public function actionXml_list_combo_products_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

			$id = (int)$id;
					
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 		
			
			if (!Tbl_Product::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}							
			
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array('product.id != :id AND product.active=1 AND product.product_type=0 AND (product_combo.id IS NULL OR product_variant.id IS NOT NULL)');
			$params=array(':id'=>$id,':language_code'=>Yii::app()->language);
			
			// filters
			
			// name
			if (isset($filters['name']) && !empty($filters['name'])) {
				$where[] = 'product_description.name LIKE CONCAT("%",:name,"%")';
				$params[':name']=$filters['name'];
			}		
			
			// sku
			if (isset($filters['sku']) && !empty($filters['sku'])) {
				$where[] = 'product.sku LIKE CONCAT("%",:sku,"%")';
				$params[':sku']=$filters['sku'];
			}		
						
			// price
			if (isset($filters['price']) && !empty($filters['price'])) {
				// <=N
				if (preg_match('/^<=([0-9\.]+)$/', $filters['price'])) {
					$where[] = 'price <= :price';
					$params[':price']=ltrim($filters['price'],'<=');
				// >=N
				} else if (preg_match('/^>=([0-9\.]+)$/', $filters['price'])) {
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
			COUNT(id)
			FROM
			(SELECT 
			product.id
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
			LEFT JOIN 
			product_combo  
			ON
			(product.id = product_combo.id_combo_product AND product_combo.id_product = :id)
			'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').'
			GROUP BY
			product.id) AS t';			
			
			//if this is the first query - get total number of records in the query result
			if($posStart==0){
				/* Select queries return a resultset */
				$command=$connection->createCommand($sql);
				$row = $command->queryRow(true, $params);
				$totalCount = $row['total'];		
			}
			//echo $sql;
			
			//create query 
			
			/*$sql = 'SELECT 
			id, 
			sku,
			name,
			IF(NOW() BETWEEN special_price_from_date AND special_price_to_date,special_price,price) AS price			
			FROM 
			(SELECT 
			product.id, 
			product.sku,
			product_description.name,
			product.special_price_from_date,
			product.special_price_to_date,
			product.special_price,
			product.price
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
			LEFT JOIN 
			product_combo  
			ON
			(product.id = product_combo.id_combo_product AND product_combo.id_product = :id)
			'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').'
			GROUP BY
			product.id) AS t';*/
			
			$sql = 'SELECT 
			product.id, 
			product.sku,
			product_description.name,
			IF(NOW() BETWEEN product.special_price_from_date AND product.special_price_to_date,product.special_price,product.price) AS price			
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
			LEFT JOIN 
			product_combo  
			ON
			(product.id = product_combo.id_combo_product AND product_combo.id_product = :id)
			'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').'
			GROUP BY
			product.id';
				
			// sorting
			
			
			// name
			if (isset($sort_col[1]) && !empty($sort_col[1])) {
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY name ".$direct;
			// sku
			} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
				$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY sku ".$direct;								
			// price
			} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
				$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY price ".$direct;								
				
			}else{
				$sql.=" ORDER BY name ASC";
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
				echo '<row id="'.$row['id'].':0">
				<cell type="ch" />
				<cell type="ro"><![CDATA['.$row['name'].']]></cell>
				<cell type="ro"><![CDATA['.$row['sku'].']]></cell>		
				<cell type="ro"><![CDATA['.Html::nf($row['price']).']]></cell>
				</row>';
			}
			
			echo '</rows>';
			
		
	}			
	
	public function actionAdd_combo_product($id=0)
	{
		
		$id = (int)$id;
		$ids = $_POST['ids'];
		$default_qty = (int)$_POST['default_qty'];
		$default_qty = $default_qty ? $default_qty:1;
		
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = Yii::app()->user->getId();
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		if (!$model = Tbl_Product::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}					
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $value) {	
				list($id_product,$id_product_variant) = explode(':',$value);
				/*
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND id_combo_product=:id_combo_product'; 
				$criteria->params=array(':id_product'=>$id,':id_combo_product'=>$id_product);
				*/
				
				$ps = new Tbl_ProductCombo;
				$ps->id_product = $id;
				$ps->id_combo_product = $id_product;
				$ps->qty = $default_qty;
				$ps->id_user_created = $current_id_user;
				$ps->date_created = $current_datetime;
				if (!$ps->save()){
					echo 'false';
					exit;
				}	
			}
		}	

		// update combo cost_price, price and sell_price
		$sql = 'SELECT
		get_product_cost_price(:id_product,0) AS cost_price,
		get_combo_base_price(:id_product) AS price,
		get_product_current_price(:id_product,0,0) AS sell_price';									
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true, array(':id_product'=>$id));
		
		$model->cost_price = $row['cost_price'];
		$model->price = $row['price'];
		$model->sell_price = $row['sell_price'];	
		$model->save();
	}	
	
	public function actionXml_list_combo_products($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

			$id = (int)$id;
					
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 		
			
			if (!Tbl_Product::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}							
			
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array('product_combo.id_product = :id');
			$params=array(':id'=>$id,':language_code'=>Yii::app()->language);
			
			// filters
			
			// name
			if (isset($filters['name']) && !empty($filters['name'])) {
				$where[] = 'product_description.name LIKE CONCAT("%",:name,"%")';
				$params[':name']=$filters['name'];
			}		
			
			// sku
			if (isset($filters['sku']) && !empty($filters['sku'])) {
				$where[] = 'product.sku LIKE CONCAT("%",:sku,"%")';
				$params[':sku']=$filters['sku'];
			}		
			
			// price
			if (isset($filters['price']) && !empty($filters['price'])) {
				// <=N
				if (preg_match('/^<=([0-9\.]+)$/', $filters['price'])) {
					$where[] = 'price <= :price';
					$params[':price']=ltrim($filters['price'],'<=');
				// >=N
				} else if (preg_match('/^>=([0-9\.]+)$/', $filters['price'])) {
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
			COUNT(product_combo.id) AS total
			FROM 
			product_combo
			INNER JOIN
			(product CROSS JOIN product_description)
			ON
			(product_combo.id_combo_product = product.id AND product.id = product_description.id_product AND product_description.language_code = :language_code)
			LEFT JOIN 
			product_variant
			ON
			(product_combo.id_combo_product = product_variant.id_product)				
			'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').'
			GROUP BY 
			product_combo.id';	
			
			
				
			//if this is the first query - get total number of records in the query result
			if($posStart==0){
				/* Select queries return a resultset */
				$command=$connection->createCommand($sql);
				$row = $command->queryRow(true, $params);
				$totalCount = $row['total'];		
			}
			
			
			//create query 
			
			$sql = 'SELECT 
			product_combo.id,
			product.sku,
			product_description.name,
			product_combo.qty,
			product.price,
			product_variant.id AS id_product_variant,
			product.active
			FROM 
			product_combo
			INNER JOIN
			(product CROSS JOIN product_description)
			ON
			(product_combo.id_combo_product = product.id AND product.id = product_description.id_product AND product_description.language_code = :language_code)
			LEFT JOIN 
			product_variant
			ON
			(product_combo.id_combo_product = product_variant.id_product)	
			'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').'
			GROUP BY 
			product_combo.id';	
	
				
			// sorting
			
			// name
			if (isset($sort_col[1]) && !empty($sort_col[1])) {
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY name ".$direct;
			// sku
			} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
				$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY sku ".$direct;								
			// price
			} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
				$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY price ".$direct;								
				
			}else{
				$sql.=" ORDER BY id ASC";
			}	
			
			//add limits to query to get only rows necessary for the output
			$sql.= " LIMIT ".$posStart.",".$count;
			
			$command=$connection->createCommand($sql);
			
			$sql = 'SELECT 
			product_combo_variant.id,
			product_variant.sku,
			product_variant.active,
			GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
			IF(product_variant.price_type=0,IF(NOW() BETWEEN product.special_price_from_date AND product.special_price_to_date,product.special_price,product.price)+product_variant.price,IF(NOW() BETWEEN product.special_price_from_date AND product.special_price_to_date,product.special_price,product.price)+ROUND((IF(NOW() BETWEEN product.special_price_from_date AND product.special_price_to_date,product.special_price,product.price)*product_variant.price)/100,2)) AS price
			FROM 
			product_combo_variant
			INNER JOIN 
			(product_combo CROSS JOIN product CROSS JOIN product_description)
			ON
			(product_combo_variant.id_product_combo = product_combo.id AND product_combo.id_combo_product = product.id AND product.id = product_description.id_product AND product_description.language_code = :language_code)
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
				product_combo_variant.id_product_variant = product_variant.id AND product_variant.active = 1
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
			product_combo_variant.id_product_combo=:id_product_combo						
			GROUP BY 
			product.id,
			product_variant.id';		
			$command_variant=$connection->createCommand($sql);			
			
			
			//set content type and xml tag
			header("Content-type:text/xml");
			
			//output data in XML format   
			echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
			
			// Cycle through results
			foreach ($command->queryAll(true, $params) as $row) {
				echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
				<cell />
				<cell><![CDATA['.$row['name'].']]></cell>
				<cell><![CDATA['.$row['sku'].']]></cell>		
				<cell><![CDATA['.$row['qty'].']]></cell>						
				<cell><![CDATA['.$row['id_product_variant'].']]></cell>
				<cell><![CDATA['.Html::nf($row['price']).']]></cell>';
				
				foreach ($command_variant->queryAll(true,array(':id_product_combo'=>$row['id'],':language_code'=>Yii::app()->language)) as $row_variant) {				
					echo '<row id="'.$row['id'].':'.$row_variant['id'].'" '.($row['active']?'':'class="innactive"').'>
					<cell><![CDATA[&nbsp;]]></cell>
					<cell><![CDATA['.$row_variant['variant'].']]></cell>
					<cell><![CDATA['.$row_variant['sku'].']]></cell>		
					<cell><![CDATA['.$row['qty'].']]></cell>						
					<cell><![CDATA[&nbsp;]]></cell>
					<cell><![CDATA['.Html::nf($row_variant['price']).']]></cell>
					</row>';					
				}
				
				echo '</row>';
			}
			
			echo '</rows>';
			
		
	}		
	

	public function actionEdit_combo_products_edit_product($container, $id=0)
	{
		$model = new ProductsComboProductEditForm;
		
		$id = (int)$id;
		$id_product_combo = (int)$_POST['id_product_combo'];
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id AND id_product=:id_product'; 
		$criteria->params=array(':id'=>$id_product_combo,':id_product'=>$id); 		
		
		if (!$pc = Tbl_ProductCombo::model()->find($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}				
		
		$model->id = $id_product_combo;
		$model->qty = $pc->qty;
		
		$this->renderPartial('edit_combo_products_edit_product',array('model'=>$model, 'container'=>$container));	
	}			
	
	public function actionSave_combo_product_qty($id=0)
	{		
		$model = new ProductsComboProductEditForm;
		
		// collect user input data
		if(isset($_POST['ProductsComboProductEditForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsComboProductEditForm'] as $name=>$value)
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
	
	public function actionXml_list_combo_products_variants($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0, $id_product_combo=0)
	{		

			$id = (int)$id;
			$id_product_combo = (int)$id_product_combo;
					
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id AND id_product=:id_product'; 
			$criteria->params=array(':id'=>$id_product_combo,':id_product'=>$id); 		
			
			if (!$p = Tbl_ProductCombo::model()->find($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}							
			
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array('product.id = :id_product AND product_variant.active=1');
			$params=array(':id_product'=>$p->id_combo_product,':language_code'=>Yii::app()->language,':id_product_combo'=>$id_product_combo);			

			// filters
								
			
			$sql = 'SELECT 
			product_variant.id
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
			LEFT JOIN
			product_combo_variant 
			ON
			(product_variant.id = product_combo_variant.id_product_variant AND product_combo_variant.id_product_combo = :id_product_combo)
			'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').'
			GROUP BY 
			product.id,
			product_variant.id';
			
				
			//if this is the first query - get total number of records in the query result
			if($posStart==0){
				/* Select queries return a resultset */
				$command=$connection->createCommand($sql);
				$row = $command->queryAll(true, $params);
				$totalCount = sizeof($row);		
			}
			
			
			//create query 
			
			$sql = 'SELECT 
			product.id,
			product_variant.id AS id_product_variant,
			product_variant.sku,
			product_description.name,
			GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
			product_combo_variant.id_product_combo,
			IF(product_variant.price_type=0,product.price+product_variant.price,product.price+ROUND((product.price*product_variant.price)/100,2)) AS price,
			product_combo_variant.default_variant
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
			LEFT JOIN
			product_combo_variant 
			ON
			(product_variant.id = product_combo_variant.id_product_variant AND product_combo_variant.id_product_combo = :id_product_combo)
			'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'').'
			GROUP BY 
			product.id,
			product_variant.id';
			
			
				
			// sorting
			
			//add limits to query to get only rows necessary for the output
			//$sql.= " LIMIT ".$posStart.",".$count;
			
			$command=$connection->createCommand($sql);
			
			
			//set content type and xml tag
			header("Content-type:text/xml");
			
			//output data in XML format   
			echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
			
			// Cycle through results
			foreach ($command->queryAll(true, $params) as $row) {
				echo '<row id="'.$row['id'].':'.$row['id_product_variant'].'" '.($row['id_product_combo']?'':'class="innactive"').'>
				<cell type="ro"><![CDATA['.$row['variant'].']]></cell>
				<cell type="ro"><![CDATA['.$row['sku'].']]></cell>		
				<cell type="ro"><![CDATA['.Html::nf($row['price']).']]></cell>				
				<cell type="ch"><![CDATA['.($row['id_product_combo'] ? 1:'').']]></cell>';
				
				echo $row['id_product_combo'] ? '<cell type="ra"><![CDATA['.($row['default_variant'] ? 1:'').']]></cell>':'<cell></cell>';
				
				echo '
				</row>';
			}
			
			echo '</rows>';
			
		
	}	
	
	public function actionToggle_combo_product_variant($id=0,$id_product_combo=0)
	{
		// current 
		$ids = $_POST['ids'];
		list($id_product, $id_product_variant) = explode(':',$ids);
		$active = ($_POST['active'] == 'true') ? 1:0;
		
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id AND id_product=:id_product'; 
		$criteria->params=array(':id_product'=>$id, ':id'=>$id_product_combo); 	
		
		if (!$p = Tbl_ProductCombo::model()->find($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}

		$criteria=new CDbCriteria; 
		$criteria->condition='id_product_combo=:id_product_combo AND id_product_variant=:id_product_variant'; 
		$criteria->params=array(':id_product_combo'=>$id_product_combo, ':id_product_variant'=>$id_product_variant); 		
		
		if (!$model = Tbl_ProductComboVariant::model()->find($criteria)) {
			$model = new Tbl_ProductComboVariant;
		}
		
		if (!$active) {
			$model->delete();
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product_combo=:id_product_combo AND default_variant=1'; 
			$criteria->params=array(':id_product_combo'=>$id_product_combo); 	
			
			if (!Tbl_ProductComboVariant::model()->count($criteria)) {
				$criteria->condition='id_product_combo=:id_product_combo'; 	
				$criteria->limit=1;
				
				Tbl_ProductComboVariant::model()->updateAll(array('default_variant'=>1),$criteria);
			}			
		} else {			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product_combo=:id_product_combo AND default_variant=1'; 
			$criteria->params=array(':id_product_combo'=>$id_product_combo); 	
		
			$model->id_product_combo = $id_product_combo;
			$model->id_product_variant = $id_product_variant;
			$model->default_variant = !Tbl_ProductComboVariant::model()->count($criteria) ? 1:0;
			if (!$model->save()) {
				throw new CException(Yii::t('global','ERROR_SAVING'));	
			}
		}
		
		if (!$model = Tbl_Product::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
		// update combo cost_price, price and sell_price
		$sql = 'SELECT
		get_product_cost_price(:id_product,0) AS cost_price,
		get_combo_base_price(:id_product) AS price,
		get_product_current_price(:id_product,0,0) AS sell_price';									
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true, array(':id_product'=>$id));
		
		$model->cost_price = $row['cost_price'];
		$model->price = $row['price'];
		$model->sell_price = $row['sell_price'];	
		$model->save();		
	}	
	
	public function actionToggle_combo_product_variant_default($id=0,$id_product_combo=0)
	{
		// current 
		$ids = $_POST['ids'];
		list($id_product, $id_product_variant) = explode(':',$ids);
		
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();		
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id AND id_product=:id_product'; 
		$criteria->params=array(':id_product'=>$id, ':id'=>$id_product_combo); 	
		
		if (!$p = Tbl_ProductCombo::model()->find($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}

		$criteria=new CDbCriteria; 
		$criteria->condition='id_product_combo=:id_product_combo AND id_product_variant=:id_product_variant'; 
		$criteria->params=array(':id_product_combo'=>$id_product_combo, ':id_product_variant'=>$id_product_variant); 		
		
		if (!$model = Tbl_ProductComboVariant::model()->find($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product_combo=:id_product_combo AND default_variant=1'; 
		$criteria->params=array(':id_product_combo'=>$id_product_combo); 	
		$criteria->limit=1;
		
		Tbl_ProductComboVariant::model()->updateAll(array('default_variant'=>0),$criteria);
			
		$model->default_variant = 1;
		if (!$model->save()) {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		if (!$model = Tbl_Product::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
		// update combo cost_price, price and sell_price
		$sql = 'SELECT
		get_product_cost_price(:id_product,0) AS cost_price,
		get_combo_base_price(:id_product) AS price,
		get_product_current_price(:id_product,0,0) AS sell_price';									
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true, array(':id_product'=>$id));
		
		$model->cost_price = $row['cost_price'];
		$model->price = $row['price'];
		$model->sell_price = $row['sell_price'];	
		$model->save();				
	}		
	
	public function actionDelete_combo_product($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			// prepare delete groups and options
			$command=$connection->createCommand('DELETE FROM 
			product_combo,
			product_combo_variant
			USING
			product_combo
			LEFT JOIN
			product_combo_variant
			ON
			(product_combo.id = product_combo_variant.id_product_combo)
			WHERE 
			product_combo.id_product = :id_product
			AND
			product_combo.id = :id');					
			
			foreach ($ids as $id_product_combo) {			
				$command->execute(array(':id_product'=>$id,':id'=>$id_product_combo));																		
			}
			
			if (!$model = Tbl_Product::model()->findByPk($id)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
			
			// update combo cost_price, price and sell_price
			$sql = 'SELECT
			get_product_cost_price(:id_product,0) AS cost_price,
			get_combo_base_price(:id_product) AS price,
			get_product_current_price(:id_product,0,0) AS sell_price';									
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, array(':id_product'=>$id));
			
			$model->cost_price = $row['cost_price'];
			$model->price = $row['price'];
			$model->sell_price = $row['sell_price'];	
			$model->save();					
		}			
	}	
	
	/************************************************************
	*															*
	*															*
	*					BUNDLED PRODUCTS						*
	*															*
	*															*
	************************************************************/
			
	public function actionEdit_bundled_products_group($container, $id_product=0)
	{
		$model = new ProductsBundledProductGroupForm;
		
		$id = (int)$_POST['id'];
		$id_product = (int)$id_product;
		
		$model->id_product = $id_product;
		
		if ($id) {
			if ($pg = Tbl_ProductBundledProductGroup::model()->findByPk($id)) {
				$model->id = $pg->id;
				$model->id_product = $pg->id_product;
				$model->input_type = $pg->input_type;
				$model->required = $pg->required;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_bundled_products_group',array('model'=>$model, 'container'=>$container));	
	}		
	
	public function actionSave_bundled_products_group($id)
	{
		$id = (int)$id;
		$model = new ProductsBundledProductGroupForm;
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		// collect user input data
		if(isset($_POST['ProductsBundledProductGroupForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsBundledProductGroupForm'] as $name=>$value)
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
			} else {
				if (!$model = Tbl_Product::model()->findByPk($id)) {
					throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
				}
				
				// update bundle cost_price, price and sell_price
				$sql = 'SELECT
				get_product_cost_price(:id_product,0) AS cost_price,
				get_product_current_price(:id_product,0,0) AS sell_price';									
				$command=$connection->createCommand($sql);
				$row = $command->queryRow(true, array(':id_product'=>$id));
				
				$model->cost_price = $row['cost_price'];
				$model->price = $row['sell_price'];
				$model->sell_price = $row['sell_price'];	
				$model->save();						
			}
								
			echo CJSON::encode($output);	
		}
	}
	
	public function actionDelete_bundled_products_group($id)
	{
		$id_product = (int)$id;
		$ids = $_POST['ids'];
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		if (is_array($ids) && sizeof($ids)) {
			
			// prepare delete groups and options
			$command=$connection->createCommand('DELETE FROM 
			product_bundled_product_group,
			product_bundled_product_group_description,
			product_bundled_product_group_product
			USING 
			product_bundled_product_group
			INNER JOIN 
			product_bundled_product_group_description
			ON
			(product_bundled_product_group.id = product_bundled_product_group_description.id_product_bundled_product_group)
			LEFT JOIN
			product_bundled_product_group_product
			ON
			(product_bundled_product_group.id = product_bundled_product_group_product.id_product_bundled_product_group)
			WHERE 
			product_bundled_product_group.id=:id');					
			
			foreach ($ids as $id) {			
				$command->execute(array(':id'=>$id));																		
			}
		}			
	
		if (!$model = Tbl_Product::model()->findByPk($id_product)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
		// update bundle cost_price, price and sell_price
		$sql = 'SELECT
		get_product_cost_price(:id_product,0) AS cost_price,
		get_product_current_price(:id_product,0,0) AS sell_price';									
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true, array(':id_product'=>$id_product));
		
		$model->cost_price = $row['cost_price'];
		$model->price = $row['sell_price'];
		$model->sell_price = $row['sell_price'];	
		$model->save();			
	}
	
	/**
	 * This is the action to save product variant group order
	 */
	public function actionSave_bundled_products_groups_sort_order($id=0)
	{			
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		// prepare 
		$command=$connection->createCommand('UPDATE
		product_bundled_product_group
		SET
		sort_order=:sort_order
		WHERE 
		id=:id
		AND
		id_product=:id_product');				

		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_product_bundled_product_group) {
				$command->execute(array(':id'=>$id_product_bundled_product_group,':id_product'=>$id,':sort_order'=>$i));				
				++$i;
			}
		}
	}			
		
	/** 
	 *
	 */
	public function actionXml_list_bundled_products_group_description($container, $id=0)
	{
		$model = new ProductsBundledProductGroupForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($product_bundled_product_group = Tbl_ProductBundledProductGroup::model()->findByPk($id)) {							
				// grab description information 
				foreach ($product_bundled_product_group->tbl_product_bundled_product_group_description as $row) {
					$model->product_bundled_product_group_description[$row->language_code]['name'] = $row->name;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$columns = Html::getColumnsMaxLength(Tbl_ProductBundledProductGroupDescription::tableName());
		$help_hint_path = '/catalog/products/bundled-products/';
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';
		
		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:auto;" class="div_'.$container.'">	
					<div style="padding:10px;">			
					<div class="row">
						<strong>'.Yii::t('global','LABEL_NAME').'</strong>'.
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_product_bundled_product_group_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->product_bundled_product_group_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'group-name').'
						<div>'.
						CHtml::activeTextField($model,'product_bundled_product_group_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>$container.'_product_bundled_product_group_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_product_bundled_product_group_description['.$value->code.'][name]_errorMsg" class="error"></span>
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
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_bundled_products_groups($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('product_bundled_product_group.id_product=:id_product');
		$params=array(':id_product'=>$id,':language_code'=>Yii::app()->language);
		
		// filters
		/*
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'product_bundled_product_group_description.name LIKE CONCAT(:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(product_bundled_product_group.id) AS total 
		FROM 
		product_bundled_product_group 
		INNER JOIN 
		product_bundled_product_group_description 
		ON 
		(product_bundled_product_group.id = product_bundled_product_group_description.id_product_bundled_product_group AND product_bundled_product_group_description.language_code = :language_code) 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset 
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}*/
		
		//create query 
		$sql = "SELECT 
		product_bundled_product_group.id,
		product_bundled_product_group_description.name,
		product_bundled_product_group.input_type,
		product_bundled_product_group.required
		FROM 
		product_bundled_product_group 
		INNER JOIN 
		product_bundled_product_group_description 
		ON 
		(product_bundled_product_group.id = product_bundled_product_group_description.id_product_bundled_product_group AND product_bundled_product_group_description.language_code = :language_code) 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product_bundled_product_group_description.name ".$direct;		
		} else {

			$sql.=" ORDER BY product_bundled_product_group.sort_order ASC";
		}		
		
		//add limits to query to get only rows necessary for the output
		//$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			switch($row['input_type']){
				case 0:
					$input_type_name = Yii::t('global','LABEL_DROP_DOWN_LIST');
					break;
				case 1:
					$input_type_name = Yii::t('global','LABEL_RADIO_BUTTON');
					break;
				case 2:
					$input_type_name = Yii::t('global','LABEL_CHECKBOX');
					break;
				case 3:
					$input_type_name = Yii::t('global','LABEL_MULTI_SELECT');
					break;
			}
			
			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$input_type_name.']]></cell>
			<cell type="ro"><![CDATA['.($row['required']?Yii::t('global','LABEL_YES'):Yii::t('global','LABEL_NO')).']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	public function actionXml_list_bundled_products_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0, $id_product_bundled_product_group=0)
	{		

			$id = (int)$id;
			$id_product_bundled_product_group = (int)$id_product_bundled_product_group;
					
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id AND id_product=:id_product'; 
			$criteria->params=array(':id'=>$id_product_bundled_product_group,':id_product'=>$id); 		
			
			if (!Tbl_ProductBundledProductGroup::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}							
			
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array('id != :id');
			$params=array(':id'=>$id,':language_code'=>Yii::app()->language,':id_product_bundled_product_group'=>$id_product_bundled_product_group);
			
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
			
			// variant
			if (isset($filters['variant']) && !empty($filters['variant'])) {
				$where[] = 'variant LIKE CONCAT("%",:variant,"%")';
				$params[':variant']=$filters['variant'];
			}				
			
			// price
			if (isset($filters['price']) && !empty($filters['price'])) {
				// <=N
				if (preg_match('/^<=([0-9\.]+)$/', $filters['price'])) {
					$where[] = 'price <= :price';
					$params[':price']=ltrim($filters['price'],'<=');
				// >=N
				} else if (preg_match('/^>=([0-9\.]+)$/', $filters['price'])) {
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
			COUNT(id) AS total
			FROM 
			(
				(
					SELECT 
					product.id,
					product_variant.id AS id_product_variant,
					product.sku,
					product_description.name,
					NULL AS variant,
					product.price
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
					LEFT JOIN 
					product_bundled_product_group_product  
					ON
					(product.id = product_bundled_product_group_product.id_product AND product_bundled_product_group_product.id_product_bundled_product_group = :id_product_bundled_product_group)
					WHERE
					product.active=1
					AND
					product.product_type=0 
					AND 
					product_variant.id IS NULL
					AND 
					product_bundled_product_group_product.id IS NULL
					GROUP BY 
					product.id
				)
				UNION
				(
					SELECT 
					product.id,
					product_variant.id AS id_product_variant,
					product_variant.sku,
					product_description.name,
					GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
					IF(product_variant.price_type=0,product.price+product_variant.price,product.price+ROUND((product.price*product_variant.price)/100,2)) AS price
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
					LEFT JOIN 
					product_bundled_product_group_product  
					ON
					(product.id = product_bundled_product_group_product.id_product AND product_variant.id = product_bundled_product_group_product.id_product_variant AND product_bundled_product_group_product.id_product_bundled_product_group = :id_product_bundled_product_group)
					WHERE
					product.product_type=0 		
					AND
					product.active=1
					AND
					product_variant.active=1
					AND 
					product_bundled_product_group_product.id IS NULL											
					GROUP BY 
					product.id,
					product_variant.id
				)
			) AS t
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
			id, 
			id_product_variant,
			sku,
			name,
			variant,
			price
			FROM 
			(
				(
					SELECT 
					product.id,
					product_variant.id AS id_product_variant,
					product.sku,
					product_description.name,
					NULL AS variant,
					product.price
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
					LEFT JOIN 
					product_bundled_product_group_product  
					ON
					(product.id = product_bundled_product_group_product.id_product AND product_bundled_product_group_product.id_product_bundled_product_group = :id_product_bundled_product_group)
					WHERE
					product.active=1
					AND
					product.product_type=0 
					AND 
					product_variant.id IS NULL
					AND 
					product_bundled_product_group_product.id IS NULL
					GROUP BY 
					product.id
				)
				UNION
				(
					SELECT 
					product.id,
					product_variant.id AS id_product_variant,
					product_variant.sku,
					product_description.name,
					GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
					IF(product_variant.price_type=0,product.price+product_variant.price,product.price+ROUND((product.price*product_variant.price)/100,2)) AS price
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
					LEFT JOIN 
					product_bundled_product_group_product  
					ON
					(product.id = product_bundled_product_group_product.id_product AND product_variant.id = product_bundled_product_group_product.id_product_variant AND product_bundled_product_group_product.id_product_bundled_product_group = :id_product_bundled_product_group)
					WHERE
					product.product_type=0 		
					AND
					product.active=1
					AND
					product_variant.active=1
					AND 
					product_bundled_product_group_product.id IS NULL											
					GROUP BY 
					product.id,
					product_variant.id
				)
			) AS t
			'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
				
			// sorting
			
			// name
			if (isset($sort_col[1]) && !empty($sort_col[1])) {
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY name ".$direct;
			// variant
			} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
				$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY variant ".$direct;								
			// sku
			} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
				$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY sku ".$direct;								
			// price
			} else if (isset($sort_col[4]) && !empty($sort_col[4])) {
				$direct = $sort_col[4] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY price ".$direct;								
				
			}else{
				$sql.=" ORDER BY name ASC, id_product_variant ASC";
			}	
			
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
				echo '<row id="'.$row['id'].':'.$row['id_product_variant'].'">
				<cell type="ch" />
				<cell type="ro"><![CDATA['.$row['name'].']]></cell>
				<cell type="ro"><![CDATA['.$row['variant'].']]></cell>
				<cell type="ro"><![CDATA['.$row['sku'].']]></cell>		
				<cell type="ro"><![CDATA['.Html::nf($row['price']).']]></cell>				
				</row>';
			}
			
			echo '</rows>';
			
		
	}	
	
	public function actionAdd_bundled_products_group_product($id=0,$id_product_bundled_product_group=0)
	{
		
		$id = (int)$id;
		$id_product_bundled_product_group = (int)$id_product_bundled_product_group;
		$ids = $_POST['ids'];
		$default_qty = (int)$_POST['default_qty'];
		$default_qty = $default_qty ? $default_qty:1;
		
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();		
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id AND id_product=:id_product'; 
		$criteria->params=array(':id'=>$id_product_bundled_product_group,':id_product'=>$id); 		
		
		if (!Tbl_ProductBundledProductGroup::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}			
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product_bundled_product_group=:id_product_bundled_product_group'; 
		$criteria->params=array(':id_product_bundled_product_group'=>$id_product_bundled_product_group);
		$criteria->order='sort_order DESC';					
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			$sql = 'SELECT 
			IF(product_variant.id IS NOT NULL,IF(product_variant.price_type=0,product.price+product_variant.price,product.price+ROUND(product.price*product_variant.price,2)),product.price) AS price
			FROM 
			product
			LEFT JOIN
			product_variant
			ON
			(product.id = product_variant.id_product AND product_variant.id = :id_product_variant)
			WHERE
			product.id = :id
			LIMIT 1';
			$command=$connection->createCommand($sql);
			
	
			
			$sql='SELECT
			COUNT(id)
			FROM
			product_bundled_product_group_product
			WHERE
			id_product_bundled_product_group=:id_product_bundled_product_group
			AND
			selected=1';
			$command_selected=$connection->createCommand($sql);		
			
			foreach ($ids as $value) {	
				list($id_product,$id_product_variant) = explode(':',$value);
				
				// get current product price / regular price only
				$price = $command->queryScalar(array(':id'=>$id_product,':id_product_variant'=>$id_product_variant));
				
				$ps = new Tbl_ProductBundledProductGroupProduct;
				$ps->id_product_bundled_product_group = $id_product_bundled_product_group;
				$ps->id_product = $id_product;
				$ps->id_product_variant = $id_product_variant;
				$ps->price = $price;
				$ps->qty = $default_qty;
				$ps->sort_order = Tbl_ProductBundledProductGroupProduct::model()->find($criteria)->sort_order+1;
				$ps->id_user_created = $current_id_user;
				$ps->id_user_modified = $current_id_user;
				$ps->date_created = $current_datetime;
				$ps->date_modified = $current_datetime;
				if (!$ps->save()){
					echo 'false';
					exit;
				}		
				
				if (!$command_selected->queryScalar(array(':id_product_bundled_product_group'=>$id_product_bundled_product_group))) {
					$sql = 'UPDATE
					product_bundled_product_group_product
					SET
					selected=1
					WHERE
					id_product_bundled_product_group=:id_product_bundled_product_group
					LIMIT 1';
					$command_set_selected=$connection->createCommand($sql);
					$command_set_selected->execute(array(':id_product_bundled_product_group'=>$id_product_bundled_product_group));				
				}					
			}
		}		
	}	
	
	public function actionXml_list_bundled_products_group_products($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0,$id_product_bundled_product_group=0)
	{		

			$id = (int)$id;
			$id_product_bundled_product_group = (int)$id_product_bundled_product_group;
					
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id AND id_product=:id_product'; 
			$criteria->params=array(':id'=>$id_product_bundled_product_group,':id_product'=>$id); 		
			
			if (!Tbl_ProductBundledProductGroup::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}							
			
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array();
			$params=array(':id'=>$id_product_bundled_product_group,':language_code'=>Yii::app()->language);
			
			//create query 
			
			$sql = 'SELECT 
			id,
			id_product_variant,
			sku,
			name,
			variant,
			qty,
			price,
			price_type,
			selected,
			sort_order,
			active
			FROM 
			((SELECT 
			product_bundled_product_group_product.id,
			product_bundled_product_group_product.id_product_variant,
			product.sku,
			product_description.name,
			NULL AS variant,
			product_bundled_product_group_product.qty,
			IF(parent_product.use_product_current_price=1,product.price,IF(product_bundled_product_group_product.price_type=0,product_bundled_product_group_product.price,product.price-(ROUND((IF(NOW() BETWEEN product.special_price_from_date AND product.special_price_to_date,product.special_price,product.price)*product_bundled_product_group_product.price)/100,2)))) AS price,
			product_bundled_product_group_product.price_type,
			product_bundled_product_group_product.selected,
			product_bundled_product_group_product.sort_order,
			product.active
			FROM 
			product_bundled_product_group_product
			INNER JOIN 
			(product_bundled_product_group CROSS JOIN product AS parent_product)
			ON
			(product_bundled_product_group_product.id_product_bundled_product_group = product_bundled_product_group.id AND product_bundled_product_group.id_product = parent_product.id)			
			INNER JOIN
			(product CROSS JOIN product_description)
			ON
			(product_bundled_product_group_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = :language_code)
			WHERE
			product_bundled_product_group_product.id_product_bundled_product_group = :id
			AND
			product_bundled_product_group_product.id_product_variant = 0)
			
			UNION
			
			(SELECT 
			product_bundled_product_group_product.id,
			product_bundled_product_group_product.id_product_variant,
			product_variant.sku,
			product_description.name,
			GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS variant,
			product_bundled_product_group_product.qty,
			IF(parent_product.use_product_current_price=1,IF(product_variant.price_type=0,product.price+product_variant.price,product.price+ROUND(product.price*product_variant.price,2)),IF(product_bundled_product_group_product.price_type=0,product_bundled_product_group_product.price,ROUND(((product.price+product_variant.price)*product_bundled_product_group_product.price)/100,2))) AS price,
			product_bundled_product_group_product.price_type,
			product_bundled_product_group_product.selected,
			product_bundled_product_group_product.sort_order,
			IF(product.active=0 OR product_variant.active=0,0,1) AS active
			FROM 
			product_bundled_product_group_product
			INNER JOIN 
			(product_bundled_product_group CROSS JOIN product AS parent_product)
			ON
			(product_bundled_product_group_product.id_product_bundled_product_group = product_bundled_product_group.id AND product_bundled_product_group.id_product = parent_product.id)			
			INNER JOIN
			(product CROSS JOIN product_description)
			ON
			(product_bundled_product_group_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = :language_code)
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
				product_bundled_product_group_product.id_product_variant = product_variant.id
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
			product_bundled_product_group_product.id_product_bundled_product_group = :id
			GROUP BY 
			product.id,
			product_variant.id)) AS t
			'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
	
				
			// sorting
			$sql.=" ORDER BY sort_order ASC";
			
			$command=$connection->createCommand($sql);
			
			
			//set content type and xml tag
			header("Content-type:text/xml");
			
			//output data in XML format   
			echo '<rows>';
			
			// Cycle through results
			foreach ($command->queryAll(true, $params) as $row) {
				echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
				<cell type="ch" />
				<cell type="ro"><![CDATA['.$row['name'].']]></cell>
				<cell type="ro"><![CDATA['.$row['variant'].']]></cell>
				<cell type="ro"><![CDATA['.$row['sku'].']]></cell>		
				<cell type="ro"><![CDATA['.$row['qty'].']]></cell>						
				<cell type="ro"><![CDATA['.Html::nf($row['price']).']]></cell>
				<cell type="ra"><![CDATA['.($row['selected'] ? 1:'').']]></cell>
				</row>';
			}
			
			echo '</rows>';
			
		
	}	
	
	public function actionEdit_bundled_products_group_products($container, $id=0, $id_product_bundled_product_group_product=0, $id_product_bundled_product_group=0)
	{
		$model = new ProductsBundledProductGroupProductForm;
		
		$id = (int)$id;
		$id_product_bundled_product_group_product = (int)$id_product_bundled_product_group_product;
		$id_product_bundled_product_group = (int)$id_product_bundled_product_group;
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		/*$sql='SELECT
		product_bundled_product_group_product.id,
		product_bundled_product_group_product.price_type,
		product_bundled_product_group_product.price,
		product_bundled_product_group_product.qty,
		product_bundled_product_group_product.user_defined_qty,
		product_bundled_product_group_product.selected,
		product.use_product_current_price,
		product.use_product_special_price
		FROM 
		product_bundled_product_group_product
		INNER JOIN 
		(product_bundled_product_group CROSS JOIN product)
		ON
		(product_bundled_product_group_product.id_product_bundled_product_group = product_bundled_product_group.id AND product_bundled_product_group.id_product = product.id) 
		WHERE
		product_bundled_product_group_product.id = :id
		LIMIT 1';*/

		
		$sql = 'SELECT 
			id,
			price_type,
			price,
			qty,
			user_defined_qty,
			selected,
			use_product_current_price,
			use_product_special_price,
			product_current_price
			FROM 
			((SELECT 
			product_bundled_product_group_product.id,
			product_bundled_product_group_product.price_type,
			product_bundled_product_group_product.price,
			product_bundled_product_group_product.qty,
			product_bundled_product_group_product.user_defined_qty,
			product_bundled_product_group_product.selected,
			parent_product.use_product_current_price,
			parent_product.use_product_special_price,
			product.price as product_current_price
			FROM 
			product_bundled_product_group_product
			INNER JOIN 
			(product_bundled_product_group CROSS JOIN product AS parent_product)
			ON
			(product_bundled_product_group_product.id_product_bundled_product_group = product_bundled_product_group.id AND product_bundled_product_group.id_product = parent_product.id)			
			INNER JOIN
			product
			ON
			(product_bundled_product_group_product.id_product = product.id)
			WHERE
			product_bundled_product_group_product.id = :id
			AND
			product_bundled_product_group_product.id_product_variant = 0)
			
			UNION
			
			(SELECT 
			product_bundled_product_group_product.id,
			product_bundled_product_group_product.price_type,
			product_bundled_product_group_product.price,
			product_bundled_product_group_product.qty,
			product_bundled_product_group_product.user_defined_qty,
			product_bundled_product_group_product.selected,
			parent_product.use_product_current_price,
			parent_product.use_product_special_price,
			(product.price+product_variant.price) as product_current_price
			FROM 
			product_bundled_product_group_product
			INNER JOIN 
			(product_bundled_product_group CROSS JOIN product AS parent_product)
			ON
			(product_bundled_product_group_product.id_product_bundled_product_group = product_bundled_product_group.id AND product_bundled_product_group.id_product = parent_product.id)			
			INNER JOIN
			product
			ON
			(product_bundled_product_group_product.id_product = product.id)
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
				product_bundled_product_group_product.id_product_variant = product_variant.id
				AND 
				product_variant.id = product_variant_option.id_product_variant 
				AND 
				product_variant_option.id_product_variant_group = product_variant_group.id 
				AND 
				product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
				AND 
				product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
			)
			WHERE
			product_bundled_product_group_product.id = :id)) AS t';	
		
		$command=$connection->createCommand($sql);
		
		if ($pg = $command->queryRow(true,array(':id'=>$id_product_bundled_product_group_product))) {
			$model->id = $pg['id'];
			$model->price_type = $pg['price_type'];
			$model->price = $pg['price'];
			$model->qty = $pg['qty'];
			$model->user_defined_qty = $pg['user_defined_qty'];
			$model->selected = $pg['selected'];
			$model->use_product_current_price = $pg['use_product_current_price'];
			$model->use_product_special_price = $pg['use_product_special_price'];
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
		$this->renderPartial('edit_bundled_products_group_products',array('model'=>$model, 'container'=>$container, 'product_current_price'=>$pg['product_current_price']));	
	}								
	
	public function actionSave_bundled_products_group_product($id)
	{
		$id = (int)$id;
		$model = new ProductsBundledProductGroupProductForm;
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		// collect user input data
		if(isset($_POST['ProductsBundledProductGroupProductForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsBundledProductGroupProductForm'] as $name=>$value)
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
			} else {
				if (!$model = Tbl_Product::model()->findByPk($id)) {
					throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
				}
				
				// update bundle cost_price, price and sell_price
				$sql = 'SELECT
				get_product_cost_price(:id_product,0) AS cost_price,
				get_product_current_price(:id_product,0,0) AS sell_price';									
				$command=$connection->createCommand($sql);
				$row = $command->queryRow(true, array(':id_product'=>$id));
				
				$model->cost_price = $row['cost_price'];
				$model->price = $row['sell_price'];
				$model->sell_price = $row['sell_price'];	
				$model->save();						
			}
								
			echo CJSON::encode($output);	
		}
	}		
	
	public function actionToggle_default_bundled_products_group_product()
	{
		// current 
		$id = (int)$_POST['id'];		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		if ($p = Tbl_ProductBundledProductGroupProduct::model()->findByPk($id)) {
			$p->selected = 1;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
			
			$sql='UPDATE
			product_bundled_product_group_product
			SET
			selected=0
			WHERE
			id_product_bundled_product_group=:id_product_bundled_product_group
			AND
			id != :id';
			$command=$connection->createCommand($sql);			
			$command->execute(array(':id_product_bundled_product_group'=>$p->id_product_bundled_product_group,':id'=>$p->id));			
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}	
	
	public function actionDelete_bundled_products_group_product($id)
	{
		$id_product = (int)$id;
		$ids = $_POST['ids'];
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		if (is_array($ids) && sizeof($ids)) {			
			
			$command=$connection->createCommand('DELETE FROM 
			product_bundled_product_group_product			
			WHERE 
			id=:id');	
			
			foreach ($ids as $id) {
				$command->execute(array(':id'=>$id));							
			}
		}			
		
		if (!$model = Tbl_Product::model()->findByPk($id_product)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
		// update bundle cost_price, price and sell_price
		$sql = 'SELECT
		get_product_cost_price(:id_product,0) AS cost_price,
		get_product_current_price(:id_product,0,0) AS sell_price';									
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true, array(':id_product'=>$id_product));
		
		$model->cost_price = $row['cost_price'];
		$model->price = $row['sell_price'];
		$model->sell_price = $row['sell_price'];	
		$model->save();				
	}	
	
	/**
	 * This is the action to save product order
	 */
	public function actionSave_bundled_products_group_products_sort_order($id=0)
	{			
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		// prepare 
		$command=$connection->createCommand('UPDATE
		product_bundled_product_group_product
		SET
		sort_order=:sort_order
		WHERE 
		id=:id
		AND
		id_product_bundled_product_group=:id_product_bundled_product_group');				

		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_product_bundled_product_group_product) {
				$command->execute(array(':id'=>$id_product_bundled_product_group_product,':id_product_bundled_product_group'=>$id,':sort_order'=>$i));				
				++$i;
			}
		}
	}			
	
	public function actionXml_list_bundled_products_templates($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$posStart=(int)$posStart;
		$count=(int)$count;

		// filters	
		
		$where=array();
		$params=array();
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'tpl_product_option_category.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}							
		
		$sql = "SELECT 
		COUNT(id) AS total 
		FROM 
		tpl_product_bundled_product_category
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	;	

		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT 
		id,
		name
		FROM 
		tpl_product_bundled_product_category
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	;		
		
		// sorting

		if (isset($filters['name']) && !empty($filters['name'])) {
			$sql.=" ORDER BY 
			IF(name LIKE CONCAT(:name,'%'),1,0) DESC, name ASC";
		}else{
			$sql.=" ORDER BY name ASC";
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
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	public function actionAdd_bundled_products_template($container, $id=0)
	{
		$model = new ProductsAddBundledProductsTemplateForm;
		
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (Tbl_Product::model()->count($criteria)) {
			$model->id_product = $id;
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
		$this->renderPartial('add_bundled_products_template',array('model'=>$model,'container'=>$container));				
	}
	
	public function actionCount_bundled_products_groups($id)
	{
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product'; 
		$criteria->params=array(':id_product'=>$id); 			
		
		echo Tbl_ProductBundledProductGroup::model()->count($criteria);
	}		
	
	public function actionSave_bundled_products_template()
	{
		$model = new ProductsAddBundledProductsTemplateForm;
		
		// collect user input data
		if(isset($_POST['ProductsAddBundledProductsTemplateForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsAddBundledProductsTemplateForm'] as $name=>$value)
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
	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_bundled_products_group_template($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id = (int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('tpl_product_bundled_product_group.id_tpl_product_bundled_product_category=:id_tpl_product_bundled_product_category');
		$params=array(':id_tpl_product_bundled_product_category'=>$id,':language_code'=>Yii::app()->language);
		
		//create query 
		$sql = "SELECT 
		tpl_product_bundled_product_group.id,
		tpl_product_bundled_product_group_description.name
		FROM 
		tpl_product_bundled_product_group 
		INNER JOIN 
		tpl_product_bundled_product_group_description 
		ON 
		(tpl_product_bundled_product_group.id = tpl_product_bundled_product_group_description.id_tpl_product_bundled_product_group AND tpl_product_bundled_product_group_description.language_code = :language_code) 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		$sql.=" ORDER BY 
		tpl_product_bundled_product_group.sort_order ASC";
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows>';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}			
	
	public function actionApply_bundled_products_template($id=0)
	{
		$id = (int)$id;
		$id_tpl_product_bundled_product_category = (int)$_POST['id_tpl_product_bundled_product_category'];
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();	
		
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection			
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (!Tbl_Product::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		// prepare delete
		$command=$connection->createCommand('DELETE FROM 
		product_bundled_product_group,
		product_bundled_product_group_description,
		product_bundled_product_group_product
		USING
		product_bundled_product_group
		LEFT JOIN 
		product_bundled_product_group_description 
		ON
		(product_bundled_product_group.id = product_bundled_product_group_description.id_product_bundled_product_group)
		LEFT JOIN
		product_bundled_product_group_product
		ON
		(product_bundled_product_group.id = product_bundled_product_group_product.id_product_bundled_product_group)
		WHERE 
		product_bundled_product_group.id_product=:id_product');		
		$command->execute(array(':id_product'=>$id));			
		
		// apply template
		$criteria=new CDbCriteria; 
		$criteria->condition='id_tpl_product_bundled_product_category=:id_tpl_product_bundled_product_category'; 
		$criteria->params=array(':id_tpl_product_bundled_product_category'=>$id_tpl_product_bundled_product_category); 			
		
		foreach (Tbl_TplProductBundledProductGroup::model()->findAll($criteria) as $row){
			$model = new Tbl_ProductBundledProductGroup;
			$model->id_product = $id;
			$model->input_type = $row->input_type;
			$model->required = $row->required;
			$model->sort_order = $row->sort_order;
			$model->id_user_created = $current_id_user;
			$model->id_user_modified = $current_id_user;
			$model->date_created = $current_datetime;
			
			if ($model->save()){
				foreach ($row->tbl_tpl_product_bundled_product_group_description as $row_description) {
					$model_description = new Tbl_ProductBundledProductGroupDescription;
					$model_description->id_product_bundled_product_group = $model->id;
					$model_description->language_code = $row_description->language_code;
					$model_description->name = $row_description->name;
					
					if (!$model_description->save()){
						$model->delete();
						
						throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));		
					}
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_SAVING'));	
			}
		}
		
		if (!$model = Tbl_Product::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
		// update bundle cost_price, price and sell_price
		$sql = 'SELECT
		get_product_cost_price(:id_product,0) AS cost_price,
		get_product_current_price(:id_product,0,0) AS sell_price';									
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true, array(':id_product'=>$id));
		
		$model->cost_price = $row['cost_price'];
		$model->price = $row['sell_price'];
		$model->sell_price = $row['sell_price'];	
		$model->save();					
	}	
	
	/************************************************************
	*															*
	*															*
	*						OPTIONS								*
	*															*
	*															*
	************************************************************/
			
	public function actionEdit_options_group($container, $id_product=0)
	{
		$model = new OptionsGroupForm;
		
		$id = (int)$_POST['id'];
		$id_product = (int)$id_product;
		$model->id_product = $id_product;
		if ($id) {
			if ($pog = Tbl_OptionsGroup::model()->findByPk($id)) {
				$model->id = $pog->id;
				$model->input_type = $pog->input_type;
				$model->from_to = $pog->from_to;
				$model->user_defined_qty = $pog->user_defined_qty;
				$model->max_qty = $pog->max_qty;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_options_group',array('model'=>$model, 'container'=>$container));	
	}		
	
	public function actionSave_option_group()
	{
		$model = new OptionsGroupForm;
		
		// collect user input data
		if(isset($_POST['OptionsGroupForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['OptionsGroupForm'] as $name=>$value)
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
	
	public function actionDelete_options_group()
	{
		$id = $_POST['id'];
		
		if (!empty($id)) {
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_options_group=:id_options_group'; 
			$criteria->params=array(':id_options_group'=>$id);
			if(Tbl_OrdersItemOption::model()->find($criteria)){
				if ($options_group = Tbl_OptionsGroup::model()->findByPk($id)) {
					$options_group->archive = 1;
					if (!$options_group->save()) {
						throw new CException(Yii::t('global','LABEL_ALERT_NO_DATA_RETURN'));	
					}
				} 
			}else{
			
				$connection=Yii::app()->db;   // assuming you have configured a "db" connection
				
				// prepare delete groups and options
				$command_delete_groups_options=$connection->createCommand('DELETE 
				options_group,
				options_group_description,
				options,
				options_description,
				options_do_not_ship_region,
				options_ship_only_region,
				options_price_shipping_region
				FROM
				options_group
				LEFT JOIN 
				options_group_description
				ON
				options_group.id = options_group_description.id_options_group
				LEFT JOIN 
				options
				ON
				options_group.id = options.id_options_group
				LEFT JOIN 
				options_description
				ON
				options.id = options_description.id_options
				LEFT JOIN 
				options_do_not_ship_region
				ON
				options.id = options_do_not_ship_region.id_options
				LEFT JOIN 
				options_ship_only_region
				ON
				options.id = options_ship_only_region.id_options
				LEFT JOIN 
				options_price_shipping_region
				ON
				options.id = options_price_shipping_region.id_options
				WHERE 
				options_group.id=:id');		
				
				$command_delete_groups_options->execute(array(':id'=>$id));	
			}
		}
	}
	
	public function actionRemove_option_group()
	{
		$ids = $_POST['ids'];
		$id_product = $_POST['id_product'];
		
		if (is_array($ids) && sizeof($ids) and !empty($id_product)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			// prepare delete groups and options
			$command_delete_groups_options=$connection->createCommand('DELETE FROM 
			product_options_group
			WHERE 
			product_options_group.id_product = :id_product AND id_options_group = :id_options_group');					
			
			foreach ($ids as $id) {																		
				// delete groups and options					
				$command_delete_groups_options->execute(array(':id_options_group'=>$id,':id_product'=>$id_product));																		
			}
		}			
	}
	
	/**
	 * This is the action to save product variant group order
	 */
	public function actionSave_options_group_sort_order($id=0)
	{			
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_options_group) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND id_options_group=:id_options_group'; 
				$criteria->params=array(':id_product'=>$id,':id_options_group'=>$id_options_group);  					
								
				if ($pog = Tbl_ProductOptionsGroup::model()->find($criteria)) {
					$pog->sort_order = $i;
					$pog->save();
					
					++$i;
				}
			}
		}
	}
		
		
	/** 
	 *
	 */
	public function actionXml_list_option_group_description($container, $id=0)
	{
		$model = new OptionsGroupForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($options_group = Tbl_OptionsGroup::model()->findByPk($id)) {							
				// grab description information 
				foreach ($options_group->tbl_options_group_description as $row) {
					$model->tbl_options_group_description[$row->language_code]['name'] = $row->name;
					$model->tbl_options_group_description[$row->language_code]['description'] = $row->description;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$columns = Html::getColumnsMaxLength(Tbl_OptionsGroupDescription::tableName());
		
		$help_hint_path = '/catalog/products/options/';
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';
		
		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:auto;" class="div_'.$container.'">	
					<div style="padding:10px;">			
					<div class="row">
						<strong>'.Yii::t('global','LABEL_NAME').'</strong>'.
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_tbl_options_group_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->tbl_options_group_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'group-name').'
						<div>'.
						CHtml::activeTextField($model,'tbl_options_group_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>$container.'_tbl_options_group_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_tbl_options_group_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong>'.
						(isset($columns['description']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_tbl_options_group_description['.$value->code.'][description]_maxlength">'.($columns['description']-strlen($model->tbl_options_group_description[$value->code]['description'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'group-description').'
						<div>'.
						CHtml::activeTextArea($model,'tbl_options_group_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'rows' => 3,'maxlength'=>$columns['description'], 'id'=>$container.'_tbl_options_group_description['.$value->code.'][description]')).'
						<br /><span id="'.$container.'_tbl_options_group_description['.$value->code.'][description]_errorMsg" class="error"></span>
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
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_option_groups($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('product_options_group.id_product=:id_product');
		$params=array(':id_product'=>$id);
		
		$where[] = 'options_group.archive = 0';
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'options_group_description.name LIKE CONCAT(:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(product_options_group.id) AS total 
		FROM 
		product_options_group 
		INNER JOIN 
		options_group
		ON 
		(product_options_group.id_options_group = options_group.id) 
		INNER JOIN 
		options_group_description 
		ON 
		(options_group.id = options_group_description.id_options_group AND options_group_description.language_code = '".Yii::app()->language."') 
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
		options_group.id,
		options_group_description.name,
		options_group.input_type,
		options_group.from_to,
		options_group.maxlength 
		FROM 
		product_options_group 
		INNER JOIN 
		options_group
		ON 
		(product_options_group.id_options_group = options_group.id) 
		INNER JOIN 
		options_group_description 
		ON 
		(options_group.id = options_group_description.id_options_group AND options_group_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		$sql.=" ORDER BY product_options_group.sort_order ASC";		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			switch($row['input_type']){
				case 0:
					$input_type_name = Yii::t('global','LABEL_DROP_DOWN_LIST');
				break;
				case 1:
					$input_type_name = Yii::t('global','LABEL_RADIO_BUTTON');
				break;
				case 3:
					$input_type_name = Yii::t('global','LABEL_CHECKBOX');
				break;
				case 4:
					$input_type_name = Yii::t('global','LABEL_MULTI_SELECT');
				break;
				case 5:
					$input_type_name = Yii::t('global','LABEL_TEXTFIELD') . ($row['maxlength']?' (' . $row['maxlength'] . ')':'');
				break;
				case 6:
					$input_type_name = Yii::t('global','LABEL_TEXTAREA') . ($row['maxlength']?' (' . $row['maxlength'] . ')':'');
				break;
				case 7:
					$input_type_name = Yii::t('global','LABEL_FILE');
				break;
				case 8:
					$input_type_name = Yii::t('global','LABEL_DATE') . ($row['from_to']?' (From-To)':'');
				break;
				case 9:
					$input_type_name = Yii::t('global','LABEL_DATE_TIME') . ($row['from_to']?' (From-To)':'');
				break;
				case 10:
					$input_type_name = Yii::t('global','LABEL_TIME') . ($row['from_to']?' (From-To)':'');
				break;
			}
			
			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$input_type_name.']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	
	
	public function actionEdit_options_options($container, $id=0, $id_options_group=0)
	{
		$model = new OptionsForm;
		
		if($id_options_group){
			$model->id_options_group = $id_options_group;
		
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id_options_group'; 
			$criteria->params=array(':id_options_group'=>$id_options_group); 
			$options_group = Tbl_OptionsGroup::model()->find($criteria);
			$model->input_type = $options_group->input_type;
		}
		
		
		
		$id = (int)$id;
		if ($id) {
			if ($options = Tbl_Options::model()->findByPk($id)) {
				$model->id = $options->id;
				$model->sku = $options->sku;
				$model->cost_price = $options->cost_price;
				$model->price_type = $options->price_type;
				$model->price = $options->price;
				$model->special_price = $options->special_price;
				$model->special_price_from_date = ($options->special_price_from_date != '0000-00-00 00:00:00') ? $options->special_price_from_date:'';
				$model->special_price_to_date = ($options->special_price_to_date != '0000-00-00 00:00:00') ? $options->special_price_to_date:'';
				$model->track_inventory = $options->track_inventory;
				$model->qty = $options->qty;
				$model->out_of_stock = $options->out_of_stock;
				$model->notify = $options->notify;
				$model->notify_qty = $options->notify_qty;
				$model->allow_backorders = $options->allow_backorders;
				$model->weight = $options->weight;
				$model->length = $options->length;
				$model->width = $options->width;
				$model->height = $options->height;
				$model->extra_care = $options->extra_care;
				$model->use_shipping_price = $options->use_shipping_price;
				$model->taxable = $options->taxable;
				$model->id_tax_group = $options->id_tax_group;
				$model->in_stock = $options->in_stock;
				$model->active = $options->active;
				
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_options_options',array('model'=>$model,'container'=>$container));	
	}
	
	
	public function actionXml_list_options_description($container, $id=0)
	{
		$model = new OptionsForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($options = Tbl_Options::model()->findByPk($id)) {							
				// grab description information 
				foreach ($options->tbl_options_description as $row) {
					$model->options_description[$row->language_code]['name'] = $row->name;
					$model->options_description[$row->language_code]['description'] = $row->description;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_OptionsDescription::tableName());		
		
		$help_hint_path = '/catalog/products/options/';
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';
		
		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:auto;" class="div_'.$container.'">	
					<div style="padding:10px;">			
					<div class="row">
						<strong>'.Yii::t('global','LABEL_NAME').'</strong>'.
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_options_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->options_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'option-name').'
						<div>'.
						CHtml::activeTextField($model,'options_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>$container.'_options_description['.$value->code.'][name]')).'
						<br /><span id="options_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong>'.
						(isset($columns['description']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_options_description['.$value->code.'][description]_maxlength">'.($columns['description']-strlen($model->options_description[$value->code]['description'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'option-description').'
						<div>'.
						CHtml::activeTextArea($model,'options_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'rows' => 3,'maxlength'=>$columns['description'], 'id'=>$container.'_options_description['.$value->code.'][description]')).'
						<br /><span id="options_description['.$value->code.'][description]_errorMsg" class="error"></span>
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
	
	public function actionSave_options()
	{
		$model = new OptionsForm;
		
		
		// collect user input data
		if(isset($_POST['OptionsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['OptionsForm'] as $name=>$value)
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
	
	public function actionDelete_options()
	{
		// current product
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_options) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_options=:id_options'; 
				$criteria->params=array(':id_options'=>$id_options);
				if(Tbl_OrdersItemOption::model()->find($criteria)){
					if ($options = Tbl_Options::model()->findByPk($id_options)) {
						$options->archive = 1;
						$options->active = 0;
						if (!$options->save()) {
							throw new CException(Yii::t('global','LABEL_ALERT_NO_DATA_RETURN'));	
						}
					} 
				}else{
					$connection=Yii::app()->db;   // assuming you have configured a "db" connection
				
					// prepare delete groups and options
					$command_delete_options=$connection->createCommand('DELETE 
					options,
					options_description,
					options_do_not_ship_region,
					options_ship_only_region,
					options_price_shipping_region
					FROM
					options
					INNER JOIN 
					options_description
					ON
					options.id = options_description.id_options
					LEFT JOIN 
					options_do_not_ship_region
					ON
					options.id = options_do_not_ship_region.id_options
					LEFT JOIN 
					options_ship_only_region
					ON
					options.id = options_ship_only_region.id_options
					LEFT JOIN 
					options_price_shipping_region
					ON
					options.id = options_price_shipping_region.id_options
					WHERE 
					options.id=:id');		
					
					$command_delete_options->execute(array(':id'=>$id_options));	
				}
			}
		}		
	}
	
	public function actionXml_list_group_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		if(isset($_GET['id_product'])){
			$id_product = (int)$_GET['id_product'];
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array('product_options_group.id IS NULL');
			$params=array();
			
			$where[] = 'options_group.archive = 0';
			
			// filters
			
			// name
			if (isset($filters['name']) && !empty($filters['name'])) {
				$where[] = 'options_group_description.name LIKE CONCAT("%",:name,"%")';
				$params[':name']=$filters['name'];
			}
			
			/*$where[]='options.active=:active';
			$params[':active']="1";	*/				
			
			$sql = "SELECT 
			COUNT(options_group.id) AS total  
			FROM 
			options_group 
			INNER JOIN 
			options_group_description 
			ON 
			(options_group.id = options_group_description.id_options_group AND options_group_description.language_code = '".Yii::app()->language."')
			LEFT JOIN
			product_options_group 
			ON 
			options_group.id = product_options_group.id_options_group and product_options_group.id_product = " . $id_product . "
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
			options_group.id,
			options_group.input_type,
			options_group_description.name
			FROM 
			options_group 
			INNER JOIN 
			options_group_description 
			ON 
			(options_group.id = options_group_description.id_options_group AND options_group_description.language_code = '".Yii::app()->language."')
			LEFT JOIN
			product_options_group 
			ON 
			options_group.id = product_options_group.id_options_group and product_options_group.id_product = " . $id_product . "
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
	
			// sorting
			// name
			if (isset($sort_col[1]) && !empty($sort_col[1])) {	
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY options_group_description.name ".$direct;
			} else {
				if (isset($filters['name']) && !empty($filters['name'])) { 
					$sql.=" ORDER BY IF(options_group_description.name LIKE CONCAT(:name,'%'),0,1) ASC, options_group_description.name ASC";
				} else {
					$sql.=" ORDER BY options_group_description.name ASC";
				}
			}		
			
			//add limits to query to get only rows necessary for the output
			//$sql.= " LIMIT ".$posStart.",".$count;
			
			$command=$connection->createCommand($sql);
			
			
			//set content type and xml tag
			header("Content-type:text/xml");
			
			//output data in XML format   
			echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
			
			// Cycle through results
			foreach ($command->queryAll(true, $params) as $row) {
				switch($row['input_type']){
					case 0:
						$input_type_name = Yii::t('global','LABEL_DROP_DOWN_LIST');
					break;
					case 1:
						$input_type_name = Yii::t('global','LABEL_RADIO_BUTTON');
					break;
					case 3:
						$input_type_name = Yii::t('global','LABEL_CHECKBOX');
					break;
					case 4:
						$input_type_name = Yii::t('global','LABEL_MULTI_SELECT');
					break;
					case 5:
						$input_type_name = Yii::t('global','LABEL_TEXTFIELD') . ($row['maxlength']?' (' . $row['maxlength'] . ')':'');
					break;
					case 6:
						$input_type_name = Yii::t('global','LABEL_TEXTAREA') . ($row['maxlength']?' (' . $row['maxlength'] . ')':'');
					break;
					case 7:
						$input_type_name = Yii::t('global','LABEL_FILE');
					break;
					case 8:
						$input_type_name = Yii::t('global','LABEL_DATE') . ($row['from_to']?' (From-To)':'');
					break;
					case 9:
						$input_type_name = Yii::t('global','LABEL_DATE_TIME') . ($row['from_to']?' (From-To)':'');
					break;
					case 10:
						$input_type_name = Yii::t('global','LABEL_TIME') . ($row['from_to']?' (From-To)':'');
					break;
				}
				
				echo '<row id="'.$row['id'].'">
				<cell type="ch" />
				<cell type="ro"><![CDATA['.$row['name'].']]></cell>
				<cell type="ro"><![CDATA['.$input_type_name.']]></cell>
				</row>';
			}
			
			echo '</rows>';
			
		}
	}
	
	public function actionAdd_groups()
	{
		$model = new OptionsAddGroupForm;
		
		// current product
		$id_product = $_POST['id_product'];
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_options_group) {				
				$model->id_product = $id_product;
				$model->id_options_group = $id_options_group;
				
				$model->save();		
			}
		}		
	}
	
	
	
	
	public function actionSave_options_sort_order($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_options) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_options_group=:id_options_group AND id=:id_options'; 
				$criteria->params=array(':id_options_group'=>$id,':id_options'=>$id_options); 					
								
				if ($pvg = Tbl_Options::model()->find($criteria)) {
					$pvg->sort_order = $i;
					$pvg->save();
					
					++$i;
				}
			}
		}
	}
	
	

	/**
	 * This is the action to get an XML list of option group options
	 */
	public function actionXml_list_option_group_options($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('options.id_options_group=:id_options_group');
		$params=array(':id_options_group'=>$id);
		
		$where[] = 'options.archive=0';
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'options_description.name LIKE CONCAT(:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(options.id) AS total 
		FROM 
		options
		INNER JOIN 
		options_description 
		ON 
		(options.id = options_description.id_options AND options_description.language_code = '".Yii::app()->language."') 
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
		options.id,
		options.sku,
		options.qty,
		options.notify,
		options.notify_qty,
		options.price_type,
		options.price,
		options.special_price,
		options.special_price_from_date,
		options.special_price_to_date,
		options.active,
		options_description.name 
		FROM 
		options 
		INNER JOIN 
		options_description 
		ON 
		(options.id = options_description.id_options AND options_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY options_description.name ".$direct;		
		} else {

			$sql.=" ORDER BY options.sort_order ASC";
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
			echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
			<cell type="ro"><![CDATA['.(($row['notify'] and ($row['notify_qty'] >= $row['qty'])) ? '<span class="alert">'.$row['qty'].'</span>' : $row['qty']).']]></cell>
			<cell type="ro"><![CDATA['.(($row['special_price'] > 0 and (($row['special_price_from_date'] <= date("Y-m-d H:i:s") and $row['special_price_to_date'] > date("Y-m-d H:i:s")) or ($row['special_price_from_date'] == "0000-00-00 00:00:00"))) ? ($row['price_type'] ? $row['special_price'].'%' : Html::nf($row['special_price'])):($row['price_type'] ? $row['price'].'%' : Html::nf($row['price']))).']]></cell>
			
			</row>';
		}
		
		echo '</rows>';
	}			
	
	
	
	public function actionXml_list_price_shipping_options($id_options=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		$id_options=(int)$id_options;
		
		$where=array('options_price_shipping_region.id_options = ' . $id_options);
		$params=array(':language_code'=>Yii::app()->language);
									
		
		$sql = "SELECT 
		COUNT(options_price_shipping_region.id) AS total 
		FROM 
		options_price_shipping_region 
		LEFT JOIN 
		country_description
		ON
		(options_price_shipping_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(options_price_shipping_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
		options_price_shipping_region.id,
		options_price_shipping_region.price,
		country_description.name AS country,
		state_description.name AS state		
		FROM 
		options_price_shipping_region 
		LEFT JOIN 
		country_description
		ON
		(options_price_shipping_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(options_price_shipping_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'')."
		ORDER BY country_description.name ASC,state_description.name ASC";		
		
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
			<cell type="ro"><![CDATA['.Html::nf($row['price']).']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	public function actionDelete_region_options()
	{
		$ids = $_POST['ids'];
		$id_options = (int)$_POST['id_options'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_options_price_shipping_region) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_options_price_shipping_region);					
				
				// delete all
				Tbl_OptionsPriceShippingRegion::model()->deleteAll($criteria);						
			}
			$criteria=new CDbCriteria; 
			$criteria->condition='id_options=:id_options'; 
			$criteria->params=array(':id_options'=>$id_options); 		
			
			if (!Tbl_OptionsPriceShippingRegion::model()->count($criteria)) {
				Tbl_Options::model()->updateByPk($id_options,array('use_shipping_price'=>0));
			}
		}
	}
	public function actionEdit_regions_options_options($container, $id=0)
	{
	
		$model = new OptionsPriceShippingRegionsForm;
		$model->id_options = (int)$_POST["id_options"];
		$id = (int)$_POST["id"];		
		if ($id) {
			if ($price_shipping_region = Tbl_OptionsPriceShippingRegion::model()->findByPk($id)) {
				$model->id = $price_shipping_region->id;
				$model->country_code = $price_shipping_region->country_code;
				$model->state_code = $price_shipping_region->state_code;
				$model->id_options = $price_shipping_region->id_options;	
				$model->price = $price_shipping_region->price;	
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_regions_options_options',array('model'=>$model,'container'=>$container));		
	}
	
	public function actionSave_regions_options_options()
	{
		$model = new OptionsPriceShippingRegionsForm;
		
		// collect user input data
		if(isset($_POST['OptionsPriceShippingRegionsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['OptionsPriceShippingRegionsForm'] as $name=>$value)
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

	public function actionGet_province_list_options()
	{
		$model=new OptionsPriceShippingRegionsForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		$container = trim($_POST['container']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>$container.'_state_code'));
	}
	
	
	
	
	
	
	//Ship only into this region
	public function actionXml_list_ship_only_region_options($id_options=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		$id_options=(int)$id_options;
		
		$where=array('options_ship_only_region.id_options = ' . $id_options);
		$params=array(':language_code'=>Yii::app()->language);
									
		
		$sql = "SELECT 
		COUNT(options_ship_only_region.id) AS total 
		FROM 
		options_ship_only_region 
		LEFT JOIN 
		country_description
		ON
		(options_ship_only_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(options_ship_only_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
		options_ship_only_region.id,
		country_description.name AS country,
		state_description.name AS state		
		FROM 
		options_ship_only_region 
		LEFT JOIN 
		country_description
		ON
		(options_ship_only_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(options_ship_only_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
	
	public function actionDelete_ship_only_region_options()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_options_ship_only_region) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_options_ship_only_region);					
				
				// delete all
				Tbl_OptionsShipOnlyRegion::model()->deleteAll($criteria);						
			}
		}
	}
	
	public function actionEdit_ship_only_region_options($container, $id=0)
	{
		$model = new OptionsShipOnlyIntoThisRegionsForm;
		$model->id_options = (int)$_POST["id_options"];
		
		$id = (int)$_POST["id"];		
		if ($id) {
			if ($ship_only_region = Tbl_OptionsShipOnlyRegion::model()->findByPk($id)) {
				$model->id = $ship_only_region->id;
				$model->country_code = $ship_only_region->country_code;
				$model->state_code = $ship_only_region->state_code;	
				$model->id_options = $ship_only_region->id_options;
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_ship_only_region_options',array('model'=>$model,'container'=>$container));
				
	}

	public function actionSave_ship_only_region_options()
	{
		$model = new OptionsShipOnlyIntoThisRegionsForm;
		
		// collect user input data
		if(isset($_POST['OptionsShipOnlyIntoThisRegionsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['OptionsShipOnlyIntoThisRegionsForm'] as $name=>$value)
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
	
	public function actionGet_province_list_ship_only_region_options()
	{
		$model=new OptionsShipOnlyIntoThisRegionsForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		$container = trim($_POST['container']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>$container.'_state_code'));
	}
	//End Ship only into this region
	
	//Do not Ship into this region
	public function actionXml_list_do_not_ship_region_options($id_options=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		$id_options=(int)$id_options;
		
		$where=array('options_do_not_ship_region.id_options = ' . $id_options);
		$params=array(':language_code'=>Yii::app()->language);
									
		
		$sql = "SELECT 
		COUNT(options_do_not_ship_region.id) AS total 
		FROM 
		options_do_not_ship_region 
		LEFT JOIN 
		country_description
		ON
		(options_do_not_ship_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(options_do_not_ship_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
		options_do_not_ship_region.id,
		country_description.name AS country,
		state_description.name AS state		
		FROM 
		options_do_not_ship_region 
		LEFT JOIN 
		country_description
		ON
		(options_do_not_ship_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(options_do_not_ship_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
	
	public function actionDelete_do_not_ship_region_options()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_options_do_not_ship_region) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_options_do_not_ship_region);					
				
				// delete all
				Tbl_OptionsDoNotShipRegion::model()->deleteAll($criteria);						
			}
		}
	}
	
	public function actionEdit_do_not_ship_region_options($container, $id=0)
	{
		$model = new OptionsDoNotShipIntoThisRegionsForm;
		$model->id_options = (int)$_POST["id_options"];
		
		$id = (int)$_POST["id"];		
		if ($id) {
			if ($ship_only_region = Tbl_OptionsDoNotShipRegion::model()->findByPk($id)) {
				$model->id = $ship_only_region->id;
				$model->country_code = $ship_only_region->country_code;
				$model->state_code = $ship_only_region->state_code;
				$model->id_options = $ship_only_region->id_options;	
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_do_not_ship_region_options',array('model'=>$model,'container'=>$container));	

	}
	
	public function actionSave_do_not_ship_region_options()
	{

		$model = new OptionsDoNotShipIntoThisRegionsForm;
		
		// collect user input data
		if(isset($_POST['OptionsDoNotShipIntoThisRegionsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['OptionsDoNotShipIntoThisRegionsForm'] as $name=>$value)
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
	
	public function actionGet_province_list_do_not_ship_region_options()
	{
		$model=new OptionsDoNotShipIntoThisRegionsForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		$container = trim($_POST['container']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>$container.'_state_code'));
	}
	
	//End Do not Ship into this region
	
	
	
	
	
	
	
	
	/************************************************************
	*															*
	*															*
	*						CATEGORIES							*
	*															*
	*															*
	************************************************************/
	
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
		
		if (!Tbl_Product::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product'; 
		$criteria->params=array(':id_product'=>$id); 

		Tbl_ProductCategory::model()->deleteAll($criteria);
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_category) {
				$model = new Tbl_ProductCategory;
				$model->id_product = $id;
				$model->id_category = $id_category;
				if (!$model->save()){
					throw new CException(Yii::t('global','ERROR_SAVING'));	
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
		
		if (!Tbl_Product::model()->count($criteria)) {
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
	
	/**
	 * This is a function to get a list of the categories and sub categories recursively
	 */
	public function get_categories($id_product=0,$id_parent=0)
	{
		$id_product = (int)$id_product;
		$id_parent = (int)$id_parent;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent and active = 1'; 
		$criteria->params=array(':id_parent'=>$id_parent); 				
		$criteria->order='sort_order ASC';	
		
		$eol = "\r\n";
			
		foreach (Tbl_Category::model()->getDescription()->findAll($criteria) as $row) {		
			$criteria2=new CDbCriteria; 
			$criteria2->condition='id_parent=:id_parent'; 
			$criteria2->params=array(':id_parent'=>$row->id); 				
			$criteria2->order='sort_order ASC';			

			if($child = Tbl_Category::model()->count($criteria2)){
				//Call the function to check if there is sub category that are not checked so the parent dont have to be checked
				if($this->checked_or_not($row->id,$id_product)) {
					$checked = '';
				}else{
					$checked = '1';
				}		
			}else{
				$criteria2->condition='id_product=:id_product AND id_category=:id_category'; 
				$criteria2->params=array(':id_product'=>$id_product,':id_category'=>$row->id); 	
				$criteria2->order='';	
				$checked = Tbl_ProductCategory::model()->count($criteria2) ? 1:'';	
			}

		
			echo 'Child : ' . $child . ' child_category_product : ' . $child_category_product . '<item text="'.CHtml::encode($row->tbl_category_description[0]->name).'" id="'.$row->id.'" child="'.($child?1:0).'" checked="'.$checked.'" call="true" open="1">'.$eol;
			
			if ($child) { $this->get_categories($id_product, $row->id); }
			
			echo '</item>'.$eol;
		}			
		
	}
	
	public function checked_or_not($id_parent,$id_product){
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent'; 
		$criteria->params=array(':id_parent'=>$id_parent);
		 						
		$child = Tbl_Category::model()->count($criteria);
		
		// Verify if this category have child...if not, verify if this category is checked if not, parent category is not checked exit function
		if(!$child){
			$criteria->condition='id_product=:id_product AND id_category=:id_category'; 
			$criteria->params=array(':id_product'=>$id_product,':id_category'=>$id_parent); 	
			$criteria->order='';	
			
			$checked = Tbl_ProductCategory::model()->count($criteria) ? 1:'';
			if(!$checked){
				return 1;
			}
			
		}else{
	
			foreach (Tbl_Category::model()->findAll($criteria) as $row) {		
				if($this->checked_or_not($row->id,$id_product)){
					return 1;
					break;
				}
			}
			
		}
		
	}


	
	
	/************************************************************
	*															*
	*															*
	*							TAGS							*
	*															*
	*															*
	************************************************************/
	
	/**
	 * This is the action to delete a product tag
	 */
	public function actionDelete_tag()
	{
		// current product
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_tag) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id_tag'; 
				$criteria->params=array(':id'=>$id_tag);					
				
				// delete all
				Tbl_Tag::model()->deleteAll($criteria);			
			}
		}		
	}
	
	public function actionRemove_tag($id=0)
	{
		// current product
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_tag) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND id_tag=:id_tag'; 
				$criteria->params=array(':id_product'=>$id,':id_tag'=>$id_tag);					
				
				// delete all
				Tbl_ProductTag::model()->deleteAll($criteria);			
			}
		}		
	}	
	
	public function actionAdd_tag()
	{
		$model = new TagsAddTagForm;
		
		// current product
		$id_product = $_POST['id_product'];
		$ids = $_POST['ids'];
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();	
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_tag) {				
				$model->id_product = $id_product;
				$model->id_tag = $id_tag;
				$model->id_user_created = $current_id_user;
				$model->date_created = $current_datetime;
				
				$model->save();		
			}
		}		
	}
	
	public function actionXml_list_tag_description($container, $id=0, $id_product=0)
	{
		$model = new TagsForm;
		
		$id = (int)$id;
		$id_product = (int)$id_product;
		
		$model->id = $id;
		$model->id_product = $id_product;
		
		if ($id) {
			if ($tag = Tbl_Tag::model()->findByPk($id)) {							
				// grab description information 
				foreach ($tag->tbl_tag_description as $row) {
					$model->tag_description[$row->language_code]['name'] = $row->name;
					$model->tag_description[$row->language_code]['alias'] = $row->alias;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_TagDescription::tableName());		
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';

		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA['.(!$i?CHtml::activeHiddenField($model,'id',array('id'=>'id')) . CHtml::activeHiddenField($model,'id_product',array('id_product'=>'id_product')):'').'
					<div style="width:100%; height:100%; overflow:auto;" class="div_'.$container.'">	
					<div style="padding:10px;">			
					<div class="row">
						<strong>'.Yii::t('global','LABEL_NAME').'</strong>'.
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_tag_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->tag_description[$value->code]['name'])).'</span>)</em>':'').'
						<div>'.
						CHtml::activeTextField($model,'tag_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'onblur'=>'rewrite_alias($(this).attr("id"),"'.$container.'_tag_description['.$value->code.'][alias]");', 'id'=>$container.'_tag_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_tag_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					<div class="row">
						<strong>'.Yii::t('global','LABEL_ALIAS').'</strong> ('.Yii::t('global','LABEL_ALIAS_CHARACTERS_ALLOWED').'):'.
						(isset($columns['alias']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_tag_description['.$value->code.'][alias]_maxlength">'.($columns['alias']-strlen($model->tag_description[$value->code]['alias'])).'</span>)</em>':'').'
						<div>'.
						CHtml::activeTextField($model,'tag_description['.$value->code.'][alias]',array('style' => 'width: 98%;','maxlength'=>$columns['alias'], 'onkeyup'=>'rewrite_alias($(this).attr("id"),"");', 'id'=>$container.'_tag_description['.$value->code.'][alias]')).'
						<br /><span id="'.$container.'_tag_description['.$value->code.'][alias]_errorMsg" class="error"></span>
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
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_load_tag_group_template($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		// filters	
		
		$where=array();
		$params=array();
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'tpl_tag_group.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}						
		
		$sql = "SELECT 
		COUNT(tpl_tag_group.id) AS total 
		FROM 
		tpl_tag_group
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	;	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT 
		tpl_tag_group.id,
		tpl_tag_group.name
		FROM 
		tpl_tag_group
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	;		

		
		// sorting

		if (isset($filters['name']) && !empty($filters['name'])) {
			$sql.=" ORDER BY 
			IF(tpl_tag_group.name LIKE CONCAT(:name,'%'),1,0) DESC, tpl_tag_group.name ASC";
		}else{
			$sql.=" ORDER BY tpl_tag_group.name ASC";
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
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	
	

	
	public function actionLoad_tag_template($container)
	{		
		$this->renderPartial('load_tag_template',array('container'=>$container));				
	}
	
	/**
	 * This is the action to get a list of template of tag
	 */ 
	public function actionXml_list_search_tab_template($pos=0,$mask='')
	{
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection		
		
		$pos = (int)$pos;
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?>';
		
		if (!empty($mask)) {
			//create query 
			$sql = "SELECT 
			tpl_tag_group.id,
			tpl_tag_group.name 
			FROM 
			tpl_tag_group
			WHERE 			
			tpl_tag_group.name LIKE CONCAT('%',:mask,'%')
			ORDER BY 
			IF(tpl_tag_group.name LIKE CONCAT(:mask,'%'),1,0) DESC, tpl_tag_group.name ASC";				
			$command=$connection->createCommand($sql);
						
			echo '<complete>';
			
			foreach ($command->queryAll(true, array(':mask'=>$mask)) as $row) {
				echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
			}
			
			echo '</complete>';
		}
	}
	
	public function actionApply_tag_template($id=0)
	{
		$id = (int)$id;
		$id_tpl_tag_group = (int)$_POST['id_tpl_tag_group'];
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();	
		
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection			
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (!Tbl_Product::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		
		// apply template
		$criteria=new CDbCriteria; 
		$criteria->condition='id_tpl_tag_group=:id_tpl_tag_group'; 
		$criteria->params=array(':id_tpl_tag_group'=>$id_tpl_tag_group); 			
		
		foreach (Tbl_TplTag::model()->findAll($criteria) as $row){
			
			//Verify if already exist
			$criteria2=new CDbCriteria; 
			$criteria2->condition='id_tag=:id_tag AND id_product=:id_product'; 
			$criteria2->params=array(':id_tag'=>$row->id_tag,':id_product'=>$id); 		
			
			if (!Tbl_ProductTag::model()->count($criteria2)) {
				//If not exist
				$model = new Tbl_ProductTag;
				$model->id_tag = $row->id_tag;
				$model->id_product = $id;
				$model->id_user_created = $current_id_user;
				$model->date_created = $current_datetime;
				
				if (!$model->save()){
					throw new CException(Yii::t('global','ERROR_SAVING'));	
				}
			}
			
			
			
		}
				
	}	
	
	/**
	 * This is the action to get an XML list of tag from template
	 */
	public function actionXml_list_load_tag_template($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id = (int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('tpl_tag.id_tpl_tag_group=:id_tpl_tag_group');
		$params=array(':id_tpl_tag_group'=>$id);
		
		// filters							
		
		$sql = "SELECT 
		COUNT(tpl_tag.id_tag) AS total 
		FROM 
		tpl_tag
		INNER JOIN 
		tag_description
		ON 
		(tpl_tag.id_tag = tag_description.id_tag AND tag_description.language_code = '".Yii::app()->language."') 
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
		tpl_tag.id_tag,
		tag_description.name
		FROM 
		tpl_tag
		INNER JOIN 
		tag_description
		ON 
		(tpl_tag.id_tag = tag_description.id_tag AND tag_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		$sql.=" ORDER BY 
		tpl_tag.sort_order ASC";
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id_tag'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	/**
	 * This is the action to get a list of tag
	 */ 
	public function actionXml_list_search_tag_template($pos=0,$mask='')
	{
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection		
		
		$pos = (int)$pos;
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?>';
		
		if (!empty($mask)) {
			//create query 
			$sql = "SELECT 
			tpl_tag_group.id,
			tpl_tag_group.name 
			FROM 
			tpl_tag_group
			WHERE 			
			tpl_tag_group.name LIKE CONCAT('%',:mask,'%')
			ORDER BY
			IF(tpl_tag_group.name LIKE CONCAT(:mask,'%'),1,0) DESC, tpl_tag_group.name ASC";				
			$command=$connection->createCommand($sql);
						
			echo '<complete>';
			
			foreach ($command->queryAll(true, array(':mask'=>$mask)) as $row) {
				echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
			}
			
			echo '</complete>';
		}
	}
	
	public function actionCount_tag_groups($id)
	{
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product'; 
		$criteria->params=array(':id_product'=>$id); 			
		
		echo Tbl_ProductTag::model()->count($criteria);
	}	
	
	public function actionSave_tag_template()
	{
		$model = new ProductsAddTagTemplateForm;
		
		// collect user input data
		if(isset($_POST['ProductsAddTagTemplateForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ProductsAddTagTemplateForm'] as $name=>$value)
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
	
	public function actionAdd_tag_template($container, $id=0)
	{
		$model = new ProductsAddTagTemplateForm;
		
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (Tbl_Product::model()->count($criteria)) {
			$model->id_product = $id;
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
		$this->renderPartial('add_tag_template',array('model'=>$model,'container'=>$container));				
	}
	
	/**
	 * This is the action to get an XML list of tag for this product
	 */
	public function actionXml_list_tag($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('product_tag.id_product=:id_product');
		$params=array(':id_product'=>$id);
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'tag_description.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(product_tag.id_tag) AS total 
		FROM 
		product_tag 
		INNER JOIN 
		tag_description 
		ON 
		(product_tag.id_tag = tag_description.id_tag AND tag_description.language_code = '".Yii::app()->language."') 
		INNER JOIN 
		tag
		ON 
		(product_tag.id_tag = tag.id)
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
		product_tag.id_tag,
		tag_description.name,
		tag_description.alias,
		tag_description.description
		FROM 
		product_tag 
		INNER JOIN 
		tag_description 
		ON 
		(product_tag.id_tag = tag_description.id_tag AND tag_description.language_code = '".Yii::app()->language."') 
		INNER JOIN 
		tag
		ON 
		(product_tag.id_tag = tag.id) 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tag_description.name ".$direct;	
		} else {
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(tag_description.name LIKE CONCAT(:name,'%'),0,1) ASC, tag_description.name ASC";
			} else {
				$sql.=" ORDER BY tag_description.name ASC";
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
			echo '<row id="'.$row['id_tag'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['description'].']]></cell>
			<cell type="ro"><![CDATA['.$row['alias'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/** 
	 *
	 */
	
	
	public function actionSave_tag()
	{
		$model = new TagsForm;
		
		// collect user input data
		if(isset($_POST['TagsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['TagsForm'] as $name=>$value)
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
	
	public function actionCount_product_tag($id)
	{
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product'; 
		$criteria->params=array(':id_product'=>$id); 			
		
		echo Tbl_ProductTag::model()->count($criteria);
	}
		
	
	public function actionAdd_product_tag($id=0)
	{
		$id = (int)$id;
		$id_tag = (int)$_POST['id_tag'];
		
		if ($id && $id_tag) {			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product=:id_product AND id_tag=:id_tag'; 
			$criteria->params=array(':id_product'=>$id,':id_tag'=>$id_tag);				
			
			if (!Tbl_ProductTag::model()->count($criteria)) {
				$ps = new Tbl_ProductTag;
				$ps->id_product = $id;
				$ps->id_tag = $id_tag;
				if (!$ps->save()){
					echo 'false';
					exit;
				}
			}
			
			echo 'true';
			exit;
		}
		
		echo 'false';
	}
	
	
	
	public function actionXml_list_tag_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		if(isset($_GET['id_product']) and !empty($_GET['id_product'])){
			$id_product = (int)$_GET['id_product'];
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array();
			$params=array();
			
			$where[]='product_tag.id_tag IS NULL';
			
			// filters
			
			// name
			if (isset($filters['name']) && !empty($filters['name'])) {
				$where[] = 'tag_description.name LIKE CONCAT("%",:name,"%")';
				$params[':name']=$filters['name'];
			}				
			
			$sql = "SELECT 
			COUNT(tag.id) AS total  
			FROM 
			tag 
			INNER JOIN 
			tag_description 
			ON 
			(tag.id = tag_description.id_tag AND tag_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			product_tag 
			ON 
			tag.id=product_tag.id_tag and product_tag.id_product = " . $id_product . "
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
			tag.id,
			tag_description.name 
			FROM 
			tag 
			INNER JOIN 
			tag_description 
			ON 
			(tag.id = tag_description.id_tag AND tag_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			product_tag 
			ON 
			tag.id=product_tag.id_tag and product_tag.id_product = " . $id_product . "
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
			
			// sorting
			// name
			if (isset($sort_col[1]) && !empty($sort_col[1])) {	
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY tag_description.name ".$direct;	
			} else {
				if (isset($filters['name']) && !empty($filters['name'])) { 
					$sql.=" ORDER BY IF(tag_description.name LIKE CONCAT(:name,'%'),0,1) ASC, tag_description.name ASC";
				} else {
					$sql.=" ORDER BY tag_description.name ASC";
				}
			}	
			
			//add limits to query to get only rows necessary for the output
			//$sql.= " LIMIT ".$posStart.",".$count;
			
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
				</row>';
			}
			
			echo '</rows>';
			
		}
	}

	/************************************************************
	*															*
	*															*
	*						REVIEWS								*
	*															*
	*															*
	************************************************************/
	
	/**
	 * This is the action to delete a product tag
	 */
	public function actionDelete_review()
	{
		// current product
		$ids = $_POST['ids'];
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_review) {
				if ($p = Tbl_ProductReview::model()->findByPk($id_review)) {
					//create query 
			
					$sql = 'SELECT 
					SUM(rated) AS avg_rated,
					COUNT(id) AS total_rated
					FROM 
					product_review
					WHERE id_product = "'. $p->id_product .'"';	

					$command=$connection->createCommand($sql);
					$row = $command->queryRow(true);
					
					$total_rated = $row['total_rated']-1;
					$avg_rating = ($total_rated>0)?floor(($row['avg_rated'] - $p->rated)/$total_rated):0;
					
					$criteria=new CDbCriteria; 
					$criteria->condition='id_product=:id_product'; 
					$criteria->params=array(':id_product'=>$p->id_product); 	
					Tbl_ProductRatingCount::model()->updateAll(array('total_rating'=>$total_rated,'avg_rating'=>$avg_rating),$criteria);		
				}
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id_review'; 
				$criteria->params=array(':id_review'=>$id_review);					
				
				// delete all
				Tbl_ProductReview::model()->deleteAll($criteria);			
			}
		}		
	}
	
	
	public function actionEdit_reviews_options($container)	
	{
		
		$model=new ReviewsForm;	
		
		$id = (int)$_POST['id'];
		
		if ($id) { 
			if (!$p = Tbl_ProductReview::model()->findByPk($id)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		

			$model->id = $id;
			$model->title = $p->title;	
			$model->review = $p->review;
			$model->anonymous = $p->anonymous;
			
			$this->renderPartial('edit_reviews_options',array('model'=>$model, 'container'=>$container));	

		}	
	}
	
	
	
	/**
	 * This is the action to get an XML list of tag for this product
	 */
	public function actionXml_list_review($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('product_review.id_product=:id_product');
		$params=array(':id_product'=>$id);
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = '(customer.firstname LIKE CONCAT(:name,"%") OR customer.lastname LIKE CONCAT(:name,"%"))';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(product_review.id) AS total 
		FROM 
		product_review 
		INNER JOIN 
		product 
		ON 
		(product_review.id_product = product.id) 
		INNER JOIN 
		customer
		ON 
		(product_review.id_customer = customer.id)
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
		product_review.id,
		product_review.title,
		product_review.review,
		product_review.anonymous,
		product_review.rated,
		product_review.approved,
		product_review.date_created,
		(CONCAT(customer.firstname, ' ', customer.lastname)) AS customer_name
		FROM 
		product_review 
		INNER JOIN 
		product 
		ON 
		(product_review.id_product = product.id) 
		INNER JOIN 
		customer
		ON 
		(product_review.id_customer = customer.id)
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		// sorting
		
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product_review.date_created ".$direct;
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY customer.firstname ".$direct;
		}else if (isset($sort_col[4]) && !empty($sort_col[4])) {
			$direct = $sort_col[4] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product_review.rated ".$direct;
		} else{
			$sql.=" ORDER BY product_review.date_created DESC";
		}
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		//echo $sql;
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['date_created'].']]></cell>
			<cell type="ro"><![CDATA['.$row['customer_name'].($row['anonymous']?'<br />(Anonymous)':'').']]></cell>
			<cell type="ro"><![CDATA[<strong>'.$row['title'].'</strong><br /><br />'.nl2br($row['review']).']]></cell>
			<cell type="ro"><![CDATA['.($row['approved']?'<strong>X</strong>':'-').']]></cell>
			<cell type="ro"><![CDATA['.Html::get_rated_star($row['rated']).']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/** 
	 *
	 */
	
	
	public function actionSave_review()
	{
		$model = new ReviewsForm;
		
		// collect user input data
		if(isset($_POST['ReviewsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ReviewsForm'] as $name=>$value)
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/************************************************************
	*															*
	*															*
	*			LISTS OF COMBOS AND BUNDLED PRODUCTS			*
	*															*
	*															*
	************************************************************/
		
	
	/**
	 * This is the action to get an XML list of combos that current product is included
	 */
	public function actionXml_list_combo($id=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$params=array();

		$params[':id_combo_product'] = $id;

		$sql = "SELECT 
		product.id 
		FROM 
		product
		INNER JOIN 
		product_combo 
		ON 
		(product.id = product_combo.id_product AND product_combo.id_combo_product=:id_combo_product) 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."')
		WHERE product.product_type = 1 
		GROUP BY product.id";	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryAll(true, $params);
			$totalCount = sizeof($row);		
		}
		
		$sql = "SELECT 
		product.sku,
		product.active,
		product_description.name 
		FROM 
		product
		INNER JOIN 
		product_combo 
		ON 
		(product.id = product_combo.id_product AND product_combo.id_combo_product=:id_combo_product) 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."')
		WHERE product.product_type = 1 
		GROUP BY product.id";		

		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/**
	 * This is the action to get an XML list of bundled products that current product is included
	 */
	public function actionXml_list_bundled($id=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$params=array();

		$params[':id_product'] = $id;

		$sql = "SELECT 
		product.id 
		FROM 
		product
		INNER JOIN 
		product_bundled_product_group 
		ON 
		(product.id = product_bundled_product_group.id_product) 
		INNER JOIN 
		product_bundled_product_group_product 
		ON 
		(product_bundled_product_group.id = product_bundled_product_group_product.id_product_bundled_product_group AND product_bundled_product_group_product.id_product=:id_product) 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
		WHERE product.product_type = 2
		GROUP BY product.id";
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryAll(true, $params);
			$totalCount = sizeof($row);		
		}
		
		$sql = "SELECT 
		product.sku,
		product.active,
		product_description.name 
		FROM 
		product
		INNER JOIN 
		product_bundled_product_group 
		ON 
		(product.id = product_bundled_product_group.id_product) 
		INNER JOIN 
		product_bundled_product_group_product 
		ON 
		(product_bundled_product_group.id = product_bundled_product_group_product.id_product_bundled_product_group AND product_bundled_product_group_product.id_product=:id_product)
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
		WHERE product.product_type = 2
		GROUP BY product.id";		

		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/************************************************************
	*															*
	*															*
	* 				DOWNLOADABLE VIDEOS & FILES   				*
	*															*
	*															*
	************************************************************/	
	
	
	/** 
	 *
	 */
	public function actionXml_list_downloadable_videos($id, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':id' => $id);
		$where[] = 'product_downloadable_videos.id_product = :id';
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'product_downloadable_videos.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}								
		
		$sql = "SELECT 
		COUNT(product_downloadable_videos.id) AS total 
		FROM 
		product_downloadable_videos 
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
		product_downloadable_videos.id,
		product_downloadable_videos.name,
		product_downloadable_videos.no_days_expire,
		product_downloadable_videos.no_downloads,
		product_downloadable_videos.sort_order
		FROM 
		product_downloadable_videos
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product_downloadable_videos.name ".$direct;
		// no_days_expire
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product_downloadable_videos.no_days_expire ".$direct;
		// no_downloads
		} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product_downloadable_videos.no_downloads ".$direct;					
		} else {
			$sql.=" ORDER BY product_downloadable_videos.sort_order ASC, product_downloadable_videos.id DESC";
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
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product_downloadable_videos=:id_product_downloadable_videos'; 
			$criteria->params=array(':id_product_downloadable_videos'=>$row['id']); 
			$criteria->limit=1; 	
			if (Tbl_OrdersItemProductDownloadableVideos::model()->find($criteria)) {
				$cannot_delete = 1;
			}else{
				$cannot_delete = 0;	
			}
			echo '<row id="'.$row['id'].'">
			'.($cannot_delete?'<cell type="ro" />':'<cell type="ch" />').'
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}		
	
	/** 
	 *
	 */
	public function actionXml_list_downloadable_videos_description($container, $id=0)
	{
		$model = new ProductDownloadableVideosForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($product = Tbl_ProductDownloadableVideos::model()->findByPk($id)) {							
				// grab description information 
				foreach ($product->tbl_product_downloadable_videos_description as $row) {
					$model->description[$row->language_code]['name'] = $row->name;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_ProductDownloadableVideosDescription::tableName());		
		
		$help_hint_path = '/catalog/products/downloadable-videos-files/';
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';
		
		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:auto;" class="div_'.$container.'">	
					<div style="padding:10px;">			
					<div class="row">
						<strong>'.Yii::t('global','LABEL_NAME').'</strong>'.
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'link-name').'
						<div>'.
						CHtml::activeTextField($model,'description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>$container.'_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_description['.$value->code.'][name]_errorMsg" class="error"></span>
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
	
	public function actionEdit_downloadable_videos_options($container, $id_product)
	{
		$model = new ProductDownloadableVideosForm;
		
		$id = (int)$_POST['id'];
		$id_product = (int)$id_product;
		
		if (!$p = Tbl_Product::model()->findByPk($id_product)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}			
		
		$has_variants = $p->has_variants;	
		
		$model->id_product = $id_product;
		
		if ($id) {
			if ($p = Tbl_ProductDownloadableVideos::model()->findByPk($id)) {
				$model->id = $p->id;
				$model->id_product = $p->id_product;
				$model->id_product_variant = $p->id_product_variant;
				$model->name = $p->name;
				$model->embed_code = $p->embed_code;
				$model->stream = $p->stream;
				$model->filename = $p->filename;
				$model->no_days_expire = $p->no_days_expire;
				$model->no_downloads = $p->no_downloads;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}	
		
		$this->renderPartial('edit_downloadable_videos_options',array('model'=>$model, 'container'=>$container, 'has_variants'=>$has_variants));	
	}			
	
	/**
	 * This is the action to save downloable video
	 */
	public function actionSave_downloadable_video()
	{
		// collect user input data
		if(isset($_POST['ProductDownloadableVideosForm']))
		{
			$model = new ProductDownloadableVideosForm;
	
			// loop through each attribute and set it in our model
			foreach($_POST['ProductDownloadableVideosForm'] as $name=>$value)
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
	
	/**
	 * This is the action to save files order
	 */
	public function actionSave_videos_sort_order($id=0)
	{
		// current product
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_product_video) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND id=:id'; 
				$criteria->params=array(':id_product'=>$id,':id'=>$id_product_video); 					
				if ($ps = Tbl_ProductDownloadableVideos::model()->find($criteria)) {
					$ps->sort_order = $i;
					$ps->save();
					
					++$i;
				}
			}
		}
		
	}	
	
	/**
	 * This is the action to delete downloadable video
	 */
	public function actionDelete_downloadable_video($id_product)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$id_product = (int)$id_product;
		$ids = $_POST['ids'];
		$file_base_path = dirname(Yii::app()->getBasePath()).'/protected/streaming_videos/';
		
		if (is_array($ids) && sizeof($ids)) {
			// prepare delete options
			$command_delete_video=$connection->createCommand('DELETE FROM 
			product_downloadable_videos,
			product_downloadable_videos_description
			USING
			product_downloadable_videos
			INNER JOIN
			product_downloadable_videos_description
			ON
			(product_downloadable_videos.id = product_downloadable_videos_description.id_product_downloadable_videos)
			WHERE 
			product_downloadable_videos.id_product=:id_product
			AND
			product_downloadable_videos.id=:id');								
			
			foreach ($ids as $id) {
				if (!$p = Tbl_ProductDownloadableVideos::model()->findByPk($id)) {
					throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
				}					
				
				if (is_file($file_base_path.$p->source)) @unlink($file_base_path.$p->source);
				
				$command_delete_video->execute(array(':id_product'=>$id_product,':id'=>$id));		
			}
			
		}
	}
	
	/**
	 * This is the action to upload file
	 */
	public function actionUpload_downloadable_video($id)
	{					
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = $app->user->getId();	
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (!Tbl_Product::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		// if id is not set
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = dirname(Yii::app()->getBasePath()).'/protected/streaming_videos/';
			$targetFile = $_FILES['Filedata']['name'];
			$ext = strtolower(trim(pathinfo($targetFile, PATHINFO_EXTENSION)));
			$allowed_ext = array();									
			
			if (empty($ext)) {
				echo Yii::t('controllers/ProductsController','ERROR_NO_EXTENSION');				
				exit;
			} else {											
				if (!move_uploaded_file($tempFile,$targetPath.$targetFile)) {
					echo Yii::t('global','ERROR_SAVING');
					exit;					
				}
				
				echo 'file:'.$targetFile;
				exit;
				break;
			}
			
			echo Yii::t('controllers/ProductsController','ERROR_UPLOAD_FILE_FAILED');
		}			
	}	
	
	
	
	
	public function actionGet_video($id)
	{
		
		$id = (int)$id;
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$sql = "SELECT 
		pdv.embed_code,
		pdv.stream,
		pdv.filename,
		pdv.source,
		pdvd.name
		FROM 
		product_downloadable_videos AS pdv
		INNER JOIN 
		product_downloadable_videos_description AS pdvd
		ON 
		(pdv.id = pdvd.id_product_downloadable_videos AND pdvd.language_code = '".Yii::app()->language."') 
		WHERE pdv.id = " . $id;	
		
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true, array());		
		
		$output = array();
		$video = $row['embed_code'];
		$filename = trim($_GET['filename']);
		$name = $row['name']?$row['name']:$filename;
		$source = $row['source'];
		$width = 320;
		$height = 240;
		
		if (!$row['stream'] and empty($filename)) {
			// dailymotion
			if (strstr($video,'dailymotion')) $stream = 0;
			else {
				$stream = 1;
				
				$video = "<div id='mediaspace' style='width:".$width."px; height:".$height."px;'></div>
				<script type='text/javascript'>  
				// setimeout is used because dialog doesn't center properly when using html5
				setTimeout(function(){
					jwplayer('mediaspace').setup({
						modes: [
							{ type: 'flash', src: '/includes/js/mediaplayer/player.swf' },
							{ 
								type: 'html5',
								config: {
									'file': '".$video."',
									'provider': 'youtube'
								}			
							}
						],	
						'file': '".$video."',
						'stretching': 'fill',
						'controlbar': 'bottom',
						'width': '".$width."',
						'height': '".$height."',
					//	'bufferlength': 5,
						'smoothing': false,
						'skin': '/includes/js/mediaplayer/skins/glow/glow.zip'
					});
				},1);
				</script>";
			}
		} else {			
					
			$video = "<div id='mediaspace' style='width:".$width."px; height:".$height."px;'></div>
			<script type='text/javascript'>  
			// setimeout is used because dialog doesn't center properly when using html5
			setTimeout(function(){
				jwplayer('mediaspace').setup({
					modes: [
						{ type: 'flash', src: '/includes/js/mediaplayer/player.swf' },
						{ 
							type: 'html5',
							config: {
								'file': '/admin/protected/components/play-video.mp4?source=".$source."&filename=".$filename."',
								'provider': 'video'
							}			
						}						
					],	
					'provider': 'rtmp',	
					'streamer': 'rtmp://198.1.127.89/oflaDemo',
					'file': 'mp4:".get_current_user()."/".($id?$source:$filename)."',
					'stretching': 'fill',
					'controlbar': 'bottom',
					'width': '".$width."',
					'height': '".$height."',
				//	'bufferlength': 5,
					'smoothing': false,
					'skin': '/includes/js/mediaplayer/skins/glow/glow.zip'
				});
			},1);
			</script>";
		}
		
		$output = array('video' => $video, 'video_width' => $width, 'video_height' => $height, 'stream' => $stream, 'name' => $name);

		echo json_encode($output);
		
	}	
	
		 	

	/** 
	 *
	 */
	public function actionXml_list_downloadable_files($id, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':id' => $id);
		$where[] = 'product_downloadable_files.id_product = :id';
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'product_downloadable_files.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}								
		
		$sql = "SELECT 
		COUNT(product_downloadable_files.id) AS total 
		FROM 
		product_downloadable_files 
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
		product_downloadable_files.id,
		product_downloadable_files.name,
		product_downloadable_files.filename,
		product_downloadable_files.no_days_expire,
		product_downloadable_files.no_downloads,
		product_downloadable_files.type,
		product_downloadable_files.sort_order
		FROM 
		product_downloadable_files
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product_downloadable_files.name ".$direct;
		// no_days_expire
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product_downloadable_files.no_days_expire ".$direct;
		// no_downloads
		} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY product_downloadable_files.no_downloads ".$direct;					
		} else {
			$sql.=" ORDER BY product_downloadable_files.sort_order ASC, product_downloadable_files.id DESC";
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
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product_downloadable_files=:id_product_downloadable_files'; 
			$criteria->params=array(':id_product_downloadable_files'=>$row['id']); 
			$criteria->limit=1; 	
			if (Tbl_OrdersItemProductDownloadableFiles::model()->find($criteria)) {
				$cannot_delete = 1;
			}else{
				$cannot_delete = 0;	
			}
			echo '<row id="'.$row['id'].'">
			'.($cannot_delete?'<cell type="ro" />':'<cell type="ch" />').'
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['filename'].']]></cell>
			<cell type="ro"><![CDATA['.$row['type'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}		
	
	/** 
	 *
	 */
	public function actionXml_list_downloadable_files_description($container, $id=0)
	{
		$model = new ProductDownloadableFilesForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($product = Tbl_ProductDownloadableFiles::model()->findByPk($id)) {							
				// grab description information 
				foreach ($product->tbl_product_downloadable_files_description as $row) {
					$model->description[$row->language_code]['name'] = $row->name;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_ProductDownloadableFilesDescription::tableName());		
		
		$help_hint_path = '/catalog/products/downloadable-videos-files/';
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';
		
		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:auto;" class="div_'.$container.'">	
					<div style="padding:10px;">			
					<div class="row">
						<strong>'.Yii::t('global','LABEL_NAME').'</strong>'.
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'link-name').'
						<div>'.
						CHtml::activeTextField($model,'description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>$container.'_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_description['.$value->code.'][name]_errorMsg" class="error"></span>
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
	
			
	
	public function actionVerify_scorm($id_product)
	{
		$app = Yii::app();
		
		$id = (int)$_POST['id'];
		$id_product = (int)$id_product;
		
		if (!$p = Tbl_Product::model()->findByPk($id_product)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}			
		
		if ($id && $app->params['scorm_certificate']) {
			if ($p = Tbl_ProductDownloadableFiles::model()->findByPk($id)) {
				if($p->type == "ADL SCORM 1.2"){
					echo '1';	
				}
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}	
			
	}
	
	
	/**
	 * This is the action to get an XML list 
	 */
	public function actionXml_list_scorm_certificate($id_product_downloadable_files)
	{

		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id_product_downloadable_files;
		
		$params=array(':id'=>$id);
		
		$sql = 'SELECT 
		scorm_certificate_product.id,
		scorm_certificate.name		
		FROM		
		scorm_certificate_product
		INNER JOIN 
		product_downloadable_files
		ON
		scorm_certificate_product.id_product_downloadable_files = product_downloadable_files.id 
		AND
		product_downloadable_files.id = :id
		LEFT JOIN 
		scorm_certificate
		ON
		scorm_certificate_product.id_scorm_certificate = scorm_certificate.id 
		ORDER BY 
		scorm_certificate.name ASC';
				
		$command=$connection->createCommand($sql);
		
		$rows = $command->queryAll(true, $params);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.sizeof($rows).'">';
		
		// Cycle through results
		foreach ($rows as $row) {
			// Conditions
			$condition_text = '';
			$params_additional_field=array(':id'=>$row['id'], ':language_code'=>Yii::app()->language);

			$sql = 'SELECT 
			scorm_certificate_condition.id_custom_fields_option,
			scorm_certificate_condition.id_custom_fields,
			custom_fields_description.name AS custom_field_name,
			custom_fields_option_description.name AS custom_field_option_name,
			scorm_certificate_condition.score_from,
			scorm_certificate_condition.score_to		
			FROM		
			scorm_certificate_condition
			INNER JOIN 
			scorm_certificate_product
			ON
			scorm_certificate_condition.id_scorm_certificate_product = scorm_certificate_product.id
			AND
			scorm_certificate_product.id = :id
			
			
			LEFT JOIN 
			(custom_fields CROSS JOIN custom_fields_description)
			ON 
			(scorm_certificate_condition.id_custom_fields = custom_fields.id  AND custom_fields.id = custom_fields_description.id_custom_fields AND custom_fields_description.language_code=:language_code)
			
			
			LEFT JOIN 
			(custom_fields_option CROSS JOIN custom_fields_option_description)
			ON 
			(scorm_certificate_condition.id_custom_fields_option = custom_fields_option.id  AND custom_fields_option.id = custom_fields_option_description.id_custom_fields_option AND custom_fields_option_description.language_code=:language_code)
			
			WHERE scorm_certificate_condition.id_scorm_certificate_product = :id
			ORDER BY 
			scorm_certificate_condition.id ASC';
					
			$command_additional_field=$connection->createCommand($sql);
			
			$rows_additional_field = $command_additional_field->queryAll(true, $params_additional_field);
			
			// Cycle through results
			foreach ($rows_additional_field as $row_additional_field) {
				switch($row_additional_field['id_custom_fields']){
					// If -1 it means We have to look in the score_from and score_to instead of id_custom_fields_option
					case -1:
						$custom_field_name = Yii::t('global','LABEL_SCORE_BETWEEN_X_Y');
						$custom_field_option_name = Yii::t('global','LABEL_FROM') . ' ' . $row_additional_field['score_from'] . ' ' . Yii::t('global','LABEL_TO') . ' ' . $row_additional_field['score_to'];
					break;
					default:
						$custom_field_name = $row_additional_field['custom_field_name'];
						switch($row_additional_field['id_custom_fields_option']){
							case -1:
								$custom_field_option_name = Yii::t('global','LABEL_YES');
							break;
							case -2:
								$custom_field_option_name = Yii::t('global','LABEL_NO');
							break;
							default:
								$custom_field_option_name = $row_additional_field['custom_field_option_name'];
							break;
							
						}
					break;
					
				}
				

				
				$condition_text = $condition_text . '<strong>'.$custom_field_name.'</strong> : '. $custom_field_option_name . '<br />';
			}

			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.($row['name']?$row['name']:'-').']]></cell>
			<cell type="ro"><![CDATA['.($condition_text?$condition_text:'-').']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	public function actionEdit_scorm_certificate_options($container, $id_product)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$model = new ProductScormCertificateProductForm;
		
		$id = (int)$_POST['id'];
		$id_product_downloadable_files = (int)$_POST['id_product_downloadable_files'];
		$id_product = (int)$id_product;
		
		if (!$p = Tbl_Product::model()->findByPk($id_product)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}			
		
		if ($id) {
			if ($p = Tbl_ScormCertificateProduct::model()->findByPk($id)) {
				$model->id = $p->id;
				$model->id_product_downloadable_files = $p->id_product_downloadable_files;
				$model->id_scorm_certificate = $p->id_scorm_certificate;
				
				// Additionnals Fields
				$sql = 'SELECT
				scaf.id,
				IF(scafv.value IS NULL,"",scafv.value) AS value
				FROM
				scorm_certificate_additional_field AS scaf
				LEFT JOIN 
				scorm_certificate_additional_field_value AS scafv
				ON
				scaf.id = scafv.id_scorm_cetificate_additional_field AND scafv.id_scorm_certificate_product = "' . $model->id  .'"';
				$command=$connection->createCommand($sql);
				
				foreach ($command->queryAll(true) as $row) {
					$model->additional_field[$row['id']] = $row['value'];
				}
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}	
		
		$this->renderPartial('edit_scorm_certificate_options',array('model'=>$model, 'container'=>$container, 'id_product_downloadable_files'=>$id_product_downloadable_files));	
	}
	
	
	
	/**
	 * This is the action to create scorm certificate
	 */
	public function actionCreate_scorm_certificate_product()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id_product_downloadable_files = (int)$_POST['id_product_downloadable_files'];
		$params = array(':id_product_downloadable_files' => $id_product_downloadable_files);		
		
		$sql = 'INSERT INTO 
				scorm_certificate_product
				SET
				id_product_downloadable_files = :id_product_downloadable_files';
				$command = $connection->createCommand($sql);
				
				$command->execute($params);
					
				echo $connection->getLastInsertID();
	}
	
	
	/**
	 * This is the action to save scorm certificate
	 */
	public function actionSave_scorm_certificate_product()
	{
		// collect user input data
		if(isset($_POST['ProductScormCertificateProductForm']))
		{
			$model = new ProductScormCertificateProductForm;
	
			// loop through each attribute and set it in our model
			foreach($_POST['ProductScormCertificateProductForm'] as $name=>$value)
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
	
	
	
	/**
	 * This is the action to delete certificate
	 */
	public function actionDelete_scorm_certificate_product()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			// prepare delete options
			$command_delete_video=$connection->createCommand('DELETE FROM 
			scorm_certificate_product,
			scorm_certificate_condition,
			scorm_certificate_additional_field_value
			USING
			scorm_certificate_product
			LEFT JOIN
			scorm_certificate_condition
			ON
			(scorm_certificate_product.id = scorm_certificate_condition.id_scorm_certificate_product)
			LEFT JOIN
			scorm_certificate_additional_field_value
			ON
			(scorm_certificate_product.id = scorm_certificate_additional_field_value.id_scorm_certificate_product)
			WHERE 
			scorm_certificate_product.id=:id');								
			
			foreach ($ids as $id) {
				$command_delete_video->execute(array(':id'=>$id));		
			}
			
		}
	}
	
	/**
	 * This is the action to get an XML list 
	 */
	public function actionXml_list_certificate_condition($id_scorm_certificate_product)
	{

		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id_scorm_certificate_product;
		
		$params=array(':id'=>$id, ':language_code'=>Yii::app()->language);
		
		$sql = 'SELECT 
		scorm_certificate_condition.id,
		scorm_certificate_condition.id_custom_fields_option,
		scorm_certificate_condition.id_custom_fields,
		custom_fields_description.name AS custom_field_name,
		custom_fields_option_description.name AS custom_field_option_name,
		scorm_certificate_condition.score_from,
		scorm_certificate_condition.score_to		
		FROM		
		scorm_certificate_condition
		INNER JOIN 
		scorm_certificate_product
		ON
		scorm_certificate_condition.id_scorm_certificate_product = scorm_certificate_product.id
		AND
		scorm_certificate_product.id = :id
		
		
		LEFT JOIN 
		(custom_fields CROSS JOIN custom_fields_description)
		ON 
		(scorm_certificate_condition.id_custom_fields = custom_fields.id  AND custom_fields.id = custom_fields_description.id_custom_fields AND custom_fields_description.language_code=:language_code)
		
		
		LEFT JOIN 
		(custom_fields_option CROSS JOIN custom_fields_option_description)
		ON 
		(scorm_certificate_condition.id_custom_fields_option = custom_fields_option.id  AND custom_fields_option.id = custom_fields_option_description.id_custom_fields_option AND custom_fields_option_description.language_code=:language_code)
		
		
		ORDER BY 
		scorm_certificate_condition.id ASC';
				
		$command=$connection->createCommand($sql);
		
		$rows = $command->queryAll(true, $params);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.sizeof($rows).'">';
		
		// Cycle through results
		foreach ($rows as $row) {
			
			switch($row['id_custom_fields']){
				// If -1 it means We have to look in the score_from and score_to instead of id_custom_fields_option
				case -1:
					$custom_field_name = Yii::t('global','LABEL_SCORE_BETWEEN_X_Y');
					$custom_field_option_name = Yii::t('global','LABEL_FROM') . ' ' . $row['score_from'] . ' ' . Yii::t('global','LABEL_TO') . ' ' . $row['score_to'];
				break;
				default:
					$custom_field_name = $row['custom_field_name'];
					switch($row['id_custom_fields_option']){
						case -1:
							$custom_field_option_name = Yii::t('global','LABEL_YES');
						break;
						case -2:
							$custom_field_option_name = Yii::t('global','LABEL_NO');
						break;
						default:
							$custom_field_option_name = $row['custom_field_option_name'];
						break;
						
					}
				break;
				
			}
			
			
			
			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$custom_field_name.']]></cell>
			<cell type="ro"><![CDATA['.$custom_field_option_name.']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function actionEdit_scorm_certificate_conditions($container)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$model = new ProductScormConditionForm;
		
		$id = (int)$_POST['id'];
		$model->id_scorm_certificate_product = (int)$_POST['id_scorm_certificate_product'];
		
		if ($id) {
			if ($p = Tbl_ScormCertificateCondition::model()->findByPk($id)) {
				$model->id = $p->id;
				$model->id_scorm_certificate_product  = $p->id_scorm_certificate_product;
				$model->id_custom_fields  = $p->id_custom_fields;
				if($model->id_custom_fields==-1){
					if(!$p->score_from){
						$scp = Tbl_ScormCertificateProduct::model()->findByPk($model->id_scorm_certificate_product);
						$course_full_path = realpath(dirname(__FILE__).'/../../../').'/courses/scorm/'.$scp->id_product_downloadable_files.'/';		
			  
						// check manifesto
						if (!is_file($course_full_path.'imsmanifest.xml')) exit('<script type="text/javascript">alert("Error, manifest file not found!");</script>');
							
						$contents = file_get_contents($course_full_path.'imsmanifest.xml');
						
						// get mastery_score
						preg_match('/<adlcp:masteryscore>([0-9]+)<\/adlcp:masteryscore>/is',$contents,$matches);
						
						$score_from = sizeof($matches) ? ($matches[1] ? $matches[1]:0):0;
						$model->score_from = $score_from;
					}else{
						$model->score_from = $p->score_from;
					}
					
					$model->score_to = !$p->score_to?100:$p->score_to;
				}else{
					if ($cf = Tbl_CustomFields::model()->findByPk($model->id_custom_fields)) {
						$model->type=$cf->type;
					}
					$model->id_custom_fields_option = $p->id_custom_fields_option;
				}
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		} else {
			if ($p = Tbl_ScormCertificateProduct::model()->findByPk($model->id_scorm_certificate_product)) {
				if($p->id_product_downloadable_files){
					$course_full_path = realpath(dirname(__FILE__).'/../../../').'/courses/scorm/'.$p->id_product_downloadable_files.'/';		
		  
					// check manifesto
					if (!is_file($course_full_path.'imsmanifest.xml')) exit('<script type="text/javascript">alert("Error, manifest file not found!");</script>');
						
					$contents = file_get_contents($course_full_path.'imsmanifest.xml');
					
					// get mastery_score
					preg_match('/<adlcp:masteryscore>([0-9]+)<\/adlcp:masteryscore>/is',$contents,$matches);
					
					$model->score_from = sizeof($matches) ? ($matches[1] ? $matches[1]:0):0;
					$model->score_to = 100;	
				}
			}
		}
		
		$this->renderPartial('edit_scorm_certificate_conditions',array('model'=>$model, 'container'=>$container, 'id_scorm_certificate_product'=>$id_scorm_certificate_product));	
	}
	
	public function actionGet_custom_field_options($container)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$id_custom_fields=(int)$_POST['id_custom_fields'];
		$id=(int)$_POST['id'];
		if ($p = Tbl_CustomFields::model()->findByPk($id_custom_fields)) {
			$type=$p->type;
		}
		
		
		$output='<select id="'.$container.'_id_custom_fields_option" name="ProductScormConditionForm[id_custom_fields_option]">';		
		
		switch ($type) {
			// single checkbox
			case 0:
				$output .= '<option value="-1"'.($id==-1?' selected="selected"':'').'>'.Yii::t('global','LABEL_YES').'</option><option value="-2"'.($id==-2?' selected="selected"':'').'>'.Yii::t('global','LABEL_NO').'</option>';
			
				break;	
			default:
		
				$sql = 'SELECT 
				custom_fields_option.id,
				custom_fields_option_description.name
				FROM
				custom_fields_option
				INNER JOIN
				custom_fields_option_description
				ON
				(custom_fields_option.id = custom_fields_option_description.id_custom_fields_option AND custom_fields_option_description.language_code = :language_code) 
				
				INNER JOIN
				custom_fields
				ON
				(custom_fields_option.id_custom_fields = custom_fields.id)
				WHERE
				custom_fields_option.id_custom_fields = :id
				ORDER BY
				custom_fields_option.sort_order ASC';	
				
				$command=$connection->createCommand($sql);		
					
					
				foreach ($command->queryAll(true, array(':language_code'=>Yii::app()->language,':id'=>$id_custom_fields)) as $row) {
					$output .= '<option value="'.$row['id'].'"'.($id==$row['id']?' selected="selected"':'').'>'.$row['name'].'</option>';
				}						
				break;
		}		
	
		$output.='</select><br /><span id="'.$container.'_id_custom_fields_option_errorMsg" class="error"></span>';
		
		echo $output;
	}
	
	
	/**
	 * This is the action to save scorm certificate
	 */
	public function actionSave_condition_custom_field()
	{
		// collect user input data
		if(isset($_POST['ProductScormConditionForm']))
		{
			$model = new ProductScormConditionForm;
	
			// loop through each attribute and set it in our model
			foreach($_POST['ProductScormConditionForm'] as $name=>$value)
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
	
	/**
	 * This is the action to delete condition
	 */
	public function actionDelete_condition_custom_field()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			
			foreach ($ids as $id) {
				$criteria->params=array(':id'=>$id);
				// delete all
				Tbl_ScormCertificateCondition::model()->deleteAll($criteria);	
			}	
			
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function actionEdit_downloadable_files_options($container, $id_product)
	{
		$model = new ProductDownloadableFilesForm;
		
		$id = (int)$_POST['id'];
		$id_product = (int)$id_product;
		
		if (!$p = Tbl_Product::model()->findByPk($id_product)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}			
		
		$has_variants = $p->has_variants;			
		
		$model->id_product = $id_product;
		
		if ($id) {
			if ($p = Tbl_ProductDownloadableFiles::model()->findByPk($id)) {
				$model->id = $p->id;
				$model->id_product = $p->id_product;
				$model->id_product_variant = $p->id_product_variant;
				$model->name = $p->name;
				$model->filename = $p->filename;
				$model->no_days_expire = $p->no_days_expire;
				$model->no_downloads = $p->no_downloads;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}	
		
		$this->renderPartial('edit_downloadable_files_options',array('model'=>$model, 'container'=>$container, 'has_variants'=>$has_variants));	
	}			
	
	/**
	 * This is the action to save downloable video
	 */
	public function actionSave_downloadable_file()
	{
		// collect user input data
		if(isset($_POST['ProductDownloadableFilesForm']))
		{
			$model = new ProductDownloadableFilesForm;
	
			// loop through each attribute and set it in our model
			foreach($_POST['ProductDownloadableFilesForm'] as $name=>$value)
			{
				$model->$name=$value;
			}
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			
			$app = Yii::app();

			$output = array('id'=>$model->id,'type'=>$model->type,'config_certificate'=>$app->params['scorm_certificate']);
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);			
		}
	}
	
	
	
	
	
	/**
	 * This is the action to save files order
	 */
	public function actionSave_files_sort_order($id=0)
	{
		// current product
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_product_file) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND id=:id'; 
				$criteria->params=array(':id_product'=>$id,':id'=>$id_product_file); 					
				if ($ps = Tbl_ProductDownloadableFiles::model()->find($criteria)) {
					$ps->sort_order = $i;
					$ps->save();
					
					++$i;
				}
			}
		}
		
	}	
	
	
	
	
	
	/**
	 * This is the action to delete downloadable video
	 */
	public function actionDelete_downloadable_file($id_product)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$id_product = (int)$id_product;
		$ids = $_POST['ids'];
		$file_base_path = dirname(Yii::app()->getBasePath()).'/protected/downloadable_files/';
		
		if (is_array($ids) && sizeof($ids)) {
			// prepare delete options
			$command_delete_file=$connection->createCommand('DELETE FROM 
			product_downloadable_files,
			product_downloadable_files_description
			USING
			product_downloadable_files
			INNER JOIN
			product_downloadable_files_description
			ON
			(product_downloadable_files.id = product_downloadable_files_description.id_product_downloadable_files)
			WHERE 
			product_downloadable_files.id_product=:id_product
			AND
			product_downloadable_files.id=:id');								
			
			foreach ($ids as $id) {
				if (!$p = Tbl_ProductDownloadableFiles::model()->findByPk($id)) {
					throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
				}					
				
				if (is_file($file_base_path.$p->source)) @unlink($file_base_path.$p->source);
				
				$command_delete_file->execute(array(':id_product'=>$id_product,':id'=>$id));				
			}
		}
	}		 	
	
	/**
	 * This is the action to upload file
	 */
	public function actionUpload_downloadable_file($id)
	{					
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = $app->user->getId();	
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (!Tbl_Product::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		// if id is not set
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = dirname(Yii::app()->getBasePath()).'/protected/downloadable_files/';
			$targetFile = $_FILES['Filedata']['name'];
			$ext = strtolower(trim(pathinfo($targetFile, PATHINFO_EXTENSION)));
			$allowed_ext = array();									
			
			if (empty($ext)) {
				echo Yii::t('controllers/ProductsController','ERROR_NO_EXTENSION');				
				exit;
			} else {											
				if (!move_uploaded_file($tempFile,$targetPath.$targetFile)) {
					echo Yii::t('global','ERROR_SAVING');
					exit;					
				}
				
				echo 'file:'.$targetFile;
				exit;
				break;
			}
			
			echo Yii::t('controllers/ProductsController','ERROR_UPLOAD_FILE_FAILED');
		}			
	}		
	
	
	/**
	 * This is the action to download file
	 */
	public function actionPreview_course()
	{					
		$app = Yii::app();
		$id = (int)$_GET['id'];
		$filename = trim($_GET['filename']);
		$source = $filename;
		$targetPath = dirname(Yii::app()->getBasePath()).'/protected/downloadable_files/';
		
		if ($id) {
			if (!$p = Tbl_ProductDownloadableFiles::model()->findByPk($id)) throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			
			$filename = $p->filename;
			$source = $p->source;
		} 
		
		$course_full_path = $app->params['root_url'].'/courses/scorm/'.$id.'/';		
		$course_path = '/courses/scorm/'.$id.'/';
		
		// check manifesto
		if (!is_file($course_full_path.'imsmanifest.xml')) exit('<script type="text/javascript">alert("Error, manifest file not found!");</script>');
		
		$contents = file_get_contents($course_full_path.'imsmanifest.xml');
		
		// load xml
		$manifest = new SimpleXMLElement($contents);
		
		// get path to course
		if (!is_file($course_full_path.$manifest->resources[0]->resource[0]['href'])) exit('<script type="text/javascript">alert("Error, course not found!");</script>');
		
		$course_path .= $manifest->resources[0]->resource[0]['href'];
		
		header('Location: '.$course_path);
		exit;			
	}	
	
	/**
	 * This is the action to preview scorm file
	 */
	public function actionDownload_downloadable_file()
	{					
		$app = Yii::app();
		$id = (int)$_GET['id'];
		$filename = trim($_GET['filename']);
		$source = $filename;
		$targetPath = dirname(Yii::app()->getBasePath()).'/protected/downloadable_files/';
		
		if ($id) {
			if (!$p = Tbl_ProductDownloadableFiles::model()->findByPk($id)) throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			
			$filename = $p->filename;
			$source = $p->source;
		} 
		
		if (!$id && (empty($filename) || !is_file($targetPath.$filename))) {
			throw new CException(Yii::t('controllers/ProductsController','ERROR_INVALID_FILE'));
		} 
		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
		header("Pragma: public");  
		header("Content-Type: application/octet-stream; charset=utf-8");  
		
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		if ($filesize = @filesize($targetPath.$source)) header("Content-Length: ".$filesize);
		
		//setlocale(LC_ALL, 'en_US.UTF8');
		
		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'wb');
		
		if ($handle = @fopen($targetPath.$source, "rb")) {					
			while (($buffer = fgets($handle)) !== false) {
				fwrite($output,$buffer);
			}
			fclose($handle);
		}
		
		fclose($output);
		exit;			
	}			
	
	/************************************************************
	*															*
	*															*
	*					PRODUCT SIDE MENU						*
	*															*
	*															*
	************************************************************/
			

	/**
	 * This is the action to get an XML list of the product menu
	 */
	public function actionXml_list_product_section($id=0, $product_type=0)
	{
		$app = Yii::app();
		$id = (int)$id;
		$product_type = (int)$product_type;
		
		$disabled = '';
		
		if (!$id) { 
			$disabled = '<disabled><![CDATA[1]]></disabled>';
		} else if (!$p = Tbl_Product::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));			
		}
			
		//set content type and xml tag
		header("Content-type:text/xml");	
		
		echo '<data>';	
		
		switch ($product_type) {
			// product
			case 0:
				echo '
				<item id="edit_info">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_INFORMATION').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_INFORMATION_DESCRIPTION').']]></Description>
				</item>
				<item id="edit_categories">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_CATEGORIES').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_CATEGORIES_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>
				<item id="edit_images">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_IMAGES').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_IMAGES_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>'.(!$p->downloadable ? '
				<item id="edit_price_tiers">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_PRICE_TIERS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_PRICE_TIERS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>':'').'
				<item id="edit_inventory">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_INVENTORY').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_INVENTORY_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>';		
				
				if (!$p->downloadable && $app->params['enable_shipping']) {
				
					echo '<item id="edit_shipping">
						<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_SHIPPING').']]></Title>
						<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_SHIPPING_DESCRIPTION').']]></Description>
						'.$disabled.'
					</item>';
				
				}
				
				
				echo '<item id="edit_variants">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_VARIANTS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_VARIANTS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>'.($p->downloadable ? '
				<item id="edit_downloadable_videos_files">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_DOWNLOADABLE_VIDEOS_FILES').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_DOWNLOADABLE_VIDEOS_FILES_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>':'').(!$p->downloadable ? '
				<item id="edit_options">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_OPTIONS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_OPTIONS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>':'').'
				
							
				<item id="edit_suggestion">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_SUGGESTED_PRODUCTS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_SUGGESTED_PRODUCTS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>
				<item id="edit_related">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_RELATED_PRODUCTS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_RELATED_PRODUCTS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>';
				/*<item id="edit_tags">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_TAGS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_TAGS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>	*/		
				echo '<item id="edit_reviews">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_CUSTOMER_REVIEW').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_CUSTOMER_REVIEW_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>
				<item id="edit_combo_bundled">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_LISTS_COMBO_BUNDLED').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_LISTS_COMBO_BUNDLED_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>';			
				break;
			// combo deals
			case 1:
				echo '
				<item id="edit_info">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_INFORMATION').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_INFORMATION_DESCRIPTION_COMBO').']]></Description>
				</item>
				<item id="edit_categories">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_CATEGORIES').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_CATEGORIES_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>	
				<item id="edit_images">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_IMAGES').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_IMAGES_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>
				<item id="edit_combo_products">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_PRODUCTS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_PRODUCTS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>					
				<item id="edit_options">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_OPTIONS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_OPTIONS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>
						
				<item id="edit_suggestion">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_SUGGESTED_PRODUCTS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_SUGGESTED_PRODUCTS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>
				<item id="edit_related">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_RELATED_PRODUCTS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_RELATED_PRODUCTS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>';
				/*<item id="edit_tags">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_TAGS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_TAGS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>	*/		
				echo '<item id="edit_reviews">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_CUSTOMER_REVIEW').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_CUSTOMER_REVIEW_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>';			
				break;
			// bundled products
			case 2:
				echo '
				<item id="edit_info">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_INFORMATION').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_INFORMATION_DESCRIPTION_BUNDLED').']]></Description>
				</item>	
				<item id="edit_categories">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_CATEGORIES').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_CATEGORIES_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>					
				<item id="edit_images">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_IMAGES').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_IMAGES_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>
				<item id="edit_bundled_products">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_BUNDLED_PRODUCTS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_BUNDLED_PRODUCTS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>					
				<item id="edit_options">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_OPTIONS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_OPTIONS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>
							
				<item id="edit_suggestion">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_SUGGESTED_PRODUCTS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_SUGGESTED_PRODUCTS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>
				<item id="edit_related">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_RELATED_PRODUCTS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_RELATED_PRODUCTS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>';
				/*<item id="edit_tags">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_TAGS').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_TAGS_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>	*/		
				echo '<item id="edit_reviews">
					<Title><![CDATA['.Yii::t('controllers/ProductsController','LABEL_CUSTOMER_REVIEW').']]></Title>
					<Description><![CDATA['.Yii::t('controllers/ProductsController','LABEL_CUSTOMER_REVIEW_DESCRIPTION').']]></Description>
					'.$disabled.'
				</item>';
				break;
		}
		
		echo '

		</data>';
	}
	
	/**
	 * This action is to redirect to the right url for the product preview
	 */
	public function actionPreview($id,$language)
	{
		$id=(int)$id;	
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$sql='SELECT
		product_description.alias
		FROM
		product
		INNER JOIN
		product_description
		ON
		(product.id = product_description.id_product AND product_description.language_code = "'.$language.'")
		WHERE
		product.id = :id
		LIMIT 1';
		$command=$connection->createCommand($sql);
		if ($row = $command->queryRow(true,array(':id'=>$id))) {
			$this->redirect('/'.$language.'/product/'.$row['alias']);
		} else { echo Yii::t('global','ERROR_INVALID_PAGE'); }
		exit;
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