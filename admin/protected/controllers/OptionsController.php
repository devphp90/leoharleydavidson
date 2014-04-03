<?php

class OptionsController extends Controller
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

	
	public function actionDelete_option_group()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
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
				
			foreach ($ids as $id) {
				//Verify if options, in this options group, is sold, we archive the options group
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
					// delete groups and options					
					$command_delete_groups_options->execute(array(':id'=>$id));	
				}
			}
		}			
	}
	
	public function actionXml_list_group_description($id=0)
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
		
		$help_hint_path = '/catalog/options/';
		
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
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="tbl_options_group_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->tbl_options_group_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'group-name').'
						<div>'.
						CHtml::activeTextField($model,'tbl_options_group_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>'tbl_options_group_description['.$value->code.'][name]')).'
						<br /><span id="'.'tbl_options_group_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong>'.
						(isset($columns['description']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="tbl_options_group_description['.$value->code.'][description]_maxlength">'.($columns['description']-strlen($model->tbl_options_group_description[$value->code]['description'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'group-description').'
						<div>'.
						CHtml::activeTextArea($model,'tbl_options_group_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'rows' => 3,'maxlength'=>$columns['description'], 'id'=>'tbl_options_group_description['.$value->code.'][description]')).'
						<br /><span id="'.'tbl_options_group_description['.$value->code.'][description]_errorMsg" class="error"></span>
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
	
	public function actionEdit_options_group()
	{
		$model = new OptionsGroupForm;
		
		$id = (int)$_POST['id'];
		
		if ($id) {
			if ($pog = Tbl_OptionsGroup::model()->findByPk($id)) {
				$model->id = $pog->id;
				$model->input_type = $pog->input_type;
				$model->from_to = $pog->from_to;
				$model->maxlength = $pog->maxlength;
				$model->user_defined_qty = $pog->user_defined_qty;
				$model->max_qty = $pog->max_qty;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_options_group',array('model'=>$model));	
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
	
	public function actionXml_list_options_groups($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
		
		$where[] = 'options_group.archive = 0';
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'options_group_description.name LIKE CONCAT(:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(options_group.id) AS total 
		FROM 
		options_group 
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
		options_group 
		INNER JOIN 
		options_group_description 
		ON 
		(options_group.id = options_group_description.id_options_group AND options_group_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		/*if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY options_group_description.name ".$direct;		
		} else {

		}	*/
		
		$sql.=" ORDER BY options_group_description.name ASC";	
		
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
	
	public function actionXml_list_options($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('options.id_options_group=:id_options_group');
		$params=array(':id_options_group'=>$id);
		
		$where[] = 'options.archive = 0';
		
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
		options.active,
		options.sku,
		options.qty,
		options.price_type,
		options.price,
		options.special_price,
		options.special_price_from_date,
		options.special_price_to_date,
		options_description.name 
		FROM 
		options 
		INNER JOIN 
		options_description 
		ON 
		(options.id = options_description.id_options AND options_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY options.sort_order ASC";		
		
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
			<cell type="ro">'.$row['qty'].'</cell>
			<cell type="ro"><![CDATA['.(($row['special_price'] > 0 and (($row['special_price_from_date'] <= date("Y-m-d H:i:s") and $row['special_price_to_date'] > date("Y-m-d H:i:s")) or ($row['special_price_from_date'] == "0000-00-00 00:00:00"))) ? ($row['price_type'] ? $row['special_price'].'%' : Html::nf($row['special_price'])):($row['price_type'] ? $row['price'].'%' : Html::nf($row['price']))).']]></cell>
			<cell type="ch">'.$row['active'].'</cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	
	
	
	public function actionXml_list_options_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		if(isset($_GET['id_tpl_product_option_group'])){
			$id_tpl_product_option_group = (int)$_GET['id_tpl_product_option_group'];
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array('tpl_product_option_group_option.id IS NULL');
			
			// filters
			
			// name
			if (isset($filters['name']) && !empty($filters['name'])) {
				$where[] = 'options_description.name LIKE CONCAT(:name,"%")';
				$params[':name']=$filters['name'];
			}
			
			$where[]='options.active=:active';
			$params[':active']="1";	
			
			$where[] = 'options.archive = 0';				
			
			$sql = "SELECT 
			COUNT(options.id) AS total  
			FROM 
			options 
			INNER JOIN 
			options_description 
			ON 
			(options.id = options_description.id_options AND options_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			tpl_product_option_group_option 
			ON 
			options.id=tpl_product_option_group_option.id_options and tpl_product_option_group_option.id_tpl_product_option_group = " . $id_tpl_product_option_group . "
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
				
			//if this is the first query - get total number of records in the query result
			if($posStart==0){
				/* Select queries return a resultset */
				$command=$connection->createCommand($sql);
				$row = $command->queryRow(true, $params);
				$totalCount = $row['total'];		
			}
			
			
			//create query 
			/*$sql = "SELECT 
			tag.id,
			tag_description.name 
			FROM 
			tag 
			INNER JOIN 
			tag_description 
			ON 
			(tag.id = tag_description.id_tag AND tag_description.language_code = '".Yii::app()->language."') 
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	*/
			
			$sql = "SELECT 
			options.id,
			options_description.name,
			options.active 
			FROM 
			options 
			INNER JOIN 
			options_description 
			ON 
			(options.id = options_description.id_options AND options_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			tpl_product_option_group_option 
			ON 
			options.id=tpl_product_option_group_option.id_options and tpl_product_option_group_option.id_tpl_product_option_group = " . $id_tpl_product_option_group . "
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
			
			
				
			
	
			// name
			if (isset($sort_col[1]) && !empty($sort_col[1])) {	
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY options_description.name ".$direct;		
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
				<cell type="ro"><![CDATA['.(!$row['active'] ? '<span class="innactive">'.$row['name'].'</span>':$row['name']).']]></cell>
				</row>';
			}
			
			echo '</rows>';
			
		}
	}
	
	
	public function actionAdd_options()
	{
		$model = new OptionTemplatesAddTagForm;
		
		// current product
		$id_tpl_product_option_group = $_POST['id_tpl_product_option_group'];
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_options) {				
				$model->id_tpl_product_option_group = $id_tpl_product_option_group;
				$model->id_options = $id_options;
				
				$model->save();		
			}
		}		
	}
	
	public function actionDelete_options()
	{
		// current product
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_options) {
				//Verify if options is sold, we archive the options
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

	
	public function actionSave()
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
	
	public function actionEdit_options($id=0,$id_options_group=0)
	{
		$model = new OptionsForm;
		
		$id = (int)$id;
		$id_options_group = (int)$id_options_group;
		$model->id_options_group = $id_options_group;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id_options_group'; 
		$criteria->params=array(':id_options_group'=>$id_options_group); 
		$options_group = Tbl_OptionsGroup::model()->find($criteria);
		
		$model->input_type = $options_group->input_type;
		
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
		
		$this->renderPartial('edit_options',array('model'=>$model));	
	}

	public function actionXml_list_price_shipping($id_options=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

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
	
	public function actionDelete_region()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_options_price_shipping_region) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_options_price_shipping_region);					
				
				// delete all
				Tbl_OptionsPriceShippingRegion::model()->deleteAll($criteria);						
			}
		}
	}
	public function actionEdit_regions_options($id=0)
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
		
		$this->renderPartial('edit_regions_options',array('model'=>$model));		
	}
	
	public function actionSave_regions_options()
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

	public function actionGet_province_list()
	{
		$model=new OptionsPriceShippingRegionsForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>'state_code'));
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
	 * This is the action to toggle option active status
	 */
	public function actionToggle_active()
	{
		$id = (int)$_POST['id'];
		$active = ($_POST['active']=='true'?1:0);
		
		if ($p = Tbl_Options::model()->findByPk($id)) {
			$p->active = $active;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}	
	
	public function actionXml_list_options_description($id=0)
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

		$help_hint_path = '/catalog/options/';
		
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
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="options_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->options_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'option-name').'
						<div>'.
						CHtml::activeTextField($model,'options_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>'options_description['.$value->code.'][name]')).'
						<br /><span id="options_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong>'.
						(isset($columns['description']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="options_description['.$value->code.'][description]_maxlength">'.($columns['description']-strlen($model->options_description[$value->code]['description'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'option-description').'
						<div>'.
						CHtml::activeTextArea($model,'options_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'rows' => 3,'maxlength'=>$columns['description'], 'id'=>'options_description['.$value->code.'][description]')).'
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
	
	public function actionEdit_ship_only_region_options($id=0)
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
		
		$this->renderPartial('edit_ship_only_region_options',array('model'=>$model));
				
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
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>'state_code'));
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
	
	public function actionEdit_do_not_ship_region_options($id=0)
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
		
		$this->renderPartial('edit_do_not_ship_region_options',array('model'=>$model));	

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
		
		echo Html::generateStateList($id_prefix.'[state_code]', $country, '', '', array('style'=>'min-width:80px;','prompt'=>Yii::t('global','LABEL_PROMPT'),'id'=>'state_code'));
	}
	
	//End Do not Ship into this region
	
	
	
	
	
	
	
	
	
	
		
	
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