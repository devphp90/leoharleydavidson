<?php
class CustomersForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $id_customer_type=0; 
	public $firstname;
	public $lastname;
	public $language_code;
	public $email;
	public $dob;
	public $gender=0;
	public $tax_number;
	public $password;
	public $cpassword;
	public $activation_key;
	public $active=0;	

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
		
		if (empty($this->language_code)) {
			$this->addError('language_code',Yii::t('global','ERROR_EMPTY'));
		}
		
		if (empty($this->email)) {
			$this->addError('email',Yii::t('global','ERROR_EMPTY'));
		}else{
			$criteria=new CDbCriteria; 
			$criteria->condition='email=:email and id<>:id'; 
			$criteria->params=array(':email'=>$this->email,':id'=>$this->id); 	
			if (Tbl_Customer::model()->count($criteria)) {	
				$this->addError('email',Yii::t('global','ERROR_EXIST'));
			}
		}

		if (!empty($this->password) && empty($this->cpassword)) {
			$this->addError('cpassword',Yii::t('global','ERROR_EMPTY'));	
		} else if (empty($this->password) && !empty($this->cpassword)) {			
			$this->addError('password',Yii::t('global','ERROR_NO_MATCH'));	
		} else if (!empty($this->password) && strlen($this->password) < 6) {
			$this->addError('password',Yii::t('global','ERROR_MIN_CHARACTER').'6');	
		} else if (!empty($this->password) && !empty($this->cpassword) && $this->password != $this->cpassword) {
			$this->addError('password',Yii::t('global','ERROR_NO_MATCH'));	
			$this->addError('cpassword',Yii::t('global','ERROR_NO_MATCH'));				
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
			$model = Tbl_Customer::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_Customer;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		$model->id_customer_type = $this->id_customer_type;
		$model->firstname = $this->firstname;
		$model->lastname = $this->lastname;
		$model->language_code = $this->language_code;
		$model->email = $this->email;
		$model->dob = $this->dob;
		$model->gender = $this->gender;
		$model->tax_number = $this->tax_number;
		
		
		//$model->activation_key = $this->activation_key;
		$model->active = $this->active;		
		$model->id_user_modified = $current_id_user;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;
		if (!empty($this->password) && !empty($this->cpassword)) {
			$model->password = md5($this->id.$this->password);
		}
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
