<?php
class OrdersCustomerInfoForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $new_customer=0;
	public $id_customer=0;
	public $id_customer_new_value=0;	
	public $customer;
	public $id_customer_type=0; 
	public $email;
	public $billing_prefix;
	public $billing_firstname;
	public $billing_lastname;
	public $billing_middlename;
	public $billing_suffix;
	public $billing_company;
	public $billing_address;
	public $billing_city;
	public $billing_country_code;
	public $billing_state_code;
	public $billing_zip;
	public $billing_telephone;
	public $billing_fax;
	public $shipping_prefix;
	public $shipping_firstname;
	public $shipping_lastname;
	public $shipping_middlename;
	public $shipping_suffix;
	public $shipping_company;
	public $shipping_address;
	public $shipping_city;
	public $shipping_country_code;
	public $shipping_state_code;
	public $shipping_zip;
	public $shipping_telephone;
	public $shipping_fax;	
	public $same_as_billing=0;	
	public $save_billing_address=0;
	public $billing_address_name;
	public $save_shipping_address=0;
	public $shipping_address_name;	
	

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
		if (!$this->new_customer && !$this->id_customer) {
			$this->addError('id_customer',Yii::t('global','ERROR_EMPTY'));
		}
	
		if (empty($this->email)) {
			$this->addError('email',Yii::t('global','ERROR_EMPTY'));
		}	
		
		if (empty($this->billing_firstname)) {
			$this->addError('billing_firstname',Yii::t('global','ERROR_EMPTY'));
		}	
		
		if (empty($this->billing_lastname)) {
			$this->addError('billing_lastname',Yii::t('global','ERROR_EMPTY'));
		}	
		
		if (empty($this->billing_address)) {
			$this->addError('billing_address',Yii::t('global','ERROR_EMPTY'));
		}		
		
		if (empty($this->billing_city)) {
			$this->addError('billing_city',Yii::t('global','ERROR_EMPTY'));
		}			
		
		if (empty($this->billing_country_code)) {
			$this->addError('billing_country_code',Yii::t('global','ERROR_EMPTY'));
		}		
		
		if (empty($this->billing_zip)) {
			$this->addError('billing_zip',Yii::t('global','ERROR_EMPTY'));
		}	
		
		if (empty($this->billing_telephone)) {
			$this->addError('billing_telephone',Yii::t('global','ERROR_EMPTY'));
		}		
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_customer=:id_customer AND name=:name'; 
		$criteria->params=array(':id_customer'=>$this->id_customer,':name'=>$this->billing_address_name); 				
		
		if ($this->save_billing_address && empty($this->billing_address_name)) {
			$this->addError('billing_address_name',Yii::t('global','ERROR_EMPTY'));
		} else if ($this->save_billing_address && $this->id_customer && Tbl_CustomerAddress::model()->count($criteria)) {
			$this->addError('billing_address_name',Yii::t('global','ERROR_EXIST'));
		}
		
		if (!$this->same_as_billing) {
			if (empty($this->shipping_firstname)) {
				$this->addError('shipping_firstname',Yii::t('global','ERROR_EMPTY'));
			}	
			
			if (empty($this->shipping_lastname)) {
				$this->addError('shipping_lastname',Yii::t('global','ERROR_EMPTY'));
			}	
			
			if (empty($this->shipping_address)) {
				$this->addError('shipping_address',Yii::t('global','ERROR_EMPTY'));
			}		
			
			if (empty($this->shipping_city)) {
				$this->addError('shipping_city',Yii::t('global','ERROR_EMPTY'));
			}			
			
			if (empty($this->shipping_country_code)) {
				$this->addError('shipping_country_code',Yii::t('global','ERROR_EMPTY'));
			}		
			
			if (empty($this->shipping_zip)) {
				$this->addError('shipping_zip',Yii::t('global','ERROR_EMPTY'));
			}	
			
			if (empty($this->shipping_telephone)) {
				$this->addError('shipping_telephone',Yii::t('global','ERROR_EMPTY'));
			}		
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_customer=:id_customer AND name=:name'; 
			$criteria->params=array(':id_customer'=>$this->id_customer,':name'=>$this->shipping_address_name); 				
			
			if ($this->save_shipping_address && empty($this->shipping_address_name)) {
				$this->addError('shipping_address_name',Yii::t('global','ERROR_EMPTY'));
			} else if ($this->save_billing_address && $this->id_customer && Tbl_CustomerAddress::model()->count($criteria)) {
				$this->addError('shipping_address_name',Yii::t('global','ERROR_EXIST'));
			}		
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
			$model = Tbl_Orders::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_Orders;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		if ($this->new_customer) {
			$model_customer = new Tbl_Customer;
			$model_customer->id_customer_type = $this->id_customer_type;
			$model_customer->prefix = $this->billing_prefix;
			$model_customer->firstname = $this->billing_firstname;
			$model_customer->lastname = $this->billing_lastname;
			$model_customer->middlename = $this->billing_middlename;
			$model_customer->suffix = $this->billing_suffix;
			$model_customer->email = $this->email;
			//$model_customer->active = 1;
			$model_customer->id_user_created = $current_id_user;
			$model_customer->date_created = $current_datetime;
			$model_customer->id_user_modified = $current_id_user;
			$model_customer->date_modified = $current_datetime;
			
			if ($model_customer->save()) {
				$model->id_customer = $model_customer->id;									
			} else {
				throw new CException(Yii::t('global','ERROR_SAVING'));		
			}
		} else {
			$model->id_customer = $this->id_customer;	
		}
		
		if ($this->save_billing_address) {
			$model_customer_address = new Tbl_CustomerAddress;
			$model_customer_address->id_customer = $model->id_customer;
			$model_customer_address->name = $this->billing_address_name;
			$model_customer_address->prefix = $this->billing_prefix;
			$model_customer_address->firstname = $this->billing_firstname;
			$model_customer_address->lastname = $this->billing_lastname;
			$model_customer_address->middlename = $this->billing_middlename;
			$model_customer_address->suffix = $this->billing_suffix;
			$model_customer_address->company = $this->billing_company;
			$model_customer_address->address = $this->billing_address;
			$model_customer_address->city = $this->billing_city;
			$model_customer_address->country_code = $this->billing_country_code;
			$model_customer_address->state_code = $this->billing_state_code;
			$model_customer_address->zip = $this->billing_zip;
			$model_customer_address->telephone = $this->billing_telephone;
			$model_customer_address->fax = $this->billing_fax;
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_customer=:id_customer AND default_billing=1'; 
			$criteria->params=array(':id_customer'=>$model->id_customer); 					
			
			if (!Tbl_CustomerAddress::model()->count($criteria)) {
				$model_customer_address->default_billing = 1;	
			}
			
			if (!$model_customer_address->save()) {
				throw new CException(Yii::t('global','ERROR_SAVING'));	
			}
		}		
		
		if ($this->save_shipping_address) {
			$model_customer_address = new Tbl_CustomerAddress;
			$model_customer_address->id_customer = $model->id_customer;
			$model_customer_address->name = $this->shipping_address_name;
			$model_customer_address->prefix = $this->shipping_prefix;
			$model_customer_address->firstname = $this->shipping_firstname;
			$model_customer_address->lastname = $this->shipping_lastname;
			$model_customer_address->middlename = $this->shipping_middlename;
			$model_customer_address->suffix = $this->shipping_suffix;
			$model_customer_address->company = $this->shipping_company;
			$model_customer_address->address = $this->shipping_address;
			$model_customer_address->city = $this->shipping_city;
			$model_customer_address->country_code = $this->shipping_country_code;
			$model_customer_address->state_code = $this->shipping_state_code;
			$model_customer_address->zip = $this->shipping_zip;
			$model_customer_address->telephone = $this->shipping_telephone;
			$model_customer_address->fax = $this->shipping_fax;
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_customer=:id_customer AND default_shipping=1'; 
			$criteria->params=array(':id_customer'=>$model->id_customer); 					
			
			if (!Tbl_CustomerAddress::model()->count($criteria)) {
				$model_customer_address->default_shipping = 1;	
			}
			
			if (!$model_customer_address->save()) {
				throw new CException(Yii::t('global','ERROR_SAVING'));
			}
		}			
		
		$model->id_customer_type = $this->id_customer_type;		
		$model->email = $this->email;
		$model->billing_prefix = $this->billing_prefix;
		$model->billing_firstname = $this->billing_firstname;
		$model->billing_lastname = $this->billing_lastname;
		$model->billing_middlename = $this->billing_middlename;
		$model->billing_suffix = $this->billing_suffix;
		$model->billing_company = $this->billing_company;
		$model->billing_address = $this->billing_address;
		$model->billing_city = $this->billing_city;
		$model->billing_country_code = $this->billing_country_code;
		$model->billing_state_code = $this->billing_state_code;
		$model->billing_zip = $this->billing_zip;
		$model->billing_telephone = $this->billing_telephone;
		$model->billing_fax = $this->billing_fax;
		$model->same_as_billing = $this->same_as_billing;

		$model->shipping_prefix = $this->shipping_prefix;
		$model->shipping_firstname = $this->shipping_firstname;
		$model->shipping_lastname = $this->shipping_lastname;
		$model->shipping_middlename = $this->shipping_middlename;
		$model->shipping_suffix = $this->shipping_suffix;
		$model->shipping_company = $this->shipping_company;
		$model->shipping_address = $this->shipping_address;
		$model->shipping_city = $this->shipping_city;
		$model->shipping_country_code = $this->shipping_country_code;
		$model->shipping_state_code = $this->shipping_state_code;
		$model->shipping_zip = $this->shipping_zip;
		$model->shipping_telephone = $this->shipping_telephone;
		$model->shipping_fax = $this->shipping_fax;		

		$model->id_user_modified = $current_id_user;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}			
		
		$model->refresh();
		
		$this->id = $model->id;
		
		//$this->id = str_pad($model->id,Yii::app()->db->schema->getTable('orders')->columns['id']->size,0,STR_PAD_LEFT);
		
		return true;
	}
}
