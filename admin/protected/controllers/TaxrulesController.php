<?php

class TaxrulesController extends Controller
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
			$where[] = 'tax_rule.name LIKE CONCAT(:name,"%")';
			$params[':name']=$filters['name'];
		}
		// country_code
		if (isset($filters['country_code']) && !empty($filters['country_code'])) {
			$where[] = 'country_description.name LIKE CONCAT(:country_code,"%")';
			$params[':country_code']=$filters['country_code'];
		}
		// state_code
		if (isset($filters['state_code']) && !empty($filters['state_code'])) {
			$where[] = 'state_description.name LIKE CONCAT(:state_code,"%")';
			$params[':state_code']=$filters['state_code'];
		}
		// zip_from
		if (isset($filters['zip_from']) && !empty($filters['zip_from'])) {
			$where[] = 'tax_rule.zip_from LIKE CONCAT(:zip_from,"%")';
			$params[':zip_from']=$filters['zip_from'];
		}
		// zip_from
		if (isset($filters['zip_to']) && !empty($filters['zip_to'])) {
			$where[] = 'tax_rule.zip_to LIKE CONCAT(:zip_to,"%")';
			$params[':zip_to']=$filters['zip_to'];
		}
		
		
					
		// status
		if (isset($filters['active'])) {
			switch ($filters['active']) {
				case 0:
				case 1:					
					$where[] = 'tax_rule.active = :active';				
					$params[':active']=$filters['active'];
					break;
			}
		}								
		
		$sql = "SELECT 
		COUNT(tax_rule.id) AS total 
		FROM 
		tax_rule 
		LEFT JOIN 
		country_description
		ON
		(tax_rule.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(tax_rule.state_code = state_description.state_code AND state_description.language_code = :language_code)
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
		tax_rule.*,
		country_description.name AS country,
		state_description.name AS state		
		FROM 
		tax_rule 
		LEFT JOIN 
		country_description
		ON
		(tax_rule.country_code = country_description.country_code AND country_description.language_code = :language_code)
		LEFT JOIN 
		state_description
		ON
		(tax_rule.state_code = state_description.state_code AND state_description.language_code = :language_code)
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tax_rule.name ".$direct;
		} else if (isset($sort_col[4]) && !empty($sort_col[4])) {
			$direct = $sort_col[4] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tax_group.name ".$direct;		
		// state
		} else if (isset($sort_col[5]) && !empty($sort_col[5])) {
			$direct = $sort_col[5] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tax_group.name ".$direct;	
		} else if (isset($sort_col[6]) && !empty($sort_col[6])) {
			$direct = $sort_col[6] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tax_rule.zip_from ".$direct;		
		} else if (isset($sort_col[7]) && !empty($sort_col[7])) {
			$direct = $sort_col[7] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tax_rule.zip_to ".$direct;																									
		} else {
			$sql.=" ORDER BY tax_rule.id ASC";
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
			<cell type="ro"><![CDATA['.($row['country'] ? $row['country']:Yii::t('views/taxrules/edit_info_options','LABEL_ALL_COUNTRY_EXCEPT')).']]></cell>
			<cell type="ro"><![CDATA['.($row['state'] ? $row['state']:Yii::t('views/taxrules/edit_info_options','LABEL_ALL_STATE_EXCEPT')).']]></cell>
			<cell type="ro"><![CDATA['.$row['zip_from'].']]></cell>
			<cell type="ro"><![CDATA['.$row['zip_to'].']]></cell>
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
		$model=new TaxRulesForm;	
		
		$id = (int)$id;
		
		if ($id) { 
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 		
			
			if (!Tbl_TaxRule::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		
			
			$model->id = $id;	
		}		
				
		$this->renderPartial('edit',array('model'=>$model, 'container'=>$container));	
	}
	
	/**
	 * This is the action to edit each section
	 */ 
	public function actionEdit_section($container, $containerJS)	
	{						
		$id = trim($_POST['id']);
		$id_tax_rule = (int)$_POST['id_tax_rule'];
		
		if ($id_tax_rule) { 
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id_tax_rule); 		
			
			if (!Tbl_TaxRule::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}			
		}
						
		$this->renderPartial($id,array('id'=>$id_tax_rule,'container'=>$container,'containerJS'=>$containerJS));	
	}	
	
	public function actionEdit_info_options($container, $id=0)
	{
		$model = new TaxRulesForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($taxrule = Tbl_TaxRule::model()->findByPk($id)) {
				$model->id = $taxrule->id;
				$model->name = $taxrule->name;
				$model->country_code = $taxrule->country_code;
				$model->state_code = $taxrule->state_code;
				$model->zip_from = $taxrule->zip_from;
				$model->zip_to = $taxrule->zip_to;
				$model->active = $taxrule->active;	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_info_options',array('model'=>$model, 'container'=>$container));	
	}		
	
	/**
	 * This is the action to get the list of province	
	 */
	public function actionGet_province_list()
	{
		$model=new TaxRulesForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>'All'));
	}	
	
	/**
	 * This is the action to save the information section	 
	 */
	public function actionSave_info()
	{
		$model = new TaxRulesForm;
		
		// collect user input data
		if(isset($_POST['TaxRulesForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['TaxRulesForm'] as $name=>$value)
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
		// current category
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			foreach ($ids as $id) {				
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tax_rule=:id_tax_rule'; 
				$criteria->params=array(':id_tax_rule'=>$id); 					
				
				// delete all
				Tbl_TaxRule::model()->deleteByPk($id);
				Tbl_TaxRuleRate::model()->deleteAll($criteria);				
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
		
		if ($p = Tbl_TaxRule::model()->findByPk($id)) {
			$p->active = $active;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}		
		
	public function actionXml_list_taxes_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		if(isset($_GET['id_tax_rule'])){
			$id_tax_rule = (int)$_GET['id_tax_rule'];
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array();
			$params=array();
			
			$where[]='tax_rule_rate.id IS NULL';
			$params[':id_tax_rule']=$id_tax_rule;	
			
			// filters
			
			// name
			if (isset($filters['name']) && !empty($filters['name'])) {
				$where[] = 'tax_description.name LIKE CONCAT("%",:name,"%")';
				$params[':name']=$filters['name'];
			}
			
			// code
			if (isset($filters['code']) && !empty($filters['code'])) {
				$where[] = 'tax.code LIKE CONCAT(:code,"%")';
				$params[':code']=$filters['code'];
			}
			
			// tax number
			if (isset($filters['tax_number']) && !empty($filters['tax_number'])) {
				$where[] = 'tax.tax_number LIKE CONCAT(:tax_number,"%")';
				$params[':tax_number']=$filters['tax_number'];
			}				
			
			$sql = "SELECT 
			COUNT(tax.id) AS total  
			FROM 
			tax 
			INNER JOIN 
			tax_description 
			ON 
			(tax.id = tax_description.id_tax AND tax_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			tax_rule_rate 
			ON 
			tax.id=tax_rule_rate.id_tax AND tax_rule_rate.id_tax_rule = :id_tax_rule
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
			tax.id,
			tax_description.name,
			tax.code,
			tax.tax_number
			FROM 
			tax 
			INNER JOIN 
			tax_description 
			ON 
			(tax.id = tax_description.id_tax AND tax_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			tax_rule_rate 
			ON 
			tax.id=tax_rule_rate.id_tax AND tax_rule_rate.id_tax_rule = :id_tax_rule
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
				
			// sorting

			if (isset($filters['name']) && !empty($filters['name'])) {
				$sql.=" ORDER BY 
				IF(tax_description.name LIKE CONCAT(:name,'%'),1,0) DESC, tax_description.name ASC";
			}else{
				$sql.=" ORDER BY tax_description.name ASC";
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
				<cell type="ro"><![CDATA['.$row['name'].']]>$totalCount</cell>
				<cell type="ro"><![CDATA['.$row['code'].']]></cell>
				<cell type="ro"><![CDATA['.$row['tax_number'].']]></cell>
				</row>';
			}
			
			echo '</rows>';
			
		}
	}		
	
	public function actionAdd_taxes()
	{
		
		// current product
		$id_tax_rule = $_POST['id_tax_rule'];
		$ids = $_POST['ids'];
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_tax_rule=:id_tax_rule'; 
		$criteria->params=array(':id_tax_rule'=>$id_tax_rule);
		$criteria->order='sort_order DESC';	
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_tax) {	
				$ps = new Tbl_TaxRuleRate;
				$ps->id_tax_rule = $id_tax_rule;
				$ps->id_tax = $id_tax;
				$ps->sort_order = Tbl_TaxRuleRate::model()->find($criteria)->sort_order+1;
				if (!$ps->save()){
					echo 'false';
					exit;
				}else{
					$connection=Yii::app()->db;   // assuming you have configured a "db" connection
					
					//create query 
					$sql = "SELECT 
					id	
					FROM 
					tax_rule_exception 
					WHERE id_tax_rule = '" .$id_tax_rule."'";		
					
					$command=$connection->createCommand($sql);
					
					// Cycle through results
					foreach ($command->queryAll(true) as $row) {
						$ps_2 = new Tbl_TaxRuleExceptionRate;
						$ps_2->id_tax_rule_exception = $row['id'];
						$ps_2->id_tax_rule_rate = $ps->id;
						if (!$ps_2->save()){
							echo 'false';
							exit;
						}
					}
				}
				
			}
		}		
	}
	
	/**
	 * This is the action to delete a tax
	 */
	public function actionDelete_tax($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_tax_rule_rate) {						
				// delete all
				
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tax_rule=:id_tax_rule AND id=:id'; 
				$criteria->params=array(':id_tax_rule'=>$id,':id'=>$id_tax_rule_rate); 		
				Tbl_TaxRuleRate::model()->deleteAll($criteria);	
				
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tax_rule_rate=:id_tax_rule_rate'; 
				$criteria->params=array(':id_tax_rule_rate'=>$id_tax_rule_rate); 	
				Tbl_TaxRuleExceptionRate::model()->deleteAll($criteria);					
			}
		}
	}			
	
	/**
	 * This is the action to save suggestion order
	 */
	public function actionSave_taxes_sort_order($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_tax_rule_rate) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tax_rule=:id_tax_rule AND id=:id'; 
				$criteria->params=array(':id_tax_rule'=>$id,':id'=>$id_tax_rule_rate); 					
								
				if ($ps = Tbl_TaxRuleRate::model()->find($criteria)) {
					$ps->sort_order = $i;
					$ps->save();
					
					++$i;
				}
			}
		}
	}	
	
	
	/**
	 * This is the action to toggle stacked
	 */
	public function actionToggle_stacked()
	{
		$id = (int)$_POST['id'];
		$stacked = ($_POST['stacked']=='true'?1:0);
		
		if ($p = Tbl_TaxRuleRate::model()->findByPk($id)) {
			$p->stacked = $stacked;
			if (!$p->save()) {
				throw new CException('unable to change stacked');	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}			

	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_taxes($id=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('tax_rule_rate.id_tax_rule=:id_tax_rule');
		$params=array(':id_tax_rule'=>$id,':language_code'=>Yii::app()->language);
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'tax_description.name LIKE CONCAT(:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// code
		if (isset($filters['code']) && !empty($filters['code'])) {
			$where[] = 'tax.code LIKE CONCAT(:code,"%")';
			$params[':code']=$filters['code'];
		}
		
		// tax number
		if (isset($filters['tax_number']) && !empty($filters['tax_number'])) {
			$where[] = 'tax.tax_number LIKE CONCAT(:tax_number,"%")';
			$params[':tax_number']=$filters['tax_number'];
		}				
		
		$sql = 'SELECT 
		COUNT(tax_rule_rate.id) AS total
		FROM 
		tax_rule_rate
		INNER JOIN
		(tax CROSS JOIN tax_description)
		ON
		(tax_rule_rate.id_tax = tax.id AND tax.id = tax_description.id_tax AND tax_description.language_code = :language_code)
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
		tax_rule_rate.id,
		tax.code,
		tax.tax_number,
		tax_description.name,
		tax_rule_rate.rate,
		tax_rule_rate.stacked
		FROM 
		tax_rule_rate
		INNER JOIN
		(tax CROSS JOIN tax_description)
		ON
		(tax_rule_rate.id_tax = tax.id AND tax.id = tax_description.id_tax AND tax_description.language_code = :language_code)
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
		
		// sorting
		$sql.=" ORDER BY tax_rule_rate.sort_order ASC";
		
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
			<cell type="ro"><![CDATA['.$row['code'].']]></cell>
			<cell type="ro"><![CDATA['.$row['tax_number'].']]></cell>		
			<cell type="ro"><![CDATA['.$row['rate'].']]></cell>				
			<cell type="ch">'.$row['stacked'].'</cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	public function actionEdit_tax_rate($container, $id=0)
	{
		$model = new TaxRateForm;
		
		$id = (int)$_POST["id"];		
		
		if ($id) {
			if ($tax_rule_rate = Tbl_TaxRuleRate::model()->findByPk($id)) {
				$model->id = $tax_rule_rate->id;
				$model->rate = $tax_rule_rate->rate;
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_tax_rate',array('model'=>$model, 'container'=>$container));		
	}
	
	public function actionSave_tax_rate()
	{
		$model = new TaxRateForm;
		
		// collect user input data
		if(isset($_POST['TaxRateForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['TaxRateForm'] as $name=>$value)
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
	public function actionXml_list_exception($posStart=0, $count=100, array $filters=array(), array $sort_col=array(),$id_tax_rule=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		$id_tax_rule=(int)$id_tax_rule;
		
		$where=array('tax_rule_exception.id_tax_rule = :id_tax_rule');
		$params=array(':id_tax_rule'=>$id_tax_rule);
		
		// filters
		
		// Customer Type
		if (isset($filters['customer_type']) && !empty($filters['customer_type'])) {
			$where[] = 'customer_type.name LIKE CONCAT(:customer_type,"%")';
			$params[':customer_type']=$filters['customer_type'];
		}
		// Tax Group
		if (isset($filters['tax_group']) && !empty($filters['tax_group'])) {
			$where[] = 'tax_group.name LIKE CONCAT(:tax_group,"%")';
			$params[':tax_group']=$filters['tax_group'];
		}								
		
		$sql = "SELECT 
		COUNT(tax_rule_exception.id) AS total 
		FROM 
		tax_rule_exception 
		LEFT JOIN
		customer_type
		ON
		(tax_rule_exception.id_customer_type = customer_type.id)
		LEFT JOIN 
		tax_group
		ON
		(tax_rule_exception.id_tax_group = tax_group.id)
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
		tax_rule_exception.id,
		customer_type.name AS customer_type_name,
		tax_group.name AS tax_group_name		
		FROM 
		tax_rule_exception 
		LEFT JOIN
		customer_type
		ON
		(tax_rule_exception.id_customer_type = customer_type.id)
		LEFT JOIN 
		tax_group
		ON
		(tax_rule_exception.id_tax_group = tax_group.id)
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
			echo '<row id="'.$row['id'].'" open="1">
			<cell />					
			<cell><![CDATA['.($row['customer_type_name'] ? $row['customer_type_name']:Yii::t('global','LABEL_PROMPT')).']]></cell>
			<cell><![CDATA['.($row['tax_group_name'] ? $row['tax_group_name']:Yii::t('global','LABEL_PROMPT')).']]></cell>';
			
			$params2=array(':language_code'=>Yii::app()->language);
			$sql2 = "SELECT
						tax_rule_exception_rate.id,
						tax_description.name,
						tax_rule_exception_rate.rate
						FROM
						tax_rule
						INNER JOIN
						(tax_rule_rate CROSS JOIN tax_description)
						ON
						(tax_rule.id = tax_rule_rate.id_tax_rule AND tax_rule_rate.id_tax = tax_description.id_tax AND tax_description.language_code = '".Yii::app()->language."')
						LEFT JOIN
						tax_rule_exception_rate
						ON
						(tax_rule_rate.id = tax_rule_exception_rate.id_tax_rule_rate)
						WHERE
						tax_rule_exception_rate.id_tax_rule_exception = '".$row['id']."'
						ORDER BY 
						tax_rule_rate.sort_order ASC";
						

						
						
						
						
			$command2=$connection->createCommand($sql2);
			foreach ($command2->queryAll(true,$params2) as $row2) {
				echo '<row id="'.$row2['id'].'">
				<cell type="ro" />					
				<cell><![CDATA['.$row2['name'].' ('.$row2['rate'].')]]></cell>	
				</row>';
			}
			echo '</row>';
		}
		
		echo '</rows>';
	}
	
	public function actionEdit_exceptions_options($container, $id=0)
	{
		$model = new TaxExceptionsForm;
		
		$id = (int)$_POST["id"];
		$id_tax_rule = (int)$_POST["id_tax_rule"];		
		$model->id_tax_rule = $id_tax_rule;
		if ($id) {
			if ($tax_rule_exception = Tbl_TaxRuleException::model()->findByPk($id)) {
				$model->id = $tax_rule_exception->id;
				$model->id_customer_type = $tax_rule_exception->id_customer_type;
				$model->id_tax_group = $tax_rule_exception->id_tax_group;
				
				foreach ($tax_rule_exception->tbl_tax_rule_exception_rate as $row) {
					$model->tax_rate[$row['id_tax_rule_rate']]['id_tax_rule_exception'] = $row->id_tax_rule_exception;
					$model->tax_rate[$row['id_tax_rule_rate']]['id_tax_rule_rate'] = $row->id_tax_rule_rate;
					$model->tax_rate[$row['id_tax_rule_rate']]['rate'] = $row->rate;
				}	
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_exceptions_options',array('model'=>$model, 'container'=>$container));		
	}
	
	public function actionSave_exceptions_options()
	{
		$model = new TaxExceptionsForm;
		
		// collect user input data
		if(isset($_POST['TaxExceptionsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['TaxExceptionsForm'] as $name=>$value)
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
	 * This is the action to delete a product suggestion
	 */
	public function actionDelete_exception()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_taxe_exception) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_taxe_exception);					
				
				// delete all
				Tbl_TaxRuleException::model()->deleteAll($criteria);
				
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tax_rule_exception=:id'; 
				$criteria->params=array(':id'=>$id_taxe_exception);	
				Tbl_TaxRuleExceptionRate::model()->deleteAll($criteria);						
			}
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
		
		echo '<data>
			<item id="edit_info">
				<Title><![CDATA['.Yii::t('controllers/TaxrulesController','LABEL_INFORMATION').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/TaxrulesController','LABEL_INFORMATION_DESCRIPTION').']]></Description>
			</item>
			<item id="edit_taxes">
				<Title><![CDATA['.Yii::t('controllers/TaxrulesController','LABEL_TAXES').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/TaxrulesController','LABEL_TAXES_DESCRIPTION').']]></Description>
				'.$disabled.'
			</item>
			<item id="edit_exceptions">
				<Title><![CDATA['.Yii::t('controllers/TaxrulesController','LABEL_EXCEPTIONS').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/TaxrulesController','LABEL_EXCEPTIONS_DESCRIPTION').']]></Description>
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