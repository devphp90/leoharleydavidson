<?php
class AddressPickupForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $address;
	public $city;
	public $zip;
	public $country_code=0;
	public $state_code=0;
	

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
		if (empty($this->address)) {
			$this->addError('address',Yii::t('global','ERROR_EMPTY'));	
		} 
		if (empty($this->city)) {
			$this->addError('city',Yii::t('global','ERROR_EMPTY'));	
		}
		if (empty($this->zip)) {
			$this->addError('zip',Yii::t('global','ERROR_EMPTY'));	
		}
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{

		
		// edit or new
		if ($this->id) {
			$model = Tbl_ConfigAddressPickup::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_ConfigAddressPickup;					
		}		
		
		$model->address = $this->address;
		$model->city = $this->city;
		$model->zip = strtoupper(trim($this->zip));
		$model->country_code = $this->country_code;
		$model->state_code = $this->state_code;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));		
		}
		
		$this->id = $model->id;
		
		return true;
	}
}
