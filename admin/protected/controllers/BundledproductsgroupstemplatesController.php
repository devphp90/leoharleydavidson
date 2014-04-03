<?php

class BundledproductsgroupstemplatesController extends Controller
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
			$where[] = 'tpl_product_bundled_product_category.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(tpl_product_bundled_product_category.id) AS total 
		FROM 
		tpl_product_bundled_product_category  
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
		tpl_product_bundled_product_category.id,
		tpl_product_bundled_product_category.name 
		FROM 
		tpl_product_bundled_product_category 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';	
			
			$sql .= " ORDER BY 
			tpl_product_bundled_product_category.name ".$direct;
		}else{
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(tpl_product_bundled_product_category.name LIKE CONCAT(:name,'%'),0,1) ASC, tpl_product_bundled_product_category.name ASC";
			} else {
				$sql.=" ORDER BY tpl_product_bundled_product_category.name ASC";
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
			
			// prepare delete
			$command=$connection->createCommand('DELETE FROM 
			tpl_product_bundled_product_category,
			tpl_product_bundled_product_group,
			tpl_product_bundled_product_group_description
			USING 
			tpl_product_bundled_product_category 
			LEFT JOIN 
			tpl_product_bundled_product_group 
			ON
			(tpl_product_bundled_product_category.id = tpl_product_bundled_product_group.id_tpl_product_bundled_product_category) 
			LEFT JOIN 
			tpl_product_bundled_product_group_description 
			ON
			(tpl_product_bundled_product_group.id = tpl_product_bundled_product_group_description.id_tpl_product_bundled_product_group) 
			WHERE 
			tpl_product_bundled_product_category.id=:id');					
			
			foreach ($ids as $id) {
				// delete
				$command->execute(array(':id'=>$id));																		
			}
		}			
	}
	
	public function actionAdd_category_template()
	{
		$model = new BundledProductsTemplateForm;
		
		$id = (int)$_POST['id'];
		
		if($id){
			if ($template = Tbl_TplProductBundledProductCategory::model()->findByPk($id)) {
				$model->id = $template->id;
				$model->name = $template->name;	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}
		 		
		$this->renderPartial('add_category_template',array('model'=>$model));				
	}
	
	public function actionSave_category_template()
	{
		$model = new BundledProductsTemplateForm;
		
		// collect user input data
		if(isset($_POST['BundledProductsTemplateForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['BundledProductsTemplateForm'] as $name=>$value)
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
	
	public function actionDelete_group()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection

			// prepare delete 
			$command=$connection->createCommand('DELETE FROM 
			tpl_product_bundled_product_group,
			tpl_product_bundled_product_group_description
			USING 
			tpl_product_bundled_product_group 
			LEFT JOIN 
			tpl_product_bundled_product_group_description 
			ON
			(tpl_product_bundled_product_group.id = tpl_product_bundled_product_group_description.id_tpl_product_bundled_product_group) 
			WHERE 
			tpl_product_bundled_product_group.id=:id');					
			
			foreach ($ids as $id) {
				// delete		
				$command->execute(array(':id'=>$id));																		
			}
		}			
	}
	
	public function actionXml_list_group_description($id=0)
	{
		$model = new BundledProductsGroupTemplateForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($tpl_product_option_group = Tbl_TplProductBundledProductGroup::model()->findByPk($id)) {							
				// grab description information 
				foreach ($tpl_product_option_group->tbl_tpl_product_bundled_product_group_description as $row) {
					$model->product_bundled_product_group_description[$row->language_code]['name'] = $row->name;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$columns = Html::getColumnsMaxLength(Tbl_TplProductBundledProductGroupDescription::tableName());
		
		$help_hint_path = '/catalog/bundled-products-groups-templates/';
		
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
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="product_bundled_product_group_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->product_bundled_product_group_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'group-name').'
						<div>'.
						CHtml::activeTextField($model,'product_bundled_product_group_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>'product_bundled_product_group_description['.$value->code.'][name]')).'
						<br /><span id="'.'product_bundled_product_group_description['.$value->code.'][name]_errorMsg" class="error"></span>
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
	
	public function actionEdit_group($id_tpl_product_bundled_product_category=0)
	{
		$model = new BundledProductsGroupTemplateForm;
		
		$id = (int)$_POST['id'];
		$id_tpl_product_bundled_product_category = (int)$_GET['id_tpl_product_bundled_product_category'];
		
		$model->id_tpl_product_bundled_product_category = $id_tpl_product_bundled_product_category;
		
		if ($id) {
			if ($pog = Tbl_TplProductBundledProductGroup::model()->findByPk($id)) {							
				$model->id = $pog->id;
				$model->id_tpl_product_bundled_product_category = $pog->id_tpl_product_bundled_product_category;
				$model->input_type = $pog->input_type;
				$model->required = $pog->required;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_group',array('model'=>$model));	
	}
	
	public function actionSave_group()
	{
		$model = new BundledProductsGroupTemplateForm;
		
		// collect user input data
		if(isset($_POST['BundledProductsGroupTemplateForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['BundledProductsGroupTemplateForm'] as $name=>$value)
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
	
	public function actionXml_list_groups($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('tpl_product_bundled_product_group.id_tpl_product_bundled_product_category=:id');
		$params=array(':id'=>$id,':language_code'=>Yii::app()->language);
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'tpl_product_bundled_product_group_description.name LIKE CONCAT(:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(tpl_product_bundled_product_group.id) AS total 
		FROM 
		tpl_product_bundled_product_group 
		INNER JOIN 
		tpl_product_bundled_product_group_description 
		ON 
		(tpl_product_bundled_product_group.id = tpl_product_bundled_product_group_description.id_tpl_product_bundled_product_group AND tpl_product_bundled_product_group_description.language_code = :language_code) 
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
		tpl_product_bundled_product_group.id,
		tpl_product_bundled_product_group_description.name,
		tpl_product_bundled_product_group.input_type,
		tpl_product_bundled_product_group.required
		FROM 
		tpl_product_bundled_product_group 
		INNER JOIN 
		tpl_product_bundled_product_group_description 
		ON 
		(tpl_product_bundled_product_group.id = tpl_product_bundled_product_group_description.id_tpl_product_bundled_product_group AND tpl_product_bundled_product_group_description.language_code = :language_code) 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		/*if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tpl_product_option_group_description.name ".$direct;		
		} else {

			$sql.=" ORDER BY tpl_product_option_group.sort_order ASC";
		}	*/
		
		$sql.=" ORDER BY tpl_product_bundled_product_group.sort_order ASC";	
		
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
			<cell type="ro"><![CDATA['.($row['required'] ? Yii::t('global','LABEL_YES'):Yii::t('global','LABEL_NO')).']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/**
	 * This is the action to save product variant group order
	 */
	public function actionSave_groups_sort_order($id=0)
	{			
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		// prepare 
		$command=$connection->createCommand('UPDATE
		tpl_product_bundled_product_group
		SET
		sort_order=:sort_order
		WHERE 
		id=:id');				

		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_tpl_product_bundled_product_group) {
				$command->execute(array(':id'=>$id_tpl_product_bundled_product_group,':sort_order'=>$i));				
				++$i;
			}
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