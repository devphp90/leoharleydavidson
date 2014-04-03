<?php
class CategoriesForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $id_parent=0;
	public $start_date;
	public $end_date;
	public $featured=0;
	public $display_type=0;
	public $product_sort_by=0;
	public $price_increment=0;
	public $sort_order=0;
	public $active=0;
	public $category_description=array();

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	 
	
	public function rules()
	{
		return array(	
		);
	}	  

	public function validate()
	{			
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		// check description values for each languages	
		$columns = Html::getColumnsMaxLength(Tbl_CategoryDescription::tableName());
		
		if (!empty($this->start_date) && !empty($this->end_date) && strtotime($this->start_date) >= strtotime($this->end_date)) {
			$this->addError('start_date',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->end_date)));
		}			
		
		// check if alias is already in use				
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
			
		foreach (Tbl_Language::model()->active()->findAll() as $value) {							
			// name is required
			$name = $this->category_description[$value->code]['name'];
			if (empty($name)) {
				$this->addError('category_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
				$this->addError('category_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
			}						
			
			/*// description is required
			$description = $this->category_description[$value->code]['description'];
			if (empty($description)) {
				$this->addError('category_description['.$value->code.'][description]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['description']) && strlen($description) > $columns['description']) {
				$this->addError('category_description['.$value->code.'][description]',Yii::t('global','ERROR_MAXLENGTH').$columns['description']);	
			}*/		
			
			// meta_description is required
			$meta_description = $this->category_description[$value->code]['meta_description'];
			if (empty($this->category_description[$value->code]['meta_description'])) {
				$this->addError('category_description['.$value->code.'][meta_description]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['meta_description']) && strlen($meta_description) > $columns['meta_description']) {
				$this->addError('category_description['.$value->code.'][meta_description]',Yii::t('global','ERROR_MAXLENGTH').$columns['meta_description']);	
			}			
			
			// meta_keywords is required
			$meta_keywords = $this->category_description[$value->code]['meta_keywords'];
			if (empty($this->category_description[$value->code]['meta_keywords'])) {
				$this->addError('category_description['.$value->code.'][meta_keywords]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['meta_keywords']) && strlen($meta_keywords) > $columns['meta_keywords']) {
				$this->addError('category_description['.$value->code.'][meta_keywords]',Yii::t('global','ERROR_MAXLENGTH').$columns['meta_keywords']);	
			}	
						
			// alias is required
			$alias = mb_strtolower($this->category_description[$value->code]['alias'],'utf-8');
			$this->category_description[$value->code]['alias'] = $alias;														
			if (empty($alias)) {								
				$this->addError('category_description['.$value->code.'][alias]',Yii::t('global','ERROR_EMPTY'));	
			} else {
				if (isset($columns['alias']) && strlen($alias) > $columns['alias']) {
					$this->addError('category_description['.$value->code.'][alias]',Yii::t('global','ERROR_MAXLENGTH').$columns['alias']);	
				// check if alias is valid
				} else if (!preg_match('/[^0-9a-z-_\s]/',$alias)) {										
					// check if alias is already in use									
					$row = $command->queryRow(true, array(':id'=>$this->id,':id_parent'=>$this->id_parent,':alias'=>$alias,':language_code'=>$value->code));
					
					// if in use, tell us by whom
					if ($row['id']) {												
						$this->addError('category_description['.$value->code.'][alias]',Yii::t('global','ERROR_ALIAS_EXIST') . ' '.$row['name']);				
					}				
				} else {
					$this->addError('category_description['.$value->code.'][alias]',Yii::t('global','ERROR_ALIAS_INVALID'));					
				}
			}
		}
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id AND id=:id_parent'; 
		$criteria->params=array(':id_parent'=>$this->id_parent,':id'=>$this->id); 				
		
		if($this->id and (($this->id_parent == $this->id) or Tbl_Category::model()->count($criteria))){
			$this->addError('category',Yii::t('global','ERROR_INVALID_CATEGORY'));	
		}	
		
		
		
		// check if category has product whitout child			
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent'; 
		$criteria->params=array(':id_parent'=>$this->id_parent); 
		
		$criteria2=new CDbCriteria; 
		$criteria2->condition='id_category=:id_category'; 
		$criteria2->params=array(':id_category'=>$this->id_parent); 

		if (!Tbl_Category::model()->count($criteria) and Tbl_ProductCategory::model()->count($criteria2)) {	
			$this->addError('category',Yii::t('global','ERROR_ALREADY_PRODUCT_IN_CATEGORY') . ' '.$row['name']);				
		}		
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();
		
		// edit or new
		if ($this->id) {
			$model = Tbl_Category::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_Category;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_parent=:id_parent'; 
			$criteria->params=array(':id_parent'=>$this->id_parent); 				
			$criteria->order='sort_order ASC';
			
			$model->sort_order = Tbl_Category::model()->count($criteria)+1;
		}		
		
		$model->id_parent = $this->id_parent;
		$model->start_date = $this->start_date;
		$model->end_date = $this->end_date;		
		$model->featured = $this->featured;
		$model->display_type = $this->display_type;
		$model->product_sort_by = $this->product_sort_by;
		$model->price_increment = $this->price_increment;		
		$model->active = $this->active;		
		$model->id_user_modified = $current_id_user;
		if ($model->save()) {		
			foreach ($this->category_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_category=:id_category AND language_code=:language_code'; 
				$criteria->params=array(':id_category'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_CategoryDescription::model()->find($criteria)) {
					$model_description = new Tbl_CategoryDescription;				
					$model_description->id_category = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->name = $value['name'];
				$model_description->description = $value['description'];
				$model_description->meta_description = $value['meta_description'];
				$model_description->meta_keywords = $value['meta_keywords'];				
				$model_description->alias = $value['alias'];			
				if (!$model_description->save()) {
					if (!$this->id) { $model->delete(); }
					throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));	
				}
			}					
		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;
		
		return true;
	}
}
