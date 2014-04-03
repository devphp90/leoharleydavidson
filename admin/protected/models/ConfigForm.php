<?php
class ConfigForm extends CFormModel
{
	// database fields
	public $settings=array();

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
		if ($this->settings['cf_show_new_products_menu'] && !is_numeric($this->settings['cf_new_products_no_days'])) {
			$this->addError('settings[cf_new_products_no_days]',Yii::t('global','NOT_NUMERIC'));	
		}
		
		if ($this->settings['enable_order_email_notification'] && empty($this->settings['order_email_notification_email'])) {
			$this->addError('settings[order_email_notification_email]',Yii::t('global','ERROR_EMPTY'));	
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

		$criteria=new CDbCriteria; 
		$criteria->condition='name=:name'; 

		
		foreach ($this->settings as $key => $value) {
			$criteria->params=array(':name'=>$key); 	
			
			if (!$model = Tbl_Config::model()->find($criteria)) {
				$model = new Tbl_Config;				
			}
			
			$model->id_user_modified = $current_id_user;
			$model->name = $key;
			if($key == 'company_zip' or $key == 'shipping_sender_zip'){
				$value = strtoupper($value);
			}
			
			//Always need a value
			if($key == 'enable_free_shipping_min_cart_value' and empty($value)){
				$value = '0';
			}

			if($key == 'enable_local_pickup' and !$this->settings['enable_shipping']){
				$value = 1;
				// IF Shipping is disabled, we have to disabled all active Shipping gateway
				Tbl_ShippingGateway::model()->updateAll(array('active'=>0));
			}
			
			if($key == 'language'){
				Tbl_Language::model()->updateAll(array('default_language'=>0));
				$criteria2=new CDbCriteria; 
				$criteria2->condition='code=:code'; 
				$criteria2->params=array(':code'=>$value); 	
				Tbl_Language::model()->updateAll(array('default_language'=>1),$criteria2);
			}
			
			if($key == 'display_price' and !$this->settings['display_price']){
				// IF Price is disabled, we have to disabled all active Payment gateway
				Tbl_Config::model()->updateAll(array('value'=>0),'name = "enable_payment"');
				Tbl_Config::model()->updateAll(array('value'=>0),'name = "enable_paypal"');
				Tbl_PaymentGateway::model()->updateAll(array('active'=>0));
			}
			$model->value = trim($value);
			
			if (!$model->save()) {
				throw new CException(Yii::t('global','ERROR_SAVING'));		
			}
		}

		return true;
	}
}
