<?php
class CustomerAddressesForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $id_customer=0; 
	public $firstname;
	public $lastname;
	public $company;
	public $address;
	public $city;
	public $country_code;
	public $state_code;
	public $zip;
	public $lat;
	public $lng;
	public $telephone;
	public $fax;
	public $default_billing=0;
	public $default_shipping=0;

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
	
		if (empty($this->firstname)) {
			$this->addError('firstname',Yii::t('global','ERROR_EMPTY'));
		}	
		
		if (empty($this->lastname)) {
			$this->addError('lastname',Yii::t('global','ERROR_EMPTY'));
		}			
		
		if (empty($this->address)) {
			$this->addError('address',Yii::t('global','ERROR_EMPTY'));	
		}
		
		if (empty($this->city)) {
			$this->addError('city',Yii::t('global','ERROR_EMPTY'));	
		}			
		
		if (empty($this->country_code)) {
			$this->addError('country_code',Yii::t('global','ERROR_EMPTY'));	
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
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();
		
		// edit or new
		if ($this->id) {
			$model = Tbl_CustomerAddress::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_CustomerAddress;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		$model->id_customer = $this->id_customer;
		$model->firstname = $this->firstname;
		$model->lastname = $this->lastname;
		$model->company = $this->company;
		$model->address = $this->address;
		$model->city = $this->city;
		$model->country_code = $this->country_code;
		$model->state_code = $this->state_code;
		$model->zip = $this->zip;
		$model->lat = $this->lat;
		$model->lng = $this->lng;
		$model->telephone = $this->telephone;
		$model->fax = $this->fax;
		$model->default_billing = $this->default_billing;
		$model->default_shipping = $this->default_shipping;
		$model->id_user_modified = $current_id_user;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;
		
		return true;
	}
}
