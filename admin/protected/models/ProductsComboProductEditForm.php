<?php
class ProductsComboProductEditForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $qty=1; 

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
		$this->qty = (int)$this->qty;
		
		if (!$this->qty) $this->addError('qty',Yii::t('global','ERROR_INVALID'));
	
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{		
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();
		
		if (!$model = Tbl_ProductCombo::model()->findByPk($this->id)) throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		
		$model->qty = $this->qty;
		$model->id_user_modified = $current_id_user;
		$model->date_created = $current_datetime;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}	
		
		return true;
	}
}
