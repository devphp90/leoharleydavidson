<?php

class TagsController extends Controller
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
	 * This is the action to edit or create
	 */ 
	public function actionEdit($container, $id=0)	
	{
		$model=new TagsForm;	
		
		$id = (int)$id;
		if(isset($_POST["id"])){
			$id = (int)$_POST["id"];
		}else{
			$id = (int)$id;
		}
		
		$model->id = $id;
				
		$this->renderPartial('edit',array('model'=>$model, 'container'=>$container));	
	}	
	
	/**
	 * This is the action to save the information
	 */
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
	
	/**
	 * This is the action to delete
	 */
	public function actionDelete()
	{
		// current category
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			foreach ($ids as $id) {			
				Tbl_Tag::model()->deleteByPk($id);
				
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tag=:id_tag'; 
				$criteria->params=array(':id_tag'=>$id); 					
				
				Tbl_TagDescription::model()->deleteAll($criteria);
				Tbl_ProductTag::model()->deleteAll($criteria);					
			}
		}
	}			 

	/**
	 * This is the action to get an XML list
	 */
	public function actionXml_list($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'tag_description.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// sku
		if (isset($filters['alias']) && !empty($filters['alias'])) {
			$where[] = 'tag_description.alias LIKE CONCAT("%",:alias,"%")';
			$params[':alias']=$filters['alias'];
		}													
		
		$sql = "SELECT 
		COUNT(tag.id) AS total 
		FROM 
		tag
		INNER JOIN 
		tag_description 
		ON 
		(tag.id = tag_description.id_tag AND tag_description.language_code = '".Yii::app()->language."') 
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
		tag_description.name,
		tag_description.alias,
		tag_description.description 
		FROM 
		tag 
		INNER JOIN 
		tag_description 
		ON 
		(tag.id = tag_description.id_tag AND tag_description.language_code = '".Yii::app()->language."')  
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tag_description.name ".$direct;
		// sku
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tag_description.alias ".$direct;
		// default
		} else {
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(tag_description.name LIKE CONCAT(:name,'%'),0,1) ASC, tag_description.name ASC";
			} else if (isset($filters['alias']) && !empty($filters['alias'])) { 
				$sql.=" ORDER BY IF(tag_description.alias LIKE CONCAT(:alias,'%'),0,1) ASC, tag_description.alias ASC";

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
			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['alias'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/**
	 * This is the action to get an XML list 
	 */
	public function actionXml_list_tag_description($container, $id=0)
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