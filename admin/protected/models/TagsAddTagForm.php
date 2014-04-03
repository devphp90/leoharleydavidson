<?php
class TagsAddTagForm extends CFormModel
{
	// database fields
	public $id_tag; 
	public $id_product; 
	public $id_user_created;
	public $date_created;

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

		$model = new Tbl_ProductTag;	
		$model->id_tag = $this->id_tag;							
		$model->id_product = $this->id_product;
		$model->id_user_created = $this->id_user_created;
		$model->date_created = $this->date_created;
		
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
