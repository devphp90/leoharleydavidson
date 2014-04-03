<?php
class TaxesForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $code;
	public $tax_number;	
	public $tax_description=array();

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
		$columns = Html::getColumnsMaxLength(Tbl_TaxDescription::tableName());
		
		if (empty($this->code)) {
			$this->addError('code',Yii::t('global','ERROR_EMPTY'));	
		} else if (Tbl_Tax::model()->count('id!=:id AND code=:code',array('id'=>$this->id,'code'=>$this->code))) {
			$this->addError('code',Yii::t('global','ERROR_EXIST'));	
		}
				
		foreach (Tbl_Language::model()->active()->findAll() as $value) {							
			// name is required
			$name = $this->tax_description[$value->code]['name'];
			if (empty($name)) {
				$this->addError('tax_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
				$this->addError('tax_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
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
			$model = Tbl_Tax::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_Tax;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		$model->code = $this->code;
		$model->tax_number = $this->tax_number;		
		$model->id_user_modified = $current_id_user;
		if ($model->save()) {		
			foreach ($this->tax_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tax=:id_tax AND language_code=:language_code'; 
				$criteria->params=array(':id_tax'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_TaxDescription::model()->find($criteria)) {
					$model_description = new Tbl_TaxDescription;				
					$model_description->id_tax = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->name = $value['name'];
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
