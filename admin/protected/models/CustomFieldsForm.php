<?php
class CustomFieldsForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $form=-1; 
	public $type=-1;
	public $required=0;
	public $sort_order=0;
	public $custom_fields_description=array();

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
		if (!$this->id && $this->form == -1) {
			$this->addError('form',Yii::t('global','ERROR_EMPTY'));	
		} 
		
		if ($this->type == -1) {
			$this->addError('type',Yii::t('global','ERROR_EMPTY'));	
		}
		
		// check description values for each languages	
		$columns = Html::getColumnsMaxLength(Tbl_CustomFieldsDescription::tableName());
			
		foreach (Tbl_Language::model()->active()->findAll() as $value) {							
			// name is required
			$name = $this->custom_fields_description[$value->code]['name'];
			if (empty($name)) {
				$this->addError('custom_fields_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
				$this->addError('custom_fields_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
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
			$model = Tbl_CustomFields::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_CustomFields;	
			$model->form = $this->form;			
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
			
			$criteria=new CDbCriteria; 
			$criteria->condition='form=:form'; 
			$criteria->params=array(':form'=>$this->form); 				
			$criteria->order='sort_order ASC';
			
			$model->sort_order = Tbl_CustomFields::model()->count($criteria)+1;				
		}			

		$model->type = $this->type;
		$model->required = $this->required;
		
		if ($model->save()) {		
			foreach ($this->custom_fields_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_custom_fields=:id_custom_fields AND language_code=:language_code'; 
				$criteria->params=array(':id_custom_fields'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_CustomFieldsDescription::model()->find($criteria)) {
					$model_description = new Tbl_CustomFieldsDescription;				
					$model_description->id_custom_fields = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->name = $value['name'];
				$model_description->description = $value['description'];
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
