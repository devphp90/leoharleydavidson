<?php

class VariantstemplatesController extends Controller
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
	 * This is the action to get an XML list of template
	 */
	public function actionXml_list_option_category($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
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
			$where[] = 'tpl_product_variant_category.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(tpl_product_variant_category.id) AS total 
		FROM 
		tpl_product_variant_category  
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
		tpl_product_variant_category.id,
		tpl_product_variant_category.name 
		FROM 
		tpl_product_variant_category 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';	
			
			$sql .= " ORDER BY 
			tpl_tag_group.name ".$direct;
		}else{
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(tpl_product_variant_category.name LIKE CONCAT(:name,'%'),0,1) ASC, tpl_product_variant_category.name ASC";
			} else {
				$sql.=" ORDER BY tpl_product_variant_category.name ASC";
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
			</row>';
		}
		
		echo '</rows>';
	}
	
	public function actionDelete_template()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			// prepare delete groups and options
			$command_delete_groups_options=$connection->createCommand('DELETE FROM 
			tpl_product_variant_category,
			tpl_product_variant_group,
			tpl_product_variant_group_description,
			tpl_product_variant_group_option,
			tpl_product_variant_group_option_description
			USING 
			tpl_product_variant_category 
			LEFT JOIN 
			tpl_product_variant_group 
			ON
			(tpl_product_variant_category.id = tpl_product_variant_group.id_tpl_product_variant_category) 
			LEFT JOIN 
			tpl_product_variant_group_description 
			ON
			(tpl_product_variant_group.id = tpl_product_variant_group_description.id_tpl_product_variant_group) 
			LEFT JOIN 
			tpl_product_variant_group_option 
			ON
			(tpl_product_variant_group.id = tpl_product_variant_group_option.id_tpl_product_variant_group)
			LEFT JOIN 
			tpl_product_variant_group_option_description 
			ON
			(tpl_product_variant_group_option.id = tpl_product_variant_group_option_description.id_tpl_product_variant_group_option) 
			WHERE 
			tpl_product_variant_category.id=:id');					
			
			foreach ($ids as $id) {
				// delete groups and options					
				$command_delete_groups_options->execute(array(':id'=>$id));																		
			}
		}			
	}
	
	public function actionAdd_category_template()
	{
		$model = new VariantCategoryTemplateForm;
		
		$id = (int)$_POST['id'];
		
		if($id){
			if ($option_template = Tbl_TplProductVariantCategory::model()->findByPk($id)) {
				$model->id = $option_template->id;
				$model->name = $option_template->name;	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}
		 		
		$this->renderPartial('add_category_template',array('model'=>$model));				
	}
	
	public function actionSave_category_template()
	{
		$model = new VariantCategoryTemplateForm;
		
		// collect user input data
		if(isset($_POST['VariantCategoryTemplateForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['VariantCategoryTemplateForm'] as $name=>$value)
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
	
	public function actionDelete_option_group()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			
			
			// prepare delete groups and options
			$command_delete_groups_options=$connection->createCommand('DELETE FROM 
			tpl_product_variant_group,
			tpl_product_variant_group_description,
			tpl_product_variant_group_option,
			tpl_product_variant_group_option_description
			USING 
			tpl_product_variant_group 
			LEFT JOIN 
			tpl_product_variant_group_description 
			ON
			(tpl_product_variant_group.id = tpl_product_variant_group_description.id_tpl_product_variant_group) 
			LEFT JOIN 
			tpl_product_variant_group_option 
			ON
			(tpl_product_variant_group.id = tpl_product_variant_group_option.id_tpl_product_variant_group)
			LEFT JOIN 
			tpl_product_variant_group_option_description 
			ON
			(tpl_product_variant_group_option.id = tpl_product_variant_group_option_description.id_tpl_product_variant_group_option) 
			WHERE 
			tpl_product_variant_group.id=:id');					
			
			foreach ($ids as $id) {
				// delete groups and options					
				$command_delete_groups_options->execute(array(':id'=>$id));																		
			}
		}			
	}
	
	public function actionXml_list_option_group_description($id=0)
	{
		$model = new VariantGroupTemplateForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($tpl_product_variant_group = Tbl_TplProductVariantGroup::model()->findByPk($id)) {							
				// grab description information 
				foreach ($tpl_product_variant_group->tbl_tpl_product_variant_group_description as $row) {
					$model->tpl_product_variant_group_description[$row->language_code]['name'] = $row->name;
					$model->tpl_product_variant_group_description[$row->language_code]['description'] = $row->description;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$columns = Html::getColumnsMaxLength(Tbl_TplProductVariantGroupDescription::tableName());
		
		$help_hint_path = '/catalog/product-variants-templates/';
		
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
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="tpl_product_variant_group_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->tpl_product_variant_group_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'group-name').'
						<div>'.
						CHtml::activeTextField($model,'tpl_product_variant_group_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>'tpl_product_variant_group_description['.$value->code.'][name]')).'
						<br /><span id="'.'tpl_product_variant_group_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong>'.
						(isset($columns['description']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="tpl_product_variant_group_description['.$value->code.'][description]_maxlength">'.($columns['description']-strlen($model->tpl_product_variant_group_description[$value->code]['description'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'group-description').'
						<div>'.
						CHtml::activeTextArea($model,'tpl_product_variant_group_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'rows' => 6,'maxlength'=>$columns['description'], 'id'=>'tpl_product_variant_group_description['.$value->code.'][description]')).'
						<br /><span id="'.'tpl_product_variant_group_description['.$value->code.'][description]_errorMsg" class="error"></span>
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
	
	public function actionEdit_variants_group($id_tpl_product_variant_category=0)
	{
		$model = new VariantGroupTemplateForm;
		
		$id = (int)$_POST['id'];
		$id_tpl_product_variant_category = (int)$_GET['id_tpl_product_variant_category'];
		
		$model->id_tpl_product_variant_category = $id_tpl_product_variant_category;
		
		if ($id) {
			if ($pog = Tbl_TplProductVariantGroup::model()->findByPk($id)) {
				$model->id = $pog->id;
				$model->id_tpl_product_variant_category = $pog->id_tpl_product_variant_category;
				$model->input_type = $pog->input_type;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_variants_group',array('model'=>$model));	
	}
	
	public function actionSave_option_group()
	{
		$model = new VariantGroupTemplateForm;
		
		// collect user input data
		if(isset($_POST['VariantGroupTemplateForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['VariantGroupTemplateForm'] as $name=>$value)
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
	
	public function actionXml_list_option_groups($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('tpl_product_variant_group.id_tpl_product_variant_category=:id');
		$params=array(':id'=>$id);
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'tpl_product_variant_group_description.name LIKE CONCAT(:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(tpl_product_variant_group.id) AS total 
		FROM 
		tpl_product_variant_group 
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
		tpl_product_variant_group.id,
		tpl_product_variant_group_description.name,
		tpl_product_variant_group.input_type
		FROM 
		tpl_product_variant_group 
		INNER JOIN 
		tpl_product_variant_group_description 
		ON 
		(tpl_product_variant_group.id = tpl_product_variant_group_description.id_tpl_product_variant_group AND tpl_product_variant_group_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		/*if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tpl_product_variant_group_description.name ".$direct;		
		} else {

			$sql.=" ORDER BY tpl_product_variant_group.sort_order ASC";
		}	*/
		
		$sql.=" ORDER BY tpl_product_variant_group_description.name ASC";	
		
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
	
	public function actionXml_list_option_group_options($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('tpl_product_variant_group_option.id_tpl_product_variant_group=:id_tpl_product_variant_group');
		$params=array(':id_tpl_product_variant_group'=>$id);
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'tpl_product_variant_group_option_description.name LIKE CONCAT(:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(tpl_product_variant_group_option.id) AS total 
		FROM 
		tpl_product_variant_group_option 
		INNER JOIN 
		tpl_product_variant_group_option_description 
		ON 
		(tpl_product_variant_group_option.id = tpl_product_variant_group_option_description.id_tpl_product_variant_group_option AND tpl_product_variant_group_option_description.language_code = '".Yii::app()->language."') 
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
		tpl_product_variant_group_option_description.name 
		FROM 
		tpl_product_variant_group_option 
		INNER JOIN 
		tpl_product_variant_group_option_description 
		ON 
		(tpl_product_variant_group_option.id = tpl_product_variant_group_option_description.id_tpl_product_variant_group_option AND tpl_product_variant_group_option_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		/*if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tpl_product_variant_group_option_description.name ".$direct;		
		} else {

			$sql.=" ORDER BY tpl_product_variant_group_option.sort_order ASC";
		}*/
		
		$sql.=" ORDER BY tpl_product_variant_group_option_description.name ASC";		
		
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
	
	public function actionDelete_option_group_option()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection					
			
			foreach ($ids as $id) {			
								
				// delete all
				Tbl_TplProductVariantGroupOption::model()->deleteByPk($id);	

				$criteria=new CDbCriteria; 
				$criteria->condition='id_tpl_product_variant_group_option=:id_tpl_product_variant_group_option'; 
				$criteria->params=array(':id_tpl_product_variant_group_option'=>$id); 		
				
				Tbl_TplProductVariantGroupOptionDescription::model()->deleteAll($criteria);				
			}
		}			
	}		
	
	public function actionXml_list_option_group_option_description($id=0)
	{		
		$model = new VariantOptionTemplateForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($pvgo = Tbl_TplProductVariantGroupOption::model()->findByPk($id)) {							
				// grab description information 
				foreach ($pvgo->tbl_tpl_product_variant_group_option_description as $row) {
					$model->tpl_product_variant_group_option_description[$row->language_code]['name'] = $row->name;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}
		
		$columns = Html::getColumnsMaxLength(Tbl_TplProductVariantGroupOptionDescription::tableName());
		
		$help_hint_path = '/catalog/product-variants-templates/';
		
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
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="tpl_product_variant_group_option_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->tpl_product_variant_group_option_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'option-name').'
						<div>'.
						CHtml::activeTextField($model,'tpl_product_variant_group_option_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>'tpl_product_variant_group_option_description['.$value->code.'][name]')).'
						<br /><span id="tpl_product_variant_group_option_description['.$value->code.'][name]_errorMsg" class="error"></span>
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
	
	public function actionSave_option_option()
	{
		$model = new VariantOptionTemplateForm;
		
		// collect user input data
		if(isset($_POST['VariantOptionTemplateForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['VariantOptionTemplateForm'] as $name=>$value)
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
	
	public function actionEdit_variants_group_option()
	{
		$model = new VariantOptionTemplateForm;
		
		$id = (int)$_POST['id'];
		$id_tpl_product_variant_group = (int)$_POST['id_tpl_product_variant_group'];
		
		if (!$pvg = Tbl_TplProductVariantGroup::model()->findByPk($id_tpl_product_variant_group)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));		
		}
		
		$model->id_tpl_product_variant_group = $id_tpl_product_variant_group;
		
		if ($id) {
			if ($pvgo = Tbl_TplProductVariantGroupOption::model()->findByPk($id)) {
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
		
		$this->renderPartial('edit_variants_group_option',array('model'=>$model, 'input_type'=>$pvg->input_type));	
	}
	
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
				echo Yii::t('products','ERROR_ALLOWED_IMAGES');				
				exit;
			} else {	
				// original file renamed
				$original = md5($targetFile.time()).'.'.$ext;	
				$filename = md5($original).'.jpg';				
			
				$image = new SimpleImage();
				if (!$image->load($tempFile)) {
					// delete
					$product_image->delete();
					
					echo Yii::t('products','ERROR_LOAD_IMAGE_FAILED');
					exit;
				}			
				
				$width = $image->getWidth();
				$height = $image->getHeight();
				
				if (!$width || !$height) {
					// delete
					$product_image->delete();
					
					echo Yii::t('products','ERROR_LOAD_IMAGE_FAILED');
					exit;					
				}
															
				// save image
				if (!$image->resize(20,20)) {
					// delete
					$product_image->delete();								
					
					echo Yii::t('products', 'ERROR_RESIZE_ZOOM_FAILED');						
					exit;		
				} else if (!$image->save($targetPath.'swatch/'.$filename)) {
					echo Yii::t('products', 'ERROR_SAVE_ZOOM_FAILED');						
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