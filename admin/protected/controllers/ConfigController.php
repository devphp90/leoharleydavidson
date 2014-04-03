<?php

class ConfigController extends Controller
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
	 * This is the action to edit each section
	 */ 
	public function actionEdit()	
	{						
		$id = trim($_POST['id']);
						
		$this->renderPartial($id,array('model'=>$model));	
	}
	
	public function actionEdit_general_options()
	{
		$app = Yii::app();
		
		$model=new ConfigForm;	
		// load database configuration
		foreach (Tbl_Config::model()->findAll() as $row) {
			$model->settings[$row->name] = $row->value;	
		}					
		
		$this->renderPartial('edit_general_options',array('model'=>$model));	
	}				
	
	public function actionEdit_category_filters_options()
	{
		$app = Yii::app();
		
		$model=new ConfigForm;	
		// load database configuration
		foreach (Tbl_Config::model()->findAll() as $row) {
			$model->settings[$row->name] = $row->value;	
		}				
		
		$this->renderPartial('edit_category_filters_options',array('model'=>$model));	
	}
	
	public function actionEdit_company_info_options()
	{
		$app = Yii::app();
		
		$model=new ConfigForm;	
		// load database configuration
		foreach (Tbl_Config::model()->findAll() as $row) {
			$model->settings[$row->name] = $row->value;	
		}					
		
		$this->renderPartial('edit_company_info_options',array('model'=>$model));	
	}	
	
	public function actionEdit_social_network_options()
	{
		$app = Yii::app();
		
		$model=new ConfigForm;	
		// load database configuration
		foreach (Tbl_Config::model()->findAll() as $row) {
			$model->settings[$row->name] = $row->value;	
		}					
		
		$this->renderPartial('edit_social_network_options',array('model'=>$model));	
	}
	
	public function actionEdit_payment_options()
	{
		
		$model=new ConfigPaymentForm;
		
		$model->enable_payment = Yii::app()->params['enable_payment'];
		
		$criteria=new CDbCriteria; 
		$criteria->condition='active=:active'; 
		$criteria->params=array(':active'=>'1');
		
		// load database configuration
		if($payment_gateway = Tbl_PaymentGateway::model()->find($criteria)){			
			$model->payment_gateway = array(
				'id' => $payment_gateway->id,
				'merchant_id' => $payment_gateway->merchant_id,
				'user_id' => $payment_gateway->user_id,
				'pin' => $payment_gateway->pin,		
				'credit_cards' =>  CHtml::listData(Tbl_ConfigCreditCard::model()->findAll($criteria),'id','active'),	
			);
		}
		
				
		$model->paypal['enable_paypal'] = Yii::app()->params['enable_paypal'];
		$model->paypal['paypal_api_username'] = Yii::app()->params['paypal_api_username'];
		$model->paypal['paypal_api_password'] = Yii::app()->params['paypal_api_password'];
		$model->paypal['paypal_api_signature'] = Yii::app()->params['paypal_api_signature'];
		$model->enable_auto_completed_order = Yii::app()->params['enable_auto_completed_order'];		
		$model->enable_cash_payments = Yii::app()->params['enable_cash_payments'];
		$model->enable_check_payments = Yii::app()->params['enable_check_payments'];
		$model->check_payment_description = Yii::app()->params['check_payment_description'];
		
		$this->renderPartial('edit_payment_options',array('model'=>$model));	
	}
	
	/**
	 * This is the action to save 
	 */
	public function actionSave_payment()
	{
		$model = new ConfigPaymentForm;
		$output = array();
		// collect user input data
		if(isset($_POST['ConfigPaymentForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ConfigPaymentForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
		}
		
		
		echo CJSON::encode($output);	
	}		
	
	/**
	 * This is the action to save 
	 */
	public function actionSave()
	{
		$model = new ConfigForm;
		$model_shipping_gateway = new ConfigShippingGatewayForm;

		// collect user input data
		if(isset($_POST['ConfigForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ConfigForm'] as $name=>$value)
			{
				$mystring = $name;
				$findme   = 'shipping_gateway_';
				$pos = strpos($mystring, $findme);
				
				if($pos !== false){
					// Verify if Shipping is enabled
					if($_POST['ConfigForm']['settings']['enable_shipping']){
						$shipping_page = 1;
						$name = str_replace($findme,'',$name);
						$model_shipping_gateway->$name=$value;
					}
				}else{
					$model->$name=$value;
				}
			}			
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			// validate Shipping Gateway
			if($shipping_page and $model_shipping_gateway->validate()) {
				$model_shipping_gateway->save();
			}
			
			$output = array();
			$errors = $model->getErrors();
			
			if($shipping_page) {
				if(sizeof($model_shipping_gateway->getErrors())){
					foreach($model_shipping_gateway->getErrors() as $key=>$value){
						$errors[$key]=$value;
					}
				}
			}
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);	
		}
	}
	
	public function actionGet_province_list_company()
	{
		$model=new ConfigForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		
		echo Html::generateStateList($id_prefix.'[settings][company_state_code]', $country, '', '', array('style'=>'min-width:80px;','id'=>'settings[company_state_code]'));
	}
	
	public function actionGet_province_list_shipping_sender()
	{
		$model=new ConfigForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		
		echo Html::generateStateList($id_prefix.'[settings][shipping_sender_state_code]', $country, '', '', array('style'=>'min-width:80px;','id'=>'settings[shipping_sender_state_code]'));
	}
	
	
	/************************************************************
	*															*
	*															*
	*					SHIPPING DATAVIEW						*
	*															*
	*															*
	************************************************************/
	
	public function actionEdit_shipping_options()
	{
		$app = Yii::app();
		
		$model=new ConfigForm;	
		// load database configuration
		foreach (Tbl_Config::model()->findAll() as $row) {
			$model->settings[$row->name] = $row->value;	
		}
		
		// Shipping Gateway
		$model_shipping_gateway=new ConfigShippingGatewayForm;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='active=:active'; 
		$criteria->params=array(':active'=>'1');
		
		// load database configuration
		$shipping_gateway = Tbl_ShippingGateway::model()->find($criteria);
		
		$model_shipping_gateway->id = $shipping_gateway->id;	
		$model_shipping_gateway->access_key = $shipping_gateway->access_key;
		$model_shipping_gateway->meter_number = $shipping_gateway->meter_number;
		$model_shipping_gateway->merchant_id = $shipping_gateway->merchant_id;
		$model_shipping_gateway->merchant_password = $shipping_gateway->merchant_password;
						
		
		$this->renderPartial('edit_shipping_options',array('model'=>$model,'model_shipping_gateway'=>$model_shipping_gateway));	
	}
	
	//Ship only into this region
	public function actionXml_list_ship_only_region($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':language_code'=>Yii::app()->language);
									
		
		$sql = "SELECT 
		COUNT(config_ship_only_region.id) AS total 
		FROM 
		config_ship_only_region 
		LEFT JOIN 
		country_description
		ON
		(config_ship_only_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(config_ship_only_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
		config_ship_only_region.id,
		country_description.name AS country,
		state_description.name AS state		
		FROM 
		config_ship_only_region 
		LEFT JOIN 
		country_description
		ON
		(config_ship_only_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(config_ship_only_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
			foreach ($ids as $id_config_ship_only_region) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_config_ship_only_region);					
				
				// delete all
				Tbl_ConfigShipOnlyRegion::model()->deleteAll($criteria);						
			}
		}
	}
	
	public function actionEdit_ship_only_region($id=0)
	{
		$model = new ShipOnlyIntoThisRegionsForm;
		
		$id = (int)$_POST["id"];		
		if ($id) {
			if ($ship_only_region = Tbl_ConfigShipOnlyRegion::model()->findByPk($id)) {
				$model->id = $ship_only_region->id;
				$model->country_code = $ship_only_region->country_code;
				$model->state_code = $ship_only_region->state_code;	
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_ship_only_region',array('model'=>$model));		
	}
	
	public function actionSave_ship_only_region()
	{
		$model = new ShipOnlyIntoThisRegionsForm;
		
		// collect user input data
		if(isset($_POST['ShipOnlyIntoThisRegionsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ShipOnlyIntoThisRegionsForm'] as $name=>$value)
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
		$model=new ShipOnlyIntoThisRegionsForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>'_state_code'));
	}
	//End Ship only into this region
	
	//Do not Ship into this region
	public function actionXml_list_do_not_ship_region($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':language_code'=>Yii::app()->language);
									
		
		$sql = "SELECT 
		COUNT(config_do_not_ship_region.id) AS total 
		FROM 
		config_do_not_ship_region 
		LEFT JOIN 
		country_description
		ON
		(config_do_not_ship_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(config_do_not_ship_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
		config_do_not_ship_region.id,
		country_description.name AS country,
		state_description.name AS state		
		FROM 
		config_do_not_ship_region 
		LEFT JOIN 
		country_description
		ON
		(config_do_not_ship_region.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(config_do_not_ship_region.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
			foreach ($ids as $id_config_do_not_ship_region) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_config_do_not_ship_region);					
				
				// delete all
				Tbl_ConfigDoNotShipRegion::model()->deleteAll($criteria);						
			}
		}
	}
	
	public function actionEdit_do_not_ship_region($id=0)
	{
		$model = new DoNotShipIntoThisRegionsForm;
		
		$id = (int)$_POST["id"];		
		if ($id) {
			if ($ship_only_region = Tbl_ConfigDoNotShipRegion::model()->findByPk($id)) {
				$model->id = $ship_only_region->id;
				$model->country_code = $ship_only_region->country_code;
				$model->state_code = $ship_only_region->state_code;	
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_do_not_ship_region',array('model'=>$model));		
	}
	
	public function actionSave_do_not_ship_region()
	{
		$model = new DoNotShipIntoThisRegionsForm;
		
		// collect user input data
		if(isset($_POST['DoNotShipIntoThisRegionsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['DoNotShipIntoThisRegionsForm'] as $name=>$value)
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
		$model=new DoNotShipIntoThisRegionsForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>'_state_code'));
	}
	
	//End Do not Ship into this region
	
	
	// Free Shipping Regions
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
		
			
			if (!Tbl_ConfigFreeShippingRegion::model()->count()) {
				Tbl_Config::model()->updateAll(array('value'=>0),"name='enable_free_shipping'");
			}
		}
	}
	
	public function actionEdit_regions_options($id=0)
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
		
		$this->renderPartial('edit_regions_options',array('model'=>$model));		
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
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>'_state_code'));
	}
	//End Free Shipping Regions
	
	
	//----------------------------ADDRESS PICKUP
	
	public function actionXml_list_address_pickup($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':language_code'=>Yii::app()->language);
									
		
		$sql = "SELECT 
		COUNT(config_address_pickup.id) AS total 
		FROM 
		config_address_pickup
		LEFT JOIN 
		country_description
		ON
		(config_address_pickup.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(config_address_pickup.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
		config_address_pickup.id,
		config_address_pickup.address,
		config_address_pickup.city,
		config_address_pickup.zip,
		country_description.name AS country,
		state_description.name AS state		
		FROM 
		config_address_pickup 
		LEFT JOIN 
		country_description
		ON
		(config_address_pickup.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(config_address_pickup.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
			<cell type="ro"><![CDATA['.($row['address']).']]></cell>
			<cell type="ro"><![CDATA['.($row['city']).']]></cell>				
			<cell type="ro"><![CDATA['.($row['country'] ? $row['country']:Yii::t('global','LABEL_PROMPT')).']]></cell>
			<cell type="ro"><![CDATA['.($row['state'] ? $row['state']:Yii::t('global','LABEL_PROMPT')).']]></cell>
			<cell type="ro"><![CDATA['.($row['zip']).']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	public function actionDelete_address_pickup()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_config_address_pickup) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_config_address_pickup);					
				
				// delete all
				Tbl_ConfigAddressPickup::model()->deleteAll($criteria);						
			}
		}
	}
	
	public function actionEdit_address_pickup($id=0)
	{
		$model = new AddressPickupForm;
		
		$id = (int)$_POST["id"];		
		if ($id) {
			if ($address_pickup = Tbl_ConfigAddressPickup::model()->findByPk($id)) {
				$model->id = $address_pickup->id;
				$model->country_code = $address_pickup->country_code;
				$model->state_code = $address_pickup->state_code;	
				$model->address = $address_pickup->address;
				$model->city = $address_pickup->city;	
				$model->zip = $address_pickup->zip;	
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_address_pickup',array('model'=>$model));		
	}
	
	public function actionSave_address_pickup()
	{
		$model = new AddressPickupForm;
		
		// collect user input data
		if(isset($_POST['AddressPickupForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['AddressPickupForm'] as $name=>$value)
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
	
	public function actionGet_province_list_address_pickup()
	{
		$model=new AddressPickupForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>'_state_code'));
	}
	//End ADDRESS PICKUP
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//*****************************END SHIPPING DATAVIEW
	
	
	
	/************************************************************
	*															*
	*															*
	*						UPLOAD LOGO							*
	*															*
	*															*
	************************************************************/
	
	public function actionEdit_images_upload()
	{	
		$id_upload_button = $_POST['id_upload_button'];
	
		$this->renderPartial('edit_images_upload',array());	
	}
	
	public function actionUpload_image()
	{					

		$app = Yii::app();

		if (!empty($_FILES)) {
			
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $app->params['root_url'].'_images/';
			$targetFile = $_FILES['Filedata']['name'];
			$ext = strtolower(trim(pathinfo($targetFile, PATHINFO_EXTENSION)));
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
				
				$image = new SimpleImage();
				if (!$image->load($tempFile)) {
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;
				}
				
				$image->resizeToHeight($app->params['company_logo_max_height']);
				
				// save image logo
				if (!$image->save($targetPath.'logo.'.$ext)) {							
					echo Yii::t('global', 'ERROR_SAVE_COVER_FAILED1');					
					exit;									
				}
				
				// save image print logo
				if (!$image->grayScale($targetPath.'logo.'.$ext,$targetPath.'logo_print.'.$ext)) {							
					echo Yii::t('global', 'ERROR_SAVE_COVER_FAILED1');					
					exit;									
				}
				
				// save image paypal logo
				$image->resizeToHeight($app->params['company_logo_paypal_max_height']);

				if (!$image->save($targetPath.'logo_paypal.'.$ext)) {							
					echo Yii::t('global', 'ERROR_SAVE_COVER_FAILED1');					
					exit;									
				}

				// free up memory
				$image->destroy();
				
				Tbl_Config::model()->updateAll(array('value'=>'logo.'.$ext),"name='company_logo_file'");
				Tbl_Config::model()->updateAll(array('value'=>'logo_print.'.$ext),"name='company_logo_print_file'");
				Tbl_Config::model()->updateAll(array('value'=>'logo_paypal.'.$ext),"name='company_logo_paypal_file'");

				echo 'true';
				exit;
				break;
			}
			echo Yii::t('global','ERROR_UPLOAD_IMAGE_FAILED');
		}else{
			echo 'non';	
		}
		
		
	}
	
	
	
	
	/**
	 * This is the action to get an XML list of the product images
	 */
	public function actionXml_list_product_images()
	{

		//set content type and xml tag
		header("Content-type:text/xml");		
		
		echo '<data>
		<item id="'.$row->id.'">
			<filename><![CDATA['.Yii::app()->params['company_logo_file'].'?'.time().']]></filename>
			<print>0</print>
		</item>c
		<item id="'.$row->id.'">
			<filename><![CDATA['.Yii::app()->params['company_logo_print_file'].'?'.time().']]></filename>
			<print>1</print>
		</item>
		</data>';
	}
		
	
	//*****************
	
	
	
	
	/************************************************************
	*															*
	*															*
	*						BANNERS								*
	*															*
	*															*
	************************************************************/
	
	public function actionEdit_banner_info()
	{
						
		
		$this->renderPartial('edit_banner_info');	
	}	
	
	
	
	/**
	 * This is the action to delete a banner
	 */
	public function actionDelete_banner()
	{
		// current product
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_banner) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_banner=:id'; 
				$criteria->params=array(':id'=>$id_banner);										
				$image_base_path = Yii::app()->params['root_url'].'_images/banner/';
				
				foreach (Tbl_BannerDescription::model()->findAll($criteria) as $row) {
					if ($row->filename && is_file($image_base_path.$row->filename)) @unlink($image_base_path.$row->filename);
					$row->delete();
				}
				
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_banner);					
				
				// delete all
				Tbl_Banner::model()->deleteAll($criteria);			
			}
		}		
	}
		
	public function actionEdit_banner_options($id=0)
	{
		$model = new BannerForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($banner = Tbl_Banner::model()->findByPk($id)) {
				$model->id = $banner->id;
				$model->name = $banner->name;
				$model->display_start_date = ($banner->display_start_date != '0000-00-00 00:00:00') ? $banner->display_start_date:'';
				$model->display_end_date = ($banner->display_end_date != '0000-00-00 00:00:00') ? $banner->display_end_date:'';
				$model->active = $banner->active;
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_banner_options',array('model'=>$model));	
	}
	
	/**
	 *	This is a function we will use to get the cmspages 
	 */	
	public function get_cmspages(&$array=array(),$id_parent=0,&$ind=0,$lang='en')
	{
		$id_parent = (int)$id_parent;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent'; 
		$criteria->params=array(':id_parent'=>$id_parent); 				
		$criteria->order='sort_order ASC';	
		
		$eol = "\r\n";
			
		foreach (Tbl_Cmspage::model()->findAll($criteria) as $row) {		
			$criteria2=new CDbCriteria; 
			$criteria2->condition='id_parent=:id_parent'; 
			$criteria2->params=array(':id_parent'=>$row->id); 				
			$criteria2->order='sort_order ASC';			
			
			$child = Tbl_Cmspage::model()->count($criteria2) ? 1:0;	
			
			$criteria3=new CDbCriteria; 
			$criteria3->condition='id_cmspage=:id_cmspage AND language_code=:language_code'; 
			$criteria3->params=array(':id_cmspage'=>$row->id,':language_code'=>$lang); 								
					
			//if(!$row->home_page && !$row->header_only){
				$array[$row->id] = CHtml::encode(($ind ? str_repeat('-',$ind).' ':'').Tbl_CmspageDescription::model()->find($criteria3)->name);
			//}
			
			if ($child) { $ind += 2; $this->get_cmspages($array,$row->id,$ind,$lang); }
		}	
		
		$ind=0;	
				
	}		
    
	public function actionXml_list_banner_description($id=0)
	{
		$model = new BannerForm;
		$model_name = get_class($model);
		$current_datetime = date('Y-m-d H:i:s');
		
		$id = (int)$id;
		
		$model->id = $id;
		
		if ($id) {
			if ($banner = Tbl_Banner::model()->findByPk($id)) {							
				// grab description information 
				foreach ($banner->tbl_banner_description as $row) {
					$model->banner_description[$row->language_code]['url_type'] = $row->url_type;
					$model->banner_description[$row->language_code]['url'] = $row->url;
					$model->banner_description[$row->language_code]['target_blank'] = $row->target_blank;
					$model->banner_description[$row->language_code]['filename'] = $row->filename;
					$model->banner_description[$row->language_code]['id_cmspage'] = $row->id_cmspage;
					$model->banner_description[$row->language_code]['id_subscription_contest'] = $row->id_subscription_contest;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_BannerDescription::tableName());		
		$app = Yii::app();		
		$include_path = $app->params['includes_js_path'];	
		
		$help_hint_path = '/settings/general/banners/';
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';

		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			$ind=0;
			$cmspages=array();
			$this->get_cmspages($cmspages,0,$ind,$value->code);
						
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:auto;" class="div_">	
					<div style="padding:10px;">			
					
					<div class="row border_bottom">
						<strong>'.Yii::t('views/config/edit_banner','LABEL_URL_TYPE').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'url-type').'
						<div>'.CHtml::radioButton($model_name.'[banner_description]['.$value->code.'][url_type]',!$model->banner_description[$value->code]['url_type']?1:0,array('value'=>0,'id'=>'banner_description['.$value->code.'][url_type_0]','class'=>'select_url_type_'.$value->code)).'&nbsp;<label for="banner_description['.$value->code.'][url_type_0]" style="display:inline; text-align: left;">'.Yii::t('views/config/edit_banner','LABEL_NO_URL').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[banner_description]['.$value->code.'][url_type]',$model->banner_description[$value->code]['url_type']==1?1:0,array('value'=>1,'id'=>'banner_description['.$value->code.'][url_type_1]','class'=>'select_url_type_'.$value->code)).'&nbsp;<label for="banner_description['.$value->code.'][url_type_1]" style="display:inline; text-align: left;">'.Yii::t('views/config/edit_banner','LABEL_URL').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[banner_description]['.$value->code.'][url_type]',$model->banner_description[$value->code]['url_type'] == 2?1:0,array('value'=>2,'id'=>'banner_description['.$value->code.'][url_type_2]','class'=>'select_url_type_'.$value->code)).'&nbsp;<label for="banner_description['.$value->code.'][url_type_2]" style="display:inline; text-align: left;">'.Yii::t('views/config/edit_banner','LABEL_CMSPAGE').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[banner_description]['.$value->code.'][url_type]',$model->banner_description[$value->code]['url_type'] == 3?1:0,array('value'=>3,'id'=>'banner_description['.$value->code.'][url_type_3]','class'=>'select_url_type_'.$value->code)).'&nbsp;<label for="banner_description['.$value->code.'][url_type_3]" style="display:inline; text-align: left;">'.Yii::t('views/config/edit_banner','LABEL_REGISTRATION_CONTEST').'</label>
						</div>        
					</div>							
					
					<div class="row" id="'.$value->code.'_url_type_1" '.($model->banner_description[$value->code]['url_type'] != 1 ? 'style="display:none;"':'').'>
						<div class="row border_bottom">
							<strong>'.Yii::t('views/config/edit_banner','LABEL_URL').' '.Yii::t('views/config/edit_banner','LABEL_URL_DESCRIPTION').'</strong>'.
							(isset($columns['url']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="banner_description['.$value->code.'][url]_maxlength">'.($columns['url']-strlen($model->banner_description[$value->code]['url'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'url').'
							<div>'.
							CHtml::textField($model_name.'[banner_description]['.$value->code.'][url]',$model->banner_description[$value->code]['url']?$model->banner_description[$value->code]['url']:'http://',array('style' => 'width: 98%;','maxlength'=>$columns['url'], 'id'=>'banner_description['.$value->code.'][url]')).'
							<br /><span id="banner_description['.$value->code.'][url]_errorMsg" class="error"></span>
							</div>
						</div>								
						
						<div class="row border_bottom">
							<strong>'.Yii::t('views/config/edit_banner','LABEL_TARGET_BLANK').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'target-blank').'
							<div>'.
							CHtml::radioButton($model_name.'[banner_description]['.$value->code.'][target_blank]',$model->banner_description[$value->code]['target_blank']?1:0,array('value'=>1,'id'=>'banner_description['.$value->code.'][target_blank_1]')).'&nbsp;<label for="banner_description['.$value->code.'][target_blank_1]" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[banner_description]['.$value->code.'][target_blank]',!$model->banner_description[$value->code]['target_blank']?1:0,array('value'=>0,'id'=>'banner_description['.$value->code.'][target_blank_0]')).'&nbsp;<label for="banner_description['.$value->code.'][target_blank_0]" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>
							</div>        
						</div>
					</div>		
					
					
					<div class="row" id="'.$value->code.'_url_type_2" '.($model->banner_description[$value->code]['url_type'] != 2 ? 'style="display:none;"':'').'>
						<strong>'.Yii::t('views/config/edit_banner','LABEL_CMSPAGE').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'id-cmspage').'
						<div>
						<select name="'.$model_name.'[banner_description]['.$value->code.'][id_cmspage]" id="banner_description['.$value->code.'][id_cmspage]">
						';
						
						if (sizeof($cmspages)) {
						
							foreach ($cmspages as $id_cmspage => $page) {
								echo '<option value="'.$id_cmspage.'" '.(($model->banner_description[$value->code]['id_cmspage']==$id_cmspage)?'selected="selected"':'').'>'.$page.'</option>';	
							}
						}
						
					echo '
						</select>
						<br /><span id="banner_description['.$value->code.'][id_cmspage]_errorMsg" class="error"></span>
						</div>
					</div>	
					
					<div class="row border_bottom" id="'.$value->code.'_url_type_3" '.($model->banner_description[$value->code]['url_type'] != 3 ? 'style="display:none;"':'').'>
						<strong>'.Yii::t('views/config/edit_banner','LABEL_REGISTRATION_CONTEST').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'id-subscription-contest').'
						<div>
						<select name="'.$model_name.'[banner_description]['.$value->code.'][id_subscription_contest]" id="banner_description['.$value->code.'][id_subscription_contest]">
						';
					
					$criteria=new CDbCriteria; 
					$criteria->condition='((active=1 AND (end_date = "0000-00-00 00:00:00" OR end_date >= "'.$current_datetime.'")) OR id = "'.$model->banner_description[$value->code]['id_subscription_contest'].'")'; 									
					
					if (sizeof($subscription_contests = Tbl_SubscriptionContest::model()->findAll($criteria))) {
						
						foreach ($subscription_contests as $row) {		
							echo '<option value="'.$row->id.'" '.(($model->banner_description[$value->code]['id_subscription_contest']==$row->id)?'selected="selected"':'').'>'.$row->name.'</option>';	
						}						
					}
					
					echo '</select>
					<br /><span id="banner_description['.$value->code.'][id_subscription_contest]_errorMsg" class="error"></span>
					</div>			
					</div>						
    
					<div class="row">
						<div style="margin-bottom:10px;" id="banner_description_'.$value->code.'_image"><div style="margin-bottom:5px"><strong>'.Yii::t('views/config/edit_banner','LABEL_DIMENSION_IMAGE').'</strong> '.$app->params['banner_width'].'px <strong>X</strong> '.$app->params['banner_height'].'px&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'file').'</div>'.($model->banner_description[$value->code]['filename'] ? '<img src="/_images/banner/'.$model->banner_description[$value->code]['filename'].'" width="'.($app->params['banner_width']/3).'" height="'.($app->params['banner_height']/3).'">':'').'</div>
						<div id="banner_description_'.$value->code.'_image_upload_button"></div><br />
						<div id="banner_description_'.$value->code.'_image_upload_queue" syle="margin-bottom:5px;"></div>
						'.CHtml::hiddenField($model_name.'[banner_description]['.$value->code.'][filename]',"",array('id'=>'banner_description_'.$value->code.'_filename')).
						'
						<span id="banner_description['.$value->code.'][filename]_errorMsg" class="error"></span>                        
					</div>			
					<script type="text/javascript">
					$(function(){
	
						// bind upload file input
						$("#banner_description_'.$value->code.'_image_upload_button").uploadify({
							"swf" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify.swf?'.time().'",
							"uploader" : "'.CController::createUrl('upload_image_banner').'",
							"checkExisting" : false,
							"formData" : {"PHPSESSID":"'.session_id().'"},
							"cancelImage" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify-cancel.png",
							"buttonText" : "'.Yii::t('views/config/edit_banner','BTN_SELECT_BANNER').'",
							"width" : 170,
							"fileTypeDesc" : "Images (*.gif, *.jpeg, *.jpg, *.png)",
							"fileTypeExts" : "*.gif;*.jpeg;*.jpg;*.png",		
							// 5 mb limit per file		
							"fileSizeLimit" : 5242880,
							"auto" : true,
					
							"queueID" : "banner_description_'.$value->code.'_image_upload_queue",
							
							"onUploadSuccess" : function(file,data,response){
								if (data.indexOf("file:") == -1) {				
									$("#" + file.id).addClass("uploadifyError").find(".data").html(" - " + data);
								} else {
									var filename = data.replace("file:","");				
									
									$("#banner_description_'.$value->code.'_filename").val(filename);
									
									$("#banner_description_'.$value->code.'_image").html("").append("<div style=\"margin-bottom:5px\"><strong>'.Yii::t('views/config/edit_banner','LABEL_DIMENSION_IMAGE').'</strong> '.$app->params['banner_width'].'px <strong>X</strong> '.$app->params['banner_height'].'px</div><img src=\"/_images/banner/"+filename+"\" width=\"'.($app->params['banner_width']/3).'\" height=\"'.($app->params['banner_height']/3).'\">");
								}
							}
						});		
						
						$(".select_url_type_'.$value->code.'").on("click",function(){
							$("div[id^=\''.$value->code.'_url_type_\']:not(#'.$value->code.'_url_type_"+$(this).val()+")").hide();
							
							$("#'.$value->code.'_url_type_"+$(this).val()).show();
						});
					});
					</script>		
					
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
	 * This is the action to upload images
	 */
	public function actionUpload_image_banner()
	{			
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		
		// if id is not set
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $app->params['root_url'].'_images/banner/';
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
					
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;
				}			
				
				$width = $image->getWidth();
				$height = $image->getHeight();
				
				if (!$width || !$height) {
					
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;					
				}
				
				// if our image size is smaller than our min 800x600
				if ($width < $app->params['banner_width'] || $height < $app->params['banner_height']) { 
					echo Yii::t('global', 'ERROR_MIN_IMAGE_RESOLUTION', array('{width}'=>$app->params['banner_width'],'{height}'=>$app->params['banner_height']));							
					exit;
				}						
															
				// save image
				if (!$image->resize($app->params['banner_width'],$app->params['banner_height'])) {
					echo Yii::t('global', 'ERROR_RESIZE_BANNER_FAILED');						
					exit;		
				} else if (!$image->save($targetPath.$filename)) {
					echo Yii::t('global', 'ERROR_SAVE_BANNER_FAILED');						
					exit;									
				}

				echo 'file:'.$filename;
				exit;
				break;
			}
			
			echo Yii::t('global','ERROR_UPLOAD_IMAGE_FAILED');
		}				
	}			
	
	/**
	 * This is the action to get an XML list of tag for this product
	 */
	public function actionXml_list_banner($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		
		$app = Yii::app();
		
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
		
		// filters					
		
		$sql = "SELECT 
		COUNT(banner.id) AS total 
		FROM 
		banner 
		INNER JOIN 
		banner_description 
		ON 
		(banner.id = banner_description.id_banner AND banner_description.language_code = '".Yii::app()->language."') 
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
		banner.id,
		banner.name,
		banner.active,
		banner_description.filename
		FROM 
		banner 
		INNER JOIN 
		banner_description 
		ON 
		(banner.id = banner_description.id_banner AND banner_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY banner.date_created DESC";	
		
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
			<cell type="ro"><![CDATA[<div style="margin-bottom: 5px; margin-top:5px; font-weight: bold;">'.strtoupper($row['name']).'</div><img src="/_images/banner/'.$row['filename'].'" width="'.($app->params['banner_width']/2).'" height="'.($app->params['banner_height']/2).'" />]]></cell>
			<cell type="ch"><![CDATA['.$row['active'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/**
	 * This is the action to toggle active
	 */
	public function actionToggle_active()
	{
		$id = (int)$_POST['id'];
		$state = ($_POST['state']=='true'?1:0);
		
		if ($banner = Tbl_Banner::model()->findByPk($id)) {
			$banner->active = $state;
			if (!$banner->save()) {
				throw new CException('unable to save');	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
	
	
	
	public function actionSave_banner()
	{
		$model = new BannerForm;
		
		
		// collect user input data
		if(isset($_POST['BannerForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['BannerForm'] as $name=>$value)
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
	
	public function actionEdit_custom_form_fields()
	{
		$this->renderPartial('edit_custom_form_fields');	
	}			
	
	public function actionEdit_custom_form_fields_options()
	{
		$model = new CustomFieldsForm;
		
		$this->renderPartial('edit_custom_form_fields_options',array('model'=>$model));	
	}	
	
	/************************************************************
	*															*
	*															*
	*							CUSTOM FORM FIELDS				*
	*															*
	*															*
	************************************************************/
	
	/**
	 * This is the action to get an XML list of custom fields
	 */
	public function actionXml_list_custom_fields($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$form = (int)$_GET['form'];
		
		$where=array();
		$params=array(':form'=>$form);
		$where[] = 'custom_fields.form = :form';			
		
		$sql = "SELECT 
		COUNT(custom_fields.id) AS total 
		FROM 
		custom_fields
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
		custom_fields.id,
		custom_fields.type,
		custom_fields.required,
		custom_fields_description.name
		FROM 
		custom_fields 
		INNER JOIN 
		custom_fields_description 
		ON 
		(custom_fields.id = custom_fields_description.id_custom_fields AND custom_fields_description.language_code = '".Yii::app()->language."')  
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY custom_fields.sort_order ASC,
		custom_fields_description.name ASC";
				
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			$type = Yii::t('views/config/add_custom_form_fields_options','LABEL_TYPE_'.$row['type']);
			
			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$type.']]></cell>
			<cell type="ro"><![CDATA['.($row['required'] ? Yii::t('global','LABEL_YES'):Yii::t('global','LABEL_NO')).']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	/** 
	 *
	 */
	public function actionXml_list_custom_fields_description($id=0)
	{
		$model = new CustomFieldsForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($cf = Tbl_CustomFields::model()->findByPk($id)) {							
				// grab description information 
				foreach ($cf->tbl_custom_fields_description as $row) {
					$model->custom_fields_description[$row->language_code]['name'] = $row->name;
					$model->custom_fields_description[$row->language_code]['description'] = $row->description;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_CustomFieldsDescription::tableName());		
		
		$help_hint_path = '/settings/general/custom-form-fields/';
		
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
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_custom_fields_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->custom_fields_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'name').'
						<div>'.
						CHtml::activeTextField($model,'custom_fields_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>$container.'_custom_fields_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_custom_fields_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>					
					
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'description').'
						<div>'.
						CHtml::activeTextArea($model,'custom_fields_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'class' => 'editor', 'rows' => 6, 'id'=>$container.'_custom_fields_description['.$value->code.'][description]')).'
						<br /><span id="'.$container.'_custom_fields_description['.$value->code.'][description]_errorMsg" class="error"></span>
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
	
	public function actionAdd_custom_form_fields_options()
	{
		$model = new CustomFieldsForm;
		
		$form = (int)$_POST['form'];
		$id = (int)$_POST['id'];
		
		if ($id) {
			if (!$cf = Tbl_CustomFields::model()->findByPk($id)) throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			
			$model->id = $cf->id;
			$model->type = $cf->type;
			$model->required = $cf->required;			
		}		
		
		$this->renderPartial('add_custom_form_fields_options',array('model'=>$model));	
	}		
	
	/**
	 * This is the action to save 
	 */
	public function actionSave_custom_fields()
	{
		$model = new CustomFieldsForm;
		$output = array();

		$form = (int)$_POST['form'];
		$model->form = $form;
		
		// collect user input data
		if(isset($_POST['CustomFieldsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['CustomFieldsForm'] as $name=>$value)
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
		}
		
		
		echo CJSON::encode($output);	
	}			
	
	public function actionDelete_custom_fields()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			//delete custom fields
			$sql = 'DELETE FROM 
			custom_fields,
			custom_fields_description
			USING
			custom_fields
			INNER JOIN
			custom_fields_description
			ON
			(custom_fields.id = custom_fields_description.id_custom_fields)
			WHERE
			custom_fields.id = :id_custom_fields';							
			$command=$connection->createCommand($sql);			
			
			//delete custom fields options
			$sql = 'DELETE FROM 
			custom_fields_option,
			custom_fields_option_description
			USING
			custom_fields_option
			INNER JOIN
			custom_fields_option_description
			ON
			(custom_fields_option.id = custom_fields_option_description.id_custom_fields_option)
			WHERE
			custom_fields_option.id_custom_fields = :id_custom_fields';							
			$command_option=$connection->createCommand($sql);			
			
			foreach ($ids as $id_custom_fields) {
				$command->execute(array(':id_custom_fields'=>$id_custom_fields));
				$command_option->execute(array(':id_custom_fields'=>$id_custom_fields));
			}
		}
	}	
	
	/**
	 * This is the action to save custom fields option order
	 */
	public function actionSave_custom_fields_sort_order($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id); 					
								
				if ($ps = Tbl_CustomFields::model()->find($criteria)) {
					$ps->sort_order = $i;
					$ps->save();
					
					++$i;
				}
			}
		}
	}
		
	public function actionAdd_custom_form_fields_option_options()
	{
		$model = new CustomFieldsOptionForm;
		
		$id = (int)$_POST['id'];
		$type = (int)$_POST['type'];
		
		if ($id) {
			if (!$cf = Tbl_CustomFieldsOption::model()->findByPk($id)) throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			
			$model->id = $cf->id;
			$model->add_extra = $cf->add_extra;
			$model->extra_required = $cf->extra_required;
			$model->selected = $cf->selected;
		}		
		
		$this->renderPartial('add_custom_fields_option_options',array('model'=>$model,'type'=>$type));	
	}		
	
	/**
	 * This is the action to save 
	 */
	public function actionSave_custom_fields_option()
	{
		$model = new CustomFieldsOptionForm;
		$output = array();

		$id_custom_fields = (int)$_POST['id_custom_fields'];
		$model->id_custom_fields = $id_custom_fields;
		
		// collect user input data
		if(isset($_POST['CustomFieldsOptionForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['CustomFieldsOptionForm'] as $name=>$value)
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
		}
		
		
		echo CJSON::encode($output);	
	}			
	
	public function actionDelete_custom_fields_option()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {			
			//delete custom fields options
			$sql = 'DELETE FROM 
			custom_fields_option,
			custom_fields_option_description
			USING
			custom_fields_option
			INNER JOIN
			custom_fields_option_description
			ON
			(custom_fields_option.id = custom_fields_option_description.id_custom_fields_option)
			WHERE
			custom_fields_option.id = :id';							
			$command=$connection->createCommand($sql);			
			
			foreach ($ids as $id) {
				$command->execute(array(':id'=>$id));
			}
		}
	}		
	
	/** 
	 *
	 */
	public function actionXml_list_custom_fields_option_description($id=0)
	{
		$model = new CustomFieldsOptionForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($cf = Tbl_CustomFieldsOption::model()->findByPk($id)) {							
				// grab description information 
				foreach ($cf->tbl_custom_fields_option_description as $row) {
					$model->custom_fields_option_description[$row->language_code]['name'] = $row->name;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_CustomFieldsOptionDescription::tableName());		
		
		$help_hint_path = '/settings/general/custom-form-fields/';
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';
		
		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:auto;">	
					<div style="padding:10px;">			
					<div class="row">
						<strong>'.Yii::t('global','LABEL_NAME').'</strong>'.
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_custom_fields_option_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->custom_fields_option_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'name').'
						<div>'.
						CHtml::activeTextField($model,'custom_fields_option_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>$container.'_custom_fields_option_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_custom_fields_option_description['.$value->code.'][name]_errorMsg" class="error"></span>
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
	 * This is the action to save custom fields option order
	 */
	public function actionSave_custom_fields_option_sort_order($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id); 					
								
				if ($ps = Tbl_CustomFieldsOption::model()->find($criteria)) {
					$ps->sort_order = $i;
					$ps->save();
					
					++$i;
				}
			}
		}
	}
	
	
	/**
	 * This is the action to get an XML list of custom fields options
	 */
	public function actionXml_list_custom_fields_options($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$id = (int)$_GET['id'];
		
		if (!Tbl_CustomFields::model()->findByPk($id)) throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		
		$where=array();
		$params=array(':id_custom_fields'=>$id);
		$where[] = 'custom_fields_option.id_custom_fields = :id_custom_fields';			
		
		$sql = "SELECT 
		COUNT(custom_fields_option.id) AS total 
		FROM 
		custom_fields_option
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
		custom_fields_option.id,
		custom_fields_option.add_extra,
		custom_fields_option.extra_required,
		custom_fields_option_description.name
		FROM 
		custom_fields_option 
		INNER JOIN 
		custom_fields_option_description 
		ON 
		(custom_fields_option.id = custom_fields_option_description.id_custom_fields_option AND custom_fields_option_description.language_code = '".Yii::app()->language."')  
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY custom_fields_option.sort_order ASC,
		custom_fields_option_description.name ASC";
				
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
	
	/**
	 * This is the action to get an XML list of display price exceptions
	 */
	public function actionXml_list_display_price_exceptions($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
		
		$sql = "SELECT 
		COUNT(id_product) AS total 
		FROM 
		config_display_price_exceptions
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
		config_display_price_exceptions.id_product,
		product.sku,
		product_description.name
		FROM 
		config_display_price_exceptions 
		INNER JOIN 
		(product CROSS JOIN product_description)
		ON 
		(config_display_price_exceptions.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."')  
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY name ASC";
				
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {				
			echo '<row id="'.$row['id_product'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}		
	
	/**
	 * action to delete display price exception 
	 **/
	public function actionDelete_display_price_exceptions()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {			
			foreach ($ids as $id) {
				Tbl_ConfigDisplayPriceExceptions::model()->deleteAll('id_product=:id_product',array(':id_product'=>$id));
			}
		}		
	}
	
	/**
	 * action to list products to add to display price exceptions
	 **/
	public function actionXml_list_display_price_exceptions_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('config_display_price_exceptions.id_product IS NULL');
		
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
		
		$sql = "SELECT 
		COUNT(product.id) AS total  
		FROM 
		product 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
		LEFT JOIN
		config_display_price_exceptions 
		ON 
		product.id=config_display_price_exceptions.id_product
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
		product.sell_price AS price
		FROM 
		product 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
		LEFT JOIN
		config_display_price_exceptions 
		ON 
		product.id=config_display_price_exceptions.id_product
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
	
	/**
	 * add display price exception
	 **/
	public function actionSave_display_price_exceptions()
	{
		
		// current product
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id) {	
				$ps = new Tbl_ConfigDisplayPriceExceptions;
				$ps->id_product = $id;
				if (!$ps->save()){
					echo 'false';
					exit;
				}		
			}
		}	
		
		echo 'true';
		exit;	
	}	
	
	/**
	 * This is the action to get an XML list of allow add to cart exceptions
	 */
	public function actionXml_list_allow_add_to_cart_exceptions($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
		
		$sql = "SELECT 
		COUNT(id_product) AS total 
		FROM 
		config_allow_add_to_cart_exceptions
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
		config_allow_add_to_cart_exceptions.id_product,
		product.sku,
		product_description.name
		FROM 
		config_allow_add_to_cart_exceptions 
		INNER JOIN 
		(product CROSS JOIN product_description)
		ON 
		(config_allow_add_to_cart_exceptions.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."')  
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY name ASC";
				
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {				
			echo '<row id="'.$row['id_product'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}		
	
	/**
	 * action to delete allow add to cart exception 
	 **/
	public function actionDelete_allow_add_to_cart_exceptions()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {			
			foreach ($ids as $id) {
				Tbl_ConfigAllowAddToCartExceptions::model()->deleteAll('id_product=:id_product',array(':id_product'=>$id));
			}
		}		
	}
	
	/**
	 * action to list products to allow add to cart exceptions
	 **/
	public function actionXml_list_allow_add_to_cart_exceptions_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('config_allow_add_to_cart_exceptions.id_product IS NULL');
		
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
		
		$sql = "SELECT 
		COUNT(product.id) AS total  
		FROM 
		product 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
		LEFT JOIN
		config_allow_add_to_cart_exceptions 
		ON 
		product.id=config_allow_add_to_cart_exceptions.id_product
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
		product.sell_price AS price
		FROM 
		product 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
		LEFT JOIN
		config_allow_add_to_cart_exceptions 
		ON 
		product.id=config_allow_add_to_cart_exceptions.id_product
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
	
	/**
	 * add display price exception
	 **/
	public function actionSave_allow_add_to_cart_exceptions()
	{
		
		// current product
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id) {	
				$ps = new Tbl_ConfigAllowAddToCartExceptions;
				$ps->id_product = $id;
				if (!$ps->save()){
					echo 'false';
					exit;
				}		
			}
		}	
		
		echo 'true';
		exit;	
	}		
	
	/**
	 * This is the action to get an XML list of display price exceptions
	 */
	public function actionXml_list_free_shipping_product_exceptions($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
		
		$sql = "SELECT 
		COUNT(config_free_shipping_product_exceptions.id_product) AS total 
		FROM 
		config_free_shipping_product_exceptions
		INNER JOIN 
		(product CROSS JOIN product_description)
		ON 
		(config_free_shipping_product_exceptions.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
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
		config_free_shipping_product_exceptions.id_product,
		product.sku,
		product_description.name
		FROM 
		config_free_shipping_product_exceptions
		INNER JOIN 
		(product CROSS JOIN product_description)
		ON 
		(config_free_shipping_product_exceptions.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."')  
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY product_description.name ASC";
				
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {				
			echo '<row id="'.$row['id_product'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}		
	
	/**
	 * action to delete display price exception 
	 **/
	public function actionDelete_free_shipping_product_exceptions()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {			
			foreach ($ids as $id) {
				Tbl_ConfigFreeShippingProductExceptions::model()->deleteAll('id_product=:id_product',array(':id_product'=>$id));
			}
		}		
	}
	
	/**
	 * action to list products to add to display price exceptions
	 **/
	public function actionXml_list_free_shipping_product_exceptions_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('config_free_shipping_product_exceptions.id_product IS NULL');
		
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
		
		$sql = "SELECT 
		COUNT(product.id) AS total  
		FROM 
		product 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
		LEFT JOIN
		config_free_shipping_product_exceptions
		ON 
		product.id=config_free_shipping_product_exceptions.id_product
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
		product.sell_price AS price
		FROM 
		product 
		INNER JOIN 
		product_description 
		ON 
		(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
		LEFT JOIN
		config_free_shipping_product_exceptions
		ON 
		product.id=config_free_shipping_product_exceptions.id_product
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
	
	/**
	 * add display price exception
	 **/
	public function actionSave_free_shipping_product_exceptions()
	{
		
		// current product
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id) {	
				$ps = new Tbl_ConfigFreeShippingProductExceptions;
				$ps->id_product = $id;
				if (!$ps->save()){
					echo 'false';
					exit;
				}		
			}
		}	
		
		echo 'true';
		exit;	
	}		
	
	/************************************************************
	*															*
	*															*
	*						STORE LOCATIONS						*
	*															*
	*															*
	************************************************************/
		
	/**
	 * This is the action to delete a banner
	 */
	public function actionDelete_store_location()
	{
		// current product
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$image_base_path = Yii::app()->params['root_url'].'images/stores/';			
			
			foreach ($ids as $id) {
				if (!$s = Tbl_StoreLocations::model()->findByPk($id)) throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
				
				if ($s->image && is_file($image_base_path.$s->image)) unlink($image_base_path.$s->image);
				
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id);					
				
				// delete all
				Tbl_StoreLocations::model()->deleteAll($criteria);			
			}
		}		
	}
		
	public function actionEdit_store_locations_options($id=0)
	{
		$model = new StoreLocationsForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($s = Tbl_StoreLocations::model()->findByPk($id)) {
				$model->id = $s->id;
				$model->hide_address = $s->hide_address;
				$model->name = $s->name;
				$model->address = $s->address;
				$model->city = $s->city;
				$model->state_code = $s->state_code;
				$model->zip = $s->zip;
				$model->country_code = $s->country_code;
				$model->telephone = $s->telephone;
				$model->fax = $s->fax;
				$model->email = $s->email;
				$model->url = $s->url;
				$model->lat = $s->lat;
				$model->lng = $s->lng;
				$model->image_old = $s->image;
				$model->open_mon = $s->open_mon;
				$model->open_mon_start_time = ($s->open_mon_start_time != '00:00:00') ? $s->open_mon_start_time:'';
				$model->open_mon_end_time = ($s->open_mon_end_time != '00:00:00') ? $s->open_mon_end_time:'';
				$model->open_tue = $s->open_tue;
				$model->open_tue_start_time = ($s->open_tue_start_time != '00:00:00') ? $s->open_tue_start_time:'';
				$model->open_tue_end_time = ($s->open_tue_end_time != '00:00:00') ? $s->open_tue_end_time:'';
				$model->open_wed = $s->open_wed;
				$model->open_wed_start_time = ($s->open_wed_start_time != '00:00:00') ? $s->open_wed_start_time:'';
				$model->open_wed_end_time = ($s->open_wed_end_time != '00:00:00') ? $s->open_wed_end_time:'';
				$model->open_thu = $s->open_thu;
				$model->open_thu_start_time = ($s->open_thu_start_time != '00:00:00') ? $s->open_thu_start_time:'';
				$model->open_thu_end_time = ($s->open_thu_end_time != '00:00:00') ? $s->open_thu_end_time:'';
				$model->open_fri = $s->open_fri;
				$model->open_fri_start_time = ($s->open_fri_start_time != '00:00:00') ? $s->open_fri_start_time:'';
				$model->open_fri_end_time = ($s->open_fri_end_time != '00:00:00') ? $s->open_fri_end_time:'';
				$model->open_sat = $s->open_sat;
				$model->open_sat_start_time = ($s->open_sat_start_time != '00:00:00') ? $s->open_sat_start_time:'';
				$model->open_sat_end_time = ($s->open_sat_end_time != '00:00:00') ? $s->open_sat_end_time:'';
				$model->open_sun = $s->open_sun;
				$model->open_sun_start_time = ($s->open_sun_start_time != '00:00:00') ? $s->open_sun_start_time:'';
				$model->open_sun_end_time = ($s->open_sun_end_time != '00:00:00') ? $s->open_sun_end_time:'';
				$model->active = $s->active;				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_store_locations_options',array('model'=>$model));	
	}
		
	/**
	 * This is the action to upload images
	 */
	public function actionUpload_image_store_location()
	{			
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		
		// if id is not set
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $app->params['root_url'].'images/stores/';
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
					
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;
				}			
				
				$width = $image->getWidth();
				$height = $image->getHeight();
				
				if (!$width || !$height) {
					
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;					
				}									
															
				// save image
				if (!$image->resizeToWidth($app->params['store_locations_logo_width'])) {
					echo Yii::t('views/config/edit_store_locations_options', 'ERROR_UPLOAD_IMAGE_FAILED');						
					exit;		
				}
				
				if (!$image->save($targetPath.$filename)) {
					echo Yii::t('views/config/edit_store_locations_options', 'ERROR_UPLOAD_IMAGE_FAILED');						
					exit;									
				}

				echo 'file:'.$filename;
				exit;
				break;
			}
			
			echo Yii::t('global','ERROR_UPLOAD_IMAGE_FAILED');
		}				
	}			
	
	public function actionDelete_image_store_location($filename, $id=0)
	{
		$filename = trim($filename);
		$targetPath = Yii::app()->params['root_url'].'images/stores/';
		$id = (int)$_GET['id'];
		
		if (is_file($targetPath.$filename)) @unlink($targetPath.$filename);		
		
		if ($id) Tbl_StoreLocations::model()->updateByPk($id,array('image'=>''));
	}
	
	/**
	 * This is the action to get an XML list of tag for this product
	 */
	public function actionXml_list_store_locations($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		
		$app = Yii::app();
		
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':language_code'=>$app->language);
		
		// filters					
		
		$sql = "SELECT 
		COUNT(store_locations.id) AS total 
		FROM 
		store_locations
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
		store_locations.*,
		country_description.name AS country,
		state_description.name AS state
		FROM 
		store_locations
		LEFT JOIN
		country_description 
		ON 
		(store_locations.country_code = country_description.country_code AND country_description.language_code = :language_code)	
		LEFT JOIN
		state_description
		ON 
		(store_locations.state_code = state_description.state_code AND state_description.language_code = :language_code)		
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY store_locations.name ".$direct;
		}else $sql.=" ORDER BY store_locations.name ASC";		
		
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
			<cell type="ro"><![CDATA['.
			$row['address'].'<br />'."\r\n".
			$row['city'].($row['state'] ? ' '.$row['state']:'').($row['zip'] ? ' '.$row['zip']:'').'<br />'."\r\n".
			$row['country'].'<br />'."\r\n".
			($row['telephone']? "\r\n".'<br /><strong>'.Yii::t('views/config/edit_store_locations_options','LABEL_TELEPHONE').'</strong> '.$row['telephone']:'').
			($row['fax']? "\r\n".'<br /><strong>'.Yii::t('views/config/edit_store_locations_options','LABEL_FAX').'</strong> '.$row['fax']:'').']]></cell>
			<cell type="ch"><![CDATA['.$row['hide_address'].']]></cell>
			<cell type="ch"><![CDATA['.$row['active'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/**
	 * This is the action to toggle active
	 */
	public function actionToggle_active_store_location()
	{
		$id = (int)$_POST['id'];
		$state = ($_POST['state']=='true'?1:0);
		
		if ($s = Tbl_StoreLocations::model()->findByPk($id)) {
			$s->active = $state;
			if (!$s->save()) {
				throw new CException('unable to save');	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
	
	/**
	 * This is the action to toggle hide_address
	 */
	public function actionToggle_hide_address_store_location()
	{
		$id = (int)$_POST['id'];
		$state = ($_POST['state']=='true'?1:0);
		
		if ($s = Tbl_StoreLocations::model()->findByPk($id)) {
			$s->hide_address = $state;
			if (!$s->save()) {
				throw new CException('unable to save');	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
	
	
	
	public function actionSave_store_location()
	{
		$model = new StoreLocationsForm;
		
		
		// collect user input data
		if(isset($_POST['StoreLocationsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['StoreLocationsForm'] as $name=>$value)
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
	
	public function actionPreview_store_locations()
	{
		header('Location: '.Yii::app()->params['root_relative_url'].Yii::app()->language.'/store_locations');
		exit;
	}
	
	// fixed shipping price
	public function actionXml_list_fixed_shipping_price($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
									
		
		$sql = "SELECT 
		COUNT(config_fixed_shipping_price.id) AS total 
		FROM 
		config_fixed_shipping_price
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
		config_fixed_shipping_price.*
		FROM 
		config_fixed_shipping_price
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
			<cell type="ro"><![CDATA['.$row['price'].']]></cell>
			<cell type="ro"><![CDATA['.$row['max_cart_price'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	public function actionDelete_fixed_shipping_price()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id);					
				
				// delete all
				Tbl_ConfigFixedShippingPrice::model()->deleteAll($criteria);						
			}
		}
	}
	
	public function actionEdit_fixed_shipping_price($id=0)
	{
		$model = new ConfigFixedShippingPriceForm;
		
		$id = (int)$_POST["id"];		
		if ($id) {
			if ($s = Tbl_ConfigFixedShippingPrice::model()->findByPk($id)) {
				$model->id = $s->id;
				$model->price = $s->price;
				$model->max_cart_price = $s->max_cart_price;	
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_fixed_shipping_price',array('model'=>$model));		
	}
	
	public function actionSave_fixed_shipping_price()
	{
		$model = new ConfigFixedShippingPriceForm;
		
		// collect user input data
		if(isset($_POST['ConfigFixedShippingPriceForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ConfigFixedShippingPriceForm'] as $name=>$value)
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
	 * This is the action to get an XML list of the product menu
	 */
	public function actionXml_list_section()
	{		
		//set content type and xml tag
		header("Content-type:text/xml");		
		
		echo '<data>
			<item id="edit_general">
				<Title><![CDATA['.Yii::t('controllers/ConfigController','LABEL_GENERAL').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/ConfigController','LABEL_GENERAL_DESCRIPTION').']]></Description>
			</item>
			<item id="edit_company_info">
				<Title><![CDATA['.Yii::t('controllers/ConfigController','LABEL_COMPANY_INFO').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/ConfigController','LABEL_COMPANY_INFO_DESCRIPTION').']]></Description>
			</item>
			<item id="edit_images">
				<Title><![CDATA['.Yii::t('controllers/ConfigController','LABEL_LOGO').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/ConfigController','LABEL_LOGO_DESCRIPTION').']]></Description>
			</item>
			<item id="edit_banner">
				<Title><![CDATA['.Yii::t('controllers/ConfigController','LABEL_BANNER').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/ConfigController','LABEL_BANNER_DESCRIPTION').']]></Description>
			</item>
			<item id="edit_shipping">
				<Title><![CDATA['.Yii::t('controllers/ConfigController','LABEL_SHIPPING').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/ConfigController','LABEL_SHIPPING_DESCRIPTION').']]></Description>
			</item>
			<item id="edit_payment">
				<Title><![CDATA['.Yii::t('controllers/ConfigController','LABEL_PAYMENT').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/ConfigController','LABEL_PAYMENT_DESCRIPTION').']]></Description>
			</item>
			
			<item id="edit_social_network">
				<Title><![CDATA['.Yii::t('controllers/ConfigController','LABEL_SOCIAL_NETWORK_GOOGLE_ANALYTICS').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/ConfigController','LABEL_SOCIAL_NETWORK_GOOGLE_ANALYTICS_DESCRIPTION').']]></Description>
			</item>
			<item id="edit_custom_form_fields">
				<Title><![CDATA['.Yii::t('controllers/ConfigController','LABEL_CUSTOM_FORM_FIELDS').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/ConfigController','LABEL_CUSTOM_FORM_FIELDS_DESCRIPTION').']]></Description>
			</item>		
			<item id="edit_store_locations">
				<Title><![CDATA['.Yii::t('controllers/ConfigController','LABEL_STORE_LOCATIONS').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/ConfigController','LABEL_STORE_LOCATIONS_DESCRIPTION').']]></Description>
			</item>					
		</data>';
	}	
	
	/**
	 * This is the action to get the list of province	
	 */
	public function actionGet_province_list_store_location()
	{
		$model=new StoreLocationsForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>'--'));
	}			
	
	public function actionGet_payment_gateway_extra($id)
	{
		$id=(int)$id;
		
		$model=new ConfigPaymentForm;
		
		$output = '';
		
		if (sizeof($rows = Tbl_PaymentGatewayExtra::model()->findAll('id_payment_gateway=:id_payment_gateway',array(':id_payment_gateway'=>$id)))) {
			$output.= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';

			foreach ($rows as $row) {
				$output .= '
			<tr>
				<td valign="top" width="30%"><strong>'.$row['name'].'</strong></td>
				<td valign="top">
					'.CHtml::activeTextField($model,'payment_gateway_extra['.$row['name'].']',array('style' => 'width: 250px;', 'id'=>'payment_gateway_extra_'.$row['name'],'value'=>$row['value'])).'  
					<br /><span id="payment_gateway_extra['.$row['name'].']_errorMsg" class="error"></span>                      
				</td>
			</tr>';
			}					
			$output .= '</table>';
		}
		
		echo $output;	
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