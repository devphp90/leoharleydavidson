<?php

class CustomersController extends Controller
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
		$params=array(':language_code'=>Yii::app()->language);
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'CONCAT_WS(" ",customer.firstname,customer.lastname,customer_address.company) LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// email
		if (isset($filters['email']) && !empty($filters['email'])) {
			$where[] = 'customer.email LIKE CONCAT("%",:email,"%")';
			$params[':email']=$filters['email'];
		}			
		
		// customer_type
		if (isset($filters['customer_type']) && !empty($filters['customer_type'])) {
			$where[] = 'customer_type.name LIKE CONCAT("%",:customer_type,"%")';
			$params[':customer_type']=$filters['customer_type'];
		}		
		
		// telephone
		if (isset($filters['telephone']) && !empty($filters['telephone'])) {
			$where[] = 'customer_address.telephone LIKE CONCAT("%",:telephone,"%")';
			$params[':telephone']=$filters['telephone'];
		}			
		
		// country
		if (isset($filters['country_name']) && !empty($filters['country_name'])) {
			$where[] = 'country_description.name LIKE CONCAT("%",:country_name,"%")';
			$params[':country_name']=$filters['country_name'];
		}		
		
		// state
		if (isset($filters['state_name']) && !empty($filters['state_name'])) {
			$where[] = 'state_description.name LIKE CONCAT("%",:state_name,"%")';
			$params[':state_name']=$filters['state_name'];
		}		
		
		// zip
		if (isset($filters['zip']) && !empty($filters['zip'])) {
			$where[] = 'customer_address.zip LIKE CONCAT("%",:zip,"%")';
			$params[':zip']=$filters['zip'];
		}							
		
		// date_creation start
		if (isset($filters['date_created_start']) && !empty($filters['date_created_start'])) {
			$where[] = 'customer.date_created >= :date_created_start';
			$params[':date_created_start']=$filters['date_created_start'];
		}	
		
		// date_creation end
		if (isset($filters['date_created_end']) && !empty($filters['date_created_end'])) {
			$where[] = 'customer.date_created <= :date_created_end';
			$params[':date_created_end']=$filters['date_created_end'];
		}						
		
		// status
		if (isset($filters['active'])) {
			switch ($filters['active']) {
				case 0:
				case 1:					
					$where[] = 'customer.active = :active';				
					$params[':active']=$filters['active'];
					break;
			}
		}								
		
		$sql = "SELECT 
		COUNT(customer.id) AS total 
		FROM 
		customer
		LEFT JOIN 
		customer_type
		ON
		(customer.id_customer_type = customer_type.id)
		LEFT JOIN 
		customer_address
		ON
		(customer.id = customer_address.id_customer AND customer_address.default_billing=1)		
		LEFT JOIN
		country_description 
		ON 
		(customer_address.country_code = country_description.country_code AND country_description.language_code = :language_code)	
		LEFT JOIN
		state_description
		ON 
		(customer_address.state_code = state_description.state_code AND state_description.language_code = :language_code)			
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
		customer.*,
		CONCAT_WS(' ',customer.firstname,customer.lastname) AS name,
		customer_address.telephone,
		customer_address.zip,
		customer_address.company,
		country_description.name AS country_name,
		state_description.name AS state_name,
		customer_type.name AS customer_type
		FROM 
		customer 
		LEFT JOIN 
		customer_type
		ON
		(customer.id_customer_type = customer_type.id)
		LEFT JOIN 
		customer_address
		ON
		(customer.id = customer_address.id_customer AND customer_address.default_billing=1)
		LEFT JOIN
		country_description 
		ON 
		(customer_address.country_code = country_description.country_code AND country_description.language_code = :language_code)	
		LEFT JOIN
		state_description
		ON 
		(customer_address.state_code = state_description.state_code AND state_description.language_code = :language_code)	
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY name ".$direct;
		// email
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY customer.email ".$direct;	
		// customer_type
		} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY customer_type.name ".$direct;			
		// telphone
		} else if (isset($sort_col[4]) && !empty($sort_col[4])) {
			$direct = $sort_col[4] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY customer_address.telephone ".$direct;	
		// country
		} else if (isset($sort_col[5]) && !empty($sort_col[5])) {
			$direct = $sort_col[5] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY country_name ".$direct;	
		// state
		} else if (isset($sort_col[6]) && !empty($sort_col[6])) {
			$direct = $sort_col[6] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY state_name ".$direct;			
		// zip
		} else if (isset($sort_col[7]) && !empty($sort_col[7])) {
			$direct = $sort_col[7] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY customer_address.zip ".$direct;	
		// date_created
		} else if (isset($sort_col[8]) && !empty($sort_col[8])) {
			$direct = $sort_col[8] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY customer.date_created ".$direct;																		
		} else {

			$sql.=" ORDER BY customer.id ASC";
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
			<cell type="ro"><![CDATA['.$row['name'].(!empty($row['company'])?' ('.$row['company'].')':'').']]></cell>
			<cell type="ro"><![CDATA['.$row['email'].']]></cell>
			<cell type="ro"><![CDATA['.$row['customer_type'].']]></cell>			
			<cell type="ro"><![CDATA['.$row['telephone'].']]></cell>
			<cell type="ro"><![CDATA['.$row['country_name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['state_name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['zip'].']]></cell>
			<cell type="ro"><![CDATA['.$row['date_created'].']]></cell>
			<cell type="ch">'.$row['active'].'</cell>
			</row>';
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
			
			if (!Tbl_Customer::model()->count($criteria)) {
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
		$id_customer = (int)$_POST['id_customer'];
		
		if ($id_customer) { 
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id_customer); 		
			
			if (!Tbl_Customer::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}			
		}
						
		$this->renderPartial($id,array('id'=>$id_customer,'container'=>$container,'containerJS'=>$containerJS));	
	}	
	
	public function actionEdit_info_options($container, $id=0)
	{
		$model = new CustomersForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($c = Tbl_Customer::model()->findByPk($id)) {
				$model->id = $c->id;
				$model->id_customer_type = $c->id_customer_type;
				$model->firstname = $c->firstname;
				$model->lastname = $c->lastname;
				$model->language_code = $c->language_code;
				$model->email = $c->email;
				$model->dob = $c->dob;
				$model->gender = $c->gender;
				$model->tax_number = $c->tax_number;
				//$model->password = $c->password;
				$model->active = $c->active;	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_info_options',array('model'=>$model, 'container'=>$container));	
	}		
	
	/**
	 * This is the action to save the information section	 
	 */
	public function actionSave_info()
	{
		$model = new CustomersForm;
		
		// collect user input data
		if(isset($_POST['CustomersForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['CustomersForm'] as $name=>$value)
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
	 * This is the action to delete a product
	 */
	public function actionDelete()
	{
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		// current category
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id) {
				
				$criteria=new CDbCriteria; 
				$criteria->condition='id_customer=:id_customer'; 
				$criteria->params=array(':id_customer'=>$id); 					
				
				// delete all
				Tbl_Customer::model()->deleteByPk($id);
				Tbl_CustomerAddress::model()->deleteAll($criteria);
				Tbl_CustomerPriceAlert::model()->deleteAll($criteria);
				
				// Delete wishlist
				$sql = 'SELECT
				id
				FROM
				customer_wishlist
				WHERE id_customer = "' .$id . '"';
				$command=$connection->createCommand($sql);
				
				foreach ($command->queryAll(true) as $row) {
					$criteria2=new CDbCriteria; 
					$criteria2->condition='id_customer_wishlist=:id_customer_wishlist'; 
					$criteria2->params=array(':id_customer_wishlist'=>$row['id']); 
					Tbl_CustomerWishlistProduct::model()->deleteAll($criteria2);	
				}
				
				Tbl_CustomerWishlist::model()->deleteAll($criteria);
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
		
		if ($p = Tbl_Customer::model()->findByPk($id)) {
			$p->active = $active;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}		
	
	public function actionEdit_addresses_options($container, $id_customer=0)
	{
		$model = new CustomerAddressesForm;
		
		$id_customer = (int)$id_customer;
		$id = (int)$_POST['id'];
		
		$model->id_customer = $id_customer;
		
		if ($id) {
			if ($c = Tbl_CustomerAddress::model()->findByPk($id)) {
				$model->id = $c->id;
				$model->id_customer = $c->id_customer;
				$model->firstname = $c->firstname;
				$model->lastname = $c->lastname;
				$model->company = $c->company;
				$model->address = $c->address;
				$model->city = $c->city;
				$model->country_code = $c->country_code;
				$model->state_code = $c->state_code;
				$model->zip = $c->zip;
				$model->lat = $c->lat;
				$model->lng = $c->lng;
				$model->telephone = $c->telephone;
				$model->fax = $c->fax;
				$model->default_billing = $c->default_billing;
				$model->default_shipping = $c->default_shipping;	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_addresses_options',array('model'=>$model, 'container'=>$container));	
	}		
	
	/**
	 * This is the action to get the list of province	
	 */
	public function actionGet_province_list()
	{
		$model=new CustomerAddressesForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>'--'));
	}		
	
	/**
	 * This is the action to save  
	 */
	public function actionSave_address()
	{
		$model = new CustomerAddressesForm;
		
		// collect user input data
		if(isset($_POST['CustomerAddressesForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['CustomerAddressesForm'] as $name=>$value)
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
	 * This is the action to delete a product
	 */
	public function actionDelete_address($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_customer_address) {				
				$criteria=new CDbCriteria; 
				$criteria->condition='id_customer=:id_customer AND id=:id'; 
				$criteria->params=array(':id_customer'=>$id,':id'=>$id_customer_address); 					
				
				// delete all
				Tbl_CustomerAddress::model()->deleteAll($criteria);						
			}
		}
	}	
	
	/**
	 * This is the action to delete a product
	 */
	public function actionTransfer_store_retailer_address($id=0)
	{
		$id_customer = (int)$id;
		$id_customer_address = $_POST['id'];
		
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_customer_address=:id_customer_address'; 
		$criteria->params=array(':id_customer_address'=>$id_customer_address); 		
		
		if (Tbl_StoreLocations::model()->count($criteria)) {
			$model = Tbl_StoreLocations::model()->find($criteria);	
		}else{
			$model = new Tbl_StoreLocations;
		}
		
		//Find Info of customer and address		
		if($model_customer = Tbl_Customer::model()->findByPk($id_customer)){
			$model->email = $model_customer->email;
		}
		
		if($model_customer_address = Tbl_CustomerAddress::model()->findByPk($id_customer_address)){
			if($model_customer_address->lat && $model_customer_address->lng){
				$model->name = (!empty($model_customer_address->company)?$model_customer_address->company:$model_customer_address->firstname . ' ' . $model_customer_address->lastname);
				$model->address = $model_customer_address->address;
				$model->city = $model_customer_address->city;
				$model->state_code = $model_customer_address->state_code;
				$model->zip = $model_customer_address->zip;
				$model->country_code = $model_customer_address->country_code;
				$model->lat = $model_customer_address->lat;
				$model->lng = $model_customer_address->lng;
				$model->telephone = $model_customer_address->telephone;
				$model->fax = $model_customer_address->fax;
				$model->id_customer_address = $id_customer_address;
				$model->active = 1;
			}else{
				echo false;	
				exit;
			}
		} else {
			echo false;
			exit;
		}
		
		

		if (!$model->save()) {		
			echo false;
			exit;	
		}else{
			echo true;	
		}
	}		
	
	/**
	 * This is the action to toggle default billing
	 */
	public function actionToggle_default_billing()
	{
		$id = (int)$_POST['id'];
		
		if ($p = Tbl_CustomerAddress::model()->findByPk($id)) {
			$p->default_billing = 1;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_customer=:id_customer AND id!=:id'; 
			$criteria->params=array(':id_customer'=>$p->id_customer,':id'=>$p->id); 
			
			Tbl_CustomerAddress::model()->updateAll(array('default_billing'=>0),$criteria);
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}		
	
	/**
	 * This is the action to toggle default shipping
	 */
	public function actionToggle_default_shipping()
	{
		$id = (int)$_POST['id'];
		
		if ($p = Tbl_CustomerAddress::model()->findByPk($id)) {
			$p->default_shipping = 1;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_customer=:id_customer AND id!=:id'; 
			$criteria->params=array(':id_customer'=>$p->id_customer,':id'=>$p->id); 
			
			Tbl_CustomerAddress::model()->updateAll(array('default_shipping'=>0),$criteria);
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}		
	
	/**
	 * This is the action to get an XML list of addresses
	 */
	public function actionXml_list_addresses($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id = (int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('customer_address.id_customer=:id_customer');
		$params=array(':id_customer'=>$id,':language_code'=>Yii::app()->language);
		
		// filters
		
		
		// fullname
		if (isset($filters['fullname']) && !empty($filters['fullname'])) {
			$where[] = 'CONCAT_WS(" ",customer_address.firstname,customer_address.lastname) LIKE CONCAT("%",:fullname,"%")';
			$params[':fullname']=$filters['fullname'];
		}		
		
		$sql = "SELECT 
		COUNT(customer_address.id) AS total 
		FROM 
		customer_address 
		LEFT JOIN
		country_description 
		ON 
		(customer_address.country_code = country_description.country_code AND country_description.language_code = :language_code)	
		LEFT JOIN
		state_description
		ON 
		(customer_address.state_code = state_description.state_code AND state_description.language_code = :language_code)			
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
		customer_address.*,
		CONCAT_WS(' ',customer_address.firstname,customer_address.lastname) AS fullname,
		country_description.name AS country_name,
		state_description.name AS state_name
		FROM 
		customer_address  
		LEFT JOIN
		country_description 
		ON 
		(customer_address.country_code = country_description.country_code AND country_description.language_code = :language_code)	
		LEFT JOIN
		state_description
		ON 
		(customer_address.state_code = state_description.state_code AND state_description.language_code = :language_code)		
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY customer_address.name ".$direct;
		// fullname
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY fullname ".$direct;
		} else {

			$sql.=" ORDER BY customer_address.id ASC";
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
			<cell type="ro"><![CDATA['.$row['fullname'].']]></cell>
			<cell type="ro"><![CDATA['.
			($row['telephone']?'<strong>Telephone:</strong> '.$row['telephone']:'').
			($row['fax']?"\r\n".'<br/><strong>Fax:</strong> '.$row['fax']:'').']]></cell>
			<cell type="ro"><![CDATA['.
			($row['address']?$row['address']."\r\n".'<br/>':'').
			($row['city']?$row['city']:'').
			($row['state_name']?' '.$row['state_name']:'').
			($row['country_name']?' '.$row['country_name']:'').
			($row['zip']?"\r\n".'<br />'.$row['zip']:'').']]></cell>
			<cell type="ra"><![CDATA['.$row['default_billing'].']]></cell>
			<cell type="ra"><![CDATA['.$row['default_shipping'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	
	
	
	/**
	 * This is the action to delete a product review
	 */
	public function actionDelete_review()
	{
		// current product
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_review) {
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
		
		$where=array('product_review.id_customer=:id_customer');
		$params=array(':id_customer'=>$id);
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'product_description.name LIKE CONCAT("%",:name,"%")';
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
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
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
		product_review.date_created,
		(CONCAT(customer.firstname, ' ', customer.lastname)) AS customer_name,
		product_description.name 
		FROM 
		product_review 
		INNER JOIN 
		product 
		ON 
		(product_review.id_product = product.id) 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
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
			
			$sql.=" ORDER BY product_description.name ".$direct;
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
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA[<strong>'.$row['title'].'</strong><br /><br />'.nl2br($row['review']).']]></cell>
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
	
	public function actionEdit_custom_fields_form_0_options($container, $id=0)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$model = new CustomersCustomFieldsForm;
		
		$id = (int)$id;
		
		$sql = 'SELECT 
		custom_fields.id,
		custom_fields.type,
		custom_fields.required,
		custom_fields_description.name
		FROM 
		custom_fields 
		INNER JOIN
		custom_fields_description
		ON
		(custom_fields.id = custom_fields_description.id_custom_fields AND custom_fields_description.language_code = :language_code)
		WHERE
		custom_fields.form = 0
		ORDER BY 
		custom_fields.sort_order ASC';
		$command = $connection->createCommand($sql);
		
		$sql = 'SELECT 
		custom_fields_option.id,
		custom_fields_option.add_extra,
		custom_fields_option.extra_required,
		custom_fields_option_description.name
		FROM
		custom_fields_option
		INNER JOIN 
		custom_fields_option_description
		ON
		(custom_fields_option.id = custom_fields_option_description.id_custom_fields_option AND custom_fields_option_description.language_code = :language_code)
		WHERE
		custom_fields_option.id_custom_fields = :id_custom_fields
		ORDER BY
		custom_fields_option.sort_order ASC';
		$command_option = $connection->createCommand($sql);
		
		$custom_fields=array();
		foreach ($command->queryAll(true, array(':language_code'=>Yii::app()->language)) as $row) {
			$custom_fields[$row['id']] = array(
				'id' => $row['id'],
				'type' => $row['type'],
				'required' => $row['required'],
				'name' => $row['name'],
			);
			
			foreach ($command_option->queryAll(true, array(':language_code'=>Yii::app()->language,':id_custom_fields'=>$row['id'])) as $row_option) {
				$custom_fields[$row['id']]['options'][$row_option['id']] = array(
					'id' => $row_option['id'],
					'add_extra' => $row_option['add_extra'],
					'extra_required' => $row_option['extra_required'],
					'name' => $row_option['name'],
				);		
				
				// dropdown
				if (($row['type'] == 2 or $row['type'] == 5) && $row_option['add_extra']) {
					$custom_fields[$row['id']]['add_extra'] = 1;
					$custom_fields[$row['id']]['add_extra_options'][$row_option['id']] = array('class'=>'add_extra');
				}
			}
		}		
		
		// load 
		if ($id) {			
			foreach (Tbl_CustomerCustomFieldsValue::model()->findAll('id_customer=:id_customer',array(':id_customer'=>$id)) as $row) {
				switch ($custom_fields[$row['id_custom_fields']]['type']) {
					default:
						$model->custom_fields[$row['id_custom_fields']]['value'] = $row['value'];
						break;
					// single checkbox
					case 0:
						$model->custom_fields[$row['id_custom_fields']]['value'] = $row['id_custom_fields'];
						break;
					// multiple checkbox
					case 1:
						$model->custom_fields[$row['id_custom_fields']]['options'][$row['id_custom_fields_option']]['value'] = $row['id_custom_fields_option'];
						
						if ($custom_fields[$row['id_custom_fields']]['options'][$row['id_custom_fields_option']]['add_extra']) {
							$model->custom_fields[$row['id_custom_fields']]['options'][$row['id_custom_fields_option']]['extra'] = $row['value'];
						}
						break;
					// dropdown
					case 2:
						$model->custom_fields[$row['id_custom_fields']]['value'] = $row['id_custom_fields_option'];			
						
						if ($custom_fields[$row['id_custom_fields']]['options'][$row['id_custom_fields_option']]['add_extra']) {
							$model->custom_fields[$row['id_custom_fields']]['extra'] = $row['value'];			
						}
						break;
					// radio
					case 5:
						$model->custom_fields[$row['id_custom_fields']]['value'] = $row['id_custom_fields_option'];			
						
						if ($custom_fields[$row['id_custom_fields']]['options'][$row['id_custom_fields_option']]['add_extra']) {
							$model->custom_fields[$row['id_custom_fields']]['options'][$row['id_custom_fields_option']]['extra'] = $row['value'];				
						}
						break;
				}
			}
		}			
				
		$this->renderPartial('edit_custom_fields_form_0_options',array('model'=>$model, 'container'=>$container, 'custom_fields' => $custom_fields));	
	}				
	
	/**
	 * This is the action to save the information section	 
	 */
	public function actionSave_custom_fields_form_0($id)
	{
		$model = new CustomersCustomFieldsForm;
		
		// collect user input data
		if(isset($_POST['CustomersCustomFieldsForm']))
		{			
			$model->id_customer = $id;
		
			// loop through each attribute and set it in our model
			foreach($_POST['CustomersCustomFieldsForm'] as $name=>$value)
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
		
		$disabled = '';
		
		if (!$id) { 
			$disabled = '<disabled><![CDATA[1]]></disabled>';
		}
			
		//set content type and xml tag
		header("Content-type:text/xml");		
		
		echo '<data>';
			/*<item id="edit_view">
				<Title><![CDATA['.Yii::t('controllers/CustomersController','LABEL_VIEW').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/CustomersController','LABEL_VIEW_DESCRIPTION').']]></Description>
				'.$disabled.'
			</item>	*/		
		echo '<item id="edit_info">
				<Title><![CDATA['.Yii::t('controllers/CustomersController','LABEL_INFORMATION').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/CustomersController','LABEL_INFORMATION_DESCRIPTION').']]></Description>
			</item>
			<item id="edit_addresses">
				<Title><![CDATA['.Yii::t('controllers/CustomersController','LABEL_ADDRESSES').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/CustomersController','LABEL_ADDRESSES_DESCRIPTION').']]></Description>
				'.$disabled.'
			</item>
			<item id="edit_orders">
				<Title><![CDATA['.Yii::t('controllers/CustomersController','LABEL_ORDERS').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/CustomersController','LABEL_ORDERS_DESCRIPTION').']]></Description>
				'.$disabled.'
			</item>';
			/*<item id="edit_wishlist">
				<Title><![CDATA['.Yii::t('controllers/CustomersController','LABEL_WISHLIST').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/CustomersController','LABEL_WISHLIST_DESCRIPTION').']]></Description>
				'.$disabled.'
			</item>	*/	
		echo '<item id="edit_reviews">
				<Title><![CDATA['.Yii::t('controllers/CustomersController','LABEL_PRODUCTS_REVIEWS').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/CustomersController','LABEL_PRODUCTS_REVIEWS_DESCRIPTION').']]></Description>
				'.$disabled.'
			</item>';
			
		if (Tbl_CustomFields::model()->count('form=0')) {
			echo '		
			<item id="edit_custom_fields_form_0">
				<Title><![CDATA['.Yii::t('controllers/CustomersController','LABEL_CUSTOM_FORM_FIELDS_0').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/CustomersController','LABEL_CUSTOM_FORM_FIELDS_0_DESCRIPTION').']]></Description>
				'.$disabled.'
			</item>';	
		}
			
		echo '						
		</data>';
	}	
	
	/**
	 * This is the action to get an XML list of orders
	 */
	public function actionXml_list_orders($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		
		$id=(int)$id;

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('orders.id_customer=:id');
		$params=array(':id'=>$id,':language_code'=>Yii::app()->language);
		
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
		
		// status
		if (isset($filters['status'])) {
			if (is_numeric($filters['status'])) {
				$where[] = 'orders.status = :status';				
				$params[':status']=$filters['status'];
			}
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
		country_ship_to.name AS shipping_country,
		state_ship_to.name AS shipping_state,
		orders.shipping_zip,
		orders.shipping_telephone,			
		orders.grand_total,
		orders.status,
		orders.priority
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
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders.id ".$direct;
		// date_order
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders.date_order ".$direct;	
		// total
		} else if (isset($sort_col[5]) && !empty($sort_col[5])) {
			$direct = $sort_col[5] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders.grand_total ".$direct;				
		// priority
		} else if (isset($sort_col[6]) && !empty($sort_col[6])) {
			$direct = $sort_col[6] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders.priority ".$direct;																			
		// status
		} else if (isset($sort_col[7]) && !empty($sort_col[7])) {
			$direct = $sort_col[7] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY orders.status ".$direct;																						
		} else {
			$sql.=" ORDER BY orders.id DESC";
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
			<cell type="ro"><![CDATA['.$row['id'].']]></cell>
			<cell type="ro"><![CDATA['.$row['date_order'].']]></cell>
			<cell type="ro"><![CDATA['.$row['billing_firstname'].' '.$row['billing_lastname'].'<br />'."\r\n".
			($row['billing_company'] ? $row['billing_company'].'<br />'."\r\n":'').
			$row['billing_address'].'<br />'."\r\n".
			$row['billing_city'].($row['billing_state'] ? ' '.$row['billing_state']:'').($row['billing_zip'] ? ' '.$row['billing_zip']:'').'<br />'."\r\n".
			$row['billing_country'].'<br />'."\r\n".
			($row['billing_telephone']? "\r\n".'<br /><strong>Telephone:</strong> '.$row['billing_telephone']:'').']]></cell>			
			<cell type="ro"><![CDATA['.$row['shipping_firstname'].' '.$row['shipping_lastname'].'<br />'."\r\n".
			($row['shipping_company'] ? $row['shipping_company'].'<br />'."\r\n":'').
			$row['shipping_address'].'<br />'."\r\n".
			$row['shipping_city'].($row['shipping_state'] ? ' '.$row['shipping_state']:'').($row['shipping_zip'] ? ' '.$row['shipping_zip']:'').'<br />'."\r\n".
			$row['shipping_country'].'<br />'."\r\n".
			($row['shipping_telephone']? "\r\n".'<br /><strong>Telephone:</strong> '.$row['shipping_telephone']:'').']]></cell>		
			<cell type="ro"><![CDATA['.$row['grand_total'].']]></cell>

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
	
	public function actionLogin($id)
	{
		$id=(int)$id;	
		
		if (!$o = Tbl_Customer::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		// customer type
		if ($o_t = Tbl_CustomerType::model()->findByPk($o->id_customer_type)) {			
			$apply_on_rebate = $o_t->apply_on_rebate;
		}
		
		unset($_SESSION['customer']['id'],
		$_SESSION['customer']['name'],
		$_SESSION['customer']['email'],
		$_SESSION['customer']['dob'],
		$_SESSION['customer']['id_customer_type'],
		$_SESSION['customer']['apply_on_rebate'],
		$_SESSION['customer']['according_agreement']);
		
		$_SESSION['customer']['id'] = $o->id;
		$_SESSION['customer']['name'] = $o->firstname.' '.$o->lastname;
		$_SESSION['customer']['email'] = $o->email;
		$_SESSION['customer']['dob'] = $o->dob;
		$_SESSION['customer']['id_customer_type'] = $o->id_customer_type;
		$_SESSION['customer']['apply_on_rebate'] = $apply_on_rebate;	
		
		header('Location: '.Yii::app()->params['root_relative_url'].'account/');
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