<?php

class CategoriesController extends Controller
{
	
	public $array_id_category_all_child = array();

	
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
	public function actionXml_list()
	{		
		// xml header
		header("content-type: text/xml");	
		
		$eol = "\r\n";
		
		echo '<?xml version="1.0" encoding="utf-8"?>'.$eol;
		echo '<rows>'.$eol;		
		
		$this->get_categories_treegrid();		
		
		echo '</rows>'.$eol;
	}		
	
	/**
	 * This is a function to get a list of the categories and sub categories recursively
	 */
	public function get_categories_treegrid($id_parent=0)
	{
		$id_parent = (int)$id_parent;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent'; 
		$criteria->params=array(':id_parent'=>$id_parent); 				
		$criteria->order='sort_order ASC';	
		
		$eol = "\r\n";
			
		foreach (Tbl_Category::model()->getDescription()->findAll($criteria) as $row) {		
			$criteria2=new CDbCriteria; 
			$criteria2->condition='id_parent=:id_parent'; 
			$criteria2->params=array(':id_parent'=>$row->id); 				
			$criteria2->order='sort_order ASC';			
			
			$child = Tbl_Category::model()->count($criteria2) ? 1:0;	
		
			echo '<row id="'.$row->id.'" '.($row['active']?'':'class="innactive"').'>
			<cell />
			<cell><![CDATA['.CHtml::encode($row->tbl_category_description[0]->name).']]></cell>
			<cell><![CDATA['.(($row->start_date != '0000-00-00 00:00:00') ? $row->start_date:'').']]></cell>
			<cell><![CDATA['.(($row->end_date != '0000-00-00 00:00:00') ? $row->end_date:'').']]></cell>
			<cell>'.$row->featured.'</cell>
			<cell>'.$row->active.'</cell>';			
			
			if ($child) { $this->get_categories_treegrid($row->id); }
			
			echo '</row>'.$eol;
		}			
		
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
			
			if (!Tbl_Category::model()->count($criteria)) {
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
		$id_category = (int)$_POST['id_category'];
		
		if ($id_combo) { 
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id_category); 		
			
			if (!Tbl_Category::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}			
		}
						
		$this->renderPartial($id,array('id'=>$id_category,'container'=>$container,'containerJS'=>$containerJS));	
	}		
	
	public function actionEdit_info_options($container, $id=0)
	{
		$model = new CategoriesForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($category = Tbl_Category::model()->findByPk($id)) {
				$model->id = $category->id;
				$model->id_parent = $category->id_parent;
				$model->start_date = ($category->start_date != '0000-00-00 00:00:00') ? $category->start_date:'';
				$model->end_date = ($category->end_date != '0000-00-00 00:00:00') ? $category->end_date:'';
				$model->featured = $category->featured;
				$model->display_type = $category->display_type;
				$model->product_sort_by = $category->product_sort_by;
				$model->price_increment = $category->price_increment;
				$model->active = $category->active;	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}else{
			$model->product_sort_by = Yii::app()->params['product_sort_by'];	
		}
		
		$this->renderPartial('edit_info_options',array('model'=>$model, 'container'=>$container));	
	}		
	
	/**
	 * This is the action to save the information section	 
	 */
	public function actionSave()
	{
		$model = new CategoriesForm;
		
		$id_parent = (int)$_POST['id_parent'];
		$model->id_parent = $id_parent;
		
		// collect user input data
		if(isset($_POST['CategoriesForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['CategoriesForm'] as $name=>$value)
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
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id); 		
				
				if (Tbl_Category::model()->count($criteria)) {
					
					//Delete parent and child
					$array=array();
					$array[$id]=$id;
					$this->get_categories($array,$id);		
					
					foreach($array as $key=>$value){
						Tbl_Category::model()->deleteByPk($key);
						$criteria=new CDbCriteria; 
						$criteria->condition='id_category=:id_category'; 
						$criteria->params=array(':id_category'=>$key); 					
						
						Tbl_CategoryDescription::model()->deleteAll($criteria);
						Tbl_ProductCategory::model()->deleteAll($criteria);	
					}
				}			
			}
		}
	}		 
	
	/**
	 * This is the action to toggle featured
	 */
	public function actionToggle_featured()
	{
		$id = (int)$_POST['id'];
		$state = ($_POST['state']=='true'?1:0);
		
		if ($category = Tbl_Category::model()->findByPk($id)) {
			$category->featured = $state;
			if (!$category->save()) {
				throw new CException('unable to save');	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}		
	
	/**
	 * This is the action to toggle active
	 */
	public function actionToggle_active()
	{
		$id = (int)$_POST['id'];
		$state = ($_POST['state']=='true'?1:0);
		
		if ($category = Tbl_Category::model()->findByPk($id)) {
			$category->active = $state;
			if (!$category->save()) {
				throw new CException('unable to save');	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}		
		
	public function actionSave_sort_order()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id = (int)$_POST['id'];
		$id_parent = (int)$_POST['id_parent'];
		$index = (int)$_POST['index'];
		
		$criteria = new CDbCriteria;
		$criteria2 = new CDbCriteria; 

		if ($category = Tbl_Category::model()->findByPk($id)) {	
		
			// check if alias is already in use in this category			
			$command=$connection->createCommand('SELECT 
			category.id,
			category_description.name 
			FROM 
			category 
			INNER JOIN
			category_description
			ON
			(category.id = category_description.id_category AND category_description.alias=:alias AND category_description.language_code=:language_code)
			WHERE 
			category.id_parent=:id_parent 
			AND 
			category.id!=:id');			
		
			foreach ($category->tbl_category_description as $row) {
				// check if alias is already in use										
				$row_check = $command->queryRow(true, array(':id'=>$category->id,':id_parent'=>$id_parent,':alias'=>$row->alias,':language_code'=>$row->language_code));
				
				// if in use, tell us by whom
				if ($row_check['id']) {												
					echo Yii::t('global','ERROR_ALIAS_EXIST').' '.$row_check['name'];
					exit;
				}						
			}
			
			// check if category has product whitout child			
			$criteria->condition='id_parent=:id_parent'; 
			$criteria->params=array(':id_parent'=>$id_parent); 
			
			$criteria2->condition='id_category=:id_category'; 
			$criteria2->params=array(':id_category'=>$id_parent); 
	
			if (!Tbl_Category::model()->count($criteria) and Tbl_ProductCategory::model()->count($criteria2)) {	
				echo Yii::t('global','ERROR_ALREADY_PRODUCT_IN_CATEGORY');
				exit;				
			}		
			
			// Find every parent of the current category
			$array_id_parent_start_all = array();
			$temp_id_cat = $this->get_category_parent($id);
			if($temp_id_cat){
				$array_id_parent_start_all[$temp_id_cat] = $temp_id_cat;
			}
			while($temp_id_cat){
				$temp_id_cat = $this->get_category_parent($temp_id_cat);
				if($temp_id_cat){
					$array_id_parent_start_all[$temp_id_cat] = $temp_id_cat;
				}
			}
			
			// Find every parent of the new position of the category
			$array_id_parent_end_all = array();
			if($id_parent){
				$array_id_parent_end_all[$id_parent] = $id_parent;
			}
			$temp_id_cat = $this->get_category_parent($id_parent);
			if($temp_id_cat){
				$array_id_parent_end_all[$temp_id_cat] = $temp_id_cat;
			}
			while($temp_id_cat){
				$temp_id_cat = $this->get_category_parent($temp_id_cat);
				if($temp_id_cat){
					$array_id_parent_end_all[$temp_id_cat] = $temp_id_cat;
				}
			}
			
			// Keep category who must be modified
			$array_id_parent_start = array_diff($array_id_parent_start_all, $array_id_parent_end_all);
			$array_id_parent_end = array_diff($array_id_parent_end_all, $array_id_parent_start_all);
			
			//echo print_r($array_id_parent_start,1);
			//echo print_r($array_id_parent_end,1);
			
			if(sizeof($array_id_parent_start) or sizeof($array_id_parent_end)){
				// Find the list of product in the categoy that we drag
				$array_id_product_current_category = array();
				$criteria->condition='id_category=:id_category'; 
				$criteria->params=array(':id_category'=>$id); 				
				foreach (Tbl_ProductCategory::model()->findAll($criteria) as $row) {
					$array_id_product_current_category[] = $row['id_product'];
				}	
			}
			
			if(sizeof($array_id_parent_start)){
				 
				// Loop into every parent of the category that we drag
				foreach($array_id_parent_start as $value){
					// Verify if this category have childs
					$criteria->condition='id_parent=:id_parent and id<>:id'; 
					$criteria->params=array(':id_parent'=>$value,':id'=>$id); 		
					if (!Tbl_Category::model()->count($criteria)) {
						// If no child, delete the association with every product
						$criteria->condition='id_category=:id_category'; 
						$criteria->params=array(':id_category'=>$value); 					
						Tbl_ProductCategory::model()->deleteAll($criteria);
					}else{
						
						// We have to verify if one of the other child category have association with one of the product in the category that we drag...if not, we delete the association else we do nothing
						$keep_product = 0;
						// Put all sub category into global array: array_id_category_all_child
						$this->get_category_child($value,$id);
						// Loop into id_product in the category we drag
						foreach($array_id_product_current_category as $value_id_product){
							// Loop into array_id_category_all_child
							foreach($this->array_id_category_all_child as $value_id_child){
								$criteria->condition='id_category=:id_category and id_product=:id_product'; 
								$criteria->params=array(':id_category'=>$value_id_child,':id_product'=>$value_id_product); 		
								if (Tbl_ProductCategory::model()->count($criteria)) {
									$keep_product = 1;
									break;	
								}
							}
							if(!$keep_product){
								$criteria->condition='id_category=:id_category and id_product=:id_product'; 
								$criteria->params=array(':id_category'=>$value,':id_product'=>$value_id_product); 					
								
								Tbl_ProductCategory::model()->deleteAll($criteria);	
							}
							$keep_product = 0;
						}
					}
				}
			}
			
			if(sizeof($array_id_parent_end)){
				// Prepare the query			
				$command=$connection->createCommand('REPLACE INTO
				product_category 
				SET
				id_product=:id_product,
				id_category=:id_category');		
				// Loop into every parent of the category that we drag
				foreach($array_id_parent_end as $value){
					// Loop into id_product in the category we drag
					foreach($array_id_product_current_category as $value_id_product){
						$command->execute(array(':id_product'=>$value_id_product,':id_category'=>$value));	
					}
					
				}
			}
		
			// Set the order and assign the new parent to the category
			$criteria->condition='id_parent=:id_parent AND id!=:id'; 
			$criteria->params=array(':id_parent'=>$id_parent,':id'=>$id); 	
			$criteria->order='sort_order ASC';				
												
			$i=0;
			foreach (Tbl_Category::model()->findAll($criteria) as $row) {
				if ($i == $index) {
					$category->id_parent = $id_parent;
					$category->sort_order = $i;
					if (!$category->save()) {
						throw new CException('error saving order');	
					}
					
					++$i;
				}
				
				$row->sort_order = $i;
	
				if (!$row->save()) {
					throw new CException('error saving order');	
				}								
				
				++$i;	
			}		
			
			if ($i == $index) {								
				$category->id_parent = $id_parent;
				$category->sort_order = $index;
				if (!$category->save()) {
					throw new CException('error saving order');	
				}			
			}
	
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		
	}
	
	
	
	public function get_category_parent($id=0){		
		
		$id = (int)$id;
		
		if ($category = Tbl_Category::model()->findByPk($id)) {
			if($category->id_parent){
				return $category->id_parent;
			}else{
				return 0;
			}
		}
	}
	
	public function get_category_child($id_parent=0,$id_not=0)
	{
		$id_parent = (int)$id_parent;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent and id!=:id'; 
		$criteria->params=array(':id_parent'=>$id_parent,':id'=>$id_not); 				
			
		foreach (Tbl_Category::model()->findAll($criteria) as $row) {		
			$criteria2=new CDbCriteria; 
			$criteria2->condition='id_parent=:id_parent and id!=:id'; 
			$criteria2->params=array(':id_parent'=>$row->id,':id'=>$id_not); 						
			
			$this->array_id_category_all_child[$row->id] = $row->id;
			//echo $row->id . ' - ';
			
			if (Tbl_Category::model()->count($criteria2)) { $this->get_category_child($row->id,$id_not); }
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/** 
	 *
	 */
	public function actionXml_list_category_description($container, $id=0)
	{
		$model = new CategoriesForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($category = Tbl_Category::model()->findByPk($id)) {							
				// grab description information 
				foreach ($category->tbl_category_description as $row) {
					$model->category_description[$row->language_code]['name'] = $row->name;
					$model->category_description[$row->language_code]['description'] = $row->description;
					$model->category_description[$row->language_code]['meta_description'] = $row->meta_description;
					$model->category_description[$row->language_code]['meta_keywords'] = $row->meta_keywords;
					$model->category_description[$row->language_code]['alias'] = $row->alias;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_CategoryDescription::tableName());		
		$help_hint_path = '/catalog/categories/information/';		
		
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
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_category_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->category_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'name').'
						<div>'.
						CHtml::activeTextField($model,'category_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'onblur'=>'rewrite_alias($(this).attr("id"),"'.$container.'_category_description['.$value->code.'][alias]");', 'id'=>$container.'_category_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_category_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'description').'
						<div>'.
						CHtml::activeTextArea($model,'category_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'class'=>'editor', 'rows' => 6, 'id'=>$container.'_category_description['.$value->code.'][description]')).'
						<br /><span id="'.$container.'_category_description['.$value->code.'][description]_errorMsg" class="error"></span>
						</div>
					</div> 
					<h1>'.Yii::t('global','LABEL_TITLE_SEO').'</h1>  
					<div class="row">
						<strong>'.Yii::t('global','LABEL_META_DESCRIPTION').'</strong>'.
						(isset($columns['meta_description']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_category_description['.$value->code.'][meta_description]_maxlength">'.($columns['meta_description']-strlen($model->category_description[$value->code]['meta_description'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'meta-description').'
						<div>'.
						CHtml::activeTextArea($model,'category_description['.$value->code.'][meta_description]',array('style' => 'width: 98%;', 'rows' => 2,'maxlength'=>$columns['meta_description'], 'id'=>$container.'_category_description['.$value->code.'][meta_description]')).'
						<br /><span id="'.$container.'_category_description['.$value->code.'][meta_description]_errorMsg" class="error"></span>
						</div>
					</div>  
					<div class="row">
						<strong>'.Yii::t('global','LABEL_META_KEYWORDS').'</strong>'.
						(isset($columns['meta_keywords']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_category_description['.$value->code.'][meta_keywords]_maxlength">'.($columns['meta_keywords']-strlen($model->category_description[$value->code]['meta_keywords'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'meta-keywords').'
						<div>'.
						CHtml::activeTextArea($model,'category_description['.$value->code.'][meta_keywords]',array('style' => 'width: 98%;', 'rows' => 2,'maxlength'=>$columns['meta_keywords'], 'id'=>$container.'_category_description['.$value->code.'][meta_keywords]')).'
						<br /><span id="'.$container.'_category_description['.$value->code.'][meta_keywords]_errorMsg" class="error"></span>
						</div>
					</div>   
					<div class="row">
						<strong>'.Yii::t('global','LABEL_ALIAS').'</strong> ('.Yii::t('global','LABEL_ALIAS_CHARACTERS_ALLOWED').')'.
						(isset($columns['alias']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_category_description['.$value->code.'][alias]_maxlength">'.($columns['alias']-strlen($model->category_description[$value->code]['alias'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'alias').'
						<div>'.
						CHtml::activeTextField($model,'category_description['.$value->code.'][alias]',array('style' => 'width: 98%;','maxlength'=>$columns['alias'], 'onkeyup'=>'rewrite_alias($(this).attr("id"),"");', 'id'=>$container.'_category_description['.$value->code.'][alias]')).'
						<br /><span id="'.$container.'_category_description['.$value->code.'][alias]_errorMsg" class="error"></span>
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
	public function actionXml_list_categories($id=0)
	{		
		$id = (int)$id;
		$array=array();
		
		if ($id) {
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 		
			
			if (!Tbl_Category::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		
			$array[$id]=$id;
			$this->get_categories($array,$id);		
		}				
	
		// xml header
		header("content-type: text/xml");	
		
		$eol = "\r\n";
		
		echo '<?xml version="1.0" encoding="utf-8"?>'.$eol;
		echo '<tree id="0">'.$eol;		
		
		$this->get_categories_tree($array);		
		
		echo '</tree>'.$eol;
	}		
	
	/**
	 * This is a function to get a list of the categories and sub categories recursively
	 */
	public function get_categories_tree(&$array=array(),$id_parent=0)
	{
		$id_parent = (int)$id_parent;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent'; 
		$criteria->params=array(':id_parent'=>$id_parent); 				
		$criteria->order='sort_order ASC';	
		
		$eol = "\r\n";
			
		foreach (Tbl_Category::model()->getDescription()->findAll($criteria) as $row) {		
			if (!isset($array[$row->id])) {
				$criteria2=new CDbCriteria; 
				$criteria2->condition='id_parent=:id_parent'; 
				$criteria2->params=array(':id_parent'=>$row->id); 				
				$criteria2->order='sort_order ASC';			
				
				$child = Tbl_Category::model()->count($criteria2) ? 1:0;	
						
				echo '<item text="'.CHtml::encode($row->tbl_category_description[0]->name).'" id="'.$row->id.'" child="'.$child.'" call="true" open="1">'.$eol;
				
				if ($child) { $this->get_categories_tree($array,$row->id); }
				
				echo '</item>'.$eol;
			}
		}			
		
	}				

	/**
	 *	This is a function we will use to get the sub categories 
	 */	
	public function get_categories(&$array=array(),$id_parent=0)
	{
		$id_parent = (int)$id_parent;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent'; 
		$criteria->params=array(':id_parent'=>$id_parent); 				
		$criteria->order='sort_order ASC';	
		
		$eol = "\r\n";
			
		foreach (Tbl_Category::model()->getDescription()->findAll($criteria) as $row) {		
			$criteria2=new CDbCriteria; 
			$criteria2->condition='id_parent=:id_parent'; 
			$criteria2->params=array(':id_parent'=>$row->id); 				
			$criteria2->order='sort_order ASC';			
			
			$child = Tbl_Category::model()->count($criteria2) ? 1:0;	
					
			$array[$row->id] = CHtml::encode($row->tbl_category_description[0]->name);
			
			if ($child) { $this->get_categories($array,$row->id); }
		}					
	}				
		
	
				
	
	/**
	 * This is the action to get an XML list of the menu
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
				<Title><![CDATA['.Yii::t('controllers/CategoriesController','LABEL_INFORMATION').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/CategoriesController','LABEL_INFORMATION_DESCRIPTION').']]></Description>
			</item>
			<item id="edit_products">
				<Title><![CDATA['.Yii::t('controllers/CategoriesController','LABEL_PRODUCTS').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/CategoriesController','LABEL_PRODUCTS_DESCRIPTION').']]></Description>
				'.$disabled.'
			</item>
		</data>';
	}	
	
	
	
	
	/************************************************************
	*															*
	*															*
	*						PRODUCTS							*
	*															*
	*															*
	************************************************************/	
				
	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_products($id=0, $posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('product_category.id_category=:id_category');
		$params=array(':id_category'=>$id,':language_code'=>Yii::app()->language);
		
		$is_a_parent = 0;
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent'; 
		$criteria->params=array(':id_parent'=>$id); 		
		if (Tbl_Category::model()->count($criteria)) {
			$is_a_parent = 1;
		}
		
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
		
		$sql = 'SELECT 
		COUNT(product.id) AS total
		FROM 
		product_category
		INNER JOIN
		product
		ON
		(product_category.id_product = product.id)
		INNER JOIN
		product_description
		ON
		(product.id = product_description.id_product AND product_description.language_code = :language_code)
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
		product.id,
		product.sku,
		product_description.name
		FROM 
		product_category
		INNER JOIN
		product
		ON
		(product_category.id_product = product.id)
		INNER JOIN
		product_description
		ON
		(product.id = product_description.id_product AND product_description.language_code = :language_code)
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
		
		// sorting

		// name
		if ((isset($sort_col[0]) && !empty($sort_col[0]) && $is_a_parent) or (isset($sort_col[1]) && !empty($sort_col[1]) && !$is_a_parent)) {	
			if($is_a_parent){
				$direct = $sort_col[0] == 'des' ? 'DESC':'ASC';
			}else{
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			}
			
			$sql.=" ORDER BY product_description.name ".$direct;
		// sku
		} else if ((isset($sort_col[1]) && !empty($sort_col[1]) && $is_a_parent) or (isset($sort_col[2]) && !empty($sort_col[2]) && !$is_a_parent)) {
			
			if($is_a_parent){
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			}else{
				$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			}
			
			$sql.=" ORDER BY product.sku ".$direct;	
		} else {
			$sql.=" ORDER BY ";
			
			// name
			if (isset($filters['name']) && !empty($filters['name'])) {
				$sql.='
				CASE 
					WHEN product_description.name LIKE CONCAT(:name,"%") THEN 0               
					WHEN product_description.name LIKE CONCAT("%",:name,"%") THEN 1
				END,';
			}
			
			// sku
			if (isset($filters['sku']) && !empty($filters['sku'])) {
				$sql.='
				CASE 
					WHEN product.sku LIKE CONCAT(:sku,"%") THEN 0               
					WHEN product.sku LIKE CONCAT("%",:sku,"%") THEN 1
				END,';
			}				
		
			$sql.=" product_description.name ASC";
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
			'.(!$is_a_parent?'<cell type="ch" />':'').'
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	
	
	public function actionXml_list_product_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		if(isset($_GET['id_category'])){
			$id_category = (int)$_GET['id_category'];
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where=array('product_category.id_category IS NULL');
			
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
			
			$params[':id_category']=$id_category;				
			
			$sql = "SELECT 
			COUNT(product.id) AS total  
			FROM 
			product 
			INNER JOIN 
			product_description 
			ON 
			(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			product_category
			ON 
			product.id=product_category.id_product AND product_category.id_category = :id_category
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
			product_category.id_category,
			product.product_type,
			product.sell_price AS price
			FROM 
			product 
			INNER JOIN 
			product_description 
			ON 
			(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			product_category
			ON 
			product.id=product_category.id_product AND product_category.id_category = :id_category
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
	
	public function actionAdd_product()
	{
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		// current category
		$id_category = $_POST['id_category'];
		// current products
		$ids = $_POST['ids'];
		
		// Find every parent of the current category
		$array_id_parent = array();
		$array_id_parent[$id_category] = $id_category;
		$temp_id_cat = $this->get_category_parent($id_category);
		if($temp_id_cat){
			$array_id_parent[$temp_id_cat] = $temp_id_cat;
		}
		while($temp_id_cat){
			$temp_id_cat = $this->get_category_parent($temp_id_cat);
			if($temp_id_cat){
				$array_id_parent[$temp_id_cat] = $temp_id_cat;
			}
		}
		
		
		if(sizeof($array_id_parent)){
			// Prepare the query			
			$command=$connection->createCommand('REPLACE INTO
			product_category 
			SET
			id_product=:id_product,
			id_category=:id_category');		
			// Loop into every parent of the category that we drag
			foreach($array_id_parent as $value){
				// Loop into id_product in the category we drag
				foreach($ids as $value_id_product){
					$command->execute(array(':id_product'=>$value_id_product,':id_category'=>$value));	
				}
				
			}
		}
	}
	
	
	
	/**
	 * This is the action to delete a product suggestion
	 */
	public function actionDelete_product()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$criteria = new CDbCriteria;
		
		// current category
		$id_category = $_POST['id_category'];
		// current products
		$ids = $_POST['ids'];
		
		// Find every parent of the current category
		$array_id_parent = array();
		$temp_id_cat = $this->get_category_parent($id_category);
		if($temp_id_cat){
			$array_id_parent[$temp_id_cat] = $temp_id_cat;
		}
		while($temp_id_cat){
			$temp_id_cat = $this->get_category_parent($temp_id_cat);
			if($temp_id_cat){
				$array_id_parent[$temp_id_cat] = $temp_id_cat;
			}
		}
		
		// Prepare the query			
		$command=$connection->createCommand('DELETE FROM
		product_category 
		WHERE
		id_product=:id_product 
		AND
		id_category=:id_category');	
		
		if(sizeof($array_id_parent)){
				 
			// Loop into every parent of the category that we drag
			foreach($array_id_parent as $value){
				// Verify if this category have childs
				$criteria->condition='id_parent=:id_parent and id<>:id'; 
				$criteria->params=array(':id_parent'=>$value,':id'=>$id_category); 		
				if (!Tbl_Category::model()->count($criteria)) {
					// If no child, delete the association with every product
					foreach($ids as $value_id_product){
						$command->execute(array(':id_product'=>$value_id_product,':id_category'=>$value));	
					}
					
				}else{
					
					// We have to verify if one of the other child category have association with one of the product in the category that we drag...if not, we delete the association else we do nothing
					$keep_product = 0;
					// Reset global array
					$this->array_id_category_all_child = array();
					// Put all sub category into global array: array_id_category_all_child
					$this->get_category_child($value,$id_category);
					// Loop into id_product in the category we drag
					foreach($ids as $value_id_product){
						// Loop into array_id_category_all_child
						foreach($this->array_id_category_all_child as $value_id_child){
							$criteria->condition='id_category=:id_category and id_product=:id_product'; 
							$criteria->params=array(':id_category'=>$value_id_child,':id_product'=>$value_id_product); 		
							if (Tbl_ProductCategory::model()->count($criteria)) {
								$keep_product = 1;
								break;	
							}
						}
						if(!$keep_product){
							$command->execute(array(':id_product'=>$value_id_product,':id_category'=>$value));
						}
						$keep_product = 0;
					}
				}
			}
		}
		
	
		// Loop into id_product in the category we drag
		foreach($ids as $value_id_product){
			$command->execute(array(':id_product'=>$value_id_product,':id_category'=>$id_category));	
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