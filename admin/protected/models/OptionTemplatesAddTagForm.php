<?php
class OptionTemplatesAddTagForm extends CFormModel
{
	// database fields
	public $id_options; 
	public $id_tpl_product_option_group; 

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
		
		$model = new Tbl_TplProductOptionGroupOption;	
		$model->id_options = $this->id_options;							
		$model->id_tpl_product_option_group = $this->id_tpl_product_option_group;
		$model->id_user_created = $current_id_user;			
		$model->date_created = $current_datetime;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_tpl_product_option_group=:id_tpl_product_option_group'; 
		$criteria->params=array(':id_tpl_product_option_group'=>$this->id_tpl_product_option_group); 
		$criteria->order='sort_order ASC';
		
		$model->sort_order = Tbl_TplProductOptionGroupOption::model()->count($criteria)+1;	
		
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
