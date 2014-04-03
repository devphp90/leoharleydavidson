<?php

class TagstemplatesController extends Controller
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
	public function actionXml_list_tag_templates($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
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
			$where[] = 'tpl_tag_group.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}					
		
		$sql = "SELECT 
		COUNT(tpl_tag_group.id) AS total 
		FROM 
		tpl_tag_group  
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
		tpl_tag_group.id,
		tpl_tag_group.name 
		FROM 
		tpl_tag_group 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';	
			
			$sql .= " ORDER BY 
			tpl_tag_group.name ".$direct;
		}else{
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(tpl_tag_group.name LIKE CONCAT(:name,'%'),0,1) ASC, tpl_tag_group.name ASC";
			} else {
				$sql.=" ORDER BY tpl_tag_group.name ASC";
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
	
	/**
	 * This is the action to get an XML list of tag of a template
	 */
	public function actionXml_list_tag($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
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
		INNER JOIN 
		tag
		ON 
		(tpl_tag.id_tag = tag.id) 
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
		INNER JOIN 
		tag
		ON 
		(tpl_tag.id_tag = tag.id)
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		

		// sorting
		
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';	
		}else{
			$direct = 'ASC';
		}	

		$sql.=" ORDER BY tag_description.name " . $direct;
		
		
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
			</row>';
		}
		
		echo '</rows>';
	}	
	
	/**
	 * This is the action to get an XML list of tag of a template
	 */
	public function actionXml_list_tag_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		if(isset($_GET['id_tpl_tag_group'])){
			$id_tpl_tag_group = (int)$_GET['id_tpl_tag_group'];
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array('tpl_tag.id_tag IS NULL');
			$params=array();
			//$params=array(':id_tpl_tag_group'=>$id_tpl_tag_group);
			
			// filters
			
			// name
			if (isset($filters['name']) && !empty($filters['name'])) {
				$where[] = 'tag_description.name LIKE CONCAT(:name,"%")';
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
			tpl_tag 
			ON 
			tag.id=tpl_tag.id_tag and tpl_tag.id_tpl_tag_group = " . $id_tpl_tag_group . "
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
			tag.id,
			tag_description.name
			FROM 
			tag 
			INNER JOIN 
			tag_description 
			ON 
			(tag.id = tag_description.id_tag AND tag_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			tpl_tag 
			ON 
			tag.id=tpl_tag.id_tag and tpl_tag.id_tpl_tag_group = " . $id_tpl_tag_group . "
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
			
			
				
			
	
			// name
			if (isset($sort_col[1]) && !empty($sort_col[1])) {	
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
				
				$sql.=" ORDER BY tag_description.name ".$direct;		
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
	
	public function actionAdd_tag()
	{
		$model = new TagsTemplatesAddTagForm;
		
		// current product
		$id_tpl_tag_group = $_POST['id_tpl_tag_group'];
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_tag) {				
				$model->id_tpl_tag_group = $id_tpl_tag_group;
				$model->id_tag = $id_tag;
				
				$model->save();		
			}
		}		
	}
	
	
	public function actionDelete_template()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			foreach ($ids as $id) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id); 					
				
				// delete all
				Tbl_TplTagGroup::model()->deleteAll($criteria);	

				$criteria2=new CDbCriteria; 
				$criteria2->condition='id_tpl_tag_group=:id_tpl_tag_group'; 
				$criteria2->params=array(':id_tpl_tag_group'=>$id); 		
				
				Tbl_TplTag::model()->deleteAll($criteria2);			
																																			
			}
		}			
	}
	
	public function actionDelete_tag()
	{
		// current product
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_tag) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id_tag'; 
				$criteria->params=array(':id_tag'=>$id_tag);					
				
				// delete all
				Tbl_Tag::model()->deleteAll($criteria);			
			}
		}		
	}
	
	public function actionRemove_tag()
	{
		// current product
		$id_tpl_tag_group = $_POST['id_tpl_tag_group'];
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_tag) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tpl_tag_group=:id_tpl_tag_group AND id_tag=:id_tag'; 
				$criteria->params=array(':id_tpl_tag_group'=>$id_tpl_tag_group,':id_tag'=>$id_tag);					
				
				// delete all
				Tbl_TplTag::model()->deleteAll($criteria);			
			}
		}		
	}		
	
	public function actionAdd_tag_template()
	{
		$model = new ProductsAddTagTemplateForm;
		
		$id = (int)$_POST['id'];
		
		if($id){
			if ($tag_group = Tbl_TplTagGroup::model()->findByPk($id)) {
				$model->id = $tag_group->id;
				$model->name = $tag_group->name;	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}
		 		
		$this->renderPartial('add_tag_template',array('model'=>$model));				
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

	public function actionSave()
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
	
	public function actionXml_list_tag_description($id=0)
	{
		$model = new TagsForm;
		
		$id = (int)$id;
		
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
				<![CDATA['.(!$i?'<input id="'.$container.'_id" name="TagsForm[id]" value="'.$id.'" type="hidden">':'').'
					<div style="width:100%; height:100%; overflow:auto;" class="div_'.$container.'">	
					<div style="padding:10px;">			
					<div class="row">
						<strong>'.Yii::t('global','LABEL_NAME').'</strong>'.
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="tag_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->tag_description[$value->code]['name'])).'</span>)</em>':'').'
						<div>'.
						CHtml::activeTextField($model,'tag_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'onblur'=>'rewrite_alias($(this).attr("id"),"tag_description['.$value->code.'][alias]");', 'id'=>'tag_description['.$value->code.'][name]')).'
						<br /><span id="tag_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					<div class="row">
						<strong>'.Yii::t('global','LABEL_ALIAS').'</strong> ('.Yii::t('global','LABEL_ALIAS_CHARACTERS_ALLOWED').'):'.
						(isset($columns['alias']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="tag_description['.$value->code.'][alias]_maxlength">'.($columns['alias']-strlen($model->tag_description[$value->code]['alias'])).'</span>)</em>':'').'
						<div>'.
						CHtml::activeTextField($model,'tag_description['.$value->code.'][alias]',array('style' => 'width: 98%;','maxlength'=>$columns['alias'], 'onkeyup'=>'rewrite_alias($(this).attr("id"),"");', 'id'=>'tag_description['.$value->code.'][alias]')).'
						<br /><span id="tag_description['.$value->code.'][alias]_errorMsg" class="error"></span>
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