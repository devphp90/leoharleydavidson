<?php
class TagsForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $tag_description=array();
	public $id_product=0; //product id if necessary

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
		$columns = Html::getColumnsMaxLength(Tbl_TagDescription::tableName());
		
		// check if alias is already in use				
		$command=$connection->createCommand('SELECT 
		tag.id,
		tag_description.name 
		FROM 
		tag 
		INNER JOIN
		tag_description
		ON
		(tag.id = tag_description.id_tag AND tag_description.alias=:alias AND tag_description.language_code=:language_code)
		WHERE 
		tag_description.id_tag!=:id');			
			
		foreach (Tbl_Language::model()->active()->findAll() as $value) {							
			// name is required
			$name = $this->tag_description[$value->code]['name'];
			if (empty($name)) {
				$this->addError('tag_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
				$this->addError('tag_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
			}						
					
			// alias is required
			$alias = mb_strtolower($this->tag_description[$value->code]['alias'],'utf-8');
			$this->tag_description[$value->code]['alias'] = $alias;														
			if (empty($alias)) {								
				$this->addError('tag_description['.$value->code.'][alias]',Yii::t('global','ERROR_EMPTY'));	
			} else {
				if (isset($columns['alias']) && strlen($alias) > $columns['alias']) {
					$this->addError('tag_description['.$value->code.'][alias]',Yii::t('global','ERROR_MAXLENGTH').$columns['alias']);	
				// check if alias is valid
				} else if (!preg_match('/[^0-9a-z-_\s]/',$alias)) {										
					// check if alias is already in use									
					$row = $command->queryRow(true, array(':id'=>$this->id,':alias'=>$alias,':language_code'=>$value->code));
					// if in use, tell us by whom
					if ($row['id']) {												
						$this->addError('tag_description['.$value->code.'][alias]',Yii::t('global','ERROR_ALIAS_EXIST').' '.$row['name']);				
					}				
				} else {
					$this->addError('tag_description['.$value->code.'][alias]',Yii::t('global','ERROR_ALIAS_INVALID'));					
				}
			}
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
			$model = Tbl_Tag::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_Tag;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}			
		$model->id_user_modified = $current_id_user;
		if ($model->save()) {		
			foreach ($this->tag_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tag=:id_tag AND language_code=:language_code'; 
				$criteria->params=array(':id_tag'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_TagDescription::model()->find($criteria)) {
					$model_description = new Tbl_TagDescription;				
					$model_description->id_tag = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->name = $value['name'];
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
		//If create new from product
		if($this->id_product){
			//Verify if already exist
			$criteria2=new CDbCriteria; 
			$criteria2->condition='id_tag=:id_tag AND id_product=:id_product'; 
			$criteria2->params=array(':id_tag'=>$this->id,':id_product'=>$this->id_product); 		
			
			if (!Tbl_ProductTag::model()->count($criteria2)) {
				$model = new Tbl_ProductTag;	
				$model->id_user_created = $current_id_user;			
				$model->date_created = $current_datetime;							
				$model->id_tag = $this->id;	
				$model->id_product = $this->id_product;	
				if (!$model->save()) {		
					throw new CException(Yii::t('global','ERROR_SAVING'));	
				}
			}
		}

		return true;
	}
}
