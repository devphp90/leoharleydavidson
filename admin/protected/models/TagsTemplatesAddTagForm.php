<?php
class TagsTemplatesAddTagForm extends CFormModel
{
	// database fields
	public $id_tag; 
	public $id_tpl_tag_group; 

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

		$model = new Tbl_TplTag;	
		$model->id_tag = $this->id_tag;							
		$model->id_tpl_tag_group = $this->id_tpl_tag_group;
		
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
