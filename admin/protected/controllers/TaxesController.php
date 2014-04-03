<?php

class TaxesController extends Controller
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
			$where[] = 'tax_description.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}		
		
		// code
		if (isset($filters['code']) && !empty($filters['code'])) {
			$where[] = 'tax.code LIKE CONCAT("%",:code,"%")';
			$params[':code']=$filters['code'];
		}						
		
		// tax number
		if (isset($filters['tax_number']) && !empty($filters['tax_number'])) {
			$where[] = 'tax.tax_number LIKE CONCAT("%",:tax_number,"%")';
			$params[':tax_number']=$filters['tax_number'];
		}									
		
		$sql = "SELECT 
		COUNT(tax.id) AS total 
		FROM 
		tax
		INNER JOIN 
		tax_description 
		ON 
		(tax.id = tax_description.id_tax AND tax_description.language_code = :language_code) 
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
		tax.code,
		tax.tax_number,
		tax_description.name
		FROM 
		tax 
		INNER JOIN 
		tax_description 
		ON 
		(tax.id = tax_description.id_tax AND tax_description.language_code = :language_code)  
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tax_description.name ".$direct;
		// code
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tax.code ".$direct;
		// tax number
		} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tax.tax_number ".$direct;
		} else {
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(tax_description.name LIKE CONCAT(:name,'%'),0,1) ASC, tax_description.name ASC";
			} else if (isset($filters['code']) && !empty($filters['code'])) { 
				$sql.=" ORDER BY IF(tax.code LIKE CONCAT(:code,'%'),0,1) ASC, tax.code ASC";
			} else if (isset($filters['tax_number']) && !empty($filters['tax_number'])) { 
				$sql.=" ORDER BY IF(tax.tax_number LIKE CONCAT(:tax_number,'%'),0,1) ASC, tax.tax_number ASC";
				
			} else {
				$sql.=" ORDER BY tax.id ASC";
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
			<cell type="ro"><![CDATA['.$row['code'].']]></cell>
			<cell type="ro"><![CDATA['.$row['tax_number'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	

	/**
	 * This is the action to edit or create a product
	 */ 
	public function actionEdit($container, $id=0)	
	{
		$model=new TaxesForm;	
		
		$id = (int)$id;

		if ($id) { 
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 		
			
			if (!Tbl_Tax::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		
			
			$model->id = $id;	
		}		
				
		$this->renderPartial('edit',array('model'=>$model, 'container'=>$container));	
	}
	
	public function actionEdit_info($container, $containerJS)
	{
		$model = new TaxesForm;
		
		$id = (int)$_POST['id'];
		
		if ($id) { 
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 		
			
			if (!Tbl_Tax::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		
		}		
		
		$this->renderPartial('edit_info',array('id'=>$id, 'container'=>$container, 'containerJS'=>$containerJS));	
	}
	
	public function actionEdit_info_options($container, $id=0)
	{
		$model = new TaxesForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($tax = Tbl_Tax::model()->findByPk($id)) {
				$model->id = $tax->id;
				$model->code = $tax->code;
				$model->tax_number = $tax->tax_number;				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_info_options',array('model'=>$model, 'container'=>$container));	
	}		
	
	/**
	 * This is the action to save the information section	 
	 */
	public function actionSave()
	{
		$model = new TaxesForm;
		
		
		// collect user input data
		if(isset($_POST['TaxesForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['TaxesForm'] as $name=>$value)
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
				Tbl_Tax::model()->deleteByPk($id);
				
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tax=:id_tax'; 
				$criteria->params=array(':id_tax'=>$id); 					
				
				Tbl_TaxDescription::model()->deleteAll($criteria);
				Tbl_TaxRuleRate::model()->deleteAll($criteria);			
			}
		}
	}			 
		
	/**
	 * This is the action to get an XML list of the product menu
	 */
	/** 
	 *
	 */
	public function actionXml_list_tax_description($container, $id=0)
	{
		$model = new TaxesForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($tax = Tbl_Tax::model()->findByPk($id)) {							
				// grab description information 
				foreach ($tax->tbl_tax_description as $row) {
					$model->tax_description[$row->language_code]['name'] = $row->name;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_TaxDescription::tableName());	
		
		$help_hint_path = '/settings/taxes/taxes/';	
		
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
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_tax_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->tax_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'name').'
						<div>'.
						CHtml::activeTextField($model,'tax_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>$container.'_tax_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_tax_description['.$value->code.'][name]_errorMsg" class="error"></span>
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
	
	public function actionEdit_options($container, $id=0)
	{
		$model = new TaxesForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($tax = Tbl_Tax::model()->findByPk($id)) {
				$model->id = $tax->id;
				$model->code = $tax->code;	
				$model->tax_number = $tax->tax_number;					
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_options',array('model'=>$model, 'container'=>$container));	
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