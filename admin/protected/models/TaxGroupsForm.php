<?php
class TaxGroupsForm extends CFormModel
{
	// database fields
	public $id=0; 
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
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		// check description values for each languages	
		$columns = Html::getColumnsMaxLength(Tbl_TaxGroup::tableName());		
						
		// name is required
		$name = $this->name;
		if (empty($name)) {
			$this->addError('name',Yii::t('global','ERROR_EMPTY'));	
		} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
			$this->addError('name',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
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
			$model = Tbl_TaxGroup::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_TaxGroup;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		$model->name = $this->name;		
		$model->id_user_modified = $current_id_user;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;

		return true;
	}
}
