<?php
class OptionsAddTagForm extends CFormModel
{
	// database fields
	public $id_options; 
	public $id_product_option_group; 

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

		$model = new Tbl_ProductOptionGroupOption;	
		$model->id_options = $this->id_options;							
		$model->id_product_option_group = $this->id_product_option_group;
		$model->active = 1;
		
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
