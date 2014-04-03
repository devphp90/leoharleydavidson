<?php
class GiftCertificateForm extends CFormModel
{
	// database fields
	public $id=0;	
	public $code;
	public $price=0;
	public $active=0;
	public $id_customer;
	public $customer_name;
	public $comments;	
	public $person_name;	
	public $person_address;	
	public $person_email;	
	public $person_message;	
	public $shipping_method;	
	public $language_code;	
	public $sent;
	public $date_sent='-';

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
		if(empty($this->price)){
			$this->addError('price',Yii::t('global','ERROR_EMPTY'));	
		}else if (!empty($this->price) && !is_numeric($this->price)) {
			$this->addError('price',Yii::t('global','NOT_NUMERIC'));
		}
		
		// code is required
		if (empty($this->code)) {
			$this->addError('code',Yii::t('global','ERROR_EMPTY'));	
		}else{
			$criteria=new CDbCriteria; 
			$criteria->condition='code=:code AND id!=:id'; 
			$criteria->params=array(':code'=>$this->code,':id'=>$this->id); 					
			
			if ($model = Tbl_GiftCertificate::model()->find($criteria)) {			
				$this->addError('code',Yii::t('global','ERROR_EXIST'));	
			}	
		}
		// person_name is required
		if (empty($this->person_name)) {
			$this->addError('person_name',Yii::t('global','ERROR_EMPTY'));	
		}
		// id_customer is required
		if (empty($this->id_customer)) {
			$this->addError('id_customer',Yii::t('global','ERROR_EMPTY'));	
		}
		
		// email is required if Shipping method is email
		if (!$this->shipping_method and empty($this->person_email)) {
			$this->addError('person_email',Yii::t('global','ERROR_EMPTY'));	
		}else if(!empty($this->person_email)){
			$validator = new CEmailValidator;
			if(!$validator->validateValue($this->person_email))
			$this->addError('person_email',Yii::t('global','ERROR_INVALID'));		
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
			$model = Tbl_GiftCertificate::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_GiftCertificate;
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;
		}				
		
		$model->code = $this->code;
		$model->price = $this->price;
		$model->active = $this->active;
		$model->id_customer = $this->id_customer;
		$model->comments = $this->comments;
		$model->person_name = $this->person_name;
		$model->person_address = $this->person_address;
		$model->person_email = $this->person_email;
		$model->person_message = $this->person_message;
		$model->shipping_method = $this->shipping_method;
		$model->language_code = $this->language_code;
		$model->sent = $this->sent;	
		if(!$this->sent){
			$model->date_sent = '0000-00-00 00:00:00';
		}else{
			$model->date_sent = $this->date_sent;
		}
		$model->id_user_modified = $current_id_user;
		$model->save();
		
		$this->id = $model->id;
		
		return true;
	}	
}
