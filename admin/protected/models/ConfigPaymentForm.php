<?php
class ConfigPaymentForm extends CFormModel
{
	// database fields
/*	public $id=0; 
	public $merchant_id; 
	public $active=1;
	public $credit_card=array();
	public $paypal=array();
	public $enable_auto_completed_order=0;
	public $enable_payment=0;*/
		
		
	public $enable_payment=0;
	public $payment_gateway=array();
	public $paypal=array();
	public $enable_cash_payments=0;
	public $enable_check_payments=0;
	public $check_payment_description='';
	public $enable_auto_completed_order=0;
	public $payment_gateway_extra=array();

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
		
		// si on active le paiement
		if ($this->enable_payment) {
			if (!$this->payment_gateway['id'] && !$this->paypal['enable_paypal'] && !$this->enable_cash_payments && !$this->enable_check_payments) {
				$this->addError('payment_gateway[id]',Yii::t('global','ERROR_EMPTY'));
				$this->addError('paypal[enable_paypal]',Yii::t('global','ERROR_EMPTY'));
				$this->addError('enable_cash_payments',Yii::t('global','ERROR_EMPTY'));
				$this->addError('enable_check_payments',Yii::t('global','ERROR_EMPTY'));
			}
			
			// si on choisi un payment gateway
			if ($this->payment_gateway['id'] && !$this->payment_gateway['merchant_id'] && $this->payment_gateway['id']!=4) $this->addError('payment_gateway[merchant_id]',Yii::t('global','ERROR_EMPTY'));
			
			//if we have id_user field to fill
			if (($this->payment_gateway['id'] && $this->payment_gateway['id']==3) && !$this->payment_gateway['user_id']) $this->addError('payment_gateway[user_id]',Yii::t('global','ERROR_EMPTY'));
			
			//if we have pin field to fill
			if (($this->payment_gateway['id'] && $this->payment_gateway['id']==3) && !$this->payment_gateway['pin']) $this->addError('payment_gateway[pin]',Yii::t('global','ERROR_EMPTY'));
			
			// si on active paypal
			if ($this->paypal['enable_paypal'] && !$this->paypal['paypal_api_username']) $this->addError('paypal[paypal_api_username]',Yii::t('global','ERROR_EMPTY'));
			if ($this->paypal['enable_paypal'] && !$this->paypal['paypal_api_password']) $this->addError('paypal[paypal_api_password]',Yii::t('global','ERROR_EMPTY'));
			if ($this->paypal['enable_paypal'] && !$this->paypal['paypal_api_signature']) $this->addError('paypal[paypal_api_signature]',Yii::t('global','ERROR_EMPTY'));
			
			// si on active le paiement par cheque
			if ($this->enable_check_payments && empty($this->check_payment_description)) $this->addError('check_payment_description',Yii::t('global','ERROR_EMPTY'));
			
			// E-xact.com
			if ($this->payment_gateway['id'] == 5) {
				foreach ($this->payment_gateway_extra as $key => $value) if ($value == '') $this->addError('payment_gateway_extra['.$key.']',Yii::t('global','ERROR_EMPTY'));
			}
		}
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
			
		Tbl_PaymentGateway::model()->updateAll(array('active'=>0));
		Tbl_ConfigCreditCard::model()->updateAll(array('active'=>0));
		
		// if we selected a payment gateway
		if ($this->payment_gateway['id'] && $model = Tbl_PaymentGateway::model()->findByPk($this->payment_gateway['id'])){	
			$model->merchant_id = trim($this->payment_gateway['merchant_id']);
			$model->user_id = trim($this->payment_gateway['user_id']);
			$model->pin = trim($this->payment_gateway['pin']);
			$model->active = 1;
			
			if (!$model->save()) {		
				throw new CException(Yii::t('global','ERROR_SAVING'));	
			}
			
			$this->payment_gateway['id'] = $model->id;
		
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id';

			foreach ($this->payment_gateway['credit_card'] as $key => $value) {
				$criteria->params=array(':id'=>$value); 	
				if($model = Tbl_ConfigCreditCard::model()->find($criteria)){
					$model->active = 1;	
					if (!$model->save()) {
						throw new CException(Yii::t('global','ERROR_SAVING'));		
					}
				}
			}
		}
				
		Tbl_Config::model()->updateAll(array('value'=>trim($this->paypal['enable_paypal'])),'name = "enable_paypal"');
		Tbl_Config::model()->updateAll(array('value'=>trim($this->paypal['paypal_api_username'])),'name = "paypal_api_username"');
		Tbl_Config::model()->updateAll(array('value'=>trim($this->paypal['paypal_api_password'])),'name = "paypal_api_password"');
		Tbl_Config::model()->updateAll(array('value'=>trim($this->paypal['paypal_api_signature'])),'name = "paypal_api_signature"');
		
		Tbl_Config::model()->updateAll(array('value'=>$this->enable_auto_completed_order),'name = "enable_auto_completed_order"');
		Tbl_Config::model()->updateAll(array('value'=>$this->enable_payment),'name = "enable_payment"');
		Tbl_Config::model()->updateAll(array('value'=>$this->enable_check_payments),'name = "enable_check_payments"');
		Tbl_Config::model()->updateAll(array('value'=>$this->enable_cash_payments),'name = "enable_cash_payments"');
		Tbl_Config::model()->updateAll(array('value'=>$this->check_payment_description),'name = "check_payment_description"');
		
		if($this->payment_gateway['id']){
			foreach ($this->payment_gateway_extra as $key => $value) Tbl_PaymentGatewayExtra::model()->updateAll(array('value'=>trim($value)),'name = "'.$key.'" AND id_payment_gateway = "'.$this->payment_gateway['id'].'"');
		}else{
			Tbl_PaymentGatewayExtra::model()->updateAll(array('value'=>''));
		}
		
		/*if($ok or $this->paypal['enable_paypal']){
			Tbl_Config::model()->updateAll(array('value'=>1),'name = "enable_payment"');
			Tbl_Config::model()->updateAll(array('value'=>1),'name = "display_price"');
		}else{
			Tbl_Config::model()->updateAll(array('value'=>0),'name = "enable_payment"');	
		} */
		
		return true;
	}
}
