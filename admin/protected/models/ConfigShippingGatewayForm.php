<?php
class ConfigShippingGatewayForm extends CFormModel
{
	// database fields
	public $id;
	public $access_key="";
	public $meter_number="";
	public $merchant_id;
	public $merchant_password="";
	public $active=1;

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
		if($this->id){
			if (empty($this->merchant_id) and $this->id != 3) {
				$this->addError('shipping_gateway_merchant_id',Yii::t('global','ERROR_EMPTY'));
			}
			// UPS Validation and FedEx
			if ($this->id == 1 || $this->id == 4) {
				if (empty($this->access_key)) {
					$this->addError('shipping_gateway_access_key',Yii::t('global','ERROR_EMPTY'));
				}
				// FedEx
				if ($this->id == 4) {
					if (empty($this->meter_number)) {
						$this->addError('shipping_gateway_meter_number',Yii::t('global','ERROR_EMPTY'));
					}					
				}
				if (empty($this->merchant_password)) {
					$this->addError('shipping_gateway_merchant_password',Yii::t('global','ERROR_EMPTY'));
				}
				// Verify required field in config table
				$criteria=new CDbCriteria; 
				$criteria->condition='name=:name';
				
				// shipping_sender_city
				$criteria->params=array(':name'=>"shipping_sender_city"); 	
				$model = Tbl_Config::model()->find($criteria);	

				if($model->value == ""){
					$this->addError('settings[shipping_sender_city]',Yii::t('global','ERROR_EMPTY'));
				}
				
				// shipping_sender_country_code
				$criteria->params=array(':name'=>"shipping_sender_country_code"); 	
				$model = Tbl_Config::model()->find($criteria);	

				if($model->value == ""){
					$this->addError('settings[shipping_sender_country_code]',Yii::t('global','ERROR_EMPTY'));
				}
				
				// shipping_sender_zip
				$criteria->params=array(':name'=>"shipping_sender_zip"); 	
				$model = Tbl_Config::model()->find($criteria);	

				if($model->value == ""){
					$this->addError('settings[shipping_sender_zip]',Yii::t('global','ERROR_EMPTY'));
				}
				
			// If Canada Post	
			}else if ($this->id == 2) {
				$this->access_key = "";
				$this->merchant_password = "";
			}
		}
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{

		Tbl_ShippingGateway::model()->updateAll(array('active'=>0));
		
		if($this->id){
			$model = Tbl_ShippingGateway::model()->findByPk($this->id);		
			
			$model->access_key = trim($this->access_key);
			$model->meter_number = trim($this->meter_number);
			$model->merchant_id = trim($this->merchant_id);
			$model->merchant_password = trim($this->merchant_password);
			$model->active = 1;
			
			if (!$model->save()) {		
				throw new CException(Yii::t('global','ERROR_SAVING'));	
			}
		}
		
		return true;
	}
}
