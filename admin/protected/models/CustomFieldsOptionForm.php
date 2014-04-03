<?php
class CustomFieldsOptionForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $id_custom_fields=0;
	public $add_extra=0;
	public $extra_required=0;
	public $selected=0;
	public $custom_fields_option_description=array();

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
		if (!$this->id_custom_fields) {
			$this->addError('id_custom_fields',Yii::t('global','ERROR_EMPTY'));	
		} 
				
		// check description values for each languages	
		$columns = Html::getColumnsMaxLength(Tbl_CustomFieldsOptionDescription::tableName());
			
		foreach (Tbl_Language::model()->active()->findAll() as $value) {							
			// name is required
			$name = $this->custom_fields_option_description[$value->code]['name'];
			if (empty($name)) {
				$this->addError('custom_fields_option_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
				$this->addError('custom_fields_option_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
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
		$app = Yii::app();
		$current_id_user = (int)$app->user->getId();

		// edit or new
		if ($this->id) {
			$model = Tbl_CustomFieldsOption::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_CustomFieldsOption;	
			$model->id_custom_fields = $this->id_custom_fields;			
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;		
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_custom_fields=:id_custom_fields'; 
			$criteria->params=array(':id_custom_fields'=>$this->id_custom_fields); 				
			$criteria->order='sort_order ASC';
			
			$model->sort_order = Tbl_CustomFieldsOption::model()->count($criteria)+1;						
		}			

		$model->add_extra = $this->add_extra;
		$model->extra_required = $this->extra_required;
		$model->selected = $this->selected;
		
		if ($model->save()) {		
			if ($model->selected) Tbl_CustomFieldsOption::model()->updateAll(array('selected'=>0),'id_custom_fields=:id_custom_fields AND id != :id',array(':id_custom_fields'=>$model->id_custom_fields,':id'=>$model->id));
		
			foreach ($this->custom_fields_option_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_custom_fields_option=:id_custom_fields_option AND language_code=:language_code'; 
				$criteria->params=array(':id_custom_fields_option'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_CustomFieldsOptionDescription::model()->find($criteria)) {
					$model_description = new Tbl_CustomFieldsOptionDescription;				
					$model_description->id_custom_fields_option = $model->id;
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
