<?php
class CustomerTypeForm extends CFormModel
{
	// database fields
	public $id;	
	public $percent_discount='';
	public $taxable=1;
	public $apply_on_rebate=0;
	public $name='';

	/**
	 * Declares the validation rules.
	 */	
	public function rules()
	{
		return array(
		);
	}	  

	public function validate()
	{
		// check if we have a percentage discount
		if (!empty($this->percent_discount) && (!is_numeric($this->percent_discount) || $this->percent_discount > 100)) {
			$this->addError('percent_discount',Yii::t('global','ERROR_INVALID'));
		}
		
		// name is required
		if (empty($this->name)) {
			$this->addError('name',Yii::t('global','ERROR_EMPTY'));	
		}
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = Yii::app()->user->getId();
		
		// edit or new
		if ($this->id) {
			$model = Tbl_CustomerType::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_CustomerType;
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;
		}				
		
		$model->name = $this->name;
		$model->percent_discount = $this->percent_discount;
		$model->taxable = $this->taxable;
		$model->apply_on_rebate = $this->apply_on_rebate;
		$model->id_user_modified = $current_id_user;
		$model->save();
		
		$this->id = $model->id;
		
		return true;
	}	
}
