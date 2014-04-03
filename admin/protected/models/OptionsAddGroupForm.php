<?php
class OptionsAddGroupForm extends CFormModel
{
	// database fields
	public $id_options_group; 
	public $id_product; 

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
		
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{

		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();
		
		$model = new Tbl_ProductOptionsGroup;	
		$model->id_options_group = $this->id_options_group;
		$model->id_user_created = $current_id_user;			
		$model->date_created = $current_datetime;							
		$model->id_product = $this->id_product;

		
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
