<?php
class ProductsAddTagTemplateForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $id_product=0;
	public $name; 

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
		// check description values for each languages	
		$columns = Html::getColumnsMaxLength(Tbl_TplTagGroup::tableName());	
		
		$criteria=new CDbCriteria; 
		$criteria->condition='name=:name AND id!=:id'; 
		$criteria->params=array(':name'=>$this->name, ':id'=>$this->id);				
		
		// name is required
		$name = $this->name;
		if (empty($name)) {
			$this->addError('name',Yii::t('global','ERROR_EMPTY'));	
		} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
			$this->addError('name',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
		} else if (Tbl_TplTagGroup::model()->count($criteria)) {
			$this->addError('name',Yii::t('global','ERROR_EXIST'));	
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
			$model = Tbl_TplTagGroup::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_TplTagGroup;	
			$model->id_user_created = $current_id_user;	
			$model->date_created = $current_datetime;										
		}
		$model->name = $this->name;	
		$model->id_user_modified = $current_id_user;
		
		if ($model->save()) {		
			$this->id = $model->id;
		
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product=:id_product'; 
			$criteria->params=array(':id_product'=>$this->id_product); 		
		
			foreach (Tbl_ProductTag::model()->findAll($criteria) as $row) {
				$model = new Tbl_TplTag;
				$model->id_tpl_tag_group = $this->id;
				$model->id_tag = $row->id_tag;					
				
				if (!$model->save())	{
					throw new CException(Yii::t('global','ERROR_SAVING'));	
				}
			}					
		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
